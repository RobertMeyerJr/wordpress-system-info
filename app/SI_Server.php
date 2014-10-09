<?php 

class SI_Server{
	public static function disk_usage(){
		
	}
	public static function get_cpu_usage() {
		if(!function_exists('getrusage'))
			return false;
		$d = getrusage();	
		if(!defined('PHP_TUSAGE')){
			define('PHP_TUSAGE', microtime(true));
			define('PHP_RUSAGE', $d["ru_utime.tv_sec"]*1e6+$d["ru_utime.tv_usec"]);
			return;
		}
		else{
			$d["ru_utime.tv_usec"] = ($d["ru_utime.tv_sec"]*1e6 + $d["ru_utime.tv_usec"]) - PHP_RUSAGE;
			$time = (microtime(true) - PHP_TUSAGE) * 1000000;
			if($time > 0) {
				$cpu = sprintf("%01.2f", ($d["ru_utime.tv_usec"] / $time) * 100);
			} else {
				$cpu = '0.00';
			}	 
		}
		return $cpu;
	}
}