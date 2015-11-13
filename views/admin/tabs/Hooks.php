<?php 

global $wp_filter,$wp_actions,$merged_filters;		
$hook = $wp_filter;
ksort($hook);
#d($hook);

?>
<table class='wp-list-table widefat fixed'>
	<thead>
		<tr>
			<th width="45%">Action</th>
			<th width="10%">Priority</th>
			<th width="45%">Tasks</th>
		</tr>
	</thead>
	<tbody id=hooks>
		<?php foreach($hook as $tag => $p) : ?>
		<?php if(isset($_POST['search']) && stripos($tag,$_POST['search'])===false) continue; ?>
		<?php $count = count($p); ?>
			<?php foreach($p as $name => $props): ?>
				<tr>
					<td class=cBlue><a target=_blank href="http://adambrown.info/p/wp_hooks/hook/<?php echo $tag?>"><?php echo $tag?></td>	
					<td class=cGreen><?php echo $name?></td>
					<td><?php foreach($props as $val) :?>
						<div class=actions>
							<h2><?php var_dump( $val['function']) ?></h2>
							<pre><?php echo htmlspecialchars(print_r($val,true)); ?></pre>
						</div>
						<?php endforeach; ?>
					</td>
				</tr>
			<?php endforeach; ?>
		<?php endforeach; ?>			
	</tbody>
</table>