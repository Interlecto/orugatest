<?php
if(!($user=il_get('user'))) {
	redirect('inicio.html');
}

function sectionlist($link,$content,$text=null,$level='h2',$id="") {
	global $dir;
	if(!$text) {
		$text = ucfirst($link);
		$link = seo($link);
	}
	$x = (strrpos($link,'/') || strrpos($link,'.'))? "": "/";
	if(!$id) {
		$id=$x?$link:str_replace(array('-','/',' '),'-',$link);
	}
	return "<section class=portallist id=$id><$level><a href=\"$dir/$link$x\">$text</a></$level>\n$content\n</section>\n";
}

unset_area('left');
unset_area('right');

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
$content = 
	make_section($servicios,'Servicios',2,null,null,'remote').
	make_section(item_feed($user),'Novedades',2,null,null,'newsfeed').
	make_section(
		list_articles(0,0,"","<a href=\"/notas/page/1.html\">Artículos anteriores</a>",true,3),
		'Actualidad',2,'/notas/',null,'news');
il_put('title',"Oruga Amarilla: La comunidad de la maquinaria");
il_put('type','portal');
return $content;

?>
<section id=portal>
<?php
if(module_exists('catalog')) {
	echo sectionlist('venta',product_list(4,'venta',false,null),'Compra y venta de maquinaria');
	echo sectionlist('alquiler',product_list(4,'alquiler',false,null),'Alquiler de maquinaria');
	echo sectionlist('repuestos',product_list(4,'repuestos',false,null),'Compra y venta de repuestos');
}
?>
<section id=dns><a href=/dns.html>DNS</a></section>
<hr class=clear>
</section>
<?php
return ob_get_clean()
	#."\n<pre>".print_r($GLOBALS['il'],true)."</pre>\n";
?>
