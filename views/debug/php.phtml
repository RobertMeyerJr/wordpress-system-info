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
		<?php if(function_exists('apc_cache_info')) : $info = apc_cache_info(); ?>
			<tr><th>APC</th><td>
			<?php 
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
			?>
			</td></tr>
			<?php
				foreach($fields as $f)
					echo "<tr><th></th><td>{$info[$f]}</tr>";
			?>
		<?php endif; ?>

		<tr>
			<th>OpCode Cache</th>
			<td>
				<?php if( extension_loaded( 'xcache' ) ) : ?>XCache
				<?php elseif( extension_loaded( 'apc' ) ) : ?>APC
				<?php elseif( extension_loaded( 'eaccelerator' ) ) : ?>EAccelerator
				<?php elseif( extension_loaded( 'Zend Optimizer+' ) ) : ?>Zend Optimizer+
				<?php elseif( extension_loaded( 'wincache' ) ) : ?>WinCache
				<?php else: ?>None
				<?php endif; ?>
			</td>
		</tr>
		<?php 
		
		$extensions = get_loaded_extensions();
		foreach($extensions as $e): ?>
			<li><a href=http://www.php.net/manual/en/book.<?php echo strtolower($e)?>.php><?php echo $e?></a></li>		
		<?php endforeach; ?>
		</ul>		
		<?php $disabled = ini_get('disable_functions'); ?>
		<tr><th>Disabled Functions</th><td>
		<?php if(!empty($disabled)) :?> 
		<?php foreach($disabled as $d) : ?>
			<?php echo $d?> is Disabled</tr>
		<?php endforeach; ?>
		<?php else: ?>
			None</tr>
		<?php endif; ?>
		<tr><th>PHP Memory Usage: </th><td><?php echo memory_get_usage() ?>
	</table>