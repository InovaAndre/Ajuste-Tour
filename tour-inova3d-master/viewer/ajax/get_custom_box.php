<?php
error_reporting(E_ALL & ~E_WARNING & ~E_NOTICE & ~E_DEPRECATED);
ob_start();
require_once("../../db/connection.php");
$id_virtualtour = $_POST['id_virtualtour'];

$info_box = "";
$query = "SELECT custom_content FROM svt_virtualtours WHERE id=$id_virtualtour AND show_custom>0 LIMIT 1;";
$result = $mysqli->query($query);
if($result) {
    if($result->num_rows==1) {
        $row=$result->fetch_array(MYSQLI_ASSOC);
        $custom_content = $row['custom_content'];
        if(($custom_content=="<div></div>") || (empty($custom_content))) $custom_content="";
    }
}
ob_end_clean();
echo json_encode(array("custom_box"=>$custom_content));