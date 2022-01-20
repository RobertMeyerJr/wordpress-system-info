<?php if ( !defined('ABSPATH') ){ die('-1'); } ?>
<?php
	$mem = memory_get_usage();

	$free_disk_bytes 	= disk_free_space( $_SERVER['DOCUMENT_ROOT'] );
	$total_disk_bytes 	= disk_total_space( $_SERVER['DOCUMENT_ROOT'] );

	$free_disk 	= System_Info_Tools::formatBytes($free_disk_bytes);
	$total_disk = System_Info_Tools::formatBytes($total_disk_bytes);

	$used_disk 	= $total_disk_bytes - $free_disk_bytes;
	$perc_used = number_format($used_disk * 100 / $total_disk_bytes,2);
	
	if( function_exists('sys_getloadavg') ){
		$load_avg = sys_getloadavg();
	}

	$mem_usage = round( memory_get_usage() / 1024 / 1024, 2 );

?>
<tr><th class=hdr colspan=2><h2><i class='dashicons dashicons-chart-pie'></i> Usage</h2></th></tr>
<?php if(!empty($load_avg)) : list($avg1,$avg5,$avg15) = $load_avg; ?>
	<tr>
		<th>Load Avg</th>
		<td>1 Min: <?php echo $avg1?>%<br/>
		5 Min: <?php echo $avg5?>%<br/>
		15Min: <?php echo $avg15?>%
		</td>
	</tr>
<?php endif; ?>

<tr><th>PHP Memory Usage</th><td><?php echo $mem_usage?></td></tr>
<tr><th>Disk Space</th><td><?php echo $free_disk?> Free of <?php echo $total_disk?></td></tr>
<tr><th>Disk Usage</th><td>
	<meter high=75 value="<?php echo $perc_used?>" max=100></meter> 
	<?php echo $perc_used?>%
</td></tr>
