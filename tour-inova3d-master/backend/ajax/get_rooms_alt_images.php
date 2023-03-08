<?php
error_reporting(E_ALL & ~E_WARNING & ~E_NOTICE & ~E_DEPRECATED);
ob_start();
session_start();
if($_SESSION['svt_si']!=session_id()) {
    die();
}

require_once("../../db/connection.php");
$id_room = $_POST['id_room'];
$array = array();
$query = "SELECT * FROM svt_rooms_alt WHERE poi=0 AND id_room=$id_room;";
$result = $mysqli->query($query);
if($result) {
    while ($row = $result->fetch_array(MYSQLI_ASSOC)) {
        $array[]=$row;
    }
}
ob_end_clean();
echo json_encode($array);