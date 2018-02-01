jQuery(function($){
	jQuery('#search_hooks').click(function(){  
		var search = jQuery('#action_search').val();
		var type = jQuery('#search_type').val();
		jQuery.ajax({
			url: ajaxurl,
			type: 'POST',
			data: {action:'sysinfo_search_hooks',
				search:search,type:type},
			success:function(h){
				jQuery('#hook_area').html(h);
			}
		});
	});

	jQuery('.td-expand a').click(function(){
		console.log('test');
		$(this).parent().toggleClass('show');
		return false;
	});
	
});


function optimizeTable(table){
	jQuery.ajax({
		url: ajaxurl,
		data:{action:'sysinfo_optimize_table', table:table},
		dataType:'html',
		success:function(h){
			h = '<div>'+h+'</div>';
			jQuery(h).dialog({
				modal: true,
				buttons: { Ok: function() { jQuery( this ).dialog( "close" );}}
			});
		}
	});
	
	jQuery('.wpcron_jobs').accordion({collapsible: true,allwayOpen: false});
	
}