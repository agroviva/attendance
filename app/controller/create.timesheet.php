<?php 

use Attendance\Categories;
use AgroEgw\DB;
use Carbon\Carbon;

$data = array();
parse_str($_REQUEST["data"], $data);

$categories = Categories::GetCategories();
$work = $categories["work"];
$duration = 1 * 60;
$title = "Arbeitszeit";
$timeNow = time();


$Query = "INSERT INTO egw_timesheet(ts_start, ts_duration, ts_quantity, ts_title, cat_id, ts_owner, ts_created, ts_modified, ts_modifier) 
VALUES ('$start', '$duration', '$duration', '$title', '$work', '$user', '$user', '$timeNow', '$user')";

#(new DB($Query));

echo json_encode(array(
    $Query, 
    $_REQUEST,
    $data
));