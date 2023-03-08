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
$field = $_POST['field'];
$checked = $_POST['checked'];

$mysqli->query("UPDATE svt_assign_virtualtours SET $field=$checked WHERE id_virtualtour=$id_vt AND id_user=$id_user;");