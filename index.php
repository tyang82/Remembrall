<?php

$params = strtok($_SERVER['REQUEST_URI'], '?');
$params = explode("/", $params);
$params = array_splice($params,1);
//$params = array_map('strtolower', $params);

// array_shift($params);
// if(empty(end($params))){
//     array_pop($params);
// }

if (empty($params) || strcmp($params[0], "home")==0) {
    include("home_screen.php");
} else if (strcmp($params[0], "login")==0) {
    include("login_page.html");
}

// if(count($params)==3){
//     $focus_topic = $params[0];
//     $question_type = $params[1];
//     $sequence_number = intval($params[2]);
//     $question_list = getQuestionList($conn);
//     $question_data = getQuestion($conn, $focus_topic, $question_type, $sequence_number);
//     include("./content/multiple_choice.php");
// } else if(count($params)>1 && strcmp($params[0], "focus")==0) {
//     $focus_topic = $params[1];
//     if(count($params)==4){
//         $seq_num = intval($params[3]);
//     }else{
//         $seq_num = 1;
//     }
//     $data = getFocusList($conn, $focus_topic, $seq_num);
//     $focus_list = $data["results"];
//     $question_id = $data["question_id"];
//     $prev_question = $data["prev_question"];
//     $next_question = $data["next_question"];
//     $rand_question = $data["rand_question"];

//     $question_data = getFocusQuestion($conn, $question_id, $seq_num, $focus_topic, $prev_question, $next_question, $rand_question);
//     $focus_options = getFocusOptions($conn);
//     include("./content/focus.php");
// } else {
//     $question_list = getQuestionList($conn);
//     include("./content/home.php");
// }

// killConnection($conn);

?>