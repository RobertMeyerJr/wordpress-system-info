<?php if ( !defined('ABSPATH') ){ die('-1'); } ?>
<h2>Blocks</h2>

<?php #d(System_Info::$blocks); ?>
<table>
    <thead>
        <tr>
            <th>Block</th>
            <th>Attributes</th>
        </tr>
    </thead>
    <tbody>
    <?php foreach(System_Info::$blocks as $k=>list($name,$atts)) : ?>
        <tr>
            <th><?=$name ?? '(Empty Block)'?></th>
            <td>
                <?php if(!empty($atts)) : ?>
                    <?=System_Info_Tools::dbg_table_out($atts);?>
                <?php endif; ?>
            </td>
        </tr>
    <?php endforeach; ?>
</table>
