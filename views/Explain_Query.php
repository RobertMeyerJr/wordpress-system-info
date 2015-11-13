<?php defined('ABSPATH') or die("Nope!"); ?>
<table class=query_table>
	<thead>
		<tr>
			<th>id</th>
			<th>Table</th>
			<th>Select Type</th>
			<th>Possible Keys</th>
			<th>key</th>
			<th>key length</th>
			<th>ref</th>
			<th>rows</th>
			<th>Extra</th>
		</tr>
	</thead>
	<tbody>
	<?php foreach($results as $r) : ?>
		<tr>
			<td><?php echo $r->id?></td>
			<td><?php echo $r->table?></td>
			<td><?php echo $r->select_type?></td>
			<td><?php echo $r->possible_keys?></td>
			<td><?php echo $r->key?></td>
			<td><?php echo $r->key_len?></td>
			<td><?php echo $r->ref?></td>
			<td><?php echo $r->rows?></td>
			<td><?php echo $r->Extra?></td>
		</tr>
	<?php endforeach; ?>
	</tbody>
</table>