<?php
//Need to run ANALYZE TABLE to update statistics after a change
?>
<h2>
	<?php 
		echo "{$wpdb->num_queries} Queries in {$total_query_time_ms}ms";		
	?>
	
<?php if(!empty($query_percentage)) : ?>
	<?php echo "{$query_percentage}% of Request Time "; ?>&nbsp;
	<progress max=100 value="<?php echo $query_percentage?>"></progress>
<?php endif; ?>
</h2>
<?php if(!empty($wpdb->queries)) : ?>
	<table>
		<thead>
			<tr>
				<th>Duration</th>
				<th>Query</th>
				<th>Trace</th>
			</tr>
		</thead>
		<tbody>
			<?php foreach($wpdb->queries as $q) :?>
				<?php list($sql, $elapsed, $trace) = $q; ?>
				<tr>
					<th><?php echo number_format($elapsed*1000,2) ?>ms</th>
					<td class=qry>						
						<code class=sql><?php echo $sql ?></code><br/>
					</td>
					<td>
						<ol>
						<?php 
							$trace_parts = explode(',',$trace);
							foreach($trace_parts as $p){
								echo "<li>".hilight_trace_part($p)."</li>";
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