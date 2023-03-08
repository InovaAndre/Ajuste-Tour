<?php
error_reporting(E_ALL & ~E_WARNING & ~E_NOTICE & ~E_DEPRECATED);
ob_start();
session_start();
if($_SESSION['svt_si']!=session_id()) {
    die();
}

require_once("../../db/connection.php");
$id_virtualtour = $_POST['id_virtualtour'];

$array = array();
$query = "SELECT * FROM svt_gallery WHERE id_virtualtour=$id_virtualtour ORDER BY priority;";
$result = $mysqli->query($query);
if($result) {
    if($result->num_rows>0) {
        while($row=$result->fetch_array(MYSQLI_ASSOC)) {
            if(empty($row['title'])) $row['title']="";
            if(empty($row['description'])) $row['description']="";
            $array[]=$row;
        }
    }
}
ob_end_clean();
echo json_encode($array);