<?php if ( !defined('ABSPATH') ){ die('-1'); } ?>
<?php
$u = wp_get_current_user();
?>
<div class="inner text-center">
	<div>
	<p>
		User ID <?php echo $u->ID?>
		Caps <?php print_r($u->caps,true) ?>
	</p>
	<p>
		Peak Memory memory_get_peak_usage: <?php echo size_format( memory_get_peak_usage() )?><br/>
		Current Memory Usage: <?php echo size_format( memory_get_usage () )?>
	</p>
	<p>
		Developed by Robert Meyer Jr.
		<br/>
		<a target=_blank rel=noopener href="https://www.RobertMeyerJr.com">RobertMeyerJr.com</a>   
		<br/>     
		<a target=_blank rel=noopener href="https://www.1000Buddhas.co">1000Buddhas.co</a>
	</p>
	</div>
</div>
