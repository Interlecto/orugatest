<?php

// if(!isset($sitename)) redirect("../venta/");

function autoplural($n) {
	if(in_array(substr($n,-1),array('a','e','i','o','u'))) return $n.'s';
	return $n.'es';
}

/*
function autoplural($n) {
	if(in_array(substr($n,-1),array('a','e','i','o','u'))) return $n.'s';
	return $n.'es';
}

function tiponom($t,$arbol,$tipos) {
	if(!isset($tipos[$t])) return $t;
	return $arbol[$tipos[$t]]['tipos'][$t]['nombre'];
}




$paths[] = 'venta';
function content_venta($what,&$content,&$title) {
	global $pre,$mid,$ext;
	$content = maquinaria_action($pre,$mid,$what,$ext,$title);
	setarea('left','&nbsp;');
}

$paths[] = 'alquiler';
function content_alquiler($what,&$content,&$title) {
	global $pre,$mid,$ext;
	$content = maquinaria_action($pre,$mid,$what,$ext,$title);
	setarea('left','&nbsp;');
}

*/

function product_data($m,$ar,&$title,&$seo) {
	$title = isset($ar['title'])? $ar['title']: ucwords(str_replace(array('-','_'),' ',isset($ar['tipo'])? $ar['tipo']." $m":$m));
	$seo = str2uri($title);
}

function product_list($max=100,$section='',$withtext=false,$type=null,$action=null) {
//	if(!$action) $action=$GLOBALS['pre'];
	if(empty($action)) $action = $section;
	$dirname = 'data/catalog/';
	$dar = array(
		'root'=>$dirname,
		'folder'=>$section,
	);
	if(empty($section)) {
	} else {
		dat2array("$dirname/$section.ini",$dar);
	}
	
	$d = dir($catalog = $dirname.$dar['folder']);
	$dd = array();
	while (false !== ($entry = $d->read())) {
		if(substr($entry,-5)==".prod" && is_numeric($n = substr($entry,0,-5))) {
			$dd[] = (int)$n;
		}
	}
	$d->close();
	rsort($dd);

	$s = "";
	$i=0;
	if(is_array($type)) {
		foreach($dd as $maq) {
			if($i>=$max) break;
			$ar = array();
			dat2array("$catalog/$maq.prod",$ar);
			if(!in_array($ar['tipo'],$type)) continue;
			if(isset($ar['action']) && $ar['action']!=$action) continue;
			$s.=product_profile($maq,$ar,$withtext);
			$i++;
		}
	} elseif($type) {
		foreach($dd as $maq) {
			if($i>=$max) break;
			$ar = array();
			dat2array("$catalog/$maq.prod",$ar);
			if($ar['tipo']!=$type) continue;
			if(isset($ar['action']) && $ar['action']!=$action) continue;
			$s.=product_profile($maq,$ar,$withtext);
			$i++;
		}
	} else {
		foreach($dd as $maq) {
			if($i>=$max) break;
			$ar = array();
			dat2array("$catalog/$maq.prod",$ar);
			if(isset($ar['action']) && $ar['action']!=$action) continue;
			$s.=product_profile($maq,$ar,$withtext);
			$i++;
		}
	}
	if(!$i) {
		$s = isset($dar['empty'])? "<p>".$dar['empty']."</p>\n" :"<p>No hay productos disponibles bajo estos criterios.</p>\n";
	}

	return $s;
}

function product_profile($m,$ar,$withtext=false) {
	$s=array();
	$showall = isset($_REQUEST['show']) && in_array($_REQUEST['show'],array('all','1'));
	if(isset($ar['status']) && $ar['status']!=='' && (int)$ar['status']==0 && !$showall) return "";
	$action = isset($ar['action'])? $ar['action']: 'venta';
	$ntitle = isset($ar['title'])? $ar['title']: ucwords(str_replace(array('-','_'),' ',isset($ar['tipo'])? $ar['tipo']." $m":$m));
	$ltitle = " title=\"$ntitle\"";
	$s[]="<section class=maquina id=maquina-$m$ltitle>";
	$s[]="\t<a href=\"/$action/$m.html\">";
	$s[]="\t<img src=\"/".iconfor($m,$ar['tipo'])."\" class=entryicon>";
	$s[]="\t<strong class=entrytitle>$ntitle</strong>";
	if($withtext) $s[]="\t<span class=entrytext>{$ar['text']}</span>";
	if(isset($ar['precio'])) {
		if($action=='venta') $s[]="\t<em class=entryprice>Precio: {$ar['precio']} millones</em>";
		else $s[]="\t<em class=entryprice>Canon: {$ar['precio']} pesos/hora</em>";
	}
	$s[]="\t</a>";
	$s[]="</section>";
	$s[]='';
	return implode("\n",$s);
}

function iconfor($n,$type,$sub="") {
	if($sub and substr($sub,-1)!='/') $sub.='/';
	$idx = "{$sub}media/$n/thumb.jpg";
	if(!file_exists($idx))
		$idx = "{$sub}media/$n/index.jpg";
	if(!file_exists($idx))
		$idx = "{$sub}media/$type.png";
	if(!file_exists($idx))
		$idx = "{$sub}media/default.png";
	return $idx;
}

function product_types($file,&$arbol,&$tipos) {
	$arbol = array();
	$tipos = array();
	$f = file($file);
	$s = '';
	$ans[$s]=array('tipos'=>array(),'nombre'=>$s);
	foreach($f as $line) {
		if(substr($line,0,1)=="\t") {
			$l = trim($line);
			$ll = explode(',',$l);
			$k = count($ll)>1 && $ll[1]? $ll[0]: str2uri($ll[0]);
			$n = count($ll)>1 && $ll[1]? $ll[1]: $ll[0];
			$p = count($ll)>2 && $ll[2]? $ll[2]: autoplural($n);
			$arbol[$s]['tipos'][$k]=array('nombre'=>$n,'plural'=>$p);
			$tipos[$k]=$s;
		} else {
			$l = trim($line);
			$ll = explode(',',$l);
			$s = count($ll)>1 && $ll[1]? $ll[0]: str2uri($ll[0]);
			$n = count($ll)>1 && $ll[1]? $ll[1]: $ll[0];
			$arbol[$s]=array('tipos'=>array(),'nombre'=>$n);
		}
	}
	return count($tipos);
}

function product_types_ul($arbol) {
	$pre = il_line_get('prefix');
	$ans = "<ul id=maqs>";
	foreach($arbol as $s=>$sec) {
		if($s) {
			$ans.="<li id=maq-$s class=maq0>";
			$ans.="<a href=\"/$pre/$s/\">{$sec['nombre']}</a>";
			if(isset($sec['tipos']) && $sec['tipos']) {
				$ans.="\n\t<ul>";
				foreach($sec['tipos'] as $k=>$tip) {
					$ans.= "\n<li><a href=\"/$pre/$s/$k.html\">{$tip['plural']}</a></li>";
				}
				$ans.="\n\t</ul>";
			}
			$ans.="</li>\n";
		}
	}
	$ans.= "</ul>\n";
	return $ans;
}


function make_catalog($line) {
	set_area('left','print','&nbsp;');
	
	$section = il_line_get('prefix');
	$last = il_line_get('last');
	$mid = implode('/',il_line_get('path'));
	$ext = il_line_get('extension');
	
	$dirname = 'data/catalog/';
	$dar = array(
		'root'=>$dirname,
		'folder'=>$section,
	);
	if(empty($section)) {
	} else {
		dat2array("$dirname/$section.ini",$dar);
	}
	$catalog = $dirname.$dar['folder'];
	$action = isset($dar['action'])? $dar['action']: $section;
	$Action = isset($dar['actioncap'])? $dar['actioncap']: mb_convert_case($action,MB_CASE_TITLE,'UTF-8');
	$actions = isset($dar['actions'])? $dar['actions']: $action.'s';
	$protree = isset($dar['tree'])? $dar['tree']: $action.'.tree';
	$title = isset($dat['title'])?$dat['title']:"$Action de productos";

	product_types($dirname.$protree,$arbol,$tipos);
	$show = isset($_REQUEST['show']) && $_REQUEST['show']=='all';
	if($mid) {
		if(array_key_exists($mid,$arbol)) {
			$sec = $arbol[$mid];
			if(!$last or $last=='index') {
				$title.= " para ".mb_strtolower($sec['nombre'],'UTF-8');
				$content = "<nav class=breadcrumbs><a href=\"/$action/\">$Action de maquinaria</a></nav>\n";
				if(isset($sec['tipos']) && $sec['tipos']) {
					$content.= "<nav class=maqsn>{$sec['nombre']} <ul class=maqss>\n";
					foreach($sec['tipos'] as $k=>$tip) {
						$content.="\t<li><a href=\"$k.html\">{$tip['plural']}</a></li>\n";
					}
					$content.= "</ul></nav>\n";
				}
				$content.= product_list(100,$section,false,array_keys($sec['tipos']),null)."<hr class=clear>\n";
				$content.= bottomnav($action,"$mid/",$show);
			} elseif(array_key_exists($last,$sec['tipos'])) {
				$tip=$sec['tipos'][$last];
				$title = "$Action de {$tip['plural']}";
				$content = "<nav class=breadcrumbs><a href=\"/$action/\">$Action de maquinaria</a> » <a href=\"/$action/$mid/\">{$sec['nombre']}</a></nav>\n";
				$content.= product_list(100,$section,false,$last)."<hr class=clear>\n";
				$content.= bottomnav($action,"$mid/$last.$ext",$show);
			} elseif(array_key_exists($last,$tipos)) {
				redirect("/$action/{$tipos[$last]}/$last.html");
			} elseif(is_numeric($last) && file_exists($fn="$catalog/$last.prod")) {
				dat2array($fn,$ar);
				datos_maquina($last,$ar,$title,$seo);
				redirect("/$action/$seo/$last.html");
			} else {
				$title = "404 No encontrado";
				$content = "<p>No existe la página <code>$last.$ext</code> dentro de $actions del {$sec['nombre']}.</p>";
			}
		} else {
			if($mid=='editar') {
				include "maquinaria/edit.php";
			} elseif($mid=='enviar') {
				include "maquinaria/send.php";
			} elseif(array_key_exists($mid,$tipos)) {
				redirect("/$action/{$tipos[$mid]}/$mid.html");
			} elseif(is_numeric($last) && file_exists($fn="$catalog/$last.prod")) {
				dat2array($fn,$ar);
				product_data($last,$ar,$title,$seo);
				if($seo != $mid)
					redirect("/$action/$seo/$last.html");
				$content = include "mod/catalog/show.php";
			} else {
				$title = "404 No encontrado";
				$content = "<p>No existe la página <code>$mid/".($ext?"$last.$ext":"")."</code> dentro de $actions de maquinaria.</p>";
			}
		}
	} else {
		if(!$last or $last=='index') {
			$content = "<nav>".product_types_ul($arbol)."</nav>";
			$content.= product_list(100,$section)."<hr class=clear>\n";
			$content.= bottomnav($action,"",$show);
		} elseif(array_key_exists($last,$arbol)) {
			redirect("/$action/$last/");
		} elseif(array_key_exists($last,$tipos)) {
			redirect("/$action/{$tipos[$last]}/$last.html");
		} elseif($last=='editar') {
			redirect("/$action/editar/");
		} elseif(is_numeric($last) && file_exists($fn="$catalog/$last.prod")) {
			dat2array($fn,$ar);
			product_data($last,$ar,$title,$seo);
			redirect("/$action/$seo/$last.html");
		} else {
			$title = "404 No encontrado";
			$content = "<p>No existe la página <code>".($ext?"$last.$ext":"")."</code> dentro de $actions de maquinaria.</p>";
		}
	}
	il_put('title',$title);
	return $content;
}

function bottomnav($action,$path,$show) {
	global $theuserlevel;
	if($theuserlevel>=2) {
		$showwhat = $show? 'solo activos': 'todos';
		$showhow  = $show? '': '?show=all';
		$showness = " <a href=\"/$action/$where$showhow\">(Ver $showwhat)</a>";
		$pub = "editar";
		$pubhow = "Publicar";
	} else {
		$showness = '';
		$pub = "enviar";
		$pubhow = "Enviar";
	} 
	$pubpath = "/$action/$pub/nueva.html";
	return "<nav id=maqbotnav><a href=\"$pubpath\">($pubhow nueva maquinaria)</a>$showness</nav>\n";
}


?>