<?php
ob_start();

function wrd($d) {
	$s = date('D Y-m-d H:i:s',$d);
	echo "<p><span style='width:10em;padding-right:1ex;text-align:right'>$d</span>$s</p>\n";
}
$d = 1361750471;
wrd($d);
$d-= $d%86400;
wrd($d);

$x = 41329.0525100347;
$d = (int)(($x-25568)*86400);
wrd($d);

$d = (int)(($x-25567)*86400);
wrd($d);

$d = (int)(($x-25569.041666666666666666666666667)*86400);
wrd($d);

$d = (int)(($x-25569)*86400-3600);
wrd($d);

$d = (int)(($x-25568)*86400);
wrd($d);

$d = (int)(($x-25568)*86400-57600);
wrd($d);

return ob_get_clean();
?>
