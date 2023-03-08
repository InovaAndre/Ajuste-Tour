<?php
error_reporting(E_ALL & ~E_WARNING & ~E_NOTICE & ~E_DEPRECATED);
ob_start();
session_start();
if((($_SERVER['SERVER_ADDR']=='5.9.29.89') && ($_SERVER['REMOTE_ADDR']!=$_SESSION['ip_developer']) && ($_SESSION['id_user']==1)) || ($_SESSION['svt_si']!=session_id())) {
    die();
}
require_once("../../db/connection.php");
$id_marker = $_POST['id'];
$show_room = $_POST['show_room'];
$color = $_POST['color'];
$background = $_POST['background'];
$icon = $_POST['icon'];
$id_icon_library = $_POST['id_icon_library'];
$tooltip_type = $_POST['tooltip_type'];
$tooltip_text = strip_tags(str_replace("'","\'",$_POST['tooltip_text']));
$css_class = str_replace("'","\'",strip_tags($_POST['css_class']));
$embed_content = $_POST['embed_content'];
if(empty($embed_content)) $embed_content="NULL"; else $embed_content="'$embed_content'";
$animation = $_POST['animation'];

$query = "UPDATE svt_markers SET show_room=$show_room,color='$color',background='$background',icon='$icon',id_icon_library=$id_icon_library,tooltip_type='$tooltip_type',tooltip_text='$tooltip_text',css_class='$css_class',embed_content=$embed_content,animation='$animation' WHERE id=$id_marker;";
$result = $mysqli->query($query);

if($result) {
    ob_end_clean();
    echo json_encode(array("status"=>"ok"));
} else {
    ob_end_clean();
    echo json_encode(array("status"=>"error"));
}

