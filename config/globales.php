<?php
if(!class_exists('KLogger')){
require_once( dirname( dirname(__FILE__) ) . '/scripts/KLogger.php');
}
require_once( dirname( dirname(__FILE__) ) . '/scripts/underscore.php');

function loadClasses($dir) {
	foreach (list_modules($dir) as $model) {
		require_once ($dir . $model . '.php');
	}
}
function getmicrotime() {
	list($usec, $sec) = explode(" ", microtime());
	return ((float)$usec + (float)$sec);
}
function list_modules($path, $sort=true) {
	if ($d = opendir($path)) {
		$out = array();
		$r = null;
		while (false !== ($fn = readdir($d))) {
			if (preg_match('#^(.+)\.php$#', $fn, $r)) {
				$out[] = $r[1];
			}
		}
		if ($sort || $this->sort) {
			sort($out);
		}
		return $out;
	}
	else {
		return false;
	}
}
// Full classes loader
if(isset($dir)) {
	loadClasses($dir);
}
function getUsageMessage() {
	$msg_output  = "Usage: ./console.php [lat] [longitude] [gpsid]\n";
	$msg_output .= "Usage for file: ./console.php [filename.csv] [gpsid]\n";
	$msg_output .= "\n";
	return $msg_output;
}
