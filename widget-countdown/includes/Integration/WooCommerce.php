<?php
if ( ! defined( "ABSPATH" ) ) exit;
/*WpDevArt Countdown Pro woocommerce class*/
class wpda_countdown_woocomerce{

	function __construct(){

	}
	public function add_metabox(){
		add_meta_box('wpda_countdown_woocommerce_meta', 'WpDevArt Countdown', array( $this, 'display_metabox' ), 'product', 'side', 'high');
	}
	public function display_metabox(){
		$id = get_the_ID();
		wp_nonce_field('wpda_countdown_woo_meta', 'wpda_countdown_woo_nonce');
		$enable = get_post_meta( $id, 'wpda_countdown_enable', true );
		$timer = get_post_meta( $id, 'wpda_countdown_timer', true );
		$theme = get_post_meta( $id, 'wpda_countdown_theme', true );
		// migrate old values: old "1" → "enable", old "0" → "disable", empty → "global"
		if($enable === '1') $enable = 'enable';
		elseif($enable === '0') $enable = 'disable';
		elseif($enable === '' || $enable === false) $enable = 'global';

		$show_selects = ($enable === 'enable') ? '' : 'display:none;';
		?>
		<div style="margin-bottom:8px;">
			<label style="font-weight:600;display:block;margin-bottom:4px;">Countdown</label>
			<select style="width:100%;" name="wpdevart_countdown_enable" id="wpdevart_countdown_enable">
				<option <?php selected($enable,'global'); ?> value="global">Use global settings</option>
				<option <?php selected($enable,'enable'); ?> value="enable">Custom for this product</option>
				<option <?php selected($enable,'disable'); ?> value="disable">Disabled</option>
			</select>
			<p class="description" style="margin-top:4px;font-size:11px;color:#646970;" id="wpda_enable_desc"></p>
		</div>
		<div id="wpda_woo_selects" style="<?php echo $show_selects; ?>">
			<?php echo $this->create_timer_select($timer); ?>
			<?php echo $this->create_theme_select($theme); ?>
		</div>
		<script>
		jQuery(function($){
			var desc={
				'global':'Timer and theme from WooCommerce → Countdown settings.',
				'enable':'Select a specific timer and theme for this product.',
				'disable':'No countdown will appear on this product.'
			};
			function toggle(){
				var v=$('#wpdevart_countdown_enable').val();
				$('#wpda_woo_selects')[v==='enable'?'slideDown':'slideUp'](200);
				$('#wpda_enable_desc').text(desc[v]||'');
			}
			$('#wpdevart_countdown_enable').on('change',toggle);
			toggle();
		});
		</script>
		<?php
	}

	public function save_metabox(){
		if (!isset($_POST['wpda_countdown_woo_nonce']) || !wp_verify_nonce($_POST['wpda_countdown_woo_nonce'], 'wpda_countdown_woo_meta')) return;
		if (!current_user_can('edit_post', get_the_ID())) return;
		$id = get_the_ID();
		if(isset($_POST['wpdevart_countdown_enable'])){
			$enable = sanitize_text_field($_POST['wpdevart_countdown_enable']);
			update_post_meta($id, 'wpda_countdown_enable', $enable);
			if($enable === 'enable'){
				update_post_meta($id, 'wpda_countdown_timer', isset($_POST['wpdevart_countdown_timer'])?intval($_POST['wpdevart_countdown_timer']):0);
				update_post_meta($id, 'wpda_countdown_theme', isset($_POST['wpdevart_countdown_theme'])?intval($_POST['wpdevart_countdown_theme']):0);
			}
		}
	}
	private function create_timer_select($current_timer){
		$timers = wpda_countdown()->timer_repository()->all_names();
		$add_url = admin_url( 'admin.php?page=wpda_countdown_menu&task=add_wpda_countdown_timer' );
		$html='<div style="margin-bottom:8px;">';
		$html.='<label style="font-weight:600;display:block;margin-bottom:4px;">Timer</label>';
		$html.='<select style="width:100%;" name="wpdevart_countdown_timer" id="wpdevart_countdown_timer">';
		$html.='<option '.selected($current_timer,'0',false).' value="0">— Select Timer —</option>';
		foreach($timers as $id => $name){
			$html.='<option '.selected($current_timer,$id,false).' value="'.esc_attr($id).'">'.esc_html($name).'</option>';
		}
		$html.='</select>';
		$html.='<a style="font-size:11px;text-decoration:none;" target="_blank" href="'.esc_url($add_url).'">+ Add new</a>';
		$html.='</div>';
		return $html;
	}
	private function create_theme_select($current_theme){
		$themes = wpda_countdown()->theme_repository()->all_names();
		$add_url = admin_url( 'admin.php?page=wpda_countdown_themes&task=add_wpda_countdown_theme' );
		$html='<div style="margin-bottom:8px;">';
		$html.='<label style="font-weight:600;display:block;margin-bottom:4px;">Theme</label>';
		$html.='<select style="width:100%;" name="wpdevart_countdown_theme" id="wpdevart_countdown_theme">';
		$html.='<option '.selected($current_theme,'0',false).' value="0">— Default Theme —</option>';
		foreach($themes as $id => $name){
			$html.='<option '.selected($current_theme,$id,false).' value="'.esc_attr($id).'">'.esc_html($name).'</option>';
		}
		$html.='</select>';
		$html.='<a style="font-size:11px;text-decoration:none;" target="_blank" href="'.esc_url($add_url).'">+ Add new</a>';
		$html.='</div>';
		return $html;
	}
	public function woocommerce_settings($settings){
		$settings[] = include wpda_countdown_plugin_path.'includes/Integration/WooCommerceSettings.php';
		return $settings;
	}
}
