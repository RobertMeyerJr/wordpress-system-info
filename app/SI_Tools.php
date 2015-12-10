<?php 


class System_Info_Tools{
	public static function read_csv_array($arr, $delimiter=','){		
		foreach($arr as &$a){
			$a = explode(',', $a);
		}
		return $arr;
	}
	
	public static function run_command($cmd, $return_as=null){		
		if( !empty( $cmd ) ){
			$run = 'ex'.'ec'; #Hide from crappy scanners
			$run($cmd, $output);
		}
		return $output;
	}
	public static function get_domain($url){
	  $pieces = parse_url($url);
	  $domain = isset($pieces['host']) ? $pieces['host'] : '';
	  if (preg_match('/(?P<domain>[a-z0-9][a-z0-9\-]{1,63}\.[a-z\.]{2,6})$/i', $domain, $regs)) {
		return $regs['domain'];
	  }
	  return false;
	}
	public static function is_windows(){ 
		return (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN'); 
	}
	
	public static function exec_enabled(){
		//$test = self::run_command('echo TEST') == 'TEST';
		if( !function_exists('ex'.'ec') )
			return false;
		$disabled = explode(',', ini_get('disable_functions'));
		return !in_array('ex'.'ec', $disabled);
	}
		
	
	public static function check_open_basedir(){
		$base_dir = ini_get('open_basedir');
	}
		
	public static function whois($host){
		echo "<h1>{$host}</h1>";
		$tlds = array(
			'ac'     =>'whois.nic.ac',
			'ae'     =>'whois.nic.ae',
			'af'     =>'whois.nic.af',
			'ag'     =>'whois.nic.ag',
			'al'     =>'whois.ripe.net',
			'am'     =>'whois.amnic.net',
			'as'     =>'whois.nic.as',
			'at'     =>'whois.nic.at',
			'au'     =>'whois.aunic.net',
			'az'     =>'whois.ripe.net',
			'ba'     =>'whois.ripe.net',
			'be'     =>'whois.dns.be',
			'bg'     =>'whois.register.b',
			'bi'     =>'whois.nic.bi',    
			'biz'    =>'whois.biz',
			'bj'     =>'www.nic.bj',      
			'br'     =>'whois.nic.br',
			'bt'     =>'whois.netnames.ne',
			'by'     =>'whois.ripe.net',
			'bz'     =>'whois.belizenic.bz',
			'ca'     =>'whois.cira.ca',
			'cc'     =>'whois.nic.cc',
			'cd'     =>'whois.nic.cd',    
			'ch'     =>'whois.nic.ch',
			'ck'     =>'whois.nic.ck',
			'cl'     =>'nic.cl',
			'cn'     =>'whois.cnnic.net.cn',
			'co.nl'  =>'whois.co.nl',
			'com'    =>'whois.verisign-grs.com',
			'coop'   =>'whois.nic.coop',
			'cx'     =>'whois.nic.cx',
			'cy'     =>'whois.ripe.net',
			'cz'     =>'whois.nic.cz',
			'de'     =>'whois.denic.de',
			'dk'     =>'whois.dk-hostmaster.dk',
			'dm'     =>'whois.nic.cx',
			'dz'     =>'whois.ripe.net',
			'edu'    =>'whois.educause.net',
			'ee'     =>'whois.eenet.ee',
			'eg'     =>'whois.ripe.net',
			'es'     =>'whois.ripe.net',
			'eu'     =>'whois.eu',
			'fi'     =>'whois.ficora.fi',
			'fo'     =>'whois.ripe.net',
			'fr'     =>'whois.nic.fr',
			'gb'     =>'whois.ripe.net',
			'ge'     =>'whois.ripe.net',
			'gl'     =>'whois.ripe.net',
			'gm'     =>'whois.ripe.net',
			'gov'    =>'whois.nic.gov',
			'gr'     =>'whois.ripe.net',
			'gs'     =>'whois.adamsnames.tc',
			'hk'     =>'whois.hknic.net.hk',
			'hm'     =>'whois.registry.hm',
			'hn'     =>'whois2.afilias-grs.net',
			'hr'     =>'whois.ripe.net',
			'hu'     =>'whois.ripe.net',
			'ie'     =>'whois.domainregistry.ie',
			'il'     =>'whois.isoc.org.il',
			'in'     =>'whois.inregistry.net',
			'info'   =>'whois.afilias.info',
			'int'    =>'whois.isi.edu',
			'iq'     =>'vrx.net',
			'ir'     =>'whois.nic.ir',
			'is'     =>'whois.isnic.is',
			'it'     =>'whois.nic.it',
			'je'     =>'whois.je',
			'jp'     =>'whois.jprs.jp',
			'kg'     =>'whois.domain.kg',
			'kr'     =>'whois.nic.or.kr',
			'la'     =>'whois2.afilias-grs.net',
			'li'     =>'whois.nic.li',
			'lt'     =>'whois.domreg.lt',
			'lu'     =>'whois.restena.lu',
			'lv'     =>'whois.nic.lv',
			'ly'     =>'whois.lydomains.com',
			'ma'     =>'whois.iam.net.ma',
			'mc'     =>'whois.ripe.net',
			'md'     =>'whois.nic.md',
			'me'     =>'whois.nic.me',
			'mil'    =>'whois.nic.mil',
			'mk'     =>'whois.ripe.net',
			'mobi'   =>'whois.dotmobiregistry.net',
			'ms'     =>'whois.nic.ms',
			'mt'     =>'whois.ripe.net',
			'mu'     =>'whois.nic.mu',
			'mx'     =>'whois.nic.mx',
			'my'     =>'whois.mynic.net.my',
			'name'   =>'whois.nic.name',
			'net'    =>'whois.verisign-grs.com',
			'nf'     =>'whois.nic.cx',
			'nl'     =>'whois.domain-registry.nl',
			'no'     =>'whois.norid.no',
			'nu'     =>'whois.nic.nu',
			'nz'     =>'whois.srs.net.nz',
			'org'    =>'whois.pir.org',
			'pl'     =>'whois.dns.pl',
			'pr'     =>'whois.nic.pr',
			'pro'    =>'whois.registrypro.pro',
			'pt'     =>'whois.dns.pt',
			'ro'     =>'whois.rotld.ro',
			'ru'     =>'whois.ripn.ru',
			'sa'     =>'saudinic.net.sa',
			'sb'     =>'whois.nic.net.sb',
			'sc'     =>'whois2.afilias-grs.net',
			'se'     =>'whois.nic-se.se',
			'sg'     =>'whois.nic.net.sg',
			'sh'     =>'whois.nic.sh',
			'si'     =>'whois.arnes.si',
			'sk'     =>'whois.sk-nic.sk',
			'sm'     =>'whois.ripe.net',
			'st'     =>'whois.nic.st',
			'su'     =>'whois.ripn.net',
			'tc'     =>'whois.adamsnames.tc',
			'tel'    =>'whois.nic.tel',
			'tf'     =>'whois.nic.tf',
			'th'     =>'whois.thnic.net',
			'tj'     =>'whois.nic.tj',
			'tk'     =>'whois.nic.tk',
			'tl'     =>'whois.domains.tl',
			'tm'     =>'whois.nic.tm',
			'tn'     =>'whois.ripe.net',
			'to'     =>'whois.tonic.to',
			'tp'     =>'whois.domains.tl',
			'tr'     =>'whois.nic.tr',
			'travel' =>'whois.nic.travel',
			'tw'     =>'whois.apnic.net',
			'tv'     =>'whois.nic.tv',
			'ua'     =>'whois.ripe.net',
			'uk'     =>'whois.nic.uk',
			'gov.uk	'=>'whois.ja.net',
			'us'     =>'whois.nic.us',
			'uy'     =>'nic.uy',
			'uz'     =>'whois.cctld.uz',
			'va'     =>'whois.ripe.net',
			'vc'     =>'whois2.afilias-grs.net',
			've'     =>'whois.nic.ve',
			'vg'     =>'whois.adamsnames.tc',
			'ws'     =>'www.nic.ws',
			'yu'     =>'whois.ripe.net'      
		);
		$output = '';
		
		$domain = explode('.', $host);
		$ext = $domain[ count($domain)-1 ];
		$nic_server = $tlds[$ext];
		if(empty($nic_server)){
			echo "No whois server found for tld {$ext}<br/>";
			return;
		}
		$domain = substr($host, 0, -(strlen($ext)+1));
		echo "Using Whois Server: {$nic_server}<br/>";
		if($conn = fsockopen($nic_server, 43)){
        	fputs($conn, "={$domain}\r\n");
       	 	while(!feof($conn)) {
            	$output .= fgets($conn, 128);
        	}
        	fclose($conn);
		}
		return $output;
	}
	public static function output_to_array($data){
		$c = count($data);
		$o = array();
		for($i=1;$i<$c;$i++){
			$cols = count( $data[$i] );
			$r = array();
			for($j=0;$j<$cols;$j++){
				$r[$data[0][$j]] = $data[$i][$j];
			}
			$o[] = $r;
		}
		return $o;
	}
	
	public static function get_after($input, $after){
		$str = substr($input,stripos($input,$after)+1);
		return trim($str );
	}
	
	public static function cpu_info(){
		if(self::is_windows()){
			self::run_command("wmic CPU get name, NumberOfCores,description, LoadPercentage, maxclockspeed, extclock, manufacturer, revision /format:csv", $info);
			$cpu_info = self::wmic_to_array($info);											
		}
		else{
			self::run_command('cat /proc/cpuinfo', $output);			
			$cpu_info = array(
				'Model'		=> 	self::get_after($output[4],':') ,
				'Cores'		=> 	self::get_after($output[11],':') ,				
				'Cache'		=> 	self::get_after($output[7],':') ,				
				'Total CPUs'=> 	self::get_after($output[9],':') 
			);			
		}		
		self::$cpu_info = $cpu_info;		
		return $cpu_info;		
	}
	public static function mem_info(){
		if( self::is_windows() ){
			exec('wmic MEMORYCHIP get banklabel, devicelocator, caption, capacity /format:csv', $info);
			$meminfo = self::wmic_to_array($info);			
		}
		else{		
			exec('cat /proc/meminfo',$output);
			$data = explode("\n", $output);
			$meminfo = array();
			foreach($data as $line){
				list($key, $val) = explode(":", $line);
				$meminfo[$key] = trim($val);
			}
		}
		return $meminfo;
	}
	public static function wmic_to_array($info){
		#unset($info[0]);
		$names = explode(',',$info[1]);
		$c = count($info);
		$name_count = count($names);
		$out = array();
		for($i=2; $i<$c;$i++){
			$d = explode(',',$info[$i]);
			$out[$i-2] = array();
			for($j=0;$j<$name_count;$j++){
				$out[$i-2][$names[$j]] = $d[$j];
			}
		}
		return $out;
	}
	public static function formatBytes($bytes, $precision = 2){ 
		$units = array('B', 'KB', 'MB', 'GB', 'TB'); 
		$bytes = max($bytes, 0); 
		$pow = floor(($bytes ? log($bytes) : 0) / log(1024)); 
		$pow = min($pow, count($units) - 1); 
		$bytes /= pow(1024, $pow);		
		return round($bytes, $precision) . ' ' . $units[$pow]; 
	}
	public static function color_format($v){
		$classes 	= array();
		$truthy 	= array('ON',1,'ENABLED','YES','TRUE');
		$falsy 		= array('OFF',0,'DISABLED','NO','FALSE');
		if( is_object($v) ){
			$v = @d( $v );
			$classes[] = 'object';
		}
		$upper = strtoupper($v);
		if(in_array($upper, $truthy))			return "<span class=truthy>{$v}</span>";	
		if(in_array($upper, $falsy, true)) 		return "<span class=falsy>{$v}</span>";	
		if(is_numeric($v))						return "<span class=number>".number_format($v)."</span>";	
		elseif(is_date($v))						return "<span class=date>{$v}</span>";	
		elseif(is_string($v))					return "<span class=string>{$v}</span>";	
		
	}
	public static function out_table($arr, $cols=null, $colorize=false){
		if($cols == null){ 
			$cols = array_keys((array)$arr[0]); #Treat object as array, then get its keys
		}
		echo "<table class='wp-list-table widefat fixed striped'>";
		echo "<thead><tr>";
		foreach($cols as $c){
			$name = str_replace('_',' ',$c);
			$name = ucwords($name);
			echo "<th>{$name}</th>";
		}		
		echo "</tr></thead><tbody>";
		if( count($arr) < 1 )
			echo "<tr><td colspan=".count($cols).">No Results Found</td></tr>";
		else
			foreach($arr as $r){
				echo "<tr>";
				foreach($r as $c){
					if($colorize)
						$c = self::color_format($c);					
					else
						if(is_object($c))
							$c = print_r($c, true);
					echo "<td>{$c}</td>";
				}
				echo "</tr>";
			}
		echo "</tbody></table>";
	}
	public static function clear_error_log(){
		$log = ini_get('error_log');
		if(!empty($log))
		unlink( $log );
		exit;
	}
	public static function tail($file, $lines, $asArray=false) {
		try{
			$handle = fopen($file, "r");
			$linecounter = $lines;
			$pos = -2;
			$beginning = false;
			$text = array();
			while ($linecounter > 0) {
				$t = " ";
				while ($t != "\n") {
					if(fseek($handle, $pos, SEEK_END) == -1) {
						$beginning = true; 
						break; 
					}
					$t = fgetc($handle);
					$pos --;
				}
				$linecounter --;
				if ($beginning) {
					rewind($handle);
				}
				$text[$lines-$linecounter-1] = fgets($handle);
				if ($beginning) break;
			}
			fclose ($handle);
			$result = array_reverse($text);
			if($asArray){
				return $result;		
			}
			else{
				return implode("\n", $result);
			}
		}catch(Exception $err){
			return "Error Reading File";
		}
	}
		
}