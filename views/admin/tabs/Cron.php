<?php if ( !defined('ABSPATH') ){ die('-1'); } ?>
<?php 
global $wp_filter;

$cron 		= _get_cron_array();
$schedules 	= wp_get_schedules();
?>

<h2>Scheduled Events</h2>
<h3>Server Time: <?=date('n/j/Y @ g:iA')?> <?=date_default_timezone_get()?></h3>
<table class='wp-list-table widefat fixed striped'>
	<thead>
		<tr>
			<th>Hook</th>
			<th style='width:10%'>Args</th>
			<th>Schedule</th>
			<th>Next Due Timestamp</th>
			<th style='width:10%'>Due In</th>
			<th>Actions</th>			
		</tr>
	</thead>
	<tbody>
<?php foreach($cron as $timestamp=>$arr) : ?>
		<?php foreach($arr as $name=>$c): ?>
			<?php foreach($c as $job): ?>
				<tr>
					<td class=cGreen><?php echo $name?></td>
					<td><?php if(!empty($job['args']))var_dump($job['args'])?></td>
					<td class=cPurple><?php echo empty($job['schedule']) ? 'Single Run Event':$job['schedule']?></td>
					<td class=cOrange><?=date('n/j/Y @ g:iA',$timestamp)?></td>
					<td class=cBlue>
						<?php 
							$past=false;
							if( $timestamp < time() ){
								$past=true;
							}
						?>
						<?php echo human_time_diff($timestamp). ($past ? ' ago':'') ?>
					</td>
					<td>
						<?php if( !empty($wp_filter[$name]) ) : ?>
							<?php foreach( $wp_filter[$name]->callbacks as $pri=>$cbs ) : ?>
								Priority: <?=$pri?><br/>
									<?php foreach($cbs as $name=>$cb) : ?>
										<?php $func = $cb['function']; ?>
										<?php if(is_array($func)) : ?>
											<span class=cBlue><?php echo is_object($func[0]) ? get_class($func[0]) : $func[0]?></span><?php if(!empty($func[1])) : ?>::<span class=cGreen><?=$func[1]?>()</span><?php endif; ?>
										<?php else: ?>											
											<span class=cGreen><?=$func?>()</span>
										<?php endif; ?>										
									<?php endforeach; ?>
							<?php endforeach; ?>
						<?php endif; ?>
					</td>
					
				<?php endforeach; ?>
		<?php endforeach; ?>
<?php endforeach; ?>
</tbody>
</table>

<h2>Available Recurrance Schedules</h2>
<table class='wp-list-table widefat fixed striped'>
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
