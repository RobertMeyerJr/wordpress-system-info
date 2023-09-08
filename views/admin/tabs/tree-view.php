<?php 

$pages = get_pages([
    'post_status'=>'publish'
]);

#d($pages);
#Show if Gutenberg
#Show if Divi
#Show if VC Composer
?>
<style>
ul.child-pages{
    
}
.pull-right{float:right}
.tree .js_composer svg g{fill:black;}
.tree .gutenberg{font-weight:900;}
.tree .divi{font-weight:900;}
.tree .template{padding-right:15px;}
.tree li{margin:0;padding:5px;}
.tree li{clear:both;}
.tree li:nth-child(odd){
    background-color:rgba(220,220,220,0.9);
}
</style>
<ul class=tree>
<?php foreach($pages as $p) : ?>
    <?php if($p->post_parent == 0) : ?>
        <?php echo child_tree($p, $pages); ?>
    <?php endif; ?>
<?php endforeach; ?>
</ul>

<?php

function child_tree($page, $pages, $level=0){
    $base_url = get_home_url();
    //Get the page 
    $h = get_page_hierarchy($pages, $page->ID);
    $tpl = get_post_meta($page->ID, '_wp_page_template', true);
    $url = get_the_permalink($page);
    $vc     = strpos($page->post_content, '[vc_') !== false;
    $gb     = strpos($page->post_content, '<!-- wp') !== false;
    $divi   = strpos($page->post_content, '[et_pb') !== false;
    
    //TODO: Mark Homepage, Mark Blog Page, Mark Woocommerce Pages
    ?>
    <li class="level-<?=$level?>">
        <?php echo str_repeat('-',$level);?>
        <span title="<?=esc_attr($page->post_title)?>">
            <a target=_blank href="<?=$url?>"><?=str_replace($base_url,'',$url)?></a>
        </span>
        <span class="pull-right">
            <?php if($vc) : ?><span title="WP Bakery" class="js_composer"><?php include(WP_CONTENT_DIR .'/plugins/js_composer/assets/vc/logo/wpb-logo-white_20.svg');?></span><?php endif; ?>
            <?php if($gb) : ?><span title="Gutenberg" class="gutenberg">G</span><?php endif; ?>
            <?php if($divi) : ?><span title="Divi" class="divi">D</span><?php endif; ?>
        </span>
        <span class="template pull-right text-muted"><?=!empty($tpl) && $tpl != 'default' ? $tpl:''?></span>
        <?php if( !empty($h) ) : ?>
            <?php foreach($h as $child_id => $child_title) : ?>
            <?php $child_page = get_post($child_id); ?>
            <ul class=child-pages>
                <?php echo child_tree($child_page, $pages, $level+1); ?>
            </ul>
            <?php endforeach; ?>
        <?php endif; ?>
    </li>
    <?php
}
?>
