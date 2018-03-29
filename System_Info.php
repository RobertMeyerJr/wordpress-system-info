<?php
/*
Plugin Name: WP Total Details	
Plugin URI: http://www.github.com/robertmeyerjr/wp-total-details/
Description: Provides debugging features and insights into wordpress and the server environment it is running on.
Version: 1.0a
Author: Robert Meyer Jr.
Author URI: http://www.RobertMeyerJr.com

*/	
define('SI_START_TIME', microtime(true));

include('app/Console.php');
include('app/ErrorHandler.php');

if( isset($_GET['bench']) ){
	require_once('app/SI_Bench.php');
}

$total_details = System_Info::getInstance();

class System_Info{	
	protected static $instance;
	protected static $action_times;
	protected static $action_start_end;
	
	public static $actions;
	
	public static $action_start;
	public static $action_end;
	
	protected static $remote_get_urls = [];

	public static function getInstance(){	
		/*Make sure running PHP 5.4+, otherwise dont even load */
		if( version_compare(PHP_VERSION, '5.4') < 0 ){
			 add_action('admin_notices', array($this,'wont_load'));
			 return;
		}
		
		if(self::$instance == null){
			self::$instance = new self;
		}
		
		//Console::testLogging();
		
		return self::$instance;

		
	}
	
	public function __construct(){

		if( isset( $_GET['debug'] ) ){
			/*
				Should add another check here, but we can't check	
				is_user_logged_in yet as we want the 
				benchmarking and error handler to start as early as possible
			*/
						
			SI_ErrorHandler::enable_error_handling();
			if( !defined('SAVEQUERIES') ){
				define('SAVEQUERIES', true );
			}
			
			//http_request_args?

			add_action('requests-curl.before_request', 	array($this, 'before_remote_request'), 10, 1);
			add_action('http_api_debug', 	array($this, 'after_remote_request'),10 , 5);
			
			$GLOBALS['SI_Errors'] 			= array();
			$GLOBALS['dbg_filter_calls']	= array();
			$GLOBALS['dbg_filter_times'] 	= array();			
			$GLOBALS['dbg_filter_start']	= array();
			$GLOBALS['dbg_filter_stop']		= array();			
			
			$this->all_actions();
		}

		add_action('activated_plugin', 	array($this,'make_first_plugin') );				
		add_action('init', 				array($this,'init'));			
	}
	
	public static function before_remote_request($res){	
		self::$remote_get_urls[] = [ microtime(true)];
	}
	public static function after_remote_request($res, $ctx, $class, $r, $url){
		$arr = &self::$remote_get_urls;		
		$last_key = key( end($arr) );
		
		/*
		Console::log( curl_getinfo($res) );
		Console::log($res);
		Console::log($ctx);
		Console::log($class);
		Console::log($r);
		*/
		/*
		$start		= microtime(true);
		$end 		= microtime(true);
		*/
		#self::$remote_get_urls = [$url, $start, $end]
		
		$arr[$last_key][] = microtime(true);
		$arr[$last_key][] = $url;
	}

	public function wont_load(){
		$msg = sprintf('The Total Details plugin requires at least PHP 5.4. You have %s', PHP_VERSION);
		echo "<div class='error below-h2'><p>{$msg}</p></div>";
	}	
	
	public function all_actions(){		
		add_action('all', array($this, 'early_action'), -100);
		add_action('all', array($this, 'late_action'), PHP_INT_MAX);			
	}
	
	public function early_action($in=null){
		$arg_count 	= func_num_args(); 
		$filter 	= current_filter();
		
		$NOW = microtime(true);
		self::$action_start[] = [$filter, $NOW];
		
		return $in;
	}
	public function late_action($in=null){
		$arg_count = func_num_args();
		$filter = current_filter();
		$NOW = microtime(true);
		
		self::$action_end[$filter][] = $NOW;
		
		return $in;
	}	
	
	public function init(){			
		if( current_user_can('manage_options') ){			
			add_action('admin_bar_menu', 						array($this,'admin_menu'),9000);		
			add_action('wp_dashboard_setup', function(){			
				wp_add_dashboard_widget('debugbar_dashboard', '<i class="dashicons dashicons-dashboard"></i> Total Details', array($this,'dashboard_widget'));
			});			
			
			if( isset($_GET['debug']) ){
				$this->debug_start();
			}		
			$this->admin();
		}				
	}	
	
	//Dashboard Widget All Logic is in the php file	
	public function dashboard_widget(){ include('views/dashboard-widget.php'); }
	
	public function admin_menu(){
		global $wp_admin_bar;
		if( current_user_can('manage_options') ){
			$url = add_query_arg('debug', 1);
			$args = array(
				'id' 		=> 'total-debug', 	
				'parent' 	=> false, 			
				'title' 	=> 'Total Debug', 
				'href' 		=> $url, 
				'meta'   => array(
					'target'   	=> '_self',
					'title'    	=> 'Total Debug',
				),
			);
			$wp_admin_bar->add_menu( $args);
		}
	}
	
	public function admin(){
		try{
			$this->do_includes();
			System_Info_Admin::init();
		}catch(Exception $e){
			add_action( 'admin_notices', function(){ 
				echo "<div class=error><p>System_Info - Error<br/><pre>".print_r($e,true)."</pre></p></div>";
			});
		}
	}
	
	public function debug_start(){
		$bar_style  = plugins_url( '/media/css/bar.css',__FILE__);
		$bar_js 	= plugins_url( '/media/js/Bar.js',__FILE__);
		wp_enqueue_style( 'debug-bar', $bar_style);
		wp_enqueue_script('debug-bar', $bar_js, array('jquery'), 1, true);
		register_shutdown_function(function(){
			//Check if there was a fatal error, iff so output debugbar script/style manually
			restore_error_handler(); 
			$this->renderDebugBar();
		}); 		
	}

	public function renderDebugBar(){
		include(__DIR__.'/views/debug/bar.php');
	}
		
	/*
		Make sure this is the very first plugin that gets loaded
		This allows us to benchmark the other plugins.
		TODO: Switch to using mu plugin?
	*/
	public function make_first_plugin(){
		$path = str_replace( WP_PLUGIN_DIR . '/', '', __FILE__ );
		if ( $plugins = get_option( 'active_plugins' ) ) {
			if ( $key = array_search( $path, $plugins ) ) {
				array_splice( $plugins, $key, 1 );
				array_unshift( $plugins, $path );
				update_option( 'active_plugins', $plugins );
			}
		}
	}
		
	//Includes only happen if they are needed
	public function do_includes(){
		if( !is_user_logged_in() ){
			return;
		}
		//If font awesome isn't already enqueued, enqueue it 
		if( !wp_style_is('font-awesome') && !wp_style_is('fontawesome') ){
			wp_enqueue_style( 'font-awesome', 'https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css');
		}
		wp_enqueue_script( 'wp-dev-bar-admin', plugins_url( '/media/js/Admin.js',__FILE__), array('jQuery'), true);		
		
		require_once('app/SI_Admin.php');
		require_once('app/SI_SQL.php');
		require_once('app/SI_Tools.php');
	}

	
	public static function getActionTimes(){ return self::$action_times; }	
	public static function getActionStartEnd(){ return self::$action_start_end; }	
}


//Global functions used by debugbar
/*
function print_filters_count($hook){
	global $wp_filter;
	return count($wp_filter[$hook]);
}

function print_filters_for( $hook = null ) {
    global $wp_filter;
    if( !empty($hook) && !isset( $wp_filter[$hook] ) )
        return false;
    print '<pre>';
		if(empty($hook))
			print_r( $wp_filter );
		else
			print_r( $wp_filter[$hook] );
    print '</pre>';
}
*/


