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
$code = "";
$logo_exist = "";
$query = "SELECT code,logo FROM svt_virtualtours WHERE id=$id_virtualtour LIMIT 1;";
$result = $mysqli->query($query);
if($result) {
    if($result->num_rows==1) {
        $row = $result->fetch_array(MYSQLI_ASSOC);
        $code = $row['code'];
        $logo_exist = $row['logo'];
    }
}
$name = str_replace("'","\'",strip_tags($_POST['name']));
$author = str_replace("'","\'",strip_tags($_POST['author']));
$id_user = $_POST['id_user'];
$hfov = $_POST['hfov'];
$min_hfov = $_POST['min_hfov'];
$max_hfov = $_POST['max_hfov'];
$hfov_mobile_ratio = $_POST['hfov_mobile_ratio'];
$pan_speed = $_POST['pan_speed'];
$pan_speed_mobile = $_POST['pan_speed_mobile'];
$friction = $_POST['friction'];
$friction_mobile = $_POST['friction_mobile'];
$quality_viewer = $_POST['quality_viewer'];
$song = $_POST['song'];
$song_autoplay = $_POST['song_autoplay'];
$flyin = $_POST['flyin'];
$logo = $_POST['logo'];
$link_logo = str_replace("'","\'",$_POST['link_logo']);
$background_image = $_POST['background_image'];
$background_video = $_POST['background_video'];
$background_video_delay = $_POST['background_video_delay'];
if(empty($background_video_delay)) $background_video_delay=0;
$nadir_logo = $_POST['nadir_logo'];
$nadir_size = $_POST['nadir_size'];
$intro_desktop = $_POST['intro_desktop'];
$intro_mobile = $_POST['intro_mobile'];
$autorotate_speed = $_POST['autorotate_speed'];
if($autorotate_speed=="") $autorotate_speed=0;
if($autorotate_speed>=10) $autorotate_speed=10;
if($autorotate_speed<=-10) $autorotate_speed=-10;
$autorotate_inactivity = $_POST['autorotate_inactivity'];
if($autorotate_inactivity=="") $autorotate_inactivity=0;
$auto_start = $_POST['auto_start'];
$hide_loading = $_POST['hide_loading'];
$sameAzimuth = $_POST['sameAzimuth'];
$description = str_replace("'","\'",strip_tags($_POST['description']));
$ga_tracking_id = $_POST['ga_tracking_id'];
$compress_jpg = $_POST['compress_jpg'];
if($compress_jpg=="") { $compress_jpg=90; }
$max_width_compress = $_POST['max_width_compress'];
if($max_width_compress=="") $max_width_compress=0;
$fb_page_id = strip_tags($_POST['fb_page_id']);
$enable_multires = $_POST['enable_multires'];
$preload_panoramas = $_POST['preload_panoramas'];
$whatsapp_number = $_POST['whatsapp_number'];
$transition_time = $_POST['transition_time'];
if($transition_time=='') $transition_time = 250;
$transition_fadeout = $_POST['transition_fadeout'];
if($transition_fadeout=='') $transition_fadeout = 400;
$transition_zoom = $_POST['transition_zoom'];
$transition_loading = $_POST['transition_loading'];
$transition_effect = $_POST['transition_effect'];
$markers_default_lookat = $_POST['markers_default_lookat'];
$note = str_replace("'","\'",strip_tags($_POST['note']));
$language = $_POST['language'];
$external_url = str_replace("'","\'",strip_tags($_POST['external_url']));
$id_category = $_POST['id_category'];
if($id_category==0) $id_category='NULL';
$keyboard_mode = $_POST['keyboard_mode'];
$password_meeting = str_replace("'","\'",$_POST['password_meeting']);
$password_livesession = str_replace("'","\'",$_POST['password_livesession']);
$snipcart_api_key = $_POST['snipcart_api_key'];
$snipcart_currency = $_POST['snipcart_currency'];
$enable_visitor_rt = $_POST['enable_visitor_rt'];
$interval_visitor_rt = $_POST['interval_visitor_rt'];
if($interval_visitor_rt=="") $interval_visitor_rt=1000;
if($interval_visitor_rt<0) $interval_visitor_rt=0;
$query_add = "";
if($password_meeting!="keep_password_meeting") {
    $query_add .= ",password_meeting='$password_meeting'";
}
if($password_livesession!="keep_password_livesession") {
    $query_add .= ",password_livesession='$password_livesession'";
}
if($snipcart_api_key!="keep_snipcart_public_key") {
    $query_add .= ",snipcart_api_key='$snipcart_api_key'";
}
$click_anywhere = $_POST['click_anywhere'];
$hide_markers = $_POST['hide_markers'];
$hover_markers = $_POST['hover_markers'];
$custom_html = str_replace("'","\'",$_POST['custom_html']);
$custom_html = htmlspecialchars_decode($custom_html);
$context_info = str_replace("'","\'",$_POST['context_info']);
if($context_info=='<p><br></p>') $context_info="";
$query = "UPDATE svt_virtualtours SET name='$name',id_user=$id_user,id_category=$id_category,author='$author',hfov=$hfov,min_hfov=$min_hfov,max_hfov=$max_hfov,hfov_mobile_ratio=$hfov_mobile_ratio,pan_speed=$pan_speed,pan_speed_mobile=$pan_speed_mobile,friction=$friction,friction_mobile=$friction_mobile,song='$song',song_autoplay=$song_autoplay,logo='$logo',background_image='$background_image',background_video='$background_video',background_video_delay=$background_video_delay,nadir_logo='$nadir_logo',nadir_size='$nadir_size',autorotate_speed=$autorotate_speed,autorotate_inactivity=$autorotate_inactivity,auto_start=$auto_start,hide_loading=$hide_loading,sameAzimuth=$sameAzimuth,description='$description',ga_tracking_id='$ga_tracking_id',compress_jpg=$compress_jpg,link_logo='$link_logo',max_width_compress=$max_width_compress,quality_viewer=$quality_viewer,fb_page_id='$fb_page_id',intro_desktop='$intro_desktop',intro_mobile='$intro_mobile',enable_multires=$enable_multires,whatsapp_number='$whatsapp_number',transition_time=$transition_time,transition_zoom=$transition_zoom,transition_loading=$transition_loading,transition_effect='$transition_effect',transition_fadeout=$transition_fadeout,markers_default_lookat=$markers_default_lookat,note='$note',flyin=$flyin,language='$language',external_url='$external_url',keyboard_mode=$keyboard_mode,preload_panoramas=$preload_panoramas,click_anywhere=$click_anywhere,hide_markers=$hide_markers,hover_markers=$hover_markers,snipcart_currency='$snipcart_currency',enable_visitor_rt=$enable_visitor_rt,interval_visitor_rt=$interval_visitor_rt,custom_html='$custom_html',context_info='$context_info' $query_add WHERE id=$id_virtualtour;";
$result = $mysqli->query($query);
if($result) {
    $query = "UPDATE svt_rooms SET transition_time=$transition_time,transition_zoom=$transition_zoom,transition_fadeout=$transition_fadeout WHERE id_virtualtour=$id_virtualtour AND transition_override=0;";
    $mysqli->query($query);
    $path = __DIR__.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR;
    if(empty($logo)) {
        if(file_exists($path . "favicons" . DIRECTORY_SEPARATOR . "v_$code" . DIRECTORY_SEPARATOR)) {
            array_map('unlink', glob($path . "favicons" . DIRECTORY_SEPARATOR . "v_$code" . DIRECTORY_SEPARATOR ."*.*"));
            rmdir($path . "favicons" . DIRECTORY_SEPARATOR . "v_$code" . DIRECTORY_SEPARATOR);
        }
    } else {
        if($logo!=$logo_exist) {
            if(file_exists($path . "favicons" . DIRECTORY_SEPARATOR . "v_$code")) {
                array_map('unlink', glob($path . "favicons" . DIRECTORY_SEPARATOR . "v_$code" . DIRECTORY_SEPARATOR ."*.*"));
                rmdir($path . "favicons" . DIRECTORY_SEPARATOR . "v_$code" . DIRECTORY_SEPARATOR);
            }
        }
        generate_favicons();
    }
    generate_multires(false,$id_virtualtour);
    update_user_space_storage($_SESSION['id_user']);
    ob_end_clean();
    echo json_encode(array("status"=>"ok"));
} else {
    ob_end_clean();
    echo json_encode(array("status"=>"error","error"=>$mysqli->error));
}