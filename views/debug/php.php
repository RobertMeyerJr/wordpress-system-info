<h2>PHP Settings</h2>
	<table>
		<tr><th>Loaded Configuration</th><td><?php echo php_ini_loaded_file();?></td></tr>
		<tr><th>Extensions Directory</th><td><?php echo PHP_EXTENSION_DIR?></td></tr>
		<tr><th>max_execution_time</th><td><?php echo ini_get('max_execution_time');?></td></tr>
		<tr><th>post_max_size</th><td><?php echo ini_get('post_max_size');?></td></tr>
		<tr><th>upload_max_filesize</th><td><?php echo ini_get('upload_max_filesize');?></td></tr>
		<tr><th>memory_limit</th><td><?php echo ini_get('memory_limit');?></td></tr>
		<tr><th>open_basedir</th><td><?php echo ini_get('open_basedir');?></td></tr>
		<tr><th>short_open_tag</th><td><?php echo ini_get('short_open_tag');?></td></tr>
		<tr><th>safe_mode</th><td><?php echo ini_get('safe_mode');?></td></tr>
		<ul class=extensions>
		<?php 		
		$extensions = get_loaded_extensions();
		foreach($extensions as $e): ?>
			<li><a href=http://www.php.net/manual/en/book.<?php echo strtolower($e)?>.php><?php echo $e?></a></li>		
		<?php endforeach; ?>
		</ul>		
		<?php $disabled_funcs = explode(',', ini_get('disable_functions')); ?>
		<tr>
			<th>Disabled Functions</th>
			<td>
			<?php if( !empty($disabled_funcs) ) :?> 
				<?php foreach($disabled_funcs as $func_name) : ?>
					<?php echo $func_name?>
				<?php endforeach; ?>
			<?php else: ?>
					None
			<?php endif; ?>
		</td>
		</tr>
		<tr><th>PHP Memory Usage: </th><td><?php echo memory_get_usage() ?>
	</table>