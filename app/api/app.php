<?php

use AgroEgw\App;
use AgroEgw\DB;
use Attendance\Ajax;
use Attendance\Core\Update;
use EGroupware\Api\Asyncservice;

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
