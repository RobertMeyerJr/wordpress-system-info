<?php 

if( is_multisite() ){ //TODO: Make sure super admin
    $sites = get_sites([
        'public' => 1,
        'archived'=> 0,
        'spam'  => 0,
        'deleted'=>0
    ]);
    $current_blog_id = get_current_blog_id();
}

$blog_id = $_GET['blog_id'] ?? $current_blog_id;

#Show if home or blog page
#Show if Woocommerce Page?
?>
<style>
ul.child-pages{
    background:transparent;
}
.pull-right{float:right}
.tree .js_composer svg g{fill:black;}

.tree .page-builder .gutenberg{font-weight:900;}
.tree .page-builder .divi{font-weight:900;}

.tree .template{padding-right:15px;}
.tree li{margin:0;padding:5px;}
.tree > li:nth-child(odd){
    background-color:rgba(220,220,220,0.3);
}
.dates{padding-right:40px;}

#page_tree .info{
    padding:5px;
    width:auto;
    max-width:40vw;
    background:white;
    border:1px solid #dedede;
    display:none;
    position:absolute;
    top:0;
    left:200px;
    z-index:10;
    margin-left:30px;
}

#page_tree li ul{display:none;}
#page_tree li.active > ul{display:list-item;}
#page_tree .expand{cursor:pointer;}
#page_tree li:hover:not(:has(li:hover)) > .info{display:inline-block;}
#page_tree li{
    position:relative;
}
#page_tree .page-builder{
    min-width:25px;
    text-align:center;
}
#page_tree a{text-decoration:none;}

#page_tree .optional{display:none;}
#page_tree.show_created .created{display:inline-block;}
#page_tree.show_modified .modified{display:inline-block;}
#page_tree.show_template .template{display:inline-block;}
#page_tree.show_author .author{display:inline-block;}
</style>
<?php if(!empty($sites)) : ?>
<form action="<?php echo admin_url('admin.php')?>">
    <input type=hidden name=page value="wptd-tree-view">
        <select name=blog_id>
            <?php foreach($sites as $s) : ?>
                <option <?php selected($s->blog_id == $blog_id) ?> value="<?php echo esc_attr($s->blog_id)?>"><?php echo $s->domain?></option>
            <?php endforeach; ?>
        </select>
    <button type=submit>View Site</button>
</form>
<?php endif; ?>
<br/>
<div id=options>
    <div style="float:right">
        <label>Created<input type=checkbox id=show_created value=show_created></label>
        <label>Modified<input type=checkbox id=show_modified value=show_modified></label>
        <label>Author<input type=checkbox id=show_author value=show_author></label>
        <label>Template<input type=checkbox id=show_tpl value=show_template></label>
    </div>
    <input type=text placeholder="Search" id=search_value>        
    <select id=page_builder>
        <option value="">Any</option>
        <option value="js_composer">WP Bakery</option>
        <option value="divi">Divi</option>
        <option value="gutenberg">Gutenberg</option>
    </select>
    <button id=tree_search class="button-primary">Search</button>
</div>
<?php
if($blog_id != $current_blog_id && !empty($sites)){
    switch_to_blog($blog_id);
}
$pages = get_posts([
    'post_type'     => 'page',
    'post_status'   => 'publish',
    'posts_per_page'=> 200 #Max to 200 pages
]);
#d($pages);
?>
<ul class=tree id=page_tree>
    <?php foreach($pages as $p) : ?>
        <?php if($p->post_parent == 0) : ?>
            <?php echo child_tree($p, $pages); ?>
        <?php endif; ?>
    <?php endforeach; ?>
</ul>

<?php switch_to_blog($current_blog_id); #Switch Back?>
<script>
jQuery(function($){

    $('#options input[type=checkbox]').click(function(){
        $('#page_tree').toggleClass($(this).val());
    });

    $('#tree_search').click(search);
    $('#page_tree li .expand').on('click',function(){
        console.log('Clicked Expand');
        //console.log( $(this).parent() );
        $(this).parent().toggleClass('active');
        $(this).toggleClass('dashicons-insert').toggleClass('dashicons-remove');
    });
    function search(){
        var search = $('#search_value').val();
        $('#page_tree li').show();
        
        if(search && search.length){
            $('#page_tree li:not(:contains("'+search+'"))').hide();
        }
        
        var pb = $('#page_builder').val();
        if(pb){
            $('#page_tree li:not(:has(.'+pb+'))').hide();
        }
    }
});  
</script>
<?php
function child_tree($page, $pages, $level=0){
    global $post;
    $post = $page;
    $base_url = get_home_url();
    //Get the page 
    $h      = get_page_hierarchy($pages, $page->ID);
    $tpl    = get_post_meta($page->ID, '_wp_page_template', true);
    $url    = get_the_permalink($page);
    $vc     = strpos($page->post_content, '[vc_') !== false;
    $gb     = strpos($page->post_content, '<!-- wp') !== false;
    $divi   = strpos($page->post_content, '[et_pb') !== false;
    //console::log($page);
    $liClasses = [];
    if($vc){ $liClasses[] = 'js_composer'; } 
    if($gb){ $liClasses[] = 'gutenberg'; }
    if($divi){ $liClasses[] = 'divi'; }
    //TODO: Mark Homepage, Mark Blog Page, Mark Woocommerce Pages
    ?>
    <li class="level-<?=$level?> <?=implode($liClasses)?>">
        <?php if( !empty($h) ) : ?>
            <span class="expand dashicons dashicons-insert"></span> <!-- dashicons-remove  -->
        <?php else: ?>
            <span class="dashicons"></span>
        <?php endif; ?>

        <?php echo str_repeat('-',$level);?>
        
        <span title="<?=esc_attr($page->post_title)?>">
            <a target=_blank href="<?=$url?>">
                <?=str_replace($base_url,'',$url)?>
            </a>    
        </span>
        
        <span class="page-builder pull-right">
            <?php if($vc) : ?><span title="WP Bakery" class="js_composer"><?php include(WP_CONTENT_DIR .'/plugins/js_composer/assets/vc/logo/wpb-logo-white_20.svg');?></span><?php endif; ?>
            <?php if($gb) : ?><span title="Gutenberg" class="gutenberg">G</span><?php endif; ?>
            <?php if($divi) : ?><span title="Divi" class="divi">D</span><?php endif; ?>
        </span>
        <span class="optional cGreen author">
            <?=get_user_by('id',$post->post_author)->display_name ?? '';?>
        </span>
        <span title="<?=esc_attr($tpl)?>" class="optional template pull-right text-muted">
            <?=!empty($tpl) && $tpl != 'default' ? str_replace(['templates/','.php'],'',$tpl):''?>
        </span>
        <span class="dates cGreen pull-right">
                <?php 
                $created    = date('n/j/Y g:ia',  strtotime($post->post_date));
                $modified   = date('n/j/Y g:ia', strtotime($post->post_modified));
                $title = "Created {$created} Modified {$modified}";
                ?>
                <span title="<?=esc_attr($title)?>" class="text-green created optional">
                    <?php echo date('n/j/Y',  strtotime($post->post_date))?>
                </span>
                <span title="<?=esc_attr($title)?>" class="text-yellow modified optional">
                    <?php echo date('n/j/Y',  strtotime($post->post_modified))?>
                </span>
        </span>
    
    <?php if( !empty($h) ) : ?>
        <ul>
        <?php foreach($h as $child_id => $child_title) : ?>
        <?php $child_page = get_post($child_id); ?>
        <?php echo child_tree($child_page, $pages, $level+1); ?>
        <?php endforeach; ?>
        </ul>
    <?php endif; ?>

    
    <div class=info>
            <h3><?=$page->post_title?></h3>
            <div>
            <a target=_blank href="<?=$url?>">
                <?=str_replace($base_url,'',$url)?>
            </a>
            </div>
            <div class=cGreen>
                Created <?=$post->post_date;?>
                -  
                <?=human_time_diff( time(), strtotime($post->post_date) )?> ago
            </div>
            <div class=cGreen>
                Modified <?=$post->post_modified;?>
                -  
                <?=human_time_diff( time(), strtotime($post->post_modified) )?> ago
            </div>
            <?php if(  !empty($tpl) ) : ?>
            Template: <?= $tpl != 'default' ? str_replace(['templates/','.php'],'',$tpl):''?>
            <?php endif; ?>
            <div class=actions>
                <a href="<?php echo get_edit_post_link()?>">Edit</a>
                <a  onclick="return confirm('Move this page to the trash? You can restore it later.');"
                    style="color:red" href="<?php echo get_delete_post_link()?>">Delete</a>
            </div>
        </div>

    </li>
    <?php
    
}
