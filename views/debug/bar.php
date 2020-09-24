<!-- --------------------------------------------------------[DEBUG BAR]--------------------------------------------- -->
<?php
global $wpdb,$post,$wp_filter,$timestart,$wp_object_cache,
	$dbg_filter_calls,$SI_Errors,$dbg_filter_stop,$dbg_filter_start;
 
#calculate debug time, Server Side and JS/DOM side
$start_debug 	= microtime(true); 
$tpl_dir 		= get_template_directory();

$total_query_time=0;
if(!empty($wpdb->queries)){
	foreach($wpdb->queries as $q){
		$total_query_time+= $q[1];
	}
}
	
$total_query_time_ms 	= number_format($total_query_time*1000,2);
$time_taken 			= microtime(true) - $_SERVER['REQUEST_TIME_FLOAT'];
$query_percentage 		= number_format(($total_query_time/$time_taken) * 100,2); //Backwards?
$query_details 			= (empty($wpdb->queries)) ? $wpdb->num_queries:"{$wpdb->num_queries} in {$total_query_time_ms}ms";

$files 					= get_included_files();
$included_file_count 	= count($files);

$total_filter_duration 	= array_sum($GLOBALS['dbg_filter_times']);
$filter_details 		= number_format($total_filter_duration*1000,2)."ms";

$error_count = count($SI_Errors);


if( Console::countLog() > 0 ){
	$current = 'console';
}	
elseif( $error_count > 0 ){
	$current = 'errors';
}
else{
	$current = 'info';
}
?>	
<div id=dbg_bar>
	<div class=dbg-nav>
		<div class=resize-bar></div>
		<ul class=tabs>
			<li title="Debug Bar"><a href="#dbg_about">About</a></li>
			<li <?=($current == 'console') ? 'class=current':''?>><a href=#dbg_console>Console</a></li>
			<li <?=($current == 'info') ? 'class=current':''?>><a href=#dbg_info>Info</a></li>
			<li <?=($current == 'errors') ? 'class=current':''?>><a href=#dbg_errors>Errors <small class="bdg bgRed"><?php echo $error_count?></small></a></li>		
			<li><a href=#dbg_timeline>Timeline</a></li>				
			<li><a href=#dbg_db>Queries <small class="bdg bgBlue"><?php echo $query_details;?></small></a></li>
			<li><a href=#dbg_files>Included Files <span class="bdg bgBlue"><?php echo $included_file_count?></span></a></li>		
			<li><a href=#dbg_cache>Cache</a></li>						
			<li><a href=#dbg_scripts>Scripts &amp; Styles</a></li>				
			<li><a href=#dbg_globals>Constants</a></li>
			<li><a href=#dbg_php>PHP</a></li>
			<li><a href=#dbg_system>System</a></li>
			
			<li class=right id=close_dbg><i class=fa-times></i></li>
			<li class='right stats'<?php echo number_format(memory_get_peak_usage()/1024,0) ?>KB</li>		
			<li class='right stats' title=Total><span class="bdg bgRed TotalTime"></span></li>
			<li class='right stats' title=Browser><span class="bdg bgGreen browserTotalTime"></span></li>
			<li class='right stats' title=Server><span class="bdg bgBlue serverTotalTime"></span></li>
		</ul>	
	</div>
	
	<div class=dbg_body>	
		<div class=panel id=dbg_about><?php include('about.php');?></div>		
		<div class="panel <?=($current=='console')?'active':''?>" id=dbg_console><?php include('console.php');?></div>		
		<div class="panel <?=($current=='info')?'active':''?>" id=dbg_info><?php include('info.php');?></div>		
		<div class="panel <?=($current=='errors')?'active':''?>" id=dbg_errors><?php include('errors.php');?></div>
		<div class=panel id=dbg_db><?php include('db.php')?></div>
		<div class=panel id=dbg_scripts><?php include('scripts_styles.php'); ?></div>	
		<div class=panel id=dbg_php><?php include('php.php');?></div>	
		<div class=panel id=dbg_globals><?php include('globals.php');?></div>		
		<div class=panel id=dbg_timeline><?php include('timeline.php'); ?></div>	
		<div class=panel id=dbg_cache><?php include('cache.php'); ?></div>					
		<div class=panel id=dbg_files><?php include('included_files.php'); ?></div>	
		<div class=panel id=dbg_system><?php include('system.php'); ?></div>	
		<div class=panel id=dbg_php><?php include('php.php'); ?></div>	
	</div>
</div>
<script>
<?php $debug_time = microtime(true) - $start_debug;?>
var debug_time = <?php echo number_format($debug_time,2);?>;
</script>
