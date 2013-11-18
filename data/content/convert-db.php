<?php
ob_start();

$d = listdir('data/datalog',3,true);

function cvs_deci($x){return 0.1*$x;}
function cvs_data($x){return ($x-25568)*86400-57600;}
function cvs_bool($x){return (bool)$x;}
function cvs_int($x){return (int)$x;}
il_put('oon',array(
	'status'=>false,
	'text'=>false,
    'temperatura'=>'temperature',
    'temperatura1'=>'temp_dry',
    'TemperaturaSec'=>'temp_dry',
    'temperatura2'=>'temp_wet',
    'TemperaturaHum'=>'temp_wet',
    'umidade'=>'humidity',
    'Umidade'=>'humidity',
    'tensao'=>'tension',
    'data'=>'time',
    'dataInicio'=>'time_beg',
    'dataFim'=>'time_end',
    'periodo'=>'period',
    'estagio'=>'stage',
    'endereco'=>'address',
    'descricao'=>'description',
    'datacad'=>'time_cad',
    'alarme1L'=>'alarm1L',
    'alarme1H'=>'alarm1H',
    'alarme2L'=>'alarm2L',
    'alarme2H'=>'alarm2H',
    'alarme3L'=>'alarm3L',
    'alarme3H'=>'alarm3H',
    'alarme4L'=>'alarm4L',
    'alarme4H'=>'alarm4H',
    'alarme5L'=>'alarm5L',
    'alarme5H'=>'alarm5H',
    'alarme6L'=>'alarm6L',
    'alarme6H'=>'alarm6H',
));
il_put('oot',array(
    'temperatura'=>'cvs_deci',
    'temperatura1'=>'cvs_deci',
    'TemperaturaSec'=>'cvs_deci',
    'temperatura2'=>'cvs_deci',
    'TemperaturaHum'=>'cvs_deci',
    'umidade'=>'cvs_deci',
    'Umidade'=>'cvs_deci',
    'tensao'=>'cvs_deci',
    'data'=>'cvs_data',
    'dataInicio'=>'cvs_data',
    'dataFim'=>'cvs_data',
    'datacad'=>'cvs_data',
    'alarme1L'=>'cvs_deci',
    'alarme1H'=>'cvs_deci',
    'alarme2L'=>'cvs_deci',
    'alarme2H'=>'cvs_deci',
    'alarme3L'=>'cvs_deci',
    'alarme3H'=>'cvs_deci',
    'alarme4L'=>'cvs_deci',
    'alarme4H'=>'cvs_deci',
    'alarme5L'=>'cvs_deci',
    'alarme5H'=>'cvs_deci',
    'alarme6L'=>'cvs_deci',
    'alarme6H'=>'cvs_deci',
	'THERM'=>'cvs_bool',
	'HUMID'=>'cvs_bool',
	'AUX'=>'cvs_bool',
	'BUZZ'=>'cvs_bool',
	'ST1'=>'cvs_bool',
	'ST2'=>'cvs_bool',
	'id'=>'cvs_int',
	'periodo'=>'cvs_int',
));
il_put('oor',array(
	'id','descricao','endereco',
	'temperatura','temperatura1','TemperaturaSec','temperatura2','TemperaturaHum','umidade','Umidade','tensao',
	"THERM","HUMID","AUX","BUZZ","def","fan","comp","alrm","eco","digital1","digital2",'ST1','ST2',
	'estagio','data','dataInicio','dataFim','periodo'
	));
#		"alarm":["id","description","time_beg","time_end"],
#		"tc960":["id","temp_dry","temp_wet","tension","def","fan","comp","alrm","eco","digital1","digital2","stage","time","period"]
#		"mt530super":{"id":2,"temperature":20.4,"humidity":54.1,"THERM":true,"HUMID":true,"AUX":true,"BUZZ":false,"time":1352319891.993,"period":5000}

function convertStatus(&$sa) {
	$oon = il_get('oon');
	$oot = il_get('oot');
	$oor = il_get('oor');
	$ra = array();
	foreach($oor as $k)
		if(isset($sa[$k]))
			$ra[isset($oon[$k])?$oon[$k]:$k] = isset($oot[$k])? $oot[$k]($sa[$k]): $sa[$k];
	foreach($sa as $k=>$v)
		if(in_array($k,$oor)) continue;
		elseif(isset($oon[$k]) && $oon[$k]===false) continue;
		else $ra[$k] = $v;
	$sa = $ra;
}

$CC = json_read('data/datalog/convertion.status');
$C = 0;
foreach($d as $bb=>$bd) {
	if($bd[1] || !$bd[2]) continue;
	if(!isset($CC[$bb])) $CC[$bb]=array();
	$ba = json_read($fn=$bd[0].'/desc');
	if(empty($ba) && file_exists($fn)) {
		dat2array($fn,$ba);
		json_write($fn,$ba);
	}
	$name = isset($ba['name'])? $ba['name']: 'Base '.$bb;
	echo "$bb\t$name\n";
	foreach($bd[3] as $ii=>$id) {
		if($id[1] || !$id[2]) continue;
		if(!isset($CC[$bb][$ii])) $CC[$bb][$ii]=array();
		$iii = array();
		$ia = json_read($fn=$id[0].'.inst');
		if(empty($ia) && file_exists($fn))
			dat2array($fn,$ia);
		$desc = isset($ia['description'])? $ia['description']: 'Instrumento '.$ii;
		echo "\t$ii\t$desc\n";
		foreach($id[3] as $di=>$dd) {
			if($dd[1] || !$dd[2]) continue;
			if(!isset($CC[$bb][$ii][$di])) $CC[$bb][$ii][$di] = 0;
			$date = mktime(0,0,0,substr($di,4,2),substr($di,6,2),substr($di,0,4));
			$ds = date('Y-m-d',$date);
			$day = (int)($date/86400);
			$dfn = $id[0]."/$day.d";
			#if(file_exists($dfn)) continue;
			echo "\t\t$di\t$day\t$ds";
			$C+= $c = $i = $CC[$bb][$ii][$di];
			foreach($dd[3] as $ss=>$sf) {
				if($sf[1]) continue;
				if($i-->0) continue;
				$sa = array();
				dat2array($sf[0],$sa);
				convertStatus($sa);
				$t = isset($sa['time'])?$sa['time']:(isset($sa['time_beg'])?$sa['time_beg']:0);
				$ddd = (int)($t/86400);
				$sss = (int)$t % 86400;
				if(!isset($iii[$ddd])) $iii[$ddd] = json_read($id[0]."/$ddd.d");
				$n = isset($sa['time'])?'instrument':(isset($sa['time_beg'])?'alarm':'n/a');
				$kkk = array_keys($sa);
				if(!isset($iii[$ddd]['keys'])) $iii[$ddd]['keys'] = array();
				if(!isset($iii[$ddd]['keys'][$n])) $iii[$ddd]['keys'][$n] = $kkk;
				$vvv = array_values($sa);
				if(!isset($iii[$ddd][$sss])) $iii[$ddd][$sss] = array($n=>$vvv);

				$ts = date('Y-m-d H:i:s',$t);
				$s2 = (int)$t % 86400;
				#print_r($iii);
				#echo "\t$ss =? $ts\t".($t-$adate)."\t".(($t-$adate)/3600);
				$c++;
				#break;
			}
			echo "\t($c)\n";
			$CC[$bb][$ii][$di] = $c;
		}
		foreach($iii as $ddd=>$ooo) {
			json_write($fn=($id[0]."/$ddd.d"),$ooo);
			echo "Written $fn\n";
			json_write('data/datalog/convertion.status',$CC);
		}
	}
}
return ob_get_clean();
?>
