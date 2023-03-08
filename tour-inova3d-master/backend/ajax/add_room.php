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
$id_user = $_SESSION['id_user'];
$name = str_replace("'","\'",strip_tags($_POST['name']));
$name = ucfirst($name);
$panorama_image = $_POST['panorama_image'];
if(isset($_POST['panorama_video'])) {
    $panorama_video = $_POST['panorama_video'];
} else {
    $panorama_video = '';
}
if(isset($_POST['type_pano'])) {
    $type_pano = $_POST['type_pano'];
} else {
    $type_pano = 'image';
}
$panorama_url = $_POST['panorama_url'];
$panorama_json = $_POST['panorama_json'];
$panorama_json = str_replace("../viewer/panoramas/","",$panorama_json);
if($type_pano!='hls') $panorama_url="";
$virtual_tour = get_virtual_tour($id_virtualtour,$id_user);
$transition_time = $virtual_tour['transition_time'];
$transition_fadeout = $virtual_tour['transition_fadeout'];
$transition_zoom = $virtual_tour['transition_zoom'];
$transition_effect = $virtual_tour['transition_effect'];
$path_source = '';
switch($type_pano) {
    case 'image':
    case 'hls':
    case 'lottie':
        $name_image = str_replace("tmp_panoramas/","",$panorama_image);
        $name_video = '';
        $path_source = dirname(__FILE__).'/../tmp_panoramas/'.$name_image;
        $path_dest = dirname(__FILE__).'/../../viewer/panoramas/'.$name_image;
        if (!file_exists(dirname(__FILE__).'/../../viewer/panoramas/')) {
            mkdir(dirname(__FILE__).'/../../viewer/panoramas/', 0775);
        }
        if (!file_exists(dirname(__FILE__).'/../../viewer/panoramas/thumb/')) {
            mkdir(dirname(__FILE__).'/../../viewer/panoramas/thumb/', 0775);
        }
        if (!file_exists(dirname(__FILE__).'/../../viewer/panoramas/mobile/')) {
            mkdir(dirname(__FILE__).'/../../viewer/panoramas/mobile/', 0775);
        }
        if (!file_exists(dirname(__FILE__).'/../../viewer/panoramas/preview/')) {
            mkdir(dirname(__FILE__).'/../../viewer/panoramas/preview/', 0775);
        }
        if(copy($path_source,$path_dest)) {
            include("../../services/generate_thumb.php");
            include("../../services/generate_pano_mobile.php");
        } else {
            ob_end_clean();
            echo json_encode(array("status"=>"error image"));
            die();
        }
        break;
    case 'video':
        $name_image = "pano_".time().".jpg";
        $name_video = str_replace("../viewer/videos/","",$panorama_video);
        $path_dest = dirname(__FILE__).'/../../viewer/panoramas/'.$name_image;
        $ifp = fopen($path_dest,'wb');
        $data = explode(',', $panorama_image);
        fwrite($ifp,base64_decode($data[1]));
        fclose( $ifp );
        include("../../services/generate_thumb.php");
        include("../../services/generate_pano_mobile.php");
        break;
}


$priority = 0;
$query = "SELECT MAX(priority)+1 as priority FROM svt_rooms WHERE id_virtualtour=$id_virtualtour LIMIT 1;";
$result = $mysqli->query($query);
if($result) {
    if($result->num_rows==1) {
        $row = $result->fetch_array(MYSQLI_ASSOC);
        $priority = $row['priority'];
        if(empty($priority)) $priority=0;
    }
}

$query = "INSERT INTO svt_rooms(id_virtualtour,name,type,panorama_image,panorama_video,panorama_url,panorama_json,priority,transition_time,transition_fadeout,transition_zoom,transition_effect,protect_email)
            VALUES($id_virtualtour,'$name','$type_pano','$name_image','$name_video','$panorama_url','$panorama_json',$priority,$transition_time,$transition_fadeout,$transition_zoom,'$transition_effect',''); ";
$result = $mysqli->query($query);

if($result) {
    $id_room = $mysqli->insert_id;
    generate_multires(false,$id_virtualtour);
    $mysqli->query("UPDATE svt_rooms SET type='video' WHERE panorama_video<>'';");
    update_user_space_storage($_SESSION['id_user']);
    if(!empty($path_source)) {
        unlink($path_source);
    }
    ob_end_clean();
    echo json_encode(array("status"=>"ok","id"=>$id_room));
} else {
    ob_end_clean();
    echo json_encode(array("status"=>"error","msg"=>$mysqli->error));
}