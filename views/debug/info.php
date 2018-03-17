<?php 
global $template;
$p = get_queried_object();


if(!empty($p->post_type) ){
	$page_meta = get_post_meta($p->ID);
}

?>

<table>
	<tr><th class=hdr colspan=2>Summary 
	<tr><th>URI</th><td><?php echo $_SERVER['REQUEST_URI'] ?></td></tr>
	<?php if(!empty($_SERVER['HTTP_REFERER'])) : ?>
		<tr><th>Referrer</th><td><?php $_SERVER['HTTP_REFERER']?></td></tr>
	<?php endif; ?>		
	<?php if(!empty($p->ID)) : ?><tr><th>ID<td><?=$p->ID?><?php endif; ?>
	<?php if(!empty($p->post_title)) : ?><tr><th>Title<td><?=$p->post_title?><?php endif; ?>
	<?php if(!empty($p->post_date)) : ?><tr><th>Date<td><?=date('m/d/Y h:iA',strtotime($p->post_date))?><?php endif; ?>
	<?php if(!empty($p->post_type)) : ?><tr><th>Post Type<td><?=$p->post_type?><?php endif; ?>
	<?php if(!empty($p->comment_status)) : ?><tr><th>Comment Status<td><?=$p->comment_status?><?php endif; ?>
	<tr><th>Template<td><?=$template?>	
	<?php if(!empty($page_meta )) : ?>
		<tr><th class=hdr colspan=2>Page Meta
		<?php foreach($page_meta as $key=>$value) : ?>
			<tr><th><?=$key?><td>
				<?php 
					if(count($value) == 1){
						echo $value[0];
					}
					else{
						echo implode('<br/>',$value);
					}
		?>
		<?php endforeach; ?>
	<?php endif; ?>

	<tr><th>User ID</th><td><?php echo get_current_user_id(); ?></td></tr>
	<tr><th>ABSPATH</th><td><?php echo ABSPATH; ?></td></tr>
	<tr><th>DB_NAME</th><td><?php echo DB_NAME; ?></td></tr>
	<tr><th>DB_HOST</th><td><?php echo DB_HOST; ?></td></tr>
	<tr><th>Template Directory</th><td><?php echo $tpl_dir ?></td></tr>		
	<tr><th>SERVER_SOFTWARE</th><td><?php echo $_SERVER['SERVER_SOFTWARE'] ?></td></tr>
	<tr><th>REQUEST_URI</th><td><?php echo $_SERVER['REQUEST_URI'] ?></td></tr>		
	
	<?Php if(!empty($_SERVER['COMPUTERNAME'])) : ?><tr><th>Computer Name</th><td><?php echo $_SERVER['COMPUTERNAME'] ?></td></tr><?php endif; ?>
	<?Php if(!empty($_SERVER['NUMBER_OF_PROCESSORS'])) : ?><tr><th>NUMBER_OF_PROCESSORS</th><td><?php echo $_SERVER['NUMBER_OF_PROCESSORS'] ?></td></tr><?php endif; ?>
	<?Php if(!empty($_SERVER['OS'])) : ?><tr><th>OS</th></td><td><?php echo $_SERVER['OS'] ?></tr><?php endif; ?>
	<?Php if(!empty($_SERVER['Path'])) : ?><tr><th>Path</th><td><?php echo str_replace(';','<br/>',$_SERVER['Path']); ?></td></tr><?php endif; ?>
	<?Php if(!empty($_SERVER['PROCESSOR_IDENTIFIER'])) : ?><tr><th>PROCESSOR_IDENTIFIER</th><td><?php echo $_SERVER['PROCESSOR_IDENTIFIER'] ?></td></tr><?php endif; ?>
	
	<tr><th>SERVER_NAME</th><td><?php echo $_SERVER['SERVER_NAME'] ?></td></tr>
	<tr><th>GATEWAY_INTERFACE</th><td><?php echo $_SERVER['GATEWAY_INTERFACE'] ?></td></tr>
	<tr><th>HTTP_USER_AGENT</th><td><?php echo $_SERVER['HTTP_USER_AGENT'] ?></td></tr>		
</table>

<h3>Server Variables</h3>	
<?php	System_Info_Tools::dbg_table_out($_SERVER); 	?>
<?php if(!empty($_COOKIE)) 	: ?><h3>Cookies</h3>		<?php	System_Info_Tools::dbg_table_out($_COOKIE); 	?><?php endif; ?>
<?php if(!empty($_SESSION)) : ?><h3>Session</h3>	<?php	System_Info_Tools::dbg_table_out($_SESSION);	?><?php endif; ?>
<?php if(!empty($_REQUEST)) : ?><h3>Request</h3>	<?php	System_Info_Tools::dbg_table_out($_REQUEST);	?><?php endif; ?>