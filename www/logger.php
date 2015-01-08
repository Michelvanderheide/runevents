<?php
define("TASK_LOGLEVEL_DEBUG",0);
define("TASK_LOGLEVEL_INFO",1);
define("TASK_LOGLEVEL_WARN",2);
define("TASK_LOGLEVEL_ERROR",3);

/**
* Log functions for levels: debug, info, warning and error
* use $this -> loglevel = TASK_LOGLEVEL_<level> in the contructor to define the log level
*/
$loglevel = TASK_LOGLEVEL_DEBUG;

function setLoglevel($level) {
	$loglevel = $level;
}
function log_debug($str) {
	global $loglevel;
	if ($loglevel <= TASK_LOGLEVEL_DEBUG) {
		do_log($str, "DEBUG");
	}
}

function log_info($str) {
	global $loglevel;
	if ($loglevel <= TASK_LOGLEVEL_INFO) {
		do_log($str, "INFO");
	}
}

function log_warning($str) {
	global $loglevel;
	if ($loglevel <= TASK_LOGLEVEL_WARNING) {
		do_log($str, "WARNING");
	}
}

function log_error($str) {
	global $loglevel;
	if ($loglevel <= TASK_LOGLEVEL_ERROR) {
		do_log($str, "ERROR");
	}
}

function do_log ($str, $type="INFO") {

	if (!is_scalar ($str)) {
		$str = print_r ($str, true);
	}
	$logDir = __DIR__ .'/logs';
	if (!file_exists($logDir)) {
		@mkdir($logDir, 0777, true);
	}
	$filename = $logDir . '/runcal_' . date("Ymd").'.log';
	$fp = fopen($filename, 'a');
	$date = date("d-m-Y H:i:s -");
	fwrite($fp, "$date $type - $str\n");
	fclose($fp);
}		
