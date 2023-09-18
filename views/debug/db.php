<?php if ( !defined('ABSPATH') ){ die('-1'); } ?>
<?php 

#print_r(System_Info::$query_backtraces);

?>
<h2>
	<?php echo "{$wpdb->num_queries} Queries in {$total_query_time_ms}ms"; ?>	
	<?php if(!empty($query_percentage)) : ?>
		<?php echo "{$query_percentage}% of Request Time "; ?>&nbsp;
		<progress max=100 value="<?php echo $query_percentage?>"></progress>
	<?php endif; ?>
</h2>
<?php
$SQLbyType=[];
$TimebySource=[];
?>
<?php ob_start(); ?>
<?php if(!empty($wpdb->queries)) : ?>
	<div>
		<input type=text id=query_search_value style="width:200px;display:inline-block;">
		<button id=do_query_search type=button>Search</button>
	</div>
	<table class="sortable" id=total_debug_sql>
		<thead>
			<tr>
				<th></th>
				<th>Duration</th>
				<th>Query</th>
				<th>Type</th>
				<th>Records</th>
				<th>Trace</th>
				<th>Source</th>
			</tr>
		</thead>
		<tbody id=query_table>
			<?php $qi=1; foreach($wpdb->queries as $q) :?>
				<?php 
					list($sql, $elapsed, $trace) = $q;
				?>
				<tr>
					<td><?php echo $qi++?></td>
					<td data-sort="<?php echo $elapsed?>"><?php echo number_format($elapsed*1000,2) ?>ms</td>
					<td class=qry>						
						<div class=code_container>
							<code class=sql><?php echo esc_html($sql) ?></code>
						</div>
					</td>
					<td>
						<?php
						$type = strtok($sql,' ');
						echo strtoupper($type);
						?> 
					</td>
					<td><?=$record_count ?? ''?></td>
					<td>
						<ol class=trace>
							<a class=show_trace href="#">Toggle Trace</a>
						<?php 
							$trace_parts = explode(',',$trace);
							foreach($trace_parts as $p){
								echo "<li>".System_Info_Tools::hilight_trace_part($p)."</li>";
							}
						?>
						</ol>
					</td>
					<td>
						<?php 
							if(stripos($trace,'WP_Admin_Bar->initialize')){
								echo "WP Core - Admin";
							}
							else{
								$bt = System_Info::$query_backtraces[md5($sql)] ?? false;
								$source = System_Info_Tools::determine_wpdb_backtrace_source($bt);
								$source = ucwords(str_replace(['-','_'],' ',$source));
								echo $source;
								empty($SQLbyType[$source]) ? $SQLbyType[$source]=1 : $SQLbyType[$source]++;
								empty($TimebySource[$source]) ? $TimebySource[$source] = $elapsed : $TimebySource[$source] += $elapsed;
							}
						?>
					</td>
				</tr>
			<?php endforeach; ?>
		</tbody>
	</table>
	<?php $sqlTable = ob_get_clean(); ?>
	<?php arsort($SQLbyType); ?>
	<table>
		<?php foreach($SQLbyType as $k=>$v) : ?>
			<tr>
				<th><?=$k?>
				<td><?=$v?>
				<td><?=number_format(($TimebySource[$k] ?? 0)*1000,2)?>ms
		<?php endforeach; ?>
	</table>
	<?=$sqlTable;?>
<?php else: ?>
	<p>No Queries Record. Ensure SAVEQUERIES is enabled.
	In wp-config and the line:<br/>
	define('SAVEQUERIES', true );
	</p>
<?php endif; ?>
<script>
jQuery(function($){
	new Tablesort(document.getElementById('total_debug_sql'));
});
</script>
