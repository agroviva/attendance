<?php

$settings = include dirname(__FILE__).'/../app/default.php';
$version = $settings['version'];
include dirname(__FILE__).'/../app/v'.$version.'/inc/class.attendance_hooks.inc.php';
