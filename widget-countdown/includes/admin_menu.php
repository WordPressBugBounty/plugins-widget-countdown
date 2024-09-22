<?php 
	
	/*############################### Admin Menu ########################################*/
	
class wpdevart_countdown_admin_menu{
	
	private $menu_name;
	private $databese_parametrs;
	private $plugin_url;
	private $plugin_path;

	/*###################### Construct parameters function ##################*/		
	
	function __construct($param){
				
		$this->menu_name=$param['menu_name'];
		if(isset($param['databese_parametrs']))
			$this->databese_parametrs=$param['databese_parametrs'];
		if(isset($params['plugin_url']))
			$this->plugin_url=$params['plugin_url'];
		else
			$this->plugin_url=trailingslashit(dirname(plugins_url('',__FILE__)));
		if(isset($params['plugin_path']))
			$this->plugin_path=$params['plugin_url'];
		else
			$this->plugin_path=trailingslashit(dirname( plugin_dir_path( __FILE__ )));
		// include requered files
		$this->include_files();
		// Insert button code
		add_action('media_buttons', array($this,'wpdevart_countdown_button'));
		add_action( 'wp_ajax_wpdevart_countdown_window_manager', array($this,'wpdevart_countdown_window_insert_content') );
		/*gutenberg editor integration*/
		$this->integrete_gutenberg();
	}
	
	/*############################### Insert button function ########################################*/
	
	private function include_files(){
		require_once($this->plugin_path.'includes/gutenberg/gutenberg.php');
	}
	
	private function integrete_gutenberg(){
		$wpdevart_countdown = new wpda_countdown_gutenberg($this->plugin_url);
	}
	/*############################### Insert button function ########################################*/
	
	public function wpdevart_countdown_button() {
	  
	  $img = $this->plugin_url. 'images/post_button.jpg';
	
	  $title = 'Add Countdown';	
	  $context = '<a class="button thickbox" title="Create countdown and insert in posts/pages"    href="'.admin_url("admin-ajax.php").'?action=wpdevart_countdown_window_manager&height=750&width=640">
			<span class="wp-media-buttons-icon" style="background: url('.$img.'); background-repeat: no-repeat; background-position: left bottom;"></span>
		Add Countdown
		</a>';  
	  echo $context;
	}

    /*############  Insert countdown - content function  ################*/	
	
	public function wpdevart_countdown_window_insert_content(){
		//wp_enqueue_script('jquery-ui-slider');
		?>
        <style>
		.wp-picker-container{
			position:relative;
		}
        #miain_wpdevart_countdown_window_manager > tbody > tr:nth-child(odd) {
		  background-color: rgba(176, 176, 176, 0.07);
		}
		#miain_wpdevart_countdown_window_manager>tfoot>tr>td{
			border-top:1px solid #ccc;
		}
		#TB_window{  
			overflow-y: auto;
		}
		#TB_ajaxContent{
			width:95% !important;
		}
		.wp-picker-holder{
			position: absolute;
 			z-index: 100000;
		}
		.desription_class{
			float: right;
			cursor: default;
			color: #0074a2;
			font-size: 18px;
			font-weight: bold;
			border: 1px solid #000000;
			border-radius: 200px;
			height: 20px;
			padding-left: 6px;
			padding-right: 6px;
			margin-left: 15px;
		}
		.pro_feature {
		  font-size: 13px;
		  font-weight: bold;
		  color: #7052fb;
		}
        </style>
			<table id="miain_wpdevart_countdown_window_manager" class="wp-list-table widefat fixed posts section_parametrs_table">  
                <tbody> 
                    <tr>
						<td>
							Day field text <span title="Type here text for Day field." class="desription_class">?</span>
						</td>
						<td>
							<input type="text" name="countdown_days_text"  id="countdown_days_text" value="Days">
						</td>                
					</tr>
                     <tr>
						<td>
							Hour field text <span title="Type here text for Hour field." class="desription_class">?</span>
						</td>
						<td>
							<input type="text" name="countdown_hourse_text"   id="countdown_hourse_text" value="Hours">
						</td>                
					</tr>
                     <tr>
						<td>
							Minute field text <span title="Type here text for Minute field." class="desription_class">?</span>
						</td>
						<td>
							<input type="text" name="countdown_minuts_text"  id="countdown_minuts_text" value="Minutes">
						</td>                
					</tr>
                     <tr>
						<td>
							Second field text <span title="Type here text for Second field." class="desription_class">?</span>
						</td>
						<td>
							<input type="text" name="countdown_seconds_text"   id="countdown_seconds_text" value="Seconds">
						</td>                
					</tr>
                    <tr>
						<td>
							Countdown date picker type <span title="Select the Countdown date picker type." class="desription_class">?</span>
                           
						</td>
						<td>
							 <select class="show_hide_experet_type" id="countdown_experet_type">
                                    <option selected="selected" value="time">Time</option>
                                    <option value="date">Date</option>
                             </select>
						</td>                
					</tr>
                     <tr class="expert_type_date" style="display:none">
						<td>
							Countdown expire date: <span title="Select Countdown expire date." class="desription_class">?</span>
						</td>
						<td>
       						 <input type="text"  value="<?php echo  date('d-m-Y 23:59') ?>" id="countdown_experet_date" /><small>dd-mm-yyyy hh:ii</small>
						</td>                
					</tr>
                	<tr  class="expert_type_time">
						<td>
							Countdown expire time <span title="Type the Countdown expire time." class="desription_class">?</span>
						</td>
						<td style="vertical-align: top !important;">
                            <span style="display:inline-block; margin-right:3px; width:70px; float:left;">
                            	<input type="text" placeholder="Day"   id="countdownday" size="3" value="0"/>
                            	<small style="display:block">Day</small>
                            </span>
                           	<span style="display:inline-block; width:72px; float:left;">
                                 <input type="text"  placeholder="Hour" id="countdownhour" size="3" value="1"/>
                                 <small>Hour</small>
                            </span>
                          	<span style="display:inline-block; width:55px;"> 
                            	<input type="text"  placeholder="Minut"  id="countdownminute" size="3" value="1"/>
                            	<small>Minute</small>
                            </span>
                            <input type="hidden" value='<?php echo mktime (date("H"), date("i"), date("s"),date("n"), date("j"),date("Y")); ?>' id="countdown_start_date" name="countdown_start_date" />
                         </td>                
					</tr> 
					<tr>
						<td>
							<span>Mobile devices</span> <span title="This option allow to show or hide countdown on mobile devices." class="desription_class">?</span>
						</td>
						<td>
                           <select id="countdown_hide_on_mobile" >
                                <option selected="selected" value="show">Show</option>
                                <option value="hide">Hide</option>
                        	</select>
                         </td>                
					</tr>					
					<tr>
						<td>
							<span style="color:#7052fb">After Countdown expire</span> <span title="Select the action you prefere after Countdown time expire." class="desription_class">?</span>
						</td>
						<td>
                           <select id="countdownstart_on" >
                                <option selected="selected" value="hide">Hide countdown</option>
                                <option value="show_text">Show text</option>
								<option  value="redirect">Redirect</option>
                        	</select>
                         </td>                
					</tr>
                    </tr>
                    <tr>
						<td>
							Message after countdown expire <span title="Type the message that will appear after countdown time expire. " class="desription_class">?</span>
						</td>
						<td>
							<textarea type="text" name="expeiret_text"   id="expeiret_text"></textarea>   
                         </td>                
					</tr>
					<tr>
						<td>
							Redirect URL <span title="Type the redirect URL. " class="desription_class">?</span>
						</td>
						<td>
							<input type="text"  id="redirect_url" size="25" placeholder="http://www.example.com" value=""/>
                         </td>                
					</tr>
                    <tr>
						<td>
							Countdown timer position <span title="Select the Countdown Timer position." class="desription_class">?</span>
						</td>
						<td>
                           <select  id="countdown_in_content_position">
                                <option  value="left">Left</option>
                                <option selected="selected"  value="center">Center</option>
                                <option  value="right">Right</option>
                        	</select>
                         </td>                
					</tr>
                    <tr>
						<td>
							Countdown distance from top <span title="Type the Countdown distance from top(px)." class="desription_class">?</span>
						</td>
						<td>
							<input type="text" name="countdown_top_distance"  id="countdown_top_distance" value="10">(Px)
						</td>                
					</tr>
					<tr>
						<td>
							Countdown distance from bottom <span title="Type the Countdown distance from bottom(px)." class="desription_class">?</span>
						</td>
						<td>
							<input type="text" name="countdown_bottom_distance"  id="countdown_bottom_distance" value="10">(Px)
						</td>                
					</tr>
                    <tr>
						<td>
							Countdown timer buttons type <span class="pro_feature"> (pro)</span> <span title="Choose the Countdown buttons type." class="desription_class">?</span>
						</td>
						<td>
                           <select onChange="alert(countdown_pro_text)" id="countdown_type" class="coming_set_hiddens">
                                <option selected="selected" value="button">Button</option>
                                <option value="circle">Circle</option>
                                <option value="vertical_slide">Vertical Slider</option>
                        	</select>
                         </td>                
					</tr>
                   
                    <tr class="tr_button tr_circle tr_vertical_slide">
                        <td>
                        	Countdown timer text color <span title="Choose the Countdown text color." class="desription_class">?</span>
                        </td>
                        <td>
                            <input type="text" class="color_option" id="countdown_font_color" name="countdown_font_color"  value="#000000"/>
                         </td>                
                    </tr>
                    <tr class="tr_button tr_circle tr_vertical_slide">
                        <td>
                           Countdown timer background color <span class="pro_feature"> (pro)</span> <span title="Select the Countdown background color." class="desription_class">?</span>
                        </td>
                        <td>
                            <div onClick="alert(countdown_pro_text)">
								<div class="wp-picker-container disabled_picker">
									<button type="button" class="button wp-color-result" aria-expanded="false" style="background-color: rgb(62, 89, 165);"><span class="wp-color-result-text">Select Color</span></button>
								</div>                            
                            </div>
                         </td>                
                    </tr>
                       <tr  class="tr_circle">
						<td>
							Countdown timer size <span class="pro_feature"> (pro)</span> <span title="Type the Countdown size." class="desription_class">?</span>
						</td>
						<td>
							<input onClick="alert(countdown_pro_text)" type="text" name="countdown_circle_size"  id="countdown_circle_size" value="130">(Px)
						</td>                
					</tr>
                 
                     <tr  class="tr_circle">
						<td>
							Countdown timer border width <span class="pro_feature"> (pro)</span> <span title="Type the Countdown border width(px)." class="desription_class">?</span>
						</td>
						<td>
                        	<input onClick="alert(countdown_pro_text)" type="text" size="3" name="countdown_circle_border" value="5" id="countdown_circle_border" style="font-weight:bold; width:35px" >(0-100)%
                           
						</td>                
					</tr>
                     <tr class="tr_button">
						<td>
							Countdown timer border radius <span class="pro_feature"> (pro)</span> <span title="Type the Countdown border radius(px)." class="desription_class">?</span>
						</td>
						<td>
							<input onClick="alert(countdown_pro_text)" type="text" name="countdown_border_radius"  id="countdown_border_radius" value="8">(Px)
						</td>                
					</tr>
                     <tr  class="tr_button tr_vertical_slide">
						<td>
							Countdown timer font size <span class="pro_feature"> (pro)</span> <span title="Type the Countdown font-size(px)." class="desription_class">?</span>
						</td>
						<td>
							<input onClick="alert(countdown_pro_text)" type="text" name="countdown_font_size" id="countdown_font_size" value="30">(Px)
						</td>                
					</tr>
                  
                     <tr  class="tr_button tr_circle tr_vertical_slide">
						<td>
							Countdown timer Font Family <span class="pro_feature"> (pro)</span> <span title="Select the Countdown Font family." class="desription_class">?</span>
						</td>
						<td>
							<?php wpdevart_countdown_setting::generete_fonts('countdown_font_famaly',"monospace") ?>
						</td>                
					</tr> 
                    <tr>
						<td>
							Countdown animation type <span class="pro_feature"> (pro)</span> <span title="Select the Countdown animation type." class="desription_class">?</span>
						</td>
						<td>
							<?php wpdevart_countdown_setting::generete_animation_select('countdown_animation_type','none'); ?>
						</td>                
					</tr>
                </tbody>
                <tfoot>
                	<tr>                      
                        <td colspan="2">
                        	 <div style="display:inline-block; float:left;" class="mceActionPanel"><input type="button" id="cancel" name="cancel" value="Insert Countdown" class="button button-primary" onClick="insert_countdown();"/></div>
                        	<span style="float:right"><a href="http://wpdevart.com/wordpress-countdown-plugin" target="_blank" style="color: #7052fb; font-weight: bold; font-size: 18px; text-decoration: none;">Upgrade to Pro Version</a><br></span>
                        </td>                
                    </tr>
                </tfoot>
            </table>         
    
                   
    
    <script type="text/javascript">
	
	 	var countdown_pro_text="If you want to use this feature upgrade to Countdown Pro"
       jQuery('#TB_window').css('max-height',(jQuery('#miain_wpdevart_countdown_window_manager').height()+66)+'px');
	   jQuery('#TB_ajaxContent').css('max-height',(jQuery('#miain_wpdevart_countdown_window_manager').height()+16)+'px');
	   jQuery('#miain_wpdevart_countdown_window_manager').ready(function(e) {
                jQuery(".color_option").wpColorPicker();
        });
		jQuery('.coming_set_hiddens').change(function(){
			jQuery(this).find('option').each(function(index, element) {
				jQuery('.tr_'+jQuery(this).val()).hide();
			});
			 jQuery('.tr_'+jQuery(this).val()).show();
		})
		jQuery('.coming_set_hiddens option').each(function(index, element) {
			jQuery('.tr_'+jQuery(this).val()).hide();
		});
		jQuery('.coming_set_hiddens').each(function(index, element) {
			jQuery('.tr_'+jQuery(this).val()).show();
		});
		//Show hidden countdown timer in the end of date type
		jQuery('.show_hide_experet_type').change(function(){
			if(jQuery(this).val()=='date'){
				jQuery('.expert_type_date').show();
				jQuery('.expert_type_time').hide();
			}else{
				jQuery('.expert_type_date').hide();
				jQuery('.expert_type_time').show();
			}
		})	
		var nowTemp = new Date();
		var now = new Date(nowTemp.getFullYear(), nowTemp.getMonth(), nowTemp.getDate(), 0, 0, 0, 0);
		jQuery('#countdown_experet_date').fdatepicker({
		  format: 'dd-mm-yyyy hh:ii',
		  pickTime: true,
		  onRender: function (date) {
			return date.valueOf() < now.valueOf() ? 'disabled' : '';
		   }
		});
		
		jQuery( '.slider_div' ).slider({
			orientation: "horizontal",
			range: "min",
			value: 5,
			min: 0,
			max: 100,
			slide: function( event, ui ) {
				jQuery( loc_this ).val( ui.value );
			}
		});
        function insert_countdown() {          
			
                var tagtext;
				var variables='';
				
				if(jQuery('#countdown_days_text').length)
					variables=variables+'text_for_day="'+jQuery('#countdown_days_text').val()+'" ';
					
				if(jQuery('#countdown_hourse_text').length)
					variables=variables+'text_for_hour="'+jQuery('#countdown_hourse_text').val()+'" ';
					
				if(jQuery('#countdown_minuts_text').length)
					variables=variables+'text_for_minut="'+jQuery('#countdown_minuts_text').val()+'" ';
					
				if(jQuery('#countdown_seconds_text').length)
					variables=variables+'text_for_second="'+jQuery('#countdown_seconds_text').val()+'" ';
				
				if(jQuery('#countdown_experet_type').length)
					variables=variables+'countdown_end_type="'+jQuery('#countdown_experet_type').val()+'" ';
				
				if(jQuery('#countdown_font_color').length)
					variables=variables+'font_color="'+jQuery('#countdown_font_color').val()+'" ';
				
				if(jQuery('#countdown_hide_on_mobile').length)
					variables=variables+'hide_on_mobile="'+jQuery('#countdown_hide_on_mobile').val()+'" ';
				
				if(jQuery('#redirect_url').length)
					variables=variables+'redirect_url="'+jQuery('#redirect_url').val()+'" ';
				
				if(jQuery('#countdown_experet_date').length)
					variables=variables+'end_date="'+jQuery('#countdown_experet_date').val()+'" ';
				
				if(jQuery('#countdown_start_date').length)
					variables=variables+'start_time="'+jQuery('#countdown_start_date').val()+'" ';
				
					variables=variables+'end_time="'+jQuery('#countdownday').val()+','+jQuery('#countdownhour').val()+','+jQuery('#countdownminute').val()+'" ';
					
				if(jQuery('#countdownstart_on').length)
					variables=variables+'action_end_time="'+jQuery('#countdownstart_on').val()+'" ';
				
				if(jQuery('#countdown_in_content_position').length)
					variables=variables+'content_position="'+jQuery('#countdown_in_content_position').val()+'" ';
					
				if(jQuery('#countdown_top_distance').length)
					variables=variables+'top_ditance="'+jQuery('#countdown_top_distance').val()+'" ';
					
				if(jQuery('#countdown_bottom_distance').length)
					variables=variables+'bottom_distance="'+jQuery('#countdown_bottom_distance').val()+'" ';					
                tagtext = '[wpdevart_countdown '+variables+']'+jQuery('#expeiret_text').val()+'[/wpdevart_countdown]';
                window.send_to_editor(tagtext);
              	tb_remove()
        }

    </script>
    </body>
    </html>
<?php
die;	
}

	/*###################### Create menu function ##################*/	
	
	public function create_menu(){
		global $submenu;
		$sub_men_cap=str_replace( ' ', '-', $this->menu_name);
		$main_page 	 	  = add_menu_page( $this->menu_name, $this->menu_name, 'manage_options', str_replace( ' ', '-', $this->menu_name), array($this, 'main_menu_function'),$this->plugin_url.'images/timer.png');
		$page_countdown	  =	add_submenu_page($this->menu_name,  $this->menu_name,  $this->menu_name, 'manage_options', str_replace( ' ', '-', $this->menu_name), array($this, 'main_menu_function'));
		$page_countdown	  = add_submenu_page( $sub_men_cap, 'Featured Plugins', 'Featured Plugins', 'manage_options', 'countdown-featured-plugins', array($this, 'featured_plugins'));
		if(isset($submenu[$sub_men_cap]))
			add_submenu_page( $sub_men_cap, "Support or Any Ideas?", "<span style='color:#00ff66' >Support or Any Ideas?</span>", 'manage_options',"any_ideas",array($this, 'any_ideas'),155);
		add_action('admin_print_styles-' .$main_page, array($this,'menu_requeried_scripts'));
		add_action('admin_print_styles-' .$page_countdown, array($this,'menu_requeried_scripts'));	
		if(isset($submenu[$sub_men_cap]))
			$submenu[$sub_men_cap][2][2]=wpdevart_countdown_support_url;
	}
	
	/*###################### Any ideas function ##################*/	
	
	public function any_ideas(){
		
	}
	/*###################### Menu scripts required function ##################*/	
	
	public function menu_requeried_scripts(){
		wp_enqueue_style('wpdevart-countdown-admin-style');
	}	
	
	/*###################### Main menu function ##################*/	
	
	public function main_menu_function(){		
	?>
	<style>
		.wpdevart_plugins_get_pro {
			border-radius: 10px;
			background: #ffffff;
			padding: 15px 20px;
			box-sizing: border-box;
			float: left;
			box-shadow: 1px 1px 7px rgba(0,0,0,0.04);
		}
		.wpdevart_plugins_get_pro_info {
			float: left;
			margin-right: 30px;
		}
		.wpdevart_plugins_get_pro_info h3 {
			margin: 0 0 5px 0;
			font-size: 17px;
			font-weight: 500;
		}
		.wpdevart_plugins_get_pro_info p {
			margin: 0;
			font-size: 14px;
			font-weight: 200;
		}
		.wpdevart_support, .wpdevart_upgrade {
			display: inline-block;
			font-size: 16px;
			text-decoration: none;
			border-radius: 5px;
			border: 0;
			color: #ffffff;
			font-weight: 400;
			opacity: 1;
			-webkit-transition: opacity 0.3s;
			-moz-transition: opacity 0.3s;
			transition: opacity 0.3s;
			background-image: linear-gradient(141deg, #7052fb, #00a0d2);
		}
		.wpdevart_upgrade {
			float: left;
			padding: 11px 25px 12px;
			text-transform: uppercase;
		}
		.wpdevart_support {
			float: right;
			padding: 11px 20px 12px 50px;
			margin-right:20px;
			margin-top:15px;
			position: relative;
		}
		.wpdevart_support:before {
			content: "";
			background: url(<?php echo $this->plugin_url ?>images/support-white.png) no-repeat;
			width: 25px;
			height: 25px;
			background-size: 25px;
			top: 8px;
			position: absolute;
			left: 15px;
		}
		.div-for-clear:after {
			content: '';
			clear: both;
			display: table;
		}
		.wpdevart_support:hover,
		.wpdevart_upgrade:hover,
		.wpdevart_support:focus,
		.wpdevart_upgrade:focus {
			color:#ffffff;
			opacity:0.85;
			box-shadow: none;
			outline: none;
			text-decoration:none;
		}
		#wpwrap{
			background-color: white;
		}
	</style>
	<div class="wpdevart_plugins_header div-for-clear">
			<div class="wpdevart_plugins_get_pro div-for-clear">
				<div class="wpdevart_plugins_get_pro_info">
					<h3>WpDevArt Countdown Premium</h3>
					<p>Powerful and Customizable Countdown</p>
				</div>
					<a target="blank" href="https://wpdevart.com/wordpress-countdown-plugin/" class="wpdevart_upgrade">Upgrade</a>
			</div>
			<a target="blank" href="<?php echo wpdevart_countdown_support_url; ?>" class="wpdevart_support">Have any Questions? Get a quick support!</a>
		</div>
	<h1 style="text-align:center; font-size:35px">Quick guideline for the Countdown plugin</h1>
	<br>	
  	 <div class="image_width_description">
		 <h2 style="font-size: 20px; text-align: center; ">Adding a countdown in post or page</h2><br>
		 <div style="font-size:15px; text-align: center; max-width: 1024px; margin: 0 auto;">If you are using Classic Editor, then click on the shortcode button and set Countdown timer options, then click on the "Insert Countdown" button. Check the left screenshot below. If you are using Block-Enabled Editor, then click on the Plus button and open the Common Blocks tab, then click on WpDevArt countdown and configure settings. Check the right screenshot below.</div>
		 <br/>
		 <div style="text-align:center"><img class="image" style="max-width:35%;margin-right:10px;border: 1px solid #000000;" src="<?php echo $this->plugin_url.'images/clasic_editor_button_place.jpg' ?>"><img style="max-width:35%; border: 1px solid #000000;" class="image" src="<?php echo $this->plugin_url.'images/gutenberg_button_place.jpg' ?>"></div>
     </div>
     <div class="image_width_description">
	 <h2 style="font-size: 20px; text-align:center;">Adding a countdown in a widget</h2><br>
     <div style="font-size:15px; text-align: center; max-width: 1024px; margin: 0 auto;">For adding a countdown timer into your website Sidebars go to your website Widgets page, pick and drop the Countdown widget into your sidebar. Then set the Countdown timer options, then save changes. Look at the screenshot below.</div><br>
    	<div style="text-align:center"><img style="max-width:35%; border: 1px solid #000000;" class="image" src="<?php echo $this->plugin_url.'images/widget_place.jpg' ?>"></div>
    </div>

	<?php 
	}

	/*############################### Featured plugins function ########################################*/
		public function featured_plugins(){
		$plugins_array=array(
 			'Countdown_Extended'=>array(
						'image_url'		=>	$this->plugin_url.'images/featured_plugins/icon-128x128.png',
						'site_url'		=>	'https://wpdevart.com/wordpress-countdown-extended-version/',
						'title'			=>	'WordPress Countdown Extended',
						'description'	=>	'WordPress Countdown Extended (CountUp, WooCommerce Sales Timer) is a great tool. You can easily create countdown and countup timers for WordPress your website.'
						),	
			'gallery_album'=>array(
						'image_url'		=>	$this->plugin_url.'images/featured_plugins/gallery-album-icon.png',
						'site_url'		=>	'https://wpdevart.com/wordpress-gallery-plugin/',
						'title'			=>	'WordPress Gallery plugin',
						'description'	=>	'Gallery plugin is an useful tool that will help you to create Galleries and Albums. Try our nice Gallery views and awesome animations.'
						),		
			'coming_soon'=>array(
						'image_url'		=>	$this->plugin_url.'images/featured_plugins/coming_soon.png',
						'site_url'		=>	'https://wpdevart.com/wordpress-coming-soon-plugin/',
						'title'			=>	'Coming soon and Maintenance mode',
						'description'	=>	'Coming soon and Maintenance mode plugin is an awesome tool to show your visitors that you are working on your website to make it better.'
						),
			'Contact forms'=>array(
						'image_url'		=>	$this->plugin_url.'images/featured_plugins/contact_forms.png',
						'site_url'		=>	'https://wpdevart.com/wordpress-contact-form-plugin/',
						'title'			=>	'Contact Form Builder',
						'description'	=>	'Contact Form Builder plugin is an handy tool for creating different types of contact forms on your WordPress websites.'
						),	
			'Booking Calendar'=>array(
						'image_url'		=>	$this->plugin_url.'images/featured_plugins/Booking_calendar_featured.png',
						'site_url'		=>	'https://wpdevart.com/wordpress-booking-calendar-plugin/',
						'title'			=>	'WordPress Booking Calendar',
						'description'	=>	'WordPress Booking Calendar plugin is an awesome tool to create a booking system for your website. Create booking calendars in a few minutes.'
						),
			'Pricing Table'=>array(
						'image_url'		=>	$this->plugin_url.'images/featured_plugins/Pricing-table.png',
						'site_url'		=>	'https://wpdevart.com/wordpress-pricing-table-plugin/',
						'title'			=>	'WordPress Pricing Table',
						'description'	=>	'WordPress Pricing Table plugin is a nice tool for creating beautiful pricing tables. Use WpDevArt pricing table themes and create tables just in a few minutes.'
						),
			'chart'=>array(
						'image_url'		=>	$this->plugin_url.'images/featured_plugins/chart-featured.png',
						'site_url'		=>	'https://wpdevart.com/wordpress-organization-chart-plugin/',
						'title'			=>	'WordPress Organization Chart',
						'description'	=>	'WordPress organization chart plugin is a great tool for adding organizational charts to your WordPress websites.'
						),						
			'youtube'=>array(
						'image_url'		=>	$this->plugin_url.'images/featured_plugins/youtube.png',
						'site_url'		=>	'https://wpdevart.com/wordpress-youtube-embed-plugin',
						'title'			=>	'WordPress YouTube Embed',
						'description'	=>	'YouTube Embed plugin is an convenient tool for adding videos to your website. Use YouTube Embed plugin for adding YouTube videos in posts/pages, widgets.'
						),
            'facebook-comments'=>array(
						'image_url'		=>	$this->plugin_url.'images/featured_plugins/facebook-comments-icon.png',
						'site_url'		=>	'https://wpdevart.com/wordpress-facebook-comments-plugin/',
						'title'			=>	'Wpdevart Social comments',
						'description'	=>	'WordPress Facebook comments plugin will help you to display Facebook Comments on your website. You can use Facebook Comments on your pages/posts.'
						),						
			'lightbox'=>array(
						'image_url'		=>	$this->plugin_url.'images/featured_plugins/lightbox.png',
						'site_url'		=>	'https://wpdevart.com/wordpress-lightbox-plugin',
						'title'			=>	'WordPress Lightbox plugin',
						'description'	=>	'WordPress Lightbox Popup is an high customizable and responsive plugin for displaying images and videos in popup.'
						),
			'facebook'=>array(
						'image_url'		=>	$this->plugin_url.'images/featured_plugins/facebook.png',
						'site_url'		=>	'https://wpdevart.com/wordpress-facebook-like-box-plugin',
						'title'			=>	'Social Like Box',
						'description'	=>	'Facebook like box plugin will help you to display Facebook like box on your website, just add Facebook Like box widget to sidebar or insert it into posts/pages and use it.'
						),
			'vertical_menu'=>array(
						'image_url'		=>	$this->plugin_url.'images/featured_plugins/Vertical-menu.png',
						'site_url'		=>	'https://wpdevart.com/wordpress-vertical-menu-plugin/',
						'title'			=>	'WordPress Vertical Menu',
						'description'	=>	'WordPress Vertical Menu is a handy tool for adding nice vertical menus. You can add icons for your website vertical menus using our plugin.'
						),						
			'duplicate_page'=>array(
						'image_url'		=>	$this->plugin_url.'images/featured_plugins/featured-duplicate.png',
						'site_url'		=>	'https://wpdevart.com/wordpress-duplicate-page-plugin-easily-clone-posts-and-pages/',
						'title'			=>	'WordPress Duplicate page',
						'description'	=>	'Duplicate Page or Post is a great tool that allows duplicating pages and posts. Now you can do it with one click.'
						),						
						
			
		);
		?>
        <style>
         .featured_plugin_main{
			background-color: #ffffff;
			-webkit-box-sizing: border-box;
			-moz-box-sizing: border-box;
			box-sizing: border-box;
			float: left;
			margin-right: 30px;
			margin-bottom: 30px;
			width: calc((100% - 90px)/3);
			border-radius: 15px;
			box-shadow: 1px 1px 7px rgba(0,0,0,0.04);
			padding: 20px 25px;
			text-align: center;
			-webkit-transition:-webkit-transform 0.3s;
			-moz-transition:-moz-transform 0.3s;
			transition:transform 0.3s;   
			-webkit-transform: translateY(0);
			-moz-transform: translateY0);
			transform: translateY(0);
			min-height: 420px;
		 }
		.featured_plugin_main:hover{
			-webkit-transform: translateY(-2px);
			-moz-transform: translateY(-2px);
			transform: translateY(-2px);
		 }
		.featured_plugin_image{
			max-width: 128px;
			margin: 0 auto;
		}
		.blue_button{
    display: inline-block;
    font-size: 15px;
    text-decoration: none;
    border-radius: 5px;
    color: #ffffff;
    font-weight: 400;
    opacity: 1;
    -webkit-transition: opacity 0.3s;
    -moz-transition: opacity 0.3s;
    transition: opacity 0.3s;
    background-color: #7052fb;
    padding: 10px 22px;
    text-transform: uppercase;
		}
		.blue_button:hover,
		.blue_button:focus {
			color:#ffffff;
			box-shadow: none;
			outline: none;
		}
		.featured_plugin_image img{
			max-width: 100%;
		}
		.featured_plugin_image a{
		  display: inline-block;
		}
		.featured_plugin_information{	

		}
		.featured_plugin_title{
	color: #7052fb;
	font-size: 18px;
	display: inline-block;
		}
		.featured_plugin_title a{
	text-decoration:none;
	font-size: 19px;
    line-height: 22px;
	color: #7052fb;
					
		}
		.featured_plugin_title h4{
			margin: 0px;
			margin-top: 20px;		
			min-height: 44px;	
		}
		.featured_plugin_description{
			font-size: 14px;
				min-height: 63px;
		}
		@media screen and (max-width: 1460px){
			.featured_plugin_main {
				margin-right: 20px;
				margin-bottom: 20px;
				width: calc((100% - 60px)/3);
				padding: 20px 10px;
			}
			.featured_plugin_description {
				font-size: 13px;
				min-height: 63px;
			}
		}
		@media screen and (max-width: 1279px){
			.featured_plugin_main {
				width: calc((100% - 60px)/2);
				padding: 20px 20px;
				min-height: 420px;
			}	
		}
		@media screen and (max-width: 768px){
			.featured_plugin_main {
				width: calc(100% - 30px);
				padding: 20px 20px;
				min-height: auto;
				margin: 0 auto 20px;
				float: none;
			}	
			.featured_plugin_title h4{
				min-height: auto;
			}	
			.featured_plugin_description{
				min-height: auto;
					font-size: 14px;
			}	
		}

        </style>
      
		<h1 style="text-align: center;font-size: 50px;font-weight: 700;color: #2b2350;margin: 20px auto 25px;line-height: 1.2;">Featured Plugins</h1>
		<?php foreach($plugins_array as $key=>$plugin) { ?>
		<div class="featured_plugin_main">
			<div class="featured_plugin_image"><a target="_blank" href="<?php echo $plugin['site_url'] ?>"><img src="<?php echo $plugin['image_url'] ?>"></a></div>
			<div class="featured_plugin_information">
				<div class="featured_plugin_title"><h4><a target="_blank" href="<?php echo $plugin['site_url'] ?>"><?php echo $plugin['title'] ?></a></h4></div>
				<p class="featured_plugin_description"><?php echo $plugin['description'] ?></p>
				<a target="_blank" href="<?php echo $plugin['site_url'] ?>" class="blue_button">Check The Plugin</a>
			</div>
			<div style="clear:both"></div>                
		</div>
		<?php } 
	
	}	
}