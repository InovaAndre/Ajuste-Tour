<?php
error_reporting(E_ALL & ~E_WARNING & ~E_NOTICE & ~E_DEPRECATED);
ob_start();
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require_once('../vendor/PHPMailer/Exception.php');
require_once('../vendor/PHPMailer/PHPMailer.php');
require_once('../vendor/PHPMailer/SMTP.php');
require_once('../functions.php');
require_once("../../db/connection.php");
$settings = get_settings();
$name = $settings['name'];
$smtp_server = $settings['smtp_server'];
$smtp_auth = $settings['smtp_auth'];
$smtp_username = $settings['smtp_username'];
$smtp_password = $settings['smtp_password'];
$smtp_secure = $settings['smtp_secure'];
$smtp_port = $settings['smtp_port'];
$smtp_from_email = $settings['smtp_from_email'];
$smtp_from_name = $settings['smtp_from_name'];
$mail_activate_subject = $settings['mail_activate_subject'];
$mail_activate_body = $settings['mail_activate_body'];
$mail_forgot_subject = $settings['mail_forgot_subject'];
$mail_forgot_body = $settings['mail_forgot_body'];
$email = $_POST['email'];
$notify_id=0;
switch ($_POST['type']) {
    case 'validate':
        $subject = $name . ' - Test email';
        $body = 'This is a test e-mail for validating mail server settings.';
        break;
    case 'forgot':
        $query = "SELECT id FROM svt_users WHERE email='$email' LIMIT 1;";
        $result = $mysqli->query($query);
        if($result) {
            if($result->num_rows==1) {
                $row = $result->fetch_array(MYSQLI_ASSOC);
                $id_user = $row['id'];
            } else {
                ob_end_clean();
                echo json_encode(array("status"=>"error","msg"=>"Invalid e-mail"));
                exit;
            }
        }
        $verification_code = generateRandomString(16);
        $currentPath = $_SERVER['PHP_SELF'];
        $pathInfo = pathinfo($currentPath);
        $hostName = $_SERVER['HTTP_HOST'];
        if (is_ssl()) { $protocol = 'https'; } else { $protocol = 'http'; }
        $url = $protocol."://".$hostName.$pathInfo['dirname'];
        $url = str_replace("/ajax","",$url)."/login.php?forgot=1&email=$email&verification_code=$verification_code";
        $subject = $mail_forgot_subject;
        $mail_forgot_body = str_replace("<p>","<p style='padding:0;margin:0;'>",$mail_forgot_body);
        $mail_forgot_body = str_replace("%LINK%","<a href='$url'>$url</a>",$mail_forgot_body);
        $mail_forgot_body = str_replace("%VERIFICATION_CODE%",$verification_code,$mail_forgot_body);
        $body = $mail_forgot_body;
        break;
    case 'activate':
        $hash = $_POST['hash'];
        $currentPath = $_SERVER['PHP_SELF'];
        $pathInfo = pathinfo($currentPath);
        $hostName = $_SERVER['HTTP_HOST'];
        if (is_ssl()) { $protocol = 'https'; } else { $protocol = 'http'; }
        $url = $protocol."://".$hostName.$pathInfo['dirname'];
        $url = str_replace("/ajax","",$url)."/validate_email.php?email=$email&hash=$hash";
        $subject = $mail_activate_subject;
        $mail_activate_body = str_replace("<p>","<p style='padding:0;margin:0;'>",$mail_activate_body);
        $mail_activate_body = str_replace("%LINK%","<a href='$url'>$url</a>",$mail_activate_body);
        $body = $mail_activate_body;
        break;
    case 'notify':
        if(!$settings['notify_registrations']) {
            ob_end_clean();
            echo json_encode(array("status"=>"ok"));
            exit;
        }
        $id_user = $_POST['id_user'];
        if(!empty($settings['notify_email'])) {
            $email = $settings['notify_email'];
        } else {
            $email = $settings['smtp_from_email'];
        }
        $subject = _("New registered user");
        $query = "SELECT u.username,u.email,u.expire_plan_date,p.name as plan FROM svt_users as u LEFT JOIN svt_plans as p ON p.id=u.id_plan WHERE u.id=$id_user;";
        $result = $mysqli->query($query);
        if($result) {
            if($result->num_rows==1) {
                $row = $result->fetch_array(MYSQLI_ASSOC);
                $username = $row['username'];
                $email_u = $row['email'];
                $plan = $row['plan'];
                $expire_plan_date = $row['expire_plan_date'];
            }
        }
        $body = _("Username").": $username<br>"._("E-Mail").": $email_u<br>"._("Plan").": $plan<br>"._("Expires on").": $expire_plan_date";
        $subject_q = str_replace("'","\'",$subject);
        $body_q = str_replace("'","\'",$body);
        $result_ins = $mysqli->query("INSERT INTO svt_notifications(id_user,subject,body,notified) VALUES($id_user,'$subject_q','$body_q',1);");
        if($result_ins) {
            $vt_create_id = $mysqli->insert_id;
        }
        break;
    case 'form':
        $form_data = array();
        parse_str($_POST['form_data'], $form_data);
        $title = $form_data['title'];
        $subject = _("Form")." :".$title;
        $body = "";
        for($i=1;$i<=10;$i++) {
            if(isset($form_data['form_field_'.$i])) {
                $form_label = $form_data['form_label_'.$i];
                $form_field = $form_data['form_field_'.$i];
                $form_field = strip_tags($form_field);
                $body .= "$form_label: $form_field<br>";
            }
        }
        break;
    case 'lead':
        $room_name = $_POST['room_name'];
        $lead_name = $_POST['lead_name'];
        $lead_email = $_POST['lead_email'];
        $lead_phone = $_POST['lead_phone'];
        $subject = _("Lead")." :".$room_name;
        $body = _("Name").": $lead_name<br>"._("E-Mail").": $lead_email<br>"._("Phone").": $lead_phone";
        break;
    case 'vt_create':
        if(!$settings['notify_vt_create']) {
            ob_end_clean();
            echo json_encode(array("status"=>"ok"));
            exit;
        }
        $id_user = $_POST['id_user'];
        if(get_user_role($id_user)!='customer') {
            ob_end_clean();
            echo json_encode(array("status"=>"ok"));
            exit;
        }
        $id_vt = $_POST['id_vt'];
        if(!empty($settings['notify_email'])) {
            $email = $settings['notify_email'];
        } else {
            $email = $settings['smtp_from_email'];
        }
        $subject = _("New tour created");
        $query = "SELECT username,email FROM svt_users WHERE id=$id_user;";
        $result = $mysqli->query($query);
        if($result) {
            if($result->num_rows==1) {
                $row = $result->fetch_array(MYSQLI_ASSOC);
                $username = $row['username'];
                $email_u = $row['email'];
            }
        }
        $query = "SELECT name FROM svt_virtualtours WHERE id=$id_vt;";
        $result = $mysqli->query($query);
        if($result) {
            if($result->num_rows==1) {
                $row = $result->fetch_array(MYSQLI_ASSOC);
                $tour = $row['name'];
            }
        }
        $body = _("Username").": $username<br>"._("E-Mail").": $email_u<br>"._("Tour").": $tour";
        $subject_q = str_replace("'","\'",$subject);
        $body_q = str_replace("'","\'",$body);
        $result_ins = $mysqli->query("INSERT INTO svt_notifications(id_user,subject,body,notified) VALUES($id_user,'$subject_q','$body_q',1);");
        if($result_ins) {
            $notify_id = $mysqli->insert_id;
        }
        break;
}
$mail = new PHPMailer(true);
try {
    $mail->isSMTP();
    $mail->SMTPDebug = 2;
    $mail->Timeout = 10;
    $mail->Host = $smtp_server;
    $mail->SMTPAuth = $smtp_auth;
    $mail->Username = $smtp_username;
    $mail->Password = $smtp_password;
    switch($smtp_secure) {
        case 'ssl':
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
            break;
        case 'tls':
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            break;
    }
    $mail->Port = $smtp_port;
    $mail->setFrom($smtp_from_email, $smtp_from_name);
    $mail->addAddress($email);
    $mail->isHTML(true);
    $mail->Subject = $subject;
    $mail->Body = $body;
    $mail->send();
    switch ($_POST['type']) {
        case 'validate':
            $mysqli->query("UPDATE svt_settings SET smtp_valid=1;");
            break;
        case 'forgot':
            $mysqli->query("UPDATE svt_users SET forgot_code='$verification_code' WHERE id=$id_user;");
            break;
    }
    ob_end_clean();
    echo json_encode(array("status"=>"ok"));
    exit;
} catch (Exception $e) {
    switch ($_POST['type']) {
        case 'validate':
            $mysqli->query("UPDATE svt_settings SET smtp_valid=0;");
            break;
        case 'notify':
        case 'vt_create':
            if($notify_id!=0) {
                $mysqli->query("UPDATE svt_notifications SET notified=0 WHERE id=$notify_id;");
            }
            break;
    }
    ob_end_clean();
    echo json_encode(array("status"=>"error","msg"=>$mail->ErrorInfo));
    exit;
}