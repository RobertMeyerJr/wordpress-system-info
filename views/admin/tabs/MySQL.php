<?php 

global $wpdb;
$out = $wpdb->get_results('SHOW VARIABLES');
System_Info_Tools::out_table( $out, null, true);	