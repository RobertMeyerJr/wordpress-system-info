<?php 
function query_type($sql){
	/*
	SELECT
	UPDATE
	INSERT	
	return [type,READ or WRITE]
	*/	
}
function print_filters_count($hook){
	global $wp_filter;
	return count($wp_filter[$hook]);
}
function print_filters_for( $hook = null ) {
    global $wp_filter;
    if( !empty($hook) && !isset( $wp_filter[$hook] ) )
        return false;
    print '<pre>';
		if(empty($hook))
			print_r( $wp_filter );
		else
			print_r( $wp_filter[$hook] );
    print '</pre>';
}
function hilight_trace_part($str){
	//require_once
	//require
	//->
	//::
	//()
	return $str;
}
function dbg_style_out($v){
	if(is_array($v) || is_object($v)){	
		$str = var_export($v, true);
		$str = htmlentities($str);
		return "<pre>{$str}</pre>";		
	}
	elseif( is_numeric($v) ){		
		return "<span class=int>{$v}</span>";
	}
	else{
		htmlentities( $v ); 
		return "<span class=str>{$v}</span>";
	}
}
function dbg_table_out($arr){
	if( empty($arr) ){
		return;
	}
	echo "<table class=dbg_out>";
		echo "<thead><tr><th>Key</th><th>Value</th></tr></thead>";
		echo "<tbody>";
		$skip = array('_COOKIE','_FILES','_ENV','GLOBALS','_SERVER','_REQUEST','_GET','_POST','wp_filter');
		foreach($arr as $k=>$v){
			if( in_array($k,$skip) )
				continue;
			$value = $v;
			echo "<tr><th>{$k}</th><td>".dbg_style_out($value)."</td></tr>";
		}
		echo "</tbody>";
	echo "</table>";
}
?>