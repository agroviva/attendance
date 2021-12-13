<?php
header('Content-type: text/css', true);
$path = include dirname(__FILE__).'/../default.php';
$version = $path['version'];
echo
"
@import url('/egroupware/attendance/app/v{$version}/css/desktop.css');

@import url('/egroupware/attendance/app/v{$version}/css/mobile.css');
";
?>

/*
	@import url('/egroupware/attendance/css/mobile.css') (max-width: 1024px);

	@import url("/egroupware/attendance/css/desktop.css") (min-width: 1025px);
*/

/* phone-portrait 
@import url(“/egroupware/attendance/css/mobile.css”) only screen
and (min-width:320px )
and (max-width: 568px )
and ( orientation: portrait );

/* phone4 -landscape
@import url(‘/egroupware/attendance/css/mobile.css’) only screen
and (min-width:321px )
and (max-width: 480px)
and (orientation: landscape );

/* phone5 -landscape
@import url(‘/egroupware/attendance/css/mobile.css’) only screen
and (min-width:481px )
and (max-width: 568px)
and (orientation: landscape );

/* ———- Tablets ———- 
@import url(“/egroupware/attendance/css/mobile.css”) only screen
and (min-width: 569px )
and (max-width: 1024px)
and (orientation: portrait);

@import url(‘/egroupware/attendance/css/mobile.css’) only screen
and (min-width: 569px)
and (max-width: 1024px)
and (orientation: landscape);

/* desktop 
@import url(“/egroupware/attendance/css/desktop.css”) only screen
and (min-width: 1025px); */