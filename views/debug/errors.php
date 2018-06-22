<?php 
global $SI_Errors;
/*
TODO: Group Repeated Errors
*/
?>
<table class=widefat>
	<thead>
		<tr class=hdr>
			<th>Error</th>
			<th style='width:40%'>Code</th>
			<th>Error</th>
			<th>Trace</th>
		</tr>
	</thead>
	<tbody>
		<?php foreach($SI_Errors as $e) : list($errno,$errstr,$file,$errLine,$context,$trace) = $e; ?>
			<tr>
				<td>
					<?php
					switch($errno){
						case E_ERROR:			echo "E_ERROR"; break;
						case E_WARNING:			echo "E_USER_ERROR"; break;
						case E_NOTICE:			echo "E_NOTICE";break;
						case E_USER_ERROR: 		echo "E_USER_ERROR"; break;
						case E_USER_NOTICE:		echo "E_USER_NOTICE"; break;
						case E_USER_WARNING: 	echo "E_USER_WARNING"; break;
						case E_USER_NOTICE:		echo "E_USER_NOTICE";break;						
						default: echo $errno;
					}
					?>
				</td>
				<td class=code>
					<div class=filename title="<?=esc_attr($trace[0]['file'])?>">
						<strong><?=$trace[0]['file']?></strong>
					</div>
					<?php 
						$lines 	= file($file);	
						$start 	= ($errLine-4 > 0) ? $errLine-4 : 0;
						$end 	= $errLine+4;
						echo "<ul class=\"pre code\" start={$errLine}>";
						for($i=$start; $i<=$end; $i++){
							$currentLineNum = $i+1;
							if($start>0 && isset($lines[$i]) ){
								$lineClass = ($currentLineNum == $errLine) ? 'error':'';
								$line = htmlentities($lines[$i]);
								echo "<li class='{$lineClass}'><span class='lineNum'>{$currentLineNum}: </span>{$line}</li>";
							}
						}
						echo "<ul>";
					?>
					
				</td>
				<td><?php echo $errstr?></td>				
				<td>
					<ol>
						<?php foreach($trace as $t) : ?>
							<li>
								<?php if(!empty($t['file'])) : ?>
									<?=$t['file']?> 
								<?php endif; ?>
								<?php if(!empty($t['line'])) : ?>
									<?=$t['line']?> 
								<?php endif ?>
								<?php if(!empty($t['function'])) : ?>
									<?=$t['function']?>
								<?php endif; ?>
							</li>
						<?php endforeach; ?>
					</ol>
				</td>
			</tr>
		<?php endforeach; ?>
	</tbody>
</table>