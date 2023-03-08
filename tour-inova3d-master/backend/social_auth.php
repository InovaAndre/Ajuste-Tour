<?php
error_reporting(E_ALL & ~E_WARNING & ~E_NOTICE & ~E_DEPRECATED);
ob_start();
session_start();
include("vendor/hybridauth/autoload.php");
use Hybridauth\Hybridauth;
require_once("functions.php");

if(isset($_SESSION['provider'])) {
    $provider = $_SESSION['provider'];
    unset($_SESSION['provider']);
} else {
    $provider = $_GET['provider'];
    $_SESSION['provider'] = $provider;
}

if(isset($_SESSION['reg'])) {
    $reg = $_SESSION['reg'];
    unset($_SESSION['reg']);
} else {
    $reg = $_GET['reg'];
    $_SESSION['reg'] = $reg;
}

if (is_ssl()) { $protocol = 'https'; } else { $protocol = 'http'; }
$link_callback = $protocol ."://". $_SERVER['SERVER_NAME'].$_SERVER['SCRIPT_NAME'];

$settings = get_settings();
$config = [
    'callback' => $link_callback,
    'providers' => [
        'Twitter' => ['enabled' => $settings['social_twitter_enable'], 'keys' => ['key' => $settings['social_twitter_id'], 'secret' => $settings['social_twitter_secret']]],
        'Google' => ['enabled' => $settings['social_google_enable'], 'keys' => ['id' => $settings['social_google_id'], 'secret' => $settings['social_google_secret']]],
        'Facebook' => ['enabled' => $settings['social_facebook_enable'], 'keys' => ['id' => $settings['social_facebook_id'], 'secret' => $settings['social_facebook_secret']]]
    ]
];

$email = '';
$first_name = '';
$last_name = '';

try {
    $hybridauth = new Hybridauth($config);
    $adapter = $hybridauth->authenticate($provider);
    $isConnected = $adapter->isConnected();
    $userProfile = $adapter->getUserProfile();
    $first_name = $userProfile->firstName;
    $last_name = $userProfile->lastName;
    $email = $userProfile->email;
    $adapter->disconnect();
} catch(\Exception $e) {
    echo $e->getMessage();
}

if(!empty($email)) {
    $query = "SELECT * FROM svt_users WHERE email='$email' LIMIT 1;";
    $result = $mysqli->query($query);
    if($result) {
        if ($result->num_rows == 1) {
            $row = $result->fetch_array(MYSQLI_ASSOC);
            $_SESSION['id_user'] = $row['id'];
            if($row['active']) {
                ob_end_clean();
                header("Location:index.php");
                exit;
            }
        } else {
            if($reg==1) {
                if($last_name=="") {
                    $_SESSION['username_reg'] = strtolower(str_replace(" ","",$first_name));
                } else {
                    $_SESSION['username_reg'] = strtolower(str_replace(" ","",$first_name).".".str_replace(" ","",$last_name));
                }
                $_SESSION['email_reg'] = $email;
                $_SESSION['password_reg'] = randomPassword();
                ob_end_clean();
                header("Location:register.php");
                exit;
            } else {
                if($settings['enable_registration']) {
                    if($last_name=="") {
                        $_SESSION['username_log'] = strtolower(str_replace(" ","",$first_name));
                    } else {
                        $_SESSION['username_log'] = strtolower(str_replace(" ","",$first_name).".".str_replace(" ","",$last_name));
                    }
                    $_SESSION['email_log'] = $email;
                    $_SESSION['password_log'] = randomPassword();
                    $_SESSION['modal_register'] = 1;
                }
            }
        }
    }
}
ob_end_clean();
header("Location:login.php");
exit;

function randomPassword() {
    $alphabet = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890';
    $pass = array();
    $alphaLength = strlen($alphabet) - 1;
    for ($i = 0; $i < 8; $i++) {
        $n = rand(0, $alphaLength);
        $pass[] = $alphabet[$n];
    }
    return implode($pass);
}
