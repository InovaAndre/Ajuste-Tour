<?php
error_reporting(E_ALL & ~E_WARNING & ~E_NOTICE & ~E_DEPRECATED);
ob_start();
session_start();
if((($_SERVER['SERVER_ADDR']=='5.9.29.89') && ($_SERVER['REMOTE_ADDR']!=$_SESSION['ip_developer']) && ($_SESSION['id_user']==1)) || ($_SESSION['svt_si']!=session_id())) {
    die();
}
require_once("../../db/connection.php");
$id_user = $_POST['id_svt'];
$username = str_replace("'","\'",strip_tags($_POST['username_svt']));
$email = str_replace("'","\'",$_POST['email_svt']);
$language = $_POST['language_svt'];
$avatar = $_POST['avatar_svt'];
$first_name = str_replace("'","\'",$_POST['first_name']);
$last_name = str_replace("'","\'",$_POST['last_name']);
$company = str_replace("'","\'",$_POST['company']);
$tax_id = str_replace("'","\'",$_POST['tax_id']);
$street = str_replace("'","\'",$_POST['street']);
$city = str_replace("'","\'",$_POST['city']);
$province = str_replace("'","\'",$_POST['province']);
$postal_code = str_replace("'","\'",$_POST['postal_code']);
$country = str_replace("'","\'",$_POST['country']);
$tel = str_replace("'","\'",$_POST['tel']);

$query_check = "SELECT id FROM svt_users WHERE username='$username' AND id!=$id_user;";
$result_check = $mysqli->query($query_check);
if($result_check->num_rows>0) {
    ob_end_clean();
    echo json_encode(array("status"=>"error","msg"=>_("Username already registered!")));
    exit;
}
$query_check = "SELECT id FROM svt_users WHERE email='$email' AND id!=$id_user;";
$result_check = $mysqli->query($query_check);
if($result_check->num_rows>0) {
    ob_end_clean();
    echo json_encode(array("status"=>"error","msg"=>_("E-mail already registered!")));
    exit;
}

$reload = 0;
if (strpos($avatar, 'data:image') !== false) {
    $avatar_image = base64_decode(explode(",",$avatar)[1]);
    $im = @imagecreatefromstring($avatar_image);
    $name_avatar = 'avatar_'.time().'.jpg';
    imagejpeg($im, dirname(__FILE__).'/../assets/'.$name_avatar,100);
    if(file_exists(dirname(__FILE__).'/../assets/'.$name_avatar)) {
        $mysqli->query("UPDATE svt_users SET avatar='$name_avatar' WHERE id=$id_user;");
        $reload = true;
    }
}

$query_l = "SELECT language,username FROM svt_users WHERE id=$id_user LIMIT 1;";
$result_l = $mysqli->query($query_l);
if($result_l->num_rows==1) {
    $row_l = $result_l->fetch_array(MYSQLI_ASSOC);
    $language_exist = $row_l['language'];
    $username_exist = $row_l['username'];
    if($language!=$language_exist) {
        $_SESSION['lang']=$language;
        $reload = 1;
    }
    if($username!=$username_exist) {
        $reload = 1;
    }
}

$query = "UPDATE svt_users SET username='$username',email='$email',language='$language',first_name='$first_name',last_name='$last_name',company='$company',tax_id='$tax_id',street='$street',city='$city',province='$province',postal_code='$postal_code',country='$country',tel='$tel' WHERE id=$id_user;";
$result = $mysqli->query($query);

if($result) {
    ob_end_clean();
    echo json_encode(array("status"=>"ok","reload_page"=>$reload));
} else {
    ob_end_clean();
    echo json_encode(array("status"=>"error","msg"=>_("Error")));
}

