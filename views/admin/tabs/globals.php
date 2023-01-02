<?php if ( !defined('ABSPATH') ){ die('-1'); } ?>
<h5><label>Search</label> <input class=dbg_search><br/></h5>
<style>
.value-info.expanded{white-space: pre;}
</style>
<?php System_Info_Tools::dbg_table_out(get_defined_constants(true)); ?>
<script>
jQuery(function($){
	$('.value-info').click(function(){ $(this).toggleClass('expanded') });

	$('input.dbg_search').change(function(){
		var v = $(this).val();
		if(v.length){
			$('table.dbg_out tr').hide();
			$('table.dbg_out tr:contains('+v+')').show();
		}
		else{
			$('table.dbg_out tr').show();
		}
	});
});    
</script>
