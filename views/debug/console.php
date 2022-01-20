<?php if ( !defined('ABSPATH') ){ die('-1'); } ?>
<ul class=log id=log>
    <?php 
        $log = Console::getLog(); 
        $START =  isset($_SERVER['REQUEST_TIME_FLOAT']) ? $_SERVER['REQUEST_TIME_FLOAT'] : $_SERVER['REQUEST_TIME'];
    ?>
    <?php if( empty($log) ) : ?>
        Nothing Logged.
        <p>
            The console is a useful tool for development and debugging.
            You can use the following statements to output data to the console in your theme or plugin:
        </p>
        Console::info($variable_or_text_to_output);<br/>
        Console::success($variable_or_text_to_output);<br/>
        Console::warn($variable_or_text_to_output);<br/>
        Console::error($variable_or_text_to_output);<br/>

        Console::stopwatch('timer name'); (Call once to start, and again to stop and report duration)</br/>
        
        <b>Note</b>: These statements should be removed from production.
        </p>
    <?php else: ?>
        <?php foreach($log as $l) : ?>
            <?php 
                switch($l['type']){
                    case 'success': $icon='cGreen dashicons dashicons-yes';break;
                    case 'warn': $icon='cYellow dashicons dashicons-warning';break;
                    case 'error': $icon='cRed dashicons dashicons-no';break;
                    case 'time': $icon='cYellow dashicons dashicons-clock'; break;
                    default:$icon = 'cBlue dashicons dashicons-info';
                }
            ?>
            <li class="<?=$l['type']?>">            
                <div class=content>                
                    <i class="<?=$icon?>"></i> 
                    <?=$l['name'];?>
                    <span class=value-info><?=htmlspecialchars($l['msg'])?></span>       
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
    <?php endif; ?>
</ul>
