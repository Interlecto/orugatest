<?php
ob_start();
$r = common_feed($user,$base,$item,2,$statusid);
$stx = $r['STATUS'];
if(count($stx)) {
	$or = il_select('dl_meter','*',array('station'=>"=$base",'address'=>"=$item"),null,false,0);
	foreach($or as $orr) {
		$or[$orr['keyword']] = $orr;
	}

	$stk = array_keys($stx);
	$stv = array_values($stx);
	$sign = $stk[0];
	$status = $stv[0];
	$sd = strtotime(substr($sign,0,19));
	$dd = date('j \d\e M, g:i:s a',$sd);
	$title = "Estado $dd";
	$bb = trim(substr($sign,21,-5));
	$ii = (int)substr($sign,-3);
	$bi = sprintf('%s/%03d',$bb,$ii);
	$basename = isset($r['BASE'][$bb]['name'])? $r['BASE'][$bb]['name']: "Base $bb";
	$instname = isset($r['INST'][$bb][$ii]['description'])?$r['INST'][$bb][$ii]['description']: "Instrumento $ii";
	$title.= ", $instname ($basename)";
?>
	<table>
	<tr><th class=lalign>Local:<td><a href="/base/<?php echo $bb?>/"><?php echo $basename?></a> <a href="/item/<?php echo $bb?>/">(instrumentos)</a>
	<tr><th class=lalign>Instrumento:<td><a href="/item/<?php echo $bi?>/"><?php echo $instname?></a> <a href="/item/<?php echo $bi?>/feed.html">(Últimos estados)</a> <a href="/item/<?php echo $bi?>/month.html">(Gráficos de evolución)</a>
<?php
	foreach($status as $k=>$v) {
		if($k=='id') continue;
		//echo "<pre>[".print_r($or,true)."]</pre>\n";
		$Sk = isset($or[$k]) && $or[$k]['name']? "<th class=lalign>{$or[$k]['name']}:": "<th class=\"lalign grayed\">$k:";
		if(substr($k,0,6)=='alarm-')
			$Sv = $v==0? 'Activada y desactivada': ($v>0? 'Activada': 'Desactivada');
		else
			$Sv = isset($or[$k]) && $or[$k]['format']? sprintf($or[$k]['format'],$v): "<span class=grayed>$v</span>";
		echo "\t<tr>$Sk<td>$Sv\n";
	}
?>
	</table>
<?php
} else {
	$title = "Estado $statusid";
	echo "<p>No hay ninguna actualización de estado $statusid</p>\n";
}
il_put('title',$title);

	//echo "<pre style=\"font-size:0.6em;color:rgba(0,0,0,.5)\">".print_r(isset($r)?$r:$ia,true)."</pre>";
	//echo "<pre style=\"font-size:0.6em;color:rgba(0,0,0,.5)\">".print_r($GLOBALS['il'],true)."</pre>";

return ob_get_clean();
?>
