(function(){
var canvas,ctx,wrap;
var viewStart=0,viewEnd=0,totalStart=0,totalEnd=0;
var isDragging=false,dragStartX=0,dragViewStart=0;
var segments=[];
var nowLocalLine=0,nowTzLine=0,tzName='';
var DPR=window.devicePixelRatio||1;
var lastSegHash='';
var MAX_EVENTS=100;
var MAX_RANGE_MS=365.25*86400000;
var CANVAS_H=140;

var tzOffsets={
	'US/Hawaii':-10,'US/Alaska':-9,'US/Pacific':-8,'America/Tijuana':-8,'US/Arizona':-7,'US/Mountain':-7,
	'America/Chihuahua':-7,'America/Mazatlan':-7,'America/Mexico_City':-6,'America/Monterrey':-6,
	'Canada/Saskatchewan':-6,'US/Central':-6,'US/Eastern':-5,'US/East-Indiana':-5,'America/Bogota':-5,
	'America/Lima':-5,'America/Caracas':-4.5,'Canada/Atlantic':-4,'America/La_Paz':-4,'America/Santiago':-4,
	'Canada/Newfoundland':-3.5,'America/Buenos_Aires':-3,'Greenland':-3,'Atlantic/Stanley':-2,
	'Atlantic/Azores':-1,'Atlantic/Cape_Verde':-1,'Africa/Casablanca':0,'Europe/Dublin':0,'Europe/Lisbon':0,
	'Europe/London':0,'Africa/Monrovia':0,'Pacific/Midway':-11,'US/Samoa':-11,
	'Europe/Amsterdam':1,'Europe/Belgrade':1,'Europe/Berlin':1,'Europe/Bratislava':1,'Europe/Brussels':1,
	'Europe/Budapest':1,'Europe/Copenhagen':1,'Europe/Ljubljana':1,'Europe/Madrid':1,'Europe/Paris':1,
	'Europe/Prague':1,'Europe/Rome':1,'Europe/Sarajevo':1,'Europe/Skopje':1,'Europe/Stockholm':1,
	'Europe/Vienna':1,'Europe/Warsaw':1,'Europe/Zagreb':1,
	'Europe/Athens':2,'Europe/Bucharest':2,'Africa/Cairo':2,'Africa/Harare':2,'Europe/Helsinki':2,
	'Europe/Istanbul':2,'Asia/Jerusalem':2,'Europe/Kiev':2,'Europe/Minsk':2,'Europe/Riga':2,
	'Europe/Sofia':2,'Europe/Tallinn':2,'Europe/Vilnius':2,
	'Asia/Baghdad':3,'Asia/Kuwait':3,'Africa/Nairobi':3,'Asia/Riyadh':3,'Europe/Moscow':3,
	'Asia/Tehran':3.5,'Asia/Baku':4,'Europe/Volgograd':4,'Asia/Muscat':4,'Asia/Tbilisi':4,'Asia/Yerevan':4,
	'Asia/Kabul':4.5,'Asia/Karachi':5,'Asia/Tashkent':5,'Asia/Kolkata':5.5,'Asia/Kathmandu':5.75,
	'Asia/Yekaterinburg':6,'Asia/Almaty':6,'Asia/Dhaka':6,'Asia/Novosibirsk':7,'Asia/Bangkok':7,'Asia/Jakarta':7,
	'Asia/Krasnoyarsk':8,'Asia/Chongqing':8,'Asia/Hong_Kong':8,'Asia/Kuala_Lumpur':8,'Australia/Perth':8,
	'Asia/Singapore':8,'Asia/Taipei':8,'Asia/Ulaanbaatar':8,'Asia/Urumqi':8,
	'Asia/Irkutsk':9,'Asia/Seoul':9,'Asia/Tokyo':9,'Australia/Adelaide':9.5,'Australia/Darwin':9.5,
	'Asia/Yakutsk':10,'Australia/Brisbane':10,'Australia/Canberra':10,'Pacific/Guam':10,
	'Australia/Hobart':10,'Australia/Melbourne':10,'Pacific/Port_Moresby':10,'Australia/Sydney':10,
	'Asia/Vladivostok':11,'Asia/Magadan':12,'Pacific/Auckland':12,'Pacific/Fiji':12
};

function getSelectedTzOffset(){
	var tz=getSelectVal('timer_timezone');
	tzName=tz;
	if(tzOffsets[tz]!==undefined) return tzOffsets[tz]*3600000;
	return 0;
}

function getLocalOffset(){
	return new Date().getTimezoneOffset()*-60000;
}

function localToTz(localTs){
	var diff=getSelectedTzOffset()-getLocalOffset();
	return localTs+diff;
}

function init(){
	canvas=document.getElementById('wpda_timeline_canvas');
	wrap=document.getElementById('wpda_timeline_wrap');
	if(!canvas||!wrap) return;
	ctx=canvas.getContext('2d');
	resize();
	bindEvents();
	recalc();
	draw();
	setInterval(function(){ recalc(); draw(); },3000);
}

function resize(){
	var w=wrap.clientWidth-2;
	canvas.width=w*DPR;
	canvas.height=CANVAS_H*DPR;
	canvas.style.width=w+'px';
	canvas.style.height=CANVAS_H+'px';
	ctx.setTransform(DPR,0,0,DPR,0,0);
}

function parseDate(str){
	if(!str||typeof str!=='string') return null;
	str=str.trim();
	var parts=str.split(' ');
	if(parts.length<2) return null;
	var d=parts[0].split('/');
	var t=parts[1].split(':');
	if(d.length<3) return null;
	var day=parseInt(d[0]),month=parseInt(d[1])-1,year=parseInt(d[2]);
	var hour=parseInt(t[0])||0,min=parseInt(t[1])||0;
	if(isNaN(day)||isNaN(month)||isNaN(year)) return null;
	return new Date(year,month,day,hour,min).getTime();
}

function getVal(name){
	var el=document.querySelector('[name="'+name+'"]')||document.getElementById(name);
	return el?el.value:'';
}
function getSelectVal(name){
	var el=document.getElementById(name)||document.querySelector('select[name="'+name+'"]');
	if(!el||!el.options) return '';
	return el.options[el.selectedIndex].value;
}
function getNumVal(id){
	var el=document.getElementById(id);
	return el?parseInt(el.value)||0:0;
}

function recalc(){
	segments=[];
	var type=getSelectVal('timer_coundown_type');
	var repeat=getSelectVal('timer_coundown_repeat');
	nowLocalLine=Date.now();
	var tzDiff=getSelectedTzOffset()-getLocalOffset();
	nowTzLine=nowLocalLine+tzDiff;

	if(type==='evergreen_countdown'||type==='evergreen_countup'){
		var expireMode=getSelectVal('evergreen_expire_mode');
		if(expireMode==='daily_time'){
			var expTime=getVal('evergreen_daily_expire_time')||'23:59';
			var tp=expTime.split(':');
			var h=parseInt(tp[0])||23,m=parseInt(tp[1])||59;
			var now=new Date();
			var todayBase=new Date(now.getFullYear(),now.getMonth(),now.getDate()).getTime();
			for(var d=0;d<30;d++){
				var dayStart=todayBase+d*86400000;
				var dayEnd=dayStart+h*3600000+m*60000;
				var dayBegin=dayStart;
				segments.push({start:dayBegin,end:dayEnd,label:d===0?'Today':'Day '+(d+1),color:d%3});
			}
			autoView(); return;
		}
		var dayV=getNumVal('timer_seesion_time_day_id');
		var hourV=getNumVal('timer_seesion_time_hour_id');
		var minV=getNumVal('timer_seesion_time_minute_id');
		var dur=(dayV*86400+hourV*3600+minV*60)*1000;
		if(dur<=0) dur=60000;
		var s=nowTzLine;
		segments.push({start:s,end:s+dur,label:'Visitor sees',color:0});
		var restart=getSelectVal('evergreen_restart');
		if(restart==='immediate'){
			for(var i=1;i<MAX_EVENTS&&s+dur*(i+1)-s<=MAX_RANGE_MS;i++)
				segments.push({start:s+dur*i,end:s+dur*(i+1),label:'#'+i,color:1});
		}else if(restart==='delay'){
			var dH=getNumVal('evergreen_restart_delay_hour_id');
			var dM=getNumVal('evergreen_restart_delay_minute_id');
			var delay=(dH*3600+dM*60)*1000; if(delay<=0) delay=3600000;
			var cursor=s+dur;
			for(var i=0;i<MAX_EVENTS&&cursor+delay+dur-s<=MAX_RANGE_MS;i++){
				var gapEnd=cursor+delay;
				segments.push({start:gapEnd,end:gapEnd+dur,label:'#'+(i+1),color:1,gapFrom:cursor});
				cursor=gapEnd+dur;
			}
		}
		autoView(); return;
	}

	var startTs=parseDate(getVal('timer_start_time'));
	var endTs=parseDate(getVal('timer_end_date'));
	if(startTs===null||endTs===null){ autoView(); return; }
	var s=startTs,e=endTs;
	if(e<=s) e=s+3600000;

	if(repeat==='none'||!repeat){
		segments.push({start:s,end:e,label:'Countdown',color:0});
	}else if(repeat==='when_end'){
		var rH=getNumVal('after_countdown_repeat_time_hour_id');
		var rM=getNumVal('after_countdown_repeat_time_minute_id');
		var repDur=(rH*3600+rM*60)*1000; if(repDur<=0) repDur=3600000;
		segments.push({start:s,end:e,label:'Initial',color:0});
		var cursor=e;
		for(var i=0;i<MAX_EVENTS&&cursor+repDur-s<=MAX_RANGE_MS;i++){
			segments.push({start:cursor,end:cursor+repDur,label:'#'+(i+1),color:(i%2)?2:1});
			cursor+=repDur;
		}
	}else if(repeat==='hourly'){
		var gap=(parseInt(getVal('repeat_hourly_interval'))||1)*3600000;
		var rH=getNumVal('after_countdown_repeat_time_hour_id');
		var rM=getNumVal('after_countdown_repeat_time_minute_id');
		var duration=(rH*3600+rM*60)*1000;
		if(duration<=0) duration=gap;
		var cycle=duration+gap;
		segments.push({start:s,end:e,label:'Initial',color:0});
		for(var i=0;i<MAX_EVENTS;i++){
			var cycleStart=e+gap+i*cycle;
			if(cycleStart-s>MAX_RANGE_MS) break;
			segments.push({start:cycleStart,end:cycleStart+duration,label:'#'+(i+1),color:(i%2)?2:1});
		}
	}else if(repeat==='daily'){
		var dayInterval=parseInt(getVal('repeat_daily_quantity'))||1;
		var stMs=parseTimeToMs(getVal('repeat_countdown_start_time')||'00:00');
		var enMs=parseTimeToMs(getVal('repeat_countdown_end_time')||'23:59');
		var dayDur=enMs-stMs; if(dayDur<=0) dayDur=86400000;
		var base=new Date(e);
		var baseDay=new Date(base.getFullYear(),base.getMonth(),base.getDate()).getTime();
		for(var i=0;i<MAX_EVENTS;i++){
			var dStart=baseDay+i*dayInterval*86400000+stMs;
			if(dStart-s>MAX_RANGE_MS) break;
			segments.push({start:dStart,end:dStart+dayDur,label:'Day '+(i+1),color:i%3});
		}
	}else if(repeat==='weekly'){
		var stMs=parseTimeToMs(getVal('repeat_countdown_start_time')||'00:00');
		var enMs=parseTimeToMs(getVal('repeat_countdown_end_time')||'23:59');
		var dayDur=enMs-stMs; if(dayDur<=0) dayDur=86400000;
		var checks=document.querySelectorAll('input[name="repeat_weekly_days[]"]:checked');
		var dayMap={sun:0,mon:1,tue:2,wed:3,thu:4,fri:5,sat:6};
		var allowed=[];
		checks.forEach(function(cb){ if(dayMap[cb.value]!==undefined) allowed.push(dayMap[cb.value]); });
		if(!allowed.length) allowed=[0,1,2,3,4,5,6];
		var now=new Date(nowTzLine);
		var baseDay=new Date(now.getFullYear(),now.getMonth(),now.getDate()).getTime();
		var dayNames=['Sun','Mon','Tue','Wed','Thu','Fri','Sat'];
		var count=0;
		for(var d=0;d<400&&count<MAX_EVENTS;d++){
			var dt=new Date(baseDay+d*86400000);
			if(allowed.indexOf(dt.getUTCDay())===-1) continue;
			var dStart=baseDay+d*86400000+stMs;
			if(dStart-(nowTzLine)>MAX_RANGE_MS) break;
			segments.push({start:dStart,end:dStart+dayDur,label:dayNames[dt.getUTCDay()],color:count%3});
			count++;
		}
	}else if(repeat==='monthly'){
		var dom=parseInt(getVal('repeat_monthly_day'))||1;
		var stMs=parseTimeToMs(getVal('repeat_countdown_start_time')||'00:00');
		var enMs=parseTimeToMs(getVal('repeat_countdown_end_time')||'23:59');
		var dayDur=enMs-stMs; if(dayDur<=0) dayDur=86400000;
		var monthNames=['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
		var now=new Date(nowTzLine);
		var startMonth=now.getUTCMonth(),startYear=now.getUTCFullYear();
		for(var m=0;m<MAX_EVENTS&&m<12;m++){
			var month=startMonth+m;
			var year=startYear+Math.floor(month/12); month=month%12;
			var dStart=new Date(year,month,dom).getTime()+stMs;
			segments.push({start:dStart,end:dStart+dayDur,label:monthNames[month]+' '+dom,color:m%3});
		}
	}

	applyRepeatLimit();
	autoView();
}

function parseTimeToMs(str){
	var p=str.split(':');
	return (parseInt(p[0])||0)*3600000+(parseInt(p[1])||0)*60000;
}

function applyRepeatLimit(){
	var endType=getSelectVal('repeat_end');
	if(endType==='after'){
		var limit=getNumVal('repeat_ending_after');
		if(limit<1) limit=1;
		if(segments.length>limit+1) segments=segments.slice(0,limit+1);
	}else if(endType==='on_date'){
		var endTs=parseDate(getVal('repeat_ending_after_date'));
		if(endTs!==null){
			for(var i=0;i<segments.length;i++){
				if(segments[i].start>endTs){ segments=segments.slice(0,i); break; }
			}
		}
	}
}

var firstLoad=true;
function autoView(){
	if(!segments.length){
		var n=nowLocalLine||Date.now();
		totalStart=n-3600000;totalEnd=n+86400000;
		if(firstLoad){viewStart=totalStart;viewEnd=totalEnd;firstLoad=false;}
		return;
	}
	var min=segments[0].start,max=segments[0].end;
	for(var i=1;i<segments.length;i++){
		if(segments[i].start<min) min=segments[i].start;
		if(segments[i].end>max) max=segments[i].end;
	}
	var pad=Math.max((max-min)*0.05,3600000);
	totalStart=min-pad;totalEnd=max+pad;
	if(firstLoad){
		viewStart=totalStart;viewEnd=totalEnd;firstLoad=false;
	}
}
function fitAll(){
	viewStart=totalStart;viewEnd=totalEnd;draw();
}
function goToNow(){
	var range=viewEnd-viewStart;
	var center=nowTzLine;
	viewStart=center-range/2;viewEnd=center+range/2;draw();
}

function draw(){
	if(!canvas) return;
	var W=canvas.width/DPR,H=canvas.height/DPR;
	var range=viewEnd-viewStart;
	if(range<=0) return;
	ctx.clearRect(0,0,W,H);
	var L=30,R=W-10;
	ctx.fillStyle='#fafafa';ctx.fillRect(0,0,W,H);

	if(!segments.length){
		ctx.fillStyle='#8c8f94';ctx.font='12px -apple-system,BlinkMacSystemFont,Segoe UI,Roboto,sans-serif';
		ctx.textAlign='center';ctx.fillText('Set timer dates to see the timeline',W/2,H/2);return;
	}

	var axisY=82;
	ctx.strokeStyle='#d0d0d0';ctx.lineWidth=1;
	ctx.beginPath();ctx.moveTo(L,axisY);ctx.lineTo(R,axisY);ctx.stroke();

	var tickInterval=getTickInterval(range);
	var firstTick=Math.ceil(viewStart/tickInterval)*tickInterval;
	ctx.textAlign='center';
	for(var t=firstTick;t<=viewEnd;t+=tickInterval){
		var x=tsToX(t,W);
		if(x<L||x>R) continue;
		ctx.strokeStyle='#e0e0e0';ctx.beginPath();ctx.moveTo(x,axisY-3);ctx.lineTo(x,axisY+3);ctx.stroke();
		ctx.fillStyle='#8c8f94';ctx.font='9px -apple-system,BlinkMacSystemFont,Segoe UI,sans-serif';
		ctx.fillText(formatTick(t,tickInterval),x,axisY+13);
	}

	var barH=22,barY=axisY-barH-14;
	var colors=['#2271b1','#00a32a','#8c5ae0','#dba617','#d63638','#e26f56'];
	for(var i=0;i<segments.length;i++){
		var seg=segments[i];
		var x1=tsToX(seg.start,W),x2=tsToX(seg.end,W);
		if(x2<L||x1>R) continue;
		x1=Math.max(L,x1);x2=Math.min(R,x2);
		var w=Math.max(x2-x1,2);
		var col=colors[(seg.color||0)%colors.length];
		if(seg.gapFrom){
			var gx=tsToX(seg.gapFrom,W);
			ctx.strokeStyle='#bbb';ctx.lineWidth=1;ctx.setLineDash([3,3]);
			ctx.beginPath();ctx.moveTo(Math.max(L,gx),barY+barH/2);ctx.lineTo(x1,barY+barH/2);ctx.stroke();
			ctx.setLineDash([]);
		}
		ctx.fillStyle=col;roundRect(ctx,x1,barY,w,barH,3);ctx.fill();
		if(w>20){
			ctx.fillStyle='#fff';ctx.font='bold 9px -apple-system,BlinkMacSystemFont,sans-serif';
			ctx.textAlign='center';ctx.fillText(seg.label,x1+w/2,barY+barH/2+3,w-4);
		}
		if(w>70){
			ctx.font='8px -apple-system,BlinkMacSystemFont,sans-serif';ctx.fillStyle='#646970';
			ctx.textAlign='left';ctx.fillText(fmtDT(seg.start),x1,barY-3);
			ctx.textAlign='right';ctx.fillText(fmtDT(seg.end),x1+w,barY-3);
		}
	}

	// NOW lines — always show both
	var tzLabel=tzName||'Server';
	// Server/selected timezone NOW
	var tzX=tsToX(nowTzLine,W);
	if(tzX>=L&&tzX<=R){
		ctx.strokeStyle='#d63638';ctx.lineWidth=1.5;ctx.setLineDash([4,2]);
		ctx.beginPath();ctx.moveTo(tzX,barY-16);ctx.lineTo(tzX,axisY+3);ctx.stroke();ctx.setLineDash([]);
		ctx.fillStyle='#d63638';ctx.font='bold 8px -apple-system,BlinkMacSystemFont,sans-serif';
		ctx.textAlign='center';ctx.fillText('NOW ('+tzLabel+')',tzX,barY-19);
	}
	// Local PC NOW
	var localX=tsToX(nowLocalLine,W);
	if(localX>=L&&localX<=R&&Math.abs(localX-tzX)>5){
		ctx.strokeStyle='#2271b1';ctx.lineWidth=1;ctx.setLineDash([2,3]);
		ctx.beginPath();ctx.moveTo(localX,barY-8);ctx.lineTo(localX,axisY+3);ctx.stroke();ctx.setLineDash([]);
		ctx.fillStyle='#2271b1';ctx.font='bold 8px -apple-system,BlinkMacSystemFont,sans-serif';
		ctx.textAlign='center';ctx.fillText('NOW (Your PC)',localX,barY-10);
	}

	// Mini overview
	var ovY=H-14,ovH=6;
	ctx.fillStyle='#e8e8e8';roundRect(ctx,L,ovY,R-L,ovH,3);ctx.fill();
	var tRange=totalEnd-totalStart;
	if(tRange>0){
		for(var i=0;i<segments.length;i++){
			var seg=segments[i];
			var ox1=L+(seg.start-totalStart)/tRange*(R-L);
			var ox2=L+(seg.end-totalStart)/tRange*(R-L);
			ctx.fillStyle=colors[(seg.color||0)%colors.length];ctx.globalAlpha=0.4;
			ctx.fillRect(Math.max(L,ox1),ovY,Math.max(ox2-ox1,1),ovH);ctx.globalAlpha=1;
		}
		var vx1=L+(viewStart-totalStart)/tRange*(R-L);
		var vx2=L+(viewEnd-totalStart)/tRange*(R-L);
		ctx.strokeStyle='#1d2327';ctx.lineWidth=1;ctx.strokeRect(vx1,ovY-1,Math.max(vx2-vx1,2),ovH+2);
	}
	ctx.fillStyle='#8c8f94';ctx.font='9px -apple-system,BlinkMacSystemFont,sans-serif';
	ctx.textAlign='right';ctx.fillText(segments.length+' events',R,ovY-4);
}

function tsToX(ts,W){ return 30+(ts-viewStart)/(viewEnd-viewStart)*(W-40); }

function getTickInterval(range){
	var t=[60000,300000,600000,1800000,3600000,7200000,14400000,43200000,
		86400000,172800000,604800000,1296000000,2592000000,7776000000,15552000000,31536000000];
	for(var i=0;i<t.length;i++){ if(range/t[i]<12) return t[i]; }
	return 31536000000;
}
var MN=['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
function formatTick(ts,interval){
	var d=new Date(ts);
	if(interval>=2592000000) return MN[d.getMonth()]+' '+d.getFullYear();
	if(interval>=86400000) return p2(d.getDate())+'/'+p2(d.getMonth()+1);
	return p2(d.getHours())+':'+p2(d.getMinutes());
}
function fmtDT(ts){
	var d=new Date(ts);
	return p2(d.getDate())+'/'+p2(d.getMonth()+1)+' '+p2(d.getHours())+':'+p2(d.getMinutes());
}
function p2(n){ return n<10?'0'+n:''+n; }
function roundRect(c,x,y,w,h,r){
	c.beginPath();c.moveTo(x+r,y);c.lineTo(x+w-r,y);c.quadraticCurveTo(x+w,y,x+w,y+r);
	c.lineTo(x+w,y+h-r);c.quadraticCurveTo(x+w,y+h,x+w-r,y+h);c.lineTo(x+r,y+h);
	c.quadraticCurveTo(x,y+h,x,y+h-r);c.lineTo(x,y+r);c.quadraticCurveTo(x,y,x+r,y);c.closePath();
}

function bindEvents(){
	canvas.addEventListener('wheel',function(e){
		e.preventDefault();
		var rect=canvas.getBoundingClientRect();
		var mx=(e.clientX-rect.left)/rect.width;
		var range=viewEnd-viewStart;
		var factor=e.deltaY>0?1.3:0.77;
		var newRange=range*factor;
		newRange=Math.max(30000,Math.min(MAX_RANGE_MS*2,newRange));
		var pivot=viewStart+range*mx;
		viewStart=pivot-newRange*mx;viewEnd=pivot+newRange*(1-mx);
		draw();
	},{passive:false});

	canvas.addEventListener('mousedown',function(e){
		isDragging=true;dragStartX=e.clientX;dragViewStart=viewStart;canvas.style.cursor='grabbing';
	});
	window.addEventListener('mousemove',function(e){
		if(!isDragging) return;
		var rect=canvas.getBoundingClientRect();
		var dx=e.clientX-dragStartX;
		var range=viewEnd-viewStart;
		var shift=-dx/rect.width*range;
		viewStart=dragViewStart+shift;viewEnd=dragViewStart+shift+range;draw();
	});
	window.addEventListener('mouseup',function(){
		if(isDragging){isDragging=false;if(canvas)canvas.style.cursor='grab';}
	});
	window.addEventListener('resize',function(){ resize(); draw(); });

	var form=document.getElementById('adminForm');
	if(form){
		form.addEventListener('change',function(){ recalc(); draw(); });
		form.addEventListener('input',function(){ recalc(); draw(); });
	}
	var btnNow=document.getElementById('wpda_timeline_goto_now');
	if(btnNow) btnNow.addEventListener('click',function(){ goToNow(); });
	var btnFit=document.getElementById('wpda_timeline_fit_all');
	if(btnFit) btnFit.addEventListener('click',function(){ fitAll(); });
}

if(document.readyState==='loading') document.addEventListener('DOMContentLoaded',init);
else setTimeout(init,100);
})();
