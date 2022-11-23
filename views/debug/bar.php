<?php if ( !defined('ABSPATH') ){ die('-1'); } ?>
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
$show_filters = isset($_GET['filters']);
?>	
<div id=dbg_bar>
	<div class=dbg-nav>
		<div class=resize-bar></div>
		<ul class=tabs id=dbg_nav_tabs>
			<li title="Debug Bar"><a href="#dbg_about">About</a></li>
			<li <?=($current == 'console') ? 'class=current':''?>><a href=#dbg_console>Console</a></li>
			<li <?=($current == 'errors') ? 'class=current':''?>><a href=#dbg_errors>Errors <small class="bdg bgRed"><?php echo $error_count?></small></a></li>		
			<li <?=($current == 'info') ? 'class=current':''?>><a href=#dbg_info>Info</a></li>
			<li><a href=#dbg_qvars>WP Query</a></li>
			<li><a href=#dbg_timeline>Timeline</a></li>
			<?php if($show_filters) : ?>				
				<li><a href=#dbg_filters>Filters</a></li>				
			<?php endif; ?>
			<li><a href=#dbg_db>SQL <small class="bdg bgBlue"><?php echo $query_details;?></small></a></li>
			<?php if(!empty(System_Info::$blocks)):?><li><a href=#dbg_blocks>Blocks</a></li><?php endif; ?>
			<li><a href=#dbg_files>Included Files</a></li>		
			<li><a href=#dbg_mem>Memory</a></li>
			<li><a href=#dbg_cache>Cache</a></li>						
			<li><a href=#dbg_scripts>Scripts &amp; Styles</a></li>
			<li><a href=#dbg_resources>Resources</a></li>
			<li class="ajax_sql"style="display:none"><a href="#dbg_ajax_sql">AJAX SQL <span id=ajax_sql_count class="bdg bgBlue"></span></a></li>
			<li class=right id=close_dbg></li>
			<li class='right stats'<?php echo number_format(memory_get_peak_usage()/1024,0) ?>KB</li>		
			<li class='right stats' title=Total><span class="bdg bgRed TotalTime"></span></li>
			<li class='right stats' title=Browser><span class="bdg bgGreen browserTotalTime"></span></li>
			<li class='right stats' title=Server><span class="bdg bgBlue serverTotalTime"></span></li>
		</ul>	
	</div>
	
	<div class=dbg_body>	
		<div class=panel id=dbg_about><?php include('about.php');?></div>
		<div class="panel <?=($current=='info')?'active':''?>" id=dbg_info><?php include('info.php');?></div>
		<?php if($show_filters) : ?>
			<div class=panel id=dbg_filters><?php include('filters.php')?></div>
		<?php endif; ?>
		<div class=panel id=dbg_qvars><?php include('qvars.php')?></div>
		<div class=panel id=dbg_db><?php include('db.php')?></div>
		<div class=panel id=dbg_ajax_sql><table id=ajax_query_table><thead><tr><th>URL</th><th>SQL</th><th>Type</th><th>Time</th></tr></thead></table></div>
		<div class=panel id=dbg_scripts><?php include('scripts_styles.php'); ?></div>	
		<div class=panel id=dbg_timeline><?php include('timeline.php'); ?></div>	
		<div class=panel id=dbg_cache><?php include('cache.php'); ?></div>
		<div class=panel id=dbg_mem><?php include('memory.php'); ?></div>
		<div class=panel id=dbg_blocks>
			<?php if( !empty(System_Info::$blocks) ){ include('blocks.php'); } ?>
		</div>
		<div class=panel id=dbg_files><?php include('included_files.php'); ?></div>	
		<div class="panel <?=($current=='console')?'active':''?>" id=dbg_console><?php include('console.php');?></div>		
		<div class="panel <?=($current=='errors')?'active':''?>" id=dbg_errors><?php include('errors.php');?></div>
		<div class=panel id=dbg_resources><p>Loading...</p></div>
	</div>
</div>
<script>
<?php $debug_time = microtime(true) - $start_debug;?>
var debug_time = <?php echo number_format($debug_time,2);?>;
</script>
