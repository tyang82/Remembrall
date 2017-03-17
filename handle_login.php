<?php
function verifyLogin($req, $live) {
    if (!$live) {
        $d = array("name" => "Local Host", "email" => "local@host.com", "id" => "locoLocalHost");
    } else {
        // verify that the access token belongs to us
        $r = file_get_contents('https://api.amazon.com/auth/o2/tokeninfo?access_token=' . urlencode($req['access_token']));
        $d = json_decode($r);

        if ($d->aud != 'amzn1.application-oa2-client.60c59c23ce9a415abeff731d5078dc81') {
            // the access token does not belong to us
            return false;
        }

        // exchange the access token for user profile
        $r = file_get_contents('https://api.amazon.com/user/profile?access_token=' . urlencode($req['access_token']));
        $d = json_decode($r);
    }
    
    // now extablish db connection
    require 'vendor/autoload.php';
    date_default_timezone_set('America/New_York');
    $sdk = new Aws\Sdk([
        'region'   => 'us-east-1',
        'version'  => 'latest',
        'http' => ['verify' => false],
        'credentials' => [
            // DO NOT EVER PUT THIS INFO IN ANY OTHER FILE
            // DO NOT EVER PUSH THIS INFO TO GITHUB OR ANY OTHER PUBLIC PLACE ON THE WEB
            'key' => 'REDACTED',
            'secret' => 'REDACTED'],
    ]);
    $dynamodb = $sdk->createDynamoDb();
    
    // return the profile info and db connection object
    return array("name" => $d->name, "email" => $d->email, "id" => $d->user_id, "db" => $dynamodb);
}
?>