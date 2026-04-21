(function(blocks, element, components) {
var el = element.createElement;
var data = window['wpda_countdown_gutenberg_data'] || {};
var timers = data.timers || {};
var themes = data.themes || {};
var iconSrc = data.other_data ? data.other_data.icon_src : '';

var clockIcon = el('svg', {width:24,height:24,viewBox:'0 0 24 24',xmlns:'http://www.w3.org/2000/svg'},
	el('path',{d:'M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 18c-4.41 0-8-3.59-8-8s3.59-8 8-8 8 3.59 8 8-3.59 8-8 8zm.5-13H11v6l5.25 3.15.75-1.23-4.5-2.67V7z',fill:'currentColor'})
);

blocks.registerBlockType('wpdevart-countdown/countdown-pro', {
	title: 'WpDevArt Countdown',
	icon: clockIcon,
	category: 'common',
	keywords: ['countdown','timer','time','clock','deadline'],
	attributes: {
		mode: { type:'string', default:'timer' },
		timer: { type:'string', default:'' },
		theme: { type:'string', default:'' },
		endDate: { type:'string', default:'' },
		endTime: { type:'string', default:'23:59' },
	},

	edit: function(props) {
		var a = props.attributes;
		var setA = props.setAttributes;
		var isTimer = a.mode !== 'date';

		function timerOptions(){
			var opts = [el('option',{value:''},'— Select Timer —')];
			for(var k in timers) opts.push(el('option',{value:k,key:k},timers[k]));
			return opts;
		}
		function themeOptions(){
			var opts = [el('option',{value:''},'— Default Theme —')];
			for(var k in themes) opts.push(el('option',{value:k,key:k},themes[k]));
			return opts;
		}

		var modeTabs = el('div',{style:{display:'flex',gap:'0',marginBottom:'16px',borderRadius:'6px',overflow:'hidden',border:'1px solid #ddd'}},
			el('button',{
				type:'button',
				onClick:function(){setA({mode:'timer'})},
				style:{flex:1,padding:'10px 0',border:'none',cursor:'pointer',fontWeight:'600',fontSize:'13px',
					background:isTimer?'#1d2327':'#fff',color:isTimer?'#fff':'#50575e',transition:'all 0.2s'}
			},'Select Timer'),
			el('button',{
				type:'button',
				onClick:function(){setA({mode:'date'})},
				style:{flex:1,padding:'10px 0',border:'none',borderLeft:'1px solid #ddd',cursor:'pointer',fontWeight:'600',fontSize:'13px',
					background:!isTimer?'#1d2327':'#fff',color:!isTimer?'#fff':'#50575e',transition:'all 0.2s'}
			},'Quick Date')
		);

		var timerPanel = isTimer ? el('div',{style:{marginBottom:'12px'}},
			el('label',{style:{display:'block',fontSize:'12px',fontWeight:'600',color:'#1d2327',marginBottom:'4px'}},'Timer'),
			el('select',{
				value:a.timer||'',
				onChange:function(e){setA({timer:e.target.value})},
				style:{width:'100%',padding:'8px',borderRadius:'4px',border:'1px solid #ddd',fontSize:'13px'}
			},timerOptions())
		) : null;

		var datePanel = !isTimer ? el('div',{},
			el('div',{style:{display:'flex',gap:'8px',marginBottom:'12px'}},
				el('div',{style:{flex:2}},
					el('label',{style:{display:'block',fontSize:'12px',fontWeight:'600',color:'#1d2327',marginBottom:'4px'}},'End Date'),
					el('input',{
						type:'date',
						value:a.endDate||'',
						onChange:function(e){setA({endDate:e.target.value})},
						style:{width:'100%',padding:'8px',borderRadius:'4px',border:'1px solid #ddd',fontSize:'13px',boxSizing:'border-box'}
					})
				),
				el('div',{style:{flex:1}},
					el('label',{style:{display:'block',fontSize:'12px',fontWeight:'600',color:'#1d2327',marginBottom:'4px'}},'End Time'),
					el('input',{
						type:'time',
						value:a.endTime||'23:59',
						onChange:function(e){setA({endTime:e.target.value})},
						style:{width:'100%',padding:'8px',borderRadius:'4px',border:'1px solid #ddd',fontSize:'13px',boxSizing:'border-box'}
					})
				)
			)
		) : null;

		var themePanel = el('div',{style:{marginBottom:'4px'}},
			el('label',{style:{display:'block',fontSize:'12px',fontWeight:'600',color:'#1d2327',marginBottom:'4px'}},'Theme'),
			el('select',{
				value:a.theme||'',
				onChange:function(e){setA({theme:e.target.value})},
				style:{width:'100%',padding:'8px',borderRadius:'4px',border:'1px solid #ddd',fontSize:'13px'}
			},themeOptions())
		);

		var preview = el('div',{style:{marginTop:'12px',padding:'10px',background:'#f0f0f1',borderRadius:'6px',textAlign:'center',fontSize:'12px',color:'#646970'}},
			isTimer
				? (a.timer ? '⏱ Timer: ' + (timers[a.timer]||'#'+a.timer) + (a.theme&&themes[a.theme]?' · Theme: '+themes[a.theme]:'') : 'Select a timer above')
				: (a.endDate ? '📅 Counts down to: ' + a.endDate + ' ' + (a.endTime||'23:59') + (a.theme&&themes[a.theme]?' · Theme: '+themes[a.theme]:'') : 'Set an end date above')
		);

		return el('div',{
			className:props.className,
			style:{background:'#fff',border:'1px solid #e0e0e0',borderRadius:'10px',padding:'20px',maxWidth:'400px'}
		},
			el('div',{style:{display:'flex',alignItems:'center',gap:'8px',marginBottom:'14px'}},
				el('svg',{width:20,height:20,viewBox:'0 0 24 24',fill:'none',stroke:'#2271b1',strokeWidth:'2',strokeLinecap:'round'},
					el('circle',{cx:'12',cy:'12',r:'10'}),
					el('path',{d:'M12 6v6l4 2'})
				),
				el('span',{style:{fontWeight:'700',fontSize:'15px',color:'#1d2327'}},'Countdown')
			),
			modeTabs,
			timerPanel,
			datePanel,
			themePanel,
			preview
		);
	},

	save: function() {
		return null;
	}
});
})(
	window.wp.blocks,
	window.wp.element,
	window.wp.components || {}
);
