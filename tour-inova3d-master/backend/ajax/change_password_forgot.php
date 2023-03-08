<?php
error_reporting(E_ALL & ~E_WARNING & ~E_NOTICE & ~E_DEPRECATED);
ob_start();
require_once("../../db/connection.php");

$forgot_code = $_POST['forgot_code'];
$password = str_replace("'","\'",$_POST['password']);

$query = "SELECT id FROM svt_users WHERE forgot_code='$forgot_code' LIMIT 1;";
$result = $mysqli->query($query);
if($result) {
    if($result->num_rows==1) {
        $row = $result->fetch_array(MYSQLI_ASSOC);
        $id_user = $row['id'];
        $query = "UPDATE svt_users SET password=MD5('$password'),forgot_code='' WHERE id='$id_user';";
        $result = $mysqli->query($query);
        if($result) {
            ob_end_clean();
            echo json_encode(array("status"=>"ok"));
        } else {
            ob_end_clean();
            echo json_encode(array("status"=>"error","msg"=>_("Error, retry later")));
        }
    } else {
        ob_end_clean();
        echo json_encode(array("status"=>"error","msg"=>_("Invalid verification code")));
    }
} else {
    ob_end_clean();
    echo json_encode(array("status"=>"error","msg"=>_("Error, retry later")));
}

