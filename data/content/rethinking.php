<?php
checkorredirect("/rethinking.php");
ob_start();

$sixtydays = date('Y-m-d H:i:s',time()-60*86400);
$queries = array(
	"DROP TABLE IF EXISTS `dl_si_{};`",
	"DROP TABLE IF EXISTS `dl_sibu_{};`",
	"DROP TABLE IF EXISTS `dl_sp_{};`",
	"DROP TABLE IF EXISTS `dl_spbu_{};`",
	"CREATE TABLE IF NOT EXISTS `dls_l_{}` LIKE `x_dls_live`;",
	"CREATE TABLE IF NOT EXISTS `dls_oi_{}` LIKE `x_dls_old_idx`;",
	"CREATE TABLE IF NOT EXISTS `dls_ov_{}` LIKE `x_dls_old_val`;",
	"CREATE TABLE IF NOT EXISTS `dls_hs_{}` LIKE `x_dls_hour_sum`;",
	"CREATE TABLE IF NOT EXISTS `dls_ws_{}` LIKE `x_dls_week_sum`;",
	"INSERT IGNORE INTO `dls_l_{}` SELECT `time`,`address`,`keyword`,`value` FROM ".
		"`dl_status_i` JOIN `dl_status_p` ON  `idx`=`status` ".
		"WHERE `station`='{}' AND `time`>'$sixtydays';",
	"INSERT IGNORE INTO `dls_oi_{}` SELECT `idx`,`time`,`address` ".
		"FROM `dl_status_i` WHERE  `station`='{}' AND `time`<='$sixtydays';",
	"INSERT IGNORE INTO `dls_ov_{}` SELECT * FROM `dl_status_p`".
		"WHERE `status` IN (SELECT `idx` FROM `dls_oi_{}`);",
);
if(isset($_GET['make'])) {
	preg_match('#\w+#',$_GET['make'],$m);
	$bn = $m[0];
?>
<div style="font-family:monospace;font-size:.8em;color:#999">
<?php
	foreach($queries as $q) {
		$qq = str_replace('{}',$bn,$q);
?>
	<p style="padding-left:2em;text-indent:-2em"><?=$qq?><p>
<?php
		db_query($qq);
	}
?>
</div>
<a href="/rethinking.php">Regresar</a>
<?php
} else {
	$s = db_select('dl_station');
?>
<table>
	<tr><th>Estación</th><th>Dirección</th><th>Grupo</th><th>Visibilidad</th></tr>
<?php
	foreach($s as $ba) {
?>
	<tr>
		<td style="padding:0 2px"><a href="/base/<?=$ba['station']?>/index.html"><?=$ba['name']?></a></td>
		<td style="padding:0 2px"><?=$ba['ip']?></td>
		<td style="padding:0 2px"><?=$ba['group']?></td>
		<td style="padding:0 2px"><?=$ba['public']?'público':'privado'?></td>
	</tr>
	<tr>
		<td style="text-align:right;font-size:.9em"><a href="?make=<?=$ba['station']?>">Ejecutar</a></td>
		<td colspan=3 style="font-family:monospace;font-size:.8em;color:#999">
<?php
		foreach($queries as $q) {
			$qq = str_replace('{}',$ba['station'],$q);
?>
			<p style="padding-left:2em;text-indent:-2em"><?=$qq?><p>
<?php
		}
?>
		</td>
	</tr>
<?php
	}
?>
</table>
<?php
}
return ob_get_clean();
?>
