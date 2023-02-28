<?php
use Attendance\Graph;
use Attendance\Holidays;

$holidays = Holidays::Render('de')->District('Baden-Württemberg');
// Dump(Holidays::isHoliday());
Graph::Render('header');

?>

<div>
	<table class="table">
		<thead>
			<th>Name des Feiertages</th>
			<th>Datum</th>
		</thead>
		<tbody>
			<?php foreach ($holidays as $key => $holiday) { ?>
				<tr>
					<td><?php echo $holiday['name']?></td>
					<td><?php echo $holiday['date']?></td>
				</tr>
			<?php } ?>
		</tbody>
	</table>
</div>

<?php

Graph::Render('footer');
