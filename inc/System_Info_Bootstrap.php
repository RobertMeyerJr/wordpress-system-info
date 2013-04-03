<?php 

if(isset($_GET['sysinfo_bench']) && $_GET['sysinfo_bench']==1){
	include( ABSPATH.'wp-content/plugins/system-info/inc/SI_Bench.php' );
}