<?php
use AgroEgw\Api\User;
use Attendance\Contract;
use Attendance\TimeAccount;
use Carbon\Carbon;

setlocale(LC_TIME, 'de_DE');
Carbon::setLocale('de');

$args = $_POST;
$contract_id = explode('_', $args['contract_id'])[1];

if (empty($contract_id)) {
    die();
}

$contract = Contract::Get($contract_id);
$user = User::Read($contract['user']);

$fullname = $user['account_firstname'].' '.$user['account_lastname'];

$weekdays = [
    'MO',
    'DI',
    'MI',
    'DO',
    'FR',
    'SA',
    'SO',
];

ob_start();
?>
<style type="text/css">
	.header {
		text-align: center;
	}

	.header font {
		font-size: 24px;
    	font-weight: bold;
	}
	
	.table.overview {
		border-collapse: collapse;
		font-family: arial;
	}

	.table.overview th {
		border: 1px solid black;
		padding: 15px 0px 15px 5px;
	}

	.table.overview td {
		border: 1px solid black;
	}

	.overview .number, .table.overview th.number {
		text-align: right;
		padding: 2px 10px;
	}

	.overview .date, .overview .day {
		text-align: center;
		padding: 10px;
	}

	.overview .notice {
		text-align: left;
		padding: 2px 10px;
	}

	.overview .month_sum td {
		border-top: 1px solid black;
		border-bottom: 1px solid black;
	}

	.overview td.summe {
		font-weight: bold;
		padding: 10px 0px 10px 5px;
	}

	.overview .red, .overview .green {
		font-weight: bold;
	}

	.overview .red {
		color: red
	}

	.overview .green {
		color: green;
	}
</style>
<div class="header">
	<font>Zeitkontoübersicht: <?php echo $fullname?></font>
	<a href="/egroupware/attendance/export/timeaccount/excel/?contract_id=<?php echo $contract_id?>" target="_blank">
		<button type="button" rel="tooltip" class="btn btn-success btn-simple" data-toggle="modal" title="Excel Export">Excel Export</button>
	</a>
	<a href="/egroupware/attendance/export/timeaccount/pdf/?contract_id=<?php echo $contract_id?>" target="_blank">
		<button type="button" rel="tooltip" class="btn btn-warning btn-simple" data-toggle="modal" title="Excel Export">PDF Export</button>
	</a>
</div>
<table class="table overview" style="width: 100%;">
	<thead>
		<tr>
			<th class="day">Tag</th>
			<th class="date">Datum</th>
			<th class="notice">Bemerkung</th>
			<th class="number">Sollzeit</th>
			<th class="number">Istzeit</th>
			<th class="number">Differenz</th>
			<th class="number">Total</th>
		</tr>
	</thead>
	<tbody>
	<?php foreach (TimeAccount::oldAlgo($contract_id)['days'] as $date => $diff): ?>
		<?php
        $diffs = $diff['is'] - $diff['should'];
        $total += $diffs;
        $diff_color = $diffs < 0 ? 'red' : 'green';
        $total_color = $total < 0 ? 'red' : 'green';
        if ($diff['is'] == 0 && $diff['should'] == 0) {
            continue;
        }
        $carbon = Carbon::parse($date);
        $dayOfWeek = $carbon->dayOfWeekIso - 1;
        $date = $carbon->format('d.m.Y');

        if ($lastMonth && $lastMonth != $carbon->month) {
            ?>
	        <tr class="month_sum">
	        	<td class="summe" colspan="3" align="center" ><?php echo $endMonth?></td>
	        	<td class="number"><?php echo $sollTotal?></td>
				<td class="number"><?php echo number_format($istTotal, 2)?></td>
				<td class="number <?php echo $diffTotalColor?>"><?php echo number_format($diffTotal, 2)?></td>
	        	<td class="number <?php echo $lastTotalColor?>"><?php echo $lastTotal?></td>
	        </tr>
	        <?php
            $sollTotal = $istTotal = $diffTotal = 0;
        }

        $lastMonth = $carbon->month;
        $lastMonthName = lang($carbon->englishMonth);
        $lastTotal = number_format($total, 2);
        $sollTotal += $diff['should'];
        $istTotal += $diff['is'];
        $diffTotal += $diffs;

        $endMonth = "Zeitkonto für $lastMonthName";
        $diffTotalColor = $diffTotal < 0 ? 'red' : 'green';
        $lastTotalColor = $lastTotal < 0 ? 'red' : 'green';

        ?>
		<tr>
			<td class="day"><?php echo $weekdays[$dayOfWeek]?></td>
			<td class="date"><?php echo $date?></td>
			<td class="notice"><?php echo $diff['title']?></td>
			<td class="number"><?php echo $diff['should']?></td>
			<td class="number"><?php echo number_format($diff['is'], 2)?></td>
			<td class="<?php echo $diff_color?> number"><?php echo number_format($diffs, 2)?></td>
			<td class="<?php echo $total_color?> number"><?php echo number_format($total, 2)?></td>
		</tr>
	<?php endforeach ?>
        <tr class="month_sum">
        	<td class="summe" colspan="3" align="center" ><?php echo $endMonth?></td>
        	<td class="number"><?php echo $sollTotal?></td>
			<td class="number"><?php echo number_format($istTotal, 2)?></td>
			<td class="number <?php echo $diffTotalColor?>"><?php echo number_format($diffTotal, 2)?></td>
        	<td class="number <?php echo $lastTotalColor?>"><?php echo $lastTotal?></td>
        </tr>
	</tbody>
</table>
<?php
$html = ob_get_clean();

echo json_encode([
    'output' => $html,
]);
