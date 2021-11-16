<?php

use AgroEgw\DB;
use Attendance\Core;

if (isset($content)) {
    if (isset($content['cancel'])) {		// Cancel this action
        $this->manage($content = null);

        return;
    } elseif (isset($content['save'])) { // Save the data
        // Dump($content);
        // die();
        $not_defined_time = $content['att_defined'];
        $content = $this->bo->extraPOST() + $content;
        unset($content['save']);

        if ($content['start'] != '') {
            $content['start'] = date('Y-m-d', $content['start']);

            if ($content['end'] != '') {
                $content['end'] = date('Y-m-d', $content['end']);
            } else {
                $content['end'] = null;
            }

            $check = $this->so->check_contract($content['user'], $content['start']);
            $content['total_week_hours'] = $this->week_hours($content['monday'], $content['mon_day_type']) + $this->week_hours($content['tuesday'], $content['tue_day_type']) + $this->week_hours($content['wednesday'], $content['wed_day_type']) + $this->week_hours($content['thursday'], $content['thu_day_type']) + $this->week_hours($content['friday'], $content['fri_day_type']) + $this->week_hours($content['saturday'], $content['sat_day_type']) + $this->week_hours($content['sunday'], $content['sun_day_type']);

            $content['weekdays_rhymes'] = $this->bo->weekdays_rhymes($content['mon_day_type'], $content['tue_day_type'], $content['wed_day_type'], $content['thu_day_type'], $content['fri_day_type'], $content['sat_day_type'], $content['sun_day_type']);
            if ($content['sec_type']) {
                if ($content['sec_type'] == 'Password') {
                    $content['access_denied'] = 'no';
                    $content['access_granted'] = 'no';
                } elseif ($content['sec_type'] == 'Access denied') {
                    $content['access_denied'] = 'yes';
                    $content['password'] = null;
                    $content['access_granted'] = 'no';
                } elseif ($content['sec_type'] == 'Access granted') {
                    $content['access_denied'] = 'no';
                    $content['password'] = null;
                    $content['access_granted'] = 'yes';
                }
                if (($content['start'] < $content['end']) || ($content['end'] == '')) {
                    if ($check > 0) {
                        echo "<div style='text-align: center;'>
							  		<span style='color: red;margin: 0;background-color: oldlace;text-align: right;font-size: medium;font-weight: bolder;'>An active Contract is found for this user!</span>
								</div>";
                        $this->manage();

                        return;
                    } else {
                        $check1 = $this->so->check_contract1($content['user'], $content['start']);
                        if ($check1 > 0) {
                            echo "<div style='text-align: center;'>
								  		<span style='color: red;margin: 0;background-color: oldlace;text-align: right;font-size: medium;font-weight: bolder;'>Startdate is smaller than enddate of a old contract!</span>
									</div>";
                            $this->manage();

                            return;
                        } else {
                            // It takes the given variables and inserts them in Database
                            $this->so->add($content['user'], $content['vacation'], $content['total_week_hours'], $content['monday'], $content['tuesday'], $content['wednesday'], $content['thursday'], $content['friday'], $content['saturday'], $content['sunday'], $content['start'], $content['end'], $content['att_nfc'], $content['password'], $content['access_denied'], $content['access_granted'], $content['weekdays_rhymes'], $content['time_interval'], $content['extra_vacation']);
                            $con_id = (new DB('SELECT * FROM egw_attendance WHERE user = '.$content['user'].' ORDER BY id DESC'))->Fetch()['id'];
                            Core::setMeta('not_defined_time', $con_id, $not_defined_time);
                            $this->manage();

                            return;
                        }
                    }
                } else {
                    echo "<div style='text-align: center;'>
							  		<span style='color: red;margin: 0;background-color: oldlace;text-align: right;font-size: medium;font-weight: bolder;'>The start date musst not be greater than the end date!</span>
								</div>";
                    $this->manage();

                    return;
                }
            } else {
                echo "<div style='text-align: center;'>
							  		<span style='color: red;margin: 0;background-color: oldlace;text-align: right;font-size: medium;font-weight: bolder;'>Please give the security type!</span>
								</div>";
                $this->manage();

                return;
            }
        } else {
            echo "<div style='text-align: center;'>
							  		<span style='color: red;margin: 0;background-color: oldlace;text-align: right;font-size: medium;font-weight: bolder;'>Start date musst not be empty!</span>
								</div>";
            $this->manage();

            return;
        }
    }
}

$mon_day_type = $tue_day_type = $wed_day_type = $thu_day_type = $fri_day_type = $sat_day_type = $sun_day_type = ['weekly' => lang('weekly'),
                    'twoweekly'                                                                                           => lang('two-weekly'),
                    'threeweekly'                                                                                         => lang('three-weekly'),
                    'fourweekly'                                                                                          => lang('four-weekly'), ];

$pass_type = [''                     => lang('Choose...'),
                    'Password'       => lang('Password'),
                    'Access denied'  => lang('Access denied'),
                    'Access granted' => lang('Access granted'), ];

$login_round = $logout_round = [
                    'roundup'   => lang('Roundup'),
                    'rounddown' => lang('Rounddown'),
                ];

$sel_options = [
    'sec_type'     => $pass_type,
    'mon_day_type' => $mon_day_type,
    'tue_day_type' => $tue_day_type,
    'wed_day_type' => $wed_day_type,
    'thu_day_type' => $thu_day_type,
    'fri_day_type' => $fri_day_type,
    'sat_day_type' => $sat_day_type,
    'sun_day_type' => $sun_day_type,
    'login_round'  => $login_round,
    'logout_round' => $logout_round,
];

$content['login_interval'] = $this->bo->logInOutIntervalForm('login_interval');
$content['logout_interval'] = $this->bo->logInOutIntervalForm('logout_interval');

$content['monday'] = $this->bo->setTimeField('monday');
$content['tuesday'] = $this->bo->setTimeField('tuesday');
$content['wednesday'] = $this->bo->setTimeField('wednesday');
$content['thursday'] = $this->bo->setTimeField('thursday');
$content['friday'] = $this->bo->setTimeField('friday');
$content['saturday'] = $this->bo->setTimeField('saturday');
$content['sunday'] = $this->bo->setTimeField('sunday');

$GLOBALS['egw_info']['flags']['app_header'] = lang('Contracts - Add');
