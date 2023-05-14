<?php if ( !defined('ABSPATH') ){ die('-1'); } ?>
<?php
/*
http://php.net/manual/en/language.functions.php
http://codex.wordpress.org/Function_Reference
http://phpxref.ftwr.co.uk/buddypress/nav.html?_functions/index.html
*/
$classes = get_declared_classes();
sort($classes);				

#See where function is defined
#$details = new ReflectionFunction($FUNCTION_NAME);
#print $details->getFileName() . ':' . $details->getStartLine();
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
<script src="https://cdnjs.cloudflare.com/ajax/libs/selectize.js/0.15.2/js/selectize.min.js" integrity="sha512-IOebNkvA/HZjMM7MxL0NYeLYEalloZ8ckak+NDtOViP7oiYzG5vn6WVXyrJDiJPhl4yRdmNAG49iuLmhkUdVsQ==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/selectize.js/0.15.2/css/selectize.css" integrity="sha512-Y+AaVWdRf6zsGm7eV+EGOIuqYZoi2wUQ7wF8oHbnLy8k2zdVGSxyjn2qDUMFkLRy/9mqOAE5BeyEqx1yxDTQIw==" crossorigin="anonymous" referrerpolicy="no-referrer" />
<script>
jQuery(function($){
	$('#sysinfo_class').selectize({
		optgroupField: 'Display',
    	optgroupLabelField: 'Display',
    	optgroupValueField: 'Index',
	});
	
	$('#search_functions').click(function(){  
		var search = $('#function_search').val();
		$.ajax({
			url: ajaxurl,
			type: 'POST',
			data: {
				action:'sysinfo_search_functions',
				search:search,
				class_name: $('#sysinfo_class').val(),
			},
			success:function(h){
				$('#func_area').html(h);
			}
		});
	});
});
</script>
