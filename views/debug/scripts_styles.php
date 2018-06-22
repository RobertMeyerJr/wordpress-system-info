<?php
global $wp_scripts,$wp_styles;
#d($wp_scripts);
?>
<h2>Scripts</h2>
<table>
	<thead>
		<tr>
			<th>Name</th>
			<th>Src</th>
			<th>Deps</th>
			<th>Ver</th>
		</tr>
	</thead>
	<tbody>
		<?php foreach($wp_scripts->queue AS $q) : ?>
			<?php $s = $wp_scripts->registered[$q]; ?>
			<tr>
				<th><?php echo $q?></th>
				<td>
					<?php if(!empty($s->src)) : ?>
						<a href="<?=$s->src?>"><?=$s->src?></a>
					<?php endif; ?>
				</td>
				<td>
					<?php if(!empty($s->deps)) : ?>
						<?=implode(', ',$s->deps)?>
					<?php endif; ?>
				</td>
				<td>
					<?php if(!empty($s->ver)) : ?>
						<?=$s->ver?>
					<?php endif; ?>
				</td>
			</tr>
		<?php endforeach; ?>
	</tbody>
</table>
<h2>Styles</h2>
<table>
	<thead>
		<tr>
			<th>Name</th>
			<th>Src</th>
			<th>Deps</th>
			<th>Ver</th>
		</tr>
	</thead>
	<?php foreach($wp_styles->queue AS $q) : ?>
		<?php $s = $wp_styles->registered[$q]; ?>
		<tr>
			<th><?php echo $q?></th>
			<td>
				<?php if(!empty($s->src)) : ?>
					<a href="<?=$s->src?>"><?=$s->src?></a>
				<?php endif; ?>
			</td>
			<td>
				<?php if(!empty($s->deps)) : ?>
					<?=implode(', ',$s->deps)?>
				<?php endif; ?>
			</td>
			<td>
				<?php if(!empty($s->ver)) : ?>
					<?=$s->ver?>
				<?php endif; ?>
			</td>
		</tr>
	<?php endforeach; ?>
	</tbody>
</table>

<script>
jQuery(function(){
	var source = jQuery(this).attr('src');
	var code = jQuery(this).html();
	//source = '<div class=hidden_code> <span>( inline )</span> <pre>'+code+'<pre></div>';
	//jQuery('#si_inline_scripts table').append('<tr><th>Script</th><td>'+source);
});
</script>
