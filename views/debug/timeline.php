<?php 
global $wp_actions;

$WP_CORE_TIME = number_format(SI_START_TIME - $_SERVER['REQUEST_TIME_FLOAT'], 4);

$total_time = number_format(microtime(true) - $_SERVER['REQUEST_TIME_FLOAT'], 4);

$query_time = number_format($total_query_time,4);

$action_times = System_Info::getActionTimes();

$times = System_Info::getActionStartEnd();

?>

<h2>Browser Measurements</h2>
<table>
	<thead>
		<tr>
			<th>Part</th>
			<th>Time</th>
			<th>Percentage</th>
			<th>
		</tr>
	</thead>
	<tbody id=dbg_frontend>
	</tbody>
</table>

<h2>Wordpress Measurements</h2>
<table>
	<tr><th>WP Core Time</th><td><?php echo $WP_CORE_TIME?></td>
	<tr><th>Query Time</th><td><?php echo $query_time?></td>
	<tr><th>Request Time</th><td><?php echo $total_time?></td>	
</table>
<h3>Action Timeline</h3>	
<table>
	<thead>
		<tr>
			<th>Action
			<th>Start
			<th>End
			<th>Duration
	</thead>
	<tbody>
<?php foreach($times as $action=>$t) : ?>
		<tr>
			<th><?php echo $action?>
			<td><?php echo number_format(	$t['start'] - $_SERVER['REQUEST_TIME_FLOAT'], 4); ?>
			<td><?php echo number_format(	$t['end'] - $_SERVER['REQUEST_TIME_FLOAT'], 4);?>
			<td><?php echo number_format( ($t['end'] - $t['start']), 4); ?>
	<?php endforeach; ?>
</table>

<?Php if(!empty(System_Info::$remote_get_urls)) : ?>
	<h3>Remote URL Requests</h3>
	<table>
		<?php foreach(System_Info::$remote_get_urls as $url) : ?>
			<tr><td><?=$url?>
		<?php endforeach; ?>
	</table>
<?php endif; ?>

