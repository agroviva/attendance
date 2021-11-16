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
            'id'                   => ['type' => 'auto', 'nullable' => false],
            'user'                 => ['type' => 'int', 'precision' => '255', 'nullable' => false],
            'creator'              => ['type' => 'int', 'precision' => '4'],
            'modified'             => ['type' => 'date'],
            'vacation'             => ['type' => 'int', 'precision' => '4'],
            'start'                => ['type' => 'date'],
            'end'                  => ['type' => 'date'],
            'pl_id'                => ['type' => 'int', 'precision' => '4'],
            'total_week_hours'     => ['type' => 'int', 'precision' => '4'],
            'monday'               => ['type' => 'int', 'precision' => '4'],
            'tuesday'              => ['type' => 'int', 'precision' => '4'],
            'wednesday'            => ['type' => 'int', 'precision' => '4'],
            'thursday'             => ['type' => 'int', 'precision' => '4'],
            'friday'               => ['type' => 'int', 'precision' => '4'],
            'saturday'             => ['type' => 'int', 'precision' => '4'],
            'sunday'               => ['type' => 'int', 'precision' => '4'],
            'status'               => ['type' => 'varchar', 'precision' => '255'],
            'att_nfc'              => ['type' => 'varchar', 'precision' => '255'],
            'password'             => ['type' => 'int', 'precision' => '10'],
            'access_granted'       => ['type' => 'varchar', 'precision' => '255'],
            'access_denied'        => ['type' => 'varchar', 'precision' => '255'],
            'weekdays_rhymes'      => ['type' => 'varchar', 'precision' => '255'],
            'time_interval_data'   => ['type' => 'varchar', 'precision' => '255'],
            'extra_vacation'       => ['type' => 'int', 'precision' => '4'],
            'meta_data'            => ['type' => 'longtext'],
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
            'meta_connection_id' => ['type' => 'int', 'precision' => '11', 'nullable' => false],
            'meta_data'          => ['type' => 'longtext'],
        ],
        'pk' => ['id'],
        'fk' => [],
        'ix' => [],
        'uc' => [],
    ],
];
