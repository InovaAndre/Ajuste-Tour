<?php
error_reporting(E_ALL & ~E_WARNING & ~E_NOTICE & ~E_DEPRECATED);
ob_start();
session_start();
if((($_SERVER['SERVER_ADDR']=='5.9.29.89') && ($_SERVER['REMOTE_ADDR']!=$_SESSION['ip_developer']) && ($_SESSION['id_user']==1)) || ($_SESSION['svt_si']!=session_id())) {
    die();
}
require_once("../../db/connection.php");
require_once("../functions.php");
$id_user = $_SESSION['id_user'];
$id_virtualtour = $_POST['id_virtualtour'];
$id_room = $_POST['id_room'];
$name = str_replace("'","\'",strip_tags($_POST['name']));
$logo = $_POST['logo'];
$yaw_pitch = $_POST['yaw_pitch'];
$northOffset = $_POST['northOffset'];
$change_image = $_POST['change_image'];
$change_video = $_POST['change_video'];
$panorama_image = $_POST['panorama_image'];
$thumb_image = $_POST['thumb_image'];
$panorama_video = $_POST['panorama_video'];
$song = $_POST['song'];
$song_bg_volume = $_POST['song_bg_volume'];
$audio_track_enable = $_POST['audio_track_enable'];
$annotation_title = str_replace("'","\'",strip_tags($_POST['annotation_title']));
$annotation_description = str_replace("'","\'",strip_tags($_POST['annotation_description'],"<br><b><u><i><ul><li>"));
$allow_pitch = $_POST['allow_pitch'];
$allow_hfov = $_POST['allow_hfov'];
$visible_list = $_POST['visible_list'];
$min_pitch = $_POST['min_pitch'];
$max_pitch = $_POST['max_pitch'];
$min_yaw = $_POST['min_yaw'];
$max_yaw = $_POST['max_yaw'];
$haov = $_POST['haov'];
$vaov = $_POST['vaov'];
$hfov = $_POST['hfov'];
$h_pitch = $_POST['h_pitch'];
$h_roll = $_POST['h_roll'];
$protect_type = $_POST['protect_type'];
$passcode_title = str_replace("'","\'",strip_tags($_POST['passcode_title']));
$passcode_description = str_replace("'","\'",strip_tags($_POST['passcode_description'],"<br><b><u><i><ul><li>"));
$passcode = $_POST['passcode'];
$filters = [];
$filters['brightness'] = $_POST['brightness'];
$filters['contrast'] = $_POST['contrast'];
$filters['saturate'] = $_POST['saturate'];
$filters['grayscale'] = $_POST['grayscale'];
$filters = json_encode($filters);
$tmp = explode(",",$yaw_pitch);
$yaw = $tmp[0];
$pitch = $tmp[1];
if(empty($min_pitch)) $min_pitch=90;
if(empty($max_pitch)) $max_pitch=90;
if(empty($min_yaw)) $min_yaw=180;
if(empty($max_yaw)) $max_yaw=180;
if(empty($haov)) $haov=360;
if(empty($vaov)) $vaov=180;
if(empty($hfov)) $hfov=0;
$min_pitch = $min_pitch*-1;
$min_yaw = $min_yaw*-1;
$transition_time = $_POST['transition_time'];
$transition_fadeout = $_POST['transition_fadeout'];
$transition_zoom = $_POST['transition_zoom'];
$transition_override = $_POST['transition_override'];
$transition_effect = $_POST['transition_effect'];
$virtual_staging = $_POST['virtual_staging'];
$main_view_tooltip = str_replace("'","\'",strip_tags($_POST['main_view_tooltip']));
$background_color = $_POST['background_color'];
if(empty($background_color)) $background_color="1,1,1";
$virtual_tour = get_virtual_tour($id_virtualtour,$id_user);
if($transition_override==1) {
    if($transition_time=='') $transition_time = $virtual_tour['transition_time'];
    if($transition_fadeout=='') $transition_fadeout = $virtual_tour['transition_fadeout'];;
} else {
    $transition_time = $virtual_tour['transition_time'];
    $transition_fadeout = $virtual_tour['transition_fadeout'];
    $transition_zoom = $virtual_tour['transition_zoom'];
    $transition_effect = $virtual_tour['transition_effect'];
}
$effect = $_POST['effect'];
$apply_preset_to_vt = $_POST['apply_preset_to_vt'];
$protect_send_email = $_POST['protect_send_email'];
$protect_email = str_replace("'","\'",$_POST['protect_email']);
if($change_image==1) {
    $name_image = str_replace("tmp_panoramas/","",$panorama_image);
    $path_source = dirname(__FILE__).'/../tmp_panoramas/'.$name_image;
    $path_dest = dirname(__FILE__).'/../../viewer/panoramas/'.$name_image;
    if(copy($path_source,$path_dest)) {
        unlink($path_source);
        $q_add = ",panorama_image='$name_image',multires_status=0";
        include("../../services/generate_thumb.php");
        include("../../services/generate_pano_mobile.php");
    } else {
        ob_end_clean();
        echo json_encode(array("status"=>"error image"));
        die();
    }
} else if($change_video==1) {
    $name_image = "pano_".time().".jpg";
    $name_video = str_replace("../viewer/videos/","",$panorama_video);
    $path_dest = dirname(__FILE__).'/../../viewer/panoramas/'.$name_image;
    $ifp = fopen($path_dest,'wb');
    $data = explode(',', $panorama_image);
    fwrite($ifp,base64_decode($data[1]));
    fclose( $ifp );
    $q_add = ",panorama_image='$name_image',panorama_video='$name_video'";
    include("../../services/generate_thumb.php");
    include("../../services/generate_pano_mobile.php");
} else {
    $q_add = "";
}
if($passcode!='keep_passcode') {
    if(empty($passcode)) {
        $q_add .= ",passcode=NULL";
    } else {
        $q_add .= ",passcode=MD5('$passcode')";
    }
}
$query = "UPDATE svt_rooms SET name='$name',logo='$logo',yaw=$yaw,pitch=$pitch,hfov=$hfov,h_pitch=$h_pitch,h_roll=$h_roll,allow_pitch=$allow_pitch,allow_hfov=$allow_hfov,visible_list=$visible_list,min_pitch=$min_pitch,max_pitch=$max_pitch,min_yaw=$min_yaw,max_yaw=$max_yaw,haov=$haov,vaov=$vaov,northOffset=$northOffset,song='$song',song_bg_volume=$song_bg_volume,audio_track_enable=$audio_track_enable,annotation_title='$annotation_title',annotation_description='$annotation_description',protect_type='$protect_type',passcode_title='$passcode_title',passcode_description='$passcode_description',transition_time=$transition_time,transition_zoom=$transition_zoom,transition_fadeout=$transition_fadeout,transition_override=$transition_override,transition_effect='$transition_effect',filters='$filters',effect='$effect',thumb_image='$thumb_image',virtual_staging=$virtual_staging,main_view_tooltip='$main_view_tooltip',background_color='$background_color',protect_send_email=$protect_send_email,protect_email='$protect_email' $q_add WHERE id=$id_room;";
$result = $mysqli->query($query);
if($apply_preset_to_vt==1) {
    $query = "UPDATE svt_rooms SET hfov=$hfov,h_pitch=$h_pitch,h_roll=$h_roll,allow_pitch=$allow_pitch,allow_hfov=$allow_hfov,min_pitch=$min_pitch,max_pitch=$max_pitch,min_yaw=$min_yaw,max_yaw=$max_yaw,haov=$haov,vaov=$vaov,background_color='$background_color' WHERE id_virtualtour=$id_virtualtour;";
    $mysqli->query($query);
}
if($result) {
    generate_multires(false,$id_virtualtour);
    update_user_space_storage($_SESSION['id_user']);
    ob_end_clean();
    echo json_encode(array("status"=>"ok"));
} else {
    ob_end_clean();
    echo json_encode(array("status"=>"error","error"=>$mysqli->error));
}