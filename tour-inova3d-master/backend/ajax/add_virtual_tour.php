<?php
error_reporting(E_ALL & ~E_WARNING & ~E_NOTICE & ~E_DEPRECATED);
ob_start();
session_start();
if((($_SERVER['SERVER_ADDR']=='5.9.29.89') && ($_SERVER['REMOTE_ADDR']!=$_SESSION['ip_developer']) && ($_SESSION['id_user']==1)) || ($_SESSION['svt_si']!=session_id())) {
    die();
}
require_once("../../db/connection.php");
require_once("../functions.php");
$id_user = $_POST['id_user'];
$name = str_replace("'","\'",strip_tags($_POST['name']));
$author = str_replace("'","\'",strip_tags($_POST['author']));
$external = $_POST['external'];
$settings = get_settings();
$id_vt_template = $settings['id_vt_template'];

//$id_vt_template = NULL;
if(!$external && !empty($id_vt_template)) {
    $mysqli->query("CREATE TEMPORARY TABLE svt_virtualtour_tmp SELECT * FROM svt_virtualtours WHERE id = $id_vt_template;");
    $mysqli->query("UPDATE svt_virtualtour_tmp SET id=(SELECT MAX(id)+1 as id FROM svt_virtualtours),active=1,code=NULL,list_alt=NULL,start_date=NULL,end_date=NULL,snipcart_api_key=NULL,id_category=NULL,password=NULL,note=NULL,html_landing=NULL,description=NULL,dollhouse=NULL,ga_tracking_id=NULL,fb_page_id=NULL,friendly_url=NULL,id_user=$id_user,name='$name',author='$author',date_created=NOW();");
    $result = $mysqli->query("INSERT INTO svt_virtualtours SELECT * FROM svt_virtualtour_tmp;");
    $insert_id = $mysqli->insert_id;
    $mysqli->query("DROP TEMPORARY TABLE IF EXISTS svt_virtualtours_tmp;");
} else {
    $query = "INSERT INTO svt_virtualtours(id_user,date_created,name,author,hfov,min_hfov,max_hfov,external)
            VALUES($id_user,NOW(),'$name','$author',100,50,100,$external);";
    $result = $mysqli->query($query);
    $insert_id = $mysqli->insert_id;
}

if($result) {
    $_SESSION['id_virtualtour_sel'] = $insert_id;
    $_SESSION['name_virtualtour_sel'] = $_POST['name'];
    $code = md5($insert_id);
    $mysqli->query("UPDATE svt_virtualtours SET code='$code' WHERE id=$insert_id;");
    $query = "SELECT id FROM svt_advertisements WHERE auto_assign=1 LIMIT 1;";
    $result = $mysqli->query($query);
    if($result) {
        if($result->num_rows==1) {
            $row=$result->fetch_array(MYSQLI_ASSOC);
            $id_ads=$row['id'];
            $mysqli->query("INSERT INTO svt_assign_advertisements(id_advertisement,id_virtualtour) VALUES($id_ads,$insert_id);");
        }
    }
    ob_end_clean();
    echo json_encode(array("status"=>"ok","id"=>$insert_id));
} else {
    ob_end_clean();
    echo json_encode(array("status"=>"error","msg"=>$mysqli->error));
}

