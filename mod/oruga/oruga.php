<?php

function make_landing($line) {
	set_area('title','print','');
	unset_area('site');
	ob_start();
?>
<ul class=wid>
	<li class=item><h3>Maquinaria</h3>
		<ul class=subwid>
			<li class=subitem><a href='/venta/'>Compra y venta</a></li>
			<li class=subitem><a href='/alquiler/'>Alquiler</a></li>
			<li class=subitem><a href='/repuestos/'>Repuestos</a></li>
		</ul>
	</li>
	<li class=item><h3>Monitoreo Remoto</h3>
		<ul class=subwid>
			<li class='subitem vias'><a href='/grupo/aguilar/monitoreo.html'>Aguilar Construcciones</a></li>
			<li class='subitem flores'><a href='/grupo/elite/monitoreo.html'>Elite Flowers</a></li>
			<li class='subitem flores'><a href='/grupo/timana/monitoreo.html'>Flores Timaná</a></li>
			<li class='subitem flores'><a href='/grupo/sunshine/monitoreo.html'>Sunshine Bouquet</a></li>
		</ul>
	</li>
	<li class=item><h3>Gestión de incidencias</h3>
		<ul class=subwid>
			<li class='subitem vias'><a href="/grupo/aguilar/gestion.html">Aguilar Construcciones</a></li>
			<li class='subitem vias'><a href="/grupo/concay/gestion.html">Concay</a></li>
			<li class='subitem flores'><a href='/grupo/elite/gestion.html'>Elite Flowers</a></li>
			<li class='subitem industrial'><a href="/grupo/emcocables/gestion.html">Emcocables</a></li>
			<li class='subitem'><a href='/grupo/invermeq/gestion.html'>Invermeq</a></li>
			<li class='subitem construccion'><a href="/grupo/inversiones_p_g/gestion.html">Inversiones Prada Gómez</a></li>
			<li class='subitem flores'><a href='/grupo/singha/gestion.html'>Singha</a></li>
			<li class='subitem flores'><a href="/grupo/sunshine/gestion.html">Sunshine</a></li>
			<li class='subitem flores'><a href="/grupo/timana/gestion.html">Timaná</a></li>
		</ul>
	</li>
</ul>
<?php
	$servicios = ob_get_clean();
	ob_start();
?>
<div class=newsfeed>
	<p class=item>
		<a class=avatar-link href='maquina/00001'><img class=avatar></a>
		<a class=desc-link href='maquina/00001/76232'>
		<span class=item-date>Ahora</span>
		<span class=item-desc>El bloque 32 de Marly ha reportado una alarma.</span>
		</a>
	</p>
	<p class=item>
		<a class=avatar-link href='maquina/30001'><img class=avatar></a>
		<a class=desc-link href='maquina/30001/76232'>
		<span class=item-date>Hace 5 minutos</span>
		<span class=item-desc>El bloque 9 de Sarama ha reportado una alarma de funcionamiento.</span>
		</a>
	</p>
	<p class=item>
		<a class=avatar-link href='maquina/05001'><img class=avatar></a>
		<a class=desc-link href='maquina/30001/76232'>
		<span class=item-date>Hace 12 minutos</span>
		<span class=item-desc>El cono (con03) ha reportado una alarma de funcionamiento.</span>
		</a>
	</p>
	<p class=item>
		<a class=avatar-link href='maquina/00401'><img class=avatar></a>
		<a class=desc-link href='maquina/30001/76232'>
		<span class=item-date>Hace aprox. 1 hora</span>
		<span class=item-desc>La planta de asfalto ha reportado alarma de funcionamiento.</span>
		</a>
	</p>
</div>
<?php
	$novedades = ob_get_clean();
	$user = isset($_SESSION['user'])? $_SESSION['user']: null;
	$content = 
		make_section($servicios,'Servicios',2,null,null,'remote').
		make_section(item_feed($user),'Novedades',2,null,null,'newsfeed').
		make_section(
			list_articles(0,0,"","<a href=\"/notas/page/1.html\">Artículos anteriores</a>",true,3),
			'Actualidad',2,'/notas/',null,'news');
	il_put('title',"Oruga Amarilla - Monitorea, evalúa, analiza");
	il_put('type','portal');
	return $content;
}

?>