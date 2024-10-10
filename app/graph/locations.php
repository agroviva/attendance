<?php
// Include necessary classes like DB for database operations
use AgroEgw\DB;
use Attendance\Graph;

Graph::Render('header');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Handle adding locations
    if (isset($_POST['add_location'])) {
        $location = $_POST['location_name'];
        if (!empty($location)) {
            // Add location to the database (assume you have a DB class with a simple insert method)
            // Add location to the database
            new DB("INSERT INTO egw_attendance_meta(meta_name, meta_connection_id, meta_data) VALUES('location','$location','[]')");
            echo "<p>Location '$location' added successfully!</p>";
        }
    }

    // Handle assigning users to locations
    if (isset($_POST['assign_user'])) {
        $user = $_POST['user_name'];
        $location_id = $_POST['location_id'];
        if (!empty($user) && !empty($location_id)) {
            // Assign user to location in the database
            DB::insert('user_locations', ['user_name' => $user, 'location_id' => $location_id]);
            echo "<p>User '$user' assigned to location ID '$location_id'!</p>";
        }
    }
}
?>

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
    <h2>Assign User to Location</h2>
    <form method="POST" id="assign-user-form">
        <label for="user_name">User Name:</label>
        <input type="text" id="user_name" name="user_name" required>
        
        <label for="location_id">Select Location:</label>
        <select id="location_id" name="location_id" required>
            <?php
            // Fetch locations from the database to populate the dropdown
            $locations = DB::select('locations'); // Assuming this returns an array of location data
            foreach ($locations as $location) {
                echo "<option value='{$location['id']}'>{$location['name']}</option>";
            }
            ?>
        </select>
        
        <button type="submit" name="assign_user">Assign User</button>
    </form>
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