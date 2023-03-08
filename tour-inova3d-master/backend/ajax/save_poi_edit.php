<?php
error_reporting(E_ALL & ~E_WARNING & ~E_NOTICE & ~E_DEPRECATED);
ob_start();
session_start();
if((($_SERVER['SERVER_ADDR']=='5.9.29.89') && ($_SERVER['REMOTE_ADDR']!=$_SESSION['ip_developer']) && ($_SESSION['id_user']==1)) || ($_SESSION['svt_si']!=session_id())) {
    die();
}
require_once("../../db/connection.php");
require_once("../functions.php");
$id_poi = $_POST['id'];
$type = $_POST['type'];
$content = str_replace("'","\'",$_POST['content']);
$title = str_replace("'","\'",strip_tags($_POST['title']));
$description = str_replace("'","\'",strip_tags($_POST['description']));
if($type=='html_sc') {
    $content = htmlspecialchars_decode($content);
    if(substr( $content, 0, 4 ) != "<div") {
        $content = "<div>".$content."</div>";
    }
}
$target = $_POST['target'];
if($target=='') $target="NULL"; else $target="'$target'";
$id_room = $_POST['id_room'];
$id_poi_autoopen = $_POST['id_poi_autoopen'];
if(empty($id_poi_autoopen)) $id_poi_autoopen = 'NULL';
$view_type = $_POST['view_type'];
$box_pos = $_POST['box_pos'];
$song_bg_volume = $_POST['song_bg_volume'];
$params = $_POST['params'];
$auto_close = $_POST['auto_close'];
if(empty($auto_close) || $auto_close<0) {
    $auto_close=0;
}
$mysqli->query("UPDATE svt_rooms SET id_poi_autoopen=$id_poi_autoopen WHERE id=$id_room;");
$query = "UPDATE svt_pois SET content='$content',title='$title',description='$description',target=$target,view_type=$view_type,box_pos='$box_pos',song_bg_volume=$song_bg_volume,params='$params',auto_close=$auto_close WHERE id=$id_poi;";
$result = $mysqli->query($query);
if($result) {
    if(strpos($content, 'content/') === 0) {
        include("../../services/generate_thumb.php");
    }
    update_user_space_storage($_SESSION['id_user']);
    ob_end_clean();
    echo json_encode(array("status"=>"ok"));
} else {
    ob_end_clean();
    echo json_encode(array("status"=>"error"));
}