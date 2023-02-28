<?php
use AgroEgw\DB;
use Attendance\Core;
use Attendance\Tracker;

class TrackerController
{
	public function __construct()
	{
		$this->so = new attendance_so();
		$this->bo = new attendance_bo();
		$this->db = clone $GLOBALS['egw']->db;
	}

	public function initData()
	{
		if (isset($_POST['all']) == 'users') {
			$this->allUsers();
		}

		// This will check if we want new data from timesheet
		if (isset($_POST['type']) == 'timesheet' && isset($_POST['page']) && isset($_POST['range'])) {
			$this->timesheet($_POST['page'], $_POST['range']); // here we call the timesheet function
		}

		if (isset($_POST['type']) == 'user') {
			if (isset($_POST['user'])) {
				if (!isset($_POST['password'])) {
					$_POST['password'] = false;
				}
				$user_data = $this->get_user_access($_POST['user'], $_POST['password']);
				echo json_encode($user_data);
			} elseif (isset($_POST['get']) && $_POST['get'] == 'self') {
				if ($_POST['action'] == 'toggle') {
					$this->logInOut($GLOBALS['egw_info']['user']['account_id'], false);
				}
				$user_data = $this->get_user_access($GLOBALS['egw_info']['user']['account_id'], false);
				echo json_encode($user_data);
			} elseif (isset($_POST['nfcid'])) {
				$user_data = $this->get_user_access($_POST['user'], $_POST['password'], $_POST['nfcid']);
				echo json_encode($user_data);
			} elseif (isset($_POST['check'])) {
				$user_data = $this->logInOut($_POST['check'], $_POST['status']);
				echo json_encode($user_data);
			} elseif (isset($_POST['checkNfc'])) {
				$user_data = $this->logInOut(false, $_POST['status'], $_POST['checkNfc']);
				echo json_encode($user_data);
			}
		}
	}

	public function allUsers()
	{
		$rows = Tracker::ValidContracts();

		$users = [];

		if ($rows) {
			foreach ($rows as $user) {
				$userdata['user_id'] = $user['account_id'];
				$userdata['contact_id'] = $user['contact_id'];
				$userdata['name'] = $user['n_given'];
				$userdata['surname'] = $user['n_family'];
				$userdata['fullname'] = $user['fullname'];
				$userdata['status'] = $user['status'];
				$userdata['color'] = $user['color'];

				$users[] = $userdata;
			}
			echo json_encode($users);
			exit();
		}
		echo 0;
	}

	public function timesheet($page, $range)
	{
		$sum = $page * $range;

		$timesheet_sql = decryptIt($_COOKIE['session_result']);
		$timesheet_sql .= ' LIMIT '.$sum.','.$range.'';

		$query = (new DB($timesheet_sql))->FetchAll();

		if (count($query) > 0) {
			?> 
			<table>
				<tbody>
					<?php

						foreach ($query as $key => $row) { // run the query through this while loop
							$user = Core::Self($row['ts_owner']); // Get user data
							$username = htmlentities($user['n_family'].', '.$user['n_given']); // Get the fullname of the user
							$start = $this->bo->dateDisplay($row['ts_start']);
							$end = $this->bo->dateDisplay($row['ts_start'] + ($row['ts_duration'] * 60));
							$duration = round(($row['ts_duration'] / 60), 2);
							$category = \AgroEgw\Api\Categories::Read($row['cat_id']); // Get category data

							$title = utf8_encode($row['ts_title']);  // encode the title to get the right characters?>

							<tr class='cat_<?php echo $row['cat_id']; ?>'>	
								<td><?php echo $row['ts_id']; ?></td>
								  <td><?php echo $start; ?></td>
							      <td><?php echo $end; ?></td>
								  <td><?php echo htmlentities($title); ?></td> 

							<?php if ($row['cat_id'] != null) {  ?>
							    <td><?php echo htmlentities(lang($category['name'])); ?></td>
							<?php } else { ?>
								<td><?php echo lang('No Category'); ?></td>
							<?php } ?>

								<td><?php echo $duration; ?>h</td>
							      <td><?php echo $username; ?></td>
							      <td><?php echo $row['ts_status']; ?></td>
							    </tr>

							<?php
							/*echo "<pre>";
							var_dump($user);
							echo "</pre>";*/
						} ?>
	  			</tbody>
	  		</table>
	  	<?php
		} else {
			echo 0;
		}
	}

	public function get_user_access($user, $password = false, $nfcid = false)
	{
		if ($nfcid) {
			$nfc_check = $this->so->nfc_check($nfcid);
			$user = $nfc_check['user'];
		}

		if ($user) {
			$get_username = Core::Self($user);
			$not_defined_time = Core::getMeta(false, 'not_defined_time', $get_username['id'])['meta_data'];
			$username = htmlentities($get_username['n_family'].', '.$get_username['n_given']);

			$check_security = $this->so->proof_security($user);
			$self_check = $GLOBALS['egw_info']['user']['account_id'] == $user;
			if ($check_security) {
				if ($check_security['access_granted'] == 'yes') {
					$access = 1;
				} elseif ($check_security['access_granted'] == 'no') {
					if ($check_security['access_denied'] == 'yes') {
						$access = 0;
					} elseif ($check_security['access_denied'] == 'no') {
						$access = 2; //passwordcheck
					}
				}

				if ($access == 0) {
					if ($self_check) {
						$user_premission = 1;
					} else {
						$user_premission = 0;
					}
				} elseif ($access == 1) {
					$user_premission = 1;
				} elseif ($access == 2) {
					$user_premission = 2;
				}
			}

			if ($nfcid) {
				$user_premission = 1;
			}

			if ($user_premission == 0) {
				$premission = 'denied';
			} elseif ($user_premission == 1) {
				$premission = 'granted';
				$content = $this->bo->status($user);

				$should = $content['should'];
				$status = $content['status'];
				$start = $content['ts_start'];
				$last_modified = $content['last'];
				$made = $content['made'];
				$time_account = $content['time_account'];
				$total_vac = $content['total_vac'];
				$spent_vac = $content['spent_vac'];
				$rest_vac = $content['rest_vac'];
			} elseif ($user_premission == 2) {
				$content = $this->bo->status($user);

				if ($password or $self_check) {
					$check_pwd = $this->so->password($user);
					if (($check_pwd['password'] == $password) or $self_check) {
						$premission = 'granted';
						$pass_check = 'ok';

						$should = $content['should'];
						$start = $content['ts_start'];
						$last_modified = $content['last'];
						$made = $content['made'];
						$time_account = $content['time_account'];
						$total_vac = $content['total_vac'];
						$spent_vac = $content['spent_vac'];
						$rest_vac = $content['rest_vac'];
					} else {
						$pass_check = 'incorrect';
					}
				} else {
					$pass_check = '?';
					$premission = 'password';
				}

				$status = $content['status'];
			}
		}

		if ($status == 1) {
			$text_status = lang('Logout');
			$color = 'green';
		} elseif ($status == 0) {
			$text_status = lang('Login');
			$color = '#bd1515';
		}

		$result = [

			'user_id'       => $user,
			'contact_id'	   => $get_username['contact_id'],
			'username'      => $username,
			'premission'	   => $premission,
			'pass_check'    => $pass_check,
			'should' 	  	   => $should,
			'status' 		     => $status,
			'color'			      => $color,
			'text_status'	  => $text_status,
			'start' 		      => $start,
			'last_modified' => $last_modified,
			'made' 			      => $made,
			'time_account' 	=> $time_account,
			'total_vac' 	   => $total_vac,
			'spent_vac' 	   => $spent_vac,
			'rest_vac' 		   => (string) preg_replace("/\(([^\)]*)\)/", '', $rest_vac),
			'nfc_id'		      => $nfcid,
			'disable'		     => $not_defined_time,

		];

		return $result;
	}

	public function logInOut($user, $status, $nfcid = false)
	{
		if ($nfcid) {
			$nfc_check = $this->so->nfc_check($nfcid);
			$user = $nfc_check['user'];
		}

		$status = Tracker::isOnline($user);

		if ($status) {
			Tracker::OUT($user);
			$new_status = 0;
			$text_status = lang('Login');
			$msg = lang('You are logged out!');
			$color = '#bd1515';
		} else {
			Tracker::IN($user);
			$new_status = 1;
			$text_status = lang('Logout');
			$msg = lang('You are logged in!');
			$color = 'green';
		}

		$result = [
			'user_id'		   => $user,
			'status'		    => $new_status,
			'text_status'	=> $text_status,
			'message'		   => $msg,
			'color'			    => $color,
		];

		return $result;
	}
}

(new TrackerController())->initData();
