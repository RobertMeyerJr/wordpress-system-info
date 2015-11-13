<?php

if( System_Info_Tools::is_windows() ){
	$cmd = 'sc query';
	$out = System_Info_Tools::run_command($cmd);
	System_Info_Tools::out_table($out);
}
else{
	$cmd = 'service --status-all'; #does this work everywhere?
	$out = System_Info_Tools::run_command($cmd);
	?>
	<table>
		<?php foreach($out as $s) : ?>
			<?php 			
				//todo, split output [ + ] SERVICE
				preg_match('/\[ ([^\s]*) \] (.*)/',$s, $m);
				$status	= $m[1];
				$name 	= $m[2];				
				if($status == '+')
					$status = 'Running';
				else if($status == '-')
					$status = 'Not Running (or Controlled by Upstart)';
				else if($status == '?')
					$status = 'Unknown - Status not Supported';
				else
					$status = 'Disabled';
			?>
			<tr><th><?=$name?><td><?=$status?><td><?=$s?>
		<?php endforeach; ?>
	</table>
	<?php 
}