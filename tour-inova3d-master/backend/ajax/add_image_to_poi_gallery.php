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
$image = $_POST['image'];
$priority = 0;
$query = "SELECT MAX(priority)+1 as priority FROM svt_poi_gallery WHERE id_poi=$id_poi LIMIT 1;";
$result = $mysqli->query($query);
if($result) {
    if($result->num_rows==1) {
        $row = $result->fetch_array(MYSQLI_ASSOC);
        $priority = $row['priority'];
        if(empty($priority)) $priority=0;
    }
}
$query = "INSERT INTO svt_poi_gallery(id_poi,image,priority) VALUES($id_poi,'$image',$priority);";
$result = $mysqli->query($query);
if($result) {
    require_once("../../services/generate_thumb.php");
    update_user_space_storage($_SESSION['id_user']);
    ob_end_clean();
    echo json_encode(array("status"=>"ok"));
} else {
    ob_end_clean();
    echo json_encode(array("status"=>"error"));
}