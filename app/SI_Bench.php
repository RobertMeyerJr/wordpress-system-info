<?php 
defined('ABSPATH') or die("Nope!");
//This file gets included by the System_Info_Bootstrap if $_GET['sysinfo_bench']==1
System_Info_Bench::run();
System_Info_Bench::benchmarking();



class System_Info_Bench{
	public static $cpu_info;
	public static $load_time 			= array();
	public static $_profile 			= array();
	public static $_function_count 		= array();
	public static $_hook_history 		= array();
	public static $_total_hook_time 	= array();
	public static $_templates_used 		= array();
	public static $messages 			= array();
	public static $_path;
	public static $_last_time;
	public static $section;
	public static $_CORE_MEM_USAGE;
		
	public static function run(){		
		self::$_path 		= realpath( dirname( __FILE__ ) );
		self::$_last_time 	= microtime(true);
		
		#Add some initial start times
		self::$load_time['start']  		= ( empty($_SERVER['REQUEST_TIME_FLOAT']) ) ? $_SERVER['REQUEST_TIME'] : $_SERVER['REQUEST_TIME_FLOAT'];
		self::$load_time['Core Load'] 	= microtime(true) - self::$load_time['start'];
		
		add_action('init', array(__CLASS__, 'init'));			
	}
	
	public static function init(){
		#The locate_template filter may or may not ever appear 
		#http://core.trac.wordpress.org/ticket/13239		
		wp_enqueue_script('system-info-bench', '/wp-content/plugins/wordpress-system-info/views/benchmark.js', array('jquery'));			
	}
	
	public static function benchmark(){
		global $wpdb;
		$peak_mem = memory_get_peak_usage();
		$wpdb_query_time = 0;
		#Sort by time taken
		$wpdb_queries = isort($wpdb->queries, 1); #nope, change this, isort is from util.php
		$total_query_time = 0;
		foreach($wpdb_queries as $q){
			$wpdb_query_time += $q[1];
			$time = number_format($q[1], 4);
		}
	}
	public static function all_hook($input){
		$args 			= func_get_args();
		$tag 			= array_shift( $args );
		$bt 			= debug_backtrace();
		$hook_type 		= $bt[3]['function'];
		
		self::$_hook_history[] = array(
			'tag'			=>	$tag,
			'type'			=>	$hook_type,
			'time'			=>	microtime(true),
			'arguments'		=>	$args
		);		
		return $input;
	}
	public static function benchmarking(){
		declare(ticks = 1);
		register_tick_function(array(__CLASS__, 'tick'), true);		
	
		$FIRST_ACTION 		= -99999;
		$LAST_ACTION	 	=  99999;
		//We want a hook on all, late and early
		
		add_action( 'all', array(__CLASS__,'all_hook'), $FIRST_ACTION);
		add_action( 'all', array(__CLASS__,'all_hook'), $LAST_ACTION);
		add_action('muplugins_loaded',	array(__CLASS__,'record_core_mem_usage'),	$LAST_ACTION);
		$timers = array(
			'muplugins_loaded','plugins_loaded','setup_theme',
			'after_setup_theme','init','wp_loaded',
			'template_redirect','wp_head','shutdown','the_content'
		);
		foreach($timers as $t){
			add_action($t,	array(__CLASS__,'timer_start'),	$FIRST_ACTION);
			add_action($t,	array(__CLASS__,'timer_stop'),	$LAST_ACTION);
		}
		
		#Used for graphs
		wp_enqueue_style('datatables', '//ajax.aspnetcdn.com/ajax/jquery.dataTables/1.9.4/css/jquery.dataTables.css'); 
		wp_enqueue_style('datatables', '//ajax.aspnetcdn.com/ajax/jquery.dataTables/1.9.4/css/jquery.dataTables_themeroller.css'); 
		wp_enqueue_style('system-info', plugins_url().'/system-info/views/system-info.css');
		
		add_action('init', function(){
			wp_enqueue_script('jquery-ui-dialog');
			wp_enqueue_script('jquery-ui-accordion');
			wp_enqueue_script('jquery-ui-tabs');
			wp_enqueue_style('si-jquery-ui-css','//ajax.googleapis.com/ajax/libs/jqueryui/1.8.21/themes/smoothness/jquery-ui.css');					
		});		
				
		add_action('shutdown', array(__CLASS__, 'benchmark_output'), $LAST_ACTION);
		#Maybe register an error handler to catch all errors?		
	}
	
	public static function record_core_mem_usage(){ self::$_CORE_MEM_USAGE = memory_get_peak_usage(); }
	
	//--------------------Benchmarking 
	public function filter_benchmarking(){	
		//We add our benchmark to all filters, as the first and last action
		add_action('all', array($this,'benchmark_filter_start'), 0); 
		add_action('all', array($this,'benchmark_filter_end'), PHP_INT_MAX);
	}
	public function benchmark_filter_end($param=false){
		global $dbg_filter_times,$dbg_filter_calls,$dbg_filter_stop;
		//This is wrong, it isn't summing the exec time just recording the last time
		$filter = current_filter();
		$stop_time = microtime(true);
		$dbg_filter_stop[$filter] = $stop_time;
		$dbg_filter_times[$filter] = $stop_time - $dbg_filter_times[$filter];
		return $param;
	}
	public function benchmark_filter_start($param=false){
		global $dbg_filter_times,$dbg_filter_calls,$dbg_filter_start;
		
		$filter = current_filter();
		$start 	= microtime(true);
		
		if( !array_key_exists($filter,$dbg_filter_calls) )
			$dbg_filter_calls[$filter] = 1;
		else
			$dbg_filter_calls[$filter]++;
		
		$dbg_filter_start[$filter] = $start;
		$dbg_filter_times[$filter] = $start;
		return $param;
	}	
	
	
	public static function tick(){
		static $last_time = 0;
		static $last_call = null;
		static $CONTENT = '';
		static $THEMES 	= '';
		
		if ( empty( $CONTENT ) ) {
			$CONTENT = basename( WP_CONTENT_DIR );
			$THEMES = $CONTENT.'.themes';
		}
	
		if (!$last_time) 
			$last_time = microtime(true);
		
		if ( version_compare( PHP_VERSION, '5.3.6' ) < 0 )
            $bt = debug_backtrace( true );
        else if( version_compare( PHP_VERSION, '5.4.0' ) < 0 )
            $bt = debug_backtrace( DEBUG_BACKTRACE_IGNORE_ARGS | DEBUG_BACKTRACE_PROVIDE_OBJECT );
        else
			$bt = debug_backtrace( DEBUG_BACKTRACE_IGNORE_ARGS | DEBUG_BACKTRACE_PROVIDE_OBJECT, 2 );

		$frame = $bt[0];
		if ( count( $bt ) >= 2 ) {
			$frame = $bt[1];
		}			
		unset( $bt );#Free up memory
		#Include/require
		if ( in_array( strtolower( $frame['function'] ), array( 'include', 'require', 'include_once', 'require_once' ) ) ){	
			$file = $frame['args'][0];
		#Object instances
		}elseif ( isset( $frame['object'] ) && method_exists( $frame['object'], $frame['function'] ) ) {	
			try {	
				$reflector = new ReflectionMethod( $frame['object'], $frame['function'] );	
				$file = $reflector->getFileName();	
			}catch ( Exception $e ) {	}
		#Static calls	
		}elseif ( isset( $frame['class'] ) && method_exists( $frame['class'], $frame['function'] ) ) {	
			try {	
				$reflector = new ReflectionMethod( $frame['class'], $frame['function'] );	
				$file = $reflector->getFileName();	
			}catch ( Exception $e ){ }
		#Functions	
		}elseif ( !empty( $frame['function'] ) && function_exists( $frame['function'] ) ) {	
			try {
				$reflector = new ReflectionFunction( $frame['function'] );	
				$file = $reflector->getFileName();
			} catch ( Exception $e ) { }
		#Lambdas / closures
		}elseif ( '__lambda_func' == $frame['function'] || '{closure}' == $frame['function'] ){
			$lambda_file = @$bt[0]['file'];
			$file = preg_replace( '/\(\d+\)\s+:\s+runtime-created function/', '', $lambda_file );
		#File info only
		}elseif ( isset( $frame['file'] ) ) {	
			$file = $frame['file']; #If we get here, then at least we have a file, so that helps.
		}else {	
			$file = $_SERVER['SCRIPT_FILENAME']; #If we get here, it's probably the exact file that was called
		}
	
		#Function
		$function = (isset($frame['object'])) ? get_class($frame['object']) . '::' . $frame['function'] : $frame['function'];
		$file = str_replace('\\','/',$file); #For Windows
		
		#This is generic, does not take into account changes of defaults
		if(stripos($file, 'wp-includes') !== false) 		$area = 'WP Core';
		else if(stripos($file, 'SI_Bench.php') !== false) 	$area = 'System Info';
		else if(stripos($file, 'plugins') !== false) 		$area = 'Plugins';			
		else if(stripos($file, 'themes') !== false) 		$area = 'Theme';
		else if(stripos($file, 'wp-admin') !== false) 		$area = 'Wordpress Admin';		
		else	$area = 'Other';
		
		$time = microtime(true) - self::$_last_time;
		
        if( !isset(self::$_profile[$area]) ){
            self::$_profile[$area] = array();
			self::$_function_count[$area] = array();			
			self::$section[$area] = 0;			
        }		
		#Create the entry for the file
		if( !isset( self::$_profile[$area][$file] ) ){
			self::$_profile[$area][$file] = array();
			self::$_function_count[$area][$file] = array();				
		}
		#Create the entry for the function
        if ( !isset( self::$_profile[$area][$file][$function] ) ){
            self::$_profile[$area][$file][$function] = 0;
			self::$_function_count[$area][$function] = 0;				
		}        
        #Record the call
		self::$section[$area] += $time;
        self::$_profile[$area][$file][$function] += $time;		
		$current_call = "{$area}_{$file}_{$function}";
		if($last_call != $current_call){
			self::$_function_count[$area][$file][$function]++;
			$last_call = $current_call;
		}
        self::$_last_time = microtime(true);		 
	}
	public static function benchmark_output(){		 
		global $wpdb,$template,$EZSQL_ERROR,$wp_query,$wp,$wp_object_cache,$bp;
		if( !current_user_can('administrator') )
			return;
		/*
		//Are we an AJAX Request? Do we Want to Log to a file?
		if( headers_sent() ){
			$headers = headers_list();
			return;
		}
		*/			
		self::$load_time['stop'] = microtime(true);		

		if( System_Info_Tools::is_windows() ){
			$plugin_dir 	= str_replace('\\','/',WP_PLUGIN_DIR);
			$muplugin_dir 	= str_replace('\\','/',WP_CONTENT_DIR).'/mu-plugins';												
		}
		else{
			$muplugin_dir 	= str_replace('\\','/',WP_CONTENT_DIR).'/mu-plugins';
			$plugin_dir 	= str_replace('\\','/',WP_PLUGIN_DIR);
		}	

		$wpdb_queries 		= $wpdb->queries; 
		$wpdb_query_time 	= 0;
		$profiles 			= array();
		$plugin_times 		= array();
		
		foreach($wpdb_queries as $q){
			$wpdb_query_time += $q[1];
		}				
		uasort($wpdb_queries,function($a,$b){ return $a[1]<$b[1];  });
		$total_time = self::$load_time['stop'] - self::$load_time['start'];
		
		foreach((array)self::$_profile['Plugins'] as $k=>$values){
			$name = str_replace($plugin_dir,'',$k);
			$name = str_replace($muplugin_dir,'',$name);
			$name = str_replace('\\','/',$name);
			$pos = strpos($name, '/',1)-1;
			$dash_pos = ($pos<0) ? strlen($name):$pos;
			$plugin_name = substr($name, 1, $dash_pos);			
			$plugin_times[$plugin_name] += array_sum($values); 
		}		
		
		arsort(self::$section);
		arsort( $plugin_times );											
		
		include(__DIR__.'/../views/Benchmark.php');			
	}
	public static function timer_start($input){
		$filter = current_filter();
		$filter = current_filter();
		self::$load_time[$filter] = microtime(true);
		return $input;
	}
	public static function timer_stop($input){
		$filter = current_filter();
		$time = microtime(true) - self::$load_time[$filter];
		self::$load_time[$filter] = $time;	
		return $input;
	}	
}