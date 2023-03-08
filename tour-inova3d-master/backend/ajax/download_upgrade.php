<?php
error_reporting(E_ALL & ~E_WARNING & ~E_NOTICE & ~E_DEPRECATED);
ob_start();
session_start();
if((($_SERVER['SERVER_ADDR']=='5.9.29.89') && ($_SERVER['REMOTE_ADDR']!=$_SESSION['ip_developer']) && ($_SESSION['id_user']==1)) || ($_SESSION['svt_si']!=session_id())) {
    die();
}
ini_set('max_execution_time', 600000);
set_time_limit(600000);
$version = $_POST['version'];
if(file_exists(dirname(__FILE__).'/../../update_svt.zip')) {
    unlink(dirname(__FILE__).'/../../update_svt.zip');
}
$url = base64_decode("aHR0cHM6Ly9zaW1wbGVkZW1vLml0L3N2dF9yZXBvLw==").$version.base64_decode("L3VwZGF0ZV9zdnQuemlw");
$options = array('http' => array('user_agent' => base64_decode('c3Z0X3VzZXJfYWdlbnQ=')));
$context = stream_context_create($options);
$file = file_get_contents($url, false, $context);
file_put_contents(dirname(__FILE__).'/../../update_svt.zip', $file);
if(file_exists(dirname(__FILE__).'/../../update_svt.zip')) {
    if(filesize(dirname(__FILE__).'/../../update_svt.zip')>0) {
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