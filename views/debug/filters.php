<?php 
	$filter_percentage 		= number_format(($total_filter_duration/$time_taken) * 100, 2);
?>
<h2>Filters, Actions
	Duration <?php echo number_format($total_filter_duration, 8)?>ms
	
	<?php if(!empty($filter_percentage)) : ?>
		<?=$filter_percentage?>% of Request Time
		<progress max=100 value="<?=$filter_percentage?>"></progress>
	<?php endif; ?>
</h2>

<table class=widefat>
	<thead>
		<tr>
			<th>Filter</th>
			<!--
			<th>Start</th>
			<th>Stop</th>			
			-->
			<th>Duration</th>
			<th>Calls</th>
		</tr>
	</thead>
	<tbody>
	<?php foreach($GLOBALS['dbg_filter_times'] as $filter=>$duration) : ?>
		<tr>
			<th><?php echo $filter ?></th>			
			<!--
			<td><?php echo number_format($dbg_filter_start[$filter], 8)?>ms</td>
			<td><?php echo number_format($dbg_filter_stop[$filter], 8)?>ms</td>
			-->
			<td><?php echo number_format($duration*1000, 8)?>ms</td>
			<td><?php echo $dbg_filter_calls[$filter]?></td>
		</tr>
	<?php endforeach; ?>
	</tbody>
</table>
