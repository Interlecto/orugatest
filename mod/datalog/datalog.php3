<?php

function json_write($filename,$data,$options) {
	file_put_contents($filename,json_encode($data,$options));
	return true;
}

function json_read($filename,$default=array()) {
	if(!file_exists($filename)) return $default;
	$r = json_decode(file_get_contents($filename),true);
	print_r($r);
	return isset($r)? $r: $default;
}

function make_datalog($line) {
	il_put('templatefile','raw.txt');
	il_put('type','text');
	header('Content-type: text/plain');
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
	$stations = json_read('data/datalog/stations.db',array('test','0.0.0.0'));
	if(!isset($stations[$from]))
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
	$status = action_ip($from,array($stations,$_SERVER['REMOTE_ADDR']),$content)? 201: 200;
	return reply_status($status,null,$title,$content);
}

function xxxx($str) {
	echo $str;
	return 0;
}
function validate_post($from,$base,$ts) {
	if($from!=$base)
		return reply_status(401,null,"Malformed Update","Unauthorized of malformed update.");
	$stations = json_read('data/datalog/stations.db',array('test','0.0.0.0'));
	if(!isset($stations[$from]))
		return reply_status(404,null,"Station not found","Station '$from' not found.");
	if(!is_dir("data/datalog/$from"))
		ensure_path(array('data','datalog',$from));
	$actions = array('ip'=>array($stations,$_SERVER['REMOTE_ADDR']));
	$san = 9999;
	$hh = Null;
	$sign = Null;
	$pp = array();
	$content = '';
	$inst = array();
	$update = array();
	foreach($_POST as $key=>$val) {
		$pp[] = urlencode($key).'='.urlencode($val);
		switch($key) {
		case 'from':
			break;
		case 'sign':
			$sign = $val;
			array_pop($pp);
			break;
		case 'empresa':
			$actions[$key] = $val;
			break;
		case 'instrumentos':
		case 'alarmas':
			$actions[$key] = json_decode($val);
			break;
		default:
			if(preg_match('{^R(\d{4})$}',$key,$m)) {
				$san = (int)$m[1];
				$hh = $val;
			} elseif(substr($key,0,3)=='ud_') {
				if(!isset($actions['update']))
					$actions['update'] = array();
				$actions['update'][substr($key,3)] = json_decode($val);
			} else {
				if(!isset($actions['trash']))
					$actions['trash'] = array();
				$actions['trash'][$key] = json_decode($val);
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
	if($data[0][$id]!=$data[1]) {
		$r = true;
		$content.= " from address ".$data[1];
		$data[0][$id]=$data[1];
		json_write('data/datalog/stations.db',$data[0]);
	}
	$content.= ".\n";
	return $r;
}

function action_empresa($id,$data,&$content) {
	$r = false;
	$d = json_read($fn="data/datalog/$id/desc");
	if($d['name'] != $data) {
		$r = true;
		$d['name'] = $data;
		json_write($fn,$d);
	}
	return $r;
}

function action_instrumentos($id,$data,&$content) {
	$r = false;
	$content.= print_r($data,true);
	foreach($data as $iid=>$ides) {
		$d = json_read($fn="data/datalog/$id/$iid.inst");
		if(!arr_compare($ides,$d)) {
			$r = true;
			json_write($fn,$ides);
		}
	}
	return $r;
}

function arr_compare($ar1,$ar2) {
	$k1 = array_keys($ar1);
	$k2 = array_keys($ar2);
	foreach($k1 as $k) {
		if(!in_array($k,$k2)) return false;
		if(!isset($k1) && !isset($k1)) continue;
		if(is_float($ar1[$k]) && is_float($ar2[$k]) && abs($ar2[$k]-$ar1[$k])>0.0001) return false;
		if($ar1[$k] != $ar2[$k]) return false;
	}
	foreach($k2 as $k) {
		if(!in_array($k,$k1)) return false;
	}
	return true;
}

?>
