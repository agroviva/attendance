<?php
use Attendance\Categories;
use Attendance\Graph;
use Attendance\Core;
use AgroEgw\Api\User;

Graph::Render('header');
?>
<style type="text/css">
	section {
		width: 90%;
		padding: 20px;
	}

	section form {
		padding: 0 20px;
	}

	.not-selectable {
	  -webkit-touch-callout: none;
	  -webkit-user-select: none;
	  -khtml-user-select: none;
	  -moz-user-select: none;
	  -ms-user-select: none;
	  user-select: none;
	}
	.manager .user {
	    display: flex;
	}

	.manager {
		padding-top: 4px;
		display: inline-block;
		width: 100%;
	}
	.manager .user {
		border-radius: 5px;
		background: sandybrown;
		float: left;
		margin-right: 5px;
		margin-bottom: 4px;
		padding: 4px;
		cursor: pointer;
	}
	.manager .user.active {
		background: #008c96;
	}
	.manager .user p {
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
<section id="permission">
	<h2>Personal Büro</h2>
	<?php $managers = Core::getMeta(false, "manager"); ?>
	<form>
		<div class="form-group bmd-form-group">
		    <label for="manager" class="bmd-label-static">Benutzer oder Gruppen</label>
		    <input type="text" class="form-control" id="manager">
		   	<div class="manager not-selectable">
		   		<?php foreach ($managers as $manager): ?>
		   			<?php 
		   				$userID = $manager['meta_connection_id'];
		   				$user = User::Read($userID);
		   				$name = $user["account_fullname"];
		   			?>
		   			<div class="user active" onclick="selectManager(this)" data-uid="<?php echo $userID?>"><p><?php echo $name?></p></div>
		   		<?php endforeach ?>
		   	</div>
		  </div>
	</form>
</section>
<section id="categories">
	<h2>Kategorien</h2>
	<?php


    $gg = Categories::Get();

    ?>
</section>
<script type="text/javascript">
    $('input#manager').keyup(function(e) {
        if (e.which == 13) {
            if (this.value == "") {
                return "";
            }
            $.ajax({
                type: "POST",
                url: "/egroupware/attendance/",
                data: {
                	route: "search.addressbook",
                	query: this.value,
                	app: "addressbook",
                	type: "account",
                	account_type: "both"
                },
                success: function(data) {
                    var html = "";
	                for (var i = 0; i < data.length; i++) {
	                    if (i == 10) {
	                        break;
	                    }
	                    var user = data[i];
	                    var hasId = false;
	                    $("#permission .manager .user.active").each(function(key, elem) {
	                        if ($(this).attr('data-uid') == user["id"]) {
	                            hasId = true;
	                        }
	                    });
	                    if (hasId) {
	                        hasId = false;
	                        continue;
	                    }
	                    html += '<div class="user" onclick="selectManager(this)" data-uid="' + user["id"] + '"><p>' + (user["label"]["label"] || user["label"]) + '</p></div>'
	                }
	                $("#permission .manager .user").not('.active').remove();
	                $("#permission .manager").append(html);
                },
                error: function() {
                    alert('error handling here');
                }
            });
            return false;
        }
    });

    function selectManager(elem) {
    	elem = $(elem);
    	var managerID = elem.data("uid");
    	if (managerID) {
    		$.ajax({
                type: "POST",
                url: "/egroupware/attendance/",
                data: {
                	route: "settings.newManager",
                	manager: managerID
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
		  
</script>
<?php
Graph::Render('footer');
