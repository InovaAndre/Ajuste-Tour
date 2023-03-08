<?php
error_reporting(E_ALL & ~E_WARNING & ~E_NOTICE & ~E_DEPRECATED);
ob_start();
require_once("../../db/connection.php");

$user_id = $_POST['user_id'];
$email = $_POST['email'];

$query = "SELECT * FROM svt_users WHERE email='$email';";
$result = $mysqli->query($query);
if($result) {
    if($result->num_rows>0) {
        ob_end_clean();
        echo json_encode(array("status"=>"error","msg"=>_("E-mail address already in use!")));
    } else {
        $query = "UPDATE svt_users SET email='$email' WHERE id=$user_id;";
        $result = $mysqli->query($query);
        if($result) {
            ob_end_clean();
            echo json_encode(array("status"=>"ok"));
        } else {
            ob_end_clean();
            echo json_encode(array("status"=>"error","msg"=>_("Error, retry later!")));
        }
    }
} else {
    ob_end_clean();
    echo json_encode(array("status"=>"error","msg"=>_("Error, retry later!")));
}



