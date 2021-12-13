<?php
use AgroEgw\Api\User;


header('Content-Type: application/json');
echo json_encode(User::Search($_REQUEST['query'], null, "both"));