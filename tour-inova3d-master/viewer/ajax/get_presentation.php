<?php
error_reporting(E_ALL & ~E_WARNING & ~E_NOTICE & ~E_DEPRECATED);
ob_start();
require_once("../../db/connection.php");
$id_virtualtour = $_POST['id_virtualtour'];
$query = "SELECT id_room,action,sleep,params FROM svt_presentations WHERE id_virtualtour=$id_virtualtour ORDER BY priority_1,priority_2;";
$result = $mysqli->query($query);
$presentation = array();
if($result) {
    if($result->num_rows>0) {
        while($row=$result->fetch_array(MYSQLI_ASSOC)) {
            $row['id_room'] = (int) $row['id_room'];
            $row['sleep'] = (int) $row['sleep'];
            switch ($row['action']) {
                case 'type':
                    $row['params'] = preg_split("/\\r\\n|\\r|\\n/", $row['params']);
                    if(end($row['params'])!='') {
                        array_push($row['params'],'');
                    }
                    break;
                case 'goto':
                    $row['params'] = (int) $row['params'];
                    break;
                case 'lookAt':
                    $row['params'] = explode(",",$row['params']);
                    $row['params'] = array_map('intval', $row['params']);
                    break;
            }
            $presentation[] = $row;
        }
        ob_end_clean();
        echo json_encode(array("status"=>"ok","presentation"=>$presentation));
    } else {
        ob_end_clean();
        echo json_encode(array("status"=>"error"));
    }
}