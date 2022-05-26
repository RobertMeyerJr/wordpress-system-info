<?php if ( !defined('ABSPATH') ){ die('-1'); } ?>
<?php global $wp_query; ?>
<h2>WP Query</h2>
<table>
    <?php 
    $fields = [
        'queried_object_id',
        'post_count',
        'found_post',
        'max_num_page',
    ];
    ?>
    <tr><th>queried_object<td><?=get_class($wp_query->queried_object)?>
    <?php foreach($fields as $f) : ?>
        <tr><th><?=$f?><td><?=$wp_query->{$f} ?? ''?>
    <?php endforeach; ?>
</table>

<h2>Conditionals</h2>
<?php $conditions = [
    'is_main_query', #If this is not true, then there is a missing reset on post data
    'is_home',
    'is_singular',
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
    'is_404',
    'is_admin',
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
