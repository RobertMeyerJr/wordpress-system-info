<?php if ( !defined('ABSPATH') ){ die('-1'); } ?>
<?php
global $wp_rewrite;

?>

<table class=widefat>
<thead>
	<tr>
		<th>Regex</th>
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
/*
$rest = rest_get_server(); 
$routes = $rest->get_routes();
print_r($routes);
?>
<table>
	<?php foreach($routes as $r) : ?>
		<tr>
			
		</tr>
	<?php endforeach; ?>
</table>
<!--- 
$wp_rewrite->rewritecode
$wp_rewrite->extra_permastructs
$wp_rewrite->non_wp_rules
-->
