<?php
error_reporting(E_ALL & ~E_WARNING & ~E_NOTICE & ~E_DEPRECATED);
ob_start();
require_once("../../db/connection.php");
$form_data = array();
parse_str($_POST['form_data'], $form_data);

$id_virtualtour = $_POST['id_virtualtour'];
$id_room = $form_data['id_room'];
$title = $form_data['title'];
$title = str_replace("'","\'",$title);
$email = $form_data['email'];

if(isset($form_data['form_field_1'])) {
    $form_field_1 = $form_data['form_field_1'];
    $form_field_1 = strip_tags($form_field_1);
    $form_field_1 = str_replace("'","\'",$form_field_1);
} else {
    $form_field_1 = "";
}
if(isset($form_data['form_field_2'])) {
    $form_field_2 = $form_data['form_field_2'];
    $form_field_2 = strip_tags($form_field_2);
    $form_field_2 = str_replace("'","\'",$form_field_2);
} else {
    $form_field_2 = "";
}
if(isset($form_data['form_field_3'])) {
    $form_field_3 = $form_data['form_field_3'];
    $form_field_3 = strip_tags($form_field_3);
    $form_field_3 = str_replace("'","\'",$form_field_3);
} else {
    $form_field_3 = "";
}
if(isset($form_data['form_field_4'])) {
    $form_field_4 = $form_data['form_field_4'];
    $form_field_4 = strip_tags($form_field_4);
    $form_field_4 = str_replace("'","\'",$form_field_4);
} else {
    $form_field_4 = "";
}
if(isset($form_data['form_field_5'])) {
    $form_field_5 = $form_data['form_field_5'];
    $form_field_5 = strip_tags($form_field_5);
    $form_field_5 = str_replace("'","\'",$form_field_5);
} else {
    $form_field_5 = "";
}
if(isset($form_data['form_field_6'])) {
    $form_field_6 = $form_data['form_field_6'];
    $form_field_6 = strip_tags($form_field_6);
    $form_field_6 = str_replace("'","\'",$form_field_6);
} else {
    $form_field_6 = "";
}
if(isset($form_data['form_field_7'])) {
    $form_field_7 = $form_data['form_field_7'];
    $form_field_7 = strip_tags($form_field_7);
    $form_field_7 = str_replace("'","\'",$form_field_7);
} else {
    $form_field_7 = "";
}
if(isset($form_data['form_field_8'])) {
    $form_field_8 = $form_data['form_field_8'];
    $form_field_8 = strip_tags($form_field_8);
    $form_field_8 = str_replace("'","\'",$form_field_8);
} else {
    $form_field_8 = "";
}
if(isset($form_data['form_field_9'])) {
    $form_field_9 = $form_data['form_field_9'];
    $form_field_9 = strip_tags($form_field_9);
    $form_field_9 = str_replace("'","\'",$form_field_9);
} else {
    $form_field_9 = "";
}
if(isset($form_data['form_field_10'])) {
    $form_field_10 = $form_data['form_field_10'];
    $form_field_10 = strip_tags($form_field_10);
    $form_field_10 = str_replace("'","\'",$form_field_10);
} else {
    $form_field_10 = "";
}

$query = "INSERT INTO svt_forms_data(id_virtualtour,id_room,title,field1,field2,field3,field4,field5,field6,field7,field8,field9,field10,datetime) VALUES($id_virtualtour,$id_room,'$title','$form_field_1','$form_field_2','$form_field_3','$form_field_4','$form_field_5','$form_field_6','$form_field_7','$form_field_8','$form_field_9','$form_field_10',NOW());";
$result = $mysqli->query($query);
if($result) {
    ob_end_clean();
    echo json_encode(array("status"=>"ok","email"=>$email));
} else {
    ob_end_clean();
    echo json_encode(array("status"=>"error","msg"=>$mysqli->error));
}