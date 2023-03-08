<?php
error_reporting(E_ALL & ~E_WARNING & ~E_NOTICE & ~E_DEPRECATED);
ob_start();
session_start();
if((($_SERVER['SERVER_ADDR']=='5.9.29.89') && ($_SERVER['REMOTE_ADDR']!=$_SESSION['ip_developer']) && ($_SESSION['id_user']==1)) || ($_SESSION['svt_si']!=session_id())) {
    die();
}
require_once(dirname(__FILE__).'/../functions.php');
$settings = get_settings();
$user_info = get_user_info($_SESSION['id_user']);
if(!empty($user_info['language'])) {
    set_language($user_info['language'],$settings['language_domain']);
} else {
    set_language($settings['language'],$settings['language_domain']);
}
if (!file_exists(dirname(__FILE__).'/../../viewer/content/')) {
    mkdir(dirname(__FILE__).'/../../viewer/content/', 0775);
}
if(isset($_FILES) && !empty($_FILES['file']['name'])){
    $allowed_ext = array('mp3');
    $filename = $_FILES['file']['name'];
    $ext = explode('.',$filename);
    $ext = strtolower(end($ext));
    $name = $filename;
    if(in_array($ext,$allowed_ext)){
        $moved = move_uploaded_file($_FILES['file']['tmp_name'],dirname(__FILE__).'/../../viewer/content/'.$name);
        if($moved) {
            ob_end_clean();
            echo $name;
        } else {
            ob_end_clean();
            echo 'ERROR: code:'.$_FILES["file"]["error"];
        }
    }else{
        ob_end_clean();
        echo 'ERROR: '._("Only mp3 files are supported.");
    }
}else{
    ob_end_clean();
    echo 'ERROR: '._("File not provided.");
}
exit;
