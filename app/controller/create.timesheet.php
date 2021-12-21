<?php 

use Attendance\Categories;
use AgroEgw\DB;
use Carbon\Carbon;

$data = array();
parse_str($_REQUEST["data"], $data);
$user = $data["user"];
$start = Carbon::parse($data["datetime"])->timestamp;
$duration = $data["duration"] * 60;
$title = $data["title"];
$timeNow = time();

$categories = Categories::GetCategories();
$work = $categories["work"];

$Query = "INSERT INTO egw_timesheet(ts_start, ts_duration, ts_quantity, ts_title, cat_id, ts_owner, ts_created, ts_modified, ts_modifier) 
VALUES ('$start', '$duration', '$duration', '$title', '$work', '$user', '$user', '$timeNow', '$user')";

(new DB($Query));

echo json_encode(array(
    "response" => "success"
));