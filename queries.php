<script type='text/javascript' src='knockout-3.4.1.js'></script>



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
    'KeyConditionExpression' => 'uid = :uid',
    'ExpressionAttributeValues' =>  [
        ':uid' => ['S' => '0']
    ]
]);

$firstName = $response['Items'][0]['firstName']['S'];
$lastName = $response['Items'][0]['lastName']['S'];
$careGiverFirstName = $response['Items'][0]['careGiverFirstName']['S'];
$careGiverLastName = $response['Items'][0]['careGiverLastName']['S'];
$age = $response['Items'][0]['age']['N'];
$uid = $response['Items'][0]['uid']['S'];

print_r($firstName);
print_r($lastName);
//print_r($response['Items'][0]['careGiverFirstName']);
function getCareGiverInfo($uid) {
    
}
?>



<p>Caregiver First Name: <span data-bind="text: careGiverFirstName"> </span></p>
<p>Last name: <span data-bind="text: careGiverLastName"> </span></p>
<h2>Hello, <span data-bind="text: fullName"> </span>!</h2>
<p>First Name: <span data-bind="text: firstName"> </span></p>
<p>Last name: <span data-bind="text: lastName"> </span></p>
<p>Age: <span data-bind="text: age"> </span></p>
<p>Uid: <span data-bind="text: uid"> </span></p>








<script type="text/javascript">
var clientViewModel = function(first, last) {
    this.firstName = ko.observable("<?php echo $firstName;?>");
    this.lastName = ko.observable("<?php echo $lastName;?>");
    this.careGiverFirstName = ko.observable("<?php echo $careGiverFirstName;?>");
    this.careGiverLastName = ko.observable("<?php echo $careGiverLastName;?>");
    this.age = ko.observable("<?php echo $age;?>");
    this.uid = ko.observable("<?php echo $uid;?>");

    this.fullName = ko.pureComputed(function() {
        // Knockout tracks dependencies automatically. It knows that fullName depends on firstName and lastName, because these get called when evaluating fullName.
        return this.firstName() + " " + this.lastName();
    }, this);
};
 
ko.applyBindings(new clientViewModel());
</script>








