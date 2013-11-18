<?php
ob_start();

function index($last) {
	switch($last) {
	case 'day': return 0;
	case 'week': return 1;
	case 'month': return 2;
	case 'year': return 3;
	default: return -1;
	}
}

function setdate_button($begin,$delta=0,$value=null,$id=null) {
	if(!$value) {
		$ds = array('domingo','lunes','martes','miércoles','jueves','viernes','sábado');
		$value = $ds[(int)date('w',$begin)].' '.date('j',$begin);
	}
	if($value=='mes') {
		$ms = array('diciembre','enero','febrero','marzo','abril','mayo','junio','julio','agosto','septiembre','octubre','noviembre','diciembre');
		$value = $ms[$begin<0?$begin%12+12:$begin%12];
		$end = mktime(0,0,0,$begin+1,0);
		$begin = mktime(0,0,0,$begin,1);
		if(date("Y")!=($y=date("Y",$begin))) $value.= " $y";
	} elseif($value=='anno') {
		$value = $begin;
		$end = mktime(0,0,0,12,31,$begin);
		$begin = mktime(0,0,0,1,1,$begin);
	} elseif($delta<0) {
		echo "<!-- $begin : $delta ".date('Y-m-d',$begin)." -->\n";
		$begin+= $delta*86400;
		$delta = -$delta-1;
		echo "<!-- $begin : $delta ".date('Y-m-d',$begin)." -->\n";
	}
	$idt = $id? " id='$id'": '';
	echo "\t\t<input type=button value='$value' onclick='set_dates(\"".date('Y-m-d',$begin)."\",\"".date('Y-m-d',isset($end)? $end: $begin+$delta*86400)."\")'$idt>\n";
}

function settime_button($begin,$delta=0,$value=null,$id=null) {
	if(!$value) $value = date('Y-m-d H:i');
	if($delta<0) {
		$end = $begin;
		$begin+= $delta;
	} else {
		$end = $begin+$delta;
	}
	$idt = $id? " id='$id'": '';
	echo "\t\t<input type=button value='$value' onclick='set_times(\"".date('Y-m-d',$begin)."\",\"".date('H:i',$begin)."\",\"".date('Y-m-d',$end)."\",\"".date('H:i',$end)."\")'$idt>\n";
}

function graph_day_params($param,$sub=null) {
	if(!il_get2($g = "graph_day",0)) {
		il_put2($g,0,true);
		$now = time();
		il_put2($g,'date',isset($_GET['date'])? $_GET['date']: date('Y-m-d',$now-86400));
		il_put2($g,'time',isset($_GET['time'])? $_GET['time']: date('H:i',((int)($now/3600))*3600));
		il_put2($g,'enddate',isset($_GET['enddate'])? $_GET['enddate']: date('Y-m-d',$now));
		il_put2($g,'endtime',isset($_GET['endtime'])? $_GET['endtime']: il_get2($g,'time'));
		il_put2($g,'lapse',isset($_GET['lapse'])? $_GET['lapse']: 24);
		il_put2($g,'lunits',array(isset($_GET['lunits'])? $_GET['lunits']: 'hour'=>" selected"));
		il_put2($g,'count',isset($_GET['count'])? $_GET['count']: 144);
		il_put2($g,'range',isset($_GET['range'])? $_GET['range']: 5);
		il_put2($g,'runits',array(isset($_GET['runits'])? $_GET['runits']: 'min'=>" selected"));
	}
	$a = il_get2($g,$param);
	echo $sub? (isset($a[$sub])? $a[$sub]: ''): $a;
}

function graph_week_params($param,$sub=null) {
	if(!il_get2($g = "graph_week",0)) {
		il_put2($g,0,true);
		il_put2($g,'date',isset($_GET['date'])? $_GET['date']: date('Y-m-d',time()-7*86400));
		il_put2($g,'enddate',isset($_GET['enddate'])? $_GET['enddate']: date('Y-m-d',time()-86400));
		il_put2($g,'time',isset($_GET['time'])? $_GET['time']: '00:00');
		il_put2($g,'endtime',isset($_GET['endtime'])? $_GET['endtime']: '23:59');
		il_put2($g,'lapse',isset($_GET['lapse'])? $_GET['lapse']: 7);
		il_put2($g,'lunits',array(isset($_GET['lunits'])? $_GET['lunits']: 'day'=>" selected"));
		il_put2($g,'count',isset($_GET['count'])? $_GET['count']: 168);
		il_put2($g,'range',isset($_GET['range'])? $_GET['range']: 1);
		il_put2($g,'runits',array(isset($_GET['runits'])? $_GET['runits']: 'hour'=>" selected"));
	}
	$a = il_get2($g,$param);
	echo $sub? (isset($a[$sub])? $a[$sub]: ''): $a;
}

function graph_month_params($param,$sub=null) {
	if(!il_get2($g = "graph_month",0)) {
		il_put2($g,0,true);
		il_put2($g,'date',isset($_GET['date'])? $_GET['date']: date('Y-m-d',time()-30*86400));
		il_put2($g,'enddate',isset($_GET['enddate'])? $_GET['enddate']: date('Y-m-d',time()-86400));
		il_put2($g,'time',isset($_GET['time'])? $_GET['time']: '00:00');
		il_put2($g,'endtime',isset($_GET['endtime'])? $_GET['endtime']: '23:59');
		il_put2($g,'lapse',isset($_GET['lapse'])? $_GET['lapse']: 30);
		il_put2($g,'lunits',array(isset($_GET['lunits'])? $_GET['lunits']: 'day'=>" selected"));
		il_put2($g,'count',isset($_GET['count'])? $_GET['count']: 240);
		il_put2($g,'range',isset($_GET['range'])? $_GET['range']: 3);
		il_put2($g,'runits',array(isset($_GET['runits'])? $_GET['runits']: 'hour'=>" selected"));
	}
	$a = il_get2($g,$param);
	echo $sub? (isset($a[$sub])? $a[$sub]: ''): $a;
}

function graph_year_params($param,$sub=null) {
	if(!il_get2($g = "graph_year",0)) {
		il_put2($g,0,true);
		il_put2($g,'date',isset($_GET['date'])? $_GET['date']: date('Y-m-d',time()-365*86400));
		il_put2($g,'enddate',isset($_GET['enddate'])? $_GET['enddate']: date('Y-m-d',time()-86400));
		il_put2($g,'time',isset($_GET['time'])? $_GET['time']: '00:00');
		il_put2($g,'endtime',isset($_GET['endtime'])? $_GET['endtime']: '23:59');
		il_put2($g,'lapse',isset($_GET['lapse'])? $_GET['lapse']: 365);
		il_put2($g,'lunits',array(isset($_GET['lunits'])? $_GET['lunits']: 'day'=>" selected"));
		il_put2($g,'count',isset($_GET['count'])? $_GET['count']: 52);
		il_put2($g,'range',isset($_GET['range'])? $_GET['range']: 1);
		il_put2($g,'runits',array(isset($_GET['runits'])? $_GET['runits']: 'week'=>" selected"));
	}
	$a = il_get2($g,$param);
	echo $sub? (isset($a[$sub])? $a[$sub]: ''): $a;
}

function graph_custom_params($param,$sub=null) {
	if(!il_get2($g = "graph_custom",0)) {
		if(il_get2($h="graph_".il_line_get('last'),0))
			il_copy2($g,$h);
		else {
			il_put2($g,0,true);
			il_put2($g,'date',isset($_GET['date'])? $_GET['date']: date('Y-m-d',time()-30*86400));
			il_put2($g,'enddate',isset($_GET['enddate'])? $_GET['enddate']: date('Y-m-d',time()-86400));
			il_put2($g,'time',isset($_GET['time'])? $_GET['time']: '00:00');
			il_put2($g,'endtime',isset($_GET['endtime'])? $_GET['endtime']: '23:59');
			il_put2($g,'lapse',isset($_GET['lapse'])? $_GET['lapse']: 30);
			il_put2($g,'lunits',array(isset($_GET['lunits'])? $_GET['lunits']: 'day'=>" selected"));
			il_put2($g,'count',isset($_GET['count'])? $_GET['count']: 240);
			il_put2($g,'range',isset($_GET['range'])? $_GET['range']: 3);
			il_put2($g,'runits',array(isset($_GET['runits'])? $_GET['runits']: 'hour'=>" selected"));
		}
	}
	$a = il_get2($g,$param);
	echo $sub? (isset($a[$sub])? $a[$sub]: ''): $a;
}

$now = time();

$title = "Gráfico de evolución";
$arr = get_graph_params($last,$title);
$from=$arr[0]; $fromtext = date('Y-m-d',$from);
$lapse=$arr[1]; $lapsetext = (int)$lapse/86400; $endtext = date('Y-m-d',$from+$lapse);
$range=$arr[2]; $rangetext = (int)$range/3600; $rmin=false;
if($range<3600) { $rangetext = (int)$range/60; $rmin=true; }
$N = $lapse/$range; $counttext = (int)$N;
$r = common_feed($user,$base,$item,0,$from);
il_add('scripts','<script src="/js/item-graph.js"></script>'.chr(10));
il_add('scripts','<script>$(function(){alert("'.index($last).' ¡Hola mundo!");tabActivate('.index($last).')});</script>'.chr(10));
?>
<section id=tabs>
	<ul>
		<li><a href=#day>Día</a></li>
		<li><a href=#week>Semana</a></li>
		<li><a href=#month>Mes</a></li>
		<li><a href=#year>Año</a></li>
		<li><a href=#custom>Configurable</a></li>
	</ul>
	<form action="day.php" method=GET id=day>
		<p>Las 24 horas
		<label for=day_date>que inician el <input class=datepick name=date id=day_date value="<?php graph_day_params('date')?>"></label>
		<label for=day_time>a las <input class=timesel name=time id=day_time value="<?php graph_day_params('time')?>"></label></p>
		<p><span class=selector><?php
	settime_button($aday=$now,-86400,"Últimas 24 horas",'doday');
	setdate_button($aday,0,"Hoy");
	setdate_button($aday-=86400,0,"Ayer");
?></span><span class=selector><?php
	setdate_button($aday-=86400);
	setdate_button($aday-=86400);
	setdate_button($aday-=86400);
	setdate_button($aday-=86400);
	setdate_button($aday-=86400);
?></span></p>
		<p class=selector><input type=hidden name=lapse value=24><input type=hidden name=lunits value=hour><input type=hidden name=count value=144><input name=day value=enviar type=submit> <input type=reset></p>
	</form>
	<form action="week.php" method=GET id=week>
		<p>La semana
		<label for=week_begin>que inicia el <input class=datepick name=date id=week_begin value="<?php graph_week_params('date')?>"></label>
		<label for=week_ends>y termina el <input class=datepick name=enddate id=week_ends value="<?php graph_week_params('enddate')?>"></label></p>
		<p><?php
	$aday = $now;
	setdate_button($aday,-7,'Últimos 7 días','doweek');
	$aday+= 4*86400 - $aday%(7*86400);
	if($aday>$now) $aday-= 7*86400; 
?><span class=selector><?php
	setdate_button($aday,6,'Esta semana (desde el lunes)');
	setdate_button($aday-=86400,6,'Esta semana (desde el domingo)');
?></span><span class=selector><?php
	setdate_button($aday-=6*86400,6,'Semana pasada (desde el lunes)');
	setdate_button($aday-=86400,6,'Semana pasada (desde el domingo)');
?></span></p>
		<p class=selector><input type=hidden name=time value="00:00"><input type=hidden name=lapse value=7><input type=hidden name=lunits value=day><input type=hidden name=count value=168><input name=day value=enviar type=submit> <input type=reset></p>
	</form>
	<form action="month.php" method=GET id=month>
		<p>El mes
		<label for=month_begin>que inicia el <input class=datepick name=date id=month_begin value="<?php graph_month_params('date')?>"></label>
		<label for=month_ends>y termina el <input class=datepick name=enddate id=month_ends value="<?php graph_month_params('enddate')?>"></label></p>
		<p><?php
	setdate_button($now,-30,'Últimos 30 días','domonth');
	$n = (int)date('n',$now);
?><span class=selector><?php
	setdate_button($n--,0,'mes');
	setdate_button($n--,0,'mes');
	setdate_button($n--,0,'mes');
	setdate_button($n--,0,'mes');
	setdate_button($n--,0,'mes');
	setdate_button($n--,0,'mes');
?></span></p>
		<p class=selector><input type=hidden name=time value="00:00"><input type=hidden name=lapse value=30><input type=hidden name=lunits value=day><input type=hidden name=count value=240><input name=day value=enviar type=submit> <input type=reset></p>
	</form>
	<form action="year.php" method=GET id=year>
		<p>El año
		<label for=year_begin>que inicia el <input class=datepick name=date id=year_begin value="<?php graph_year_params('date')?>"></label>
		<label for=year_ends>y termina el <input class=datepick name=enddate id=year_ends value="<?php graph_year_params('enddate')?>"></label></p>
		<p><?php
	setdate_button($now,-365,'Últimos 365 días','doyear');
	$n = (int)date('Y',$now);
?><span class=selector><?php
	setdate_button($n--,0,'anno');
	setdate_button($n--,0,'anno');
?></span></p>
		<p class=selector><input type=hidden name=time value="00:00"><input type=hidden name=lapse value=365><input type=hidden name=lunits value=day><input type=hidden name=count value=365><input name=day value=enviar type=submit> <input type=reset></p>
	</form>
	<form action="custom.php" method=GET id=custom>
		<p>El período
		<label for=custom_date>que inicia el <input class=datepick name=date id=custom_date value="<?php graph_custom_params('date')?>"></label>
		<label for=custom_time>a las <input class=timesel name=time id=custom_time value="<?php graph_custom_params('time')?>"></label>
		<label for=custom_edate>y termina el <input class=datepick name=enddate id=custom_edate value="<?php graph_custom_params('enddate')?>"></label>
		<label for=custom_etime>a las <input class=timesel name=endtime id=custom_etime value="<?php graph_custom_params('endtime')?>"></label>.
		</p>
		<p>Esto es
		<label for=custom_lapse>un período total de <input name=lapse id=custom_lapse value="<?php graph_custom_params('lapse')?>"></label>
		<select name=lunits id=lunits>
				<option value=min<?php graph_custom_params('lunits','min')?>>minutos</option>
				<option value=hour<?php graph_custom_params('lunits','hour')?>>horas</option>
				<option value=day<?php graph_custom_params('lunits','day')?>>días</option>
				<option value=week<?php graph_custom_params('lunits','week')?>>semanas</option>
				<option value=month<?php graph_custom_params('lunits','month')?>>meses</option>
				<option value=year<?php graph_custom_params('lunits','year')?>>años</option>
		</select></label>
		<label for=custom_count>usando <input class=ralign name=count id=custom_count value="<?php graph_custom_params('count')?>"></label>
		<label for=custom_range>intervalos de <input name=range id=custom_range value="<?php graph_custom_params('range')?>"></label>
		<select name=runits id=runits>
				<option value=sec<?php graph_custom_params('runits','sec')?>>segundos</option>
				<option value=min<?php graph_custom_params('runits','min')?>>minutos</option>
				<option value=hour<?php graph_custom_params('runits','hour')?>>horas</option>
				<option value=day<?php graph_custom_params('runits','day')?>>días</option>
				<option value=week<?php graph_custom_params('runits','week')?>>semanas</option>
				<option value=month<?php graph_custom_params('runits','month')?>>meses</option>
		</select></label>.
		</p>
		<p class=selector><input name=day value=enviar type=submit> <input type=reset></p>
		<div class=hidden id=alert>&nbsp;</div>
	</form>
</section>
<?php
il_put('title',$title);
return ob_get_clean();
?>
