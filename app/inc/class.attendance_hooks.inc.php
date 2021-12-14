<?php

/**
 * attendance - hooks:.
 *
 * @link http://www.egroupware.org
 *
 * @author Enver Morinaj
 * @copyright (c) Agroviva GmbH
 */

# include_once __DIR__.'/../api/app.php';
# use Attendance\Core;

use EGroupware\Api;
use EGroupware\Api\Egw;


class attendance_hooks
{

    /**
	 * Hook for admin menu
	 *
	 * @param array|string $hook_data
	 */
    public static function admin($hook_data){
		$title = $appname = 'attendance';

		unset($hook_data);	// not used, but required by function signature

        $file = [
            'Settings' => Egw::link('/egroupware/attendance/graph/settings/'),
        ];

        # display_sidebox($appname, 'Administrator', $file);
		display_section($appname,$title,$file);
    }

    /**
	 * Hook for sidebox menu
	 *
	 * @param array|string $hook_data
	 */
	public static function sidebox_menu($hook_data)
	{
		unset($hook_data);	// not used, but required by function signature

		$appname = 'attendance';
		$menu_title = "Zeiterfassung";

        $file = [
            'Work Tracker' => Egw::link('/egroupware/attendance/graph/tracker/'),
        ];
        display_sidebox($appname, $menu_title, $file);

        # only users with permission for admin app can see sidebox
		if ($GLOBALS['egw_info']['user']['apps']['admin'])
		{

            $file = [
                'Arbeitsverträge' => Egw::link('/egroupware/attendance/graph/manage/'),
                'Attendance Time' => Egw::link('/egroupware/attendance/graph/timesheet/'),
                'Holidays'        => Egw::link('/egroupware/attendance/graph/holidays/'),
                'Synchronisation' => Egw::link('/index.php', 'menuaction=attendance.attendance_ui.sync&appname=attendance&use_private=1')
            ];

            $menu_title = 'Personal Büro';
            display_sidebox($appname, $menu_title, $file);

		}
	}
}