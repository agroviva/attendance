<?php

use AgroEgw\DB;
use Attendance\Categories;
use Attendance\TimeAccount;
use Attendance\Tracker;
use Carbon\Carbon;

/**
 * attendance - bo:.
 *
 * @link http://www.agroviva.de
 *
 * @author Enver Morinaj
 * @copyright (c) 2015-16 by Agroviva GmbH <info@agroviva.de>
 * @license ---- GPL - GNU General Public License
 *
 * @version $Id: class.attendance_bo.inc.php $
 */
class attendance_bo extends attendance_so
{
    public function __construct()
    {
        $this->db = clone $GLOBALS['egw']->db;
        $this->db->set_app('attendance');
        $this->so = new attendance_so();
    }

    public function weekdays_rhymes($mon, $tue, $wed, $thu, $fri, $sat, $sun)
    {
        $data = [
            [
                'name'  => 'monday',
                'value' => $mon,
            ],
            [
                'name'  => 'tuesday',
                'value' => $tue,
            ],
            [
                'name'  => 'wednesday',
                'value' => $wed,
            ],
            [
                'name'  => 'thursday',
                'value' => $thu,
            ],
            [
                'name'  => 'friday',
                'value' => $fri,
            ],
            [
                'name'  => 'saturday',
                'value' => $sat,
            ],
            [
                'name'  => 'sunday',
                'value' => $sun,
            ],
        ];

        foreach ($data as $weekday) {
            if ($weekday['value'] == 'weekly') {
                ${$weekday['name']} = 1;
            } elseif ($weekday['value'] == 'twoweekly') {
                ${$weekday['name']} = 2;
            } elseif ($weekday['value'] == 'threeweekly') {
                ${$weekday['name']} = 3;
            } elseif ($weekday['value'] == 'fourweekly') {
                ${$weekday['name']} = 4;
            }
        }

        $workdays_array = [
                'monday'    => $monday,
                'tuesday'   => $tuesday,
                'wednesday' => $wednesday,
                'thursday'  => $thursday,
                'friday'    => $friday,
                'saturday'  => $saturday,
                'sunday'    => $sunday,
            ];

        return json_encode($workdays_array);
    }

    public function weekdays_calculation($date, $start_contarct, $name, $short, $day_count, $day_value, $day_rhyme, $statement = false, $end_contract = null)
    {
        $day_rhyme = json_decode($day_rhyme, true);

        $day_count = $day_count[$name];
        $day_value = $day_value[$name];
        $day_rhyme = $day_rhyme[$name];

        if (!is_int($day_rhyme)) {
            $day_rhyme = 1;
        }

        $day_count = $day_count / $day_rhyme;

        if ($date === ucfirst($short)) {
            $diff_day = $this->dateDiff($start_contarct, $end_contract);
        } else {
            $first_day = date('Y-m-d', strtotime('next '.$monday, strtotime($start_contarct)));
            $diff_day = $this->dateDiff($first_day, $end_contract);
        }
        if (($day_rhyme) && ($day_rhyme != 0)) {
            $weekday = $diff_day / $day_rhyme;
        } else {
            $weekday = 0;
        }

        $day_sum = $day_value * ceil($day_count);

        if ($statement) {
            return [$name => $weekday];
        }
        if ($_SERVER['SERVER_NAME'] == 'e01.agroviva.net') {
            //Dump($day_count);
        }

        return $day_sum;
    }

    /**
     * TO BE REVIEWED.
     *
     * Before removing make sure this function is not at use anymore.
     *
     * @param  $user       [description]
     * @param  $categories [description]
     * @param bool $statement [description]
     */
    public function Time_Account($user, $categories, $statement = false)
    {
        // Time account
        /*
            The function dayCount make the calculation to find out how oft will
            come every day from the start day of Contract till the current date
        */

        $time_should = $this->so->get_should_hours($user); // it takes the data from database to show the should Hours
        $mon = $time_should['monday'];
        $tue = $time_should['tuesday'];
        $wed = $time_should['wednesday'];
        $thu = $time_should['thursday'];
        $fri = $time_should['friday'];
        $sat = $time_should['saturday'];
        $sun = $time_should['sunday'];

        $now_time_unix = Carbon::now()->timestamp;
        $now_time = date('Y-m-d', $now_time_unix);
        $start_contarct = $time_should['start'];

        if (!$statement) {
            if (Carbon::parse($start_contarct)->timestamp > Carbon::now()->timestamp) {
                return 0;
            }
        }

        $end_contract = ((is_null($time_should['end']) || $time_should['end'] >= $now_time) ? $now_time : $time_should['end']);

        // here we will find how oft comes every day
        $mon_num = $this->dayCount($start_contarct, $end_contract, 1); // Monday
        $tue_num = $this->dayCount($start_contarct, $end_contract, 2); // Tuesday
        $wed_num = $this->dayCount($start_contarct, $end_contract, 3); // Wednsday
        $thu_num = $this->dayCount($start_contarct, $end_contract, 4); // Thursday
        $fri_num = $this->dayCount($start_contarct, $end_contract, 5); // Friday
        $sat_num = $this->dayCount($start_contarct, $end_contract, 6); // Saturday
        $sun_num = $this->dayCount($start_contarct, $end_contract, 7); // Sunday

        $user_contract = $this->so->should_user($user);

        $date = date('D', strtotime($start_contarct));

        $The_Days = [
            ['name' => 'monday', 'short' => 'mon'],
            ['name' => 'tuesday', 'short' => 'tue'],
            ['name' => 'wednesday', 'short' => 'wed'],
            ['name' => 'thursday', 'short' => 'thu'],
            ['name' => 'friday', 'short' => 'fri'],
            ['name' => 'saturday', 'short' => 'sat'],
            ['name' => 'sunday', 'short' => 'sun'],
        ];

        $day_value = [
            'monday'    => $mon,
            'tuesday'   => $tue,
            'wednesday' => $wed,
            'thursday'  => $thu,
            'friday'    => $fri,
            'saturday'  => $sat,
            'sunday'    => $sun,
        ];

        $day_count = [
            'monday' 		   => $mon_num,
            'tuesday' 		  => $tue_num,
            'wednesday' 	 => $wed_num,
            'thursday' 		 => $thu_num,
            'friday' 		   => $fri_num,
            'saturday' 		 => $sat_num,
            'sunday' 		   => $sun_num,
        ];

        if ($statement) {
            $day_data = [];
            foreach ($The_Days as $The_Day) {
                $data = $this->weekdays_calculation($date, $start_contarct, $The_Day['name'], $The_Day['short'], $day_count, $day_value, $user_contract['weekdays_rhymes'], true, $end_contract);
                $day_data += $data;
            }

            return $day_data;
        } else {
            foreach ($The_Days as $The_Day) {
                $sum = $this->weekdays_calculation($date, $start_contarct, $The_Day['name'], $The_Day['short'], $day_count, $day_value, $user_contract['weekdays_rhymes'], false, $end_contract);
                $all_sum += $sum;
            }
        }

        $total_days_sum = ($all_sum) * 60; // Here it shows how many should hours are

        $time_account = $this->so->made_all($user, $start_contarct, $categories, $end_contract); // Here it shows the total of hours

        // it only calculates the exact difference between the start and the time right now
        $start_now = round(($time_account['all_dur'] - $total_days_sum) / 60, 2);
        // if ($_SERVER['SERVER_NAME'] == "e01.agroviva.net") {
        // 	Dump(($total_days_sum/60));
        // }
        return $start_now;
    }

    /**
     * TO BE REVIEWED.
     *
     * @param  $weekdays_rhymes [description]
     * @param  $start           [description]
     * @param  $user            [description]
     *
     * @return [description]
     */
    public function weekdaysSplit($weekdays_rhymes, $start, $user)
    {
        $now_time_unix = Carbon::now()->timestamp;
        $now_time = date('Y-m-d', $now_time_unix);

        // here we define how many days of a weekday are from a startdate until the actually date
        $mon_num = $this->dayCount($start, $now_time, 1); // Monday
        $tue_num = $this->dayCount($start, $now_time, 2); // Tuesday
        $wed_num = $this->dayCount($start, $now_time, 3); // Wednsday
        $thu_num = $this->dayCount($start, $now_time, 4); // Thursday
        $fri_num = $this->dayCount($start, $now_time, 5); // Friday
        $sat_num = $this->dayCount($start, $now_time, 6); // Saturday
        $sun_num = $this->dayCount($start, $now_time, 7); // Sunday

        $time_should = $this->so->get_should_hours($user); // it takes the data from database to show the should Hours

        // review duplicate(triplicate)
        $mon = $time_should['monday'];
        $tue = $time_should['tuesday'];
        $wed = $time_should['wednesday'];
        $thu = $time_should['thursday'];
        $fri = $time_should['friday'];
        $sat = $time_should['saturday'];
        $sun = $time_should['sunday'];

        $The_Days = [
            ['name' => 'monday', 'short' => 'mon'],
            ['name' => 'tuesday', 'short' => 'tue'],
            ['name' => 'wednesday', 'short' => 'wed'],
            ['name' => 'thursday', 'short' => 'thu'],
            ['name' => 'friday', 'short' => 'fri'],
            ['name' => 'saturday', 'short' => 'sat'],
            ['name' => 'sunday', 'short' => 'sun'],
        ];

        $day_value = [
            'monday'    => $mon,
            'tuesday'   => $tue,
            'wednesday' => $wed,
            'thursday'  => $thu,
            'friday'    => $fri,
            'saturday'  => $sat,
            'sunday'    => $sun,
        ];

        $day_count = [
            'monday' 		   => $mon_num,
            'tuesday' 		  => $tue_num,
            'wednesday' 	 => $wed_num,
            'thursday' 		 => $thu_num,
            'friday' 		   => $fri_num,
            'saturday' 		 => $sat_num,
            'sunday' 		   => $sun_num,
        ];

        $date = date('D', strtotime($start));

        $data = $this->weekdays_calculation($date, $start, $The_Day['name'], $The_Day['short'], $day_count, $day_value, $weekdays_rhymes, true);

        return $data;
    }

    /**
     * TO BE REVIEWED
     * This function should be enhanced and better placed since it is a duplicate from attendance_sync.
     *
     * @param  $unix_time [description]
     * @param  $ts_owner  [description]
     * @param  $cat_name  [description]
     * @param  $username  [description]
     * @param  $days      [description]
     * @param  $user      [description]
     * @param  $title     [description]
     * @param  $proof     [description]
     * @param  $cat_id    [description]
     * @param bool $backsync [description]
     */
    public function UserAvailability($unix_time, $ts_owner, $cat_name, $username, $days, $user, $title, $proof, $cat_id, $backsync = false)
    {
        if ($proof > 0) {
            if ($backsync) {
                echo "This $cat_name proces it's maded one time today for $username</p>";
            }
        } else {
            switch (date('D', $unix_time)) {
                case 'Mon':

                        if (strpos($days['monday'], '.') !== false) {
                            $duration = 0;
                        } else {
                            $duration = $user['monday'] / 60;
                        }

                    break;
                case 'Tue':

                        if (strpos($days['tuesday'], '.') !== false) {
                            $duration = 0;
                        } else {
                            $duration = $user['tuesday'] / 60;
                        }

                    break;
                case 'Wed':

                        if (strpos($days['wednesday'], '.') !== false) {
                            $duration = 0;
                        } else {
                            $duration = $user['wednesday'] / 60;
                        }

                    break;
                case 'Thu':

                        if (strpos($days['thursday'], '.') !== false) {
                            $duration = 0;
                        } else {
                            $duration = $user['thursday'] / 60;
                        }

                    break;
                case 'Fri':

                        if (strpos($days['friday'], '.') !== false) {
                            $duration = 0;
                        } else {
                            $duration = $user['friday'] / 60;
                        }

                    break;
                case 'Sat':

                        if (strpos($days['saturday'], '.') !== false) {
                            $duration = 0;
                        } else {
                            $duration = $user['saturday'] / 60;
                        }

                    break;
                case 'Sun':

                        if (strpos($days['sunday'], '.') !== false) {
                            $duration = 0;
                        } else {
                            $duration = $user['sunday'] / 60;
                        }

                    break;

                default:
                    print 'Error on handling your request';
                    break;
            }

            $start = $unix_time;
            if ($duration > 5) {
                // $this->logs->setLogs([
                //     'type'     => 'synchron',
                //     'user'     => $ts_owner,
                //     'time'     => $unix_time,
                //     'category' => $cat_id,
                // ]);
                $this->so->insert_timesheet($start, $duration, $title, $cat_id, $ts_owner, $start, $ts_owner);
                if ($backsync) {
                    echo 'The timesheet is inserted</p>';
                }
            } else {
                if ($backsync) {
                    echo "$user[n_fileas] doesn't work today</p>";
                }
            }
        }
    }

    /*
        The function dayCount make the calculation to find out how oft will
        come every day from the start day of Contract until the current date
    */

    /**
     * TO BE REVIEWED
     * It could not be needed anymore since we are using Carbon.
     */
    public function dayCount($from, $to, $day)
    {
        $from = new DateTime($from);
        $to = new DateTime($to);

        $wF = $from->format('w');
        $wT = $to->format('w');
        if ($wF < $wT) {
            $isExtraDay = $day >= $wF && $day <= $wT;
        } elseif ($wF == $wT) {
            $isExtraDay = $wF == $day;
        } else {
            $isExtraDay = $day >= $wF || $day <= $wT;
        }

        return floor($from->diff($to)->days / 7) + $isExtraDay;
    }

    public function dateDiff($d1, $d2)
    {
        // Return the number of days between the two dates:
        $result = round(abs(strtotime($d1) - strtotime($d2)) / 86400);

        return $result;
    }

    /**
     * TO BE REVIEWED.
     *
     * @param  $unixTime [description]
     * @param bool $time [description]
     *
     * @return [description]
     */
    public function dateDisplay($unixTime, $time = true)
    {
        if ($time) {
            $result = date('d.m.Y H:i', $unixTime); // date and time result
        } else {
            $result = date('d.m.Y', $unixTime); // date result
        }

        return $result; // return the date
    }

    public function status($user)
    {
        $vacation = new \Attendance\Vacation();

        //start weg
        $category = Categories::Get();

        $time_should = $this->so->get_should_hours($user); // it takes the data from database to show the should Hours
        $mon = $time_should['monday'];
        $tue = $time_should['tuesday'];
        $wed = $time_should['wednesday'];
        $thu = $time_should['thursday'];
        $fri = $time_should['friday'];
        $sat = $time_should['saturday'];
        $sun = $time_should['sunday'];

        $contract = (new DB("
            SELECT * FROM egw_attendance 
            WHERE user = $user AND (end IS NULL OR end >= '".date('Y-m-d')."');
        "))->Fetch();

        $time_account = TimeAccount::Get($contract['id'])['timeaccount']; // Here it shows the total of hours

        // $time_account = $this->Time_Account($user, [$category['vacation']['id'], $category['sickness']['id'], $category['school']['id'], $category['holiday']['id'], $category['work']['id']]); // Here it shows the total of hours

        $data = $this->Time_Account($user, [$category['vacation']['id'], $category['sickness']['id'], $category['school']['id'], $category['holiday']['id'], $category['work']['id']], true);

        if ($time_should) { // checks if whether there is a work contract for this user or not
            // this checks what day is today

            function shouldTime($day, $daytime)
            {
                if (strpos($day, '.') !== false) {
                    $time = 0;
                } else {
                    $time = $daytime;
                }
                if ($time <= 1) {
                    $should = lang('Only').' '.$time.' '.lang('Hour');
                } else {
                    $should = $time.' '.lang('Hours');
                }

                return $should;
            }

            switch (date('D', Carbon::now()->timestamp)) {
                case 'Mon':
                    $should = shouldTime($data['monday'], $mon);
                    break;
                case 'Tue':
                    $should = shouldTime($data['tuesday'], $tue);
                    break;
                case 'Wed':
                    $should = shouldTime($data['wednesday'], $wed);
                    break;
                case 'Thu':
                    $should = shouldTime($data['thursday'], $thu);
                    break;
                case 'Fri':
                    $should = shouldTime($data['friday'], $fri);
                    break;
                case 'Sat':
                    $should = shouldTime($data['saturday'], $sat);
                    break;
                case 'Sun':
                    $should = shouldTime($data['sunday'], $sun);
                    break;

                default:
                    $should = lang('Error');
                    break;
            }
        } else {
            $should = lang('Undefined Contract');
        }

        /*
            This if else statement checks if the user is logged in or not
            and shows the correct data for this user
        */
        $date = Carbon::now()->timestamp;
        $today_duration = $this->so->made_today($user, $category['vacation']['id'], $category['sickness']['id'], $category['school']['id'], $category['holiday']['id'], $category['work']['id']);
        $duration = $today_duration['ts_dur'];

        $check = Tracker::isOnline($user);

        if ($check) {
            $curr_status = 1;
            $row = $this->so->get_time($user, $category['vacation']['id'], $category['sickness']['id'], $category['school']['id'], $category['holiday']['id'], $category['work']['id']);
            $last = $row['ts_start'];
            $ts_start_date = date('d.m.Y H:i', $last);
            $start = $ts_start_date;

            $calc = ($date - $last) / 60 + $duration;
            $last_calc = ($date - $last) / 60;

            if ($last_calc <= 59) {
                $last_calc_min = round($last_calc, 1);
                $last_modified = lang('before').' '.$last_calc_min.' Min';
            } else {
                $last_calc_hour = round($last_calc / 60, 2);
                $last_modified = lang('before').' '.$last_calc_hour.' '.lang('Hour');
            }

            if ($calc <= 59) {
                if (($duration < 1) or ($duration == null)) {
                    $calc_min = ($date - $last) / 60;
                    $made = round($calc_min, 1);
                    $made = $made.' Min';
                } else {
                    $calc_min = ($date - $last) / 60 + $duration;
                    $made = round($calc_min, 1);
                    $made = $made.' Min';
                }
            } else {
                $calc_hour = (($date - $last) / 60 + $duration) / 60;
                $made = round($calc_hour, 2);
                $made = $made.' '.lang('Hour');
            }

            if ($time_should) {
                $time_account_sum = $time_account;
                if ($time_account_sum > 0) {
                    $time_account = '+'.round($time_account_sum, 2).' '.lang('Hours');
                } elseif ($time_account_sum < 0) {
                    $time_account = round($time_account_sum, 2).' '.lang('Hours');
                } else {
                    $time_account = lang('Your account is balanced');
                }
            } else {
                $time_account = lang('Undefined Contract');
            }
        } else {
            $curr_status = 0;
            $start = lang('Not started yet');
            $row = $this->so->get_last_time($user);
            $modified = $row['ts_modified'];
            $last = $row['ts_start'];

            if ((!$last) or ($last <= 1)) { // this checks if the user it is here for the first time or not
                $made = lang('Nothing');
                $last_modified = lang('First time here!');
            } else {
                $ts_last_modified = date('d.m.Y H:i', $modified);
                $last_modified = $ts_last_modified;

                $calc = $duration;

                if ($calc <= 59) {
                    if (($duration < 1) or ($duration == null)) {
                        $made = lang('Nothing');
                    } else {
                        $calc_min = $duration;
                        $made = round($calc_min, 1);
                        $made = $made.' Min';
                    }
                } else {
                    $calc_hour = $duration / 60;
                    $made = round($calc_hour, 2);
                    $made = $made.' '.lang('Hour');
                }
            }
            $time_account_sum = $time_account;

            if ($time_should) {
                if ($time_account_sum > 0) {
                    $time_account = '+'.round($time_account_sum, 2).' '.lang('Hours');
                } elseif ($time_account_sum < 0) {
                    $time_account = round($time_account_sum, 2).' '.lang('Hours');
                } else {
                    $time_account = lang('Your account is balanced');
                }
            } else {
                $time_account = lang('Undefined Contract');
            }
        }

        $vacation = $vacation->set($this->so->Contract($user))->get();
        $vacation = $vacation[count($vacation) - 1];

        if ($vacation['vacation'] <= 1) {
            $total_vac = $vacation['vacation'].' '.lang('day');
        } else {
            $total_vac = $vacation['vacation'].' '.lang('days');
        }

        $spent_vac = $vacation['spent_vac'];
        $rest_vac = $vacation['rest_vac'];

        $result = [
            'ts_owner'     => $user,
            'should'       => $should,
            'status'       => $curr_status,
            'ts_start'     => $start,
            'last'         => $last_modified,
            'made'         => $made,
            'time_account' => $time_account,
            'total_vac'    => $total_vac,
            'spent_vac'    => $spent_vac,
            'rest_vac'     => $rest_vac,
            ];

        return $result;
    }

    /**
     * TO REMOVE.
     *
     * @param  $date [description]
     */
    public function setTimeField($name, $H = false, $i = false)
    {
        $name = "exec[$name]";
        ob_start()
        ?>
		<div>
			<!-- BEGIN grid  -->
			<table class="eTdate" cellspacing="0">
				<tbody>
					<tr>
						<td align="left">
							<select name="<?php echo $name; ?>[H]" id="<?php echo $name ?>[H]" onfocus="self.status=\'Stunde: \'; return true;" onblur="self.status=\'\'; return true;">
							<?php 
                                $num = 0;
        while ($num <= 23) {
            ?>
										<option <?php if ($H == $num) {
                echo 'selected="selected"';
            } ?> value="<?php echo $num ?>"><?php if ($num <= 9) {
                echo '0'.$num;
            } else {
                echo $num;
            } ?></option>
									<?php
                                    $num++;
        } ?>
							</select>
						</td>
						<td align="left"><label for="'.$name.'[i]">:</label> 
							<select name="<?php echo $name; ?>[i]" id="<?php echo $name; ?>[i]" onfocus="self.status=\'Minute: \'; return true;" onblur="self.status=\'\'; return true;">
							<?php 
                                $num = 0;
        while ($num <= 59) {
            ?>
										<option <?php if ($i == $num) {
                echo 'selected="selected"';
            } ?> value="<?php echo $num ?>"><?php if ($num <= 9) {
                echo '0'.$num;
            } else {
                echo $num;
            } ?></option>
									<?php
                                    $num++;
        } ?>
							</select>
						</td>
					</tr>
				</tbody>
			</table>
			<!-- END grid  -->
		</div>
		<?php
        $output = ob_get_contents();
        ob_clean();

        return $output;
    }

    /**
     * TO REMOVE.
     *
     * @param  $date [description]
     */
    public function TimeToUnix($H, $i)
    {
        $result = ($H * 60 * 60) + ($i * 60);

        return $result;
    }

    /**
     * TO REMOVE.
     *
     * @param  $date [description]
     */
    public function UnixToTime($date)
    {
        $H = floor($date / 60 / 60);
        $i = floor(($date / 60) % 60);

        return [
            'H' => $H,
            'i' => $i,
            ];
    }

    /**
     * TO REMOVE.
     *
     * @param  $date [description]
     */
    public function extraPOST()
    {
        $content['monday'] = $this->TimeToUnix($_POST['exec']['monday']['H'], $_POST['exec']['monday']['i']);
        $content['tuesday'] = $this->TimeToUnix($_POST['exec']['tuesday']['H'], $_POST['exec']['tuesday']['i']);
        $content['wednesday'] = $this->TimeToUnix($_POST['exec']['wednesday']['H'], $_POST['exec']['wednesday']['i']);
        $content['thursday'] = $this->TimeToUnix($_POST['exec']['thursday']['H'], $_POST['exec']['thursday']['i']);
        $content['friday'] = $this->TimeToUnix($_POST['exec']['friday']['H'], $_POST['exec']['friday']['i']);
        $content['saturday'] = $this->TimeToUnix($_POST['exec']['saturday']['H'], $_POST['exec']['saturday']['i']);
        $content['sunday'] = $this->TimeToUnix($_POST['exec']['sunday']['H'], $_POST['exec']['sunday']['i']);

        $content['time_interval'] = json_encode([
                                        'inTimeValue'		   => $_POST['login_interval']['time'],
                                        'inRoundValue'		  => $_POST['login_interval']['roundpoint'],
                                        'inRoundingType'	 => $_POST['exec']['login_round'],
                                        'outTimeValue'		  => $_POST['logout_interval']['time'],
                                        'outRoundValue'		 => $_POST['logout_interval']['roundpoint'],
                                        'outRoundingType'	=> $_POST['exec']['logout_round'],
                                    ]);

        return $content;
    }

    /**
     * TO REMOVE.
     *
     * @param  $date [description]
     */
    public function logInOutIntervalForm($get, $data = [])
    {
        if (!$data) {
            $inValue = $inRoundValue = $outValue = $outRoundValue = '';
        } else {
            $inValue = $data['inTimeValue'];
            $inRoundValue = $data['inRoundValue'];
            $outValue = $data['outTimeValue'];
            $outRoundValue = $data['outRoundValue'];
        }

        if ($get == 'login_interval') {
            return '<input type="number" placeholder="'.lang('time interval').'" max="59" min="0" name="'.$get.'[time]" value="'.$inValue.'" id="login_interval" step="any" style="width: 14ex;" size="8"><input type="number" placeholder="'.lang('rounding point').'" max="59" min="0" name="login_interval[roundpoint]" value="'.$inRoundValue.'" id="login_interval" step="any" style="width: 14ex;" size="8">';
        } elseif ($get == 'logout_interval') {
            return '<input type="number" placeholder="'.lang('time interval').'" max="59" min="0" name="logout_interval[time]" value="'.$outValue.'" id="logout_interval" step="any" style="width: 14ex;" size="8"><input type="number" placeholder="'.lang('rounding point').'" max="59" min="0" name="logout_interval[roundpoint]" value="'.$outRoundValue.'" id="logout_interval" step="any" style="width: 14ex;" size="8"> ';
        }
    }

    public function weekdays()
    {
        return [
            'monday',
            'tuesday',
            'wednesday',
            'thursday',
            'friday',
            'saturday',
            'sunday',
        ];
    }
}
