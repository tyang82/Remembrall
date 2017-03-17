<?php
// CHANGE THIS TO TRUE WHEN PUSHING LIVE
// CHANGE TO FALSE WHEN USING LOCALHOST/DEBUGGING
// (unless you are debugging login, in which case, may God have mercy on your soul)
$LIVE = true;

//if($LIVE && (empty($_SERVER['HTTPS']) || $_SERVER['HTTPS'] == "off")){
//    $redirect = 'https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
//    header('HTTP/1.1 301 Moved Permanently');
//    header('Location: ' . $redirect);
//    exit();
//}
////If the HTTPS is not found to be "on"
//if($LIVE && (!isset($_SERVER["HTTPS"]) || $_SERVER["HTTPS"] != "on"))
//{
//    //Tell the browser to redirect to the HTTPS URL.
//    header("Location: https://" . $_SERVER["HTTP_HOST"] . $_SERVER["REQUEST_URI"]);
//    //Prevent the rest of the script from executing.
//    exit;
//}

$params = strtok($_SERVER['REQUEST_URI'], '?');
$params = explode("/", $params);
$params = array_splice($params,1);
include("handle_login.php");
if (strcmp($params[0], "app") == 0) {
    $currUser = verifyLogin($_REQUEST, $LIVE);
    if ($currUser) { // if the login was good
        include("app.php");
    } else if ($LIVE) { // otherwise, if we're live, take them to login
        header("Location: https://" . gethostname() . "/login");
    } else { // this shouldn't happen
        echo "Invalid default credentials";
    }
} else if (strcmp($params[0], "login") == 0) {
    include("login.html");
} else if ($LIVE) { // if we're live and something weird happens, go home
    header("Location: https://" . gethostname() . "/app");
    exit;
}
?>