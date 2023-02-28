<?php

use Attendance\Core;

if (is_array($content)) {
	if (isset($content['save'])) { // Save Button on attendance.edit
		if (isset($content['id'])) { // Checks if id is available
			$not_defined_time = $content['att_defined'];
			$content = $this->bo->extraPOST() + $content;

			if ($content['start'] != '') {
				$content['start'] = date('Y-m-d', $content['start']);

				if ($content['end'] != '') {
					$content['end'] = date('Y-m-d', $content['end']);
				} else {
					$content['end'] = null;
				}

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
						$time_interval = json_encode([
							'inTimeValue'		   => $content['login_interval']['time'],
							'inRoundValue'		  => $content['login_interval']['roundpoint'],
							'inRoundingType'	 => $content['login_round'],
							'outTimeValue'		  => $content['logout_interval']['time'],
							'outRoundValue'		 => $content['logout_interval']['roundpoint'],
							'outRoundingType'	=> $content['logout_round'],
						]);

						// It takes the given variables and updates them in Database
						$this->so->edit_result($content['id'], $content['user'], $content['vacation'], $content['total_week_hours'], $content['monday'], $content['tuesday'], $content['wednesday'], $content['thursday'], $content['friday'], $content['saturday'], $content['sunday'], $content['start'], $content['end'], $content['att_nfc'], $content['password'], $content['access_denied'], $content['access_granted'], $content['weekdays_rhymes'], $content['time_interval'], $content['extra_vacation']);

						$meta_id = Core::getMeta(false, 'not_defined_time', $content['id'])['id'];
						if (!$meta_id) {
							Core::setMeta('not_defined_time', $content['id'], $not_defined_time);
						} else {
							Core::updateMeta($meta_id, $not_defined_time);
						}

						$this->manage();

						return;
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
		} else {
			echo "<div style='text-align: center;'>
							  		<span style='color: red;margin: 0;background-color: oldlace;text-align: right;font-size: medium;font-weight: bolder;'>Cannot find the Contract!</span>
								</div>";
			$this->manage();

			return;
		}
	} elseif (isset($content['cancel'])) {
		$this->manage($content = null);

		return;
	}
}

$mon_day_type = $tue_day_type = $wed_day_type = $thu_day_type = $fri_day_type = $sat_day_type = $sun_day_type = ['weekly' => lang('weekly'),
	'twoweekly'                                                                                                              => lang('two-weekly'),
	'threeweekly'                                                                                                            => lang('three-weekly'),
	'fourweekly'                                                                                                             => lang('four-weekly'), ];

$pass_type = [''                     => lang('Choose...'),
	'Password'                          => lang('Password'),
	'Access denied'                     => lang('Access denied'),
	'Access granted'                    => lang('Access granted'), ];

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

$content = $this->so->get_idresult($id);

$style_css = "<style>\n";
$not_defined_time = Core::getMeta(false, 'not_defined_time', $content['id'])['meta_data'];
$content['att_defined'] = $not_defined_time;
if ($not_defined_time == 1) {
	$style_css .= '.day{display: none;}';
}
$style_css .= '</style>';
echo $style_css;

$time_interval_data = json_decode($content['time_interval_data'], true);

$content['login_round'] = $time_interval_data['inRoundingType'];
$content['logout_round'] = $time_interval_data['outRoundingType'];

$content['login_interval'] = $this->bo->logInOutIntervalForm('login_interval', $time_interval_data);
$content['logout_interval'] = $this->bo->logInOutIntervalForm('logout_interval', $time_interval_data);

$content['monday'] = $this->bo->setTimeField('monday', $this->bo->UnixToTime($content['monday'])['H'], $this->bo->UnixToTime($content['monday'])['i']);
$content['tuesday'] = $this->bo->setTimeField('tuesday', $this->bo->UnixToTime($content['tuesday'])['H'], $this->bo->UnixToTime($content['tuesday'])['i']);
$content['wednesday'] = $this->bo->setTimeField('wednesday', $this->bo->UnixToTime($content['wednesday'])['H'], $this->bo->UnixToTime($content['wednesday'])['i']);
$content['thursday'] = $this->bo->setTimeField('thursday', $this->bo->UnixToTime($content['thursday'])['H'], $this->bo->UnixToTime($content['thursday'])['i']);
$content['friday'] = $this->bo->setTimeField('friday', $this->bo->UnixToTime($content['friday'])['H'], $this->bo->UnixToTime($content['friday'])['i']);
$content['saturday'] = $this->bo->setTimeField('saturday', $this->bo->UnixToTime($content['saturday'])['H'], $this->bo->UnixToTime($content['saturday'])['i']);
$content['sunday'] = $this->bo->setTimeField('sunday', $this->bo->UnixToTime($content['sunday'])['H'], $this->bo->UnixToTime($content['sunday'])['i']);

$weekdays = json_decode($content['weekdays_rhymes'], true);

foreach ($weekdays as $key => $value) {
	$short_name = substr($key, 0, 3);

	if ($value == 1) {
		$content[$short_name.'_day_type'] = 'weekly';
	} elseif ($value == 2) {
		$content[$short_name.'_day_type'] = 'twoweekly';
	} elseif ($value == 3) {
		$content[$short_name.'_day_type'] = 'threeweekly';
	} elseif ($value == 4) {
		$content[$short_name.'_day_type'] = 'fourweekly';
	}
}

$content['id'] = $id;

$GLOBALS['egw_info']['flags']['app_header'] = lang('Contract - Edit');
