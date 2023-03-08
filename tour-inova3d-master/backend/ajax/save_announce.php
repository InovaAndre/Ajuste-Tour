<?php
error_reporting(E_ALL & ~E_WARNING & ~E_NOTICE & ~E_DEPRECATED);
ob_start();
session_start();
if((($_SERVER['SERVER_ADDR']=='5.9.29.89') && ($_SERVER['REMOTE_ADDR']!=$_SESSION['ip_developer']) && ($_SESSION['id_user']==1)) || ($_SESSION['svt_si']!=session_id())) {
    die();
}
require_once("../../db/connection.php");
$id_advertisement = $_POST['id'];
$name = str_replace("'","\'",strip_tags($_POST['name']));
$link = str_replace("'","\'",$_POST['link']);
$type = str_replace("t_","",$_POST['type']);
$image = $_POST['image'];
$video = $_POST['video'];
$iframe_link = str_replace("'","\'",$_POST['iframe_link']);
$youtube = str_replace("'","\'",$_POST['youtube']);
$countdown = $_POST['countdown'];
if(empty($countdown)) $countdown=0;
switch($type) {
    case 'video':
        if($countdown<-1) $countdown=-1;
        break;
    default:
        if($countdown<0) $countdown=0;
        break;
}
$auto_assign = $_POST['auto_assign'];
$list_s_vt = $_POST['list_s_vt'];
$list_p_vt = $_POST['list_p_vt'];
if(!empty($list_p_vt)) {
    if(count($list_p_vt)>0) {
        $id_plans = implode(",",$list_p_vt);
    } else {
        $id_plans = "";
    }
} else {
    $id_plans = "";
}
$query = "UPDATE svt_advertisements SET name='$name',type='$type',link='$link',image='$image',video='$video',iframe_link='$iframe_link',youtube='$youtube',countdown=$countdown,auto_assign=$auto_assign,id_plans='$id_plans' WHERE id=$id_advertisement;";
$result = $mysqli->query($query);
$mysqli->query("DELETE FROM svt_assign_advertisements WHERE id_advertisement=$id_advertisement;");
foreach ($list_s_vt as $id_vt) {
    $mysqli->query("DELETE FROM svt_assign_advertisements WHERE id_virtualtour=$id_vt;");
    $mysqli->query("INSERT INTO svt_assign_advertisements(id_advertisement,id_virtualtour) VALUES($id_advertisement,$id_vt);");
}
if($result) {
    if($auto_assign) {
        $mysqli->query("UPDATE svt_advertisements SET auto_assign=0 WHERE id!=$id_advertisement;");
    }
    ob_end_clean();
    echo json_encode(array("status"=>"ok"));
} else {
    ob_end_clean();
    echo json_encode(array("status"=>"error"));
}