VERSION: 1.0.025a

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
		
	