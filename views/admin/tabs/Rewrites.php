<?php if ( !defined('ABSPATH') ){ die('-1'); } ?>
<?php global $wp_rewrite; ?>
<table class="widefat striped">
<thead>
	<tr>
		<th>Regex</th>
		<th>Regular Expression</th>
		<th>Rule</th>
	</tr>
</thead>
<tbody>
	<?php $rewrite_areas = ['extra_rules_top','extra_rules','rules']; ?>
	<?php foreach($rewrite_areas as $area) : ?>
		<?php if(!empty($wp_rewrite->$area)) : ?>
			<?php foreach($wp_rewrite->$area as $regex=>$rule) : ?>
			<tr>
				<th><?=$area?></th>
				<th><?=$regex?></th>
				<td><?=$rule?></td>
			</tr>
			<?php endforeach; ?>
		<?php endif; ?>
	<?php endforeach; ?>
</tbody>
</table>

<h2>Endpoints</h2>
<?php foreach($wp_rewrite->endpoints as $ep) : ?>
<ul>
	<li><?=$ep[2]; ?></li>
</ul>
<?php endforeach; ?>

<?php
$rest = rest_get_server(); 
$routes = $rest->get_routes();
?>
<table class="table widefat striped">
	<thead>
		<tr>
			<th>Route
			<th>Show in Index
			<th>Methods
			<th>Args
			<th>Permission
			<th>
		</tr>
	</thead>
	<tbody>
	<?php foreach($routes as $key => $handler ) : ?>
		<tr>
			<th><?=$key?>
			<td><?=print_r( $handler[0]['show_in_index'],true)?>
			<td><?=print_r( $handler[0]['methods'],true)?>
			<td><?=print_r( $handler[0]['args'],true)?>
			<td><?=print_r( $handler[0]['permission_callback'],true)?>
			<td><?=print_r( array_keys($handler[0]['callback']),true)?>
		</tr>
	<?php endforeach; ?>
	</tbody>
</table>
<!--- 
$wp_rewrite->rewritecode
$wp_rewrite->extra_permastructs
$wp_rewrite->non_wp_rules
-->

