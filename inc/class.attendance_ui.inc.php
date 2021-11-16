<?php

$settings = include dirname(__FILE__).'/../app/default.php';
$version = $settings['version'];
include_once dirname(__FILE__).'/../app/v'.$version.'/inc/class.attendance_ui.inc.php';
