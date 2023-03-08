<?php
error_reporting(E_ALL & ~E_WARNING & ~E_NOTICE & ~E_DEPRECATED);
ob_start();
session_start();
if((($_SERVER['SERVER_ADDR']=='5.9.29.89') && ($_SERVER['REMOTE_ADDR']!=$_SESSION['ip_developer']) && ($_SESSION['id_user']==1)) || ($_SESSION['svt_si']!=session_id())) {
    die();
}
require_once("../../db/connection.php");
require_once("../functions.php");
$settings = get_settings();
$user_info = get_user_info($_SESSION['id_user']);
if(!empty($user_info['language'])) {
    set_language($user_info['language'],$settings['language_domain']);
} else {
    set_language($settings['language'],$settings['language_domain']);
}
$id_user = $_SESSION['id_user'];
if(!get_user_role($id_user)=='administrator') {
    ob_end_clean();
    echo json_encode(array("status"=>"error"));
    exit;
}
$id_user = $_POST['id_svt'];
$username = str_replace("'","\'",strip_tags($_POST['username_svt']));
$email = str_replace("'","\'",$_POST['email_svt']);
$role = $_POST['role_svt'];
$id_plan = $_POST['plan_svt'];
$active = $_POST['active_svt'];
$language = $_POST['language_svt'];
$expire_plan_date_manual_date = $_POST['expire_plan_date_manual_date_svt'];
$expire_plan_date_manual_time = $_POST['expire_plan_date_manual_time_svt'];

if(empty($expire_plan_date_manual_date) && empty($expire_plan_date_manual_time)) {
    $expire_plan_date_manual = 'NULL';
} else if(!empty($expire_plan_date_manual_date) && empty($expire_plan_date_manual_time)) {
    $expire_plan_date_manual = "'$expire_plan_date_manual_date 23:59:00'";
} else if(empty($expire_plan_date_manual_date) && !empty($expire_plan_date_manual_time)) {
    $expire_plan_date_manual = 'NULL';
} else {
    $expire_plan_date_manual = "'$expire_plan_date_manual_date $expire_plan_date_manual_time'";
}

$query_check = "SELECT * FROM svt_users WHERE username='$username' AND id!=$id_user;";
$result_check = $mysqli->query($query_check);
if($result_check->num_rows>0) {
    ob_end_clean();
    echo json_encode(array("status"=>"error","msg"=>_("Username already registered!")));
    exit;
}
$query_check = "SELECT * FROM svt_users WHERE email='$email' AND id!=$id_user;";
$result_check = $mysqli->query($query_check);
if($result_check->num_rows>0) {
    ob_end_clean();
    echo json_encode(array("status"=>"error","msg"=>_("E-mail already registered!")));
    exit;
}

$reload = 0;
$query_check = "SELECT expire_plan_date_manual FROM svt_users WHERE id=$id_user LIMIT 1;";
$result_check = $mysqli->query($query_check);
if($result_check->num_rows==1) {
    $row_check = $result_check->fetch_array(MYSQLI_ASSOC);
    if(empty($row_check['expire_plan_date_manual'])) $row_check['expire_plan_date_manual']='NULL'; else $row_check['expire_plan_date_manual']="'".$row_check['expire_plan_date_manual']."'";
    if($row_check['expire_plan_date_manual']!=$expire_plan_date_manual) {
        $reload = 1;
    }
}

$query = "UPDATE svt_users SET username='$username',email='$email',role='$role',id_plan=$id_plan,active=$active,language='$language',expire_plan_date_manual=$expire_plan_date_manual WHERE id=$id_user;";
$result = $mysqli->query($query);

if($result) {
    update_plans_expires_date($id_user);
    ob_end_clean();
    echo json_encode(array("status"=>"ok","reload"=>$reload));
} else {
    ob_end_clean();
    echo json_encode(array("status"=>"error","msg"=>_("An error has occurred, please try again later")));
}

