<?php
$in = (int)$item;
$bi = sprintf('%s/%03d',$base,$in);
checkorredirect("/item/$bi/");
ob_start();
$bb = db_str($base);
$wh = array('address'=>"=$in",'station'=>"=$base");
$whb = array('station'=>"=$base");
$ba = db_select_first('dl_station','*',$whb);
$ia = db_select_first('dl_instrument','*',$wh);
$dlp = db_select('dl_param','*',$wh);
$dlm = db_select_key('dl_meter','keyword',$wh);
//$ia = isset($dli[0])? $dli[0]: array();
//$ba = isset($dls[0])? $dls[0]: array();
if($dlp) foreach($dlp as $pr) {
	$p = $pr['param'];
	if($pr['param_idx']!=1) $p.= '-'.$pr['param_idx'];
	$ia[$p] = $pr['value'];
}
/*if($dlm) {
	$ia['meters'] = array();
	$ia['alarms'] = array();
	foreach($dlm as $k=>$mr) {
		//$k = $mr['keyword'];
		if(substr($k,0,6)=='alarm-') {
			$ia['alarms'][(int)substr($k,6)] = $mr['name'];
		} else {
			$ia['meters'][$k] = $mr;
		}
	}
}*/
echo "<!--";print_r(array('ins'=>$ia,'sta'=>$ba));echo "-->\n";
//$bb = $base;
//$ii = (int)$item;
$basename = empty($ba['name'])? "Base $base": $ba['name'];
$instname = empty($ia['description'])? "Instrumento $in": $ia['description'];
il_put('title',"$instname en $basename");
	
	/*
	if(empty($ia)) {
		$r = common_feed($user,$base,$item,10);
		if(empty($r['inst'])) {
			$title = "Instrumento '$item' no encontrado";
			$content = "$title.\n".ob_get_clean();
			return reply_status(404,null,$title,$content);
		}
		$at = $r['STATUS'][0];
		$bb = $at['data'][0];
		$ii = $at['data'][1];
		$ba = $r['BASE'][$bb];
		$ia = $r['INST'][$bb][$ii];
	} else {
		$bb = $base;
		$ii = $item;
	}*/

	
?>
	<p>Aquí estará la información general de <?php echo $instname?> en <a href="/base/<?php echo $bb?>/"><?php echo $basename?></a>.</p>
	<p class=nav>Ver <a href="/item/<?php echo $bi?>/feed.html">últimos estados</a>, <a href="/item/<?php echo $bi?>/month.html">gráfico de evolución</a> o <a href="/item/<?php echo $bb?>/">todos los instrumentos</a> de <?php echo $basename?>.</p>
	<table class=datasheet>
		<thead>
		<tr><th>Item<th colspan=2>Valor</tr>
		<tbody>
		<tr><td colspan=2>Descripción<td colspan=4><?php echo $ia['description']?></tr>
		<tr><td colspan=2>Dirección<td colspan=4><?php echo $ia['address-2']?></tr>
		<tr><td colspan=2>Modelo<td colspan=4><?php echo $ia['modelo']?></tr>
		<tr><td colspan=2>Tipo<td colspan=4><?php echo $ia['tipo']?> / <?php echo $ia['tipo2']?></tr>
		<tr><td colspan=2>Estado<td colspan=4><?php echo $ia['status']?></tr>
		<tr><td colspan=2>Tiempo cad<td colspan=4><?php echo date('j \d\e M, g:i a',$ia['time_cad'])?></tr>
		<thead>
		<tr><th>Medidas<th>Valor mínimo<th>Alarma baja<th>Valor medio<th>Alarma alta<th>Valor máximo</tr>
		<tbody>
<?php
	foreach($dlm as $m=>$md) {
		if($dlm['type']=='alarm') continue;
		$name = empty($md['name'])? $m: $md['name'];
		$min = isset($md['min'])? sprintf($md['format'],$md['min']): '';
		$max = isset($md['max'])? sprintf($md['format'],$md['max']): '';
		$q = <<<QUERY
SELECT `value`, `time`, `idx` FROM `dl_status_p` JOIN `dl_status_i` ON `dl_status_p`.`status`=`dl_status_i`.`idx`
WHERE `station`='$bb' AND `address`=$in AND `keyword`='$m'
ORDER BY `time` DESC LIMIT 0,100;
QUERY;
		$vals = array();
		$valsMx = -1000000;
		$valsMn = 1000000;
		unset($t1);
		$i = 0;
		$qr = db_query($q);
		if($qr) while($qrr = $qr->fetch_array(MYSQLI_ASSOC)) {
			if(!isset($t1)) $t1=$qrr['time'];
			$vals[] = $qrr['value'];
			if($qrr['value']>$valsMx) { $MxI=(int)$qrr['idx']; $valsMx=$qrr['value']; $MxT=$qrr['time']; }
			if($qrr['value']<$valsMn) { $MnI=(int)$qrr['idx']; $valsMn=$qrr['value']; $MnT=$qrr['time']; }
			$t0 = $qrr['time'];
			$i++;
		}
		$qr->free();
		$valsMm = $i=0? '---': array_sum($vals)/$i;
		if(isset($md['format'])) {
			$valsMx = sprintf($md['format'],$valsMx);
			$valsMn = sprintf($md['format'],$valsMn);
			$valsMm = $i=0? '---': sprintf($md['format'],$valsMm);
		}
		$valsMn = $MnI!=$MxI? "<a href=\"/item/$bi/$MnI\" title=\"$MnT\">$valsMn</a>": $valsMn;
		$valsMx = $MnI!=$MxI? "<a href=\"/item/$bi/$MxI\" title=\"$MxT\">$valsMx</a>": $valsMx;
		echo "\t\t<tr><td>$name<td>$valsMn<td>$min<td>$valsMm<td>$max<td>$valsMx</tr>\n";
	}
?>
		<thead>
		<tr><th colspan=2>Alarmas<th colspan=2>Última activación<th colspan=2>Última desactivación</tr>
		<tbody>
<?php
	foreach($dlm as $a=>$ad) {
		if($dlm['type']!='alarm') continue;
		$alarm = db_str($a);
		$q1 = <<<QUERY
SELECT `time`,`idx` FROM `dl_status_p` JOIN `dl_status_i` ON `dl_status_p`.`status`=`dl_status_i`.`idx`
WHERE `station`='$bb' AND address=$in AND `keyword`='$alarm' AND `value`>=0
ORDER BY `time` DESC LIMIT 0,1;
QUERY;
		$q0 = <<<QUERY
SELECT `time`,`idx` FROM `dl_status_p` JOIN `dl_status_i` ON `dl_status_p`.`status`=`dl_status_i`.`idx`
WHERE `station`='$bb' AND address=$ii AND `keyword`='$alarm' AND `value`<=0
ORDER BY `time` DESC LIMIT 0,1;
QUERY;
		if($qr = db_query($q1)) {
			$qr1 = $qr->fetch_array();
			if(substr($qr1[0],0,1)==2)
				$qd1 = '<a href="/item/'.$bi.'/'.$qr1[1].'">'.
					date('j \d\e M, g:i:s a',strtotime($qr1[0]))
					.'</a>';
			else
				$qd1 = '';
			$qr->free();
		} else $qd1 = 'no activada';
		if($qr = db_query($q0)) {
			$qr0 = $qr->fetch_array();
			if(substr($qr0[0],0,1)==2)
				$qd0 = '<a href="/item/'.$bi.'/'.$qr0[1].'">'.
					date('j \d\e M, g:i:s a',strtotime($qr0[0]))
					.'</a>';
			else
				$qd0 = '';
			$qr->free();
		} else $qd0 = 'no desactivada';
		
		echo "\t\t<tr><td colspan=2>{$ad['name']}<td colspan=2>$qd1<td colspan=2>$qd0</tr>\n";
	} //*/
?>
	</table>
<?php
	//echo "<pre style=\"font-size:0.6em;color:rgba(0,0,0,.5)\">".print_r(isset($r)?$r:$ia,true)."</pre>";
return ob_get_clean();
?>