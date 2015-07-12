wordpress-system-info
=====================


[TO DO]
Catch Fatal Errors, Display Like Simplified Whoops

for errors, open the file, show 5 before, 5 after hilight error line
??	show callstack 2 before

Issue with Services on Linux

---------------------------------------------------------------------



Name Ideas:
WP Total Info


Determine Critial info for info panel
	Errors + Warnings
	Load time overview
	

Install APCu	(Works with Zend Optimizer+)		(Server Specific)
pecl:			memcached 

Speed:	First 14KB are critical

---------------------------------------
Goals
	Secure:			No security or DDOS vulnerabilities. Load nothing if the user is not an admin
	Fast:			Zero Impact when not used
	Insight:		Provide transparency into the how Wordpress functions for developers
wp_ajax_si_beacon
	Posts
	Comments	
	Active Plugins
	CPU
	Memory
	Disk
-----------------------------------------------
Beacon Every 1 Hour
	Details
Beacon Every X Minutes
Long Review
	check for "admin" user with id = 1
	check for prefix != "wp_"
	disable			XML-RPC
On Comment:	
	deny comments without a referrer
Beacon:
	Plugin Updates
	Wordpress Updates	
-----Inspiration

https://github.com/eworksmedia/php-server-status-dashboard
https://github.com/afaqurk/linux-dash		

[Security]

http://safebrowsing.clients.google.com/safebrowsing/diagnostic?site=theajcf.org
http://safeweb.norton.com/report/show?url=theajcf.org
http://www.siteadvisor.com/sites/theajcf.org
http://www.yandex.com/infected?url=theajcf.org&l10n=en


SELECT * FROM your-table-name WHERE your-table-field-or-column LIKE '%<iframe%'


ini check:	
	allow_url_fopen 
	allow_url_include 
	
	
grep -lr --include=*.php "eval(base64_decode" /path/to/webroot

find [path] -name .htaccess -type f | wc -l

file_get_contents
eval
gzinflate
base64_decode
base64


https://wordpress.org/plugins/debug-bar-constants/screenshots/
https://wordpress.org/plugins/debug-bar-shortcodes/



Check for 
	apc
	gd
	imagick
	memcache

	
	
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
	
	
	
	
	
	
	
	
	
	