<?php if ( !defined('ABSPATH') ){ die('-1'); } ?>
<h5><label>Search</label> <input class=dbg_search><br/></h5>
<style>
.value-info.expanded{white-space: pre;}
</style>
<?php System_Info_Tools::dbg_table_out(get_defined_constants(true)); ?>
<script>
jQuery(function($){
    jQuery(function($){
		$('.value-info').click(function(){ $(this).toggleClass('expanded') });
	});
});    
</script>
