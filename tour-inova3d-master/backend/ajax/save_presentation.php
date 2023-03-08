<?php
error_reporting(E_ALL & ~E_WARNING & ~E_NOTICE & ~E_DEPRECATED);
ob_start();
session_start();
if((($_SERVER['SERVER_ADDR']=='5.9.29.89') && ($_SERVER['REMOTE_ADDR']!=$_SESSION['ip_developer']) && ($_SESSION['id_user']==1)) || ($_SESSION['svt_si']!=session_id())) {
    die();
}
require_once("../../db/connection.php");
$id_virtualtour = $_POST['id_virtualtour'];
$presentation_type = $_POST['presentation_type'];
$presentation_video = $_POST['presentation_video'];
$auto_presentation_speed = $_POST['auto_presentation_speed'];
if(($auto_presentation_speed=='') || ($auto_presentation_speed==0)) {
    $auto_presentation_speed = 10;
};

$result = $mysqli->query("UPDATE svt_virtualtours SET auto_presentation_speed=$auto_presentation_speed,presentation_type='$presentation_type',presentation_video='$presentation_video' WHERE id=$id_virtualtour;");
if($result) {
    ob_end_clean();
    echo json_encode(array("status"=>"ok"));
} else {
    ob_end_clean();
    echo json_encode(array("status"=>"error"));
}
