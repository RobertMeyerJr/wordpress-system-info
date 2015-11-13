<h2>WP Object Cache</h2>
<?php global $wp_object_cache; ?>
<?php if( method_exists($wp_object_cache, 'stats') ) : ?>
	<?php $wp_object_cache->stats(); ?>
<?php else : ?>
<table class=widefat>
	<?php if( !empty($wp_object_cache) ): ?>
		<tr><th></th><td><?php if(isset($wp_object_cache->hits)) echo $wp_object_cache->hits ?></td></tr>
		<tr><th></th><td><?php if(isset($wp_object_cache->misses)) echo $wp_object_cache->misses?></td></tr>	
	<?php endif; ?>
		
	<?php if( extension_loaded( 'wincache' ) ) : ?>
			Wincache	
			<tr><th>Mem Info<td><?php d( wincache_ucache_meminfo() ) ?>	
	<?php elseif( extension_loaded( 'apc' ) ) : ?>
		APC
		<?php 
			echo "User: ".apc_cache_info('user');
			echo "Hits: ".apc_cache_info('filehits');
			print_r( apc_cache_info() );
		?>
	<?php endif; ?>
</table>
<?php endif; ?>