<?php

use AgroEgw\Api\User;
use Attendance\Contract;
use Attendance\TimeAccount;
use Carbon\Carbon;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

setlocale(LC_TIME, 'de_DE');
Carbon::setLocale('de');

$args = $_REQUEST;
$contract_id = $args['contract_id'];

if (empty($contract_id)) {
    die();
}

$weekdays = [
    'MO',
    'DI',
    'MI',
    'DO',
    'FR',
    'SA',
    'SO',
];

$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();
foreach (range('A', 'G') as $columnID) {
    $sheet->getColumnDimension($columnID)->setAutoSize(true);
}

$contract = Contract::Get($contract_id);
$user = User::Read($contract['user']);

$fullname = $user['account_firstname'].' '.$user['account_lastname'];

$sheet->setCellValue('A1', 'Zeitkontoübersicht: '.$fullname);
$sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
$sheet->getStyle('A1')->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
$sheet->getStyle('A1:A1')->getFont()->setBold(true);
$sheet->mergeCells('A1:F1');

$sheet->setCellValue('A2', 'Datum');
$sheet->setCellValue('B2', 'Bemerkung');
$sheet->setCellValue('C2', 'Sollzeit');
$sheet->setCellValue('D2', 'Istzeit');
$sheet->setCellValue('E2', 'Differenz');
$sheet->setCellValue('F2', 'Total');

$row = 3;
foreach (TimeAccount::oldAlgo($contract_id)['days'] as $date => $diff) {
    $diffs = $diff['is'] - $diff['should'];
    $total += $diffs;
    $diff_color = $diffs < 0 ? 'FF0000' : '00FF00';
    $total_color = $total < 0 ? 'FF0000' : '00FF00';
    if ($diff['is'] == 0 && $diff['should'] == 0) {
        continue;
    }
    $carbon = Carbon::parse($date);
    $dayOfWeek = $carbon->dayOfWeekIso - 1;
    $date = $carbon->format('d.m.Y');

    if ($lastMonth && $lastMonth != $carbon->month) {
        $sheet->setCellValue("A$row", "Zeitkonto für $lastMonthName: $lastTotal");
        $sheet->getStyle("A$row")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle("A$row")->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
        $sheet->getStyle("A$row:A$row")->getFont()->setBold(true);
        $sheet->mergeCells("A$row:F$row");
        $row++;
    }

    $lastMonth = $carbon->month;
    $lastMonthName = $carbon->englishMonth;
    $lastTotal = round($total, 2);

    $sheet->setCellValue("A$row", ($weekdays[$dayOfWeek].' '.$date));

    $sheet->setCellValue("B$row", str_replace('<br>', "\n", trim($diff['title'], '<br>')));
    $spreadsheet->getActiveSheet()->getStyle("B$row")->getAlignment()->setWrapText(true);

    $sheet->setCellValue("C$row", $diff['should']);
    $sheet->setCellValue("D$row", round($diff['is'], 2));
    $sheet->setCellValue("E$row", round($diffs, 2));
    $sheet->setCellValue("F$row", round($total, 2));

    $sheet->getStyle("E$row:E$row")->getFill()->setFillType(Fill::FILL_SOLID);
    $sheet->getStyle("E$row:E$row")->getFill()->getStartColor()->setARGB($diff_color);

    $sheet->getStyle("F$row:F$row")->getFill()->setFillType(Fill::FILL_SOLID);
    $sheet->getStyle("F$row:F$row")->getFill()->getStartColor()->setARGB($total_color);

    $row++;
}

$sheet->setCellValue("A$row", "Zeitkonto für $lastMonthName: $lastTotal");
$sheet->getStyle("A$row")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
$sheet->getStyle("A$row")->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
$sheet->getStyle("A$row:A$row")->getFont()->setBold(true);
$sheet->mergeCells("A$row:F$row");

$writer = new Xlsx($spreadsheet);
// $objWriter = IOFactory::createWriter($spreadsheet, 'Xlsx');
$writer->save(sys_get_temp_dir().'/Zeitkontoübersicht.xlsx');

$filename = 'Zeitkontoübersicht.xlsx';
$file = sys_get_temp_dir().'/Zeitkontoübersicht.xlsx';

header('Content-Disposition: attachment; filename="'.$filename.'"');
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Length: '.filesize($file));
header('Content-Transfer-Encoding: binary');
header('Cache-Control: must-revalidate');
header('Pragma: public');
readfile($file);
die();
