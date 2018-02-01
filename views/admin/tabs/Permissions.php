<?php 

function get_permissions(){
		$uploads = wp_upload_dir();
		$dirs = array(
			ABSPATH						=> '0755',
			ABSPATH.WPINC				=> '0755',
			ABSPATH.'wp-admin'			=> '0755',
			WP_CONTENT_DIR				=> '0755',
			WP_PLUGIN_DIR				=> '0755',
			ABSPATH . '.htaccess'		=> '0644',
			ABSPATH . 'wp-config.php'	=> '0644',
			$uploads['basedir']			=> '0660',
		);
		$perms = array();
		foreach($dirs as $d=>$correct){
			if( file_exists($d) ){
				$perms[] = array( 'path'=>$d, 'permissions'=> fileperms($d),'correct'=>$correct);		
			}			
		}
		return $perms;
	}
	
	function perm_type($perms){
		if (($perms & 0xC000) == 0xC000) 		$type = 'socket'; // Socket
		elseif (($perms & 0xA000) == 0xA000) 	$type = 'symlink'; // Symbolic Link
		elseif (($perms & 0x8000) == 0x8000)	$type = 'regular file'; // Regular
		elseif (($perms & 0x6000) == 0x6000)	$type = 'block';// Block special
		elseif (($perms & 0x4000) == 0x4000)	$type = 'directory';// Directory
		elseif (($perms & 0x2000) == 0x2000) 	$type = 'character';// Character special
		elseif (($perms & 0x1000) == 0x1000) 	$type = 'pipe';// FIFO pipe
		else 									$type = 'unknown';// Unknown
		return $type;
	}
	function perm_decode($perms){
		#User
		$u = (($perms & 0x0100) ? 'r' : '-');
		$u .= (($perms & 0x0080) ? 'w' : '-');
		$u .= (($perms & 0x0040) ? (($perms & 0x0800) ? 's' : 'x' ) : (($perms & 0x0800) ? 'S' : '-'));
		#Group
		$g = (($perms & 0x0020) ? 'r' : '-');
		$g .= (($perms & 0x0010) ? 'w' : '-');
		$g .= (($perms & 0x0008) ? (($perms & 0x0400) ? 's' : 'x' ) : (($perms & 0x0400) ? 'S' : '-'));
		#World
		$w = (($perms & 0x0004) ? 'r' : '-');
		$w .= (($perms & 0x0002) ? 'w' : '-');
		$w .= (($perms & 0x0001) ? (($perms & 0x0200) ? 't' : 'x' ) : (($perms & 0x0200) ? 'T' : '-'));
		return [$u,$g,$w];
	}
	
?>
<table class='wp-list-table widefat fixed striped'>
	<thead>
		<tr>
			<th>Name</th>
			<th>Type</th>
			<th>Permissions</th>
			<th>Suggested</th>
			<th>User</th>
			<th>Group</th>
			<th>World</th>
		</tr>
	</thead>
	<tbody>
		<?php foreach(get_permissions() as $p) : ?>
			<?php 
				$perms = $p['permissions'];
				$perm_info = perm_decode($perms);						
			?>
			<tr>
				<th class=cBlue><?php echo $p['path']?></th>
				<td class=cPurple><?=perm_type($perms)?></td>
				<th class=cOrange><?php echo substr(sprintf('%o', $p['permissions']), -4);?></th>
				<th><?=$p['correct']?></th>
				<td class=cRed><?php echo $perm_info[0]?></td>
				<td class=cRed><?php echo $perm_info[1]?></td>
				<td class=cRed><?php echo $perm_info[2]?></td>
			</tr>
		<?php endforeach; ?>
	</tbody>
</table>
<?php 
	$user 	= (System_Info_Tools::is_windows()) ? get_current_user() : System_Info_Tools::run_command('whoami');
?>
