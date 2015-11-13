var dbg_resizing = false;
var dbg_start = Date.now();

//Run out dbg_performance method after everything else is done
jQuery(window).load(dbg_performance);

jQuery(function($){	


	$('#close_dbg').click(function(){
		$('#dbg_bar').hide();
	});
	
	$('#wp-admin-bar-debug-bar a').click(function(e){
		if( $('#dbg_bar').length ){
			$('#dbg_bar').toggle();
			e.preventDefault();
		}
	});	

	//Run this in a timer, so that all dom stuff should be done
	if(typeof debug_time != 'undefined'){
		$('#debug_time').html(debug_time);
	}
	$('.dbg_search').change(function(){
		var search_str = jQuery(this).val();		
		jQuery('#dbg_globals tr').show();
		jQuery('#dbg_globals tr:not(:contains("'+search_str+'"))').hide();
		//hide rows not containing		
	});

	$('#debug-bar-resize').mousedown(function(e){      
		console.log('resize clicked');
        e.preventDefault();        
		startY = e.pageY;
		
		jQuery(document).mousemove(function(e){
			var h =  startY - e.pageY;
			var height = jQuery('.dbg_body').height();
			Math.max(height,height+h);
			jQuery('.dbg_body').height(h);
       });
	   
    });
	$(document).mouseup(function(e){
		startY = e.pageY;
		jQuery(document).unbind('mousemove');
	});
   
	$('#dbg_bar .tabs a').click(function(e){
		e.preventDefault();
		var tab = $(this).attr('href');
		$('.dbg_body .panel').hide().removeClass('active');
		$('#dbg_bar .tabs a').removeClass('active');		
		$(this).addClass('active');
		$(tab).show().addClass('active');
		
		$('#dbg_bar .tabs li').removeClass('current');
		$(this).parent().addClass('current');
		
	});
	
	colorize_sql();
});


function dbg_performance(){
	var t 				= window.performance.timing;		
	var dns 			= t.domainLookupEnd - t.domainLookupStart;
	var tcp 			= t.connectEnd - t.connectStart;
	var ttfb 			= t.responseStart - t.navigationStart;
	
	var responseTime 	= t.responseStart - t.responseEnd;
	
	var pageloadtime 	= t.loadEventEnd - t.navigationStart;
	var connectTime 	= t.responseEnd - t.requestStart;
	var domTime 		= t.domContentLoadedEventEnd - t.domContentLoadedEventStart;
	
	var basePage 		= t.responseEnd - t.responseStart;
	var frontEnd 		= t.loadEventStart - t.responseEnd;
	
	var total = pageloadtime;
	var percs = {
		dns:		dbg_percentage(dns,total),
		tcp:		dbg_percentage(tcp,total),
		connect:	dbg_percentage(connectTime,total),
		ttfb:		dbg_percentage(ttfb,total),
		basePage:	dbg_percentage(basePage,total),
		frontEnd:	dbg_percentage(frontEnd,total),	
	};
		
	var serverTotalTime 	= connectTime;
	var browserTotalTime 	= frontEnd + domTime
	jQuery('.serverTotalTime').html( (serverTotalTime).toFixed(2) )
	jQuery('.browserTotalTime').html( (browserTotalTime).toFixed(2) );
	jQuery('.TotalTime').html((browserTotalTime + serverTotalTime).toFixed(2) );
	
	var h = '';
		h += '<tr><th>DNS</th><td>'+dns.toFixed(2)+'ms</td><td>'+percs.dns+'%</td><td>'+dbg_progress_bar(percs.dns)+'</td></tr>';
		h += '<tr><th>TCP</th><td>'+tcp.toFixed(2)+'ms</td><td>'+percs.tcp+'%</td><td>'+dbg_progress_bar(percs.tcp)+'</td></tr>';				
		h += '<tr><th>Time to First Byte</th><td>'+ttfb.toFixed(2)+'ms</td><td>'+percs.ttfb+'%</td><td>'+dbg_progress_bar(percs.ttfb)+'</td></tr>';
		h += '<tr><th>Connect Time</th><td>'+connectTime.toFixed(2)+'ms</td><td>'+percs.connect+'%</td><td>'+dbg_progress_bar(percs.connect)+'</td></tr>';				
		h += '<tr><th>Send Response</th><td>'+basePage.toFixed(2)+'ms</td><td>'+percs.basePage+'%</td><td>'+dbg_progress_bar(percs.basePage)+'</td></tr>';
		h += '<tr><th>Front End</th><td>'+frontEnd.toFixed(2)+'ms</td><td>'+percs.frontEnd+'%</td><td>'+dbg_progress_bar(percs.frontEnd)+'</td></tr>';
		h += '<tr><th>DOM</th><td>'+domTime.toFixed(2)+'ms</td><td></td></tr>';			
		h += '<tr><th>Page Load</th><td>'+pageloadtime.toFixed(2)+'ms</td><td></td></tr>';
		
	jQuery('#dbg_frontend').html(h);	
}

function dbg_percentage(v,total){
	var perc = ((v/total) * 100).toFixed(2); 
	return perc;
}
function dbg_progress_bar(perc){ return '<progress max=100 value="'+perc+'"></progress>'; }

/*
This is a quick and dirty colorizer
We don't want to load a seperate library like prism or rainbow to do this
Just some basic syntax hilighting
*/
function colorize_sql(){
	jQuery('#dbg_db code.sql').each(function(){
		var h = jQuery(this).html();
		
			h = h.replace(/([!=])/gi,	'<i class=op>$1</i> ');
		
			h = h.replace(/SELECT /gi,		'<i class=mn>SELECT</i> ');
			h = h.replace(/FROM /gi,		'<i class=mn>FROM</i> ');
			h = h.replace(/LIMIT /gi,		'<br/><i class=mn>LIMIT</i> ');
			h = h.replace(/ORDER BY/gi ,	'<br/><i class=mn>ORDER BY</i> ');
			h = h.replace(/GROUP BY/gi ,	'<br/><i class=mn>GROUP BY</i> ');
			h = h.replace(/LEFT JOIN /gi,	'<br/><i class=mn>LEFT JOIN</i> ');
			h = h.replace(/RIGHT JOIN /gi,	'<br/><i class=mn>RIGHT JOIN</i> ');
			h = h.replace(/JOIN /gi,		'<br/><i class=mn>JOIN</i> ');
			h = h.replace(/WHERE /gi,		'<br/><i class=cnd>WHERE</i> ');		
			h = h.replace(/'(.*)'/gi,		'<i class=str>\'$1\'</i> ');
			h = h.replace(/ ON /gi,			' <i class=mn>ON</i> ');
			h = h.replace(/ AS /gi,			' <i class=mn>AS</i> ');
			
			h = h.replace(/ASC /gi,			'<i class=mn>ASC</i> ');
			h = h.replace(/DESC /gi,		'<i class=mn>DESC</i> ');
			h = h.replace(/ IN /gi,			' <i class=mn>IN</i> ');
			h = h.replace(/ AND /gi,		'<br/>&nbsp;&nbsp;<i class=mn> AND </i> ');
			h = h.replace(/ LIKE /gi,		'<i class=mn> LIKE </i> ');			
			h = h.replace(/SHOW VARIABLES/gi,	'<i class=mn>SHOW VARIABLES </i><br/> ');					
			
			h = h.replace(/(\d+)/gi,		'<i class=int>$1</i>');				
			
			
		jQuery(this).html(h);	
	});
}
