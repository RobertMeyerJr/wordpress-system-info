<?php 

global $WP_TIMING;
#print_r($WP_TIMING);
ob_start();
$plugins = get_option('active_plugins');
?>
<style>
.file-categories{width:100%;}
.file-categories li{
	padding:5px;
	cursor:pointer;
}
.file-categories li:hover{
	background-color:#f9f9f9;
}
.file-categories li.active{
	background-color:#dadbdc;
}
#included_file_search{display:inline-block;width:150px;}
</style>
<?php
$files 					= get_included_files();
$included_file_count 	= count($files);
?>
<h1>Included Files <?=number_format($included_file_count)?></h1>
<?php#System_Info::$templates) ?>
<?php 
$plugin_details = [];

foreach($plugins as $p){
	$plugin_folder = substr($p,0,strpos($p,'/'));
	$plugin_details[$plugin_folder] = $p;
}
?>
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
			$part_counts = [];
			$plugin_file_counts=[];
			$plugin_file_sizes=[];
		?>
		<?php $i=1; foreach($files as $f): ?>	
			<?php 
				if(false !== strpos($f, WP_PLUGIN_DIR)){
					$part = 'Plugin';
					$plg = strtok(str_replace(WP_PLUGIN_DIR,'',$f),'/');
					if(!empty($plugin_file_counts[$plg])){
						$plugin_file_sizes[$plg] += filesize($f);
						$plugin_file_counts[$plg]++;
					}
					else{
						$plugin_file_sizes[$plg] = filesize($f);
						$plugin_file_counts[$plg] = 1;
					}
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
<?php $html = ob_get_clean(); ?>
<table>
	<thead>
		<tr>
			<th>Plugin</th>
			<th>Path</th>
			<th>File Count</th>
			<th>Size</th>
		</tr>
	</thead>
	<?php arsort($plugin_file_counts); ?>
	<?php foreach($plugin_file_counts as $plg=>$count) : ?>
	<tr>
		<th><?=$plg?></th>
		<td><?=$plugin_details[$plg] ?? ''?></td>
		<td><?=number_format($count)?></td>
		<td><?=size_format($plugin_file_sizes[$plg])?></td>
	</tr>
	<?php endforeach; ?>
</table>
<div class=tab-nav>	
	<ul class=file-categories>
		<li data-area="WP-Core">Core <?=$part_counts['WP-Core']?></li>
		<li data-area=WP-Includes>WP Includes <?=$part_counts['WP-Includes']?></li>
		<li data-area=WP-Admin>WP Admin <?=$part_counts['WP-Admin']?></li>
		<li data-area=Theme>Theme <?=$part_counts['Theme']?></li>	
		<li data-area=Plugin>Plugin <?=$part_counts['Plugin']?></li>
		<li data-area=Other>Other <?=$part_counts['Other']?></li>
		<li>
			<input type=text id=included_file_search />
			<button class="" id=included_file_search_do>Search</button>
		<li>
	</ul>
</div>
<?php echo $html; ?>
<script>
jQuery(function($){
	$('.file-categories li[data-area]').click(filterFiles);
	function filterFiles(){
		$('#included_files tr').show();

		if( $(this).hasClass('active') ){
			$('.file-categories li').removeClass('active');
		}
		else{
			var area = $(this).data('area');
			$('#included_files tr:not(.part_'+area+')').hide();
			$('.file-categories li').removeClass('active');
			$(this).addClass('active');
		}	
		
		
	}
	function searchFiles(){
		$('.file-categories li').removeClass('active');
		$('#included_files tr').show();
		$('#included_files tr:not(:contains('+$(this).val()+'))').hide();
	}
});
</script>

