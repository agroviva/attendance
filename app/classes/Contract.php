<?php

namespace Attendance;

use AgroEgw\DB;

class Contract
{
	private $contract;

	public function __construct($contract_id)
	{
		$this->contract = self::Get($contract_id);
	}

	public static function New(array $contract)
	{
		$columns = $values = [];
		foreach ($contract as $column => $value) {
			$columns[] = $column;
			if (is_string($value)) {
				$values[] = "'$value'";
			} elseif (is_null($value)) {
				$values[] = 'NULL';
			} else {
				$values[] = $value;
			}
		}
		$columns = implode(', ', $columns);
		$values = implode(', ', $values);

		$sql = "
            INSERT INTO egw_attendance ($columns)
            VALUES ($values)
        ";

		(new DB($sql));
	}

	public static function Update(int $contractID, array $contract)
	{
		$SETS = [];
		foreach ($contract as $column => $value) {
			if (is_string($value)) {
				$SETS[] = "$column = '$value'";
			} elseif (is_null($value)) {
				$SETS[] = "$column = NULL";
			} else {
				$SETS[] = "$column = $value";
			}
		}
		$SET = implode(', ', $SETS);
		$sql = "
            UPDATE egw_attendance
            SET $SET 
            WHERE id = $contractID
        ";
		(new DB($sql));
	}

	public function shouldHoursOn(string $dayName)
	{
		if ($this->contract) {
			$should = $this->contract[$dayName] / 60 / 60;

			return $should;
		}

		return 0;
	}

	public function shouldOn($id, $date)
	{
		$data = \Attendance\TimeAccount::oldAlgo($id);

		if (!empty($data['days'])) {
			foreach ($data['days'] as $datum => $meta) {
				if ($date == $datum) {
					return $meta['should'];
				}
			}
		}

		return 0;
	}

	public static function Get($contract_id, $onlyActive = false)
	{
		$contracts = new Contracts($onlyActive);
		$contract = $contracts->Load($contract_id);
		if (empty($contract)) {
			return false;
		}
		$contract = array_values($contract)[0] ?? false;

		return $contract;
	}

	public static function Delete($id)
	{
		(new DB("
        	DELETE FROM egw_attendance 
        	WHERE id = $id
        "));
	}
}
