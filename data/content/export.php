<?php
checkorredirect('export.cgi');
ob_start();
il_put('templatefile','raw.txt');
il_put('type','text');
header('Content-type: text/plain');

$dayX0 = isset($_GET['day'])? (int)(strtotime($_GET['day'].' 00:00:00')/86400): (int)(time()/86400);
$dayX1 = isset($_GET['to'])? (int)(strtotime($_GET['to'].' 00:00:00')/86400): $dayX0+1;

echo ">>>Exporting from ".date('M d, H:i',$dayX0*86400)." to ".date('M d, H:i',$dayX1*86400)." <<<\n";
echo "['$dayX0', '$dayX1']\n\n";

$nokk = array('id','stage','time','period');
$or = il_get('or');

il_add('queries',"\n");
$d = listdir('data/datalog',2,true);
foreach($d as $bb=>$bd) {
	if($bd[1] || !$bd[2]) continue;
	if($bb=='test') continue;
	/**************** Cecking and updating BASE STATION ****************/
	$ba = json_read($fn=$bd[0].'/desc');
	$ip = isset($ba['ip'])? $ba['ip']: '0.0.0.0';
	$eip = db_str($ip);
	$bname = db_str($bb);
	$whb = array('id'=>"=$bb");
	$whbb= array('station'=>"=$bb");
	$bo = db_select_first('user','*',$whb);
	if($bo!==false) {
		db_comment("Base '$bb' existe.");
		if(isset($ba['name']) && $ba['name'] != $bo['name']) {
			$fname = db_str($name = $ba['name']);
			db_update('user',array('name'=>$name),$whb);
			db_update('dl_station',array('name'=>$name),$whbb);
		} else {
			$name = $ba['name'] = $bo['name'];
		}
	} else {
		db_comment("Base '$bb' NO existe.");
		$fname = db_str($name = isset($ba['name'])? $ba['name']: 'Base '.$bb);
		$pw = md5("[$bb:1234]");
		db_insert('user',array('id'=>$bb,'hatch'=>$pw));
		db_insert('user_group',array('user'=>$bb,'group'=>'elite','role'=>1));
		db_insert('dl_station',array('station'=>$bb,'name'=>$name,'ip'=>$ip,'public'=>0));
	}
	echo "$name: ";
	print_r($ba);

	if(isset($ba['avatar'])) {
		echo $ba['avatar'].chr(10);
		$n = strrpos($ba['avatar'],'/');
		if($n===false) {
			$dir   = '';
			$thumb = $ba['avatar'];
		} else {
			$dir   = substr($ba['avatar'],0,$n+1);
			$thumb = substr($ba['avatar'],  $n+1);
		}
		$wha = array('basedir'=>"=$dir",'small'=>"=$thumb");
		if(!($avi=db_select_one('avatar','idx',$wha))) {
			db_insert('avatar',array('basedir'=>$dir,'small'=>$thumb));
			if(!($avi=db_select_one('avatar','idx',$wha)))
				echo "Avatar was not created.";
		}
		if(isset($avi) && $avi!==false)
			db_update('user',array('avatar'=>$avi),array_merge($whb,array('avatar'=>"#")));
	} else echo "$name has no avatar.\n\n";

	foreach($bd[3] as $ii=>$id) {
		if($id[1] || !$id[2]) continue;
		//**/if($bb=='valentina'&&$ii<3) continue;
		/**************** Cecking and updating STATION INSTRUMENT ****************/
		$ia = json_read($fn=$id[0].'.inst');
		$ino = (int)$ii;
		$whbi = array('station'=>$bb,'address'=>$ii);
		$io = db_select_first('dl_instrument','*',$whbi);
		if($io) {
			if(isset($ia['description']) && $ia['description'] != $io['description']) {
				$idesc = db_str($desc = $ia['description']);
				db_update('dl_instrument',array('description'=>$desc),$whbi);
			} else {
				$desc = $ia['description'] = $io['description'];
			}
		} else {
			$idesc = db_str($desc = isset($ia['description'])? $ia['description']: 'Instrumento '.$ii);
			db_insert('dl_instrument',array_merge($whbi,array('description'=>$desc)));
			$io = db_select_first('dl_instrument','*',$whbi);
		}
		db_comment("Instrumento: $desc");

		if(isset($ia['avatar'])) {
			echo $ia['avatar'].chr(10);
			$n = strrpos($ia['avatar'],'/');
			if($n===false) {
				$dir   = '';
				$thumb = $ia['avatar'];
			} else {
				$dir   = substr($ia['avatar'],0,$n+1);
				$thumb = substr($ia['avatar'],  $n+1);
			}
			$wha = array('basedir'=>"=$dir",'small'=>"=$thumb");
			if(!($avi=db_select_one('avatar','idx',$wha))) {
				db_insert('avatar',array('basedir'=>$dir,'small'=>$thumb));
				if(!($avi=db_select_one('avatar','idx',$wha)))
					echo "Avatar was not created.";
			}
			if(isset($avi) && $avi!==false)
				db_update('dl_instrument',array('avatar'=>$avi),array_merge($whbi,array('avatar'=>"#")));
		} else echo "$desc has no avatar.\n\n";

		$r = db_select('dl_param','*',$whbi);
		$q = array();
		foreach($r as $row) $q[$row['param']] = $row['value'];
		foreach($ia as $key=>$val) {
			if(substr($key,0,5)=='alarm' || $key=='description' || $key=='avatar') continue;
			$whpar = array_merge($whbi,array('param'=>$key));
			$setpar = array('param_idx'=>($key=='address'? 2: 1), 'value'=>$val);
			if(!isset($q[$key]))
				db_insert('dl_param',array_merge($whpar,$setpar));
			elseif($q[$key]!=$val)
				db_update('dl_param',$setpar,$whpar);
		}

		$q2 = db_select_key('dl_meter',array('keyword','type','format','min','max'),$whbi);

		unset($in);
		unset($vo);
		$iq = array();
		foreach($id[3] as $di=>$dd) {
			/**************** Cecking and updating INSTRUMENT STATUS ****************/
			if($dd[1] || $dd[2]) continue;
			if(substr($di,-2)!='.d') continue;
			if((int)$di<$dayX0) continue;
			if((int)$di>=$dayX1) continue;

			$day0 = date('Y-m-d H:i:s',$di*86400);
			$day1 = date('Y-m-d H:i:s',(1.1+$di)*86400);
			#echo "Chequing $di (from $day0 to $day1)\n";

			$r4 = db_select_key('dl_status_i',array('idx','time'),array_merge($whbi,array('time'=>"&{$day0}&{$day1}")),null,0);
			$q4 = array();
			$qt = array();
			foreach($r4 as $idx=>$x) {
				$qt[$x['time']] = $idx;
				$q4[$idx] = db_select_pairs('dl_status_p','keyword','value',array('status'=>"=$idx"),null,0);
			}
			#echo '$q4 - '; print_r($q4);
			#echo '$qt - '; print_r($qt);
			$it = (int) substr($di,0,-2);
			$da = json_read($dd[0]);
			$dk = $da['keys'];
			// $io (dl_item), $q2 (dl_meter), $q3 (dl_alarm), $q4 (dl_status)
			foreach($dk as $in=>$iv) {
				if($in=='alarm') continue;
				if(empty($iq)) {
					$iq = $iv;
					if(count($iq)<=4) { echo "##################################\n"; print_r($dk); }
					$i = 0;
					foreach($iv as $k) {
						if(in_array($k,$nokk)) continue;
						++$i;
						$all = $i<=6? $ia["alarm{$i}L"]: null;
						$alh = $i<=6? $ia["alarm{$i}H"]: null;
						$whbim = array_merge($whbi,array('keyword'=>$k));
						$setdf = isset($or[$k])?
							array('type'=>'common','name'=>$or[$k][0],'format'=>'%.1f'.$or[$k][1]):
							array('type'=>'flag','format'=>'%d');
						$setal = array('min'=>$all,'max'=>$alh);
						if(empty($all) && empty($alh)) {
							if(!isset($q2[$k])) {
								db_insert('dl_meter',array_merge($whbim,$setdf));
							}
						} else {
							if(isset($q2[$k])) {
								$mm = $q2[$k];
								if($all!=$mm['min'] || $alh!=$mm['max'])
									db_update('dl_meter',$setal,$whbim); // 3103351653
							} else {
								db_insert('dl_meter',array_merge($whbim,$setdf,$setal));
							}
						}
					}
				}

				if($in=='instrument') continue;
				if(empty($io['reference'])) {
					$ein = db_str($io['reference'] = $in);
					db_update('dl_item',array('reference'=>$in),$whbi);
				}
			}

			$C = 0;
			foreach($da as $dtm=>$values) {
				if($dtm=='keys') continue;
				#echo "Estado: ($dtm)\n";
				foreach($values as $ik=>$iw) {
					if($ik=='alarm') {
						$tibe = empty($iw[2])? null: date('Y-m-d H:i:s',$iw[2]);
						$stbe = isset($qt[$tibe])? $qt[$tibe]: null;
						$tien = empty($iw[3])? null: date('Y-m-d H:i:s',$iw[3]);
						$sten = isset($qt[$tien])? $qt[$tien]: null;
						db_comment("alarmas para $tibe-$tien");
						$alde = empty($iw[1])? null: $iw[1];
						$whad = array('name'=>$alde,'type'=>'alarm');
						#get if the alarm type has already been reported for this instrument
						$alkw = db_select_one('dl_meter','keyword',array_merge($whbi,$whad));
						if(!$alkw) $alkw = db_select_one('dl_meter','keyword',$whad); // or for any instrument
						if(!$alkw) {
							$alkws = db_select_one('dl_meter','keyword',array('type'=>'=alarm'),true);
							for($j=0;!in_array($alkw="alarm-$j",$alkws);$j++);
							db_insert('db_meter',array_merge($whbi,$whad,array('keyword'=>$alkw,'format'=>'%d')));
						}
						$setal = array();
						if(!isset($q4[$stbe][$alkw])) {
							if($stbe) {
								$setal[] = array($stbe,$alkw,$stbe==$sten?0:1);
							} elseif($tibe) {
								$whtb = array_merge(array('time'=>$tibe),$whbi);
								$stbe = db_select_one('dl_status_i','idx',$whtb);
								if(!$stbe) {
									db_insert('dl_status_i',$whtb);
									$stbe = db_select_one('dl_status_i','idx',$whtb);
								}
								if($stbe)
									$setal[] = array($stbe,$alkw,$tibe==$tien?0:1);
							}
						}
						if(!isset($q4[$sten][$alkw])) {
							if($sten && $sten!=$stbe) {
								$setal[] = array($sten,$alkw,-1);
							} elseif($tien && $tien!=$tibe) {
								$whte = array_merge(array('time'=>$tien),$whbi);
								$sten = db_select_one('dl_status_i','idx',$whte);
								if(!$sten) {
									db_insert('dl_status_i',$whte);
									$sten = db_select_one('dl_status_i','idx',$whte);
								}
								if($sten)
									$setal[] = array($sten,$alkw,-1);
							}
						}
						if(count($setal)) db_insert('dl_status_p',$setal,array('status','keyword','value'));
					} else {
						$vo = array_combine($iq,$iw);
						//print_r(array($dk,$iq,$ik,$iv,$iw,$vo));
						$qvar = array();
						$time = date('Y-m-d H:i:s',$vo['time']);
						if(isset($qt[$time]))
							$stme = $qt[$time];
						else {
							$whst = array_merge(array('time'=>$time),$whbi);
							$stme = db_select_one('dl_status_i','idx',$whst);
							if(!$stme) {
								db_insert('dl_status_i',$whst);
								$stme = db_select_one('dl_status_i','idx',$whst);
							}
							if(!$stme) continue;
						}

						foreach($vo as $ok=>$ov) {
							if(in_array($ok,$nokk)) continue;
							if(isset($q4[$stme][$ok])) continue;
							$qvar[] = array($stme,$ok,$ov);
						}
						if(count($qvar)) db_insert('dl_status_p',$qvar,array('status','keyword','value'));
					}
				}
				$C++;
				//if($C>=20) break;
			}

			echo ">>>> $di (".implode(',',array_keys($dk))."): ";
			echo "$C status.\n";
			//print_r($da['keys']);
			//echo implode(', ',array_keys($da))."\n";
		}
	}
}

echo "The Queries: \n".il_get('queries');
return ob_get_clean();
?>
