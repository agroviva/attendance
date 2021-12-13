<?php

use AgroEgw\App;
use AgroEgw\DB;
use Attendance\Ajax;
use Attendance\Core\Update;
use EGroupware\Api\Asyncservice;

if (!function_exists("errorHandler")) {
	function errorHandler($error_level, $error_message, $error_file, $error_line, $error_context)
	{
		$error = "lvl: " . $error_level . " | msg:" . $error_message . " | file:" . $error_file . " | ln:" . $error_line;
		switch ($error_level) {
		    case E_ERROR:
		    case E_CORE_ERROR:
		    case E_COMPILE_ERROR:
		    case E_PARSE:
		        mylog($error, "fatal");
		        break;
		    case E_USER_ERROR:
		    case E_RECOVERABLE_ERROR:
		        mylog($error, "error");
		        break;
		    case E_WARNING:
		    case E_CORE_WARNING:
		    case E_COMPILE_WARNING:
		    case E_USER_WARNING:
		        mylog($error, "warn");
		        break;
		    case E_NOTICE:
		    case E_USER_NOTICE:
		        mylog($error, "info");
		        break;
		    case E_STRICT:
		        mylog($error, "debug");
		        break;
		    default:
		        mylog($error, "warn");
		}
	}
}

if (!function_exists("shutdownHandler")) {
	function shutdownHandler() //will be called when php script ends.
	{
		$lasterror = error_get_last();
		switch ($lasterror['type'])
		{
		    case E_ERROR:
		    case E_CORE_ERROR:
		    case E_COMPILE_ERROR:
		    case E_USER_ERROR:
		    case E_RECOVERABLE_ERROR:
		    case E_CORE_WARNING:
		    case E_COMPILE_WARNING:
		    case E_PARSE:
		        $error = "[SHUTDOWN] lvl:" . $lasterror['type'] . " | msg:" . $lasterror['message'] . " | file:" . $lasterror['file'] . " | ln:" . $lasterror['line'];
		        mylog($error, "fatal");
		}
	}
}

if (!function_exists("mylog")) {
	function mylog($error, $errlvl)
	{
		// echo $error;
	}	
}

set_error_handler("errorHandler");
register_shutdown_function("shutdownHandler");

require_once __DIR__.'/../../../agroviva/vendor/autoload.php';

if (!defined('APPDIR')) {
    define('APPDIR', dirname(__DIR__));
}

if (!defined('TEMPLATE')) {
    define('TEMPLATE', __DIR__.'/../views');
}

App::setName('attendance');
App::Start();
require_once '/usr/share/egroupware/header.inc.php';
require_once EGW_INCLUDE_ROOT.'/attendance/setup/setup.inc.php';
require_once __DIR__.'/functions.php';
require_once __DIR__.'/../classes/autoload.php';

$async = new asyncservice();

$async->delete('attendance_api');
if (($async->read('attendance')['attendance']['method'] != 'attendance.attendance_sync.synchron')) {
    $async->delete('attendance');
    $async->set_timer(['hour' => '*/2'], 'attendance', 'attendance.attendance_sync.synchron', null);
}

$db = (new DB("SHOW TABLES LIKE 'egw_attendance'"))->Fetch();
if (empty($db)) {
    $async->delete('attendance');
    die();
}

Update::Start();

require_once APPDIR.'/inc/class.attendance_ui.inc.php';
require_once APPDIR.'/inc/class.attendance_so.inc.php';
require_once APPDIR.'/inc/class.attendance_bo.inc.php';

Ajax::Response();
App::Clean();
