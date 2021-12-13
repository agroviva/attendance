<?php

namespace Attendance;

use AgroEgw\DB;
use attendance_so;
use EGroupware\Api;
use EGroupware\Api\Translation as Translation;

class Install extends attendance_so
{
    public function __construct()
    {
    }

    /*
     * Calling the parent constructor so that the needed
     * data for attendance_so will be initialised
     * @return : returns all the methods and properties of this class
    */
    public function set()
    {
        parent::__construct();

        return $this;
    }

    /*
     * You should make a special Translation class to manage
     * all translations, which we need.
     * This is the best way to add Translation using the eGroupware API
     * @return : returns all the methods and properties of this class
    */
    public function translate()
    {
        # Translation::write('en', 'custom', strtolower(trim('attendance')), 'Zeiterfassung');
        echo "Adding translation for \"attendance\" was successfully</br>\n";

        return $this;
    }

    /*
     * WARNING: this function should be reviews because is to dangerous
     * Changin the key position of status labels is not a good idea, since
     * there will be a key or id change of those status labels and this will create a mess.
     * Search for an possible eGroupware API, since it is more secure to change configuration.
     * @return : returns all the methods and properties of this class
    */
    public function status_labels()
    {
        $data = (new DB("
            SELECT * FROM egw_config 
            WHERE config_name = 'status_labels'
        "))->Fetch();

        $status_labels = ($data ? json_decode($data['config_value'], true) : false);
        foreach ($status_labels as $num => $status) {
            if ($status['name'] == 'attendance') {
                $key = $num;
            }
        }
        if (!$key) {
            if ($status_labels) {
                foreach ($status_labels as $key => $status) {
                    if ($status['name'] == 'attendancy') {
                        unset($status_labels[$key]);
                        $last_key = $key;
                        break;
                    } else {
                        $last_key = $key + 1;
                    }
                }

                $status_labels[$last_key] = [
                                                'name'   => 'attendance',
                                                'parent' => '',
                                                'admin'  => true,
                                            ];

                $config_value = json_encode($status_labels);
                (new DB("
                    UPDATE egw_config SET config_value = '{$config_value}' 
                    WHERE config_name = 'status_labels'
                "));
                echo "Attendance status is applied</br>\n";
            } else {
                $data = [
                    1 => [
                            'name'   => 'attendance',
                            'parent' => '',
                            'admin'  => true,
                    ],
                ];
                $status_labels = json_encode($data);
                (new DB("
                    INSERT INTO egw_config (config_app, config_name, config_value) 
                    VALUES ('timesheet', 'status_labels', '{$status_labels}')
                "));
            }
        } else {
            echo "Attendance status was already found</br>\n";
        }

        $attendance_key = Tracker::TimesheetStatusID('attendance');
        $categories = Categories::Get();
        $count = count($categories);
        $i = 1;
        $cats = '(';
        foreach ($categories as $key => $category) {
            if ($key != 'parent') {
                if ($count != $i) {
                    $cats .= 'cat_id = '.$category['id'].' OR ';
                } else {
                    $cats .= 'cat_id = '.$category['id'].')';
                }
            }
            $i++;
        }
        if ($cats && $attendance_key) {
            $sql = "
                UPDATE egw_timesheet SET ts_status = $attendance_key 
                WHERE ts_status = 99 
                    OR ($cats AND (ts_status != -1 AND ts_status != $attendance_key))
            ";
            echo $sql;
            (new DB($sql));
            echo ": Updated</br>\n";
        }

        return $this;
    }
}
