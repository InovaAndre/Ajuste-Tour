<?php
error_reporting(E_ALL & ~E_WARNING & ~E_NOTICE & ~E_DEPRECATED);
ob_start();
session_start();
if((($_SERVER['SERVER_ADDR']=='5.9.29.89') && ($_SERVER['REMOTE_ADDR']!=$_SESSION['ip_developer']) && ($_SESSION['id_user']==1)) || ($_SESSION['svt_si']!=session_id())) {
    die();
}
require_once("../../db/connection.php");
$id_virtualtour = $_POST['id_virtualtour'];
$id_room = $_POST['id_room'];
$yaw = $_POST['yaw'];
$pitch = $_POST['pitch'];
$yaw_m = $_POST['yaw_m'];
$pitch_m = $_POST['pitch_m'];
if($yaw_m=='') $yaw_m = 'NULL';
if($pitch_m=='') $pitch_m = 'NULL';
$id_room_target = $_POST['id_room_target'];
$rotateX = 0;
$rotateZ = 0;
$embed_type = $_POST['embed_type'];
if(empty($embed_type)) {
    $embed_type="NULL";
    $embed_coords="NULL";
    $embed_size="NULL";
    $embed_content="NULL";
} else {
    $embed_type="'$embed_type'";
    $coord_1 = ($pitch+5).",".($yaw-10);
    $coord_2 = ($pitch-5).",".($yaw-10);
    $coord_3 = ($pitch+5).",".($yaw+10);
    $coord_4 = ($pitch-5).",".($yaw+10);
    $embed_coords = "'$coord_1|$coord_2|$coord_3|$coord_4'";
    $embed_size = "'300,150'";
    if($_POST['embed_type']=='selection') {
        $embed_content="'border-width:3px;'";
    } else {
        $embed_content="''";
    }
}
$lookat = $_POST['lookat'];

$query_v = "SELECT markers_icon,markers_id_icon_library,markers_color,markers_background,markers_show_room,markers_tooltip_type FROM svt_virtualtours WHERE id=$id_virtualtour LIMIT 1;";
$result_v = $mysqli->query($query_v);
if($result_v) {
    if ($result_v->num_rows == 1) {
        $row_v = $result_v->fetch_array(MYSQLI_ASSOC);
        $markers_icon = $row_v['markers_icon'];
        $markers_id_icon_library = $row_v['markers_id_icon_library'];
        if($_POST['embed_type']=='selection') {
            $markers_color = 'rgb(255,255,255)';
            $markers_background = 'rgba(255,255,255,0.1)';
        } else {
            $markers_color = $row_v['markers_color'];
            $markers_background = $row_v['markers_background'];
        }
        $markers_show_room = $row_v['markers_show_room'];
        $markers_tooltip_type = $row_v['markers_tooltip_type'];
        $query = "INSERT INTO svt_markers(id_room,yaw,pitch,id_room_target,rotateX,rotateZ,icon,id_icon_library,color,background,show_room,tooltip_type,yaw_room_target,pitch_room_target,embed_type,embed_coords,embed_size,embed_content,lookat) VALUES($id_room,$yaw,$pitch,$id_room_target,$rotateX,$rotateZ,'$markers_icon',$markers_id_icon_library,'$markers_color','$markers_background',$markers_show_room,'$markers_tooltip_type',$yaw_m,$pitch_m,$embed_type,$embed_coords,$embed_size,$embed_content,$lookat);";
        $result = $mysqli->query($query);
        if($result) {
            $insert_id = $mysqli->insert_id;
            ob_end_clean();
            echo json_encode(array("status"=>"ok","id"=>$insert_id));
        } else {
            ob_end_clean();
            echo json_encode(array("status"=>"error"));
        }
    } else {
        ob_end_clean();
        echo json_encode(array("status"=>"error"));
    }
} else {
    ob_end_clean();
    echo json_encode(array("status"=>"error"));
}

