<?php
/*
Plugin Name: System Info
Plugin URI: http://www.github.com/robertmeyerjr/wordpress-system-info/
Description: This needs a lot of work before it can be released. 
Version: 1.0a
Author: Robert Meyer Jr.
Author URI: http://www.robertmeyerjr.com
License: GPL2

*/

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

System_Info::init();
class System_Info{
	public static $cpu_info;
	public static $load_time 			= array();
	public static $_profile 			= array();
	public static $_function_count 		= array();
	public static $_hook_history 		= array();
	public static $_total_hook_time 	= array();
	public static $_path;
	public static $_last_time;
	public static $section;
	public static $_CORE_MEM_USAGE;
	public static $_templates_used = array();
	
	public static $messages = array();
	
	public static function init(){		
		self::$_path 		= realpath( dirname( __FILE__ ) );
		self::$_last_time 	= microtime(true);
		
		add_action('init', array(__CLASS__, 'startup'));		
		
		#Add some initial start times
		self::$load_time['start']  = ( empty($_SERVER['REQUEST_TIME_FLOAT']) ) ? $_SERVER['REQUEST_TIME'] : $_SERVER['REQUEST_TIME_FLOAT'];
		self::$load_time['Core Load'] = microtime(true) - self::$load_time['start'];
		
		//Should add check here, only admin, or other url var to validate
		if( isset($_GET['sysinfo_bench']) && $_GET['sysinfo_bench'] == 1 ){
			define('SAVEQUERIES', true );			
			self::benchmarking();
			self::getCpuUsage();
		}							
		
	}
	
	public static function read_csv_array($arr, $delimiter=','){		
		foreach($arr as &$a){
			$a = explode(',', $a);
			d($a);
		}
		return $arr;
		#return array_map('explode', $arr, array($delimiter));
	}
	
	public static function ajax_tab(){
		$start = microtime(true);
		switch($_REQUEST['tab']){
			case 'info': 			self::page_info(); break;
			case 'phpinfo': 		self::page_php_info(); break;
			case 'db': 				self::page_db_info(); break;
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
	public static function run_command($cmd, $return_as=null){
		if( !empty( $cmd ) )
			exec($cmd, $output);
		return $output;
	}
	#-----------------Work in progress
	public static function page_open_ports(){
		$cmd = (self::is_windows()) ? 'netstat -ano | find "LISTENING"':'lsof -i -n | egrep "COMMAND|LISTEN"';
		$out = self::read_csv_array( self::run_command( $cmd ),"\t");
		d($out);
		#self::out_table($out);
		#print_r($out);
	}
	
	#-------------------------------
	public static function startup(){		
		if(is_user_logged_in() && current_user_can('administrator') ){
			add_action('admin_enqueue_scripts', 			array(__CLASS__, 'admin_scripts'), 90);
			add_action('admin_menu', 						array(__CLASS__, 'admin_menu'));			
			add_action('wp_ajax_sysinfo', 					array(__CLASS__, 'ajax_tab'));		
			add_action('wp_ajax_sysinfo_optimize_table', 	array(__CLASS__, 'ajax_optimize_table'));							
			add_action('wp_ajax_sysinfo_clear_error_log', 	array(__CLASS__, 'clear_error_log'));			
			add_action('wp_ajax_sysinfo_replace_content', 	array(__CLASS__, 'ajax_replace_content'));							
			add_action('wp_ajax_sysinfo_search_hooks', 		array(__CLASS__, 'get_hooks'));			
			add_action('wp_ajax_sysinfo_search_functions', 	array(__CLASS__, 'ajax_function_search'));			
			add_action('wp_ajax_sysinfo_explain_query', 	array(__CLASS__, 'explain_query'));
			add_action('wp_ajax_sysinfo_run_code', 			array(__CLASS__, 'run_code'));
			
			#This filter will be in Wordpress 3.6
			add_filter('locate_template', function($template_name, $load, $require_once){
				#ToDO: Backtrace and record where included from
				System_Info::$_templates_used[] = $template_name;
				return $template_name;
			}, 10, 3);
			
			add_filter('show_admin_bar', array(__CLASS__,'admin_bar')); 
		}
	}
	
	public static function admin_bar($bar){
		return $bar;
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
		if($filter == 'setup_theme'){
			self::$load_time['until_theme'] = $time;
		}
		elseif($filter == 'init'){
			#self::$load_time[''] = 
		}
		return $input;
	}
	
	public static function explain_query(){
		global $wpdb;
		$sql = "EXPLAIN ".stripslashes($_POST['sql']); 
		$results = $wpdb->get_results($sql);
		?>
		<table class=query_table>
			<thead>
				<tr>
					<th>id</th>
					<th>Table</th>
					<th>Select Type</th>
					<th>Possible Keys</th>
					<th>key</th>
					<th>key length</th>
					<th>ref</th>
					<th>rows</th>
					<th>Extra</th>
				</tr>
			</thead>
			<tbody>
			<?php foreach($results as $r) : ?>
				<tr>
					<td><?php echo $r->id?></td>
					<td><?php echo $r->table?></td>
					<td><?php echo $r->select_type?></td>
					<td><?php echo $r->possible_keys?></td>
					<td><?php echo $r->key?></td>
					<td><?php echo $r->key_len?></td>
					<td><?php echo $r->ref?></td>
					<td><?php echo $r->rows?></td>
					<td><?php echo $r->Extra?></td>
				</tr>
			<?php endforeach; ?>
			</tbody>
		</table>
		<?php
		exit;
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
		$FIRST_ACTION 		= -99999;
		$LAST_ACTION	 	=  99999;
		//We want a hook on all, late and early
		add_action( 'all', array(__CLASS__,'all_hook'), $FIRST_ACTION);
		add_action( 'all', array(__CLASS__,'all_hook'), $LAST_ACTION);

		add_action('muplugins_loaded',	array(__CLASS__,'timer_start'),	$FIRST_ACTION);
		add_action('muplugins_loaded',	array(__CLASS__,'timer_stop'),	$LAST_ACTION);
		
		add_action('muplugins_loaded',	function(){ System_Info::$_CORE_MEM_USAGE = memory_get_peak_usage();  },	$LAST_ACTION);
		
		add_action('plugins_loaded', 	array(__CLASS__,'timer_start'),	$FIRST_ACTION);
		add_action('plugins_loaded',	array(__CLASS__,'timer_stop'),	$LAST_ACTION);
		
		add_action('setup_theme',		array(__CLASS__,'timer_start'),	$FIRST_ACTION);
		add_action('setup_theme',		array(__CLASS__,'timer_stop'),	$LAST_ACTION);
		
		add_action('after_setup_theme',		array(__CLASS__,'timer_start'),	$FIRST_ACTION);
		add_action('after_setup_theme',		array(__CLASS__,'timer_stop'),	$LAST_ACTION);
		
		add_action('init',					array(__CLASS__,'timer_start'),	$FIRST_ACTION);
		add_action('init',					array(__CLASS__,'timer_stop'),	$LAST_ACTION);
		
		add_action('wp_loaded',				array(__CLASS__,'timer_start'),	$FIRST_ACTION);
		add_action('wp_loaded',				array(__CLASS__,'timer_stop'),	$LAST_ACTION);
		
		add_action('template_redirect',		array(__CLASS__,'timer_start'),	$FIRST_ACTION);
		add_action('template_redirect',		array(__CLASS__,'timer_stop'),	$LAST_ACTION);
		
		
		add_action('wp_head',			array(__CLASS__,'timer_start'),	$FIRST_ACTION);
		add_action('wp_head',			array(__CLASS__,'timer_stop'),	$LAST_ACTION);
		
		add_action('shutdown',			array(__CLASS__,'timer_start'),	$FIRST_ACTION);
		add_action('shutdown',			array(__CLASS__,'timer_stop'),	$LAST_ACTION);
		
		add_filter('the_content',		array(__CLASS__,'timer_start'),	$FIRST_ACTION);
		add_filter('the_content',		array(__CLASS__,'timer_stop'),	$LAST_ACTION);
		
		#Used for graphs
		wp_enqueue_script('google_jsapi', 'https://www.google.com/jsapi');
		wp_enqueue_script('datatables','http://ajax.aspnetcdn.com/ajax/jquery.dataTables/1.9.4/jquery.dataTables.min.js', array('jquery'));
		wp_enqueue_style('datatables', 'http://ajax.aspnetcdn.com/ajax/jquery.dataTables/1.9.4/css/jquery.dataTables.css'); 
		wp_enqueue_style('datatables', 'http://ajax.aspnetcdn.com/ajax/jquery.dataTables/1.9.4/css/jquery.dataTables_themeroller.css'); 
		
		add_action('init', function(){
			wp_enqueue_script('jquery-ui-dialog');
			wp_enqueue_script('jquery-ui-accordion');
			wp_enqueue_script('jquery-ui-tabs');
		});		
		wp_enqueue_style('jquery-ui-style', 'http://ajax.googleapis.com/ajax/libs/jqueryui/1.8.2/themes/smoothness/jquery-ui.css'); 
		#wp_enqueue_script('tablesorter', 'http://cachedcommons.org/cache/jquery-table-sorter/2.0.3/javascripts/jquery-table-sorter-min.js', array('jquery'));
		
		declare(ticks = 1);
		register_tick_function(array(__CLASS__, 'tick'), true);		
		add_action('shutdown', array(__CLASS__, 'benchmark_output'), $LAST_ACTION);
		#Maybe register an error handler to catch all errors?		
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
		$file = str_replace('\\','/',$file); #Because f-u windows
		/*
		This is generic, does not take into account changes of defaults
		*/
		if(stripos($file, 'wp-includes') !== false){
			$area = 'WP Core';
		}
		else if(stripos($file, 'plugins') !== false){
			$area = 'Plugins';			
		}
		else if(stripos($file, 'themes') !== false){
			$area = 'Theme';
		}
		else if(stripos($file, 'wp-admin') !== false){
			$area = 'Wordpress Admin';
		}
		else{
			$area = 'Other';
		}
		
		$time = microtime(true) - self::$_last_time;
		
        if( !isset(self::$_profile[$area]) ){
            self::$_profile[$area] = array();
			self::$_function_count[$area] = array();			
			self::$section[$area] = 0;			
        }
		
		#Create the entry for the file
		if( !isset(self::$_profile[$area][$file]) ){
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
			if( headers_sent() ){
				$headers = headers_list();
				return;
			}
			*/
			//Are we an AJAX Request? Do we Want to Log to a file?
			self::$load_time['stop'] = microtime(true);			
			{{
			?>
			<style>
			#sysinfo-logger-bar{
				position: fixed !important;
				bottom: 0 !important;
				right: 0 !important;
				font-family: Helvetica, "Helvetica Neue", Arial, sans-serif !important;
				font-size: 14px !important;
				background-color: #222 !important;
				width: 100%;
				z-index: 9999 !important;
				color: #000;
				display:none;
			}
			#sysinfo-logger-bar h1{margin:0;padding:0;}
			#sysinfo-logger-bar .trace{font-size:0.6em;color:green;font-style:italic;}
			#sysinfo-logger-bar .date{color:blue;font-size:0.6em;font-style:italic;}
			#sysinfo-logger-bar .info_block{padding-right:10px;width:20%;margin-top:5px;display:inline-block;}			
			#sysinfo-logger-bar .msg{display:inline-block;width:75%;}
			#sysinfo-logger-bar .event{clear:both;margin-top:15px;margin:0 auto;border-radius:5px;}
			#sysinfo-logger-bar .event:hover{background-color:rgba(255,255,100,0.4);}
			#sysinfo-logger-bar .red{color:red}
			#sysinfo-logger-bar .green{color:green}
			#sysinfo-logger-bar .yellow{color:yellow;}
			#sysinfo-logger-bar .request{border:1px solid black;border-radius:5px;padding:5px;}
			.query_table{width:90%;margin:0 auto;}
			.query_table th{background-color:black;color:white;}			
			.query_display{width:100%;}
			.query_display thead{
				background-color:black;
				color:white;			
			}			
			.sysbench_output{font-size:16px;}			
			.sysbench_output table{border-collapse:collapse;}			
			.sysbench_output td.query{
				font-size:11px;
				width:80%;
				text-align:left;
				padding:3px;
				font-family: consolas, san-serif;
			}
			
			.sysbench_output td.backtrace{font-size:11px;}
			
			
			.sysbench_output .delete{color:red;}
			.sysbench_output .select,
			.sysbench_output .update,
			.sysbench_output .join,
			.sysbench_output .from{ color:green; }
			
			.sysbench_output .where{color:#013d7e;}
			.sysbench_output .clause{ color:orange; }
			
			.sysbench_output .order,
			.sysbench_output .limit,
			.sysbench_output .group{ color:purple; }
			
			.sysbench_output .string{color:#0e722c;font-style:italic;}
			.sysbench_output .func{color:#800000;}
			
			.sysbench_tab_menu li{
				display:inline-block;
				background-color:white;
				color:black;
				border-radius: 5px 5px 0 0;
				padding: 2px 5px;
			}
			.sysbench_tab_menu li.active{
				color:red;
			}
			
			</style>
			
			<div id=sysinfo-logger-bar>
				<ul class=menu>
					<li><a href=Queries>Queries</a></li>
				</ul>
				<table>
					<thead><tr><th>Area</th><th>Message</th></tr></thead>
					<tbody>
						<?php foreach(self::$messages as $log=>$msgs) : ?>
							<?php foreach(self::$messages as $log=>$msgs) : ?>
								<tr>
									<th><?php echo $log?></th>
									<td><?php echo $msgs?></td>
								</tr>
							<?php endforeach; ?>
						<?php endforeach; ?>
					</tbody>
				</table>
			</div>
			<?php 
				$wpdb_queries = $wpdb->queries; 
				$wpdb_query_time = 0;
				foreach($wpdb_queries as $q){
					$wpdb_query_time += $q[1];
				}				
				uasort($wpdb_queries,function($a,$b){ return $a[1]<$b[1];  });
				$total_time = self::$load_time['stop'] - self::$load_time['start'];
			?>
			<?php 
					if(self::is_windows()){
						$plugin_dir 	= str_replace('\\','/',WP_PLUGIN_DIR);
						$muplugin_dir 	= str_replace('\\','/',WP_CONTENT_DIR).'/mu-plugins';												
					}
					else{
						$muplugin_dir 	= str_replace('\\','/',WP_CONTENT_DIR).'/mu-plugins';
						$plugin_dir 	= str_replace('\\','/',WP_PLUGIN_DIR);
					}					
					$plugin_times = array();
					foreach(self::$_profile['Plugins'] as $k=>$values){
						$name = str_replace($plugin_dir,'',$k);
						$name = str_replace($muplugin_dir,'',$name);
						$name = str_replace('\\','/',$name);
						$pos = strpos($name, '/',1)-1;
						$dash_pos = ($pos<0) ? strlen($name):$pos;
						$plugin_name = substr($name, 1, $dash_pos);
						
						$plugin_times[$plugin_name] += array_sum($values); 
					}
					
					$profiles = array();
					arsort(self::$section);
					arsort( $plugin_times );						
				?>
			<div id=sysbench_output class=sysbench_output style='display:none'>
				<div id=sysbench_tabs>
					<ul class=sysbench_tab_menu>
						<li><a href=#sysbench_graphs>Graphs</a></li>
						<li><a href=#sysbench_queries>Queries</a></li>
						<li><a href=#sysbench_files>Files</a></li>
						<li><a href=#sysbench_hooks>Hooks</a></li>
						<li><a href=#cache>Cache</a></li>
					</ul>
						<div id=sysbench_graphs>
							<h2>Total Load Time: <?php echo round($total_time,2)?> seconds</h2>
							<h2>Query Time: <?php echo number_format($wpdb_query_time,4)?> seconds</h2>
							
							<?php if( !self::is_windows() ) : ?>
								<h2>CPU Usage: <?php echo self::getCpuUsage()?></h2>
							<?php endif; ?>
							
							<table class=hook_times style='float:left'>
								<?php foreach(self::$load_time as $hook=>$time) : if(in_array($hook,array('start','stop'))) continue; ?>
									<tr><th><?php echo $hook?></th><td><?php echo round($time,4)?></td></tr>
								<?php endforeach; ?>
							</table>
							
							<?php 
								$qo = get_queried_object();
								if ( $qo && isset( $qo->post_type ) )
									$post_type = get_post_type_object( $qo->post_type );
								
								if ( !empty($template) )
									echo '<h2><span>Query Template:</span>' . basename($template) . "</h2>\n";
									
								echo "Request URI {$_SERVER['REQUEST_URI']}</br>";
								if ( empty($wp->matched_rule) )
									$matched_rule = 'None';
								else
									$matched_rule = $wp->matched_rule;

								echo '<h3>Matched Rewrite Rule:</h3>';
								echo '<p>' . esc_html( $matched_rule ) . '</p>';	
								
								if(!empty($bp)){
									echo '<h3>Buddypress:</h3>';
									echo '<p>current_component ' . esc_html( $bp->current_component ) . '</p>';	
									echo '<p>current_action ' . esc_html( $bp->current_action ) . '</p>';										
								}
								
								$usage 			= self::formatBytes( memory_get_peak_usage() );
								$core_usage 	= self::formatBytes( self::$_CORE_MEM_USAGE );
								$current_usage 	= self::formatBytes( memory_get_usage() );
								echo '<h3>Current Memory Usage:</h3>';
								echo "<p>{$current_usage}</p>";	
								echo '<h3>Peak Memory Usage:</h3>';
								echo "<p>{$usage}</p>";	
								echo '<h3>WP Core Memory Usage:</h3>';
								echo "<p>{$core_usage}</p>";									
							?>
							
							<div id=load_gauge style='float:left'></div>
							<div id=load_chart1 style='float:left'></div>
							<div id=load_chart2 style='float:left'></div>
							<br style='clear:both' />
							<div id=plugin_chart></div>
						</div>
						<div id=sysbench_queries>
							<h2><?php echo get_num_queries()?> SQL Queries took <?php echo number_format($wpdb_query_time,4)?> seconds</h2>
							<table class='datatable query_display'>
								<thead><tr><th>Time<th>Query<th>Backtrace<th>&nbsp;</thead>
								<tbody>
								<?php foreach($wpdb->queries as $q) : ?>								
								<?php list($query, $elapsed, $debug) = $q; ?>
									<tr>
										<td style='width:10%'><?php echo number_format($elapsed, 4)?></td>
										<td style='width:50%' class=query><?php echo $query?></td>
										<td style='width:30%' class=backtrace><?php 
											$qi = explode(',',$debug);
											echo implode('<br/>',$qi);
										?>
										<td style='width:10%'><button class=button-secondary onClick="sysinfo_explain(this);">Explain</button></td>
									</tr>
								<?php endforeach; ?>
								</tbody>
								<tfoot>
									<tr>
										<th style="text-align:right" colspan=3>Total:</th>
										<th></th>
									</tr>
								</tfoot>
							</table>
						</div>
						<div id=sysbench_files>
							<?php 
								$wp_content_dir = str_replace('\\','/',WP_CONTENT_DIR.'/'); 
								echo "<h2>".$wp_content_dir."</h2>";
							?>
							<?php $total_known = array_sum(self::$section);?>
							<label>Total Known Time</label> <?php echo $total_known?><br/>
							
							<table class=datatable>
								<thead>
										<tr>
											<th>Section</th>
											<th>File</th>
											<th>Function</th>
											<th>Count</th>
											<th>Time</th>
										</tr>
								</thead>
								<tbody>
								<?php foreach(self::$_profile as $section=>$data) :?>											
										<?php foreach($data as $file=>$data) : ?>
											<?php foreach($data as $function=>$t) : ?>												
													<tr>
														<td><?php echo $section?></td>														
														<td><?php echo str_replace($wp_content_dir,'',$file)?></td>
														<td><?php echo $function?></td>
														<td><?php echo self::$_function_count[$section][$file][$function]?></td>
														<td class=time><?php echo number_format($t,5)?></td>
													</tr>
												<?php endforeach; ?>
										 <?php endforeach; ?>
								<?php endforeach; ?>							
								</tbody>
								<tfoot>
									<tr>
										<th style="text-align:right" colspan=4>Total:</th>
										<th></th>
									</tr>
								</tfoot>
							</table>
							<?php #d( self::$_profile ); ?>
					</div>
					<div id=sysbench_hooks>		
						<table>
							<thead>
								<tr><th>Hook<th>Type<th>Time
							<tbody>
						<?php #foreach(self::$_hook_history as $h) :?>
							<tr>
								<td><?php echo $h['tag']?>
								<td><?php echo $h['type']?>
								<td><?php echo $h['time']?>
						<?php #endforeach; ?>
						</table>
					</div>
					<div id=cache>
						<?php 
							if( !empty($wp_object_cache) && method_exists($wp_object_cache,'stats') )
								$wp_object_cache->stats();
						?>
						<?php if( extension_loaded( 'wincache' ) ) : ?>
							Wincache
							<?php 
							#s( wincache_ucache_meminfo() );
							#s( wincache_ucache_info() );
							#s( wincache_scache_info() );
							
							#s( wincache_ocache_fileinfo() );
							#s( wincache_ocache_meminfo() );
							
							#s( wincache_fcache_fileinfo() );
							#s( wincache_fcache_meminfo() );
							?>
						<?php elseif( extension_loaded( 'apc' ) ) : ?>
							APC
							<?php 
								#s( apc_cache_info('user') );
								#s( apc_cache_info('filehits') );
								#s( apc_cache_info() );
							?>
						<?php else: ?>
							No Cache found (Currently only Wincache and APC are supported)
						<?php endif; ?>
					</div>
				</div>
			<div class=clear></div>
			<script>
			//if(console != 'undefined' && console.profileEnd != 'undefined')
				//console.profile();
			console.time('SysInfo');
			if(typeof(google)!='undefined'){
				google.load('visualization', '1.0', {'packages':['corechart']});
				google.load('visualization', '1', {packages:['gauge']});				
			}
			
			function calc_footer(nRow, aaData, iStart, iEnd, aiDisplay){
				var last = aaData[0].length - 1;
				var total = 0;
				for( var i=0 ; i<aaData.length ; i++ ){
					var n = parseFloat( aaData[i][last] );
					total += n; 
				}
				var visible_total = 0;
				for ( var i=iStart ; i<iEnd ; i++ ){
					visible_total += aaData[ aiDisplay[i] ][last]*1;
				}
				var nCells = nRow.getElementsByTagName('th');
				nCells[0].innerHTML = visible_total.toFixed(4)+' / '+total.toFixed(4);
				nCells[1].innerHTML = ((total - visible_total)*100 / 100).toFixed(4);
			}
			
			jQuery(document).ready(function(){
				jQuery('table.datatable').dataTable({
					"bPaginate": false,
					 "bJQueryUI": true,
					 'bRetrieve':true,
					 "sScrollY": "300px",
					 "fnFooterCallback": calc_footer
				});
				console.log('Opening sysbench output');
				jQuery('#sysbench_output').dialog({
					title: 		'System Info - Benchmarking',
					width:		'80%',
					minWidth: 	'600px',
				});
				jQuery('#sysbench_tabs').tabs();
				jQuery('table.time_table').tablesorter({selectorHeaders: '> thead > tr > th'});
				
				jQuery('.sysbench_tab_menu a').click(function(){
					var tab = jQuery(this).attr('href');
					jQuery('.sysbench_tabs > div').hide();					
					jQuery('.sysbench_tab_menu a').parent().removeClass('active');
					jQuery(tab).show();
					jQuery(this).parent().addClass('active');
					return false;
				});
				jQuery('.sysbench_tab_menu a:first').click();
				if(typeof(google)!='undefined'){
					pie1();
					pie2();
					plugin_chart();
				}
				jQuery('.section_accordion').accordion({
					collapsible: true,
					alwayOpen: false,
					active: false 
				});
				jQuery('#sysbench_output td.query').each(function(){
					var me = jQuery(this);
					var txt = me.text();
					txt = txt.replace(/(SELECT\s)/ig,'<b class=select>$1</b>');
					txt = txt.replace(/(UPDATE\s)/i,'<b class=update>$1</b>');
					txt = txt.replace(/(DELETE\s)/i,'<b class=delete>$1</b>');
					
					txt = txt.replace(/(\sFROM\s)/ig,'<b class=from>$1</b>' );
					txt = txt.replace(/(\sWHERE\s)/ig,'<b class=where>$1</b>' );
					txt = txt.replace(/(\RIGHT JOIN\s)/ig,'<br/><b class=join>$1</b>' );
					txt = txt.replace(/(\sLEFT JOIN\s)/ig,'<br/><b class=join>$1</b>' );
					txt = txt.replace(/(\sINNER JOIN\s)/ig,'<br/><b class=join>$1</b>' );
					txt = txt.replace(/(\sJOIN\s)/ig,'<br><b class=join>$1</b>' );
					
					//txt = txt.replace(/(<)/ig,'<b class=clause>$1</b>') ;
					//txt = txt.replace(/(>)/ig,'<b class=clause>$1</b>') ;
					txt = txt.replace(/(\s=)/ig,'<b class=clause>$1</b> ') ;
					txt = txt.replace(/(!=)/ig,'<b class=clause>$1</b> ') ;
					txt = txt.replace(/(\sLIKE\s)/ig,'<b class=clause>$1</b> ') ;
					txt = txt.replace(/(\sSET\s)/ig,'<b class=clause>$1</b> ') ;
					
					txt = txt.replace(/(\sAND\s)/ig,'<b class=clause>$1</b> ') ;
					txt = txt.replace(/(\sON\s)/ig,'<b class=clause>$1</b> ') ;
					txt = txt.replace(/(\sOR\s)/ig,'<b class=clause>$1</b> ') ;
					txt = txt.replace(/(\sGROUP BY\s)/ig,'<br><b class=group>$1</b>') ;
					txt = txt.replace(/(\sORDER BY\s)/ig,'<br><b class=order>$1</b>') ;
					txt = txt.replace(/(\DESC\s)/ig,'<b class=order>$1</b>') ;
					txt = txt.replace(/(\ASC\s)/ig,'<b class=order>$1</b>') ;
					txt = txt.replace(/(\sLIMIT\s)/ig,'<b class=limit>$1</b>') ;
					txt = txt.replace(/('[^']*')/ig,'<b class=string>$1</b> ') ;
					
					txt = txt.replace(/(\sCOUNT\([\w]+\))/ig,'<b class=func>$1</b>') ;
					txt = txt.replace(/(\sSUM)/ig,'<b class=func>$1</b>') ;
					txt = txt.replace(/(\sAVG)/ig,'<b class=func>$1</b>') ;
					txt = txt.replace(/(\sDISTINCT)/ig,'<b class=func>$1</b>') ;
					me.html(txt);
				});
				
				drawGauge();
				
				//if(console != 'undefined' && console.profileEnd != 'undefined')
					//console.profileEnd()
				console.timeEnd('SysInfo');	
			});
			function drawGauge(){
				var data = google.visualization.arrayToDataTable([
					  ['Label', 'Value'],
					  ['Time', <?php echo round($total_time,2)?>],
				]);

				var options = {
				  width: 150, height: 150,
				  redFrom: 6, redTo: 10,
				  yellowFrom: 3, yellowTo: 6,
				  greenFrom:0, 	greenTo:3,
				  minorTicks: 0.25,
				  min: 0, max: 10,
				  backgroundColor:'transparent'
				};

				var chart = new google.visualization.Gauge(document.getElementById('load_gauge'));
				chart.draw(data, options);			
			}
			function plugin_chart(){
				var data = google.visualization.arrayToDataTable([
					[ 'Plugin',
						<?php 
							$keys = array_keys($plugin_times);
							$keys = array_map(function($v){ return "'{$v}'"; },$keys);
							echo implode(',', $keys); 
						?>,'Theme <?php echo wp_get_theme()?>'
					],
					[
						'',
						<?php echo implode(',',$plugin_times); ?>,
						<?php echo self::$section['Theme']?>
					]					
				]);

				// Create and draw the visualization.
				new google.visualization.ColumnChart(document.getElementById('plugin_chart')).draw(
					data,
					{
						chartArea:{width:"60%"},
						title:"Time Per Plugin",
						width: '90%', 
						height: 500,
						backgroundColor:'transparent'
					}
				);				
			}
			function pie2(){
				var data = new google.visualization.DataTable();
				data.addColumn('string', 'Section');
				data.addColumn('number', 'Time');
				var time_plugin 	= <?php echo self::$load_time['plugins_loaded']?>;
				var time_theme 		= <?php echo self::$load_time['setup_theme']?>;
				var time_init 		= <?php echo self::$load_time['init']?>;
				var time_query 		= <?php echo $wpdb_query_time?>;
				data.addRows([ 
							<?php foreach(self::$section as $name=>$time):?>
								["<?php echo $name?> \t<?php echo round($time,2)?>",<?php echo $time?>],
							<?php endforeach; ?>
								["WP Load",<?php echo self::$load_time['Core Load']?>]								
							]);
				var options = {
					'title':'Load Time By Area', 'width':325, 'height':300,
					backgroundColor:'transparent'
				};
				var chart2 = new google.visualization.PieChart(document.getElementById('load_chart2'));
				chart2.draw(data, options);
			}
			function pie1(){
				var data = new google.visualization.DataTable();
				data.addColumn('string', 'Module');
				data.addColumn('number', 'Time');
				var time_plugin 	= <?php echo round(self::$load_time['plugins_loaded'],2)?>;
				var time_theme 		= <?php echo round(self::$load_time['setup_theme'],2)?>;
				var time_init 		= <?php echo round(self::$load_time['init'],2)?>;
				var time_query 		= <?php echo round($wpdb_query_time,2)?>;
				data.addRows([ 
								['Load Plugins '+time_plugin, 	time_plugin		], 
								['Load Theme '+time_theme,		time_theme		], 
								['Init '+time_init,			time_init		], 
								<?php if(!empty(self::$load_time['the_content'])) : ?>
									['Process Content <?php echo self::$load_time['the_content']?>', <?php echo self::$load_time['the_content']?>	], 
								<?php endif; ?>
								['SQL Queries', 	time_query		]
							]);
				var options = {
					'title':'Hook Time',
					'width':325,
					'height':300,
					backgroundColor:'transparent'
				};
				var chart1 = new google.visualization.PieChart(document.getElementById('load_chart1'));
				chart1.draw(data, options);
			}
			function sysinfo_explain(element){
				var $td = jQuery(element).parent().parent().find('td.query');
				var sql = $td.text();
				sql_html = $td.html();
				jQuery.ajax({
					url: 	'/wp-admin/admin-ajax.php',
					type:	'POST',
					data: 	{action:'sysinfo_explain_query',sql:sql},
					success:function(h){
						var d = '<div class=sysbench_output>'+sql_html+'<p>'+h+'<p></div>';
						jQuery(d).dialog({
							buttons: { "Ok": function () { jQuery(this).dialog("close"); } },
							close: function (event, ui) { jQuery(this).remove(); },
							modal: true,
							title: 'Query Explained',
							zIndex: 999999999,
							width: '960px'
						});
					}					
				});
			}
			</script>
			<?
			}}
			
	}
	
	
	function page_shell(){
		?>
		<div class=sysinfo_shell>
			<h3>PHP to Run</h3>
			<textarea id=sysinfo_code></textarea><br/>
			<button id=run_code type=button>Run</button>			
			<h3>Output</h3>
			<div id=sysinfo_output></div>
		</div>
		<script>
		jQuery(document).ready(function(){
			jQuery('#run_code').click(function(){
				var code = jQuery('#sysinfo_code').val();
				jQuery.ajax({
					url: ajaxurl,
					type:'POST',
					data: {action:'sysinfo_run_code',code:code},
					success: function(h){
						jQuery('#sysinfo_output').html(h);
					}
				});
			});
		});
		</script>
		<?
	}
	public function benchmark(){
		global $wpdb;
		$peak_mem = memory_get_peak_usage();
		$wpdb_query_time = 0;
		#Sort by time taken
		$wpdb_queries = isort($wpdb->queries, 1); #nope, change this, isort is from util.php
		#$wpdb_queries = $wpdb->queries; #No Sorting, in Order
		$total_query_time = 0;
		foreach($wpdb_queries as $q){
			$wpdb_query_time += $q[1];
			$time = number_format($q[1], 4);
		}
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
	}
	public function admin_menu(){
		//Should probably go under tools
		add_menu_page('Info', 'Server Info', 10, 'sys_info', array(__CLASS__,'info_page')); #, CIQ_TPL . '/images/ciq-icon.png' );
	}
	
	public function maintenance_mode() {
		//Should check if trying to login as well.
		if ( !current_user_can( 'edit_themes' ) || !is_user_logged_in() ) {
			wp_die('Site Maintenance, please come back soon.');
		}
	}
	
	public function page_mysql_info(){
		global $wpdb;
		$out = $wpdb->get_results('SHOW VARIABLES');
		self::out_table( $out, null, true);		
	}
	
	//------------------------------Load
	public function server_load(){
		if( self::is_windows() ){
			
		}
		else{
		
		}
	}
	public function cpu_load(){
		if( self::is_windows() ){
			exec('wmic cpu get loadpercentage', $output);
		}
		else{
			#???
		}
	}
	public function uptime(){
		$cmd = (self::is_windows()) ? "net statistics workstation | find 'Statistics since' " : 'uptime';
		exec($cmd, $output);		
	}
	
	//--------------------------Network
	public function whois($host){
		echo "{$host}<br/>";
		$tlds = array(
			'ac'     =>'whois.nic.ac',
			'ae'     =>'whois.nic.ae',
			'af'     =>'whois.nic.af',
			'ag'     =>'whois.nic.ag',
			'al'     =>'whois.ripe.net',
			'am'     =>'whois.amnic.net',
			'as'     =>'whois.nic.as',
			'at'     =>'whois.nic.at',
			'au'     =>'whois.aunic.net',
			'az'     =>'whois.ripe.net',
			'ba'     =>'whois.ripe.net',
			'be'     =>'whois.dns.be',
			'bg'     =>'whois.register.b',
			'bi'     =>'whois.nic.bi',    
			'biz'    =>'whois.biz',
			'bj'     =>'www.nic.bj',      
			'br'     =>'whois.nic.br',
			'bt'     =>'whois.netnames.ne',
			'by'     =>'whois.ripe.net',
			'bz'     =>'whois.belizenic.bz',
			'ca'     =>'whois.cira.ca',
			'cc'     =>'whois.nic.cc',
			'cd'     =>'whois.nic.cd',    
			'ch'     =>'whois.nic.ch',
			'ck'     =>'whois.nic.ck',
			'cl'     =>'nic.cl',
			'cn'     =>'whois.cnnic.net.cn',
			'co.nl'  =>'whois.co.nl',
			'com'    =>'whois.verisign-grs.com',
			'coop'   =>'whois.nic.coop',
			'cx'     =>'whois.nic.cx',
			'cy'     =>'whois.ripe.net',
			'cz'     =>'whois.nic.cz',
			'de'     =>'whois.denic.de',
			'dk'     =>'whois.dk-hostmaster.dk',
			'dm'     =>'whois.nic.cx',
			'dz'     =>'whois.ripe.net',
			'edu'    =>'whois.educause.net',
			'ee'     =>'whois.eenet.ee',
			'eg'     =>'whois.ripe.net',
			'es'     =>'whois.ripe.net',
			'eu'     =>'whois.eu',
			'fi'     =>'whois.ficora.fi',
			'fo'     =>'whois.ripe.net',
			'fr'     =>'whois.nic.fr',
			'gb'     =>'whois.ripe.net',
			'ge'     =>'whois.ripe.net',
			'gl'     =>'whois.ripe.net',
			'gm'     =>'whois.ripe.net',
			'gov'    =>'whois.nic.gov',
			'gr'     =>'whois.ripe.net',
			'gs'     =>'whois.adamsnames.tc',
			'hk'     =>'whois.hknic.net.hk',
			'hm'     =>'whois.registry.hm',
			'hn'     =>'whois2.afilias-grs.net',
			'hr'     =>'whois.ripe.net',
			'hu'     =>'whois.ripe.net',
			'ie'     =>'whois.domainregistry.ie',
			'il'     =>'whois.isoc.org.il',
			'in'     =>'whois.inregistry.net',
			'info'   =>'whois.afilias.info',
			'int'    =>'whois.isi.edu',
			'iq'     =>'vrx.net',
			'ir'     =>'whois.nic.ir',
			'is'     =>'whois.isnic.is',
			'it'     =>'whois.nic.it',
			'je'     =>'whois.je',
			'jp'     =>'whois.jprs.jp',
			'kg'     =>'whois.domain.kg',
			'kr'     =>'whois.nic.or.kr',
			'la'     =>'whois2.afilias-grs.net',
			'li'     =>'whois.nic.li',
			'lt'     =>'whois.domreg.lt',
			'lu'     =>'whois.restena.lu',
			'lv'     =>'whois.nic.lv',
			'ly'     =>'whois.lydomains.com',
			'ma'     =>'whois.iam.net.ma',
			'mc'     =>'whois.ripe.net',
			'md'     =>'whois.nic.md',
			'me'     =>'whois.nic.me',
			'mil'    =>'whois.nic.mil',
			'mk'     =>'whois.ripe.net',
			'mobi'   =>'whois.dotmobiregistry.net',
			'ms'     =>'whois.nic.ms',
			'mt'     =>'whois.ripe.net',
			'mu'     =>'whois.nic.mu',
			'mx'     =>'whois.nic.mx',
			'my'     =>'whois.mynic.net.my',
			'name'   =>'whois.nic.name',
			'net'    =>'whois.verisign-grs.com',
			'nf'     =>'whois.nic.cx',
			'nl'     =>'whois.domain-registry.nl',
			'no'     =>'whois.norid.no',
			'nu'     =>'whois.nic.nu',
			'nz'     =>'whois.srs.net.nz',
			'org'    =>'whois.pir.org',
			'pl'     =>'whois.dns.pl',
			'pr'     =>'whois.nic.pr',
			'pro'    =>'whois.registrypro.pro',
			'pt'     =>'whois.dns.pt',
			'ro'     =>'whois.rotld.ro',
			'ru'     =>'whois.ripn.ru',
			'sa'     =>'saudinic.net.sa',
			'sb'     =>'whois.nic.net.sb',
			'sc'     =>'whois2.afilias-grs.net',
			'se'     =>'whois.nic-se.se',
			'sg'     =>'whois.nic.net.sg',
			'sh'     =>'whois.nic.sh',
			'si'     =>'whois.arnes.si',
			'sk'     =>'whois.sk-nic.sk',
			'sm'     =>'whois.ripe.net',
			'st'     =>'whois.nic.st',
			'su'     =>'whois.ripn.net',
			'tc'     =>'whois.adamsnames.tc',
			'tel'    =>'whois.nic.tel',
			'tf'     =>'whois.nic.tf',
			'th'     =>'whois.thnic.net',
			'tj'     =>'whois.nic.tj',
			'tk'     =>'whois.nic.tk',
			'tl'     =>'whois.domains.tl',
			'tm'     =>'whois.nic.tm',
			'tn'     =>'whois.ripe.net',
			'to'     =>'whois.tonic.to',
			'tp'     =>'whois.domains.tl',
			'tr'     =>'whois.nic.tr',
			'travel' =>'whois.nic.travel',
			'tw'     =>'whois.apnic.net',
			'tv'     =>'whois.nic.tv',
			'ua'     =>'whois.ripe.net',
			'uk'     =>'whois.nic.uk',
			'gov.uk	'=>'whois.ja.net',
			'us'     =>'whois.nic.us',
			'uy'     =>'nic.uy',
			'uz'     =>'whois.cctld.uz',
			'va'     =>'whois.ripe.net',
			'vc'     =>'whois2.afilias-grs.net',
			've'     =>'whois.nic.ve',
			'vg'     =>'whois.adamsnames.tc',
			'ws'     =>'www.nic.ws',
			'yu'     =>'whois.ripe.net'      
		);
		$output = '';
		
		$domain = explode('.', $host);
		$ext = $domain[ count($domain)-1 ];
		$nic_server = $tlds[$ext];
		if(empty($nic_server)){
			echo "No whois server found for tld {$ext}<br/>";
			return;
		}
		$domain = substr($host, 0, -(strlen($ext)+1));
		echo "Using Whois Server: {$nic_server}<br/>";
		if ($conn = fsockopen ($nic_server, 43)){
        	fputs($conn, $domain."\r\n");
       	 	while(!feof($conn)) {
            	$output .= fgets($conn, 128);
        	}
        	fclose($conn);
		}
		echo "<pre>{$output}</pre>";
	}
	
	public function get_hooks(){
		global $wp_filter,$wp_actions,$merged_filters;		
		$hook = $wp_filter;
		ksort($hook);
		{{ 
		?>
			<table class='wp-list-table widefat fixed'>
				<thead>
					<tr>
						<th width="45%">Action</th>
						<th width="10%">Priority</th>
						<th width="45%">Tasks</th>
					</tr>
				</thead>
				<tbody id=hooks>
			<?php foreach($hook as $tag => $p) : if(stripos($tag,$_POST['search'])===false) continue; ?>
				<?php $count = count($p); ?>
				<?php foreach($p as $name => $props): ?>
				<tr>
					<th><a target=_blank href="http://adambrown.info/p/wp_hooks/hook/<?php echo $tag?>"><?php echo $tag?></td>	
					<td><?php echo $name?></td>
					<td><?php foreach($props as $val) :?>
						<div class=actions>
							<h2><?php echo $val['function']?></h2>
							<pre><?php echo htmlspecialchars(print_r($val,true)); ?></pre>
						</div>
						<?php endforeach; ?>
					</td>
				</tr>
				<?php endforeach; ?>
				</<tbody></tbody>
				>
			<?php endforeach; ?>
			<script>jQuery('.actions').accordion({collapsible: true,allwayOpen: false,active: false });</script>
			</table>		
		<?php 
		
		}}
		exit;
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
	public static function page_func(){
		$classes = get_declared_classes();
		sort($classes);
		/*
		http://php.net/manual/en/language.functions.php
		http://codex.wordpress.org/Function_Reference
		http://phpxref.ftwr.co.uk/buddypress/nav.html?_functions/index.html
		*/
		?>
		<label>Location</label>
			<select id=sysinfo_class>
				<optgroup label=Global>
					<option>(Internal)</option>
					<option>(User)</option>
				</optgroup>
				<optgroup label=Classes>
				<?php foreach($classes as $c) : ?>
					<option><?php echo $c?></option>
				<?php endforeach; ?>
				</optgroup>
			</select>
		<label>Function Name</label>
		<input type=text id=function_search>
		<button id=search_functions>Search</button>
		<div><ul id=func_area></ul></div>
		<script>
			jQuery('#search_functions').click(function(){  
				var search = jQuery('#function_search').val();
				jQuery.ajax({
					url: ajaxurl,
					type: 'POST',
					data: {
						action:'sysinfo_search_functions',
						search:search,
						class_name: jQuery('#sysinfo_class').val(),
					},
					success:function(h){
						jQuery('#func_area').html(h);
					}
				});
			});
		</script>
		<?php exit;		
	}
	#-------------AJAX Functions
	public static function page_hooks(){ 
	?>
		<label>Hook/Filter Name</label>
		<input type=text id=action_search>
		<button id=search_hooks>Search</button>
		<div id=hook_area></div>
		<script>
			jQuery('#search_hooks').click(function(){  
				var search = jQuery('#action_search').val();
				var type = jQuery('#search_type').val();
				jQuery.ajax({
					url: ajaxurl,
					type: 'POST',
					data: {action:'sysinfo_search_hooks',
						search:search,type:type},
					success:function(h){
						jQuery('#hook_area').html(h);
					}
				});
			});
		</script>
		<?php
	}
	public static function page_whois(){
		$host = self::get_domain( 'http://'.$_SERVER['HTTP_HOST'] );
		self::whois( $host );
	}
	public static function page_info(){
		global $wpdb,$wp_version;					
		?>
			<table class=server_info>
			<tr><th colspan=2 class=hdr>Software
			<tr><th>OS<td><?php echo self::color_format(php_uname())?>
			<tr><th>PHP Version<td><?php echo PHP_VERSION?>
			<tr><th>MySQL Version<td><?php echo self::color_format($wpdb->db_version()); ?>
			<tr><th>Webserver<td><?php echo self::color_format($_SERVER['SERVER_SOFTWARE'])?>
			<tr><th>Webserver User<td><?php echo (self::is_windows()) ? get_current_user():exec('whoami');?></tr>
			<tr><th colspan=2 class=hdr>Wordpress Info</th></tr>
			<tr><th>Wordpress Version</th><td><?php echo $wp_version?></tr>
			<tr><th>WP_DEBUG</th><td><?php echo self::color_format((WP_DEBUG)?'Yes':'No')?></tr>
			<tr><th>WP_DEBUG_DISPLAY</th><td><?php echo self::color_format((WP_DEBUG_DISPLAY)?'Yes':'No')?></tr>
			<tr><th>WP_DEBUG_LOG</th><td><?php echo self::color_format(WP_DEBUG_LOG)?></td></tr>
			<tr><th>WP_PLUGIN_DIR</th><td><?php echo self::color_format(WP_PLUGIN_DIR)?></tr>
			<tr><th>FS_METHOD</th><td><?php echo self::color_format(FS_METHOD)?></tr>			
			<tr><th>Post Revisions<td><?php echo (WP_POST_REVISIONS)?'Yes':'No'?></tr>
			<tr><th>Auto Save Interval</th><td><?php echo AUTOSAVE_INTERVAL?></tr>
			<tr><th colspan=2 class=hdr>MySQL Info</th></tr>
			<tr><th>MySQL Query Cache Size</th><td><?php 
				$query_cache = $wpdb->get_row("SHOW VARIABLES LIKE 'query_cache_size'");
				if(!$query_cache)
					echo "<span class=red>Query Cache Not Enabled!</span>";
				else 
					echo self::formatBytes($query_cache->Value);
			?></td></tr>
			<?php $slow_queries = self::check_query_log(); ?>
			<tr><th>Slow Query Output</th><td><?php echo $slow_queries->log_output?></td></tr>
			<tr><th>Slow Query Location</th><td><?php echo $slow_queries->slow_query_log?></td></tr>
			<tr><th>Slow Query Not Using Indexes</th><td><?php echo $slow_queries->log_not_using_indexes?></td></tr>
			<tr><th>Database<td><?php echo DB_NAME?>
			<tr><th>User<td><?php echo DB_USER?>
			<tr><th>Host<td><?php echo DB_HOST?>				
			<tr><th colspan=2 class=hdr>Environment Info
			<tr><?php if(!empty($_SERVER['APPLICATION_ENV']) ) : ?>
				<th>Environment<td><?php echo self::color_format($_SERVER['APPLICATION_ENV'])?>
			<?php endif; ?>
			<tr><th>Server API</th><td><?php echo self::color_format(PHP_SAPI)?></td></tr>
			<tr><th>Document Root</th><td><?php echo $_SERVER['DOCUMENT_ROOT'];?></th></tr>
			
			<?php if(($_SERVER['SERVER_SOFTWARE']=='Apache') && function_exists('apache_get_modules')) : 
					$modules = apache_get_modules(); ?>	
					<tr><th>Apache Modules</th>
					<td>
						<?php foreach($modules as $m) : ?>
							<span><?php echo $m?></span>
						<?php endforeach; ?>
					</td></tr>
			<?php endif; ?>
			
			<?
	}
	
	public static function get_domain($url){
	  $pieces = parse_url($url);
	  $domain = isset($pieces['host']) ? $pieces['host'] : '';
	  if (preg_match('/(?P<domain>[a-z0-9][a-z0-9\-]{1,63}\.[a-z\.]{2,6})$/i', $domain, $regs)) {
		return $regs['domain'];
	  }
	  return false;
	}
	
	
	public static function page_dns(){	
		$host = self::get_domain( 'http://'.$_SERVER['HTTP_HOST'] );
		$dns = dns_get_record( $host );
		$c = count($dns);
		?>
		<h3>DNS Records for <?php echo $host?></h3>
		<table class='wp-list-table widefat fixed'>
			<thead><tr><th>Host<th>Class<th>Type<th><th>TTL</tr></thead>
		<?php foreach($dns as $d) : ?>
			<tr>
				<td><?php echo $d['host']?></td>
				<td><?php echo $d['class']?></td>
				<td><?php echo $d['type']?></td>
				<td><?php 
					if($d['type'] == 'A')
						echo $d['ip'];
					elseif($d['type'] == 'MC')
						echo $d['pri'];
					elseif($d['type'] == 'TXT')
						echo $d['txt'];
				?></td>
				<td><?php echo $d['ttl']?></td>
		<?php endforeach; ?>
		</table>
		<?	
	}
	
	public static function page_cron(){
		$cron = _get_cron_array();
		?>
		<h3>WP Cron</h3>
		<table class='wp-list-table widefat fixed'>
			<thead><tr><th>ID</th><th>Name</th><th>args</th><th>Schedule</th></tr>
		<?php 
			foreach($cron as $arr){ 
				foreach($arr as $name=>$c){ 	
					foreach($c as $job){
					?><tr>
						<td><?php echo $name?></td>
						<td><?php echo $job['schedule']?></td>
						<td><?php 
							if( empty( $job['args'] ) ){
								echo "Empty";
							}
							else{
								echo "<div class=wpcron_job>";
								foreach($job['args'] as $k=>$j){
									if( is_array($k) ){ 
										foreach($k as $l=>$r){
											?>
											<h3>!!<?php echo $l?></h3>
											<div><?php echo print_r($r, true);?></div>
											<?
										}
									}								
									else{
										?>
											<h3>@<?php echo $k?></h3>
											<div><?php echo print_r($j, true);?></div>
										<?
									}
									echo "</div>";
								}						
							?>
							</td>
							<td><?php echo $job['interval']?></td>
		<?php 			}
					}#$c as $job
				} #arr as $name=>$c
			}#cron as $ar
		?>
		</table>
		<script>jQuery('.wpcron_jobs').accordion({collapsible: true,allwayOpen: false});</script>
		<h3>crontab</h3>
		<?php
		if( self::is_windows() ){
			$xmlString = self::run_command(exec('schtasks /query /XML'));
			$xml = simplexml_load_string($xmlString);
			print_r( $xmlString );
		}
		else{
			$out = self::run_command("/usr/bin/crontab -l");
			print_r($cout);
		}
	}
	
	public static function page_php_info(){
		?>
		
		<h2>PHP Settings</h2>
		<label>Loaded Configuration</label><?php echo php_ini_loaded_file();?><br/>
		<label>Extensions Directory</label><?php echo PHP_EXTENSION_DIR?><br/>
		<label>max_execution_time</label><?php echo ini_get('max_execution_time');?><br/>
		<label>post_max_size</label><?php echo ini_get('post_max_size');?><br/>
		<label>upload_max_filesize</label><?php echo ini_get('upload_max_filesize');?><br/>
		<label>memory_limit</label><?php echo ini_get('memory_limit');?><br/>
		<label>open_basedir</label><?php echo ini_get('open_basedir');?><br/>
		<label>short_open_tag</label><?php echo ini_get('short_open_tag');?><br/>
		<label>safe_mode</label><?php echo ini_get('safe_mode');?><br/>
		
		<?php if(function_exists('apc_cache_info')) : $info = apc_cache_info(); ?>
			<label>APC</label>
			<pre><?php 
				$fields = array(
					'num_slots',       
					'ttl',                 
					'num_hits',            
					'num_misses',          
					'num_inserts',         
					'expunges',            
					'start_time',          
					'mem_size',            
					'num_entries',         
					'file_upload_progress',
					'memory_type',         
					'locking_type',        
				);
				foreach($fields as $f)
					echo "<label></label> {$info[$f]}<br/>";
			?></pre>
		<?php endif; ?>
		
		<label>OpCode Cache</label>
		<?php if( extension_loaded( 'xcache' ) ) : ?>XCache
		<?php elseif( extension_loaded( 'apc' ) ) : ?>APC
		<?php elseif( extension_loaded( 'eaccelerator' ) ) : ?>EAccelerator
		<?php elseif( extension_loaded( 'Zend Optimizer+' ) ) : ?>Zend Optimizer+
		<?php elseif( extension_loaded( 'wincache' ) ) : ?>WinCache
		<?php else: ?>None
		<?php endif; ?>
		<br/>
		
		<?php $disabled = ini_get('disable_functions'); ?>
		<label>Disabled Functions</label>
		<?php if(!empty($disabled)) :?> 
		<?php foreach($disabled as $d) : ?>
			<?php echo $d?> is Disabled<br/>
		<?php endforeach; ?>
		<?php else: ?>
			None<br/>
		<?php endif; ?>
		<label>PHP Memory Usage: </label><?php echo self::formatBytes($mem_usage,2)?></br>
		<ul class=threecol>
		<?php 
		/*
		$extensions = get_loaded_extensions();
		foreach($extensions as $e): ?>
			<li><a href=http://www.php.net/manual/en/book.<?php echo strtolower($e)?>.php><?php echo $e?></a></li>		
		<?php endforeach; ?>
		</ul>
		*/ ?>
		<?php 
		phpinfo(); ?>
		<?
	}
	public static function page_procs(){
		$mem_usage = memory_get_usage();
		?>
		<table class='wp-list-table widefat fixed proc_table datatable'><thead><tr>
			<?php $procs = self::running_procs(); $keys = array_keys($procs[0]); ?>
			<?php foreach($keys as $h): ?>
				<th><?php echo $h?>					
			<?php endforeach; ?>
			</thead>
			 <?php $i=0; foreach($procs as $p) : ?>
				<tr <?php echo ($i++%2)?'class=alternate':''?>>
				<?php foreach($p as $d) : ?>
					<td><?php echo $d?>
				<?php endforeach; ?>
			<?php endforeach; ?>
		</table>
		<?
	}
	public static function page_permissions(){
		?>
		<table class='wp-list-table widefat fixed'>
					<thead>
						<tr>
							<th>Name</th>
							<th>Type</th>
							<th>Permissions</th>
							<th>User</th>
							<th>Group</th>
							<th>World</th>
						</tr>
					</thead>
					<tbody>
						<?php foreach(self::permissions() as $p) : ?>
							<?php 
								$perms = $p['permissions'];
								if (($perms & 0xC000) == 0xC000) 		$type = 'socket'; // Socket
								elseif (($perms & 0xA000) == 0xA000) 	$type = 'symlink'; // Symbolic Link
								elseif (($perms & 0x8000) == 0x8000)	$type = 'regular file'; // Regular
								elseif (($perms & 0x6000) == 0x6000)	$type = 'block';// Block special
								elseif (($perms & 0x4000) == 0x4000)	$type = 'directory';// Directory
								elseif (($perms & 0x2000) == 0x2000) 	$type = 'character';// Character special
								elseif (($perms & 0x1000) == 0x1000) 	$type = 'pipe';// FIFO pipe
								else 									$type = 'unkown';// Unknown
								
								#User
								$user = (($perms & 0x0100) ? 'r' : '-');
								$user .= (($perms & 0x0080) ? 'w' : '-');
								$user .= (($perms & 0x0040) ? (($perms & 0x0800) ? 's' : 'x' ) : (($perms & 0x0800) ? 'S' : '-'));
								#Group
								$group = (($perms & 0x0020) ? 'r' : '-');
								$group .= (($perms & 0x0010) ? 'w' : '-');
								$group .= (($perms & 0x0008) ? (($perms & 0x0400) ? 's' : 'x' ) : (($perms & 0x0400) ? 'S' : '-'));
								#World
								$world = (($perms & 0x0004) ? 'r' : '-');
								$world .= (($perms & 0x0002) ? 'w' : '-');
								$world .= (($perms & 0x0001) ? (($perms & 0x0200) ? 't' : 'x' ) : (($perms & 0x0200) ? 'T' : '-'));
							?>
							<tr>
								<th><?php echo $p['path']?></th>
								<td><?php echo $type?></td>
								<th><?php echo substr(sprintf('%o', $p['permissions']), -4);?></th>
								<td><?php echo $user?></td>
								<td><?php echo $group?></td>
								<td><?php echo $world?></td>
							</tr>
						<?php endforeach; ?>
					</tbody>
					</table>
					<?php 
					$user = (self::is_windows()) ? get_current_user():exec('whoami');
					?>
					chmod -R 755
					chown -R <?php echo $user?>:{$group} <?php echo WP_CONTENT_DIR?>
					
		<?
	}
	public static function page_php(){
	}
	public static function page_tools(){
		{{?>
		<a target=_blank href=http://sitecheck.sucuri.net/results/<?php echo $_SERVER['HTTP_HOST']?>/>Sucuri Sitecheck</a><br/>
		<a target=_blank href=#>Pingdom</a><br/>
		<!--
		http://safebrowsing.clients.google.com/safebrowsing/diagnostic?site=theajcf.org
		http://safeweb.norton.com/report/show?url=theajcf.org
		http://www.siteadvisor.com/sites/theajcf.org
		http://www.yandex.com/infected?url=theajcf.org&l10n=en
		-->
		<a target=_blank href=#>Alexa</a><br/>
		<a target=_blank href=#>Google</a><br/>					
		<form class=postbox>
			<h2>Content Replace</h2>
			<input type=hidden name=action value=sysinfo_replace_content>
			<label>Replace<label><input type=text name=replace><br/>
			<label>With<label><input type=text name=with><br/>
			<button type=button id=replace onClick="replace_content();">Replace</button>
		</form>
		<script>
		function replace_content(){
			$.ajax({
				url: ajaxurl,
				data: jQuery().serialize(),
				success: function(){
					alert('Replaced!');
				}
			});
		}
		</script>		
		<?php }}
	}
	
	
	public static function page_rewrite_rules(){
		$rules = ( self::is_windows() ) ? file_get_contents(ABSPATH.'/web.config') : file_get_contents(ABSPATH.'/.htaccess');
		echo "<pre>".htmlspecialchars($rules)."</pre>";
	}
	
	public static function is_windows(){
		return (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN');
	}
	public static function can_exec(){
		exec('echo test', $output);
		if(strstr($output,'test')===false)
			return false;
		return true;
	}
	public static function check_open_basedir(){
		$base_dir = ini_get('open_basedir');
	}
	
	public static function running_procs(){
		if( self::is_windows() ){
			$result = self::run_command("tasklist /v /fo CSV");
			print_r($result);			
		}
		else{
			exec('ps aux', $output);
			$c = count($output);
			$procs = array();
			for($i=1;$i<$c;$i++){
				$ps = $output[$i];
				$ps = preg_split('/ +/', $ps);
				$procs[] = array(
					'pid'		=> $ps[1],
					'cpu'		=> $ps[2],
					'mem'		=> $ps[3],
					'time'		=> $ps[8],
					'command'	=> $ps[10],
					'args'		=> (!empty($ps[11])) ? $ps[11]:''
				);				
			}
		}		
		return $procs;
	}
	public static function output_to_array($data){
		$c = count($data);
		$o = array();
		for($i=1;$i<$c;$i++){
			$cols = count( $data[$i] );
			$r = array();
			for($j=0;$j<$cols;$j++){
				$r[$data[0][$j]] = $data[$i][$j];
			}
			$o[] = $r;
		}
		return $o;
	}
	
	public static function get_after($input, $after){
		$str = substr($input,stripos($input,$after)+1);
		return trim($str );
	}
	
	public static function cpu_info(){
		if(self::is_windows()){
			/*
			echo %PROCESSOR_ARCHITECTURE% %PROCESSOR_IDENTIFIER% %PROCESSOR_LEVEL% %PROCESSOR_REVISION%
			*/
			exec("wmic CPU get name, NumberOfCores,description, LoadPercentage, maxclockspeed, extclock, manufacturer, revision /format:csv", $info);
			$cpu_info = self::wmic_to_array($info);											
		}
		else{
			exec('cat /proc/cpuinfo', $output);			
			$cpu_info = array(
				'Model'		=> 	self::get_after($output[4],':') ,
				'Cores'		=> 	self::get_after($output[11],':') ,				
				'Cache'		=> 	self::get_after($output[7],':') ,				
				'Total CPUs'=> 	self::get_after($output[9],':') 
			);			
		}		
		self::$cpu_info = $cpu_info;		
		return $cpu_info;		
	}
	public static function mem_info(){
		if( self::is_windows() ){
			exec('wmic MEMORYCHIP get banklabel, devicelocator, caption, capacity /format:csv', $info);
			$meminfo = self::wmic_to_array($info);			
		}
		else{		
			exec('cat /proc/meminfo',$output);
			$data = explode("\n", $output);
			$meminfo = array();
			foreach($data as $line){
				list($key, $val) = explode(":", $line);
				$meminfo[$key] = trim($val);
			}
		}
		return $meminfo;
	}
	public static function wmic_to_array($info){
		#unset($info[0]);
		$names = explode(',',$info[1]);
		$c = count($info);
		$name_count = count($names);
		$out = array();
		for($i=2; $i<$c;$i++){
			$d = explode(',',$info[$i]);
			$out[$i-2] = array();
			for($j=0;$j<$name_count;$j++){
				$out[$i-2][$names[$j]] = $d[$j];
			}
		}
		return $out;
	}
	
	#-----------Database
	public static function slow_queries(){
		global $wpdb;
		return $wpdb->get_results('SELECT db,user_host,
											avg(query_time) query_time,
											avg(rows_sent) rows_sent,
											avg(rows_examined) rows_examined,
											count(1) as times_called,
											sql_text
									FROM mysql.slow_log 
									WHERE db = :db
									GROUP BY sql_text
									ORDER BY query_time DESC,times_called DESC
									LIMIT 200;', array('db'=>$db->database_name));
	}
	public static function check_query_log(){
		global $wpdb;
		$sql = "SELECT @@log_output as log_output,
				@@slow_query_log as slow_query_log,
				@@LOG_QUERIES_NOT_USING_INDEXES as log_not_using_indexes";
		return $wpdb->get_row($sql);
	}
	public static function enable_slow_query_log_table(){
		global $wpdb;
		$sql = "SET GLOBAL log_output = 'TABLE';
				SET GLOBAL slow_query_log = 'ON'; 
				SET GLOBAL LOG_QUERIES_NOT_USING_INDEXES = 'ON';
				";
		$wpdb->get_results($sql);
	}
	public static function disable_slow_query_log_table(){	
		global $wpdb;
		$sql = "SET GLOBAL log_output = 'FILE';
				SET GLOBAL slow_query_log = 'OFF'; 
				SET GLOBAL LOG_QUERIES_NOT_USING_INDEXES = 'OFF';
				";	
		$wpdb->get_results($sql);
	}
	
	public static function optimize_table($table){
		global $wpdb;
		$sql = $wpdb->prepare("OPTIMIZE TABLE %s", $table);
		echo "<p>{$sql}</p>";
		$result = $wpdb->get_results($sql);
		self::out_table($result);
	}
	public static function get_tables(){
		global $wpdb;
		$tables = $wpdb->get_results("SHOW TABLE STATUS");
		foreach($tables as &$t){	 
			if($t->Data_length > 0){
				$t->fragmentation =  round( ($t->Data_free * 100 / $t->Data_length), 2)."%";
			}
		}
		return $tables;
	}
	public static function list_databases(){
		global $wpdb;
		$sql = "SELECT count(*) tables,
			table_schema,concat(round(sum(table_rows)/1000000,2),'MB') rows,
			concat(round(sum(data_length)/(1024*1024*1024),2),'GB') data,
			concat(round(sum(index_length)/(1024*1024*1024),2),'GB') idx,
			concat(round(sum(data_length+index_length)/(1024*1024*1024),2),'GB') total_size,
			concat(round(sum(data_free)/(1024*1024),2),'MB') free_space,
			round(sum(index_length)/sum(data_length),2) idxfrac, engine
			FROM information_schema.TABLES
			GROUP BY table_schema
			ORDER BY sum(data_length+index_length) DESC";
		$result = $wpdb->get_results($sql);	
		self::out_table($result, null, true);	
	}
	public static function convert_to_file_per_table(){	
		global $wpdb;
		return;
		$sql = "SELECT concat('ALTER TABLE ',TABLE_SCHEMA ,'.',table_name,' ENGINE=InnoDB;') 
				FROM INFORMATION_SCHEMA.tables where table_type='BASE TABLE' and engine = 'InnoDB';";
		$sql = "ALERT TABLE {$schema}.{$table} ENGINE=InnoDB;";		
		#$result = $wpdb->FetchObjects('OPTIMIZE TABLE :table', array('table'=>$tbl));
		foreach($result as $r){
			#$wpdb->query($r);
		}
	}
	
	public static function formatBytes($bytes, $precision = 2){ 
		$units = array('B', 'KB', 'MB', 'GB', 'TB'); 
		$bytes = max($bytes, 0); 
		$pow = floor(($bytes ? log($bytes) : 0) / log(1024)); 
		$pow = min($pow, count($units) - 1); 
		$bytes /= pow(1024, $pow);		
		return round($bytes, $precision) . ' ' . $units[$pow]; 
	}
	public static function color_format($v){
		$classes 	= array();
		$truthy 	= array('ON',1,'ENABLED','YES','TRUE');
		$falsy 		= array('OFF',0,'DISABLED','NO','FALSE');
		if( is_object($v) ){
			$v = @d( $v );
			$classes[] = 'object';
		}
		$upper = strtoupper($v);
		if(in_array($upper, $truthy))		$classes[] = 'truthy';
		if(in_array($upper, $falsy, true)) 	$classes[] = 'falsy';
		if(is_numeric($v))					$classes[] = 'number';
		if(is_date($v))						$classes[] = 'date';
		$c = implode(' ', $classes);
		return "<span class='{$c}'>{$v}</span>";	
	}
	public static function out_table($arr, $cols=null, $colorize=false){
		if($cols == null){ 
			$cols = array_keys((array)$arr[0]); #Treat object as array, then get its keys
		}
		echo "<table class='wp-list-table widefat fixed'>";
		echo "<thead><tr>";
		foreach($cols as $c){
			$name = str_replace('_',' ',$c);
			$name = ucwords($name);
			echo "<th>{$name}</th>";
		}		
		echo "</tr></thead><tbody>";
		if( count($arr) < 1 )
			echo "<tr><td colspan=".count($cols).">No Results Found</td></tr>";
		else
			foreach($arr as $r){
				echo "<tr>";
				foreach($r as $c){
					if($colorize)
						$c = self::color_format($c);					
					else
						if(is_object($c))
							$c = print_r($c, true);
					echo "<td>{$c}</td>";
				}
				echo "</tr>";
			}
		echo "</tbody></table>";
	}

	
	
	public static function info_page(){
		global $wpdb;
		?>
		<style>
		table.server_info {width:90%;}
		table.server_info th.hdr{background-color:black;color:white;text-align:center;}
		table.server_info th{text-align:left;}
		
		#func_area li{display:inline-block;margin-right:15px;}
		
		.infopage label{font-weight:bold;display:inline-block;width:230px}
		ul.threecol li{width:30%;display:inline-block;}
		.green{color:green;}
		.red{color:red;}
		.number{color:blue}
		.truthy{font-style:italic;color:green;}
		.falsy{font-style:italic;color:red}
		.date{color:green}
		#tabs .ui-tabs-panel{font-size:1.25em;}
		#tabs > div.ui-tabs-panel{max-height:80%;overflow-y:scroll;}
		.sysinfo_shell textarea,#sysinfo_output{
			width:90%;
			height:300px;
			background-color:black;
			color: #00ff00;
			border-radius:5px;
			overflow-y:auto;
		}			
		</style>
		<div id=icon-tools class=icon32></div>
		<h2>System Information</h2><br/>
		<script>
		jQuery(document).ready(function() {
			jQuery('#tabs').tabs({
				cache: true,
				load: function(e,ui){
					 jQuery(ui.panel).find(".tab-loading").remove();
				},
				select: function(e,ui){
					 var panel = jQuery(ui.panel);
					 if (panel.is(":empty")) {
						 panel.append("<div class='tab-loading'>Loading...</div>")
					}
				},
				load : function(){
					jQuery('#tabs .datatable').datatable({
						"bPaginate": false,
						 "bJQueryUI": true,
						 'bRetrieve':true
					});
				}
				
			});
		});
		function optimizeTable(table){
			jQuery.ajax({
				url: ajaxurl,
				data:{action:'sysinfo_optimize_table', table:table},
				dataType:'html',
				success:function(h){
					h = '<div>'+h+'</div>';
					jQuery(h).dialog({
						modal: true,
						buttons: { Ok: function() { jQuery( this ).dialog( "close" );}}
					});
				}
			});
		}
		</script>
		<div class='wrap infopage'>		
			<div id=tabs>
				<ul>
					<li><a href='<?php bloginfo('wpurl'); ?>/wp-admin/admin-ajax.php?action=sysinfo&tab=info'>Info</a></li>
					<li><a href='<?php bloginfo('wpurl'); ?>/wp-admin/admin-ajax.php?action=sysinfo&tab=phpinfo'>PHP Info</a></li>
					<li><a href='<?php bloginfo('wpurl'); ?>/wp-admin/admin-ajax.php?action=sysinfo&tab=mysql_info'>MySQL Info</a></li>
					<li><a href='<?php bloginfo('wpurl'); ?>/wp-admin/admin-ajax.php?action=sysinfo&tab=db'>Database</a></li>
					<li><a href='<?php bloginfo('wpurl'); ?>/wp-admin/admin-ajax.php?action=sysinfo&tab=perm'>Permissions</a></li>
					<li><a href='<?php bloginfo('wpurl'); ?>/wp-admin/admin-ajax.php?action=sysinfo&tab=procs'>Procceses</a></li>
					<li><a href='<?php bloginfo('wpurl'); ?>/wp-admin/admin-ajax.php?action=sysinfo&tab=services'>Services</a></li>
					<li><a href='<?php bloginfo('wpurl'); ?>/wp-admin/admin-ajax.php?action=sysinfo&tab=ports'>Open Ports</a></li>
					<li><a href='<?php bloginfo('wpurl'); ?>/wp-admin/admin-ajax.php?action=sysinfo&tab=rewrite'>Rewrite Rules</a></li>
					<li><a href='<?php bloginfo('wpurl'); ?>/wp-admin/admin-ajax.php?action=sysinfo&tab=cron'>Cron</a></li>
					<li><a href='<?php bloginfo('wpurl'); ?>/wp-admin/admin-ajax.php?action=sysinfo&tab=dns'>DNS</a></li>
					<li><a href='<?php bloginfo('wpurl'); ?>/wp-admin/admin-ajax.php?action=sysinfo&tab=whois'>Whois</a></li>
					<li><a href='<?php bloginfo('wpurl'); ?>/wp-admin/admin-ajax.php?action=sysinfo&tab=errors'>Error Log</a></li>
					<li><a href='<?php bloginfo('wpurl'); ?>/wp-admin/admin-ajax.php?action=sysinfo&tab=tools'>Tools</a></li>
					<li><a href='<?php bloginfo('wpurl'); ?>/wp-admin/admin-ajax.php?action=sysinfo&tab=hooks'>Hooks</a></li>
					<li><a href='<?php bloginfo('wpurl'); ?>/wp-admin/admin-ajax.php?action=sysinfo&tab=functions'>Functions</a></li>
					<li><a href='<?php bloginfo('wpurl'); ?>/wp-admin/admin-ajax.php?action=sysinfo&tab=shell'>Shell</a></li>
				</ul>
				<div id=info></div>
				<div id=mysql_info></div>
				<div id=php></div>
				<div id=db></div>
				<div id=perm></div>
				
				<div id=procs></div>
				<div id=services></div>
				<div id=ports></div>
				
				<div id=tools></div>
				<div id=cron></div>
				<div id=hooks></div>
				<div id=dns></div>
				<div id=whois></div>
				
				<div id=rewrite></div>
				<div id=errors></div>
				<div id=functions></div>
				<div id=shell></div>
			</div>
		</div>
		<?
	}
	
	
	
	public static function page_services(){
		if(self::is_windows()){
			$cmd = 'sc query';
			$out = self::run_command($cmd);
		}
		else{
			$cmd = 'chkconfig --list';
			$out = self::run_command($cmd);
		}
		print_r($out);		
	}
	public static function page_db_info(){
		?>
		<h3 class=hndle>Tables</h3>
					<table class='wp-list-table widefat fixed'>
						<thead>
							<tr>
								<th>Name</th>
								<th>Engine</th>
								<th>Rows</th>
								<th>Created</th>
								<th>Collation</th>
								<th>Size</th>
								<th>Fragmentation</th>
								<th></th>
							</tr>
						</thead>
					</thead>
					<tbody>
						<?php 			
						$tables = self::get_tables();
						$i = 0;
						foreach($tables as $t){ ?>
							<tr class="table-<?php echo $t->Name?> <?php echo ($i++%2)?'alternate':''?>">
								<td><?php echo $t->Name?></td>
								<td><?php echo $t->Engine?></td>
								<td><?php echo $t->Rows?></td>
								<td><?php echo $t->Create_time?></td>
								<td><?php echo $t->Collation?></td>
								<td><?php echo self::formatBytes($t->Data_length)?></td>
								<td><?php echo $t->fragmentation?></td>
								<td><?php if($t->Data_length < 1000000) : ?>
									<a href=# class=button-secondary onClick='optimizeTable("<?php echo $t->Name?>");'>Optimize</a>
									<?php else : ?>
									(Must be optimized Manually)
									<?php endif; ?>
						<?php } ?>
					</tbody>
					</table>
		<?
	}
	public static function page_errors(){
		?>
		<div style='overflow:auto;width:100%;;height:500px;'>
			<?php $error_log = ini_get('error_log');?>
			<h2>Error Log Location <?php echo $error_log?> <a href=# onClick="sysinfo_clear_log();" class=button-secondary>Clear Log</a></h2>
			<?php #preg_match('\[(.*)\] (.*) on line (\d+)', $l, $m); ?>
			<?php if(file_exists($error_log) && !empty($error_log)) : ?>
				<pre><?php echo htmlspecialchars(self::tail($error_log, 500));?></pre>
				<label>Log Size</label> <?php echo self::formatBytes(filesize($error_log),2); ?>
			<?php else : ?>
				<label class=update>Log is Empty</label>
			<?php endif; ?>
		</div>				
		<script>
		function sysinfo_clear_log(){
			jQuery.ajax({
				url:ajaxurl,
				data:{action:'sysinfo_clear_error_log'},
				success:function(){
				}
			});
		}
		</script>		
		<?
	}
	public static function clear_error_log(){
		$log = ini_get('error_log');
		if(!empty($log))
		unlink( $log );
		exit;
	}
	public static function tail($file, $lines, $asArray=false) {
		//global $fsize;
		$handle = fopen($file, "r");
		$linecounter = $lines;
		$pos = -2;
		$beginning = false;
		$text = array();
		while ($linecounter > 0) {
			$t = " ";
			while ($t != "\n") {
				if(fseek($handle, $pos, SEEK_END) == -1) {
					$beginning = true; 
					break; 
				}
				$t = fgetc($handle);
				$pos --;
			}
			$linecounter --;
			if ($beginning) {
				rewind($handle);
			}
			$text[$lines-$linecounter-1] = fgets($handle);
			if ($beginning) break;
		}
		fclose ($handle);
		$result = array_reverse($text);
		if($asArray)
			return $result;
		
		return implode("\n", $result);
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
	
	#Dup code
	public static function run_code(){
		$code = stripslashes($_POST['code']);
		$result = eval($code);
		if(function_exists('d'))
			d( $result );
		else{
			echo "<pre>";
				print_r($result, true);
			echo "</pre>";		
		}
		exit;
	}
	
	public static function ajax_replace_content(){		
		exit;
	}
	
	public static function ajax_optimize_table(){
		echo self::optimize_table($_REQUEST['table']);
		exit;
	}
	
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
	
	function getCpuUsage() {
		if(!function_exists('getrusage'))
			return false;
		$d = getrusage();	
		if(!defined('PHP_TUSAGE')){
			define('PHP_TUSAGE', microtime(true));
			define('PHP_RUSAGE', $d["ru_utime.tv_sec"]*1e6+$d["ru_utime.tv_usec"]);
			return;
		}
		else{
			$d["ru_utime.tv_usec"] = ($d["ru_utime.tv_sec"]*1e6 + $d["ru_utime.tv_usec"]) - PHP_RUSAGE;
			$time = (microtime(true) - PHP_TUSAGE) * 1000000;
			if($time > 0) {
				$cpu = sprintf("%01.2f", ($d["ru_utime.tv_usec"] / $time) * 100);
			} else {
				$cpu = '0.00';
			}	 
		}
		return $cpu;
	}
	
}

?>




