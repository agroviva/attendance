<?php

use AgroEgw\Api\User;
use Attendance\Contracts;

$post = $_POST;

if (empty($post)) {
    die();
}

$users = User::Search($post['query']);

// $contracts = (new Contracts(true))->Load();

// foreach ($users as $key => $user) {
// 	foreach ($contracts as $contract) {
// 		if ($user['id'] == $contract['user']) {
// 			unset($users[$key]);
// 		}
// 	}
// }

$html = '
<div class="username" data-uid="{id}">
	<div class="worker-photo" style="box-sizing: border-box;background-image: url(/egroupware/attendance/image.php?id={id}&amp;etag=8);"></div><span class="name">{label}</span>
</div>
';

echo json_encode([
    'users' => $users,
    'html'  => $html,
]);
