<?php
error_reporting(E_ALL & ~E_WARNING & ~E_NOTICE & ~E_DEPRECATED);
ob_start();
session_start();
if((($_SERVER['SERVER_ADDR']=='5.9.29.89') && ($_SERVER['REMOTE_ADDR']!=$_SESSION['ip_developer']) && ($_SESSION['id_user']==1)) || ($_SESSION['svt_si']!=session_id())) {
    die();
}
require_once("../../db/connection.php");
$id_plan = $_POST['id'];

$query_check = "SELECT default_id_plan FROM svt_settings LIMIT 1;";
$result_check = $mysqli->query($query_check);
if($result_check) {
    if($result_check->num_rows==1) {
        $row_check = $result_check->fetch_array(MYSQLI_ASSOC);
        if($id_plan==$row_check['default_id_plan']) {
            ob_end_clean();
            echo json_encode(array("status"=>"error","msg"=>"Can't delete default plan assigned to registration."));
            exit;
        }
    }
}

$query_check = "SELECT id_plan FROM svt_users WHERE id_plan=$id_plan;";
$result_check = $mysqli->query($query_check);
if($result_check) {
    if($result_check->num_rows>0) {
        ob_end_clean();
        echo json_encode(array("status"=>"error","msg"=>"Can't delete a plan assigned to ".$result_check->num_rows." users."));
        exit;
    }
}

$query = "DELETE FROM svt_plans WHERE id=$id_plan; ";
$result = $mysqli->query($query);

if($result) {
    $mysqli->query("ALTER TABLE svt_plans AUTO_INCREMENT = 1;");
    ob_end_clean();
    echo json_encode(array("status"=>"ok"));
} else {
    ob_end_clean();
    echo json_encode(array("status"=>"error"));
}
