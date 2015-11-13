<?php 
global $_wp_additional_image_sizes, $wpdb, $wp_version; 

$have_query_cache = System_Info_SQL::option('have_query_cache');
$query_cache_size = System_Info_SQL::option('query_cache_size');

$web_root 		= realpath($_SERVER['DOCUMENT_ROOT'].'/../');
$directories 	= glob($web_root.'/*' , GLOB_ONLYDIR);

ob_start();
	phpinfo();
	$php_info = ob_get_contents();
ob_end_clean();		 
$php_info = preg_replace( '%^.*<body>(.*)</body>.*$%ms','$1',$php_info);

?>

<br/><h2><i class='fa fa-tachometer cBlue'></i> Speed</h2>
<table class=widefat>
	<tr><th>OpCode Cache</th><td>
		<?php if( extension_loaded( 'xcache' ) ) : ?>XCache
		<?php elseif( extension_loaded( 'apc' ) ) : ?>APC
		<?php elseif( extension_loaded( 'eaccelerator' ) ) : ?>EAccelerator
		<?php elseif( extension_loaded( 'Zend Optimizer+' ) ) : ?>Zend Optimizer+
		<?php elseif( extension_loaded( 'wincache' ) ) : ?>WinCache
		<?php else: ?>None
		<?php endif; ?>
	</td></tr>
	<tr><th>Memory Limit</th></tr>	
	<tr><th>APC Cache Limit</th></tr>		
	<tr><th>MySQL Cache</th></tr>	
	<tr><th>Query Cache</th><td><?php echo $have_query_cache ?></td></tr>
	<tr><th>Cache Size</th><td><?php echo number_format($query_cache_size)?></td></tr>
	
</table>
<BR/><h2><i class='cPurple fa fa-info'></i> Server Info</h2>
<table class='wp-list-table widefat fixed server_info'>
	<tr><th colspan=2 class=hdr>Software
	<tr><th>OS<td><?php echo System_Info_Tools::color_format(php_uname())?>
	<tr><th>PHP Version<td><?php echo PHP_VERSION?>
	<tr><th>MySQL Version<td><?php echo System_Info_Tools::color_format($wpdb->db_version()); ?>
	<tr><th>Webserver<td><?php echo System_Info_Tools::color_format($_SERVER['SERVER_SOFTWARE'])?>
	<tr><th>Webserver User<td><?php echo (System_Info_Tools::is_windows()) ? get_current_user():exec('whoami');?></tr>
	
	<?php if(($_SERVER['SERVER_SOFTWARE']=='Apache') && function_exists('apache_get_modules')) : 
			$modules = apache_get_modules(); ?>	
			<tr><th>Apache Modules</th>
			<td>
				<?php foreach($modules as $m) : ?>
					<span><?php echo $m?></span><br/>
				<?php endforeach; ?>
			</td></tr>
	<?php endif; ?>		
	
	<tr><th colspan=2 class=hdr>Wordpress Info</th></tr>
	<tr><th>Wordpress Version</th><td><?php echo $wp_version?></tr>
	<tr><th>WP_DEBUG</th><td><?php echo System_Info_Tools::color_format((WP_DEBUG)?'Yes':'No')?></tr>
	<tr><th>WP_DEBUG_DISPLAY</th><td><?php echo System_Info_Tools::color_format((WP_DEBUG_DISPLAY)?'Yes':'No')?></tr>
	<tr><th>WP_DEBUG_LOG</th><td><?php echo System_Info_Tools::color_format(WP_DEBUG_LOG)?></td></tr>
	<tr><th>WP_PLUGIN_DIR</th><td><?php echo System_Info_Tools::color_format(WP_PLUGIN_DIR)?></tr>
	<tr><th>FS_METHOD</th><td><?php if (defined('FS_METHOD')) echo System_Info_Tools::color_format(FS_METHOD)?></tr>			
	<tr><th>Post Revisions<td><?php echo (WP_POST_REVISIONS)?'Yes':'No'?></tr>
	<tr><th>Auto Save Interval</th><td><?php echo AUTOSAVE_INTERVAL?></tr>
	<tr><th colspan=2 class=hdr>MySQL Info</th></tr>
	<tr><th>MySQL Query Cache Size</th><td><?php 
		$query_cache = $wpdb->get_row("SHOW VARIABLES LIKE 'query_cache_size'");
		if(!$query_cache)
			echo "<span class=red>Query Cache Not Enabled!</span>";
		else 
			echo System_Info_Tools::formatBytes($query_cache->Value);
	?></td></tr>
	<?php $slow_queries = System_Info_SQL::check_query_log(); ?>
	<tr><th>Slow Query Output</th><td><?php echo $slow_queries->log_output?></td></tr>
	<tr><th>Slow Query Location</th><td><?php echo $slow_queries->slow_query_log?></td></tr>
	<tr><th>Slow Query Not Using Indexes</th><td><?php echo $slow_queries->log_not_using_indexes?></td></tr>
	<tr><th>Database<td><?php echo DB_NAME?>
	<tr><th>User<td><?php echo DB_USER?>
	<tr><th>Host<td><?php echo DB_HOST?>				
	<tr><th colspan=2 class=hdr>Environment Info
	<tr><?php if(!empty($_SERVER['APPLICATION_ENV']) ) : ?>
		<th>Environment<td><?php echo System_Info_Tools::color_format($_SERVER['APPLICATION_ENV'])?>
	<?php endif; ?>
	<tr><th>Server API</th><td><?php echo System_Info_Tools::color_format(PHP_SAPI)?></td></tr>
	<tr><th>Document Root</th><td><?php echo $_SERVER['DOCUMENT_ROOT'];?></th></tr>			
	<?php if(($_SERVER['SERVER_SOFTWARE']=='Apache') && function_exists('apache_get_modules')) : 
			$modules = apache_get_modules(); ?>	
			<tr><th>Apache Modules</th>
			<td>
				<?php foreach($modules as $m) : ?>
					<span><?php echo $m?></span>
				<?php endforeach; ?>
			</td></tr>
	<?php endif; ?>			
</table>

<?php if(!empty($_wp_additional_image_sizes)) : ?>
<BR/><h2><i class="fa fa-2x fa-photo"></i> Image Sizes</h2>
<table class='widefat'>
	<thead>
		<tr>
			<th>Name</th>
			<th>Hard Crop</th>
			<th>Size</th>
	</thead>
	<tbody>
	<?php 
	
	$built_in_sizes = ['thumbnail','medium','large'];
	$built_in = [];
	foreach($built_in_sizes as $t){
		$built_in[$t] = [
			'width'		=> get_option( $t.'_size_w' ),
			'height'	=> get_option( $t.'_size_h' ),
			'crop'		=> false
		];
	}
	
	$all_sizes = $built_in + $_wp_additional_image_sizes;
	
	?>
	<?php foreach($all_sizes as $name=>$i) : ?>		
		<tr>
			<td><?php echo $name?></td>
			<td><?php echo ($i['crop'])?'Yes':'No'?></td>
			<td style="text-center">
				<span class="thumb_example" style="width:<?php echo $i['width']?>px;height:<?php echo $i['height']?>px">
				<?php echo $i['width']?>
				x
				<?php echo $i['height']?>
				</span>
			</td>
		</tr>
	<?php endforeach; ?>
	</tbody>
</table>
<?php endif; ?>

<BR/><h2><i class="fa fa-2x fa-cogs"></i> PHP Settings</h2>
<table class='widefat'>
<tr><th>Loaded Configuration</th><td><?php echo php_ini_loaded_file();?></td></tr>
	<tr><th>Extensions Directory</th><td><?php echo PHP_EXTENSION_DIR?></td></tr>
	<tr><th>max_execution_time</th><td><?php echo ini_get('max_execution_time');?></td></tr>
	<tr><th>post_max_size</th><td><?php echo ini_get('post_max_size');?></td></tr>
	<tr><th>upload_max_filesize</th><td><?php echo ini_get('upload_max_filesize');?></td></tr>
	<tr><th>memory_limit</th><td><?php echo ini_get('memory_limit');?></td></tr>
	<tr><th>open_basedir</th><td><?php echo ini_get('open_basedir');?></td></tr>
	<tr><th>short_open_tag</th><td><?php echo ini_get('short_open_tag');?></td></tr>
	<tr><th>safe_mode</th><td><?php echo ini_get('safe_mode');?></td></tr>		
		<?php if(function_exists('apc_cache_info')) : $info = apc_cache_info(); ?>
			<tr><th>APC</th><td>
			<pre><?php 
				$fields = array(
					'num_slots',       
					'ttl',                 
					'num_hits',            
					'num_misses',          
					'num_inserts',         
					'expunges',            
					'start_time',          
					'mem_size',            
					'num_entries',         
					'file_upload_progress',
					'memory_type',         
					'locking_type',        
				);
				foreach($fields as $f)
					echo "<label></label> {$info[$f]}<br/>";
			?></pre>
		<?php endif; ?></td></tr>
		
		<?php $disabled = ini_get('disable_functions'); ?>
		<tr><th>Disabled Functions</th><td>
		<?php if(!empty($disabled)) :?> 
		<?php foreach($disabled as $d) : ?>
			<?php echo $d?> is Disabled 
		<?php endforeach; ?>
		<?php else: ?>
			None
		<?php endif; ?>
		</td></tr>
		<tr><th>PHP Extensions</th>
		<td>
			<ul class=extensions>		
			<?php $extensions = get_loaded_extensions(); ?>
			<?php foreach($extensions as $e): ?>
				<li><a target=_blank href=http://www.php.net/manual/en/book.<?php echo strtolower($e)?>.php><?php echo $e?></a></li>		
			<?php endforeach; ?>
			</ul> 	
		</td>
		</tr>
</table>		

<h2><i class='fa fa-tachometer cBlue'></i> Other Folders in WebRoot <?php echo $web_root?></h2>
<div class=postbox>
	<div class=inside>	
	<table>
		<thead>
			<tr>
				<th>Name
				<th>Size			
			</tr>
		</thead>
		<tbody>
		<?php foreach($directories as $d) : ?>
			<tr>
				<td><?php echo str_replace($web_root.'/','',$d)?></td>
				<td>
				<?php 
					//Get Folder Size
					if( !System_Info_Tools::is_windows() ){
						unset($size);
						System_Info_Tools::run_command("du -sh {$d}",$size);
						$parts = preg_split('/\s+/', $size[0]);
						echo $parts[0];
					}
				?>
				</td>
			</tr>
		<?php endforeach ?>
		</tbody>
	</table>		
	</div>
</div>

<div class=postbox>
	<div class='inside phpinfo'>
		<?php echo $php_info ?>
	</div>
</div>		
		