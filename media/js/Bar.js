var dbg_start = Date.now();

console.log('in Bar.js');

jQuery(document).ajaxComplete(function(event, jqXHR, opt){
	console.log('ajaxComplete');
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
			console.log('Logs',logs);
			return;
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
			jQuery('#ajax_query_table').append(html);
			jQuery('.ajax_sql').show();

			var curCount = parseInt( jQuery('#ajax_sql_count').text() || 0 );
			var total = curCount + logs.length;
			jQuery('#ajax_sql_count').text(total);
		}
	}
	//console.log(headers);
});

jQuery(window).on("load", function(){
	var $ = jQuery;
	setTimeout(dbg_performance, 3000);
	console.log('In Debug Bar');
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
		if($('.dbg_body').height() < 5){
			$('.dbg_body').css('height','300px');
		}
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
	$('#dbg_bar .expand .toggle').click(function(){
		$(this).parent().toggleClass('show');
		return false;
	});
	
	var robots = $('meta[name=robots]').attr('content') || '';
	var title = $('head title').text() || '';
	var desc = $('meta[name=description]').attr('content') || '';
	var og_type = $('meta[property="og:type"]').attr('content') || '';
	var og_image = $('meta[property="og:image"]').attr('content') || '';
	var site_name = $('meta[property="og:site_name"]').attr('content') || '';
	var meta_html = `<tr><th>Robots</th><td>${robots}</td></tr>
					 <tr><th>Title</th><td>${title}</td></tr>
					 <tr><th>Description</th><td>${desc}</td></tr>
					 <tr><th>OG Site Name</th><td>${site_name}</td></tr>
					 <tr><th>OG Type</th><td>${og_type}</td></tr>
					 <tr><th>OG Image</th><td>${og_image}</td></tr>
					 `;
	$(meta_html).insertBefore('#dbg_bar_info');
});

function tdLog(msg, type){
	jQuery('#dbg_console #log').append('<li class="'+type+'">'+msg+'</li>');
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
	var total_files = $('#included_files tbody tr').length; 
	var file_count = $('#included_files tbody tr:visible').length;
	var filter_count = total_files == file_count ? '' : '( Filtered : '+file_count+')';
	$('#wptd_filtered_files').html(filter_count);
}

// https://medium.com/zattoo_tech/performance-monitoring-928bcaa88272
// https://github.com/GoogleChrome/web-vitals
// https://stackoverflow.com/questions/72485999/lcp-result-is-totally-opposite-from-performance-api-than-the-pagespeed-insights
// https://kinsta.com/blog/eliminate-render-blocking-javascript-css/
// https://developer.mozilla.org/en-US/docs/Web/API/PerformanceResourceTiming
var PAGELOADTIME;
function dbg_performance(){
	//Need a performance observer for LCP and other items
	// Need % of LCP

	var now = new Date().getTime();
	//https://web.dev/navigation-and-resource-timing/
	var t = performance.getEntriesByType("navigation")[0];

	dbg_resources(); //Move this, or fine here?
	dbg_cwv();
	//Show Front End Time + Backend Time as a single bar with Percentages

	//performance.getEntriesByType('resource')
	//performance.getEntriesByName('first-contentful-paint')[0]

	//images = performance.getEntriesByType('img')
	//img.duration
	//img.name

	//link 
	//script 
	//css

	//Show TTFB

	//var t 				= window.performance.timing;		
	PAGELOADTIME 	= t.duration;
	var pageloadtime = PAGELOADTIME;
	//var domComplete = t.domComplete;
	//var domInteractive = t.interactive;

	//DOM:		domInteractive 	to loadEventStart
	//Parsing: 	responseEnd 	to domInteractive

	var dns 			= t.domainLookupEnd - t.domainLookupStart;
	var tcp 			= t.connectEnd - t.connectStart;
	var ttfb 			= t.responseStart - t.requestStart; 

	var responseTime 	= t.responseStart - t.responseEnd;
	
	var connectTime 	= t.responseEnd - t.requestStart;
	var domTime 		= t.domComplete - t.responseEnd;//Is this right? Also includes Parsing Time?
	var basePage 		= t.responseEnd - t.responseStart;
	var frontEnd 		= t.loadEventStart - t.responseEnd;
	var loadEvent 		= t.loadEventEnd - t.loadEventStart;

	var pageloadtime 	= t.loadEventStart - t.requestStart;

	var ssl = t.requestStart - t.secureConnectionStart;//If SSL
	/*
		jQuery ready vs load?

		Simple Pie Chart:
		scripts
		css
		images
		font
		media (video)
		Bytes for each
		domComplete - domLoading
	 	unloadEventStart;
    	unloadEventEnd;
    	domInteractive;
    	domContentLoadedEventStart;
    	domContentLoadedEventEnd;
    	domComplete;
    	loadEventStart;
    	loadEventEnd;
	*/
	//t.transferSize
	var percs = {
		dns:		dbg_percentage(dns, 		pageloadtime),
		tcp:		dbg_percentage(tcp, 		pageloadtime),
		connect:	dbg_percentage(connectTime, pageloadtime),
		ttfb:		dbg_percentage(ttfb, 		pageloadtime),
		basePage:	dbg_percentage(basePage,	pageloadtime),
		frontEnd:	dbg_percentage(frontEnd,	pageloadtime),	
		load:		dbg_percentage(loadEvent,	pageloadtime),	
		dom:		dbg_percentage(domTime,		pageloadtime),	
	};
	//console.table(percs);	
	var serverTotalTime 	= connectTime;
	var browserTotalTime 	= frontEnd + domTime;

	jQuery('.serverTotalTime').html( (serverTotalTime).toFixed(2)+'ms' )
	jQuery('.browserTotalTime').html( (browserTotalTime).toFixed(2)+'ms' );
	jQuery('.TotalTime').html((browserTotalTime + serverTotalTime).toFixed(2)+'ms' ); //This is wrong, ignore debug bar time.
	
	
	//t.transferSize
	
	var h = '';
		h += '<tr><th>DNS</th><td>'+dbg_progress_bar(percs.dns)+'</td><td>'+dns.toFixed(2)+'ms</td><td>'+percs.dns+'%</td></tr>';
		h += '<tr><th>TCP</th><td>'+dbg_progress_bar(percs.tcp)+'</td><td>'+tcp.toFixed(2)+'ms</td><td>'+percs.tcp+'%</td></tr>';				
		//h += '<tr><th>SSL</th><td>'+dbg_progress_bar(percs.ssl)+'</td><td>'+tcp.toFixed(2)+'ms</td><td>'+percs.ssl+'%</td></tr>';				
		//h += '<tr><th>Connect Time</th>	<td>'+dbg_progress_bar(percs.connect)+'</td><td>'+connectTime.toFixed(2)+'ms</td><td>'+percs.connect+'%</td></tr>';				
		h += '<tr><th>Time to First Byte</th><td>'+dbg_progress_bar(percs.ttfb)+'</td><td>'+ttfb.toFixed(2)+'ms</td><td>'+percs.ttfb+'%</td></tr>';
		h += '<tr><th>Send Response</th><td>'+dbg_progress_bar(percs.basePage)+'</td><td>'+basePage.toFixed(2)+'ms</td><td>'+percs.basePage+'%</td></tr>';
		h += '<tr><th>Front End</th><td>'+dbg_progress_bar(percs.frontEnd)+'</td><td>'+frontEnd.toFixed(2)+'ms</td><td>'+percs.frontEnd+'%</td></tr>';
		h += '<tr><th>Page Load</th><td></td><td>'+pageloadtime.toFixed(2)+'ms</td><td>100%</td></tr>';
		
	jQuery('#dbg_frontend').html(h);	
}

function dbg_percentage(v,total){
	var perc = ((v/total) * 100).toFixed(2); 
	return perc;
}

function dbg_cwv(){
	jQuery('#dbg_cwv').html('');

	let cls = 0;
	new PerformanceObserver((entryList) => {
	  for (const entry of entryList.getEntries()) {
		if (!entry.hadRecentInput) {
		  cls += entry.value;
		  //console.log(entry);
		  //console.log('Current CLS value:', cls, entry);
		}
	  }
	}).observe({type: 'layout-shift', buffered: true});
	
	jQuery('#dbg_cwv').append(`<div><b>Cumulative Largest Shift</b> ${cls.toFixed(2)}</div>`);

	let lcp = '';
	new PerformanceObserver(function(entryList){
		for(const entry of entryList.getEntries()){
			var lcp_time = entry.renderTime || entry.loadTime;
			lcp = entry.name+' '+lcp_time.toFixed(2);
	  		//var el = entry.element;
			//console.log('<div><b>Largest Content Paint</b> candidate:', entry.startTime.toFixed(2), entry);
			//el.id;
			//el.outerHTML
			//If Element is Image
			//If Element is text then font related
			var msg = `<div><b>Largest Content Paint Candidate</b>: Start:${entry.startTime.toFixed(2)} Load Time:${entry.loadTime}
				<br/>${entry.name} ${entry.url ?? ''} 
			</div>`;
			jQuery('#dbg_cwv').append(msg);
		}
	}).observe({type: 'largest-contentful-paint', buffered: true});
	
	//$('#dbg_lcp').html(lcp);
	//Should be in head?
	new PerformanceObserver(function(entryList){
		var fidEntry = entryList.getEntries()[0];
		var fid = fidEntry.processingStart - fidEntry.startTime;
		//console.log('FID:',fid,fidEntry);
		//console.log("First Input Delay: " + fid);
		jQuery('#dbg_cwv').append(`<div><b>First Input Delay</b>: ${fid.toFixed(2)}</div>`);	
	}).observe({ type: "first-input", buffered: true });

	//Calculate TBT per script?

	var totalBlockingTime = 0;
	var observer = new PerformanceObserver(function (list) {
	  let perfEntries = list.getEntries();
	  //console.log('perfEntries',perfEntries);
	  for (const perfEntry of perfEntries) {
	    totalBlockingTime += perfEntry.duration - 50;
	  }
	  jQuery('#dbg_cwv').append('<b>Total Blocking Time</b> '+totalBlockingTime)
	});
	observer.observe({ type: "longtask", buffered: true });

	//Interaction to Next Paint
}

//See what sweet-alert comes up as for non-blocking css when defered
//We want to call out tracking, so anything that isn't local or CDN (Just consider External)
function dbg_resources(){
	var blockingCount = 0;
	var durationByType = {};
	var durationBlockingByType = {};
	var bytesByType = {};
	var resources = performance.getEntriesByType('resource'); //May not all be loaded yet? Still some 0's
	var index = 0;

	var PAGELOADTIME = performance.getEntriesByType("navigation")[0].duration;

	//TODO: Indicate blocking before LCP or fully loaded, indicate % impact
	var html = `<div id=dbg_resource_top></div><table class=wpdb_table style="width:100%;table-layout:fixed">
		<thead>
			<tr>
				<th width=5%>Index</th>
				<th width=5%>Origin</th>
				<th width=65%>Name</th>
				<th width=10%>Type</th>
				<th width=5%>Blocking</th>
				<th width=5%>Transferred</th>
				<th width=5% title="Size After Decompression">Filesize</th>
				<th width=5%>Protocol</th>
				<th width=5%>Time Taken (ms)</th>
		</thead>
		<tbody>
	`;
	var origin = document.location.origin;
	var total_blocking_time = 0;
	
	console.log(resources);

	for(var i=0; i<resources.length; i++){
		var r = resources[i];
		if(r.connectEnd > PAGELOADTIME){
			//console.log('Skipping entry, start after load: '+r.name);
			continue;
		}
		var type = r.name.indexOf(origin) === 0 ? 'Local' : 'Remote';
		//Should detect CDN, WP Rocket or other source? set variable css if used

		bytesByType[r.initiatorType] = (bytesByType[r.initiatorType] || 0) + r.decodedBodySize;
		durationByType[r.initiatorType] = (durationByType[r.initiatorType] || 0) + r.duration;

		if(r.renderBlockingStatus == 'blocking'){
			blockingCount++;
			durationBlockingByType[r.initiatorType] = (durationBlockingByType[r.initiatorType] || 0) + r.duration;
		}
		else{

		}
		var name = r.name.split('?')[0];
		if( name.indexOf('data:') !== -1 ){
			name = 'Data URI: '+name.split('base64')[0];
		}
		//r.initiatorType css means intiated by css, link is the actual css
		
		var size  = formatBytes(r.transferSize);
		var decoded_size = formatBytes(r.decodedBodySize);
		if(size == 0){
			size = '';//formatBytes(r.encodedBodySize);
			//transfer_size = '';
		}
		
		if(r.renderBlockingStatus == 'blocking'){
			total_blocking_time += r.duration;
			//Todo: TBT by Type
		}

		var cls = r.renderBlockingStatus == 'blocking' ? 'blocking':'non-blocking';

		html += `<tr class="${cls}">
				<td>${index++}
				<td>${type}</td>
				<td style="word-wrap:break-word"><span title="${r.name}">${name}</span></td>
				<td>${r.initiatorType}</td>
				<td>${r.renderBlockingStatus == 'blocking' ? '🧱':'' }</td>
				<td>${ size }</td>
				<td>${ decoded_size }</td>
				<td>${r.nextHopProtocol}</td>
				<td>${r.duration.toFixed(0)}</td>
		</tr>`;
	}
	//console.log(PAGELOADTIME);
	//Need Blocking/Non-Blocking by type so we can compare % of blocking (Page Load)
	var typeHtml = `<table id="dbg_resources_table">
						<thead>
							<tr>
								<th>Type</th>
								<th>Size</th>
								<th>Time</th>
								<th>Blocking</th>
								<th>% of Load</th>
							</tr>
						</thead>
						<tbody>`;
	for(var k in bytesByType){
		var type = k.replace('css','CSS Initiated').replace('link','css')
		var dur	 = durationByType[k];///1000;
		var durBlock = durationBlockingByType[k] || 0;
		var perc = (durBlock / PAGELOADTIME)*100;
		typeHtml += `<tr><th>${type}<td>${formatBytes(bytesByType[k])}<td>${(dur).toFixed(4)}<td>${(durBlock).toFixed(4)}<td>${dbg_progress_bar(perc)}`;
	}
	typeHtml += '</table>';
	//size 0 may mean cached or blocked
	html = `<div><div><span id=dbg_lcp></span><span id=dbg_fid></span><span id=dbg_cls></span></div><h2>Blocking: ${blockingCount} <span style="float:right">Non-Blocking: ${resources.length - blockingCount}</span></h2></div>`+html;
	jQuery('#dbg_resources').html(typeHtml+html);
}

function dbg_progress_bar(perc){
	var p = parseFloat(perc).toFixed(2);
	return `<div class=dbg-progress><progress max=100 value="${p}">${p}</progress><label>${p}</label></div>`; 
}

/*
This is a quick and dirty colorizer
We don't want to load a seperate library like prism or rainbow to do this
Just some basic syntax hilighting
*/

function colorizeQuery(h){
	h = h.replace(/([!=])/gi,	'<i class=op>$1</i> ');

	h = h.replace(/ SQL_CALC_FOUND_ROWS /gi,		' <i class=op>SQL_CALC_FOUND_ROWS</i> ');

	h = h.replace(/SELECT /gi,		'<i class=mn>SELECT</i> ');
	h = h.replace(/INSERT INTO /gi, '<i class=mn>INSERT INTO</i> ');
	h = h.replace(/DELETE /gi, 		'<i class=mn>DELETE</i> ');
	h = h.replace(/FROM /gi,		'<br class=fmt/><i class=mn>FROM</i> ');
	h = h.replace(/\wVALUES /gi,		'<br class=fmt/><i class=mn>VALUES</i> ');
	h = h.replace(/LIMIT /gi,		'<br class=fmt/><i class=mn>LIMIT</i> ');
	h = h.replace(/ORDER BY/gi ,	'<br class=fmt/><i class=mn>ORDER BY</i> ');
	h = h.replace(/GROUP BY/gi ,	'<br class=fmt/><i class=mn>GROUP BY</i> ');
	h = h.replace(/LEFT JOIN /gi,	'<br class=fmt/><i class=mn>LEFT JOIN</i> ');
	h = h.replace(/RIGHT JOIN /gi,	'<br class=fmt/><i class=mn>RIGHT JOIN</i> ');
	h = h.replace(/INNER JOIN /gi,	'<br class=fmt/><i class=mn>INNER JOIN</i> ');
	h = h.replace(/OUTER JOIN /gi,	'<br class=fmt/><i class=mn>OUTER JOIN</i> ');
	h = h.replace(/JOIN /gi,		'<br class=fmt/><i class=mn>JOIN</i> ');
	h = h.replace(/WHERE /gi,		'<br class=fmt/><i class=cnd>WHERE</i> ');		
	h = h.replace(/'(.*)'/gi,		'<i class=str>\'$1\'</i> ');
	h = h.replace(/ ON /gi,			' <i class=mn>ON</i> ');
	h = h.replace(/ AS /gi,			' <i class=mn>AS</i> ');
	h = h.replace(/ OR /gi,			' <i class=mn>OR</i> ');
	
	h = h.replace(/ ASC /gi,		' <i class=mn>ASC</i> '); //May also end the string
	h = h.replace(/ DESC /gi,		' <i class=mn>DESC</i> ');//May also end the string
	h = h.replace(/ IN /gi,			' <i class=mn>IN</i> ');
	h = h.replace(/\sAND /gi,		'<br class=fmt><i class=mn> AND </i> ');
	h = h.replace(/`([a-zA-Z0-9_]+)`/gi,			'<i class=op>`$1`</i>');
	h = h.replace(/ LIKE /gi,		'<i class=mn> LIKE </i> ');			
	h = h.replace(/ FOUND_ROWS\(\)/gi,'<i class=op> FOUND_ROWS()</i> ');
	h = h.replace(/SHOW VARIABLES/gi,	'<i class=mn>SHOW VARIABLES </i><br/> ');
	//h = h.replace(/\w+\(\)/gi,			'<i class=OP>$1()</i><br/> ');
	h = h.replace(/\s(\d+)\s/gi,		' <i class=int>$1</i> ');
	return h;
}

function colorize_sql(){
	jQuery('#dbg_db code.sql').each(function(){
		var h = jQuery(this).html();
		h = colorizeQuery(h);
		//hide br's depending on length
		jQuery(this).html(h);	
	});
}
function colorize_php(){

}

function formatBytes(bytes,decimals) {
	if(bytes == 0) return '0';
	var k = 1024,
		dm = decimals || 2,
		sizes = ['Bytes', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB'],
		i = Math.floor(Math.log(bytes) / Math.log(k));
	return parseFloat((bytes / Math.pow(k, i)).toFixed(dm)) + ' ' + sizes[i];
 }

