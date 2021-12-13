var showMore = jQuery('#show').html();
// showMore.addEventListener('click', display);

jQuery(function(e){
	$('input[name="daterange"]').daterangepicker({
		opens: 'right',
		locale: {
		    format: "DD.MM.YYYY",
		    customRangeLabel: "Benutzerdefiniert",
		    firstDay: 1 // Monday as the first day of the week in the ui
		},
		ranges: {
		   'Heute': [moment(), moment()],
		   'Gestern': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
		   'Diese Woche': [moment().startOf('isoWeek'), moment()],
		   'Letzte Woche': [moment().subtract(1, 'isoWeek').startOf('isoWeek'), moment().subtract(1, 'isoWeek').endOf('isoWeek')],
		   'Diesen Monat': [moment().startOf('month'), moment()],
		   'Letzten Monat': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')],
		   'Dieses Jahr': [moment().startOf('year'), moment()],
		   'Letztes Jahr': [moment().subtract(1, 'year').startOf('year'), moment().subtract(1, 'year').endOf('year')]
		}
	}, function(start, end, label) {
		console.log("A new date selection was made: " + start.format('YYYY-MM-DD') + ' to ' + end.format('YYYY-MM-DD'));
	});

	/**
	 * Update the results when bottom of the page is reached
	 * @return void
	 */
	function scrollFunction(){
		var $win = $(window);

		$win.scroll(function () {
			if ($win.height() + $win.scrollTop() == $(document).height()) {
				ResultUpdating(true);
			}
		});
	}
	scrollFunction();
});

var filterSettings = {
	"pageCount": 1
};
var $activeRange = "thismonth";


function reports(id) {


	var users = jQuery(".list #user .dropdown-filter span");
	var selectedUser = jQuery("#user .dropdown-filter .selected");
	var dates = jQuery('.custom_date input');
	var range = window.$activeRange;
	var owner = selectedUser.attr("id");

	dates = dates.val();

	var params = "?type="+id+"&owner="+owner;
	dates = $('input[name=daterange]').val();
	params += "&date="+range+"&dates="+dates;

	var categories = [];

	jQuery("#category .dropdown-filter").find(".selected").each(function(){
		var str = jQuery(this).attr("id");
		categories.push(str.replace("cat_", ""));
	});

	if (categories.length > 0) {
		params += "&categories=" + btoa(categories);
	}

	var url = "/egroupware/attendance/app/api/export/"+params;

	if (id == "excel"){
		var files = [];

		if (selectedUser.length == 1) {
			files.push(url);
			multiDownload(files);
		} else {
			users.each(function(index, elem){
				url = "/egroupware/attendance/app/api/export/?type="+id+"&owner="+elem.id+"&date="+range+"&dates="+dates;
				files.push(url);
			});
			multiDownload(files);
		}
	} else if (id != 1) {
		if (owner) {
			window.open(url, "_blank");
		} else {
			alert("Um einen PDF-Export erstellen zu können, wählen Sie bitte einen Benutzer aus!")
		}
	}
}

function checkDrop(el) {

	if (!el) {
		el = false;
	}

	jQuery('.dropdown-filter').each(function(element, count){
		if (el != element) {
		    if (element.className != 'dropdown-filter') {
		    	element.className = 'dropdown-filter';
		    }
		}
	});
}

$('input[name=daterange]').change(function() {
	ResultUpdating();
});

function ResultUpdating(increment = false) {
	if (increment) {
		window.filterSettings.pageCount++;
	} else {
		window.filterSettings.pageCount = 1;
	}
	var filter_frames = jQuery('.filter_frame');
	var query = [];
	for (var i = filter_frames.length - 1; i >= 0; i--) {
		var filter_frame = filter_frames[i];
		if (filter_frame.id != 'export' && filter_frame.id != 'count') {
			element = jQuery('span.selected',filter_frame);
			var arr = [];
			for (var j = element.length - 1; j >= 0; j--) {
				arr[j] = element[j].id;
			}
			query[filter_frame.id] = arr;
		}
	}
	jQuery('#preloader').css('display','table');
	var category = btoa(query.category), user = query.user;
	var range = window.$activeRange;

	var params = "route=timesheet&category="+category+"&user="+user+"&pageCount="+window.filterSettings.pageCount;

	if (range == "customDate") {
		var dates = $('input[name=daterange]').val();
		params += "&dates="+dates;
	} else {
		params += "&date="+range;
	}

 	jQuery.ajax({
	  	url: "/egroupware/attendance/",
		method: "POST",
		data: params,
		success: function(result){
		  	if (result == '404') {
		  		throw new Error('No Results');
		  	} else {
		  		if (window.filterSettings.pageCount > 1) {
		  			jQuery('#TimeEntries li.timeSet').last().after(result['html']);
		  		} else {
		  			jQuery('#TimeEntries').html(result['html']);
		  			jQuery('#count .button').html(result['count']);
			  		jQuery('#start_date').val(result['start_time']);
			  		jQuery('#end_date').val(result['end_time']);
		  		}
		  	}
		  	jQuery('#preloader').css("display","none");
		  	
		},
		error: function(){
		  	jQuery.log('Something went wrong!');
		},
	});
}

function pdfCheck(){
	switch(window.$activeRange){
		case "thismonth":
		case "lastmonth":
		case "thisyear":
		case "lastyear":
			// $(".pdfExport").removeClass("disabled");
			break;
		default:
			if (!$(".pdfExport").hasClass("disabled")) {
				// $(".pdfExport").addClass("disabled");
			}
			break;
	}
}

jQuery('.list').click(function (e) {

	var $filter_frame = jQuery(this).find('.filter_frame');
	var $dropdown_filter = $filter_frame.find('.dropdown-filter');
	jQuery(".dropdown-filter").removeClass("active");
	$dropdown_filter.toggleClass('active');
	console.log(this);
});

jQuery(".exportOptions div").click(function(){
	reports(this.id);
});

jQuery('.dropdown-filter span').click(function(){
	var $type =this.parentNode.parentNode.id;

	if ($type == "user") {
		pdfCheck();
		jQuery(this.parentNode).find('span').removeClass("selected");
		jQuery(this).toggleClass("selected");
	} else if ($type == "category") {
		jQuery(this).toggleClass("selected");
	}
	ResultUpdating();
})

jQuery(document).click(function(e) {
    var target = e.target; //target div recorded
 	console.log(jQuery(target));
    if (!jQuery(target).hasClass('filter_frame') && !jQuery(target).hasClass('down_white_icon') && !jQuery(target).hasClass('button') && !jQuery(target).parent().hasClass('dropdown-filter')) {
        jQuery('.list .dropdown-filter').removeClass("active");
    }
})

jQuery(function(e){
	var $ranges = $(".daterangepicker .ranges li");
	var rangesIDs = ["today", "yesterday", "thisweek", "lastweek", "thismonth", "lastmonth", "thisyear", "lastyear", "customDate"];
	
	$ranges.each(function(index, elem){
		$(elem).attr("id", rangesIDs[index]);
	});

	$(".daterangepicker .ranges li").click(function(){
		window.$activeRange = $(this).attr("id");
		var selectedUser = jQuery("#user .dropdown-filter .selected");
		if (selectedUser.length){
			pdfCheck();
		}
	});
});