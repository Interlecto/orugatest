<?php

function make_root($line) {
	$pre = il_line_get('prefix');
	$nom = il_line_get('nominal');
	$ext = il_line_get('extension');
	$fil = il_line_get('dashed');
	$tit = il_line_get('spaced');
	set_area('left');
	set_area('right');

	$dd = $nom? "data/content/$nom/": 'data/content/';
	il_default('title', mb_convert_case($nom?"$nom: $tit":$tit,MB_CASE_TITLE,'UTF-8'));
	if(file_exists($fn="$dd$fil.info")) {
	} elseif(file_exists($fn="$dd$fil.php")) {
		return include $fn;
	} elseif(file_exists($fn="$dd$fil.ilm")) {
		return ilm2html(file($fn));
	} elseif(file_exists($fn="$dd$fil.htm")) {
		return file_get_contents($fn);
	} else {
		return make_status($line,404);
	}/*
	$s = $pre? "<h2>$pre / $line</h2>\n": "<h2>$line</h2>\n";
	$s.= "<pre>".print_r(il_line_get('matches'),true)."</pre>\n";
	$s = "<p>$nom / $fil . $ext</p>\n";
	$s.= "<pre style=\"font-size:.8em\">".print_r($GLOBALS,true)."</pre>\n";
	return $s;*/
	return '';
}

function make_status($line,$status=null) {
	if(!$status) {
		if(is_numeric($line)) {
			$status = (int)$line;
			$desc = il_get2('status',$status);
		} else {
			$status = 404;
			$desc = $line;
		}
	} else {
		$desc = $line==index? il_get2('status',$status): $line;
	}
	if(!$desc)
		return reply_status(404,null,"Status $status not found");
	il_put('title',$desc);
	if(file_exists($fn="data/status/$status.php"))
		return include $fn;
	if(file_exists($fn="data/status/$status.html"))
		return file_unbrace($fn);
	if(file_exists($fn="data/status/$status.ilm"))
		return ilm2html($fn);
	set_area('left');
	set_area('right');
	return "<p class=advice>Status: $status $desc.</p>";
}

function ilm2html($lines) {
	if(is_string($lines)) $lines=explode("\n",$lines);
	il_put('title',trim(array_shift($lines)));
	$text = implode("</p>\n<p>",$lines);
	return "\n<p>$text</p>\n";
}

static $response_codes = array(
	100 => "Continue",
	101 => "Switching Protocols",
	200 => "OK",
	201 => "Created",
	202 => "Accepted",
	203 => "Non-Authoritative Information",
	204 => "No Content",
	205 => "Reset Content",
	206 => "Partial Content",
	207 => "Multi-Status",
	208 => "Already Reported",
	226 => "IM Used",
	230 => "Authentication Successful",
	300 => "Multiple Choices",
	301 => "Moved Permanently",
	302 => "Found",
	303 => "See Other",
	304 => "Not Modified",
	305 => "Use Proxy",
	306 => "Switch Proxy",
	307 => "Temporary Redirect",
	308 => "Permanent Redirect",
	400 => "Bad Request",
	401 => "Unauthorized",
	402 => "Payment Required",
	403 => "Forbidden",
	404 => "Not Found",
	405 => "Method Not Allowed",
	406 => "Not Acceptable",
	407 => "Proxy Authentication Required",
	408 => "Request Timeout",
	409 => "Conflict",
	410 => "Gone",
	411 => "Length Required",
	412 => "Precondition Failed",
	413 => "Request Entity Too Large",
	414 => "Request-URI Too Long",
	415 => "Unsupported Media Type",
	416 => "Requested Range Not Satisfiable",
	417 => "Expectation Failed",
	418 => "I'm a teapot",
	420 => "Enhance Your Calm",
	422 => "Unprocessable Entity",
	423 => "Locked",
	424 => "Failed Dependency",
	424 => "Method Failure",
	425 => "Unordered Collection",
	426 => "Upgrade Required",
	428 => "Precondition Required",
	429 => "Too Many Requests",
	431 => "Request Header Fields Too Large",
	444 => "No Response",
	449 => "Retry With",
	450 => "Blocked by Windows Parental Controls",
	451 => "Unavailable For Legal Reasons",
	451 => "Redirect",
	494 => "Request Header Too Large",
	495 => "Cert Error",
	496 => "No Cert",
	497 => "HTTP to HTTPS",
	499 => "Client Closed Request",
	500 => "Internal Server Error",
	501 => "Not Implemented",
	502 => "Bad Gateway",
	503 => "Service Unavailable",
	504 => "Gateway Timeout",
	505 => "HTTP Version Not Supported",
	506 => "Variant Also Negotiates",
	507 => "Insufficient Storage",
	508 => "Loop Detected",
	509 => "Bandwidth Limit Exceeded",
	510 => "Not Extended",
	511 => "Network Authentication Required",
	598 => "Network read timeout error",
	599 => "Network connect timeout error",
);
foreach($response_codes as $c=>$d) {
	il_put2('status',$c,$d);
}

function reply_status($code,$name=null,$title=null,$text=null) {
	if(!($desc = il_get2('status',$code)))
		return null;
	if(!$name) $name = $desc;
	if(!$title) $title = $desc;
	if(!$text) $text = $title.'.';
	il_put('title',$title);
	header(sprintf('HTTP/1.1 %03d %s',$code,$name));
	header(sprintf('Status: %03d %s',$code,$name));
	set_area('left');
	set_area('right');
	return (substr($text,0,1)=='<' || il_get('type','html')!='html')? $text: "<p class=advice>$text</p>";
}


?>
