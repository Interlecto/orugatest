<?php
ob_start();
$navclass = (module_exists('users') && $theuser=il_get('user'))? "usernav": "orientation";
?>
	<nav id=<?php echo $navclass?>>
<?php
if (module_exists('users')) {
	$cannonical = '/'.il_get2('line','cannon',il_get('line'));
	if($theuser=il_get('user')) {
		$username = il_get2('user','name',$theuser);
		$userlong = il_get2('user','long',$username);
		$thisone = $cannonical? '/inicio.html': urlencode($cannonical);
?>
		<p>
			<span class=welcome>Bienvenido <?php echo $userlong?></span>
			<a id=macro href="/login/macro.cgi">Opciones</a>
			<a id=logout href="/login/logout.cgi?next=<?php echo $thisone?>">Salir</a>
		</p>
<?php
	} else {
		$next = $cannonical=="/inicio.html"? "/": urlencode($cannonical);
?>
		<a id=login href="/login/?next=<?php echo $next?>">Entrar</a>
		<a id=go-twitter href="http://twitter.com/orugaamarilla">Twitter</a>
		<a id=go-facebook href="http://www.facebook.com/pages/Oruga-Amarilla/304747586237631">Facebook</a>
<?php
	}
} else {
?>
		<a id=go-twitter href="http://twitter.com/orugaamarilla">Twitter</a>
		<a id=go-facebook href="http://www.facebook.com/pages/Oruga-Amarilla/304747586237631">Facebook</a>
<?php
}
?>
		<a id=go-moreinfo href="/nosotros/">Más información</a>
		<a id=go-contact href="/contacto/">Contacto</a>
	</nav>
<?php
return ob_get_clean();
?>
