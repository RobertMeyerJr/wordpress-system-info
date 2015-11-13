<?php 
global $wpdb;

/*
	have_query_cache
	query_cache_size
	query_cache_limit	
*/

$slow_queries = $wpdb->get_results($wpdb->prepare("SELECT db,user_host,
						avg(query_time) query_time,
						avg(rows_sent) rows_sent,
						avg(rows_examined) rows_examined,
						count(1) as times_called,
						sql_text
				FROM mysql.slow_log 
				WHERE db = %s
				GROUP BY sql_text
				ORDER BY query_time DESC,times_called DESC
				LIMIT 200", DB_NAME));
?>
<table>
	<thead>
		<th>Query Time</th>
		<th>Rows Sent</th>
		<th>Rows Examined</th>
		<th>Times Called</th>
		<th>SQL</th>
	</thead>
	<tbody>
		<?php if(!empty($slow_queries)) : ?>
		<?php foreach($slow_queries as $q) : ?>
			<tr>
				<td><?php echo $q->query_time?></td>
				<td><?php echo $q->rows_sent?></td>
				<td><?php echo $q->rows_examined?></td>
				<td><?php echo $q->times_called?></td>
				<td><code><?php echo $q->sql_text ?></code>			
		<?php endforeach; ?>
		<?php else: ?>
			<tr><td colspan=5>No Slow Queries Logged
		<?php endif; ?>
	</tbody>
</table>