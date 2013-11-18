<?php
if(base_user_level($base)<7) redirect(sprintf("/item/%s/%03d/",$base,$item));
checkorredirect($redir=sprintf("/item/%s/%03d/edit.php",$base,$item));
ob_start();

$wthis = array(
	'station' => $base,
	'address' => (int)$item,
);

$changed = false;
$notes = '';
if(!empty($_FILES['avatar'])) {
	$r = update_avatar($_FILES['avatar']);
	#$notes.= print_r($r,true);
	$k = array_keys($r);
	$f = false;
	while($k && !($f=is_numeric($i = array_pop($k))));
	if($f) {
		db_update('dl_instrument',array('avatar'=>$i),$wthis);
		$changed |= true;
	}
}
$bUs = db_select_first('user','*',array('id'=>"=$base"));
$bSt = db_select_first('dl_station','*',array('station'=>"=$base"));
$iIn = db_select_first('dl_instrument','*',$wthis);
$iMt = db_select_key('dl_meter','keyword',$wthis);
$iPr = db_select('dl_param','*',$wthis);
il_put('title', "Edición: ".$iIn['description']." (".$bSt['name'].")");
if(!empty($_POST)) {
	$i=0;
	$meters = array();
	$alarms = array();
	$params = array();
	//$notes .= "<!-- Data: ".print_r($_POST,true)." -->\n";
	foreach($_POST as $k=>$v) {
		$kk = explode('_', $k);
		#print_r($kk);
		switch($kk[0]) {
		case 'x':
			if(isset($meters[$kk[1]]))
				$meters[$kk[1]][$kk[2]] = $v;
			else
				$meters[$kk[1]] = array($kk[2] => $v);
			break;
		case 'y':
			//$notes.= (++$i).". <!-- param: $k => '$v' -->\n";
			if(isset($kk[2]) && is_numeric($kk[2])) {
				$p = $kk[1];
				$n = (int)$kk[2];
			} else {
				array_shift($kk);
				$p = implode('_',$kk);
				$n = 1;
			}
			if(!isset($params[$p]))
				$params[$p] = array($n=>$v);
			else
				$params[$p][$n] = $v;
			break;
		case 'z':
			array_shift($kk);
			$a = implode($kk);
			$alarms[$a] = $v;
			break;
		case 'description':
			if(!empty($v)) {
				db_update('dl_instrument',array($k=>$v),$wthis);
				$changed |= true;
			}
			break;
		case 'newpn':
			if(!empty($v)) {
				$rr = array_merge($wthis, array(
						'param'=>$v,
						'param_idx'=>1,
						'value'=>$_POST['newpv'],
					));
				db_insert('dl_param',$rr);
				$changed |= true;
			}
			break;
		case 'newpv':
			break;
		default:
			$notes.= (++$i).". <!-- $k => '$v' -->\n";
		}
	}
	foreach($meters as $m=>$a) {
		if(!isset($iMt[$m])) continue;
		$mar = array();
		if(empty($a['common'])) {
			$notes.= "empty \n";
			if($iMt[$m]['type'] == 'common') $mar['type'] = 'flag';
		} else {
			$notes.= "not-empty \n";
			if($iMt[$m]['type'] != 'common') $mar['type'] = 'common';
			else $notes.= $iMt[$m]['type'].chr(10);
		}
		if(isset($a['name']) && $iMt[$m]['name'] != $a['name']) $mar['name'] = $a['name'];
		if(isset($a['format']) && $iMt[$m]['format'] != $a['format']) $mar['format'] = $a['format'];
		if(!empty($mar)) {
			$where = array_merge($wthis,array('keyword'=>$m));
			db_update('dl_meter',$mar,$where);
			$changed |= true;
		}
		$notes.= (++$i).". <!-- param: $m - ".print_r($iMt[$m],true).print_r($a,true).print_r($mar,true).print_r($where,true)." -->\n";
	}
	//$notes.= (++$i).'. <!-- meters: '.print_r($meters,true)." -->\n";
	$notes.= (++$i).'. <!-- alarms: '.print_r($alarms,true)." -->\n";
	$notes.= (++$i).'. <!-- params: '.print_r($params,true)." -->\n";
}
if($changed) redirect($redir);

$id = json_read($fn="data/datalog/$base/$item.inst");
if(!empty($notes)) echo "<div style='white-space:pre-wrap'>$notes</div>\n";
#print_r($id);

?>
	<form method=POST enctype="multipart/form-data">
	<fieldset><legend>Datos de la estación</legend>
		<label class="uilabel uimid" for=base>Estación: </label><input class=shorttext id=base name=base value="<?=$bSt['station']?>" disabled><br>
		<label class="uilabel uimid" for=name>Nombre: </label><input class=midtext id=name name=name value="<?=$bSt['name']?>" disabled><br>
		<label class="uilabel uimid" for=ip>Direción IP: </label><input class=shorttext id=ip name=ip value="<?=$bSt['ip']?>" disabled><br>
		<label class="uilabel uimid" for=group>Grupo: </label><select class=shorttext name=group id=group disabled>
			<option><?=$bSt['group']?></option>
		</select><br>
		<label class="uilabel uimid" for=public>Público: </label><input class=checkbox type=checkbox id=public name=public<?=($ba['public']?' checked':'')?> disabled><br>
		<a href="/base/<?=$base?>/edit.php" class=button>Editar</a>
	</fieldset>
	<fieldset><legend>Datos del instrumento</legend>
		<label class="uilabel uimid" for=address>Estación: </label><input id=address name=address value="<?=$iIn['address']?>" disabled><br>
		<label class="uilabel uimid" for=reference>Referencia: </label><input id=reference name=reference value="<?=$iIn['reference']?>" disabled><br>
		<label class="uilabel uimid" for=description>Descripción: </label><input id=description name=description value="<?=$iIn['description']?>"><br>
	</fieldset>
	<fieldset><legend>Medidores</legend>
		<table>
			<tr>
				<th>Clave</th>
				<th>Activo</th>
				<th>Nombre</th>
				<th>Formato</th>
				<th>Mínimo</th>
				<th>Máximo</th>
			</tr>
<?php
	foreach($iMt as $mid=>$x) {
		if($x['type']=='alarm') continue;
		$act = $x['type']=='common'? ' checked': '';
?>
			<tr>
				<td><?=$mid?></td>
				<td class=centered><input class=checkbox name=x_<?=$mid?>_common type=checkbox<?=$act?>></td>
				<td><input class=midtext name=x_<?=$mid?>_name value="<?=$x['name']?>"></td>
				<td><input class=shorttext name=x_<?=$mid?>_format value="<?=$x['format']?>"></td>
				<td><input class=numeric name=x_<?=$mid?>_min value="<?=$x['min']?>" disabled></td>
				<td><input class=numeric name=x_<?=$mid?>_max value="<?=$x['max']?>" disabled></td>
			</tr>
<?php
	}
?>
		</table>
	</fieldset>
	<fieldset><legend>Parámetros</legend>
<?php
	foreach($iPr as $pid=>$x) {
		$par = $pid = $x['param'];
		if(isset($x['param_idx']) && $x['param_idx']!=1) {
			$par.= '['.$x['param_idx'].']';
			$pid.= '-'.$x['param_idx'];
		}
		echo "\t\t<label class=\"uilabel uimid\" for=$pid>$par: </label><input class=numeric id=$pid name=y_$pid value=\"{$x['value']}\"><br>\n";
	}
?>
		<span class="uilabel uimid"><input class=textshort id=newpn name=newpn placeholder="Nuevo parámetro">: </span><input class=numeric id=newpv name=newpv placeholder="Valor">
	</fieldset>
	<fieldset><legend>Alarmas</legend>
<?php
	foreach($iMt as $aid=>$x) {
		if($x['type']!='alarm') continue;
		echo "\t\t<label class=\"uilabel uimid\" for=$aid>$aid: </label><input class=midtext id=$aid name=z_$aid value=\"{$x['name']}\"><br>\n";
	}
?>
	</fieldset>
	<fieldset><legend>Avatar</legend>
<?php
	if(isset($iIn['avatar']) && ($avi=$iIn['avatar'])) {
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
<!--
<?php
print_r($bUs);
print_r($bSt);
print_r($iIn);
print_r($iMt);
print_r($iPr);
?>
-->
<?php
return ob_get_clean();
?>
