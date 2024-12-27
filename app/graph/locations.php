<?php
// Include necessary classes like DB for database operations
use AgroEgw\DB;
use Attendance\Graph;
use Attendance\Contracts;
use Attendance\Location;
use AgroEgw\Api\User;


$contracts = new Contracts();
$contracts = $contracts->Load();

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
		background: green;
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
<div>
    <?php
        $locations = Location::all();
        foreach ($locations as $location) {
            $locationID = $location['id'];
            ?>
            <div class="locations form-group bmd-form-group">
                <strong><?php echo $location['location']?></strong>
                <div class="location not-selectable">
                    <?php foreach ($contracts as $contract) { ?>
                        <?php
                            $userID = $contract['user'];
                            $user = User::Read($userID);
                            $name = $user['account_fullname'];

                            if (Location::UserInLocation($userID, $locationID)) {
                                ?>
                                    <div class="user active" onclick="updateLocation(this)" data-uid="<?php echo $userID?>"  data-locationid="<?php echo $locationID?>"><p><?php echo $name?></p></div>
                                <?php
                            } else {
                                ?>
                                    <div class="user" onclick="updateLocation(this)" data-uid="<?php echo $userID?>"  data-locationid="<?php echo $locationID?>"><p><?php echo $name?></p></div>
                                <?php
                            }
                    } ?>
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

        function selectManager(elem) {
            elem = $(elem);
            var userID = elem.data("uid");
            var locationID = elem.data("locationid");

            if (userID) {
                $.ajax({
                    type: "POST",
                    url: "/egroupware/attendance/",
                    data: {
                        route: "location.update",
                        userID: userID,
                        locationID: locationID
                    },
                    success: function(data) {
                        if (data.response = "success") {
                            if (data.action == "removed") {
                                elem.removeClass("active");
                            } else {
                                elem.addClass("active");
                            }
                        }
                    },
                    error: function() {
                        alert('error handling here');
                    }
                });
            }
        }
    });


</script>

<?php
Graph::Render('footer');