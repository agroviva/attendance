<?php

namespace Attendance;

use AgroEgw\Api\User;
use AgroEgw\DB;
use Carbon\CarbonPeriod;

class TimeAccount
{
    protected static $timeaccounts = [];

    public static function init_static()
    {
        $so = new \attendance_so();
        $categoriesData = Categories::Get();
        // unset($categoriesData["parent"]);
        foreach ($categoriesData as $categoryData) {
            if (!empty($categoryData['id'])) {
                $categories[] = $categoryData['id'];
            }
        }
        $categories = implode(',', $categories);

        $contracts = (new Contracts())->Load();

        foreach ($contracts as $contract) {
            if (!$contract['meta_data']) {
                (new \attendance_so())->updateMetaData($contract['id']);
                continue;
            }

            $user = (object) User::Read($contract['user']);

            $startOfTheContract = $contract['start'];
            $endOfTheContract = $contract['end'];

            $startTime = strtotime($startOfTheContract);
            $endTime = $endOfTheContract ? strtotime($endOfTheContract) : time();

            $isTime = (new DB("
				SELECT SUM(ts_duration) AS duration FROM egw_timesheet
				WHERE cat_id IN($categories) AND 
				(ts_status >= 0 OR ts_status IS NULL) AND 
				ts_owner = $contract[user] AND
				ts_start >= $startTime AND ts_start <= $endTime
			"))->Fetch()['duration'];
            $isTime /= 60;

            $endDate = $endTime < time() ? date('Y-m-d', $endTime) : date('Y-m-d', time());
            $period = CarbonPeriod::create($startOfTheContract, $endDate);

            $days = [
                $contract['monday'],
                $contract['tuesday'],
                $contract['wednesday'],
                $contract['thursday'],
                $contract['friday'],
                $contract['saturday'],
                $contract['sunday'],
            ];

            $rhythm = array_values(json_decode($contract['weekdays_rhymes'], true));
            $rhythmCount = $rhythm;

            $weekdays = array_values(json_decode($contract['meta_data'], true)['weekdays']);

            $shouldTime = 0;
            foreach ($period as $carbon) {
                $date = $carbon->format('Y-m-d');
                $dayOfWeek = $carbon->dayOfWeekIso - 1;

                if (!($rhythm[$dayOfWeek] > 1)) {
                    $shouldTimeDay = $days[$dayOfWeek] ? $days[$dayOfWeek] / 60 / 60 : 0;
                } elseif (!empty($weekdays[$dayOfWeek]['valid_on']) && $date < $weekdays[$dayOfWeek]['valid_on']) {
                    $shouldTimeDay = 0;
                } elseif ($rhythm[$dayOfWeek] == $rhythmCount[$dayOfWeek]) {
                    $shouldTimeDay = $days[$dayOfWeek] ? $days[$dayOfWeek] / 60 / 60 : 0;
                    $rhythmCount[$dayOfWeek]--;
                } elseif ($rhythm[$dayOfWeek] > $rhythmCount[$dayOfWeek]) {
                    if ($rhythmCount[$dayOfWeek] == 1) {
                        $rhythmCount[$dayOfWeek] = $rhythm[$dayOfWeek];
                    } else {
                        $rhythmCount[$dayOfWeek]--;
                    }
                    $shouldTimeDay = 0;
                } else {
                    $shouldTimeDay = 0;
                }

                $shouldTime += $shouldTimeDay;
            }

            self::$timeaccounts[$contract['id']] = [
                'name'        => $user->account_fullname,
                'uid'         => $user->account_id,
                'isTime'      => round($isTime, 2),
                'shouldTime'  => $shouldTime,
                'timeaccount' => round($isTime - $shouldTime, 2),
            ];
        }
    }

    public static function oldAlgo($contract_id)
    {
        $so = new \attendance_so();
        $categoriesData = Categories::Get();
        // unset($categoriesData["parent"]);
        foreach ($categoriesData as $categoryData) {
            if (!empty($categoryData['id'])) {
                $categories[] = $categoryData['id'];
            }
        }
        $categories = implode(',', $categories);

        $contracts = (new Contracts())->Load();

        foreach ($contracts as $contract) {
            $user = (object) User::Read($contract['user']);

            if (!$contract['meta_data']) {
                (new \attendance_so())->updateMetaData($contract['id']);
                continue;
            }

            if ($contract['id'] != $contract_id) {
                continue;
            }

            $startOfTheContract = $contract['start'];
            $endOfTheContract = $contract['end'];

            $startTime = strtotime($startOfTheContract);
            $endTime = $endOfTheContract ? strtotime($endOfTheContract) : time();

            $isTime = (new DB("
				SELECT SUM(ts_duration) AS duration FROM egw_timesheet
				WHERE cat_id IN($categories) AND 
				(ts_status >= 0 OR ts_status IS NULL) AND 
				ts_owner = $contract[user] AND
				ts_start >= $startTime AND ts_start <= $endTime
			"))->Fetch()['duration'];
            $isTime /= 60;

            $timesheets = (new DB("
				SELECT * FROM egw_timesheet
				WHERE cat_id IN($categories) AND 
				(ts_status >= 0 OR ts_status IS NULL) AND 
				ts_owner = $contract[user] AND
				ts_start >= $startTime AND ts_start <= $endTime ORDER BY ts_start DESC
			"))->FetchAll() ?? array();

            $endDate = $endTime < time() ? date('Y-m-d', $endTime) : date('Y-m-d', time());
            $period = CarbonPeriod::create($startOfTheContract, $endDate);

            $days = [
                $contract['monday'],
                $contract['tuesday'],
                $contract['wednesday'],
                $contract['thursday'],
                $contract['friday'],
                $contract['saturday'],
                $contract['sunday'],
            ];

            $rhythm = array_values(json_decode($contract['weekdays_rhymes'], true));
            $rhythmCount = $rhythm;

            $weekdays = array_values(json_decode($contract['meta_data'], true)['weekdays']);

            $periodData = [];

            if (!empty($timesheets)) {
                foreach ($timesheets as $timesheet) {
                    $date = date('Y-m-d', $timesheet['ts_start']);
                    $periodData['days'][$date]['is'] = ($periodData['days'][$date]['is'] ?: 0) + ($timesheet['ts_duration'] / 60);

                    $start = $timesheet['ts_start'];
                    $end = $timesheet['ts_start'] + ($timesheet['ts_duration'] * 60);
                    $inTime = date('H:i', $start);
                    $outTime = date('H:i', $end);
                    $isTimeInHours = number_format($timesheet['ts_duration'] / 60, 2);

                    if ($isTimeInHours < 0) {
                        $text = "Korrektur: $timesheet[ts_title] ({$isTimeInHours}h)";
                    } else {
                        $text = "$inTime - $outTime: $timesheet[ts_title] ({$isTimeInHours}h)";
                    }

                    $periodData['days'][$date]['title'] = $text.'<br>'.$periodData['days'][$date]['title'];
                }
            }

            $shouldTime = 0;
            foreach ($period as $carbon) {
                $date = $carbon->format('Y-m-d');
                $dayOfWeek = $carbon->dayOfWeekIso - 1;

                if (!($rhythm[$dayOfWeek] > 1)) {
                    $shouldTimeDay = $days[$dayOfWeek] ? $days[$dayOfWeek] / 60 / 60 : 0;
                } elseif (!empty($weekdays[$dayOfWeek]['valid_on']) && $date < $weekdays[$dayOfWeek]['valid_on']) {
                    $shouldTimeDay = 0;
                } elseif ($rhythm[$dayOfWeek] == $rhythmCount[$dayOfWeek]) {
                    $shouldTimeDay = $days[$dayOfWeek] ? $days[$dayOfWeek] / 60 / 60 : 0;
                    $rhythmCount[$dayOfWeek]--;
                } elseif ($rhythm[$dayOfWeek] > $rhythmCount[$dayOfWeek]) {
                    if ($rhythmCount[$dayOfWeek] == 1) {
                        $rhythmCount[$dayOfWeek] = $rhythm[$dayOfWeek];
                    } else {
                        $rhythmCount[$dayOfWeek]--;
                    }
                    $shouldTimeDay = 0;
                } else {
                    $shouldTimeDay = 0;
                }

                $periodData['days'][$date]['should'] = $shouldTimeDay;

                $shouldTime += $shouldTimeDay;
            }
            $periodData['conclusion']['is'] = $isTime;
            $periodData['conclusion']['should'] = $shouldTime;

            if (is_array($periodData['days'])) {
                ksort($periodData['days']);
            }

            return $periodData;
            // $user->account_fullname
                // $user->account_id
        }
    }

    public static function Get($contract_id)
    {
        return self::$timeaccounts[$contract_id];
    }
}
TimeAccount::init_static();
