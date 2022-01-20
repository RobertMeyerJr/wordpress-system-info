<?php if ( !defined('ABSPATH') ){ die('-1'); } ?>
<?php 
global $wp_actions;

$WP_CORE_TIME = number_format(SI_START_TIME - $_SERVER['REQUEST_TIME_FLOAT'], 4);
$total_time = number_format(microtime(true) - $_SERVER['REQUEST_TIME_FLOAT'], 4);
$query_time = number_format($total_query_time,4);

?>
<?php if(!empty(System_Info::$remote_get_urls)) : ?>	
	<h3>Remote URL Requests</h3>
	<table>
		<thead>
			<tr>
				<th>Method</th>
				<th>URL</th>
				<th>Response Code</th>
				<th>Time</th>
			</tr>
		</thead>
		<tbody>
		<?php foreach(System_Info::$remote_get_urls as $req) : ?>
			<tr>
				<td><?=$req['method']?>
				<td><?=$req['url']?>
				<td><?=$req['code']?>
				<td>
					<?php if(empty($end) || empty($start)) : ?>
						Start: <?=$req['start']?> End: <?=$req['end']?>
					<?php else : ?>
						<?=number_format($req['end']-$req['start'],4)?> seconds
					<?php endif; ?>
		<?php endforeach; ?>
	</table>
<?php endif; ?>
<h2>Wordpress Measurements</h2>
<table>
	<tr><th>WP Core Time</th><td><?php echo $WP_CORE_TIME?></td>
	<tr><th>Query Time</th><td><?php echo $query_time?></td>
	<tr><th>Request Time</th><td><?php echo $total_time?></td>	
</table>
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
			<th>Memory</th>
			<th>Time Fired</th>
		</tr>
	</thead>
	<?php foreach(System_Info::$timeline as list($f, $mem, $dur)) : ?>
		<tr>
			<th><?=$f?>
			<td><?=size_format($mem)?>
			<td><?=number_format($dur,5)?>
		</tr>
	<?php endforeach; ?>
</table>

<?php if( isset($_GET['all_actions']) ) : ?>
<h3>Action Timeline</h3>
<?php 
#d(System_Info::$actions);
?>
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
				if( $dur <= 0.0000001 ){
					continue;
				}
			?>
			<tr>
				<th><?php echo $filter?></th>
				<td><?php echo number_format( $start - $_SERVER['REQUEST_TIME_FLOAT'], 4); ?>
				<td><?php echo number_format( $end - $_SERVER['REQUEST_TIME_FLOAT'], 4);?>
				<td><?php echo number_format( ($dur), 8); ?>
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

