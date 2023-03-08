<?php
error_reporting(E_ALL & ~E_WARNING & ~E_NOTICE & ~E_DEPRECATED);
ob_start();
session_start();
if((($_SERVER['SERVER_ADDR']=='5.9.29.89') && ($_SERVER['REMOTE_ADDR']!=$_SESSION['ip_developer']) && ($_SESSION['id_user']==1)) || ($_SESSION['svt_si']!=session_id())) {
    die();
}
require_once("../../db/connection.php");
require_once("../functions.php");
$id_virtualtour = $_POST['id_virtualtour'];
$p = $_POST['p'];
$icon = $_POST['icon'];
$id_icon_library = $_POST['id_icon_library'];
$color = $_POST['color'];
$background = $_POST['background'];
$style = $_POST['style'];
$tooltip_type = $_POST['tooltip_type'];
$apply_style = $_POST['apply_style'];
$apply_tooltip_type = $_POST['apply_tooltip_type'];
$apply_icon = $_POST['apply_icon'];
$apply_color = $_POST['apply_color'];
$apply_background = $_POST['apply_background'];

switch ($p) {
    case 'markers':
        if($style!=4) $id_icon_library=0;
        $query = "UPDATE svt_virtualtours SET markers_icon='$icon',markers_id_icon_library=$id_icon_library,markers_color='$color',markers_background='$background',markers_show_room=$style,markers_tooltip_type='$tooltip_type' WHERE id=$id_virtualtour;";
        $query_add = "";
        if($apply_style) {
            $query_add .= "show_room=$style,";
        }
        if($apply_tooltip_type) {
            $query_add .= "tooltip_type='$tooltip_type',";
        }
        if($apply_icon) {
            $query_add .= "icon='$icon',id_icon_library=$id_icon_library,";
        } else {
            if($style!=4) {
                $query_add .= "id_icon_library=0,";
            }
        }
        if($apply_color) {
            $query_add .= "color='$color',";
        }
        if($apply_background) {
            $query_add .= "background='$background',";
        }
        $query_add = rtrim($query_add,",");
        $query_a = "UPDATE svt_markers SET $query_add WHERE (embed_type!='selection' OR embed_type IS NULL) AND id_room IN (SELECT DISTINCT id FROM svt_rooms WHERE id_virtualtour=$id_virtualtour);";
        break;
    case 'pois':
        if($style!=1) $id_icon_library=0;
        $query_add = "";
        if($apply_style) {
            $query_add .= "style=$style,";
        }
        if($apply_tooltip_type) {
            $query_add .= "tooltip_type='$tooltip_type',";
        }
        if($apply_icon) {
            $query_add .= "icon='$icon',id_icon_library=$id_icon_library,";
        } else {
            if($style!=4) {
                $query_add .= "id_icon_library=0,";
            }
        }
        if($apply_color) {
            $query_add .= "color='$color',";
        }
        if($apply_background) {
            $query_add .= "background='$background',";
        }
        $query_add = rtrim($query_add,",");
        $query = "UPDATE svt_virtualtours SET pois_icon='$icon',pois_id_icon_library=$id_icon_library,pois_color='$color',pois_background='$background',pois_style=$style,pois_tooltip_type='$tooltip_type' WHERE id=$id_virtualtour;";
        $query_a = "UPDATE svt_pois SET $query_add WHERE (embed_type!='selection' OR embed_type IS NULL) AND id_room IN (SELECT DISTINCT id FROM svt_rooms WHERE id_virtualtour=$id_virtualtour);";
        break;
}
$result = $mysqli->query($query);
if($result) {
    $result_a = $mysqli->query($query_a);
    if($result_a) {
        ob_end_clean();
        echo json_encode(array("status"=>"ok"));
    } else {
        ob_end_clean();
        echo json_encode(array("status"=>"error","msg"=>$mysqli->error));
    }
} else {
    ob_end_clean();
    echo json_encode(array("status"=>"error","msg"=>$mysqli->error));
}

