<?php if ( !defined('ABSPATH') ){ die('-1'); } ?>
<h2>
	<?php echo "{$wpdb->num_queries} Queries in {$total_query_time_ms}ms"; ?>	
	<?php if(!empty($query_percentage)) : ?>
		<?php echo "{$query_percentage}% of Request Time "; ?>&nbsp;
		<progress max=100 value="<?php echo $query_percentage?>"></progress>
	<?php endif; ?>
</h2>
<?php if(!empty($wpdb->queries)) : ?>
	<div>
		<input type=text id=query_search_value style="width:200px;display:inline-block;">
		<button id=do_query_search type=button>Search</button>
	</div>
	<table class="sortable">
		<thead>
			<tr>
				<th></th>
				<th>Duration</th>
				<th>Query</th>
				<th>Type</th>
				<th>Trace</th>
			</tr>
		</thead>
		<tbody id=query_table>
			<?php $qi=1; foreach($wpdb->queries as $q) :?>
				<?php list($sql, $elapsed, $trace) = $q; ?>
				<tr>
					<td><?php echo $qi++?></td>
					<td data-sort-value="<?php echo $elapsed?>"><?php echo number_format($elapsed*1000,2) ?>ms</td>
					<td class=qry>						
						<div class=code_container>
							<code class=sql><?php echo $sql ?></code>
						</div>
					</td>
					<td>
						<?php
						$type = strtok($sql,' ');
						echo strtoupper($type);
						?> 
					</td>
					<td>
						<ol class=trace>
							<a class=show_trace href="#">Show Trace</a>
						<?php 
							$trace_parts = explode(',',$trace);
							foreach($trace_parts as $p){
								echo "<li>".System_Info_Tools::hilight_trace_part($p)."</li>";
							}
						?>
						</ol>
					</td>
				</tr>
			<?php endforeach; ?>
		</tbody>
	</table>
<?php else: ?>
	<p>No Queries Record. Ensure SAVEQUERIES is enabled.
	In wp-config and the line:<br/>
	define('SAVEQUERIES', true );
	</p>
<?php endif; ?>
