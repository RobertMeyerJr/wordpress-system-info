<?php
/*
Plugin Name: WP Total Details	
Plugin URI: http://www.github.com/robertmeyerjr/wp-total-details/
Description: Provides debugging features and insights into wordpress and the server environment it is running on.
Version: 1.0.1a
Author: Robert Meyer Jr.
Author URI: http://www.RobertMeyerJr.com
*/	
define('SI_START_TIME', microtime(true)); #Todo: Move this to Must Use Plugin

include('app/Console.php'); #Always included to prevent errors if called

if( isset($_GET['debug']) && is_td_debug() ){
	include('app/ErrorHandler.php');
	
	//Todo: Do something better here
	add_action('plugin_loaded',function($plugin){
		global $plugin_memory_load;
		$plugin_memory_load[$plugin] = [memory_get_usage(),microtime(true)-SI_START_TIME];
	});
}

/* 
SQL Debug headers not working for rest routes
*/
$total_details = System_Info::getInstance();

function is_td_guest_debug(){
	if( !defined('DEBUG_KEY') ){
		return false;
	}
	if( strlen(DEBUG_KEY) < 10 ){
		return false;
	}
	
	return $_GET['debug'] == DEBUG_KEY;
}

function is_td_debug(){
	//If we have already fired init, see if we are an admin, otherwise return false
	if( did_action('init') && !is_td_guest_debug() && !current_user_can('manage_options') ){
		return false;
	}
	else if( empty($_COOKIE[LOGGED_IN_COOKIE]) ){
		return false;
	}

	if( isset( $_GET['debug'] ) ){
		return true;
	}

	$referer_debug = false != stripos($_SERVER['HTTP_REFERER'],'debug=1');
	
	if(defined('DOING_AJAX') && DOING_AJAX && $referer_debug){ //Need a better check here
		return true;
	}

	if( defined('REST_REQUEST') && REST_REQUEST && $referer_debug){ //Need a better check here
		return true;
	}

	if($referer_debug && !empty( $GLOBALS['wp']->query_vars['rest_route'] ) ){
		return true;
	}

	return false;
}

class System_Info{	
	protected static $instance;
	
	public static $actions = [];
	public static $action_start;
	public static $action_end;
	public static $templates;
	public static $timeline 	= [];
	public static $timeline_end = [];
	public static $blocks = [];
	public static $plugin_memory_load = [];
	public static $doing_it_wrong = [];
	protected static $remote_get_urls = [];
	protected static $remote_request_count = 0;
	public static function getInstance(){
		if( empty($_COOKIE[LOGGED_IN_COOKIE]) && !is_td_guest_debug() ){
			return;
		}
		/*Make sure running PHP 7.3+, otherwise dont even load */
		if( version_compare(PHP_VERSION, '7.3') < 0 ){
			 add_action('admin_notices', array(__CLASS__,'wont_load'));
			 return;
		}
		
		if(self::$instance == null){
			self::$instance = new self;
		}
		return self::$instance;
	}

	public function __construct(){
		if( is_td_debug() || is_td_guest_debug() ){
			define('DONOTCACHEPAGE',true); #PREVENT CACHING THE PAGE
			add_action('doing_it_wrong_run',[$this,'doing_it_wrong_run'], 10, 3);
			if( !defined('SAVEQUERIES') ){
				define('SAVEQUERIES', true );
			}
			
			if( defined('DOING_AJAX') && DOING_AJAX || defined('REST_REQUEST') && REST_REQUEST || !empty($GLOBALS['wp']->query_vars['rest_route']) ){
				add_filter('log_query_custom_data',function($query_data, $query, $query_time, $query_callstack, $query_start){
					//Limit this
					$json = json_encode([$query,$query_time]);//Limit data passed in headers
					if( !headers_sent() ){
						header('x-total-debug-sql: '.base64_encode($json), false);
					}
					return $query_data;
				},10,5);
				/*
				add_action('shutdown',function(){
					Console::log('Ajax Shutdown');
				});
				*/
			}
			else if( class_exists('SI_ErrorHandler') ){
				SI_ErrorHandler::enable_error_handling();
			}
			//http_request_args?

			add_action('requests-curl.before_request', 	array($this, 'before_remote_request'), 10, 1);
			#add_filter('pre_http_request', 				array($this, 'before_remote_request'), 10, 3);
			add_action('http_api_debug', 	array($this, 'after_remote_request'),10 , 5);
			
			$GLOBALS['SI_Errors'] 			= array();
			$GLOBALS['dbg_filter_calls']	= array();
			$GLOBALS['dbg_filter_times'] 	= array();
			$GLOBALS['dbg_filter_start']	= array();
			$GLOBALS['dbg_filter_stop']		= array();
			
			if(!empty($_GET['all_actions'])){
				$this->all_actions();
			}

			add_action('get_template_part',function($slug=null, $name=null, $templates=null, $args=null){
				self::$templates[] = [$slug, $name, $templates];
			},10,4);
			
			#add_action('plugin_loaded', [$this,'plugin_loaded']);
		}
		
		add_action('init', array($this,'init'));

		add_action('activated_plugin', 	array($this,'make_first_plugin') );
		
		$important_filters = [
			'plugins_loaded',
			'setup_theme',
			#'unload_textdomain',
			#'load_textdomain',
			'after_setup_theme',
			'auth_cookie_malformed',
			'auth_cookie_valid',
			'set_current_user',
			'init',
			'widgets_init',
			'register_sidebar',
			'wp_loaded',
			'parse_request',
			'send_headers',
			#'query',		This is a filter
			'parse_query',
			'pre_get_posts',
			'wp',
			'template_redirect',
			'wp_print_scripts',
			'wp_print_styles',
			'get_header',
			'wp_body_open',
			'wp_head',
			'loop_start',
			'the_post',
			'loop_end',
			'get_sidebar',
			'get_footer',
			'wp_footer',
			'wp_print_footer_scripts',
			'wp_before_admin_bar_render',
			'wp_after_admin_bar_render',
			'shutdown'
		];
		foreach($important_filters as $f){
			//Todo: Get more info here. Number of Queries
			add_action($f,[$this,'timeline'], -100);
			add_action($f,[$this,'timeline_end'], PHP_INT_MAX-1);
		}

		//Add action after each plugin load to show memory

		add_filter('render_block',[$this,'render_block'], 10, 2);
	}	

	public function doing_it_wrong_run($function, $message, $version){
		self::$doing_it_wrong[] = [$function, $message, $version];
	}

	public function render_block($content, $block){
		self::$blocks[] = [
			$block['blockName'],
			$block['attrs'],
		];
		return $content;
	}
	
	public function timeline(){
		self::$timeline[] = [
			current_filter(),
			memory_get_peak_usage(),
			microtime(true)-SI_START_TIME,
			get_num_queries()
		];
	}

	public function timeline_end(){
		self::$timeline_end[] = [
			current_filter(),
			memory_get_peak_usage(),
			microtime(true)-SI_START_TIME,
			get_num_queries()
		];
	}

	public static function before_remote_request($res){
		$index = self::$remote_request_count++;
		//Trace?
		self::$remote_get_urls[$index] = ['start'=>microtime(true)];
	}
	public static function after_remote_request($res, $ctx, $class, $r, $url){
		//$arr = &self::$remote_get_urls;		

		$index = self::$remote_request_count - 1;
		
		self::$remote_get_urls[$index]['end'] = microtime(true);
		self::$remote_get_urls[$index]['method'] = $r['method'];
		self::$remote_get_urls[$index]['url'] = $url;
		if( is_wp_error($res) ){
			self::$remote_get_urls[$index]['code'] = 'WP Error';
		}
		else{
			self::$remote_get_urls[$index]['code'] = $res['response']['code'];
		}

		
		//See if we can get the size of the request
		//Console::log($res);
		#Console::log($ctx);
		#Console::log($last_key);
		#Console::log('completed');
		#Console::log($res);
		/*
		Console::log( curl_getinfo($res) );
		Console::log($res);
		Console::log($ctx);
		Console::log($class);
		Console::log($r);
		*/
		/*
		*/
		#self::$remote_get_urls = [$url, $start, $end]
	
	}

	public static function wont_load(){
		$msg = sprintf('The Total Details plugin requires at least PHP 7.3 You have %s', PHP_VERSION);
		echo "<div class='error below-h2'><p>{$msg}</p></div>";
	}	
	
	public function all_actions(){		
		add_action('all', array($this, 'early_action'), -100);
		add_action('all', array($this, 'late_action'), PHP_INT_MAX);			
	}
	
	public function early_action($in=null){
		#$arg_count 	= func_num_args(); 
		#$args 			= func_get_args();
		#$tag 			= array_shift( $args );
		#$bt 			= debug_backtrace();
		#$hook_type 	= $bt[3]['function'];
		//calls, last_start, end
		$filter = current_filter();
		
		self::$action_start[$filter][] = microtime(true);
		
		return $in;
	}
	
	public function late_action($in=null){
		$filter = current_filter();
		$NOW = microtime(true);
		//Cumulative Time?
		self::$action_end[$filter][] = $NOW;
		
		$start = end(self::$action_start[$filter]);
		self::$actions[] = [$filter, $start, $NOW];

		return $in;
	}	
	
	public function init(){			
		global $wp;
		if( current_user_can('manage_options') || is_td_guest_debug() ){
			add_action('admin_bar_menu', 						array($this,'admin_menu'),9000);	
			add_action('wp_dashboard_setup', function(){			
				wp_add_dashboard_widget('debugbar_dashboard', '<i class="dashicons dashicons-dashboard"></i> Total Details', array($this,'dashboard_widget'));
			});			
			
			if( (is_td_guest_debug() || is_td_debug()) && !defined('DOING_AJAX') && empty($wp->query_vars['rest_route']) ){
				$this->debug_start();
			}		
			
			$this->admin();
			
			add_action('deprecated_function_run',[$this,'deprecated_function_run'],10,3);
		}
	}	
	
	public function deprecated_function_run($func, $replace, $ver){
		Console::warn("Deprecated Function {$func} Replacement: $replace Version: $ver");
		if( !defined('DOING_AJAX') ){
			Console::log(debug_backtrace(false));
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
			if( class_exists('System_Info_Admin') ){
				System_Info_Admin::init();
			}
		}catch(Exception $e){
			add_action( 'admin_notices', function() use($e){ 
				echo "<div class=error><p>System_Info - Error<br/><pre>".print_r($e,true)."</pre></p></div>";
			});
		}
	}
	
	public function debug_start(){
		$bar_style  = plugins_url( '/media/css/bar.css',__FILE__);
		$bar_js 	= plugins_url( '/media/js/Bar.js',__FILE__);
		wp_enqueue_style( 'debug-bar', $bar_style);
		wp_enqueue_script('debug-bar', $bar_js, array('jquery'), '2.2', true);
		register_shutdown_function(function(){
			//Check if there was a fatal error, iff so output debugbar script/style manually
			restore_error_handler(); 
			$this->renderDebugBar();
		}); 		
	}

	public function renderDebugBar(){
		global $wp;
		if( empty($wp->query_vars['rest_route']) ){
			include(__DIR__.'/views/debug/bar.php');
		}
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
		if( !is_user_logged_in() && !is_td_guest_debug()){
			return;
		}
		if( is_admin() ){ //Should also make sure page is ours
			wp_enqueue_script( 'wp-dev-bar-admin', plugins_url( '/media/js/Admin.js',__FILE__), array('jQuery'), true);		
		}
		require_once('app/SI_Admin.php');
		require_once('app/SI_SQL.php');
		require_once('app/SI_Tools.php');
	}

	public static function sizeofvar(&$var){
		try{
			$start_memory = memory_get_usage();
			$tmp = unserialize(serialize($var));
			return memory_get_usage() - $start_memory;
		}catch(Throwable $e){
			return -1;
		}
	}

}

