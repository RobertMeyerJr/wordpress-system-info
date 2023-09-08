<?php if ( !defined('ABSPATH') ){ die('-1'); } ?>
<h2>Global Variables</h2>
<h5><label>Search</label> <input class=dbg_search><br/></h5>
<table id=result_table class="widefat striped">
	<thead>
		<tr>
			<th>Name
			<th>Type
			<th>Class
			<th>Value
		</tr>
	</thead>
	<tbody>
<?php foreach($GLOBALS as $k=>&$v) : $type = gettype($v); ?>
	<tr>
		<th><?=$k?></th>
		<td><?=$type?>
		<td>
			<?php $class = is_object($v) ? get_class($v) : ''; ?>
			<?=$class?>
		<td>
			<?php if($type == 'boolean') : ?>
				<?=$v ? 'True' : 'False'; ?>
			<?php elseif($type == 'integer') : ?>
				<span class=cBlue><?=$v?></span>
			<?php elseif($type == 'double') : ?>
				<span class=cBlue><?=$v?></span>
			<?php elseif($type == 'string') : ?>
				<?php $length = strlen($v) ; ?>
				<span class=cGreen><?=$length<=200 ? $v : 'Length '.$length; ?></span>
			<?php endif; ?>
		<td>
	</tr>
<?php endforeach; ?>
</table>
<script>
jQuery(function($){
	$('.value-info').click(function(){ $(this).toggleClass('expanded') });

	$('input.search').change(function(){
		var v = $(this).val();
		if(v.length){
			$('#result_table tbody tr').hide();
			$('table.dbg_out tr:contains('+v+')').show();
		}
		else{
			$('table.dbg_out tr').show();
		}
	});
});    
</script>
