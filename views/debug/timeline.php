<?php 
$WP_CORE_TIME = number_format(SI_START_TIME - $_SERVER['REQUEST_TIME_FLOAT'], 4);

$total_time = number_format(microtime(true) - $_SERVER['REQUEST_TIME_FLOAT'], 4);

$query_time = number_format($total_query_time,4);
#$times = System_Info::getActionTimes();
#d($times);

$action_times = System_Info::getActionTimes();

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
	<tr><th colspan=2><h3>Action Timeline</h3>
	<?php foreach($action_times as $action=>$t) : ?>
		<tr><th><?php echo $action?><td><?php echo number_format($t,4); ?>
	<?php endforeach; ?>
</table>

