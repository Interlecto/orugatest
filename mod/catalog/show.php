<?php
/*
if(!isset($GLOBALS['sitename'])) redirect("../venta/");
*/
$statclass = isset($ar['status']) && $ar['status']? "active": "inactive";
$actclass = !isset($ar['action'])? 'venta': $ar['action'];
/*
if ($actclass!=$GLOBALS['pre'])
	redirect("/$actclass/$seo/$last.html");
*/
$tip = $ar['tipo'];
$sec = $tipos[$tip];
$secn = $arbol[$sec]['nombre'];
$tipn = $arbol[$sec]['tipos'][$tip]['plural'];
$content = "<nav class=breadcrumbs><a href=\"/$action/\">$Action de maquinaria</a> » <a href=\"/$action/$sec/\">$secn</a> » <a href=\"/$action/$sec/$tip.html\">$tipn</a></nav>\n";
//$content.= galeria($last,$ar['tipo']);

function tabrow($uno,$dos,$x=0) {
	$ans = $x<2? "<tr>":"\t";
	$ans.= "<td class=uno>$uno</td><td class=dos>$dos</td>";
	$ans.= $x==0||$x==2? "</tr>\n":"\n";
	return $ans;
}
function tabrowx($ar,$t,$x=0,$nn=null) {
	return tabrow($nn?$nn:ucfirst($t),$ar[$t],$x);
}
function tabblank($x) {
	return ($x==1?"<tr>":"\t")."<td colspan=2 class=blank> </td>".($x==2?"</tr>\n":"\n");
}

function getpais($q) {
	$pp = file("data/paises.db");
	foreach($pp as $line){
		$qq = explode(',',trim($line));
		if($q==$qq[0]) return $qq[1];
	}
	return 'otro';
}

$formfields=array('marca','modelo','anno','contacto','telefono','email','precio','disponibilidad','pais','ciudad','accesorios','horas');
foreach($formfields as $key) {
	if(!isset($ar[$key])) $ar[$key]='';
}


$content.= "\n<table id={$ar['tipo']}-$last class=\"maq-full {$ar['tipo']} $statclass $actclass\">\n";
$content.= tabrow("Tipo de oferta",isset($ar['status']) && $ar['status']? "Activa": "Caduca",1);
$content.= tabrow("Para",$actclass,2);
//$content.= tabrow('Tipo de maquinaria',tiponom($ar['tipo'],$arbol,$tipos),1);
$content.= tabrow('Sector',$arbol[$tipos[$ar['tipo']]]['nombre'],2);
if ($actclass=='venta') $content.= tabrow('Precio',$ar['precio']." millones",1).tabblank(2);
else $content.= tabrow('Canon',$ar['precio']." pesos/hora",1).tabblank(2);
$content.= tabrowx($ar,'marca',1);
$content.= tabrowx($ar,'modelo',2);
$content.= tabrow("Año de fabricación",$ar['anno'],1);
$content.= tabrow("Horas de uso",$ar['horas'],2);
global $theuserlevel;
if($theuserlevel>=2) {
	$content.= tabrow("Persona de contacto",$ar['contacto'],1);
	$content.= tabrow("Teléfono",$ar['telefono'],2);
	$content.= tabblank(1);
	$content.= tabrow("Correo electrónico",$ar['email'],2);
}
$content.= tabrowx($ar,"disponibilidad",1);
$content.= tabblank(2);
$content.= tabrow("País",getpais($ar['pais']),1);
$content.= tabrowx($ar,'ciudad',2);
$content.= "<tr><td class=uno>Accesorios</td><td class=dos colspan=3>{$ar['accesorios']}</td></tr>";
$content.= "<tr><td class=uno>Descripción</td><td class=dos colspan=3>{$ar['text']}</td></tr>";
$content.= "</table>\n";
if($theuserlevel>=2)
	$content.= "<p class=editentry><a href=\"/$action/editar/$last.html\">(Editar esta entrada)</a></p>";
$content.= "<hr class=clear>";

return $content
?>
