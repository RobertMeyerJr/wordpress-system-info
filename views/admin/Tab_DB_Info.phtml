<?php 
/*
thread_cache_size = 8
innodb_buffer_pool_size = 1G
key_buffer_size = 128M
table_cache = 1024
*/

//if InnoDB, get rowcount Manually via SELECT COUNT(1) FROM $TABLE
$tables = System_Info_SQL::get_tables();

$total_db_size = 0;
foreach($tables as $t){
	$total_db_size += $t->Data_length;
}

?>

<h3 class=hndle>
	Tables in Database: <span style='color:blue;font-weight:bold;'><?php echo DB_NAME?></span>
	Total DB Size <span class=cPurple><?php echo System_Info_Tools::formatBytes($total_db_size)?></span>
</h3>
<table class='wp-list-table widefat fixed'>
	<thead>
		<tr>
			<th>Name</th>
			<th>Engine</th>
			<th>Rows</th>
			<th>Created</th>
			<th>Collation</th>
			<th>Size</th>
			<th>Fragmentation</th>
			<th></th>
		</tr>
	</thead>
</thead>
<tbody>
	<?php $TenMB = 10000000; ?>
	<?php $i = 0; foreach($tables as $t): ?>
		<tr class="table-<?php echo $t->Name?>">
			<td class=cGreen><?php echo $t->Name?></td>
			<td class=cDark><?php echo $t->Engine?></td>
			<td class=cBlue><?php echo number_format($t->Rows)?></td>
			<td class=cGreen><?php echo date('m/d/Y h:ia',strtotime($t->Create_time))?></td>
			<td class=cPurple><?php echo $t->Collation?></td>
			<td class=cOrange><?php echo System_Info_Tools::formatBytes($t->Data_length)?></td>
			<td class=cRed><?php echo $t->fragmentation?>%</td>
			<td class=cPurple>				
				<?php if($t->fragmentation == 0) : ?>
					( None )
				<?php elseif($t->Data_length < $TenMB) : ?>
					<a href=# class=button-secondary onClick='optimizeTable("<?php echo $t->Name?>");'>Optimize</a>
				<?php else : ?>
					(Table Too Large. Must be optimized Manually)
				<?php endif; ?>
			</td>
	<?php endforeach; ?>
</tbody>
</table>