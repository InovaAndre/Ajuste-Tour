<?php
header('Access-Control-Allow-Origin: *');
header("Access-Control-Allow-Headers: *");
header('Content-Type: application/json; charset=utf-8');
error_reporting(E_ALL & ~E_WARNING & ~E_NOTICE & ~E_DEPRECATED);
ob_start();
session_start();
require_once("../db/connection.php");

$content = trim(file_get_contents("php://input"));
$params = json_decode($content, true);

$endpoint = $params['endpoint'];
if(empty($endpoint)) {
    echo json_encode(array("code"=>400,"message"=>"missing endpoint parameter"));
    exit;
}

switch($endpoint) {
    case 'login':
        $username = $params['username'];
        $password = $params['password'];
        if(empty($username)) {
            echo json_encode(array("code"=>403,"message"=>"missing username parameter"));
            exit;
        }
        if(empty($password)) {
            echo json_encode(array("code"=>404,"message"=>"missing password parameter"));
            exit;
        }
        $response = check_login($username,$password);
        ob_end_clean();
        echo $response;
        break;
}

function check_login($username,$password) {
    global $mysqli;
    $username = str_replace("'","\'",$username);
    $password = str_replace("'","\'",$password);
    $query = "SELECT id FROM svt_users WHERE (username='$username' OR email='$username') LIMIT 1;";
    $result = $mysqli->query($query);
    if($result) {
        if ($result->num_rows == 1) {
            $row = $result->fetch_array(MYSQLI_ASSOC);
            $id_user = $row['id'];
            $query = "SELECT * FROM svt_users WHERE id=$id_user AND password=MD5('$password') LIMIT 1;";
            $result = $mysqli->query($query);
            if($result) {
                if ($result->num_rows == 1) {
                    $row = $result->fetch_array(MYSQLI_ASSOC);
                    if($row['active']) {
                        $response =  json_encode(array("code"=>200,"message"=>"ok","token"=>encrypt_decrypt('encrypt',$id_user,date('YmdHi'))));
                    } else {
                        $response =  json_encode(array("code"=>201,"message"=>"blocked"));
                    }
                } else {
                    $response =  json_encode(array("code"=>202,"message"=>"incorrect password"));
                }
            } else {
                $response = json_encode(array("code"=>401,"message"=>"error"));
            }
        } else {
            $response = json_encode(array("code"=>203,"message"=>"incorrect username"));
        }
    } else {
        $response = json_encode(array("code"=>402,"message"=>"error"));
    }
    return $response;
}

function encrypt_decrypt($action, $string, $secret_key = "supersecret_key") {
    $output = false;
    $encrypt_method = "AES-256-CBC";
    $secret_iv = '#svt#';
    $key = hash('sha256', $secret_key);
    $iv = substr(hash('sha256', $secret_iv), 0, 16);
    if ( $action == 'encrypt' ) {
        $output = openssl_encrypt($string, $encrypt_method, $key, 0, $iv);
        $output = base64_encode($output);
    } else if( $action == 'decrypt' ) {
        $output = openssl_decrypt(base64_decode($string), $encrypt_method, $key, 0, $iv);
    }
    return $output;
}
