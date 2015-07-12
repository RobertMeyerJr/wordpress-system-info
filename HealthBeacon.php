<?php 

define('ACCESS_KEY','o3rgqh3p354yp3h45yp13y');

$healthBeacon = new HealthBeacon();
$healthBeacon->run();

class HealthBeacon{
	
	protected static $watch_folders = array(
		'WWW'	=> '/var/www',
		'Logs'	=> '/var/log',
		'Mail'	=> '/var/mail/root',
		#'Plesk Backups'=> '/var/lib/psa/dumps'
	);	
	
	public function run(){
		if( function_exists('add_action') ){ 
			self::wordpress();
		}
		else{
			self::beacon();
		}
	}
	
	public function beacon(){
		if(!isset($_POST['key']) || $_POST['key']!= ACCESS_KEY){
			http_response_code(401);
			echo "Access Denied";
			exit;
		}	
	}
	
	public function wp_beacon(){
	}
	
	public function wordpress(){
		//Am I running as a wordpress plugin?
		//Add actions for ajax
		//get # posts
		//get # comments
		//get # users
		//get wp version
		//get plugins and versions	
	}
	function is_exec_allowed(){
		if( !function_exists('exec') ){
			return false;
		}
		else{
			return exec('echo EXEC') == 'EXEC';
		}
	}
	
	function watch_folders(){
		$data = array();
		foreach(as $key=>$folder){
			$data[] =	array($key,$folder,exec('du --max-depth=1 -h '.$folder));
		}
		return $data;
	}
	
	//If Windows
	//Quit	
	
	//Memory				"cat /proc/meminfo"
	//Disks And Usage		"df -h"
	//OS					"php_uname"
	//Services				""
	//Cron					"crontab -l"
	//Processes				"ps -ef"
	//Ports?				"netstat -tulpn"
	//network info 			ifconfig?
	//Backups?
	//Hostname				php_uname( 'n' )	
	//	Disk Usage for Common Folders
	//	Ability to add watch folders?
	//		du --max-depth=1 -h /var/www
	//--------------------------------------------
	//	On Browser/Server side
	//	Check Uptime
	//	Check SSL
	//	Response Time
	//	UA Code
	//	Robots	

	//--------------Web Report Info
	//	Daily Email
	//	Weekly Email
	
	
}



