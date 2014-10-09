<?php
/*
Plugin Name: WP Developer Bar
Plugin URI: http://www.github.com/robertmeyerjr/wp-info-bar/
Description: This needs a lot of work before it can be released. 
Version: 1.5a
Author: Robert Meyer Jr.
Author URI: http://www.RobertMeyerJr.com
*/	
define('SI_START_TIME',microtime(true));

System_Info::run();

/*
System Info Core
*/
class System_Info{	
	public static function admin_menu(){
		global $wp_admin_bar;
		$url = add_query_arg('debug',1);
		$args = array(
            'id' => 'debug-bar', // an unique id (required)
            'parent' => false, // false for a top level menu
            'title' => 'Debug', // title/menu text to display
            'href' => $url, // target url of this menu ite
		);
		$wp_admin_bar->add_menu( $args);
	}
	public static function run(){		
		add_action( 'activated_plugin', array(__CLASS__,'make_first_plugin') );		
		add_action( 'init', 			array(__CLASS__,'init'));		
		
		add_action('admin_bar_menu', array(__CLASS__,'admin_menu'));
		
		if( isset( $_GET['debug'] ) ){
			self::enable_error_handling();
			
			$GLOBALS['SI_Errors'] 			= array();
			$GLOBALS['dbg_filter_times'] 	= array();			
			$GLOBALS['dbg_filter_calls']		= array();
			//Need more checks before doing this, but cannot wait for init
			//Check cookie first? Not 100%, but helps
			self::benchmarking();
		}
	}	

	public static function enable_error_handling(){
		error_reporting(E_ALL & E_STRICT); 
		set_error_handler(array(__CLASS__,'error_handler'), E_ALL & E_STRICT);		
	}
	
	public static function error_handler($errno,$str,$file=null,$line=null,$context=null){ 
			global $SI_Errors;	
			$SI_Errors[] = array(
				$errno,
				$str,
				$file,
				$line,
				$context
			);
			//Fatal?
			if($errno == E_USER_ERROR){
				die(1);
			}
			return true; 
		}
	
	
	/*
		Make sure this is the very first plugin that gets loaded
		This allows us to benchmark the other plugins
	*/
	function make_first_plugin(){
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
		add_action('all', array(__CLASS__,'benchmark_filter'), 0); 
		add_action('all', array(__CLASS__,'benchmark_filter_end'), PHP_INT_MAX);
	}
	public static function benchmark_filter_end($param=false){
		global $dbg_filter_times,$dbg_filter_calls;
		//This is wrong, it isn't summing the exec time just recording the last time
		$filter = current_filter();
		$dbg_filter_times[$filter] = microtime(true) - $dbg_filter_times[$filter];
		return $param;
	}
	public static function benchmark_filter($param=false){
		global $dbg_filter_times,$dbg_filter_calls;
		$filter = current_filter();
		
		if( !array_key_exists($filter,$dbg_filter_calls) )
			$dbg_filter_calls[$filter] = 1;
		else
			$dbg_filter_calls[$filter]++;
			
		$dbg_filter_times[$filter] = microtime(true);
		return $param;
	}	
	
	//This only happens if needed
	public static function do_includes(){
		include('app/SI_Admin.php');
		include('app/Log_Highlight.php');
		include('app/SI_SQL.php');
		include('app/SI_Tools.php');
	}
	
	public static function init(){	
		if( isset($_GET['debug']) && current_user_can('manage_options') ){
			wp_enqueue_style( 'debug-bar', plugins_url( '/media/css/bar.css',__FILE__));
			register_shutdown_function(function(){
				restore_error_handler();
				$bar = plugins_url( '/media/js/bar.js', __FILE__);
				echo "<script src=\"{$bar}\"></script>";
				include(__DIR__.'/views/debug/bar.phtml');								
			}); 			
		}	
	
		if(is_user_logged_in() && current_user_can('administrator') ){ #If we aren't admin, dont even run
			try{							
			
				if(!wp_style_is('font-awesome') && !wp_style_is('fontawesome') )
					wp_enqueue_style( 'font-awesome', 'https://maxcdn.bootstrapcdn.com/font-awesome/4.2.0/css/font-awesome.min.css');
		
			
				self::do_includes();
				System_Info_Admin::init();			
			}catch(Exception $e){
				add_action( 'admin_notices', function(){ 
					echo "<div class=updated><p>System_Info - Error<br/><pre>".print_r($e,true)."</pre></p></div>";
				});
			}
		}
	}	
}
