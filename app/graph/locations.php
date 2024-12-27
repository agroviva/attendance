<?php
// Include necessary classes like DB for database operations
use AgroEgw\DB;
use Attendance\Graph;
use Attendance\Contracts;
use Attendance\Location;
use AgroEgw\Api\User;


$contracts = new Contracts();

Graph::Render('header');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Handle adding locations
    if (isset($_POST['add_location'])) {
        $location = trim($_POST['location_name']);
        if (!empty($location)) {
            Location::add($location);
            echo "<p>Location '$location' added successfully!</p>";
        } else {
            echo "<p>Please provide a valid location name.</p>";
        }
    }

    // Handle assigning users to locations
    if (isset($_POST['assign_user'])) {
        $users = json_encode($_POST['users']);
        $location_id = $_POST['location_id'];

        if (!empty($user) && !empty($location_id)) {
             // Get existing users and merge new ones
            $existing_location = DB::Get("SELECT users FROM egw_attendance_locations WHERE id = $location_id");
            if ($existing_location) {
                $existing_users = json_decode($existing_location['users'], true);
                $new_users = array_unique(array_merge($existing_users, $_POST['users']));

                // Update the users array
                $users_json = json_encode($new_users);
                DB::Run("UPDATE egw_attendance_locations SET users = '$users_json' WHERE id = $location_id");

                echo "<p>Users assigned successfully to location ID '$location_id'!</p>";
            }
        } else {
            echo "<p>Please provide valid users and a location ID.</p>";
        }
    }
}
?>
<style type="text/css">
	div.locations {
		width: 90%;
		padding: 20px;
	}

	.not-selectable {
	  -webkit-touch-callout: none;
	  -webkit-user-select: none;
	  -khtml-user-select: none;
	  -moz-user-select: none;
	  -ms-user-select: none;
	  user-select: none;
	}
	.locations .user {
	    display: flex;
	}

	.locations {
		padding-top: 4px;
		display: inline-block;
		width: 100%;
	}
	.locations .user {
		border-radius: 5px;
		background: sandybrown;
		float: left;
		margin-right: 5px;
		margin-bottom: 4px;
		padding: 4px;
		cursor: pointer;
	}
	.locations .user.active {
		background: #008c96;
	}
	.locations .user p {
		font-size: 12px;
		border-radius: 4px;
		float: left;
		line-height: 2;
		font-weight: 500;
		color: white;
		margin-bottom: 0;
	}
	[data-marked]{
		background: linear-gradient(135deg, #4caf50, #00d486 60%, #00bcd4);
	}
</style>

<!-- HTML for Add Location Form -->
<div class="form-container">
    <h2>Add a New Location</h2>
    <form method="POST" id="add-location-form">
        <label for="location_name">Location Name:</label>
        <input type="text" id="location_name" name="location_name" required>
        <button type="submit" name="add_location">Add Location</button>
    </form>
</div>

<!-- HTML for Assign User to Location Form -->
<div class="form-container">
    <?php
        $locations = Location::all();
        foreach ($locations as $location) {
            $locationID = $location['id'];
            ?>
            <div class="locations form-group bmd-form-group" data-uid="<?php echo $location['id']?>">
                <label for="location" class="bmd-label-static"><?php echo $location['location']?></label>
                <input type="text" class="form-control" id="location">
                <div class="location not-selectable">
                    <?php foreach ($contracts as $contract) { ?>
                        <?php
                            $userID = $contract['user'];
                            $user = User::Read($userID);
                            $name = $user['account_fullname'];
                        ?>
                        <div class="user active" onclick="selectManager(this)" data-uid="<?php echo $userID?>"><p><?php echo $name?></p></div>
                    <?php } ?>
		   	    </div>
            </div>
            <?php
        }
    ?>
</div>

<style>
    .form-container {
        background-color: #f4f4f4;
        padding: 20px;
        margin: 20px auto;
        width: 300px;
        border-radius: 8px;
        box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
    }

    .form-container h2 {
        font-size: 20px;
        margin-bottom: 10px;
    }

    .form-container label {
        display: block;
        margin-bottom: 5px;
        font-weight: bold;
    }

    .form-container input, .form-container select {
        width: 100%;
        padding: 8px;
        margin-bottom: 10px;
        border: 1px solid #ddd;
        border-radius: 4px;
    }

    .form-container button {
        background-color: #28a745;
        color: white;
        padding: 10px;
        border: none;
        border-radius: 4px;
        cursor: pointer;
    }

    .form-container button:hover {
        background-color: #218838;
    }
</style>

<script>
    $(document).ready(function() {
        // Validate location name
        $('#add-location-form').on('submit', function(e) {
            var locationName = $('#location_name').val();
            if (locationName.trim() === '') {
                alert('Please enter a valid location name.');
                e.preventDefault();
            }
        });

        // Validate user assignment
        $('#assign-user-form').on('submit', function(e) {
            var userName = $('#user_name').val();
            var locationId = $('#location_id').val();
            
            if (userName.trim() === '' || locationId === '') {
                alert('Please fill in all fields.');
                e.preventDefault();
            }
        });
    });


</script>

<?php
Graph::Render('footer');