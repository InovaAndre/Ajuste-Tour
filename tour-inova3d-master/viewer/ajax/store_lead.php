<?php
error_reporting(E_ALL & ~E_WARNING & ~E_NOTICE & ~E_DEPRECATED);
ob_start();
require_once("../../db/connection.php");

$id_virtualtour = $_POST['id_virtualtour'];
$name = trim(str_replace("'","\'",$_POST['name']));
$email = strtolower(trim(str_replace("'","\'",$_POST['email'])));
$phone = str_replace("'","\'",$_POST['phone']);

$query_check = "SELECT * FROM svt_leads WHERE id_virtualtour=$id_virtualtour AND email='$email' LIMIT 1;";
$result_check = $mysqli->query($query_check);
if($result_check) {
    if($result_check->num_rows==0) {
        $query = "INSERT INTO svt_leads(id_virtualtour,name,email,phone,datetime) VALUES($id_virtualtour,'$name','$email','$phone',NOW());";
        $result = $mysqli->query($query);
        if($result) {
            ob_end_clean();
            echo json_encode(array("status"=>"ok"));
        } else {
            ob_end_clean();
            echo json_encode(array("status"=>"error"));
        }
    } else {
        ob_end_clean();
        echo json_encode(array("status"=>"ok"));
    }
} else {
    ob_end_clean();
    echo json_encode(array("status"=>"error"));
}

