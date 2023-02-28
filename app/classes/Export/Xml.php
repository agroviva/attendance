<?php

namespace Attendance\Export;

use AgroEgw\DB;
use Attendance\Categories;
use Carbon\Carbon;

class Xml
{
	public function __construct($data)
	{
		global $so, $bo;
		ob_get_clean();
		$header = file_get_contents(__DIR__.'/Xml/header.xml');

		$owner = $_REQUEST['owner'];
		$categories = $_REQUEST['categories'] ?: Categories::GetCategories();
		$categories = implode(',', $categories);

		$date = $_REQUEST['dates'];
		$date = explode('-', $date);
		$start = Carbon::parse($date[0])->startOfDay()->timestamp;
		$end = Carbon::parse($date[1])->endOfDay()->timestamp;

		$sql = "SELECT * FROM egw_timesheet 
                WHERE (ts_status !='-1' OR ts_status IS NULL) 
                AND cat_id IN($categories)
                AND ts_start >= $start AND ts_start <= $end";
		$sql .= " AND ts_owner = $owner";
		$sql .= ' ORDER BY ts_start DESC';

		$data = DB::GetAll($sql);
		$data = array_values(array_reverse($data));

		$user_sql = "SELECT * FROM egw_addressbook WHERE account_id = '$owner'";
		$user = $GLOBALS['egw']
			->db
			->query($user_sql, __LINE__, __FILE__, 0, -1, false, 2)->GetAll()[0];

		$fullname = $user['n_given'].' '.$user['n_family'];
		$familyname = $user['n_family'].', '.$user['n_given'];

		$contentTemplate = file_get_contents(__DIR__.'/Xml/content.xml');

		$personalnumber = ''; // $user['account_id']
		$date = date('Y-m', $start);
		$header = str_replace('{{name}}', $familyname, $header);
		$header = str_replace('{{personalnumber}}', $personalnumber, $header);
		$header = str_replace('{{date}}', $date, $header);

		$dataInOut = [];
		$totalDuration = 0;
		foreach ($data as $key => $timesheet) {
			$start = $timesheet['ts_start'];
			$duration = $timesheet['ts_duration'];
			if ($duration <= 0) {
				continue;
			}
			$date = date('Y-m-d', $start);

			if (!$last_date || $date == $last_date) {
				$dataInOut[] = [
					'start' => $start,
					'end'   => $start + ($duration * 60),
				];
				$totalDuration += $duration;
			} else {
				$content .= self::replacePlaceholders([
					'date'      => date('Y-m-d', $dataInOut[0]['start']),
					'day'       => self::weekdaysInGerman(date('l', $dataInOut[0]['start'])),
					'from1'     => self::convertUnixToTime($dataInOut[0]['start']),
					'to1'       => self::convertUnixToTime($dataInOut[0]['end']),
					'from2'     => self::convertUnixToTime($dataInOut[1]['start']),
					'to2'       => self::convertUnixToTime($dataInOut[1]['end']),
					'from3'     => self::convertUnixToTime($dataInOut[2]['start']),
					'to3'       => self::convertUnixToTime($dataInOut[2]['end']),
					'from4'     => self::convertUnixToTime($dataInOut[3]['start']),
					'to4'       => self::convertUnixToTime($dataInOut[3]['end']),
					'duration'  => '1899-12-31T00:00:00.000',
				], $contentTemplate);

				$dataInOut = [];
				$totalDuration = 0;
				$dataInOut[] = [
					'start' => $start,
					'end'   => $start + ($duration * 60),
				];
			}

			$last_start = $start;
			$last_date = $date;
		}
		$content .= self::replacePlaceholders([
			'date'      => date('Y-m-d', $dataInOut[0]['start']),
			'day'       => self::weekdaysInGerman(date('l', $dataInOut[0]['start'])),
			'from1'     => self::convertUnixToTime($dataInOut[0]['start']),
			'to1'       => self::convertUnixToTime($dataInOut[0]['end']),
			'from2'     => self::convertUnixToTime($dataInOut[1]['start']),
			'to2'       => self::convertUnixToTime($dataInOut[1]['end']),
			'from3'     => self::convertUnixToTime($dataInOut[2]['start']),
			'to3'       => self::convertUnixToTime($dataInOut[2]['end']),
			'from4'     => self::convertUnixToTime($dataInOut[3]['start']),
			'to4'       => self::convertUnixToTime($dataInOut[3]['end']),
			'duration'  => '1899-12-31T00:00:00.000',
		], $contentTemplate);

		$filename = "{$fullname}_Bericht.xml";
		$footer = file_get_contents(__DIR__.'/Xml/footer.xml');
		$ausgabe = $header."\n";
		$ausgabe .= $content."\n";
		$ausgabe .= $footer."\n";

		header('Content-Disposition: attachment; filename="'.$filename.'"');
		header('Content-Type: text/xml, application/xml');
		header('Content-Transfer-Encoding: UTF-8');
		header('Cache-Control: must-revalidate');
		header('Pragma: public');
		print_r($ausgabe);
	}

	public static function weekdaysInGerman($day)
	{
		$day = trim($day);
		switch (strtolower($day)) {
			case 'monday':
				$day = 'Montag';
				break;
			case 'tuesday':
				$day = 'Dienstag';
				break;
			case 'wednesday':
				$day = 'Mittwoch';
				break;
			case 'thursday':
				$day = 'Donnerstag';
				break;
			case 'friday':
				$day = 'Freitag';
				break;
			case 'saturday':
				$day = 'Samstag';
				break;
			case 'sunday':
				$day = 'Sonntag';
				break;
		}

		return $day;
	}

	public static function replacePlaceholders($data, $string)
	{
		if (is_array($data)) {
			foreach ($data as $key => $value) {
				$string = str_replace('{{'.$key.'}}', $value, $string);
			}
		}

		return $string;
	}

	public static function convertUnixToTime($timestamp)
	{
		return $timestamp ? '<Data ss:Type="DateTime">'.date('Y-m-d', $timestamp).'T'.date('H:i', $timestamp).':00.000</Data>' : '';
	}
}
