<?php
if( System_Info_Tools::is_windows() ){			
	$cmd = 'netstat -ano | find "LISTENING"';
	$out = System_Info_Tools::run_command( $cmd );
	$out = preg_replace('!\s+!', ' ', $out);
	$out = str_replace(' ',',',$out);
	$out = System_Info_Tools::read_csv_array( $out,",");
	foreach($out as $k=>$o){
		unset($out[$k][0]);
	}
	System_Info_Tools::out_table($out);
}
else{
	$out = System_Info_Tools::run_command('netstat -tuln | grep LISTEN');
	?>
	<table style='width:60%;'>
	<?php foreach($out as $l) : ?>
		<?php preg_match('/([^\s]*)\s+([^\s]*)\s+([^\s]*)\s+([^\s]*)\s+([^\s]*)\s+([^\s]*)/', $l, $m);	?>		
			<tr>
				<?php for($i=1;$i<=6;$i++) : ?>				
					<td><?=$m[$i]?></td>
				<?php endfor; ?>		
			</tr>		
	<?php endforeach; ?>
	</table>
	<?php 
}