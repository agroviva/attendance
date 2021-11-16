<?php

namespace Attendance;

class Holidays
{
    private static $holidays = [];
    private static $districts = [];

    private static $tempHolidays = [];

    public static function Render($country_code, $yearShift = 0)
    {
        $filename = $country_code.'_Holidays.json';
        $path = APPDIR.'/../../holidays/'.$filename;

        if (file_exists($path)) {
            $data = json_decode(file_get_contents($path), true);
            $year = intval(date('Y')) + $yearShift;

            $holidays = $data['holidays'];
            self::$districts = $data['districts'];

            foreach ($holidays as $holiday) {
                $name = $holiday['name'];
                if (!empty($holiday['const'])) {
                    $hDate = $holiday['const'];
                } else {
                    $hDate = $holiday[$year];
                }
                $day = $hDate['d'];
                $month = $hDate['m'];
                self::$holidays[] = [
                    'name' => $name,
                    'date' => "$year-$month-$day",
                ];
            }
            self::$tempHolidays = self::$holidays;

            return new static();
        }
    }

    public static function District($district)
    {
        if (!empty(self::$holidays)) {
            $holidays = [];
            foreach (self::$holidays as $key => $holiday) {
                if (in_array($key, self::$districts[$district])) {
                    $holidays[] = $holiday;
                }
            }
            self::$tempHolidays = $holidays;

            return $holidays;
        }

        return [];
    }

    public static function All()
    {
        if (!empty(self::$holidays)) {
            return self::$holidays;
        }

        return [];
    }

    public static function isHoliday($date = false)
    {
        $date = $date ?: date('Y-m-d');
        foreach (self::$tempHolidays as $holiday) {
            if ($date == $holiday['date']) {
                return $holiday['name'];
            }
        }

        return false;
    }
}
