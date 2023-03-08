<?php
error_reporting(E_ALL & ~E_WARNING & ~E_NOTICE & ~E_DEPRECATED);
ob_start();
session_start();
if((($_SERVER['SERVER_ADDR']=='5.9.29.89') && ($_SERVER['REMOTE_ADDR']!=$_SESSION['ip_developer']) && ($_SESSION['id_user']==1)) || ($_SESSION['svt_si']!=session_id())) {
    die();
}
require_once("../../db/connection.php");

$username = str_replace("'","\'",strip_tags($_POST['username_svt']));
$email = str_replace("'","\'",strip_tags($_POST['email_svt']));
$password = str_replace("'","\'",$_POST['password_svt']);
$role = $_POST['role_svt'];

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

$query = "INSERT INTO svt_users(username,email,password,role) VALUES('$username','$email',MD5('$password'),'$role'); ";
$result = $mysqli->query($query);

if($result) {
    ob_end_clean();
    echo json_encode(array("status"=>"ok"));
} else {
    ob_end_clean();
    echo json_encode(array("status"=>"error","msg"=>_("Error")));
}

