<?php if ( !defined('ABSPATH') ){ die('-1'); } ?>
<h2>WP Object Cache</h2>
<?php 
global $wp_object_cache;

?>
<div class=object-cache>
	<?php if( method_exists($wp_object_cache, 'stats') ) : ?>
		<?php $wp_object_cache->stats(); ?>	
	<?php endif  ?>
</div>
<table class=widefat>
	<?php if( !empty($wp_object_cache) ): ?>
		<tr><th></th><td><?php if(isset($wp_object_cache->hits)) 	echo number_format($wp_object_cache->hits)  ?></td></tr>
		<tr><th></th><td><?php if(isset($wp_object_cache->misses)) 	echo number_format($wp_object_cache->misses) ?></td></tr>	
	<?php endif; ?>
</table>

<?php if(!empty(System_Info::$cache_hits)) : ?>
	<h2>Cache Hits <?=number_format(count(System_Info::$cache_hits))?></h2>
	<table>
		<?php foreach(System_Info::$cache_hits as $k=>$c) : ?>
			<tr>
				<th><?=$k?></th>
				<td><?=$c?></td>
			</tr>
		<?php endforeach; ?>
	</table>
	<h2>Cache Miss <?=number_format(count(System_Info::$cache_misses))?></h2>
	<table>
		<?php foreach(System_Info::$cache_misses as $k=>$c) : ?>
			<tr>
				<th><?=$k?></th>
				<td><?=$c?></td>
			</tr>
		<?php endforeach; ?>
	</table>
<?php endif; ?>
