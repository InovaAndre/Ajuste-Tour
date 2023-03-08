<?php
error_reporting(E_ALL & ~E_WARNING & ~E_NOTICE & ~E_DEPRECATED);
ob_start();
session_start();
if((($_SERVER['SERVER_ADDR']=='5.9.29.89') && ($_SERVER['REMOTE_ADDR']!=$_SESSION['ip_developer']) && ($_SESSION['id_user']==1)) || ($_SESSION['svt_si']!=session_id())) {
    die();
}
require_once("../../db/connection.php");
require_once("../functions.php");
$id_user_del = $_POST['id_user'];
$id_user = $_SESSION['id_user'];
if(!get_user_role($id_user)=='administrator') {
    ob_end_clean();
    echo json_encode(array("status"=>"error"));
    exit;
}
$id_user_del = xor_deobfuscator($id_user_del);

$query = "DELETE FROM svt_users WHERE id=$id_user_del;";
$result = $mysqli->query($query);

if($result) {
    $mysqli->query("ALTER TABLE svt_users AUTO_INCREMENT = 1;");
    ob_end_clean();
    echo json_encode(array("status"=>"ok"));
} else {
    ob_end_clean();
    echo json_encode(array("status"=>"error"));
}

