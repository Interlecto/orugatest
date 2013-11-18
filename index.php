<?php
# Interlecto Content Management System
# ICMS Version 0.5
$il_time_start = microtime(true);
ob_start();

# Get general cms options
require_once "lib/cms.php";

# Get site configuration options
require_once "site_config.php";

# Get session and request status
require_once "lib/params.php";

# Get the page
ob_start();
require_once "lib/go.php";
$page = ob_get_clean();
if(il_empty('user') && il_get('cachable'))
	il_cache_set(il_get('line'),$page);

# Send everything
il_close_db();
$debug = ob_get_clean();
echo $page;
if($debug)
	echo "\n<!-- ## DEBUG INFORMATION ##\n$debug\n-->\n";
#if($queries = il_get('queries')) echo "\n<!-- QUERIES:\n".print_r($queries,true)." -->\n";
$time_start = il_get('time_start');
$time_end = microtime(true);
echo "\n<!-- ## Ejecucion en ".($time_end-$time_start)." segundos ## -->\n";
?>
