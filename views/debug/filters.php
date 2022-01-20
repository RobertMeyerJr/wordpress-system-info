<?php if ( !defined('ABSPATH') ){ die('-1'); } ?>
<?php 
global $wp_filter,$wp_actions;
	$filter_percentage 		= number_format(($total_filter_duration/$time_taken) * 100, 2);
	$findex = 1;


?>
<h2>Filters, Actions
	Duration <?php echo number_format($total_filter_duration, 8)?>ms
	<?php if(!empty($filter_percentage)) : ?>
		<?=$filter_percentage?>% of Request Time
		<progress max=100 value="<?=$filter_percentage?>"></progress>
	<?php endif; ?>
</h2>


<input type=text id=filter_hooks_by placeholder="Filter Hooks"/>
<button type=button id=filter_hooks>Search</button>

<table class=widefat>
	<thead>
		<tr>
			<th>Index</th>
			<th>Filter</th>
			<th>Duration</th>
		</tr>
	</thead>
	<tbody id=dbg_hooks>
	<?php foreach(System_Info::$actions as list($filter,$start,$end) ) : ?>
		<tr>
			<th><?=$findex++?></th>
			<th><?php echo $filter ?></th>
			<td><?php echo number_format(($end-$start)*1000, 8)?>s</td>
		</tr>
	<?php endforeach; ?>
	</tbody>
</table>
