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
#See which of these can be conditional
include('inc/SI_Admin.php');
include('inc/Log_Highlight.php');
include('inc/SI_AJAX.php');
include('inc/SI_SQL.php');
include('inc/SI_Tools.php');

System_Info::startup();
class System_Info{	
	public static function startup(){
		add_action( 'init', array(__CLASS__,'init'));
		register_activation_hook(__FILE__, array(__CLASS__,'activate'));
		register_deactivation_hook(__FILE__, array(__CLASS__,'activate'));
	}	
	public static function init(){
		if(is_user_logged_in() && current_user_can('administrator') ){ #If we aren't admin, dont even run
			try{				
				System_Info_Admin::init();			
			}catch(Exception $e){
				add_action( 'admin_notices', function(){ 
					echo "<div class=updated><p>System_Info - Error<br/><pre>".print_r($e,true)."</pre></p></div>";
				});
			}
		}
	}
	//This process is not yet working
	public static function activate(){
		#error_log("Activating...\r\n",3,__DIR__.'/activate.log');
		$mu_plugins = self::get_mu_plugins_dir();
		$file_path = $mu_plugins.'/System_Info_Bootstrap.php';
		$from = __DIR__.'/inc/System_Info_Bootstrap.php';
		
		if( !is_dir($mu_plugins) ){
			mkdir($mu_plugins);
		}		
		if( copy($from,$file_path) ){
			add_action( 'admin_notices', function(){ 
				echo "<div class=updated><p>System_Info - Must-Use Bootstrapper Installed</p></div>";
			});
		}
		else{
			add_action( 'admin_notices', function(){ 
				echo "<div class=error><p>System_Info - Must-Use Bootstrapper Failed!</p></div>";
			});
		}
	}
	public static function deactivate(){
		#error_log("Deactivating...\r\n",3,__DIR__.'/deactivate.log');
		$mu_plugins = self::get_mu_plugins_dir();
		$file_path = $mu_plugins.'/System_Info_Bootstrap.php';
		if( file_exists($file_path) )
			unlink($file_path);
	}
	public static function get_mu_plugins_dir(){
		$wp_content_dir = ABSPATH . 'wp-content';
		return $wp_content_dir . '/mu-plugins';
	}
}
