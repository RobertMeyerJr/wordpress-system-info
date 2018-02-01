<div>
	Check for "admin" user with id = 1
	Check for prefix != "wp_"
	Disable			XML-RPC	
	ini check:	
	allow_url_include 	
	
http://safebrowsing.clients.google.com/safebrowsing/diagnostic?site={$domain}
http://safeweb.norton.com/report/show?url={$domain}
http://www.siteadvisor.com/sites/{$domain}
http://www.yandex.com/infected?url={$domain}&l10n=en
	
Scan: (if Exec)
	grep -lr --include=*.php "

No Exec:
	glob('*.php',
	
Suspicious
	file_get_contents
	passthru
	eval
	gzinflate
	base64_decode
	base64	
	
	
https://docs.joomla.org/Security_Checklist/Hosting_and_Server_Setup

open_basedir Example:
open_basedir = /home/users/you/public_html:/tmp
	
SELECT * FROM wp_posts WHERE post_content LIKE '%<iframe%'
UNION
SELECT * FROM wp_posts WHERE post_content LIKE '%<noscript%'
UNION
SELECT * FROM wp_posts WHERE post_content LIKE '%display:%'
UNION
SELECT * FROM wp_posts WHERE post_content LIKE '%<?%'
UNION
SELECT * FROM wp_posts WHERE post_content LIKE '%<?php%'



SELECT * FROM wp_comments WHERE comment_content LIKE '%<iframe%'
UNION
SELECT * FROM wp_comments WHERE comment_content LIKE '%<noscript%'
UNION
SELECT * FROM wp_comments WHERE comment_content LIKE '%display:%'
UNION
SELECT * FROM wp_comments WHERE comment_content LIKE '%<?%'
UNION
SELECT * FROM wp_comments WHERE comment_content LIKE '%<?php%'	
	
<div>

