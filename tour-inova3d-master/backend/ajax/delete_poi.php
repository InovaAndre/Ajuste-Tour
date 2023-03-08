<?php
error_reporting(E_ALL & ~E_WARNING & ~E_NOTICE & ~E_DEPRECATED);
ob_start();
session_start();
if((($_SERVER['SERVER_ADDR']=='5.9.29.89') && ($_SERVER['REMOTE_ADDR']!=$_SESSION['ip_developer']) && ($_SESSION['id_user']==1)) || ($_SESSION['svt_si']!=session_id())) {
    die();
}
require_once("../../db/connection.php");
require_once("../functions.php");
$id_poi = $_POST['id_poi'];
if(!check_can_delete($_SESSION['id_user'],$_SESSION['id_virtualtour_sel'])) {
    ob_end_clean();
    echo json_encode(array("status"=>"error"));
    die();
}
$query = "DELETE FROM svt_pois WHERE id=$id_poi;";
$result = $mysqli->query($query);
if($result) {
    $mysqli->query("UPDATE svt_rooms SET id_poi_autoopen=NULL WHERE id_poi_autoopen=$id_poi;");
    $mysqli->query("ALTER TABLE svt_pois AUTO_INCREMENT = 1;");
    $mysqli->query("ALTER TABLE svt_poi_gallery AUTO_INCREMENT = 1;");
    $mysqli->query("ALTER TABLE svt_poi_embedded_gallery AUTO_INCREMENT = 1;");
    $mysqli->query("ALTER TABLE svt_poi_objects360 AUTO_INCREMENT = 1;");
    include("../../services/clean_images.php");
    update_user_space_storage($_SESSION['id_user']);
    ob_end_clean();
    echo json_encode(array("status"=>"ok"));
} else {
    ob_end_clean();
    echo json_encode(array("status"=>"error"));
}