<h3>Globals</h3>
<h5><label>Search</label> <input class=dbg_search><br/></h5>
<?php 
	#if( !empty($GLOBALS) ){
		#dbg_table_out($GLOBALS);
	#}
	dbg_table_out(get_defined_constants(true));
?>