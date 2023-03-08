<?php
error_reporting(E_ALL & ~E_WARNING & ~E_NOTICE & ~E_DEPRECATED);
ob_start();
require_once("../../db/connection.php");
$id_virtualtour = $_POST['id_virtualtour'];

$info_box = "";
$query = "SELECT info_box FROM svt_virtualtours WHERE id=$id_virtualtour AND show_info>0 LIMIT 1;";
$result = $mysqli->query($query);
if($result) {
    if($result->num_rows==1) {
        $row=$result->fetch_array(MYSQLI_ASSOC);
        $info_box = $row['info_box'];
        if(($info_box=="<p><br></p>") || (empty($info_box))) $info_box="";
    }
}
ob_end_clean();
echo json_encode(array("info_box"=>$info_box));