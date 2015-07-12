<?php 

class System_Info_Admin{	
	
	#-------------------------------
	public static function init(){				
		add_action('admin_enqueue_scripts', 			array(__CLASS__, 'admin_scripts'), 90); #Run Late
		add_action('admin_menu', 						array(__CLASS__, 'admin_menu'));			
		
		#Move to AJAX
		#have single ajax, with a seperate "module=" parameter
		add_action('wp_ajax_sysinfo_optimize_table', 	array(__CLASS__, 	'ajax_optimize_table'));							
		add_action('wp_ajax_sysinfo_clear_error_log', 	array(__CLASS__, 	'clear_error_log'));			
		add_action('wp_ajax_sysinfo_replace_content', 	array(__CLASS__, 	'ajax_replace_content'));							
		add_action('wp_ajax_sysinfo_search_hooks', 		array(__CLASS__, 	'get_hooks'));			
		add_action('wp_ajax_sysinfo_search_functions', 	array(__CLASS__, 	'ajax_function_search'));			
		add_action('wp_ajax_sysinfo_explain_query', 	array(__CLASS__, 	'explain_query'));
	}
	
	public static function admin_menu(){
		$slug = basename(__CLASS__);
		
		add_menu_page('Info', 'Total Details', 'manage_options', 'total_details', array(__CLASS__,'main'));
		
		#add_menu_page('Info', 'Developer Bar', 'manage_options', $slug, array(__CLASS__,'main'));
		#add_submenu_page($slug, 'Options', 'Options', 'manage_options', $slug,  	array(__CLASS__,'options'));
		#add_submenu_page($slug, 'Donations', 'Donations', 'manage_options', 'ruxly_authnet_donations',  	array(__CLASS__,'donations'));
		
	}
	
	#-------------AJAX
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
	public static function ajax_optimize_table(){ wp_send_json(System_Info_SQL::optimize_table($_REQUEST['table'])); }
	
	public static function admin_scripts($suffix){
		
		wp_enqueue_style( 'debug-bar', plugins_url( '../media/css/admin.css',__FILE__));
		
		if($suffix != 'toplevel_page_sys_info'){
			return;		
		}
		
		wp_enqueue_script('jquery-ui-dialog');	
		wp_enqueue_script('jquery-ui-accordion');
		wp_enqueue_script('jquery-ui-tabs');			
		wp_enqueue_script('jquery-ui-datepicker');				
		wp_enqueue_script('tablesorter', 'https://cdnjs.cloudflare.com/ajax/libs/jquery.tablesorter/2.22.1/js/jquery.tablesorter.js', array('jquery'));	
		
		//Enqueue if not already
		if(!wp_style_is('font-awesome') && !wp_style_is('fontawesome') ){
			wp_enqueue_style( 'font-awesome', 'https://maxcdn.bootstrapcdn.com/font-awesome/4.2.0/css/font-awesome.min.css');
		}
		
	}
	
	
	#-----------------------------Tabs
	public static function main(){ 
		include(__DIR__.'/../views/admin/SystemInfo.phtml'); 
	}
	public static function page_mysql_info(){
		global $wpdb;
		$out = $wpdb->get_results('SHOW VARIABLES');
		System_Info_Tools::out_table( $out, null, true);		
	}
	public static function page_open_ports(){
		
	}
	public static function page_services(){
		if(System_Info_Tools::is_windows()){
			$cmd = 'sc query';
			$out = System_Info_Tools::run_command($cmd);
			System_Info_Tools::out_table($out);
		}
		else{
			$cmd = 'chkconfig --list'; #does this work everywhere?
			$out = System_Info_Tools::run_command($cmd);
			var_dump($out);
		}
	}
	//TODO, Combine these into a single function based on the page variable
	public static function get_hooks(){
		global $wp_filter,$wp_actions,$merged_filters;		
		$hook = $wp_filter;
		ksort($hook);
		include(__DIR__.'/../views/admin/Tab_Hooks2.phtml');
		exit;
	}
	
	#----------------Server Details
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
					'exec('
					'shell_exec(',
					'hacked by',
					'viagra',
					'iframe'
		);		
		//  filemtime
		// .htaccess		
	}
	*/
}