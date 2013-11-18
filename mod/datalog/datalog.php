<?php

function make_datalog($line) {
	il_put('templatefile','raw.txt');
	il_put('type','text');
	header('Content-type: text/plain;charset=utf-8');
	if(substr($line,-4)!='.cgi')
		return reply_status(404,null,"Update script not found");

	$ts = (int)(time()/300);
	$code = substr($line,0,-4);
	$pp = array();
	if(isset($_GET['from']))
		return validate_get($_GET['from'],$code,$ts);
	if(isset($_POST['from']))
		return validate_post($_POST['from'],$code,$ts);

	return reply_status(404,null,"Incomplete data");
}

function validate_get($from,$base,$ts) {
	if($from!=$base)
		return reply_status(401,null,"Malformed Keepalive","Unauthorized of malformed keepalive.");
	//$stations = json_read('data/datalog/stations.db',array('test','0.0.0.0'));
	$station = il_select('dl_station','*',array('station'=>"=$base"));
	//if(!isset($stations[$from]))
	if(empty($station) || !count($station))
		return reply_status(404,null,"Station not found","Station '$from' not found.");
	$san = 9999;
	$hh = Null;
	foreach($_GET as $key=>$val) {
		if(preg_match('{^R(\d{4})$}',$key,$m)) {
			$san = (int)$m[1];
			$hh = $val;
		}
	}
	if(!$hh)
		return reply_status(401,null,"Malformed Keepalive","This keepalive is not signed.");
	$rocks = array(
		sprintf("[%09d%-9s:%4d]",$ts,$from,$san),
		md5(sprintf("[%09d%-9s:%4d]",$ts-1,$from,$san)),
		md5(sprintf("[%09d%-9s:%4d]",$ts,$from,$san)),
		md5(sprintf("[%09d%-9s:%4d]",$ts+1,$from,$san)),
	);
	if(!in_array($hh,$rocks))
		return reply_status(401,null,"Malformed Keepalive","Incorrect signature.");
	$content = $title = "Keepalive from $from";
	$status = action_ip($from,array($station[0],$_SERVER['REMOTE_ADDR']),$content)? 201: 200;
	return reply_status($status,null,$title,$content);
}

function xxxx($str) {
	echo $str;
	return 0;
}
function validate_post($from,$base,$ts) {
	if($from!=$base)
		return reply_status(401,null,"Malformed Update","Unauthorized of malformed update.");
	//$stations = json_read('data/datalog/stations.db',array('test','0.0.0.0'));
	$station = il_select('dl_station','*',array('station'=>"=$base"));
	//if(!isset($stations[$from]))
	if(empty($station) || !count($station))
		return reply_status(404,null,"Station not found","Station '$from' not found.");
	if(!is_dir("data/datalog/$from"))
		ensure_path(array('data','datalog',$from));
	$actions = array('ip'=>array($station[0],$_SERVER['REMOTE_ADDR']));
	$san = 9999;
	$hh = Null;
	$sign = Null;
	$pp = array();
	$content = '';
	$inst = array();
	$update = array();
	foreach($_POST as $key=>$val) {
		$pp[] = urlencode($key).'='.urlencode($val);
		#$content.="$key = ".print_r($val,true).chr(10);
		switch($key) {
		case 'from':
			break;
		case 'sign':
			$sign = $val;
			array_pop($pp);
			break;
		case 'empresa':
			$actions[$key] = array($station[0],$val);
			break;
		case 'instrumentos':
		case 'alarmas':
			$actions[$key] = json_decode($val,true);
			break;
		default:
			if(preg_match('{^R(\d{4})$}',$key,$m)) {
				$san = (int)$m[1];
				$hh = $val;
			} elseif(substr($key,0,3)=='ud_') {
				if(!isset($actions['update']))
					$actions['update'] = array();
				$actions['update'][substr($key,3)] = $uxx = json_decode($val,true);
				if(isset($uxx[0]['time']) && $uxx[0]['time']>3000000000) {
					for($i=0;$i<count($uxx);$i++) {
						$uxx[$i]['time'] -= 1795585288;
					}
					$actions['update'][substr($key,3)] = $uxx;
				}
				$content.= "$key ".print_r($uxx[0],true)."\n";
				#$content.= "$key ".print_r($uxx,true)."\n";
			} else {
				if(!isset($actions['trash']))
					$actions['trash'] = array();
				$actions['trash'][$key] = json_decode($val,true);
			}
		}
	}
	if(!$sign)
		return reply_status(401,null,"Malformed Update","This update does not have verification code.");
	$prepar = 'O:'.implode('&',$pp).':A';
	if($sign !== md5($prepar))
		return reply_status(401,null,"Malformed Update","This update's verification code is invalid.\n$prepar\n");
	if(!$hh)
		return reply_status(401,null,"Malformed Update","This update is not signed.");
	$rocks = array(
		sprintf("[%09d%-9s:%4d]",$ts,$from,$san),
		md5(sprintf("[%09d%-9s:%4d]",$ts-1,$from,$san)),
		md5(sprintf("[%09d%-9s:%4d]",$ts,$from,$san)),
		md5(sprintf("[%09d%-9s:%4d]",$ts+1,$from,$san)),
	);
	if(!in_array($hh,$rocks))
		return reply_status(401,null,"Malformed Update","Incorrect signature.");
	if($content) {
		$title = "Udate from $from";
		$content = "title\n$content";
	}
	else
		$content = $title = "Update from $from";
	$ud = false;
	foreach($actions as $action=>$data) {
		if(function_exists($func="action_$action"))
			$ud|= $func($from,$data,$content);
		else
			$content.= "Undefinded function for $action.\n";
	}
	return reply_status($ud?201:200,null,$title,$content);
}

function action_ip($id,$data,&$content) {
	$r = false;
	if($data[0]['ip']!=$data[1]) {
		$r = true;
		$content.= " from address ".$data[1];
		//$data[0][$id]=$data[1];
		//json_write('data/datalog/stations.db',$data[0]);
		$ipe = il_escape($data[1]);
		$ide = il_escape($id);
		il_query("UPDATE `dl_station` SET `ip`='$ipe' WHERE `station`='$ide';\n");
	}
	$content.= ".\n";
	return $r;
}

function action_empresa($id,$data,&$content) {
	$r = false;
	if($data[0]['name'] != $data[1]) {
		$r = true;
		$nme = il_escape($data[1]);
		$ide = il_escape($id);
		il_query("UPDATE `dl_station` SET `name`='$nme' WHERE `station`='$ide';\n");
	}
	return $r;
}

function action_instrumentos($id,$data,&$content) {
	$r = false;

	$inss = array_keys($data);
	$where = array('station'=>"=$id", 'address'=>$inss);
	$insa = il_select('dl_instrument','*',$where);
	$insb = il_select('dl_param','*',$where);
	$insc = il_select('dl_meter','*',array_merge($where,array('type'=>"=common")));

	$i = 0;
	$ia = array();
	foreach($insa as $ir) $ia[$ir['address']] = $ir;
	foreach($insb as $ir) $ia[$ir['address']][$ir['param']] = $ir['value'];
	foreach($insc as $ir) $ia[$ir['address']][++$i] = array($ir['min'],$ir['max']);

	$sto = il_escape($id);
	foreach($data as $iid=>$ides) {
		//$d = json_read($fn="data/datalog/$id/$iid.inst");
		$d = $ia[$iid];
		$ino = (int)$iid;
		$wh = "WHERE `station`='$sto' AND `address`=$ino";
		if(!isset($d['description'])) {
			$dd = il_escape($ides['description']);
			il_query("INSERT INTO `dl_instrument`(`station`,`address`,`description`) VALUES ('$id',$ino,'$dd')\n");
			$r = true;
		} elseif($ides['description']!=$d['description']) {
			$dd = il_escape($ides['description']);
			il_query("UPDATE `dl_instrument` SET `description`='$dd' $wh;\n");
			$r = true;
		}
		$r0 = array();
		foreach($ides as $k=>$v) {
			if($k=='description') continue;
			if(substr($k,0,5)=='alarm') continue;
			$ke = il_escape($k);
			$ve = il_escape($v);
			if(!isset($d[$k])) {
				$i = $k=='address'? 2: 1;
				$r0[] = "('$id',$ino,'$ke',$i,'$ve')";
			} elseif($v != $d[$k]) {
				il_query("UPDATE `dl_param` SET `value`='$ve' WHERE `param`='$ke' AND `station`='$id' AND `address`=$ino;\n");
				$r = true;
			}
		}
		if(count($r0)) {
			il_query('INSERT INTO `dl_param`(`station`,`address`,`param`,`param_idx`,`value`) VALUES '.implode(', ',$r0).";\n");
			$r = true;
		}
		/*
		if(!arr_compare($ides,$d)) {
			$r = true;
			$d = array_merge($d,$ides);
			json_write($fn,$d);
		}*/
	}
	return $r;
}

function action_alarmas($id,$data,&$content) {
	$r = false;
	foreach($data as $alarm) {
		$vals = array_values($alarm);
		$keys = array_keys($alarm);
		$ins = (int)$alarm['id'];
		$wh0 = array(
			'station'=>"=$id",
			'address'=>"=$ins"
		);
		$wh1 = array(
			'name'=>"=".$alarm['description'],
		);
		$al = il_select('dl_meter','*',array_merge($wh0,$wh1));
		if(!$al) {
			$al = il_select('dl_meter','*',$wh1);
			$ede = il_escape($alarm['description']);
			if($al) {
				$kw = $al[0]['keyword'];
				$ekw = il_escape($kw);
				il_query("INSERT INTO `dl_meter`(`station`, `address`, `keyword`, `type`, `name`, `format`) ".
					"VALUES ('$id',$ins,'$ekw','alarm','$ede','%d');\n");
			} else {
				$al = il_select('dl_meter','*',array('type'=>"=alarm"));
				$i;
				foreach($al as $row) if($i<=($j=(int)substr($row['keyword'],6))) $i = 1+$j;
				$kw = "alarm-$i";
				$ekw = il_escape($kw);
				il_query("INSERT INTO `dl_meter`(`station`, `address`, `keyword`, `type`, `name`, `format`) ".
					"VALUES ('$id',$ins,'$ekw','alarm','$ede','%d');\n");
			}
		} else {
			$kw = $al[0]['keyword'];
			$ekw = il_escape($kw);
		}
		if(!empty($alarm['time_beg'])) {
			$t = $alarm['time_beg'];
			$wh0['time'] = '='.($tm = date('Y-m-d H:i:s',$t));
			$q = il_select('dl_status_i','*',$wh0);
			if($q && count($q)) {
				$sti = (int)$q[0]['idx'];
			} else {
				il_query("INSERT INTO `dl_status_i`(`time`,`station`,`address`)VALUES('$tm','$id',$ins);\n");
				$q = il_select('dl_status_i','*',$wh0);
				if(!$q) continue;
				$sti = (int)$q[0]['idx'];
			}
			$val = (empty($alarm['time_end']) || (int)$alarm['time_end']!=(int)$alarm['time_beg'])? 1: 0;
			il_query("INSERT INTO `dl_status_p`(`status`, `keyword`, `value`)VALUES($sti,'$ekw',$val);\n");
		}
		if(!empty($alarm['time_end']) && (int)$alarm['time_end']!=(int)$alarm['time_beg']) {
			$t = $alarm['time_end'];
			$wh0['time'] = '='.($tm = date('Y-m-d H:i:s',$t));
			$q = il_select('dl_status_i','*',$wh0);
			if($q && count($q)) {
				$sti = (int)$q[0]['idx'];
			} else {
				il_query("INSERT INTO `dl_status_i`(`time`,`station`,`address`)VALUES('$tm','$id',$ins)");
				$q = il_select('dl_status_i','*',$wh0);
				if(!$q) continue;
				$sti = (int)$q[0]['idx'];
			}
			il_query("INSERT INTO `dl_status_p`(`status`, `keyword`, `value`)VALUES($sti,'$ekw',-1);\n");
		}
	}
	return $r;
}

function action_update($id,$data,&$content) {
	$r = false;
	$est = il_escape($id);
	foreach($data as $ref=>$updates) {
		$content .= "$ref (".count($updates).")\n";
		$ere = il_escape($ref);
		if($updates) {
			$iii = array();
			foreach($updates as $update) {
				$ins = (int)$update['id'];
				if(!isset($iii[$ins]))
					$iii[$ins] = array();
			}
			foreach($iii as $ins=>$b) {
				$wh0 = array(
					'station'=>"=$id",
					'address'=>"=$ins"
				);
				$d = il_select('dl_instrument','*',$wh0);
				if(!$d) {
					il_query("INSERT INTO `dl_instrument`(`station`, `address`, `reference`) VALUES ('$est',$ins,'$ere');\n");
				} elseif($d[0]['reference']!=$ref) {
					il_query("UPDATE `dl_instrument` SET `reference`='$ere' WHERE `station`='$est' AND `address`=$ins\n");
				}
				$m = il_select('dl_meter','*',$wh0);
				foreach($m as $l) $iii[$ins][$m['keyword']] = $m;
			}
			foreach($updates as $update) {
				$ins = (int)$update['id'];
				$mm = $iii[$ins];
				$time = $update['time'];
				$r |= insert_statuses($time,$id,$ins,$update,$mm);
			}
		}
	}
	return $r;
}

function action_trash($id,$data,&$content) {
	$content.= "=== $id ===\n";
	$content.= print_r($data,true);
}

function arr_compare($ar1,$ar2) {
	if(is_object($ar1)) $ar1 = (array)$ar1;
	if(is_object($ar2)) $ar2 = (array)$ar2;
	$k1 = array_keys($ar1);
	$k2 = array_keys($ar2);
	foreach($k1 as $k) {
		if(!in_array($k,$k2)) { echo "$k not in \$k2\n"; return false; }
		if(!isset($k1) && !isset($k1)) continue;
		if(is_float($ar1[$k]) && is_float($ar2[$k]) && abs($ar2[$k]-$ar1[$k])<0.0001) continue;
		if($ar1[$k] != $ar2[$k]) { echo "'{$ar2[$k]}' '{$ar1[$k]}' are not equal\n"; return false; }
	}
	foreach($k2 as $k) {
		if(!in_array($k,$k1)){ echo "$k not in \$k1\n"; return false; }
	}
	return true;
}

function insert_statuses($time,$base,$item,$starray,$meters=null) {
	$r = false;
	$t = date('Y-m-d H:i:s',$time);
	$ins = (int)$item;
	$bb = il_escape($base);
	if(is_null($meters)) $meters=array();
	foreach($starray as $k=>$v) {
		if(!isset($meters[$k])) {
			$ek = il_escape($k);
			il_query("INSERT INTO `dl_meter` SET `station`='$bb', `address`=$ins, `keyword`='$ek', `type`='flag';\n");
		}
	}
	$wh1 = array('time'=>"=$t",'station'=>"=$base",'address'=>"=$ins");
	#get the status id; create a new status if it doesn't exists
	$st = il_select('dl_status_i','idx',$wh1);
	while(empty($st)) {
		il_query("INSERT INTO `dl_status_i` SET `time`='$t', `station`='$bb', `address`=$ins;\n");
		$st = il_select('dl_status_i','idx',$wh1);
	}
	$sid = (int)$st[0]['idx'];
	#check if there are some values with that status id
	$siv = il_select('dl_status_p','*',array('status'=>"=$sid")) or array();
	$chvl = array();
	$sup = array('id','time');
	$nik = array_keys($starray);
	foreach($siv as $sir) {
		$kw = $sir['keyword'];
		if(in_array($kw,$nik)) {
			if($sir['value']==$starray[$kw]) $sup[] = $kw;
			else {
				$ekw = il_escape($kw);
				$evl = il_escape($starray[$kw]);
				il_query("UPDATE `dl_status_p` SET `value`='$evl' WHERE `status`=$sid AND `keyword`='$ekw';\n");
				$sup[] = $kw;
				$r = true;
			}
		}
	}
	$values = array();
	foreach($starray as $kw=>$val) {
		if(in_array($kw,$sup)) continue;
		$ekw = il_escape($kw);
		$evl = il_escape($val);
		$values[] = "($sid,'$ekw','$evl')";
	}
	if(count($values)) {
		il_query("INSERT INTO `dl_status_p`(`status`,`keyword`,`value`) VALUES ".implode(', ',$values).";\n");
		$r = true;
	}
	return $r;
}

?>
