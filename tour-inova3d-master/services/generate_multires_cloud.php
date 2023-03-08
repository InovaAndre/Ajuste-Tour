<?php
error_reporting(E_ALL & ~E_WARNING & ~E_NOTICE & ~E_DEPRECATED);
session_start();
ini_set("memory_limit",-1);
ini_set('max_execution_time', 9999);
set_time_limit(9999);
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
require_once(__DIR__."/../db/connection.php");
require_once(__DIR__."/../backend/functions.php");
$mysqli->query("SET session wait_timeout=3600");
if(isset($_GET['check_req'])) {
    $check_req = 1;
    if(isset($_GET['multires_cloud_url'])) {
        $multires_cloud_url = $_GET['multires_cloud_url'];
        $mysqli->query("UPDATE svt_settings SET multires_cloud_url='$multires_cloud_url';");
    }
} else {
    $check_req = 0;
}
$settings = get_settings();
$user_info = get_user_info($_SESSION['id_user']);
if(!empty($user_info['language'])) {
    set_language($user_info['language'],$settings['language_domain']);
} else {
    set_language($settings['language'],$settings['language_domain']);
}
$path = realpath(dirname(__FILE__) . '/..');
if(isset($argv[1])) {
    if($argv[1]==1) {
        $force_update = true;
    } else {
        $force_update = false;
    }
} else {
    $force_update = false;
}
if(isset($argv[2])) {
    $id_virtualtour = $argv[2];
    $where = "AND id = $id_virtualtour";
} else {
    $where = "";
}
$multires_cloud_url = $settings['multires_cloud_url'];

if (!file_exists(dirname(__FILE__).'/../viewer/panoramas/multires/')) {
    mkdir(dirname(__FILE__).'/../viewer/panoramas/multires/', 0775);
}

$check_url = file_get_contents($multires_cloud_url."?check=1");
if($check_url!='ok') {
    ob_end_clean();
    echo json_encode(array("status"=>"error","msg"=>$multires_cloud_url." "._("not reachable")));
    exit;
}

if($check_req) {
    if(!isEnabled('shell_exec')) {
        echo json_encode(array("status"=>"error","msg"=>"php <b>shell_exec</b> "._("function disabled")));
        exit;
    }
}

if (!class_exists('ZipArchive')) {
    ob_end_clean();
    echo json_encode(array("status"=>"error","msg"=>_("php zip not enabled")));
    exit;
}

if($check_req)  {
    echo json_encode(array("status"=>"ok","msg"=>_("All requirements are met.")));
    exit;
}

$array_rooms = array();
$query_vt = "SELECT id,compress_jpg FROM svt_virtualtours WHERE enable_multires=1 $where;";
$result_vt = $mysqli->query($query_vt);
if($result_vt) {
    if ($result_vt->num_rows>0) {
        while($row_vt = $result_vt->fetch_array(MYSQLI_ASSOC)) {
            $id_vt = $row_vt['id'];
            $quality = $row_vt['compress_jpg'];
            if(empty($quality)) $quality = 100;
            if($force_update) {
                $query = "SELECT id,panorama_image,blur,haov,vaov FROM svt_rooms WHERE id_virtualtour=$id_vt AND type='image';";
            } else {
                $query = "SELECT id,panorama_image,blur,haov,vaov FROM svt_rooms WHERE id_virtualtour=$id_vt AND multires_status=0 AND type='image';";
            }
            $result = $mysqli->query($query);
            if($result) {
                if ($result->num_rows>0) {
                    while ($row = $result->fetch_array(MYSQLI_ASSOC)) {
                        $row['t']='room';
                        $array_rooms[] = $row;
                        $id_room = $row['id'];
                        $mysqli->query("UPDATE svt_rooms SET multires_status=1 WHERE id=$id_room;");
                    }
                }
            }
            if($force_update) {
                $query_ra = "SELECT ra.id,ra.panorama_image,0 as blur,r.haov,r.vaov FROM svt_rooms_alt as ra JOIN svt_rooms as r ON r.id=ra.id_room WHERE ra.id_room IN (SELECT id FROM svt_rooms WHERE id_virtualtour=$id_vt);";
            } else {
                $query_ra = "SELECT ra.id,ra.panorama_image,0 as blur,r.haov,r.vaov FROM svt_rooms_alt as ra JOIN svt_rooms as r ON r.id=ra.id_room WHERE ra.id_room IN (SELECT id FROM svt_rooms WHERE id_virtualtour=$id_vt) AND ra.multires_status=0;";
            }
            $result_ra = $mysqli->query($query_ra);
            if($result_ra) {
                if ($result_ra->num_rows>0) {
                    while ($row_ra = $result_ra->fetch_array(MYSQLI_ASSOC)) {
                        $row_ra['t']='room_alt';
                        $array_rooms[] = $row_ra;
                        $id_room_alt = $row_ra['id'];
                        $mysqli->query("UPDATE svt_rooms_alt SET multires_status=1 WHERE id=$id_room_alt;");
                    }
                }
            }
        }
    }
}
foreach ($array_rooms as $room) {
    $id_room = $room['id'];
    $t = $room['t'];
    $blur = $room['blur'];
    $vaov = $room['vaov'];
    $haov = $room['haov'];
    if($blur==1) {
        $quality_t = 100;
    } else {
        $quality_t = $quality;
    }
    $pano = str_replace(".jpg","",$room['panorama_image']);
    if($force_update) {
        $command = "rm -R ".$path.DIRECTORY_SEPARATOR."viewer".DIRECTORY_SEPARATOR."panoramas".DIRECTORY_SEPARATOR."multires".DIRECTORY_SEPARATOR.$pano;
        shell_exec($command);
    }
    if($blur==0 && file_exists($path.DIRECTORY_SEPARATOR."viewer".DIRECTORY_SEPARATOR."panoramas".DIRECTORY_SEPARATOR."original".DIRECTORY_SEPARATOR.$pano.".jpg")) {
        $pano_path = $path.DIRECTORY_SEPARATOR."viewer".DIRECTORY_SEPARATOR."panoramas".DIRECTORY_SEPARATOR."original".DIRECTORY_SEPARATOR.$pano.".jpg";
    } else {
        $pano_path = $path.DIRECTORY_SEPARATOR."viewer".DIRECTORY_SEPARATOR."panoramas".DIRECTORY_SEPARATOR.$pano.".jpg";
    }
    $cfile = new CURLFile($pano_path,'image/jpg',$room['panorama_image']);
    $post = array('file'=>$cfile,'id_room'=>$id_room,"pano"=>$pano,"quality"=>$quality_t,"haov"=>$haov,"vaov"=>$vaov);
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL,$multires_cloud_url);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/4.0 (compatible;)");
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: multipart/form-data'));
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
    curl_setopt($ch, CURLOPT_FRESH_CONNECT, 1);
    curl_setopt($ch, CURLOPT_TIMEOUT, 600);
    curl_setopt($ch, CURLOPT_POST,1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
    $curl_result = curl_exec($ch);
    curl_close ($ch);
    $response = json_decode($curl_result,true);
    if($response['status']=='ok') {
        $file_url = stripFile($multires_cloud_url)."/multires_tmp/".$pano.".zip";
        $file_zip = file_get_contents($file_url);
        file_put_contents(dirname(__FILE__).'/../viewer/panoramas/multires/'.$pano.".zip",$file_zip);
        if(file_exists(dirname(__FILE__).'/../viewer/panoramas/multires/'.$pano.".zip")) {
            $zip = new ZipArchive;
            $res = $zip->open(dirname(__FILE__).'/../viewer/panoramas/multires/'.$pano.".zip");
            if ($res === TRUE) {
                $zip->extractTo(dirname(__FILE__).'/../viewer/panoramas/multires/'.$pano);
                $zip->close();
                unlink(dirname(__FILE__).'/../viewer/panoramas/multires/'.$pano.".zip");
                $post = array('complete_pano'=>$pano);
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL,$multires_cloud_url);
                curl_setopt($ch, CURLOPT_HEADER, 0);
                curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/4.0 (compatible;)");
                curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: multipart/form-data'));
                curl_setopt($ch, CURLOPT_FRESH_CONNECT, 1);
                curl_setopt($ch, CURLOPT_TIMEOUT, 600);
                curl_setopt($ch, CURLOPT_POST,1);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
                $curl_result = curl_exec($ch);
            }
        }
    }
    if(file_exists($path.DIRECTORY_SEPARATOR."viewer".DIRECTORY_SEPARATOR."panoramas".DIRECTORY_SEPARATOR."multires".DIRECTORY_SEPARATOR.$pano.DIRECTORY_SEPARATOR."config.json")) {
        if($t=='room') {
            $query = "UPDATE svt_rooms SET multires_status=2 WHERE id=$id_room;";
        } else {
            $query = "UPDATE svt_rooms_alt SET multires_status=2 WHERE id=$id_room;";
        }
    } else {
        if($t=='room') {
            $query = "UPDATE svt_rooms SET multires_status=0 WHERE id=$id_room;";
        } else {
            $query = "UPDATE svt_rooms_alt SET multires_status=0 WHERE id=$id_room;";
        }
    }
    $mysqli->query($query);
}

function stripFile($in){
    $pieces = explode("/", $in);
    if(strpos(end($pieces), ".") !== false){
        array_pop($pieces);
    }elseif(end($pieces) !== ""){
        $pieces[] = "";
    }
    return implode("/", $pieces);
}