<?php if ( !defined('ABSPATH') ){ die('-1'); } ?>
<?php
global $wpdb;
$sql = "SELECT u.user_email,u.user_nicename, m.meta_value 
FROM {$wpdb->usermeta} as m 
JOIN {$wpdb->users} as u 
WHERE m.user_id = u.id AND m.meta_key = 'session_tokens'
AND meta_value != ''
LIMIT 200";

$sessions = $wpdb->get_results($sql);

#Todo: Make sure we are an admin before showing anything
?>
<table class='wp-list-table widefat fixed striped'>
    <thead>
        <tr>
            <th>Email</th>
            <th>Username</th>
            <th>IP</th>
            <th>User Agent</th>
            <th>Login DateTime</th>
        </tr>
    </thead>
    <tbody>
    <?php foreach($sessions as $s): 
        $d = unserialize($s->meta_value,['allowed_classes'=>false]);
        $last = !empty($d) ? end($d) : false;
    ?>
        <tr>
            <td><?=esc_html($s->user_email)?></td>
            <td><?=esc_html($s->user_nicename)?></td>
            <td><?php echo $last['ip'] ?? '' ?></td>
            <td><?php echo $last['ua'] ?? '' ?></td>
            <td>
                <?php echo date('n/j/Y g:ia e', $last['login']); ?>
                <?php 
                    if(!empty($last['login'])){
                        echo '<div>'.human_time_diff($last['login']).' ago</div>';
                    }
                ?>
            </td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>
