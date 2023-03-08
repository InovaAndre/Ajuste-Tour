<?php
error_reporting(E_ALL & ~E_WARNING & ~E_NOTICE & ~E_DEPRECATED);
ob_start();
session_start();
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require_once(__DIR__."/../db/connection.php");
require_once(__DIR__."/../backend/functions.php");
require_once(__DIR__."/../backend/vendor/PHPMailer/Exception.php");
require_once(__DIR__."/../backend/vendor/PHPMailer/PHPMailer.php");
require_once(__DIR__."/../backend/vendor/PHPMailer/SMTP.php");

$settings = get_settings();

$now = date('Y-m-d H:i');

if($settings['notify_plan_expires']) {
    $query = "SELECT u.id,u.username,u.email,u.expire_plan_date,p.name as plan FROM svt_users as u LEFT JOIN svt_plans as p ON p.id=u.id_plan WHERE DATE_FORMAT(u.expire_plan_date, '%Y-%m-%d %H:%i')='$now';";
    $result = $mysqli->query($query);
    if($result) {
        if($result->num_rows>0) {
            while($row=$result->fetch_array(MYSQLI_ASSOC)) {
                $id_user = $row['id'];
                $username = $row['username'];
                $email_u = $row['email'];
                $plan = $row['plan'];
                $expire_plan_date = $row['expire_plan_date'];
                $subject = _("Expired plan");
                $body = _("Username").": $username<br>"._("E-Mail").": $email_u<br>"._("Plan").": $plan<br>"._("Expired").": $expire_plan_date";
                $subject_q = str_replace("'","\'",$subject);
                $body_q = str_replace("'","\'",$body);
                $mysqli->query("INSERT INTO svt_notifications(id_user,subject,body,notified) VALUES($id_user,'$subject_q','$body_q',0);");
            }
        }
    }
}

$smtp_server = $settings['smtp_server'];
$smtp_auth = $settings['smtp_auth'];
$smtp_username = $settings['smtp_username'];
$smtp_password = $settings['smtp_password'];
$smtp_secure = $settings['smtp_secure'];
$smtp_port = $settings['smtp_port'];
$smtp_from_email = $settings['smtp_from_email'];
$smtp_from_name = $settings['smtp_from_name'];

if(!empty($settings['notify_email'])) {
    $email = $settings['notify_email'];
} else {
    $email = $settings['smtp_from_email'];
}

$query = "SELECT * FROM svt_notifications WHERE notified=0;";
$result = $mysqli->query($query);
if($result) {
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_array(MYSQLI_ASSOC)) {
            $id = $row['id'];
            $subject = $row['subject'];
            $body = $row['body'];
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
                $mysqli->query("UPDATE svt_notifications SET notified=1 WHERE id=$id");
            } catch (Exception $e) {

            }
            sleep(5);
        }
    }
}