<?php
error_reporting(E_ALL & ~E_WARNING & ~E_NOTICE & ~E_DEPRECATED);
ob_start();
session_start();
if((($_SERVER['SERVER_ADDR']=='5.9.29.89') && ($_SERVER['REMOTE_ADDR']!=$_SESSION['ip_developer']) && ($_SESSION['id_user']==1)) || ($_SESSION['svt_si']!=session_id())) {
    die();
}
require_once("../../db/connection.php");
require_once("../functions.php");

$settings = get_settings();

$id_virtualtour = $_POST['id_virtualtour'];
$friendly_url = str_replace("'","",strip_tags($_POST['friendly_url']));
$friendly_url = str_replace("\"","",$friendly_url);
$friendly_url = str_replace(" ","_",$friendly_url);
$friendly_url = strtolower($friendly_url);

$furl_blacklist = explode(",",$settings['furl_blacklist']);

if(get_user_role($_SESSION['id_user']!='administrator')) {
    if($friendly_url!='') {
        if(in_array($friendly_url,$furl_blacklist)) {
            ob_end_clean();
            echo json_encode(array("status"=>"error"));
            exit;
        }
    }
}

if(!empty($friendly_url)) {
    $query_check = "SELECT id FROM svt_virtualtours WHERE friendly_url='$friendly_url' AND id != $id_virtualtour;";
    $result_check = $mysqli->query($query_check);
    if($result_check) {
        if($result_check->num_rows>0) {
            ob_end_clean();
            echo json_encode(array("status"=>"error"));
            exit;
        }
    }
}

$query = "UPDATE svt_virtualtours SET friendly_url='$friendly_url' WHERE id=$id_virtualtour;";
$result = $mysqli->query($query);

if($result) {
    ob_end_clean();
    echo json_encode(array("status"=>"ok"));
} else {
    ob_end_clean();
    echo json_encode(array("status"=>"error"));
}

