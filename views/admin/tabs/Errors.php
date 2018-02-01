<?php 

$error_log = ini_get('error_log');

if(file_exists($error_log) && !empty($error_log)){
		if( filesize($error_log) > 1000000){
			$error_string = System_Info_Tools::tail($error_log, 1000);
		}
		else{
			$error_string = file_get_contents($error_log);	
		}
		$results = $error_string;
}

function parse_error_log($lines) {
	$out = array();
	if (preg_match_all('/^(?<date>\[[^\]]+\])\s+(?<level>[^:]+):\s+(?<error>.*\s+)on line\s+(?<line>\d+)\s+in\s+(?<fn>[^\s]+)/mu', $lines, $m, PREG_SET_ORDER) > 0) {
		foreach ($m as $match) {
			$out[] = array('date'=>$match['date'], 'level'=>$match['level'], 'error'=>$match['error'].' on line '.$match['line'].' in '.$match['fn'], 'line'=>$match['line']);
		}
	}
	return $out;
}
?>
<div>
	<h2>Error Log Location <?php echo $error_log?></h2>
	<?php if(!empty($error_string)) : ?>
		<code style='white-space:pre;'class=error_log><?php echo htmlentities($error_string)?></code>
		<label>Log Size</label> 
		<?php echo System_Info_Tools::formatBytes(filesize($error_log),2);	?>
	<?php endif; ?>
</div>	