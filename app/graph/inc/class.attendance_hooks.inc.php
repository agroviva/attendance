<?php

/**
 * attendance - hooks:.
 *
 * @link http://www.egroupware.org
 *
 * @author Enver Morinaj
 * @copyright (c) Agroviva GmbH
 */

# Autoload Classes
require_once __DIR__.'/../classes/autoload.php';
# Autoload Third Party Libraries
require_once __DIR__.'/../../../agroviva/vendor/autoload.php';


use Attendance\Core;
use EGroupware\Api\Egw;
use EGroupware\Api\Link;


class attendance_hooks
{
    // To register all hooks for the app. on the proper location
    public static function all_hooks($args)
    {
        $appname = 'attendance';
        $menu_title = lang($GLOBALS['egw_info']['apps'][$appname]['title']);
        $location = is_array($args) ? $args['location'] : $args;

        if ($location == 'sidebox_menu') {
            $file = [
                'Work Tracker' => Egw::link('/egroupware/attendance/graph/tracker/'),
            ];
            display_sidebox($appname, $menu_title, $file);

            $isManager = Core::isManager($GLOBALS['egw_info']['user']['account_id']);
            if ($isManager) {
                $file = [
                    'Arbeitsverträge' => Egw::link('/egroupware/attendance/graph/manage/'),
                    'Erstellen' => Egw::link('/egroupware/attendance/graph/create/'),
                    'Attendance Time' => Egw::link('/egroupware/attendance/graph/timesheet/'),
                    'Holidays'        => Egw::link('/egroupware/attendance/graph/holidays/'),
                    'Synchronisation' => Egw::link('/index.php', 'menuaction=attendance.attendance_ui.sync&appname=attendance&use_private=1'),

                ];

                $menu_title = 'Personal Büro';
                display_sidebox($appname, $menu_title, $file);
            }

            // only users with permission for admin app can see sidebox
            if ($GLOBALS['egw_info']['user']['apps']['admin']) {
                $file = [
                    'Settings' => Egw::link('/egroupware/attendance/graph/settings/'),
                ];

                display_sidebox($appname, 'Administrator', $file);
            }
        }
    }
}
