Wordpress Total Details


http://www.smashingmagazine.com/2015/07/designing-simple-pie-charts-with-css/

Add Comment Disabling



[TO DO]
Catch Fatal Errors, Display Like Simplified Whoops
For errors, open the file, show 5 before, 5 after hilight error line
??	show callstack 2 before
Issue with Services on Linux

---------------------------------------------------------------------

Goals
	Secure:			No security or DDOS vulnerabilities. Load nothing if the user is not an admin
	Fast:			Zero Impact when not used
	Insight:		Provide transparency into the how Wordpress functions for developers
wp_ajax_health_beacon
	Posts
	Comments	
	Active Plugins
	CPU
	Memory
	Disk
-----------------------------------------------
On Comment:	
	deny comments without a referrer
Beacon:
	Plugin Updates
	Wordpress Updates	
	
-----Inspiration
https://github.com/eworksmedia/php-server-status-dashboard
https://github.com/afaqurk/linux-dash		

[Security]

https://wordpress.org/plugins/debug-bar-constants/screenshots/
https://wordpress.org/plugins/debug-bar-shortcodes/


Check for 
	apc,gd,imagick,memcache
		
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
		
	