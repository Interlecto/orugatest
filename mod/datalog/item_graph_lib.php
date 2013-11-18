<?php

function lunits($lu,$n=1) {
	switch($lu) {
	case 'sec': return $n;
	case 'min': return $n*60;
	case 'hour': return $n*3600;
	case 'day': return $n*86400;
	case 'week': return $n*86400*7;
	case 'month': return $n*86400*30;
	case 'year': return $n*86400*365;
	default: return $b*(int)$lu;
	}
}

function item_graph_temporal_params($last) {
	if(isset($_REQUEST['date'])) {
		$dt0 = strtotime($_REQUEST['date'].' '.(isset($_REQUEST['time'])?$_REQUEST['time']:'00:00'));
		$count = (int)$_REQUEST['count'];
		if(isset($_REQUEST['enddate'])) {
			$dt1 = strtotime($_REQUEST['enddate'].' '.(isset($_REQUEST['endtime'])?$_REQUEST['endtime']:'24:00'));
			$delta = $dt1-$dt0;
		} else {
			$delta = lunits($_REQUEST['lunits'],$_REQUEST['lapse']);
			$dt1 = $dt0 + $delta;
		}
		$req = array($dt0,$dt1,$delta,$count);
	}
	$ar = array();
	$now = time();
	$nh = $now-($now%3600);
	$nd = $now-($now%86400)+18000;
	$dd = 86400;
	$dw = $dd*7;
	$dm = $dd*30;
	$dy = $dd*364;

	$ar['day'] = array($nh-$dd,$nh,$dd,240);
	$ar['week'] = array($nd-$dw,$nd,$dw,168);
	$ar['month'] = array($nd-$dm,$nd,$dm,240);
	$ar['year'] = array($nd-$dy,$nd,$dy,182);

	$ar[0] = isset($req)? $req: $ar[$last=='custom'?'month':$last];
	return $ar;
}

function item_graph_instrument_params($user,$base,$item) {
	$on = array();
	$off = array();
	$bases = user_bases($user,$base);
	foreach($_REQUEST as $k=>$v) {
		if(!$v) continue;
		$kr = explode('_',$k);
		if(count($kr)!=4) continue;
		if(array_shift($kr)!='item') continue;
		if(!in_array($kr[0],$bases)) continue;
		$on[] = sprintf('%s/%03d/%s',$kr[0],(int)$kr[1],$kr[2]);
	}
	foreach($bases as $bb) {
		$where = array('station'=>"=$bb",'type'=>'=common');
		if($item) $where['address'] = "=$item";
		$q = il_select('dl_meter',array('address','keyword'),$where);
		//il_add('queries',print_r($q,true));
		foreach($q as $r) {
			$k = sprintf('%s/%03d/%s',$bb,(int)$r['address'],$r['keyword']);
			//il_add('queries',">> $k\n");
			if(!in_array($k,$on)) $off[] = $k;
		}
	}
	sort($on);
	sort($off);
	il_add('queries',print_r(array('on'=>$on,'off'=>$off),true));
	return empty($on)? array($off,$on): array($on,$off);
}

function item_graph_useragent_params() {
	$ans = '800';
	return $ans;
}

function item_graph_get_data($tarr,$iarr) {
	static $ar;
	if(isset($ar)) return $ar;
	$ar = array('_t'=>$tarr,'_i'=>$iarr,'_@'=>array());
	$dt = $tarr[2]/$tarr[3];
	$wh = array();
	foreach($iarr as $k) {
		$kr = explode('/',$k);
		$wh[] = sprintf("(`station`='%s' AND `address`=%d AND `keyword`='%s')",$kr[0],(int)$kr[1],$kr[2]);
	}
	$where = implode(' OR ',$wh);
	if(count($wh)==0) $where = '0';
	if(count($wh)==1) $where = trim($where,'()');
	il_add('queries','iarr: WHERE '.$where.chr(10));
	for($i=0;$i<$tarr[3];$i++) {
		$t0 = $tarr[0]+$i*$dt;
		$t1 = $t0+$dt;
		$q = "SELECT `station`, `address`, `keyword`, COUNT(*) as `count`, AVG(`value`) as `mean`, STD(`value`) as `std`, MIN(`value`) as `min`, MAX(`value`) as `max`";
		$q.= " FROM `dl_status_i` JOIN `dl_status_p` ON `dl_status_i`.`idx` = `dl_status_p`.`status`";
		$q.= " WHERE `time`>='".date('Y-m-d H:i:s',$t0)."' AND `time`<'".date('Y-m-d H:i:s',$t1)."' AND ($where)";
		$q.= " GROUP BY `station`,`address`,`keyword`";
		$q.= ";\n";
		$r = db_query($q);// or il_add('queries','FAILED: '.il_dberror());
		if($r) {
			while($s = $r->fetch_array(MYSQLI_NUM)) {
				$k = sprintf("%s/%03d/%s",$s[0],$s[1],$s[2]);
				$m = substr($s[2],0,4);
				if(!isset($ar[$k])) $ar[$k] = array();
				$ar[$k][$i] = array($s[3],$s[4],$s[5],$s[6],$s[7]);
				if(!isset($ar['_@'][$m])) $ar['_@'][$m] = array(floor($s[6]/10)*10,ceil($s[7]/10)*10);
				if($s[6] < $ar['_@'][$m][0]) $ar['_@'][$m][0] = floor($s[6]/10)*10;
				if($s[7] > $ar['_@'][$m][1]) $ar['_@'][$m][1] = ceil($s[7]/10)*10;
			}
			$r->free();
		}
	}
	return $ar;
}

function item_graph_generate($graphdata,$uasz) {
	$tp = $graphdata['_t'];
	$ip = $graphdata['_i'];
	$mm = $graphdata['_@'];
	$mn = count(array_keys($mm));
	$nn = count($graphdata)-3; if(!$nn) $nn=1;
	switch($uasz) {
	case 800:
		$x = 800;
		$y = 585+ceil($nn/3)*18;
		$im = imagecreatetruecolor($x,$y);
		$bg = imagecolorallocate($im, 221,221,221);
		$tx = imagecolorallocate($im, 0, 0, 0);
		imagefilledrectangle($im,0,0,$x-1,$y-1,$bg);
		$x0 = 5+25*$mn;
		$x1 = 785;
		$y0 = 525;
		$y1 = 25;
		$dx = $x1-$x0;
		if($tp[2]/86400 < $dx/30) {
			$bd = imagecolorallocate($im, 238,238,238);
			$bn = imagecolorallocate($im, 204,204,204);
			imagefilledrectangle($im,$x0,$y1,$x1,$y0,$bd);
			for($i=0;$i<=$dx;$i++) {
				$hh = $tp[0]+$tp[2]*$i/$dx;
				$hhh = (int)date('H',$hh);
				if($hhh<6 || $hhh>18) imageline($im,$x0+$i,$y1,$x0+$i,$y0,$bn);
			}
			$sx2 = -25;
			for($t = ceil($tp[0]/3600)*3600-25200;$t<$tp[1];$t+=3600) {
				$x2 = ($t-$tp[0])*$dx/$tp[2]+$x0;
				if($x2<$x0) continue;
				imageline($im,$x2,$y0,$x2,$y0+20,$bn);
				if($x2<$sx2+50) continue;
				$sx2 = $x2;
				imagestring($im,2,$x2-15,$y0+5,date('H:i',$t),$tx);
			}
			for($t = ceil($tp[0]/86400)*86400-25200;$t<$tp[1];$t+=86400) {
				$x2 = ($t-$tp[0])*$dx/$tp[2]+$x0;
				if($x2<$x0) continue;
				imagestring($im,2,$x2-15,$y0+25,date('D j',$t),$tx);
				imagestring($im,2,$x2-7,$y0+40,date('M',$t),$tx);
			}
		} elseif($tp[2]/86400 < 7*$dx/30) {
			$bd = imagecolorallocate($im, 204,238,238);
			$bw = imagecolorallocate($im, 238,204,204);
			imagefilledrectangle($im,$x0,$y1,$x1,$y0,$bd);
			for($i=0;$i<=$dx;$i++) {
				$hh = $tp[0]+$tp[2]*$i/$dx;
				$hhh = (int)date('N',$hh);
				if($hhh>=6) imageline($im,$x0+$i,$y1,$x0+$i,$y0,$bw);
			}
			$sx2 = -25;
			for($t = ceil($tp[0]/3600)*3600-25200;$t<$tp[1];$t+=3600) {
				$x2 = ($t-$tp[0])*$dx/$tp[2]+$x0;
				if($x2<$x0) continue;
				imageline($im,$x2,$y0,$x2,$y0+20,$bn);
				if($x2<$sx2+50) continue;
				$sx2 = $x2;
				imagestring($im,2,$x2-15,$y0+5,date('H:i',$t),$tx);
			}
			for($t = ceil($tp[0]/86400)*86400-25200;$t<$tp[1];$t+=7*86400) {
				$x2 = ($t-$tp[0])*$dx/$tp[2]+$x0;
				if($x2<$x0) continue;
				imagestring($im,2,$x2-15,$y0+25,date('D j',$t),$tx);
				imagestring($im,2,$x2-7,$y0+40,date('M',$t),$tx);
			}
		} else {
			$be = imagecolorallocate($im, 221,238,204);
			$bo = imagecolorallocate($im, 221,204,238);
			imagefilledrectangle($im,$x0,$y1,$x1,$y0,$be);
			for($i=0;$i<=$dx;$i++) {
				$hh = $tp[0]+$tp[2]*$i/$dx;
				$hhh = (int)date('m',$hh);
				if($hhh%2) imageline($im,$x0+$i,$y1,$x0+$i,$y0,$bo);
			}
			$yr = (int)date('Y',$tp[0]);
			$mo = (int)date('m',$tp[0]);
			for($t = mktime(12,0,0,$mo,15,$yr); $t<$tp[1]; $t = mktime(12,0,0,++$mo,15,$yr)) {
				$x2 = ($t-$tp[0])*$dx/$tp[2]+$x0;
				if($x2<$x0) continue;
				imagestring($im,2,$x2-7,$y0+25,date('M',$t),$tx);
				imagestring($im,2,$x2-10,$y0+40,date('Y',$t),$tx);
			}
		}

		$i = 0;
		foreach($mm as $k=>$vv) {
			imagestring($im, 1, 5+$i*25, 5, $k, $tx);
			$d = $vv[1]-$vv[0];
			$st = $d<=30? 5: (($d<=60)? 10: ($d<=120? 20: 50));
			il_add('queries',">> $k: delta $d, step $st\n");
			for($j=0;$j<$d;$j+=$st) {
				imagestring($im, 2, 5+$i*25, 520-(int)(.5+$j*500/$d), $vv[0]+$j, $tx);
				il_add('queries',"$j ".(520-(int)(.5+$j*500/$d))."<br>\n");
			}
			imagestring($im, 2, 5+$i*25, 20, $vv[1], $tx);
			$i++;
		}
		$cols = array();
		$i = 0;
		$j = 0;
		$step = (int)(256*6/$nn);
		$c = (int)($step/2);
		foreach($ip as $k) {
			$cc = $c%256;
			if($c<256) {
				$r=255; $g=$cc; $b=0;
			} elseif ($c<512) {
				$r=255-$cc; $g=255; $b=0;
			} elseif ($c<768) {
				$r=0; $g=255; $b=$cc;
			} elseif ($c<1024) {
				$r=0; $g=255-$cc; $b=255;
			} elseif ($c<1280) {
				$r=$cc; $g=0; $b=255;
			} else {
				$r=255; $g=0; $b=255-$cc;
			}
			$cols[$k] = array(
				imagecolorallocate($im,$r,$g,$b),
				imagecolorallocate($im,(int)(255+$r)/2,(int)(255+$g)/2,(int)(255+$b)/2),
				imagecolorallocate($im,(int)(765+$r)/4,(int)(765+$g)/4,(int)(765+$b)/4)
			);
			il_add('queries',"$k>> ($r $g $b) (".(int)(255+$r)/2 .",". (int)(255+$g)/2 .",". (int)(255+$b)/2 .") (". (int)(765+$r)/4 .",". (int)(765+$g)/4 .",". (int)(765+$b)/4 .")\n");
			$kr = explode('/',$k);
			$lb = sprintf('%s, instr.%d (%s)',$kr[2],$kr[1],$kr[0]);
			imagestring($im, 2, $x0+10+$j*250, $y0+40+$i*18, $lb, $tx);
			imagefilledrectangle($im, $x0+2+$j*250, $y0+45+$i*18, $x0+7+$j*250, $y0+48+$i*18, $cols[$k][0]);
			$j++;
			if($j*250+110 > $dx) {
				$j=0;
				$i++;
			}
			$c+=$step;
		}
		foreach($graphdata as $k=>$ar) {
			if(substr($k,0,1)=='_') continue;
			$kr = explode('/',$k);
			$ko = substr($kr[2],0,4);
			$kmm = $mm[$ko];
			$txf = $dx/$tp[3];
			$tyf = ($y0-$y1)/($kmm[1]-$kmm[0]);
			for($i=0; $i<$tp[3]; $i++) {
				if(isset($ar[$i])) {
					$tx = $x0+$txf*($i+.5);
					$ty0 = $y0-$tyf*($ar[$i][3]-$kmm[0]);
					$ty1 = $y0-$tyf*($ar[$i][4]-$kmm[0]);
					imagefilledrectangle($im,$tx-1,$ty1,$tx+1,$ty0,$cols[$k][2]);
					$ty = $y0-$tyf*($ar[$i][1]-$kmm[0]);
					$dy = $tyf+$ar[$i][2];
					imagefilledrectangle($im,$tx-1,$ty-$dy,$tx+1,$ty+$dy,$cols[$k][1]);
				}
			}
		}
		foreach($graphdata as $k=>$ar) {
			if(substr($k,0,1)=='_') continue;
			$kr = explode('/',$k);
			$ko = substr($kr[2],0,4);
			$kmm = $mm[$ko];
			$txf = $dx/$tp[3];
			$tyf = ($y0-$y1)/($kmm[1]-$kmm[0]);
			for($i=0; $i<$tp[3]; $i++) {
				if(isset($ar[$i])) {
					if(isset($ar[$i-1])) {
						$tx0 = $x0+$txf*($i-.5);
						$ty0 = $y0-$tyf*($ar[$i-1][1]-$kmm[0]);
						$tx1 = $x0+$txf*($i+.5);
						$ty1 = $y0-$tyf*($ar[$i][1]-$kmm[0]);
						imageline($im,$tx0,$ty0,$tx1,$ty1,$cols[$k][0]);
					} else {
						$tx1 = $x0+$txf*($i+.5);
						$ty1 = $y0-$tyf*($ar[$i][1]-$kmm[0]);
						imagefilledrectangle($im,$tx1-1,$ty1-1,$tx1,$ty1+1,$cols[$k][0]);
					}
				} elseif(isset($ar[$i-1])) {
					$tx0 = $x0+$txf*($i-.5);
					$ty0 = $y0-$tyf*($ar[$i-1][1]-$kmm[0]);
					imagefilledrectangle($im,$tx0,$ty0-1,$tx0+1,$ty0+1,$cols[$k][0]);
				}
			}
		}
	}

	return $im;
}

function item_graph_instrument_selector($iarr) {
	$all = array_merge($iarr[0],$iarr[1]);
	sort($all);
	$s = '<form method=get>';
	$bb = '';$ii = '';
	$first=true;
	foreach($all as $k) {
		$kr = explode('/',$k);
		if($bb!=$kr[0]) {
			$bb = $kr[0];
			$ii = '';
			if(!$first) $s.="</div>\n";
			$s.= "<h2>$bb</h2>\n";
			$first = true;
		}
		if($ii!=$kr[1]) {
			$ii = $kr[1];
			if(!$first) $s.="</div>\n";
			$s.= "<h3>Instrument $ii</h3>\n";
			$first = true;
		}
		$mm = $kr[2];
		$n = 'item_'.implode('_',$kr);
		$checked = in_array($k,$iarr[0])? ' checked': '';
		if($first) $s.= "<div class=selector>\n";
		$s.= "<input$checked name=$n id=$n type=checkbox><label for=$n> $mm</label>\n";
		$first = false;
	}
	if(!$first) $s.="</div>\n";
	$s.='<input type=submit></form>';
	return $s;
}

function item_graph_data_sum_url($tarr,$iarr) {
	$params = json_encode(array($tarr[0],$iarr[0]));
	$phash = str_replace(array('+','/','=='),array('_','-','~'),base64_encode(md5($params,true)));
	if(!file_exists($dfn = "data/datalog/$phash.json")) {
		$graphdata = item_graph_get_data($tarr[0],$iarr[0]);
		json_write($dfn,$graphdata);
	}
	echo "/item/graphdata.cgi?data=$phash";
}

function item_graph_data_all_url($tarr,$iarr) {
	$params = date("Y-m-d H:i:s\n",$tarr[0][0]);
	$params.= date("Y-m-d H:i:s\n",$tarr[0][1]);
	$params.= implode(chr(10),$iarr[0]);
	$phash = str_replace(array('+','/','=='),array('_','-','!'),base64_encode(md5($params,true)));
	if(!file_exists($tfn = "data/datalog/$phash.def")) {
		file_put_contents($tfn,$params);
	}
	echo "/item/alldata.cgi?data=$phash";
}

?>
