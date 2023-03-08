<?php
error_reporting(E_ALL & ~E_WARNING & ~E_NOTICE & ~E_DEPRECATED);
ob_start();
session_start();
if((($_SERVER['SERVER_ADDR']=='5.9.29.89') && ($_SERVER['REMOTE_ADDR']!=$_SESSION['ip_developer']) && ($_SESSION['id_user']==1)) || ($_SESSION['svt_si']!=session_id())) {
    die();
}
ini_set("memory_limit",-1);
ini_set('max_execution_time', 9999);
ini_set('max_input_time', 9999);
require_once(dirname(__FILE__)."/../../db/connection.php");
require_once(dirname(__FILE__).'/ImageResizeException.php');
require_once(dirname(__FILE__).'/ImageResize.php');
use \Gumlet\ImageResize;

$id_room = $_POST['id_room'];
$image = $_POST['image'];
$image = base64_decode(explode(",",$_POST['image'])[1]);
$crop_data = $_POST['crop_data'];

if (!file_exists(dirname(__FILE__).'/../../viewer/panoramas/thumb_custom/')) {
    mkdir(dirname(__FILE__).'/../../viewer/panoramas/thumb_custom/', 0775);
}

$query = "SELECT panorama_image FROM svt_rooms WHERE id=$id_room LIMIT 1;";
$result = $mysqli->query($query);
if($result) {
    if($result->num_rows==1) {
        $row = $result->fetch_array(MYSQLI_ASSOC);
        $im = @imagecreatefromstring($image);
        $image_cropped = imageCrop($im, [
            'x' => $crop_data['x'],
            'y' => $crop_data['y'],
            'width' => $crop_data['width'],
            'height' => $crop_data['height']
        ]);
        $name_thumb = 'thumb_'.time().'.jpg';
        imagejpeg($image_cropped, dirname(__FILE__).'/../../viewer/panoramas/thumb_custom/'.$name_thumb,100);
        try {
            $image = new ImageResize(dirname(__FILE__).'/../../viewer/panoramas/thumb_custom/'.$name_thumb);
            $image->quality_jpg = 100;
            $image->interlace = 1;
            $image->resizeToBestFit(213,120);
            $image->gamma(false);
            $image->save(dirname(__FILE__).'/../../viewer/panoramas/thumb_custom/'.$name_thumb);
        } catch (ImageResizeException $e) {}
        if(file_exists(dirname(__FILE__).'/../../viewer/panoramas/thumb_custom/'.$name_thumb)) {
            $mysqli->query("UPDATE svt_rooms SET thumb_image='$name_thumb' WHERE id=$id_room;");
            ob_end_clean();
            echo json_encode(array("status"=>"ok","thumb_image"=>$name_thumb));
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