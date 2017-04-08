<!DOCTYPE html>
<html>
<title>Remembrall</title>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="stylesheet" href="/w3_style.css">
<link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Poppins">
    <link rel="stylesheet" href="/jquery.css">
<style>
body,h1,h2,h3,h4,h5 {font-family: "Poppins", sans-serif}
body {font-size:16px;background: url(./top_red_dots.PNG) no-repeat top center;}
.w3-half img{margin-bottom:-6px;margin-top:16px;opacity:0.8;cursor:pointer}
.w3-half img:hover{opacity:1}
</style>
<body>

<script type='text/javascript' src='knockout-3.4.1.js'></script>

<!-- php code to dynamically populate the html-->
<?php

$dynamodb = $currUser['db'];
$care_giver_email = $currUser['email'];
//echo $care_giver_email;
$first_response = $dynamodb->query([
    'TableName' => 'care_givers',
    'KeyConditionExpression' => 'care_giver_email = :email',
    'ExpressionAttributeValues' =>  [
        ':email' => ['S' => $care_giver_email]
    ]
]);
//echo "Vardump:";
//echo var_dump($first_response);
$acct_email = $first_response['Items'][0]['acct_email']['S'];
$care_giver_name = $first_response['Items'][0]['name']['S'];
    
$response = $dynamodb->query([
    'TableName' => 'care_receivers',
    'KeyConditionExpression' => 'acct_email = :email',
    'ExpressionAttributeValues' =>  [
        ':email' => ['S' => $acct_email]
    ]
]);
    
$care_receiver_name = $response['Items'][0]['name']['S'];
?>

<!-- Sidenav/menu -->
<nav class="w3-sidenav w3-red w3-collapse w3-top w3-large w3-padding" style="z-index:3;width:300px;font-weight:bold;" id="mySidenav"><br>
  <div class="w3-container">
    <h3 class="w3-padding-64"><b>Remembrall<br></b></h3>
  </div>
    
  <a href="#" onclick="w3_close()" class="w3-padding w3-hover-white">Home</a> 
  <a href="#history" onclick="w3_close()" class="w3-padding w3-hover-white">History</a> 
  <a href="#settings" onclick="w3_close()" class="w3-padding w3-hover-white">Settings</a> 
  <a href="https://remembrall.me/login" onclick="w3_close()"class="w3-padding w3-hover-white">Logout</a>
  
</nav>

<!-- Top menu on small screens -->
<header class="w3-container w3-top w3-hide-large w3-red w3-xlarge w3-padding">
  <a href="javascript:void(0)" class="w3-button w3-red w3-margin-right" onclick="w3_open()">☰</a>
  <span>Remembrall</span>
</header>

<!-- Overlay effect when opening sidenav on small screens -->
<div class="w3-overlay w3-hide-large" onclick="w3_close()" style="cursor:pointer" title="close side menu" id="myOverlay"></div>

<!-- !PAGE CONTENT! -->
<div class="w3-main" style="margin-left:340px;margin-right:40px">

  <!-- Home: what are you doing -->
  <a name="home"></a>
  <div class="w3-container" style="margin-top:80px" id="home">
    <h1 class="w3-xxxlarge"><b>Remembrall Home</b></h1>
    <hr style="width:50px;border:5px solid red" class="w3-round">
      
      <h1><b>Let <strong data-bind="text: care_receiver_name"></strong> know what you're doing</b></h1>
       <form action="" method="POST" name="reminder_form">
         
               <textarea name="status_submit" placeholder="What are you doing?"></textarea>
           <input type="text" placeholder="When?" id="datepicker" name="datepicker" class="datepick">
         
          
          <input type="submit" style="background-color:red;border:none; color:white;position: relative;
    top: -20px;
    width: 15%;height: 50px;
    padding: 12px 20px;
    box-sizing: border-box;
    border: 2px solid #ccc;
    border-radius: 4px;" value="Remind" name="remind_button">
        </form>
    <!-- incomplete tasks-->
      <?php
        $caregiver_tasks = $dynamodb->query([
        'TableName' => 'reminders',
        'KeyConditionExpression' => 'acct_email = :email',
        'ExpressionAttributeValues' =>  [
        ':email' => ['S' => $acct_email]
            ]
        ]);
      $caregiver_tasks = $caregiver_tasks['Items'];
    ?>
      <div style="padding-top:125px">
      <h1><b>Incomplete Tasks</b></h1>
      <table>
          <tr>
              <th>Task</th>
              <th>Due Date</th>
              <th>Assigned</th>
              <th>Status</th>
          </tr>
          <!-- php code to load each of the entries --> 	
		  <?php 
			//$date = date('m/d/Y');
			
			for ($x = 0; $x < count($caregiver_tasks); $x++) {?> 
  				<tr class="item_row">
  					<?php
  						if (false == $caregiver_tasks[$x]['self_flag']['BOOL'] && false == $caregiver_tasks[$x]['complete']['BOOL']) { ?>
        					<td> <?php echo $caregiver_tasks[$x]['text']['S']; ?></td>
        					<td> <?php echo $caregiver_tasks[$x]['due']['S']; ?></td>
                            <td> <?php 
                                    $assigned_email = $caregiver_tasks[$x]['assigned_caregiver']['S'];
                                    $person_name = $dynamodb->query([
                                        'TableName' => 'care_givers',
                                        'KeyConditionExpression' => 'care_giver_email = :email',
                                        'ExpressionAttributeValues' =>  [
                                        ':email' => ['S' => $assigned_email]
                                        ]
                                    ]);
                                    echo $person_name['Items'][0]['name']['S'];                                                                                 
                                                                                                                                                                                                                                         
                                ?></td>
                            <td> <form action="" method="POST" name="reminder_form" >
                                <input type="submit" placeholder="complete" value="complete" name="<?php echo $x;?>" ></form>
                                <?php
                                                                                                                                       
                                    if(isset($_POST[$x])){
                                       $timestamp = $caregiver_tasks[$x]['timestamp']['S'];
                                       $response = $dynamodb->updateItem([
                                           'TableName' => 'reminders',
                                            'Key' => [
                                                'acct_email' => ['S' => $acct_email],
                                                'timestamp' => ['S' => $timestamp]
                                            ],
                                           'ExpressionAttributeValues' => [
                                            ':val1' => [
                                                'BOOL' => true
                                                ]
                                            ],
                                           'UpdateExpression' => 'set complete = :val1'
                                       ]);
                                        header("Refresh:0");
                                    }
                                   ?>                                                                                                    
                                                                                                                                       
                            </td>
        			<?php } ?>
  				</tr>
		<?php }?>
		<!-- php code to load each of the entries ENDS! -->
      </table>
      </div>

  </div>
    <!-- php code for submit -->
    <?php 
  
      
   if (isset($_POST["status_submit"]))
   {    
       $remindtask = $_POST["status_submit"];
       $date = $_POST['datepicker'];
        $response = $dynamodb->putItem([
           'TableName' => 'statuses',
           'Item' => [
                'acct_email' => ['S' => $acct_email],
                'care_giver_email' => ['S' => $care_giver_email],
                'name' => ['S' => $care_giver_name],
                'text'  => ['S' => $remindtask],
               'timestamp' => ['S' => date('Y-m-d H:i:s')],
                'day' => ['S' => $date ]
                
            ]
       ]);
      
   }
?>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script>
<script src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.11.4/jquery-ui.min.js"></script>
  
  <script>
  $(document).ready(function() {
    $("#datepicker").datepicker();
  });

  </script>
    
    

<!-- History -->
  <a name="history"></a>
<div class="w3-container" style="margin-top:80px" id="history">
    <h1 class="w3-xxxlarge"><b>History</b></h1>
    <hr style="width:50px;border:5px solid red" class="w3-round">
<?php
	$date = date('m/d/Y');
?>
	<h4>Today's Date is: <?php echo $date ?> </h4>

<!-- PHP code for loading the Alexa set reminder -->
<?php
// echo $acct_email;
// echo $care_receiver_name;
$alexa_in_moment_response = $dynamodb->query([
    'TableName' => 'reminders',
    'KeyConditionExpression' => 'acct_email = :email',
    'ExpressionAttributeValues' =>  [
        ':email' => ['S' => $acct_email]
    ]
]);
$alexa_in_moment_response = $alexa_in_moment_response['Items'];
$array = array();

for ($x = 0; $x < count($alexa_in_moment_response); $x++) {
	// key value pair task (text) -> timestamp
	// only query the self assigned ones! self_flag will essentually be a primary key 
	if ($alexa_in_moment_response[$x]['self_flag']['BOOL'] == true) {
		$array[$alexa_in_moment_response[$x]['timestamp']['S']] = $alexa_in_moment_response[$x]['text']['S'];
	}
}
$array = array_reverse($array);
if (count($array) >= 10) {
  $array = array_slice($array, 0, 10);
}
?>
  
    <h1><?php echo $care_receiver_name; ?>'s Instant Reminder History</h1>
    <table>
		<tr>
    		<th>Tasks</th>
    		<th>Time Stamps</th>
  		</tr>
  		
  		<!-- php code to load each of the entries --> 	
		<?php 
			foreach ($array as $key=>$value) :?> 
            <tr class="item_row">
              <td> <?php echo $value; ?></td>
              <td> <?php echo $key; ?></td>
            </tr>
		<?php endforeach;?>
		<!-- php code to load each of the entries ENDS! -->
	</table>
	
<?php
$family_schedule = $dynamodb->query([
    'TableName' => 'statuses',
    'KeyConditionExpression' => 'acct_email = :email',
    'ExpressionAttributeValues' =>  [
        ':email' => ['S' => $acct_email]
    ]
]);
$family_schedule = $family_schedule['Items'];
?>


<h1>Family Members' Tasks History</h1>
	<table>
		<tr>
			<th>Name</th>
			<th>Tasks</th>
		</tr>
		<!-- php code to load each of the entries --> 	
		<?php 

			$date = date('m/d/Y');
			//echo 'Today\'s Date is: ';
			//echo $date;

			for ($x = 0; $x < count($family_schedule); $x++) {?> 
  				<tr class="item_row">
  					<?php
  						if ($date == $family_schedule[$x]['day']['S']) { ?>
        					<td> <?php echo $family_schedule[$x]['name']['S']; ?></td>
        					<td> <?php echo $family_schedule[$x]['text']['S']; ?></td>
        			<?php } ?>
  				</tr>
		<?php }?>
		<!-- php code to load each of the entries ENDS! -->
	</table>
</div>
    

<!-- Settings -->
  <a name="settings"></a>
<div class="w3-container" style="margin-top:80px" id="settings">
    <h1 class="w3-xxxlarge"><b>Settings</b></h1>
    <hr style="width:50px;border:5px solid red" class="w3-round">
     
    <!--code for settings here -->

    <h1 id="editCareGiver">Edit Caregivers</h1>
    
    <?php
        $list_of_care_givers = $dynamodb->scan([
            'TableName' => 'care_givers',
            'ExpressionAttributesValues' => [
                ':email' => ['S' => $acct_email]] ,
            'FilterExpressions' => 'acct_email = :email'
        ]);
        $care_giver_array = $list_of_care_givers['Items'];
    ?>
    
    <table id="care_giver_table" >
        <tr>
            <th>Name</th>    
            <th>Email</th>
            <th>Remove</th>
        </tr>
    <?php
        for ($j = 0; $j < count($care_giver_array); $j++) { ?>
        <tr class="item_row">
            <td><?php echo $care_giver_array[$j]['name']['S']; ?></td>
            <td><?php echo $care_giver_array[$j]['care_giver_email']['S']; ?></td>
            <td><form action="" method="POST" name="user_form">
                <input type="submit" style="background-color:red;border:none;color:white;" value="Remove" name="delete<?php echo $j ?>"></form>
                <?php
                    if (isset($_POST['delete' . $j])) {
                        $email_to_delete = $care_giver_array[$j]['care_giver_email']['S'];
                        $delete_response = $dynamodb->deleteItem(['TableName' => 'care_givers',
                                                                 'Key' => [
                                                                    'care_giver_email' => ['S' => $email_to_delete],
                                                                    'acct_email' => ['S' => $acct_email]
                                                                        ]
                                                                ]);
                        ?><meta http-equiv="refresh" content="0"/><?php
                    }
                ?>
            </td>
        </tr>
    <?php        
    }
    ?>
    </table>
    <button id="add_care_giver" type="submit" style="background-color:red;border:none;color:white;" onclick="addCareGiverPopUp()">Add Caregiver</button>
    
    
    

</div>
    
    

  <!-- Modal for full size images on click-->
  <div id="modal01" class="w3-modal w3-black w3-padding-0" onclick="this.style.display='none'">
    <span class="w3-closebtn w3-text-white w3-opacity w3-hover-opacity-off w3-xxlarge w3-container w3-display-topright">×</span>
    <div class="w3-modal-content w3-animate-zoom w3-center w3-transparent w3-padding-64">
      <img id="img01" class="w3-image">
      <p id="caption"></p>
    </div>
  </div>

  
<script>
// Script to open and close sidenav
function w3_open() {
    document.getElementById("mySidenav").style.display = "block";
    document.getElementById("myOverlay").style.display = "block";
}
 
function w3_close() {
    document.getElementById("mySidenav").style.display = "none";
    document.getElementById("myOverlay").style.display = "none";
}

// Modal Image Gallery
function onClick(element) {
  document.getElementById("img01").src = element.src;
  document.getElementById("modal01").style.display = "block";
  var captionText = document.getElementById("caption");
  captionText.innerHTML = element.alt;
}
function DeleteRow(o) {
     //no clue what to put here?
     var p=o.parentNode.parentNode;
         p.parentNode.removeChild(p);
    }

function spawnDeletePopup(position) {
    var confirmation = confirm("Are you sure you want to delete: " + document.getElementById("care_giver_row_" + position).textContent);
    if (confirmation == true) {
        deleteCareGiver(position);
    }
}
    
function addCareGiverPopUp() {
    
}

function deleteCareGiver(position) {
    //var email_to_remove = document.getElementById("care_giver_row_" + position).textContent;

    
//    if (window.XMLHttpRequest) {
//        // code for IE7+, Firefox, Chrome, Opera, Safari
//        xmlhttp = new XMLHttpRequest();
//    } else {
//        // code for IE6, IE5
//        xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
//    }
//    xmlhttp.onreadystatechange = function() {
//        if (this.readyState == 4 && this.status == 200) {
//            //
//            document.getElementById("care_giver_table_load").textContent =  this.responseText;
//            if (this.responseText) {
//                //document.getElementById("care_giver_table_load").textContent = "successful";
//            }
//            populateCareGiverTable();
//        }
//    };

//    xmlhttp.open("GET","deleteUser.php?email="+email_to_remove+"&tablename=care_givers"+"&acct_email="+account_email,true);
//    xmlhttp.send();
    populateCareGiverTable();
}
function populateCareGiverTable() {

    <?php
        $list_of_caregivers = $dynamodb->scan([
            'TableName' => 'care_givers',
            'ExpressionAttributeValues' => [
                    ':email' => ['S' => $acct_email]] ,
            'FilterExpression' => 'acct_email = :email'
        ]);

        $array = $list_of_caregivers['Items'];
    ?>
    //document.getElementById("care_giver_table_load").textContent = "Count: " + count;
    document.getElementById("care_giver_table").innerHTML = "<tr><th>Name</th><th>Email</th><th>Remove</th></tr><?php for ($x = 0; $x < count($array); $x++) {?> <tr class=\"item_row\"><td> <?php echo $array[$x]['name']['S']; ?></td><td id=\"care_giver_row_<?php echo $x ?>\"> <?php echo $array[$x]['care_giver_email']['S']; ?></td><td> <a href=\"javascript:void(0)\" onclick=\"spawnDeletePopup(<?php echo $x ?>)\">Remove</a></td></tr><?php } ?>";
}

//populateCareGiverTable();
</script>


<script type="text/javascript">
var clientViewModel = function(care_receiver_name) {
    this.care_receiver_name = ko.observable("<?php echo $care_receiver_name;?>");
};
 
ko.applyBindings(new clientViewModel());
</script>


</body>
</html>
