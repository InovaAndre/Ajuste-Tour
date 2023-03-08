<?php
header('Access-Control-Allow-Origin: *');
error_reporting(E_ALL & ~E_WARNING & ~E_NOTICE & ~E_DEPRECATED);
session_start();
require_once("../db/connection.php");
$v = time();
if((isset($_GET['furl'])) || (isset($_GET['code']))) {
    if(isset($_GET['furl'])) {
        $furl = $_GET['furl'];
        $where = "v.friendly_url = '$furl'";
    }
    if(isset($_GET['code'])) {
        $code = $_GET['code'];
        $where = "v.code = '$code'";
    }
    $query = "SELECT v.html_landing,v.code,v.logo,v.name as name_virtualtour,v.background_image,v.description,u.expire_plan_date,v.start_date,v.end_date,v.start_url,v.end_url,u.id_subscription_stripe,u.status_subscription_stripe FROM svt_virtualtours AS v
                JOIN svt_users AS u ON u.id=v.id_user
                WHERE $where AND v.active=1 LIMIT 1;";
    $result = $mysqli->query($query);
    if($result) {
        if ($result->num_rows == 1) {
            $row = $result->fetch_array(MYSQLI_ASSOC);
            if(!empty($row['id_subscription_stripe'])) {
                if($row['status_subscription_stripe']==0) {
                    die("Expired link");
                }
            }
            if($row['expire_plan_date']!=null) {
                if (new DateTime() > new DateTime($row['expire_plan_date'])) {
                    die("Expired link");
                }
            }
            if((!empty($row['start_date'])) && ($row['start_date']!='0000-00-00')) {
                if (new DateTime() < new DateTime($row['start_date']." 00:00:00")) {
                    if(!empty($row['start_url'])) {
                        header("Location: ".$row['start_url']);
                        exit();
                    } else {
                        die("Expired link");
                    }
                }
            }
            if((!empty($row['end_date'])) && ($row['end_date']!='0000-00-00')) {
                if (new DateTime() > new DateTime($row['end_date']." 23:59:59")) {
                    if(!empty($row['end_url'])) {
                        header("Location: ".$row['end_url']);
                        exit();
                    } else {
                        die("Expired link");
                    }
                }
            }
            $code = $row['code'];
            $name_virtualtour = strtoupper($row['name_virtualtour']);
            $background_image = $row['background_image'];
            $logo = $row['logo'];
            $description = $row['description'];
            $html_landing = $row['html_landing'];
        } else {
            die("Invalid link");
        }
    } else {
        die("Invalid link");
    }
} else {
    die("Invalid link");
}
$currentPath = $_SERVER['PHP_SELF'];
$pathInfo = pathinfo($currentPath);
$hostName = $_SERVER['HTTP_HOST'];
if (is_ssl()) { $protocol = 'https'; } else { $protocol = 'http'; }
$url = $protocol."://".$hostName.$pathInfo['dirname']."/";
$url = str_replace("/landing/","/viewer/",$url);

$iframe_html = "<iframe allowfullscreen allow=\"gyroscope; accelerometer; xr; microphone *\" width=\"100%\" height=\"100%\" frameborder=\"0\" scrolling=\"no\" marginheight=\"0\" marginwidth=\"0\" src=\"".$url."index.php?code=$code\"></iframe>";
$html_landing = str_replace("<img style=\"width: 100%;\" src=\"snippets/preview/vt_preview.jpg\">",$iframe_html,$html_landing);
?>
<!DOCTYPE HTML>
<html>
<head>
    <title><?php echo $name_virtualtour; ?></title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, user-scalable=no, maximum-scale=1, minimum-scale=1">
    <meta property="og:title" content="<?php echo $name_virtualtour; ?>">
    <?php if($background_image!='') : ?>
    <meta property="og:image" content="<?php echo $url."content/".$background_image; ?>" />
    <?php endif; ?>
    <?php if($description!='') : ?>
    <meta name="description" content="<?php echo $description; ?>"/>
    <meta property="og:description" content="<?php echo $description; ?>" />
    <?php endif; ?>
    <?php echo print_favicons_vt($code,$logo); ?>
    <link rel="stylesheet" type="text/css" href="../backend/vendor/keditor/plugins/bootstrap-3.4.1/css/bootstrap.min.css" data-type="keditor-style" />
</head>
<body>
    <style>
        body {
            overflow-x: hidden;
        }
        .row {
            padding: 15px;
        }
    </style>
    <?php echo $html_landing; ?>
</body>
</html>

<?php
function is_ssl() {
    if ( isset( $_SERVER['HTTPS'] ) ) {
        if ( 'on' == strtolower( $_SERVER['HTTPS'] ) ) {
            return true;
        }
        if ( '1' == $_SERVER['HTTPS'] ) {
            return true;
        }
    } elseif ( isset( $_SERVER['SERVER_PORT'] ) && ( '443' == $_SERVER['SERVER_PORT'] ) ) {
        return true;
    }
    return false;
}
function print_favicons_vt($code,$logo) {
    $path = '';
    $version = time();
    $path_m = 'v_'.$code.'/';
    if (file_exists(dirname(__FILE__).'/../favicons/v_'.$code.'/favicon.ico')) {
        $path = $path_m;
        $version = preg_replace('/[^0-9]/', '', $logo);
    } else {
        if (file_exists(dirname(__FILE__).'/../favicons/custom/favicon.ico')) {
            $path = 'custom/';
        }
    }
    return '<link rel="apple-touch-icon" sizes="180x180" href="../favicons/'.$path.'apple-touch-icon.png?v='.$version.'">
    <link rel="icon" type="image/png" sizes="32x32" href="../favicons/'.$path.'favicon-32x32.png?v='.$version.'">
    <link rel="icon" type="image/png" sizes="16x16" href="../favicons/'.$path.'favicon-16x16.png?v='.$version.'">
    <link rel="manifest" href="../favicons/'.$path_m.'site.webmanifest?v='.$version.'">
    <link rel="mask-icon" href="../favicons/'.$path.'safari-pinned-tab.svg?v='.$version.'" color="#ffffff">
    <link rel="shortcut icon" href="../favicons/'.$path.'favicon.ico?v='.$version.'">
    <meta name="msapplication-TileColor" content="#ffffff">
    <meta name="msapplication-config" content="../favicons/'.$path.'browserconfig.xml?v='.$version.'">
    <meta name="theme-color" content="#ffffff">';
}
?>