VERSION: 1.00001a
Goals
	Secure:			No security or DDOS vulnerabilities. Load nothing if the user is not an admin
	Fast:			Zero Impact when not used
	Insight:		Provide transparency into the how Wordpress functions for developers

------------------------------------------------------------------------------	


OverWhelming Pages:	
	Developer Only	
		Info (Need Restricted Version)
		MySQL
		Merge DNS & Whois
	
Settings	
	Disable Comments
	Pingback header
	XML RPC
	Emoji Scripts				
	Generator					remove_action('wp_head', 'wp_generator');
	
	
	Speed & Security
		prefix
		file permissions
		admin username exists
		
[Comment Management]

Comments:
		Delete all by IP
		Delete by URL Contents
		Delete by Comment Contents
		Delete by Comment Agent	
	Show Comments by IP	
	Comment Stats
		by IP
		by Agents
		by
		by post id		
		
	Comments Stats:
		Top User Agents		
		Top


Show Recently Modified Files
	

[TO DO]
Filter List/Search

Catch Fatal Errors, Display Like Simplified Whoops
For errors, open the file, show 5 before, 5 after hilight error line
??	show callstack 2 before
Issue with Services on Linux

-----------------------------------------------
On Comment:	
	Deny comments without a referrer
Beacon:
	Plugin Updates
	Wordpress Updates	
	
-----Inspiration
https://github.com/eworksmedia/php-server-status-dashboard
https://github.com/afaqurk/linux-dash		

[Security]

https://wordpress.org/plugins/debug-bar-constants/screenshots/
https://wordpress.org/plugins/debug-bar-shortcodes/


Check for :		apc,gd,imagick,memcache
		
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
		
	