<?php

		$setup_info['attendance']['name'] = 'attendance';
		$setup_info['attendance']['title'] = 'attendance';
		$setup_info['attendance']['version'] = '21.1.001';
		$setup_info['attendance']['app_order'] = 10;
		$setup_info['attendance']['tables'] = ['egw_attendance', 'egw_attendance_meta'];
		$setup_info['attendance']['enable'] = 1;

		//The application's hooks rergistered.
		$setup_info['attendance']['hooks']['admin'] = 'attendance_hooks::all_hooks';
		$setup_info['attendance']['hooks']['sidebox_menu'] = 'attendance_hooks::all_hooks';        /* Dependencies for this app to work */
		$setup_info['attendance']['hooks']['search_link'] = 'attendance_hooks::search_link';

		$setup_info['attendance']['depends'][] = [
			'appname'  => 'api',
			'versions' => ['16.1'],
		];
