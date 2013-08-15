<?php 

if(isset($_GET['sysinfo_bench']) && $_GET['sysinfo_bench']==1){
	$bench_path = ABSPATH.'wp-content/plugins/wordpress-system-info/inc/SI_Bench.php';
	if( !file_exists($bench_path) ){
		echo "Could not find the file {$bench_path}";
		exit;
	}
	else
		include( $bench_path );
}