<?php

namespace Attendance;

use AgroEgw\DB;
use attendance_so;

class Contracts
{
    private $allContracts;
    private $activeContracts;
    private $inactiveContracts;

    private $ContractID;

    public function __construct($onlyActive = false)
    {
        $this->onlyActive = $onlyActive;
    }

    public function Load($ContractID = false)
    {
        $this->ContractID = $ContractID;

        $this->initialize_contract();
        if ((new attendance_so())->check_disabled_contracts() && !$this->onlyActive) {
            return $this->allContracts;
        } else {
            return $this->activeContracts;
        }
    }

    public function renderAccessType()
    {
        foreach ($this->allContracts as $key => $row) {
            if ($row['access_granted'] == 'yes') {
                $this->allContracts[$key]['sec_type'] = 'Access granted';
            } elseif ($row['access_denied'] == 'yes') {
                $this->allContracts[$key]['sec_type'] = 'Access denied';
            } else {
                $this->allContracts[$key]['sec_type'] = 'Password protection';
            }

            $this->allContracts[$key]['total_week_hours'] = $row['total_week_hours'] / 60 / 60; // To show it in hours
        }
    }

    public function loadVacations()
    {
        Categories::Get();
        foreach ($this->allContracts as $key => $row) {
            $this->allContracts[$key]['time_account'] = TimeAccount::Get($row['id'])['timeaccount'];
        }

        $this->allContracts = (new Vacation())->set($this->allContracts)->get();
    }

    public function detectExpiredContracts()
    {
        foreach ($this->allContracts as $key => $row) {
            if ((!$row['end']) or ($row['end'] >= date('Y-m-d'))) {
                $this->activeContracts[] = $row;
            } else {
                $this->allContracts[$key]['status'] = 'expired';
                $this->inactiveContracts[] = $row;
                // $row['rest_vac'] = "-";
                // $row['time_account'] = "-";
            }
        }
    }

    public function initialize_contract()
    {
        $sql = "
            SELECT A.*, B.meta_data AS norules FROM egw_attendance A 
            LEFT JOIN egw_attendance_meta B ON A.id = B.meta_connection_id 
            WHERE 1=1
        ";

        if ($this->ContractID) {
            $sql .= ' AND A.id = '.$this->ContractID;
        }
        $sql .= ' GROUP BY A.id ORDER BY A.id';

        $this->allContracts = (new DB($sql))->FetchAll();
        if ($this->allContracts) {
            $this->renderAccessType();
            $this->loadVacations();
            $this->detectExpiredContracts();
        }
    }
}
