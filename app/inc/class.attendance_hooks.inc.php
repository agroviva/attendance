<?php

/**
 * attendance - hooks:.
 *
 * @link http://www.egroupware.org
 *
 * @author Enver Morinaj
 * @copyright (c) Agroviva GmbH
 */

#include_once __DIR__.'/../api/app.php';
#use Attendance\Core;

use EGroupware\Api\Egw;
use EGroupware\Api\Link;


class attendance_hooks
{
    // To register all hooks for the app. on the proper location
    public static function all_hooks($args)
    {
        $appname = 'attendance';
        $title = lang($GLOBALS['egw_info']['apps'][$appname]['title']);
        $location = is_array($args) ? $args['location'] : $args;

        if ($location == 'sidebox_menu') {
            $file = [
                'Work Tracker' => Egw::link('/egroupware/attendance/graph/tracker/'),
            ];
            display_sidebox($appname, $title.'', $file);

            // only users with permission for admin app can see sidebox
            if ($GLOBALS['egw_info']['user']['apps']['admin']) {

                $isManager = true; # Core::isManager($GLOBALS['egw_info']['user']['account_id']);
                if ($isManager) {
                    $file = [
                        // 'Work Contracts'               => Egw::link('/index.php', 'menuaction=attendance.attendance_ui.manage&appname=attendance&use_private=1'),
                        'Arbeitsverträge' => Egw::link('/egroupware/attendance/graph/manage/'),
                        //'Work Contracts 1' => Egw::link('/index.php','menuaction=attendance.attendance_ui.management&appname=attendance&use_private=1'),
                        //'Work scheduling' => Egw::link('/index.php','menuaction=attendance.attendance_ui.work_schedule&appname=attendance&use_private=1'),
                        'Attendance Time' => Egw::link('/egroupware/attendance/graph/timesheet/'),
                        // 'Log'             => Egw::link('/index.php', 'menuaction=attendance.attendance_ui.logs&appname=attendance&use_private=1'),
                        'Holidays'        => Egw::link('/egroupware/attendance/graph/holidays/'),
                        'Synchronisation' => Egw::link('/index.php', 'menuaction=attendance.attendance_ui.sync&appname=attendance&use_private=1'),

                    ];

                    $menu_title = 'Personal Büro';
                    display_sidebox($appname, $menu_title, $file);
                }

                $file = [
                    'Settings' => Egw::link('/egroupware/attendance/graph/settings/'),
                ];

                display_sidebox($appname, 'Administrator', $file);
            }
        }
    }
}
