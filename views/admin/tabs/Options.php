<?php if ( !defined('ABSPATH') ){ die('-1'); } ?>
<?php 
global $wpdb;

$options = $wpdb->get_results("SELECT * FROM {$wpdb->options}");

#TODO: Ability to delete transients

$options = array_map(function($o){
	$size = strlen($o->option_value);
	return [$o->option_name, $size, $o->option_value];
},$options);
usort($options,function($a,$b){ return $b[1] > $a[1]; });
?>
<table class='widefat striped'>
	<thead>
	<tr>
		<th width="10%">Size</th>		
		<th width="20%">Name</th>
		<th width="50%">Value</th>
		<th width="10%">Autoload</th>
	</thead>
	<tbody>
	<?php foreach($options as $o) : ?>
		<tr>
			<?php 
				list($name, $size, $v) = $o;			
			?>
			<td><?php echo size_format($size)?></td>
			<th valign=top><?php echo $name; ?></th>
			<td>
				<?php 
					$v= maybe_unserialize($v);
					if( is_array($v) ){
						?>
							<details>
								<summary>Expand</summary>
								<pre><?=var_dump($v)?></pre>
							</details>
						<?php
					}
					else {
						if(is_object($v)){
							print_r($v);
						}
						else{
							$v = htmlentities($v);
							if( is_numeric($v) )
								echo "<span class=cBlue>{$v}</span>";
							elseif( is_string($o->option_value) )
								echo "<span class=cGreen>{$v}</span>";				
							else
								echo "<span class=cOrange>{$v}</span>";
						}
					}
				?>
			</td>
			<td><?php echo $o->autoload?></td>
		</tr>
	<?php endforeach; ?>
	</tbody>
</table>

