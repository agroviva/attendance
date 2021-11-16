<?php
namespace Attendance;

use Attendance\Core;
use AgroEgw\DB;

class Manager
{
    static function Exists($uid){
        $result = Core::getMeta(false, "manager", $uid);

        if (!empty($result)) {
            return true;
        }
        return false;
    }

    static function Delete($uid){
        DB::Run("
            DELETE FROM egw_attendance_meta 
            WHERE meta_name = 'manager' 
                AND meta_connection_id = $uid"
        );
    }

    static function New($uid){
        Core::setMeta("manager", $uid, 1);
    }
}
