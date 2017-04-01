<div class=tab-nav>	
	<ul>
		<li><i class=fa-cogs></i> Core</li>
		<li><i class=fa-image></i> Theme</li>	
		<li><i class=fa-plug></i> Plugin</li>
		<li><i class=fa-question-circle></i> Other</li>
	</ul>
</div>
<input type=text id=included_file_search>

<table class=dbg_table>
	<thead>
		<tr>
			<thead>
				<th>Order</th>
				<th>Part</th>
				<th>File</th>
			</thead>
		</tr>
	</thead>
	<tbody id=included_files>	
		<?php 
			$plugins_path = 'wp-content'.DIRECTORY_SEPARATOR.'plugins';
			$part_counts = [];
		?>
		<?php $i=1; foreach($files as $f): ?>	
			<?php 
				/*
				TODO: Keep Count
				Extract out plugin name
				*/			
				if(false !== strpos($f, $plugins_path)){
					$part = 'Plugin';						
				}
				else if(false !== strpos($f, 'wp-admin')){
					$part = 'WP-Admin';
				}
				else if(false !== strpos($f, WP_CONTENT_DIR)){
					$part = 'Theme';
				}
				else if(false !== strpos($f, WPINC)){
					$part = 'WP-Includes';
				}
				else if(false !== strpos($f, ABSPATH)){
					$part = 'WP-Core';
				}
				else{
					$part = 'Other';
				}
				
				if( empty($part_counts[$part]) )
					$part_counts[$part] = 1;
				else
					$part_counts[$part]++;
			?>
			<tr class="part_<?=$part?>">
				<td><?php echo $i++?></td>				
				<td><?php echo $part?></td>
				<td class="included_file file-<?php echo $part?>"><?php echo $f?></td>				
			</tr>
		<?php endforeach; ?>
	</tbody>
</table>

<?php #d( $part_counts) ; ?>