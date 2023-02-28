<?php

namespace Attendance;

class Vacation
{
	public $contracts;

	public $user;

	public $start_contract;
	public $start_contract_unix;
	public $dateUnix;
	public $date;

	public $category_id;

	public $year_begin;
	public $year_begin_unix;
	public $year_end;
	public $year_end_unix;

	public $startdate;
	public $enddate;

	public $key = 0;

	public function __construct()
	{
		$this->so = new \attendance_so();
		$this->bo = new \attendance_bo();
		$this->dateUnix = time();
		$this->date = date('Y-m-d');
		$this->year_begin_unix = strtotime(date('Y-01-01'));
		$this->year_end_unix = strtotime(date('Y-12-31'));
		$this->year_begin = date('Y-01-01');
		$this->year_end = date('Y-12-31');
		$this->category_id = Categories::Get()['vacation']['id'];
	}

	public function set($con)
	{
		$this->contracts = $con;

		$this->calculate_vacations();

		return $this;
	}

	public function get()
	{
		return $this->contracts;
	}

	public function Real_Vacation($contract)
	{
		$start_year = date('Y', $this->start_contract_unix);
		$end_year = ($contract['end'] ? date('Y', strtotime($contract['end'])) : 0);
		$this_year = date('Y');

		$Rest_Vacation = 0;
		for ($i = (int) $start_year; $i <= $this_year; $i++) {
			if (($end_year == 0) || ($i <= $end_year)) {
				if ($i == $start_year) {
					$start = $this->start_contract;
					$Rest_Vacation += $contract['extra_vacation'];
					if ($contract['extra_vacation'] == 0) {
						$Rest_Vacation_Output = $Rest_Vacation;
					} else {
						$Rest_Vacation_Output = $Rest_Vacation.' ('.$contract['extra_vacation'].')';
					}
				} else {
					$start = $i.'-01-01';
				}

				if ($i == $this_year) {
					$end = $i.'-12-31';
					if ($contract['end']) {
						if ($i == $end_year) {
							$end = $contract['end'];
						}
					}
				} else {
					$end = $i.'-12-31';
				}

				$this->contracts[$this->key]['vacation'] = $should_vacation = $this->Real_Have_Vacation($contract, $this->Days_Between($start, $end));

				$Spent_Vacation = $this->so->Get_Vacation_Time($contract['user'], $this->category_id, $start, $end);
				if ($Rest_Vacation == 0) {
					$Rest_Vacation_Output = ($should_vacation - $Spent_Vacation);
				} else {
					$Rest_Vacation_Output = ($should_vacation - $Spent_Vacation + $Rest_Vacation).' ('.$Rest_Vacation.')';
				}
				$Rest_Vacation = ($should_vacation - $Spent_Vacation) + $Rest_Vacation;
			}
		}

		if ($Spent_Vacation <= 1) {
			$this->contracts[$this->key]['spent_vac'] = $Spent_Vacation.' '.lang('day');
		} else {
			$this->contracts[$this->key]['spent_vac'] = $Spent_Vacation.' '.lang('days');
		}

		$this->contracts[$this->key]['rest_vac'] = $Rest_Vacation_Output;
	}

	public function Real_Have_Vacation($contract, $have_days)
	{
		$total_days = 365.25;
		$vacation_per_year = $contract['vacation'];

		$contract_percentage = ($have_days / $total_days) * 100; // Percentage of have month

		$real_vacation = ($contract_percentage / 100) * $vacation_per_year;

		return round($real_vacation);
		//return $vacation_per_year;  //we return vacation per year
	}

	public function Days_Between($date1, $date2)
	{
		$start = strtotime($date1);
		$end = strtotime($date2);

		$datediff = abs($end - $start);

		return $datediff / (60 * 60 * 24);
	}

	public function calculate_vacations()
	{
		foreach ($this->contracts as $contract) {
			$this->user = $contract['user'];

			$contract['annualLeave'] = $contract['vacation'];
			$this->contracts[$this->key]['annualLeave'] = $contract['vacation'];

			$this->start_contract = $contract['start'];

			$this->start_contract_unix = strtotime($contract['start']);

			$this->Rest_Vacation($contract);

			$this->key++;
		}
	}

	public function Rest_Vacation($contract)
	{
		if (!$contract['extra_vacation']) {
			$contract['extra_vacation'] = 0;
		}

		$this->Real_Vacation($contract);
	}
}
