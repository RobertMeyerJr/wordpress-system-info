<?php 

class Console{
	protected static $log = [];
	protected static $timers = [];

	public static function stopwatch($name){
		if( !empty(self::$timers[$name]) ){
			$total_time = microtime(true) - self::$timers[$name];
			self::timerLog("{$name} took ".number_format($total_time,4).'s');
			unset( self::$timers[$name] );
		}
		else{
			self::$timers[$name] = microtime(true);
		}
	}

	public static function findArgName($trace){
		
		$t =  $trace[0]['file'] == __FILE__ ? $trace[1] : $trace[0];
		$file = $t['file'];
		
		$regex = "/Console::[^\(]+\(([^)]*)/";
		if( is_file($file) ){
			$lines = file($file);//file in to an array
			$line = $lines[ $t['line']-1 ];
			preg_match($regex, $line, $matches);
			if(empty($matches[1]) || $matches[1][0] == "'"){
				return '';
			}
			else{
				 return "<span class=\"var\">{$matches[1]}</span> &nbsp;";
			}
		}		
		return '';
	}

	public static function log($msg, $type='info', $where=false){
		$bt = debug_backtrace();
		
		if( empty($where) ){
			$where = empty($bt[1]) ? [] : $bt[1];
		}
		else{			
			$where = empty($bt[2]) ? [] : $bt[2];
		}

		self::$log[] = [
			'date'	=> microtime(true),
			'name'	=> self::findArgName($bt),
			'type'	=> $type,
			'trace'	=> $where,
			'msg'	=> $msg
		];
	}
	protected static function timerLog($msg){ self::log($msg, 'time'); }
	public static function debug($msg){ self::log($msg,'debug'); }
	public static function info($msg){ self::log($msg,'info'); }
	public static function error($msg){ self::log($msg,'error'); }
	public static function warn($msg){ self::log($msg,'warning'); }
	public static function warning($msg){ self::log($msg,'warning'); }
	public static function success($msg){ self::log($msg,'success'); }
	
	public static function countLog(){
		return count(self::$log);
	}

	public static function getLog(){
		return self::$log;
	}

	protected function outputVar($v, $depth=2){
		//Type
		//Tree View w/ limit
	}

}