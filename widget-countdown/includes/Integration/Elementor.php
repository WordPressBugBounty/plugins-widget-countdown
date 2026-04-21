<?php
if(!defined('ABSPATH')) exit;

class Wpda_Countdown_Elementor{
	public function __construct(){
		add_action('elementor/widgets/register',array($this,'register_widget'));
		add_action('elementor/elements/categories_registered',array($this,'add_category'));
	}
	public function add_category($elements_manager){
		$elements_manager->add_category('wpdevart',array(
			'title'=>'WpDevArt',
			'icon'=>'eicon-clock-o',
		));
	}
	public function register_widget($widgets_manager){
		$widgets_manager->register(new Wpda_Countdown_Elementor_Widget());
	}
}

class Wpda_Countdown_Elementor_Widget extends \Elementor\Widget_Base{

	public function get_name(){return 'wpdevart_countdown';}
	public function get_title(){return 'Countdown';}
	public function get_icon(){return 'eicon-countdown';}
	public function get_categories(){return ['wpdevart'];}
	public function get_keywords(){return ['countdown','timer','clock','deadline','evergreen'];}

	private function get_timers(){
		return array( '' => '— Select Timer —' ) + wpda_countdown()->timer_repository()->all_names();
	}
	private function get_themes(){
		return array( '' => '— Default Theme —' ) + wpda_countdown()->theme_repository()->all_names();
	}

	protected function register_controls(){

		// --- Mode selector ---
		$this->start_controls_section('section_mode',array(
			'label'=>'Countdown',
			'tab'=>\Elementor\Controls_Manager::TAB_CONTENT,
		));

		$this->add_control('mode',array(
			'label'=>'Mode',
			'type'=>\Elementor\Controls_Manager::CHOOSE,
			'options'=>array(
				'timer'=>array('title'=>'Select Timer','icon'=>'eicon-clock-o'),
				'date'=>array('title'=>'Quick Date','icon'=>'eicon-calendar'),
			),
			'default'=>'timer',
			'toggle'=>false,
		));

		// --- Timer mode controls ---
		$this->add_control('timer_id',array(
			'label'=>'Timer',
			'type'=>\Elementor\Controls_Manager::SELECT,
			'options'=>$this->get_timers(),
			'default'=>'',
			'condition'=>array('mode'=>'timer'),
		));

		// --- Date mode controls ---
		$this->add_control('end_date',array(
			'label'=>'End Date',
			'type'=>\Elementor\Controls_Manager::DATE_TIME,
			'default'=>'',
			'condition'=>array('mode'=>'date'),
		));

		// --- Shared: theme ---
		$this->add_control('theme_id',array(
			'label'=>'Theme',
			'type'=>\Elementor\Controls_Manager::SELECT,
			'options'=>$this->get_themes(),
			'default'=>'',
		));

		$this->end_controls_section();
	}

	protected function render(){
		$s=$this->get_settings_for_display();
		$mode=$s['mode'];
		$theme_id=!empty($s['theme_id'])?$s['theme_id']:'0';

		if($mode==='date' && !empty($s['end_date'])){
			// Elementor DATE_TIME returns "Y-m-d H:i" format
			$dt=sanitize_text_field($s['end_date']);
			$parts=explode(' ',$dt);
			$d=explode('-',$parts[0]);
			if(count($d)===3){
				$formatted=$d[2].'/'.$d[1].'/'.$d[0].' '.(isset($parts[1])?$parts[1]:'23:59');
				echo do_shortcode('[wpda_countdown end_date="'.esc_attr($formatted).'" theme_id="'.intval($theme_id).'"]');
			}
			return;
		}

		if(!empty($s['timer_id'])){
			echo do_shortcode('[wpda_countdown timer_id="'.intval($s['timer_id']).'" theme_id="'.intval($theme_id).'"]');
			return;
		}

		if(\Elementor\Plugin::$instance->editor->is_edit_mode()){
			echo '<div style="padding:20px;text-align:center;background:#f0f0f1;border:1px dashed #ccc;border-radius:6px;color:#646970;font-size:13px;">';
			echo '<span style="font-size:24px;display:block;margin-bottom:6px;">⏱</span>';
			echo 'Select a timer or set an end date in the widget settings.';
			echo '</div>';
		}
	}
}
