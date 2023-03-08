<?php
error_reporting(E_ALL & ~E_WARNING & ~E_NOTICE & ~E_DEPRECATED);
ob_start();
session_start();
if((($_SERVER['SERVER_ADDR']=='5.9.29.89') && ($_SERVER['REMOTE_ADDR']!=$_SESSION['ip_developer']) && ($_SESSION['id_user']==1)) || ($_SESSION['svt_si']!=session_id())) {
    die();
}
require_once("../functions.php");
require_once("../../db/connection.php");

$name = str_replace("'","\'",strip_tags($_POST['name']));
$n_virtual_tours = ($_POST['n_virtual_tours']=="") ? -1 : $_POST['n_virtual_tours'];
$n_rooms = ($_POST['n_rooms']=="") ? -1 : $_POST['n_rooms'];
$n_markers = ($_POST['n_markers']=="") ? -1 : $_POST['n_markers'];
$n_pois = ($_POST['n_pois']=="") ? -1 : $_POST['n_pois'];
$days = ($_POST['days']=="") ? -1 : $_POST['days'];
$max_file_size_upload = ($_POST['max_file_size_upload']=="") ? -1 : $_POST['max_file_size_upload'];
$max_storace_space = ($_POST['max_storace_space']=="") ? -1 : $_POST['max_storace_space'];
$create_landing = $_POST['create_landing'];
$create_showcase = $_POST['create_showcase'];
$create_gallery = $_POST['create_gallery'];
$create_presentation = $_POST['create_presentation'];
$enable_live_session = $_POST['enable_live_session'];
$enable_meeting = $_POST['enable_meeting'];
$enable_chat = $_POST['enable_chat'];
$enable_voice_commands = $_POST['enable_voice_commands'];
$enable_share = $_POST['enable_share'];
$enable_device_orientation = $_POST['enable_device_orientation'];
$enable_webvr = $_POST['enable_webvr'];
$enable_logo = $_POST['enable_logo'];
$enable_nadir_logo = $_POST['enable_nadir_logo'];
$enable_song = $_POST['enable_song'];
$enable_forms = $_POST['enable_forms'];
$enable_annotations = $_POST['enable_annotations'];
$enable_panorama_video = $_POST['enable_panorama_video'];
$enable_rooms_multiple = $_POST['enable_rooms_multiple'];
$enable_rooms_protect = $_POST['enable_rooms_protect'];
$enable_info_box = $_POST['enable_info_box'];
$enable_context_info = $_POST['enable_context_info'];
$enable_maps = $_POST['enable_maps'];
$enable_icons_library = $_POST['enable_icons_library'];
$enable_password_tour = $_POST['enable_password_tour'];
$enable_expiring_dates = $_POST['enable_expiring_dates'];
$enable_export_vt = $_POST['enable_export_vt'];
$enable_statistics = $_POST['enable_statistics'];
$enable_auto_rotate = $_POST['enable_auto_rotate'];
$enable_flyin = $_POST['enable_flyin'];
$enable_multires = $_POST['enable_multires'];
$enable_shop = $_POST['enable_shop'];
$enable_dollhouse = $_POST['enable_dollhouse'];
$price = str_replace(",",".",$_POST['price']);
if(empty($price)) $price=0;
if($price<0) $price=0;
$currency = $_POST['currency'];
$custom_features = str_replace("'","\'",$_POST['custom_features']);
$visible = $_POST['visible'];
$external_url = str_replace("'","\'",$_POST['external_url']);
$frequency = $_POST['frequency'];
$interval_count = $_POST['interval_count'];
if(empty($interval_count)) $interval_count=1;
if($interval_count<1) $interval_count=1;
if($interval_count>12) $interval_count=12;
$customize_menu = $_POST['customize_menu'];

$settings = get_settings();
if(($price>0) && ($settings['stripe_enabled'] || $settings['paypal_enabled']) && ($frequency=='recurring')) {
    $days = -1;
}

$query = "INSERT INTO svt_plans(name,n_virtual_tours,n_rooms,n_markers,n_pois,days,create_landing,create_gallery,create_presentation,enable_live_session,price,currency,custom_features,max_file_size_upload,max_storage_space,enable_chat,enable_voice_commands,enable_share,enable_device_orientation,enable_webvr,enable_logo,enable_nadir_logo,enable_song,enable_forms,enable_annotations,enable_panorama_video,enable_rooms_multiple,enable_rooms_protect,enable_info_box,enable_context_info,enable_maps,enable_icons_library,enable_password_tour,enable_expiring_dates,enable_statistics,enable_auto_rotate,enable_flyin,enable_multires,enable_meeting,create_showcase,enable_export_vt,enable_shop,enable_dollhouse,visible,external_url,frequency,interval_count,customize_menu) 
VALUES('$name',$n_virtual_tours,$n_rooms,$n_markers,$n_pois,$days,$create_landing,$create_gallery,$create_presentation,$enable_live_session,$price,'$currency','$custom_features',$max_file_size_upload,$max_storace_space,$enable_chat,$enable_voice_commands,$enable_share,$enable_device_orientation,$enable_webvr,$enable_logo,$enable_nadir_logo,$enable_song,$enable_forms,$enable_annotations,$enable_panorama_video,$enable_rooms_multiple,$enable_rooms_protect,$enable_info_box,$enable_context_info,$enable_maps,$enable_icons_library,$enable_password_tour,$enable_expiring_dates,$enable_statistics,$enable_auto_rotate,$enable_flyin,$enable_multires,$enable_meeting,$create_showcase,$enable_export_vt,$enable_shop,$enable_dollhouse,$visible,'$external_url','$frequency',$interval_count,'$customize_menu'); ";
$result = $mysqli->query($query);

if($result) {
    $id = $mysqli->insert_id;
    ob_end_clean();
    echo json_encode(array("status"=>"ok","id"=>$id));
} else {
    ob_end_clean();
    echo json_encode(array("status"=>"error"));
}

