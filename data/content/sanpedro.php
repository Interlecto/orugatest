<?php
ob_start();
onuserlevel(1,'/base/sanpedro/','elite');

function sitraddate($f) {
	$n = 86400*($f-70*365-19);
	$n += 18000;
	return date('Y-m-d H:i:s',$n);
}

function write_data($n,&$data,$spec) {
	$a=array(2=>0,3=>0,4=>0,6=>0,7=>0);
	$k = array_keys($spec);
	$d = substr($k[1],0,10);
	return array("media/sanpedro-$d.csv","media/sanpedro-$d.es.csv");
	$fn="media/sanpedro-$d.csv";
	$f = fopen($fn,"w");
	$l='Fecha y hora';
	foreach($data['instrumentos'] as $i=>$r) {
		$a[$i] = $n = count($r['keys']);
		foreach($r['keys'] as $k=>$v) {
			$l.= ",$i-$v";
		}
	}
	fwrite($f,$l.chr(10));
	foreach($spec as $da=>$r) {
		if(empty($da)) continue;
		$l = "$da";
		foreach($a as $i=>$n) {
			if(isset($r[$i])) foreach($r[$i] as $k=>$v) {
				$l.=",$v";
			}
			else {
				$l.=str_repeat(',',$n);
			}
		}
		fwrite($f,$l.chr(10));
	}
	fclose($f);
	return $fn;
}

function write_cum(&$data,&$spec) {
	static $b=false;
	static $a=array(2=>0,3=>0,4=>0,6=>0,7=>0);
	return array("media/sanpedro-acum.csv","media/sanpedro-acum.es.csv");
	$fn="media/sanpedro-acum.csv";
	if($b) {
		$f = fopen($fn,"a");
	} else {
		$f = fopen($fn,"w");
		$b = true;
		$l='Fecha y hora';
		foreach($data['instrumentos'] as $i=>$r) {
			$a[$i] = $n = count($r['keys']);
			foreach($r['keys'] as $k=>$v) {
				$l.= ",$i-$v";
			}
		}
		fwrite($f,$l.chr(10));
	}
	foreach($spec as $da=>$r) {
		if(empty($da)) continue;
		$l = "$da";
		foreach($a as $i=>$n) {
			if(isset($r[$i])) foreach($r[$i] as $k=>$v) {
				$l.=",$v";
			}
			else {
				$l.=str_repeat(',',$n);
			}
		}
		fwrite($f,$l.chr(10));
	}
	fclose($f);
	return $fn;
}

function read_spsql3(&$data,$n='') {
	static $tables=array();
	static $inss = array('endereco'=>'dir','modelo'=>'modelo','tipo'=>'tipo','descricao'=>'nombre');
	static $insd = array(
		'ahc80plus' => array(
			'TemperaturaSec'=>'Temp sec',
			'TemperaturaHum'=>'Temp hum',
			'Umidade'=>'Humedad'
		),
		'mt530super' => array(
			'temperatura'=>'Temp',
			'umidade'=>'Humedad'
		),
		'Trifasico' => array(
			'VoltR'=>'Volt R',
			'VoltS'=>'Volt S',
			'VoltT'=>'Volt T',
		),
	);
	$thisfile = array();
	$dfn = "datos$n.db";
	if(!file_exists($fn = "data/datalog/sanpedro/$dfn")) return;
	$bd = new SQLite3($fn);
	if(empty($tables)) {
		$r = $bd->query("SELECT * FROM sqlite_master WHERE type='table'");
		while($row = $r->fetchArray()) {
			$tables[] = $tn = $row['name'];
			#$data[$tn] = array();
		}
		#$data['tables'] = $tables;
	}
	foreach($tables as $tab) switch($tab) {
	case 'empresa':
		$r = $bd->query("SELECT * FROM $tab");
		while($row = $r->fetchArray()) {
			if(!isset($data[$tab]))
				$data[$tab] = array($row[0]);
			elseif($row[0] != $data[$tab][0])
				array_unshift($data[$tab],$row[0]);
		}
		break;
	case 'instrumentos':
		$r = $bd->query("SELECT * FROM $tab");
		while($row = $r->fetchArray()) {
			if(!isset($data[$tab][$row['id']])) {
				$data[$tab][$row['id']] = array('class'=>'');
				foreach($row as $k=>$v) {
					if(!isset($inss[$k])) continue;
					$data[$tab][$row['id']][$inss[$k]] = $v;
				}
			}
		}
		break;
	default:
		if(!isset($insd[$tab])) break;
		$r = $bd->query("SELECT * FROM $tab");
		while($row = $r->fetchArray()) {
			$id = $row['id'];
			if(empty($data['instrumentos'][$id]['class'])) {
				$data['instrumentos'][$id]['class'] = $tab;
			}
			$date = sitraddate($row['data']);
 			if(!isset($data['instrumentos'][$id]['keys']))
				$data['instrumentos'][$id]['keys'] = $insd[$tab];
			if(!isset($thisdata['']))
				$thisdata[''] = array();
			if(!isset($thisdata[''][$id]))
				$thisdata[''][$id] = array();
			if(!isset($thisdata[$date]))
				$thisdata[$date] = array();
			if(!isset($thisdata[$date][$id]))
				$thisdata[$date][$id] = array();
/** / break;
			foreach($insd[$tab] as $k=>$v) {
				if(!isset($thisdata[''][$id][$k]))
					$thisdata[''][$id][$k] = $v;
				$vv = $row[$k];
				if(substr($k,0,1)!='V') $vv*=0.1;
				$thisdata[$date][$id][$k] = $vv;
			} // */
//**/			if(!empty($data['instrumentos'][$id]['class']) && isset($data['instrumentos'][$id]['keys'])) break;
			if(!empty($n)) break;
			$breakyet = true;
			foreach($data['instrumentos'] as $x=>$y) {
				$breakyet &= empty($y['class']);
			}
			if($breakyet) break;
		}
	}
	$bd->close();
	$f1 = write_data($n,$data,$thisdata);
	$f2 = write_cum($data,$thisdata);
	return array_merge($f1,$f2);
}

$data = array('tables'=>array());
echo "<p>Archivos (para Excel en español es preferible la versión con puntos y comas):</p><ul>";
for($i=140;$i<=152;$i++) {
	$r = read_spsql3($data,$i);
	$s = substr($r[0],15,10);
	echo "<li>Datos a partir de $s: <a href=\"/{$r[0]}\">CSV (sep. por comas)</a>,"
		." <a href=\"/{$r[1]}\">CSV (sep. por puntos y comas)</a></li>\n";
}
$r = read_spsql3($data);
$s = substr($r[0],15,10);
echo "<li>Datos a partir de $s: <a href=\"/{$r[0]}\">CSV (sep. por comas)</a>,"
	." <a href=\"/{$r[1]}\">CSV (sep. por puntos y comas)</a></li>\n";
echo "</ul>\n<p>Total:</p>\n<ul>\n"
	."<li>Acumulativo <a href=\"/{$r[2]}\">CSV (sep. por comas)</a></li>\n"
	."<li>Acumulativo <a href=\"/{$r[3]}\">CSV (sep. por puntos y comas)</a></li>\n"
	."</ul>\n";
il_put('title',$data['empresa'][0]." - backup de datos");

foreach($data['instrumentos'] as $id=>$ins) {
?>
	<p style="font-size:1.11em"><strong><?=$id?></strong>: <?=$ins['nombre']?></p>
	<table class="std-h-d">
		<tr><th>Referencia:</th><td><?=$ins['class']?></td></tr>
		<tr><th>Modelo:</th><td><?=$ins['modelo'].'/'.$ins['tipo']?></td></tr>
		<tr><th>Dirección:</th><td><?=$ins['dir']?></td></tr>
<?php
	$i = 0;
	if(isset($ins['keys'])) foreach($ins['keys'] as $k=>$v) {
		++$i;
?>
		<tr><th>Medidor <?=$i?>:</th><td><?=$v?> (<?=$k?>)</td></tr>
<?php
	}
?>
	</table>
<?php
}

return ob_get_clean();
?>
