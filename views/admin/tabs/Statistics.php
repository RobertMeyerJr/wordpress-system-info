<?php if ( !defined('ABSPATH') ){ die('-1'); } ?>
<?php

global $wpdb;

$sql = "SELECT count(1) as total, post_type FROM {$wpdb->posts} GROUP BY post_type ORDER BY total DESC";

$posts_by_type = $wpdb->get_results($sql);

$sql = "SELECT count(1) as total, comment_approved, comment_type FROM {$wpdb->comments} GROUP BY comment_type, comment_approved";
$comment_stats = $wpdb->get_results($sql);

?>
<div>
<h2>Posts by Type</h2>
<table>
<?php foreach($posts_by_type as $p) : ?>
    <tr><th><?=$p->post_type?><td><?=$p->total?>
<?php endforeach; ?>
</table>
</div>
