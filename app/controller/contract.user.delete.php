<?php

use AgroEgw\Api\User;
use Attendance\Contract;
use Carbon\Carbon;

$args = $_POST;

$contract_id = (int) explode('_', $args['contract_id'])[1];
$contract = Contract::Get($contract_id);
if (!empty($contract)) {
	try {
    	Contract::Delete($contract_id);

		echo json_encode(array(
			"response" 	=> "success",
			"msg"		=> "Der Vertrag wurde erfolgreich gelöscht!"
		));
	} catch (Exception $e) {	
		echo json_encode(array(
			"response" 	=> "error",
			"msg"		=> $e->getMessage()
		));
	}
} else {
	echo json_encode(array(
		"response" 	=> "error",
		"msg"		=> "Der Vertrag existiert nicht"
	));
}