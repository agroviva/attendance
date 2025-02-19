var usersAll = setInterval(function(){  allUsers(); }, 30000);

var reloadGUI = setInterval(function(){
	window.location.reload();
}, 1800000);

function websocket() {
	// Websocket
	var websocket_server = new WebSocket("ws://localhost:9090/");
	websocket_server.onopen = function(e) {
		websocket_server.send(
			JSON.stringify({
				'type':'socket',
			})
		);
	};
	websocket_server.onerror = function(e) {
		// Errorhandling
	}
	websocket_server.onmessage = function(e)
	{
		window.nfc_data = JSON.parse(e.data);
		switch(nfc_data.type) {
			case 'nfc':
				var nfc = JSON.parse(nfc_data.data);
				var nfcid = nfc.nfc_id;
				open_user(nfcid);
				console.log(nfcid);
				break;
		}
	}
}

// websocket();

function allUsers() {

	jQuery.ajax({
	  url: "/egroupware/attendance/",
	  method: "POST",
	  data: "route=tracker&all=users",
	  success: function(res){
	  		var user_data = res;

		  	for (i = 0; i < user_data.length; i++) { 
				jQuery('#user_'+user_data[i]['user_id']+' .status_back').css({background: user_data[i]['color']});
			}
	  },
	  error: function(){
	  	console.log('Something went wrong!');
	  },
	});

}

function checkOpen(premission, user_id, contact_id, username, color){

	var user_ui = jQuery('.modal#user_ui');


	function slide() {
		if (!jQuery('#user_ui.modal').hasClass('in')) {jQuery('#user_ui.modal').addClass('in');}
		if (!jQuery('.modal-backdrop').hasClass('in')) {jQuery('.modal-backdrop').addClass('in');}
		if (!jQuery('body').hasClass('no-overflow')) {jQuery('body').addClass('no-overflow');}
		boxResize();
	}

	if (premission == 'denied') {
		var winWidth = window.innerWidth;
        var winHeight = window.innerHeight;
        
        jQuery('#dlgbox').css({left: (winWidth/2) - 480/2 + 'px', top: '150px', display: 'block'});

	} else if (premission == 'granted') {
		jQuery('#user_ui.modal #password').css('display', 'none'); 
		jQuery('#user_ui.modal .content-box').css('display','block');

		slide();
	}
	else if (premission == 'password') {
		var userID = '#user_'+user_id;
		var image_src = jQuery(userID+" #user_image").attr('src');
		jQuery('#user_ui.modal #password #user_image').attr('src', image_src);
		jQuery('#user_ui.modal #password .user_details p span').html(username);
		jQuery('#user_ui.modal #password .user').attr("id", "user_"+user_id);
		jQuery('#user_ui.modal .content-box').css('display', 'none');
		jQuery('#user_ui.modal #password .user .status_back').css('background', color);

		slide();
	}
}

function closeBox(){

	var args = {display: 'none'}; 

    jQuery('#user_ui').removeClass("in");
    jQuery('#user_ui.modal .user').removeAttr('id');
    jQuery('body').removeClass('no-overflow');
    // jQuery('#user_ui #password').css(args);
    // jQuery('#user_ui .content-box').css(args);
    jQuery('.modal-backdrop').css(args);
    jQuery('#dlgbox').css(args);
    jQuery('#user_ui #message').css(args);
    window.nfcid = false;

    if (typeof(repeatOpenuser) != 'undefined') {
    	clearInterval(repeatOpenuser);
    	window.repeatOpenuser = false;
    }
}

function update_user_data(user_id,username,contact_id,last_modified,made,rest_vac,should,start,status,time_account,color,disable=false) {

	var userID = '#user_'+user_id;
	if (disable) {
		jQuery('p.should').css('display','none');
		jQuery('p.timeaccount').css('display','none');
	} else {
		jQuery('p.should').css('display','inline-block');
		jQuery('p.timeaccount').css('display','inline-block');
	}
	var image_src = jQuery(userID+" #user_image").attr('src');
	jQuery('#user_ui #data-logs #user_image').attr('src', image_src);
	jQuery('#user_ui .status_back').css({background: color});
	jQuery('#user_ui .user_name > span').html(username);
	jQuery('#user_ui #data-logs .user_details p span').html(username);
	jQuery('#user_ui #data-logs .user').attr('id', 'user_'+user_id);

	jQuery('#user_ui #user_data #start').html(start);
	jQuery('#user_ui #user_data #should').html(should);
	jQuery('#user_ui #user_data #vacation').html(rest_vac);
	jQuery('#user_ui #user_data #made').html(made);
	jQuery('#user_ui #user_data #timeaccount').html(time_account);
	jQuery('#user_ui #user_data #last_change').html(last_modified);

	jQuery(userID+' .status_back').css({background: color});
	jQuery('#content '+userID+' .status_back').css({background: color});

	
}

function logInOut() {

	if (userSession) {

		var params = "route=tracker&";

		if ((typeof nfcid != 'undefined') && (nfcid != false)) {
			params += 'type=user&checkNfc='+nfcid+'&status='+userStatus;
		} else {
	 		params += 'type=user&check='+userSession+'&status='+userStatus;
	 	}

		jQuery.ajax({
		  	url: "/egroupware/attendance/",
			method: "POST",
			data: params,
			success: function(res){
				var user_data = res;

				jQuery('#user_ui #message').html(user_data['message']);
				jQuery('#user_ui #message').css('borderLeft', '10px solid '+user_data['color']);
				jQuery('#user_ui #message').css('display', 'block');
				window.userStatus = user_data['status'];
				jQuery('.content-box .buttonbox .submit').html(user_data['text_status']);
				  	
				if ((typeof nfcid != 'undefined') && (nfcid != false)) {
					open_user(false, false, nfcid);
				} else if (typeof userPassword !== 'undefined') {
					open_user(userSession, userPassword);
				} else {
				  	open_user(userSession);
				}
			},
			error: function(){
			  	console.log('Something went wrong!');
			},
		});

	
	}

}

function checkPassword() {
	var user_ui = document.getElementById('user_ui');
	user_ui.querySelector('#password .incorrect').style.display = 'none';
	var user_id = user_ui.querySelector('#password .user').id;
	user_id = user_id.split("_").pop();
	var password = user_ui.querySelector('#pass').value;
	open_user(user_id, password);
}

function user_session(userID, status) {
	window.userSession = userID;
	window.userStatus  = status;
}

function open_user(user, password, nfcid) {

	jQuery("#pass").val('');

	if (password) {
		window.userPassword  = password;
		password = '&password='+password;
	} else {
		password = '';
	}

	if (user || nfcid) {

		var params = "route=tracker&";

		if (user == false) {
			window.nfcid = nfcid;
			params += 'type=user&nfcid='+nfcid+password;
		} else {
			params += 'type=user&user='+user+password;
		}

		jQuery.ajax({
			url: "/egroupware/attendance/",
			method: "POST",
			data: params,
			success: function(res){
			  	var user_data = res;

			  	if (user_data['premission'] == 'denied') {
		  			checkOpen('denied');
			  	}
			  	else if (user_data['premission'] == 'granted') {
			  		var disable_n;
			  		if (user_data['disable'] == 1) {
			  			disable_n = true;
			  		} else {
			  			disable_n = false;
			  		}
			  		update_user_data(user_data['user_id'],user_data['username'],user_data['contact_id'],user_data['last_modified'],user_data['made'],user_data['rest_vac'],user_data['should'],user_data['start'],user_data['status'],user_data['time_account'],user_data['color'],disable_n);
			  		user_session(user_data['user_id'], user_data['status']);
			  		jQuery('.content-box .buttonbox .submit').html(user_data['text_status']);
			  		checkOpen('granted');
			  		if ((typeof repeatOpenuser == 'boolean') || (typeof repeatOpenuser == 'undefined')) {
				  		window.repeatOpenuser = setInterval(function(){ 
				  			if (userPassword) {
				  				open_user(userSession, userPassword);
				  			} else if (typeof nfcid != 'undefined') {
				  				open_user(false,false,user_data['nfc_id']);
				  			} else {
				  				open_user(userSession);
				  			}
				  			
				  		}, 6000);
				  	}
			  	}
			  	else if (user_data['premission'] == 'password'){
			  		checkOpen('password',user_data['user_id'], user_data['contact_id'], user_data['username'],user_data['color']);
			  	}

			  	if (user_data['pass_check'] == 'incorrect'){
			  	 	document.getElementById('user_ui').querySelector('#password .incorrect').style.display = 'block';
			  	} else if(user_data['pass_check'] == 'ok') {

			  	} else {
			  		window.userPassword = false;
			  	}
			},
			error: function(){
			  	console.log('Something went wrong!');
			},
		});
	}
}

function boxResize(){
	var elSelector;
	if (jQuery('#user_ui').hasClass('in')) {
		if (jQuery('#user_ui #password').css('display') != 'none') {
			elSelector = jQuery('#password').html();
		}
	 	if (jQuery('#user_ui .content-box').css('display') != 'none') {
	 		elSelector = jQuery('.content-box')[0];
		}

		var height = jQuery(elSelector).innerHeight();

		if (innerWidth <= 1000) {
			check = height >= innerHeight;
			if (check) {
				jQuery('.modal-dialog').css('height',height);
			} else {
				jQuery('.modal-dialog').css('height','100%');
			}
		} else {
			jQuery('.modal-dialog').css('height','auto');
		}
	}
}

jQuery(window).ready(function(){
	boxResize();
	jQuery(window).resize(boxResize);
});
