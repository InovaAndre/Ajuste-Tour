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
if(empty($id_virtualtour)) $id_virtualtour='NULL';
$file = $_POST['file'];
$query = "INSERT INTO svt_media_library(id_virtualtour,file) VALUES($id_virtualtour,'$file');";
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