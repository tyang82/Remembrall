<?php
function verifyLogin($req) {
    // verify that the access token belongs to us
    $c = curl_init('https://api.amazon.com/auth/o2/tokeninfo?access_token=' . urlencode($req['access_token']));
    curl_setopt($c, CURLOPT_RETURNTRANSFER, true);

    $r = curl_exec($c);
    curl_close($c);
    $d = json_decode($r);

    if ($d->aud != 'amzn1.application-oa2-client.60c59c23ce9a415abeff731d5078dc81') {
      // the access token does not belong to us
      header('HTTP/1.1 404 Not Found');
      echo 'Page not found';
      exit;
    }

    // exchange the access token for user profile
    $c = curl_init('https://api.amazon.com/user/profile');
    curl_setopt($c, CURLOPT_HTTPHEADER, array('Authorization: bearer ' . $req['access_token']));
    curl_setopt($c, CURLOPT_RETURNTRANSFER, true);

    $r = curl_exec($c);
    curl_close($c);
    $d = json_decode($r);

//    echo sprintf('%s %s %s', $d->name, $d->email, $d->user_id);
    return array("name" => $d->name, "email" => $d->email, "id" => $d->user_id);
}
?>