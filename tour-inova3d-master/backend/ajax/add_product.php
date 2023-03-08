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
$price = str_replace(",",".",$_POST['price']);
if(empty($price)) $price=0;
if($price<0) $price=0;

$query = "INSERT INTO svt_products(id_virtualtour,name,price) VALUES($id_virtualtour,'$name',$price); ";
$result = $mysqli->query($query);

if($result) {
    $insert_id = $mysqli->insert_id;
    ob_end_clean();
    echo json_encode(array("status"=>"ok","id"=>$insert_id));
} else {
    ob_end_clean();
    echo json_encode(array("status"=>"error","msg"=>$query));
}

