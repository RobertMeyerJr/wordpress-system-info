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
			$this->wordpress();
		}
		else{
			$this->beacon();
		}
	}
	
	public function beacon(){
		if(!isset($_REQUEST['key']) || $_REQUEST['key']!= ACCESS_KEY){
			http_response_code(401);
			echo "Access Denied";
			exit;
		}	
		else{
			$start = microtime(true);			
			$o = [];
			
			$is_windows = (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN');
			$can_exec = $this->is_exec_allowed();
			
			$o['free_disk_bytes'] 	= disk_free_space( $_SERVER['DOCUMENT_ROOT'] );
			$o['total_disk_bytes'] 	= disk_total_space( $_SERVER['DOCUMENT_ROOT'] );
			$o['used_disk'] 		= $total_disk_bytes - $free_disk_bytes;
			$o['perc_used'] 		= number_format($used_disk * 100 / $total_disk_bytes,2);
		
			$uname = php_uname();
			
			if( !$is_windows ){
				exec('cat /proc/meminfo', $mem_info);
				exec('cat /proc/cpuinfo',	$cpu_info);
				exec('ps -ef', $procs);
				exec('uptime', $uptime);	
				exec('netstat -tulpn', $netstat);
				exec('crontab -l', $cron);
				
				$o['mem_info']	= self::splitNameValuePairs($mem_info);
				$o['cpu_info']	= self::splitNameValuePairs($cpu_info);
				$o['procs']		= $procs;
				$o['uptime'] 	= $uptime;
				$o['netstat'] 	= $netstat;
				$o['crontab'] 	= $crontab;
			
				//exec('du -h', $df);
				//exec('df -h', $df);
			}
			else{
				#$wmi = 
				//	"wmic cpu get loadpercentage"
				
				exec("net statistics workstation | find 'Statistics since'", $uptime);
			}
						
			echo json_encode($o);
			exit;
		}
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
	
	protected function watch_folders(){
		$data = array();
		foreach(self::$watch_folders as $key=>$folder){
			$data[] =	array($key,$folder,exec('du --max-depth=1 -h '.$folder));
		}
		return $data;
	}
	
	protected function splitNameValuePairs($lines){
		$out = [];
		foreach($lines as $l){
			if(!empty($l)){
				list($prop,$val) = explode(':',$l, 2);						
				$out[trim($prop)] = trim($val);
			}
		}
		return $out;
	}
	
	//network info 			ifconfig?
	
	//	Disk Usage for Common Folders
	//	Ability to add watch folders?
	//		du --max-depth=1 -h /var/www

	//--------------Web Report Info
	//	Daily Email
	//	Weekly Email	
	
}



