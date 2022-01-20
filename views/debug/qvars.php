<?php if ( !defined('ABSPATH') ){ die('-1'); } ?>
<h2>Query Vars</h2>
<?php
System_Info_Tools::dbg_table_out($wp_query->query_vars);
?>
