<?php

if (module_exists('users')) {
	$cannonical = '/'.il_get2('line','cannon',il_get('line'));
	if($theuser=il_get('user')) {
		$username = il_get2('user','name',$theuser);
		$userlong = il_get2('user','long',$username);
		$thisone = urlencode($cannonical);
		$usernav = <<<LOGIN
Usuario: $username
<ul class=menu-1>
<li><a href="/usuario/">ver perfil</a></li>
<li><a href="/usuario/editar/$theuser.html">configurar</a></li>
<li><a href="/login/logout.html?next=$thisone">logout</a></li>
</ul>
LOGIN;
	} else {
		$next = $cannonical=="/inicio.html"? "/": urlencode($cannonical);
	$usernav=<<<LOGIN
Entrar
<ul class=menu-1><li><form id=login method=post action="/login/">
<input type=hidden name=next value="$next"><input type=hidden name=action value=login><label for=name>Usuario: </label><input name=name><br>
<label for=passwd>Contraseña: </label><input type=password name=passwd> <input type=submit value=entrar>
</form></li></ul>
LOGIN;
}

	return make_menu('topmenu',false)."<li id=login>$usernav</li></ul>";
}
echo "\nUSER DOSN'T EXISTS!!!\n";
return make_menu('topmenu');
?>
