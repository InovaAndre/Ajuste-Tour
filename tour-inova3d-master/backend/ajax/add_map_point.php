<?php
error_reporting(E_ALL & ~E_WARNING & ~E_NOTICE & ~E_DEPRECATED);
ob_start();
session_start();
if((($_SERVER['SERVER_ADDR']=='5.9.29.89') && ($_SERVER['REMOTE_ADDR']!=$_SESSION['ip_developer']) && ($_SESSION['id_user']==1)) || ($_SESSION['svt_si']!=session_id())) {
    die();
}
require_once("../../db/connection.php");
$id_room = $_POST['id_room'];
$id_map = $_POST['id_map'];
$map_type = $_POST['map_type'];
$lat = $_POST['lat'];
$lon = $_POST['lon'];

switch ($map_type) {
    case 'floorplan':
        $query = "UPDATE svt_rooms SET id_map=$id_map,map_left=10,map_top=10 WHERE id=$id_room;";
        break;
    case 'map':
        $query = "SELECT panorama_image FROM svt_rooms WHERE id=$id_room LIMIT 1;";
        $result = $mysqli->query($query);
        if($result) {
            $row=$result->fetch_array(MYSQLI_ASSOC);
            $panorama_image = $row['panorama_image'];
            $path = realpath(dirname(__FILE__) . '/../..').DIRECTORY_SEPARATOR."viewer".DIRECTORY_SEPARATOR."panoramas".DIRECTORY_SEPARATOR."original".DIRECTORY_SEPARATOR;
            if(file_exists($path.$panorama_image)) {
                $exif = exif_read_data($path.$panorama_image);
                $latitude = gps($exif["GPSLatitude"], $exif['GPSLatitudeRef']);
                $longitude = gps($exif["GPSLongitude"], $exif['GPSLongitudeRef']);
                if($latitude!=0 && !empty($latitude) && !is_nan($latitude)) {
                    $lat=$latitude;
                }
                if($longitude!=0 && !empty($longitude) && !is_nan($longitude)) {
                    $lon=$longitude;
                }
            }
        }
        $query = "UPDATE svt_rooms SET lat='$lat',lon='$lon' WHERE id=$id_room;";
        break;
}
$result = $mysqli->query($query);

if($result) {
    $_SESSION['id_room_point_sel'] = $id_room;
    ob_end_clean();
    echo json_encode(array("status"=>"ok","coordinates"=>"$lat - $lon"));
} else {
    ob_end_clean();
    echo json_encode(array("status"=>"error"));
}

function gps($coordinate, $hemisphere) {
    if (is_string($coordinate)) {
        $coordinate = array_map("trim", explode(",", $coordinate));
    }
    for ($i = 0; $i < 3; $i++) {
        $part = explode('/', $coordinate[$i]);
        if (count($part) == 1) {
            $coordinate[$i] = $part[0];
        } else if (count($part) == 2) {
            if($part[1]!=0) {
                $coordinate[$i] = floatval($part[0])/floatval($part[1]);
            } else {
                $coordinate[$i] = 0;
            }
        } else {
            $coordinate[$i] = 0;
        }
    }
    list($degrees, $minutes, $seconds) = $coordinate;
    $sign = ($hemisphere == 'W' || $hemisphere == 'S') ? -1 : 1;
    return $sign * ($degrees + $minutes/60 + $seconds/3600);
}