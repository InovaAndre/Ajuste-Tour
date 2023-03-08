<?php
error_reporting(E_ALL & ~E_WARNING & ~E_NOTICE & ~E_DEPRECATED);
ob_start();
session_start();
if((($_SERVER['SERVER_ADDR']=='5.9.29.89') && ($_SERVER['REMOTE_ADDR']!=$_SESSION['ip_developer']) && ($_SESSION['id_user']==1)) || ($_SESSION['svt_si']!=session_id())) {
    die();
}
require_once("../../db/connection.php");
require_once("../functions.php");
$logo_exist = "";
$small_logo_exist = "";
$query = "SELECT logo,small_logo FROM svt_settings LIMIT 1;";
$result = $mysqli->query($query);
if($result) {
    if($result->num_rows==1) {
        $row = $result->fetch_array(MYSQLI_ASSOC);
        $logo_exist = $row['logo'];
        $small_logo_exist = $row['small_logo'];
    }
}
$purchase_code = str_replace("'","\'",$_POST['purchase_code']);
$name = str_replace("'","\'",strip_tags($_POST['name']));
$theme_color = $_POST['theme_color'];
if(empty($theme_color)) $theme_color='#0b5394';
$font_backend = $_POST['font_backend'];
if(empty($font_backend)) $font_backend='Nunito';
$welcome_msg = str_replace("'","\'",$_POST['welcome_msg']);
if($welcome_msg=='<p><br></p>') $welcome_msg="";
$furl_blacklist = str_replace("'","\'",strip_tags($_POST['furl_blacklist']));
$furl_blacklist = strtolower($furl_blacklist);
$furl_blacklist = str_replace(" ","",$furl_blacklist);
$logo = $_POST['logo'];
$small_logo = $_POST['small_logo'];
$background = $_POST['background'];
$background_reg = $_POST['background_reg'];
$smtp_server = $_POST['smtp_server'];
$smtp_port = $_POST['smtp_port'];
if($smtp_port=='') $smtp_port=0;
$smtp_secure = $_POST['smtp_secure'];
$smtp_auth = $_POST['smtp_auth'];
$smtp_username = str_replace("'","\'",$_POST['smtp_username']);
$smtp_password = str_replace("'","\'",$_POST['smtp_password']);
$smtp_from_email = str_replace("'","\'",$_POST['smtp_from_email']);
$smtp_from_name = str_replace("'","\'",$_POST['smtp_from_name']);
$language = $_POST['language'];
$language_domain = $_POST['language_domain'];
$language_domain = str_replace("_lang","",$language_domain);
$languages_enabled = $_POST['languages_enabled'];
$css_array = json_decode($_POST['css_array'],true);
$js_array = json_decode($_POST['js_array'],true);
$contact_mail = str_replace("'","\'",$_POST['contact_mail']);
$help_url = str_replace("'","\'",$_POST['help_url']);
$enable_external_vt = $_POST['enable_external_vt'];
$enable_wizard = $_POST['enable_wizard'];
$enable_sample = $_POST['enable_sample'];
$id_vt_sample = $_POST['id_vt_sample'];
if($id_vt_sample==0) $id_vt_sample='NULL';
$id_vt_template = $_POST['id_vt_template'];
if($id_vt_template==0) $id_vt_template='NULL';
$social_google_enable = $_POST['social_google_enable'];
$social_facebook_enable = $_POST['social_facebook_enable'];
$social_twitter_enable = $_POST['social_twitter_enable'];
$social_google_id = str_replace("'","\'",$_POST['social_google_id']);
$social_google_secret = str_replace("'","\'",$_POST['social_google_secret']);
$social_facebook_id = str_replace("'","\'",$_POST['social_facebook_id']);
$social_facebook_secret = str_replace("'","\'",$_POST['social_facebook_secret']);
$social_twitter_id = str_replace("'","\'",$_POST['social_twitter_id']);
$social_twitter_secret = str_replace("'","\'",$_POST['social_twitter_secret']);
$enable_registration = $_POST['enable_registration'];
$default_id_plan = $_POST['default_id_plan'];
$change_plan = $_POST['change_plan'];
$validate_email = $_POST['validate_email'];
$stripe_enabled = $_POST['stripe_enabled'];
$stripe_secret_key = $_POST['stripe_secret_key'];
$stripe_public_key = $_POST['stripe_public_key'];
$paypal_enabled = $_POST['paypal_enabled'];
$paypal_live = $_POST['paypal_live'];
$paypal_client_id = $_POST['paypal_client_id'];
$paypal_client_secret = $_POST['paypal_client_secret'];
$mail_activate_subject = str_replace("'","\'",$_POST['mail_activate_subject']);
$mail_activate_body = str_replace("'","\'",$_POST['mail_activate_body']);
$mail_forgot_subject = str_replace("'","\'",$_POST['mail_forgot_subject']);
$mail_forgot_body = str_replace("'","\'",$_POST['mail_forgot_body']);
$first_name_enable = $_POST['first_name_enable'];
$last_name_enable = $_POST['last_name_enable'];
$company_enable = $_POST['company_enable'];
$tax_id_enable = $_POST['tax_id_enable'];
$street_enable = $_POST['street_enable'];
$city_enable = $_POST['city_enable'];
$province_enable = $_POST['province_enable'];
$postal_code_enable = $_POST['postal_code_enable'];
$country_enable = $_POST['country_enable'];
$tel_enable = $_POST['tel_enable'];
$first_name_mandatory = $_POST['first_name_mandatory'];
$last_name_mandatory = $_POST['last_name_mandatory'];
$company_mandatory = $_POST['company_mandatory'];
$tax_id_mandatory = $_POST['tax_id_mandatory'];
$street_mandatory = $_POST['street_mandatory'];
$city_mandatory = $_POST['city_mandatory'];
$province_mandatory = $_POST['province_mandatory'];
$postal_code_mandatory = $_POST['postal_code_mandatory'];
$country_mandatory = $_POST['country_mandatory'];
$tel_mandatory = $_POST['tel_mandatory'];
$peerjs_host = $_POST['peerjs_host'];
$peerjs_port = $_POST['peerjs_port'];
$peerjs_path = $_POST['peerjs_path'];
$turn_host = $_POST['turn_host'];
$turn_port = $_POST['turn_port'];
$turn_username = $_POST['turn_username'];
$turn_password = $_POST['turn_password'];
$jitsi_domain = $_POST['jitsi_domain'];
$leaflet_street_basemap = $_POST['url_street'];
$leaflet_satellite_basemap = $_POST['url_sat'];
$leaflet_street_subdomain = $_POST['sub_street'];
$leaflet_satellite_subdomain = $_POST['sub_sat'];
$leaflet_street_maxzoom = $_POST['zoom_street'];
$leaflet_satellite_maxzoom = $_POST['zoom_sat'];
if(empty($peerjs_host)) $peerjs_host='svtpeerjs.simpledemo.it';
if(empty($peerjs_port)) $peerjs_port='443';
if(empty($peerjs_path)) $peerjs_path='/svt';
if(empty($jitsi_domain)) $jitsi_domain='meet.jit.si';
if(empty($leaflet_street_basemap)) $leaflet_street_basemap='https://{s}.google.com/vt/lyrs=m&x={x}&y={y}&z={z}';
if(empty($leaflet_satellite_basemap)) $leaflet_satellite_basemap='https://{s}.google.com/vt/lyrs=s&x={x}&y={y}&z={z}';
if(empty($leaflet_street_maxzoom)) $leaflet_street_maxzoom='20';
if(empty($leaflet_satellite_maxzoom)) $leaflet_satellite_maxzoom='20';
$query_add = "";
if($stripe_public_key!="keep_stripe_public_key") {
    $query_add .= ",stripe_public_key='$stripe_public_key'";
}
if($stripe_secret_key!="keep_stripe_secret_key") {
    $query_add .= ",stripe_secret_key='$stripe_secret_key'";
}
if($paypal_client_id!="keep_paypal_client_id") {
    $query_add .= ",paypal_client_id='$paypal_client_id'";
}
if($paypal_client_secret!="keep_paypal_client_secret") {
    $query_add .= ",paypal_client_secret='$paypal_client_secret'";
}
if($smtp_password!='keep_password') {
    $query_add .= ",smtp_password='$smtp_password'";
}
if($social_google_id!='keep_password') {
    $query_add .= ",social_google_id='$social_google_id'";
}
if($social_google_secret!='keep_password') {
    $query_add .= ",social_google_secret='$social_google_secret'";
}
if($social_facebook_id!='keep_password') {
    $query_add .= ",social_facebook_id='$social_facebook_id'";
}
if($social_facebook_secret!='keep_password') {
    $query_add .= ",social_facebook_secret='$social_facebook_secret'";
}
if($social_twitter_id!='keep_password') {
    $query_add .= ",social_twitter_id='$social_twitter_id'";
}
if($social_twitter_secret!='keep_password') {
    $query_add .= ",social_twitter_secret='$social_twitter_secret'";
}
$footer_link_1 = str_replace("'","\'",$_POST['footer_link_1']);
$footer_link_2 = str_replace("'","\'",$_POST['footer_link_2']);
$footer_link_3 = str_replace("'","\'",$_POST['footer_link_3']);
$footer_value_1 = str_replace("'","\'",$_POST['footer_value_1']);
$footer_value_2 = str_replace("'","\'",$_POST['footer_value_2']);
$footer_value_3 = str_replace("'","\'",$_POST['footer_value_3']);
if(strpos(get_string_between($footer_value_1, '<p>', '</p>'), 'http') === 0) {
    $footer_value_1 = str_replace(['<p>','</p>'],'',$footer_value_1);
}
if(strpos(get_string_between($footer_value_2, '<p>', '</p>'), 'http') === 0) {
    $footer_value_2 = str_replace(['<p>','</p>'],'',$footer_value_2);
}
if(strpos(get_string_between($footer_value_3, '<p>', '</p>'), 'http') === 0) {
    $footer_value_3 = str_replace(['<p>','</p>'],'',$footer_value_3);
}
$multires = $_POST['multires'];
$multires_cloud_url = str_replace("'","\'",$_POST['multires_cloud_url']);
$enable_screencast = $_POST['enable_screencast'];
$url_screencast = $_POST['url_screencast'];
$notify_email = str_replace("'","\'",$_POST['notify_email']);
$notify_registrations = $_POST['notify_registrations'];
$notify_plan_expires = $_POST['notify_plan_expires'];
$notify_plan_changes = $_POST['notify_plan_changes'];
$notify_plan_cancels = $_POST['notify_plan_cancels'];
$notify_vt_create = $_POST['notify_vt_create'];

$query = "UPDATE svt_settings SET purchase_code='$purchase_code',name='$name',theme_color='$theme_color',font_backend='$font_backend',welcome_msg='$welcome_msg',logo='$logo',small_logo='$small_logo',background='$background',background_reg='$background_reg',smtp_server='$smtp_server',smtp_port=$smtp_port,smtp_secure='$smtp_secure',smtp_auth=$smtp_auth,smtp_username='$smtp_username',smtp_from_email='$smtp_from_email',smtp_from_name='$smtp_from_name',furl_blacklist='$furl_blacklist',language='$language',language_domain='$language_domain',languages_enabled='$languages_enabled',contact_email='$contact_mail',help_url='$help_url',enable_external_vt=$enable_external_vt,enable_wizard=$enable_wizard,social_google_enable=$social_google_enable,social_facebook_enable=$social_facebook_enable,social_twitter_enable=$social_twitter_enable,enable_registration=$enable_registration,default_id_plan=$default_id_plan,change_plan=$change_plan,validate_email=$validate_email,stripe_enabled=$stripe_enabled,paypal_enabled=$paypal_enabled,paypal_live=$paypal_live,mail_activate_subject='$mail_activate_subject',mail_activate_body='$mail_activate_body',mail_forgot_subject='$mail_forgot_subject',mail_forgot_body='$mail_forgot_body',first_name_enable=$first_name_enable,last_name_enable=$last_name_enable,company_enable=$company_enable,tax_id_enable=$tax_id_enable,street_enable=$street_enable,city_enable=$city_enable,province_enable=$province_enable,postal_code_enable=$postal_code_enable,country_enable=$country_enable,tel_enable=$tel_enable,first_name_mandatory=$first_name_mandatory,last_name_mandatory=$last_name_mandatory,company_mandatory=$company_mandatory,tax_id_mandatory=$tax_id_mandatory,street_mandatory=$street_mandatory,city_mandatory=$city_mandatory,province_mandatory=$province_mandatory,postal_code_mandatory=$postal_code_mandatory,country_mandatory=$country_mandatory,tel_mandatory=$tel_mandatory,peerjs_host='$peerjs_host',peerjs_port='$peerjs_port',peerjs_path='$peerjs_path',turn_host='$turn_host',turn_port='$turn_port',turn_username='$turn_username',turn_password='$turn_password',jitsi_domain='$jitsi_domain',leaflet_street_basemap='$leaflet_street_basemap',leaflet_satellite_basemap='$leaflet_satellite_basemap',leaflet_street_subdomain='$leaflet_street_subdomain',leaflet_street_maxzoom='$leaflet_street_maxzoom',leaflet_satellite_subdomain='$leaflet_satellite_subdomain',leaflet_satellite_maxzoom='$leaflet_satellite_maxzoom',enable_sample=$enable_sample,id_vt_sample=$id_vt_sample,id_vt_template=$id_vt_template,footer_link_1='$footer_link_1',footer_link_2='$footer_link_2',footer_link_3='$footer_link_3',footer_value_1='$footer_value_1',footer_value_2='$footer_value_2',footer_value_3='$footer_value_3',multires='$multires',multires_cloud_url='$multires_cloud_url',enable_screencast=$enable_screencast,url_screencast='$url_screencast', notify_email='$notify_email',notify_registrations=$notify_registrations,notify_plan_expires=$notify_plan_expires,notify_plan_changes=$notify_plan_changes,notify_plan_cancels=$notify_plan_cancels,notify_vt_create=$notify_vt_create $query_add;";
$result = $mysqli->query($query);

foreach ($css_array as $name=>$content) {
    if($name=='custom_b') {
        $url_css = __DIR__.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'backend'.DIRECTORY_SEPARATOR.'css'.DIRECTORY_SEPARATOR.'custom_b.css';
    } else {
        $url_css = __DIR__.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'viewer'.DIRECTORY_SEPARATOR.'css'.DIRECTORY_SEPARATOR.$name.'.css';
    }
    if(file_exists($url_css) && $content=='') {
        @unlink($url_css);
    } else {
        if($content!='') {
            @file_put_contents($url_css,$content);
        }
    }
}

foreach ($js_array as $name=>$content) {
    $url_js = __DIR__.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'viewer'.DIRECTORY_SEPARATOR.'js'.DIRECTORY_SEPARATOR.$name.'.js';
    if(file_exists($url_js) && $content=='') {
        @unlink($url_js);
    } else {
        if($content!='') {
            @file_put_contents($url_js,$content);
        }
    }
}

$query_vc = "UPDATE svt_voice_commands SET ";
foreach($_POST['voice_commands'] as $key=>$value){
    $value = str_replace("'","\'",strip_tags($value));
    $query_vc .= $key." = '".$value."', ";
}
$query_vc = rtrim($query_vc,", ").";";
$mysqli->query($query_vc);

if($result) {
    $path = __DIR__.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR;
    if(empty($logo)) {
        if(file_exists($path . "favicons" . DIRECTORY_SEPARATOR . "custom")) {
            array_map('unlink', glob($path . "favicons" . DIRECTORY_SEPARATOR . "custom" . DIRECTORY_SEPARATOR ."*.*"));
            rmdir($path . "favicons" . DIRECTORY_SEPARATOR . "custom" . DIRECTORY_SEPARATOR);
        }
    } else {
        if($logo!=$logo_exist) {
            if(file_exists($path . "favicons" . DIRECTORY_SEPARATOR . "custom")) {
                array_map('unlink', glob($path . "favicons" . DIRECTORY_SEPARATOR . "custom" . DIRECTORY_SEPARATOR ."*.*"));
                rmdir($path . "favicons" . DIRECTORY_SEPARATOR . "custom" . DIRECTORY_SEPARATOR);
            }
        }
    }
    if(!empty($logo) || !empty($small_logo)) {
        if (($logo != $logo_exist) || ($small_logo != $small_logo_exist)) {
            generate_favicons();
        }
    }
    ob_end_clean();
    echo json_encode(array("status"=>"ok"));
} else {
    ob_end_clean();
    echo json_encode(array("status"=>"error $query"));
}

