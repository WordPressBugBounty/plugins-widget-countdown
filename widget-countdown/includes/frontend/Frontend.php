<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class wpdevart_countdown_frontend {

	function __construct() {
		$this->init();
	}

	private function init() {
		$this->include_files();
		add_shortcode( 'wpda_countdown', array( $this, 'shortcode' ) );
		add_shortcode( 'wpda_countdown_pro', array( $this, 'shortcode' ) );
		if ( WPDA_COUNTDOWN_IS_PRO ) {
			$this->create_popup();
			add_action( 'wp_footer', array( $this, 'render_sticky_bar' ), 50 );
		}
		if ( wpda_countdown_is_plugin_active( 'woocommerce/woocommerce.php' ) ) {
			$this->woocommerce_front_filters();
		}
	}
	private function enqueue_frontend_assets(){
		wp_enqueue_script('wpdevart_countdown_standart',wpda_countdown_plugin_url.'includes/frontend/js/front_timer_pro.js',array(),WPDA_COUNTDOWN_VERSION,true);
		wp_enqueue_style('wpdevart_countdown_front',wpda_countdown_plugin_url.'includes/frontend/css/front_end.css',array(),WPDA_COUNTDOWN_VERSION);
		wp_enqueue_style('animated');
	}
	private function include_files(){
		require_once(wpda_countdown_plugin_path.'includes/frontend/CountdownEngine.php');
		if ( WPDA_COUNTDOWN_IS_PRO ) {
			$popup_file=wpda_countdown_plugin_path.'includes/frontend/Popup.php';
			if(file_exists($popup_file)) require_once($popup_file);
		}
	}
	private function woocommerce_front_filters(){
		$single_prod_filter=get_option('wpdevart_countdown_product_position','woocommerce_single_product_summary');
		if($single_prod_filter!='dont_add'){
			add_filter($single_prod_filter,array($this,'woocomerce_front_end_single'),11);
		}
		$shop_filter=get_option('wpdevart_countdown_shop_position','woocommerce_shop_loop_item_title');
		if($shop_filter!='dont_add'){
			add_filter($shop_filter,array($this,'woocomerce_front_end_single'),1);
		}
		// AJAX handler for setting product out of stock after expire
		add_action('wp_ajax_wpda_woo_expire_product',array($this,'ajax_expire_product'));
		add_action('wp_ajax_nopriv_wpda_woo_expire_product',array($this,'ajax_expire_product'));
	}
	public function ajax_expire_product(){
		$product_id = isset( $_POST['product_id'] ) ? intval( $_POST['product_id'] ) : 0;
		$nonce      = isset( $_POST['nonce'] ) ? $_POST['nonce'] : '';
		if ( ! $product_id || ! wp_verify_nonce( $nonce, 'wpda_woo_expire_' . $product_id ) ) wp_die( '0' );
		if ( get_option( 'wpdevart_countdown_woo_after_expire', 'nothing' ) !== 'out_of_stock' ) wp_die( '0' );

		// Only per-product countdowns (not global)
		$enable = get_post_meta( $product_id, 'wpda_countdown_enable', true );
		if ( $enable !== 'enable' && $enable !== '1' ) wp_die( '0' );

		$timer_id = get_post_meta( $product_id, 'wpda_countdown_timer', true );
		if ( empty( $timer_id ) || $timer_id === '0' ) wp_die( '0' );

		// Server-side verification that timer has actually expired (don't trust client)
		if ( ! $this->timer_has_expired( $timer_id ) ) wp_die( '0' );

		$product = wc_get_product( $product_id );
		if ( ! $product ) wp_die( '0' );
		$product->set_stock_status( 'outofstock' );
		$product->save();
		wp_die( '1' );
	}

	/**
	 * Authoritative check: has this timer actually reached its end time?
	 * For evergreen timers (per-visitor), trusts the client since there's no global truth.
	 */
	private function timer_has_expired( $timer_id ) {
		$timer = wpda_countdown()->timer_repository()->find_data( $timer_id );
		if ( ! is_array( $timer ) ) return false;

		$type = isset( $timer['timer_coundown_type'] ) ? $timer['timer_coundown_type'] : 'countdown';
		if ( in_array( $type, array( 'evergreen_countdown', 'evergreen_countup' ), true ) ) {
			// Evergreen is per-visitor — no global expiration, trust the client
			return true;
		}

		if ( empty( $timer['timer_end_date'] ) ) return false;

		$saved_tz = date_default_timezone_get();
		if ( ! empty( $timer['timer_timezone'] ) ) {
			date_default_timezone_set( $timer['timer_timezone'] );
		}
		$parts = explode( ' ', $timer['timer_end_date'] );
		$d     = isset( $parts[0] ) ? explode( '/', $parts[0] ) : array();
		$t     = isset( $parts[1] ) ? explode( ':', $parts[1] ) : array( 0, 0 );
		if ( count( $d ) !== 3 ) {
			date_default_timezone_set( $saved_tz );
			return false;
		}
		$end = mktime( (int) $t[0], (int) $t[1], 0, (int) $d[1], (int) $d[0], (int) $d[2] );
		$now = time();
		date_default_timezone_set( $saved_tz );
		return $end > 0 && $now >= $end;
	}
	private function create_popup(){
		if(class_exists('wpdevart_countdown_popup'))
			new wpdevart_countdown_popup();
	}
	private function controller( $timer_id, $theme_id ) {
		$this->enqueue_frontend_assets();
		// If the plugin tables don't exist (e.g. an admin_init hasn't created
		// them yet, or they were manually dropped), render nothing rather than
		// letting $wpdb throw a visible error on the page.
		if ( ! wpda_countdown()->timer_repository()->table_exists() ) {
			return '';
		}
		$timer = $this->get_timer( $timer_id );
		if ( $timer == null ) {
			return current_user_can( 'manage_options' ) ? '<p><em>Countdown: invalid timer id.</em></p>' : '';
		}
		$timer=json_decode($timer->option_value,true);
		if(!is_array($timer)) $timer=array();
		$theme=$this->get_theme($theme_id);
		$theme_decoded=$theme?json_decode($theme->option_value,true):null;
		if(!is_array($theme_decoded)) $theme_decoded=array();
		$timer_default=wpda_countdown_timer_page::get_default_values_array();
		$theme_default=wpda_countdown_theme_page::get_default_values_array();
		$timer=array_merge($timer_default,$timer);
		$timer["timer_id"]=$timer_id;
		$theme=array_merge($theme_default,$theme_decoded);

		if(!WPDA_COUNTDOWN_IS_PRO){
			if(in_array($timer['timer_coundown_type'],array('countup','evergreen_countdown','evergreen_countup'),true))
				$timer['timer_coundown_type']='countdown';
			$timer['timer_coundown_repeat']='none';
			$theme['countdown_type']='standart';
			$theme['countdown_date_display']=array('day','hour','minut','second');
		}

		$countdown_html='';
		switch($theme["countdown_type"]){
			case "standart":
				$obj= new wpdevart_countdown_forntend_stanadart_view($timer,$theme);
				$countdown_html=$obj->create_countdown();
				break;
			case "circle":
				$obj= new wpdevart_countdown_forntend_circle_view($timer,$theme);
				$countdown_html=$obj->create_countdown();
				break;
			case "vertical":
				$obj= new wpdevart_countdown_forntend_vertical_view($timer,$theme);
				$countdown_html=$obj->create_countdown();
				break;
			case "flip":
				$obj= new wpdevart_countdown_forntend_flip_view($timer,$theme);
				$countdown_html=$obj->create_countdown();
				break;
		}
		return $countdown_html;
	}

	public function render_sticky_bar(){
		require_once(wpda_countdown_plugin_path.'includes/admin/StickyBarController.php');
		if(!wpda_countdown_sticky_bar_page::is_enabled()) return;
		$s=wpda_countdown_sticky_bar_page::get_settings();
		if(empty($s['sticky_timer_id'])||$s['sticky_timer_id']==='0') return;
		if(!$this->sticky_should_show($s)) return;
		$countdown_html=$this->controller($s['sticky_timer_id'],isset($s['sticky_theme_id'])?$s['sticky_theme_id']:'0');
		if(empty($countdown_html)) return;
		$pos=(isset($s['sticky_position'])&&$s['sticky_position']==='bottom')?'bottom:0':'top:0';
		$bg=esc_attr(isset($s['sticky_bg_color'])?$s['sticky_bg_color']:'#1d2327');
		$color=esc_attr(isset($s['sticky_text_color'])?$s['sticky_text_color']:'#ffffff');
		$msg=isset($s['sticky_message'])?$s['sticky_message']:'';
		$msg_pos=isset($s['sticky_message_position'])?$s['sticky_message_position']:'left';
		$closable=isset($s['sticky_closable'])&&$s['sticky_closable']==='yes';
		$close_color=esc_attr(isset($s['sticky_close_color'])?$s['sticky_close_color']:'#ffffff');
		$close_size=intval(isset($s['sticky_close_size'])?$s['sticky_close_size']:22);
		$close_pos=isset($s['sticky_close_position'])&&$s['sticky_close_position']==='left'?'left:8px':'right:8px';
		$close_bg_color=esc_attr(isset($s['sticky_close_bg_color'])?$s['sticky_close_bg_color']:'#000000');
		$close_bg_opacity=intval(isset($s['sticky_close_bg_opacity'])?$s['sticky_close_bg_opacity']:15);
		$close_bg_rgba=Wpda_Countdown_Admin_Fields::hex2rgba($close_bg_color,$close_bg_opacity/100);
		$icon_style=isset($s['sticky_close_icon'])?$s['sticky_close_icon']:'cross';
		$svg_icons=array(
			'cross'=>'<svg viewBox="0 0 24 24" width="1em" height="1em" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M18 6L6 18M6 6l12 12"/></svg>',
			'times'=>'<svg viewBox="0 0 24 24" width="1em" height="1em" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round"><path d="M17 7L7 17M7 7l10 10"/></svg>',
			'circle_x'=>'<svg viewBox="0 0 24 24" width="1em" height="1em" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><circle cx="12" cy="12" r="10"/><path d="M15 9l-6 6M9 9l6 6"/></svg>',
		);
		$close_content=isset($svg_icons[$icon_style])?$svg_icons[$icon_style]:$svg_icons['cross'];
		$hide_on_expire=(isset($s['sticky_hide_after_expire'])&&$s['sticky_hide_after_expire']==='yes')?'yes':'no';
		$msg_html=$msg?'<span class="wpda_sticky_msg" style="font-weight:500;">'.wp_kses_post($msg).'</span>':'';
		$timer_html='<span class="wpda_sticky_timer">'.$countdown_html.'</span>';
		$content_order=$msg_pos==='right'?$timer_html.$msg_html:$msg_html.$timer_html;
		echo '<div class="wpda_sticky_bar" data-hide-on-expire="'.$hide_on_expire.'" style="position:fixed;'.$pos.';left:0;width:100%;z-index:99999;background:'.$bg.';color:'.$color.';box-shadow:0 2px 12px rgba(0,0,0,0.15);font-family:-apple-system,BlinkMacSystemFont,Segoe UI,Roboto,sans-serif;font-size:14px;box-sizing:border-box;">'
			.'<div style="display:flex;align-items:center;justify-content:center;gap:15px;padding:10px 20px;max-width:100%;">'
			.$content_order
			.'</div>'
			.($closable?'<span class="wpda_sticky_close" onclick="this.closest(\'.wpda_sticky_bar\').style.display=\'none\'" style="position:absolute;'.$close_pos.';top:4px;cursor:pointer;font-size:'.$close_size.'px;color:'.$close_color.';line-height:1;font-weight:700;width:28px;height:28px;display:flex;align-items:center;justify-content:center;border-radius:50%;background:'.$close_bg_rgba.';">'.$close_content.'</span>':'')
			.'</div>';
	}

	private function sticky_should_show($s){
		// Legacy simple mode — old saved data before multi-select was added
		if(isset($s['sticky_show_on'])&&!isset($s['sticky_show_action'])){
			$show_on=$s['sticky_show_on'];
			if($show_on==='all') return true;
			if($show_on==='front'&&is_front_page()) return true;
			if($show_on==='posts'&&is_single()) return true;
			if($show_on==='pages'&&is_page()) return true;
			if($show_on==='shop'&&function_exists('is_woocommerce')&&(is_shop()||is_product_category()||is_product())) return true;
			return false;
		}

		// New multi-select mode — same logic as popup
		$action=isset($s['sticky_show_action'])?$s['sticky_show_action']:'hide';
		$selected=isset($s['sticky_show_on_multiple'])&&is_array($s['sticky_show_on_multiple'])?$s['sticky_show_on_multiple']:array();
		if(empty($selected)) return true;

		global $wp_query;
		$additional='not_cached';
		$main='not_cached';

		foreach($selected as $value){
			if($value==='logged_in'&&is_user_logged_in()) $additional=true;
			if($value==='logged_out'&&!is_user_logged_in()) $additional=true;
			if($value==='mobile'&&wp_is_mobile()) $additional=true;
			if($value==='desktop'&&!wp_is_mobile()) $additional=true;
			if($value==='front_page'&&is_front_page()) $main=true;
			if($value==='blog_page'&&is_home()&&!is_front_page()) $main=true;
			if($value==='single_post'&&is_single()) $main=true;
			if($value==='sticky_post'&&is_sticky()) $main=true;
			if($value==='date_archive'&&is_date()) $main=true;
			if($value==='author_archive'&&is_author()) $main=true;
			if($value==='search_page'&&is_search()) $main=true;
			if($value==='404_page'&&is_404()) $main=true;
			if(preg_match('/^page_(\d+)$/',$value,$pm)&&isset($wp_query->post)&&$wp_query->is_page&&$wp_query->post->ID==intval($pm[1])) $main=true;
			if(preg_match('/^post_(\d+)$/',$value,$pm)&&isset($wp_query->post)&&$wp_query->is_single&&!$wp_query->is_attachment&&$wp_query->post->ID==intval($pm[1])) $main=true;
			if(preg_match('/^category_(\d+)$/',$value,$pm)&&$wp_query->is_category&&get_queried_object_id()==intval($pm[1])) $main=true;
			if(preg_match('/^custom_post_type_(.+)$/',$value,$pm)&&$wp_query->is_single&&$pm[1]===get_post_type()) $main=true;
			if(preg_match('/^taxonomy_(.+)$/',$value,$pm)&&is_tax()){
				$obj=get_queried_object();
				if($obj&&!empty($obj->taxonomy)&&$obj->taxonomy===$pm[1]) $main=true;
			}
		}

		$matched=($additional===true&&$main===true)||($additional===true&&$main==='not_cached')||($additional==='not_cached'&&$main===true);

		return $action==='show'?$matched:!$matched;
	}
	private function get_timer($timer_id){
		return wpda_countdown()->timer_repository()->find( $timer_id );
	}
	private function get_theme($theme_id){
		return wpda_countdown()->theme_repository()->find_or_default( $theme_id );
	}
	public function shortcode( $atts ) {
		if ( isset( $atts['end_date'] ) && ! empty( $atts['end_date'] ) ) {
			return $this->quick_countdown( $atts );
		}
		if ( empty( $atts['timer_id'] ) || empty( $atts['theme_id'] ) ) {
			return current_user_can( 'manage_options' ) ? '<p><em>Countdown shortcode: timer_id and theme_id are required.</em></p>' : '';
		}
		return $this->controller( intval( $atts['timer_id'] ), intval( $atts['theme_id'] ) );
	}

	private function quick_countdown($atts){
		$this->enqueue_frontend_assets();
		$timer_default=wpda_countdown_timer_page::get_default_values_array();
		$theme_default=wpda_countdown_theme_page::get_default_values_array();
		$end_date=$atts['end_date'];
		// auto-detect ISO format (yyyy-mm-dd) and convert to dd/mm/yyyy
		if(preg_match('/^\d{4}-\d{2}-\d{2}/',$end_date)){
			$parts=explode(' ',$end_date);
			$d=explode('-',$parts[0]);
			$end_date=$d[2].'/'.$d[1].'/'.$d[0].(isset($parts[1])?' '.$parts[1]:' 23:59');
		}
		$timer=array_merge($timer_default,array(
			'timer_coundown_type'=>'countdown',
			'timer_start_time'=>date('d/m/Y H:i'),
			'timer_end_date'=>$end_date,
			'timer_timezone'=>isset($atts['timezone'])?$atts['timezone']:date_default_timezone_get(),
			'after_countdown_end_type'=>isset($atts['action'])?$atts['action']:'hide',
			'after_countdown_text'=>isset($atts['expire_text'])?wp_kses_post($atts['expire_text']):'',
		));
		$timer['timer_id']='quick_'.uniqid();
		$theme_id=isset($atts['theme_id'])&&$atts['theme_id']!=='0'?$atts['theme_id']:0;
		if($theme_id){
			$theme_data = wpda_countdown()->theme_repository()->find_data( $theme_id );
			$theme = $theme_data ?: $theme_default;
		}else{
			$theme=$theme_default;
		}
		$theme=array_merge($theme_default,$theme);
		if(!WPDA_COUNTDOWN_IS_PRO){
			$theme['countdown_type']='standart';
			$theme['countdown_date_display']=array('day','hour','minut','second');
		}
		switch($theme["countdown_type"]){
			case "standart":
				$obj=new wpdevart_countdown_forntend_stanadart_view($timer,$theme);
				return $obj->create_countdown();
			case "circle":
				$obj=new wpdevart_countdown_forntend_circle_view($timer,$theme);
				return $obj->create_countdown();
			case "vertical":
				$obj=new wpdevart_countdown_forntend_vertical_view($timer,$theme);
				return $obj->create_countdown();
			case "flip":
				$obj=new wpdevart_countdown_forntend_flip_view($timer,$theme);
				return $obj->create_countdown();
		}
		$obj=new wpdevart_countdown_forntend_stanadart_view($timer,$theme);
		return $obj->create_countdown();
	}
	
	public function woocomerce_front_end_single(){
		$prod_id=get_the_ID();
		$enable = get_post_meta( $prod_id, 'wpda_countdown_enable', true );

		// "disable" or legacy "0" → no countdown
		if($enable === 'disable' || $enable === '0') return '';

		$timer=null;$theme=null;$is_per_product=false;

		// "enable" or legacy "1" → use per-product timer/theme
		if($enable === 'enable' || $enable === '1'){
			$t = get_post_meta( $prod_id, 'wpda_countdown_timer', true );
			$th = get_post_meta( $prod_id, 'wpda_countdown_theme', true );
			if(!empty($t) && $t !== '0'){
				$timer=$t;$theme=$th;$is_per_product=true;
			}
		}

		// "global" or empty or per-product not set → use global settings
		if($timer===null && get_option('woocommerce_enable_timer_in_all_prod','disable') === 'enable'){
			$t = get_option('wpdevart_countdown_woocommerce_all_timer','0');
			$th = get_option('wpdevart_countdown_woocommerce_all_theme','0');
			if(!empty($t) && $t !== '0'){
				$timer=$t;$theme=$th;
			}
		}

		if($timer===null) return;
		echo $this->controller($timer, $theme);

		// inject out-of-stock AJAX ONLY for per-product countdowns, not global
		if($is_per_product && is_product() && get_option('wpdevart_countdown_woo_after_expire','nothing')==='out_of_stock'){
			$nonce=wp_create_nonce('wpda_woo_expire_'.$prod_id);
			echo '<script>(function(){
				var fired=false,wasActive=false,goneAt=0;
				function findEl(){
					var all=document.querySelectorAll("[id^=wpdevart_countdown_]");
					return all.length?all[all.length-1]:null;
				}
				var check=setInterval(function(){
					if(fired) return;
					var el=findEl();
					if(!el) return;
					var cd=el.querySelector(".wpdevart_countdown_element");
					if(cd){wasActive=true;goneAt=0;return;}
					if(wasActive&&!cd){
						if(!goneAt){goneAt=Date.now();return;}
						if(Date.now()-goneAt<10000) return;
						var recheck=findEl();
						if(recheck&&recheck.querySelector(".wpdevart_countdown_element")){goneAt=0;return;}
						fired=true;clearInterval(check);
						var x=new XMLHttpRequest();
						x.open("POST","'.esc_url(admin_url('admin-ajax.php')).'");
						x.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
						x.send("action=wpda_woo_expire_product&product_id='.intval($prod_id).'&nonce='.esc_js($nonce).'");
					}
				},2000);
				setTimeout(function(){clearInterval(check);},86400000);
			})()</script>';
		}
	}
}