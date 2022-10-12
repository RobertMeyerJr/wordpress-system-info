<?php if ( !defined('ABSPATH') ){ die('-1'); } ?>
<!-- PHP -->
	<tr><th class=hdr colspan=2><h2><i class='dashicons dashicons-admin-generic'></i> PHP</h2></th></tr>
	<tr><th>Version</th><td><?=PHP_VERSION?></td></tr>
	<tr><th>SAPI</th><td><?=php_sapi_name()?></td></tr>

	<tr><th>Session Started<td><?php echo session_status() == PHP_SESSION_NONE ? '<span class=cGreen>No</span>':'<span class=cRed>Yes!</span> (Sessions Slow Down Site)'; ?>
	
	<tr><th>OpCode Cache</th><td>
		<?php if( version_compare(PHP_VERSION, '7.0') >= 0 ) : ?>Built In (PHP >= 7.0)
		<?php elseif( extension_loaded('Zend OPcache') ): ?>Zend OPcache
		<?php elseif( extension_loaded( 'xcache' ) ) : ?>XCache		
		<?php elseif( extension_loaded( 'apc' ) ) : ?>APC
		<?php elseif( extension_loaded( 'eaccelerator' ) ) : ?>EAccelerator
		<?php elseif( extension_loaded( 'Zend Optimizer+' ) ) : ?>Zend Optimizer+
		<?php elseif( extension_loaded( 'wincache' ) ) : ?>WinCache
		<?php else: ?>None
		<?php endif; ?>
	</td></tr>	
	<tr><th>Max Post</th><td><?=ini_get('post_max_size')?></td></tr>
	<tr><th>Upload Max</th><td><?=ini_get('upload_max_filesize')?></td></tr>
	<tr><th>Memory Limit</th><td><?=ini_get('memory_limit')?></td></tr>			
	<tr><th>Max Time</th><td><?=ini_get('max_execution_time')?>s</td></tr>
	<tr><th>User</th><td><?=getenv('USERNAME') ?: getenv('USER');?></td></tr>
	<tr><th>Disabled Functions</th><td>
		<?php $disabled= ini_get('disable_functions')?>
		<span class=breakword><?=(empty($disabled))?'No functions are disabled':$disabled?></span></td></tr>
	<tr><th>Error Log</th><td><?=ini_get('error_log')?></td></tr>
