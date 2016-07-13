<?php 

global $wpdb;
$out = $wpdb->get_results('SHOW VARIABLES',ARRAY_A);
System_Info_Tools::out_table( $out, null, true);	