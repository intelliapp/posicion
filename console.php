#!/usr/bin/php -q
<?php
// define('DEBUG', TRUE);
define('LOG_DIR', dirname(__FILE__) . "/log");
$log = null;
$dir = dirname(__FILE__) . '/classes/';
if(defined('LOG_DIR')) {
	require_once('scripts/KLogger.php');
	$log = new KLogger ( LOG_DIR , KLogger::DEBUG );
}
if (defined('DEBUG')) {
  ini_set('display_errors', '1');
  ini_set('error_reporting', E_ALL & ~E_DEPRECATED & ~E_STRICT);
  ini_set('html_errors', false);
  ini_set('implicit_flush', true);
  ini_set('max_execution_time', 0);
}
require_once('config/globales.php');
require_once('config/config.php');
$TIME_START = getmicrotime();
if(defined('STDIN')
	&& count($argv)>1) {
	if($argv[1] == '-h'
		|| mb_strpos($argv[1],'help')!== false) {
		echo getUsageMessage();
		exit(1);

	}
	if(mb_strpos($argv[1], '.csv')) {
		$filepath = dirname(__FILE__) . "/" . $argv[1];
		if(!file_exists($filepath)) {
			echo "no se encuentra el archivo: $filepath";
			exit(1);
		}
		if(empty($argv[2])){
			echo getUsageMessage();
			exit(1);
		}
		$facade = new PosicionLogica($log, array('gpsid' => $argv[2]));
		$facade->setFileHistory(fopen($filepath, "r"));

	}else if(
		!empty($argv[1])
		&& !empty($argv[2])){
		$options = array(floatval($argv[1]), floatval($argv[2]));
		$facade = new PosicionLogica($log, array_merge($options, array('country_name' => 'Colombia')));
		if (isset($argv[3])) {
			$gpsid = $argv[3];
			$updater = new Updater($log, $gpsid, floatval($argv[1]), floatval($argv[2]));
			if($updater->execute()) {
				$facade->setLastPosition($updater->oldpos);
			}
		}
		$facade->execute();
	}
}
$log->LogInfo("Tiempo de ejecucion: " . round(getmicrotime() - $TIME_START, 2) . " segundos");
exit(1);
?>