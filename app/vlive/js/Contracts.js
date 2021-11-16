var contracts = {
	section_html: '<div class="form-section">'+$(".form-section").html()+'</div>'
};

var weekdays = ["monday", "tuesday", "wednesday", "thursday", "friday", "saturday", "sunday"];

$(document).on("click", ".weekdays span", function(){
	$(this).toggleClass("selected");
	clearDays();
});

$(document).on("change", ".form-section select", function(){
	var val = $(this).val();
	var elem = $(this).parent().parent().find(".datepicker");
	if (val > 1) {
		elem.removeAttr("disabled");
	} else {
		elem.attr("disabled", true);
	}
});

function newRule(e){
	e.preventDefault();
	$(".form-sections .sections").append(contracts.section_html);
	renewEvents();
    clearDays();
}

$("form").keypress(function(e){
	if(e.keyCode == 13 || e.keyCode == 169) { 
		e.preventDefault();
		return; 
	}
});

function clearDays(){
	for (var day in weekdays) {
		if ($(".weekdays span."+weekdays[day]).hasClass("selected")) {
			$(".weekdays span."+weekdays[day]).each(function(key, elem){
				if (!$(elem).hasClass("selected")) {
					$(elem).addClass("disabled");
				}
			});
		} else {
			$(".weekdays span."+weekdays[day]).removeClass("disabled");
		}
	}
}

function renewEvents(){
	$("input.timepicker").datetimepicker({
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
}

function NewContract(){
	document.querySelector('form').reset();
	$(".form-sections .sections").html(contracts.section_html).parent().css("display", "");
	$(".users").html("");
	$(".form-user input").removeClass("hidden");
	$(".form-user .username").remove();
	clearDays();
	renewEvents();
}

$('input#norules').change(function() {
    if (this.checked) {
        $(".form-sections").slideUp("fast");
    } else {
        $(".form-sections").slideDown("fast");
    }
});

$("#securityLevel").change(function(){
	if ($(this).val() == "password") {
		$("#passwordField").removeAttr("disabled");
	} else {
		$("#passwordField").val("").attr("disabled", "true");

	}
});

$("button.EditContract").click(function(e){
	NewContract();
	var elem = $(this).parent().parent();
	$.post("/egroupware/attendance/", {
		route: "contract.user.get",
		uid: elem.data("account"),
		contract_id: elem[0].id
	}, function(data){
		var user_html = data.user_html;
		var user = data.user;
		var contract = data.contract;

		user_html = user_html.replaceAll(user);
		$(".form-user label").after(user_html);
		$(".form-user input, .users").addClass("hidden");

		$("input#vacationInput").val(contract.annualLeave);
		$("input#extraVacInput").val(contract.extra_vacation);
		$("input#startOfContract").val(contract.start);
		$("input#endOfContract").val(contract.end);
		if (contract.access_granted == "yes") {
			$("#securityLevel").val("access_granted");
		} else if(contract.access_denied == "yes"){
			$("#securityLevel").val("access_denied");
		} else {
			$("#securityLevel").val("password");
			$("#passwordField").val(contract.password);
			$("#passwordField").removeAttr("disabled");
		}

		if (data.ruleSection) {
			$(".form-sections .sections").html(data.ruleSection);
		}

		$("#stepwiseIN").val(data.meta_data.interval.in.stepwise);
		$("#roundingPointIN").val(data.meta_data.interval.in.rounding_point);

		$("#stepwiseOUT").val(data.meta_data.interval.out.stepwise);
		$("#roundingPointOUT").val(data.meta_data.interval.out.rounding_point);


		if (contract.norules == 1) {
			
			$("#norules").click();
		}
		$("#NewContract").modal('show');
		clearDays();
		renewEvents();
	});
});

$("button.DeleteContract").click(function(e){
	var elem = $(this).parent().parent();

	var action = {
		title: "Sind Sie sicher?",
		text: "Wollen Sie diesen Vertrag löschen?",
		icon: "info",
		buttons: {
			cancel: "Nein",
			confirm: {
				text: "Ja",
				closeModal: false
			}
		},
		showLoaderOnConfirm: true,
	};

	swal(action).then((pressedYes) => {
		if (pressedYes) {
			$.post("/egroupware/attendance/", {
				route: "contract.user.delete",
				uid: elem.data("account"),
				contract_id: elem[0].id
			}, function(data){
				var icon = data.response == "success" ? "success" : "warning";

				swal(data.msg, {
					icon: icon,
					timer: 2000,
					button: false
				}).then(function() {
					window.location.reload();
				});
			});
		}	
	});
});

$("button.overview").click(function(e){
	var elem = $(this).parent().parent();
	$.post("/egroupware/attendance/", {
		route: "contract.timeaccount.overview",
		uid: elem.data("account"),
		contract_id: elem[0].id
	}, function(data){
		$("#TimeOverview .modal-body").html(data.output);
		$("#TimeOverview").modal('show');
	});
});

$("#userInput").keyup(function(e){

	clearTimeout($.data(this, 'timer'));
    if (e.keyCode == 13)
      SearchUser(true);
    else
      $(this).data('timer', setTimeout(SearchUser, 500));
});

function SearchUser(force = false){
	var searchString = $("#userInput").val();
    if (!force && searchString.length < 3) return; //wasn't enter, not > 2 char

	$.post("/egroupware/attendance/", {
		route: "contract.user.search",
		query: searchString
	}, function(data){
		var users = data.users;
		var html = data.html;
		var outputHtml = "";
		var tempVar = "";
		var count = 0;
		for (var user in users) {
			if (count == 8) {break;}
			user = users[user];
			tempVar = html.replaceAll(user);
			outputHtml += tempVar;
			tempVar = "";
			count++;
		}
		$(".users").html(outputHtml);
	});
}

$('.form-user input').focus( function() {
  $(this).siblings(".users").removeClass('hidden');
});

$('.form-user').focusout( function(e) {
	if (this.contains(e.relatedTarget)) {
        return;
    }
 	$(this).find(".users").addClass('hidden');
});

$(document).on("mousedown", ".form-user .users .username", function(e){
	var clone = $(this).clone();
	$(".form-user label").after(clone);
	$(".form-user input").addClass("hidden");
});

function sortUser(){
	var string = $("#sortUser").val().toLowerCase();

	$("table .username .name").each(function(index, elem){
		if ($(elem).text().toLowerCase().indexOf(string) >= 0) {
			$(elem).parent().parent().removeAttr("hidden");
		} else {
			$(elem).parent().parent().attr("hidden", "true");
		}
	});
}

$("#sortUser").keyup(function(e) {
    // if (e.which == 13) {
       sortUser();
    // }
});

String.prototype.replaceAll = function(data) {
    var target = this;
    return target.replace(/\{(.+?)\}/g, function(string, first){
	  return data[first];
	})
};


$("#SaveButton").click(function(e){
	e.preventDefault();
	e.stopPropagation();

	var elem = $(".form-user label").next();

	var userID = elem.data("uid");
	var contractID = elem.data("id");
	var annualLeave = $("#vacationInput").val();
	var extraVacation = $("#extraVacInput").val();
	var startOfContract = $("#startOfContract").val();
	var endOfContract = $("#endOfContract").val();
	var securityLevel = $("#securityLevel").val();
	var password = $("#passwordField").val();

	var stepwiseIN = $("#stepwiseIN").val();
	var roundingPointIN = $("#roundingPointIN").val();

	var stepwiseOUT = $("#stepwiseOUT").val();
	var roundingPointOUT = $("#roundingPointOUT").val();

	elem = $("#norules");
	var norules = false;

	if (elem.is(":checked")){ norules = true; }

	var rules = {};

	$(".form-section").each(function(index, elem){
		elem = $(elem);
		var TimeInputs = elem.find(".time_inputs");
		var shouldHours = TimeInputs.find(".shouldHours").val();
		var repetition = TimeInputs.find(".repetition").val();
		var validDate = TimeInputs.find(".validDate").val();
		for(day in weekdays){
			day = weekdays[day];
			if (elem.find("."+day).hasClass("selected")) {
				if (!rules[day]) {
					rules[day] = {
						"should": shouldHours,
						"rythm": repetition,
						"valid_on": validDate
					};
				}
			}
		}
	});

	var contract = {
		"route": "contract.user.save",
		"contractID": contractID,
		"userID": userID,
		"annualLeave": annualLeave,
		"extraVacation": extraVacation,
		"startOfContract": startOfContract,
		"endOfContract": endOfContract,
		"securityLevel": securityLevel,
		"password": password,
		"stepwiseIN": stepwiseIN,
		"stepwiseOUT": stepwiseOUT,
		"roundingPointIN": roundingPointIN,
		"roundingPointOUT": roundingPointOUT,
		"norules": norules,
		"rules": rules
	}

	 $.ajax({
        type: "POST",
        url: "/egroupware/attendance/",
        data: contract,
        success: function(data){
        	if (data.response) {
        		var response = data.response;
        		if (response == "success") {
        			console.log(data);
        			swal(data.msg, {
				      icon: "success",
				      timer: 2000,
				      button: false
				    }).then(function(){
				    	window.location.reload();
					});
        		} else {
        			swal(data.msg, {
				      icon: "warning",
				      timer: 2000,
				      button: false
				    }).then(function(){

					});
        		}
        	} else {
        		swal("Es ist ein Fehler aufgetretten!", {
			      icon: "warning",
			      timer: 2000,
			      button: false
			    }).then(function(){

				});
        		console.log(data);
        	}
        },
        error: function(errMsg) {
            swal("Es ist ein Fehler aufgetretten!", {
		      icon: "warning",
		      timer: 2000,
		      button: false
		    }).then(function(){

			});

        }
  	});
});

function ShowAll(){
	jQuery("table tr.expired").removeClass("hidden");
}
