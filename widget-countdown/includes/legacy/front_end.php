<?php 

class wpdevart_countdown_front_end{
	private $menu_name;
	
	private $plugin_url;
	
	private $databese_parametrs;
	
	private $params;
	
	public static $id_for_content=0;

	function __construct($params){
		if(isset($param['databese_parametrs']))
			$this->databese_parametrs=$params['databese_parametrs'];
		//if plugin url not come in parent class
		if(isset($params['plugin_url']))
			$this->plugin_url=$params['plugin_url'];
		else
			$this->plugin_url=trailingslashit(dirname(dirname(plugins_url('',__FILE__))));

		//hooks for popup iframe
		add_action('wp_head',array($this,'generete_front_javascript'));
		// add shortcode
		add_shortcode( 'wpdevart_countdown', array($this,'wpdevart_wpdevart_countdown_shortcode') );
		//for updated parametrs
		
	}
	/*###################### scripts and styles ##################*/
	public function generete_front_javascript(){
		wp_enqueue_script('countdown-front-end');
		wp_enqueue_script('thickbox');
		if ( WPDA_COUNTDOWN_IS_PRO ) {
			wp_enqueue_script('canvase_countedown_main');
			wp_enqueue_script('canvase_countedown_jquery_lib_knop');
			wp_enqueue_script('canvase_countedown_jquery_lib_thortle');
			wp_enqueue_script('canvase_countedown_lexsus');
		}
		wp_enqueue_style('animated');
		wp_enqueue_style('countdown_css');
		wp_enqueue_style('thickbox');
	}
	public function wpdevart_wpdevart_countdown_shortcode( $atts,$content){
		self::$id_for_content++;
		$output_html='';
		$curent_value= shortcode_atts( array(
			"text_for_day" 			=> __( "Days", "wpdevart_countdown" ),
			"text_for_hour"			=> __( "Hours", "wpdevart_countdown" ),
			"text_for_minut"		=> __( "Minutes", "wpdevart_countdown" ),
			"text_for_second"		=> __( "Seconds", "wpdevart_countdown" ),
			"start_time"			=> mktime (date("H"), date("i"), date("s"),date("n"), date("j"),date("Y")),	
			"redirect_url"			=> get_home_url(),			
			"countdown_end_type"	=> "time",
			"end_date"				=> date('d-m-Y 23:59'),
			"end_time"				=> "0,1,0",
			"hide_on_mobile"		=> "show",
			"action_end_time"		=> "hide",
			"content_position"		=> "center",
			"top_ditance"			=> "10",
			"bottom_distance"		=> "10",
			"countdown_type"		=> "button",
			"font_color"			=> "#000000",
			"button_bg_color"		=> "#3DA8CC",
			"circle_size"			=> "130",
			"circle_border"			=> "4",
			"border_radius"			=> "8",			
			"font_size"				=> "30",
			"countdown_font_famaly"	=> "monospace",
			"animation_type"		=> "none",
			"content"				=>$content
		), $atts);

		// Free build: lock styling fields to fixed values so existing shortcodes
		// with pro attributes still render consistently, and downgrade pro types.
		if ( ! WPDA_COUNTDOWN_IS_PRO ) {
			$curent_value['countdown_type']        = 'button';
			$curent_value['button_bg_color']       = '#3DA8CC';
			$curent_value['border_radius']         = '8';
			$curent_value['font_size']             = '30';
			$curent_value['countdown_font_famaly'] = 'monospace';
			$curent_value['animation_type']        = 'none';
		}

		if($curent_value["hide_on_mobile"]=="hide" && wp_is_mobile()){
			return "";
		}
		if(isset($curent_value['countdown_end_type']) && $curent_value['countdown_end_type']=='date'){
			$end_date=explode(' ',$curent_value['end_date']);
			$end_date_only_date=explode('-',$end_date[0]);
			$end_date_hour=explode(':',$end_date[1]);
			$curent_time=mktime ($end_date_hour['0'], $end_date_hour[1],0,$end_date_only_date[1], $end_date_only_date[0],$end_date_only_date[2]);
			$time_diferent=$curent_time-mktime (date("H"), date("i"), date("s"),date("n"), date("j"),date("Y"));
		}else{
			$time_experit=explode(',',$curent_value['end_time']);
			$time_diferent=(int)$time_experit[0]*24*3600+(int)+$time_experit[1]*3600+(int)$time_experit[2]*60+$curent_value['start_time']-mktime (date("H"), date("i"), date("s"),date("n"), date("j"),date("Y"));
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
		
				$output_html.='<div class="content_countdown" id="main_countedown_'.self::$id_for_content.'">';
			switch($curent_value['countdown_type']){
				case 'button':
			
			$output_html.='<div class="countdown">
				<span class="element_conteiner"><span  class="days time_left">'.$day_left.'</span><span class="time_description">'.$curent_value['text_for_day'].'</span></span>
				<span class="element_conteiner"><span  class="hourse time_left">'.$hourse_left.'</span><span class="time_description">'.$curent_value['text_for_hour'].'</span></span>
				<span class="element_conteiner"><span  class="minutes time_left">'.$minuts_left.'</span><span class="time_description">'.$curent_value['text_for_minut'].'</span></span>
				<span class="element_conteiner"><span  class="secondes time_left">'.$seconds_left.'</span><span class="time_description">'.$curent_value['text_for_second'].'</span></span>
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
		$output_html.='<script>'.$this->wpdevart_countdown_javascript($curent_value).'</script><style>'.$this->wpdevart_countdown_css($curent_value).'</style>';
		return	$output_html;		
	}
	public function wpdevart_countdown_javascript($parametrs_for_countedown){
		$output_js='';
		
		if(isset($parametrs_for_countedown['countdown_end_type']) && $parametrs_for_countedown['countdown_end_type']=='date'){
					$end_date=explode(' ',$parametrs_for_countedown['end_date']);
					$end_date_only_date=explode('-',$end_date[0]);
					$end_date_hour=explode(':',$end_date[1]);
					$curent_time=mktime ($end_date_hour['0'], $end_date_hour[1],0,$end_date_only_date[1], $end_date_only_date[0],$end_date_only_date[2]);
					$time_diferent=$curent_time-mktime (date("H"), date("i"), date("s"),date("n"), date("j"),date("Y"));
		}else{
			$time_experit=explode(',',$parametrs_for_countedown['end_time']);
			$time_diferent=(int)$time_experit[0]*24*3600+(int)+$time_experit[1]*3600+(int)$time_experit[2]*60+$parametrs_for_countedown['start_time']-mktime (date("H"), date("i"), date("s"),date("n"), date("j"),date("Y"));
		}		$day_left=(int)($time_diferent/(3600*24));
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
		switch($parametrs_for_countedown['countdown_type']){
		case 'button':
		$output_js_code='';
			
		if(($day_left<=0 && $hourse_left<=0 && $minuts_left<=0 && $seconds_left<=0)){
			if($parametrs_for_countedown['action_end_time']=='redirect'){
				$output_js_code="window.location.replace('".$parametrs_for_countedown['redirect_url']."')";
			}
			elseif($parametrs_for_countedown['action_end_time']=='show_text'){
				$output_js_code="jQuery('#main_countedown_".self::$id_for_content." .countdown').html('".htmlspecialchars($parametrs_for_countedown['content'])."')";
			}
			else{				
				$output_js_code="jQuery('#main_countedown_".self::$id_for_content." .countdown').html('')";
			}
		}else{
			$output_js_code="setInterval(function(){countdown_wpdevart_timer('main_countedown_".self::$id_for_content."');},1000);";
			
		}
				
		$output_js.="
        document.addEventListener('DOMContentLoaded', function(event) {			wpdevart_countdown_animated_element('".wpdevart_countdown_setting::get_animations_type_array($parametrs_for_countedown['animation_type'])."','main_countedown_".self::$id_for_content."'); jQuery(window).scroll(function(){wpdevart_countdown_animated_element('".wpdevart_countdown_setting::get_animations_type_array($parametrs_for_countedown['animation_type'])."','main_countedown_".self::$id_for_content."');});
			".$output_js_code."
		});
		";
		break;
		case 'circle':
		$output_js_code='';
			
		if($parametrs_for_countedown['action_end_time']=='redirect'){
			$output_js_code="window.location.replace('".$parametrs_for_countedown['redirect_url']."')";
		}
		elseif($parametrs_for_countedown['action_end_time']=='show_text'){
			$output_js_code="jQuery('#main_countedown_".self::$id_for_content." .countdown').html('".htmlspecialchars($parametrs_for_countedown['content'])."')";
		}
		else{				
			$output_js_code="jQuery('#main_countedown_".self::$id_for_content." .countdown').html('')";
		}
		$output_js.="
        document.addEventListener('DOMContentLoaded', function(event) {
			wpdevart_countdown_animated_element('".wpdevart_countdown_setting::get_animations_type_array($parametrs_for_countedown['animation_type'])."','main_countedown_".self::$id_for_content."'); jQuery(window).scroll(function(){wpdevart_countdown_animated_element('".wpdevart_countdown_setting::get_animations_type_array($parametrs_for_countedown['animation_type'])."','main_countedown_".self::$id_for_content."');});
            jQuery('#main_countedown_".self::$id_for_content." .countdown').ClassyCountdown({
                end: '".$time_diferent."',
                now: '0',
                labels: true,               
                labelsOptions: {
                    lang: {
                        days: '".$parametrs_for_countedown['text_for_day']."',
                        hours: '".$parametrs_for_countedown['text_for_hour']."',
                        minutes: '".$parametrs_for_countedown['text_for_minut']."',
                        seconds: '".$parametrs_for_countedown['text_for_second']."'
                    },                  
                },
                style: {
                    element: \"\",
                    textResponsive:3.5,
                    days: {
                        gauge: {
                            thickness:".((float)((int)$parametrs_for_countedown['circle_border']/100)).",
                            bgColor: 'rgba(0,0,0,0)',
                            fgColor: '".$parametrs_for_countedown['button_bg_color']."',
                            lineCap: 'round'
                        },
                        textCSS: 'font-family:".stripslashes($parametrs_for_countedown['countdown_font_famaly']).";  color:".stripslashes($parametrs_for_countedown['font_color']).";'
                    },
                    hours: {
                        gauge: {
                            thickness: ".((float)((int)$parametrs_for_countedown['circle_border']/100)).",
                            bgColor: 'rgba(0,0,0,0)',
                            fgColor: '".$parametrs_for_countedown['button_bg_color']."',
                            lineCap: 'round'
                        },
                        textCSS: 'font-family:".stripslashes($parametrs_for_countedown['countdown_font_famaly']).";  color:".stripslashes($parametrs_for_countedown['font_color']).";'
                    },
                    minutes: {
                        gauge: {
                            thickness:".((float)((int)$parametrs_for_countedown['circle_border']/100)).",
                            bgColor: 'rgba(0,0,0,0)',
                            fgColor: '".$parametrs_for_countedown['button_bg_color']."',
                            lineCap: 'round'
                        },
                        textCSS: 'font-family:".stripslashes($parametrs_for_countedown['countdown_font_famaly'])."; color:".stripslashes($parametrs_for_countedown['font_color']).";'
                    },
                    seconds: {
                        gauge: {
                            thickness: ".((float)((int)$parametrs_for_countedown['circle_border']/100)).",
                            bgColor:'rgba(0,0,0,0)',
                            fgColor: '".$parametrs_for_countedown['button_bg_color']."',
                            lineCap: 'round'
                        },
                        textCSS: 'font-family:".stripslashes($parametrs_for_countedown['countdown_font_famaly'])."; color:".stripslashes($parametrs_for_countedown['font_color']).";'
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
		if($parametrs_for_countedown['action_end_time']=='redirect'){
			$output_js_code="window.location.replace('".$parametrs_for_countedown['redirect_url']."')";
		}
		elseif($parametrs_for_countedown['action_end_time']=='show_text'){
			$output_js_code="jQuery('#main_countedown_".self::$id_for_content." .countdown').html('".htmlspecialchars($parametrs_for_countedown['content'])."')";
		}
		else{				
			$output_js_code="jQuery('#main_countedown_".self::$id_for_content." .countdown').html('')";
		}
		if(($day_left<=0 && $hourse_left<=0 && $minuts_left<=0 && $seconds_left<=0)){
		$output_js.=" document.addEventListener('DOMContentLoaded', function(event) {
			wpdevart_countdown_animated_element('".wpdevart_countdown_setting::get_animations_type_array($parametrs_for_countedown['animation_type'])."','main_countedown_".self::$id_for_content."'); jQuery(window).scroll(function(){wpdevart_countdown_animated_element('".wpdevart_countdown_setting::get_animations_type_array($parametrs_for_countedown['animation_type'])."','main_countedown_".self::$id_for_content."');});
			".$output_js_code."
			})";
		}else{	
			$output_js.=" document.addEventListener('DOMContentLoaded', function(event) {
				wpdevart_countdown_animated_element('".wpdevart_countdown_setting::get_animations_type_array($parametrs_for_countedown['animation_type'])."','main_countedown_".self::$id_for_content."'); jQuery(window).scroll(function(){wpdevart_countdown_animated_element('".wpdevart_countdown_setting::get_animations_type_array($parametrs_for_countedown['animation_type'])."','main_countedown_".self::$id_for_content."');});
				jQuery('#main_countedown_".self::$id_for_content." .countdown').timeTo({
					".(($day_left<100)?' displayDays: 2,':'')."
					seconds:".$time_diferent.",
					lang:{
							days: '".$parametrs_for_countedown['text_for_day']."',
							hours: '".$parametrs_for_countedown['text_for_hour']."',
							min: '".$parametrs_for_countedown['text_for_minut']."',
							sec: '".$parametrs_for_countedown['text_for_second']."'
						},                  
				 
					displayCaptions: true,
					fontSize: ".$parametrs_for_countedown['font_size'].",
					captionSize: ".((int)($parametrs_for_countedown['font_size']/2.2)).",
					callback:function(){ ".$output_js_code."} 
				}); 
			})";
		}
		break;
		}
		return $output_js;
	}
	public function wpdevart_countdown_css($parametrs_for_countedown){
		$output_css='';
		$output_css.='#main_countedown_'.self::$id_for_content.' .countdown{text-align:'.$parametrs_for_countedown['content_position'].';}';
		$output_css.= '#main_countedown_'.self::$id_for_content.' .countdown{margin-top:'.$parametrs_for_countedown['top_ditance'].'px;margin-bottom:'.$parametrs_for_countedown['bottom_distance'].'px}';
		switch($parametrs_for_countedown['countdown_type']){
			case 'button':
				$output_css.= "#main_countedown_".self::$id_for_content." .time_left{\r\n";
				$output_css.= "border-radius:".$parametrs_for_countedown['border_radius']."px;\r\n";
				$output_css.= "background-color:".$parametrs_for_countedown['button_bg_color'].";\r\n";
				$output_css.= "font-size:".($parametrs_for_countedown['font_size'])."px;\r\n";
				$output_css.= "font-family:".$parametrs_for_countedown['countdown_font_famaly'].";\r\n";
				$output_css.= "color:".$parametrs_for_countedown['font_color'].";\r\n";
				$output_css.= "}\r\n";
				$output_css.= "#main_countedown_".self::$id_for_content." .time_description{\r\n";
				$output_css.= "font-size:".($parametrs_for_countedown['font_size']-8)."px;\r\n";
				$output_css.= "font-family:".$parametrs_for_countedown['countdown_font_famaly'].";\r\n";
				$output_css.= "color:".$parametrs_for_countedown['font_color'].";\r\n";
				$output_css.= "}\r\n";
				$output_css.= "#main_countedown_".self::$id_for_content." .element_conteiner{min-width:".($parametrs_for_countedown['font_size']*3.5)."px}";
				break;
			case 'circle':
				$output_css.= ".ClassyCountdown-wrapper > div {width:".$parametrs_for_countedown['circle_size']."px;}\r\n";
			break;
			case 'vertical_slide':
				$output_css.= "#main_countedown_".self::$id_for_content." .countdown.timeTo div {color:".$parametrs_for_countedown['font_color'].";\r\n";
				$output_css.= "font-size:".$parametrs_for_countedown['font_size']."px;\r\n";
				$output_css.= "font-family:".$parametrs_for_countedown['countdown_font_famaly'].";\r\n";
				$output_css.= "background: #45484d;\r\n";
				$output_css.= "background: -moz-linear-gradient(top, #45484d 0%, #000000 100%);\r\n";
				$output_css.= "background: -webkit-gradient(linear, left top, left bottom, color-stop(0%,#45484d), color-stop(100%,#000000));\r\n";
				$output_css.= "background: -webkit-linear-gradient(top, #45484d 0%,#000000 100%);\r\n";
				$output_css.= "background: -o-linear-gradient(top, #45484d 0%,#000000 100%); \r\n";
				$output_css.= "background: -ms-linear-gradient(top, #45484d 0%,#000000 100%);\r\n";
				$output_css.= "background: linear-gradient(to bottom, #".wpdevart_countdown_setting::darkest_brigths($parametrs_for_countedown['button_bg_color'],20)." 0%,".$parametrs_for_countedown['button_bg_color']." 100%); \r\n";
				$output_css.= "filter: progid:DXImageTransform.Microsoft.gradient( startColorstr='#45484d', endColorstr='#000000',GradientType=0 );\r\n";
				$output_css.= "}";
				$output_css.= "#main_countedown_".self::$id_for_content." .timeTo figcaption {color:".$parametrs_for_countedown['font_color'].";\r\n font-family:".$parametrs_for_countedown['countdown_font_famaly'].";\r\n}";
			break;
		}
		return $output_css;
	}
}
?>