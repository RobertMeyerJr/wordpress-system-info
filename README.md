VERSION: 1.0.025a

TODO:
TODO: CSS and JS that isn't in enqueue?
TODO:

HTML pretty dump
Details::dump($obj, $limit=3)

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
	Secure:			No security or DDOS vulnerabilities. Load as near to nothing as possible if the user is not an admin or not debugging
	Fast:			Zero Impact when not used
	Insight:		Provide transparency into the how Wordpress functions for developers

------------------------------------------------------------------------------	

[TO DO]

Catch Fatal Errors, Display Like Simplified Whoops

-----------------------------------------------
	
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
		
	