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
$color = $_POST['color'];
$background = $_POST['background'];
$icon = $_POST['icon'];
$label = str_replace("'","\'",strip_tags($_POST['label']));
$style = $_POST['style'];
$id_icon_library = $_POST['id_icon_library'];
$tooltip_type = $_POST['tooltip_type'];
$tooltip_text = str_replace("'","\'",$_POST['tooltip_text']);
$css_class = str_replace("'","\'",strip_tags($_POST['css_class']));
$embed_content = $_POST['embed_content'];
if(empty($embed_content)) $embed_content="NULL"; else $embed_content="'$embed_content'";
$embed_video_autoplay = $_POST['embed_video_autoplay'];
$embed_video_muted = $_POST['embed_video_muted'];
$embed_gallery_autoplay = $_POST['embed_gallery_autoplay'];
if(empty($embed_gallery_autoplay)) $embed_gallery_autoplay=0;
$animation = $_POST['animation'];
$query = "UPDATE svt_pois SET color='$color',background='$background',icon='$icon',label='$label',style=$style,id_icon_library=$id_icon_library,tooltip_type='$tooltip_type',tooltip_text='$tooltip_text',css_class='$css_class',embed_content=$embed_content,embed_video_autoplay=$embed_video_autoplay,embed_video_muted=$embed_video_muted,embed_gallery_autoplay=$embed_gallery_autoplay,animation='$animation' WHERE id=$id_poi;";
$result = $mysqli->query($query);
if($result) {
    if(strpos($embed_content, 'content/') === 0) {
        include("../../services/generate_thumb.php");
    }
    update_user_space_storage($_SESSION['id_user']);
    ob_end_clean();
    echo json_encode(array("status"=>"ok"));
} else {
    ob_end_clean();
    echo json_encode(array("status"=>"error"));
}