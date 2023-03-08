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
$yaw = (float) $_POST['yaw'];
$pitch = (float) $_POST['pitch'];
$type = $_POST['type'];
if(empty($type)) $type="NULL"; else $type="'$type'";
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

$query_v = "SELECT pois_icon,pois_id_icon_library,pois_color,pois_background,pois_style,pois_tooltip_type FROM svt_virtualtours WHERE id=$id_virtualtour LIMIT 1;";
$result_v = $mysqli->query($query_v);
if($result_v) {
    if ($result_v->num_rows == 1) {
        $row_v = $result_v->fetch_array(MYSQLI_ASSOC);
        $pois_icon = $row_v['pois_icon'];
        $pois_id_icon_library = $row_v['pois_id_icon_library'];
        if($_POST['embed_type']=='selection') {
            $pois_color = 'rgb(255,255,255)';
            $pois_background = 'rgba(255,255,255,0.1)';
        } else {
            $pois_color = $row_v['pois_color'];
            $pois_background = $row_v['pois_background'];
        }
        $pois_style = $row_v['pois_style'];
        $pois_tooltip_type = $row_v['pois_tooltip_type'];
        $query = "INSERT INTO svt_pois(id_room,yaw,pitch,type,icon,id_icon_library,color,background,style,tooltip_type,embed_type,embed_coords,embed_size,embed_content) VALUES($id_room,$yaw,$pitch,$type,'$pois_icon',$pois_id_icon_library,'$pois_color','$pois_background',$pois_style,'$pois_tooltip_type',$embed_type,$embed_coords,$embed_size,$embed_content);";
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

