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
	}
	
	public static function admin_menu(){
		$slug = 'wp-total-details';
		$cap = 'edit_pages';
		
		add_menu_page('Info', 'Total Details', 						$cap, $slug, [__CLASS__,'admin_tab'], 'dashicons-welcome-view-site');
		
		add_submenu_page($slug, 'Info', 'Info', 					$cap, $slug, [__CLASS__,'admin_tab']);
		if( version_compare(PHP_VERSION,'7.0.0') >= 0 ){
			add_submenu_page($slug, 'OpCache', 'OpCache', 			$cap, 'wptd-opcache', [__CLASS__,'admin_tab']);
		}
		add_submenu_page($slug, 'Sessions', 'Sessions',				$cap, 'wptd-Sessions', [__CLASS__,'admin_tab']);
		add_submenu_page($slug, 'Database', 'Database', 			$cap, 'wptd-DB', [__CLASS__,'admin_tab']);
		add_submenu_page($slug, 'Cron', 'Cron', 					$cap, 'wptd-Cron', [__CLASS__,'admin_tab']);
		add_submenu_page($slug, 'Rewrites', 'Rewrites', 			$cap, 'wptd-Rewrites', [__CLASS__,'admin_tab']);
		add_submenu_page($slug, 'Post Types', 'Post Types', 		$cap, 'wptd-post_types', [__CLASS__,'admin_tab']);
		add_submenu_page($slug, 'Errors', 'Errors', 				$cap, 'wptd-Errors', [__CLASS__,'admin_tab']);
		add_submenu_page($slug, 'Hooks', 'Hooks', 					$cap, 'wptd-Hooks', [__CLASS__,'admin_tab']);
		add_submenu_page($slug, 'Functions', 'Functions', 			$cap, 'wptd-Functions', [__CLASS__,'admin_tab']);		
		add_submenu_page($slug, 'Options', 'WP Options', 			$cap, 'wptd-Options', [__CLASS__,'admin_tab']);
		add_submenu_page($slug, 'Globals', 'Globals', 				$cap, 'wptd-globals', [__CLASS__,'admin_tab']);		
		add_submenu_page($slug, 'Roles', 'Roles', 					$cap, 'wptd-roles', [__CLASS__,'admin_tab']);
		add_submenu_page($slug, 'Shortcodes', 'Shortcodes', 		$cap, 'wptd-shortcodes', [__CLASS__,'admin_tab']);
		add_submenu_page($slug, 'Blocks', 'Blocks', 				$cap, 'wptd-blocks', [__CLASS__,'admin_tab']);	
		add_submenu_page($slug, 'Statistics', 'Statistics', 		$cap, 'wptd-Statistics', [__CLASS__,'admin_tab']);

		add_submenu_page($slug, 'Theme', 	 'Theme', 				$cap, 'wptd-theme', [__CLASS__,'admin_tab']);
		add_submenu_page($slug, 'Tree View', 'Tree View', 			$cap, 'wptd-tree-view', [__CLASS__,'admin_tab']);
	}
	#-----------------------------Tabs
	
	public static function admin_tab(){
		$start = microtime(true);
		$tab = $_GET['page'];
		$tab = str_replace('wptd-','',$tab);
		//sanitize the tab?
		if($tab == 'wp-total-details')
			$tab = 'Info';
		?>
		<div class='wrap dev-bar-admin'>			
			<div class=container>
				<h1><i class='fa fa-cogs cGreen'></i> Total Details - <span class=cBlue><?php echo $tab?></span></h1>
				<?php 
					$tab_path = __DIR__.'/../views/admin/tabs/'.$tab.'.php';
					if( !include($tab_path) ){
						echo "<h2>Error with Section [{$tab}]</h2>";
					}
				?>				
			</div>
		</div>	
		<?php		
		$total = microtime(true) - $start;
		echo "<h4>Time Taken - ".number_format($total*1000,2)."ms</h4>";		
	}
	
	#-------------AJAX
	public static function ajax_function_search(){	
		if( !current_user_can('manage_options') ){
			wp_send_json_error(['msg'=>'Access Denied']);
		}
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
		$count = count($funcs);
		
		$search = $_POST['search'] ?? '';

		echo "<h3>{$count} Functions found ".esc_html($search)."</h3>";
		$items = array();
		foreach($funcs as $function_name){ 
			if( empty($search) ){
				$items[] = $function_name;
			}
			else if( stripos($function_name, $search) !== false ){
				$items[] = $function_name;
			}
			
			if($count <= 10){
				#$cls = (!in_array($class,['(Internal)','(User)'])) ? $class:false;
				#$item .= self::getFunctionSource($f, $cls);
			}
		} 
		#print_r($items);
		echo "<ul>";
		$items = array_unique($items);
		sort($items);
		foreach($items as $f){
			echo "<li>{$f}</li>";
		}
		echo "</ul>";
		exit;
	}
	
	public function getFunctionSource($function, $class=false){
		if($class == false){
			$func = new ReflectionFunction('myfunction');
		}
		else{
			$func = new ReflectionMethod($class, $function);
		}		
		
		$filename 	= $func->getFileName();
		$start_line = $func->getStartLine() - 1; // -1 to get function() block
		$end_line 	= $func->getEndLine();
		$length 	= $end_line - $start_line;

		$source = file($filename);
		$body 	= implode('', array_slice($source, $start_line, $length));
		return "<code class=php>".print_r($body, true)."</code>";
	}
	
	public static function ajax_replace_content(){		
		global $wpdb;
		#$wpdb->content
		exit;
	}
	public static function ajax_optimize_table(){ 
		wp_send_json(System_Info_SQL::optimize_table($_REQUEST['table'])); 
	}
	
	public static function admin_scripts($suffix){
		$folder = plugins_url( '../media/');
		wp_enqueue_style( 'debug-bar-admin', plugins_url( '../media/css/admin.css',__FILE__));
		
		if($suffix != 'toplevel_page_sys_info'){
			return;		
		}
		
		#wp_enqueue_script('td-admin', $folder.'js/Admin.js',['jquery']);

		
	}
	
	
}
