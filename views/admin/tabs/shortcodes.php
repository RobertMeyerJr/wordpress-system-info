<?php if ( !defined('ABSPATH') ){ die('-1'); } ?>
<?php

global $shortcode_tags;

?>

<h2>Total Shortcodes <?php echo count($shortcode_tags)?></h2>

<input type=text name=search value="" placeholder="Search Shortcodes">
<table class="table widefat striped">
<?php foreach($shortcode_tags as $name=>$s) : $type = gettype($s);?>
    <tr>
        <th><?=$name?></th>
        <td><?=$type?>
        <td>
            <?php if($type == 'object') : ?>
                <b>Object</b> <?=get_class($s)?>
            <?php elseif($type == 'array') : ?>
                <b>ARRAY</b>
                <?php foreach($s as $e) :  $et = gettype($e)?>
                    <?php if($et != 'object') : ?>
                        <?=get_class($e); ?><br/>
                    <?php elseif($et != 'string') : ?>
                        <?=$et; ?><br/>
                    <?php else : ?>
                        <?=$s?><br/>
                    <?php endif; ?>
                <?php endforeach; ?>
            <?php else: ?>
                <?=$type?> <?=$s?>
            <?php endif; ?>
        </td>
    </tr>
<?php endforeach; ?>
</table>
