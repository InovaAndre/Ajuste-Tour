<?php
error_reporting(E_ALL & ~E_WARNING & ~E_NOTICE & ~E_DEPRECATED);
ob_start();
session_start();
if((($_SERVER['SERVER_ADDR']=='5.9.29.89') && ($_SERVER['REMOTE_ADDR']!=$_SESSION['ip_developer']) && ($_SESSION['id_user']==1)) || ($_SESSION['svt_si']!=session_id())) {
    die();
}
require_once("../../db/connection.php");
require_once("../functions.php");
$id = $_POST['id'];
$query = "DELETE FROM svt_icons WHERE id=$id;";
$result = $mysqli->query($query);
if($result) {
    $mysqli->query("UPDATE svt_markers SET show_room=0,id_icon_library=0 WHERE id_icon_library=$id;");
    $mysqli->query("UPDATE svt_pois SET style=0,id_icon_library=0 WHERE id_icon_library=$id;");
    $mysqli->query("ALTER TABLE svt_icons AUTO_INCREMENT = 1;");
    require_once("../../services/clean_images.php");
    update_user_space_storage($_SESSION['id_user']);
    ob_end_clean();
    echo json_encode(array("status"=>"ok"));
} else {
    ob_end_clean();
    echo json_encode(array("status"=>"error"));
}