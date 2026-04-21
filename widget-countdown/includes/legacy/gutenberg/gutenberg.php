<?php
class wpda_countdown_basic_gutenberg{	
	private $plugin_url;
	function __construct($plugin_url){
		$this->plugin_url=$plugin_url;
		$this->hooks_for_gutenberg();
	}
	private function hooks_for_gutenberg(){
		add_action( 'init', array($this,'guthenberg_init') );
	}
	public function guthenberg_init(){
		if ( ! function_exists( 'register_block_type' ) ) {
		// Gutenberg is not active.
		return;
		}
		register_block_type( 'wpdevart-countdown/countdown', array(
			'style' => 'wpda_countdown_basic_gutenberg_css',
			'editor_script' => 'wpda_countdown_basic_gutenberg_js',
		) );
		wp_add_inline_script(
			'wpda_countdown_basic_gutenberg_js',
			sprintf('var wpda_countdown_basic_gutenberg = { other_data: %s};',json_encode($this->other_dates(),JSON_PRETTY_PRINT)),
			'before'
		);
	}
	
	private function other_dates(){
		$array=array(
			'icon_src'     => $this->plugin_url."includes/admin/images/icon.svg",
			'content_icon' => $this->plugin_url."includes/admin/images/icon.png",
			'is_pro'       => defined( 'WPDA_COUNTDOWN_IS_PRO' ) && WPDA_COUNTDOWN_IS_PRO ? 1 : 0,
		);
		return $array;
	}
	
}

