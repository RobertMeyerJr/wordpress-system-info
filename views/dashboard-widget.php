<?php
global $wpdb,$wp_version;
defined('ABSPATH') or die("Nope!");
		
add_thickbox();
		
#$_SERVER['NUMBER_OF_PROCESSORS']	#Defined on Windows

#getrusage() ?

$start = microtime(true);		


function explode_array_sep($arr, $seperator=":"){
	$out = [];
	foreach($arr as $row){
		if( !empty($row) ){
			list($prop,$val) = explode($seperator, $row, 2);
			$out[trim($prop)] = trim($val);
		}
	}
	return $out;
}

try{		
	$mem = memory_get_usage();

	$free_disk_bytes 	= disk_free_space( $_SERVER['DOCUMENT_ROOT'] );
	$total_disk_bytes 	= disk_total_space( $_SERVER['DOCUMENT_ROOT'] );

	$free_disk 	= System_Info_Tools::formatBytes($free_disk_bytes);
	$total_disk = System_Info_Tools::formatBytes($total_disk_bytes);

	$used_disk 	= $total_disk_bytes - $free_disk_bytes;

	$perc_used = number_format($used_disk * 100 / $total_disk_bytes,2);

	if(strtoupper(substr(PHP_OS, 0, 3)) === 'WIN'){
		//Windows
		#exec("net statistics workstation | find 'Statistics since'", $uptime);
		#d($uptime);
	}
	else{
		
		//Assume Linux, check if can exec
		if( System_Info_Tools::exec_enabled() ){
			System_Info_Tools::run_command('uptime', 				$uptime);			
			System_Info_Tools::run_command('lsb_release -a', 		$linux_details); #TODO: Check
			System_Info_Tools::run_command('cat /proc/meminfo',		$mem_info);
			System_Info_Tools::run_command('cat /proc/cpuinfo',		$cpu_info); 
			System_Info_Tools::run_command('lscpu',					$cpu_details); 
			
			$mem_info 			= explode_array_sep($mem_info);
			$cpu_info 			= explode_array_sep($cpu_info);
			$cpu_details 		= explode_array_sep($cpu_details);
			$linux_details 		= explode_array_sep($linux_details);			
		}
	}

	if( function_exists('sys_getloadavg') ){
		$load_avg = sys_getloadavg();
	}

	$mem_usage = round( memory_get_usage() / 1024 / 1024, 2 );

	$db_size = $wpdb->get_row($wpdb->prepare("SELECT table_schema,
			SUM( data_length + index_length ) AS `Size`,
			SUM( data_free ) AS  `Free`
			FROM information_schema.TABLES
			WHERE table_schema = %s
			GROUP BY table_schema",DB_NAME));
	
	$db_used = System_Info_Tools::formatBytes($db_size->Size);
	$db_free = System_Info_Tools::formatBytes($db_size->Free); 

	$theme = wp_get_theme();
}catch(Exception $e){
	echo "<h2>Error Getting System Information</h2>";
	//$e->getMessage();
}

?>		
<link rel=stylesheet href="<?=plugin_dir_url(__DIR__.'../')?>media/css/Dashboard.css"/>
<div class=wp_system_info>
<table id=wp_system_info>
	<tr><th class=hdr colspan=2><h2><i class='dashicons dashicons-desktop'></i> Server</h2></th></tr>
	<tr><th>OS</th><td><?=php_uname()?></td></tr>
	<?php if( !empty($linux_details) ) : ?>
		<tr><th>OS Details</th><td><?=$linux_details['Description']?> <?=$linux_details['Codename']?></td></tr>	
	<?php endif; ?>
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
				<?php echo $cpu_details['BogoMIPS']?><br/>
				<a target=_blank href=https://www.cpubenchmark.net/common_cpus.html>How do I Compare?</a>
			</td>
		</tr>
	<?php endif; ?>
		
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
	<tr><th>Disk Usage</th><td style="text-align:center;">
		<meter high=75 value="<?php echo $perc_used?>" max=100></meter> 
		<?php echo $perc_used?>%
	</td></tr>
	
	
	<!-- Wordpress -->
	<tr><th class=hdr colspan=2><h2><i class='dashicons dashicons-wordpress'></i> WordPress</h2></th></tr>
	<tr><th>Version</th><td><?php echo $wp_version?></td></tr>			
	<tr><th>Document Root</th><td><?php echo $_SERVER['DOCUMENT_ROOT']?></td></tr>
	<tr><th>Database</th><td><?php echo DB_NAME?></td></tr>
	<tr><th>WP Cache<td><?=(defined('WP_CACHE') && WP_CACHE == 1) ? '<span class=cGreen>✔</span>':'<span class=cRed>✘</span>' ?>
	<tr><th>DB Size</th><td><?php echo "{$db_used}, {$db_free} Free" ?></td></tr>
	<tr><th>Theme</th><td><?php echo $theme->Name?><br/><?php echo $theme->theme_root?></td></tr>
	<tr><th>Allow Search Engines</th><td><?=get_option('blog_public',1) == 1 ? '<span class=cGreen>✔</span>':'<span class=cRed>✘</span>' ?></td></tr>
	<tr><th>Admin Email</th><td><?=get_option('admin_email','')?></td></tr>
	<tr><th>Blog Name</th><td><?=get_option('blogname')?></td></tr>
	<tr><th>Blog Description</th><td>    <?=get_option('blogdescription')?></td></tr>
	<!-- PHP -->
	<tr><th class=hdr colspan=2><h2><i class='dashicons dashicons-admin-generic'></i> PHP</h2></th></tr>
	<tr><th>Version</th><td><?=PHP_VERSION?></td></tr>
	<tr><th>SAPI</th><td><?=php_sapi_name()?></td></tr>
	
	<tr><th>OpCode Cache</th><td>
		<?php if( extension_loaded('Zend OPcache') ): ?>Zend OPcache
		<?php elseif( extension_loaded( 'xcache' ) ) : ?>XCache		
		<?php elseif( extension_loaded( 'apc' ) ) : ?>APC
		<?php elseif( extension_loaded( 'eaccelerator' ) ) : ?>EAccelerator
		<?php elseif( extension_loaded( 'Zend Optimizer+' ) ) : ?>Zend Optimizer+
		<?php elseif( extension_loaded( 'wincache' ) ) : ?>WinCache
		<?php else: ?>None
		<?php endif; ?>
	</td></tr>	
	<tr><th>Max Post</th><td><?=ini_get('post_max_size')?></td></tr>
	<tr><th>Upload Max</th><td><?=ini_get('upload_max_filesize')?></td></tr>
	<tr><th>Memory Limit</th><td><?=ini_get('memory_limit')?></td></tr>			
	<tr><th>Max Time</th><td><?=ini_get('max_execution_time')?></td></tr>
	<tr><th>User</th><td><?=getenv('USERNAME') ?: getenv('USER');?></td></tr>
	<tr><th>Disabled Functions</th><td>
		<?php $disabled= ini_get('disable_functions')?>
		<span class=breakword><?=(empty($disabled))?'No functions are disabled':$disabled?></span></td></tr>
</table>
<div class=bottom>
	<a href="#TB_inline?width=600&height=400&inlineId=total_details_extra" class="thickbox">View Details</a>	
	<span>
		<?php 
			$elapsed = number_format(microtime(true) - $start,4);
			echo "<small>Time Taken: {$elapsed}s</small>";
		?>
	</span>
</div>
</div>
<div id="total_details_extra" style="display:none;">     
	<div class=total_details_extra>
	<pre>
		<?php if(!empty($uptime)) : ?>
			<?php print_r( $uptime ); ?>
		<?php endif; ?>
	</pre>
	<h2>$_SERVER</h2>
	<table>
		<?php foreach($_SERVER as $k=>$v):?>
			<tr><th><?=$k?></th><td><?=$v?></td></tr>
		<?php endforeach;?>
	</table>
	<?php if( !empty($cpu_info_details) ) : ?>
		<h2>CPU</h2>
		<table>
			<?php foreach($cpu_info_details as $k=>$v):?>
				<tr><th><?=$k?></th><td><?=$v?></td></tr>
			<?php endforeach;?>
		</table>
	<?php endif; ?>
	<?php if( !empty($cpu_details) ) : ?>
		<table>
			<?php foreach($cpu_details as $k=>$v):?>
				<tr><th><?=$k?></th><td><?=$v?></td></tr>
			<?php endforeach;?>
		</table>
	<?php endif; ?>
	<?php if( !empty($mem_info) ) : ?>
		<h2>Memory</h2>
		<table>
			<?php foreach($mem_info as $k=>$v):?>
				<tr><th><?=$k?></th><td><?=$v?></td></tr>
			<?php endforeach;?>
		</table>
	<?php endif; ?>		 
	</div>
	
</div>



