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
$query = "SELECT p.*,r.panorama_image,r.name as room_name FROM svt_presentations as p 
LEFT JOIN svt_rooms as r ON r.id=p.id_room
WHERE p.id_virtualtour=$id_virtualtour ORDER BY p.priority_1,p.priority_2;";
$result = $mysqli->query($query);
if($result) {
    if($result->num_rows>0) {
        while($row=$result->fetch_array(MYSQLI_ASSOC)) {
            switch ($row['action']) {
                case 'type':
                    $row['text'] = $row['params'];
                    $row['params'] = preg_split("/\\r\\n|\\r|\\n/", $row['params']);
                    $row['params'] = implode(" | ",$row['params']);
                    break;
                case 'lookAt':
                    $row['params'] = explode(",",$row['params']);
                    $row['params'] = array_map('intval', $row['params']);
                    $row['yaw'] = $row['params'][1];
                    $row['pitch'] = $row['params'][0];
                    $row['hfov'] = $row['params'][2];
                    $row['animation'] = $row['params'][3];
                    $row['params'] = $row['params'][1].",".$row['params'][0]." (".$row['params'][2].") <i class='far fa-clock'></i> ".$row['params'][3]."ms";
                    break;
            }
            $array[]=$row;
        }
    }
}
ob_end_clean();
echo json_encode($array);