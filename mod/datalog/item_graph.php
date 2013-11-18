<?php
/*
 * tres problemas a resolver.
 * 1) determinar qué pidió el usuario,
 * 2) permitir el que usuario pida datos adicionales
 * 3) presentar los datos.
 ************************************
 * 1) EL PEDIDO
 *  - el pedido consta de dos partes: el período de tiempo y el selector de instrumentos
 *  - el período de tiempo se define con tres variables: inicio del período, fin o tamaño del período, número de muestras.
 *  - el selector de instrumentos se da por una combinación del URI y variables GET o POST.
 *  estableciendo estos períodos de una forma unificada se puede determinar si dos pedidos son el mismo.
 *  - adicionalmente puede incluirse el tamaño a visualizar como parte del pedido.
 *
 * 2) EL SELECTOR
 *  - el selector corresponde a una serie de formularios que permiten cambiar el período de visualización
 *    y los instrumentos a visualizar.
 *  - requiere de javascript (jquery?) para conformar los datos antes de ser enviados.
 *
 * 3) LA PRESENTACION
 *  - si el pedido coincide con un pedido anterior, no deben recalcularse los datos.
 *  - si es la primera vez que se hace el pedido, la presentación debe hacer dos labores:
 *  - a) calcular los datos a presentar
 *  - b) crear el gráfico, guardarlo y presentarlo.
 */
ob_start();
	require_once 'mod/datalog/item_graph_lib.php';
	### GET params
	$tarr = item_graph_temporal_params($last);
	$iarr = item_graph_instrument_params($user,$base,$item);
	$uasz = item_graph_useragent_params();

	### CHECK if request is old or new:
	$params = json_encode(array($tarr[0],$iarr[0]));
	$phash = str_replace(array('+','/','=='),array('_','-','~'),base64_encode(md5($params,true)));
	if(!file_exists($gfn = "media/datalog/$phash.$uasz.png") or 1) {
		if(!file_exists($dfn = "data/datalog/$phash.json") or 1) {
			$graphdata = item_graph_get_data($tarr[0],$iarr[0]);
			//il_add('queries',print_r($graphdata,true));
			json_write($dfn,$graphdata);
		} else {
			$graphdata = json_read($dfn);
		}
		$graph = item_graph_generate($graphdata,$uasz);
		imagepng($graph,$gfn);
		imagedestroy($graph);
	}

	### GENERATE page content
?>
	<header id=selector>
<?php echo require 'mod/datalog/item_graph_selector.php'; ?> 
	</header>
	<div id=the_graph>
<?php echo item_graph_instrument_selector($iarr); ?> 
		<a href="/<?php echo $gfn?>" title=""><img src="/<?php echo $gfn?>"></a>
	</div>
	<footer id=additional>
		<p>Descargar datos: <a class=button href="<?php item_graph_data_sum_url($tarr,$iarr)?>">Equivalentes a este gráfico</a> <a class=button href="<?php item_graph_data_all_url($tarr,$iarr)?>">Datos completos de este período de tiempo</a>.</p>
	</footer>
<?php
return ob_get_clean();
?>
