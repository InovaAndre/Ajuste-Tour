<?php
error_reporting(E_ALL & ~E_WARNING & ~E_NOTICE & ~E_DEPRECATED);
ob_start();
session_start();
if((($_SERVER['SERVER_ADDR']=='5.9.29.89') && ($_SERVER['REMOTE_ADDR']!=$_SESSION['ip_developer']) && ($_SESSION['id_user']==1)) || ($_SESSION['svt_si']!=session_id())) {
    die();
}
require_once("../../db/connection.php");

$id = $_POST['id'];
$name = str_replace("'","\'",$_POST['name']);

$query = "UPDATE svt_maps SET name='$name' WHERE id=$id; ";
$result = $mysqli->query($query);

if($result) {
    ob_end_clean();
    echo json_encode(array("status"=>"ok"));
} else {
    ob_end_clean();
    echo json_encode(array("status"=>"error","msg"=>$mysqli->error));
}

