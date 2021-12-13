<?php
use Attendance\Categories;
use AgroEgw\DB;
use Carbon\Carbon;

ini_set('memory_limit', '-1');

class TimesheetQuery
{
    public $POST;

    public $user;
    public $date;
    public $categories;
    public $dates;

    public $start_time;
    public $end_time;

    public $where = "WHERE (ts_status !='-1' OR ts_status IS NULL)";
    public $orderby = ' ORDER BY ts_start DESC';
    public $sqlQuery = 'SELECT * FROM egw_timesheet ';

    public $html = '';

    public function __construct()
    {
        $this->user = $_POST['user'] ?: false;
        $this->date = $_POST['date'] ?: false;
        $this->pageCount = $_POST['pageCount'] ?: 1;
    }

    public function RenderSettings()
    {
        if (isset($_POST)) {
            if (!empty($_POST['category'])) {
                $categories = explode(',', base64_decode($_POST['category']));
                $cats = [];
                foreach ($categories as $category) { // go through the loop
                    $cat = explode('_', $category); // split for example cat_123 to arrays
                    $cats[] = $cat[1]; 	// get the cat id wich is the last array from cat
                }
            } else {
                $cats = false;
            }

            $dates = $_POST['dates'];
            if (isset($dates)) {
                $dates = explode("-", $dates);
                $start = Carbon::parse($dates[0])->startOfDay()->timestamp;
                $end = Carbon::parse($dates[1])->endOfDay()->timestamp;

                $this->dates = ['start' => trim($start), 'end' => trim($end)];
            } elseif ($date == 'custom_date') {
                $date = false;
            }
            $this->categories = $cats;

            return true;
        } else {
            return json_encode('404');
        }
    }

    public function Where_Statement()
    {
        if ($this->RenderSettings() === true) {
            // Adding user to the statement if this one is given
            if ($this->user) {
                $this->where .= ' AND ts_owner = '.$this->user.'';
            }

            // Adding categories to the statement if those one are given
            if (!empty($this->categories)) {
                $this->where .= " AND cat_id IN(".implode(",", $this->categories).")";
            } else {
                $categories = Categories::GetCategories();
                $this->where .= " AND cat_id IN(".implode(",", $categories).")";
            }

            // Adding the date to the statement if this one is given
            if ($this->date) {
                foreach (TimeFilter() as $time) {
                    if ($this->date == $time['name']) {
                        $startTime = $time['start'];
                        $endTime = $time['end'];
                    }
                }
            } elseif (!empty($this->dates)) {
                $startTime = $this->dates['start'];
                $endTime = $this->dates['end'];
            }

            if ($startTime && $endTime) { 
                $this->where .= ' AND ts_start >= '.$startTime;
                $this->where .= ' AND ts_start <= '.$endTime;

                $this->start_time = date('d.m.Y H:i', $startTime);
                $this->end_time = date('d.m.Y H:i', $endTime);
            }

            return true;
        } else {
            echo $this->RenderSettings();
        }
    }

    public function exec()
    {
        $itemsPerPage = 100;
        $page = intval($this->pageCount) - 1;
        $offset = $page * $itemsPerPage;

        if ($this->Where_Statement() === true) {
            $count = count(DB::GetAll($this->sqlQuery." ".$this->where));
            $this->sqlQuery .= $this->where." ".$this->orderby." LIMIT $offset, $itemsPerPage";

            foreach (DB::GetAll($this->sqlQuery) as $row) {
                $start_time = date('H:i', $row['ts_start']);
                $start_date = date('d.m.Y', $row['ts_start']);
                $duration = round(($row['ts_duration'] / 60), 2);
                foreach (DB::GetAll('SELECT * FROM egw_addressbook WHERE account_id='.$row['ts_owner'].'') as $user) {
                    $username = $user['n_family'].', '.$user['n_given'];
                    $cat_id = $row['cat_id'];

                    if ($row['cat_id'] != null) {
                        foreach (DB::GetAll('SELECT * FROM egw_categories WHERE cat_id='.$cat_id.'') as $category) {
                            $cat_name = lang($category['cat_name']);
                            $cat_id = $category['cat_id'];
                        }
                    } else {
                        $cat_name = lang('No Category');
                        $cat_id = 'NaN';
                    }

                    $this->html .= '<li id="time_'.$row['ts_id'].' " class="timeSet">
									<ul class="select-cat">
										<li class="cat_color_'.$cat_id.'"></li>
									</ul>
									<div id="title">'.$row['ts_title'].'</div>
									<div id="category">
										<font class="cat_color_'.$cat_id.'">
											<span class="Circle cat_color_'.$cat_id.'"></span>
											'.$cat_name.'
										</font>
									</div>
									<div id="duration">'.$duration.'h</div>
									<div id="user_'.$user['account_id'].'" title="'.$username.'" class="username">'.$username.'</div>
									<div id="start_time" class="date">
										<div class="s_time">'.$start_time.'</div></br>
										<div class="s_date">'.$start_date.'</div>
									</div>
								</li>';
                }
            }

            echo json_encode([
                    'categories' => $this->categories,
                    'date'		 => $this->date,
                    'user'		 => $this->user,
                    'query'		 => $this->sqlQuery,
                    'html'		 => $this->html,
                    'count'		 => $count,
                    'start_time' => $this->start_time,
                    'end_time'	 => $this->end_time,
                ]);
        }
    }
}

$timesheet = new TimesheetQuery();
$timesheet->exec();
