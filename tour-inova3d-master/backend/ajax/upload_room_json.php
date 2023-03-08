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
require_once(dirname(__FILE__).'/../functions.php');
$settings = get_settings();
$user_info = get_user_info($_SESSION['id_user']);
if(!empty($user_info['language'])) {
    set_language($user_info['language'],$settings['language_domain']);
} else {
    set_language($settings['language'],$settings['language_domain']);
}
if (!file_exists(dirname(__FILE__).'/../../viewer/panoramas/')) {
    mkdir(dirname(__FILE__).'/../../viewer/panoramas/', 0775);
}
if(isset($_FILES) && !empty($_FILES['file']['name'])){
    $allowed_ext = array('json');
    $filename = $_FILES['file']['name'];
    $ext = explode('.',$filename);
    $ext = strtolower(end($ext));
    if(in_array($ext,$allowed_ext)){
        $video = "pano_".round(microtime(true) * 1000).".json";
        $moved = move_uploaded_file($_FILES['file']['tmp_name'],dirname(__FILE__).'/../../viewer/panoramas/'.$video);
        if($moved) {
            ob_end_clean();
            echo "../viewer/panoramas/".$video;
        } else {
            ob_end_clean();
            echo 'ERROR: code:'.$_FILES["file"]["error"];
        }
    }else{
        ob_end_clean();
        echo 'ERROR: '._("Only json files are supported.");
    }
}else{
    ob_end_clean();
    echo 'ERROR: '._("File not provided.");
}
exit;