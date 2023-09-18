<h1>Theme &amp; Gutenberg Features</h1>
<?php 
//filter_theme_json_theme
global $_wp_theme_features;

$pattern_registry = WP_Block_Pattern_Categories_Registry::get_instance();
$cats = $pattern_registry->get_all_registered();
?>
<table class="table widefat striped">
    <?php foreach($_wp_theme_features as $feat=>$v) : ?>
        <tr>
            <th><?=$feat?>
            <td><?=is_array($v) ? print_r($v):$v;?>
        </tr>
    <?php endforeach; ?>
</table>

<h2>Pattern Categories</h2>
<table class="table widefat striped">
<?php foreach($cats as $c) : ?>
    <tr>
        <td><?=$c['name']?>
        <td><?=$c['label']?>
        <td><?=$c['description'] ?? ''?>
    </tr>
<?php endforeach; ?>
</table>


