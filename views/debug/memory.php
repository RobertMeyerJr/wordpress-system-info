<?php if ( !defined('ABSPATH') ){ die('-1'); } ?>
<?php

$vars = get_defined_vars();
global $plugin_memory_load;

$last_mem = SI_START_MEM;

$peak_mem = memory_get_peak_usage();
?>
<h2>Peak Usage - <?=size_format($peak_mem,2)?></h2>
<h2>Plugin Load</h2>
<table>
    <thead>
        <tr>
            <th>Plugin</th>
            <th>Memory Usage</th>
            <th>Plugin Load Memory</th>
            <th>Load Time</th>
        </tr>
    </thead>
    <tbody>
    <?php foreach($plugin_memory_load as $name=>list($mem,$dur)) : ?>
        <tr>
            <th><?=str_replace(WP_PLUGIN_DIR.'/','', $name)?></th>
            <td><?=size_format($mem,2)?></td>
            <td><?=size_format($mem-$last_mem,2)?></td>
            <td><?=number_format($dur-SI_START_TIME,4)?></td>
        </tr>
        <?php $last_mem = $mem; ?>
    <?php endforeach; ?>
    </tbody>
</table>


<?php if(false) : ?>
<h2>Memory Usage</h2>
<table>
    <?php foreach($vars as $name=>$data) : ?>
        <tr>
            <th><?=$name?></th>
            <td><?=size_format(System_Info::sizeofvar($data),2)?></td>
        </tr>
    <?php endforeach; ?>
</table>
<?php endif; ?>
