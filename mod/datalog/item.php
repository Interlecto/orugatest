<?php

// URI cases:

//	/item/base/itemid/timestamp	one status
//	/item/base/itemid/feed.ext	list of statuses.  if ext='json', is a JSON formated db of last 100 statuses, otherwise is a template-formated printed list of las 20 statuses
//	/item/base/itemid/feed.ext?page=n&len=m	list of statuses.  from the m*n+1-th to te m*(n+1)-th in reverse order (m defaults to 20, and n defaults to 0)
//	/item/base/itemid/	item profile
//	/item/base/itemid/lines.ext	line graph representing values

//	/item/base/timestamp	status at timestamp
//	/item/base/feed.ext	list of statuses.  if ext='json', is a JSON formated db of last 100 statuses, otherwise is a template-formated printed list of las 20 statuses
//	/item/base/feed.ext?page=n&len=m	list of statuses.  from the m*n+1-th to te m*(n+1)-th in reverse order (m defaults to 20, and n defaults to 0)
//	/item/base/	facility profile
//	/item/base/lines.ext	line graph representing values

//	/item/timestamp	status at timestamp
//	/item/feed.ext	list of statuses.  if ext='json', is a JSON formated db of last 100 statuses, otherwise is a template-formated printed list of las 20 statuses
//	/item/feed.ext?page=n&len=m	list of statuses.  from the m*n+1-th to te m*(n+1)-th in reverse order (m defaults to 20, and n defaults to 0)
//	/item/	facility profile
//	/item/lines.ext	line graph representing values

function make_item($line) {
	set_area('left');
	set_area('right');

	$path = il_line_get('path');
	$last = il_line_get('last','index',true);
	$ext = il_line_get('extension','html',true);
	$base = isset($path[0])? $path[0]: null;
	$item = isset($path[1])? $path[1]: null;
	$user = isset($_SESSION['user'])? $_SESSION['user']: null;

	switch($last) {
	case 'feed':
		return $ext=='json'? json_feed($user,$base,$item): item_feed($user,$base,$item);
	case 'index':
		if($item) return require 'item_profile.php';
		if($base) return require 'item_list.php';
		//if($base) return item_list($user,$base);
		if($user) redirect('/mine.'.$ext);
		redirect('/item/feed.'.$ext);
	case 'graph':
	case 'day':
	case 'week':
	case 'month':
	case 'year':
	case 'custom':
		return require "item_graph.php";
		return item_graph($user,$base,$item,$last);
	case 'edit':
		if($item) return require 'item_edit.php';
		if($base) return require 'base_edit.php';
		redirect('/item/feed.'.$ext);
		return item_edit($user,$base,$item,$last);
	case 'create':
		return item_create($user,$base,$item,$last);
	case 'graphdata':
		return item_hist_data_sum($user,$base,$item,$last);
	case 'alldata':
		return item_hist_data_all($user,$base,$item,$last);
	default:
		if(preg_match('{(!?)(\d+)}',$last,$m)) {
			$timestamp = !empty($m[1]);
			$statusid = (int)$m[2];
			return require 'item_status.php';
		}
	}
	return "base:'$base'<br>\nitem:'$item'<br>\nuser:'$user'<br>\nlast:'$last'<br>\next:'$ext'<br>\n";
}

function user_bases($user=null,$base=null) {
	$cu = il_get('user');
	if(empty($user)) $user = $cu;
	if(!empty($base)) {
		# if $base is set, check if $base is public,
		# then if user has declared right to $base,
		# then if $user has admin rights to $base
		# on success return an array contining $base
		# otherwise return an empty array.

		il_add('queries','comprobando base '.$base.chr(10));
		$r = il_select('dl_station',array('public','group'),array('station'=>"=$base"));
		if(empty($r)) return array();
		if($r[0]['public']) return array($base);
		$group = $r? $r[0]['group']: '';

		$r = il_select('dl_userstation','*',array('user'=>"=$user",'station'=>"=$base"));
		if($r && count($r)) return array($base);

		$ur = il_select('user_group','role',array('user'=>"=$user",'group'=>"=$group"));
		if($ur && $ur[0]['role']>=7) return array($base);

		$uq = il_select('user_group','role',array('user'=>"=$user",'group'=>"=this_site"));
		if($uq && $uq[0]['role']>=7) return array($base);

		return array();
	} else {
		# if $base is null, then check first all
		# stations a user has been declared right to.
		# then all bases a user has admin right to.
		# finally, if user has no declared or admin right
		# find all public stations.
		$ans = array();

		$r = il_select('dl_userstation','station',array('user'=>"=$user"));
		if($r) foreach($r as $rl) $ans = array_merge($ans,array_values($rl));

		if($ug = il_select('user_group','*',array('user'=>"=$user")))
			foreach($ug as $gr)
				if($gr['role']>=7) {
					$group = $gr['group'];
					if($group=='this_site') {
						$q = il_select('dl_station','station');
					} else {
						$q = il_select('dl_station','station',array('group'=>"=$group"));
					}
					if($q) foreach($q as $qr) $ans = array_merge($ans,array_values($qr));
				}

		if(!count($ans)) {
			$q = il_select('dl_station','station','`public`');
			if($q) foreach($q as $qr) $ans = array_merge($ans,array_values($qr));
		}

		return $ans;
	}
}

function base_user_level($base,$user=null) {
	$cu = il_get('user');
	if(empty($user)) $user = $cu;

	$group = db_select_one('user_group','group',array('station'=>"=$base"));
	$ul = db_select_key('user_group','group',array('user'=>"=$user"));
	$aul = isset($ul['this_site'])? $ul['this_site']['role']: 0;
	$gul = isset($ul[$group])? $ul[$group]['role']: 0;
	return max($aul,$gul);
}

function common_feed($user=null,$base=null,$item=null,$max=3,$sid=null) {
	$ar = array("BASE"=>array(),"INST"=>array(),'STATUS'=>array());
	// **** get user and public base stations
	$bases = user_bases($user,$base);
	$m = 5*$max;

	$rr = array();
	foreach($bases as $bb) {
		$b = il_escape($bb);
		$br = il_select('dl_station',array('name','ip','group'),array('station'=>"=$bb")); if(!$br) $br = array(array());
		$bu = il_select('user',array('id','avatar','banner'),array('id'=>"=$bb")); if(!$bu) $bu = array(array());
		$ar['BASE'][$bb] = array_merge($br[0],$bu[0]);
		$where = array('station'=>"=$bb");
		if(!empty($item)) { $where['address'] = "=".(int)$item; $m/= 5; }
		if(!empty($sid)) $where['idx'] = "=".(int)$sid;
		$ra = il_select('dl_status_i','*',$where,'time',true,$m);# or array();
		$rb = array();
		foreach($ra as $sa) {
			$ad = $sa['address'];
			if(!isset($rb[$ad])) $rb[$ad] = 0;
			if(++$rb[$ad] > $max) continue;
			$rr[] = $sa;
			if(!isset($ar['INST'][$bb])) $ar['INST'][$bb] = array();
			if(!isset($ar['INST'][$bb][$i = $sa['address']])) {
				$ir = il_select('dl_instrument',array('address','reference','description','avatar'),array('station'=>"=$bb",'address'=>"=$i")) or array(array());
				$ip = il_select('dl_param',array('param','param_idx','value'),array('station'=>"=$bb",'address'=>"=$i")) or array(array());
				$ar['INST'][$bb][$i] = $ir[0];
				foreach($ip as $ipl) {
					$p = $ipl['param'];
					if($ipl['param_idx']!=1) $p.= '-'.$ipl['param_idx'];
					$ar['INST'][$bb][$i][$p] = $ipl['value'];
				}
			}
		}
	}

	foreach($rr as $rl) {
		$ro = il_select('dl_status_p',array('keyword','value'),array('status'=>'='.$rl['idx'])); 
		$rq = sprintf('%19s//%-12s//%03d',$rl['time'],$rl['station'],$rl['address']);
		$ar['STATUS'][$rq] = array('id'=>$rl['idx']);
		if($ro) {
			foreach($ro as $rx)
				$ar['STATUS'][$rq][$rx['keyword']] = $rx['value'];
		}
	}
	return $ar;
}

function json_feed($user=null,$base=null,$item=null) {
	il_put('templatefile','raw.json');
	il_put('type','json');
	header('Content-type: application/json;charset=utf-8');
	$r = array('data'=>common_feed($user,$base,$item,10));
	if($user) $r['user'] = $user;
	if($base) $r['base'] = $base;
	if($item) $r['item'] = $item;
	return json_encode($r);
}

function item_feed($user=null,$base=null,$item=null) {
	$or = il_select('dl_meter','*',null,null,false,0);
	if($or) foreach($or as $ori) {
		if(!empty($base) && $base!=$ori['station']) continue;
		if(!empty($item) && $base!=$ori['address']) continue;
		il_put2('or',sprintf('%s@%s/%03d',$ori['keyword'],$ori['station'],$ori['address']),$ori);
		il_put2('or',$ori['keyword'],$ori);
	}

	$r = common_feed($user,$base,$item,$item?30:($base?16:3));
	$s = "<div class=newsfeed>\n";
	krsort($r['STATUS']);
	foreach($r['STATUS'] as $sign=>$st) {
		$ii = (int)substr($sign,-3);
		$bb = trim(substr($sign,21,-5));
		$s.= item_write($st,$sign,$r['INST'][$bb][$ii],$r['BASE'][$bb]);
	}
	$s.= "</div>\n";
	$title = 'Últimos estados';
	if(!empty($item)) {
		$itemname = isset($r['INST'][$base][$item]['description'])? $r['INST'][$base][$item]['description']: 'Instrumento '.$item;
		$itemlink = sprintf('/item/%s/%03d/',$base,$item);
		$theitem = sprintf('<a href="%s">%s</a>',$itemlink,$itemname);
		$title.= " para $theitem";
	}
	if(!empty($base)) {
		$basename = isset($r['BASE'][$base]['name'])? $r['BASE'][$base]['name']: 'Base '.$base;
		$baselink = sprintf('/base/%s/',$base);
		$thebase = sprintf('<a href="%s">%s</a>',$baselink,$basename);
		$title.= " en $thebase";
		//$s.= print_r($r['BASE'],true);
	}
	il_put('title',$title);
	return $s;
}

function item_write($status,$sign,$inst,$base) {
	$sd = strtotime(substr($sign,0,19));
	ob_start();
	$avi = empty($status['avatar'])? (
		empty($inst['avatar'])? (
			empty($base['avatar'])? null: $base['avatar']
		): $inst['avatar']
	): $status['avatar'];
	$ava = il_avatar($avi,'small','/images/def-avatar.png');

	$maq = sprintf('/item/%s/%03d/',$base['id'],$inst['address']);
	$sta = $maq.(isset($status['id'])? $status['id']: '!'.$sd);
	$dat = date('j \d\e M, g:i:s a',$sd);

	$mpars = array();
	$mpars[0] = (!empty($inst['description'])? $inst['description']: 'Instrumento '.$inst['address'])." (";
	$mpars[0].= (!empty($base['name'])? $base['name']: 'Base '.$inst['id']).")";
	foreach($status as $k=>$v) {
		if($k=='id') continue;
		$or = il_get2('or',$u=sprintf('%s@%s/%03d',$k,$base['id'],$inst['address']));
		if($or && $or['type']=='common') {
			$mpars[] = sprintf("%s: %s",$or['name']?$or['name']:$k,$or['format']?sprintf($or['format'],$v):$v);
#			$mpars[] = "<!-- $u=".print_r($or,true)."-->";
		}
	}
	if(isset($status['time_beg'])) {
		$mpars[0] = "Alarma en {$mpars[0]} <strong class=alarm>{$status['description']}</strong>";
		$di = $status['time_beg'];
		$mpars[] = 'Inició: '.date('D j \d\e M, g:i:s a',$di);
		if(isset($status['time_end'])) {
			$df = $status['time_end'];
			if(date('z',$df)==date('z',$di))
				$mpars[] = 'Finalizó: '.date(' g:i:s a',$df);
			elseif(date('n',$df)==date('n',$di))
				$mpars[] = 'Finalizó: '.date('D j g:i:s a',$df);
			else
				$mpars[] = 'Finalizó: '.date('D j \d\e M g:i:s a',$df);
		}
	}
	$msg = implode(",<br>\n",$mpars).'.';
?>
	<p class=item>
		<a class=avatar-link href="<?php echo $maq?>"><img class=avatar src="<?php echo $ava?>"></a>
		<a class=desc-link href='<?php echo $sta?>'>
		<span class=item-date><?php echo $dat?></span>
		<span class=item-desc><?php echo $msg?></span>
		</a>
	</p>
<?php
	return ob_get_clean();
}

function item_graph($user=null,$base=null,$item=null,$last=null) {
	ob_start();
	$or = il_get('or');
	ensure_path(array('media','datalog'));
	$title = "Gráfico de evolución";
	$arr = get_graph_params($last,$title);
	$from=$arr[0]; $fromtext = date('Y-m-d',$from);
	$lapse=$arr[1]; $lapsetext = (int)$lapse/86400; $endtext = date('Y-m-d',$from+$lapse);
	$range=$arr[2]; $rangetext = (int)$range/3600; $rmin=false;
	if($range<3600) { $rangetext = (int)$range/60; $rmin=true; }
	$N = $lapse/$range; $counttext = (int)$N;
	$r = common_feed($user,$base,$item,0,$from);
?>
<form action="custom.php" method=GET>
	<ul class=tabs>
		<li class=tab><a <?php if($last=='day') echo 'class=selected '?>href="day.html">Día</a></li>
		<li class=tab><a <?php if($last=='week') echo 'class=selected '?>href="week.html">Semana</a></li>
		<li class=tab><a <?php if($last=='month') echo 'class=selected '?>href="month.html">Mes</a></li>
		<li class=tab><a <?php if($last=='year') echo 'class=selected '?>href="year.html">Año</a></li>
		<li class=tab><input class="anchor<?php if($last=='custom') echo ' selected'?>" type=submit value=Configurable>
			<table class=panel>
			<tr>
				<td class=ralign><label for=begin>A partir de </label><td><input type=date name=begin id=begin value="<?php echo $fromtext?>">
				<td class=ralign><label for=end>Hasta </label><td><input type=date name=end id=end value="<?php echo $endtext?>">
				<td class=ralign><label for=lapse>Periodo total </label><td><input class=ralign type=text name=lapse id=lapse value="<?php echo $lapsetext?>">
				<td><select name=lunits id=lunits>
					<option value=300>×5 minutos</option>
					<option value=hour>horas</option>
					<option value=10800>×3 horas</option>
					<option value=day selected>días</option>
					<option value=week>semanas</option>
					<option value=month>meses</option>
					<option value=year>años</option>
				</select></label>
			<tr>
				<td colspan=2>
				<td class=ralign><label for=count># de períodos </label><td><input class=ralign type=text name=count id=count value="<?php echo $counttext?>">
				<td class=ralign><label for=range>Periodo </label><td><input class=ralign type=text name=range id=range value="<?php echo $rangetext?>">
				<td><select name=runits id=runits>
					<option value=sec>segundos</option>
					<option value=min<?php if($rmin) echo ' selected'?>>minutos</option>
					<option value=300>×5 minutos</option>
					<option value=hour<?php if(!$rmin) echo ' selected'?>>horas</option>
					<option value=10800>×3 horas</option>
					<option value=day>días</option>
					<option value=week>semanas</option>
					<option value=month>meses</option>
				</select></label>
			</table>
		</li>
	</ul>
	<ol class=labels>
<?php
	$lines = array();
	$periods = array();
	for($i=0;$i<$N;$i++) {
		$ar = get_graph_data($r['STATUS'],$b=$from+$i*$range,$range);
		$e = $b+$range;
		if(empty($ar)) continue;
		$periods[$i] = array($b,$e);
		foreach($ar as $key=>$data) {
			if(!isset($lines[$key])) $lines[$key]=array();
			$lines[$key][$i] = $data;
		}
	}
	$hash = str_replace(array('+','/','=='),array('_','-','~'),base64_encode(md5($from.'::'.$_SERVER['REQUEST_URI'],true)));
	if(!file_exists($imagefile = "media/datalog/$hash.png"))
		create_image($imagefile,$lines,$N);
	$keys = array_keys($lines);
	sort($keys);
	$lb = '';
	$li = -1;
	$ind = 1;
	foreach($keys as $key) {
		$lk = explode(':',$key);
		$ll = implode('_',$lk);
		if($lb!=$lk[0]) {
			while($ind>1) {
				$ind--;
				echo str_repeat("\t\t",$ind)."\t</ol>\n".str_repeat("\t\t",$ind)."</il>\n";
			}
			$lb = $lk[0];
			$li = -1;
			$ba = $r['BASE'][$lb];
			$name = isset($ba['name'])? $ba['name']: 'Base '.$lb;
			echo "\t\t<li><h3>$name</h3>\n\t\t\t<ol>\n";
			$ind = 1;
		}
		if($li!=$lk[1]) {
			while($ind>2) {
				$ind--;
				echo str_repeat("\t\t",$ind)."\t</ol>\n".str_repeat("\t\t",$ind)."</il>\n";
			}
			$li = $lk[1];
			$ia = $r['INST'][$lb][$li];
			$description = isset($ia['description'])? $ia['description']: 'Instrumento '.$li;
			echo "\t\t\t\t<li><h4>$description</h4>\n\t\t\t\t\t<ol>\n";
			$ind = 2;
		}
		$lp=$lk[2];
		$param = $lp=='alarm'? 'alarma': $or[$lp][0].' ('.$or[$lp][1].')';
		$checked = empty($_REQUEST['input'])||in_array($ll,$_REQUEST['input'])? ' checked': '';
		echo "\t\t\t\t\t\t<li><label for=$ll><input id=$ll name='input[]' value=$ll type=checkbox$checked> $param</label></li>\n";
		$ind = 3;
	}
	while($ind>1) {
		$ind--;
		echo str_repeat("\t\t",$ind)."\t</ol>\n".str_repeat("\t\t",$ind)."</il>\n";
	}
?>
	</ol>
	<img class=graphic src="/<?php echo $imagefile?>" alt="grafica <?php echo $fromtext?> <?php echo $endtext?>">
</form>
<?php
	il_put('title',$title);
	return ob_get_clean();
}

function create_image($imagefile,$lines,$N) {
	$n = count($lines);
	$f = (int)(800/$N);
	$x0=40;
	$x1=$x0+$f*$N;
	$y0=550;
	$y1=$y0-500;
	$x = $x0+$x1;
	$y = $y0+$y1;
	if(false===($im = @imagecreatetruecolor($x,$y))) return false;
	if(false===($bk = imagecolorallocate($im,255,255,255))) return false;
	if(false===($bg = imagecolorallocate($im,221,221,221))) return false;
	if(false===($fg = imagecolorallocate($im,51,51,51))) return false;
	if(false===($mg = imagecolorallocate($im,170,170,170))) return false;
	$st = $n? (int)(600.0/$n): 300;
	imagefilledrectangle($im,0,0,$x-1,$y-1,$bg);
	imagefilledrectangle($im,$x0,$y1,$x1,$y0,$bk);
	$c = (int)($st/2);
	for($i=5;$i<=100;$i+=5) {
		imageline($im,$x0-5,$y0-5*$i,$x1+5,$y0-5*$i,$mg);
	}
	imageline($im,$x0,$y0,$x1,$y0,$fg);
	imageline($im,$x0,$y1,$x0,$y0,$fg);
	imageline($im,$x1,$y1,$x1,$y0,$fg);
	foreach($lines as $l=>$d) {
		$c1 = $c%100;
		$c2 = (int)($c/200);
		switch($c2) {
			case 0: $r = 240; $g =  40+$c1; $b =  40; break;
			case 1: $r = 240-$c1; $g = 240; $b =  40; break;
			case 2: $r =  40; $g = 240; $b =  40+$c1; break;
			case 3: $r =  40; $g = 240-$c1; $b = 240; break;
			case 4: $r =  40+$c1; $g =  40; $b = 240; break;
			case 5: $r = 240; $g =  40; $b = 240-$c1; break;
			default: $r = $g = $b = 140;
		}
		if(false===($col = imagecolorallocate($im,$r,$g,$b))) return false;
		if(false===($sof = imagecolorallocate($im,(250+$r)/2,(250+$r)/2,(250+$r)/2))) return false;
		if(substr($l,-5)=='alarm') {
			foreach($d as $cc=>$va) {
				foreach($va as $k=>$m) {
					imagefilledrectangle($im,$x0+$f*$cc+1,$y0-5*$m,$x0+$f*$cc+2,$y0,$sof);
				}
			}
		} else {
			foreach($d as $cc=>$va) {
				$m0 = $va['min'];
				$m1 = $va['mean'];
				$m2 = $va['max'];
				imagefilledrectangle($im,$x0+$f*$cc+1,$y0-5*$m2,$x0+$f*$cc+2,$y0-5*$m0,$sof);
				imagefilledrectangle($im,$x0+$f*$cc,$y0-5*$m1-1,$x0+$f*$cc+3,$y0-5*$m1+1,$col);
			}
		}
		$c+= $st;
	}
	imagepng($im,$imagefile);
	imagedestroy($im);
}

function get_graph_params($last,&$title) {
	$time = time();
	switch($last) {
	case 'day';
		$lapse = 86400;
		$range = 600;
		$align = 3600;
		$title.= " diario";
		break;
	case 'week';
		$lapse = 7*86400;
		$range = 3600;
		$align = 86400;
		$title.= " semanal";
		break;
	case 'custom';
	case 'month';
		$lapse = 30*86400;
		$range = 21600;
		$align = 86400;
		$title.= $last='month'?" mensual":'';
		break;
	case 'year';
		$lapse = 364*86400;
		$range = 7*86400;
		$align = 86400;
		$title.= " anual";
		break;
	}
	$from = empty($_REQUEST['begin'])? $time - $lapse: 3600+strtotime($_REQUEST['begin']);
	$lmult = 86400;
	if(isset($_REQUEST['lunits'])) {
		switch($_REQUEST['lunits']) {
		case 'sec': $lmult/=60;
		case 'min': $lmult/=60;
		case 'hour': $lmult/=24; break;
		case 'week': $lmult*=7; break;
		case 'mon': $lmult*=30; break;
		case 'year': $lmult*=364; break;
		default:
			if(is_numeric($_REQUEST['lunits'])) $rmult = (int)$_REQUEST['lunits'];
		}
	}
	if(!empty($_REQUEST['end'])) $lapse = (3600+strtotime($_REQUEST['end']))-$from;
	if(!empty($_REQUEST['lapse'])) $lapse = $lmult*$_REQUEST['lapse'];
	$rmult = 3600;
	if(isset($_REQUEST['runits'])) {
		switch($_REQUEST['runits']) {
		case 'sec': $rmult/=60;
		case 'min': $rmult/=60; break;
		case 'week': $rmult*=7;
		case 'day': $rmult*=24; break;
		case 'mon': $rmult*=720; break;
		default:
			if(is_numeric($_REQUEST['runits'])) $rmult = (int)$_REQUEST['runits'];
		}
	}
	if(!empty($_REQUEST['range'])) $range = $rmult*$_REQUEST['range'];
	if(!empty($_REQUEST['count'])) $range = $lapse/$_REQUEST['count'];
	if($range<=0) $range=$lapse=200;

	$from-= $from%$align;
	$N = $lapse/$range;
	
	return array($from,$lapse,$range,$N);
}

function get_graph_data($slist,$from,$lapse) {
	$data = array();
	$or = il_get('or');
	foreach($slist as $status) {
		$bb = $status['data'][0];
		$ii = $status['data'][1];
		$bi = "$bb:$ii";
		foreach($status as $source=>$values) {
			if($source=='data') continue;
			if($source=='alarm') {
				$vt1 = $values['time_beg'];
				$vt2 = $values['time_end'];
				if(($vt1<$from && $vt2<$from) || ($vt1>=$from+$lapse && $vt2>=$from+$lapse)) continue;
				$vto = $vt1>=$from? $vt1: $from;
				$to = $from+$lapse;
				$vtf = $vt2<$to? $vt2: $to;
				$id = $values['id'];
				$des = $values['description'];
				$key = "$bb:$id:alarm";
				if(!isset($data[$key])) $data[$key]=array();
				if(!isset($data[$key][$des])) $data[$key][$des] = 0.0;
				$data[$key][$des] += 100.0*($vtf-$vto)/$lapse;
			} else {
				$vt = $values['time'];
				if($vt<$from || $vt>=$from+$lapse) continue;
				if(!isset($data[$bi])) $data[$bi]=array();
				foreach($values as $k=>$v) {
					if(!isset($or[$k])) continue;
					if(!isset($data[$bi][$k])) $data[$bi][$k]=array();
					$data[$bi][$k][] = $v;
				}
			}
		}
	}
	if(empty($data)) return null;
	$r = array();
	foreach($data as $item=>$rows) {
		if(substr($item,-6)==':alarm') {
			$r[$item] = $rows;
			continue;
		}
		foreach($rows as $key=>$values) {
			$nkey = "$item:$key";
			$m = 1000;
			$M = -1000;
			$c = 0;
			$n = 0;
			foreach($values as $v) {
				if($v>$M) $M=$v;
				if($v<$m) $m=$v;
				$c+= $v;
				$n++;
			}
			$r[$nkey] = array(
				'min'=>$m<1000? $m: (float)'nan',
				'max'=>$M>-1000? $M: (float)'nan',
				'mean'=>$n? $c/$n: (float)'nan'
			);
		}
	}
	return $r;
}

function make_base($line) {
	set_area('left');
	set_area('right');

	$path = il_line_get('path');
	$last = il_line_get('last','index',true);
	$ext = il_line_get('extension','html',true);
	$base = isset($path[0])? $path[0]: $last;
	$user = isset($_SESSION['user'])? $_SESSION['user']: null;
	if($last=='create') return item_create($user,$base,null,$last);
	if($last=='edit') return require 'base_edit.php';
	if(($cannonical = "base/$base/")!=il_line_get('cannon')) redirect("/$cannonical");
	ob_start();

	$ba = json_read($bf="data/datalog/$base/desc");
	//echo "<pre style=\"font-size:0.6em;color:rgba(0,0,0,.5)\">\$as ".print_r($as,true)."</pre>";
	if(empty($ba)) {
		$r = common_feed($user,$base,null,1);
		//$as = $r['STATUS'][0];
		$bb = $base;//$as['data'][0];
		$ba = $r['BASE'][$base];
		//print_r($ba);
	} else {
		$bb = $base;
		$ba = $ba['BASE'];
		//print_r($ba);
	}
	$basename = isset($ba['name'])? $ba['name']: "Base $bb";
	il_put('title',$basename);
	echo "<p class=nav>Ver un <a href=\"/item/$bb/\">listado de los instrumentos</a> de esta estación.</p>";

	//echo "<pre style=\"font-size:0.6em;color:rgba(0,0,0,.5)\">\$ba ($bb) ".print_r($ba,true)."</pre>";
	//if(isset($r))echo "<pre style=\"font-size:0.6em;color:rgba(0,0,0,.5)\">\$r ".print_r($r,true)."</pre>";
	return ob_get_clean();
}

function item_edit($user,$base,$item,$last) {
	if(!$user) redirect("/item/$base/$item/");
	if($item) return include "mod/datalog/item_edit.php";
	if($base) return include "mod/datalog/base_edit.php";
}

function item_create($user,$base,$item,$last) {
	if(empty($user)) $user = il_get('user');
	if(empty($user)) return make_status(401,'Registered user required for this action');

	$base = empty($_REQUEST['station'])? $base: strtolower($_REQUEST['station']);
	$canuri = preg_match('{\w+}',$base)? "/item/$base/create.cgi": "/item/create.cgi";
	checkorredirect($canuri);
	$group = empty($_REQUEST['group'])? null: $_REQUEST['group'];
	$fullname = empty($_REQUEST['fullname'])? null: $_REQUEST['fullname'];
	$passwd = empty($_REQUEST['passwd'])? null: $_REQUEST['passwd'];
	$hash = md5("[$base:$passwd]");
	$messages = array();

	$groups = il_get2('user','group',array());
	if(!empty($_REQUEST['create'])) {
		if(empty($base)) $messages[] = "No se proporcionó nombre para la estación";
		if(!preg_match('{\w+}',$base)) $messages[] = "Nombre '$base' incorrecto.  El nombre de la estación debe tener sólo letras y números";
		$lu = loadusers();
		if(isset($lu[$base])) $messages[] = "Ya existe un usuario o estación con el nombre de la estación '$base'";
		if(empty($group)) $messages[] = "No se especificó grupo";
		if(isset($groups['this_site']) && $groups['this_site']>=7) {}
		elseif(!isset($groups[$group]) || $groups[$group]<7) $messages[] = "Usuario '$user' no tiene permisos para crear estación en gruppo'$group'";
		if(!count($messages)) {
			$ename = il_escape($fullname);
			$egroup = il_escape($group);
			il_query("INSERT INTO `user`(`id`,`name`,`hatch`) VALUES ('$base','$ename','$hash');\n") or die(il_dberror());
			il_query("INSERT INTO `user_group`(`user`,`group`,`role`) VALUES ('$base','$egroup',1);\n") or die(il_dberror());
			il_query("INSERT INTO `dl_station`(`station`,`name`,`group`) VALUES ('$base','$ename','$egroup');\n") or die(il_dberror());
			redirect("base/$base/");
		}
	}
	$posible = array();
	if(isset($groups['this_site']) && $groups['this_site']>=7) {
		$d = il_select('group');
		foreach($d as $r)
			$posible[] = $r['id'];
	} else
		foreach($groups as $group=>$level)
			if($level>=7) $posible[] = $group;
	if(!count($posible)) return make_status(401,'User does not have permision to perform this action');
	$selector = '<select name=group>'.chr(10);
	foreach($posible as $gr)
		$selector.= "\t<option value=\"$gr\"".($gr==$group?' selected':'').">$gr</option>\n";
	$selector.= '</select>'.chr(10);
	ob_start();
	foreach($messages as $warning)
		echo "<p class=warning>Aviso: $warning.</p>\n";
?>
	<form method=POST action="<?php echo $canuri?>">
		<label for=station>Nombre (corto) de la estación: <input name=station<?php if(!empty($base)) echo " value=\"$base\""?>></label><br>
		<label for=fullname>Nombre completo de la estación: <input name=fullname<?php if(!empty($base)) echo " value=\"$base\""?>></label><br>
		<label for=passwd>Contraseña inicial: <input type=password name=passwd></label><br>
		<label for=group>Pertenece al grupo: <?php echo $selector?></label><br>
		<input type=submit name=create value="Crear">
	</form>
<?php
	il_put('title',empty($base)? 'Crear nueva estación': "Crear nueva estación '$base'");
	return ob_get_clean();
}

function quoteitem(&$item,$idx=null) {
	$q = '"';
	if($item===false) return $item='FALSE';
	if($item===true) return $item='TRUE';
	if(is_null($item)) return $item='NULL';
	if($item==='') return '';
	if(is_numeric($item)) return $item=(float)$item;
	if(is_array($item)) $item=implode(',',array_walk($item,'quoteitem'));
	elseif(is_object($item)) $item=json_encode($item);
	else $item= "$item";
	return $item = $q.str_replace(array($q,'\\',"\r\n","\n","\t"),array($q.$q,'\\\\','\\_','\\_','\\t'),$item).$q;
}
function array2csl($ar,$sep=';') { array_walk($ar,'quoteitem'); return implode($sep,$ar).chr(10); }

function item_hist_data_sum($user,$base,$item,$last) {
	if(isset($_REQUEST['data'])) {
		$phash = $_REQUEST['data'];
		if(file_exists($vfn="data/datalog/$phash.csv")) {
			il_put('templatefile','raw.csv');
			il_put('type','text');
			header('Content-type: text/csv');
			header('Content-disposition: attachment;filename=graphdata.csv');
			return file_get_contents($vfn);
		}
		if(file_exists($dfn="data/datalog/$phash.json")) {
			$rowdata = json_read($dfn);
		} else return reply_status(404,"Los datos <code>$data</code> no existen y no pueden ser recreados.");
	} else {
		require_once 'mod/lib/item_graph_lib.php';
		$tarr = item_graph_temporal_params($last);
		$iarr = item_graph_instrument_params($user,$base,$item);
		$params = json_encode(array($tarr[0],$iarr[0]));
		$phash = str_replace(array('+','/','=='),array('_','-','~'),base64_encode(md5($params,true)));
		if(file_exists($vfn="data/datalog/$phash.csv")) {
			il_put('templatefile','raw.csv');
			il_put('type','text');
			header('Content-type: text/csv');
			header('Content-disposition: attachment;filename=graphdata.csv');
			return file_get_contents($vfn);
		}
		if(file_exists($dfn="data/datalog/$phash.json")) {
			$rowdata = json_read($dfn);
		} else {
			$rowdata = item_graph_get_data($tarr[0],$iarr[0]);
			json_write($dfn,$rowdata);
		}
	}
	il_put('templatefile','raw.csv');
	il_put('type','text');
	header('Content-type: text/csv');
	header('Content-disposition: attachment;filename=graphdata.csv');
	ob_start();
	$td = $rowdata['_t'];
	$id = $rowdata['_i'];
	$t0 = $td[0];
	$dt = $td[2];
	$n = $td[3];
	$pt = $dt/$n;
	$r = array('inicio','fin');
	foreach($id as $ins) {
		$t = explode('/',$ins);
		$r = array_merge($r,array("{$t[2]} ({$t[0]} {$t[1]})",'std','min','max'));
	}
	echo array2csl($r);
	for($i=0;$i<$n;$i++) {
		$tt = $t0+$dt*$i/$n;
		$r = array(date('Y-m-d H:i:s',$tt),date('Y-m-d H:i:s',$tt+$pt));
		foreach($id as $ins) {
			if(isset($rowdata[$ins][$i])) {
				$d = $rowdata[$ins][$i];
				array_shift($d);
				$r = array_merge($r,$d);
			} else {
				$r = array_merge($r,array('','','',''));
			}
		}
		echo array2csl($r);
	}
	$s = ob_get_clean();
	file_put_contents($vfn,$s);
	return $s;
}

function item_hist_data_all($user,$base,$item,$last) {
	if(isset($_REQUEST['data'])) {
		$phash = $_REQUEST['data'];
		if(file_exists($vfn="data/datalog/$phash.csv")) {
			il_put('templatefile','raw.csv');
			il_put('type','text');
			header('Content-type: text/csv');
			header('Content-disposition: attachment;filename=alldata.csv');
			return file_get_contents($vfn);
		}
		if(file_exists($dfn="data/datalog/$phash.def")) {
			$params = file_get_contents($dfn);
		} else return reply_status(404,"Los datos <code>$data</code> no existen y no pueden ser recreados.");
	} else {
		require_once 'mod/lib/item_graph_lib.php';
		$tarr = item_graph_temporal_params($last);
		$iarr = item_graph_instrument_params($user,$base,$item);
		$params = date("Y-m-d H:i:s\n",$tarr[0][0]);
		$params.= date("Y-m-d H:i:s\n",$tarr[0][1]);
		$params.= implode(chr(10),$iarr[0]);
		$phash = str_replace(array('+','/','=='),array('_','-','!'),base64_encode(md5($params,true)));
		if(file_exists($vfn="data/datalog/$phash.csv")) {
			il_put('templatefile','raw.csv');
			il_put('type','text');
			header('Content-type: text/csv');
			header('Content-disposition: attachment;filename=alldata.csv');
			return file_get_contents($vfn);
		}
		if(file_exists($dfn="data/datalog/$phash.def")) {
			$params = json_read($dfn);
		} else {
			$params = item_graph_get_data($tarr,$iarr);
			json_write($dfn,$rowdata);
		}
	}
	$pp = explode(chr(10),$params);
	$t0 = array_shift($pp);
	$t1 = array_shift($pp);
	$bases = array();
	$insts = array();
	$met = array();
	foreach($pp as $it) {
		$itr = explode('/',$it);
		if(!in_array($itr[0],$bases)) {
			$bases[] = $itr[0];
			$insts[$itr[0]] = array();
		}
		if(!in_array((int)$itr[1],$insts[$itr[0]])) $insts[$itr[0]][] = (int)$itr[1];
		$met[] = $itr;
	}
	il_put('templatefile','raw.csv');
	il_put('type','text');
	header('Content-type: text/csv');
	header('Content-disposition: attachment;filename=alldata.csv');
	ob_start();
	$d = db_select_key('dl_status_i','idx',array('time'=>"&$t0&$t1",'station'=>$bases),'-time',0);
	$r = array_merge(array('dia','hora'),$pp);
	echo array2csl($r);
	foreach($d as $i=>$dd) {
		$bb = $dd['station'];
		$ii = $dd['address'];
		if(!in_array($ii,$insts[$bb])) continue;
		$mm = db_select_pairs('dl_status_p','keyword','value',array('status'=>$i));
		$r = array(substr($dd['time'],0,10),substr($dd['time'],11));
		foreach($met as $bim) {
			$r[] = ($bb==$bim[0] && $ii==$bim[1] && isset($mm[$bim[2]]))? $mm[$bim[2]]: '';
		}
		echo array2csl($r);
	}
	$s = ob_get_clean();
	file_put_contents($vfn,$s);
	return $s;
}

/*********************
Problemas a resolver:
	para que la lista sea dinámica no deben verse todos los elementos pero deben refrescarse poco a poco.
	alternativa 1: crear una página que devuelva el contenido en, p. ej. JSON, desde la página HTML se incluye un script que lea el JSON y cree el contenido.
	alternativa 2: crear una página que devuelva el contenido en HTML y se autoactualize.  Desde la página principal insertar el contenido con un iframe.
 *********************/
?>
