VERSION: 1.00001a

TODO:
TODO: CSS and JS that isn't in enqueue




https://kinsta.com/knowledgebase/wp-options-autoloaded-data/
To Add: Auto Load Data Size:
SELECT SUM(LENGTH(option_value)) as autoload_size FROM wp_options WHERE autoload='yes';

SELECT 'autoloaded data in KiB' as name, ROUND(SUM(LENGTH(option_value))/ 1024) as value FROM wp_options WHERE autoload='yes'
UNION
SELECT 'autoloaded data count', count(*) FROM wp_options WHERE autoload='yes'
UNION
(SELECT option_name, length(option_value) FROM wp_options WHERE autoload='yes' ORDER BY length(option_value) DESC LIMIT 10)

Admin:
Add list of ajax hooks
Add list json routes

Goals
	Secure:			No security or DDOS vulnerabilities. Load nothing if the user is not an admin
	Fast:			Zero Impact when not used
	Insight:		Provide transparency into the how Wordpress functions for developers

------------------------------------------------------------------------------	
https://wordpress.org/plugins/disabler/

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


[TO DO]

Catch Fatal Errors, Display Like Simplified Whoops

-----------------------------------------------
On Comment:	
	Deny comments without a referrer
	
-----Inspiration
https://github.com/eworksmedia/php-server-status-dashboard
https://github.com/afaqurk/linux-dash		

[Security]

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
		
	