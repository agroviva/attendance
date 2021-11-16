<?php

use AgroEgw\DB;

$entries = $_POST['entry'];

if (!empty($entries)) {
    $i = 0;
    foreach ($entries as $contractID) {
        $contractID = intval($contractID);
        if ($contractID) {
            $i++;
            (new DB("UPDATE egw_attendance SET sort_order = $i WHERE id = $contractID"));
        }
    }
    echo json_encode(['response' => 'success']);
} else {
    echo json_encode(['response' => 'failed', 'msg' => 'No entries were sent!']);
}
