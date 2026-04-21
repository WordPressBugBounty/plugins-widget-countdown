<?php
if ( ! defined( 'ABSPATH' ) ) exit;
if (class_exists('wpda_countdown_post_page_popup')) return;

class wpda_countdown_post_page_popup{
	function __construct(){
		$this->generete_html();
	}
	private function required_js_and_style(){
		wp_print_scripts("jquery");
		?>
		<script language="javascript" type="text/javascript" src="<?php echo site_url(); ?>/wp-includes/js/tinymce/tiny_mce_popup.js"></script>
		<?php
	}
	private function generete_html(){
		?>
		<!DOCTYPE html>
		<html><head>
			<?php $this->required_js_and_style(); ?>
			<title>WpDevArt Countdown</title>
			<style>
				*{box-sizing:border-box;margin:0;padding:0}
				body{font-family:-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,sans-serif;padding:16px;background:#f0f0f1}
				.wpda_card{background:#fff;border:1px solid #e0e0e0;border-radius:8px;padding:16px;margin-bottom:12px}
				.wpda_tabs{display:flex;gap:0;border-radius:6px;overflow:hidden;border:1px solid #ddd;margin-bottom:14px}
				.wpda_tab{flex:1;padding:9px 0;border:none;cursor:pointer;font-weight:600;font-size:12px;text-align:center;transition:all 0.2s;background:#fff;color:#50575e}
				.wpda_tab.active{background:#1d2327;color:#fff}
				.wpda_tab+.wpda_tab{border-left:1px solid #ddd}
				label{display:block;font-size:11px;font-weight:600;color:#1d2327;margin-bottom:3px}
				select,input[type=date],input[type=time]{width:100%;padding:7px;border-radius:4px;border:1px solid #ddd;font-size:12px}
				.wpda_row{margin-bottom:10px}
				.wpda_flex{display:flex;gap:8px}
				.wpda_flex>div{flex:1}
				.wpda_actions{display:flex;gap:8px;justify-content:flex-end;margin-top:12px}
				.wpda_btn{padding:7px 18px;border-radius:5px;border:none;cursor:pointer;font-size:12px;font-weight:500}
				.wpda_primary{background:#2271b1;color:#fff}
				.wpda_secondary{background:#fff;border:1px solid #ccc;color:#50575e}
				a.wpda_link{font-size:11px;color:#2271b1;text-decoration:none}
				#date_panel{display:none}
			</style>
		</head><body>
			<div class="wpda_card">
				<div class="wpda_tabs">
					<button type="button" class="wpda_tab active" onclick="switchMode('timer')">Select Timer</button>
					<button type="button" class="wpda_tab" onclick="switchMode('date')">Quick Date</button>
				</div>
				<div id="timer_panel">
					<div class="wpda_row">
						<label>Timer</label>
						<select id="select_timer"><?php $this->print_timers(); ?></select>
						<a class="wpda_link" target="_blank" href="<?php echo get_admin_url().'admin.php?page=wpda_countdown_menu&task=add_wpda_countdown_timer'; ?>">+ Add new</a>
					</div>
				</div>
				<div id="date_panel">
					<div class="wpda_row wpda_flex">
						<div><label>End Date</label><input type="date" id="end_date" value=""></div>
						<div><label>End Time</label><input type="time" id="end_time" value="23:59"></div>
					</div>
				</div>
				<div class="wpda_row">
					<label>Theme</label>
					<select id="select_theme"><?php $this->print_themes(); ?></select>
				</div>
			</div>
			<div class="wpda_actions">
				<button type="button" class="wpda_btn wpda_secondary" onclick="tinyMCEPopup.close()">Cancel</button>
				<button type="button" class="wpda_btn wpda_primary" onclick="insertShortcode()">Insert</button>
			</div>
			<script>
			var currentMode='timer';
			function switchMode(mode){
				currentMode=mode;
				document.getElementById('timer_panel').style.display=mode==='timer'?'block':'none';
				document.getElementById('date_panel').style.display=mode==='date'?'block':'none';
				document.querySelectorAll('.wpda_tab').forEach(function(t){t.classList.remove('active')});
				document.querySelectorAll('.wpda_tab')[mode==='timer'?0:1].classList.add('active');
			}
			function insertShortcode(){
				var tag='';
				if(currentMode==='timer'){
					var tid=document.getElementById('select_timer').value;
					var thid=document.getElementById('select_theme').value;
					tag='[wpda_countdown timer_id="'+tid+'" theme_id="'+thid+'"]';
				}else{
					var d=document.getElementById('end_date').value;
					var t=document.getElementById('end_time').value||'23:59';
					var thid=document.getElementById('select_theme').value;
					if(!d){alert('Please select an end date');return;}
					var parts=d.split('-');
					var endStr=parts[2]+'/'+parts[1]+'/'+parts[0]+' '+t;
					tag='[wpda_countdown end_date="'+endStr+'"';
					if(thid&&thid!=='0') tag+=' theme_id="'+thid+'"';
					tag+=']';
				}
				window.parent.tinyMCE.execCommand('mceInsertContent',false,'<p>'+tag+'</p>');
				tinyMCEPopup.close();
			}
			</script>
		</body></html>
		<?php die();
	}
	private function print_timers(){
		$timers = wpda_countdown()->timer_repository()->all_names();
		$timer_id = isset( $_GET['timer_id'] ) ? intval( $_GET['timer_id'] ) : 0;
		foreach ( $timers as $id => $name ) {
			?><option <?php selected( $timer_id, $id ); ?> value="<?php echo esc_attr( $id ); ?>"><?php echo esc_html( $name ); ?></option><?php
		}
	}
	private function print_themes(){
		$themes = wpda_countdown()->theme_repository()->all_names();
		$theme_id = isset( $_GET['theme_id'] ) ? intval( $_GET['theme_id'] ) : 0;
		echo '<option value="0">— Default —</option>';
		foreach ( $themes as $id => $name ) {
			?><option <?php selected( $theme_id, $id ); ?> value="<?php echo esc_attr( $id ); ?>"><?php echo esc_html( $name ); ?></option><?php
		}
	}
}
