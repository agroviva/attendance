<?php
/**
 * eGroupWare - Setup
 * http://www.egroupware.org
 * Created by eTemplates DB-Tools written by ralfbecker@outdoor-training.de.
 *
 * @license http://opensource.org/licenses/gpl-license.php GPL - GNU General Public License
 *
 * @version $Id$
 */

function attendance_upgrade16_1_001()
{
	$GLOBALS['egw_setup']->oProc->CreateTable('egw_attendance_meta', [
		'fd' => [
			'id'                 => ['type' => 'int', 'precision' => '11'],
			'meta_name'          => ['type' => 'varchar', 'precision' => '255', 'nullable' => false],
			'meta_connection_id' => ['type' => 'int', 'precision' => '11', 'nullable' => false],
			'meta_data'          => ['type' => 'longtext'],
		],
		'pk' => ['id'],
		'fk' => [],
		'ix' => [],
		'uc' => ['id'],
	]);

	return $GLOBALS['setup_info']['attendance']['currentver'] = '16.1.002';
}

function attendance_upgrade16_1_002()
{
	$GLOBALS['egw_setup']->oProc->DropColumn('egw_attendance_meta', [
		'fd' => [
			'meta_name'          => ['type' => 'varchar', 'precision' => '255', 'nullable' => false],
			'meta_connection_id' => ['type' => 'int', 'precision' => '11', 'nullable' => false],
			'meta_data'          => ['type' => 'longtext'],
		],
		'pk' => [],
		'fk' => [],
		'ix' => [],
		'uc' => [],
	], 'id');
	$GLOBALS['egw_setup']->oProc->DropColumn('egw_attendance_meta', [
		'fd' => [
			'meta_connection_id' => ['type' => 'int', 'precision' => '11', 'nullable' => false],
			'meta_data'          => ['type' => 'longtext'],
		],
		'pk' => [],
		'fk' => [],
		'ix' => [],
		'uc' => [],
	], 'meta_name');
	$GLOBALS['egw_setup']->oProc->DropColumn('egw_attendance_meta', [
		'fd' => [
			'meta_data' => ['type' => 'longtext'],
		],
		'pk' => [],
		'fk' => [],
		'ix' => [],
		'uc' => [],
	], 'meta_connection_id');
	$GLOBALS['egw_setup']->oProc->DropColumn('egw_attendance_meta', [
		'fd' => [

		],
		'pk' => [],
		'fk' => [],
		'ix' => [],
		'uc' => [],
	], 'meta_data');
	$GLOBALS['egw_setup']->oProc->RefreshTable('egw_attendance_meta', [
		'fd' => [

		],
		'pk' => [],
		'fk' => [],
		'ix' => [],
		'uc' => [],
	]);

	return $GLOBALS['setup_info']['attendance']['currentver'] = '16.1.003';
}

function attendance_upgrade16_1_003()
{
	$GLOBALS['egw_setup']->oProc->DropColumn('egw_attendance_timesheet', [
		'fd' => [
			'start'    => ['type' => 'timestamp', 'precision' => '8'],
			'end'      => ['type' => 'timestamp', 'precision' => '8'],
			'titel'    => ['type' => 'varchar', 'precision' => '255'],
			'category' => ['type' => 'int', 'meta' => 'category', 'precision' => '4'],
			'duration' => ['type' => 'int', 'precision' => '8'],
			'user'     => ['type' => 'int', 'precision' => '4', 'nullable' => false],
			'status'   => ['type' => 'int', 'precision' => '11'],
		],
		'pk' => [],
		'fk' => [],
		'ix' => [],
		'uc' => [],
	], 'id');
	$GLOBALS['egw_setup']->oProc->DropColumn('egw_attendance_timesheet', [
		'fd' => [
			'end'      => ['type' => 'timestamp', 'precision' => '8'],
			'titel'    => ['type' => 'varchar', 'precision' => '255'],
			'category' => ['type' => 'int', 'meta' => 'category', 'precision' => '4'],
			'duration' => ['type' => 'int', 'precision' => '8'],
			'user'     => ['type' => 'int', 'precision' => '4', 'nullable' => false],
			'status'   => ['type' => 'int', 'precision' => '11'],
		],
		'pk' => [],
		'fk' => [],
		'ix' => [],
		'uc' => [],
	], 'start');
	$GLOBALS['egw_setup']->oProc->DropColumn('egw_attendance_timesheet', [
		'fd' => [
			'titel'    => ['type' => 'varchar', 'precision' => '255'],
			'category' => ['type' => 'int', 'meta' => 'category', 'precision' => '4'],
			'duration' => ['type' => 'int', 'precision' => '8'],
			'user'     => ['type' => 'int', 'precision' => '4', 'nullable' => false],
			'status'   => ['type' => 'int', 'precision' => '11'],
		],
		'pk' => [],
		'fk' => [],
		'ix' => [],
		'uc' => [],
	], 'end');
	$GLOBALS['egw_setup']->oProc->DropColumn('egw_attendance_timesheet', [
		'fd' => [
			'category' => ['type' => 'int', 'meta' => 'category', 'precision' => '4'],
			'duration' => ['type' => 'int', 'precision' => '8'],
			'user'     => ['type' => 'int', 'precision' => '4', 'nullable' => false],
			'status'   => ['type' => 'int', 'precision' => '11'],
		],
		'pk' => [],
		'fk' => [],
		'ix' => [],
		'uc' => [],
	], 'titel');
	$GLOBALS['egw_setup']->oProc->DropColumn('egw_attendance_timesheet', [
		'fd' => [
			'duration' => ['type' => 'int', 'precision' => '8'],
			'user'     => ['type' => 'int', 'precision' => '4', 'nullable' => false],
			'status'   => ['type' => 'int', 'precision' => '11'],
		],
		'pk' => [],
		'fk' => [],
		'ix' => [],
		'uc' => [],
	], 'category');
	$GLOBALS['egw_setup']->oProc->DropColumn('egw_attendance_timesheet', [
		'fd' => [
			'user'   => ['type' => 'int', 'precision' => '4', 'nullable' => false],
			'status' => ['type' => 'int', 'precision' => '11'],
		],
		'pk' => [],
		'fk' => [],
		'ix' => [],
		'uc' => [],
	], 'duration');
	$GLOBALS['egw_setup']->oProc->DropColumn('egw_attendance_timesheet', [
		'fd' => [
			'status' => ['type' => 'int', 'precision' => '11'],
		],
		'pk' => [],
		'fk' => [],
		'ix' => [],
		'uc' => [],
	], 'user');
	$GLOBALS['egw_setup']->oProc->DropColumn('egw_attendance_timesheet', [
		'fd' => [

		],
		'pk' => [],
		'fk' => [],
		'ix' => [],
		'uc' => [],
	], 'status');
	$GLOBALS['egw_setup']->oProc->RefreshTable('egw_attendance_timesheet', [
		'fd' => [

		],
		'pk' => [],
		'fk' => [],
		'ix' => [],
		'uc' => [],
	]);

	return $GLOBALS['setup_info']['attendance']['currentver'] = '16.1.004';
}

function attendance_upgrade16_1_004()
{
	/* done by RefreshTable() anyway
	$GLOBALS['egw_setup']->oProc->AddColumn('egw_attendance_timesheet','id',array(
		'type' => 'auto',
		'nullable' => False
	));*/
	/* done by RefreshTable() anyway
	$GLOBALS['egw_setup']->oProc->AddColumn('egw_attendance_timesheet','start',array(
		'type' => 'timestamp',
		'precision' => '8'
	));*/
	/* done by RefreshTable() anyway
	$GLOBALS['egw_setup']->oProc->AddColumn('egw_attendance_timesheet','end',array(
		'type' => 'timestamp',
		'precision' => '8'
	));*/
	/* done by RefreshTable() anyway
	$GLOBALS['egw_setup']->oProc->AddColumn('egw_attendance_timesheet','titel',array(
		'type' => 'varchar',
		'precision' => '255'
	));*/
	/* done by RefreshTable() anyway
	$GLOBALS['egw_setup']->oProc->AddColumn('egw_attendance_timesheet','category',array(
		'type' => 'int',
		'meta' => 'category',
		'precision' => '4'
	));*/
	/* done by RefreshTable() anyway
	$GLOBALS['egw_setup']->oProc->AddColumn('egw_attendance_timesheet','duration',array(
		'type' => 'int',
		'precision' => '8'
	));*/
	/* done by RefreshTable() anyway
	$GLOBALS['egw_setup']->oProc->AddColumn('egw_attendance_timesheet','user',array(
		'type' => 'int',
		'precision' => '4',
		'nullable' => False
	));*/
	/* done by RefreshTable() anyway
	$GLOBALS['egw_setup']->oProc->AddColumn('egw_attendance_timesheet','status',array(
		'type' => 'int',
		'precision' => '4'
	));*/
	$GLOBALS['egw_setup']->oProc->RefreshTable('egw_attendance_timesheet', [
		'fd' => [
			'id'       => ['type' => 'auto', 'nullable' => false],
			'start'    => ['type' => 'timestamp', 'precision' => '8'],
			'end'      => ['type' => 'timestamp', 'precision' => '8'],
			'titel'    => ['type' => 'varchar', 'precision' => '255'],
			'category' => ['type' => 'int', 'meta' => 'category', 'precision' => '4'],
			'duration' => ['type' => 'int', 'precision' => '8'],
			'user'     => ['type' => 'int', 'precision' => '4', 'nullable' => false],
			'status'   => ['type' => 'int', 'precision' => '11'],
		],
		'pk' => ['id'],
		'fk' => [],
		'ix' => [],
		'uc' => [],
	]);

	return $GLOBALS['setup_info']['attendance']['currentver'] = '16.1.005';
}

function attendance_upgrade16_1_005()
{
	/* done by RefreshTable() anyway
	$GLOBALS['egw_setup']->oProc->AddColumn('egw_attendance_meta','id',array(
		'type' => 'int',
		'precision' => '11',
		'nullable' => False
	));*/
	/* done by RefreshTable() anyway
	$GLOBALS['egw_setup']->oProc->AddColumn('egw_attendance_meta','meta_connection_id',array(
		'type' => 'int',
		'precision' => '11',
		'nullable' => False
	));*/
	/* done by RefreshTable() anyway
	$GLOBALS['egw_setup']->oProc->AddColumn('egw_attendance_meta','meta_name',array(
		'type' => 'varchar',
		'precision' => '255',
		'nullable' => False
	));*/
	/* done by RefreshTable() anyway
	$GLOBALS['egw_setup']->oProc->AddColumn('egw_attendance_meta','meta_data',array(
		'type' => 'longtext'
	));*/
	$GLOBALS['egw_setup']->oProc->RefreshTable('egw_attendance_meta', [
		'fd' => [
			'id'                 => ['type' => 'int', 'precision' => '11', 'nullable' => false],
			'meta_connection_id' => ['type' => 'int', 'precision' => '11', 'nullable' => false],
			'meta_name'          => ['type' => 'varchar', 'precision' => '255', 'nullable' => false],
			'meta_data'          => ['type' => 'longtext'],
		],
		'pk' => ['id'],
		'fk' => [],
		'ix' => [],
		'uc' => [],
	]);

	return $GLOBALS['setup_info']['attendance']['currentver'] = '16.1.006';
}

function attendance_upgrade16_1_006()
{
	$GLOBALS['egw_setup']->oProc->DropColumn('egw_attendance_meta', [
		'fd' => [
			'meta_connection_id' => ['type' => 'int', 'precision' => '11', 'nullable' => false],
			'meta_name'          => ['type' => 'varchar', 'precision' => '255', 'nullable' => false],
			'meta_data'          => ['type' => 'longtext'],
		],
		'pk' => [],
		'fk' => [],
		'ix' => [],
		'uc' => [],
	], 'id');
	$GLOBALS['egw_setup']->oProc->DropColumn('egw_attendance_meta', [
		'fd' => [
			'meta_name' => ['type' => 'varchar', 'precision' => '255', 'nullable' => false],
			'meta_data' => ['type' => 'longtext'],
		],
		'pk' => [],
		'fk' => [],
		'ix' => [],
		'uc' => [],
	], 'meta_connection_id');
	$GLOBALS['egw_setup']->oProc->DropColumn('egw_attendance_meta', [
		'fd' => [
			'meta_data' => ['type' => 'longtext'],
		],
		'pk' => [],
		'fk' => [],
		'ix' => [],
		'uc' => [],
	], 'meta_name');
	$GLOBALS['egw_setup']->oProc->DropColumn('egw_attendance_meta', [
		'fd' => [

		],
		'pk' => [],
		'fk' => [],
		'ix' => [],
		'uc' => [],
	], 'meta_data');
	$GLOBALS['egw_setup']->oProc->RefreshTable('egw_attendance_meta', [
		'fd' => [

		],
		'pk' => [],
		'fk' => [],
		'ix' => [],
		'uc' => [],
	]);

	return $GLOBALS['setup_info']['attendance']['currentver'] = '16.1.007';
}

function attendance_upgrade16_1_007()
{
	$GLOBALS['egw_setup']->oProc->RefreshTable('egw_attendance_meta', [
		'fd' => [
			'id'                 => ['type' => 'int', 'precision' => '11', 'nullable' => false],
			'meta_name'          => ['type' => 'varchar', 'precision' => '255', 'nullable' => false],
			'meta_connection_id' => ['type' => 'int', 'precision' => '11', 'nullable' => false],
			'meta_data'          => ['type' => 'longtext'],
		],
		'pk' => ['id'],
		'fk' => [],
		'ix' => [],
		'uc' => [],
	]);

	return $GLOBALS['setup_info']['attendance']['currentver'] = '16.1.008';
}

function attendance_upgrade16_1_008()
{
	$GLOBALS['egw_setup']->oProc->RefreshTable('egw_attendance_meta', [
		'fd' => [
			'id'                 => ['type' => 'auto', 'precision' => '11', 'nullable' => false],
			'meta_name'          => ['type' => 'varchar', 'precision' => '255', 'nullable' => false],
			'meta_connection_id' => ['type' => 'int', 'precision' => '11', 'nullable' => false],
			'meta_data'          => ['type' => 'longtext'],
		],
		'pk' => ['id'],
		'fk' => [],
		'ix' => [],
		'uc' => [],
	]);

	return $GLOBALS['setup_info']['attendance']['currentver'] = '16.1.009';
}

function attendance_upgrade16_1_009()
{
	$GLOBALS['egw_setup']->oProc->AddColumn('egw_attendance', 'extra_vacation', [
		'type'      => 'int',
		'precision' => '4',
	]);

	return $GLOBALS['setup_info']['attendance']['currentver'] = '16.1.010';
}

function attendance_upgrade16_1_010()
{
	$GLOBALS['egw_setup']->oProc->AddColumn('egw_attendance', 'defined_time_count', [
		'type'      => 'int',
		'precision' => '4',
	]);

	return $GLOBALS['setup_info']['attendance']['currentver'] = '16.1.011';
}

function attendance_upgrade16_1_011()
{
	$GLOBALS['egw_setup']->oProc->DropColumn('egw_attendance', [
		'fd' => [
			'id'                   => ['type' => 'auto', 'nullable' => false],
			'user'                 => ['type' => 'int', 'precision' => '255', 'nullable' => false],
			'creator'              => ['type' => 'int', 'precision' => '11'],
			'modified'             => ['type' => 'date'],
			'vacation'             => ['type' => 'int', 'precision' => '11'],
			'start'                => ['type' => 'date'],
			'end'                  => ['type' => 'date'],
			'pl_id'                => ['type' => 'int', 'precision' => '11'],
			'total_week_hours'     => ['type' => 'int', 'precision' => '11'],
			'monday'               => ['type' => 'int', 'precision' => '11'],
			'tuesday'              => ['type' => 'int', 'precision' => '11'],
			'wednesday'            => ['type' => 'int', 'precision' => '11'],
			'thursday'             => ['type' => 'int', 'precision' => '11'],
			'friday'               => ['type' => 'int', 'precision' => '11'],
			'saturday'             => ['type' => 'int', 'precision' => '11'],
			'sunday'               => ['type' => 'int', 'precision' => '11'],
			'status'               => ['type' => 'varchar', 'precision' => '255'],
			'att_nfc'              => ['type' => 'varchar', 'precision' => '255'],
			'password'             => ['type' => 'int', 'precision' => '10'],
			'access_granted'       => ['type' => 'varchar', 'precision' => '255'],
			'access_denied'        => ['type' => 'varchar', 'precision' => '255'],
			'weekdays_rhymes'      => ['type' => 'varchar', 'precision' => '255'],
			'time_interval_data'   => ['type' => 'varchar', 'precision' => '255'],
			'extra_vacation'       => ['type' => 'int', 'precision' => '11'],
		],
		'pk' => ['id'],
		'fk' => [],
		'ix' => [],
		'uc' => [],
	], 'defined_time_count');

	return $GLOBALS['setup_info']['attendance']['currentver'] = '16.1.012';
}

function attendance_upgrade16_1_012()
{
	$GLOBALS['egw_setup']->oProc->DropColumn('egw_attendance', [
		'fd' => [
			'id'                 => ['type' => 'auto', 'nullable' => false],
			'user'               => ['type' => 'int', 'precision' => '255', 'nullable' => false],
			'creator'            => ['type' => 'int', 'precision' => '11'],
			'modified'           => ['type' => 'int', 'precision' => '11'],
			'vacation'           => ['type' => 'int', 'precision' => '11'],
			'start'              => ['type' => 'date'],
			'end'                => ['type' => 'date'],
			'total_week_hours'   => ['type' => 'int', 'precision' => '11'],
			'monday'             => ['type' => 'int', 'precision' => '11'],
			'tuesday'            => ['type' => 'int', 'precision' => '11'],
			'wednesday'          => ['type' => 'int', 'precision' => '11'],
			'thursday'           => ['type' => 'int', 'precision' => '11'],
			'friday'             => ['type' => 'int', 'precision' => '11'],
			'saturday'           => ['type' => 'int', 'precision' => '11'],
			'sunday'             => ['type' => 'int', 'precision' => '11'],
			'status'             => ['type' => 'varchar', 'precision' => '255'],
			'att_nfc'            => ['type' => 'varchar', 'precision' => '255'],
			'password'           => ['type' => 'int', 'precision' => '11'],
			'access_granted'     => ['type' => 'varchar', 'precision' => '255'],
			'access_denied'      => ['type' => 'varchar', 'precision' => '255'],
			'weekdays_rhymes'    => ['type' => 'varchar', 'precision' => '255'],
			'time_interval_data' => ['type' => 'varchar', 'precision' => '255'],
			'extra_vacation'     => ['type' => 'int', 'precision' => '11'],
			'meta_data'          => ['type' => 'longtext'],
			'sort_order'         => ['type' => 'int', 'precision' => '11'],
		],
		'pk' => ['id'],
		'fk' => [],
		'ix' => [],
		'uc' => [],
	], 'pl_id');

	return $GLOBALS['setup_info']['attendance']['currentver'] = '16.1.013';
}

function attendance_upgrade21_1_001()
{
	$GLOBALS['egw_setup']->oProc->CreateTable('egw_attendance_locations', [
		'fd' => [
			'id'            	=> ['type' => 'auto', 'precision' => '11', 'nullable' => false],
			'location'         	=> ['type' => 'varchar', 'precision' => '255', 'nullable' => false],
			'users'          	=> ['type' => 'longtext'],
		],
		'pk' => ['id'],
		'fk' => [],
		'ix' => [],
		'uc' => [],
	]);

	return $GLOBALS['setup_info']['attendance']['currentver'] = '21.1.002';
}

function attendance_upgrade21_1_002()
{
	return $GLOBALS['setup_info']['attendance']['currentver'] = '21.1.003';
}