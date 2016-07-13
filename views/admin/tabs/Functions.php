<?php
/*
http://php.net/manual/en/language.functions.php
http://codex.wordpress.org/Function_Reference
http://phpxref.ftwr.co.uk/buddypress/nav.html?_functions/index.html
*/
$classes = get_declared_classes();
sort($classes);				
?>
<style>
code{white-space:pre;}
</style>
<label>Location</label>
	<select id=sysinfo_class>
		<optgroup label=Global>
			<option>(Internal)</option>
			<option>(User)</option>
		</optgroup>
		<optgroup label=Classes>
			<?php foreach($classes as $c) : ?>
				<option><?php echo $c?></option>
			<?php endforeach; ?>
		</optgroup>
	</select>
<label>Function Name</label>
<input type=text id=function_search>
<button id=search_functions>Search</button>
<div><ul id=func_area></ul></div>
<script>
	jQuery('#search_functions').click(function(){  
		var search = jQuery('#function_search').val();
		jQuery.ajax({
			url: ajaxurl,
			type: 'POST',
			data: {
				action:'sysinfo_search_functions',
				search:search,
				class_name: jQuery('#sysinfo_class').val(),
			},
			success:function(h){
				jQuery('#func_area').html(h);
			}
		});
	});
</script>