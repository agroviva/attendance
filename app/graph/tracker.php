<?php
use Attendance\Core;
use Attendance\Tracker;
use Attendance\Location;

$so = new attendance_so();

?>
<!DOCTYPE html>
<html>
<head>
	<title>Zeiterfassung</title>
	<meta name="apple-mobile-web-app-title" content="Zeiterfassung">
	<meta name="apple-mobile-web-app-capable" content="yes" />
	<link rel="apple-touch-startup-image" href="/egroupware/attendance/templates/default/images/navbar.png">
	<link rel="icon" type="image/png" href="/egroupware/attendance/templates/default/images/navbar.png" sizes="32x32">
	<link rel="apple-touch-icon" href="/egroupware/attendance/templates/default/images/navbar.png">
	<link rel="apple-touch-icon" sizes="152x152" href="/egroupware/attendance/templates/default/images/navbar.png">
	<link rel="apple-touch-icon" sizes="180x180" href="/egroupware/attendance/templates/default/images/navbar.png">
	<link rel="apple-touch-icon" sizes="167x167" href="/egroupware/attendance/templates/default/images/navbar.png">
	<link rel="apple-touch-icon" sizes="180x180" href="/egroupware/attendance/templates/default/images/navbar.png">
	<link rel="stylesheet" type="text/css" href="/egroupware/attendance/app/css/app.css">
	<link rel="stylesheet" href="/egroupware/attendance/app/css/jquery.numpad.css">

	<script src="/egroupware/attendance/app/js/jquery.js"></script>
	<script src="/egroupware/attendance/app/js/jquery.numpad.js"></script>
	<script src="/egroupware/attendance/app/js/attendance.js"></script>
</head>
<body>
<?php
	$GLOBALS['egw_info']['flags']['app_header'] = lang('attendance - Tracker');
	$user = $GLOBALS['egw_info']['user']['account_id'];

	$check = Core::Self($user);

	$rows = Tracker::ValidContracts(); // take the available users

	?>
<div id='dlgbox'>
    <div id='dlg-header' class="roboto_regular"><?php echo lang('You dont have premission on this page.'); ?></div>
    <div id='dlg-body' class="roboto_regular"><?php echo lang('Only the user itself can open this page'); ?></div>
    <div id='dlg-footer'>
        <button class="dlg_button cancel" onclick='closeBox()'><?php echo lang('close'); ?></button>
    </div>
</div>

<div class="modal fade" id="user_ui" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel">
    <div class="modal-dialog" role="document">
      <div class="modal-content">

	        <div id="password">
	        	<div class='boxcontent'>
                   <div class="user" style="display: inline-block;">
						<img id="user_image" src="/egroupware/attendance/templates/default/images/photo.png">
						<div class="status_back" style="background:green;"></div>
						<div class="user_details">
							<p class="user_name roboto_regular"> <span style="color: #CFCFCF"></span></p>
						</div>
					</div>
					<div id="pass_data" style="display: inline-block;">
                        <div class='security roboto_regular'><?php echo lang('For your security, you must enter your password.'); ?></div>
                        <div class="roboto_regular" style="display: none;">Password:<span style='margin-left: 5px; display: none;'>
                        <input type='password' class='inputpassword' required='required' name='password' id='pass1' onkeypress = 'if (event.keyCode == 13) checkPassword();'><span class="incorrect" style="display: none; color: red;font-weight: bold;float: right;">Incorrect</span></span>
                        </div>
                    </div>
                </div>
               <div id="nmpd1" class="nmpd-wrapper">
				   <div class="nmpd-overlay"></div>
				   <table class="nmpd-grid">
				      <tr>
				         <td colspan="4">
					         <input type="password" id="pass" class="nmpd-display" onkeypress = "if (event.keyCode == 13) checkPassword();">
					         <input type="hidden" class="dirty" value="0">
				         </td>
				      </tr>
				      <tr>
				         <td><button class="numero">7</button></td>
				         <td><button class="numero">8</button></td>
				         <td><button class="numero">9</button></td>
				         <td><button class="cancel" onclick="closeBox();"><?php echo lang('Cancel'); ?></button></td>
				      </tr>
				      <tr>
				         <td><button class="numero">4</button></td>
				         <td><button class="numero">5</button></td>
				         <td><button class="numero">6</button></td>
				         <td><button class="done" onclick="checkPassword();"><?php echo lang('Submit'); ?></button></td>
				      </tr>
				      <tr>
				         <td><button class="numero">1</button></td>
				         <td><button class="numero">2</button></td>
				         <td><button class="numero">3</button></td>
				      </tr>
				      <tr>
				         <td><button class="neg">±</button></td>
				         <td><button class="numero">0</button></td>
				         <td><button class="sep">,</button></td>
				      </tr>
				   </table>
				</div>
	        </div>
	        <div class="content-box">
	        	<div id="message">-------</div>
	        	<div id="data-logs">
	        		<div class="left side">
						 <div class="user" style="float: none;">
							<img id="user_image" src="/egroupware/attendance/templates/default/images/photo.png">
							<div class="status_back" style="background:green;"></div>
							<div class="user_details">
								<p class="user_name roboto_regular"> <span style="color: #CFCFCF"></span></p>
							</div>
						</div>
					</div>

					<div class="right side">
						<div id="user_data"> 
							<p class="started"><?php echo lang('started'); ?></br><span id="start">Value</span></p>
							<p class="should"><?php echo lang('should today'); ?></br><span id="should">Value</span></p>
							<p class="working_time"><?php echo lang('working time today'); ?></br><span id="made">Value</span></p>
							<p class="remaining"><?php echo lang('remaining vacations'); ?></br><span id="vacation">Value</span></p>
							<p class="timeaccount"><?php echo lang('time account'); ?></br><span id="timeaccount">Value</span></p>
							<p class="last_change"><?php echo lang('last change:'); ?></br><span id="last_change">Value</span></p>
						</div>
					</div>
	            </div>
	            <div class="buttonbox" style="padding: 3px; margin: 0;text-align: center;background: none;">
                    <table style="border-collapse: collapse; border-spacing: 0; width: 100%;" cellspacing="0" cellpadding="0">
                        <tbody>
                            <tr>
                            	<td style="text-align: left;">
                                    <h4 style="margin-top: 2px;"></h4>
                                </td>
                                <td>
                                    <button class="dlg_button submit" id="submit" name="login" onclick="logInOut();" type="submit">Login</button>
                                    <button class="dlg_button cancel" name="close" onclick="closeBox();" role="button" style="border-color: #ced0d4;"><?php echo lang('close'); ?></button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
	        </div>
      </div>
    </div>
    <script type="text/javascript">
    	window.onload = boxResize();
    	document.addEventListener('click', function(){
    		boxResize();
		});
    </script>
 </div>
<div class="modal-backdrop fade"></div>

<div id="content">
	<?php
	$locationUsers = Location::getUsersFromSameLocation();
		if (!empty($locationUsers)) {
			?>
			<h3 style="text-align: center;">Standort: <?php echo $locationUsers['location'];?></h2>
			<?php
		}
	?>
	<div id="user_wrapper" class="tracker">

		<?php
			if ($rows) {
				foreach ($rows as $user) {
					// $image_src = (!empty($user['contact_jpegphoto']) ? "data:image/jpeg;base64,".base64_encode($user['contact_jpegphoto'])."" : "/egroupware/attendance/templates/default/images/photo.png");
					$image_src = '/egroupware/api/avatar.php?contact_id='.$user['contact_id'].'&etag=8'; 
					?>
				
				<div class="user_box" id="user_<?php echo $user['account_id']; ?>" onclick="open_user(<?php echo $user['account_id']; ?>);" >
					<div id="user_<?php echo $user['account_id']; ?>" class="user">
						<img id="user_image" src="<?php echo $image_src; ?>" />
						<div class="status_back" style="background:<?php echo $user['color']; ?>;"></div>
						<div class="user_details">
							<p class="user_name roboto_regular"> <span style="color: #CFCFCF"><?php echo $user['fullname']; ?></span></p>
						</div>
					</div>
				</div>

		<?php
				}
			}
		?>
	</div>
</div>
    <?php

	if ($_GET) {
		$nfc_id = $_GET['id']; ?>
			<script>
				
				$(document).ready(function() {
					open_user(false,false,<?php echo $nfc_id?>);
				});

			</script>
		<?php
	}
?>
<script type="text/javascript">
	$('.inputpassword').numpad();
</script>
</body>
</html>