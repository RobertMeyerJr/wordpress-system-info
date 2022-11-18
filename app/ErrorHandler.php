<?php 

class SI_ErrorHandler{
	//Custom Error Handling
	public static function enable_error_handling(){
		//Make sure we are logged in and an admin
		set_error_handler( array(__CLASS__,'error_handler'), E_ALL );
		set_exception_handler( array(__CLASS__,'exception_handler'));
		register_shutdown_function( array(__CLASS__,'shutdown_function') );	
	}


	protected static function return_bytes($val){ 
		$val = trim($val); 
		if( !is_numeric($val) ){
			return $val;
		}
		$last = strtolower($val[strlen($val) - 1]);
		switch ($last) {
			case 'g': $val *= 1024; 
			case 'm': $val *= 1024; 
			case 'k': $val *= 1024; 
		} 
	 
		return $val; 
	} 

	public static function exception_handler($ex){
		#UnCaught Exception
		
		$out = ob_get_clean();
		echo "<h1>Fatal Exception</h1>";
		echo "<pre>
				{$ex->getCode()}
				<p>{$ex->getMessage()}</p>
				{$ex->getFile()} 
				Line: {$ex->getLine()}
		</pre>";
		self::error_handler($ex->getCode(),$ex->getMessage(),$ex->getFile(),$ex->getLine(),null);
		exit;
	}

	public static function error_handler($errno, $str="",$file=null,$line=null,$context=null){ 
		global $SI_Errors;	
		
		#$mem_usage = memory_get_usage();
		#$mem_limit = self::return_bytes(ini_get('memory_limit'));
		
		//Todo: Only backtrace if we haven't seen the error before, make sure not near mem limit
		$trace = debug_backtrace(false, 20);
		
		$SI_Errors[] = array(
			$errno,
			$str,
			$file,
			$line,
			$context,
			$trace ?? false
		);

		//Fatal Error?
		if($errno === E_USER_ERROR){
			echo "<h1>Fatal Error</h1>";
			$out = ob_get_clean();
			echo $out;
			exit;
		}				
		return false; #Just record the error, don't catch or do anything
	}		
	public static function fatal_error($error){
		global $wpdb;
		//Handle fatal error
		if( ob_get_contents() ){
			ob_clean();
		}
		echo "<h1>WP Total Details: Fatal Error Caught:</h1>";
		echo "<p>{$error['message']}</p>";
		echo "<p>File: {$error['file']} : Line {$error['line']}</p>";
		var_dump($error);
		echo "<h2>Backtrace</h2>";
		echo "<pre>".print_r(debug_backtrace(),true)."</pre>";
		#echo "<h2>Last Query</h2>";
		#echo "<pre>".print_r($wpdb->last_query,true)."</pre>";
	}
	public static function shutdown_function(){
		if( is_td_debug() || function_exists('current_user_can') && current_user_can('manage_options') ){
			//Nothing in here right now
			$error = error_get_last();
			if($error !== NULL && $error['type'] === E_ERROR) {
				self::fatal_error($error);			
			}
		}
	}

}
