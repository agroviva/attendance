<?php

namespace Attendance;

class Graph
{
	public static function Render($graph, $needsPermission = false)
	{
		$isManager = Core::isManager($GLOBALS['egw_info']['user']['account_id']);

		if ($needsPermission && !$isManager) {
			$graph = 'errors.notfound';
		}

		$graph = explode('.', $graph);
		$graph[count($graph) - 1] .= '.php';
		$graph = implode('/', $graph);

		include APPDIR.'/graph/'.$graph;
	}

	public static function Route()
	{
		$_GET['cd'] = 'no';
		// Dump($_REQUEST);
		if (self::hasRoute('/attendance/graph/tracker/')) {
			self::Render('tracker');
		} elseif (self::hasRoute('/attendance/graph/manage/')) {
			self::Render('contracts', true);
		} elseif (self::hasRoute('/attendance/graph/holidays/')) {
			self::Render('holidays', true);
		} elseif (self::hasRoute('/attendance/graph/timesheet/')) {
			self::Render('timesheet', true);
		} elseif (self::hasRoute('/attendance/graph/settings/')) {
			self::Render('settings');
		} elseif (self::hasRoute('/attendance/graph/create/')) {
			self::Render('create');
		} else {
			self::Render('tracker');
		}
	}

	public static function hasRoute($route)
	{
		$REQUEST_URI = explode('?', $_SERVER['REQUEST_URI']);
		$REQUEST_URI = $REQUEST_URI[0];
		$REQUEST_URI = str_replace('index.php', '', $REQUEST_URI);

		if ($REQUEST_URI == '/egroupware'.$route || $REQUEST_URI == $route) {
			return true;
		}

		return false;
	}
}
