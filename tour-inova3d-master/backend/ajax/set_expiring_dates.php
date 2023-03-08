<?php
error_reporting(E_ALL & ~E_WARNING & ~E_NOTICE & ~E_DEPRECATED);
ob_start();
session_start();
if((($_SERVER['SERVER_ADDR']=='5.9.29.89') && ($_SERVER['REMOTE_ADDR']!=$_SESSION['ip_developer']) && ($_SESSION['id_user']==1)) || ($_SESSION['svt_si']!=session_id())) {
    die();
}
require_once("../../db/connection.php");

$id_virtualtour = $_POST['id_virtualtour'];
$start_date = $_POST['start_date'];
$end_date = $_POST['end_date'];
$start_url = str_replace("'","\'",strip_tags($_POST['start_url']));
$end_url = str_replace("'","\'",strip_tags($_POST['end_url']));

$query = "UPDATE svt_virtualtours SET start_date='$start_date',end_date='$end_date',start_url='$start_url',end_url='$end_url' WHERE id=$id_virtualtour;";
$result = $mysqli->query($query);

if($result) {
    ob_end_clean();
    echo json_encode(array("status"=>"ok"));
} else {
    ob_end_clean();
    echo json_encode(array("status"=>"error"));
}

