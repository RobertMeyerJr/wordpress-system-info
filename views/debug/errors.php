<?php if ( !defined('ABSPATH') ){ die('-1'); } ?>
<?php 
global $SI_Errors;
/*
TODO: Group Repeated Errors
*/
?>
<table class=widefat>
	<thead>
		<tr class=hdr>
			<th width=50%>Error</th>
			<th width=50%>Trace</th>
		</tr>
	</thead>
	<tbody>
		<?php foreach($SI_Errors as $e) : list($errno,$errstr,$file,$errLine,$context,$trace) = $e; ?>
			<tr>
				<td>
					<?php
					switch($errno){
						case E_ERROR:				echo "E_ERROR"; break;
						case E_WARNING:				echo "E_USER_ERROR"; break;
						case E_NOTICE:				echo "E_NOTICE";break;
						case E_USER_ERROR: 			echo "E_USER_ERROR"; break;
						case E_USER_NOTICE:			echo "E_USER_NOTICE"; break;
						case E_USER_WARNING: 		echo "E_USER_WARNING"; break;
						case E_USER_NOTICE:			echo "E_USER_NOTICE";break;
						case E_DEPRECATED:			echo "E_DEPRECATED";break;
						case E_USER_DEPRECATED:		echo "E_USER_DEPRECATED"; break;
						case E_RECOVERABLE_ERROR:	echo "E_RECOVERABLE_ERROR"; break;
						case E_ALL :				echo "E_ALL"; break;
						case E_STRICT :				echo "E_STRICT"; break;
						default: echo $errno;
					}
					?>
					<b><?php echo $errstr?></b>
					<div class=filename title="<?=esc_attr($file)?>">
						<strong><?=$file?></strong>
					</div>
					<div class=code>
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
					</div>
				</td>			
				<td>
					<ol>
						<?php foreach($trace as $t) : ?>
							<li>
								<?php if(!empty($t['file'])) : ?>
									<span><?=$t['file']?></span>
								<?php endif; ?>
								<?php if(!empty($t['line'])) : ?>
									<span><?=$t['line']?></span>
								<?php endif ?>
								<?php if(!empty($t['function'])) : ?>
									<span style="float:right;"><?=$t['function']?></span>
								<?php endif; ?>
							</li>
						<?php endforeach; ?>
					</ol>
				</td>
			</tr>
		<?php endforeach; ?>
	</tbody>
</table>
