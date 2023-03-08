<?php
error_reporting(E_ALL & ~E_WARNING & ~E_NOTICE & ~E_DEPRECATED);
ob_start();
session_start();
if($_SESSION['svt_si']!=session_id()) {
    die();
}

require_once("../../db/connection.php");
require_once("../functions.php");

$settings = get_settings();
$id_plan = $settings['default_id_plan'];

$username = str_replace("'","\'",$_POST['username_svt']);
$email = str_replace("'","\'",$_POST['email_svt']);
$password = str_replace("'","\'",$_POST['password_svt']);

$query_check = "SELECT * FROM svt_users WHERE username = '$username';";
$result_check = $mysqli->query($query_check);
if($result_check->num_rows>0) {
    ob_end_clean();
    echo json_encode(array("status"=>"error","msg"=>_("Username already registered!")));
    exit;
}
$query_check = "SELECT * FROM svt_users WHERE email = '$email';";
$result_check = $mysqli->query($query_check);
if($result_check->num_rows>0) {
    ob_end_clean();
    echo json_encode(array("status"=>"error","msg"=>_("E-mail already registered!")));
    exit;
}

$validate_email = $settings['validate_email'];
if($validate_email) {
    $active = 0;
    $hash = md5(rand(0,1000));
} else {
    $active = 1;
    $hash = "";
}

$query = "INSERT INTO svt_users(username,email,password,role,id_plan,active,hash) VALUES('$username','$email',MD5('$password'),'customer',$id_plan,$active,'$hash'); ";
$result = $mysqli->query($query);

if($result) {
    $user_id = $mysqli->insert_id;
    if(!$validate_email) $_SESSION['id_user'] = $user_id;
    update_plans_expires_date($user_id);
    ob_end_clean();
    echo json_encode(array("status"=>"ok","id_user"=>$user_id,"validate_email"=>$validate_email,"hash"=>$hash));
} else {
    ob_end_clean();
    echo json_encode(array("status"=>"error","msg"=>_("Error")));
}

