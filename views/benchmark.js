/*
if(console.profile)
	console.profile('System Info Load');
http://www.webpagetest.org/runtest.php?url={$url}
*/
console.log('in benchmark.js');

jQuery(window).load(function(){
	console.log('do_benchmarking');
	setTimeout(SI_BenchMark.do_benchmarking, 1000);	
});


SI_BenchMark = {
	pie_width :		375,
	pie_height : 	350,
	startup: 		function(){
		SI_BenchMark.load_google_api();
	},
	google_api_loaded : function(){
		google.load('visualization', 1, {
				packages:['corechart','gauge'],
				callback: SI_BenchMark.google_loaded
			}
		);				
	},
	load_google_api: function(){
		jQuery.getScript('http://www.google.com/jsapi', SI_BenchMark.google_api_loaded);
		jQuery.getScript('http://ajax.aspnetcdn.com/ajax/jquery.dataTables/1.9.4/jquery.dataTables.min.js',function(){
			jQuery('table.datatable').dataTable({
				"bPaginate": false,
				 "bJQueryUI": true,
				 'bRetrieve':true,
				 "sScrollY": "300px",
				 "fnFooterCallback": SI_BenchMark.calc_footer
			});
		});
	},
	pie2 : 			function(){
		var data = new google.visualization.DataTable();
		data.addColumn('string', 'Section');
		data.addColumn('number', 'Time');				
		data.addRows( pie_load );
		var options = {
			'title':'Load Time By Area', 'width':SI_BenchMark.pie_width, 'height':SI_BenchMark.pie_height,
			backgroundColor:'transparent'
		};
		var chart2 = new google.visualization.PieChart(document.getElementById('load_chart2'));
		chart2.draw(data, options);
	},
	pie1 : 			function(){
		var data = new google.visualization.DataTable();
		data.addColumn('string', 'Module');
		data.addColumn('number', 'Time');
		data.addRows(bench_pie1);
		var options = {
			'title':'Hook Time',
			'width':SI_BenchMark.pie_width,
			'height':SI_BenchMark.pie_height,
			backgroundColor:'transparent'
		};
		var chart1 = new google.visualization.PieChart(document.getElementById('load_chart1'));
		chart1.draw(data, options);
	},
	drawGauge : 	function(){
		var data = google.visualization.arrayToDataTable([
			  ['Label', 'Value'],
			  ['Time', input_time],
		]);
		var options = {
		  width: 200, height: 200,
		  redFrom: 6, redTo: 10,
		  yellowFrom: 3, yellowTo: 6,
		  greenFrom:0, 	greenTo:3,
		  minorTicks: 0.25,
		  min: 0, max: 10,
		  backgroundColor:'transparent'
		};
		var chart = new google.visualization.Gauge(document.getElementById('load_gauge'));
		chart.draw(data, options);			
	},
	plugin_chart : 	function(){
		try{				
			var total = 0;
			var data_arr = plugin_data_arr;
			
			for(var i=1;i<data_arr[1].length;i++){
				if(!data_arr[1][i])
					data_arr[1][i] = 0;
				total += parseFloat(data_arr[1][i]);
			}
			var data = google.visualization.arrayToDataTable(data_arr);
			new google.visualization.ColumnChart(document.getElementById('plugin_chart')).draw(
				data,{
					chartArea:{width:"70%"},
					title:"Load Time by Area ("+total+")",
					width:  960, 
					height: 650,
					backgroundColor:'transparent'
				}
			);			
		}catch(err){	
			console.log('Error Drawing Plugin Chart');
			console.log(err);
		}				
	},
	performance : 	function(){							
		var now 			= new Date().getTime();
		var t 				= window.performance.timing; 									
		var request_time 	= t.responseEnd - t.navigationStart 									
		var page_load_time 	= t.domInteractive - t.navigationStart;									  
		var dom_load 		= t.domInteractive - t.domLoading;
		var net_done 		= t.responseEnd;
		var latency 		= t.responseEnd - t.fetchStart;
		var dom_complete 	= ((t.domComplete) ? t.domComplete:t.loadEventStart) - t.domLoading;
		var load_event		= t.loadEventEnd - t.loadEventStart;
		var network 		= Math.round(request_time - total_time,0);
		
		//Load Bar
		var app_time	= total_time;	
		var total 		= t.loadEventEnd-t.navigationStart;		
		
		console.info('Total Load Time: '+total);
		var totals = {
			net_1	: (t.connectEnd - t.fetchStart), 					
			app 	: (t.domInteractive - t.responseEnd), 				
			net_2	: (t.responseEnd - t.responseStart), 				
			load 	: (t.domComplete - t.domContentLoadedEventEnd), 	
			render	: (t.loadEventStart - t.domContentLoadedEventStart)
		}
		var percs = {
			net_1 	: 		Math.round(	totals.net_1	/ total * 100),
			proc 	:		Math.round( totals.app 		/ total * 100),
			net_2 	: 		Math.round(	totals.net_2	/ total * 100),
			load 	: 		Math.round(	totals.load 	/ total * 100),
			render	: 		Math.round( totals.render 	/ total * 100)
		}
		
		var load_bar = '<div id=si_load_bar>';
		if(percs.net_1 > 0)
			load_bar += '<span class=net style="width:'+percs.net_1+'%"	title="Net 1 '+totals.net_1+'">'+percs.net_1+'</span>';
		if(percs.proc > 0)		
			load_bar += '<span class=app 	style="width:'+percs.proc+'%"	title="App	'+totals.app+'">'+percs.proc+'</span>';
		if(percs.net_2 > 0)	
			load_bar += '<span class=net 	style="width:'+percs.net_2+'%"	title="Net 2 '+totals.net_2+'">'+percs.net_2+'</span>';
		if(percs.load > 0)	
			load_bar += '<span class=load 	style="width:'+percs.load+'%"	title="Dom Load	'+totals.load+'">'+percs.load+'</span>';
		if(percs.render > 0)	
			load_bar += '<span class=render 	style="width:'+percs.render+'%"	title="Dom Processing '+totals.render+'">'+percs.render+'</span>';
		load_bar += '</div>';
		
		jQuery('#load_bar_area').html( load_bar );
		
		//perfomance.memory		
		var h = '';
		h += '<tr><th>Request Time<td>'+request_time+'ms';
		h += '<tr><th>Network Latency<td>'+latency+'ms';
		h += '<tr><th>Network Time<td>'+network+'ms';
		h += '<tr><th>Perceived Load Time<td>'+page_load_time+'ms';
		h += '<tr><th>Dom Load<td>'+dom_load+'ms';
		h += '<tr><th>Dom Complete<td>'+dom_complete+'ms';
		h += '<tr><th>Load Event<td>'+load_event+'ms';						
		
		jQuery('#user_performance').html( h );	
		/*
			pie chart of
			Server
			Network
			DOM
			domContentLoadedEventEnd							
			DomContentReady							
		var percs = {
			request:	(total-/100)
			network:
			dom			
		};
		
		*/
	},
	hilight_sql : 	function(){
		jQuery('#sysbench_output td.query').each(function(){
			var me = jQuery(this);
			var txt = me.text();
			txt = txt.replace(/(SELECT\s)/ig,'<b class=select>$1</b>');
			txt = txt.replace(/(UPDATE\s)/i,'<b class=update>$1</b>');
			txt = txt.replace(/(DELETE\s)/i,'<b class=delete>$1</b>');
			
			txt = txt.replace(/(\sFROM\s)/ig,'<b class=from>$1</b>' );
			txt = txt.replace(/(\sWHERE\s)/ig,'<b class=where>$1</b>' );
			txt = txt.replace(/(\RIGHT JOIN\s)/ig,'<br/><b class=join>$1</b>' );
			txt = txt.replace(/(\sLEFT JOIN\s)/ig,'<br/><b class=join>$1</b>' );
			txt = txt.replace(/(\sINNER JOIN\s)/ig,'<br/><b class=join>$1</b>' );
			txt = txt.replace(/(\sJOIN\s)/ig,'<br><b class=join>$1</b>' );
			
			//txt = txt.replace(/(<)/ig,'<b class=clause>$1</b>') ;
			//txt = txt.replace(/(>)/ig,'<b class=clause>$1</b>') ;
			txt = txt.replace(/(\s=)/ig,'<b class=clause>$1</b> ') ;
			txt = txt.replace(/(!=)/ig,'<b class=clause>$1</b> ') ;
			txt = txt.replace(/(\sLIKE\s)/ig,'<b class=clause>$1</b> ') ;
			txt = txt.replace(/(\sSET\s)/ig,'<b class=clause>$1</b> ') ;
			
			txt = txt.replace(/(\sAND\s)/ig,'<b class=clause>$1</b> ') ;
			txt = txt.replace(/(\sON\s)/ig,'<b class=clause>$1</b> ') ;
			txt = txt.replace(/(\sOR\s)/ig,'<b class=clause>$1</b> ') ;
			txt = txt.replace(/(\sGROUP BY\s)/ig,'<br><b class=group>$1</b>') ;
			txt = txt.replace(/(\sORDER BY\s)/ig,'<br><b class=order>$1</b>') ;
			txt = txt.replace(/(\DESC\s)/ig,'<b class=order>$1</b>') ;
			txt = txt.replace(/(\ASC\s)/ig,'<b class=order>$1</b>') ;
			txt = txt.replace(/(\sLIMIT\s)/ig,'<b class=limit>$1</b>') ;
			txt = txt.replace(/('[^']*')/ig,'<b class=string>$1</b> ') ;
			
			txt = txt.replace(/(\sCOUNT\([\w]+\))/ig,'<b class=func>$1</b>') ;
			txt = txt.replace(/(\sSUM)/ig,'<b class=func>$1</b>') ;
			txt = txt.replace(/(\sAVG)/ig,'<b class=func>$1</b>') ;
			txt = txt.replace(/(\sDISTINCT)/ig,'<b class=func>$1</b>') ;
			me.html(txt);
		});
	},
	do_benchmarking : function(){	
		var http_host = location.protocol+'//'+window.location.hostname;
		if(console.profileEnd)
			console.profileEnd();
		if( window.performance )
			setTimeout(SI_BenchMark.performance, 100 );
		var known_images = [];
		jQuery('#sysbench_output').dialog({
			title: 		'System Info - Benchmarking',
			width:		'80%',
			minWidth: 	'600px'
		});
		jQuery('script').each(function(){ 
			var source = jQuery(this).attr('src');
				if( !source ){
					var code = jQuery(this).html();
					source = '<div class=hidden_code> <span>( inline )</span> <pre>'+code+'<pre></div>';
					jQuery('#si_inline_scripts table').append('<tr><th>Script</th><td>'+source);
				}
				else{
					var link = '<a href="'+source+'">'+source+'</a>';
					link = link.replace('/^'+http_host+'/', link);
					jQuery('#si_scripts table').append('<tr><th>Script</th><td>'+link);
				}
		});
		jQuery('link[type="text/css"]').each(function(){ 
			var css = jQuery(this).attr('href');
			var link = '<a href="'+css+'">'+css+'</a>';
			jQuery('#si_styles table').append('<tr><th>CSS</th><td>'+link );
		});
		jQuery('img').each(function(){ 
			var image = jQuery(this).attr('src');
			if(1==2 && jQuery.inArray(image, known_images) !== false ){
				return;
			}
			else{
				known_images.push( image );
				jQuery('#si_images table').append('<tr><th>Image</th><td>'+image );
			}
		});		
		jQuery('#sysbench_tabs').tabs();
		jQuery('#bench_resources').tabs();
		jQuery('.sysbench_tab_menu a:first').click();
		jQuery('.sysbench_tab_menu a').click(function(){
				var tab = jQuery(this).attr('href');
				jQuery('.sysbench_tabs > div').hide();					
				jQuery('.sysbench_tab_menu a').parent().removeClass('active');
				jQuery(tab).show();
				jQuery(this).parent().addClass('active');
				return false;
			});
		
		SI_BenchMark.hilight_sql();						
		SI_BenchMark.load_google_api();
	},	
	calc_footer:	function(nRow, aaData, iStart, iEnd, aiDisplay){
		try{
			var last = aaData[0].length - 1;
			var total = 0;
			for( var i=0 ; i<aaData.length ; i++ ){
				var n = parseFloat( aaData[i][last] );
				total += n; 
			}
			var visible_total = 0;
			for ( var i=iStart ; i<iEnd ; i++ ){
				visible_total += aaData[ aiDisplay[i] ][last]*1;
			}
			var nCells = nRow.getElementsByTagName('th');
			nCells[0].innerHTML = visible_total.toFixed(4)+' / '+total.toFixed(4);
			nCells[1].innerHTML = ((total - visible_total)*100 / 100).toFixed(4);
		}catch(err){
			console.log(err);
		}
	},
	google_loaded : function(){		
		SI_BenchMark.pie1();
		SI_BenchMark.pie2();
		SI_BenchMark.plugin_chart();
		SI_BenchMark.drawGauge( input_time );
	}
};

/*
Idea with this was to loop through each element and check for background-image
this is too slow and not worth it.
*/
function bimages(){
	elems = document.getElementsByTagName('*');
	var nElems = elems.length;
	for ( var i = 0; i < nElems; i++ ) {
	}
}
function sysinfo_explain(element){
	var $td = jQuery(element).parent().parent().find('td.query');
	var sql = $td.text();
	sql_html = $td.html();
	jQuery.ajax({
		url: 	'/wp-admin/admin-ajax.php',
		type:	'POST',
		data: 	{action:'sysinfo_explain_query',sql:sql},
		success:function(h){
			var d = '<div class=sysbench_output>'+sql_html+'<p>'+h+'<p></div>';
			jQuery(d).dialog({
				buttons: { "Ok": function () { jQuery(this).dialog("close"); } },
				close: function (event, ui) { jQuery(this).remove(); },
				modal: true,
				title: 'Query Explained',
				zIndex: 999999999,
				width: '960px'
			});
		}					
	});
}

			