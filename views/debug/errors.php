<?php 

global $SI_Errors,$SI_Console;

#d($SI_Errors);

?>
<table class=widefat>
	<thead>
		<tr class=hdr>
			<th>Error Number</th>
			<th>File</th>
			<th>Line</th>			
			<th>Error</th>
			<th>Code</th>
			<th>Trace</th>
		</tr>
	</thead>
	<tbody>
		<?php foreach($SI_Errors as $e) : list($errno,$errstr,$file,$line,$context,$trace) = $e; ?>
			<tr>
				<td><?php echo $errno?></td>
				<td><?php echo $file?></td>
				<td><?php echo $line?></td>
				<td><?php echo $errstr?></td>				
				<td>
				</td>
				<td>
					<?php #d($trace); ?>
					<ul>
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
					</ul>
				</td>
			</tr>
		<?php endforeach; ?>
	</tbody>
</table>