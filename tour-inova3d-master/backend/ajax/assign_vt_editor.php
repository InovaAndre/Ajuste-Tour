<?php
error_reporting(E_ALL & ~E_WARNING & ~E_NOTICE & ~E_DEPRECATED);
ob_start();
session_start();
if((($_SERVER['SERVER_ADDR']=='5.9.29.89') && ($_SERVER['REMOTE_ADDR']!=$_SESSION['ip_developer']) && ($_SESSION['id_user']==1)) || ($_SESSION['svt_si']!=session_id())) {
    die();
}
require_once("../../db/connection.php");
require_once("../functions.php");

$id_vt = $_POST['id_vt'];
$id_user = $_POST['id_user'];
$checked = $_POST['checked'];

if($checked==1) {
    $mysqli->query("INSERT INTO svt_assign_virtualtours(id_user,id_virtualtour) VALUES($id_user,$id_vt);");
} else {
    $mysqli->query("DELETE FROM svt_assign_virtualtours WHERE id_user=$id_user AND id_virtualtour=$id_vt;");
}