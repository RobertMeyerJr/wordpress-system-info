<?php if ( !defined('ABSPATH') ){ die('-1'); } ?>
<?php
$u = wp_get_current_user();
#d($u);
?>
<div class="inner text-center">
	<table>
		<tr><th>User ID<td><?php echo $u->ID ?? ''?>
		<tr><th>User<td><?php echo $u->display_name ?? ''?>
		<tr><th>Registered<td><?php echo $u->user_registered ?? ''?>
		<tr><th>Caps<td><?php echo implode(', ',array_keys($u->caps) ?? '') ?>
		<tr><th>Roles<td><?php echo implode(', ',$u->roles ?? '') ?>
		<tr><th>WordPress<td><?php echo $GLOBALS['wp_version']?>
		<tr><th>PHP<td><?php echo phpversion(); ?>
		<tr><th>Peak Memory memory_get_peak_usage<td><?php echo size_format( memory_get_peak_usage() )?>
		<tr><th>Current Memory Usage<td><?php echo size_format( memory_get_usage () )?>
		<tr><th>Operating System<td><?=PHP_OS?>
		<tr><th>ABSPATH<td><?php echo ABSPATH; ?>
		<tr><th>TEMPLATEPATH<td><?php echo TEMPLATEPATH; ?>
		<tr><th>STYLESHEETPATH<td><?php echo STYLESHEETPATH; ?>
	</table>
	<br/>
	<p>
		Developed by Robert Meyer Jr.
		<br/>
		<a target=_blank rel=noopener href="https://www.RobertMeyerJr.com">RobertMeyerJr.com</a>   
		<br/>     
		<a target=_blank rel=noopener href="https://www.1000Buddhas.co">1000Buddhas.co</a>
	</p>
</div>
