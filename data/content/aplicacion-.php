<?php
global $il;
il_put('title','Aplicaciones');
$il->add('scripts','<script type="text/javascript" src="/js/acordeon.js"></script>'.chr(10));
return "<section id=aplicaciones>".make_menu('aplicacion')."</section>";
?>