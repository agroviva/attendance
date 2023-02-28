 <?php
/**
 * Attendance - SO.
 *
 * @author Enver Morinaj
 *
 * @version $Id: class.attendance_ui.inc.php $
 */
use AgroEgw\DB;

class attendance_so
{
	public $categories;

	public function __construct()
	{
	}

	/**
	 * TO BE REVIEWED.
	 *
	 * @param $user [description]
	 */
	public function Contract($user)
	{
		return (new DB("
            SELECT * FROM  egw_attendance 
            WHERE user = '$user'
        "))->FetchAll();
	}

	// this function adds a contract

	/** TO BE REVIEWED */
	public function add($user, $vacation, $total_week_hours, $monday, $tuesday, $wednesday, $thursday, $friday, $saturday, $sunday, $start, $end, $att_nfc, $password, $access_denied, $access_granted, $weekdays_rhymes, $time_interval_data, $extra_vacation)
	{
		if (!is_null($start)) {
			$start_sql = "'$start'";
		} else {
			$start_sql = 'NULL';
		}

		if (!is_null($end)) {
			$end_sql = "'$end'";
		} else {
			$end_sql = 'NULL';
		}

		new DB("
            INSERT INTO egw_attendance(user, vacation, total_week_hours, monday, tuesday, wednesday, thursday, friday, saturday, sunday, start, end, att_nfc, password, access_denied, access_granted, weekdays_rhymes, time_interval_data, extra_vacation) 
            VALUES ('$user', '$vacation', '$total_week_hours', '$monday', '$tuesday', '$wednesday', '$thursday', '$friday', '$saturday', '$sunday', $start_sql, $end_sql, '$att_nfc', '$password', '$access_denied', '$access_granted', '$weekdays_rhymes', '$time_interval_data', '$extra_vacation')
        ");

		$this->updateMetaData();
	}

	public function updateMetaData(int $ContractID = 0)
	{
		$column = (new DB("SHOW COLUMNS FROM `egw_attendance` LIKE 'meta_data';"))->Fetch();
		if (empty($column)) {
			(new DB('ALTER TABLE `egw_attendance` ADD COLUMN `meta_data` LONGTEXT;'));
		}
		$sql = 'SELECT * FROM egw_attendance';
		if ($ContractID) {
			$sql .= " WHERE id = $ContractID";
		} else {
			$sql .= ' ORDER BY id DESC';
		}

		$contract = (new DB($sql))->Fetch();
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
		(new DB("UPDATE egw_attendance SET meta_data = '$rules' WHERE id = $contract[id]"));
	}

	// this function updates a contract

	/** TO BE REVIEWED */
	public function edit_result($id, $user, $vacation, $total_week_hours, $monday, $tuesday, $wednesday, $thursday, $friday, $saturday, $sunday, $start, $end, $att_nfc, $password, $access_denied, $access_granted, $weekdays_rhymes, $time_interval_data, $extra_vacation)
	{
		if (!is_null($start)) {
			$start_sql = "start='$start'";
		} else {
			$start_sql = 'start=NULL';
		}

		if (!is_null($end)) {
			$end_sql = "end='$end'";
		} else {
			$end_sql = 'end=NULL';
		}

		new DB("
            UPDATE egw_attendance 
            SET user='$user', vacation='$vacation', total_week_hours='$total_week_hours', monday='$monday', tuesday='$tuesday', wednesday='$wednesday', thursday='$thursday', friday='$friday', saturday='$saturday', sunday='$sunday', att_nfc='$att_nfc', password='$password', access_denied='$access_denied', access_granted='$access_granted', weekdays_rhymes='$weekdays_rhymes', time_interval_data='$time_interval_data', extra_vacation='$extra_vacation', $start_sql, $end_sql 
            WHERE id='$id'
        ");

		$this->updateMetaData($id);
	}

	/** TO BE REVIEWED */
	public function password($user)
	{
		return (new DB("
            SELECT * FROM  egw_attendance 
            WHERE (user = '$user') AND (end is NULL OR end >= CURDATE())
        "))->Fetch();
	}

	public function check_contract($user, $start)
	{ // Get the count of Contracts
		$output = (new DB("
            SELECT * FROM  egw_attendance 
            WHERE  (user = '$user') AND (UNIX_TIMESTAMP('end') >= UNIX_TIMESTAMP('CURDATE()') OR end is NULL)
        "))->FetchAll();

		return $output ? count($output) : 0;
	}

	public function check_contract1($user, $start)
	{ // here we proof Contracts
		$output = (new DB("
            SELECT * FROM  egw_attendance 
            WHERE  (user = '$user') AND (UNIX_TIMESTAMP(end) > UNIX_TIMESTAMP(DATE('$start')) OR end is NULL)
        "))->FetchAll();

		return $output ? count($output) : 0;
	}

	public function get_idresult($id)
	{ // this function take the id of a contract

		$i = 0;

		$result = (new DB("
            SELECT id, user, vacation, total_week_hours, monday, tuesday, wednesday, thursday, friday, saturday, sunday, start, end, att_nfc, password, access_denied, access_granted, weekdays_rhymes, time_interval_data, extra_vacation FROM egw_attendance 
            WHERE id = $id
        "))->FetchAll();

		foreach ($result as $row) {
			if ($row['access_granted'] == 'yes') {
				$row['sec_type'] = 'Access granted';
			} elseif ($row['access_denied'] == 'yes') {
				$row['sec_type'] = 'Access denied';
			} else {
				$row['sec_type'] = 'Password';
			}
			$rows[$i] = $row;
			$i++;
		}

		return $rows[0];
	}

	public function should_user($user)
	{ // this function take the user id of a contract
		if (is_array($user)) {
			return (new DB("SELECT * FROM egw_attendance WHERE id = '".$user[0]."'"))->Fetch();
		} else {
			return (new DB("SELECT * FROM egw_attendance WHERE user = '$user'"))->Fetch();
		}
	}

	public function get_time($user, $vacation_cat, $sickness_cat, $school_cat, $holiday_cat, $work_cat)
	{ // it takes the last start time of the user from timesheet
		return (new DB("
            SELECT * FROM egw_timesheet 
            WHERE (from_unixtime(ts_start,'%Y-%m-%d') <= CURDATE()) AND (ts_status !='-1' OR ts_status IS NULL) 
                AND (ts_owner = '$user') 
                AND (cat_id = '$vacation_cat' OR cat_id = '$sickness_cat' OR cat_id = '$school_cat' OR cat_id = '$holiday_cat' OR cat_id = '$work_cat') 
            ORDER BY ts_start DESC
        "))->Fetch();
	}

	public function get_last_time($user)
	{ // it takes the last start time of the user from timesheet
		$categories = \Attendance\Categories::Get(true);
		$categories = array_map(function ($el) {
			return $el['id'];
		}, $categories);
		$IN = implode(',', $categories);

		return (new DB("
            SELECT * FROM egw_timesheet 
            WHERE ts_owner = '$user' AND (ts_status !='-1' OR ts_status IS NULL)
                AND cat_id IN({$IN})
            ORDER BY ts_start DESC
        "))->Fetch();
	}

	public function get_should_hours($user)
	{ // it takes should hours from a available contract
		if (is_array($user)) {
			$row = (new DB("
                SELECT * FROM egw_attendance 
                WHERE id = $user[0]
            "))->Fetch();
		} else {
			$row = (new DB("
                SELECT * FROM egw_attendance 
                WHERE user = $user AND (end is NULL OR end >= CURDATE())
            "))->Fetch();
		}
		$row['monday'] = $row['monday'] / 60 / 60;
		$row['tuesday'] = $row['tuesday'] / 60 / 60;
		$row['wednesday'] = $row['wednesday'] / 60 / 60;
		$row['thursday'] = $row['thursday'] / 60 / 60;
		$row['friday'] = $row['friday'] / 60 / 60;
		$row['saturday'] = $row['saturday'] / 60 / 60;
		$row['sunday'] = $row['sunday'] / 60 / 60;

		return $row;
	}

	public function made_today($ts_owner, $vacation_cat, $sickness_cat, $school_cat, $holiday_cat, $work_cat)
	{ 	// it will show how many hours have made the user today
		return (new DB("
            SELECT CURDATE(), from_unixtime(ts_start,'%Y-%m-%d'), ts_start, SUM(ts_duration) AS ts_dur FROM egw_timesheet 
            WHERE (from_unixtime(ts_start,'%Y-%m-%d') = CURDATE()) 
                AND (ts_status !='-1' OR ts_status IS NULL) 
                AND (ts_owner = '$ts_owner') 
                AND (cat_id = '$vacation_cat' OR cat_id = '$sickness_cat' OR cat_id = '$school_cat' OR cat_id = '$holiday_cat' OR cat_id = '$work_cat')
        "))->Fetch();
	}

	public function made_all($ts_owner, $start_contract, $categories, $end_contract = null)
	{ // it will show how many hours have made the user all time
		if (is_array($ts_owner)) {
			$ts_owner = (new DB("
                SELECT * FROM egw_attendance 
                WHERE id = $ts_owner[0]
            "))->Fetch()['user'];
		}
		$second_where = '( ';
		$i = 1;
		foreach ($categories as $id) {
			if ($i != count($categories)) {
				$second_where .= "cat_id = '".$id."' OR ";
			} else {
				$second_where .= "cat_id = '".$id."'";
			}
			$i++;
		}
		$second_where .= ' )';
		if (!is_null($end_contract)) {
			return (new DB("
                SELECT SUM(ts_duration) AS all_dur FROM egw_timesheet 
                WHERE ((from_unixtime(ts_start,'%Y-%m-%d') >= '$start_contract') 
                    AND (from_unixtime(ts_start,'%Y-%m-%d') <= '$end_contract')) 
                    AND (ts_status !='-1' OR ts_status IS NULL) 
                    AND (ts_owner = '$ts_owner') 
                    AND $second_where
            "))->Fetch();
		}

		return (new DB("
            SELECT SUM(ts_duration) AS all_dur FROM egw_timesheet 
            WHERE (from_unixtime(ts_start,'%Y-%m-%d') >= '$start_contract') 
                AND (ts_status !='-1' OR ts_status IS NULL) 
                AND (ts_owner = '$ts_owner') 
                AND $second_where
        "))->Fetch();
	}

	public function proof_security($user)
	{
		return (new DB("
            SELECT * FROM  egw_attendance 
            WHERE (user = '$user') 
            AND (end is NULL OR end >= CURDATE())
        "))->Fetch();
	}

	/* From here start the work with
	   Holiday, Vacation, School and Sickness.
	*/
	// review
	public function proof_timesheet($date, $ts_owner, $cat_id)
	{ // here we proof if a timesheet is inserted with given categorie or not

		if (is_array($cat_id)) {
			$cat_sql = '(';
			$count = count($cat_id) - 1;
			foreach ($cat_id as $key => $id) {
				$cat_sql .= "cat_id = $id";
				if ($key != $count) {
					$cat_sql .= ' OR ';
				}
			}
			$cat_sql .= ')';
		} else {
			$cat_sql = "(cat_id = $cat_id)";
		}

		$result = (new DB("
            SELECT * FROM egw_timesheet
            WHERE ts_owner = $ts_owner
                AND (ts_status != -1 OR ts_status IS NULL) 
                AND $cat_sql 
                AND (from_unixtime(ts_start,'%Y-%m-%d') = '$date')
        "))->FetchAll();

		return $result ? count($result) : 0;
	}

	public function insert_timesheet($ts_start, $ts_duration, $ts_title, $cat_id, $ts_owner, $ts_modified, $ts_modifier)
	{ // here we insert a timesheet
		(new DB("
            INSERT INTO egw_timesheet(ts_start, ts_duration, ts_quantity, ts_title, cat_id, ts_owner, ts_created, ts_modified, ts_modifier, ts_status, pl_id) 
            VALUES ('$ts_start', '$ts_duration', '$ts_duration', '$ts_title', '$cat_id', '$ts_owner', '$ts_modified', '$ts_modified', '$ts_modifier', NULL, NULL )
        "));
	}

	/**
	 * [Get_Vacation_Time description].
	 *
	 * @param int $user   [User id]
	 * @param int $cat_id [The category id of "Vacation"]
	 * @param [date] $start  [The start date]
	 * @param [date] $end    [The end date]
	 */
	public function Get_Vacation_Time($user, $cat_id, $start, $end)
	{
		$output = (new DB("
            SELECT * FROM egw_timesheet 
            WHERE (ts_owner = '$user') 
                AND (ts_status != '-1' OR ts_status IS NULL) 
                AND (cat_id = '$cat_id') 
                AND (ts_start >= UNIX_TIMESTAMP('$start')) 
                AND (ts_start <= UNIX_TIMESTAMP('$end'))
        "))->FetchAll();

		return $output ? count($output) : 0;
	}

	/*
	*	Nfc reader beginns here
	*/

	public function nfc_check($att_nfc)
	{
		return (new DB("
            SELECT * FROM egw_addressbook a RIGHT OUTER JOIN egw_attendance b ON a.account_id = b.user 
            WHERE (att_nfc = '$att_nfc') 
                AND (end is NULL OR end >= CURDATE())
        "))->Fetch();
	}

	public function toogle_disabled_contracts()
	{
		$data = (new DB("
            SELECT * FROM egw_attendance_meta 
            WHERE meta_name = 'disabled_contacts'
        "))->Fetch();

		if (!empty($data)) {
			if ($data['meta_data'] == '1') {
				return $this->disableExpiredContracts();
			} else {
				return $this->enableExpiredContracts();
			}
		}

		return $this->enableExpiredContracts();
	}

	public function disableExpiredContracts()
	{
		$data = (new DB("
            SELECT * FROM egw_attendance_meta 
            WHERE meta_name = 'disabled_contacts'
        "))->FetchAll();

		if (!empty($data)) {
			new DB("UPDATE egw_attendance_meta SET meta_data='0' WHERE meta_name = 'disabled_contacts'");

			return true;
		}
		new DB("INSERT INTO egw_attendance_meta(meta_name, meta_connection_id, meta_data) VALUES('disabled_contacts','0','0')");

		return true;
	}

	public function enableExpiredContracts()
	{
		$data = (new DB("
            SELECT * FROM egw_attendance_meta 
            WHERE meta_name = 'disabled_contacts'
        "))->FetchAll();

		if (!empty($data)) {
			new DB("UPDATE egw_attendance_meta SET meta_data='1' WHERE meta_name = 'disabled_contacts'");

			return true;
		}
		new DB("INSERT INTO egw_attendance_meta(meta_name, meta_connection_id, meta_data) VALUES('disabled_contacts','0','1')");

		return true;
	}

	public function check_disabled_contracts()
	{
		return true;

		$output = (new DB("
                SELECT * FROM egw_attendance_meta 
                WHERE meta_name = 'disabled_contacts'
        "))->Fetch();

		return $output['meta_data'] == 1 ? true : false;
	}
}
