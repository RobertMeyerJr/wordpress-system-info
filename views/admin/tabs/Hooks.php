<?php 

global $wp_filter,$wp_actions,$merged_filters;		
$hook = $wp_filter;
ksort($hook);
#d($hook);

$search = !empty($_GET['search']) ? $_GET['search']:'';

function where_defined($function_name){
	try{
	$rf = new ReflectionFunction($function_name);
	return [
		'file'	=> $rf->getFileName(),
		'line'	=> $rf->getStartLine()
	];
	}catch(Exception $e){
		return ['file'=>'unknown','line'=>'unknown'];
	}
}

?>
<h2>Common Hooks</h2>
<a href=>the_content</a>
<a href=>wp_head</a>
<a href=>wp_footer</a>

<form>
	<input type=hidden name=page value="wptd-Hooks">
	<input type=hidden name=get_hooks value=1>
	<input type=text name=search value="<?=$search?>">
<button>Search</button>
</form>


<?php if(!empty($_GET['get_hooks'])) : ?>
<table class='wp-list-table widefat fixed striped'>
	<thead>
		<tr>
			<th width="15%">Action</th>
			<th width="10%">Priority</th>
			<th width="75%">Tasks</th>
		</tr>
	</thead>
	<tbody id=hooks>	
		<?php foreach($hook as $tag => $p) : ?>
			<?php 
				if(!empty($search) && stripos($tag, $search)===false){
					continue;
				}
			?>
			<?php foreach($p as $name => $props): ?>				
				<tr>
					<td class=cBlue><a target=_blank href="http://adambrown.info/p/wp_hooks/hook/<?php echo $tag?>"><?php echo $tag?></td>	
					<td class=cGreen><?php echo $name?></td>
					<td>
						<table>
							<thead>
								<tr>
									<th>Function
									<th>File
									<th>Line
									<th>Arguments
							</thead>
						<?php foreach($props as $val) :?>
							<tr>
							
							<?php if(is_string($val['function'])) : ?>
								<td><h2><?=$val['function']?></h2>
								<td><?php $where_def = where_defined($val['function']) ; ?>
								<td><?=$where_def['file']?>
								<td><?=$where_def['line']?>
								<td><?=$val['accepted_args']?>
							<?php else: ?>
								<td>ARRAY</td>
								<td colspan=4><?php print_r($val);?></td>
							<?php endif; ?>
						
						<?php endforeach; ?>
						</table>
					</td>
				</tr>
			<?php endforeach; ?>
		<?php endforeach; ?>		
	</tbody>
</table>
<?php endif; ?>