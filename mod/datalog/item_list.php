<?php
ob_start();
$bas = il_select('dl_station','*',array('station'=>"=$base"));
$bau = il_select('user','*',array('id'=>"=$base"));
if(empty($bas)) {
	$r = common_feed($user,$base,null,1);
	$as = $r['STATUS'][0];
	$bb = $as['data'][0];
	$ba = $r['BASE'][$bb];
} else {
	$bb = $base;
	$ba = $bas[0];
	$bu = $bau[0];
}
$basename = isset($ba['name'])? $ba['name']: "Base $bb";
$title = "Listado de los items de $basename";
il_put('title',$title);
?>
<p class=nav>Ver un <a href="/base/<?php echo $bb?>/">perfil de la estación</a>.</p>
<?php
$ins = il_select('dl_instrument','*',array('station'=>"=$bb"));
$tpar = il_select('dl_param','*',array('station'=>"=$bb"));
$par = array();
foreach($tpar as $x) {
	$y=$x['address'];
	if(!isset($par[$y])) $par[$y]=array();
	$p=$x['param_idx']==1?$x['param']:$x['param'].'-'.$x['param_idx'];
	$par[$y][$p] = $x['value'];
}
$tmet = il_select('dl_meter','*',array('station'=>"=$bb"));
$met = array();
foreach($tmet as $x) {
	$y=$x['address'];
	if(!isset($met[$y])) $met[$y]=array();
	$met[$y][$x['keyword']] = $x;
}
?>
	<div class=itemlist>
<?php
foreach($ins as $ia) {
	$ii = $ia['address'];
	$ij = sprintf('%03d',$ii);
	$avi = empty($ia['avatar'])? (
		empty($bu['avatar'])? '/images/def-avatar.png': $bu['avatar']
		): $ia['avatar'];
	$ava = il_avatar($avi,'small','/images/def-avatar.png');
	$maq = "/item/$base/$ij/";
	$lst = "/item/$base/$ij/feed.html";
	$desc = isset($ia['description'])? $ia['description']: 'Instrumento '.$ii;
?>
		<p class=item>
			<a class=avatar-link href="<?php echo $maq?>"><img class=avatar src="<?php echo $ava?>"></a>
			<a class=desc-link href='<?php echo $maq?>'>
			<span class=item-desc><strong><?php echo $desc?></strong>
				<em class=aside><a href="<?php echo $lst?>">(últimos estados)</a></em>
			</span>
			</a>
		</p>
<?php
}
?>
	</div>
<?php
return ob_get_clean();
?>
