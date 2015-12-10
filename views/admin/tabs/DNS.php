<?php 
$host 	= System_Info_Tools::get_domain( 'http://'.$_SERVER['HTTP_HOST'] );
$dns 	= dns_get_record( $host );
$c 		= count($dns);
		
?>
<h3>DNS Records for <?php echo $host?></h3>
<table class='wp-list-table widefat fixed striped'>
	<thead><tr><th>Host<th>Class<th>Type<th><th>TTL</tr></thead>
<?php foreach($dns as $d) : ?>
	<tr>
		<td class=cGreen><?php echo $d['host']?></td>
		<td class=cBlue><?php echo $d['class']?></td>
		<td class=cPurple><?php echo $d['type']?></td>
		<td class=cRed><?php 
			if($d['type'] == 'A')
				echo $d['ip'];
			elseif($d['type'] == 'MC')
				echo $d['pri'];
			elseif($d['type'] == 'TXT')
				echo htmlentities($d['txt']);
		?></td>
		<td class=cOrange><?php echo $d['ttl']?></td>
<?php endforeach; ?>
</table>