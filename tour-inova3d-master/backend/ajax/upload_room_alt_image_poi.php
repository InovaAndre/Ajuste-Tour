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
require_once(dirname(__FILE__).'/../../db/connection.php');
require_once(dirname(__FILE__).'/../functions.php');
require_once(dirname(__FILE__).'/ImageResizeException.php');
require_once(dirname(__FILE__).'/ImageResize.php');
use \Gumlet\ImageResize;
$settings = get_settings();
$user_info = get_user_info($_SESSION['id_user']);
if(!empty($user_info['language'])) {
    set_language($user_info['language'],$settings['language_domain']);
} else {
    set_language($settings['language'],$settings['language_domain']);
}
if (!file_exists(dirname(__FILE__).'/../../viewer/panoramas/original/')) {
    mkdir(dirname(__FILE__).'/../../viewer/panoramas/original/', 0775);
}
$id_room = $_POST['id_room'];
$id_virtualtour = $_SESSION['id_virtualtour_sel'];
$compress_jpg = $_SESSION['compress_jpg'];
$max_width_compress = $_SESSION['max_width_compress'];
if($compress_jpg=="") $compress_jpg=90;
if($max_width_compress=="") $max_width_compress=0;
if(isset($_FILES) && !empty($_FILES['file']['name'])){
    $allowed_ext = array('png','jpg','jpeg');
    $filename = $_FILES['file']['name'];
    $ext = explode('.',$filename);
    $ext = strtolower(end($ext));
    if(in_array($ext,$allowed_ext)){
        if(strtolower($ext)=='png') {
            png2jpg($_FILES['file']['tmp_name'],$_FILES['file']['tmp_name'],100);
        }
        $name = "pano_".round(microtime(true) * 1000).".jpg";
        $moved = move_uploaded_file($_FILES['file']['tmp_name'],dirname(__FILE__).'/../../viewer/panoramas/'.$name);
        if($moved) {
            try {
                copy(dirname(__FILE__).'/../../viewer/panoramas/'.$name,dirname(__FILE__).'/../../viewer/panoramas/original/'.$name);
            } catch (Exception $e) {}
            list($width, $height) = getimagesize(dirname(__FILE__).'/../../viewer/panoramas/'.$name);
            $ratio = $width / $height;
            if($compress_jpg<100) {
                try {
                    $image = new ImageResize(dirname(__FILE__).'/../../viewer/panoramas/'.$name);
                    $image->quality_jpg = $compress_jpg;
                    $image->interlace = 1;
                    if($max_width_compress>0) {
                        $image->resizeToWidth($max_width_compress,false);
                    }
                    $image->gamma(false);
                    $image->save(dirname(__FILE__).'/../../viewer/panoramas/'.$name);
                } catch (ImageResizeException $e) {}
            }
            $mysqli->query("INSERT INTO svt_rooms_alt(id_room,panorama_image,poi) VALUES($id_room,'$name',1);");
            $insert_id = $mysqli->insert_id;
            include("../../services/generate_thumb.php");
            include("../../services/generate_pano_mobile.php");
            generate_multires(false,$id_virtualtour);
            update_user_space_storage($_SESSION['id_user']);
            ob_end_clean();
            echo json_encode(array("name"=>"panoramas/$name","id"=>$insert_id));
        } else {
            ob_end_clean();
            echo 'ERROR: code:'.$_FILES["file"]["error"];
        }
    }else{
        ob_end_clean();
        echo 'ERROR: '._("Only jpg,png files are supported.");
    }
}else{
    ob_end_clean();
    echo 'ERROR: '._("File not provided.");
}

if(!function_exists("png2jpg")) {
    function png2jpg($originalFile, $outputFile, $quality) {
        $image = imagecreatefrompng($originalFile);
        imagejpeg($image, $outputFile, $quality);
        imagedestroy($image);
    }
}

exit;
