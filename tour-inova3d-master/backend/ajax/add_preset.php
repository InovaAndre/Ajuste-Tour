<?php
error_reporting(E_ALL & ~E_WARNING & ~E_NOTICE & ~E_DEPRECATED);
ob_start();
session_start();
if((($_SERVER['SERVER_ADDR']=='5.9.29.89') && ($_SERVER['REMOTE_ADDR']!=$_SESSION['ip_developer']) && ($_SESSION['id_user']==1)) || ($_SESSION['svt_si']!=session_id())) {
    die();
}
require_once("../../db/connection.php");

$id_virtualtour = $_POST['id_virtualtour'];
$name = str_replace("'","\'",$_POST['name']);
$type = $_POST['type'];
$value = str_replace("'","\'",$_POST['value']);

$query = "INSERT INTO svt_presets(id_virtualtour,name,type,value) VALUES($id_virtualtour,'$name','$type','$value'); ";
$result = $mysqli->query($query);

if($result) {
    $insert_id = $mysqli->insert_id;
    ob_end_clean();
    echo json_encode(array("status"=>"ok","id"=>$insert_id));
} else {
    ob_end_clean();
    echo json_encode(array("status"=>"error","msg"=>$mysqli->error));
}

