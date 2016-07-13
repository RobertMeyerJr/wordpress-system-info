<div id="total_details_extra" style="display:none;">     
	<div class=total_details_extra>
	<pre>
		<?php if(!empty($uptime)) : ?>
			<?php print_r( $uptime ); ?>
		<?php endif; ?>
	</pre>
	<h2>$_SERVER</h2>
	<table>
		<?php foreach($_SERVER as $k=>$v):?>
			<tr><th><?=$k?></th><td><?=$v?></td></tr>
		<?php endforeach;?>
	</table>
	<?php if( !empty($cpu_info_details) ) : ?>
		<h2>CPU</h2>
		<table>
			<?php foreach($cpu_info_details as $k=>$v):?>
				<tr><th><?=$k?></th><td><?=$v?></td></tr>
			<?php endforeach;?>
		</table>
	<?php endif; ?>
	<?php if( !empty($cpu_details) ) : ?>
		<table>
			<?php foreach($cpu_details as $k=>$v):?>
				<tr><th><?=$k?></th><td><?=$v?></td></tr>
			<?php endforeach;?>
		</table>
	<?php endif; ?>
	<?php if( !empty($mem_info) ) : ?>
		<h2>Memory</h2>
		<table>
			<?php foreach($mem_info as $k=>$v):?>
				<tr><th><?=$k?></th><td><?=$v?></td></tr>
			<?php endforeach;?>
		</table>
	<?php endif; ?>		 
	</div>
	
</div>



