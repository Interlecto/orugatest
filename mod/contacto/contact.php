<?php
require_once "lib/braces.php";

function make_contact($line) {
	global $il;
	il_put('emailto',null);
	il_put2('from','nombre','');
	il_put2('from','email','');
	il_put2('from','phone','');
	$last = il_line_get('last');
	set_area('right','print',file_get_contents('data/contact/data.info'));
	switch($last) {
	case '':
	case 'index':
	case 'users':
	case 'email':
		il_put('title',"Contáctenos");
		$content = unbrace(file_get_contents("data/contact/contact.html"));
		break;
	case 'gracias':
	case 'enviar':
	case 'afiliacion':
		il_put('title',"Contáctenos: $last");
		if(file_exists($fn = "data/contact/$last.html")) {
			$content = unbrace(file_get_contents($fn));
			break;
		}
		if(file_exists($fn = "data/contact/$last.php")) {
			$content = require $fn;
			break;
		}
		$content = "<article><p>No hay contacto $last.</p></article>";
		break;
	default:
		$title = "Mensaje no especificado";
		$emailto = null;
		if(strpos($line,'-at-')) {
			$emailto = str_replace('-at-','@',$line);
			$title = "Menasaje a $emailto";
		}
		else foreach(($users = file("data/contact/users.db")) as $row) {
			$ll = explode("\t",$row);
			if($line==trim($ll[0])) {
				$title = "Mensaje a ".trim($ll[1]);
				$emailto = $line;
				break;
			}
		}
		il_put('emailto',$emailto);
		il_put('title',$title);
		$content = unbrace(file_get_contents($emailto? "data/contact/email.html": "data/contact/contact.html"));
	}
	set_area('left');
	return $content;
}

?>