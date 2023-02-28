<?php

use Attendance\Install;
use EGroupware\Api\Asyncservice;

include_once dirname(__FILE__).'/../app/api/app.php';
// add import job
$async = new Asyncservice();
$async->set_timer(['hour' => '*/2'], 'attendance', 'attendance.attendance_sync.synchron', null);

$install = new Install();

$install->set()->translate()->status_labels();
