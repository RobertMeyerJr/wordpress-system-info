<h3 class=hndle>Tables</h3>
					<table class='wp-list-table widefat fixed'>
						<thead>
							<tr>
								<th>Name</th>
								<th>Engine</th>
								<th>Rows</th>
								<th>Created</th>
								<th>Collation</th>
								<th>Size</th>
								<th>Fragmentation</th>
								<th></th>
							</tr>
						</thead>
					</thead>
					<tbody>
						<?php 			
						$tables = self::get_tables();
						$i = 0;
						foreach($tables as $t){ ?>
							<tr class="table-<?php echo $t->Name?> <?php echo ($i++%2)?'alternate':''?>">
								<td><?php echo $t->Name?></td>
								<td><?php echo $t->Engine?></td>
								<td><?php echo $t->Rows?></td>
								<td><?php echo $t->Create_time?></td>
								<td><?php echo $t->Collation?></td>
								<td><?php echo self::formatBytes($t->Data_length)?></td>
								<td><?php echo $t->fragmentation?>%</td>
								<td>
									<?php if($t->fragmentation == 0) : ?>
									
									<?php elseif($t->Data_length < 5000000) : ?>
										<a href=# class=button-secondary onClick='optimizeTable("<?php echo $t->Name?>");'>Optimize</a>
									<?php else : ?>
										(Too Large, Must be optimized Manually)
									<?php endif; ?>
						<?php } ?>
					</tbody>
					</table>