
TODO:
do_action suport for logging

Console::dump()
custom dump function?

[------------------------------------------]
enqueue_block_assets 


VERSION: 1.0.025a

Output theme support for various items.
output wp_lazy_loading_enabled

Dashboard:
check if imagick or gd in use

Sessions: If user count > 100, show only admins and editors

HTML pretty dump
Details::dump($obj, $limit=3)

Admin:
Add list of ajax hooks
Add list json routes

Goals
	Secure:			No security or DDOS vulnerabilities. Load as near to nothing as possible if the user is not an admin or not debugging
	Fast:			Zero Impact when not used
	Insight:		Provide transparency into the how Wordpress functions for developers

-----------------------------------------------
	
[Security]
Check for :		apc,gd,imagick,memcache
		
	