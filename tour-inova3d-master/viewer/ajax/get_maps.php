<?php
error_reporting(E_ALL & ~E_WARNING & ~E_NOTICE & ~E_DEPRECATED);
ob_start();
require_once("../../db/connection.php");
$id_virtualtour = $_POST['id_virtualtour'];
$map_tour = array();
$map_tour_points = array();
$maps = array();
$query = "SELECT * FROM svt_maps WHERE id_virtualtour=$id_virtualtour AND map_type='map' LIMIT 1;";
$result = $mysqli->query($query);
if($result) {
    if ($result->num_rows == 1) {
        $row = $result->fetch_array(MYSQLI_ASSOC);
        $row['point_size'] = $row['point_size']*2;
        $map_tour = $row;
        $query_m = "SELECT id,name,lat,lon,thumb_image,panorama_image FROM svt_rooms WHERE id_virtualtour=$id_virtualtour AND lat IS NOT NULL AND lon IS NOT NULL;";
        $result_m = $mysqli->query($query_m);
        if($result_m) {
            if ($result_m->num_rows > 0) {
                while ($row_m = $result_m->fetch_array(MYSQLI_ASSOC)) {
                    if(!empty($row['thumb_image']) && file_exists(__DIR__.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'panoramas'.DIRECTORY_SEPARATOR.'thumb_custom'.DIRECTORY_SEPARATOR.$row_m['thumb_image'])) {
                        $row_m['icon'] = 'panoramas/thumb_custom/'.$row_m['thumb_image'];
                    } else if(file_exists(__DIR__.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'panoramas'.DIRECTORY_SEPARATOR.'preview'.DIRECTORY_SEPARATOR.$row_m['panorama_image'])) {
                        $row_m['icon'] = 'panoramas/preview/'.$row_m['panorama_image'];
                    } else {
                        $row_m['icon'] = 'panoramas/thumb/'.$row_m['panorama_image'];
                    }
                    unset($row_m['thumb_image']);
                    unset($row_m['panorama_image']);
                    $map_tour_points[] = $row_m;
                }
            }
        }
    }
}
$query = "SELECT * FROM svt_maps WHERE id_virtualtour=$id_virtualtour AND map_type='floorplan' ORDER BY priority ASC, id ASC;";
$result = $mysqli->query($query);
if($result) {
    if($result->num_rows>0) {
        while($row=$result->fetch_array(MYSQLI_ASSOC)) {
            $map = __DIR__.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'maps'.DIRECTORY_SEPARATOR.$row['map'];
            list($width, $height) = getimagesize($map);
            $row['map_ratio'] = $width/$height;
            if(empty($row['info_link'])) $row['info_link']='';
            if(empty($row['id_room_default'])) $row['id_room_default']='';
            $maps[] = $row;
        }
    }
}
ob_end_clean();
echo json_encode(array("status"=>"ok","maps"=>$maps,"map_tour"=>$map_tour,"map_tour_points"=>$map_tour_points));