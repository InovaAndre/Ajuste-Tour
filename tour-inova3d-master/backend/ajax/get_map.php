<?php
error_reporting(E_ALL & ~E_WARNING & ~E_NOTICE & ~E_DEPRECATED);
ob_start();
session_start();
if($_SESSION['svt_si']!=session_id()) {
    die();
}

require_once("../../db/connection.php");
$id_virtualtour = $_POST['id_virtualtour'];
$id_map = $_POST['id_map'];
$map_type = $_POST['map_type'];
$all_points = true;
switch ($map_type) {
    case 'floorplan':
        $array = array();
        $map = "";
        $query = "SELECT id,map,point_color FROM svt_maps WHERE id_virtualtour = $id_virtualtour AND id=$id_map LIMIT 1;";
        $result = $mysqli->query($query);
        if($result) {
            if($result->num_rows==1) {
                $row = $result->fetch_array(MYSQLI_ASSOC);
                $id_map = $row['id'];
                $map = $row['map'];
                $point_color = $row['point_color'];
                if($map==null) $map="";
                $query = "SELECT id,id_map,name,map_top,map_left,panorama_image FROM svt_rooms WHERE id_virtualtour = $id_virtualtour;";
                $result = $mysqli->query($query);
                if($result) {
                    if($result->num_rows>0) {
                        while($row=$result->fetch_array(MYSQLI_ASSOC)) {
                            if($row['map_top']==null) {
                                $all_points = false;
                            }
                            $row['point_color'] = $point_color;
                            $array[]=$row;
                        }
                    }
                }
            }
        }
        ob_end_clean();
        echo json_encode(array("id_map"=>$id_map,"map"=>$map,"map_points"=>$array,"all_points"=>$all_points));
        break;
    case 'map':
        $array = array();
        $query = "SELECT id,id_map,name,lat,lon,thumb_image,panorama_image FROM svt_rooms WHERE id_virtualtour = $id_virtualtour;";
        $result = $mysqli->query($query);
        if($result) {
            if($result->num_rows>0) {
                while($row=$result->fetch_array(MYSQLI_ASSOC)) {
                    if($row['lat']==null) {
                        $all_points = false;
                    }
                    if(!empty($row['thumb_image']) && file_exists(__DIR__.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'viewer'.DIRECTORY_SEPARATOR.'panoramas'.DIRECTORY_SEPARATOR.'thumb_custom'.DIRECTORY_SEPARATOR.$row['thumb_image'])) {
                        $row['icon'] = '../viewer/panoramas/thumb_custom/'.$row['thumb_image'];
                    } else if(file_exists(__DIR__.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'viewer'.DIRECTORY_SEPARATOR.'panoramas'.DIRECTORY_SEPARATOR.'preview'.DIRECTORY_SEPARATOR.$row['panorama_image'])) {
                        $row['icon'] = '../viewer/panoramas/preview/'.$row['panorama_image'];
                    } else {
                        $row['icon'] = '../viewer/panoramas/thumb/'.$row['panorama_image'];
                    }
                    $array[]=$row;
                }
            }
        }
        ob_end_clean();
        echo json_encode(array("id_map"=>$id_map,"map_points"=>$array,"all_points"=>$all_points));
        break;
}