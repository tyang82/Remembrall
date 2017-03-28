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
body {font-size:16px;}
.w3-half img{margin-bottom:-6px;margin-top:16px;opacity:0.8;cursor:pointer}
.w3-half img:hover{opacity:1}
</style>
<body>

<script type='text/javascript' src='knockout-3.4.1.js'></script>

<!-- php code to dynamically populate the html-->
<?php

$dynamodb = $currUser['db'];
$care_giver_email = $currUser['email'];
    
$first_response = $dynamodb->query([
    'TableName' => 'care_givers',
    'KeyConditionExpression' => 'care_giver_email = :email',
    'ExpressionAttributeValues' =>  [
        ':email' => ['S' => $care_giver_email]
    ]
]);

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
    <h1 class="w3-xxxlarge"><b>Let <strong data-bind="text: care_receiver_name"></strong> know what you're doing</b></h1>
    <hr style="width:50px;border:5px solid red" class="w3-round">
      
      
       <form action="" method="POST" name="reminder_form">
         
               <textarea name="status_submit">What are you doing?</textarea>
           <input type="text" value="When?" id="datepicker" name="datepicker" class="datepick">
         
          
          <input type="submit" style="background-color:red;border:none; color:white;" value="Remind" name="remind_button">
        </form>
 
      
     

  </div>
    <!-- php code for submit -->
    <?php 
      
   if (isset($_POST["status_submit"]))
   {    
       $remindtask = $_POST["status_submit"];
       $date = $_POST['datepicker'];
       echo $date;
       echo $remindtask;
       echo $care_giver_name;
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
    <h1 class="w3-jumbo"><b>History</b></h1>
    <hr style="width:50px;border:5px solid red" class="w3-round">
     
    <!--code for history here -->
    
</div>
    

<!-- Settings -->
  <a name="settings"></a>
<div class="w3-container" style="margin-top:80px" id="settings">
    <h1 class="w3-jumbo"><b>Settings</b></h1>
    <hr style="width:50px;border:5px solid red" class="w3-round">
     
    <!--code for settings here -->
    
</div>
    
    
    
    
  
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
  


</script>


<script type="text/javascript">
var clientViewModel = function(care_receiver_name) {
    this.care_receiver_name = ko.observable("<?php echo $care_receiver_name;?>");
};
 
ko.applyBindings(new clientViewModel());
</script>


</body>
</html>