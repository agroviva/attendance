<?php

namespace Attendance;

class Ajax
{
    public static function Response()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['route'])) {
            header('Content-Type: application/json; charset=utf-8');
            $route = $_POST['route'];
            unset($_POST['route']);

            $file = APPDIR."/controller/$route.php";
            if (file_exists($file)) {
                try {
                    require $file;
                } catch (\Exception $e) {
                    throw new \Exception($e->getMessage());
                }
            } else {
                throw new \Exception("Controller for $route not found!");
            }
            die();
        }
    }
}
