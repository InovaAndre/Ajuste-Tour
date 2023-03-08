<?php
error_reporting(E_ALL & ~E_WARNING & ~E_NOTICE & ~E_DEPRECATED);
ob_start();
session_start();
if($_SESSION['svt_si']!=session_id()) {
    die();
}

require_once("../../db/connection.php");
$username = str_replace("'","\'",$_POST['username_svt']);
$password = str_replace("'","\'",$_POST['password_svt']);

$result = $mysqli->query("SHOW COLUMNS FROM svt_users LIKE 'email';");
if($result) {
    if ($result->num_rows==0) {
        $mysqli->query("ALTER TABLE svt_users ADD `email` varchar(100) DEFAULT NULL AFTER `username`;");
    }
}

$id_user = 0;
$query = "SELECT id FROM svt_users WHERE (username='$username' OR email='$username') LIMIT 1;";
$result = $mysqli->query($query);
if($result) {
    if ($result->num_rows == 1) {
        $row = $result->fetch_array(MYSQLI_ASSOC);
        $id_user = $row['id'];
    } else {
        ob_end_clean();
        echo json_encode(array("status"=>"incorrect_username"));
        exit;
    }
}

$query = "SELECT * FROM svt_users WHERE id=$id_user AND password=MD5('$password') LIMIT 1;";
$result = $mysqli->query($query);
if($result) {
    if ($result->num_rows == 1) {
        $row = $result->fetch_array(MYSQLI_ASSOC);
        if($row['email']=='') $row['email']='set';
        if($row['active']) {
            $_SESSION['id_user'] = $id_user;
            ob_end_clean();
            echo json_encode(array("status"=>"ok","id"=>$row['id'],"role"=>$row['role'],"email"=>$row['email']));
        } else {
            ob_end_clean();
            echo json_encode(array("status"=>"blocked"));
        }
    } else {
        ob_end_clean();
        echo json_encode(array("status"=>"incorrect_password"));
    }
} else {
    ob_end_clean();
    echo json_encode(array("status"=>"error"));
}

