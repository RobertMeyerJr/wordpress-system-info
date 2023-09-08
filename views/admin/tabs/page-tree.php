<?php if ( !defined('ABSPATH') ){ die('-1'); } ?>
<?php 

$sql = "select id,post_parent,post_name,post_title
from wp_posts
where post_type = 'page'
and post_status = 'publish'
";
