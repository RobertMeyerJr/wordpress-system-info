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

require_once('app/Console.php');

if( isset($_GET['bench']) ){
	#require_once('app/SI_Bench.php');
}

$total_details = System_Info::getInstance();

/*
Console::stopwatch('Load to Shutdown');
add_action('shutdown',function(){
	Console::stopwatch('Load to Shutdown');
});
self::testLogging();
*/	

class System_Info{	
	protected static $instance;
	protected static $action_times;
	protected static $action_start_end;
	protected static $remote_get_urls = [];
	
	public static function testLogging(){
			$arr1 = ['test'=>'test','test2'=>'test','test3'=>'test'];
			$obj1 = (object)['test'=>'test','test2'=>'test','test3'=>'test','o'=>['a','b','c']];
			$a = 123;
			Console::log($arr1);
			Console::info($arr1);
			Console::success($arr1);			
			Console::warn($arr1);
			Console::error($arr1);
			
			add_action('wp',function(){
				global $post;
				Console::info($GLOBALS['wpdb']);
				Console::info($GLOBALS['wp']);
				Console::success($_SERVER);
				Console::info($post);
			});
	}

	public static function getInstance(){	
		/*Make sure running PHP 5.4+, otherwise dont even load */
		if( version_compare(PHP_VERSION, '5.4') < 0 ){
			 add_action('admin_notices', array($this,'wont_load'));
			 return;
		}
		
		if(self::$instance == null){
			self::$instance = new self;
		}
		
		return self::$instance;
		
	}
	
	public function __construct(){

		if( isset( $_GET['debug'] ) ){
			/*
				Should add another check here, but we can't check	
				is_user_logged_in yet as we want the 
				benchmarking and error handler to start as early as possible
			*/
			include('app/ErrorHandler.php');			
			SI_ErrorHandler::enable_error_handling();
			if( !defined('SAVEQUERIES') ){
				define('SAVEQUERIES', true );
			}
			
			add_action('http_api_curl', array($this,'hook_http_api_curl'), 10, 3);
			
			$GLOBALS['SI_Errors'] 			= array();
			$GLOBALS['dbg_filter_calls']	= array();
			$GLOBALS['dbg_filter_times'] 	= array();			
			$GLOBALS['dbg_filter_start']	= array();
			$GLOBALS['dbg_filter_stop']		= array();			
			
			$this->benchmarking();
		}

		add_action('activated_plugin', 	array($this,'make_first_plugin') );				
		add_action('init', 				array($this,'init'));			
	}
	
	public function wont_load(){
		$msg = sprintf('The Total Details plugin requires at least PHP 5.4. You have %s', PHP_VERSION);
		echo "<div class='error below-h2'><p>{$msg}</p></div>";
	}	
	

	public function benchmarking(){
		$actions = array(
			'plugins_loaded',
			'setup_theme',
			'after_setup_theme',
			'init',
			'widgets_init',
			'wp_loaded',
			'parse_request',
			'pre_get_posts',
			'wp',
			'template_redirect',
			'wp_register_sidebar_widget',
			'wp_loaded',
			'get_header',
			'wp_head',
			'wp_enqueue_scripts',
			'wp_print_styles',
			'wp_print_scripts',
			'loop_start',
			'the_post',
			'loop_end',
			'wp_footer',
			'admin_footer',
			'admin_notices',
			'restrict_manage_posts',
			'admin_head',
			'adminmenu',
			'all_admin_notices',
			'admin_bar_menu',
			'wp_before_admin_bar_render',
			'wp_after_admin_bar_render',
			'shutdown'
		);
		/*
		foreach($actions as $a){
			#add_action($a, array($this, 'early_action'), 1);
			#add_action($a, array($this, 'late_action'), PHP_INT_MAX);			
		}
		*/
		add_action('all', array($this, 'early_action'), -100);
		add_action('all', array($this, 'late_action'), PHP_INT_MAX);			
	}
	
	public function early_action(){
		$filter = current_filter();
		#Only set the start if it isnt set
		if( empty( self::$action_start_end[$filter]['start'] ) ){
			self::$action_start_end[$filter]['start'] = microtime(true);
		}
	}
	public function late_action(){
		$filter = current_filter();
		self::$action_start_end[$filter]['end']  = microtime(true);
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

	
	public static function hook_http_api_curl($handle, $r, $url){
		self::$remote_get_urls[] = $url;
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


