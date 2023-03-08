<?php
error_reporting(E_ALL & ~E_WARNING & ~E_NOTICE & ~E_DEPRECATED);
ob_start();
session_start();
if((($_SERVER['SERVER_ADDR']=='5.9.29.89') && ($_SERVER['REMOTE_ADDR']!=$_SESSION['ip_developer']) && ($_SESSION['id_user']==1)) || ($_SESSION['svt_si']!=session_id())) {
    die();
}
require_once("../../db/connection.php");
$id_virtualtour = $_POST['id_virtualtour'];
$array_rooms_priority = json_decode($_POST['array_rooms_priority'],true);

foreach ($array_rooms_priority as $priority=>$id) {
    $mysqli->query("UPDATE svt_rooms SET priority=$priority WHERE id=$id AND id_virtualtour=$id_virtualtour;");
}
ob_end_clean();