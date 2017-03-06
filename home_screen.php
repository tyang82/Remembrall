<!DOCTYPE html>
<html>
<title>HomeScreen</title>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="stylesheet" href="/w3_style.css">
<link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Poppins">
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
require 'vendor/autoload.php';

$sdk = new Aws\Sdk([
    'region'   => 'us-east-1',
    'version'  => 'latest',
    'credentials' => [
        'key' => 'AKIAIXF4IAK25EI56ZLA',
        'secret' => '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']
]);

$dynamodb = $sdk->createDynamoDb();

$response = $dynamodb->query([
    'TableName' => 'users',
    'KeyConditionExpression' => 'email = :email',
    'ExpressionAttributeValues' =>  [
        ':email' => ['S' => 'wbroome14@gmail.com']
    ]
]);

$firstName = $response['Items'][0]['firstName']['S'];
$lastName = $response['Items'][0]['lastName']['S'];
$careGiverFirstName = $response['Items'][0]['careGiverFirstName']['S'];
$careGiverLastName = $response['Items'][0]['careGiverLastName']['S'];
$age = $response['Items'][0]['age']['N'];


?>






 
<!-- Sidenav/menu -->
<nav class="w3-sidenav w3-red w3-collapse w3-top w3-large w3-padding" style="z-index:3;width:300px;font-weight:bold;" id="mySidenav"><br>
  <div class="w3-container">
    <h3 class="w3-padding-64"><b>Remembrall<br></b></h3>
  </div>
    
  <a href="#" onclick="w3_close()" class="w3-padding w3-hover-white">Home</a> 

  <a href="history.html" onclick="w3_close()" class="w3-padding w3-hover-white">History</a> 
  <a href="settings.html" onclick="w3_close()" class="w3-padding w3-hover-white">Settings</a> 
  <a href="#packages" onclick="w3_close()" class="w3-padding w3-hover-white">Sign Out</a> 
  <a href="#contact" onclick="w3_close()" class="w3-padding w3-hover-white">Contact</a>
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

  <!-- Header -->
  <div class="w3-container" style="margin-top:80px" id="showcase">
    <h1 class="w3-jumbo"><b>Who would you like to remind?</b></h1>

    <hr style="width:50px;border:5px solid red" class="w3-round">
  </div>
  
  <!-- Photo grid (modal) *** THIS WILL HAVE TO BE POPULATED BY DB LATER ALSO INCLUDE SEARCH FUNCTION-->
  <div class="w3-row-padding">
    
    <!-- <img class="thumblist" style="border-radius:25%" src="/pig.jpg" />

     <img class="w3-image" style="border-radius:50%;max-width:50%" src="/pig.jpg" /> -->
    <img class="w3-image" style="border-radius:50%;max-width:50%" src="/pig.jpg" />
    <!-- <p>Caregiver First Name: <span data-bind="text: careGiverFirstName"> </span></p>
    <p>Last name: <span data-bind="text: careGiverLastName"> </span></p> -->
    <p>First Name: <span data-bind="text: firstName"> </span></p>
    <p>Last name: <span data-bind="text: lastName"> </span></p>

    <!-- <p>Age: <span data-bind="text: age"> </span></p>
    <p>Uid: <span data-bind="text: uid"> </span></p> -->

    
    <!-- <div class="w3-half">

       <img class="w3-image" style="border-radius:50%;max-width:50%" src="/pig.jpg" />

       <img class="w3-image" style="border-radius:50%;max-width:50%" src="/pig.jpg" onclick="onClick(this)" alt="Edward Foyle"/>
    </div> -->
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
var clientViewModel = function(first, last) {
    this.firstName = ko.observable("<?php echo $firstName;?>");
    this.lastName = ko.observable("<?php echo $lastName;?>");
    this.careGiverFirstName = ko.observable("<?php echo $careGiverFirstName;?>");
    this.careGiverLastName = ko.observable("<?php echo $careGiverLastName;?>");
    this.age = ko.observable("<?php echo $age;?>");
    this.uid = ko.observable("<?php echo $uid;?>");
};
 
ko.applyBindings(new clientViewModel());
</script>

</body>
</html>
