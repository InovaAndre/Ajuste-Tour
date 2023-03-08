<?php
error_reporting(E_ALL & ~E_WARNING & ~E_NOTICE & ~E_DEPRECATED);
ob_start();
session_start();
if((($_SERVER['SERVER_ADDR']=='5.9.29.89') && ($_SERVER['REMOTE_ADDR']!=$_SESSION['ip_developer']) && ($_SESSION['id_user']==1)) || ($_SESSION['svt_si']!=session_id())) {
    die();
}
require_once("../../db/connection.php");
require_once("../functions.php");
$id_virtualtour = $_POST['id_virtualtour'];
$id_map = $_POST['id_map'];
if(!check_can_delete($_SESSION['id_user'],$_SESSION['id_virtualtour_sel'])) {
    ob_end_clean();
    echo json_encode(array("status"=>"error"));
    die();
}
$query = "SELECT map_type FROM svt_maps WHERE id=$id_map LIMIT 1;";
$result = $mysqli->query($query);
if($result) {
    if($result->num_rows==1) {
        $row = $result->fetch_array(MYSQLI_ASSOC);
        $map_type = $row['map_type'];
    }
}
switch ($map_type) {
    case 'floorplan':
        $query = "UPDATE svt_rooms SET id_map=NULL,map_left=NULL,map_top=NULL WHERE id_virtualtour=$id_virtualtour AND id_map=$id_map;";
        break;
    case 'map':
        $query = "UPDATE svt_rooms SET lat=NULL,lon=NULL WHERE id_virtualtour=$id_virtualtour;";
        break;
}
$result = $mysqli->query($query);
$query = "DELETE FROM svt_maps WHERE id=$id_map;";
$result = $mysqli->query($query);
if($result) {
    $mysqli->query("ALTER TABLE svt_maps AUTO_INCREMENT = 1;");
    include("../../services/clean_images.php");
    update_user_space_storage($_SESSION['id_user']);
    ob_end_clean();
    echo json_encode(array("status"=>"ok"));
} else {
    ob_end_clean();
    echo json_encode(array("status"=>"error"));
}