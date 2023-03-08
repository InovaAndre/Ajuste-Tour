<?php
error_reporting(E_ALL & ~E_WARNING & ~E_NOTICE & ~E_DEPRECATED);
ob_start();
session_start();
if((($_SERVER['SERVER_ADDR']=='5.9.29.89') && ($_SERVER['REMOTE_ADDR']!=$_SESSION['ip_developer']) && ($_SESSION['id_user']==1)) || ($_SESSION['svt_si']!=session_id())) {
    die();
}
require_once("../functions.php");
$id_virtualtour = $_POST['id_virtualtour'];
$compress_jpg = $_POST['compress_jpg'];
$max_width_compress = $_POST['max_width_compress'];
$enable_multires = $_POST['enable_multires'];
if($compress_jpg=="") $compress_jpg=90;
if($max_width_compress=="") $max_width_compress=0;
$mysqli->query("UPDATE svt_virtualtours SET enable_multires=$enable_multires,compress_jpg=$compress_jpg,max_width_compress=$max_width_compress WHERE id=$id_virtualtour;");
generate_multires(true,$id_virtualtour);
ob_end_clean();