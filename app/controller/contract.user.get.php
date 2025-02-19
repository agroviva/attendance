<?php

use AgroEgw\Api\User;
use Attendance\Contract;
use Carbon\Carbon;

$args = $_POST;

$contract_id = explode('_', $args['contract_id'])[1];
$contract = Contract::Get($contract_id);
$user = $contract['user'];
if (empty($contract)) {
	exit();
}
$contract['start'] = date('d.m.Y', strtotime($contract['start']));
$contract['end'] = $contract['end'] ? date('d.m.Y', strtotime($contract['end'])) : '';

$meta_data = json_decode($contract['meta_data'], true);

if (is_array($meta_data['weekdays'])) {
	$weekplan = [];
	foreach ($meta_data['weekdays'] as $dayName => $day) {
		$dayShould = $contract[$dayName] ? $contract[$dayName] / 60 / 60 : 0;
		if ($dayShould <= 0) {
			continue;
		}
		$meta_data['weekdays'][$dayName]['should'] = clockalize($dayShould);

		$weekplan[('a'.$day['rythm'].$dayShould)][] = $dayName;
	}
	$weekplan = array_values($weekplan);
}

$ruleSection = '';
foreach ($weekplan as $key => $days) {
	$name = $days[0];
	$should = $meta_data['weekdays'][$name]['should'];
	$valid_on = $meta_data['weekdays'][$name]['valid_on'];
	if ($valid_on) {
		$valid_on = Carbon::parse($valid_on)->format('d.m.Y');
	}
	$rythm = $meta_data['weekdays'][$name]['rythm'];
	$ruleSection .= '
<div class="form-section">
	<div class="form-group col-md-12 weekdays no-selection">
		<span class="monday'.(in_array('monday', $days) ? ' selected' : '').'">MO</span>
		<span class="tuesday'.(in_array('tuesday', $days) ? ' selected' : '').'">DI</span>
		<span class="wednesday'.(in_array('wednesday', $days) ? ' selected' : '').'">MI</span>
		<span class="thursday'.(in_array('thursday', $days) ? ' selected' : '').'">DO</span>
		<span class="friday'.(in_array('friday', $days) ? ' selected' : '').'">FR</span>
		<span class="saturday'.(in_array('saturday', $days) ? ' selected' : '').'">SA</span>
		<span class="sunday'.(in_array('sunday', $days) ? ' selected' : '').'">SO</span>
	</div>
	<div class="time_inputs col-md-12">
		<div class="form-group col-md-4">
			<label>Sollstunden</label> 
			<input type="text" class="form-control timepicker shouldHours" value="'.$should.'" autocomplete="off" />
		</div>
		<div class="form-group col-md-4">
			<label>Wiederholung</label> 
			<select class="form-control repetition">
				<option value="1"'.(($rythm == 1) ? ' selected' : '').'>wöchentlich</option>
				<option value="2"'.(($rythm == 2) ? ' selected' : '').'>zweiwöchentlich</option>
				<option value="3"'.(($rythm == 3) ? ' selected' : '').'>dreiwöchentlich</option>
				<option value="4"'.(($rythm == 4) ? ' selected' : '').'>vierwöchentlich</option>
			</select>
		</div>
		<div class="form-group col-md-4">
			<label>Gültig ab*</label> 
			<input type="text" autocomplete="off" class="form-control datepicker validDate" value="'.$valid_on.'" '.(($rythm > 1) ? '' : 'disabled').'/>
		</div>
	</div>
</div>';
}

$user = User::Read($contract['user']);
$contact_id = $user['person_id'];
unset($user['account_pwd']);
$user['contract_id'] = $contract_id;

$user_html = '
<div class="username" data-uid="{account_id}" data-id="{contract_id}">
	<div class="worker-photo" style="box-sizing: border-box;background-image: url(/egroupware/api/avatar.php?contact_id={contact_id}&amp;etag=8);"></div><span class="name">{account_firstname} {account_lastname}</span>
</div>
';

echo json_encode([
	'user'        => $user,
	'user_html'   => $user_html,
	'contract'    => $contract,
	'meta_data'   => $meta_data,
	'weekplan'    => $weekplan,
	'ruleSection' => $ruleSection,
]);
