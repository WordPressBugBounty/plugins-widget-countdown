<?php
if ( ! defined( 'ABSPATH' ) ) exit;
if (class_exists('wpdevart_countdown_forntend_main')) return;

class wpdevart_countdown_forntend_main{
	const SECONDS_PER_MINUTE = 60;
	const SECONDS_PER_HOUR = 3600;
	const SECONDS_PER_DAY = 86400;
	const SECONDS_PER_WEEK = 604800;
	const REPEAT_LOOKAHEAD = 1728000; // 20 days in seconds

	protected static $id_counter=0;
	protected $timer;
	protected $theme;
	protected $mk_time;
	protected $r_p_sec;
	
	// apply settings to 
	function __construct($timer,$theme){
		$this->timer=$timer;
		$this->theme=$theme;
		$this->caluclate_time();
		self::$id_counter++;
	}
	protected function caluclate_time(){		
		$curent_timezone=date_default_timezone_get();
		date_default_timezone_set($this->timer['timer_timezone']);
		$this->mk_time['now']=mktime ((int)date("H"),(int)date("i"),(int)date("s") ,(int)date("n"), (int)date("j"),(int)date("Y"));
		// get start of date		
		$exploded_date_time=explode(" ",$this->timer['timer_start_time']);
		$exploded_date=explode("/",$exploded_date_time[0]);
		$exploded_time=explode(":",$exploded_date_time[1]);
		$year=(int)$exploded_date[2];
		$month=(int)$exploded_date[1];
		$day=(int)$exploded_date[0];		
		$hour=(int)$exploded_time[0];
		$minute=(int)$exploded_time[1];		
		$this->mk_time['start_date']=mktime ($hour, $minute, 0, $month, $day, $year);
		if(!isset($this->timer['version']) && isset($this->timer['timer_coundown_type']) && $this->timer['timer_coundown_type']=="countdown"){
			$this->mk_time['start_date']=mktime ($hour, $minute, 0, $month, $day-1, $year);
		}
		
		
		// get end date
		$exploded_date_time=explode(" ",$this->timer['timer_end_date']);
		$exploded_date=explode("/",$exploded_date_time[0]);
		$exploded_time=explode(":",$exploded_date_time[1]);
		$year=(int)$exploded_date[2];
		$month=(int)$exploded_date[1];
		$day=(int)$exploded_date[0];		
		$hour=(int)$exploded_time[0];
		$minute=(int)$exploded_time[1];
		$this->mk_time['end_date']=mktime ($hour, $minute, 0, $month, $day, $year);
		
		
		//repeat end on date mktime
		$exploded_date_time=explode(" ",$this->timer['repeat_ending_after_date']);
		$exploded_date=explode("/",$exploded_date_time[0]);
		$exploded_time=explode(":",$exploded_date_time[1]);
		$year=(int)$exploded_date[2];
		$month=(int)$exploded_date[1];
		$day=(int)$exploded_date[0];		
		$hour=(int)$exploded_time[0];
		$minute=(int)$exploded_time[1];
		$this->mk_time['repeat_end_on_date']=mktime ($hour, $minute, 0, $month, $day, $year);	
		
		// get daily repeat first begin point mktime
		$exploded_date_time=explode(" ",$this->timer['timer_end_date']);
		$exploded_date=explode("/",$exploded_date_time[0]);
		$exploded_time=explode(":",$this->timer['repeat_countdown_start_time']);
		$year=(int)$exploded_date[2];
		$month=(int)$exploded_date[1];
		$day=(int)$exploded_date[0];		
		$hour=(int)$exploded_time[0];
		$minute=(int)$exploded_time[1];
		$this->mk_time['begin_of_daily_rep']=mktime ($hour, $minute, 0, $month, $day, $year);
		if($this->mk_time['begin_of_daily_rep']<$this->mk_time['end_date'])
			$this->mk_time['begin_of_daily_rep']+=86400;
		$this->correct_timer_to_personal();
		date_default_timezone_set($curent_timezone);		
	}
	protected function correct_timer_to_personal(){
		if($this->timer['timer_coundown_type']=="evergreen_countdown" || $this->timer['timer_coundown_type']=="evergreen_countup"){
			$this->timer['is_evergreen']=true;
			$this->timer['evergreen_duration']=$this->p_s_l();
			$this->timer['evergreen_restart']=isset($this->timer['evergreen_restart'])?$this->timer['evergreen_restart']:'none';
			$restart_delay_h=isset($this->timer['evergreen_restart_delay']['hour'])?intval($this->timer['evergreen_restart_delay']['hour']):24;
			$restart_delay_m=isset($this->timer['evergreen_restart_delay']['minute'])?intval($this->timer['evergreen_restart_delay']['minute']):0;
			$this->timer['evergreen_restart_delay_sec']=$restart_delay_h*3600+$restart_delay_m*60;
			$this->timer['evergreen_expire_mode']=isset($this->timer['evergreen_expire_mode'])?$this->timer['evergreen_expire_mode']:'duration';
			$this->timer['evergreen_daily_expire_time']=isset($this->timer['evergreen_daily_expire_time'])?$this->timer['evergreen_daily_expire_time']:'23:59';
			$this->mk_time['start_date']=$this->mk_time['now'];
			$this->mk_time['end_date']=$this->mk_time['start_date']+$this->timer['evergreen_duration'];
			$this->timer['timer_coundown_repeat']="none";
			$this->timer['repeat_end']="never";
		}
	}
	protected function is_ended(){
		if($this->mk_time['end_date'] < $this->mk_time['now'])
			return true;
		return false;
	}
	
	protected function is_started(){
		if($this->mk_time['start_date'] >= $this->mk_time['now'])
			return true;
		return false;
	}
	
	//s_l="seconds left" functions
	private function s_l_when_end(){
		$h=isset($this->timer['after_countdown_repeat_time']['hour'])?intval($this->timer['after_countdown_repeat_time']['hour']):0;
		$m=isset($this->timer['after_countdown_repeat_time']['minute'])?intval($this->timer['after_countdown_repeat_time']['minute']):0;
		$rep_time=$h*3600+$m*60;
		 if($rep_time>0)
			 return $rep_time;
		 return 300;
	}
	//	p_s_l= personal seconds left
	private function p_s_l(){
		$days=isset($this->timer['timer_seesion_time']['day'])?intval($this->timer['timer_seesion_time']['day']):0;
		$hours=isset($this->timer['timer_seesion_time']['hour'])?intval($this->timer['timer_seesion_time']['hour']):0;
		$minutes=isset($this->timer['timer_seesion_time']['minute'])?intval($this->timer['timer_seesion_time']['minute']):0;
		$total=$days*86400+$hours*3600+$minutes*60;
		return $total>0?$total:300;
	}
	private function parse_time_str($time_str){
		$parts=explode(":",$time_str);
		return (isset($parts[0])?intval($parts[0]):0)*3600+(isset($parts[1])?intval($parts[1]):0)*60;
	}
	private function s_l_daily(){
		$start_time=$this->parse_time_str($this->timer['repeat_countdown_start_time']);
		$end_time=$this->parse_time_str(isset($this->timer['repeat_countdown_end_time'])?$this->timer['repeat_countdown_end_time']:'23:59');
		return max($end_time-$start_time,10);
	}
	private function s_l_weekly(){
		$start_time=$this->parse_time_str($this->timer['repeat_countdown_start_time']);
		$end_time=$this->parse_time_str(isset($this->timer['repeat_countdown_end_time'])?$this->timer['repeat_countdown_end_time']:'23:59');
		return max($end_time-$start_time,10);
	}
	private function s_l_monthly(){
		$start_time=$this->parse_time_str($this->timer['repeat_countdown_start_time']);
		$end_time=$this->parse_time_str(isset($this->timer['repeat_countdown_end_time'])?$this->timer['repeat_countdown_end_time']:'23:59');
		return max($end_time-$start_time,10);
	}
	
	
	
	//g_r_b_p = get repeat begin points
	protected function g_r_b_p(){
		$g_r_b_p_array=[];
		$this->r_p_sec=array('beg'=>0,'mid'=>0,'end'=>0);
		switch($this->timer['timer_coundown_repeat']){
			case "when_end":
				$g_r_b_p_array=$this->g_r_b_p_when_end();
			break;
			case "hourly":
				$g_r_b_p_array=$this->g_r_b_p_interval('hourly');
			break;
			case "daily":
				$g_r_b_p_array=$this->g_r_b_p_daily();
			break;
			case "weekly":
				$g_r_b_p_array=$this->g_r_b_p_interval('weekly');
			break;
			case "monthly":
				$g_r_b_p_array=$this->g_r_b_p_interval('monthly');
			break;
		}
		return $g_r_b_p_array;
	}
	
	private function g_r_b_p_when_end(){
		$r_p=[];//repeat point
		switch($this->timer['repeat_end']){
			case "never":
				$r_s_l_when_end=(int)$this->s_l_when_end();//repeat second				
				if(!$this->is_ended()){
					// repeat seconds
					$this->r_p_sec['beg']=$r_s_l_when_end;
					$this->r_p_sec['mid']=$r_s_l_when_end;
					$this->r_p_sec['end']=$r_s_l_when_end;
					
					$repeat_time_interval=$this->mk_time['now']+1728000-$this->mk_time['end_date'];
					if($repeat_time_interval<=0)
						return $r_p;
					$repeat_count=$repeat_time_interval/$r_s_l_when_end;
					if($repeat_count>365) $repeat_count=365;
					for($i=0;$i<$repeat_count;$i++){
						$r_p[$i]=$this->mk_time['end_date']+$i*$r_s_l_when_end-$this->mk_time['now'];
					}
					return $r_p;
				}				
				$this->r_p_sec['mid']=$r_s_l_when_end;
				$this->r_p_sec['end']=$r_s_l_when_end;				
				$repeat_time_interval=1728000;
				$repeat_count=$repeat_time_interval/$r_s_l_when_end;
				$offset_from_start=$r_s_l_when_end-($this->mk_time['now']-$this->mk_time['end_date'])%$r_s_l_when_end;
				$this->r_p_sec['beg']=$offset_from_start;
				if($repeat_count>365) $repeat_count=365;
				$r_p[0]=0;
				for($i=1;$i<$repeat_count;$i++){
					$r_p[$i]=$this->mk_time['now']+($i-1)*$r_s_l_when_end+$offset_from_start-$this->mk_time['now'];
				}
				return $r_p;				
			break;	
			
			case "after":			
				$r_s_l_when_end=(int)$this->s_l_when_end();//repeat second	
					$this->r_p_sec['beg']=$r_s_l_when_end;
					$this->r_p_sec['mid']=$r_s_l_when_end;
					$this->r_p_sec['end']=$r_s_l_when_end;				
				if(!$this->is_ended()){
					$repeat_time_interval=$this->mk_time['now']+1728000-$this->mk_time['end_date'];
					
					if($repeat_time_interval<=0)
						return $r_p;
					$repeat_time_interval=min($this->timer['repeat_ending_after']*$r_s_l_when_end,$repeat_time_interval);
					$repeat_count=$repeat_time_interval/$r_s_l_when_end;
					if($repeat_count>365) $repeat_count=365;
					for($i=0;$i<$repeat_count;$i++){
						$r_p[$i]=$this->mk_time['end_date']+$i*$r_s_l_when_end-$this->mk_time['now'];
					}
					return $r_p;
				}				
				$this->r_p_sec['mid']=$r_s_l_when_end;
				$this->r_p_sec['end']=$r_s_l_when_end;
				$repeat_time_interval=min(1728000,$this->mk_time['end_date']-$this->mk_time['now']+$this->timer['repeat_ending_after']*$r_s_l_when_end);
				if($repeat_time_interval<=0)
						return $r_p;
				$repeat_count=$repeat_time_interval/$r_s_l_when_end;
				$offset_from_start=$r_s_l_when_end-($this->mk_time['now']-$this->mk_time['end_date'])%$r_s_l_when_end;
				$this->r_p_sec['beg']=$offset_from_start;
				if($repeat_count>365) $repeat_count=365;
				$r_p[0]=0;
				for($i=1;$i<$repeat_count;$i++){
					$r_p[$i]=$this->mk_time['now']+($i-1)*$r_s_l_when_end+$offset_from_start-$this->mk_time['now'];
				}
				return $r_p;
			
			break;
			case "on_date":
				$r_s_l_when_end=(int)$this->s_l_when_end();//repeat second			
				if(!$this->is_ended()){
					$this->r_p_sec['beg']=$r_s_l_when_end;
					$this->r_p_sec['mid']=$r_s_l_when_end;
					$this->r_p_sec['end']=$r_s_l_when_end;
					$repeat_time_interval=$this->mk_time['now']+1728000-$this->mk_time['end_date'];					
					$repeat_time_interval=min($this->mk_time['repeat_end_on_date']-$this->mk_time['now'],$repeat_time_interval);
					if($repeat_time_interval<=0)
						return $r_p;
					
					$repeat_count=(int)($repeat_time_interval/$r_s_l_when_end);
					if($repeat_count>365) $repeat_count=365;
					for($i=0;$i<$repeat_count;$i++){
						$r_p[$i]=$this->mk_time['end_date']+$i*$r_s_l_when_end-$this->mk_time['now'];
					}
					if(count($r_p)>0)
						$this->r_p_sec['end']=min($this->mk_time['repeat_end_on_date']-$r_p[count($r_p)-1],$r_s_l_when_end);
					return $r_p;
				}

				$this->r_p_sec['beg']=$r_s_l_when_end;
				$this->r_p_sec['mid']=$r_s_l_when_end;
				$this->r_p_sec['end']=$r_s_l_when_end;
				$repeat_time_interval=min(1728000,$this->mk_time['repeat_end_on_date']-$this->mk_time['now']);
				if($repeat_time_interval<=0)
						return $r_p;
				$repeat_count=(int)($repeat_time_interval/$r_s_l_when_end);
				$offset_from_start=$r_s_l_when_end-($this->mk_time['now']-$this->mk_time['end_date'])%$r_s_l_when_end;
				$this->r_p_sec['beg']=$offset_from_start;
				if($repeat_count>365) $repeat_count=365;
				$r_p[0]=0;
				for($i=1;$i<$repeat_count;$i++){
					$r_p[$i]=$this->mk_time['now']+($i-1)*$r_s_l_when_end+$offset_from_start-$this->mk_time['now'];
				}
				if(count($r_p)>0)
					$this->r_p_sec['end']=min($this->mk_time['repeat_end_on_date']-$r_p[count($r_p)-1],$r_s_l_when_end);
				return $r_p;
			break;
		}
	}
	
	
	private function g_r_b_p_daily(){
		$r_p=[];
		$this->timer['repeat_daily_quantity']=max(1,intval($this->timer['repeat_daily_quantity']));
		switch($this->timer['repeat_end']){
			case "never":
				$s_l_daily=(int)$this->s_l_daily();
				$this->r_p_sec['beg']=$s_l_daily;
				$this->r_p_sec['mid']=$s_l_daily;
				$this->r_p_sec['end']=$s_l_daily;
				$repeat_in_cur_day=1;		
				$all_count_rep_points=floor(($this->mk_time['now']+1728000-$this->mk_time['begin_of_daily_rep'])/(86400*$this->timer['repeat_daily_quantity']));
				if($all_count_rep_points<0)
					return $r_p;
				$cur_count_pos=floor(($this->mk_time['now']-$this->mk_time['begin_of_daily_rep'])/(86400*$this->timer['repeat_daily_quantity']));
				if($cur_count_pos>=0){						
					if(($this->mk_time['begin_of_daily_rep']+$cur_count_pos*(86400*$this->timer['repeat_daily_quantity'])+$s_l_daily)>=$this->mk_time['now']){						
						$r_p[0]=0;
						$this->r_p_sec['beg']= $s_l_daily-($this->mk_time['now']-$this->mk_time['begin_of_daily_rep']-$cur_count_pos*(86400*$this->timer['repeat_daily_quantity']));
						if($this->r_p_sec['beg']<0)
							$this->r_p_sec['beg']=$s_l_daily;
						$count=$all_count_rep_points-$cur_count_pos;
						for($i=1;$i<=$count;$i++){
							$r_p[$i]=$this->mk_time['begin_of_daily_rep']+($cur_count_pos+$i)*(86400*$this->timer['repeat_daily_quantity'])-$this->mk_time['now'];
						}
						$this->r_p_sec['end']=min(abs($s_l_daily-($this->mk_time['now']+1728000-($this->mk_time['begin_of_daily_rep']+$cur_count_pos*$count*(86400*$this->timer['repeat_daily_quantity'])))),$s_l_daily);
					}else{
						
						$r_p[0]=$this->mk_time['begin_of_daily_rep']+($cur_count_pos+1)*(86400*$this->timer['repeat_daily_quantity'])-$this->mk_time['now'];
						$count=$all_count_rep_points-$cur_count_pos;
						for($i=1;$i<$count;$i++){
							$r_p[$i]=$this->mk_time['begin_of_daily_rep']+($cur_count_pos+$i)*(86400*$this->timer['repeat_daily_quantity'])-$this->mk_time['now'];						}
						$this->r_p_sec['end']=min(abs($s_l_daily-($this->mk_time['now']+1728000-($this->mk_time['begin_of_daily_rep']+($cur_count_pos+$count)*(86400*$this->timer['repeat_daily_quantity'])))),$s_l_daily);
					}
				}else{
					for($i=0;$i<$all_count_rep_points;$i++){
						$r_p[$i]=$this->mk_time['begin_of_daily_rep']+$i*(86400*$this->timer['repeat_daily_quantity'])-$this->mk_time['now'];
					}
				}
				return $r_p;	
			break;	
			
			case "after":			
				$s_l_daily=(int)$this->s_l_daily();
				$this->r_p_sec['beg']=$s_l_daily;
				$this->r_p_sec['mid']=$s_l_daily;
				$this->r_p_sec['end']=$s_l_daily;
				$repeat_in_cur_day=1;		
				$all_count_rep_points=floor(($this->mk_time['now']+1728000-$this->mk_time['begin_of_daily_rep'])/(86400*$this->timer['repeat_daily_quantity']));
				if($all_count_rep_points<0)
					return $r_p;
				if($all_count_rep_points>$this->timer['repeat_ending_after']){
					$all_count_rep_points=$this->timer['repeat_ending_after'];
				}
				$cur_count_pos=floor(($this->mk_time['now']-$this->mk_time['begin_of_daily_rep'])/(86400*$this->timer['repeat_daily_quantity']));
				if($cur_count_pos>=$this->timer['repeat_ending_after'])
					return $r_p;
				if($cur_count_pos>=0){						
					if(($this->mk_time['begin_of_daily_rep']+$cur_count_pos*(86400*$this->timer['repeat_daily_quantity'])+$s_l_daily)>=$this->mk_time['now']){						
						$r_p[0]=0;
						$this->r_p_sec['beg']= $s_l_daily-($this->mk_time['now']-$this->mk_time['begin_of_daily_rep']-$cur_count_pos*(86400*intval($this->timer['repeat_daily_quantity'])));
						if($this->r_p_sec['beg']<0) $this->r_p_sec['beg']=$s_l_daily;
						$count=$all_count_rep_points-$cur_count_pos;
						for($i=1;$i<=$count;$i++){
							$r_p[$i]=$this->mk_time['begin_of_daily_rep']+($cur_count_pos+$i)*(86400*$this->timer['repeat_daily_quantity'])-$this->mk_time['now'];
						}
						$this->r_p_sec['end']=min(abs($s_l_daily-($this->mk_time['now']+1728000-($this->mk_time['begin_of_daily_rep']+$cur_count_pos*$count*(86400*$this->timer['repeat_daily_quantity'])))),$s_l_daily);
					}else{
						
						$r_p[0]=$this->mk_time['begin_of_daily_rep']+($cur_count_pos+1)*(86400*$this->timer['repeat_daily_quantity'])-$this->mk_time['now'];
						$count=$all_count_rep_points-$cur_count_pos;
						for($i=1;$i<$count;$i++){
							$r_p[$i]=$this->mk_time['begin_of_daily_rep']+($cur_count_pos+$i)*(86400*$this->timer['repeat_daily_quantity'])-$this->mk_time['now'];						}
						$this->r_p_sec['end']=min(abs($s_l_daily-($this->mk_time['now']+1728000-($this->mk_time['begin_of_daily_rep']+($cur_count_pos+$count)*(86400*$this->timer['repeat_daily_quantity'])))),$s_l_daily);
					}
				}else{
					for($i=0;$i<$all_count_rep_points;$i++){
						$r_p[$i]=$this->mk_time['begin_of_daily_rep']+$i*(86400*$this->timer['repeat_daily_quantity'])-$this->mk_time['now'];
					}
				}
				return $r_p;
			break;
			case "on_date":
				$s_l_daily=(int)$this->s_l_daily();
				$this->r_p_sec['beg']=$s_l_daily;
				$this->r_p_sec['mid']=$s_l_daily;
				$this->r_p_sec['end']=$s_l_daily;
				$repeat_in_cur_day=1;		
				$all_count_rep_points=floor(($this->mk_time['now']+1728000-$this->mk_time['begin_of_daily_rep'])/(86400*$this->timer['repeat_daily_quantity']));
				$all_count_rep_points=min($all_count_rep_points,1+floor(($this->mk_time['repeat_end_on_date']-$this->mk_time['begin_of_daily_rep'])/(86400*$this->timer['repeat_daily_quantity'])));
				if($all_count_rep_points<0)
					return $r_p;
				$cur_count_pos=floor(($this->mk_time['now']-$this->mk_time['begin_of_daily_rep'])/(86400*$this->timer['repeat_daily_quantity']));
				if(($this->mk_time['repeat_end_on_date']-$this->mk_time['begin_of_daily_rep'] - $cur_count_pos*(86400*$this->timer['repeat_daily_quantity']))<=0)
					return $r_p;
				if($cur_count_pos>=0){						
					if(($this->mk_time['begin_of_daily_rep']+$cur_count_pos*(86400*$this->timer['repeat_daily_quantity'])+$s_l_daily)>=$this->mk_time['now']){						
						$r_p[0]=0;
						$this->r_p_sec['beg']= $s_l_daily-($this->mk_time['now']-$this->mk_time['begin_of_daily_rep']-$cur_count_pos*(86400*$this->timer['repeat_daily_quantity']));
						if($this->r_p_sec['beg']<0) $this->r_p_sec['beg']=$s_l_daily;
						$this->r_p_sec['beg'] = min($this->r_p_sec['beg'],max(0,$this->mk_time['repeat_end_on_date']-$this->mk_time['now']));						
						$count=$all_count_rep_points-$cur_count_pos;
						for($i=1;$i<=$count;$i++){
							$r_p[$i]=$this->mk_time['begin_of_daily_rep']+($cur_count_pos+$i)*(86400*$this->timer['repeat_daily_quantity'])-$this->mk_time['now'];
						}
						$end_date=min($this->mk_time['now']+1728000,$this->mk_time['repeat_end_on_date']);
						$this->r_p_sec['end']=min(abs($s_l_daily-($end_date-($this->mk_time['begin_of_daily_rep']+$cur_count_pos*
						$count*(86400*$this->timer['repeat_daily_quantity'])))),$s_l_daily);
							
						
						
					}else{
						
						$r_p[0]=$this->mk_time['begin_of_daily_rep']+($cur_count_pos+1)*(86400*$this->timer['repeat_daily_quantity'])-$this->mk_time['now'];
						$count=$all_count_rep_points-$cur_count_pos;
						for($i=1;$i<$count;$i++){
							$r_p[$i]=$this->mk_time['begin_of_daily_rep']+($cur_count_pos+$i)*(86400*$this->timer['repeat_daily_quantity'])-$this->mk_time['now'];						}
						$this->r_p_sec['end']=min(abs($s_l_daily-($this->mk_time['now']+1728000-($this->mk_time['begin_of_daily_rep']+($cur_count_pos+$count)*(86400*$this->timer['repeat_daily_quantity'])))),$s_l_daily);
					}
				}else{
					for($i=0;$i<$all_count_rep_points;$i++){
						$r_p[$i]=$this->mk_time['begin_of_daily_rep']+$i*(86400*$this->timer['repeat_daily_quantity'])-$this->mk_time['now'];
					}
				}
				return $r_p;			
			break;
		}
	}
		
	
	
	private function g_r_b_p_interval($type){
		$r_p=[];
		$saved_tz=date_default_timezone_get();
		date_default_timezone_set($this->timer['timer_timezone']);
		$start_parts=explode(":",$this->timer['repeat_countdown_start_time']);
		$start_sec=(isset($start_parts[0])?intval($start_parts[0]):0)*3600+(isset($start_parts[1])?intval($start_parts[1]):0)*60;
		$end_parts=isset($this->timer['repeat_countdown_end_time'])?explode(":",$this->timer['repeat_countdown_end_time']):array(23,59);
		$end_sec=(isset($end_parts[0])?intval($end_parts[0]):23)*3600+(isset($end_parts[1])?intval($end_parts[1]):59)*60;
		$duration=max($end_sec-$start_sec,60);
		$now=$this->mk_time['now'];

		if($type==='hourly'){
			$gap=max(1,intval(isset($this->timer['repeat_hourly_interval'])?$this->timer['repeat_hourly_interval']:1))*3600;
			$duration=(int)$this->s_l_when_end();
			if($duration<=0) $duration=$gap;
			$cycle=$duration+$gap;
			$cycle_start=$now-($now%$cycle);
			$secs_into=$now-$cycle_start;
			if($secs_into<$duration){
				// inside active countdown window
				$this->r_p_sec['beg']=$duration-$secs_into;
				$r_p[0]=0;
				for($i=1;$i<20;$i++){
					$r_p[$i]=$cycle-$secs_into+($i-1)*$cycle;
				}
			}else{
				// in idle gap — next cycle starts after gap ends
				$next_start=$cycle-$secs_into;
				$r_p[0]=$next_start;
				for($i=1;$i<20;$i++){
					$r_p[$i]=$next_start+$i*$cycle;
				}
				$this->r_p_sec['beg']=$duration;
			}
			$this->r_p_sec['mid']=$duration;
			$this->r_p_sec['end']=$duration;
			date_default_timezone_set($saved_tz);
			return $this->apply_repeat_limit($r_p);
		}

		if($type==='weekly'){
			$day_map=array('mon'=>1,'tue'=>2,'wed'=>3,'thu'=>4,'fri'=>5,'sat'=>6,'sun'=>0);
			$setting=isset($this->timer['repeat_weekly_days'])?$this->timer['repeat_weekly_days']:array();
			if(is_string($setting)) $setting=array($setting);
			$allowed=array();
			if(empty($setting)){
				$allowed=array(0,1,2,3,4,5,6);
			}else{
				foreach($setting as $day_key){
					if(isset($day_map[$day_key])) $allowed[]=$day_map[$day_key];
				}
			}
			if(empty($allowed)) $allowed=array(0,1,2,3,4,5,6);
			$this->r_p_sec['beg']=$duration;
			$this->r_p_sec['mid']=$duration;
			$this->r_p_sec['end']=$duration;
			$today_dow=intval(date('w'));
			$today_start=mktime(0,0,0,date('n'),date('j'),date('Y'));
			for($d=0;$d<60;$d++){
				$check_day=($today_dow+$d)%7;
				if(!in_array($check_day,$allowed)) continue;
				$day_begin=$today_start+$d*86400+$start_sec;
				$day_end=$day_begin+$duration;
				if($day_end<$now) continue;
				$offset=$day_begin-$now;
				if($offset<0){
					$this->r_p_sec['beg']=max(0,$day_end-$now);
					$r_p[]=0;
				}else{
					$r_p[]=$offset;
				}
				if(count($r_p)>=30) break;
			}
			date_default_timezone_set($saved_tz);
			return $this->apply_repeat_limit($r_p);
		}

		if($type==='monthly'){
			$dom=max(1,min(28,intval(isset($this->timer['repeat_monthly_day'])?$this->timer['repeat_monthly_day']:1)));
			$this->r_p_sec['beg']=$duration;
			$this->r_p_sec['mid']=$duration;
			$this->r_p_sec['end']=$duration;
			$cur_month=intval(date('n'));
			$cur_year=intval(date('Y'));
			for($m=0;$m<12;$m++){
				$month=$cur_month+$m;
				$year=$cur_year+intval(($month-1)/12);
				$month=(($month-1)%12)+1;
				$day_begin=mktime(isset($start_parts[0])?intval($start_parts[0]):0,isset($start_parts[1])?intval($start_parts[1]):0,0,$month,$dom,$year);
				$day_end=$day_begin+$duration;
				if($day_end<$now) continue;
				$offset=$day_begin-$now;
				if($offset<0){
					$this->r_p_sec['beg']=max(0,$day_end-$now);
					$r_p[]=0;
				}else{
					$r_p[]=$offset;
				}
				if(count($r_p)>=12) break;
			}
			date_default_timezone_set($saved_tz);
			return $this->apply_repeat_limit($r_p);
		}
		date_default_timezone_set($saved_tz);
		return $r_p;
	}

	private function apply_repeat_limit($r_p){
		$end_type=isset($this->timer['repeat_end'])?$this->timer['repeat_end']:'never';
		if($end_type==='after'){
			$limit=max(1,intval(isset($this->timer['repeat_ending_after'])?$this->timer['repeat_ending_after']:1));
			$r_p=array_slice($r_p,0,$limit);
		}elseif($end_type==='on_date'){
			$end_ts=$this->mk_time['repeat_end_on_date'];
			$now=$this->mk_time['now'];
			foreach($r_p as $i=>$offset){
				if($now+$offset>$end_ts){
					$r_p=array_slice($r_p,0,$i);
					break;
				}
			}
		}
		return $r_p;
	}

	protected function build_common_params(){
		$this->timer['time_is_expired']="0";
		$p=array();
		$p["seconds_left"]=max(0,$this->mk_time['end_date']-$this->mk_time['now']);
		$p["end_timestamp"]=$this->mk_time['end_date'];
		$p["start_timestamp"]=$this->mk_time['start_date'];
		$p["server_time"]=$this->mk_time['now'];
		$p["repeat_points"]=$this->g_r_b_p();
		$p["repeat_seconds_start"]=$this->r_p_sec['beg'];
		$p["repeat_seconds_mid"]=$this->r_p_sec['mid'];
		$p["repeat_seconds_end"]=$this->r_p_sec['end'];
		$p["timer_start_time"]=$this->mk_time['start_date']-$this->mk_time['now'];
		$p["time_is_expired"]=$this->timer["time_is_expired"];
		$p["after_countdown_end_type"]=$this->timer["after_countdown_end_type"];
		$p["after_countdown_text"]=$this->timer["after_countdown_text"];
		$p["before_countup_start_type"]=$this->timer["before_countup_start_type"];
		$p["before_countup_text"]=$this->timer["before_countup_text"];
		$p["before_countup_redirect"]=isset($this->timer["before_countup_redirect"])?$this->timer["before_countup_redirect"]:"";
		$p["coundown_type"]=$this->timer["timer_coundown_type"];
		$p["is_evergreen"]=!empty($this->timer["is_evergreen"]);
		$p["evergreen_duration"]=isset($this->timer["evergreen_duration"])?$this->timer["evergreen_duration"]:0;
		$p["evergreen_restart"]=isset($this->timer["evergreen_restart"])?$this->timer["evergreen_restart"]:"none";
		$p["evergreen_restart_delay"]=isset($this->timer["evergreen_restart_delay_sec"])?$this->timer["evergreen_restart_delay_sec"]:0;
		$p["evergreen_expire_mode"]=isset($this->timer["evergreen_expire_mode"])?$this->timer["evergreen_expire_mode"]:"duration";
		$p["evergreen_daily_expire_time"]=isset($this->timer["evergreen_daily_expire_time"])?$this->timer["evergreen_daily_expire_time"]:"23:59";
		$p["after_countdown_button_text"]=isset($this->timer["after_countdown_button_text"])?$this->timer["after_countdown_button_text"]:"Shop Now";
		$p["after_countdown_button_url"]=isset($this->timer["after_countdown_button_url"])?$this->timer["after_countdown_button_url"]:"";
		$p["after_countdown_css_selector"]=isset($this->timer["after_countdown_css_selector"])?$this->timer["after_countdown_css_selector"]:"";
		$p["timer_id"]=$this->timer["timer_id"];
		$p["after_countdown_redirect"]=$this->timer["after_countdown_redirect"];
		$display_days=$this->theme["countdown_date_display"];
		if(is_array($display_days)){
			$normalized=array();
			foreach($display_days as $k=>$v){
				$normalized[$v]=$v;
			}
			$display_days=$normalized;
		}
		$p["display_days"]=$display_days;
		$p["top_html_text"]=isset($this->timer["top_countdown_show_html"])?$this->timer["top_countdown_show_html"]:'';
		$p["bottom_html_text"]=isset($this->timer["bottom_countdown_show_html"])?$this->timer["bottom_countdown_show_html"]:'';
		$p["display_days_texts"]=$this->get_texts();
		return $p;
	}

	protected function get_texts(){
		if($this->theme["countdown_text_type"]=="standart"){
			return array(
				"week"=>$this->theme["text_for_weeks"],
				"day"=>$this->theme["text_for_day"],
				"hour"=>$this->theme["text_for_hour"],
				"minut"=>$this->theme["text_for_minute"],
				"second"=>$this->theme["text_for_second"],
			);
		}else{
			return array(
				"week"=>__("Weeks","wpdevart_countdown_n"),
				"day"=>__("Days","wpdevart_countdown_n"),
				"hour"=>__("Hours","wpdevart_countdown_n"),
				"minut"=>__("Minutes","wpdevart_countdown_n"),
				"second"=>__("Seconds","wpdevart_countdown_n"),
			);
		}
	}
}

// standart_countdown class
class wpdevart_countdown_forntend_stanadart_view extends wpdevart_countdown_forntend_main{

	public function create_countdown(){
		$params_array=$this->build_common_params();
		$params_array["gorup_animation"]=$this->theme["countdown_standart_gorup_animation"];
		$params_array["inline"]=$this->theme["countdown_standart_display_inline"];
		$params_array["effect"]=($this->theme["countdown_standart_animation_type"]=="random")?Wpda_Countdown_Admin_Fields::random_animation():$this->theme["countdown_standart_animation_type"];
		$params_converted_to_js_objec=wp_json_encode($params_array);
		$countdown_html='<div class="wpdevart_countdown_standart" id="wpdevart_countdown_'.self::$id_counter.'"></div>';
		$countdown_script='<script>document.addEventListener("DOMContentLoaded",function(){wpdevart_countdown_standart("wpdevart_countdown_'.self::$id_counter.'",'.$params_converted_to_js_objec.')})</script>';
		$countdown_style='<style>'.$this->get_css('wpdevart_countdown_'.self::$id_counter).'</style>';
		return $countdown_html.$countdown_script.$countdown_style;
	}	
	
	private function get_css($main_id){
		$main_id="#".$main_id;
		$css="";
		// NOTE on !important: applied only to properties that themes commonly
		// override AND the fit-to-row JS never touches (color, background,
		// font-family, line-height, text-align, border-color/radius/style).
		// Layout props (width, min-width, font-size, padding, margin, border-width)
		// are left without !important so the scaler (element.style.width = ...) can win.
		$css.=$main_id."{max-width:100%;width:".$this->theme["countdown_global_width"].$this->theme["countdown_global_width_metrick"].";text-align:".$this->theme["countdown_horizontal_position"]." !important;}";
		$css.=$main_id." .wpdevart_countdown_element{min-width:".$this->theme["countdown_standart_elements_width"]."px;text-align:center !important;margin-right:".$this->theme["countdown_standart_elements_distance"]."px;}";
		$css.=$main_id." .time_left_pro{";
		$css.="background-color:".$this->theme["countdown_standart_time_bg_color"]." !important;";
		$css.="font-size:".$this->theme["countdown_standart_time_font_size"]."px;";
		$css.="line-height:normal !important;";
		$css.="color:".$this->theme["countdown_standart_time_color"]." !important;";
		$css.="font-family:".$this->theme["countdown_standart_time_font_famely"]." !important;";
		$css.="padding:".$this->theme["countdown_standart_time_padding"]["top"]."px ".$this->theme["countdown_standart_time_padding"]["right"]."px ".$this->theme["countdown_standart_time_padding"]["bottom"]."px ".$this->theme["countdown_standart_time_padding"]["left"]."px;";
		$css.="margin:".$this->theme["countdown_standart_time_margin"]["top"]."px ".$this->theme["countdown_standart_time_margin"]["right"]."px ".$this->theme["countdown_standart_time_margin"]["bottom"]."px ".$this->theme["countdown_standart_time_margin"]["left"]."px;";
		$css.="border-width:".$this->theme["countdown_standart_time_border_width"]."px;";
		$css.="border-radius:".$this->theme["countdown_standart_time_border_radius"]."px !important;";
		$css.="border-color:".$this->theme["countdown_standart_time_border_color"]." !important;";
		$css.="}";
		$css.=$main_id." .time_text{";
		$css.="background-color:".$this->theme["countdown_standart_time_text_bg_color"]." !important;";
		$css.="font-size:".$this->theme["countdown_standart_time_text_font_size"]."px;";
		$css.="line-height:normal !important;";
		$css.="color:".$this->theme["countdown_standart_time_text_color"]." !important;";
		$css.="font-family:".$this->theme["countdown_standart_time_text_font_famely"]." !important;";
		$css.="padding:".$this->theme["countdown_standart_time_text_padding"]["top"]."px ".$this->theme["countdown_standart_time_text_padding"]["right"]."px ".$this->theme["countdown_standart_time_text_padding"]["bottom"]."px ".$this->theme["countdown_standart_time_text_padding"]["left"]."px;";
		$css.="margin:".$this->theme["countdown_standart_time_text_margin"]["top"]."px ".$this->theme["countdown_standart_time_text_margin"]["right"]."px ".$this->theme["countdown_standart_time_text_margin"]["bottom"]."px ".$this->theme["countdown_standart_time_text_margin"]["left"]."px;";
		$css.="border-width:".$this->theme["countdown_standart_time_text_border_width"]."px;";
		$css.="border-radius:".$this->theme["countdown_standart_time_text_border_radius"]."px !important;";
		$css.="border-color:".$this->theme["countdown_standart_time_text_border_color"]." !important;";
		$css.="}";
		if($this->theme["countdown_standart_animation_type"]!="none"){
			if($this->theme["countdown_standart_gorup_animation"]=="group"){
				$css.=$main_id."{";
				$css.="visibility: hidden;";
				$css.="}";
			}else{
				$css.=$main_id." .wpdevart_countdown_element,".$main_id." .wpdevart_top_html,".$main_id." .wpdevart_bottom_html{";
				$css.="visibility: hidden;";
				$css.="}";

			}
		}
		
		return $css;
	}
}

// vertical_countdown class
class wpdevart_countdown_forntend_vertical_view extends wpdevart_countdown_forntend_main{
	// generete html
	public function create_countdown(){
		$params_array=$this->build_common_params();
		$params_array["gorup_animation"]=$this->theme["countdown_vertical_gorup_animation"];
		$params_array["inline"]=$this->theme["countdown_vertical_display_inline"];
		$params_array["effect"]=($this->theme["countdown_vertical_animation_type"]=="random")?Wpda_Countdown_Admin_Fields::random_animation():$this->theme["countdown_vertical_animation_type"];
		$params_converted_to_js_objec=wp_json_encode($params_array);
		
		$countdown_html='<div class="wpdevart_countdown_vertical" id="wpdevart_countdown_'.self::$id_counter.'"></div>';
		$countdown_script='<script>document.addEventListener("DOMContentLoaded",function(){setTimeout(function(){wpdevart_countdown_vertical("wpdevart_countdown_'.self::$id_counter.'",'.$params_converted_to_js_objec.')},100)})</script>';
		$countdown_style='<style>'.$this->get_css('wpdevart_countdown_'.self::$id_counter).'</style>';
		return $countdown_html.$countdown_script.$countdown_style;
	}	
	// generete css
	public function get_css($main_id){
		$main_id="#".$main_id;
		$css="";
		// !important only on visual props (color/background/font-family/line-height/border-color/radius).
		// Layout props (width/font-size/padding/margin/border-width/margin-right) are left without
		// !important so the fit-to-row JS scaler can override via element.style.
		$css.=$main_id." .wpdevart_countdown_element{margin-right:".$this->theme["countdown_vertical_elements_distance"]."px;text-align:center !important;}";
		$css.=$main_id."{max-width:100%;width:".$this->theme["countdown_global_width"].$this->theme["countdown_global_width_metrick"].";text-align:".$this->theme["countdown_horizontal_position"]." !important;}";
		$bg_vert  = $this->theme["countdown_vertical_background_color"];
		$bg_vert2 = Wpda_Countdown_Admin_Fields::darken_color($bg_vert,"20");
		$css.=$main_id." .time_left_pro > span{";
		$css.="background: ".$bg_vert." !important;";
		$css.="background: -moz-linear-gradient(top, ".$bg_vert." 38%, ".$bg_vert2." 100%) !important;";
		$css.="background: -webkit-gradient(linear, left top, left bottom, color-stop(38%,".$bg_vert."), color-stop(100%,".$bg_vert2.")) !important;";
		$css.="background: -webkit-linear-gradient(top, ".$bg_vert." 38%,".$bg_vert2." 100%) !important;";
		$css.="background: -o-linear-gradient(top, ".$bg_vert." 38%,".$bg_vert2." 100%) !important;";
		$css.="background: -ms-linear-gradient(top, ".$bg_vert." 38%,".$bg_vert2." 100%) !important;";
		$css.="background: linear-gradient(to bottom, ".$bg_vert." 38%,".$bg_vert2." 100%) !important;";
		$css.="border-width:".$this->theme["countdown_vertical_time_border_width"]."px;";
		$css.="border-color:".$this->theme["countdown_vertical_time_border_color"]." !important;";
		$css.="}";
		$css.=$main_id." .time_left_pro li{";
		$css.="font-size:".$this->theme["countdown_vertical_time_font_size"]."px;";
		$css.="line-height:normal !important;";
		$css.="color:".$this->theme["countdown_vertical_time_color"]." !important;";
		$css.="font-family:".$this->theme["countdown_vertical_time_font_famely"]." !important;";
		$css.="}";
		$css.=$main_id." .time_text{";
		$css.="background-color:".$this->theme["countdown_vertical_time_text_bg_color"]." !important;";
		$css.="font-size:".$this->theme["countdown_vertical_time_text_font_size"]."px;";
		$css.="line-height:normal !important;";
		$css.="color:".$this->theme["countdown_vertical_time_text_color"]." !important;";
		$css.="font-family:".$this->theme["countdown_vertical_time_text_font_famely"]." !important;";
		$css.="padding:".$this->theme["countdown_vertical_time_text_padding"]["top"]."px ".$this->theme["countdown_vertical_time_text_padding"]["right"]."px ".$this->theme["countdown_vertical_time_text_padding"]["bottom"]."px ".$this->theme["countdown_vertical_time_text_padding"]["left"]."px;";
		$css.="margin:".$this->theme["countdown_vertical_time_text_margin"]["top"]."px ".$this->theme["countdown_vertical_time_text_margin"]["right"]."px ".$this->theme["countdown_vertical_time_text_margin"]["bottom"]."px ".$this->theme["countdown_vertical_time_text_margin"]["left"]."px;";
		$css.="border-width:".$this->theme["countdown_vertical_time_text_border_width"]."px;";
		$css.="border-radius:".$this->theme["countdown_vertical_time_text_border_radius"]."px !important;";
		$css.="border-color:".$this->theme["countdown_vertical_time_text_border_color"]." !important;";
		$css.="}";
		if($this->theme["countdown_vertical_animation_type"]!="none"){
			if($this->theme["countdown_vertical_gorup_animation"]=="group"){
				$css.=$main_id."{";
				$css.="visibility: hidden;";
				$css.="}";
			}else{
				$css.=$main_id." .wpdevart_countdown_element,".$main_id." .wpdevart_top_html,".$main_id." .wpdevart_bottom_html{";
				$css.="visibility: hidden;";
				$css.="}";

			}
		}
		return $css;
		
	}	
}
// circle_countdown class
class wpdevart_countdown_forntend_circle_view extends wpdevart_countdown_forntend_main{
	// generete html
	public function create_countdown(){
		$params_array=$this->build_common_params();
		$params_array["bg_color"]=$this->theme["countdown_circle_border_color_outside"];
		$params_array["fg_color"]=$this->theme["countdown_circle_border_color_inside"];
		$params_array["thickness"]=$this->theme["countdown_circle_width_parcents"]/100;
		$params_array["linecap"]=$this->theme["countdown_circle_type_of_rounding"];
		$params_array["direction"]=$this->theme["countdown_circle_border_direction"];
		$params_array["gorup_animation"]=$this->theme["countdown_circle_gorup_animation"];
		$params_array["inline"]=isset($this->theme["countdown_circle_display_inline"])?$this->theme["countdown_circle_display_inline"]:"0";
		$params_array["effect"]=($this->theme["countdown_circle_animation_type"]=="random")?Wpda_Countdown_Admin_Fields::random_animation():$this->theme["countdown_circle_animation_type"];
		$params_converted_to_js_objec=wp_json_encode($params_array);
		$countdown_html='<div class="wpdevart_countdown_circle" id="wpdevart_countdown_'.self::$id_counter.'"></div>';
		$countdown_script='<script>document.addEventListener("DOMContentLoaded",function(){wpdevart_countdown_circle("wpdevart_countdown_'.self::$id_counter.'",'.$params_converted_to_js_objec.')})</script>';
		$countdown_style='<style>'.$this->get_css('wpdevart_countdown_'.self::$id_counter).'</style>';
		return $countdown_html.$countdown_script.$countdown_style;
	}	
	public function get_css($main_id){
		$id="#".$main_id;
		$t=$this->theme;
		$size=intval($t["countdown_circle_elements_width_height"]);
		$gap=intval($t["countdown_circle_elements_distance"]);
		$bgColor=Wpda_Countdown_Admin_Fields::hex2rgba($t["countdown_circle_background_color"],intval($t["countdown_circle_background_color_opacity"])/100);
		$numFs=intval($t["countdown_circle_time_font_size"]);
		$lblFs=intval($t["countdown_circle_time_text_font_size"]);
		$numColor=$t["countdown_circle_time_color"];
		$lblColor=$t["countdown_circle_time_text_color"];
		$ff=isset($t["countdown_circle_time_font_famely"])?$t["countdown_circle_time_font_famely"]:'Arial,sans-serif';
		$css="";
		// Circle view scaler also writes to element.style for width/height/font-size, so leave
		// those without !important. Visual props (color/background/font-family/line-height) keep it.
		$css.=$id."{max-width:100%;width:".$t["countdown_global_width"].$t["countdown_global_width_metrick"].";text-align:".$t["countdown_horizontal_position"]." !important;}";
		$css.=$id." .wpdevart_countdown_element{width:".$size."px;height:".$size."px;margin-right:".$gap."px;}";
		$css.=$id." .wpdevart_countdown_element .wpdevart_countdown_background{background-color:".$bgColor." !important;}";
		$css.=$id." .time_left_pro{font-size:".$numFs."px;line-height:1 !important;color:".$numColor." !important;font-family:".$ff." !important;font-weight:700 !important;}";
		$css.=$id." .time_text{font-size:".$lblFs."px;line-height:normal !important;color:".$lblColor." !important;font-family:".$ff." !important;}";
		if($t["countdown_circle_animation_type"]!="none"){
			if($t["countdown_circle_gorup_animation"]=="group"){
				$css.=$id."{visibility:hidden;}";
			}else{
				$css.=$id." .wpdevart_countdown_element,".$id." .wpdevart_top_html,".$id." .wpdevart_bottom_html{visibility:hidden;}";
			}
		}
		return $css;
		
	}	
}




// flip clock view
class wpdevart_countdown_forntend_flip_view extends wpdevart_countdown_forntend_main{
	public function create_countdown(){
		$params_array=$this->build_common_params();
		$params_array["gorup_animation"]=isset($this->theme["countdown_flip_gorup_animation"])?$this->theme["countdown_flip_gorup_animation"]:"group";
		$params_array["inline"]=isset($this->theme["countdown_flip_display_inline"])?$this->theme["countdown_flip_display_inline"]:"0";
		$params_array["effect"]=isset($this->theme["countdown_flip_animation_type"])?(($this->theme["countdown_flip_animation_type"]=="random")?Wpda_Countdown_Admin_Fields::random_animation():$this->theme["countdown_flip_animation_type"]):"none";
		$params_converted_to_js_objec=wp_json_encode($params_array);
		$id='wpdevart_countdown_'.self::$id_counter;
		$countdown_html='<div class="wpdevart_countdown_flip" id="'.$id.'"></div>';
		$countdown_script='<script>document.addEventListener("DOMContentLoaded",function(){wpdevart_countdown_flip("'.$id.'",'.$params_converted_to_js_objec.')})</script>';
		$countdown_style='<style>'.$this->get_css($id).'</style>';
		return $countdown_html.$countdown_script.$countdown_style;
	}
	private function get_css($main_id){
		$id="#".$main_id;
		$t=$this->theme;
		$bg=isset($t['countdown_flip_card_bg'])?$t['countdown_flip_card_bg']:'#1d2327';
		$bg2=self::darken($bg,8);
		$color=isset($t['countdown_flip_card_color'])?$t['countdown_flip_card_color']:'#ffffff';
		$label=isset($t['countdown_flip_label_color'])?$t['countdown_flip_label_color']:'#50575e';
		$w=isset($t['countdown_flip_card_width'])?intval($t['countdown_flip_card_width']):70;
		$h=isset($t['countdown_flip_card_height'])?intval($t['countdown_flip_card_height']):80;
		$fs=isset($t['countdown_flip_font_size'])?intval($t['countdown_flip_font_size']):36;
		$gap=isset($t['countdown_flip_gap'])?intval($t['countdown_flip_gap']):12;
		$br=isset($t['countdown_flip_border_radius'])?intval($t['countdown_flip_border_radius']):8;
		$ff=isset($t['countdown_flip_font_family'])?$t['countdown_flip_font_family']:'Arial,sans-serif';
		// Using CSS custom properties on the container makes the fit-to-row JS
		// scale work by setting vars — b::before line-height follows card height automatically.
		$css="";
		$css.=$id."{max-width:100%;width:".$t["countdown_global_width"].$t["countdown_global_width_metrick"]." !important;text-align:".$t["countdown_horizontal_position"]." !important;";
		$css.="--wpda-fc-w:".$w."px;--wpda-fc-h:".$h."px;--wpda-fc-fs:".$fs."px;--wpda-fc-gap:".$gap."px;}";
		$css.=$id." .wpda_flip_wrap{gap:var(--wpda-fc-gap,".$gap."px) !important;}";
		$css.=$id." .wpda_fc{width:var(--wpda-fc-w,".$w."px) !important;height:var(--wpda-fc-h,".$h."px) !important;font-size:var(--wpda-fc-fs,".$fs."px) !important;color:".$color." !important;font-family:".$ff." !important;}";
		$css.=$id." .wpda_fc b.t,".$id." .wpda_fc b.ft{background:".$bg." !important;border-radius:".$br."px ".$br."px 0 0 !important;}";
		$css.=$id." .wpda_fc b.b,".$id." .wpda_fc b.fb{background:".$bg2." !important;border-radius:0 0 ".$br."px ".$br."px !important;}";
		$css.=$id." .wpda_fc b::before{line-height:var(--wpda-fc-h,".$h."px) !important;}";
		$css.=$id." .wpda_flip_label{color:".$label." !important;}";
		$anim=isset($t["countdown_flip_animation_type"])?$t["countdown_flip_animation_type"]:"none";
		if($anim!=="none"){
			$group=isset($t["countdown_flip_gorup_animation"])?$t["countdown_flip_gorup_animation"]:"group";
			if($group==="group"){
				$css.=$id."{visibility:hidden;}";
			}else{
				$css.=$id." .wpda_flip_unit,".$id." .wpdevart_top_html,".$id." .wpdevart_bottom_html{visibility:hidden;}";
			}
		}
		return $css;
	}
	private static function darken($hex,$percent){
		$hex=ltrim($hex,'#');
		if(strlen($hex)==3) $hex=$hex[0].$hex[0].$hex[1].$hex[1].$hex[2].$hex[2];
		$r=max(0,hexdec(substr($hex,0,2))-round(255*$percent/100));
		$g=max(0,hexdec(substr($hex,2,2))-round(255*$percent/100));
		$b=max(0,hexdec(substr($hex,4,2))-round(255*$percent/100));
		return '#'.str_pad(dechex($r),2,'0',STR_PAD_LEFT).str_pad(dechex($g),2,'0',STR_PAD_LEFT).str_pad(dechex($b),2,'0',STR_PAD_LEFT);
	}
}
