<?php 
$cron 		= _get_cron_array();
$schedules 	= wp_get_schedules();
?>

<h2>Available Recurrance Schedules</h2>
<table>
	<thead>
		<tr>
			<th>Name</th>
			<th>Display</th>
			<th>Interval Timestamp</th>
			<th>Interval</th>
		</tr>
	</thead>
	<?php foreach($schedules as $key=>$s) :?>
		<tr>
			<td><?=$key?></td>
			<td><?=$s['display']?></td>
			<td><?=$s['interval']?></td>			
			<td><?=human_time_diff( 0, $s[ 'interval' ] )?></td>
		</tr>
	<?php endforeach; ?>
</table>

<h2>Scheduled Events</h2>
<h3>Server Time: <?=date('n/j/Y @ g:iA')?> <?=date_default_timezone_get()?></h3>
<table class='wp-list-table widefat fixed'>
	<thead>
		<tr>
			<th>Hook</th>
			<th>Schedule</th>
			<th>Next Due Timestamp</th>
			<th>Due In</th>
			<th>args</th>
		</tr>
	</thead>
	<tbody>
<?php foreach($cron as $timestamp=>$arr) : ?>
		<?php foreach($arr as $name=>$c): ?>
			<?php foreach($c as $job): ?>
				<tr>
					<td class=cGreen><?php echo $name?></td>
					<td class=cPurple><?php echo empty($job['schedule']) ? 'Single Run Event':$job['schedule']?></td>
					<td class=cOrange><?=date('n/j/Y @ g:iA',$timestamp)?></td>
					<td class=cBlue><?=human_time_diff($timestamp, $now)?></td>
					<td><?php if(!empty($job['args']))var_dump($job['args'])?></td>
				<?php endforeach; ?>
		<?php endforeach; ?>
<?php endforeach; ?>
</tbody>
</table>
<?php if( !System_Info_Tools::is_windows() ) : ?>
	<h3>Crontab</h3>
	<?php
		$out = System_Info_Tools::run_command("crontab -l");
		echo "<pre>".print_r($out,true)."</pre>";
	?>
<?php endif; ?>