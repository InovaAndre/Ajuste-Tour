<?php
error_reporting(E_ALL & ~E_WARNING & ~E_NOTICE & ~E_DEPRECATED);
ob_start();
session_start();
if((($_SERVER['SERVER_ADDR']=='5.9.29.89') && ($_SERVER['REMOTE_ADDR']!=$_SESSION['ip_developer']) && ($_SESSION['id_user']==1)) || ($_SESSION['svt_si']!=session_id())) {
    die();
}
require_once("../../db/connection.php");
require_once("../functions.php");
if(get_user_role($_SESSION['id_user']) != 'administrator') {
    die();
}
$id_user = $_POST['id_user'];
$id_virtualtour = $_POST['id_virtualtour'];
if(get_user_role($id_user)=='administrator') {
    $query = "SELECT id FROM svt_virtualtours WHERE id=$id_virtualtour; ";
} else {
    $query = "SELECT id FROM svt_virtualtours WHERE id_user=$id_user AND id=$id_virtualtour; ";
}
$result = $mysqli->query($query);
if($result) {
    if($result->num_rows==1) {
        $mysqli->query("DELETE FROM svt_forms_data WHERE id_virtualtour=$id_virtualtour;");
        ob_end_clean();
        echo json_encode(array("status"=>"ok"));
    } else {
        ob_end_clean();
        echo json_encode(array("status"=>"error"));
    }
} else {
    ob_end_clean();
    echo json_encode(array("status"=>"error"));
}
