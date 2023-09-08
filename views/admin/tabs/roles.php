<?php if ( !defined('ABSPATH') ){ die('-1'); } ?>
<dl>
  <?php foreach (get_editable_roles() as $role_name => $role_info): ?>
    <dt><?php echo $role_name ?></dt>
    <dd>
      <ul>
        <?php foreach ($role_info['capabilities'] as $capability => $_): ?>
          <li><?php echo $capability ?></li>
        <?php endforeach; ?>
      </ul>
    </dd>
  <?php endforeach; ?>
</dl>
