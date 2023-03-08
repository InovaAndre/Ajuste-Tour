<?php
error_reporting(E_ALL & ~E_WARNING & ~E_NOTICE & ~E_DEPRECATED);
ob_start();
session_start();
if((($_SERVER['SERVER_ADDR']=='5.9.29.89') && ($_SERVER['REMOTE_ADDR']!=$_SESSION['ip_developer']) && ($_SESSION['id_user']==1)) || ($_SESSION['svt_si']!=session_id())) {
    die();
}
require_once("../../db/connection.php");
require_once("../functions.php");
$settings = get_settings();
$user_info = get_user_info($_SESSION['id_user']);
$old_plan = $user_info['plan'];
if(!empty($user_info['language'])) {
    set_language($user_info['language'],$settings['language_domain']);
} else {
    set_language($settings['language'],$settings['language_domain']);
}
$id_user = $_POST['id_user'];
$intent = $_POST['intent'];
if(isset($_POST['subscriptionID'])) {
    $subscriptionID = $_POST['subscriptionID'];
} else {
    ob_end_clean();
    echo json_encode(array("status"=>"error","msg"=>_("An error has occurred, please try again later")));
}
$client_id = $settings['paypal_client_id'];
$client_secret = $settings['paypal_client_secret'];
if($settings['paypal_live']) {
    $url_paypal = "api-m.paypal.com";
} else {
    $url_paypal = "api-m.sandbox.paypal.com";
}
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'https://'.$url_paypal.'/v1/oauth2/token');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, "grant_type=client_credentials");
curl_setopt($ch, CURLOPT_USERPWD, $client_id . ':' . $client_secret);
$headers = array();
$headers[] = 'Accept: application/json';
$headers[] = 'Accept-Language: en_US';
$headers[] = 'Content-Type: application/x-www-form-urlencoded';
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
$result = curl_exec($ch);
if (curl_errno($ch)) {
    echo json_encode(array("status"=>"error","msg"=>curl_error($ch)));
    die();
} else {
    $response = json_decode($result,true);
    if(isset($response['error'])) {
        echo json_encode(array("status"=>"error","msg"=>$response['error_description']));
        die();
    } else {
        if(isset($response['access_token'])) {
            $access_token = $response['access_token'];
        } else {
            echo json_encode(array("status"=>"error","msg"=>"An error has occurred, please try again later"));
            die();
        }
    }
}
curl_close($ch);
switch($intent) {
    case 'subscription':
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://'.$url_paypal.'/v1/billing/subscriptions/'.$subscriptionID);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
        $headers = array();
        $headers[] = 'Content-Type: application/json';
        $headers[] = 'Authorization: Bearer '.$access_token;
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        $result = curl_exec($ch);
        if (curl_errno($ch)) {
            echo json_encode(array("status"=>"error","msg"=>curl_error($ch)));
            die();
        } else {
            $response = json_decode($result,true);
            if(isset($response['plan_id'])) {
                $id_paypal_plan = $response['plan_id'];
            } else {
                echo json_encode(array("status"=>"error","msg"=>"An error has occurred, please try again later"));
                die();
            }
        }
        curl_close($ch);
        $query = "UPDATE svt_users SET id_subscription_paypal='$subscriptionID',id_plan=(SELECT id FROM svt_plans WHERE id_plan_paypal='$id_paypal_plan' LIMIT 1),expire_plan_date=NULL,status_subscription_paypal=1 WHERE id=$id_user;";
        break;
    case 'order':
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://'.$url_paypal.'/v1/checkout/orders/'.$subscriptionID);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
        $headers = array();
        $headers[] = 'Content-Type: application/json';
        $headers[] = 'Authorization: Bearer '.$access_token;
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        $result = curl_exec($ch);
        if (curl_errno($ch)) {
            echo json_encode(array("status"=>"error","msg"=>curl_error($ch)));
            die();
        } else {
            $response = json_decode($result,true);
            if(isset($response['purchase_units'][0]['payments']['captures'][0]['custom_id'])) {
                $id_paypal_plan = $response['purchase_units'][0]['payments']['captures'][0]['custom_id'];
            } else {
                echo json_encode(array("status"=>"error","msg"=>"An error has occurred, please try again later"));
                die();
            }
        }
        curl_close($ch);
        $query = "UPDATE svt_users SET id_subscription_paypal=NULL,id_plan=$id_paypal_plan,expire_plan_date=NULL,status_subscription_paypal=1 WHERE id=$id_user;";
        break;
}
$result = $mysqli->query($query);
if($result) {
    if($settings['notify_plan_changes']) {
        $query = "SELECT u.id,u.username,u.email,p.name as plan FROM svt_users as u LEFT JOIN svt_plans as p ON p.id=u.id_plan WHERE u.id=$id_user;";
        $result = $mysqli->query($query);
        if($result) {
            if($result->num_rows>0) {
                while($row=$result->fetch_array(MYSQLI_ASSOC)) {
                    $username = $row['username'];
                    $email_u = $row['email'];
                    $plan = $row['plan'];
                    $subject = _("Changed plan");
                    $body = _("Username").": $username<br>"._("E-Mail").": $email_u<br>"._("Old Plan").": $old_plan<br>"._("New Plan").": $plan";
                    $subject_q = str_replace("'","\'",$subject);
                    $body_q = str_replace("'","\'",$body);
                    $mysqli->query("INSERT INTO svt_notifications(id_user,subject,body,notified) VALUES($id_user,'$subject_q','$body_q',0);");
                }
            }
        }
    }
    ob_end_clean();
    echo json_encode(array("status"=>"ok"));
} else {
    ob_end_clean();
    echo json_encode(array("status"=>"error","msg"=>_("An error has occurred, please try again later")));
}

