<?php

namespace Attendance;

use AgroEgw\DB;
use Carbon\Carbon;

class Core
{
	protected static $temp = [];

	public static function Self($user, $default = 'a.account_id, a.contact_id, a.n_fileas, a.n_given, a.n_family, b.user, b.time_interval_data, b.id')
	{ // take a special user if exist

		return (new DB("
            SELECT $default FROM egw_addressbook a RIGHT OUTER JOIN egw_attendance b ON a.account_id = b.user 
            WHERE b.user = '$user' AND (b.end is NULL OR b.end >= CURDATE())
        "))->Fetch();
	}

	public static function Console($what, $dir)
	{
		$file = file_get_contents($dir);
		if ($what == 'js') {
			return "<script type='text/javascript'>".$file.'</script>';
		} elseif ($what == 'css') {
			return '<style>'.$file.'</style>';
		}
	}

	public static function getMeta($meta_id = false, $meta_name = false, $conn_id = false)
	{
		$where = 'WHERE 1=1 ';
		$where .= ($meta_id ? "AND id = {$meta_id} " : '');
		$where .= ($meta_name ? "AND meta_name LIKE '{$meta_name}' " : '');
		$where .= ($conn_id ? "AND meta_connection_id = {$conn_id} " : '');

		return (new DB("SELECT * FROM egw_attendance_meta {$where} ORDER BY id ASC"))->FetchAll();
	}

	public static function setMeta($meta_name, $conn_id, $meta_data = '')
	{
		if (is_array($meta_data)) {
			$meta_data = json_encode($meta_data);
		}
		(new DB("INSERT INTO egw_attendance_meta(meta_name, meta_connection_id, meta_data) VALUES('{$meta_name}','{$conn_id}','{$meta_data}')"));
	}

	public static function updateMeta($meta_id, $meta_data = '')
	{
		if (is_array($meta_data)) {
			$meta_data = json_encode($meta_data);
		}
		(new DB("UPDATE egw_attendance_meta SET meta_data='{$meta_data}' WHERE id = {$meta_id}"));
	}

	public static function isManager($uid)
	{
		return Manager::Exists($uid) ?: call_user_func(function () {
			foreach ($GLOBALS['egw_info']['user']['memberships'] as $id => $name) {
				if (Manager::Exists($id)) {
					return true;
				}
			}

			return false;
		});
	}

	public static function unixToDate($timestamp, $format = 'd.m.Y')
	{
		return Carbon::createFromTimestamp($timestamp)->format($format);
	}

	public static function dateToUnix($string, $endOfDay = false)
	{
		return $endOfDay ? Carbon::parse($string)->endOfDay()->timestamp : Carbon::parse($string)->timestamp;
	}
}
