<?php
if(!isset($GLOBALS['sitename'])) header("Location: ../media/");

require_once 'media/upload.php';

$imgexts=array('png','jpg','jpeg','gif');
$imgindex=array('index.jpg','thumb.jpg');

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

function listagaleria($base) {
	global $imgexts,$imgindex,$dir;
	$dd = array();
	if(is_dir($base)) {
		$d = dir($base);
		while (false !== ($entry = $d->read())) {
			$ext = ($r = strrpos($entry,'.'))? strtolower(substr($entry,$r+1)): '';
			if(in_array($ext,$imgexts) && !in_array($entry,$imgindex)) {
				$dd[] = $entry;
			}
		}
		$d->close();
		sort($dd);
	}
	return $dd;
}

function galeria($n,$t='default') {
	global $imgexts,$dir;
	$base = "media/$n";
	$idx = "$base/index.jpg";
	if(!file_exists($idx))
		$idx = "media/$t.png";
	if(!file_exists($idx))
		$idx = 'media/default.png';
	$dd = listagaleria($base);
	$c = "\n\t<aside class=gallery-frame>\n\t\t<div class=gallery-panel id=gallery-$n><img src=\"$dir/$idx\"></div>";
	$mid = $n;
	if(count($dd)) {
		$c.= "\n\t\t<ol class=gallery>";
		foreach($dd as $im) {
			$ims = "$dir/$base/$im";
			$imt = file_exists("$base/48/$im") ? "$dir/$base/48/$im" : $ims;
			$imm = file_exists("$base/300/$im") ? "$dir/$base/300/$im" : $ims;
			list($w,$h) = getimagesize($_SERVER['DOCUMENT_ROOT'].$imm);
			if($w!=300 || $h!=225) {
				$hh = 2*(int)($h*112.5/$w);
				$dy = (225-$hh)/2;
				$midstyle=" style=\"height:{$hh}px;top:{$dy}px;\"";
			} else $midstyle="";
			$hr = "$dir/galeria/$mid/$im";
			$c.= "\n\t\t\t<li><a href=\"$hr\"><img class=thumb src=\"$imt\"><img class=midsize src=\"$imm\"$midstyle></a></li>";
		}
		$c.= "\n\t\t</ol>";
	} else {
		$c.="<div class=gallery></div>";
	}
	$c.= "\n\t</aside>\n";
	return $c;
}

function mosaicogaleria($n=100) {
	global $imgexts,$dir,$tipospl;
	$c = "\n\t<ol class=index id=gal-index>\n";
	$d = dir("media");
	$dd = array();
	while (false !== ($entry = $d->read())) {
		if(is_dir("media/$entry")) {
			if(is_numeric($entry))
				$dd[]=$entry;
		}
	}
	$d->close();
	sort($dd);
#	$c.= "<li>".implode(',',$dd)."</li>";
	$i=0;
	foreach($dd as $k) {
		$ar=array();
		$src="";
		dat2array("maquinaria/$k.dat",$ar);
		if(isset($ar['status']) && $ar['status']==='0') continue;
		if(file_exists($fn="media/$k/thumb.jpg")) {
			$src="$dir/$fn";
			$style="";
		} elseif(file_exists($fn="media/$k/index.jpg")) {
			$src="$dir/$fn";
			$style="";
		} elseif(is_dir("media/$k/48")) {
			$d = dir("media/$k/48");
			while (false !== ($en = $d->read())) {
				$enp = explode('.',$en);
				$enx = strtolower(array_pop($enp));
				if(in_array($enx,$imgexts)) {
					$src="$dir/media/$k/48/$en";
					$style=" style=\"width:48px;height:48px;padding:51px 76px;\"";
					break;
				}
			}
		}
		if($src)
			$c.= "\t\t<li><a href=\"$dir/galeria/$k.html\"><img src=\"$src\"$style>{$ar['title']}</a></li>\n";
		else $c.="\t\t<li>$k</li>\n";
		if(++$i>=$n) break;
	}
	$c .= "\t</ol>\n";
	if(!$i) {
		$c= <<<AAA

	<article><p>No existe aún una galería de fotos.</p></article>

AAA;
		setarea('left');
		setarea('right');
	}
	return $c;
}

$paths[] = 'galeria';
$paths[] = 'media';
function content_galeria($id,&$content,&$title) {
	global $dir,$mid,$ext;
	if(!$mid) {
		if($id=='index') {
			$title = "Galería de imágenes";
			$content = mosaicogaleria();
		} elseif(is_dir("media/$id")) {
			header("Location: $dir/galeria/$id/");
		} else {
			$base = "media";
			$dd = listagaleria($base);
			$tit = "$id.$ext";
			if(in_array($tit,$dd)) {
				$title = "Imagen $tit";
				$i = array_search($tit,$dd);
				$prev = $i>0? "<a class=\"but prev\" href=\"$dir/galeria/".$dd[$i-1]."\" title=\"Anterior\">&#9664;</a>": "";
				$next = $i+1<count($dd)? "<a class=\"but next\" href=\"$dir/galeria/".$dd[$i+1]."\" title=\"Siguiente\">&#9654;</a>": "";
				$src = "$dir/media/$tit";
				$content = "<nav><a href=\".\">Galería</a></nav>\n";
				$content.= "<article class=centered>$prev<a href=\"$src\"><img src=\"$src\"></a>$next</article>\n";
			} else {
				$content = print_r($dd,true);
			}
		}
	} elseif(is_dir("media/$mid")) {
		if($id=='index') {
			if(file_exists($dat="maquinaria/$mid.dat")) {
				dat2array($dat,$ar);
				$action = isset($ar['action']) && $ar['action']? $ar['action']: 'venta';
				$title = "Galería para ".$ar['title'];
			} else {
				$action = 'venta';
				$title = "Galería para $mid";
			}
			$links = "<a href=\"$dir/$action/$mid.html\">Ver ficha de la maquinaria</a>";
			if(isset($_SESSION['level']) && (int)$_SESSION['level']>=2)
				$links.= " - <a href=\"$dir/$action/edit/$mid.html\">Editar la maquinaria</a>";
			$content = "<article><nav class=breadcrumbs><a href=\"..\">Galería</a></nav>".galeria($mid)."<hr class=clear><nav>$links</nav></article>";
		} elseif(file_exists($fn="media/$mid/$id.$ext")) {
			if(file_exists($dat="maquinaria/$mid.dat")) {
				dat2array($dat,$ar);
				$action = isset($ar['action']) && $ar['action']? $ar['action']: 'venta';
				$back = "Galería para ".$ar['title'];
			} else {
				$action = 'venta';
				$back = "Galería para $mid";
			}
			$base = "media/$mid";
			$dd = listagaleria($base);
			$i = array_search("$id.$ext",$dd);
			$prev = $i>0? "<a class=\"but prev\" href=\"$dir/galeria/$mid/".$dd[$i-1]."\" title=\"Anterior\">&#9664;</a>": "";
			$next = $i+1<count($dd)? "<a class=\"but next\" href=\"$dir/galeria/$mid/".$dd[$i+1]."\" title=\"Siguiente\">&#9654;</a>": "";
			$fnf = "$base/full/$id.$ext";
			$src = "$dir/$fn";
			$hr = file_exists($fnf) ? "$dir/$fnf" : "$dir/$fn";
			$tit = "$mid/$id.$ext";
			$title = "Imagen $tit";
			$links = "<a href=\"$dir/$action/$mid.html\">Ver ficha de la maquinaria</a>";
			if(isset($_SESSION['level']) && (int)$_SESSION['level']>=2)
				$links.= " - <a href=\"$dir/$action/edit/$mid.html\">Editar la maquinaria</a>";
			$content = "<nav><a href=\"..\">Galería general</a> » <a href=\".\">$back</a></nav>\n";
			$content.= "<article class=centered>$prev<a href=\"$hr\"><img src=\"$src\"></a>$next<nav>$links</nav></article>\n";
		} else {
			$title = "404 No encontrado";
			$content = "<div>No existe el archivo <code>$mid/$id.$ext</code></div>";
		}
	} elseif(file_exists($fn="maquinaria/$mid.dat")) {
		dat2array($fn,$ar);
		$title = "Galería para ".$ar['title'];
		$content = "<article><nav class=breadcrumbs><a href=\"..\">Galería</a></nav><div>No existe galería para {$ar['title']}</div>".galeria($mid)."<hr class=clear></article>";
	} else {
		$title = "404 No encontrado";
		$content = $id=='index'?
			"<div>No existe la carpeta <code>$mid</code> de medios.</div>":
			"<div>No existe el archivo <code>$mid/$id.$ext</code></div>";
	}
	unsetarea('site');
}
function content_media($id,&$content,&$title) {
	global $dir,$mid,$ext;
	$redir = "$dir/galeria/";
	if($mid) $redir.="$mid/";
	if($ext) $redir.="$id.$ext";
	header("Location: $redir");
}

?>