<?php
error_reporting(E_ALL & ~E_WARNING & ~E_NOTICE & ~E_DEPRECATED);
ob_start();
session_start();
if((($_SERVER['SERVER_ADDR']=='5.9.29.89') && ($_SERVER['REMOTE_ADDR']!=$_SESSION['ip_developer']) && ($_SESSION['id_user']==1)) || ($_SESSION['svt_si']!=session_id())) {
    die();
}
require_once("../../db/connection.php");
$id = $_POST['id'];
$content_type = $_POST['content_type'];
if(empty($content_type)) {
    $query = "UPDATE svt_pois SET type=NULL,content=NULL WHERE id=$id;";
} else {
    $query = "UPDATE svt_pois SET type='$content_type',content=NULL WHERE id=$id;";
}
$result=$mysqli->query($query);
if($result) {
    ob_end_clean();
    echo json_encode(array("status"=>"ok"));
} else {
    ob_end_clean();
    echo json_encode(array("status"=>"error"));
}
