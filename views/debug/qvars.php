<?php if ( !defined('ABSPATH') ){ die('-1'); } ?>
<?php global $wp_query; ?>
<h2>Conditionals</h2>
<?php $conditions = [
    'is_main_query', #If this is not true, then there is a missing reset on post data
    'is_single',
    'is_page',
    'is_archive',
    'is_preview',
    'is_author',
    'is_category',
    'is_tag',
    'is_tax',
    'is_search',
    #'is_feed',
    #'is_comment_feed',
    #'is_trackback',
    #'is_comments_popup',
    'is_home',
    'is_404',
    'is_admin',
    'is_singular',
    'is_posts_page',
    'is_paged',
    'is_date',
    'is_year',
    'is_month',
    'is_time',
    'is_attachment',
];?>
<table>
    <?php foreach($conditions as $func) : ?>
        <tr><th><?=$func?><td><?=$wp_query->{$func}() ? 'Yes' : ''?>
    <?php endforeach; ?>
</table>
<h2>Query Vars</h2>
<?php
System_Info_Tools::dbg_table_out($wp_query->query_vars);
?>
