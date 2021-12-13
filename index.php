<?php

$_GET['cd'] = 'no';
$GLOBALS['egw_info']['flags'] = [
    'currentapp'    => 'attendance',
    'noheader'      => true,
    'nonavbar'      => true,
];
include '../header.inc.php';
$GLOBALS['egw']->redirect_link('/egroupware/attendance/app/index.php');
