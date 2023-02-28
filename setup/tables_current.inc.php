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
$phpgw_baseline = [
	'egw_attendance' => [
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
	],
	'egw_attendance_meta' => [
		'fd' => [
			'id'                 => ['type' => 'auto', 'precision' => '11', 'nullable' => false],
			'meta_name'          => ['type' => 'varchar', 'precision' => '255', 'nullable' => false],
			'meta_connection_id' => ['type' => 'int', 'precision' => '11', 'nullable' => true],
			'meta_data'          => ['type' => 'longtext'],
		],
		'pk' => ['id'],
		'fk' => [],
		'ix' => [],
		'uc' => [],
	],
];
