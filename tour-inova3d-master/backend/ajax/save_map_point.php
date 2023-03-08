<?php
error_reporting(E_ALL & ~E_WARNING & ~E_NOTICE & ~E_DEPRECATED);
ob_start();
session_start();
if((($_SERVER['SERVER_ADDR']=='5.9.29.89') && ($_SERVER['REMOTE_ADDR']!=$_SESSION['ip_developer']) && ($_SESSION['id_user']==1)) || ($_SESSION['svt_si']!=session_id())) {
    die();
}
require_once("../../db/connection.php");
$id_room = $_POST['id_room'];
$position = $_POST['position'];
$map_type = $_POST['map_type'];
$tmp = explode(",",$position);
$top = $tmp[0];
$left = $tmp[1];

switch ($map_type) {
    case 'floorplan':
        $query = "UPDATE svt_rooms SET map_top=$top,map_left=$left WHERE id=$id_room;";
        break;
    case 'map':
        $query = "UPDATE svt_rooms SET lat='$top',lon='$left' WHERE id=$id_room;";
        break;
}
$result = $mysqli->query($query);

if($result) {
    ob_end_clean();
    echo json_encode(array("status"=>"ok"));
} else {
    ob_end_clean();
    echo json_encode(array("status"=>"error"));
}

