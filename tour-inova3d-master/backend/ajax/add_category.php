<?php
error_reporting(E_ALL & ~E_WARNING & ~E_NOTICE & ~E_DEPRECATED);
ob_start();
session_start();
if((($_SERVER['SERVER_ADDR']=='5.9.29.89') && ($_SERVER['REMOTE_ADDR']!=$_SESSION['ip_developer']) && ($_SESSION['id_user']==1)) || ($_SESSION['svt_si']!=session_id())) {
    die();
}
require_once("../../db/connection.php");
$name = str_replace("'","\'",strip_tags($_POST['name']));
$query = "INSERT INTO svt_categories(name) VALUES('$name');";
$result = $mysqli->query($query);
if($result) {
    $id_category = $mysqli->insert_id;
    ob_end_clean();
    echo json_encode(array("status"=>"ok","id"=>$id_category));
} else {
    ob_end_clean();
    echo json_encode(array("status"=>"error"));
}