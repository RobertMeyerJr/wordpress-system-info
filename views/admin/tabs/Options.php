<?php 
global $wpdb;

$options = $wpdb->get_results("SELECT * FROM {$wpdb->options}");

#d($options);

?>

<table class=widefat>
	<thead>
	<tr>
		<th>Name</th>
		<th>Value</th>
		<th>Autoload</th>
	</thead>
	<tbody>
	<?php foreach($options as $o) : ?>
		<tr>
			<th><?php echo $o->option_name; ?></th>
			<td>
				<?php 
					$v = $o->option_value;
					
					$v = maybe_unserialize($v);

					if( is_array($v) ){
						echo "<pre>";
							var_dump($v);
						echo "</pre>";
					}
					else {	
						$v = htmlentities($v);
						if( is_numeric($v) )
							echo "<span class=cBlue>{$v}</span>";
						elseif( is_string($o->option_value) )
							echo "<span class=cGreen>{$v}</span>";				
						else
							echo "<span class=cOrange>{$v}</span>";
					}
				?>
			</td>
			<td><?php echo $o->autoload?></td>
		</tr>
	<?php endforeach; ?>
	</tbody>
</table>