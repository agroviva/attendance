<?php
use Attendance\Graph;

Graph::Render('header');
?>

<div>
	<form class="createTimesheet">
        <label for="titel">Titel</label><br>
        <input type="text" id="titel" name="titel" value="" placeholder="Arbeitszeit"><br>
        <label for="datetime">Datum & Startzeit</label><br>
        <input type="datetime" id="datetime" name="datetime"><br>
        <label for="dauer">Dauer</label><br>
        <input type="number" step="0.01" id="dauer" name="dauer"><br>
        <label for="user">Benutzer</label><br>
        <input type="text" id="user" name="user" value="Doe"><br>
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
