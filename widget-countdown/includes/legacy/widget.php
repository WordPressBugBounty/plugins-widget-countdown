<?php 
/*############################### WIDGET ###############################################*/

class wpdevart_countdown extends WP_Widget {
	private static $id_for_content=0;
	// Constructor //	
	function __construct() {		
		$widget_ops = array( 'classname' => 'wpdevart_countdown', 'description' => 'Countdown timer for widget ' ); // Widget Settings
		$control_ops = array( 'id_base' => 'wpdevart_countdown' ); // Widget Control Settings
		parent::__construct( 'wpdevart_countdown', 'Countdown', $widget_ops, $control_ops ); // Create the widget
	}

	/*countdown display in front*/
	function widget($args, $instance) {
		self::$id_for_content++;
		$before_widget = $args['before_widget'];
		$after_widget = $args['after_widget'];
		$before_title = $args['before_title'];
		$after_title = $args['after_title'];
		$defaults = array(
			'title' 				=> '',
			'text_for_day' 			=> __( 'Days', "wpdevart_countdown" ),
			'text_for_hour' 		=> __( 'Hours', "wpdevart_countdown" ),
			'text_for_minut' 		=> __( 'Minutes', "wpdevart_countdown" ),
			'text_for_second' 		=> __( 'Seconds', "wpdevart_countdown" ),
			'start_time' 			=> mktime (date("H"), date("i"), date("s"),date("n"), date("j"),date("Y")),
			'hide_on_mobile' 		=> 'show',
			'end_time_type' 		=> 'time',
			'end_time' 				=> '0,1,5',
			'end_time_date' 		=> date('d-m-Y 23:59'),
			'action_end_time' 		=> 'hide',
			'redirect_url' 			=> get_home_url(),
			'content' 				=> '',
			'content_position' 		=> 'center',
			'top_ditance' 			=> '10',
			'bottom_distance' 		=> '10',
			'countdown_type'		=> 'button',
			'font_color'			=> '#000000',
			'button_bg_color' 		=> '#3DA8CC',
			'circle_size' 			=> '50',
			'circle_border' 		=> '5',
			'border_radius' 		=> '8',
			'font_size' 			=> '25',
			'countdown_font_famaly' => 'monospace',
			'animation_type' 		=> 'none',
		);
		$instance = wp_parse_args( (array) $instance, $defaults );
		$title = $instance['title'];    
		// Before widget //
		echo $before_widget;		
		// Title of widget //
		if ( $title ) { echo $before_title . $title . $after_title; }
		// Widget output //
		echo $this->wpdevart_generete_front_end($instance);
		// After widget //		
		echo $after_widget;
	}
	// Update Settings //
		function update($new_instance, $old_instance) {
		$instance = is_array( $old_instance ) ? $old_instance : array();
		$instance['title'] = strip_tags($new_instance['title']);
		$instance['text_for_day'] 			= $new_instance['text_for_day'];
		$instance['text_for_hour'] 			= $new_instance['text_for_hour'];
		$instance['text_for_minut'] 		= $new_instance['text_for_minut'];
		$instance['text_for_second'] 		= $new_instance['text_for_second'];
		$instance['end_time_type'] 			= $new_instance['end_time_type'];
		$instance['end_time_date'] 			= $new_instance['end_time_date'];
		$instance['hide_on_mobile'] 		= $new_instance['hide_on_mobile'];
		$instance['end_time'] 				= $new_instance['end_time'];
		$instance['start_time'] 			= $new_instance['start_time'];
		$instance['content'] 				= $new_instance['content'];
		$instance['action_end_time'] 		= $new_instance['action_end_time'];
		$instance['redirect_url'] 			= $new_instance['redirect_url'];
		$instance['content_position'] 		= $new_instance['content_position'];
		$instance['top_ditance'] 			= $new_instance['top_ditance'];
		$instance['bottom_distance'] 		= $new_instance['bottom_distance'];
		$instance['font_color'] 			= isset( $new_instance['font_color'] ) ? $new_instance['font_color'] : ( isset( $old_instance['font_color'] ) ? $old_instance['font_color'] : '#000000' );
		if ( WPDA_COUNTDOWN_IS_PRO ) {
			$instance['countdown_type'] 		= $new_instance['countdown_type'];
			$instance['button_bg_color'] 		= $new_instance['button_bg_color'];
			$instance['circle_size'] 			= $new_instance['circle_size'];
			$instance['circle_border'] 			= $new_instance['circle_border'];
			$instance['border_radius'] 			= $new_instance['border_radius'];
			$instance['font_size'] 				= $new_instance['font_size'];
			$instance['countdown_font_famaly'] 	= $new_instance['countdown_font_famaly'];
			$instance['animation_type'] 		= $new_instance['animation_type'];
		} else {
			// Free: these are locked to fixed values
			$instance['countdown_type']        = 'button';
			$instance['button_bg_color']       = '#3DA8CC';
			$instance['circle_size']           = '50';
			$instance['circle_border']         = '5';
			$instance['border_radius']         = '8';
			$instance['font_size']             = '30';
			$instance['countdown_font_famaly'] = 'monospace';
			$instance['animation_type']        = 'none';
		}
		return $instance;  /// return new value of parametrs
	}

	/* admin page opions */
	function form($instance) {
		$defaults = array( 
			'title' 				=> '',
			'text_for_day' 			=> __( 'Days', "wpdevart_countdown" ),
			'text_for_hour' 		=> __( 'Hours', "wpdevart_countdown" ),
			'text_for_minut' 		=> __( 'Minutes', "wpdevart_countdown" ),
			'text_for_second' 		=> __( 'Seconds', "wpdevart_countdown" ),
			'start_time' 			=> mktime (date("H"), date("i"), date("s"),date("n"), date("j"),date("Y")),
			'hide_on_mobile' 		=> 'show',
			'end_time_type' 		=> 'time',
			'end_time' 				=> '0,1,5',
			'end_time_date' 		=> date('d-m-Y 23:59'),
			'action_end_time' 		=> 'hide',
			'redirect_url' 			=> get_home_url(),
			'content' 				=> '',
			'content_position' 		=> 'center',
			'top_ditance' 			=> '10',
			'bottom_distance' 		=> '10',
			'countdown_type'		=> 'button',
			'font_color'			=> '#000000',
			'button_bg_color' 		=> '#3DA8CC',
			'circle_size' 			=> '50',
			'circle_border' 		=> '5',
			'border_radius' 		=> '8',
			'font_size' 			=> '25',
			'countdown_font_famaly' => 'monospace',
			'animation_type' 		=> 'none',
		);
		$instance = wp_parse_args( (array) $instance, $defaults );
		?> 
        <p class="flb_field">
          <label for="title">Title:</label>
          <br>
          <input id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $instance['title']; ?>" class="widefat">
        </p>
        
        <p class="flb_field">
          <label for="<?php echo $this->get_field_id('text_for_day'); ?>">Day field text:</label>
          <br>
          <input id="<?php echo $this->get_field_id('text_for_day'); ?>" name="<?php echo $this->get_field_name('text_for_day'); ?>" type="text" value="<?php echo $instance['text_for_day']; ?>" class="widefat">
        </p>
          
        <p class="flb_field">
          <label for="<?php echo $this->get_field_id('text_for_hour'); ?>">Hour field text:</label>
          <br>
          <input id="<?php echo $this->get_field_id('text_for_hour'); ?>" name="<?php echo $this->get_field_name('text_for_hour'); ?>" type="text" value="<?php echo $instance['text_for_hour']; ?>" class="widefat">
        </p>
          
        <p class="flb_field">
          <label for="<?php echo $this->get_field_id('text_for_minut'); ?>">Minute field text:</label>
          <br>
          <input id="<?php echo $this->get_field_id('text_for_minut'); ?>" name="<?php echo $this->get_field_name('text_for_minut'); ?>" type="text" value="<?php echo $instance['text_for_minut']; ?>" class="widefat">
        </p>
          
        <p class="flb_field">
          <label for="<?php echo $this->get_field_id('text_for_second'); ?>">Second field text:</label>
          <br>
          <input id="<?php echo $this->get_field_id('text_for_second'); ?>" name="<?php echo $this->get_field_name('text_for_second'); ?>" type="text" value="<?php echo $instance['text_for_second']; ?>" class="widefat">
        </p>
        
        
        <?php $time_end=explode(',',$instance['end_time']);
		
		if(isset($time_end[0]) && isset($time_end[1]) && isset($time_end[2]) ){
			$time_diferent_seconds=$time_end[0]*24*3600+$time_end[1]*3600+$time_end[2]*60;
			$ancac_jamanaky=mktime (date("H"), date("i"), date("s"),date("n"), date("j"),date("Y"))-$instance['start_time'];
			$time_diferent_seconds=$time_diferent_seconds-$ancac_jamanaky;
			if($time_diferent_seconds<0)
				$time_diferent_seconds=0;
		}
		else{		
			$time_diferent_seconds=0;
		}
		$day_of_end		=(int)($time_diferent_seconds/(24*3600));
		$day_of_end		=($day_of_end>=0)?$day_of_end:0;
		$hour_of_end	=(int)(($time_diferent_seconds-$day_of_end*24*3600)/3600);
		$hour_of_end	=($hour_of_end>=0)?$hour_of_end:0;
		$minute_of_end	=(int)(($time_diferent_seconds-$day_of_end*24*3600-$hour_of_end*3600)/60);
		$minute_of_end	=($minute_of_end>=0)?$minute_of_end:0;
		
		
		?>
		<p class="experet_type">
          <label for="<?php echo $this->get_field_id('end_time_type'); ?>">Show/Hide countdown on mobile devices</label>
          <br>
          <select class="show_hide_experet_type" id="<?php echo $this->get_field_id('end_time_type'); ?>" name="<?php echo $this->get_field_name('hide_on_mobile'); ?>">
                <option <?php selected('show',$instance['hide_on_mobile']) ?> value="show">Show</option>
                <option <?php selected('hide',$instance['hide_on_mobile']) ?> value="hide">Hide</option>
          </select>
        </p>		
        <p class="experet_type">
          <label for="<?php echo $this->get_field_id('end_time_type'); ?>">Countdown expire type :</label>
          <br>
          <select class="show_hide_experet_type" id="<?php echo $this->get_field_id('end_time_type'); ?>" name="<?php echo $this->get_field_name('end_time_type'); ?>">
                <option <?php selected('time',$instance['end_time_type']) ?> value="time">Time</option>
                <option <?php selected('date',$instance['end_time_type']) ?> value="date">Date</option>
          </select>
        </p>
        <p class="experet_type_date">
        <label>Countdown expiry date :</label>
        <br>
        <input type="text" id="<?php echo $this->get_field_id('end_time_date'); ?>" name="<?php echo $this->get_field_name('end_time_date'); ?>" value="<?php echo $instance['end_time_date'] ?>" class="wpdevart-date-time-picker" /><small>dd-mm-yyyy hh:ii</small>
        </p>
        <p class="flb_field experet_type_time">  
        <label>Countdown expiry time :</label>
          <br>     
            <span style="display:inline-block; margin-right:3px; width:70px; float: left;">
                <input onChange="insert_in_input();" type="text" placeholder="Day"   class="countdownday" size="3" value="<?php echo $day_of_end ?>"/><small style="display:block">Day</small>                
            </span>
            
            <span style="display:inline-block; width:72px; float: left;">
                <input onChange="insert_in_input();" type="text"  placeholder="Hour" class="countdownhour" size="3" value="<?php echo $hour_of_end ?>"/><small>Hour</small>                
            </span>
            
            <span style="display:inline-block; width:70px;"> 
                <input onChange="insert_in_input();" type="text"  placeholder="Minut"  class="countdownminute" size="3" value="<?php echo $minute_of_end ?>"/><small>minute</small>                
            </span>
            <script>function insert_in_input(){
				document.getElementById('<?php echo $this->get_field_id('end_time'); ?>').value=document.getElementById('<?php echo $this->get_field_id('end_time'); ?>').parentNode.getElementsByClassName('countdownday')[0].value+','+document.getElementById('<?php echo $this->get_field_id('end_time'); ?>').parentNode.getElementsByClassName('countdownhour')[0].value+','+document.getElementById('<?php echo $this->get_field_id('end_time'); ?>').parentNode.getElementsByClassName('countdownminute')[0].value
			}</script>
            <input type="hidden" value='<?php echo $day_of_end.','.$hour_of_end.','.$minute_of_end; ?>' id="<?php echo $this->get_field_id('end_time'); ?>" name="<?php echo $this->get_field_name('end_time'); ?>"/>
            <input type="hidden" value='<?php echo mktime (date("H"), date("i"), date("s"),date("n"), date("j"),date("Y")); ?>' id="<?php echo $this->get_field_id('start_time'); ?>" name="<?php echo $this->get_field_name('start_time'); ?>" />
        </p>
        
        <p class="flb_field">
            <label>After Countdown expired: </label>
            <br>
            <select id="<?php echo $this->get_field_id('action_end_time'); ?>" name="<?php echo $this->get_field_name('action_end_time'); ?>">
                <option <?php selected('show_text',$instance['action_end_time']) ?> value="show_text">Show text</option>
				<option <?php selected('redirect',$instance['action_end_time']) ?> value="redirect">Redirect</option>
                <option <?php selected('hide',$instance['action_end_time']) ?> value="hide">Hide</option>
            </select>
        </p>
        
        <p class="flb_field">
          <label for="<?php echo $this->get_field_id('content'); ?>">Message after countdown expired:</label>
          <br>
          <textarea type="text" id="<?php echo $this->get_field_id('content'); ?>" name="<?php echo $this->get_field_name('content'); ?>"><?php echo $instance['content']; ?></textarea>   
        </p>
        <p class="flb_field">
          <label for="<?php echo $this->get_field_id('redirect_url'); ?>">Redirect URL:</label>
          <br>
          <input id="<?php echo $this->get_field_id('redirect_url'); ?>" name="<?php echo $this->get_field_name('redirect_url'); ?>" type="text" value="<?php echo $instance['redirect_url']; ?>" class="widefat">
        </p>
         <p class="flb_field">
            <label>Countdown timer position: </label>
            <br>
            <select id="<?php echo $this->get_field_id('content_position'); ?>" name="<?php echo $this->get_field_name('content_position'); ?>">
                <option <?php selected('left',$instance['content_position']) ?> value="left">Left</option>
                <option <?php selected('center',$instance['content_position']) ?> value="center">Center</option>
                <option <?php selected('right',$instance['content_position']) ?> value="right">Right</option>                
            </select>
        </p>
        
         <p class="flb_field">
            <label for="<?php echo $this->get_field_id('top_ditance'); ?>">Distance from top:</label>
            <br>
            <input id="<?php echo $this->get_field_id('top_ditance'); ?>" name="<?php echo $this->get_field_name('top_ditance'); ?>" type="text" value="<?php echo $instance['top_ditance']; ?>" class="widefat">
        </p>
        
        <p class="flb_field">
            <label for="<?php echo $this->get_field_id('bottom_distance'); ?>">Distance from bottom:</label>
            <br>
            <input id="<?php echo $this->get_field_id('bottom_distance'); ?>" name="<?php echo $this->get_field_name('bottom_distance'); ?>" type="text" value="<?php echo $instance['bottom_distance']; ?>" class="widefat">
        </p>
        
        <p class="flb_field">
            <label>Countdown timer Buttons type: </label>
            <br>
            <select id="<?php echo $this->get_field_id('countdown_type'); ?>" class="countdown_set_hiddens" name="<?php echo $this->get_field_name('countdown_type'); ?>">
                <option <?php selected('button',$instance['countdown_type']) ?> value="button">Button</option>
                <option <?php selected('circle',$instance['countdown_type']) ?> value="circle"><?php echo WPDA_COUNTDOWN_IS_PRO ? 'Circle' : 'Circle (Pro)'; ?></option>
                <option <?php selected('vertical_slide',$instance['countdown_type']) ?> value="vertical_slide"><?php echo WPDA_COUNTDOWN_IS_PRO ? 'Vertical Slider' : 'Vertical Slider (Pro)'; ?></option>
            </select>
        </p>
        
        <p class="flb_field tr_button tr_circle tr_vertical_slide">
            <label for="<?php echo $this->get_field_id('font_color'); ?>">Countdown timer text color:</label>
            <br>
            <input  class="color_option" id="<?php echo $this->get_field_id('font_color'); ?>" name="<?php echo $this->get_field_name('font_color'); ?>" type="text" value="<?php echo $instance['font_color']; ?>">
        </p>
        
        <?php if ( WPDA_COUNTDOWN_IS_PRO ) : ?>
        <p class="flb_field tr_button tr_circle tr_vertical_slide">
            <label for="<?php echo $this->get_field_id('button_bg_color'); ?>"> Countdown timer background color:</label>
            <br>
            <input  class="color_option" id="<?php echo $this->get_field_id('button_bg_color'); ?>" name="<?php echo $this->get_field_name('button_bg_color'); ?>" type="text" value="<?php echo esc_attr( $instance['button_bg_color'] ); ?>">
        </p>

         <p class="flb_field tr_circle">
          <label for="<?php echo $this->get_field_id('circle_size'); ?>">Countdown timer Size</label>
          <br>
          <input id="<?php echo $this->get_field_id('circle_size'); ?>" name="<?php echo $this->get_field_name('circle_size'); ?>" type="text" value="<?php echo esc_attr( $instance['circle_size'] ); ?>" class="widefat">(Px)(min value 60)
        </p>

         <p class="flb_field tr_circle">
          <label for="<?php echo $this->get_field_id('circle_border'); ?>">Countdown timer border width:</label>
          <br>
          <input id="<?php echo $this->get_field_id('circle_border'); ?>" name="<?php echo $this->get_field_name('circle_border'); ?>" type="text" value="<?php echo esc_attr( $instance['circle_border'] ); ?>" class="widefat">%(0-100)
        </p>

         <p class="flb_field tr_button">
          <label for="<?php echo $this->get_field_id('border_radius'); ?>">Countdown timer border radius:</label>
          <br>
          <input id="<?php echo $this->get_field_id('border_radius'); ?>" name="<?php echo $this->get_field_name('border_radius'); ?>" type="text" value="<?php echo esc_attr( $instance['border_radius'] ); ?>" class="widefat">
        </p>

         <p class="flb_field tr_button tr_vertical_slide">
          <label for="<?php echo $this->get_field_id('font_size'); ?>">Countdown timer font-size:</label>
          <br>
          <input id="<?php echo $this->get_field_id('font_size'); ?>" name="<?php echo $this->get_field_name('font_size'); ?>" type="text" value="<?php echo esc_attr( $instance['font_size'] ); ?>" class="widefat">(Px)
        </p>

        <p class="flb_field tr_button tr_circle tr_vertical_slide">
          <label for="<?php echo $this->get_field_id('countdown_font_famaly'); ?>">Countdown timer Font family:</label>
          <br>
          <?php wpdevart_countdown_setting::generete_fonts($this->get_field_name('countdown_font_famaly'),$instance['countdown_font_famaly']) ?>
        </p>
         <p class="flb_field">
          <label for="<?php echo $this->get_field_id('animation_type'); ?>">Countdown animation type:</label>
          <br>
          <?php wpdevart_countdown_setting::generete_animation_select($this->get_field_name('animation_type'),$instance['animation_type']) ?>
        </p>
        <?php endif; ?>
        <br>
        <input type="hidden" id="flb-submit" name="flb-submit" value="1">
        <script>
            jQuery(".color_option").ready(function(e) {				
				jQuery(".color_option").each(function(index, element) {
                    if(!jQuery(this).hasClass('wp-color-picker') && jQuery(this).attr('name').indexOf('__i__')==-1){jQuery(this).wpColorPicker()};
                });
               
            });
			jQuery('.countdown_set_hiddens').ready(function(){
				jQuery('.countdown_set_hiddens').change(function(){
				jQuery(this).find('option').each(function(index, element) {
						jQuery(this).parent().parent().parent().find(jQuery('.tr_'+jQuery(this).val())).hide();
					});
					 	jQuery(this).parent().parent().parent().find(jQuery('.tr_'+jQuery(this).val())).show();
				})
				jQuery('.countdown_set_hiddens option').each(function(index, element) {
					jQuery(this).parent().parent().parent().find(jQuery('.tr_'+jQuery(this).val())).hide();
				});
				jQuery('.countdown_set_hiddens').each(function(index, element) {
					jQuery(this).parent().parent().parent().find(jQuery('.tr_'+jQuery(this).val())).show();
				});
			});
			jQuery('.wpdevart-date-time-picker').ready(function(){
				var nowTemp = new Date();
				var now = new Date(nowTemp.getFullYear(), nowTemp.getMonth(), nowTemp.getDate(), 0, 0, 0, 0);
				jQuery('.wpdevart-date-time-picker').fdatepicker({
				  format: 'dd-mm-yyyy hh:ii',
				  pickTime: true,
				  onRender: function (date) {
					return date.valueOf() < now.valueOf() ? 'disabled' : '';
				}
				});
			});
			jQuery('.show_hide_experet_type').ready(function(){
				jQuery('.show_hide_experet_type').each(function(index, element) {
					if(jQuery(this).val()=='time'){
						jQuery(this).parent().parent().find(jQuery('.experet_type_date')).hide();
						jQuery(this).parent().parent().find(jQuery('.experet_type_time')).show();
					}
					if(jQuery(this).val()=='date'){
						jQuery(this).parent().parent().find(jQuery('.experet_type_date')).show();
						jQuery(this).parent().parent().find(jQuery('.experet_type_time')).hide();
					}
				});
				jQuery('.show_hide_experet_type').change(function(){
					if(jQuery(this).val()=='time'){
						jQuery(this).parent().parent().find(jQuery('.experet_type_date')).hide();
						jQuery(this).parent().parent().find(jQuery('.experet_type_time')).show();
					}
					if(jQuery(this).val()=='date'){
						jQuery(this).parent().parent().find(jQuery('.experet_type_date')).show();
						jQuery(this).parent().parent().find(jQuery('.experet_type_time')).hide();
					}
				})				
			})
        </script> 
		<?php 
	}
	
	private function wpdevart_generete_front_end($parametrs){
		self::$id_for_content++;
		$output_html='';
		if($parametrs['hide_on_mobile']=="hide" && wp_is_mobile()){
			return "";
		}
		// Free build: enforce fixed styling regardless of any pro values
		// the widget instance may still hold from a prior pro install.
		if ( ! WPDA_COUNTDOWN_IS_PRO ) {
			$parametrs['countdown_type']        = 'button';
			$parametrs['button_bg_color']       = '#3DA8CC';
			$parametrs['border_radius']         = '8';
			$parametrs['font_size']             = '30';
			$parametrs['countdown_font_famaly'] = 'monospace';
			$parametrs['animation_type']        = 'none';
		}
		if(isset($parametrs['end_time_type']) && $parametrs['end_time_type']=='date'){
			$end_date=explode(' ',$parametrs['end_time_date']);
			$end_date_only_date=explode('-',$end_date[0]);
			$end_date_hour=explode(':',$end_date[1]);
			$curent_time=mktime ($end_date_hour['0'], $end_date_hour[1],0,$end_date_only_date[1], $end_date_only_date[0],$end_date_only_date[2]);
			$time_diferent=$curent_time-mktime (date("H"), date("i"), date("s"),date("n"), date("j"),date("Y"));
		}else{
			$time_experit=explode(',',$parametrs['end_time']);
			$time_diferent=(int)$time_experit[0]*24*3600+(int)+$time_experit[1]*3600+(int)$time_experit[2]*60+$parametrs['start_time']-mktime (date("H"), date("i"), date("s"),date("n"), date("j"),date("Y"));
		}
		$day_left=(int)($time_diferent/(3600*24));
		$hourse_left=(int)(($time_diferent-$day_left*24*3600)/(3600));
		$minuts_left=(int)(($time_diferent-$day_left*24*3600-$hourse_left*3600)/(60));
		$seconds_left=(int)(($time_diferent-$day_left*24*3600-$hourse_left*3600 - $minuts_left*60));	
		if(strlen("".$day_left)>0 && strlen("".$day_left)<2)
			$day_left='0'.$day_left;
		if(strlen("".$hourse_left)>0 && strlen("".$hourse_left)<2)
			$hourse_left='0'.$hourse_left;
		if(strlen("".$minuts_left)>0 && strlen("".$minuts_left)<2)
			$minuts_left='0'.$minuts_left;
		if(strlen("".$seconds_left)>0 && strlen("".$seconds_left)<2)
			$seconds_left='0'.$seconds_left;		 
		
				$output_html.='<div class="content_countdown" id="main_countedown_widget_'.self::$id_for_content.'">';
			switch($parametrs['countdown_type']){
				case 'button':
			
			$output_html.='<div class="countdown">
				<span class="element_conteiner"><span  class="days time_left">'.$day_left.'</span><span class="time_description">'.$parametrs['text_for_day'].'</span></span>
				<span class="element_conteiner"><span  class="hourse time_left">'.$hourse_left.'</span><span class="time_description">'.$parametrs['text_for_hour'].'</span></span>
				<span class="element_conteiner"><span  class="minutes time_left">'.$minuts_left.'</span><span class="time_description">'.$parametrs['text_for_minut'].'</span></span>
				<span class="element_conteiner"><span  class="secondes time_left">'.$seconds_left.'</span><span class="time_description">'.$parametrs['text_for_second'].'</span></span>
			</div>';
		 
			break;
			case 'circle':
			$output_html.='<div  class="countdown"></div>';
			
			 
			break;
			case 'vertical_slide':
			$output_html.='<div  class="countdown"></div>';
			               
			 
			break;
		}
		$output_html.='</div>';
		
		
		
		
		
		
		
		
		
		
		/*************************************************************/
		$output_js='';
			 
		switch($parametrs['countdown_type']){
		case 'button':
		if(($day_left<=0 && $hourse_left<=0 && $minuts_left<=0 && $seconds_left<=0)){
			if($parametrs['action_end_time']=='redirect'){
				$output_js_code="window.location.replace('".$parametrs['redirect_url']."')";
			}
			elseif($parametrs['action_end_time']=='show_text'){
				$output_js_code="jQuery('#main_countedown_widget_".self::$id_for_content." .countdown').html('".htmlspecialchars($parametrs['content'])."')";
			}
			else{				
				$output_js_code="jQuery('#main_countedown_widget_".self::$id_for_content." .countdown').html('')";
			}
		}else{
			$output_js_code="setInterval(function(){countdown_wpdevart_timer('main_countedown_widget_".self::$id_for_content."');},1000);";
			
		}
		$output_js.="
        document.addEventListener('DOMContentLoaded', function(event) {
			wpdevart_countdown_animated_element('".wpdevart_countdown_setting::get_animations_type_array($parametrs['animation_type'])."','main_countedown_widget_".self::$id_for_content."'); jQuery(window).scroll(function(){wpdevart_countdown_animated_element('".wpdevart_countdown_setting::get_animations_type_array($parametrs['animation_type'])."','main_countedown_widget_".self::$id_for_content."');});
			".$output_js_code."
		});
		";
		break;
		case 'circle':
		$output_js_code="";
		if($parametrs['action_end_time']=='redirect'){
			$output_js_code="window.location.replace('".$parametrs['redirect_url']."')";
		}
		elseif($parametrs['action_end_time']=='show_text'){
			$output_js_code="jQuery('#main_countedown_widget_".self::$id_for_content." .countdown').html('".htmlspecialchars($parametrs['content'])."')";
		}
		else{				
			$output_js_code="jQuery('#main_countedown_widget_".self::$id_for_content." .countdown').html('')";
		}
		$output_js.="
         document.addEventListener('DOMContentLoaded', function(event) {
			wpdevart_countdown_animated_element('".wpdevart_countdown_setting::get_animations_type_array($parametrs['animation_type'])."','main_countedown_widget_".self::$id_for_content."'); jQuery(window).scroll(function(){wpdevart_countdown_animated_element('".wpdevart_countdown_setting::get_animations_type_array($parametrs['animation_type'])."','main_countedown_widget_".self::$id_for_content."');});
            jQuery('#main_countedown_widget_".self::$id_for_content." .countdown').ClassyCountdown({
                end: '".$time_diferent."',
                now: '0',
                labels: true,               
                labelsOptions: {
                    lang: {
                        days: '".$parametrs['text_for_day']."',
                        hours: '".$parametrs['text_for_hour']."',
                        minutes: '".$parametrs['text_for_minut']."',
                        seconds: '".$parametrs['text_for_second']."'
                    },                  
                },
                style: {
                    element: \"\",
                    textResponsive:3.5,
                    days: {
                        gauge: {
                            thickness:".((float)((int)$parametrs['circle_border']/100)).",
                            bgColor: 'rgba(0,0,0,0)',
                            fgColor: '".$parametrs['button_bg_color']."',
                            lineCap: 'round'
                        },
                        textCSS: 'font-family:".stripslashes($parametrs['countdown_font_famaly']).";  color:".stripslashes($parametrs['font_color']).";'
                    },
                    hours: {
                        gauge: {
                            thickness: ".((float)((int)$parametrs['circle_border']/100)).",
                            bgColor: 'rgba(0,0,0,0)',
                            fgColor: '".$parametrs['button_bg_color']."',
                            lineCap: 'round'
                        },
                        textCSS: 'font-family:".stripslashes($parametrs['countdown_font_famaly']).";  color:".stripslashes($parametrs['font_color']).";'
                    },
                    minutes: {
                        gauge: {
                            thickness:".((float)((int)$parametrs['circle_border']/100)).",
                            bgColor: 'rgba(0,0,0,0)',
                            fgColor: '".$parametrs['button_bg_color']."',
                            lineCap: 'round'
                        },
                        textCSS: 'font-family:".stripslashes($parametrs['countdown_font_famaly'])."; color:".stripslashes($parametrs['font_color']).";'
                    },
                    seconds: {
                        gauge: {
                            thickness: ".((float)((int)$parametrs['circle_border']/100)).",
                            bgColor:'rgba(0,0,0,0)',
                            fgColor: '".$parametrs['button_bg_color']."',
                            lineCap: 'round'
                        },
                        textCSS: 'font-family:".stripslashes($parametrs['countdown_font_famaly'])."; color:".stripslashes($parametrs['font_color']).";'
                    }
        
                },
                onEndCallback: function() {
                 ".$output_js_code."
                }
            });
        })";		
		break;
		case 'vertical_slide':
		$output_js_code="";
		if($parametrs['action_end_time']=='redirect'){
			$output_js_code="window.location.replace('".$parametrs['redirect_url']."')";
		}
		elseif($parametrs['action_end_time']=='show_text'){
			$output_js_code="jQuery('#main_countedown_widget_".self::$id_for_content." .countdown').html('".htmlspecialchars($parametrs['content'])."')";
		}
		else{				
			$output_js_code="jQuery('#main_countedown_widget_".self::$id_for_content." .countdown').html('')";
		}
		if(($day_left<=0 && $hourse_left<=0 && $minuts_left<=0 && $seconds_left<=0)){
		$output_js.=" document.addEventListener('DOMContentLoaded', function(event) {
			wpdevart_countdown_animated_element('".wpdevart_countdown_setting::get_animations_type_array($parametrs['animation_type'])."','main_countedown_widget_".self::$id_for_content."'); jQuery(window).scroll(function(){wpdevart_countdown_animated_element('".wpdevart_countdown_setting::get_animations_type_array($parametrs['animation_type'])."','main_countedown_widget_".self::$id_for_content."');});
			".$output_js_code."
			})";
		}else{	
			$output_js.=" document.addEventListener('DOMContentLoaded', function(event) {
				wpdevart_countdown_animated_element('".wpdevart_countdown_setting::get_animations_type_array($parametrs['animation_type'])."','main_countedown_widget_".self::$id_for_content."'); jQuery(window).scroll(function(){wpdevart_countdown_animated_element('".wpdevart_countdown_setting::get_animations_type_array($parametrs['animation_type'])."','main_countedown_widget_".self::$id_for_content."');});
				jQuery('#main_countedown_widget_".self::$id_for_content." .countdown').timeTo({
					".(($day_left<100)?' displayDays: 2,':'')."
					seconds:".$time_diferent.",
					lang:{
							days: '".$parametrs['text_for_day']."',
							hours: '".$parametrs['text_for_hour']."',
							min: '".$parametrs['text_for_minut']."',
							sec: '".$parametrs['text_for_second']."'
						},                  
				 
					displayCaptions: true,
					fontSize: ".$parametrs['font_size'].",
					captionSize: ".((int)($parametrs['font_size']/2.2)).",
					callback:function(){ ".$output_js_code."} 
				}); 
			})";
		}
		break;
		}
		/************************************************************/
		
		$output_css='';
		$output_css.='#main_countedown_widget_'.self::$id_for_content.' .countdown{text-align:'.$parametrs['content_position'].';}';
		$output_css.= '#main_countedown_widget_'.self::$id_for_content.' .countdown{margin-top:'.$parametrs['top_ditance'].'px;margin-bottom:'.$parametrs['bottom_distance'].'px}';
		switch($parametrs['countdown_type']){
			case 'button':
				
				$output_css.= "#main_countedown_widget_".self::$id_for_content." .time_left{\r\n";
				$output_css.= "border-radius:".$parametrs['border_radius']."px;\r\n";
				$output_css.= "background-color:".$parametrs['button_bg_color'].";\r\n";
				$output_css.= "font-size:".($parametrs['font_size'])."px;\r\n";
				$output_css.= "font-family:".$parametrs['countdown_font_famaly'].";\r\n";
				$output_css.= "color:".$parametrs['font_color'].";\r\n";
				$output_css.= "}\r\n";
				$output_css.= "#main_countedown_widget_".self::$id_for_content." .time_description{\r\n";
				$output_css.= "font-size:".($parametrs['font_size']-8)."px;\r\n";
				$output_css.= "font-family:".$parametrs['countdown_font_famaly'].";\r\n";
				$output_css.= "color:".$parametrs['font_color'].";\r\n";
				$output_css.= "}\r\n";
				$output_css.= "#main_countedown_widget_".self::$id_for_content." .element_conteiner{min-width:".($parametrs['font_size']*3.5)."px; min-height:".($parametrs['font_size']*2)."px;}";
				break;
			case 'circle':
				$output_css.= ".ClassyCountdown-wrapper > div {width:".$parametrs['circle_size']."px;}\r\n";
			break;
			case 'vertical_slide':
				$output_css.= "#main_countedown_widget_".self::$id_for_content." .countdown.timeTo div {color:".$parametrs['font_color'].";\r\n";
				$output_css.= "font-size:".$parametrs['font_size']."px;\r\n";
				$output_css.= "font-family:".$parametrs['countdown_font_famaly'].";\r\n";
				$output_css.= "background: ".$parametrs['button_bg_color'].";\r\n";
				$output_css.= "background: -moz-linear-gradient(top, ".$parametrs['button_bg_color']." 0%, #".wpdevart_countdown_setting::darkest_brigths($parametrs['button_bg_color'],20)." 100%);\r\n";
				$output_css.= "background: -webkit-gradient(linear, left top, left bottom, color-stop(0%,".$parametrs['button_bg_color']."), color-stop(100%,#".wpdevart_countdown_setting::darkest_brigths($parametrs['button_bg_color'],20)."));\r\n";
				$output_css.= "background: -webkit-linear-gradient(top, ".$parametrs['button_bg_color']." 0%,#".wpdevart_countdown_setting::darkest_brigths($parametrs['button_bg_color'],20)." 100%);\r\n";
				$output_css.= "background: -o-linear-gradient(top, ".$parametrs['button_bg_color']." 0%,#".wpdevart_countdown_setting::darkest_brigths($parametrs['button_bg_color'],20)." 100%); \r\n";
				$output_css.= "background: -ms-linear-gradient(top, ".$parametrs['button_bg_color']." 0%,#".wpdevart_countdown_setting::darkest_brigths($parametrs['button_bg_color'],20)." 100%);\r\n";
				$output_css.= "background: linear-gradient(to bottom, #".wpdevart_countdown_setting::darkest_brigths($parametrs['button_bg_color'],20)." 0%,".$parametrs['button_bg_color']." 100%); \r\n";
				$output_css.= "filter: progid:DXImageTransform.Microsoft.gradient( startColorstr='#45484d', endColorstr='#000000',GradientType=0 );\r\n";
				$output_css.= "}";
				$output_css.= "#main_countedown_widget_".self::$id_for_content." .timeTo figcaption {color:".$parametrs['font_color'].";\r\n font-family:".$parametrs['countdown_font_famaly'].";\r\n}";
			break;
		}
		$output_html.='<script>'.$output_js.'</script><style>'.$output_css.'</style>';
		return $output_html;
	}
}