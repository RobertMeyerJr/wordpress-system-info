get registered blocks

output them.
Screenshot if we have.
Indicate if Dynamic

Also show theme.json settings?
<?php

$blocks = WP_Block_Type_Registry::get_instance()->get_all_registered();

#d($blocks);
?>
<table class="tabe widefat">
<?php foreach($blocks as $b) : ?>
    <tr>
        <th><?=$b->name?></th>
        <td><?=$b->title?></td>
        <td><?=$b->category?></td>
        <td><?=$b->description?></td>
        <td><?=is_scalar($b->render_callback) ? $b->render_callback: gettype($b->render_callback);?>
        <td><?=$b->provides_context?>
        <td><?php echo (!empty($b->uses_context)) ? print_r($b->uses_context) : '' ?>
        <td><?=$b->example?>
    </tr>
<?php endforeach; ?>
</table>