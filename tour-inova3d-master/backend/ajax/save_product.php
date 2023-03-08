<?php
error_reporting(E_ALL & ~E_WARNING & ~E_NOTICE & ~E_DEPRECATED);
ob_start();
session_start();
if((($_SERVER['SERVER_ADDR']=='5.9.29.89') && ($_SERVER['REMOTE_ADDR']!=$_SESSION['ip_developer']) && ($_SESSION['id_user']==1)) || ($_SESSION['svt_si']!=session_id())) {
    die();
}
require_once("../../db/connection.php");
$id_product = $_POST['id'];

$name = str_replace("'","\'",strip_tags($_POST['name']));
$price = $_POST['price'];
if(empty($price)) $price=0;
if($price<0) $price=0;
$description = str_replace("'","\'",$_POST['description']);
if($description=='<p><br></p>') $description="";
$link = str_replace("'","\'",$_POST['link']);
$purchase_type = $_POST['purchase_type'];

$query = "UPDATE svt_products SET name='$name',price=$price,description='$description',link='$link',purchase_type='$purchase_type' WHERE id=$id_product;";
$result = $mysqli->query($query);

if($result) {
    ob_end_clean();
    echo json_encode(array("status"=>"ok"));
} else {
    ob_end_clean();
    echo json_encode(array("status"=>"error"));
}

