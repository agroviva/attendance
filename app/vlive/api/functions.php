<?php

use Carbon\Carbon;

if (!function_exists("Dump")) {
    function Dump($dmp, $prnt = false)
    {
        echo '<pre>';
        if ($prnt) {
            print_r($dmp);
        } else {
            var_dump($dmp);
        }
        echo '</pre>';
    }
}

if (!function_exists("encryptIt")) {
    function encryptIt($q)
    {
        $cryptKey = 'qJB0rGtIn5UB1xG03efyCp';
        $qEncoded = base64_encode(mcrypt_encrypt(MCRYPT_RIJNDAEL_256, md5($cryptKey), $q, MCRYPT_MODE_CBC, md5(md5($cryptKey))));

        return  $qEncoded;
    }
}

if (!function_exists("decryptIt")) {
    function decryptIt($q)
    {
        $cryptKey = 'qJB0rGtIn5UB1xG03efyCp';
        $qDecoded = rtrim(mcrypt_decrypt(MCRYPT_RIJNDAEL_256, md5($cryptKey), base64_decode($q), MCRYPT_MODE_CBC, md5(md5($cryptKey))), "\0");

        return  $qDecoded;
    }
}

if (!function_exists("clockalize")) {
    function clockalize($in)
    {
        $h = intval($in);
        $m = round((((($in - $h) / 100.0) * 60.0) * 100), 0);
        if ($m == 60) {
            $h++;
            $m = 0;
        }
        $retval = sprintf('%02d:%02d', $h, $m);

        return $retval;
    }
}

if (!function_exists("decimalHours")) {
    function decimalHours($time)
    {
        $hms = explode(':', $time);
        $h = intval($hms[0]);
        $m = $hms[1] ? (intval($hms[1]) / 60) : 0;
        $s = $hms[2] ? (intval($hms[2]) / 3600) : 0;

        return $h + $m + $s;
    } 
}

if (!function_exists("Weekdays")) {
    function Weekdays()
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

if (!function_exists("TimeFilter")) {
    function TimeFilter()
    {
        $time = [
            [
                'name'    => 'today',
                'start'   => Carbon::now()->startOfDay()->timestamp,
                'end'     => Carbon::now()->endOfDay()->timestamp,
            ],
            [
                'name'    => 'yesterday',
                'start'   => Carbon::now()->subDay()->startOfDay()->timestamp,
                'end'     => Carbon::now()->subDay()->endOfDay()->timestamp,
            ],
            [
                'name'    => 'thisweek',
                'start'   => Carbon::now()->startOfWeek()->timestamp,
                'end'     => Carbon::now()->endOfWeek()->timestamp,
            ],
            [
                'name'    => 'lastweek',
                'start'   => Carbon::now()->subWeek()->startOfWeek()->timestamp,
                'end'     => Carbon::now()->subWeek()->endOfWeek()->timestamp,
            ],
            [
                'name'    => 'thismonth',
                'start'   => Carbon::now()->startOfMonth()->timestamp,
                'end'     => Carbon::now()->endOfMonth()->timestamp,
            ],
            [
                'name'    => 'lastmonth',
                'start'   => Carbon::now()->subMonth()->startOfMonth()->timestamp,
                'end'     => Carbon::now()->subMonth()->endOfMonth()->timestamp,
            ],
            [
                'name'    => 'thisyear',
                'start'   => Carbon::now()->startOfYear()->timestamp,
                'end'     => Carbon::now()->endOfYear()->timestamp,
            ],
            [
                'name'    => 'lastyear',
                'start'   => Carbon::now()->subYear()->startOfYear()->timestamp,
                'end'     => Carbon::now()->subYear()->endOfYear()->timestamp,
            ],
        ];

        return $time;
    }
}

if (!function_exists("SecurityLevels")) {
    function SecurityLevels()
    {
    }
}