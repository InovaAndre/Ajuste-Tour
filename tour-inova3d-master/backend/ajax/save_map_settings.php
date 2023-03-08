<?php
error_reporting(E_ALL & ~E_WARNING & ~E_NOTICE & ~E_DEPRECATED);
ob_start();
session_start();
if((($_SERVER['SERVER_ADDR']=='5.9.29.89') && ($_SERVER['REMOTE_ADDR']!=$_SESSION['ip_developer']) && ($_SESSION['id_user']==1)) || ($_SESSION['svt_si']!=session_id())) {
    die();
}
require_once("../../db/connection.php");
$id_map = $_POST['id_map'];
$map_name = str_replace("'","\'",strip_tags($_POST['map_name']));
$point_color = $_POST['point_color'];
$point_size = $_POST['point_size'];
$north_degree = $_POST['north_degree'];
if((empty($point_size)) || ($point_size<=0)) {
    $point_size=20;
}
$zoom_level = $_POST['zoom_level'];
$zoom_to_point = $_POST['zoom_to_point'];
$width_d = $_POST['width_d'];
$width_m = $_POST['width_m'];
$default_view = $_POST['default_view'];
$info_link = str_replace("'","\'",$_POST['info_link']);
$info_type = $_POST['info_type'];
$id_room_default = $_POST['id_room_default'];
if((empty($width_d)) || ($width_d<=0)) {
    $width_d=300;
}
if((empty($width_m)) || ($width_m<=0)) {
    $width_d=225;
}
if(empty($id_room_default) || $id_room_default==0) {
    $id_room_default='NULL';
}

$query = "UPDATE svt_maps SET name='$map_name',point_color='$point_color',point_size=$point_size,north_degree=$north_degree,zoom_level=$zoom_level,zoom_to_point=$zoom_to_point,width_d=$width_d,width_m=$width_m,default_view='$default_view',info_link='$info_link',info_type='$info_type',id_room_default=$id_room_default WHERE id=$id_map;";
$result = $mysqli->query($query);

if($result) {
    ob_end_clean();
    echo json_encode(array("status"=>"ok"));
} else {
    ob_end_clean();
    echo json_encode(array("status"=>"error"));
}

