<?php
error_reporting(E_ALL & ~E_WARNING & ~E_NOTICE & ~E_DEPRECATED);
ob_start();
session_start();
if((($_SERVER['SERVER_ADDR']=='5.9.29.89') && ($_SERVER['REMOTE_ADDR']!=$_SESSION['ip_developer']) && ($_SESSION['id_user']==1)) || ($_SESSION['svt_si']!=session_id())) {
    die();
}
require_once("../../db/connection.php");
$id_virtualtour = $_POST['id_virtualtour'];
$password = str_replace("'","\'",$_POST['password']);
$password_title = str_replace("'","\'",$_POST['password_title']);
$password_description = str_replace("'","\'",$_POST['password_description']);
if(empty($password)) {
    $query = "UPDATE svt_virtualtours SET password=NULL,password_title='$password_title',password_description='$password_description' WHERE id=$id_virtualtour;";
} else {
    if($password=="keep_password") {
        $query = "UPDATE svt_virtualtours SET password_title='$password_title',password_description='$password_description' WHERE id=$id_virtualtour;";
    } else {
        $query = "UPDATE svt_virtualtours SET password=MD5('$password'),password_title='$password_title',password_description='$password_description' WHERE id=$id_virtualtour;";
    }
}
$result = $mysqli->query($query);
if($result) {
    ob_end_clean();
    echo json_encode(array("status"=>"ok"));
} else {
    ob_end_clean();
    echo json_encode(array("status"=>"error"));
}