<?php
error_reporting(E_ALL & ~E_WARNING & ~E_NOTICE & ~E_DEPRECATED);
session_start();
ini_set("memory_limit",-1);
ini_set('max_execution_time', 9999);
ini_set('max_input_time', 9999);
require_once(__DIR__.'/ImageResizeException.php');
require_once(__DIR__.'/ImageResize.php');
use \Gumlet\ImageResize;
require_once(__DIR__."/../db/connection.php");
require_once(__DIR__."/../backend/functions.php");
$settings = get_settings();
$user_info = get_user_info($_SESSION['id_user']);
if(!empty($user_info['language'])) {
    set_language($user_info['language'],$settings['language_domain']);
} else {
    set_language($settings['language'],$settings['language_domain']);
}
$path = realpath(dirname(__FILE__) . '/..');

$id_virtualtour = $_POST['id_virtualtour'];
$compress_jpg = $_POST['compress_jpg'];
$max_width_compress = $_POST['max_width_compress'];
$enable_multires = $_POST['enable_multires'];
if($compress_jpg=="") $compress_jpg=90;
if($max_width_compress=="") $max_width_compress=0;

$mysqli->query("UPDATE svt_virtualtours SET enable_multires=$enable_multires,compress_jpg=$compress_jpg,max_width_compress=$max_width_compress WHERE id=$id_virtualtour;");

if (!file_exists(dirname(__FILE__).'/../viewer/panoramas/original/')) {
    mkdir(dirname(__FILE__).'/../viewer/panoramas/original/', 0775);
}

$result = $mysqli->query("SELECT id,panorama_image FROM svt_rooms WHERE type='image' AND id_virtualtour=$id_virtualtour;");
if($result) {
    if($result->num_rows>0) {
        while($row = $result->fetch_array(MYSQLI_ASSOC)) {
            $id = $row['id'];
            $panorama_image = $row['panorama_image'];
            resize_panorama($id,'svt_rooms',$panorama_image);
        }
    }
}

$result = $mysqli->query("SELECT id,panorama_image FROM svt_rooms_alt WHERE id_room IN (SELECT id FROM svt_rooms WHERE type='image' AND id_virtualtour=$id_virtualtour);");
if($result) {
    if($result->num_rows>0) {
        while($row = $result->fetch_array(MYSQLI_ASSOC)) {
            $id = $row['id'];
            $panorama_image = $row['panorama_image'];
            resize_panorama($id,'svt_rooms_alt',$panorama_image);
        }
    }
}

include("generate_thumb.php");
include("generate_pano_mobile.php");

function resize_panorama($id,$table,$panorama_image) {
    global $path,$compress_jpg,$max_width_compress,$mysqli;
    $new_name = "pano_".round(microtime(true) * 1000).".jpg";
    $original_pano = $path.DIRECTORY_SEPARATOR."viewer".DIRECTORY_SEPARATOR."panoramas".DIRECTORY_SEPARATOR."original".DIRECTORY_SEPARATOR.$panorama_image;
    $resized_pano = $path.DIRECTORY_SEPARATOR."viewer".DIRECTORY_SEPARATOR."panoramas".DIRECTORY_SEPARATOR.$panorama_image;
    $original_pano_new = $path.DIRECTORY_SEPARATOR."viewer".DIRECTORY_SEPARATOR."panoramas".DIRECTORY_SEPARATOR."original".DIRECTORY_SEPARATOR.$new_name;
    $resized_pano_new = $path.DIRECTORY_SEPARATOR."viewer".DIRECTORY_SEPARATOR."panoramas".DIRECTORY_SEPARATOR.$new_name;
    if(file_exists($original_pano)) {
        copy($original_pano,$original_pano_new);
    } else {
        copy($resized_pano,$original_pano_new);
    }
    if(file_exists($original_pano_new)) {
        if($compress_jpg<100) {
            try {
                $image = new ImageResize($original_pano_new);
                $image->quality_jpg = $compress_jpg;
                $image->interlace = 1;
                if ($max_width_compress > 0) {
                    $image->resizeToWidth($max_width_compress, false);
                }
                $image->gamma(false);
                $image->save($resized_pano_new);
            } catch (ImageResizeException $e) {}
        } else {
            copy($original_pano_new,$resized_pano_new);
        }
        if(file_exists($resized_pano_new)) {
            $mysqli->query("UPDATE $table SET multires_status=0,panorama_image='$new_name' WHERE id=$id;");
        }
    }
}