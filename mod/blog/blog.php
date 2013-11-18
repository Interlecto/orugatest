<?php

il_put2('blog','size',5);

function articleurl($n,$ar,$x="") {
	global $dir;
	if(!$x)$x=$n;
	$ntitle = isset($ar['title'])&&$ar['title'] ? $ar['title'] : (
		isset($ar['status'])&&$ar['status'] ? $ar['status'] : $x);
	return "$dir/notas/".str2uri($ntitle)."/$n.html";
}

function articlemore($n,$ar) {
	return '<a class=readmore href="'.articleurl($n,$ar).'">... (leer más)</a>';
}

function articlecontent($n,$ar,$default="",$summary=false) {
	if($summary) {
		if(isset($ar['summary']) && $ar['summary']) return $ar['summary'].articlemore($n,$ar);
		if(isset($ar['hhtml']) && $ar['hhtml']) return $ar['hhtml'].articlemore($n,$ar);
		if(isset($ar['htext']) && $ar['htext']) return $ar['htext'].articlemore($n,$ar);
	}
	if(isset($ar['html']) && $ar['html']) return $ar['html'];
	if(isset($ar['text']) && $ar['text']) return $ar['text'];
	return $default;
}

function list_articles($off=0,$quan=null,$prev="",$next="",$summary=false,$hlevel=2) {
	if(!$quan) $quan = il_get2('blog','size',5);
	$d=dir('data/notes');
	$dd = array();
	while (false !== ($entry = $d->read())) {
		if(substr($entry,-5)==".note") {
			$dd[] = substr($entry,0,-5);
		}
	}
	$d->close();
	rsort($dd);
	
	$ans = "";
	$isprev=false;
	$isnext=false;
	foreach($dd as $i=>$fn) {
		if($i<$off) {
			$isprev=true;
			continue;
		}
		if($i>=$off+$quan) {
			$isnext=true;
			break;
		}
		$ar = array();
		dat2array("data/notes/$fn.note",$ar);
		$ntitle = !empty($ar['title']) ? trim($ar['title']) : (
			!empty($ar['status']) ? trim($ar['status']) : "Nota $fn");
		$nmid = str2uri($ntitle);
		$nlink = "/notas/$nmid/$fn.html";
		$ncont =  articlecontent($fn,$ar,"",$summary);
		$ans.= <<<THENOTE
<article class=note id=article-$i>
<header class=note-header>
<h$hlevel><a href="$nlink">$ntitle</a></h$hlevel>
</header>
<section class=note-body>
$ncont
</section>
<footer class=note-footer>
</footer>
</article>
THENOTE;
	}
	$ans.= $isprev? ($isnext? "\n<nav>$prev, $next</nav>": "\n<nav>$prev</nav>"):($isnext? "\n<nav>$next</nav>": "");
	return $ans;
}

function make_blog($line) {
	ob_start();
	$last = il_line_get('last');
	$mid = implode('/',il_line_get('path'));
	$ext = il_line_get('extension');
	if(empty($ext)) $ext='html';
	$blogsize = il_get2('blog','size',10);
	if($line=='index') {
		$title = "Últimos artículos";
		$content = list_articles(
			0,
			il_get2('blog','size',10),
			"",
			"<a href=\"/notas/page/1.html\">Artículos viejos</a>",
			true);
		set_area('left');
	} elseif($mid=='page') {
		$page = (int)$last;
		if(!$page) redirect('/notas/');
		$title = "Artículos: página $page";
		$content = list_articles(
			$blogsize*$page,
			$blogsize,
			"<a href=\"/notas/page/".($page-1).".html\">Artículos posteriores</a>",
			"<a href=\"/notas/page/".($page+1).".html\">Artículos anteriores</a>",
			true);
		set_area('left');
	} elseif(file_exists($fn="data/notes/$last.note")) {
		dat2array($fn,$ar);
		$title = !empty($ar['title']) ? trim($ar['title']) : (
			!empty($ar['status']) ? trim($ar['status']) : (
				!empty($mid)? $mid: "Nota $last"));
		$mmid = str2uri($title);
		if($mmid!=$mid) { 
			$red = "/notas/$mmid/$last.$ext";
			echo "Location: $red";
			redirect($red);
		}
		$content = '';
		$content = articlecontent($last,$ar,$content);
	} else {
		echo "<pre>".print_r($GLOBALS['il'],true)."</pre>\n";
		$title = "404 No encontrado";
		$content = "<p>El artículo <code>$last.$ext</code> no existe.</p>";
		set_area('left');
		header($_SERVER["SERVER_PROTOCOL"].' 404 Not Found');
	}
	il_put('title',$title);
	return $content.ob_get_clean();
}



?>