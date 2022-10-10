<?php if ( !defined('ABSPATH') ){ die('-1'); } ?>
<?php 
global $SI_Errors;
/*
TODO: Group Repeated Errors
*/
?>
<h2>Errors</h2>
<table class=widefat>
	<thead>
		<tr class=hdr>
			<th width=5%>Type</th>
			<th width=15%>Error</th>
			<th width=75%>Trace</th>
		</tr>
	</thead>
	<tbody>
		<?php foreach($SI_Errors as $e) : list($errno,$errstr,$file,$errLine,$context,$trace) = $e; ?>
			<tr>
				<td>
					<?php
					if( is_numeric($errno) ){
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
					}
					?>
					<td>
						<b><?php echo $errstr?></b>
					</td>
					<td>
					<span class=filename title="<?=esc_attr($file)?>"> <strong><?=$file?></strong></span>
					<div class=code>
					<?php
						if( !empty($file) && file_exists($file)){
							$lines 	= file($file);	
							$start 	= ($errLine-4 > 0) ? $errLine-4 : 0;
							$end 	= $errLine+4;
							echo "<ul class=\"pre code\" start={$errLine}>";
							for($i=$start; $i<=$end; $i++){
								$currentLineNum = $i+1;
								if($start>0 && isset($lines[$i]) ){
									$lineClass = ($currentLineNum == $errLine) ? 'error':'';
									$line = htmlentities($lines[$i]);
									echo "<li class='{$lineClass}'><code><span class='lineNum'>{$currentLineNum}: </span> {$line}</code></li>";
								}
							}
							echo "<ul>";
						}
					?>
					</div>
				</td>			
				<td>
					<ol class=trace>
						<?php if(!empty($trace)) : ?>
							<a class=show_trace href="#">Show Trace</a>
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
						<?php endif; ?>
					</ol>
				</td>
			</tr>
		<?php endforeach; ?>
	</tbody>
</table>

<?php if( !empty(System_Info::$doing_it_wrong) ) : ?>
<h2>Doing it Wrong</h2>
<table>
	<?php foreach(System_Info::$doing_it_wrong as list($function,$message,$ver)) : ?>
		<tr>
			<td><?=esc_html($function)?></td>
			<td><?=esc_html($message)?></td>
			<td><?=esc_html($ver)?></td>
		</tr>
	<?php endforeach; ?>
</table>
<?php endif; ?>
