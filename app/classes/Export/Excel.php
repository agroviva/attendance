<?php

namespace Attendance\Export;

use AgroEgw\DB;
use Attendance\Categories;
use Carbon\Carbon;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class Excel
{
	public function __construct($data)
	{
		global $so, $bo;
		ob_get_clean();
		$owner = $_REQUEST['owner'];
		if ($_REQUEST['categories']) {
			$categories = base64_decode($_REQUEST['categories']);
		} else {
			$categories = Categories::GetCategories();
			$categories = implode(',', $categories);
		}

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
		// Dump($data);
		// die();
		// $array = array_filter($array, function($a) {
		//     return trim($a) !== "";
		// });

		$spreadsheet = new Spreadsheet();
		$sheet = $spreadsheet->getActiveSheet();
		foreach (range('A', 'H') as $columnID) {
			$sheet->getColumnDimension($columnID)->setAutoSize(true);
		}

		$user_sql = "SELECT * FROM egw_addressbook WHERE account_id = '$owner'";
		$user = $GLOBALS['egw']->db->query($user_sql, __LINE__, __FILE__, 0, -1, false, 2)->GetAll()[0];

		$fullname = $user['n_given'].' '.$user['n_family'];

		$sheet->setCellValue('A1', 'Zeiterfassung Bericht: '.$fullname);
		$sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
		$sheet->getStyle('A1')->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
		$sheet->getStyle('A1:A1')->getFont()->setBold(true);
		$sheet->mergeCells('A1:H2');

		$sheet->setCellValue('A3', 'Datum');
		$sheet->setCellValue('B3', 'Anmerkung');
		$sheet->setCellValue('C3', 'Beginn');
		$sheet->setCellValue('D3', 'Ende');
		$sheet->setCellValue('E3', 'Pause');
		$sheet->setCellValue('F3', 'Dauer');
		$sheet->setCellValue('G3', 'Sollzeit');
		$sheet->setCellValue('H3', 'Differenz');
		$sheet->getStyle('A3:H3')->getFont()->setBold(true);
		$sheet->getStyle('B3:H3')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

		$row = 4;
		foreach ($data as $key => $timesheet) {
			if ($date == date('d.m.Y', $timesheet['ts_start'])) {
				$duration += $timesheet['ts_duration'];
				$pause += $timesheet['ts_start'] - $last_end;
				$last_end = ($timesheet['ts_start'] + ($timesheet['ts_duration'] * 60));
				$end = date('H:i:s', $last_end);
			} else {
				$start = date('H:i:s', $timesheet['ts_start']);
				$title = $timesheet['ts_title'];
				$last_end = ($timesheet['ts_start'] + ($timesheet['ts_duration'] * 60));
				$end = date('H:i:s', $last_end);
				$duration = $timesheet['ts_duration'];
				$pause = 0;
				$userData = $GLOBALS['egw']->db->query("
					SELECT * FROM egw_attendance WHERE user = '$owner'
				", __LINE__, __FILE__, 0, -1, false, 2)->GetAll()[0];
				$SHOULD_DAYS = [
					'Mon' 	=> $userData['monday'] / 60 / 60,
					'Tue' 	=> $userData['tuesday'] / 60 / 60,
					'Wed' 	=> $userData['wednesday'] / 60 / 60,
					'Thu' 	=> $userData['thursday'] / 60 / 60,
					'Fri' 	=> $userData['friday'] / 60 / 60,
					'Sat' 	=> $userData['saturday'] / 60 / 60,
					'Sun' 	=> $userData['sunday'] / 60 / 60,
				];
				$should = ($SHOULD_DAYS[date('D', $timesheet['ts_start'])] ?
					$SHOULD_DAYS[date('D', $timesheet['ts_start'])] : 0);
			}

			$date = date('d.m.Y', $timesheet['ts_start']);

			if ($date == date('d.m.Y', $data[$key + 1]['ts_start'])) {
				continue;
			}
			$pause = $pause / 60 / 60;
			$duration = $duration / 60;

			if ($title !== 'Arbeitszeit') {
				$start = $end = $pause = '';
			}

			$sheet->getStyle('A'.$row)->getNumberFormat()->setFormatCode('dddd, d. mmmm yyyy');
			$sheet->getStyle('E'.$row.':H'.$row)->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_NUMBER_00);

			$sheet->setCellValue("A$row", $date);
			$sheet->setCellValue("B$row", $title);
			$sheet->setCellValue("C$row", $start);
			$sheet->setCellValue("D$row", $end);
			$sheet->setCellValue("E$row", $pause);
			$sheet->setCellValue("F$row", $duration); //$duration
			$sheet->setCellValue("G$row", $should);
			$sheet->setCellValue("H$row", "=F$row-G$row");
			$sheet->getStyle("B$row:H$row")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

			$color = \AgroEgw\Api\Categories::getColor($timesheet['cat_id']);
			$color = strtoupper(trim($color, '#'));

			$sheet->getStyle('A'.$row.':H'.$row)->getFill()->setFillType(Fill::FILL_SOLID);
			$sheet->getStyle('A'.$row.':H'.$row)->getFill()->getStartColor()->setARGB($color);

			$row++;
		}

		$last_row = $row - 1;
		$sheet->getStyle("A$row:H$row")->getFont()->setBold(true);
		$sheet->setCellValue("E$row", 'Summe:');
		$sheet->setCellValue("F$row", "=SUM(F4:F$last_row)");
		$sheet->setCellValue("G$row", "=SUM(G4:G$last_row)");
		$sheet->setCellValue("H$row", "=SUM(H4:H$last_row)");
		$sheet->getStyle("F$row:H$row")->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_NUMBER_00);

		$filename = "{$fullname}_Bericht.xlsx";

		$writer = new Xlsx($spreadsheet);
		// $objWriter = IOFactory::createWriter($spreadsheet, 'Xlsx');
		$writer->save(sys_get_temp_dir().'/'.$filename);

		$file = sys_get_temp_dir().'/'.$filename;

		header('Content-Disposition: attachment; filename="'.$filename.'"');
		header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
		header('Content-Length: '.filesize($file));
		header('Content-Transfer-Encoding: binary');
		header('Cache-Control: must-revalidate');
		header('Pragma: public');
		readfile($file);
	}

	public function isWeekend($date)
	{
		if (is_string($date)) {
			return date('N', strtotime($date)) >= 6;
		} elseif (is_int($date)) {
			return date('N', $date) >= 6;
		}

		return false;
	}
}
