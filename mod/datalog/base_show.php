<?php
checkorredirect("/base/$base/edit.php");
ob_start();
$bas = il_select('dl_station','*',array('station'=>"=$base"));
$bau = il_select('user','*',array('id'=>"=$base"));
$bap = il_select('dl_param','*',array('station'=>"=$base",'address'=>"=0"));
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
$bp = array();
foreach($bap as $bi) {
	$k = $bi['param'];
	if($bi['param_idx'] != 1)
		$k.= sprintf('_%d',$bi['param_idx']);
	$bp[$k] = $bi['value'];
}
$basename = isset($ba['name'])? $ba['name']: "Base $bb";
$title = "Edición de estación $basename";
il_put('title',$title);
?>
<p class=nav>Ver un <a href="/base/<?php echo $bb?>/">perfil</a> o <a href="/item/<?php echo $bb?>/">listado de instrumentos</a> de la estación.</p>
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
	<form method=POST>
	<fieldset><legend>Datos de la estaci&oacute;n</legend>
		<label for=base>Estaci&oacute;n: <input id=base name=base value="<?=$ba['station']?>" disabled></label><br>
		<label for=name>Nombre: <input id=name name=name value="<?=$ba['name']?>"></label><br>
		<label for=ip>Direci&oacute;n IP: <input id=ip name=ip value="<?=$ba['ip']?>" disabled></label><br>
		<label for=group>Grupo: <select name=group id=group>
			<option><?=$ba['group']?></option>
		</select></label><br>
		<label for=public>P&uacute;blico: <input type=checkbox id=public name=public<?=($ba['public']?' checked':'')?>></label><br>
	</fieldset>
	<fieldset><legend>Par&aacute;metros</legend>
<?php
	foreach($bp as $k=>$v) {
?>
		<label for="<?=$k?>">Nombre: <input id="<?=$k?>" name="<?=$k?>" value="<?=$v?>"></label><br>
<?php
	}
?>
		<label for=newpn><input id=newpn name=newpn placeholder="Nuevo par&aacute;metro"></label>:
		<label for=newpv><input id=newpv name=newpv placeholder="Valor"></label>
	</fieldset>
	<fieldset><legend>Avatar</legend>
<?php
	if(isset($bu['avatar']) && ($avi=$bu['avatar'])) {
		$avb = il_avatar($avi,'big','/images/big-avatar.png');
		$avs = il_avatar($avi,'small','/images/def-avatar.png');
		echo "\t\t<img src=\"$avb\" class=left>\n";
		echo "\t\t<img src=\"$avs\" class=left>\n";
	}
?>
		<label for=avatar>Escoja un archivo: <input type=file id=avatar name=avatar></label><br>
	</fieldset>
		<input type=submit><input type=reset>
	</form>
<?php
return ob_get_clean();
?> 
