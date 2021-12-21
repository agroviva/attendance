<?php 

use Attendance\Categories;
use AgroEgw\DB;
use Carbon\Carbon;


echo json_encode(Categories::GetCategories());