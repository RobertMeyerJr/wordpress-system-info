wordpress-system-info
=====================

Determine Critial info for info panel
	Errors + Warnings
	Load time overview
	
If 5.5, Zend Optimizer+
APC no user space cache
	Install APCu	(Works with Zend Optimizer+)		(Server Specific)
	pecl:			memcached better than memcache
---------------------------------------
First 14KB are critical
---------------------------------------

Debug Bar
System Information

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
Beacon Every 10 Minutes


Long Review
	check for admin user
	user with id = 1
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

apt-get install php5-dev	
sudo pecl install memcache
	
	
PHP Check For
	cURL
	zlib
	Opcode cache
	Memcache extension
	HTML Tidy extension
	Check for PECL memcache module
Apache Check For:
	mod_deflate
	mod_env
	mod_expires
	mod_headers
	mod_mime
	mod_rewrite
	mod_setenvif
	
	

php.ini	
	extension=memcache.so
	
	
/etc/apache2/apache2.conf
<Directory /var/www/>
    Options Indexes FollowSymLinks MultiViews
    AllowOverride All
    Order allow,deny
    allow from all
</Directory>
	
	
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
	
	
	
	
	
	
	
	
	
	