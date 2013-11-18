<?php
ob_start();
il_put('title','Portafolio de servicios');

global $il;
$il->add('scripts',<<<AAA

	<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.2.6/jquery.min.js"></script>
	<script type="text/javascript" src="js/fancyzoom.min.js"></script>
	<script type="text/javascript" charset="utf-8">
		$(document).ready(function() {
			$('div.photo a').fancyZoom({directory: 'images/zoom', scaleImg: true, closeOnClick: true});
		});
	</script>
	
AAA
);
?>
<!-- bits -->
		<section id="bits">
			<div class="bit">
				<h4>Incidencias</h4>
				<div class="photo">
					<a href="#approach"><img src="/images/inci_mini.png" alt="Thumb" /></a>
				</div>
				<p>Resumen de todas las incidencias creadas en una sola pantalla de fácil navegación</p>
				<p class="more"><a href="#">Leer mas</a></p>
				<div id="approach">
					<img src="/images/inci_big.jpg" alt="Approach" />
				</div>
			</div>
			<div class="bit">
				<h4>Resumen</h4>
				<div class="photo">
					<a href="#methods"><img src="/images/resu_mini.png" alt="Thumb" /></a>
				</div>
				<p>Compendio de información unificada de todos los proyectos activos. Contiene tablas y gráficas explicativas</p>
				<p class="more"><a href="#">Leer mas</a></p>
				<div id="methods">
					<img src="/images/resu_big.jpg" alt="Methods" />
				</div>
			</div>
			<div class="bit last">
				<h4>Administracion</h4>
				<div class="photo">
					<a href="#results"><img src="/images/admin_mini.png" alt="Thumb" /></a>
				</div>
				<p>Sitio completamente administrable desde el módulo de gestión web</p>
				<p class="more"><a href="#">Leer mas</a></p>
				<div id="results">
					<img src="/images/admin_big.jpg" alt="Results" />
				</div>
			</div>
			<div class="clear"></div>
		</section>
<!-- /bits -->
<?php
return ob_get_clean();
?>
