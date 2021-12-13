<?php

namespace Attendance;

use AgroEgw\DB;
use Carbon\Carbon;

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
        $accounts = (new DB('
            SELECT * FROM egw_addressbook a 
            RIGHT OUTER JOIN egw_attendance b ON a.account_id = b.user 
            WHERE b.end is NULL OR b.end >= CURDATE() ORDER BY b.sort_order
        '))->FetchAll();

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
     * @param  $minute     [description]
     * @param  $roundnr    [description]
     * @param  $roundpoint [description]
     * @param int $type [description]
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
