<?php
error_reporting(E_ALL & ~E_WARNING & ~E_NOTICE & ~E_DEPRECATED);
ob_start();
session_start();
if((($_SERVER['SERVER_ADDR']=='5.9.29.89') && ($_SERVER['REMOTE_ADDR']!=$_SESSION['ip_developer']) && ($_SESSION['id_user']==1)) || ($_SESSION['svt_si']!=session_id())) {
    die();
}
require_once("../../db/connection.php");
require_once("../functions.php");
$id_showcase = $_POST['id'];
$code = "";
$logo_exist = "";
$query = "SELECT code,logo FROM svt_showcases WHERE id=$id_showcase LIMIT 1;";
$result = $mysqli->query($query);
if($result) {
    if($result->num_rows==1) {
        $row = $result->fetch_array(MYSQLI_ASSOC);
        $code = $row['code'];
        $logo_exist = $row['logo'];
    }
}
$name = str_replace("'","\'",strip_tags($_POST['name']));
$friendly_url = str_replace("'","\'",strip_tags($_POST['friendly_url']));
$bg_color = $_POST['bg_color'];
if(empty($bg_color)) $bg_color='#eeeeee';
$logo = $_POST['logo'];
$banner = $_POST['banner'];
$list_s_vt = $_POST['list_s_vt'];
$list_s_type = $_POST['list_s_type'];
$header_html = str_replace("'","\'",htmlspecialchars_decode($_POST['header_html']));
$footer_html = str_replace("'","\'",htmlspecialchars_decode($_POST['footer_html']));
$custom_css = $_POST['custom_css'];

$url_css = __DIR__.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'showcase'.DIRECTORY_SEPARATOR.'css'.DIRECTORY_SEPARATOR.'custom_'.$code.'.css';
if(file_exists($url_css) && $custom_css=='') {
    @unlink($url_css);
} else {
    if($custom_css!='') {
        @file_put_contents($url_css,$custom_css);
    }
}

if(!empty($friendly_url)) {
    $query_check = "SELECT id FROM svt_showcases WHERE friendly_url='$friendly_url' AND id != $id_showcase;";
    $result_check = $mysqli->query($query_check);
    if($result_check) {
        if($result_check->num_rows>0) {
            ob_end_clean();
            echo json_encode(array("status"=>"error_furl"));
            exit;
        }
    }
}

$query = "UPDATE svt_showcases SET name='$name',friendly_url='$friendly_url',bg_color='$bg_color',logo='$logo',banner='$banner',header_html='$header_html',footer_html='$footer_html' WHERE id=$id_showcase;";
$result = $mysqli->query($query);

$mysqli->query("DELETE FROM svt_showcase_list WHERE id_showcase=$id_showcase;");
foreach ($list_s_vt as $index=>$id_vt) {
    $type = $list_s_type[$index];
    $mysqli->query("INSERT INTO svt_showcase_list(id_showcase,id_virtualtour,type_viewer) VALUES($id_showcase,$id_vt,'$type');");
}

if($result) {
    $path = __DIR__.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR;
    if(empty($logo)) {
        if(file_exists($path . "favicons" . DIRECTORY_SEPARATOR . "s_$code")) {
            array_map('unlink', glob($path . "favicons" . DIRECTORY_SEPARATOR . "s_$code" . DIRECTORY_SEPARATOR ."*.*"));
            rmdir($path . "favicons" . DIRECTORY_SEPARATOR . "s_$code" . DIRECTORY_SEPARATOR);
        }
    } else {
        if($logo!=$logo_exist) {
            if(file_exists($path . "favicons" . DIRECTORY_SEPARATOR . "s_$code")) {
                array_map('unlink', glob($path . "favicons" . DIRECTORY_SEPARATOR . "s_$code" . DIRECTORY_SEPARATOR ."*.*"));
                rmdir($path . "favicons" . DIRECTORY_SEPARATOR . "s_$code" . DIRECTORY_SEPARATOR);
            }
        }
        generate_favicons();
    }
    ob_end_clean();
    echo json_encode(array("status"=>"ok"));
} else {
    ob_end_clean();
    echo json_encode(array("status"=>"error"));
}

