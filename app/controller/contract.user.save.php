<?php

use Attendance\Contract;
use Attendance\User;
use Carbon\Carbon;

$post = $_POST;

if (empty($post)) {
    die();
}

$response = [
    'response' => 'success',
    'msg'      => '',
];

$contract = [];
$responde = function (array $response) use (&$contract) {
    echo json_encode($response + ['data' => $contract]);
    exit;
};

$error = function (string $msg) use ($response, $responde) {
    $response['response'] = 'error';
    $response['msg'] = $msg;
    $responde($response);
};

$success = function (string $msg) use ($response, $responde) {
    $response['response'] = 'success';
    $response['msg'] = $msg;
    $responde($response);
};

if (!$GLOBALS['egw_info']['user']['apps']['admin']) {
    $error('Sie haben keine rechte!');
}

$userID = (int) $post['userID'] ?: $error('Wählen Sie einen Benutzer aus!');
$contractID = (int) $post['contractID'];

if (!$contractID) {
    $user = new User($userID);
    if ($user->hasValidContract()) {
        $error('Der ausgewählte Benutzer existiert bereits in den Arbeitsverträgen');
    }
}

$annualLeave = (int) $post['annualLeave'] ?: $error('Jahresurlaub wurde nicht eingegeben');
$extraVacation = (int) $post['extraVacation'] ?: 0;
$startOfContract = $post['startOfContract'] ? Carbon::parse($post['startOfContract']) : $error('Das Startdatum ist nicht eingegeben!');
$endOfContract = $post['endOfContract'] ? Carbon::parse($post['endOfContract']) : null;
if ($endOfContract->timestamp) {
    if ($endOfContract->timestamp < $startOfContract->timestamp) {
    }
}
$startOfContract = $startOfContract->format('Y-m-d');
$endOfContract = $endOfContract ? $endOfContract->format('Y-m-d') : null;

$securityLevel = $post['securityLevel'];
$password = $post['password'];

$stepwiseIN = (int) $post['stepwiseIN'] ?: '';
$roundingPointIN = (int) $post['roundingPointIN'] ?: '';

$stepwiseOUT = (int) $post['stepwiseOUT'] ?: '';
$roundingPointOUT = (int) $post['roundingPointOUT'] ?: '';

$norules = $post['norules'];
$rules = $post['rules'];

$contract = [
    'user' 				       => $userID,
    'creator' 			     => $GLOBALS['egw_info']['user']['account_id'],
    'modified' 			    => time(),
    'vacation' 			    => $annualLeave,
    'extra_vacation'		=> $extraVacation,
    'start'				       => $startOfContract,
    'end'				         => $endOfContract,
    'access_granted' 	=> 'no',
    'access_denied' 	 => 'no',
];

switch ($securityLevel) {
    case 'access_granted':
        $contract['access_granted'] = 'yes';
        break;
    case 'access_denied':
        $contract['access_denied'] = 'yes';
        break;
    case 'password':
        $contract['password'] = $password;
        break;
    default:
        $error('Bitte geben Sie die Sicherheitsstufe ein!');
        break;
}

$total_week_hours = 0;
$weekdays_rhymes = [];
$meta_data = [];

foreach (Weekdays() as $day) {
    $dayData = $rules[$day];
    $rythm = intval($dayData['rythm']) ?: 1;
    $rythm = ($rythm > 0 && $rythm < 5) ? $rythm : 1;
    $valid_on = !empty($dayData['valid_on']) && $rythm !== 1
                ? Carbon::parse($dayData['valid_on'])->format('Y-m-d')
                : null;

    // set to 0 if norules activated
    $contract[$day] = $dayData['should'] && !filter_var($norules, FILTER_VALIDATE_BOOLEAN)
                        ? decimalHours($dayData['should']) * 60 * 60 : 0;
    $total_week_hours += $contract[$day] / $rythm;

    $weekdays_rhymes[$day] = $rythm;

    $meta_data['weekdays'][$day]['rythm'] = $rythm;
    if ($valid_on) {
        $meta_data['weekdays'][$day]['valid_on'] = $valid_on;
    }
}

$contract['total_week_hours'] = $total_week_hours;
$contract['weekdays_rhymes'] = json_encode($weekdays_rhymes);

$time_interval_data = [
    'inTimeValue'     => $stepwiseIN,
    'inRoundValue'    => $roundingPointIN,
    'inRoundingType'  => null,
    'outTimeValue'    => $stepwiseOUT,
    'outRoundValue'   => $roundingPointOUT,
    'outRoundingType' => null,
];
$contract['time_interval_data'] = json_encode($time_interval_data);

$meta_data['interval']['in'] = [
    'stepwise'       => $stepwiseIN,
    'rounding_point' => $roundingPointIN,
    'rounding_type'  => null,
];
$meta_data['interval']['out'] = [
    'stepwise'       => $stepwiseOUT,
    'rounding_point' => $roundingPointOUT,
    'rounding_type'  => null,
];
$contract['meta_data'] = json_encode($meta_data);

// Dump($norules);

if ($contractID) {
    Contract::Update($contractID, $contract);
    $success('Der Arbeitsvertrag wurde aktualisiert');
} else {
    Contract::New($contract);
    $success('Der Arbeitsvertrag wurde hinzugefügt!');
}
