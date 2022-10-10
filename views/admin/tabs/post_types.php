<?php if ( !defined('ABSPATH') ){ die('-1'); } ?>
<?php 
global $wp_post_types,$wpdb;

$results = $wpdb->get_results("SELECT post_type, count(1) as total FROM {$wpdb->posts} GROUP BY post_type");

$counts = [];
foreach($results as $r){
    $counts[$r->post_type] = $r->total;
}
?>
<style>
table{position:relative;}
table thead {
    position: sticky;
    top: 32px;
    background: white;
}    
</style>
<h3>Registered Post Types (<?=count($wp_post_types)?>)</h3>
<table class='wp-list-table widefat fixed striped'>
    <thead>
        <tr>
            <th width="30px;"></th>
            <th>label</th>
            <th>name</th>
            <th>description</th>
            <th>public</th>
            <th>hierarchical</th>
            <th>exclude_from_search</th>
            <th>publicly_queryable</th>
            <th>has_archive</th>
            <th>rewrite</th>
            <th>show_in_rest</th>
            <th>Count</th>
        </tr>
    </thead>
    <tbody>
    <?php foreach($wp_post_types as $t) : ?>
        <tr>
            <td>
                <?php if( !empty($t->menu_icon) ) : ?>
                    <span title="<?=$t->menu_icon?>" class="dashicons <?=$t->menu_icon?>"></span>
                <?php endif; ?>
            </td>
            <th>
                <?=$t->label?>
            </th>
            <th><?=$t->name?></th>
            <td><?=$t->description?></td>
            <td><?=$t->public?></td>
            <td><?=$t->hierarchical?></td>
            <td><?=$t->exclude_from_search?></td>
            <td><?=$t->publicly_queryable?></td>
            <td><?=$t->has_archive?></td>
            <td><pre><?=print_r($t->rewrite,true)?></pre></td>
            <td><?=$t->show_in_rest?></td>
            <td>
                <?php echo  $counts[$t->name] ?? 0 ?>
            </td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>
