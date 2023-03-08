<?php
error_reporting(E_ALL & ~E_WARNING & ~E_NOTICE & ~E_DEPRECATED);
ob_start();
session_start();
if((($_SERVER['SERVER_ADDR']=='5.9.29.89') && ($_SERVER['REMOTE_ADDR']!=$_SESSION['ip_developer']) && ($_SESSION['id_user']==1)) || ($_SESSION['svt_si']!=session_id())) {
    die();
}
ini_set('max_execution_time', 600000);
set_time_limit(600000);
$file = dirname(__FILE__).'/../../update_svt.zip';
if(file_exists($file)) {
    $path = pathinfo(realpath($file), PATHINFO_DIRNAME);
    $zip = new ZipArchive;
    $res = $zip->open($file);
    if ($res === TRUE) {
        $zip->extractTo($path);
        $zip->close();
        unlink($file);
        $lang = $_SESSION['lang'];
        unset($_SESSION['id_user']);
        session_destroy();
        session_start();
        $_SESSION['lang'] = $lang;
        ob_end_clean();
        echo json_encode(array("status"=>"ok"));
    } else {
        ob_end_clean();
        echo json_encode(array("status"=>"error"));
    }
} else {
    ob_end_clean();
    echo json_encode(array("status"=>"error"));
}