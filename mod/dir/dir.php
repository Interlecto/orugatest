<?php

function makedir($m,&$c,&$t) {
	global $phy_base,$uri_base;
	$p = $m['path'];
	$s = $m['suf'];
	$db = $s? "$p/$s": $p;
	if(substr($db,-1)=='/') $db=substr($db,0,-1);
	$dn = "$phy_base/$db";
	$dr = "$uri_base/$db";
	if(is_dir($dn)) {
		$d = dir($dn);
		$c = "<ul>\n";
		$c.= "<li class=dir><a href=\"$dr/..\"><em>[Parent]</em></a></li>\n";
		$dd = array();
		while($e=$d->read()) {
			if(substr($e,0,1)=='.') continue;
			$dd[] = $e;
		}
		sort($dd);
		foreach($dd as $e) {
			$fr = "$dr/$e";
			$fn = "$dn/$e";
			if(is_dir($fn)) {
				$fx = 'dir';
				$e.= '/';
			} else {
				$n = strrpos($e,'.');
				$fx = $n? ext_type(substr($e,$n+1)): 'file';
			}
			$c.="<li class=$fx><a href=\"$fr\">$e</a></li>\n";
		}
		$c.= "</ul>\n";
	} else {
		makestatus(404,$c,$t);
		$c.= $dn;
	}
}

?>
