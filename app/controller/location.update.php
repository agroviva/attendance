<?php

use Attendance\Location;

$userID = $_POST['userID'];
$locationID = $_POST['locationID'];

if (Location::UserInLocation($userID, $locationID)) {
    Location::updateLocation($userID, $locationID);
    echo json_encode([
        'response' => 'success',
        'action'   => 'removed',
    ]);
} else {
    Location::updateLocation($userID, $locationID);
    echo json_encode([
        'response' => 'success',
        'action'   => 'added',
    ]);
}