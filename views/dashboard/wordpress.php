<!-- Wordpress -->
<?php global $wpdb; ?>
<tr><th class=hdr colspan=2><h2><i class='dashicons dashicons-wordpress'></i> WordPress</h2></th></tr>
<tr><th>Allow Search Engines</th><td><?=get_option('blog_public',1) == 1 ? '<span class="dashicons dashicons-yes cGreen"></span>':'<span class="dashicons dashicons-no cRed"></span>' ?></td></tr>	
<tr><th>Version</th><td><?php echo $wp_version?></td></tr>			
<tr><th>Multi-Site</th><td><?php echo is_multisite() ? 'Yes':'No'?></td></tr>			
<tr><th>Document Root</th><td><?php echo $_SERVER['DOCUMENT_ROOT']?></td></tr>
<tr><th>Database</th><td><?php echo DB_NAME?></td></tr>
<tr><th>Table Prefix</th><td><?php echo $wpdb->prefix?></td></tr>
<tr><th>WP Cache<td><?=(defined('WP_CACHE') && WP_CACHE == 1) ? '<span class="dashicons dashicons-yes cGreen"></span>':'<span class="dashicons dashicons-no cRed"></span>' ?></td></tr>	
<tr><th>DB Size</th><td><?php echo "{$db_used}, {$db_free} Free" ?></td></tr>
<tr><th>Theme</th><td><?php echo $theme->Name?><br/><?php echo $theme->theme_root?></td></tr>
<tr><th>Admin Email</th><td><?=get_option('admin_email','')?></td></tr>
<tr><th>Blog Name</th><td><?=get_option('blogname')?></td></tr>
<tr><th>Blog Description</th><td>    <?=get_option('blogdescription')?></td></tr>