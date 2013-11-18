<?php

function il_cache_path_check($line,$path=array(),$output=false,$def='index.html') {
	$fn = str_replace('/','--',$line);
	if(!$fn) $fn = $def;
	$dir = 'cache/'.implode('/',$path);
	if(file_exists($ffn="$dir/$fn")) {
		if($output) {
			while(@ob_end_clean());
			readfile($ffn);
			exit;
		}
		return file_get_contents($ffn);
	}
	return null;
}

function il_cache_path_set($line,$what,$path=array()) {
	$fn = str_replace('/','--',$line);
	array_unshift($path,'cache');
	$dir = ensure_path($path);
	file_put_contents($dir.$fn,$what);
}

function il_cache_check($line,$template='default') {
	il_cache_path_check($line,array('files',$template),true);
}

function il_cache_set($line,$content,$template='default') {
	il_cache_path_set($line,$content,array('files',$template));
}

function il_cache_content_get($line,$format='html5') {
	return il_cache_path_check($line,array('content',$format));
}

function il_cache_content_set($line,$content,$format='html5') {
	il_cache_path_set($line,$content,array('content',$format),true);
}

function il_cache_area_get($area,$template='default') {
	return il_cache_path_check($area,array('template',$template));
}

function il_cache_area_set($area,$content,$template='default') {
	il_cache_path_set($area,$content,array('template',$template),true);
}

?>