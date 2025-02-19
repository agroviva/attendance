<?php
use AgroEgw\Api\User;
use Attendance\Contracts;
use Attendance\Graph;

$contracts = new Contracts();
$contracts = $contracts->Load();

if (!empty($contracts)) {
	/*
	 * PLEASE REVIEW
	 * This could be unnessesary work done, code may change for better
	 */
	foreach ($contracts as $key => $contract) {
		$user = User::Read($contract['user']);
		$contracts[$key]['fullname'] = $user['account_firstname'].' '.$user['account_lastname'];
	}
	unset($contract); // unset this variable because it will be used later below
	
	/*
	 * Sort by the alphabetic order
	 */
	usort($contracts, function ($x, $y) {
		// return strcasecmp($x['fullname'], $y['fullname']);
		return $x['sort_order'] - $y['sort_order'];
	});
}



Graph::Render('header');
?>
<link rel="stylesheet" type="text/css" href="/egroupware/attendance/app/css/contracts.css">
<div>
	<div class="modal fade bd-example-modal-lg" id="NewContract" role="" tabindex="-1">
		<div class="modal-dialog modal-login" role="document">
			<div class="modal-content">
				<div class="card card-signup card-plain">
					<div class="modal-body">
						<form>
							<div class="form-group form-user">
								<label>Benutzer</label> 
								<input class="form-control" name="user" type="text" id="userInput" autocomplete="off">
								<div class="users">
									
								</div>
							</div>
							<div class="form-row">
								<div class="form-group col-md-6">
									<label>Jahresurlaub</label> 
									<input class="form-control" name="vacations" type="number" id="vacationInput" min="0" placeholder="0">
								</div>
								<div class="form-group col-md-6">
									<label>Urlaubskorrektur zu Beginn</label> 
									<input class="form-control" name="extra_vac" type="number" value="0" id="extraVacInput" min="0">
								</div>
							</div>
							<div class="form-row">
								<div class="form-group col-md-6">
									<label>Startdatum</label> 
									<input type="text" name="startdate" autocomplete="off" class="form-control datepicker" value="" id="startOfContract" required/>
								</div>
								<div class="form-group col-md-6">
									<label>Enddatum</label> 
									<input type="text" name="startdate" autocomplete="off" class="form-control datepicker" id="endOfContract" value=""/>
								</div>
							</div>
							<div class="form-row">
								<div class="form-group col-md-6">
									<label for="security_level">Sicherheitsstufe</label> 
									<select class="form-control" id="securityLevel">
										<option>Auswählen</option>
										<option value="password">Passwort</option>
										<option value="access_denied">Zugriff verweigert</option>
										<option value="access_granted">Zugriff gewährt</option>
									</select>
								</div>
								<div class="form-group col-md-6">
									<label>Passwort</label> 
									<input type="text" name="password" id="passwordField" class="form-control" value="" disabled/>
								</div>
							</div>
							<div class="form-row">
								<div class="form-group col-md-6">
									<label>Anmeldungsintervall</label>
									<div class="form-group">
										<label>Intervall in Minuten</label>
										<input class="form-control" name="stepwiseIN" type="number" id="stepwiseIN" placeholder="" min="0" max="60">
									</div>
									<div class="form-group">
										<label>Rundungspunkt (n). Minute</label>
										<input class="form-control" name="roundingPointIN" type="number" id="roundingPointIN" placeholder="" min="0" max="60">
									</div>
								</div>
								<div class="form-group col-md-6">
									<label>Abmeldungsintervall</label> 
									<div class="form-group">
										<label>Intervall in Minuten</label>
										<input class="form-control" name="stepwiseOUT" type="number" id="stepwiseOUT" placeholder="" min="0" max="60">
									</div>
									<div class="form-group">
										<label>Rundungspunkt (n). Minute</label>
										<input class="form-control" name="roundingPointOUT" type="number" id="roundingPointOUT" placeholder="" min="0" max="60">
									</div>
								</div>
							</div>
							<div class="form-group">
							    <div class="form-check">
							      <label class="form-check-label">
							          <input id="norules" class="form-check-input" type="checkbox" value="">
							          Keine festgelegte Arbeitszeiten/Stundenanzahl	
							          <span class="form-check-sign">
							            <span class="check"></span>
							          </span>
							      </label>
							    </div>
							</div>
							<div class="form-group form-sections">
								<div class="sections">
									<div class="form-section">
										<div class="form-group col-md-12 weekdays no-selection">
											<span class="monday">MO</span>
											<span class="tuesday">DI</span>
											<span class="wednesday">MI</span>
											<span class="thursday">DO</span>
											<span class="friday">FR</span>
											<span class="saturday">SA</span>
											<span class="sunday">SO</span>
										</div>
										<div class="time_inputs col-md-12">
											<div class="form-group col-md-4">
												<label>Sollstunden</label> 
												<input type="text" class="form-control timepicker shouldHours" value="00:00" autocomplete="off" />
											</div>
											<div class="form-group col-md-4">
												<label>Wiederholung</label> 
												<select class="form-control repetition">
													<option value="1">wöchentlich</option>
													<option value="2">zweiwöchentlich</option>
													<option value="3">dreiwöchentlich</option>
													<option value="4">vierwöchentlich</option>
												</select>
											</div>
											<div class="form-group col-md-4">
												<label>Gültig ab*</label> 
												<input type="text" autocomplete="off" class="form-control datepicker validDate" value="" disabled/>
											</div>
										</div>
									</div>
								</div>
								<div class="success_button" style="text-align: center;">
									<button class="btn" title="Neue Regel" onclick="newRule(event)"><i class="material-icons">add_circle_outline</i></button>
								</div>
							</div>
						</form><button id="SaveButton" class="btn btn-success">Speichern</button>
					</div>
				</div>
			</div>
		</div>
	</div>
	<div class="modal fade bd-example-modal-lg" id="TimeOverview" role="" tabindex="-1">
		<div class="modal-dialog modal-login" style="max-width: 90%;width: 90%;" role="document">
			<div class="modal-content">
				<div class="modal-body">
				</div>
			</div>
		</div>
	</div>
	<div class="table-responsive">
		<table class="table">
			<thead>
				<tr>
					<th>
						<div class="form-group" style="margin-bottom: 0px;">
					      <label for="sortUser">Benutzer</label>
					      <input type="text" class="form-control" id="sortUser">
					    </div>
					</th>
					<!-- <th>Status </th> -->
					<th>Urlaub </th>
					<th>Resturlaub (*Vorjahr) </th>
					<th onclick="($('.expired').children('td, th').slideToggle('slow'))">Zeitkonto </th>
					<th>Starten </th>
					<th>End </th>
					<th>Sicherheitsstufe </th>
					<th>Stunde(n) pro Woche </th>
					<th>
						<button type="button" rel="tooltip" class="btn btn-success btn-simple" data-toggle="modal" onclick="NewContract()" data-target="#NewContract" title="Neuen Arbeitsvertrag anlegen!">Hinzufügen</button>
					</th>
					<th>
						<button type="button" rel="tooltip" class="btn btn-success btn-simple" onclick="ShowAll()">Alle Verträge</button>
					</th>
				</tr>
			</thead>
			<tbody id="sortable">
				<?php foreach ($contracts as $contract) { ?>
					<?php
					$user = User::Read($contract['user']);
					$user['account_fullname'] = $user['account_lastname'].', '.$user['account_firstname'];
					$account_id = $user['account_id'];
					$contact_id = $user['person_id'];
					$status = $contract['status'] == 'Active' ? 'online' :
					($contract['status'] == 'expired' ? 'expired hidden' : 'offline');
					$TimeAccountState = ($contract['time_account'] < 0 ? 'danger' : 'success');
					?>
					<tr id="entry_<?php echo $contract['id']?>" data-account="<?php echo $account_id?>" data-contact="<?php echo $contact_id?>" class="<?php echo $status?>">
						<td class="username">
							<div class="worker-photo" style="box-sizing: border-box;background-image: url(/egroupware/api/avatar.php?contact_id=<?php echo $contract['contact_id']?>&etag=8);"></div>
							<span class="name"><?php echo $user['account_fullname']?></span>
						</td>
						<!-- <td><?php echo $contract['status']?></td> -->
						<td><strong><?php echo $contract['vacation']?></strong></td>
						<td><strong><?php echo $contract['rest_vac']?></strong></td>
						<td><button title="See more Details!" class="btn btn-info btn-<?php echo $TimeAccountState?> overview"><strong><?php echo $contract['time_account']?></strong></button></td>
						<td><?php echo $contract['start']?></td>
						<td><?php echo $contract['end']?></td>
						<td><?php echo lang($contract['sec_type'])?></td>
						<td><strong><?php echo $contract['total_week_hours']?></strong></td>
						<td><button type="button" rel="tooltip" class="btn btn-info btn-simple EditContract" data-toggle="modal">Bearbeiten</button></td>
						<td><button type="button" rel="tooltip" class="btn btn-danger btn-simple DeleteContract">Löschen</button></td>
					</tr>
				<?php } ?>
			</tbody>
		</table>
	</div>
</div>
<script>
    $("#sortable").sortable({
        update: function() {
            var order = $(this).sortable("serialize") + '&route=updateContractsListings'; 
            $.post("/egroupware/attendance/", order);
        }
    });
    $("#sortable").disableSelection();
</script>
<?php
Graph::Render('footer');
