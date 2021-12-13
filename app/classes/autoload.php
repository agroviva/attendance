<?php

spl_autoload_register(function ($class) {
    $parts = explode('\\', $class);
    if (array_shift($parts) != 'Attendance') {
        return;
    }	// not our prefix

    $path = __DIR__.'/'.implode('/', $parts).'.php';

    if (file_exists($path)) {
        require_once $path;
    }
});
