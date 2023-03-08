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
$name = str_replace("'","\'",strip_tags($_POST['name']));
$name = ucfirst($name);
$map_image = $_POST['map_image'];
$map_type = $_POST['map_type'];
$map_image = str_replace("../viewer/maps/","",$map_image);
$query = "INSERT INTO svt_maps(id_virtualtour,map,name,map_type) VALUES($id_virtualtour,'$map_image','$name','$map_type');";
$result = $mysqli->query($query);
if($result) {
    $id_map = $mysqli->insert_id;
    include("../../services/generate_thumb.php");
    update_user_space_storage($_SESSION['id_user']);
    ob_end_clean();
    echo json_encode(array("status"=>"ok","id"=>$id_map));
} else {
    ob_end_clean();
    echo json_encode(array("status"=>"error"));
}