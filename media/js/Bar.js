var dbg_start = Date.now();



jQuery(window).load(function(){
	//setTimeout(dbg_performance,1000);
	dbg_performance();
});

jQuery(document).ajaxComplete(function(event, jqXHR, opt){
	var headers = jqXHR.getAllResponseHeaders().trim().split(/[\r\n]+/);
	var url = opt.url;
	tdLog('AJAX Request '+opt.type+' '+url,'info');
	for(var i=0;i<headers.length;i++){
		var [hdr,content] = headers[i].split(':',2);
		if( -1 != hdr.indexOf('x-total-debug-console') ){
			var logs = content.split(',');
			var msg = '';
			for(var j=0; j<logs.length; j++){
				tdLog( atob( logs[j].trim() ) );
			}
		}
		else if( -1 != hdr.indexOf('x-total-debug-sql') ){
			var logs = content.split(',');
			var html = '';
			for(var j=0; j<logs.length; j++){
				try{
					var json = atob( logs[j].trim() );
					var [sql,time] = JSON.parse(json);
					var type = sql.split(' ')[0];
					html += '<tr><td>'+url+'</td><td><code class=sql>'+colorizeQuery(sql)+'</code></td><td>'+type+'</td><td>'+time.toFixed(4)+'</td></tr>';
				}catch(err){
					console.log('Error with AJAX SQL Debug',err);
				}
			}
			$('#ajax_query_table').append(html);
			$('.ajax_sql').show();

			var curCount = parseInt( $('#ajax_sql_count').text() || 0 );
			var total = curCount + logs.length;
			$('#ajax_sql_count').text(total);
		}
	}
	//console.log(headers);
});

jQuery(function($){	

	/*
	jQuery.ajaxSetup({
		beforeSend: function(j,xhr,o) {
			//console.log('Ajax Request URL'+xhr.url+' Type '+xhr.type);
			//console.log(q);
			//console.log('beforeSend');
		},
		complete: function() {
			console.log('complete');
		},
		success: function() {
			console.log('success');
		}
	});
	*/
	jQuery('#dbg_bar table.sortable').tablesorter();

	jQuery(function($){
		$('.value-info').click(function(){ $(this).toggleClass('expanded') });
	});

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

	
	$('#dbg_bar .resize-bar').mousedown(function(e){      
		e.preventDefault();        		
		jQuery(document).mousemove(mouse_move);
    });
	
	$(document).mouseup(function(e){
		//jQuery('#dbg_bar .resize-bar').unbind('mousemove');
		jQuery(document).unbind('mousemove');
	});
   
	$('#dbg_bar .tabs a').click(function(e){
		var tab = $(this).attr('href');
		$('.dbg_body .panel').hide().removeClass('active');
		$('#dbg_bar .tabs a').removeClass('active');		
		$(this).addClass('active');
		$(tab).show().addClass('active');
		
		$('#dbg_bar .tabs li').removeClass('current');
		$(this).parent().addClass('current');
		
		e.preventDefault();
		e.stopPropagation();
		return false;
	});
	
	
	$('#included_file_search_do').click(included_file_search);
	colorize_sql();

	$('#filter_hooks').click(filter_hooks);

	$('#do_query_search').click(do_query_search);

	$('.trace .show_trace').click(function(){
		$(this).closest('ol').toggleClass('show');
	});
});

function tdLog(msg, type){
	$('#dbg_console #log').append('<li class="'+type+'">'+msg+'</li>');
}

function do_query_search(){
	var v = jQuery('#query_search_value').val();
	console.log('Queyr Search :'+v)
	if(v == ''){
		jQuery('#query_table tr').show();
	}
	else {
		jQuery('#query_table tr:not(:contains("'+v+'"))').hide();
	}
}

function filter_hooks(){
	var name = jQuery('#filter_hooks_by').val();
	console.log('filtering hooks '+name);
	if(name == ''){
		jQuery('#dbg_hooks tr').show();
	}
	else {
		jQuery('#dbg_hooks tr:not(:contains("'+name+'"))').hide();
	}
	console.log('done!');
}

function mouse_move(event){
	var scrollPosition = jQuery(window).scrollTop();
	var h = window.innerHeight + scrollPosition - event.pageY;
	jQuery('body').css('padding-bottom', h);
	jQuery('.dbg_body').height(h);
}

function included_file_search(){
	var file = jQuery('#included_file_search').val();
	
	//console.log('in file search');
	
	if(file == ''){
		jQuery('#included_files tr').show();
	}
	else {
		jQuery('#included_files tr:not(:contains("'+file+'"))').hide();
	}
}

function dbg_performance(){
	var now = new Date().getTime();
	//https://web.dev/navigation-and-resource-timing/
	var t = performance.getEntriesByType("navigation")[0];

	//var t 				= window.performance.timing;		
	var dns 			= t.domainLookupEnd - t.domainLookupStart;
	var tcp 			= t.connectEnd - t.connectStart;
	var ttfb 			= t.responseStart;
	
	var responseTime 	= t.responseStart - t.responseEnd;
	
	var pageloadtime 	= t.loadEventStart;
	var connectTime 	= t.responseEnd - t.requestStart;
	var domTime 		= window.performance.timing.domContentLoadedEventEnd - window.performance.timing.domContentLoadedEventStart;
	var basePage 		= t.responseEnd - t.responseStart;
	var frontEnd 		= t.loadEventStart - t.responseEnd;
	
	var percs = {
		dns:		dbg_percentage(dns, 		pageloadtime),
		tcp:		dbg_percentage(tcp, 		pageloadtime),
		connect:	dbg_percentage(connectTime, pageloadtime),
		ttfb:		dbg_percentage(ttfb, 		pageloadtime),
		basePage:	dbg_percentage(basePage,	pageloadtime),
		frontEnd:	dbg_percentage(frontEnd,	pageloadtime),	
		dom:		dbg_percentage(domTime,		pageloadtime),	
	};
	//console.table(percs);	
	var serverTotalTime 	= connectTime;
	var browserTotalTime 	= frontEnd + domTime;

	jQuery('.serverTotalTime').html( (serverTotalTime).toFixed(2)+'ms' )
	jQuery('.browserTotalTime').html( (browserTotalTime).toFixed(2)+'ms' );
	jQuery('.TotalTime').html((browserTotalTime + serverTotalTime).toFixed(2)+'ms' );
	
	
	//t.transferSize
	
	var h = '';
		h += '<tr><th>DNS</th><td>'+dbg_progress_bar(percs.dns)+'</td><td>'+dns.toFixed(2)+'ms</td><td>'+percs.dns+'%</td></tr>';
		h += '<tr><th>TCP</th><td>'+dbg_progress_bar(percs.tcp)+'</td><td>'+tcp.toFixed(2)+'ms</td><td>'+percs.tcp+'%</td></tr>';				
		//h += '<tr><th>SSL</th><td>'+dbg_progress_bar(percs.ssl)+'</td><td>'+tcp.toFixed(2)+'ms</td><td>'+percs.ssl+'%</td></tr>';				
		h += '<tr><th>Connect Time</th>	<td>'+dbg_progress_bar(percs.connect)+'</td><td>'+connectTime.toFixed(2)+'ms</td><td>'+percs.connect+'%</td></tr>';				
		h += '<tr><th>Time to First Byte</th><td>'+dbg_progress_bar(percs.ttfb)+'</td><td>'+ttfb.toFixed(2)+'ms</td><td>'+percs.ttfb+'%</td></tr>';
		h += '<tr><th>Send Response</th><td>'+dbg_progress_bar(percs.basePage)+'</td><td>'+basePage.toFixed(2)+'ms</td><td>'+percs.basePage+'%</td></tr>';
		h += '<tr><th>Front End</th><td>'+dbg_progress_bar(percs.frontEnd)+'</td><td>'+frontEnd.toFixed(2)+'ms</td><td>'+percs.frontEnd+'%</td></tr>';
		h += '<tr><th>DOM</th><td>'+dbg_progress_bar(percs.dom)+'</td><td>'+domTime.toFixed(2)+'ms</td><td>'+percs.dom+'%</td></tr>';
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

function colorizeQuery(h){
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
	
	h = h.replace(/ ASC /gi,		' <i class=mn>ASC</i> ');
	h = h.replace(/ DESC /gi,		' <i class=mn>DESC</i> ');
	h = h.replace(/ IN /gi,			' <i class=mn>IN</i> ');
	h = h.replace(/ AND /gi,		'<br/>&nbsp;&nbsp;<i class=mn> AND </i> ');
	h = h.replace(/ LIKE /gi,		'<i class=mn> LIKE </i> ');			
	h = h.replace(/SHOW VARIABLES/gi,	'<i class=mn>SHOW VARIABLES </i><br/> ');					
	
	h = h.replace(/(\d+)/gi,		'<i class=int>$1</i>');		
	return h;
}

function colorize_sql(){
	jQuery('#dbg_db code.sql').each(function(){
		var h = jQuery(this).html();
		h = colorizeQuery(h);
		jQuery(this).html(h);	
	});
}
function colorize_php(){

}



