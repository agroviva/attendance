<?php
ob_start();
?>
{
	"settings":{
		"appname": "attendance",
		"version": 1.1,
		"developer": "Agroviva GmbH",
		"developer_link": "http://www.agroviva.de",
		"depended": ["Core API","EGroupware 14.1 API"],
		"api":{
			"version": 1.1,
			"ajax": "compatible"
		},
		"uses": [
			"iCap JS",
			"iCalReader PHP",
			"Mobile Detect PHP",
			"MPDF Export PHP"
		],
		"category":{
			"parent":{
				"name":"Attendance",
				"color":"",
				"icon":""
			},
			"child": [
				{
					"name":"Work",
					"color":"#56aaff",
					"icon":""
				},
				{
					"name":"Holiday",
					"color":"#999999",
					"icon":""
				},
				{
					"name":"Vacation",
					"color":"#ef9643",
					"icon":""
				},
				{
					"name":"School",
					"color":"#00ff00",
					"icon":""
				},
				{
					"name":"Sickness",
					"color":"#ff5656",
					"icon":""
				}
			]
		},
		"status": [
			{
				"name": "Report",
				"parent": "",
				"admin": "true"	
			},
			{
				"name": "Active",
				"parent": "",
				"admin": "true"
			}
		],
		"database": {
			"table_names": {
				"contracts": "egw_attendance",
				"settings": "egw_settings"
			}
		}
	},

	"domain": [],
	
	"repair": false,

	"update": false,

	"install": false
}
<?php
return ob_get_clean();
