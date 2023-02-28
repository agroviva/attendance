<?php

use Attendance\Manager;

$manager = $_POST['manager'];

if (is_numeric($manager) && $manager != 0) {
	if (Manager::Exists($manager)) {
		Manager::Delete($manager);
		echo json_encode([
			'response' => 'success',
			'action'   => 'removed',
		]);
	} else {
		Manager::New($manager);
		echo json_encode([
			'response' => 'success',
			'action'   => 'added',
		]);
	}
}
