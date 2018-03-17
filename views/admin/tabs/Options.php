<?php 
global $wpdb;

$options = $wpdb->get_results("SELECT * FROM {$wpdb->options}");


?>

<table class='widefat striped'>
	<thead>
	<tr>
		<th>Name</th>
		<th>Autoload</th>
		<th>Size</th>
		<th>Value</th>		
	</thead>
	<tbody>
	<?php foreach($options as $o) : ?>
		<tr>
			<?php 
				$v = $o->option_value;
				$size = size_format( strlen($o->option_value) );					
				$v = maybe_unserialize($v);			
			?>
			<th><?php echo $o->option_name; ?></th>
			<td><?php echo $o->autoload?></td>
			<td><?php echo $size?></td>
			<td>
				<?php 
					if( is_array($v) ){
						?>
							<div class=td-expand>
								<a href="#">Read More</a>
								<div>
									<pre><?=var_dump($v)?></pre>
								</div>
							</div>
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
			
		</tr>
	<?php endforeach; ?>
	</tbody>
</table>

