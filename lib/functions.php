<?php
// is_prefix checks if string $a match the first characters of string $b
function is_prefix($a,$b) {
	return substr($b,0,strlen($a))==$a;
}

// ensure_path makes shoure that the physical path to $path exists
// otherwise it creates it recursively
function ensure_path($path) {
	if(is_array($path)) {
		$d = '';
		foreach($path as $sd) {
			$d.= $sd.'/';
			if(!is_dir($d)) mkdir($d);
		}
	} else {
		$d = "$path";
		if(!is_dir($d)) mkdir($d);
	}
	return $d;
}

// ensure file makes sure that $filepath exists where $filepath is
// either a string with path and filename or an array of tockens
// for the path and filename.
function ensure_file($filepath) {
	if(is_array($filepath)) {
		$fn = array_pop($filepath);
		$d = ensure_path($filepath);
	} else {
		$path = explode('/',"$filepath");
		$fn = array_pop($path);
		$d = ensure_path($path);
	}
	$cfn = $d.$fn;
	file_put_contents($cfn,"#$fn\n");
	return $cfn;
}

function listdir($path,$ordered=0,$recursive=false,$rlevel=10) {
	if(!is_dir($path))
		return Null;
	$r = array();
	$d = dir($path);
	while(false!==($e=$d->read())) {
		$pe = "$path/$e";
		$r[$e]=array($pe,substr($e,0,1)=='.',is_dir($pe));
		if($recursive && $rlevel>0 && !$r[$e][1] && $r[$e][2])
			$r[$e][3] = listdir($pe,$ordered,true,$rlevel-1);
	}
	if($ordered>0) ksort($r);
	elseif($ordered<0) krsort($r);
	return $r;
}

function str2uri($str,$esp='-') {
	return preg_replace(
		array(
			'{\s+|\xc2\xa0}',
			'{\xc2\xa3}',
			'{\xc2\xa7}',
			'{\xc2\xad}',
			'{\xc2\xae}',
			'{\xc2\xb2}',
			'{\xc2\xb3}',
			'{\xc2\xb6}',
			'{\xc2\xb9}',
			'{\xc3\x9f}',
			'{\xc3[\xa0-\xa5]|\xc2\xaa}',
			'{\xc3\xa6}',
			'{\xc3\xa7|\xc2[\xa2\xa9]}',
			'{\xc3[\xa8-\xab]}',
			'{\xc3[\xac-\xaf]}',
			'{\xc3\xb0}',
			'{\xc3\xb1}',
			'{\xc3[\xb2-\xb6\xb8]|\xa2\xba}',
			'{\xc3[\xb9-\xbc]|\xc2\xb5}',
			'{\xc3[\xbd\xbf]|\xc2\xa5}',
			'{\xc3\xbe}',
			'{\xc2[\xa1\xbf]}',
			'{[\xc4\xdf][\x91\xbf]}',
			'{[\xe0\xef][\x91\xbf][\x91\xbf]}',
			'{[\xf0\xf7][\x91\xbf][\x91\xbf][\x91\xbf]}',
			'{[^-./0-9_a-z]}',
			'{-*_+-*}','{-*/+-*}','{^-+|-+$}','{--+}'
			),
		array(
			$esp,'l','s','','r','2','3','p','1','ss',
			'a','ae','c','e','i',
			'd','n','o','u','y',
			'th','-','-','-','-',
			'-','_','/','','-'
			),
		mb_convert_case($str,MB_CASE_LOWER,'UTF-8'));
}

function dat2array($file,&$ar,$spanned=false) {
	$f=file($file);
	$ar[$g="status"]='';
	foreach($f as $l) {
		if(($p=strpos($l,':'))!==false) {
			$ar[$g = trim(substr($l,0,$p))] = str_replace('\x3a',':',trim(substr($l,$p+1)));
		} else {
			if($g=='status' && $ar[$g]) $ar[$g='text']='';
			$ar[$g].="\n".str_replace('\x3a',':',trim($l));
		}
	}
	if(!isset($ar['text'])) $ar['text']="";
	elseif($spanned) $ar['text'] = "<span>".str_replace("\n","</span>\n<span>",trim($ar['text']))."</span>";
}

function array2dat($file,$ar,$order=array('status'),$deny=array('line')) {
	$t = "";
	foreach($order as $key)
		if(isset($ar[$key])&&($val=trim($ar[$key]))!=='')
			if(strrpos($val,"\n")) {
				$val = str_replace(':','\x3a',$val);
				$t.="$key:\n$val\n";
			}
			else $t.="$key:$val\n";
	foreach($ar as $key=>$val)
		if(!in_array($key,$order) && !in_array($key,$deny))
			if(strrpos($val,"\n")) {
				$val = str_replace(':','\x3a',$val);
				$t.="$key:\n$val\n";
			}
			else $t.="$key:$val\n";
	$fp = fopen($file,'w');
	fwrite($fp, $t);
	fclose($fp);
}

function onuserlevel($minuserlevel,$redirecturi='/',$group='this_site') {
	$ugrd = il_get2('user','group',null);
	if(isset($ugrd['this_site']) && $ugrd['this_site']>=$minuserlevel)
		return;
	if(isset($ugrd[$group]) && $ugrd[$group]>=$minuserlevel)
		return;
	redirect($redirecturi);
}

function checkorredirect($url,$pattern=null,$transform=false) {
	$cannon = il_line_get('cannon');
	if(substr($url,-2)=='.*')
		$url = substr($url,0,-1).il_line_get('extension','html',true);
	if($pattern) {
		if(!preg_match($pattern,$cannon))
			redirect($transform? preg_replace($transform,$url,$cannon): $url);
	} else {
		if(ltrim($cannon,'/') != ltrim($url,'/'))
			redirect($url);
	}
}
// alias for http_redirect. Allows to comment out the actual redirect for debugging processes
function redirect($url, $params=null, $session=false, $status=0) {
	if(!$params) $params=array();
	if(function_exists('http_redirect')) return http_redirect($url,$params,$session,$status);
	while(@ob_end_clean());
	if(preg_match('{^\w+://}',$url)) {
		$fullurl = $url;
	} else {
		$protocol = empty($_SERVER['HTTPS']) || $_SERVER['HTTPS']!='on' ? 'http://': 'https://';
		$host = $_SERVER['HTTP_HOST'];
		if(substr($url,0,1)=='/') {
			$fullurl = $protocol.$host.$url;
		} else {
			$dir = rtrim(dirname($_SERVER['PHP_SELF']), '/\\');
			$cpi = pathinfo(il_get2('line','cannon'));
			$dir = (isset($cpi['dirname']) || $cpi['dirname']='.' || $cpi['dirname']='/' || $cpi['dirname'] = '\\')?
				'/': '/'.$cpi['dirname'].'/';
			$fullurl = $protocol.$host.$dir.$url;
		}
	}
	header("Location: $fullurl");
	die( "Redirect to <a href=\"$fullurl\">$url</a> (from ".il_line_get('cannon').")" );
}

function make_section($content,$title=null,$level=2,$title_link=null,$class=null,$id=null) {
	$s = "<section";
	if($class) {
		$s.= " class=";
		if(is_array($class)) $s.='"'.implode(' '.$class).'"';
		else $s.="$class";
	}
	if($id) $s.=" id=$id";
	$s.=">\n";
	if($title) {
		$s.="<h$level>";
		if($title_link) $s.="<a href=\"$title_link\">";
		$s.=$title;
		if($title_link) $s.="</a>";
		$s.="</h$level>\n";
	}
	$s.=$content;
	$s.="\n</section>\n";
	return $s;
}

function json_write($filename,$data,$options=0) {
	if(!file_exists($filename)) ensure_file($filename);
	file_put_contents($filename,json_encode($data,$options));
	return true;
}

function json_read($filename,$default=array()) {
	if(!file_exists($filename)) return $default;
	$r = json_decode(file_get_contents($filename),true);
	return isset($r)? $r: $default;
}

function array_mergeinto(&$array1,$array2) {
	return $array1 = array_merge($array1,$array2);
}

function update_avatar($avatar) {
	$name = $avatar['name'];
	$type = $avatar['type'];
	$file = $avatar['tmp_name'];

	$n = strrpos($name,'.');

	$lext = strtolower($ext = substr($name,$n+1));
	$base = substr($name,0,$n);
	$dir = 'media/avatar/';

	if(substr($type,0,6)!='image/')
		return array('error'=>'Wrong file type');
	if(!in_array(substr($type,6),array('png','gif','jpeg','pgpeg','x-png','jpg')))
		return array('error'=>'Wrong file type');
	if(!in_array($lext,array('png','gif','jpg','jpeg')))
		return array('error'=>'Wrong file extension');

	$rename = $name;
	for($i=0; file_exists($fnn = $dir.$rename); $rename="$base-".(++$i).".$ext");
	$n = strrpos($rename,'.');
	$base = substr($rename,0,$n);

	if($lext=='jpg') $lext = 'jpeg';
	$imagecreate = "imagecreatefrom$lext";
	$imagewrite = "image$lext";

	$src = $imagecreate($file);
	list($w,$h)=getimagesize($file);

	if($w>$h) {
		$nh1 = 24; $nw1 = 24*$w/$h;
		$nh3 = 180; $nw3 = 180*$w/$h;
		$xor = ($w-$h)/2;
		$yor = 0;
		$zor = $h;
	} else {
		$nw1 = 24; $nh1 = 24*$h/$w;
		$nw3 = 180; $nh3 = 180*$h/$w;
		$xor = 0;
		$yor = ($h-$w)/2;
		$zor = $w;
	}

	$tmp=imagecreatetruecolor($nw1,$nh1);
	imagecopyresampled($tmp,$src,0,0,0,0,$nw1,$nh1,$w,$h);
	if(!$imagewrite($tmp,$dir.($p1="$base.24.$ext"))) {
		return array('error'=>"File $p1 could not be created.");
	}
	if($ext!='png') imagepng($tmp,$dir.($p0="$base.24.png"));
	else $p0=$p1;
	imagedestroy($tmp);

	$tmp=imagecreatetruecolor(48,48);
	imagecopyresampled($tmp,$src,0,0,$xor,$yor,48,48,$zor,$zor);
	if(!$imagewrite($tmp,$dir.($p2="$base.48.$ext"))) {
		return array('error'=>"File $p2 could not be created.");
	}
	imagedestroy($tmp);

	$tmp=imagecreatetruecolor($nw3,$nh3);
	imagecopyresampled($tmp,$src,0,0,0,0,$nw3,$nh3,$w,$h);
	if(!$imagewrite($tmp,$dir.($p3="$base.180.$ext"))) {
		return array('error'=>"File $p3 could not be created.");
	}
	imagedestroy($tmp);

	if(!$imagewrite($src,$dir.$rename)) {
		return array('error'=>"File $rename could not be created.");
	}
	imagedestroy($src);

	db_insert('avatar',array('basedir'=>"/$dir",'tiny'=>$p0,'small'=>$p2,'big'=>$p3,'full'=>$rename));

	$r = db_select_key('avatar','idx',array('full'=>$rename));

	$r['note'] = "El archivo <code>$name</code> es válido y fue cargado exitosamente como <a href=\"/$dir$rename\">$rename</a>.\n";
	return $r;
}

?>
