<?php

namespace Attendance\Core;

use AgroEgw\DB;
use EGroupware\Api;

class Update
{
    public static $start;

    private static $executedAlready = false;

    private static $version;

    private static $update = [1, 2, 3, 4, 5, 6, 7, 8];

    public static function Start()
    {
        if (self::$executedAlready) {
            return;
        }

        self::version_7();

        self::$version = (new DB("
			SELECT * FROM egw_attendance_meta
			WHERE meta_name = 'attendance_version';
		"))->Fetch()['meta_data'] ?: 1;

        if (self::$version <= max(self::$update)) {
            call_user_func(__NAMESPACE__.'\Update::version_'.(self::$version));
            self::incrementV();
        }

        self::$executedAlready = true;
    }

    private static function incrementV()
    {
        $data = (new DB("
			SELECT * FROM egw_attendance_meta
			WHERE meta_name LIKE 'attendance_version';
		"))->Fetch();

        self::$version += 1;

        if (empty($data)) {
            new DB("INSERT INTO egw_attendance_meta 
				(meta_name, meta_data)
				VALUES('attendance_version', ".self::$version.')
			');
        } else {
            new DB('UPDATE egw_attendance_meta SET meta_data = '.self::$version." 
				WHERE meta_name LIKE 'attendance_version'
			");
        }
    }

    public static function version_1()
    {
    }

    public static function version_2()
    { // Support until 15.10.2018
        $column = (new DB("SHOW COLUMNS FROM `egw_attendance` LIKE 'meta_data';"))->Fetch();
        if (empty($column)) {
            (new DB('ALTER TABLE `egw_attendance` ADD COLUMN `meta_data` LONGTEXT;'));

            $contracts = (new DB('SELECT * FROM egw_attendance'))->FetchAll();
            foreach ($contracts as $contract) {
                $rythms = json_decode($contract['weekdays_rhymes'], true);
                if (empty($rythms)) {
                    $rythms = [
                        'monday'    => 1,
                        'tuesday'   => 1,
                        'wednesday' => 1,
                        'thursday'  => 1,
                        'friday'    => 1,
                        'saturday'  => 1,
                        'sunday'    => 1,
                    ];
                }

                $rounding = json_decode($contract['time_interval_data'], true);

                $rules = [
                    'weekdays' => [
                        'monday' => [
                            'rythm' => (int) $rythms['monday'],
                        ],
                        'tuesday' => [
                            'rythm' => (int) $rythms['tuesday'],
                        ],
                        'wednesday' => [
                            'rythm' => (int) $rythms['wednesday'],
                        ],
                        'thursday' => [
                            'rythm' => (int) $rythms['thursday'],
                        ],
                        'friday' => [
                            'rythm' => (int) $rythms['friday'],
                        ],
                        'saturday' => [
                            'rythm' => (int) $rythms['saturday'],
                        ],
                        'sunday' => [
                            'rythm' => (int) $rythms['sunday'],
                        ],
                    ],
                    'interval' => [
                        'in' => [
                            'stepwise'       => $rounding['inTimeValue'],
                            'rounding_point' => $rounding['inRoundValue'],
                            'rounding_type'  => $rounding['inRoundType'],
                        ],
                        'out' => [
                            'stepwise'       => $rounding['outTimeValue'],
                            'rounding_point' => $rounding['outRoundValue'],
                            'rounding_type'  => $rounding['outRoundType'],
                        ],
                    ],
                ];

                $rules = json_encode($rules);
                (new DB("UPDATE egw_attendance SET meta_data = '$rules' WHERE att_id = $contract[att_id]"));
            }
        }
    }

    public static function version_3()
    { // Support until 20.10.2018
        try {
            (new DB('ALTER TABLE `egw_attendance` CHANGE `att_hours_mon` `monday` INT(11) NULL DEFAULT NULL;'));
        } catch (\Exception $e) {
            // empty
        }

        try {
            (new DB('ALTER TABLE `egw_attendance` CHANGE `att_hours_tue` `tuesday` INT(11) NULL DEFAULT NULL;'));
        } catch (\Exception $e) {
            // empty
        }

        try {
            (new DB('ALTER TABLE `egw_attendance` CHANGE `att_hours_mid` `wednesday` INT(11) NULL DEFAULT NULL;'));
        } catch (\Exception $e) {
            // empty
        }

        try {
            (new DB('ALTER TABLE `egw_attendance` CHANGE `att_hours_thu` `thursday` INT(11) NULL DEFAULT NULL;'));
        } catch (\Exception $e) {
            // empty
        }

        try {
            (new DB('ALTER TABLE `egw_attendance` CHANGE `att_hours_fri` `friday` INT(11) NULL DEFAULT NULL;'));
        } catch (\Exception $e) {
            // empty
        }

        try {
            (new DB('ALTER TABLE `egw_attendance` CHANGE `att_hours_sat` `saturday` INT(11) NULL DEFAULT NULL;'));
        } catch (\Exception $e) {
            // empty
        }

        try {
            (new DB('ALTER TABLE `egw_attendance` CHANGE `att_hours_sun` `sunday` INT(11) NULL DEFAULT NULL;'));
        } catch (\Exception $e) {
            // empty
        }

        (new DB('DROP TABLE IF EXISTS `egw_attendancy`;'));

        (new DB('DROP TABLE IF EXISTS `egw_attendancy_timesheet`;'));
    }

    public static function version_4()
    { // Support until 12.11.2018
        $categories = (new DB("SELECT * FROM egw_attendance_meta WHERE meta_name LIKE 'categories'"))->Fetch();
        if ($categories) {
            $categories = json_decode($categories['meta_data'], true);

            foreach ($categories as $key => $cat_id) {
                $category = \AgroEgw\Api\Categories::Read($cat_id);

                $categories_data = [
                    'name'        => $category['name'],
                    'id'          => $category['id'],
                    'description' => $category['description'],
                    'data'        => $category['data'],
                    'key'         => $key,
                ];

                if ($key == 'parent') {
                    if (empty(\Attendance\Core::getMeta(false, 'mainCategory'))) {
                        \Attendance\Core::setMeta('mainCategory', $cat_id, $categories_data);
                    }
                } else {
                    if (empty(\Attendance\Core::getMeta(false, 'subCategory', $cat_id))) {
                        \Attendance\Core::setMeta('subCategory', $cat_id, $categories_data);
                    }
                }
            }
        }
    }

    public static function version_5()
    {
        $column = (new DB("SHOW COLUMNS FROM `egw_attendance` LIKE 'sort_order';"))->Fetch();
        if (empty($column)) {
            (new DB('ALTER TABLE `egw_attendance` ADD COLUMN `sort_order` INTEGER;'));
        }
    }

    public static function version_6()
    {
        try {
            (new DB('ALTER TABLE `egw_attendance` CHANGE `att_id` `id` INT(11) NULL DEFAULT NULL;'));
            (new DB('ALTER TABLE `egw_attendance` CHANGE `att_user` `user` INT(11) NULL DEFAULT NULL;'));
            (new DB('ALTER TABLE `egw_attendance` CHANGE `att_creator` `creator` INT(11) NULL DEFAULT NULL;'));
            (new DB('ALTER TABLE `egw_attendance` CHANGE `att_modified` `modified` INT(11) NULL DEFAULT NULL;'));
            (new DB('ALTER TABLE `egw_attendance` CHANGE `att_vacation` `vacation` INT(11) NULL DEFAULT NULL;'));
            (new DB('ALTER TABLE `egw_attendance` CHANGE `att_start` `start` DATE NULL DEFAULT NULL;'));
            (new DB('ALTER TABLE `egw_attendance` CHANGE `att_end` `end` DATE NULL DEFAULT NULL;'));
            (new DB('ALTER TABLE `egw_attendance` CHANGE `att_hours_week` `total_week_hours` INT(11) NULL DEFAULT NULL;'));
            (new DB('ALTER TABLE `egw_attendance` CHANGE `att_status` `status` VARCHAR(255) NULL DEFAULT NULL;'));
            (new DB('ALTER TABLE `egw_attendance` CHANGE `att_extra_vacation` `extra_vacation` INT(11) NULL DEFAULT NULL;'));
        } catch (\Exception $e) {
            // empty
        }
    }

    public static function version_7()
    {
        try {
            (new DB('ALTER TABLE `egw_attendance` CHANGE `start` `start` DATE NULL DEFAULT NULL;'));
            (new DB('ALTER TABLE `egw_attendance` CHANGE `end` `end` DATE NULL DEFAULT NULL;'));
            (new DB('ALTER TABLE `egw_attendance` CHANGE `status` `status` VARCHAR(255) NULL DEFAULT NULL;'));
        } catch (\Exception $e) {
            // empty
        }
    }

    public static function version_8()
    {
        $templ_data = [];
        require EGW_INCLUDE_ROOT.'/attendance/setup/etemplates.inc.php';
        foreach ($templ_data as $templ) {
            $exists = (new DB("
                SELECT * FROM egw_etemplate
                WHERE et_name LIKE '$templ[name]' AND et_version LIKE '$templ[version]'
            "))->FetchAll();
            if (empty($exists)) {
                (new DB("
                    INSERT INTO egw_etemplate
                    VALUES('$templ[name]', '$templ[template]', '$templ[lang]', '$templ[group]', '$templ[version]', '".addslashes($templ['data'])."', '$templ[size]', '$templ[style]', $templ[modified])
                "));
            }
        }
        Api\Cache::flush(Api\Cache::INSTANCE, 'all');
    }
}
