<?php 
global $wp_actions;

$WP_CORE_TIME = number_format(SI_START_TIME - $_SERVER['REQUEST_TIME_FLOAT'], 4);

$total_time = number_format(microtime(true) - $_SERVER['REQUEST_TIME_FLOAT'], 4);

$query_time = number_format($total_query_time,4);



?>

<?php if(!empty(System_Info::$remote_get_urls)) : ?>	
	<h3>Remote URL Requests</h3>
	<table>
		<?php foreach(System_Info::$remote_get_urls as $request) : ?>
			<?php list($start, $end, $url) = $request; ?>
			<tr>
				<td><?=$url?>	
				<td><?=number_format($end-$start,4)?> seconds
		<?php endforeach; ?>
	</table>
<?php endif; ?>

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
		<?php foreach(System_Info::$action_start as $a) : ?>
			<?php 
				list($filter, $start) = $a;
				$end = array_shift( System_Info::$action_end[$filter] );
			?>
			<tr>
				<th><?=$filter?></th>
				<td><?php echo number_format( $start - $_SERVER['REQUEST_TIME_FLOAT'], 4); ?>
				<td><?php echo number_format( $end - $_SERVER['REQUEST_TIME_FLOAT'], 4);?>
				<td><?php echo number_format( ($end - $start), 4); ?>
			</tr>
		<?php endforeach; ?>
</table>


