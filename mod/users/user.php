<?php

function make_user($line) {
	il_default('title', mb_convert_case(il_get2('line','spaced',$line),MB_CASE_TITLE,'UTF-8'));
	return "<pre>".print_r($_SESSION,true)."</pre>\n";
	return "<pre>".print_r($GLOBALS['il'],true)."</pre>\n";
}

?>
