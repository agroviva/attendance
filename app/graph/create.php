<?php
use Attendance\Graph;
use AgroEgw\DB;
use Carbon\Carbon;

Graph::Render('header');
?>

<div>
	<form class="createTimesheet">
        <label for="title">Titel</label><br>
        <input type="text" id="title" name="title" value="" placeholder="Arbeitszeit"><br>
        <label for="datetime">Datum & Startzeit</label><br>
        <input type="datetime-local" id="datetime" name="datetime"><br>
        <label for="duration">Dauer</label><br>
        <input type="number" step="0.01" id="duration" name="duration"><br>
        <label for="user">Benutzer</label><br>
        <select name="user">
        <?php
            $sql = "SELECT * FROM egw_addressbook a RIGHT OUTER JOIN egw_attendance b ON a.account_id = b.user WHERE (a.account_id IS NOT NULL AND b.user IS NOT NULL) AND (b.end is NULL OR b.end >= CURDATE()) ORDER BY n_family;";
            foreach (DB::GetAll($sql) as $row) {
                echo "<option value='".$row['account_id']."'>".$row['n_family'].', '.$row['n_given'].'</option>';
            }
        ?>
        </select>
        <br>
        <input type="submit" value="Speichern">
    </form>
    <script type="text/javascript">
        $("form.createTimesheet").submit(function(e) {

            //prevent Default functionality
            e.preventDefault();

            //get the action-url of the form
            var actionurl = "/egroupware/attendance/";

            //do your own request an handle the results
            $.ajax({
                type: "POST",
                url: actionurl,
                data: {
                	route: "create.timesheet",
                	data: $("form.createTimesheet").serialize(),
                },
                success: function(data) {
                    window.location.reload();
                },
                error: function() {
                    alert('error handling here');
                }
            });

        });		  
    </script>
</div>


<?php

Graph::Render('footer');
