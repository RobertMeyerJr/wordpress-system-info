<?php
global $wp_rewrite;
/*
$rules = ( System_Info_Tools::is_windows() ) ? file_get_contents(ABSPATH.'/web.config') : file_get_contents(ABSPATH.'/.htaccess');		
if( System_Info_Tools::is_windows() )
	echo "<pre>".System_Info_Tools::xml_highlight( $rules )."</pre>";
else
	echo "<pre>".htmlspecialchars($rules)."</pre>";
*/
var_dump($wp_rewrite);