<?php 
$WP_CORE_TIME = number_format(SI_START_TIME - $_SERVER['REQUEST_TIME_FLOAT'],4);
?>
<table>
	<tr><th>Core Time</th><td><?php echo $WP_CORE_TIME?></td>
</table>

<h2>Browser Measurements</h2>
<table>
	<thead>
		<tr>
			<th>Part</th>
			<th>Time</th>
			<th>Percentage</th>
			<th>
		</tr>
	</thead>
	<tbody id=dbg_frontend>
	</tbody>
</table>