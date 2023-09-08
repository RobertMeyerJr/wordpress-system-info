<?php if ( !defined('ABSPATH') ){ die('-1'); } ?>
<?php 
global $_wp_additional_image_sizes, $wpdb, $wp_version, $total_details, $wp_roles;

$web_root 		= realpath($_SERVER['DOCUMENT_ROOT'].'/../');
$directories 	= glob($web_root.'/*' , GLOB_ONLYDIR);

ob_start();
	phpinfo();
	$php_info = ob_get_contents();
ob_end_clean();		 
$php_info = preg_replace( '%^.*<body>(.*)</body>.*$%ms','$1',$php_info);

if( !empty($_GET['make_first']) ){
	$total_details->make_first_plugin();
	echo "<h3>Made First Plugin</h3>";
}
?>
<a class="button button-info" href="<?php echo admin_url('?page=wp-total-details&make_first=1')?>">Make First Plugin</a>

<?php  if(function_exists('fpm_get_status') ): ?>
	<?php 
	$fpm_status = fpm_get_status();
	?>
	<h2>PHP FPM</h2>
	<table>
		<?php foreach($fpm_status as $k=>$v) : ?>
			<tr>
				<th><?=$k?></th>
				<td>
					<?php if($k == 'start-time') : ?>
						<?=date('n/j/Y g:ia', $v)?>
					<?php else : ?>
						<?php if(is_array($v)) : ?>
							<pre><?=print_r($v,true)?></pre>
						<?php else : ?>
							<?=$v?>
						<?php endif; ?>
					<?php endif;?>
				</td>				
			</tr>
		<?php endforeach; ?>
	</table>
<?php endif; ?>
<BR/><h2>Server Info</h2>
<table class='wp-list-table widefat fixed server_info striped'>
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
	
	<tr><th>COOKIE_DOMAIN</th><td><?php echo System_Info_Tools::color_format(COOKIE_DOMAIN)?></tr>
	<tr><th>COOKIEPATH</th><td><?php echo System_Info_Tools::color_format(COOKIEPATH)?></tr>
	<tr><th>ADMIN_COOKIE_PATH</th><td><?php echo System_Info_Tools::color_format(ADMIN_COOKIE_PATH)?></tr>
	<tr><th>SITECOOKIEPATH</th><td><?php echo System_Info_Tools::color_format(SITECOOKIEPATH)?></tr>
	<tr><th>COOKIEHASH</th><td><?php echo System_Info_Tools::color_format(COOKIEHASH)?></tr>


	<tr><th>AUTH_KEY</th><td><?php echo System_Info_Tools::color_format(AUTH_KEY)?></td></tr>
 	<tr><th>SECURE_AUTH_KEY</th><td><?php echo System_Info_Tools::color_format(SECURE_AUTH_KEY)?></td></tr>
 	<tr><th>LOGGED_IN_KEY</th><td><?php echo System_Info_Tools::color_format(LOGGED_IN_KEY)?></td></tr>
 	<tr><th>NONCE_KEY</th><td><?php echo System_Info_Tools::color_format(NONCE_KEY)?></td></tr>
 	<tr><th>AUTH_SALT</th><td><?php echo System_Info_Tools::color_format(AUTH_SALT)?></td></tr>      
 	<tr><th>SECURE_AUTH_SALT</th><td><?php echo System_Info_Tools::color_format(SECURE_AUTH_SALT)?></td></tr>
 	<tr><th>LOGGED_IN_SALT</th><td><?php echo System_Info_Tools::color_format(LOGGED_IN_SALT)?></td></tr> 
 	<tr><th>NONCE_SALT</th><td><?php echo System_Info_Tools::color_format(NONCE_SALT)?></td></tr>     


	<tr><th>Post Revisions<td><?php echo (WP_POST_REVISIONS)?'Yes':'No'?></tr>
	<tr><th>Auto Save Interval</th><td><?php echo AUTOSAVE_INTERVAL?></tr>


	<tr><th colspan=2 class=hdr>MySQL Info</th></tr>	
	<?php 
	/*
	<?php $slow_queries = System_Info_SQL::check_query_log(); ?>
	<tr><th>Slow Query Output</th><td><?php echo $slow_queries->log_output?></td></tr>
	<tr><th>Slow Query Location</th><td><?php echo $slow_queries->slow_query_log?></td></tr>
	<tr><th>Slow Query Not Using Indexes</th><td><?php echo $slow_queries->log_not_using_indexes?></td></tr>
	*/
	?>
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


<h2>Image Sizes</h2>
<table class='widefat striped'>
	<thead>
		<tr>
			<th>Name</th>
			<th>Hard Crop</th>
			<th>Size</th>
	</thead>
	<tbody>
		
			<?php 
			$built_in = [];
			$built_in_sizes = ['thumbnail','medium','large'];
			foreach($built_in_sizes as $t){
				$built_in[$t] = [
					'width'		=> get_option( $t.'_size_w' ),
					'height'	=> get_option( $t.'_size_h' ),
					'crop'		=> false
				];
			}
			
			$all_sizes = array_merge($built_in,$_wp_additional_image_sizes);
			
			?>
			<?php foreach($all_sizes as $name=>$i) : ?>		
				<tr>
					<td><span style="font-size:2.5em"><?php echo $name?></span></td>
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


<BR/><h2>PHP Settings</h2>
<table class='widefat striped'>
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
		<tr><th>Disabled Functions</th><td>
		<?php 
			$disabled = ini_get('disable_functions');
			$disabled = explode(',',$disabled);
			if( !empty($disabled) ){
				//test
				foreach($disabled as $d){
					echo "{$d} ";
				}
			}				
			else{
				echo "None";
			}
		?>
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

<BR/><h2>PHP Info</h2>
<div class=postbox>
	<div class='inside phpinfo'>
		<?php echo $php_info ?>
	</div>
</div>		
		