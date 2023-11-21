<?php if ( !defined('ABSPATH') ){ die('-1'); } ?>
<style>
.tab{display:none;padding:5px;}
.tab.active{display:block;}
.cache-stats{position:relative;height:152px;margin:15px 0;}
</style>
<?php 
global $wp_object_cache;

if( method_exists($wp_object_cache, 'redis_version') ){
	$info 			= $wp_object_cache->info();
	$redis 			= $wp_object_cache->redis_instance();
	
	$redisinfo = [];
	if( method_exists($redis,'rawCommand') ){
		$redis_info = $redis->rawCommand('info');
		//Read line by line, # signifies new section
		$lines = explode("\n",$redis_info);
		$key = 'NOKEY';
		$line_count = count($lines);
		for($i=0; $i<$line_count; $i++){
			$l = $lines[$i];
			if($i==0 || substr($l, 0, 1) == '#'){
				$key = trim( str_replace('#','',$lines[$i]) );
				continue;
			}
			list($name,$value) = explode(':', $l, 2); //key:value
			$redisinfo[$key][$name] = $value;
		}
	}
}
?>
<?php if( !empty($redisinfo) ) : ?>
	<h2>Redis Stats</h2>

	<?php $tab_index=0;?>
	<div id=redis_tabs>
		<h2 class="nav-tab-wrapper tabbed_nav">
			<a class="nav-tab nav-tab-active" href="#tab_summary">Summary</a>
			<?php foreach($redisinfo as $section=>$data) : ?>
    		<a class="nav-tab" href="#tab_<?=esc_attr($section)?>"><?=$section?></a>
			<?php endforeach; ?>
		</h2>	
		<div id=tab_summary class="tab active">
			<table>
				<tr><th>Redis Version<td><?=$redisinfo['Server']['redis_version']?>
				<tr><th>User Memory<td><?=$redisinfo['Memory']['used_memory_human']?>
				<tr><th>User Memory RSS<td><?=$redisinfo['Memory']['used_memory_rss_human']?>
				<tr><th>Max Memory<td><?=$redisinfo['Memory']['maxmemory_human']?>
				<tr><th>Max Memory Policy<td><?=$redisinfo['Memory']['maxmemory_policy']?>
				<tr><th>Total Connections<td><?=$redisinfo['Stats']['total_connections_received']?>
				<tr><th>instantaneous_ops_per_sec<td><?=$redisinfo['Stats']['instantaneous_ops_per_sec']?>
				<tr><th>keyspace_hits<td><?=$redisinfo['Stats']['keyspace_hits']?>
				<tr><th>keyspace_misses<td><?=$redisinfo['Stats']['keyspace_misses']?>
			</table>
		</div>
		<?php foreach($redisinfo as $section=>$data) : ?>
			<div class="tab" id="tab_<?=esc_attr($section)?>">
				<table>
					<?php foreach($data as $k=>$v) : ?>
						<tr>
							<th><?=$k?></th>
							<td><?=$v?></td>
						</tr>
					<?php endforeach; ?>
				</table>
			</div>
		<?php endforeach; ?>
	</div>
	<table>
		
	</table>
<?php endif; ?>

<script>
jQuery(function($){
	console.log('redis info');
	$('#redis_tabs .nav-tab').click(function(){
		var tab = $(this).attr('href');
		$('#redis_tabs .tab').removeClass('active');
		$(tab).addClass('active');
		return false;
	});
})	
</script>
