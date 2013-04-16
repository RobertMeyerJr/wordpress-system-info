<?php 
/*
SHOW VARIABLES LIKE 'have_query_cache';
SHOW STATUS LIKE 'Qcache%';
mysqlcheck -c %DATABASE% -u root -p

nmap -sT localhost
netstat -ano
Permissions: 
	Directories:		find /path/to/your/wordpress/install/ -type d -exec chmod 755 {} \;
	Files:				find /path/to/your/wordpress/install/ -type f -exec chmod 644 {} \;	
*/

class System_Info_Admin{	
	
	#-------------------------------
	public static function init(){				
		add_action('admin_enqueue_scripts', 				array(__CLASS__, 'admin_scripts'), 90);
		add_action('admin_menu', 								array(__CLASS__, 'admin_menu'));			
		add_action('wp_ajax_sysinfo', 						array(__CLASS__, 'ajax_tab'));		
		add_action('wp_ajax_sysinfo_optimize_table', 	array(__CLASS__, 'ajax_optimize_table'));							
		add_action('wp_ajax_sysinfo_clear_error_log', 	array(__CLASS__, 'clear_error_log'));			
		add_action('wp_ajax_sysinfo_replace_content', 	array(__CLASS__, 'ajax_replace_content'));							
		add_action('wp_ajax_sysinfo_search_hooks', 		array(__CLASS__, 'get_hooks'));			
		add_action('wp_ajax_sysinfo_search_functions', 	array(__CLASS__, 'ajax_function_search'));			
		add_action('wp_ajax_sysinfo_explain_query', 	array(__CLASS__, 'explain_query'));
		add_action('wp_ajax_sysinfo_run_code', 			array(__CLASS__, 'run_code'));
		add_filter('show_admin_bar', array(__CLASS__,'admin_bar')); 		
	}
	
	public static function admin_bar($bar){	
		global $wp_admin_bar;
		$wp_admin_bar ->add_menu(array(	
			#'parent' => 'MY ACCOUNT',
			'id' => 'sysinfo-benchmark',
			'title' => 'Run Benchmark',
			'href' => $_SERVER['PATH_INFO']
		));		
		return $bar;
	}
	
	public function admin_scripts($suffix){
		if($suffix != 'toplevel_page_sys_info')
			return;		
			
		wp_enqueue_script('jquery-ui-dialog');	
		wp_enqueue_script('jquery-ui-accordion');
		wp_enqueue_script('jquery-ui-tabs');			
		wp_enqueue_script('jquery-ui-datepicker');		
		
		
		
		wp_enqueue_script('tablesorter', 'http://cachedcommons.org/cache/jquery-table-sorter/2.0.3/javascripts/jquery-table-sorter-min.js', array('jquery'));	
		wp_enqueue_script('google_jsapi', 'https://www.google.com/jsapi');
		
		wp_enqueue_style('jquery-ui-style', 'http://ajax.googleapis.com/ajax/libs/jqueryui/1.8.2/themes/smoothness/jquery-ui.css'); 
		#$css_url = WP_PLUGIN_URL.'/system-info/system_info.css');
		#wp_enqueue_style('system_info', $css_url );
		
	}
	public function admin_menu(){
		//Should probably go under tools
		add_menu_page('Info', 'System Info', 10, 'sys_info', array(__CLASS__,'info_page')); #, CIQ_TPL . '/images/ciq-icon.png' );
	}
	
	public static function ajax_tab(){		
		if(!current_user_can('administrator'))
			return;
			
		$start = microtime(true);
		switch($_REQUEST['tab']){
			case 'info': 			self::page_info(); break;
			case 'phpinfo': 		self::page_php_info(); break;
			case 'db':				self::page_db_info(); break;
			case 'perm': 			self::page_permissions(); break;
			case 'procs': 			self::page_procs(); break;
			case 'rewrite': 		self::page_rewrite_rules(); break;
			case 'cron': 			self::page_cron(); break;
			case 'hooks': 			self::page_hooks(); break;
			case 'dns': 			self::page_dns(); break;
			case 'whois': 			self::page_whois(); break;
			case 'errors': 			self::page_errors(); break;
			case 'tools': 			self::page_tools(); break;
			case 'shell': 			self::page_shell(); break;
			case 'functions': 		self::page_func(); break;
			case 'mysql_info':		self::page_mysql_info(); break;
			case 'services':		self::page_services(); break;
			case 'ports':			self::page_open_ports(); break;
			default: echo "No such Tab"; break;			
		}		
		$total = microtime(true) - $start;
		echo "<h4>Total Time - ".number_format($total,4)."</h4>";
		exit;
	}
	public function page_mysql_info(){
		global $wpdb;
		$out = $wpdb->get_results('SHOW VARIABLES');
		System_Info_Tools::out_table( $out, null, true);		
	}
	
	public function get_hooks(){
		global $wp_filter,$wp_actions,$merged_filters;		
		$hook = $wp_filter;
		ksort($hook);
		include(__DIR__.'/../views/Tab_Hooks2.phtml');
		exit;
	}
	
	
	#-----------------Work in progress
	public static function page_open_ports(){
		$cmd = (System_Info_Tools::is_windows()) ? 'netstat -ano | find "LISTENING"':'lsof -i -n | egrep "COMMAND|LISTEN"';
		$out = System_Info_Tools::read_csv_array( System_Info_Tools::run_command( $cmd ),"\t");
		System_Info_Tools::out_table($out);
	}
	public static function page_services(){
		if(System_Info_Tools::is_windows()){
			$cmd = 'sc query';
			$out = System_Info_Tools::run_command($cmd);
		}
		else{
			$cmd = 'chkconfig --list';
			$out = self::run_command($cmd);
		}
		print_r($out);		
	}
	public static function page_db_info(){ include(__DIR__.'/../views/Tab_DB_Info.phtml');}
	public static function page_errors(){ include(__DIR__.'/../views/Tab_Errors.phtml'); }
	public static function info_page(){ include(__DIR__.'/../views/SystemInfo.phtml'); }
	public static function page_func(){
		$classes = get_declared_classes();
		sort($classes);
		/*
		http://php.net/manual/en/language.functions.php
		http://codex.wordpress.org/Function_Reference
		http://phpxref.ftwr.co.uk/buddypress/nav.html?_functions/index.html
		*/
		include(__DIR__.'/../views/Tab_Functions.phtml');
	}
	public static function page_shell(){ include(__DIR__.'/../views/Tab_Shell.phtml');}
	public static function page_hooks(){  include(__DIR__.'/../views/Tab_Hooks.phtml'); }
	public static function page_whois(){
		$host = System_Info_Tools::get_domain( 'http://'.$_SERVER['HTTP_HOST'] );
		System_Info_Tools::whois( $host );
	}
	public static function page_info(){ global $wpdb,$wp_version; include(__DIR__.'/../views/Tab_Info.phtml'); }
	public static function page_dns(){	
		$host 	= System_Info_Tools::get_domain( 'http://'.$_SERVER['HTTP_HOST'] );
		$dns 	= dns_get_record( $host );
		$c 		= count($dns);
		include(__DIR__.'/../views/Tab_DNS.phtml');
	}
	public static function page_cron(){$cron = _get_cron_array(); include(__DIR__.'/../views/Tab_Cron.phtml'); }
	public static function page_php_info(){ $mem_usage = memory_get_usage(); include(__DIR__.'/../views/Tab_PHP.phtml');}
	public static function page_procs(){ $mem_usage = memory_get_usage(); include(__DIR__.'/../views/Tab_Procs.phtml');}
	public static function page_permissions(){ include(__DIR__.'/../views/Tab_Permissions.phtml');}
	public static function page_tools(){ include(__DIR__.'/../views/Tab_Tools.phtml'); }
	public static function page_rewrite_rules(){
		$rules = ( System_Info_Tools::is_windows() ) ? file_get_contents(ABSPATH.'/web.config') : file_get_contents(ABSPATH.'/.htaccess');
		echo "<pre>".htmlspecialchars($rules)."</pre>";
	}
	
	public static function ajax_function_search(){	
		$class = $_POST['class_name'];
		if($class == '(User)'){
			$funcs = get_defined_functions();
			$funcs = $funcs['user'];
			echo "<h2>User Defined Global Functions</h2>";
		}
		else if($class == '(Internal)'){
			$funcs = get_defined_functions();
			$funcs = $funcs['internal'];
			echo "<h2>PHP Global Functions</h2>";
		}
		else{
			echo "<h2>Class {$class} Methods</h2>";
			$funcs = get_class_methods($class);			
		}
		
		$items = array();
		foreach($funcs as $f){ 
			if( empty($_POST['search']) )
				$items[] = $f;
			elseif(!empty($_POST['search']) && stripos($f, $_POST['search']) !== false )
				$items[] = $f;				
		} 
		
		sort($items);
		foreach($items as $f)
			echo "<li>{$f}</li>";		
		exit;
	}
	public static function ajax_replace_content(){		
		exit;
	}
	public static function ajax_optimize_table(){
		echo self::optimize_table($_REQUEST['table']);
		exit;
	}
	
	public static function permissions(){
		$uploads = wp_upload_dir();
		$dirs = array(
			ABSPATH,
			WP_CONTENT_DIR,
			WP_PLUGIN_DIR,
			ABSPATH . '.htaccess',
			ABSPATH . 'wp-config.php',
			$uploads['basedir']
		);
		$perms = array();
		foreach($dirs as $d){
			if( file_exists($d) ){
				$perms[] = array( 'path'=>$d, 'permissions'=> fileperms($d) );		
			}			
		}
		return $perms;
	}
	public static function server_load(){
		if( System_Info_Tools::is_windows() ){
			
		}
		else{
		
		}
	}
	public static function cpu_load(){
		if( System_Info_Tools::is_windows() ){
			exec('wmic cpu get loadpercentage', $output);
		}
		else{
			#???
		}
	}
	public static function uptime(){
		$cmd = (System_Info_Tools::is_windows()) ? "net statistics workstation | find 'Statistics since' " : 'uptime';
		exec($cmd, $output);		
	}
	/*
	NOT YET IMPLEMENTED
	public static function quick_scan(){		
		$suspicious('eval',
					'base64_decode',
					'shell_exec',
					'hacked by',
					'viagra',
					'iframe'
		);		
		//  filemtime
		// .htaccess		
		#glob(ABSPATH,		
	}
	*/
}