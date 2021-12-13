<?php
use AgroEgw\DB;
use Carbon\Carbon;

ini_set('memory_limit', '256M');

use Attendance\Categories;

$GLOBALS['egw_info']['flags']['app_header'] = lang('attendance - Time');

$bo = new attendance_bo();
$so = new attendance_so();

$startOfMonth = Carbon::now()->startOfMonth();
$endOfMonth = Carbon::now()->endOfMonth();
$thisMonth = $startOfMonth->format("d.m.Y")." - ".$endOfMonth->format("d.m.Y");

?>
<!DOCTYPE html>
<html lang="de">
<head>
	<title>agroviva [Zeiterfassung - Arbeitsverträge]</title>
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body>
	<?php

	$categories = Categories::Get();
	echo '<style type="text/css">';
	    foreach ($categories as $key => $category) {
	        $color = $category['data']['color'];
	        $id = $category['id']; ?>
			div.cat_color_<?php echo $id; ?>, span.cat_color_<?php echo $id; ?> {
			    background-color: <?php echo $color; ?>;
			}
			.select-cat li.cat_color_<?php echo $id; ?> {
				border-left-color: <?php echo $color; ?>;
			}
		<?php
	    }
	    echo '.select-cat li {
		    border-left: 6px solid transparent;
		}';
	echo '</style>';

    $query = "SELECT * FROM egw_timesheet WHERE (ts_status !='-1' OR ts_status IS NULL)";
    $CATS = Categories::GetCategories();
    $CATS = implode(",", $CATS);
    $query .= " AND cat_id IN({$CATS})";
    $query .= " AND ts_start >= ".$startOfMonth->timestamp." AND ts_start <= ".$endOfMonth->timestamp;

	$attendances = $query;
	$result = $attendances." ORDER BY ts_start DESC LIMIT 0, 100";

	$category = $_POST['category'];
	$date = $_POST['date'];
	$user = $_POST['user'];
	?>
<link rel="stylesheet" type="text/css" href="/egroupware/attendance/app/css/timesheet.css">
<link rel="stylesheet" type="text/css" href="/egroupware/attendance/app/css/pikaday.css">
<script type="text/javascript" src="/egroupware/attendance/app/js/jquery.js"></script><!-- 
<script type="text/javascript" src="/egroupware/attendance/app/js/moment.min.js">></script>
 -->


	<script type="text/javascript" src="https://cdn.jsdelivr.net/jquery/latest/jquery.min.js"></script>
	<script type="text/javascript" src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
	<script type="text/javascript" src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
	<link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />

<div id="filter">
	<div class="column-2">
		<div class="custom_date">
			<input type="text" name="daterange" value="<?php echo $thisMonth?>" />
		</div>
	</div>
	<div class="column-2" id="table">
	
		<ul>
			<li class="list">
				<div id="category" class="filter_frame">
					<span class="button timesheet"><?php echo lang('Category'); ?></span>
					<div class="dropdown-filter">
						<?php
                            foreach ($categories as $key => $row) {
                                if (!empty($row) && $key != 'parent') {
                                    echo "<span id='cat_".$row['id']."'>".$row['name'].'</span>';
                                }
                            }
                        ?>
					</div>
					<img class="down_white_icon" width="11" height="7" alt="Down Icon" src="/egroupware/attendance/templates/default/images/down_white_icon.png">
				</div>	
			</li>
			<li class="list">
				<div id="user" class="filter_frame">
					<span class="button timesheet"><?php echo lang('User'); ?></span>
					<div class="dropdown-filter">
						<?php
							$sql = "SELECT * FROM egw_addressbook a RIGHT OUTER JOIN egw_attendance b ON a.account_id = b.user WHERE (a.account_id IS NOT NULL AND b.user IS NOT NULL) AND (b.end is NULL OR b.end >= CURDATE()) ORDER BY n_family;";
                            foreach (DB::GetAll($sql) as $row) {
                                echo "<span id='".$row['account_id']."'>".$row['n_family'].', '.$row['n_given'].'</span>';
                            }
                        ?>
					</div>
					<img class="down_white_icon" width="11" height="7" alt="Down Icon" src="/egroupware/attendance/templates/default/images/down_white_icon.png">
				</div>
			</li>
			<li class="list">
				<div class="exportOptions">
					<div id="pdf" class="pdfExport">
						<img src="/egroupware/attendance/templates/default/images/pdf.svg">
					</div>
					<div id="excel" class="excelExport">
						<img src="/egroupware/attendance/templates/default/images/excel.svg">
					</div>
				</div>
			</li>
			<li class="list">
				<div id="count" class="filter_frame">
					<span class='button timesheet'>
						<?php echo count(DB::GetAll($attendances))?>
					</span>
				</div>
			</li>
		</ul>
	</div>
</div>
<div id="preloader">
	<div class="preloader-container">
		<img src="/egroupware/attendance/templates/default/images/clock.gif" class="preload-gif wow fadeInUp">
	</div>
</div>

<ul id='TimeEntries'>
<?php

$i = 1;
foreach (DB::GetAll($result) as $row) {
    $start_time = date('H:i', $row['ts_start']);
    $start_date = date('d.m.Y', $row['ts_start']);

    $duration = round(($row['ts_duration'] / 60), 2);
    foreach ($GLOBALS['egw']->db->query('SELECT * FROM egw_addressbook WHERE account_id='.$row['ts_owner'].'') as $user) {
        $username = $user['n_family'].', '.$user['n_given'];
        $cat_id = $row['cat_id'];

        if ($row['cat_id'] != null) {
            foreach ($GLOBALS['egw']->db->query('SELECT * FROM egw_categories WHERE cat_id='.$cat_id.'') as $category) {
                $cat_name = $category['cat_name'];
                $cat_id = $category['cat_id'];
            }
        } else {
            $cat_name = lang('No Category');
            $cat_id = 'NaN';
        } ?>
			<li id="time_<?php echo $row['ts_id']; ?>" class="timeSet">
				<ul class='select-cat'>
					<li class="cat_color_<?php echo $cat_id; ?>"></li>
				</ul>
				<div id="title"><?php echo $row['ts_title']; ?></div>
				<div id="category">
					<font class="cat_color_<?php echo $cat_id; ?>">
						<span class="Circle cat_color_<?php echo $cat_id; ?>"></span>
						<?php echo $cat_name; ?>
					</font>
				</div>
				<div id="duration"><?php echo $duration; ?>h</div>
				<div id="user_<?php echo $user['account_id']; ?>" title="<?php echo $username; ?>" class="username"><?php echo $username; ?></div>
				<div id="start_time" class="date">
					<?php 
                        echo "<div class='s_time'>".$start_time.'</div></br>';
        echo "<div class='s_date'>".$start_date.'</div>'
                    ?>
				</div>
			</li>
		<?php
    }
    $i++;
}
?> 
</ul>
</table>
<script type="text/javascript" src="/egroupware/attendance/app/js/timesheet.js"></script>
<script type="text/javascript" src="/egroupware/attendance/app/js/download.js"></script>
</div>
</body>
</html>