<?php

namespace Attendance;

use AgroEgw\DB;
use Carbon\Carbon;
use Attendance\Location;

class Tracker
{
	public static function IN($user)
	{
		$realtime = Carbon::now()->timestamp;

		$work_cat = Categories::Get()['work'];
		$cat_id = $work_cat['id'];

		$title = lang('Ongoing Work');
		$modifier = $GLOBALS['egw']->session->account_id;
		$status = self::TimesheetStatusID('attendance');
		$start = self::getIntervalTime($user, 'login');
		$modified = Carbon::now()->timestamp;

		(new DB("
            INSERT INTO egw_timesheet(ts_start, ts_title, ts_quantity, cat_id, ts_owner, ts_created, ts_modified, ts_modifier, ts_status, pl_id) 
            VALUES ('$start', '$title', 0, '$cat_id', '$user', '$modified', '$modified', '$modifier', '$status', NULL )
        "));
		$status = 'Active';
		self::Status($status, $user);
	}

	public static function OUT($user)
	{
		$realtime = Carbon::now()->timestamp;

		$row = self::isOnline($user);
		$id = $row['ts_id'];

		$title = lang('Worktime');
		$end = self::getIntervalTime($user, 'logout');
		$start = $row['ts_start'];
		$calc = $end - $start;
		$duration = $calc / 60;
		$modified = Carbon::now()->timestamp;

		$status = self::TimesheetStatusID('attendance');
		$work_cat = Categories::Get()['work'];
		$cat_id = $work_cat['id'];

		(new DB("
            UPDATE egw_timesheet 
            SET ts_title = '$title', ts_duration = '$duration', ts_modified = '$modified', ts_status = '$status', cat_id = '$cat_id' 
            WHERE ts_id = '$id'
        "));

		$status = 'Not active';
		self::Status($status, $user);

		self::addAutomaticBreaks($user);
	}

	public static function addAutomaticBreaks($user) {
	
		// Fetch all timesheets for today, sorted by start time
		$status = self::TimesheetStatusID('attendance');
	 
		$result = (new DB("
			SELECT * FROM egw_timesheet 
			WHERE ts_owner = '$user' 
			AND ts_start >= UNIX_TIMESTAMP(CURDATE()) AND ts_status = $status
			ORDER BY ts_start ASC
		"))->FetchAll();

		# var_dump($result);
	
		$timesheetCount = count($result);
	
		if ($timesheetCount == 1) {  
			// ✅ Case 1: Only ONE timesheet (Split it into two with a break)
			self::splitSingleTimesheet($user, $result[0]);
		} elseif ($timesheetCount > 1) {  
			// ✅ Case 2: TWO timesheets (Calculate break between them)
			self::calculateBreakBetween($result);
		}
	}

	/**
	 * Splits a single timesheet into two and inserts a calculated break.
	 */
	public static function splitSingleTimesheet($user, $timesheet) {
		$timesheetID = $timesheet['ts_id'];
		$total_minutes = $timesheet['ts_duration'];
		$minutes_to_deduct = 0;

		// Apply break rules
		if ($total_minutes >= 360 && $total_minutes < 540) { // 6-9 hours (360-540 min)
			$minutes_to_deduct = 30;
		} elseif ($total_minutes >= 540 && $total_minutes < 600) { // 9-10 hours (540-600 min)
			$minutes_to_deduct = 45;
		}

		if ($minutes_to_deduct > 0) {
			$split_time = $timesheet['ts_start'] + (($total_minutes / 2) * 60); // Midpoint of shift in seconds
			$break_start = $split_time;
			$break_end = $break_start + ($minutes_to_deduct * 60); // Break time in seconds
			$half_duration = floor($total_minutes / 2); // Avoid floating point errors
			$deducted_duration = $half_duration - $minutes_to_deduct; // deduct break time from second timesheet

			// Update the original timesheet to end at the split point
			(new DB("
				UPDATE egw_timesheet 
				SET ts_duration = $half_duration 
				WHERE ts_id = '$timesheetID'
			"));

			// Insert new timesheet after break
			(new DB("
				INSERT INTO egw_timesheet (ts_start, ts_title, ts_quantity, ts_duration, cat_id, ts_owner, ts_created, ts_modified, ts_modifier, ts_status, pl_id) 
				VALUES ('$break_end', '".$timesheet['ts_title']."', 0, '$deducted_duration', '".$timesheet['cat_id']."', '$user', '".$timesheet['ts_created']."', '".$timesheet['ts_modified']."', '".$timesheet['ts_modifier']."', '".$timesheet['ts_status']."', NULL)
			"));
		}
	}

	/**
	 * Ensures a break exists between multiple timesheets.
	 */
	public static function calculateBreakBetween($result) {
		$total_minutes = 0;
		foreach ($result as $timesheet) {
			$total_minutes += $timesheet['ts_duration'];
		}

		// Determine required break time
		$break_to_add = 0;
		if ($total_minutes >= 360 && $total_minutes < 540) { 
			$break_to_add = 30;
		} elseif ($total_minutes >= 540 && $total_minutes < 600) { 
			$break_to_add = 45;
		}
		
		$existingBreak = 0;
	
		if ($break_to_add > 0) {
			for ($i = 1; $i < count($result); $i++) {
				$previousTimesheet = $result[$i - 1];
				$previousEnd = $previousTimesheet['ts_start'] + ($previousTimesheet['ts_duration'] * 60);
				$currentTimesheet = $result[$i];
				$currentStart = $currentTimesheet['ts_start'];
				$currentID = $currentTimesheet['ts_id'];

				$existingBreak += ($currentStart - $previousEnd) / 60; // Convert to minutes
			}

			// If break is too short, move the next timesheet forward
			if ($existingBreak < $break_to_add) {
				$total_left_break = $break_to_add - $existingBreak;
				$newStart = $currentStart + ($total_left_break * 60);
				$duration = $currentTimesheet['ts_duration'] - $total_left_break; #deduct the rest of the break form last timesheet
				
				(new DB("
					UPDATE egw_timesheet 
					SET ts_start = $newStart, ts_duration = $duration
					WHERE ts_id = '$currentID'
				"));
			}
		}
}

	// This will add values to status to see on the first page if the user is online or not
	public static function Status($status, $ts_owner)
	{
		(new DB("
            UPDATE egw_attendance SET status = '$status' 
            WHERE (user = $ts_owner) AND (end is NULL OR end >= CURDATE())
        "));
	}

	public static function hasVacation()
	{
	}

	public static function hasAnyOtherAbsence()
	{
	}

	public static function isHoliday(int $unixtime)
	{
		$date = $unixtime ? date('Y-m-d', $unixtime) : date('Y-m-d');

		$Holidays = Holidays::Render('de')->District('Baden-Württemberg');

		$Holiday = Holidays::isHoliday($date);

		return $Holiday;
	}

	// take the users that have an available contract
	public static function ValidContracts()
	{
		$locationUsers = Location::getUsersFromSameLocation();
		if (!empty($locationUsers)) {
			$users = implode(',', $locationUsers['users']);
			$accounts = (new DB("
				SELECT * FROM egw_addressbook a 
				RIGHT OUTER JOIN egw_attendance b ON a.account_id = b.user 
				WHERE b.user IN ($users) AND (b.end is NULL OR b.end >= CURDATE())
				ORDER BY b.sort_order
			"))->FetchAll();
		} else {
			$accounts = (new DB("
				SELECT * FROM egw_addressbook a 
				RIGHT OUTER JOIN egw_attendance b ON a.account_id = b.user 
				WHERE b.end is NULL OR b.end >= CURDATE()
				ORDER BY b.sort_order
			"))->FetchAll();
		}
		

		foreach ($accounts as $key => $account) {
			$account['fullname'] = $account['n_family'].', '.$account['n_given'];
			if (self::isOnline($account['account_id'])) {
				$account['status'] = 1;
				$account['color'] = 'green';
			} else {
				$account['status'] = 0;
				$account['color'] = '#bd1515';
			}
			$accounts[$key] = $account;
		}

		return $accounts;
	}

	/**
	 * Logout Account after 10 Hours reached
	 */
	public static function timeOutTrackerAfter10Hours() {

		// Fetch all accounts that are currently active
		$accounts = (new DB("
			SELECT * FROM egw_addressbook a 
			RIGHT OUTER JOIN egw_attendance b ON a.account_id = b.user 
			WHERE b.end is NULL OR b.end >= CURDATE()
			ORDER BY b.sort_order
		"))->FetchAll();

		if (!$accounts) {
			return; // No active accounts, exit early.
		}
	
		foreach ($accounts as $account) {
			$online = self::isOnline($account['account_id']);
			if ($online) {
				$ts_owner = $online['ts_owner']; // Ensure ts_owner is set correctly

				// Get total work time from timesheets (optimized query)
				$timesheets = (new DB("
					SELECT * FROM egw_timesheet 
					WHERE ts_owner = '$ts_owner' 
					AND ts_start >= UNIX_TIMESTAMP(CURDATE())
				"))->FetchAll();

				$total_minutes = 0;

				foreach ($timesheets as $timesheet) {
					$total_minutes += $timesheet['ts_duration'];
				}

				if ($total_minutes >= 600) {
					static::OUT($ts_owner);
				}
			}
		}
	}

	// Checks if the user is logged in or not
	public static function isOnline($user)
	{
		$status = self::TimesheetStatusID('attendance');
		$title = lang('Ongoing Work');
		$sql = "
            SELECT * FROM egw_timesheet 
            WHERE ts_owner = '$user' AND ts_title = '$title' AND ts_status = $status
        ";
		$output = (new DB($sql))->Fetch();

		return $output;
	}

	/**
	 * TO BE REVIEWED
	 * maybe another table will becreated to save values on the first install
	 * egw_attendance_settings or egw_attendance_config.
	 */
	public static function TimesheetStatusID($name)
	{
		if (is_string($name)) {
			$data = (new DB())->Query("SELECT * FROM egw_config WHERE config_name = 'status_labels'")->Fetch();
			$status_labels = ($data ? json_decode($data['config_value'], true) : false);
			$key = array_search('attendance', array_column($status_labels, 'name'));
			foreach ($status_labels as $num => $status) {
				if ($status['name'] == 'attendance') {
					$key = $num;
				}
			}

			return $key ? $key : false;
		} else {
			return false;
		}
	}

	public static function init_static()
	{
	}

	public static function getIntervalTime($user, $type = false)
	{
		$data = json_decode(Core::Self($user)['time_interval_data'], true); // we turn the interval data from json to an array

		if ($data && $type) {
			$date = date('Y-m-d H:i:s', Carbon::now()->timestamp);
			$hour = (int) date('H', Carbon::now()->timestamp);
			$minute = (int) date('i', Carbon::now()->timestamp);

			switch ($type) {
				case 'login':
					if ($data['inTimeValue'] && $data['inRoundValue']) {
						$roundingType = ($data['inRoundingType'] == 'roundup') ? 1 : 0;

						$round = self::roundTime($minute, $data['inTimeValue'], $data['inRoundValue'], $roundingType);

						if ($round['result'] == '60') {
							$minute = 0;
							$hour++;
						} else {
							$minute = $round['result'];
						}
					} else {
						return Carbon::now()->timestamp;
					}
					break;

				case 'logout':
					if ($data['outTimeValue'] && $data['outRoundValue']) {
						$roundingType = ($data['outRoundingType'] == 'roundup') ? 1 : 0;

						$round = self::roundTime($minute, $data['outTimeValue'], $data['outRoundValue'], $roundingType);

						if ($round['result'] == '60') {
							$minute = 0;
							$hour++;
						} else {
							$minute = $round['result'];
						}
					} else {
						return Carbon::now()->timestamp;
					}
					break;

				default:
					return Carbon::now()->timestamp;
					break;
			}

			$newtimestamp = strtotime(date('Y-m-d ', Carbon::now()->timestamp).''.$hour.':'.$minute.':00'); // notice when minute is 60 than minute 00 hour = hour + 1
			//echo date('Y-m-d H:i:s', $newtimestamp);
			return $newtimestamp;
		} else {
			return Carbon::now()->timestamp;
		}
	}

	/**
	 * TO BE REVIEWED.
	 *
	 * @param     $minute     [description]
	 * @param     $roundnr    [description]
	 * @param     $roundpoint [description]
	 * @param int $type       [description]
	 *
	 * @return [description]
	 */
	public static function roundTime($minute, $roundnr, $roundpoint, $type = 1)
	{

		// $roundnr = 15;
		// $roundpoint = 5;
		// $type = 1; DAS BEDEUTET AUFRUNEDEN
		// $type = 0; Das bedeutet abrunden
		// $minute = 12; is the minute

		$divisor = $roundnr / $roundpoint; // for exapmle 15 / 5 = 3

		// number of possibilities
		$possibilities = 60 / $roundnr; // 60 / 15 = 4
		$i = 1;                         // counter
		$last_intervalPoint = 0;        // last point of interval
		$intervalPoint = 0;             // exact point of interval
		while ($i <= $possibilities) {  // loop through all possibilities
			$devider = $i * $divisor;   // this is the devider
										// $i * 3 = 3

			$sumer = $intervalPoint;    //summ number

			$intervalPoint = $i * $roundnr; // 1 * 15 = 15

			if (($minute >= $last_intervalPoint) && ($minute <= $intervalPoint)) {
				$check = ($sumer + ($intervalPoint / $devider)) == $minute;
				if ($check) {
					if ($type == 1) {
						$output = $intervalPoint;
					} elseif ($type == 0) {
						$output = $last_intervalPoint;
					}
				} else {
					if (($sumer + ($intervalPoint / $devider)) < $minute) {
						$output = $intervalPoint;
					} elseif (($sumer + ($intervalPoint / $devider)) > $minute) {
						$output = $last_intervalPoint;
					}
				}

				break;
			}

			$last_intervalPoint = $intervalPoint;

			$i++;
		}

		$summ = $sumer.' + ('.$intervalPoint.' / '.$divisor.') = '.$output.'</br>';

		return [
			'startnumber'       => $i,
			'minute'            => $minute,
			'possibilities'     => $possibilities,
			'result'            => $output,
			'sumer'             => $sumer,
			'devider'           => $devider,
		];
	}
}

Tracker::init_static();
