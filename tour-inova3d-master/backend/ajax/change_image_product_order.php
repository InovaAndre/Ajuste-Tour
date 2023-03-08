<?php
error_reporting(E_ALL & ~E_WARNING & ~E_NOTICE & ~E_DEPRECATED);
ob_start();
session_start();
if((($_SERVER['SERVER_ADDR']=='5.9.29.89') && ($_SERVER['REMOTE_ADDR']!=$_SESSION['ip_developer']) && ($_SESSION['id_user']==1)) || ($_SESSION['svt_si']!=session_id())) {
    die();
}
require_once("../../db/connection.php");
$id_product= $_POST['id_product'];
$array_images_priority = json_decode($_POST['array_images_priority'],true);

foreach ($array_images_priority as $priority=>$id) {
    $mysqli->query("UPDATE svt_product_images SET priority=$priority WHERE id=$id AND id_product=$id_product;");
}
ob_end_clean();