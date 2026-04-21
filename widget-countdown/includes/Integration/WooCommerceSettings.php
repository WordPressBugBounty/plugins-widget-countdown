<?php
if ( ! defined( "ABSPATH" ) ) exit;

class wpdevart_countdown_woocommerce_settings extends WC_Settings_Page {

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->id    = 'wpdevart_countdown_woocommerce_settings';
		$this->label = "WpDevArt Countdown Pro";

		parent::__construct();
	}

	/**
	 * Get settings array.
	 *
	 * @return array
	 */
	public function get_settings() {

		$currency_code_options = get_woocommerce_currencies();

		foreach ( $currency_code_options as $code => $name ) {
			$currency_code_options[ $code ] = $name . ' (' . get_woocommerce_currency_symbol( $code ) . ')';
		}

		$settings =	array(
				array(
					'title' => 'Countdown Pro ',
					'type'  => 'title',
					'id'    => 'store_address',
				),
				array(
					'title'    => 'Countdown position on product page',
					'desc'     => 'Select where to insert the countdown on the product page',
					'id'       => 'wpdevart_countdown_product_position',
					'default'  => 'woocommerce_single_product_summary',
					'type'     => 'select',
					'class'    => 'wc-enhanced-select',
					'desc_tip' => true,
					'options'  => array(
						'dont_add'=>"Disable",
						'woocommerce_before_single_product'         => 'Before product',
						'woocommerce_before_single_product_summary' => 'Before product summary',
						'woocommerce_single_product_summary'        => 'Inside product summary', 
						'woocommerce_after_single_product_summary'  => 'After product summary', 
						'woocommerce_after_single_product'          => 'After product',
						'woocommerce_before_add_to_cart_form'       => 'Before add to cart form',
						'woocommerce_before_add_to_cart_button'     => 'Before add to cart button',
						'woocommerce_after_add_to_cart_button'      => 'After add to cart button',
						'woocommerce_after_add_to_cart_form'        => 'After add to cart form',
					),
				),
				array(
					'title'    => 'Archive/Shop/Category Page position' ,
					'desc'     => 'Select where to insert the countdown on the Archive/Shop/Category page',
					'id'       => 'wpdevart_countdown_shop_position',
					'default'  => 'dont_add',
					'type'     => 'select',
					'class'    => 'wc-enhanced-select',
					'desc_tip' => true,
					'options'  => array(
						'dont_add'=>"Disable",
						'woocommerce_before_shop_loop_item'         => 'Before products',
						'woocommerce_before_shop_loop_item_title' 	=> 'Before products image',
						'woocommerce_shop_loop_item_title'        	=> 'After products image', 
						'woocommerce_after_shop_loop_item_title'  	=> 'After products title', 
						'woocommerce_after_shop_loop_item'          => 'After products price',
					),
				),
				array(
					'title'    => 'Enable the Countdown for all products' ,
					'desc'     => 'This option will enable the countdown for all products',
					'id'       => 'woocommerce_enable_timer_in_all_prod',
					'default'  => 'dont_add',
					'type'     => 'select',
					'class'    => 'wc-enhanced-select',
					'desc_tip' => true,
					'options'  => array(
						'disable'=>"Disable",
						'enable' => 'Enable',
					),
				),
				array(
					'title'    => 'Select the Countdown for all products' ,
					'desc'     => 'The selected Countdown will be displayed on all products pages. If you want to disable the timer on specific product page, then you need to go to the product page and disable timer or choose another timer from the right side.',
					'id'       => 'wpdevart_countdown_woocommerce_all_timer',
					'default'  => '0',
					'type'     => 'select',
					'class'    => 'wc-enhanced-select',
					'desc_tip' => true,
					'options'  => $this->get_timers_array(),
				),
				array(
					'title'    => 'Select the Countdown theme' ,
					'desc'     => 'Set the Countdown theme you want.',
					'id'       => 'wpdevart_countdown_woocommerce_all_theme',
					'default'  => '0',
					'type'     => 'select',
					'class'    => 'wc-enhanced-select',
					'desc_tip' => true,
					'options'  => $this->get_themes_array(),
				),
				array(
					'title'    => 'After countdown expires',
					'desc'     => 'Action to perform on the product when its countdown reaches zero. "Set out of stock" changes the product stock status so it can no longer be purchased.',
					'id'       => 'wpdevart_countdown_woo_after_expire',
					'default'  => 'nothing',
					'type'     => 'select',
					'class'    => 'wc-enhanced-select',
					'desc_tip' => true,
					'options'  => array(
						'nothing'      => 'Do nothing',
						'out_of_stock' => 'Set product out of stock',
					),
				),

				array(
					'type' => 'sectionend',
					'id'   => '',
				),
		);

		return apply_filters( 'woocommerce_get_settings_' . $this->id, $settings );
	}

	/**
	 * Output a color picker input box.
	 *
	 * @param mixed  $name Name of input.
	 * @param string $id ID of input.
	 * @param mixed  $value Value of input.
	 * @param string $desc (default: '') Description for input.
	 */
	public function color_picker( $name, $id, $value, $desc = '' ) {
		echo '<div class="color_box">' . wc_help_tip( $desc ) . '
			<input name="' . esc_attr( $id ) . '" id="' . esc_attr( $id ) . '" type="text" value="' . esc_attr( $value ) . '" class="colorpick" /> <div id="colorPickerDiv_' . esc_attr( $id ) . '" class="colorpickdiv"></div>
		</div>';
	}

	/**
	 * Output the settings.
	 */
	public function output() {
		$settings = $this->get_settings();
		WC_Admin_Settings::output_fields( $settings );
	}

	/**
	 * Save settings.
	 */
	public function save() {
		$settings = $this->get_settings();

		WC_Admin_Settings::save_fields( $settings );
	}
	private function get_timers_array(){
		return wpda_countdown()->timer_repository()->all_names();
	}
	private function get_themes_array(){
		return wpda_countdown()->theme_repository()->all_names();
	}
}
return new wpdevart_countdown_woocommerce_settings();
