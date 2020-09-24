<?php 
ob_start();
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
			
			$files 					= get_included_files();
			$included_file_count 	= count($files);
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
<?php $html = ob_get_clean(); ?>

<div class=tab-nav>	
	<ul class=file-categories>
		<li data-area="WP-Core"><i class=fa-cogs></i> Core <?=$part_counts['WP-Core']?></li>
		<li data-area=WP-Includes><i class=fa-cogs></i> WP Includes <?=$part_counts['WP-Includes']?></li>
		<li data-area=WP-Admin><i class=fa-cogs></i> WP Admin <?=$part_counts['WP-Admin']?></li>
		<li data-area=Theme><i class=fa-image></i> Theme <?=$part_counts['Theme']?></li>	
		<li data-area=Plugin><i class=fa-plug></i> Plugin <?=$part_counts['Plugin']?></li>
		<li data-area=Other><i class=fa-question-circle></i> Other <?=$part_counts['Other']?></li>
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

