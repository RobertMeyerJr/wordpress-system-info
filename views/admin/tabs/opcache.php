<?php 

if( !empty($_POST['reset_op_cache']) ){
	if( function_exists('opcache_reset') ){
		if( wp_verify_nonce( $_POST['_wpnonce'], 'reset_op_cache') ){
			opcache_reset();
		}
		else{
			echo "<h1>Error: Invalid Nonce</h1>";
		}
	}
}

$config = opcache_get_configuration();

$status = opcache_get_status();

$cfg = $config['directives'];

$m = $status['memory_usage'];

$stats = $status['opcache_statistics'];

$file_count = $stats['num_cached_scripts'];

$hit_rate = $stats['opcache_hit_rate'];
$hit_rate_perc = intval($hit_rate);

$string_stats = $status['interned_strings_usage'];
$string_stats['buffer_size'];
$string_stats['used_memory'];
$string_stats['free_memory'];
$string_stats['number_of_strings'];


$mem_usage = ($m['used_memory'] / $cfg['opcache.memory_consumption']) * 100;
$mem_usage_percentage = number_format($mem_usage,2);


$folder_scripts = [];
foreach($status['scripts'] as $f){
	$dir = dirname($f['full_path']);
	$folder_scripts[$dir][] = $f;
}
ksort($folder_scripts);


$string_stats = $status['interned_strings_usage'];


?>
<style>
.ib{display:inline-block;}
.bg{display:inline-block;}
.right{float:right;}
.card ul{
	font-size:1.2em;
}

.card li{font-weight:bold;}
.card li span{float:right;}


.card table{font-size:1.2em;}
.card th{text-align:left;}
.card h2{margin:15px 0 15px 0;color:rgba(255,255,255,0.75);font-size:1.5em;}
.card{	
	position;relative;
	color:white;
	min-width:unset;
}
.card .topleft{
	transform:rotate(-45deg);
	font-size:3em;
	color:rgba(255,255,255,0.8);
	position:absolute;
	top:0;
	right:0;
	padding:10px;
}


div .cache-stats{
	position:relative;
	
	display:inline-block;
}
div.donut{
	display: flex;
	align-items: center;
	justify-content: center;  
	width: 7vw;
	height: 7vw;
	top: 0;
	right: 0;
	bottom: 0;
	left: 0;
	margin: auto;
	position: absolute;
	background: #DDDDDD -webkit-linear-gradient(left, #DDDDDD 50%, #0af 50%);
	background: #DDDDDD linear-gradient(to right, #DDDDDD 50%, #0af 50%);
	color: #0af;
	border-radius: 50%;
	padding:5px;
}
@-webkit-keyframes spin {
  to {
    -webkit-transform: rotate(180deg);
            transform: rotate(180deg);
  }
}
@keyframes spin {
  to {
    -webkit-transform: rotate(180deg);
            transform: rotate(180deg);
  }
}
@-webkit-keyframes background {
  50% {
    background-color: currentColor;
  }
}
@keyframes background {
  50% {
    background-color: currentColor;
  }
}
div.donut::after {
  content: '';
  position: absolute;
  width: 80%;
  height: 80%;
  top: 0;
  right: 0;
  bottom: 0;
  left: 0;
  margin: auto;
  background: #545b7a;
  border-radius: 50%;
}
div.donut::before {
  content: '';
  position: absolute;
  display: block;  
  height: 100%;
  width: 50%;
  bottom: 0;
  right: 0;
  border-radius: 0 100% 100% 0 / 50%;
          transform: rotate(0);
  -webkit-transform-origin: 0 50%;
          transform-origin: 0 50%;
  -webkit-animation: 50s spin infinite linear, 100s background infinite step-end;
          animation: 50s spin infinite linear, 100s background infinite step-end;
  -webkit-animation-play-state: paused;
          animation-play-state: paused;
  -webkit-animation-delay: inherit;
          animation-delay: inherit;
}
div.donut span{
	color:white;
	font-weight:bold;
	font-size: 1.2vw;
	z-index:100;
	position:relative;
	text-align:center;	
}
div.donut span span{padding-top:15px;font-size:0.7em;}

table.table th{font-weight:bold;font-size:1.4em;}

div.donut.green{
	color:#27ae60;
	background: #DDDDDD -webkit-linear-gradient(left, #DDDDDD 50%, #27ae60 50%);
	background: #DDDDDD linear-gradient(to right, #DDDDDD 50%, #27ae60 50%);
}
div.donut.purple{
	color:#8e44ad;
	background: #DDDDDD -webkit-linear-gradient(left, #DDDDDD 50%, #8e44ad 50%);
	background: #DDDDDD linear-gradient(to right, #DDDDDD 50%, #8e44ad 50%);
}
.flex-row{
   display: -webkit-flex;
   display: flex;
   -webkit-flex-direction: row;
   flex-direction: row;
   -webkit-flex-wrap: nowrap; /* Safari 6.1+ */
   flex-wrap: nowrap;   
}
.flex-row > *{
	margin-top:10px;
	flex-grow: 1;
}

.flex-row .stats{
	justify-content: center;
	display: flex;
	align-items: center;
}

tr.files{display:none;}

.wp-core-ui .button-danger{	
	border-color:#ff0000;
	box-shadow:none;
	background:#ff0000;
	text-shadow:none;
}
.wp-core-ui .button-danger:hover{
	background:#d00000;
}
</style>
<form method=POST>
	<input type=hidden name=reset_op_cache value=1>
	<?php wp_nonce_field( 'reset_op_cache' ); ?>
	<button class="button-danger button-primary" type=submit>Clear OpCache</button>
</form>
<div class=flex-row>
<div class=cache-stats>
			<div class="donut green" style="animation-delay: -<?=$mem_usage_percentage?>s">
				<span>
					<?=number_format($mem_usage_percentage)?>%
					<br/>
					<span class=cGreen>Mem Usage</span>
				</span>		
			</div>
		</div>
	<div class=cache-stats>
		<div class=donut style="animation-delay: -<?=$hit_rate_perc?>s">
			<span>
				<?=number_format($hit_rate,2)?>%
				<br/>
				<span class=cBlue>Hit Rate</span>
			</span>
		</div>
	</div>
	<div class="card bgBlue">
		<h2>Memory Stats</h2>
		<span class=topleft><i class="fa fa-microchip"></i></span>
		<ul>
			<li><label>Total 		</label><span><?=size_format($cfg['opcache.memory_consumption'],0)?></span></li>
			<li><label>Used</label> 	<span><?=size_format($m['used_memory'])?></span></li>
			<li><label>Free</label> 	<span><?=size_format($m['free_memory'])?></span></li>		
			<li><label>Wasted</label> 	<span><?=size_format($m['wasted_memory'])?></span></li>
			<li><label>Wasted</label> 	<span><?=number_format($m['current_wasted_percentage'],2)?>%</span></li>
		</ul>
	</div>
	<div class="card bgGreen">
		<h2>String Stats</h2>
		<span class=topleft><i class="fa fa-quote-right"></i></span>
		<ul>
			<li><label>Buffer Size</label>			<span><?=size_format($string_stats['buffer_size']);?></span></li>
			<li><label>Used Memory</label>			<span><?=size_format($string_stats['used_memory']);?></span></li>
			<li><label>Free Memory</label>			<span><?=size_format($string_stats['free_memory']);?></span></li>		
			<li><label>Number of Strings</label>	<span><?=number_format($string_stats['number_of_strings']);?></span></li>	
		</ul>
	</div>
	<div class="card bgDark">
		<h2>Configuration</h2>
		<span class=topleft><i class="fa fa-cogs"></i></span>
		<ul>
			<li><label>Start Time</label><span><?=date('n/j/Y h:ia',$stats['start_time'])?></span></li>
			<li><label><!-- Reset Count --></label><span></span></li>		
			<li><label>validate_timestamps</label><span><?=number_format($cfg['opcache.validate_timestamps'])?></span></li>
			<li><label>revalidate_freq</label><span><?=number_format($cfg['opcache.revalidate_freq'])?></span></li>
			<li><label>interned_strings_buffer</label><span><?=number_format($cfg['opcache.interned_strings_buffer'])?></span></li>
			<li><label>max_accelerated_files 	</label><span><?=number_format($cfg['opcache.max_accelerated_files'])?></span></li>
		</ul>
	</div>
	
</div>
<?php $folder_id = 0; ?>
<h2>
	<span class=cPurple>Files Cached</span> <?=number_format($file_count)?>
	<span class=right>
	<b class=cGreen>Hits</b> <?=number_format($stats['hits'])?>
	&nbsp;&nbsp;
	<b class=cRed>Misses</b> <?=number_format($stats['misses'])?>
	</span>
</h2>
<table class="wp-list-table widefat fixed striped table">
	<thead>
		<tr>
			<th>File
			<th>Hits
			<th>
	<tbody>
<?php foreach($folder_scripts as $folder=>$files) : ?>
	<tr class=folder><th colspan=2><?=$folder?><th><?=count($files)?> Files
	<?php foreach($files as $f) : ?>
		<tr class="files folder_<?=$folder_id?>"><td><?=basename($f['full_path'])?><td><?=$f['hits']?><td>
	<?php endforeach; ?>
	<?php $folder_id++;?>
<?php endforeach; ?>
</table>






