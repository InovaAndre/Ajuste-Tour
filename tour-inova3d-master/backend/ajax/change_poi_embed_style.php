<?php
error_reporting(E_ALL & ~E_WARNING & ~E_NOTICE & ~E_DEPRECATED);
ob_start();
session_start();
if((($_SERVER['SERVER_ADDR']=='5.9.29.89') && ($_SERVER['REMOTE_ADDR']!=$_SESSION['ip_developer']) && ($_SESSION['id_user']==1)) || ($_SESSION['svt_si']!=session_id())) {
    die();
}
require_once("../../db/connection.php");
$id = $_POST['id'];
$yaw = $_POST['yaw'];
$pitch = $_POST['pitch'];
$embed_type = $_POST['embed_type'];
if(empty($embed_type)) {
    $query = "UPDATE svt_pois SET embed_type=NULL WHERE id=$id;";
} else {
    $coord_1 = ($pitch+5).",".($yaw-10);
    $coord_2 = ($pitch-5).",".($yaw-10);
    $coord_3 = ($pitch+5).",".($yaw+10);
    $coord_4 = ($pitch-5).",".($yaw+10);
    $embed_coords = "$coord_1|$coord_2|$coord_3|$coord_4";
    $embed_size = "300,150";
    $content_q_add = "";
    switch($_POST['embed_type']) {
        case 'gallery':
        case 'video':
        case 'video_chroma':
        case 'video_transparent':
        case 'link':
            $embed_content="";
            $content_q_add = ",type=NULL,content=NULL";
            break;
        case 'selection':
            $embed_content="border-width:3px;";
            break;
        default:
            $embed_content="";
            break;
    }
    $query = "UPDATE svt_pois SET embed_type='$embed_type',embed_size='$embed_size',embed_coords='$embed_coords',embed_content='$embed_content' $content_q_add WHERE id=$id;";
}
$result=$mysqli->query($query);
if($result) {
    ob_end_clean();
    echo json_encode(array("status"=>"ok"));
} else {
    ob_end_clean();
    echo json_encode(array("status"=>"error"));
}
