<?php

namespace Attendance;

use AgroEgw\DB;

class Location
{

    static function all(){
        // Fetch locations from the database to populate the dropdown
        $locations = DB::GetAll("SELECT * FROM egw_attendance_locations"); // Assuming this returns an array of location data
        return $locations;
    }

    static function getUsers($locationID){
        return false; # return list of users from location
    }

    static function add($location) {
       // Sanitize input
       $location = htmlspecialchars($location, ENT_QUOTES, 'UTF-8');
            
       // Add location to the database
       DB::Run("INSERT INTO egw_attendance_locations(location, users) VALUES('$location','[]')");
    }
}
