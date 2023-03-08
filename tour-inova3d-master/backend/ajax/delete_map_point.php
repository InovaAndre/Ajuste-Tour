<?php
error_reporting(E_ALL & ~E_WARNING & ~E_NOTICE & ~E_DEPRECATED);
ob_start();
session_start();
if((($_SERVER['SERVER_ADDR']=='5.9.29.89') && ($_SERVER['REMOTE_ADDR']!=$_SESSION['ip_developer']) && ($_SESSION['id_user']==1)) || ($_SESSION['svt_si']!=session_id())) {
    die();
}
require_once("../../db/connection.php");
require_once("../functions.php");
$id_room = $_POST['id_room'];
$map_type = $_POST['map_type'];

if(!check_can_delete($_SESSION['id_user'],$_SESSION['id_virtualtour_sel'])) {
    ob_end_clean();
    echo json_encode(array("status"=>"error"));
    die();
}

switch ($map_type) {
    case 'floorplan':
        $query = "UPDATE svt_rooms SET id_map=NULL,map_left=NULL,map_top=NULL WHERE id=$id_room;";
        break;
    case 'map':
        $query = "UPDATE svt_rooms SET lat=NULL,lon=NULL WHERE id=$id_room;";
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

