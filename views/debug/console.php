<ul class=log id=log>
    <?php 
        $log = Console::getLog(); 
        $START =  isset($_SERVER['REQUEST_TIME_FLOAT']) ? $_SERVER['REQUEST_TIME_FLOAT'] : $_SERVER['REQUEST_TIME'];
    ?>
    <?php foreach($log as $l) : ?>
        <?php 
            switch($l['type']){
                case 'success': $icon='cGreen dashicons dashicons-yes';break;
                case 'warn': $icon='cYellow dashicons dashicons-warning';break;
                case 'error': $icon='cRed dashicons dashicons-no';break;
                default:$icon = 'cBlue dashicons dashicons-info';
            }
        ?>
        <li class="<?=$l['type']?>">            
            <div class=content>                
                <i class="<?=$icon?>"></i> 
                <?=$l['name'];?>
                <?php if( !is_array($l['msg']) && !is_object($l['msg']) ) : ?>
                    <?=htmlspecialchars($l['msg'])?>
                <?php else : ?>
                    <span class=value-info><?=htmlentities(print_r($l['msg'],true))?></span>
                <?php endif; ?>         
            </div>
            <span class="label"><?=$l['type']?></span>
            <!-- TODO: Show as time into request -->
            <span class='date cPurple'><?=number_format((($l['date']-$START)*1000), 2)?>ms</span>            
            <small class=where>
                File <?=$l['trace']['file']?>
                Line <?=$l['trace']['line']?>                           
            </small>
        </li>
    <?php endforeach ?>
</ul>
<script>
jQuery(function($){
    $('.value-info').click(function(){ $(this).toggleClass('expanded') });
});
</script>