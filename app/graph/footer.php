<!--   Core JS Files   -->
<script src="/egroupware/attendance/material/assets/js/core/popper.min.js" type="text/javascript"></script>
<script src="/egroupware/attendance/material/assets/js/core/bootstrap-material-design.min.js" type="text/javascript"></script>
<script src="/egroupware/attendance/material/assets/js/plugins/moment.min.js"></script>
<!--	Plugin for the Datepicker, full documentation here: https://github.com/Eonasdan/bootstrap-datetimepicker -->
<script src="/egroupware/attendance/material/assets/js/plugins/bootstrap-datetimepicker.js" type="text/javascript"></script>
<!--  Plugin for the Sliders, full documentation here: http://refreshless.com/nouislider/ -->
<script src="/egroupware/attendance/material/assets/js/plugins/nouislider.min.js" type="text/javascript"></script>
<!--	Plugin for Sharrre btn -->
<script src="/egroupware/attendance/material/assets/js/plugins/jquery.sharrre.js" type="text/javascript"></script>
<!-- Control Center for Material Kit: parallax effects, scripts for the example pages etc -->
<script src="/egroupware/attendance/material/assets/js/material-kit.js?v=2.0.4" type="text/javascript"></script>
<script type="text/javascript" src="/egroupware/attendance/app/js/sweetalert.min.js"></script>
<script type="text/javascript" src="/egroupware/attendance/app/js/Contracts.js"></script>
<script type="text/javascript">
	function processRelativeTime$2(number, withoutSuffix, key, isFuture) {
		var format = {
			'm': ['eine Minute', 'einer Minute'],
			'h': ['eine Stunde', 'einer Stunde'],
			'd': ['ein Tag', 'einem Tag'],
			'dd': [number + ' Tage', number + ' Tagen'],
			'M': ['ein Monat', 'einem Monat'],
			'MM': [number + ' Monate', number + ' Monaten'],
			'y': ['ein Jahr', 'einem Jahr'],
			'yy': [number + ' Jahre', number + ' Jahren']
		};
		return withoutSuffix ? format[key][0] : format[key][1];
	}
	moment.defineLocale('de', {
		months: 'Januar_Februar_März_April_Mai_Juni_Juli_August_September_Oktober_November_Dezember'.split('_'),
		monthsShort: 'Jan._Feb._März_Apr._Mai_Juni_Juli_Aug._Sep._Okt._Nov._Dez.'.split('_'),
		monthsParseExact: true,
		weekdays: 'Sonntag_Montag_Dienstag_Mittwoch_Donnerstag_Freitag_Samstag'.split('_'),
		weekdaysShort: 'So._Mo._Di._Mi._Do._Fr._Sa.'.split('_'),
		weekdaysMin: 'So_Mo_Di_Mi_Do_Fr_Sa'.split('_'),
		weekdaysParseExact: true,
		longDateFormat: {
			LT: 'HH:mm',
			LTS: 'HH:mm:ss',
			L: 'DD.MM.YYYY',
			LL: 'D. MMMM YYYY',
			LLL: 'D. MMMM YYYY HH:mm',
			LLLL: 'dddd, D. MMMM YYYY HH:mm'
		},
		calendar: {
			sameDay: '[heute um] LT [Uhr]',
			sameElse: 'L',
			nextDay: '[morgen um] LT [Uhr]',
			nextWeek: 'dddd [um] LT [Uhr]',
			lastDay: '[gestern um] LT [Uhr]',
			lastWeek: '[letzten] dddd [um] LT [Uhr]'
		},
		relativeTime: {
			future: 'in %s',
			past: 'vor %s',
			s: 'ein paar Sekunden',
			ss: '%d Sekunden',
			m: processRelativeTime$2,
			mm: '%d Minuten',
			h: processRelativeTime$2,
			hh: '%d Stunden',
			d: processRelativeTime$2,
			dd: processRelativeTime$2,
			M: processRelativeTime$2,
			MM: processRelativeTime$2,
			y: processRelativeTime$2,
			yy: processRelativeTime$2
		},
		dayOfMonthOrdinalParse: /\d{1,2}\./,
		ordinal: '%d.',
		week: {
			dow: 1, // Monday is the first day of the week.
			doy: 4 // The week that contains Jan 4th is the first week of the year.
		}
	});
	$('.timepicker').datetimepicker({
        locale: 'de',
        format: 'HH:mm',
        icons: {
            time: "fa fa-clock-o",
            date: "fa fa-calendar",
            up: "fa fa-chevron-up",
            down: "fa fa-chevron-down",
            previous: 'fa fa-chevron-left',
            next: 'fa fa-chevron-right',
            today: 'fa fa-screenshot',
            clear: 'fa fa-trash',
            close: 'fa fa-remove'
        }
    });
    $('.datepicker').datetimepicker({
        locale: 'de',
        format: 'DD.MM.YYYY',
        icons: {
            time: "fa fa-clock-o",
            date: "fa fa-calendar",
            up: "fa fa-chevron-up",
            down: "fa fa-chevron-down",
            previous: 'fa fa-chevron-left',
            next: 'fa fa-chevron-right',
            today: 'fa fa-screenshot',
            clear: 'fa fa-trash',
            close: 'fa fa-remove'
        }
    });
</script>
</body>
</html>