<?php
/*
Plugin Name: WP Developer Bar
Plugin URI: http://www.github.com/robertmeyerjr/wp-info-bar/
Description: This needs a lot of work before it can be released. 
Version: 1.5a
Author: Robert Meyer Jr.
Author URI: http://www.RobertMeyerJr.com

*/	
define('SI_START_TIME', microtime(true));

System_Info::start();
class System_Info{	
	/*
	Start
	*/
	public static function start(){		
		add_action('activated_plugin', 	array(__CLASS__,'make_first_plugin') );				
		add_action('init', 				array(__CLASS__,'init'));				
		add_action('admin_bar_menu', 	array(__CLASS__,'admin_menu'),9000);
		
		add_action('wp_dashboard_setup', function(){
			wp_add_dashboard_widget('ruxly_dashboard', '<i class="dashicons dashicons-dashboard"></i> Total Details', array(__CLASS__,'dashboard_widget'));
		});
		
		if( isset( $_GET['debug'] ) ){
			self::enable_error_handling();		
			$GLOBALS['SI_Errors'] 			= array();
			$GLOBALS['dbg_filter_calls']	= array();
			$GLOBALS['dbg_filter_times'] 	= array();			
			$GLOBALS['dbg_filter_start']	= array();
			$GLOBALS['dbg_filter_stop']		= array();
			
			//Need more checks before doing this, but cannot wait for init
			//Check cookie first? Not 100%, but it would help			
			self::benchmarking();
		}
	}	
		
	public static function dashboard_widget(){
		include('views/dashboard-widget.phtml');		
	}
	
	public static function admin_menu(){
		global $wp_admin_bar;
		$url = add_query_arg('dev-debug', 1);
		$args = array(
            'id' 		=> 'debug-bar', // a unique id (required)
            'parent' 	=> false, // false for a top level menu
            'title' 	=> 'Total Debug', // title/menu text to display
            'href' 		=> $url, // target url of this menu ite
			'meta'   => array(
				'target'   => '_self',
				'title'    => 'Total Debug',
				#'html'     => '<!-- Custom HTML that goes below the item -->',
			),
		);
		$wp_admin_bar->add_menu( $args);
	}
	
	public static function init(){	
		if( isset($_GET['debug']) && current_user_can('manage_options') ){
			self::debug_start();
		}		
		#If we aren't admin, Dont even run
		if(is_user_logged_in() && current_user_can('administrator') ){ 			
			self::admin();
		}
	}	
	
	public static function admin(){
		try{										
			self::do_includes();
			System_Info_Admin::init();			
		}catch(Exception $e){
			add_action( 'admin_notices', function(){ 
				echo "<div class=error><p>System_Info - Error<br/><pre>".print_r($e,true)."</pre></p></div>";
			});
		}
	}
	
	public static function debug_start(){
		wp_enqueue_style( 'debug-bar', plugins_url( '/media/css/bar.css',__FILE__));
		wp_enqueue_script('debug-bar', plugins_url( '/media/js/Bar.js',__FILE__), array('jquery'), 1, true);
		register_shutdown_function(function(){
			restore_error_handler();				
			include(__DIR__.'/views/debug/bar.phtml');								
		}); 			
	}
	
	public static function enable_error_handling(){
		#error_reporting(E_ERROR & E_WARNING); 
		set_error_handler(array(__CLASS__,'error_handler'), E_ALL & E_STRICT);		
		register_shutdown_function(array(__CLASS__,'shutdown_function'));
	}
	public static function shutdown_function(){
		
	}
	public static function error_handler($errno,$str="",$file=null,$line=null,$context=null){ 
			global $SI_Errors;	
			$SI_Errors[] = array(
				$errno,
				$str,
				$file,
				$line,
				$context
			);
			//Fatal Error?
			if($errno == E_USER_ERROR){
				//Dump the output and die
				die(1);
			}			
			return true; 
		}
	/*
		Make sure this is the very first plugin that gets loaded
		This allows us to benchmark the other plugins
	*/
	public static function make_first_plugin(){
		$path = str_replace( WP_PLUGIN_DIR . '/', '', __FILE__ );
		if ( $plugins = get_option( 'active_plugins' ) ) {
			if ( $key = array_search( $path, $plugins ) ) {
				array_splice( $plugins, $key, 1 );
				array_unshift( $plugins, $path );
				update_option( 'active_plugins', $plugins );
			}
		}
	}
	public static function benchmarking(){	
		add_action('all', array(__CLASS__,'benchmark_filter_start'), 0); 
		add_action('all', array(__CLASS__,'benchmark_filter_end'), PHP_INT_MAX);
	}
	public static function benchmark_filter_end($param=false){
		global $dbg_filter_times,$dbg_filter_calls,$dbg_filter_stop;
		//This is wrong, it isn't summing the exec time just recording the last time
		$filter = current_filter();
		$stop_time = microtime(true);
		$dbg_filter_stop[$filter] = $stop_time;
		$dbg_filter_times[$filter] = $stop_time - $dbg_filter_times[$filter];
		return $param;
	}
	public static function benchmark_filter_start($param=false){
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
	
	//Includes only happen if they are needed
	public static function do_includes(){
		//If font awesome isnt already enqueued, enqueue it 
		if(!wp_style_is('font-awesome') && !wp_style_is('fontawesome') ){
			wp_enqueue_style( 'font-awesome', 'https://maxcdn.bootstrapcdn.com/font-awesome/4.2.0/css/font-awesome.min.css');
		}
				
		wp_enqueue_script( 'wp-dev-bar-admin', plugins_url( '/media/js/Admin.js',__FILE__), array('jQuery'), true);	
		
		include('app/SI_Admin.php');
		//--------------------------
		include('app/Log_Highlight.php');
		include('app/SI_SQL.php');
		include('app/SI_Tools.php');
	}
		
}
