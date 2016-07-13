<?php
/*
Plugin Name: WP Total Details	
Plugin URI: http://www.github.com/robertmeyerjr/wp-total-details/
Description: Provides insights into wordpress and the server environment it is running on.
Version: 1.0a
Author: Robert Meyer Jr.
Author URI: http://www.RobertMeyerJr.com

*/	
define('SI_START_TIME', microtime(true));

include(__DIR__.'/app/ErrorHandler.php');

SI_ErrorHandler::enable_error_handling();

$total_details = System_Info::getInstance();
class System_Info{	
	protected static $instance;
	
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
		add_action('activated_plugin', 	array($this,'make_first_plugin') );				
		add_action('init', 				array($this,'init'));			
		
		if( isset( $_GET['debug'] ) ){
			if( !defined('SAVEQUERIES') ){
				define('SAVEQUERIES', true );
			}
			$GLOBALS['SI_Errors'] 			= array();
			$GLOBALS['dbg_filter_calls']	= array();
			$GLOBALS['dbg_filter_times'] 	= array();			
			$GLOBALS['dbg_filter_start']	= array();
			$GLOBALS['dbg_filter_stop']		= array();			
			/*
				Should add another check here, but we can't check	
				is_user_logged_in yet as we want the 
				benchmarking to start as early as possible
			*/
			#$this->enable_error_handling();
			#$this->benchmarking();
		}
	}
	
	public function wont_load(){
		$msg = sprintf('The Total Details plugin requires at least PHP 5.4. You have %s', PHP_VERSION);
		echo "<div class='error below-h2'><p>{$msg}</p></div>";
	}	
	
	public function init(){			
		$user = wp_get_current_user();
		if( in_array( 'administrator', (array) $user->roles ) ){			
			add_action('admin_bar_menu', 						array($this,'admin_menu'),9000);		
			add_action('wp_ajax_total_details_query_explain', 	array($this,'explain'));					
			add_action('wp_dashboard_setup', function(){			
				wp_add_dashboard_widget('ruxly_dashboard', '<i class="dashicons dashicons-dashboard"></i> Total Details', array($this,'dashboard_widget'));
			});			
			
			if( isset($_GET['debug']) ){
				$this->debug_start();
			}		
			$this->admin();
		}
	}	
	
	public function explain(){
		global $wpdb;
		$sql = "EXPLAIN ".stripslashes($_POST['sql']); 
		$results = $wpdb->get_results($sql);
		include(__DIR__.'/views/Explain_Query.php');
		exit;
	}
	
	
	//Dashboard Widget All Logic is in the php file	
	public function dashboard_widget(){ include('views/dashboard-widget.php'); }
	
	public function admin_menu(){
		global $wp_admin_bar;
		if( current_user_can('manage_options') ){
			$url = add_query_arg('dev-debug', 1);
			$args = array(
				'id' 		=> 'total-debug', 	
				'parent' 	=> false, 			
				'title' 	=> 'Total Debug', 
				'href' 		=> $url, 
				'meta'   => array(
					'target'   => '_self',
					'title'    => 'Total Debug',
					#'html'     => '<!-- Custom HTML that goes below the item -->',
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
		wp_enqueue_style( 'debug-bar', plugins_url( '/media/css/bar.css',__FILE__));
		wp_enqueue_script('debug-bar', plugins_url( '/media/js/Bar.js',__FILE__), array('jquery'), 1, true);
		register_shutdown_function(function(){
			//was there an error?
			restore_error_handler(); 				
			include(__DIR__.'/views/debug/bar.php');								
		}); 			
	}
		
	/*
		Make sure this is the very first plugin that gets loaded
		This allows us to benchmark the other plugins.
		TODO: Switch to using mu plugin
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
		//If font awesome isn't already enqueued, enqueue it 
		if(!wp_style_is('font-awesome') && !wp_style_is('fontawesome') ){
			wp_enqueue_style( 'font-awesome', 'https://maxcdn.bootstrapcdn.com/font-awesome/4.2.0/css/font-awesome.min.css');
		}
				
		wp_enqueue_script( 'wp-dev-bar-admin', plugins_url( '/media/js/Admin.js',__FILE__), array('jQuery'), true);	
		
		require_once('app/SI_Admin.php');
		require_once('app/SI_SQL.php');
		require_once('app/SI_Tools.php');
	}
	
	//---------------------------------
}


//Global functions used by debugbar
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
function hilight_trace_part($str){
	//require_once
	//require
	//->
	//::
	//()
	return $str;
}
function dbg_style_out($v){
	if(is_array($v) || is_object($v)){	
		$str = var_export($v, true);
		$str = htmlentities($str);
		return "<pre>{$str}</pre>";		
	}
	elseif( is_numeric($v) ){		
		return "<span class=int>{$v}</span>";
	}
	else{
		htmlentities( $v ); 
		return "<span class=str>{$v}</span>";
	}
}
function dbg_table_out($arr){
	try{
		if( empty($arr) ){
			return;
		}
		echo "<table class=dbg_out>";
			echo "<thead><tr><th>Key</th><th>Value</th></tr></thead>";
			echo "<tbody>";
			$skip = array('_COOKIE','_FILES','_ENV','GLOBALS','_SERVER','_REQUEST','_GET','_POST','wp_filter');
			foreach($arr as $k=>$v){
				if( in_array($k,$skip) )
					continue;
				$value = $v;
				echo "<tr><th>{$k}</th><td>".dbg_style_out($value)."</td></tr>";
			}
			echo "</tbody>";
		echo "</table>";
	}catch(Any $e){
		echo "dbg_table_out Error";
	}
}

