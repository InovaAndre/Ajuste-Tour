<?php
error_reporting(E_ALL & ~E_WARNING & ~E_NOTICE & ~E_DEPRECATED);
ob_start();
session_start();
if($_SESSION['svt_si']!=session_id()) {
    die();
}

require_once("../../db/connection.php");
require_once("../functions.php");
$id_room = $_POST['id_room'];
$array = array();
$room=array();

$query = "SELECT r.panorama_image,r.panorama_video,v.enable_multires,r.yaw,r.pitch,r.h_pitch,r.h_roll,r.allow_pitch,r.min_pitch,r.max_pitch,r.min_yaw,r.max_yaw,r.haov,r.vaov,r.type,r.id_poi_autoopen FROM svt_rooms as r 
            JOIN svt_virtualtours as v ON v.id=r.id_virtualtour
            WHERE r.id = $id_room LIMIT 1;";
$result = $mysqli->query($query);
if($result) {
    if ($result->num_rows > 0) {
        $row = $result->fetch_array(MYSQLI_ASSOC);
        $room['id_poi_autoopen'] = $row['id_poi_autoopen'];
        $room['yaw'] = $row['yaw'];
        $room['pitch'] = $row['pitch'];
        $room['h_pitch'] = $row['h_pitch'];
        $room['h_roll'] = $row['h_roll'];
        $room['min_yaw'] = $row['min_yaw'];
        $room['max_yaw'] = $row['max_yaw'];
        $room['allow_pitch'] = $row['allow_pitch'];
        $room['min_pitch'] = $row['min_pitch'];
        $room['max_pitch'] = $row['max_pitch'];
        $room['haov'] = $row['haov'];
        $room['vaov'] = $row['vaov'];
        $room['panorama_video'] = $row['panorama_video'];
        $room['room_type'] = $row['type'];
        if($row['enable_multires']) {
            $room_pano = str_replace('.jpg','',$row['panorama_image']);
            $multires_config_file = __DIR__.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'viewer'.DIRECTORY_SEPARATOR.'panoramas'.DIRECTORY_SEPARATOR.'multires'.DIRECTORY_SEPARATOR.$room_pano.DIRECTORY_SEPARATOR.'config.json';
            if(file_exists($multires_config_file)) {
                $multires_tmp = file_get_contents($multires_config_file);
                $multires_array = json_decode($multires_tmp,true);
                $multires_config = $multires_array['multiRes'];
                $multires_config['basePath'] = '../viewer/panoramas/multires/'.$room_pano;
                $room['multires']=1;
                $room['multires_config']=json_encode($multires_config);
                $room['multires_dir']='../viewer/panoramas/multires/'.$room_pano;
            } else {
                $room['multires']=0;
                $room['multires_config']='';
                $room['multires_dir']='';
            }
        } else {
            $room['multires']=0;
            $room['multires_config']='';
            $room['multires_dir']='';
        }
    }
}

$query = "SELECT 'poi' as what,p.*,IFNULL(i.id,0) as id_icon_library, i.image as img_icon_library FROM svt_pois as p
            LEFT JOIN svt_icons as i ON i.id=p.id_icon_library
            WHERE p.id_room=$id_room;";
$result = $mysqli->query($query);
if($result) {
    if($result->num_rows>0) {
        while($row=$result->fetch_array(MYSQLI_ASSOC)) {
            if($row['type']=='html_sc') {
                $row['content'] = htmlspecialchars_decode($row['content']);
            }
            if($row['label']==null) $row['label']='';
            if($row['type']==null) $row['type']='';
            if($row['params']==null) $row['params']='';
            if($row['embed_type']==null) $row['embed_type']='';
            if($row['embed_content']==null) $row['embed_content']='';
            if($row['embed_coords']==null) $row['embed_coords']='';
            if($row['embed_size']==null) $row['embed_size']='';
            if($row['embed_type']=='gallery') {
                $id_poi = $row['id'];
                $query_g = "SELECT image FROM svt_poi_embedded_gallery WHERE id_poi=$id_poi ORDER BY priority LIMIT 1;";
                $result_g = $mysqli->query($query_g);
                if($result_g) {
                    if ($result_g->num_rows == 1) {
                        $row_g = $result_g->fetch_array(MYSQLI_ASSOC);
                        $row['embed_content'] = "../viewer/gallery/".$row_g['image'];
                    }
                }
            }
            $row['switch_panorama_image'] = '';
            if($row['type']=='switch_pano') {
                $id_room_alt = $row['content'];
                if($id_room_alt!='' && $id_room_alt!=0) {
                    $query_ra = "SELECT panorama_image FROM svt_rooms_alt WHERE id=$id_room_alt LIMIT 1;";
                    $result_ra = $mysqli->query($query_ra);
                    if($result_ra) {
                        if ($result_ra->num_rows == 1) {
                            $row_ra = $result_ra->fetch_array(MYSQLI_ASSOC);
                            $row['switch_panorama_image'] = "panoramas/".$row_ra['panorama_image'];
                        }
                    }
                }
            }
            if(!empty($row["img_icon_library"])) {
                $row['base64_icon_library'] = convert_image_to_base64(dirname(__FILE__).'/../../viewer/icons/'.$row["img_icon_library"]);;
            } else {
                $row['base64_icon_library'] = '';
            }
            if($row['embed_type']=='text') {
                if (strpos($row['embed_content'], 'border-width') === false) {
                    $row['embed_content'] = $row['embed_content']." border-width:0px;";
                }
            }
            $array[]=$row;
        }
    }
}
$query = "SELECT 'marker' as what,m.*,r.name as name_room_target,r.panorama_image as marker_preview,r.id as id_room_target,IFNULL(i.id,0) as id_icon_library, i.image as img_icon_library FROM svt_markers AS m
          JOIN svt_rooms AS r ON m.id_room_target = r.id 
          JOIN svt_virtualtours as v ON v.id = r.id_virtualtour
          LEFT JOIN svt_icons as i ON i.id=m.id_icon_library
          WHERE m.id_room=$id_room;";
$result = $mysqli->query($query);
if($result) {
    if($result->num_rows>0) {
        while($row=$result->fetch_array(MYSQLI_ASSOC)) {
            unset($row['id']);
            if($row['embed_type']==null) $row['embed_type']='';
            if($row['embed_content']==null) $row['embed_content']='';
            if($row['embed_coords']==null) $row['embed_coords']='';
            if($row['embed_size']==null) $row['embed_size']='';
            if(!empty($row["img_icon_library"])) {
                $row['base64_icon_library'] = convert_image_to_base64(dirname(__FILE__).'/../../viewer/icons/'.$row["img_icon_library"]);;
            } else {
                $row['base64_icon_library'] = '';
            }
            if(!empty($row["marker_preview"])) {
                $row['base64_marker_preview'] = convert_image_to_base64(dirname(__FILE__).'/../../viewer/panoramas/preview/'.$row["marker_preview"]);;
            } else {
                $row['base64_marker_preview'] = '';
            }
            $array[]=$row;
        }
    }
}
ob_end_clean();
echo json_encode(array("pois"=>$array,"room"=>$room));