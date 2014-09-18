<?php

class Log_Highlight{
	public static function file($file){
		return SELF::string( file_get_contents($file) );
	}
	public static function string($str){
		$lines = explode(PHP_EOL, $str); 
		foreach($lines as $line){
			echo self::line($line);
		} 
	}
	public static function line($line){	
		$pos_end_bracket = strpos($line,']',1);
		$time 	= date('m/d/Y h:ia', strtotime(substr($line, 1, $pos_end_bracket)) );
		$level 	= substr($line, $pos_end_bracket, strpos($line,':',$pos_end_bracket) - $pos_end_bracket);
		return "<span class=time>{$time}</span><span>{$level}</span>".$line;
		#Escape %'s for Console_Color
		#$line = preg_replace('/%/', '%%', $line);
		#Remove referer
		#$line = preg_replace('/, referer: .+$/', '', $line);
		#Remove [error]
		#$line = preg_replace('/(\[error\]) /', '', $line);
		#Remove [client ...]
		#$line = preg_replace('/(\[client[^\[\]]+\]) /', '', $line);
		#Highlight and simplify timestamp
		#$line = preg_replace('/\[[^\[\]]+(\d{2}:\d{2}:\d{2})[^\[\]]+\] /', '[%y\\1%n] ', $line);
		#Highlight depth in stack traces and file and line number
		#$line = preg_replace('/( #\d+) (.*)\((\d+)\):/', '%g\\1%n %b\\2%n [%g\\3%n]', $line);
		#Highlight depth in stack traces, non files
		#$line = preg_replace('/( #\d+)/', '%g\\1%n', $line);
		#Highlight PHP Stack trace: head
		#$line = preg_replace('/PHP (Stack trace):/', 'PHP %g\\1%n:', $line);
		#Highlight PHP error type and message
		#$line = preg_replace('/PHP ([a-zA-Z]+):  (.*) in (.*) on line (\d+)/', 'PHP %r\\1%n: %k\\2%n in %b\\3%n [%g\\4%n]', $line);
		#Highlight PHP Fatal error:
		#$line = preg_replace('/PHP (Fatal error):  (.*) in (.*) on line (\d+)/', 'PHP %R\\1%n: %k\\2%n in %b\\3%n [%g\\4%n]', $line);
		#Highlight PHP Fatal error stack trace lines
		#$line = preg_replace('/PHP\s+(\d+)\.\s+([^\s]+)\s+(.*):(\d+)/', '%g#\\1%n %b\\3%n [%g\\4%n] \\2', $line);
		#Strip paths relative to cwd
		#$line = preg_replace('/' . preg_quote(getcwd(), '/') . '\//', '', $line);
		return $line;
	}
}
 
