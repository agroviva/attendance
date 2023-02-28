<?php

namespace Attendance;

use AgroEgw\DB;
use Attendance\Export\Excel;
use Attendance\Export\Xml;
use EGroupware\Api;
use Mpdf\Mpdf;

class Export
{
	public $export_data;

	public $dates;

	public $first_month;
	public $second_month;
	public $weekdays = [];

	public $DOCUMENT_NAME;
	public $logo;

	public $date;

	public function __construct($data)
	{
		$this->Contracts = new Contracts();
		$this->export_data = $data;
		$this->weekdays = [
			1 => 'Mo',
			2 => 'Di',
			3 => 'Mi',
			4 => 'Do',
			5 => 'Fr',
			6 => 'Sa',
			7 => 'So',
		];
		$this->logo = Api\Image::find('api', $GLOBALS['egw_info']['server']['login_logo_file'] ? $GLOBALS['egw_info']['server']['login_logo_file'] : 'logo', '', null);
		$this->dates = TimeFilter();
	}

	public function load()
	{
		global $so, $bo;

		$owner = $_REQUEST['owner'];
		$categories = base64_decode($_REQUEST['categories']) ?: Categories::GetCategories();
		$categories = is_array($categories) ? implode(',', $categories) : $categories;

		$date = $_REQUEST['dates'];
		$date = explode('-', $date);
		$startTime = Core::dateToUnix($date[0]);
		$endTime = Core::dateToUnix($date[1], true);

		if (!empty($_REQUEST['date'])) {
			foreach ($this->dates as $date) {
				if ($_REQUEST['date'] == $date['name']) {
					$startTime = $date['start'];
					$endTime = $date['end'];
				}
			}
		}

		$startDate = Core::unixToDate($startTime);
		$endDate = Core::unixToDate($endTime);

		$sql = "SELECT * FROM egw_timesheet 
                WHERE (ts_status !='-1' OR ts_status IS NULL) 
                AND cat_id IN($categories)
                AND ts_start >= $startTime AND ts_start <= $endTime";
		$sql .= " AND ts_owner = $owner";
		$sql .= ' ORDER BY ts_start DESC';

		$data = DB::Get($sql);
		$userData = Core::Self($owner);
		$username = $userData['n_given'].' '.$userData['n_family'];

		$this->DOCUMENT_NAME = 'Auswertung_'.$username;

		ob_start();
		echo Core::Console('css', APPDIR.'/css/export-style.css');

		//PDF HEADER begin
		$logo = $this->logo;
		$reverse_username = $userData['n_family'].', '.$userData['n_given'];
		$month = lang(date('F', $data['ts_start']));
		$year = date('Y', $data['ts_start']);

		$timeline = "<font><strong>{$reverse_username}: vom {$startDate} bis zu {$endDate}</strong></font>";
		$heading = '<strong>Zeiterfassungsbericht</strong>';

		echo "<div class='left_pane'>
				<div>
					<p style='font-size: 18px;'>{$heading}</p>
				</div>
				<div>
				{$timeline}
				</div>
			</div>";
		if (!is_null($logo) && isset($logo)) {
			/* $this->PDF_HEADER .= "<div class='right_pane'>
									<img src='{$logo}' />
								</div>"; */
		}
		echo "<div class='right_pane'>
				<p>Erstellungsdatum: ".(date('d.m.Y')).'</p>
			</div>';
		// The end of PDF header

		if ($_REQUEST['type'] == 'pdf' || $_REQUEST['type'] == 'excel') {
			$category = $categories;
			$date = $post_data['date'];
			$user = $post_data['user'];

			if ($user != 'user') {
				echo "<table border='1'>
						  <tr>
						  	<th>".lang('Tag').'</th>
						  	<th>'.lang('Date and Time').'</th>
						  	<th>'.lang('Category').'</th>
						    <th>'.lang('Duration').'</th>
						    <th>'.lang('User').'</th>
						  </tr>
						 ';

				$month_order = [];
				$end_query = [];

				foreach (DB::GetAll($sql) as $zeile) {
					if ((date('Y-m', $zeile['ts_start']) != $last_date_month) && $last_date_month) {
						$end_query[] = $month_order;
						//Dump($month_order);
						$month_order = [];
					}
					if ((date('m-d', $zeile['ts_start']) == $last_date_day)) {
						//$month_order[$id]['ts_duration'] += $zeile['ts_duration'];
						$month_order[$id]['children'][] = $zeile;
					} else {
						if ((date('m-d', $zeile['ts_start']) > $last_date_day) && $last_date_day) {
							array_push($month_order, $zeile); // Am Ende des Arrays einbinden
							$id = count($month_order) - 1; //find the key id
						} else {
							array_unshift($month_order, $zeile); // Am Anfang des Arrays einbinden
							$id = 0; //the key id is 0 in this case
						}
					}
					$last_date_day = date('m-d', $zeile['ts_start']);
					$last_date_month = date('Y-m', $zeile['ts_start']);
				}
				if (!empty($month_order)) {
					$end_query[] = $month_order;
					$month_order = [];
				}

				$i = 1;
				$end_query = (empty($end_query) ? [0=>$result] : $end_query);
				//Dump($end_query);
				foreach ($end_query as $key => $month) {
					foreach ($month as $row) {
						foreach (DB::GetAll('SELECT * FROM egw_categories WHERE cat_id='.$row['cat_id'].'') as $categories) {
							$category = $categories['cat_name'];
						}

						$start = ($category == 'Work' ? $bo->dateDisplay($row['ts_start']) : $bo->dateDisplay($row['ts_start'], false));

						$duration = round(($row['ts_duration'] / 60), 2);
						if ((date('Y-m', $row['ts_start']) != $last_date) && $last_date) { // If dates are not the same by month and if the last_date variable exist
							echo "<tr><td colspan='7'>".$username.': '.lang('Sum').' '.lang(date('F', $last_unixtime)).' = '.$month_duration.'h</td></tr>';
							$month_duration = 0;
							if ($date == 'date') {
								if ((date('Y', $row['ts_start']) != $last_date_year) && $last_date_year) { // If dates are the same by year and if the last_date_year variable exist
									echo "<tr><td colspan='7'>".$year_duration.'</td></tr>';
									$year_duration = 0;
								}
							}
						}
						$last_unixtime = $row['ts_start'];
						$month_duration = $month_duration + $duration;
						$year_duration = $year_duration + $duration;
						foreach (DB::GetAll('SELECT * FROM egw_addressbook WHERE account_id='.$row['ts_owner'].'') as $user) {
							$username = $user['n_family'].', '.$user['n_given'];
							if (!empty($row['children'])) {
								$childernString = '';
								$pausetime = 0;
								$last_time_ = null;
								$full_duration = $row['ts_duration'];
								for ($k = count($row['children']) - 1; $k >= 0; $k--) {
									$value = $row['children'][$k];
									$full_duration += $value['ts_duration'];
									$pausetime += (!is_null($last_time_) ? ($value['ts_start'] - $last_time_) : $pausetime);
									// if (!is_null($last_time_)) {
									// 	echo date("H:i", $value['ts_start'])." - ".date("H:i", $last_time_)."</br>";
									// }
									$childernString .= '<tr>';
									$childernString .= '<td></td>';
									if ($_SERVER['SERVER_NAME'] != 'e34.agroviva.net') {
										$childernString .= '<td>'.date('H:i', $value['ts_start']).' - '.date('H:i', ($value['ts_start'] + $value['ts_duration'] * 60)).'</td>';
									} else {
										$childernString .= '<td></td>';
									}
									if ($value['cat_id'] != null) {
										$childernString .= '<td>'.lang($category).'</td>';
									} else {
										$childernString .= '<td>'.lang('No Category').'</td>';
									}
									$childernString .= '<td>'.round(($value['ts_duration'] / 60), 2).'h</td>
									    <td>'.$username.'</td>
									  </tr>';
									$last_time_ = $value['ts_start'] + ($value['ts_duration'] * 60);
									//echo floor($pausetime / 60 / 60).":".floor(($pausetime / 60) % 60)."</br>";
								}
								$pausetime += ($last_time_ ? ($row['ts_start'] - $last_time_) : $pausetime);
								//echo floor($pausetime / 60 / 60).":".floor(($pausetime / 60) % 60)."</br>";
								//echo date("H:i", $row['ts_start'])." - ".date("H:i", $last_time_)."</br></br>";
								$childernString .= '<tr>';
								$childernString .= '<td></td>';
								if ($_SERVER['SERVER_NAME'] != 'e34.agroviva.net') {
									$childernString .= '<td>'.date('H:i', $row['ts_start']).' - '.date('H:i', ($row['ts_start'] + $row['ts_duration'] * 60)).'</td>';
								} else {
									$childernString .= '<td></td>';
								}

								if ($row['cat_id'] != null) {
									$childernString .= '<td>'.lang($category).'</td>';
								} else {
									$childernString .= '<td>'.lang('No Category').'</td>';
								}
								$childernString .= '<td>'.$duration.'h</td>
								    <td>'.$username.'</td>
								  </tr>';

								if ($_SERVER['SERVER_NAME'] != 'e34.agroviva.net') {
									$childernString .= '<tr>';
									$childernString .= "<td colspan='2'></td>";
									$childernString .= '<td>'.lang('Pausenzeit').'</td>';
									$childernString .= '<td>'.round(($pausetime / 60 / 60), 2).'h</td>
									    <td>'.$username.'</td>
									  </tr>';
								}

								echo "<tr class='day_bold'>";
								echo '<td>'.$this->weekdays[date('N', $row['ts_start'])].'</td>
								   <td>'.date('d.m.Y', $row['ts_start']).'</td>';
								if ($row['cat_id'] != null) {
									echo '<td>'.lang($category).'</td>';
								} else {
									echo '<td>'.lang('No Category').'</td>';
								}
								echo '<td>Summe: '.round(($full_duration / 60), 2).'h</td>
								    <td>'.$username.'</td>
								  </tr>';
								echo $childernString;

								//we substract the last row duration because it is counted one time before
								$month_duration += round((($full_duration - $row['ts_duration']) / 60), 2);
								$year_duration += round((($full_duration - $row['ts_duration']) / 60), 2);
							} else {
								echo '<tr id='.$i.'>';
								echo '<td>'.$this->weekdays[date('N', $row['ts_start'])].'</td>
								    <td>'.$start.'</td>';
								if ($row['cat_id'] != null) {
									echo '<td>'.lang($category).'</td>';
								} else {
									echo '<td>'.lang('No Category').'</td>';
								}
								echo '<td>'.$duration.'h</td>
								    <td>'.$username.'</td>
								  </tr>';
							}
						}
						$last_date = date('Y-m', $row['ts_start']);
						$last_date_year = date('Y', $row['ts_start']);
						$i++;
					}
				}
				if ($i >= 1) {
					echo "<tr><td colspan='5'>".$username.': '.lang('Sum').' '.lang(date('F', $row['ts_start'])).' = '.$month_duration.'h</td></tr>';

					if (($date == 'thisyear') or ($date == 'lastyear')) {
						echo "<tr><td colspan='5'>".$username.': '.lang('Sum This Year =').' '.$year_duration.'h</td></tr>';
					}
					foreach ($this->Contracts->Load() as $key => $asset) {
						if (!empty($asset['status']) && $asset['status'] != 'expired') {
							if ($asset['user'] == $userData['account_id']) {
								echo "<tr><td colspan='5'>".$username.': '.lang('Resturlaub =').' '.$asset['rest_vac'].' Tage(n)</td></tr>';
							}
						}
					}
				}
			} else {
				echo "<table border='1'>
						  <tr>
					";
				if (($date == 'thismonth') or ($date == 'lastmonth')) {
					echo "<th colspan='7'>Die monatliche Auswertung für ".lang(date('F', $startTime)).'</th>
							</tr>';
					echo '<tr>
								<th>Benutzername</th>
								<th>Arbeitszeit</th>
								<th>Arbeitstage</th>
								<th>Urlaubstage</th>
								<th>Krankheitstage</th>
								<th>Feiertage</th>
								<th>Schulung</th>
							</tr>';
				} else {
					echo 'It is not defined';
				}
				$i = 1;
				$work_num = 0;
				$vac_num = 0;
				$sick_num = 0;
				$hol_num = 0;
				$school_num = 0;
				foreach (DB::GetAll($res) as $row) {
					$user_id = $row['ts_owner'];
					$cat_name = \AgroEgw\Api\Categories::Read($row['cat_id']);
					if ($cat_name['name'] == 'Work') {
						$work_num = $work_num + 1;
					} elseif ($cat_name['name'] == 'Vacation') {
						$vac_num = $vac_num + 1;
					} elseif ($cat_name['name'] == 'Sickness') {
						$sick_num = $sick_num + 1;
					} elseif ($cat_name['name'] == 'Holiday') {
						$hol_num = $hol_num + 1;
					} elseif ($cat_name['name'] == 'School') {
						$school_num = $school_num + 1;
					}
					if (($user_id != $last_user_id) && $last_user_id) { // If user is the same with the last and if a last user is available
						foreach (DB::GetAll('SELECT * FROM egw_addressbook WHERE account_id='.$last_user_id.'') as $user) {
							$username = $user['n_family'].', '.$user['n_given'];
							echo '
							<tr id='.$i.'>
								<td>'.$username.'</td>
								<td>'.$user_duration.'h</td>
								<td>'.$work_num.' '.lang('day(s)').'</td>
	 							<td>'.$vac_num.' '.lang('day(s)').'</td>
	 							<td>'.$sick_num.' '.lang('day(s)').'</td>
	 							<td>'.$hol_num.' '.lang('day(s)').'</td>
	 							<td>'.$school_num.' '.lang('day(s)').'</td>
							</tr>';
						}
						$user_duration = 0;
						$work_num = 0;
						$vac_num = 0;
						$sick_num = 0;
						$hol_num = 0;
						$school_num = 0;
					}

					$duration = round(($row['ts_duration'] / 60), 2);
					$user_duration = $user_duration + $duration;
					$last_user_id = $row['ts_owner'];
					$i++;
				}
				if ($i >= 1) {
					foreach (DB::GetAll('SELECT * FROM egw_addressbook WHERE account_id='.$last_user_id.'') as $user) {
						$username = $user['n_family'].', '.$user['n_given'];
						echo '
							<tr id='.$i.'>
								<td>'.$username.'</td>
								<td>'.$user_duration.'h</td>
								<td>'.$work_num.' '.lang('day(s)').'</td>
	 							<td>'.$vac_num.' '.lang('day(s)').'</td>
	 							<td>'.$sick_num.' '.lang('day(s)').'</td>
	 							<td>'.$hol_num.' '.lang('day(s)').'</td>
	 							<td>'.$school_num.' '.lang('day(s)').'</td>
							</tr>';
					}
				}
			}

			echo '</table>';

			echo "<div>
					<div class='date'>Ort, Datum</div>
				</div>

				<div>
					<div class='first_sign'>Unterschrift Arbeitnehmer</div>
					<div class='second_sign'>Unterschrift Arbeitgeber</div>
				</div>";
		}
	}

	public function exec($html = false)
	{
		if ($_REQUEST['type'] == 'excel') {
			if ($_SERVER['SERVER_NAME'] == 'e16.agroviva.net') {
				(new Xml());
			} else {
				(new Excel());
			}
		} else {
			ob_start();
			$this->load();
			$output = ob_get_clean();

			$mpdf = new Mpdf(['tempDir' => sys_get_temp_dir()]);
			$mpdf->WriteHTML($output);
			$mpdf->Output($this->DOCUMENT_NAME.'.pdf', 'I');
		}
	}

	public static function Render($export)
	{
		$export = explode('.', $export);
		$export[count($export) - 1] .= '.php';
		$export = implode('/', $export);

		require APPDIR.'/export/'.$export;
	}

	public static function Route()
	{
		if (self::hasRoute('/attendance/export/timeaccount/excel/')) {
			self::Render('timeaccount.excel');
		} elseif (self::hasRoute('/attendance/export/timeaccount/pdf/')) {
			self::Render('timeaccount.pdf');
		}
	}

	public static function hasRoute($route)
	{
		$uri = explode('?', $_SERVER['REQUEST_URI'])[0];
		if ($uri == '/egroupware'.$route || $uri == $route) {
			return true;
		}

		return false;
	}
}
