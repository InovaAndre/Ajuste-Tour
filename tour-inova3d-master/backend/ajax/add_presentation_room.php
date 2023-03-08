<?php
error_reporting(E_ALL & ~E_WARNING & ~E_NOTICE & ~E_DEPRECATED);
ob_start();
session_start();
if((($_SERVER['SERVER_ADDR']=='5.9.29.89') && ($_SERVER['REMOTE_ADDR']!=$_SESSION['ip_developer']) && ($_SESSION['id_user']==1)) || ($_SESSION['svt_si']!=session_id())) {
    die();
}
require_once("../../db/connection.php");
$id_room = $_POST['id_room'];
$sleep = $_POST['sleep'];
$id_virtualtour = $_POST['id_virtualtour'];
if($sleep=='') $sleep=0;

$query = "SELECT IFNULL(MAX(priority_1),0) as priority_1 FROM svt_presentations WHERE id_virtualtour=$id_virtualtour;";
$result = $mysqli->query($query);
if($result) {
    if ($result->num_rows == 1) {
        $row = $result->fetch_array(MYSQLI_ASSOC);
        $priority_1 = $row['priority_1'];
    } else {
        ob_end_clean();
        echo json_encode(array("status"=>"error"));
        die();
    }
} else {
    ob_end_clean();
    echo json_encode(array("status"=>"error"));
    die();
}

$priority_1 = $priority_1 + 1;
$result = $mysqli->query("INSERT INTO svt_presentations(id_virtualtour,id_room,action,params,sleep,priority_1,priority_2) VALUES($id_virtualtour,$id_room,'goto','$id_room',$sleep,$priority_1,1);");
if($result) {
    ob_end_clean();
    echo json_encode(array("status"=>"ok"));
} else {
    ob_end_clean();
    echo json_encode(array("status"=>"error"));
}
