<?php
global $wpdb,$wp_version;
defined('ABSPATH') or die("Nope!");
		
add_thickbox();
		
#getrusage() ?

$start = microtime(true);		

function explode_array_sep($arr, $seperator=":"){
	$out = [];
	if( !empty($arr) ){
		foreach($arr as $row){
			if( !empty($row) ){
				list($prop,$val) = explode($seperator, $row, 2);
				$out[trim($prop)] = trim($val);
			}
		}
	}
	return $out;
}

try{		
	
	if(strtoupper(substr(PHP_OS, 0, 3)) !== 'WIN'){ //Don't do this on windows
		if( System_Info_Tools::exec_enabled() ){
			$uptime 		= System_Info_Tools::run_command('uptime');			
			$linux_details 	= System_Info_Tools::run_command('lsb_release -a'); 
			$mem_info 		= System_Info_Tools::run_command('cat /proc/meminfo');
			$cpu_info 		= System_Info_Tools::run_command('cat /proc/cpuinfo'); 
			$cpu_details 	= System_Info_Tools::run_command('lscpu'); 
			
			$mem_info 			= explode_array_sep($mem_info);
			$cpu_info 			= explode_array_sep($cpu_info);
			$cpu_details 		= explode_array_sep($cpu_details);
			$linux_details 		= explode_array_sep($linux_details);			
		}
	}

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
}

?>		
<link rel=stylesheet href="<?=plugin_dir_url(__DIR__.'../')?>media/css/Dashboard.css"/>
<div class=wp_system_info>
<table id=wp_system_info>
	<?php include(__DIR__.'/dashboard/server.php'); ?>		
	<?php include(__DIR__.'/dashboard/usage.php'); ?>	
	<?php include(__DIR__.'/dashboard/wordpress.php'); ?>
	<?php include(__DIR__.'/dashboard/php.php'); ?>
</table>
<div class=bottom>
	<a href="#TB_inline?width=600&height=400&inlineId=total_details_extra" class="thickbox btn-primary btn">View Details</a>		
	<span>
		<?php 
			$elapsed = number_format(microtime(true) - $start,4);
			echo "<small>Time Taken: {$elapsed}s</small>";
		?>
	</span>
</div>
</div>
<?php include(__DIR__.'/dashboard/details.php'); ?>