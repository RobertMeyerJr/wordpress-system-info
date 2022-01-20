<?php if ( !defined('ABSPATH') ){ die('-1'); } ?>
<tr><th class=hdr colspan=2><h2><i class='dashicons dashicons-desktop'></i> Server</h2></th></tr>
<tr><th>OS</th><td><?=php_uname()?></td></tr>
<?php if( !empty($linux_details) ) : ?>
	<tr><th>OS Details</th><td><?=$linux_details['Description']?> <?=$linux_details['Codename']?></td></tr>	
<?php endif; ?>
<tr><th>Time / Timezone<td><?php echo date('h:ia').' - '.date_default_timezone_get(); ?>
<?php if(!empty( $_SERVER['USERDOMAIN'] )) : ?>
	<tr><th>Domain</th><td><?=$_SERVER['USERDOMAIN']?></td></tr>
<?php endif; ?>
<?php if( !empty($_SERVER['SERVER_ADDR']) ) : ?>
	<tr><th>IP</th><td><?=$_SERVER['SERVER_ADDR']?></td></tr>
<?php endif; ?>
<tr><th>Webserver</th><td><?=$_SERVER['SERVER_SOFTWARE']?></td></tr>						
<?php if(!empty($mem_info)) : ?>
	<tr><th>Total Memory</th><td><?=$mem_info['MemTotal']?></td></tr>
	<tr><th>Free Memory</th><td><?=$mem_info['MemFree']?></td></tr>
<?php endif; ?>
<?php if(!empty($cpu_info)) : ?>
	<tr><th>CPU</th><td><?php echo $cpu_info['model name']?></td></tr>
	<tr><th>MHz</th><td><?php echo $cpu_info['cpu MHz']?></td></tr>
	<tr><th>CPU Cache</th><td><?php echo $cpu_info['cache size']?></td></tr>
<?php endif; ?>

<?php if(!empty($_SERVER['PROCESSOR_IDENTIFIER'])) : ?>
	<tr><th>Processor</th><td><?=$_SERVER['PROCESSOR_IDENTIFIER']?></td></tr>
<?php endif; ?>
<?php if(!empty($_SERVER['NUMBER_OF_PROCESSORS'])) : ?>
	<tr><th>Processors</th><td><?=$_SERVER['NUMBER_OF_PROCESSORS']?></td></tr>
<?php endif; ?>
<?php if(!empty($cpu_details)) : ?>
	<tr><th>CPU(s)</th><td><?php echo $cpu_details['CPU(s)']?></td></tr>
	<tr><th>Cores Per Socket</th><td><?php echo $cpu_details['Core(s) per socket']?></td></tr>				
	<tr>
		<th>BogoMIPS</th>
		<td>
			<?php echo $cpu_details['BogoMIPS']?>
			<a target=_blank href=https://www.cpubenchmark.net/common_cpus.html>How do I Compare?</a>
		</td>
	</tr>
<?php endif; ?>
