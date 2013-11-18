<?php

function makeroot($m,&$c,&$t) {
	global $phy_base;
	$p = $m['suf']? $m['suf']: 'index';
	$q = $m['query'];
	if($n = strrpos($p,'.')) {
		$pb = substr($p,0,$n);
		$px = ext_type(substr($p,$n+1));
	} else {
		$pb = $p;
		$px = 'html';
	}
	if("$pb.$px" == "index.html" && $m['suf']) redirect('');
	$pd = str_replace('/','--',$pb);
	$fun = "convert_meta_$px";
	$dir = "$phy_base/data/base/";
	if($m['path']) $dir.= $m['path'].'/';
	if(file_exists($fn="$dir$pd.php")) {
		require $fn;
		if(isset($content)) $c=$content;
		elseif(isset($text))$c = function_exists($fun)? $fun($text):
			"<p class=error>Unable to output a <code>$px</code> file.</p>\n";
		else $c="Documento en blanco.";
		if(isset($title)) $t = $title;
		else $t = $p;
	} elseif(file_exists($fn="$dir$pd.dat")) {
		$f = file($fn);
		$t = trim(array_shift($f));
		$c = function_exists($fun)? $fun(implode($f)):
			"<p class=error>Unable to output a <code>$px</code> file.</p>\n";
	} elseif(file_exists($fn="$dir$pd.htm")) {
		$c = implode(file($fn));
	} elseif(file_exists($fn="$dir$pd.txt")) {
		$c = "<p class=text>".str_replace(array("\n","\r\n"),"<br>\n",implode(file($fn)))."</p>\n";
	} else {
		$m['error'] = 404;
		makestatus($m,$c,$t,true);
	}
}

function forbidden($m,&$c,&$t) {
	makestatus(array('error'=>403),$c,$t,true);
}

function makestatus($m,&$c,&$t,$header=false) {
	global $uri_script,$phy_base;
	if(isset($m['error'])) $er = (int)$m['error'];
	elseif(is_numeric($m['suf'])) $er = (int)$m['suf'];
	else $er=999;
	if(file_exists($fn = "$phy_base/data/base/status/$er")) {
		$f = file($fn);
		$h = trim(array_shift($f));
		$t = trim(array_shift($f));
		if($header) header("HTTP/1.0 $er $h");
		$c = "";
		while($f) {
			$l = trim(array_shift($f));
			if($l) $c.="<p class=error>$l</p>";
		}
		$c = str_replace('{request}',$uri_script,$c);
	} else {
		if($header) header("HTTP/1.0 $er");
		if($er>=400 && $er<600) {
			$t = "Error $er";
		} else {
			$t = "Estado $er";
		}
	}
}

?>
