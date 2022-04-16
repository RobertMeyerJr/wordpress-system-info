<?php

$vars = get_defined_vars();
global $plugin_memory_load;

?>
<h2>Plugin Load</h2>
<table>
    <?php foreach($plugin_memory_load as $name=>list($mem,$dur)) : ?>
        <tr>
            <th><?=str_replace(WP_PLUGIN_DIR.'/','', $name)?></th>
            <td><?=size_format($mem,4)?></td>
            <td><?=number_format($dur,4)?></td>
        </tr>
    <?php endforeach; ?>
</table>

<h2>Memory Usage</h2>
<table>
    <?php foreach($vars as $name=>$data) : ?>
        <tr>
            <th><?=$name?></th>
            <td><?=size_format(System_Info::sizeofvar($data))?></td>
        </tr>
    <?php endforeach; ?>
</table>
