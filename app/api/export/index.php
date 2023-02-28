<?php

use Attendance\Export;

ini_set('memory_limit', '-1');
include __DIR__.'/../app.php';

$so = new attendance_so();
$bo = new attendance_bo();

if (isset($_GET)) {
	$export = new Export($_GET);
} elseif (isset($_POST)) {
	$export = new Export($_POST);
}

$export->exec();
