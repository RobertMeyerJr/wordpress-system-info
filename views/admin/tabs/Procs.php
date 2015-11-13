<?php 
	if( System_Info_Tools::is_windows() ){
		$result = System_Info_Tools::run_command("tasklist /v /fo CSV");
		$procs 	= array_map('str_getcsv', $result);
	}
	else{
		$output = System_Info_Tools::run_command('ps aux');
		$procs = [];		
		foreach($output as $ps){
			$ps = preg_split('/\s+/', $ps);
			$procs[] = array(
				'pid'		=> $ps[1],
				'cpu'		=> $ps[2],
				'mem'		=> $ps[3],
				'time'		=> $ps[8],
				'command'	=> $ps[10],
				'args'		=> (!empty($ps[11])) ? $ps[11]:''
			);				
		}
	}		
		
?>
<table class='wp-list-table widefat fixed proc_table datatable'>
	<thead>
		<tr>			
			<?php foreach($keys as $h): ?>
				<th><?php echo $h?></th>
			<?php endforeach; ?>			
		</tr>
	</thead>
	<tbody>
		<?php foreach($procs as $p) : ?>
			<tr>
				<?php foreach($p as $d) : ?>
					<td><?php echo System_Info_Tools::color_format($d)?></td>
				<?php endforeach; ?>
			</tr>
		<?php endforeach; ?>
	<tbody>
</table>