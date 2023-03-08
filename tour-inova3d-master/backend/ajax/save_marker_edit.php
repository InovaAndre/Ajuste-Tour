<?php
error_reporting(E_ALL & ~E_WARNING & ~E_NOTICE & ~E_DEPRECATED);
ob_start();
session_start();
if((($_SERVER['SERVER_ADDR']=='5.9.29.89') && ($_SERVER['REMOTE_ADDR']!=$_SESSION['ip_developer']) && ($_SESSION['id_user']==1)) || ($_SESSION['svt_si']!=session_id())) {
    die();
}
require_once("../../db/connection.php");
$id_marker = $_POST['id'];
$id_room_target = $_POST['id_room_target'];
$yaw = $_POST['yaw'];
$pitch = $_POST['pitch'];
if($yaw=='') $yaw = 'NULL';
if($pitch=='') $pitch = 'NULL';
$lookat = $_POST['lookat'];

$query = "UPDATE svt_markers SET id_room_target=$id_room_target,yaw_room_target=$yaw,pitch_room_target=$pitch,lookat=$lookat WHERE id=$id_marker;";
$result = $mysqli->query($query);

if($result) {
    ob_end_clean();
    echo json_encode(array("status"=>"ok"));
} else {
    ob_end_clean();
    echo json_encode(array("status"=>"error"));
}

