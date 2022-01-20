<?php if ( !defined('ABSPATH') ){ die('-1'); } ?>
<?php $host = $_SERVER['HTTP_HOST']; ?>
<a target=_blank href=http://sitecheck.sucuri.net/results/<?=$host?>/>Sucuri Sitecheck</a><br/>
<a target=_blank href=http://safebrowsing.clients.google.com/safebrowsing/diagnostic?site=<?=$host?>>GoogleSafe Browsing</a>
<a target=_blank href=http://safeweb.norton.com/report/show?url=<?=$host?>>Norton SafeWeb</a>
<a target=_blank href=http://www.siteadvisor.com/sites/<?=$host?>>Site Advisor</a>

<a target=_blank href=#>Alexa</a><br/>
<a target=_blank href=#>Google</a><br/>					
<a target=_blank href=/robots.txt>Robots.txt</a>


<button type=button class='btn btn-primary'>Clear Deleted Posts</button>
<button type=button class='btn btn-primary'>Clear Post Revisions</button>
<button type=button class='btn btn-primary'>Spam Comments</button>

<!-- Iframe for this? -->
<form>	
	This can't just work like this.
	It might be serialized data in certain areas like meta	
	<h2>Content Replace</h2>
	<input type=hidden name=action value=sysinfo_replace_content>
	<label>Replace<label><input type=text name=replace><br/>
	<label>With<label><input type=text name=with><br/>
	<button type=button id=replace onClick="replace_content();">Replace</button>
</form>
<script>
function replace_content(){
	$.ajax({
		url: ajaxurl,
		data: jQuery().serialize(),
		success: function(){
			alert('Replaced!');
		}
	});
}
</script>
