<?php defined('ABSPATH') or die("Nope!"); ?>
<div id=sysbench_output class=sysbench_output style='display:none;z-index:99999;'>				
	<div id=sysbench_tabs>
		<ul class=sysbench_tab_menu>
			<li><a href=#sysbench_graphs>Graphs</a></li>
			<li><a href=#frontend>Frontend</a></li>
			<li><a href=#sysbench_queries>Queries</a></li>
			<li><a href=#sysbench_files>Files</a></li>
			<li><a href=#sysbench_hooks>Hooks</a></li>
			<li><a href=#cache>Cache</a></li>			
		</ul>						
			<div id=sysbench_graphs>
				<div id=load_bar_area></div>
				<br/>
				<table id=user_performance></table>				
				<table class=hook_times style='float:left'>
					<thead><tr><th>Area<th>Time</tr></thead>									
					<tbody>
						<?php $total = 0; ?>
						<?php foreach(self::$load_time as $hook=>$time) : if(in_array($hook,array('start','stop'))) continue; ?>
							<?php $total += $time; ?>
							<tr><th><?php echo $hook?></th><td><?php echo round($time,4)?></td></tr>
						<?php endforeach; ?>
					</tbody>
					<tfoot><tr><td></td><td><?=round($total,4);?></td></tr></tfoot>
				</table>				
				<table>
					<tr>
						<th>Load Time<td><?php echo round($total_time,2)?> seconds
						<th>Query Time<td><?php echo number_format($wpdb_query_time,4)?> seconds
						<th>CPU<td><?php echo (!System_Info_Tools::is_windows()) ? self::getCpuUsage():'(Unable to retrieve in windows)'; ?>
				<tr>
				<?php 
					$qo = get_queried_object();
					if ( $qo && isset( $qo->post_type ) )
						$post_type = get_post_type_object( $qo->post_type );
					echo '<th>Query Template:</th><td>' . basename($template);
					echo "<th>Request URI<td>{$_SERVER['REQUEST_URI']}";
					if ( empty($wp->matched_rule) )
						$matched_rule = 'None';
					else
						$matched_rule = $wp->matched_rule;
					echo '<th>Matched Rewrite Rule<td>'.esc_html( $matched_rule );
					
					if(!empty($bp) && !empty($bp->current_component)){
						echo '<tr><th>Buddypress<';
						echo '<td>current_component ' . esc_html( $bp->current_component );
						echo '<td>current_action ' . esc_html( $bp->current_action );
					}
					
					$usage 			= System_Info_Tools::formatBytes( memory_get_peak_usage() );
					$core_usage 	= System_Info_Tools::formatBytes( self::$_CORE_MEM_USAGE );
					$current_usage 	= System_Info_Tools::formatBytes( memory_get_usage() );
				?>	
					<tr><th>Current Memory Usage<td><?php echo $current_usage?>
					<th>Peak<td><?php echo $usage?>
					<th>WP Core Memory Usage<td><?php echo $core_usage?>				
				</table>
				<table>
					<tr>
						<td><div id=load_gauge></div> </td>
						<td><div id=load_chart1></div></td>
						<td><div id=load_chart2></div></td>
					</tr>
					<tr><td colspan=3><div id=plugin_chart></div></td></tr>
				</table>				
			</div>
			<div id=frontend>							
				<div id=bench_resources>
					<ul>
						<li><a href=#si_scripts>Scripts</a></li>
						<li><a href=#si_inline_scripts>Inline Scripts</a></li>
						<li><a href=#si_styles>CSS</a></li>
						<li><a href=#si_images>Images</a></li>
					</ul>
					<div id=si_scripts><table style='width:90%'></table></div>
					<div id=si_inline_scripts><table style='width:90%'></table></div>
					<div id=si_styles><table style='width:90%'></table></div>
					<div id=si_images><table style='width:90%'></table></div>
				</div>
			</div>
			<div id=sysbench_queries>
				<h2><?php echo get_num_queries()?> SQL Queries took <?php echo number_format($wpdb_query_time,4)?> seconds</h2>
				<table class='datatable query_display'>
					<thead><tr><th>Time<th>Query<th>Backtrace<th>&nbsp;</thead>
					<tbody>
					<?php foreach($wpdb->queries as $q) : ?>								
					<?php list($query, $elapsed, $debug) = $q; ?>
						<tr>
							<td style='width:10%'><?php echo number_format($elapsed, 4)?></td>
							<td style='width:50%' class=query><?php echo $query?></td>
							<td style='width:30%' class=backtrace><?php 
								$qi = explode(',',$debug);
								echo implode('<br/>',$qi);
							?>
							<td style='width:10%'><button class=button-secondary onClick="sysinfo_explain(this);">Explain</button></td>
						</tr>
					<?php endforeach; ?>
					</tbody>
					<tfoot>
						<tr>
							<th style="text-align:right" colspan=3>Total:</th>
							<th></th>
						</tr>
					</tfoot>
				</table>
			</div>
			<div id=sysbench_files>
				<?php 
					$wp_content_dir = str_replace('\\','/',WP_CONTENT_DIR.'/'); 
					echo "<h2>".$wp_content_dir."</h2>";
				?>
				<?php $total_known = array_sum(self::$section);?>
				<label>Total Known Time</label> <?php echo $total_known?><br/>
				
				<table class=datatable>
					<thead>
							<tr>
								<th>Section</th>
								<th>File</th>
								<th>Function</th>
								<th>Count</th>
								<th>Time</th>
							</tr>
					</thead>
					<tbody>
					<?php foreach(self::$_profile as $section=>$data) :?>											
							<?php foreach($data as $file=>$data) : ?>
								<?php foreach($data as $function=>$t) : ?>												
										<tr>
											<td><?php echo $section?></td>														
											<td><?php echo str_replace($wp_content_dir,'',$file)?></td>
											<td><?php echo $function?></td>
											<td><?php echo self::$_function_count[$section][$file][$function]?></td>
											<td class=time><?php echo number_format($t,5)?></td>
										</tr>
									<?php endforeach; ?>
							 <?php endforeach; ?>
					<?php endforeach; ?>							
					</tbody>
					<tfoot>
						<tr>
							<th style="text-align:right" colspan=4>Total:</th>
							<th></th>
						</tr>
					</tfoot>
				</table>
		</div>
		<div id=sysbench_hooks>							
			<?php d(self::$_profile); ?>					
			<table>
				<thead>
					<tr><th>Hook<th>Type<th>Time
				<tbody>
			<?php foreach(self::$_hook_history as $h) :?>
				<tr>
					<td><?php echo $h['tag']?>
					<td><?php echo $h['type']?>
					<td><?php echo $h['time']?>
			<?php endforeach; ?>
			</table>
		</div>
		<div id=cache>
			<?php 
				if( !empty($wp_object_cache) && method_exists($wp_object_cache,'stats') )
					$wp_object_cache->stats();
			?>
			<?php if( extension_loaded( 'wincache' ) ) : ?>
				Wincache
				<?php 
					s( wincache_ucache_meminfo() );
					s( wincache_ucache_info() );
					s( wincache_scache_info() );
					s( wincache_ocache_fileinfo() );
					s( wincache_ocache_meminfo() );
					s( wincache_fcache_fileinfo() );
					s( wincache_fcache_meminfo() );
				?>
			<?php elseif( extension_loaded( 'apc' ) ) : ?>
				APC
				<?php 
					s( apc_cache_info('user') );
					s( apc_cache_info('filehits') );
					s( apc_cache_info() );
				?>
			<?php else: ?>
				No Cache found (Currently only Wincache and APC are supported)
			<?php endif; ?>
		</div>
	</div>
<div class=clear></div>
<script>
var input_time = <?php echo round($total_time,2)?>;
var total_time = <?=$total_time?>;
var plugin_data_arr = [
		[ 'Plugin',
			<?php 
				$keys = array_keys($plugin_times);
				$keys = array_map(function($v){ return "'{$v}'"; },$keys);
				echo implode(',', $keys); 
			?>,
			'Theme <?php echo wp_get_theme()?>', 
			'(Other)',
			'(Benchmarking)',
			'Wordpress Core'
		],
		[
			'',
			<?php if( !empty($plugin_times) ) echo implode(',', $plugin_times); ?>,
			<?php echo self::$section['Theme']?:0 ?>,
			<?php echo self::$section['Other']?:0 ?>,
			<?php echo self::$section['System Info']?:0 ?>,
			<?php echo self::$load_time['Core Load']?>
		]					
	];
var bench_time = {
	time_plugin 	: <?php echo self::$load_time['plugins_loaded']?>,
	time_theme 		: <?php echo self::$load_time['setup_theme']?>,
	time_init 		: <?php echo self::$load_time['init']?>,
	time_query 		: <?php echo $wpdb_query_time?>
}			
var pie_load = [ 
				<?php foreach(self::$section as $name=>$time):?>
					["<?php echo $name?> \t<?php echo round($time,2)?>",<?php echo $time?>],
				<?php endforeach; ?>
					["WP Load",<?php echo self::$load_time['Core Load']?>]								
				];
var bench_pie1 = [ 
					['Load Plugins '+bench_time.time_plugin, 	bench_time.time_plugin		], 
					['Load Theme '+bench_time.time_theme,		bench_time.time_theme		], 
					['Init '+bench_time.time_init,			bench_time.time_init		], 
					<?php if(!empty(self::$load_time['the_content'])) : ?>
						['Process Content <?php echo self::$load_time['the_content']?>', <?php echo self::$load_time['the_content']?>	], 
					<?php endif; ?>
					['SQL Queries', 	bench_time.time_query]
				];
</script>