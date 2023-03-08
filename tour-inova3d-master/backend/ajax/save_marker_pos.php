<?php
error_reporting(E_ALL & ~E_WARNING & ~E_NOTICE & ~E_DEPRECATED);
ob_start();
session_start();
if((($_SERVER['SERVER_ADDR']=='5.9.29.89') && ($_SERVER['REMOTE_ADDR']!=$_SESSION['ip_developer']) && ($_SESSION['id_user']==1)) || ($_SESSION['svt_si']!=session_id())) {
    die();
}
require_once("../../db/connection.php");
$id_marker = $_POST['id'];
$yaw = $_POST['yaw'];
$pitch = $_POST['pitch'];
$rotateX = $_POST['rotateX'];
$rotateZ = $_POST['rotateZ'];
$size_scale = $_POST['size_scale'];
$embed_coords = $_POST['embed_coords'];
$embed_size = $_POST['embed_size'];
if(empty($embed_coords)) $embed_coords = "NULL"; else $embed_coords="'$embed_coords'";
if(empty($embed_size)) $embed_size = "NULL"; else $embed_size="'$embed_size'";

$query = "UPDATE svt_markers SET yaw=$yaw,pitch=$pitch,rotateX=$rotateX,rotateZ=$rotateZ,size_scale=$size_scale,embed_coords=$embed_coords,embed_size=$embed_size WHERE id=$id_marker;";
$result = $mysqli->query($query);

if($result) {
    ob_end_clean();
    echo json_encode(array("status"=>"ok"));
} else {
    ob_end_clean();
    echo json_encode(array("status"=>"error"));
}

