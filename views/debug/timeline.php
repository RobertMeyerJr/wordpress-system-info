<?php if ( !defined('ABSPATH') ){ die('-1'); } ?>
<?php 
global $wp_actions;

$NOW = microtime(true);

$WP_CORE_TIME 	= number_format(WP_START_TIMESTAMP - $_SERVER['REQUEST_TIME_FLOAT'], 4);
$total_time 	= number_format($NOW - $_SERVER['REQUEST_TIME_FLOAT'], 4);
$query_time 	= number_format($total_query_time,4);
$wp_plugin_load = number_format(SI_PLUGINS_LOADED - WP_START_TIMESTAMP, 4);

?>
<?php if(!empty(System_Info::$remote_get_urls)) : $total_req_time = 0;?>	
	<h3>Remote URL Requests</h3>
	<table>
		<thead>
			<tr>
				<th>Method</th>
				<th>URL</th>
				<th>Response Code</th>
				<th>Time</th>
				<th>Source</th>
			</tr>
		</thead>
		<tbody>
		<?php foreach(System_Info::$remote_get_urls as $req) : 
			if(empty($end) || empty($start)){
				$req_time = $req['end']-$req['start'];
				$total_req_time += $req_time;
			}
			else{
				$req_time = false;
			}
			?>
			<tr>
				<td><?=$req['method']?>
				<td><?=esc_attr($req['url'])?>
				<td><?=$req['code']?>
				<td>
					<?php if( empty($req_time) ) : ?>
						Start: <?=$req['start']?> End: <?=$req['end']?>
					<?php else : ?>
						<?=number_format($req_time,4)?> seconds
					<?php endif; ?>
				<td>
					<?=System_Info_Tools::determine_wpdb_backtrace_source($req['trace'])?>
		<?php endforeach; ?>
		<tfoot>
			<tr>
				<th colspan=4>Total Time <?=number_format($total_req_time,2)?></th>
			</tr>
		</tfoot>
	</table>
<?php endif; ?>
<h2>Wordpress Measurements</h2>
<table>
	<tr><th>WP Core Time</th><td><?php echo $WP_CORE_TIME?></td><td><?=number_format(($WP_CORE_TIME/$total_time)*100,2)?>%</td>
	<tr><th>Plugin Load</th><td><?php echo $wp_plugin_load?></td><td><?=number_format(($wp_plugin_load/$total_time)*100,2)?>%</td>
	<tr><th>Query Time</th><td><?php echo $query_time?></td><td><?=number_format(($query_time/$total_time)*100,2)?>%</td>
	<tr><th>Setup Theme</th><td>
	<tr><th>Head</th><td>
	<tr><th>Loop</th><td>
	<tr><th>Footer</th><td>
	<tr><th>Request Time</th><td><?php echo $total_time?></td><td></td>
</table>
<?php if(!empty(System_Info::$templates_loaded)) : ?>
<h2>Templates</h2>
<table>
	<thead>
		<th>Template</th>
		<th>Require Once</th>
		<th>Duration</th>
		<th>%</th>
	</thead>
	<tbody>
		<?php $total_tpl_time = 0; ?>
		<?php foreach(System_Info::$templates_loaded as $tpl=>$data) : $r = $data[0]; $time_taken = $r['end'] - $r['start'];
			$total_tpl_time += $time_taken;
			?>
			<tr>
				<th><?=str_replace(WP_CONTENT_DIR,'',$tpl)?></th>
				<td><?=$r['once']?></td>
				<td><?=number_format($time_taken,4)?></td>
				<td class=perc><?php echo number_format(($time_taken/$total_time)*100,4); ?>%</td>
			</tr>
		<?php endforeach; ?>
	</tbody>
	<tfoot>
		<tr>
			<th>Total Time in Templates</th>
			<td></td>
			<td><?=number_format($total_tpl_time,4)?></td>
			<th class=perc><?php echo number_format(($total_tpl_time/$total_time)*100,4); ?>%</th>
		</tr>
	</tfoot>
</table>
<?php endif; ?>
<h2>Browser Measurements</h2>
<table>
	<thead>
		<tr>
			<th>Part</th>
			<th>Percentage</th>
			<th>Time</th>
			<th>
		</tr>
	</thead>
	<tbody id=dbg_frontend>
	</tbody>
</table>
<h2>Important Action Timeline</h2>
<table>
	<thead>
		<tr>
			<th>Action</th>
			<th>Mem</th>
			<th>Mem Used</th>
			<th>Time Fired</th>
			<th>Queries</th>
			<th>Duration</th>
			<th>Count</th>
			<th>%</th>
		</tr>
	</thead>
	<?php
	$timeline_entries = count(System_Info::$timeline);
	?>
	<?php for($i=0;$i<$timeline_entries;$i++) : 
		list($f, $mem, $dur, $queries) = System_Info::$timeline[$i];
		list($f2, $mem2, $dur2, $queries2) = System_Info::$timeline_end[$i];
		$time_taken = $dur2-$dur;
	?>
		<tr>
			<th><?=$f?>
			<td><?=size_format($mem)?>
			<td><?=size_format($mem2-$mem)?>
			<td><?=number_format($dur,5)?>
			<td><?=$queries?> / <?=$queries2-$queries?>
			<td><?=number_format($time_taken,5)?>
			<td><?=$wp_actions[$f] ?? ''?>
			<td class=perc><?php echo number_format(($time_taken/$total_time)*100,4); ?>%</td>
		</tr>
	<?php endfor; ?>
</table>

<?php if( isset($_GET['all_actions']) ) : ?>
<h3>Action Timeline</h3>
<input type=text id=wptd_action_search placeholder="Search by Action">
<table>
	<thead>
		<tr>
			<th>Action
			<th>Start
			<th>End
			<th>Duration
	</thead>
	<tbody id=wptd_action_timeline>
		
		<?php foreach(System_Info::$actions as list($filter, $start, $end) ) : $dur = $end-$start;?>
			<?php
				if( $dur <= 0.00000010 ){
					continue;
				}
			?>
			<tr>
				<th><?php echo $filter?></th>
				<td><?php echo number_format( $start - $_SERVER['REQUEST_TIME_FLOAT'], 4); ?>
				<td><?php echo number_format( $end - $_SERVER['REQUEST_TIME_FLOAT'], 4);?>
				<td><?php echo number_format( $dur, 8); ?>
			</tr>
		<?php endforeach; ?>
</table>
<?php endif; ?>


<script>
jQuery(function($){

	//console.table(performance.getEntriesByType("navigation").toJSON());

	$('#wptd_action_search').keyup( debounce(searchActions, 500) );

	function lcp(){
		new PerformanceObserver(entryList => {
    		console.log(entryList.getEntries());
		}).observe({ type: "largest-contentful-paint", buffered: true });
	}

	function searchActions(){
		var s = $(this).val();
		$('#wptd_action_timeline tr').show();
		if(s.length){
			$('#wptd_action_timeline tr').not(':contains('+s+')').hide();
		}
	}

	function debounce(func, wait, immediate) {
		var timeout;
		return function() {
			var context = this, args = arguments;
			var later = function() {
				timeout = null;
				if (!immediate) func.apply(context, args);
			};
			var callNow = immediate && !timeout;
			clearTimeout(timeout);
			timeout = setTimeout(later, wait);
			if (callNow) func.apply(context, args);
		};
	};
});
</script>

