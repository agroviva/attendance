<?php

require_once __DIR__.'/api/app.php';
require_once dirname(__FILE__).'/inc/class.attendance_ui.inc.php';
$attendance_ui = new attendance_ui();
$attendance_ui->index();
