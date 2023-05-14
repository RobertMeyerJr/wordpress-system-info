<?php if ( !defined('ABSPATH') ){ die('-1'); } ?>
<?php
global $wp_scripts,$wp_styles;
#ToDo: Add files included due to dependency arrays
#ToDo: Add fetchpriority
?>
<h2>Body Classes</h2>
<div>
	<?php $body_classes = get_body_class(); ?>
	<ul style="columns: 3;">
		<li><?php echo implode('<li>',$body_classes);?>
	</ul>
</div>
<h2>Scripts (<?=count($wp_scripts->queue)?>)</h2>
<table id=scripts_table>
	<thead>
		<tr>
			<th>Name</th>
			<th>Position</th>
			<th>Src</th>
			<th>Deps</th>
			<th>Ver</th>
			<th>Attr</th>
			<th>Size</th>
		</tr>
	</thead>
	<tbody>
		<?php foreach($wp_scripts->queue AS $q) : ?>
			<?php $s = $wp_scripts->registered[$q]; ?>
			<tr>
				<th><?php echo $q?></th>
				<td><?=in_array($q, $wp_scripts->in_footer) ? 'footer':'header';?></td>
				<td>
					<?php if(!empty($s->src)) : ?>
						<a class="script-src" href="<?=$s->src?>"><?=$s->src?></a>
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
				<td class=attrs></td>
				<td>
					<?php 
						if( stripos($s->src,'/wp-content/') !== false || stripos($s->src,'/wp-includes/') !== false ){
							$file = rtrim(ABSPATH,'.').parse_url($s->src,PHP_URL_PATH);
							echo size_format(filesize($file));
						}
					?>
				</td>
			</tr>
		<?php endforeach; ?>
	</tbody>
</table>
<h2>Styles (<?=count($wp_styles->queue)?>)</h2>
<table id=styles_table>
	<thead>
		<tr>
			<th>Name</th>
			<th>Src</th>
			<th>Deps</th>
			<th>Ver</th>
			<th>Attr</th>
			<th>Size</th>
		</tr>
	</thead>
	<?php foreach($wp_styles->queue AS $q) : ?>
		<?php $s = $wp_styles->registered[$q]; ?>
		<tr>
			<th><?php echo $q?></th>
			<td class="<?=empty($s->src)?'inline':'file'?>" data-name="<?=esc_attr($q)?>">
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
			<td class=attr></td>
			<td>
				<?php 
					if( stripos($s->src,'/wp-content/') !== false || stripos($s->src,'/wp-includes/') !== false ){
						$file = rtrim(ABSPATH,'.').parse_url($s->src,PHP_URL_PATH);
						echo size_format(filesize($file));
					}
				?>
			</td>
		</tr>
	<?php endforeach; ?>
	</tbody>
</table>
<!-- wordpress-system-info -->
<script>
jQuery(function($){
	$('#scripts_table .script-src').each(function(){
		var src = $(this).attr('href');
		var defer = $('script[src*="'+src+'"]').attr('defer') || '';
		var async = $('script[src*="'+src+'"]').attr('async') || '';
		//console.log(defer);
		$(this).closest('tr').find('.attrs').html(defer+' '+async);
	});
	//var source = jQuery(this).attr('src');
	//var code = jQuery(this).html();
	//source = '<div class=hidden_code> <span>( inline )</span> <pre>'+code+'<pre></div>';
	//jQuery('#si_inline_scripts table').append('<tr><th>Script</th><td>'+source);
	$('#styles_table td.inline').each(function(){
		var name = $(this).data('name');
		var css = '<details><summary>Toggle</summary><div>'+$(`#${name}-inline-css`).html()+'</div></details>';
		$(this).html(css);
	});

	$('#styles_table th').each(function(){
		/*
		var name = $(this).text();
		var onload = $('link#'+name).attr('onload') || '';
		if( onload.indexOf('this.rel') != -1 ){
			$(this).parent().find('.attr').html('Defer');
		}
		*/
	});
});
</script>
