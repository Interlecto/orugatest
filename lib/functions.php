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

// ensure_file makes sure that $filepath exists where $filepath is
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

// listdir reads a directory and builds an array with the results
// which includes complete paths and filetype and can recurse into
// subdirectories
// $path      is the path to the directory, it must be a string.
// $ordered   determines if the array should be ordered by filenames
//            True or a possitive number gives direct order
//            a negative number indicates reverse order
//            False or 0 if no order is needed (used system default)
// $recursive must be set to true to recurse into dubdirectories
// $rlevel    Means how many levels of recursion are allowed.
//            it always execute once. 0 (or negative) list only
//            $path directory. 1 recurses once, etc.
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

// str2uri cleans a string into an ASCII all-lowercase uri-safe string
// $str  is the string to convert
// $esp  (op) determines how to convert spaces, defaults to '-'.
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

// dat2array converts the contents of a propietary .dat file into an
// array.  Most .dat files have either be replaced to .json files or
// info stored in the database.
// $file    is the filename (and path) of the .dat file
// $ar      is the array to fill.  This function adds into existing
//          information.
// $spanned must be set to true if lines in final text must be
//          surrounded by <span> HTML tags.
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

// array2dat writes down a file with propietary .dat 
// $file  is the filename (and path) of the .dat file
// $ar    is the array from where the data is taking from
// $order is an array containing which fields, if exist, must be
//        stored first (in the given order)
// $deny  is a list of fields that should not be stored in the .dat
//        file.
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

// onuserlevel checks if user is an administrator or if s/he has enough
// user level in a group to look at certain content.
// $minuserlevel is the minimum user level required
// $redirecturi  is the URI to which the page redirects if the user
//               does not meet the required user level
// $group        is the group to which the user level is compared to
function onuserlevel($minuserlevel,$redirecturi='/',$group='this_site') {
	$ugrd = il_get2('user','group',null);
	if(isset($ugrd['this_site']) && $ugrd['this_site']>=8)
		return;
	if(isset($ugrd[$group]) && $ugrd[$group]>=$minuserlevel)
		return;
	redirect($redirecturi);
}

// checkorredirect ensures the uri path is appropiate for the current
// request.  This particularly fixes file extentions and alternative
// formats so that request to the same resource all use the same URI.
// $url       is the required URI, it is either a string already
//            formated to the desired uri, or in combination to
//            $pattern, it is the trasnformation target.
//            if extension is unimportant, $uri should end in '.*'
// $pattern   if given it is a preg regex to which the current URI is
//            compared.  If current URI matches $pattern no redirection
//            is performed.
// $transform If given, this is a preg regex that finds the pattern
//            that should be found and replaced by $url in the current
//            URI.
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

// alias for http_redirect. Allows to comment out the actual redirect
// for debugging processes
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

// This function souround $content into HTML <section> tags.
// the aditional parameters include:
// $title      if present, it adds a heading to the section.
// $level      it is the heading level of the title.
// $title_link if present, it adds a HREF anchor to the title.
// $class      if present it adds this as a class to the section.
//             it can either be a string or an array of strings (clases)
// $id         if present, it provides an id for the section.
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

// json_write takes any $data and writes down it in JSON format
// in file $filename.
// $options are pased as $options for the json_encode rutine.
function json_write($filename,$data,$options=0) {
	if(!file_exists($filename)) ensure_file($filename);
	file_put_contents($filename,json_encode($data,$options));
	return true;
}

// json_read opens a JSON file $filename and returns an assosiative
// array with the date in it.
// on failure it returns $default (which defaults to an empty array)
function json_read($filename,$default=array()) {
	if(!file_exists($filename)) return $default;
	$r = json_decode(file_get_contents($filename),true);
	return isset($r)? $r: $default;
}

// array_margeinto adds the filds of second parameter $array2 into
// first parameter $array1, modifying $array1 as well as returning it.
function array_mergeinto(&$array1,$array2) {
	return $array1 = array_merge($array1,$array2);
}

// update_avatar recieves an array including avatar data from an
// uploaded file and writes down in the filesystem the image as well
// as it creates a new entry in the database for this new file.
// It returns the new entry index as well as any error or success
// message of the operation in an array.
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
