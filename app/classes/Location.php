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
        $location = DB::Get("SELECT * FROM egw_attendance_locations WHERE id = $locationID");
        return $location;
    }

    static function updateLocation($userID, $locationID){
        // Get existing users and merge new ones
        $location = DB::Get("SELECT users FROM egw_attendance_locations WHERE id = $locationID");
        if ($location) {
            if (static::UserInLocation($userID, $locationID)) {
                $users = json_decode($location['users'], true);
                foreach ($users as $key => $user) {
                    if ($user == $userID) {
                        unset($users[$key]); # remove user
                    }
                }
            } else {
                $users = json_decode($location['users'], true);
                $users[] = $userID; # add user
            }
            // Update the users array
            $users_json = json_encode($users);
            DB::Run("UPDATE egw_attendance_locations SET users = '$users_json' WHERE id = $locationID");
            return true;
        }
        return false;
    }

    static function UserInLocation($userID, $locationID) {
        $location = Location::getUsers($locationID);
        if (!empty($location)) {
            $users = json_decode($location['users'], true);
            foreach ($users as $key => $user) {
                if ($user == $userID) {
                    return true;
                }
            }
        }
        return false;
    }

    static function add($location) {
       // Sanitize input
       $location = htmlspecialchars($location, ENT_QUOTES, 'UTF-8');
            
       // Add location to the database
       DB::Run("INSERT INTO egw_attendance_locations(location, users) VALUES('$location','[]')");
    }
}
