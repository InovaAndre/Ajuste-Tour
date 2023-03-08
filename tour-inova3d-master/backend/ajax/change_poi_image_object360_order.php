<?php
error_reporting(E_ALL & ~E_WARNING & ~E_NOTICE & ~E_DEPRECATED);
ob_start();
session_start();
if((($_SERVER['SERVER_ADDR']=='5.9.29.89') && ($_SERVER['REMOTE_ADDR']!=$_SESSION['ip_developer']) && ($_SESSION['id_user']==1)) || ($_SESSION['svt_si']!=session_id())) {
    die();
}
require_once("../../db/connection.php");
$id_poi = $_POST['id_poi'];
$array_images_priority = json_decode($_POST['array_images_priority'],true);

foreach ($array_images_priority as $priority=>$id) {
    $mysqli->query("UPDATE svt_poi_objects360 SET priority=$priority WHERE id=$id AND id_poi=$id_poi;");
}
require_once("../../services/rename_objects360_images.php");
ob_end_clean();