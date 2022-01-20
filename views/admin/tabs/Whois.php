<?php if ( !defined('ABSPATH') ){ die('-1'); } ?>
<?php 
$host = System_Info_Tools::get_domain( 'http://'.$_SERVER['HTTP_HOST'] );
echo "<pre class=cGreen style='font-size:1.2em'>".System_Info_Tools::whois( $host )."</pre>";
?>
