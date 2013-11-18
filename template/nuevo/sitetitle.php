<?php
ob_start();

$lline = il_get2('line','cannon',il_get('line'));
$sname = il_get('sitename');
#print_r($il);
if($lline=='inicio.html') {
?>
	<hgroup id=hometitle>
		<h1><?php echo $sname?></h1>
		<h2>Monitorea<span>, </span>evalúa<span>, </span>analiza</h2>
	</hgroup>
	<p id=tagline>Con la orugaamarilla convierte tus máquinas en amigas que te estén reportando su estado en tiempo real.</p>
<?php
} else {
	$gtag = $lline? 'section': 'hgroup';
	$htag = $lline? 'p': 'h1';
?>
	<<?php echo $gtag?> id=sitetitle>
		<<?php echo $htag?>><a href="/"><?php echo $sname?></a></<?php echo $htag?>>
	</<?php echo $gtag?>>
<?php
}

return ob_get_clean();
?>
