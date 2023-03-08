<?php
error_reporting(E_ALL & ~E_WARNING & ~E_NOTICE & ~E_DEPRECATED);
ob_start();
session_start();
if(($_SERVER['SERVER_ADDR']=='5.9.29.89') && ($_SERVER['REMOTE_ADDR']!=$_SESSION['ip_developer'])) {
    //DEMO CHECK
    die();
}
ini_set("memory_limit",-1);
ini_set('max_execution_time', 9999);
ini_set('max_input_time', 9999);
require_once(__DIR__."/../db/connection.php");
require_once(__DIR__."/../backend/functions.php");
require_once(__DIR__."/Minifier.php");
use \JShrink\Minifier;

$plan_permissions = get_plan_permission($_SESSION['id_user']);
if($plan_permissions['enable_export_vt']==0) {
    ob_end_clean();
    echo json_encode(array("status"=>"error"));
    exit;
}

$settings = get_settings();
$user_info = get_user_info($_SESSION['id_user']);
if(!empty($user_info['language'])) {
    set_language($user_info['language'],$settings['language_domain']);
} else {
    set_language($settings['language'],$settings['language_domain']);
}

if (!class_exists('ZipArchive')) {
    ob_end_clean();
    echo json_encode(array("status"=>"error","msg"=>_("php zip not enabled")));
    exit;
}

$id_vt = $_POST['id_virtualtour'];
$code = '';
$query = "SELECT name,description,author,code,song,logo,nadir_logo,background_image,background_video,intro_desktop,intro_mobile,presentation_video FROM svt_virtualtours WHERE id=$id_vt LIMIT 1;";
$result = $mysqli->query($query);
if($result) {
    if($result->num_rows==1) {
        $row = $result->fetch_array(MYSQLI_ASSOC);
        $code = $row['code'];
        $song = $row['song'];
        $logo = $row['logo'];
        $nadir_logo = $row['nadir_logo'];
        $background_image = $row['background_image'];
        $background_video = $row['background_video'];
        $intro_desktop = $row['intro_desktop'];
        $intro_mobile = $row['intro_mobile'];
        $presentation_video = $row['presentation_video'];
        if($presentation_video!='') $presentation_video = basename($presentation_video);
        $name = $row['name'];
        $description = $row['description'];
        $author = $row['author'];
    }
}
if(empty($code)) {
    ob_end_clean();
    echo json_encode(array("status"=>"error"));
    exit;
}
$mysqli->close();
$mysqli = new mysqli(DATABASE_HOST, DATABASE_USERNAME, DATABASE_PASSWORD, DATABASE_NAME);
if (mysqli_connect_errno()) {
    echo mysqli_connect_error();
    exit();
}
$mysqli->query("SET NAMES 'utf8';");

if(file_exists(dirname(__FILE__)."/export_tmp/$code/")) {
    deleteDirectory(dirname(__FILE__)."/export_tmp/$code/");
}

check_directory("/export_tmp/");
check_directory("/export_tmp/$code/");
check_directory("/export_tmp/$code/favicons/");
check_directory("/export_tmp/$code/favicons/v_$code");
check_directory("/export_tmp/$code/css/");
check_directory("/export_tmp/$code/css/images/");
check_directory("/export_tmp/$code/css/font/");
check_directory("/export_tmp/$code/js/");
check_directory("/export_tmp/$code/webfonts/");
check_directory("/export_tmp/$code/panoramas/");
check_directory("/export_tmp/$code/panoramas/lowres/");
check_directory("/export_tmp/$code/panoramas/mobile/");
check_directory("/export_tmp/$code/panoramas/multires/");
check_directory("/export_tmp/$code/panoramas/original/");
check_directory("/export_tmp/$code/panoramas/preview/");
check_directory("/export_tmp/$code/panoramas/thumb/");
check_directory("/export_tmp/$code/panoramas/thumb_custom/");
check_directory("/export_tmp/$code/videos/");
check_directory("/export_tmp/$code/content/");
check_directory("/export_tmp/$code/gallery/");
check_directory("/export_tmp/$code/gallery/thumb/");
check_directory("/export_tmp/$code/icons/");
check_directory("/export_tmp/$code/maps/");
check_directory("/export_tmp/$code/maps/thumb/");
check_directory("/export_tmp/$code/media/");
check_directory("/export_tmp/$code/media/thumb/");
check_directory("/export_tmp/$code/objects360/");
check_directory("/export_tmp/$code/products/");
check_directory("/export_tmp/$code/products/thumb/");

$readme = <<<STR
Due to browser security restrictions, a web server must be used locally as well.
To see the tour you can use one of these 3 methods:
1) upload the files to a web server and access the corresponding url (http: //server.domain/folder/index.html)
2) use a local web server (like xampp, mamp, python http server, ...) and access the corresponding url (http://127.0.0.1:port/index.html)
3) the files are ready to be packaged as desktop application (mac, linux, windows) with electron (https://www.electronjs.org/)
STR;
file_put_contents(dirname(__FILE__)."/export_tmp/$code/readme.txt",$readme);

$electron_js = <<<STR
const {app, BrowserWindow} = require('electron')
const url = require('url')
const path = require('path')
let win
function createWindow() {
   win = new BrowserWindow({width: 1280, height: 720})
   win.loadURL(url.format ({
      pathname: path.join(__dirname, 'index.html'),
      protocol: 'file:',
      slashes: true
   }))
}
app.on('ready', createWindow)
STR;
file_put_contents(dirname(__FILE__)."/export_tmp/$code/main.js",$electron_js);

$eletron_package = <<<STR
{
  "name": "$name",
  "version": "1.0.0",
  "description": "$description",
  "main": "main.js",
  "author": "$author"
}
STR;
file_put_contents(dirname(__FILE__)."/export_tmp/$code/package.json",$eletron_package);

copy(dirname(__FILE__)."/../favicons/android-chrome-192x192.png",dirname(__FILE__)."/export_tmp/$code/favicons/android-chrome-192x192.png");
copy(dirname(__FILE__)."/../favicons/android-chrome-256x256.png",dirname(__FILE__)."/export_tmp/$code/favicons/android-chrome-256x256.png");
copy(dirname(__FILE__)."/../favicons/apple-touch-icon.png",dirname(__FILE__)."/export_tmp/$code/favicons/apple-touch-icon.png");
copy(dirname(__FILE__)."/../favicons/browserconfig.xml",dirname(__FILE__)."/export_tmp/$code/favicons/browserconfig.xml");
copy(dirname(__FILE__)."/../favicons/favicon.ico",dirname(__FILE__)."/export_tmp/$code/favicons/favicon.ico");
copy(dirname(__FILE__)."/../favicons/favicon-16x16.png",dirname(__FILE__)."/export_tmp/$code/favicons/favicon-16x16.png");
copy(dirname(__FILE__)."/../favicons/favicon-32x32.png",dirname(__FILE__)."/export_tmp/$code/favicons/favicon-32x32.png");
copy(dirname(__FILE__)."/../favicons/mstile-150x150.png",dirname(__FILE__)."/export_tmp/$code/favicons/mstile-150x150.png");
copy(dirname(__FILE__)."/../favicons/safari-pinned-tab.svg",dirname(__FILE__)."/export_tmp/$code/favicons/safari-pinned-tab.svg");
recursive_copy(dirname(__FILE__)."/../favicons/v_$code",dirname(__FILE__)."/export_tmp/$code/favicons/v_$code");

$array_js_files = ['js/jquery-ui.min.js','js/libpannellum.js','js/pannellum.js','vendor/videojs/video.min.js','js/videojs-pannellum-plugin.js','vendor/videojs/youtube.min.js','vendor/fancybox/jquery.fancybox.min.js','js/sly.min.js','js/jquery.floating-social-share.js','vendor/tooltipster/js/tooltipster.bundle.min.js','js/mobile-detect.min.js','js/typed.min.js','vendor/nanogallery2/jquery.nanogallery2.core.min.js','vendor/SpeechKITT/annyang.js','vendor/SpeechKITT/speechkitt.min.js','vendor/jquery-confirm/jquery-confirm.min.js','js/peerjs.min.js','vendor/clipboard.js/clipboard.min.js','js/pixi.min.js','js/jquery.ui.touch-punch.min.js','vendor/leaflet/leaflet.js','vendor/leaflet/L.Control.Locate.min.js','js/numeric.min.js','vendor/simplebar/simplebar.min.js','vendor/glide/glide.min.js','js/effects.js','js/360-view.min.js','js/lottie.min.js','js/progress.min.js'];
$js = '';
foreach($array_js_files as $js_file) {
    $js .= file_get_contents(dirname(__FILE__)."/../viewer/$js_file");
    $js .= "\r\n\r\n";
}
file_put_contents(dirname(__FILE__)."/export_tmp/$code/js/script.js",$js);

copy_file('jquery-3.4.1.min.js','js');
copy_file('bootstrap.min.js','js');
copy_file('jquery.touchSwipe.min.js','js');
copy(dirname(__FILE__)."/../viewer/vendor/threejs/three.min.js",dirname(__FILE__)."/export_tmp/$code/js/three.min.js");
copy(dirname(__FILE__)."/../viewer/vendor/threejs/Tween.js",dirname(__FILE__)."/export_tmp/$code/js/Tween.js");
copy(dirname(__FILE__)."/../viewer/vendor/threejs/OrbitControls.js",dirname(__FILE__)."/export_tmp/$code/js/OrbitControls.js");
copy(dirname(__FILE__)."/../viewer/vendor/threejs/CSS2DRenderer.js",dirname(__FILE__)."/export_tmp/$code/js/CSS2DRenderer.js");
copy(dirname(__FILE__)."/../viewer/vendor/threejs/threex.domevents.js",dirname(__FILE__)."/export_tmp/$code/js/threex.domevents.js");
copy(dirname(__FILE__)."/../viewer/vendor/sweet-dropdown/jquery.sweet-dropdown.min.js",dirname(__FILE__)."/export_tmp/$code/js/jquery.sweet-dropdown.min.js");
copy(dirname(__FILE__)."/../viewer/vendor/videojs/videojs-vr.min.js",dirname(__FILE__)."/export_tmp/$code/js/videojs-vr.min.js");
copy_file('index.js','js');
copy_file('model-viewer.min.js','js');
copy_file('hls.min.js','js');

$js = Minifier::minify(file_get_contents(dirname(__FILE__)."/export_tmp/$code/js/index.js"),array('flaggedComments' => false));
file_put_contents(dirname(__FILE__)."/export_tmp/$code/js/index.js",$js);
if(file_exists(dirname(__FILE__)."/../viewer/js/custom.js")) {
    copy_file('custom.js','js');
    $js = Minifier::minify(file_get_contents(dirname(__FILE__)."/export_tmp/$code/js/custom.js"),array('flaggedComments' => false));
    file_put_contents(dirname(__FILE__)."/export_tmp/$code/js/custom.js",$js);
}
if(file_exists(dirname(__FILE__)."/../viewer/js/custom_$code.js")) {
    copy_file("custom_$code.js",'js');
    $js = Minifier::minify(file_get_contents(dirname(__FILE__)."/export_tmp/$code/js/custom_$code.js"),array('flaggedComments' => false));
    file_put_contents(dirname(__FILE__)."/export_tmp/$code/js/custom_$code.js",$js);
}

copy_file('icomoon.eot','css');
copy_file('icomoon.svg','css');
copy_file('icomoon.ttf','css');
copy_file('icomoon.woff','css');
copy_file('compass.eot','css');
copy_file('compass.svg','css');
copy_file('compass.ttf','css');
copy_file('compass.woff','css');
copy_file('Smoke10.png','css');
copy_file('transparent.png','css');
copy(dirname(__FILE__)."/../viewer/vendor/leaflet/images/layers.png",dirname(__FILE__)."/export_tmp/$code/css/images/layers.png");
copy(dirname(__FILE__)."/../viewer/vendor/SpeechKITT/themes/flat.css",dirname(__FILE__)."/export_tmp/$code/css/skflat.css");
file_put_contents(dirname(__FILE__)."/export_tmp/$code/css/skflat.css",minify_css(file_get_contents(dirname(__FILE__)."/export_tmp/$code/css/skflat.css")));

$array_css_files = ['css/jquery-ui.min.css','vendor/fontawesome-free/css/all.min.css','css/pannellum.css','vendor/fancybox/jquery.fancybox.min.css','css/jquery.floating-social-share.css','css/progress.css','vendor/tooltipster/css/tooltipster.bundle.min.css','vendor/tooltipster/css/plugins/tooltipster/sideTip/themes/tooltipster-sideTip-borderless.min.css','vendor/nanogallery2/css/nanogallery2.min.css','vendor/videojs/video-js.min.css','vendor/jquery-confirm/jquery-confirm.min.css','css/bootstrap-iso.css','vendor/leaflet/leaflet.css','vendor/leaflet/L.Control.Locate.min.css','vendor/simplebar/simplebar.css','vendor/glide/glide.core.min.css','vendor/glide/glide.theme.min.css','css/effects.css','css/index.css','css/animate.min.css','vendor/sweet-dropdown/jquery.sweet-dropdown.min.css'];
$css = '';
foreach($array_css_files as $css_file) {
    $css .= minify_css(file_get_contents(dirname(__FILE__)."/../viewer/$css_file"));
    $css .= "\r\n\r\n";
}
file_put_contents(dirname(__FILE__)."/export_tmp/$code/css/style.css",$css);

if(file_exists(dirname(__FILE__)."/../viewer/css/custom.css")) {
    copy_file('custom.css','css');
    $css = minify_css(file_get_contents(dirname(__FILE__)."/export_tmp/$code/css/custom.css"));
    file_put_contents(dirname(__FILE__)."/export_tmp/$code/css/custom.css",$css);
}
if(file_exists(dirname(__FILE__)."/../viewer/css/custom_$code.css")) {
    copy_file("custom_$code.css",'css');
    $css = minify_css(file_get_contents(dirname(__FILE__)."/export_tmp/$code/css/custom_$code.css"));
    file_put_contents(dirname(__FILE__)."/export_tmp/$code/css/custom_$code.css",$css);
}

recursive_copy(dirname(__FILE__)."/../viewer/vendor/nanogallery2/css/font",dirname(__FILE__)."/export_tmp/$code/css/font");
recursive_copy(dirname(__FILE__)."/../viewer/vendor/fontawesome-free/webfonts",dirname(__FILE__)."/export_tmp/$code/webfonts");

copy_file($song,'content');
copy_file($logo,'content');
copy_file($nadir_logo,'content');
copy_file($background_image,'content');
copy_file($background_video,'content');
copy_file($intro_desktop,'content');
copy_file($intro_mobile,'content');
copy_file($presentation_video,'content');

$query = "SELECT a.image,a.video FROM svt_advertisements as a JOIN svt_assign_advertisements saa on a.id = saa.id_advertisement WHERE saa.id_virtualtour=$id_vt;";
$result = $mysqli->query($query);
if($result) {
    if ($result->num_rows > 0) {
        while($row = $result->fetch_array(MYSQLI_ASSOC)) {
            $image = $row['image'];
            $video = $row['video'];
            copy_file($image,'content');
            copy_file($video,'content');
        }
    }
}
$mysqli->close();
$mysqli = new mysqli(DATABASE_HOST, DATABASE_USERNAME, DATABASE_PASSWORD, DATABASE_NAME);
if (mysqli_connect_errno()) {
    echo mysqli_connect_error();
    exit();
}
$mysqli->query("SET NAMES 'utf8';");
$query = "SELECT image FROM svt_gallery WHERE id_virtualtour=$id_vt;";
$result = $mysqli->query($query);
if($result) {
    if ($result->num_rows > 0) {
        while($row = $result->fetch_array(MYSQLI_ASSOC)) {
            $image = $row['image'];
            copy_file($image,'gallery');
            copy_file($image,'gallery/thumb');
        }
    }
}
$mysqli->close();
$mysqli = new mysqli(DATABASE_HOST, DATABASE_USERNAME, DATABASE_PASSWORD, DATABASE_NAME);
if (mysqli_connect_errno()) {
    echo mysqli_connect_error();
    exit();
}
$mysqli->query("SET NAMES 'utf8';");
$query = "SELECT map FROM svt_maps WHERE id_virtualtour=$id_vt;";
$result = $mysqli->query($query);
if($result) {
    if ($result->num_rows > 0) {
        while($row = $result->fetch_array(MYSQLI_ASSOC)) {
            $map = $row['map'];
            copy_file($map,'maps');
            copy_file($map,'maps/thumb');
        }
    }
}
$mysqli->close();
$mysqli = new mysqli(DATABASE_HOST, DATABASE_USERNAME, DATABASE_PASSWORD, DATABASE_NAME);
if (mysqli_connect_errno()) {
    echo mysqli_connect_error();
    exit();
}
$mysqli->query("SET NAMES 'utf8';");
$array_id_rooms = array();
$query = "SELECT id,type,panorama_image,panorama_video,thumb_image,logo FROM svt_rooms WHERE id_virtualtour=$id_vt;";
$result = $mysqli->query($query);
if($result) {
    if ($result->num_rows > 0) {
        while($row = $result->fetch_array(MYSQLI_ASSOC)) {
            $id_room = $row['id'];
            array_push($array_id_rooms,$id_room);
            $panorama_image = $row['panorama_image'];
            $panorama_name = explode(".",$panorama_image)[0];
            $panorama_video = $row['panorama_video'];
            $thumb_image = $row['thumb_image'];
            $logo = $row['logo'];
            copy_file($panorama_image,'panoramas');
            copy_file($panorama_image,'panoramas/lowres');
            copy_file($panorama_image,'panoramas/mobile');
            copy_file($panorama_image,'panoramas/original');
            copy_file($panorama_image,'panoramas/preview');
            copy_file($panorama_image,'panoramas/thumb');
            copy_file($panorama_video,'videos');
            copy_file($thumb_image,'panoramas/thumb_custom');
            copy_file($logo,'content');
            if(file_exists(dirname(__FILE__)."/../viewer/panoramas/multires/$panorama_name/")) {
                recursive_copy(dirname(__FILE__)."/../viewer/panoramas/multires/$panorama_name",dirname(__FILE__)."/export_tmp/$code/panoramas/multires/$panorama_name");
            }
        }
    }
}
$mysqli->close();
$mysqli = new mysqli(DATABASE_HOST, DATABASE_USERNAME, DATABASE_PASSWORD, DATABASE_NAME);
if (mysqli_connect_errno()) {
    echo mysqli_connect_error();
    exit();
}
$mysqli->query("SET NAMES 'utf8';");
$array_id_pois = array();
$array_id_products = array();
$id_rooms = implode(",",$array_id_rooms);
if(!empty($id_rooms)) {
    $query = "SELECT panorama_image FROM svt_rooms_alt WHERE id_room IN ($id_rooms);";
    $result = $mysqli->query($query);
    if($result) {
        if ($result->num_rows > 0) {
            while($row = $result->fetch_array(MYSQLI_ASSOC)) {
                $panorama_image = $row['panorama_image'];
                $panorama_name = explode(".",$panorama_image)[0];
                copy_file($panorama_image,'panoramas');
                copy_file($panorama_image,'panoramas/lowres');
                copy_file($panorama_image,'panoramas/mobile');
                copy_file($panorama_image,'panoramas/original');
                copy_file($panorama_image,'panoramas/preview');
                copy_file($panorama_image,'panoramas/thumb');
                if(file_exists(dirname(__FILE__)."/../viewer/panoramas/multires/$panorama_name/")) {
                    recursive_copy(dirname(__FILE__)."/../viewer/panoramas/multires/$panorama_name",dirname(__FILE__)."/export_tmp/$code/panoramas/multires/$panorama_name");
                }
            }
        }
    }
    $mysqli->close();
    $mysqli = new mysqli(DATABASE_HOST, DATABASE_USERNAME, DATABASE_PASSWORD, DATABASE_NAME);
    if (mysqli_connect_errno()) {
        echo mysqli_connect_error();
        exit();
    }
    $mysqli->query("SET NAMES 'utf8';");
    $query = "SELECT id,type,content,embed_type,embed_content FROM svt_pois WHERE id_room IN ($id_rooms);";
    $result = $mysqli->query($query);
    if($result) {
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_array(MYSQLI_ASSOC)) {
                $id_poi = $row['id'];
                array_push($array_id_pois,$id_poi);
                $type = $row['type'];
                $content = $row['content'];
                $embed_type = $row['embed_type'];
                $embed_content = $row['embed_content'];
                if (strpos($content, 'content/') === 0) {
                    $content_file = basename($content);
                    copy_file($content_file,'content');
                }
                if (strpos($content, 'media/') === 0) {
                    $content_file = basename($content);
                    copy_file($content_file,'media');
                }
                if($type=='product') {
                    if(!empty($content)) array_push($array_id_products,$content);
                }
                switch($embed_type) {
                    case 'image':
                    case 'video':
                    case 'video_chroma':
                        if (strpos($embed_content, 'content') === 0) {
                            $content_file = basename($embed_content);
                            copy_file($content_file,'content');
                        }
                        if (strpos($embed_content, 'media') === 0) {
                            $content_file = basename($embed_content);
                            copy_file($content_file,'media');
                        }
                        break;
                    case 'video_transparent':
                        if (strpos($embed_content, ',') !== false) {
                            $array_contents = explode(",",$embed_content);
                            foreach ($array_contents as $content) {
                                if (strpos($content, 'content') === 0) {
                                    $content_file = basename($content);
                                    copy_file($content_file,'content');
                                }
                                if (strpos($content, 'media') === 0) {
                                    $content_file = basename($content);
                                    copy_file($content_file,'media');
                                }
                            }
                        } else {
                            if (strpos($embed_content, 'content') === 0) {
                                $content_file = basename($embed_content);
                                copy_file($content_file,'content');
                            }
                            if (strpos($embed_content, 'media') === 0) {
                                $content_file = basename($embed_content);
                                copy_file($content_file,'media');
                            }
                        }
                        break;
                }
            }
        }
    }
    $mysqli->close();
    $mysqli = new mysqli(DATABASE_HOST, DATABASE_USERNAME, DATABASE_PASSWORD, DATABASE_NAME);
    if (mysqli_connect_errno()) {
        echo mysqli_connect_error();
        exit();
    }
    $mysqli->query("SET NAMES 'utf8';");
    $query = "SELECT image FROM svt_icons WHERE id IN (SELECT id_icon_library FROM svt_markers WHERE id_icon_library!=0 AND id_room IN ($id_rooms));";
    $result = $mysqli->query($query);
    if($result) {
        if ($result->num_rows > 0) {
            while($row = $result->fetch_array(MYSQLI_ASSOC)) {
                $image = $row['image'];
                copy_file($image,'icons');
            }
        }
    }
    $mysqli->close();
    $mysqli = new mysqli(DATABASE_HOST, DATABASE_USERNAME, DATABASE_PASSWORD, DATABASE_NAME);
    if (mysqli_connect_errno()) {
        echo mysqli_connect_error();
        exit();
    }
    $mysqli->query("SET NAMES 'utf8';");

    $query = "SELECT image FROM svt_icons WHERE id IN (SELECT id_icon_library FROM svt_pois WHERE id_icon_library!=0 AND id_room IN ($id_rooms));";
    $result = $mysqli->query($query);
    if($result) {
        if ($result->num_rows > 0) {
            while($row = $result->fetch_array(MYSQLI_ASSOC)) {
                $image = $row['image'];
                copy_file($image,'icons');
            }
        }
    }
    $mysqli->close();
    $mysqli = new mysqli(DATABASE_HOST, DATABASE_USERNAME, DATABASE_PASSWORD, DATABASE_NAME);
    if (mysqli_connect_errno()) {
        echo mysqli_connect_error();
        exit();
    }
    $mysqli->query("SET NAMES 'utf8';");
}
$id_pois = implode(",",$array_id_pois);
if(!empty($id_pois)) {
    $query = "SELECT image FROM svt_poi_embedded_gallery WHERE id_poi IN ($id_pois);";
    $result = $mysqli->query($query);
    if($result) {
        if ($result->num_rows > 0) {
            while($row = $result->fetch_array(MYSQLI_ASSOC)) {
                $image = $row['image'];
                copy_file($image,'gallery');
                copy_file($image,'gallery/thumb');
            }
        }
    }
    $mysqli->close();
    $mysqli = new mysqli(DATABASE_HOST, DATABASE_USERNAME, DATABASE_PASSWORD, DATABASE_NAME);
    if (mysqli_connect_errno()) {
        echo mysqli_connect_error();
        exit();
    }
    $mysqli->query("SET NAMES 'utf8';");
    $query = "SELECT image FROM svt_poi_gallery WHERE id_poi IN ($id_pois);";
    $result = $mysqli->query($query);
    if($result) {
        if ($result->num_rows > 0) {
            while($row = $result->fetch_array(MYSQLI_ASSOC)) {
                $image = $row['image'];
                copy_file($image,'gallery');
                copy_file($image,'gallery/thumb');
            }
        }
    }
    $mysqli->close();
    $mysqli = new mysqli(DATABASE_HOST, DATABASE_USERNAME, DATABASE_PASSWORD, DATABASE_NAME);
    if (mysqli_connect_errno()) {
        echo mysqli_connect_error();
        exit();
    }
    $mysqli->query("SET NAMES 'utf8';");
    $query = "SELECT image FROM svt_poi_objects360 WHERE id_poi IN ($id_pois);";
    $result = $mysqli->query($query);
    if($result) {
        if ($result->num_rows > 0) {
            while($row = $result->fetch_array(MYSQLI_ASSOC)) {
                $image = $row['image'];
                copy_file($image,'objects360');
            }
        }
    }
    $mysqli->close();
    $mysqli = new mysqli(DATABASE_HOST, DATABASE_USERNAME, DATABASE_PASSWORD, DATABASE_NAME);
    if (mysqli_connect_errno()) {
        echo mysqli_connect_error();
        exit();
    }
    $mysqli->query("SET NAMES 'utf8';");
}
$id_products = implode(",",$array_id_products);
if(!empty($id_products)) {
    $query = "SELECT image FROM svt_product_images WHERE id_product IN ($id_products);";
    $result = $mysqli->query($query);
    if($result) {
        if ($result->num_rows > 0) {
            while($row = $result->fetch_array(MYSQLI_ASSOC)) {
                $image = $row['image'];
                copy_file($image,'products');
                copy_file($image,'products/thumb');
            }
        }
    }
}

$currentPath = $_SERVER['PHP_SELF'];
$pathInfo = pathinfo($currentPath);
$hostName = $_SERVER['HTTP_HOST'];
if (is_ssl()) { $protocol = 'https'; } else { $protocol = 'http'; }
$url = $protocol."://".$hostName.$pathInfo['dirname']."/";
$url = str_replace('services/','viewer/',$url);

$data = file_get_contents($url."index.php?code=$code&export=1");
file_put_contents(dirname(__FILE__)."/export_tmp/$code/index.html",$data);

$file_name_zip = str_replace(" ","_",$name).".zip";

RemoveEmptySubFolders(dirname(__FILE__)."/export_tmp/$code/");
zip_folder($code,$file_name_zip);
if(file_exists(dirname(__FILE__)."/export_tmp/$file_name_zip")) {
    deleteDirectory(dirname(__FILE__)."/export_tmp/$code/");
    ob_end_clean();
    echo json_encode(array("status"=>"ok","zip"=>"$file_name_zip"));
    exit;
} else {
    ob_end_clean();
    echo json_encode(array("status"=>"error"));
    exit;
}

function check_directory($path) {
    try {
        if (!file_exists(dirname(__FILE__).$path)) {
            mkdir(dirname(__FILE__).$path, 0775,true);
        }
    } catch (Exception $e) {}
}

function copy_file($file_name,$dir) {
    global $code;
    if(!empty($file_name)) {
        $source = dirname(__FILE__)."/../viewer/$dir/$file_name";
        if(file_exists($source)) {
            $dest = dirname(__FILE__)."/export_tmp/$code/$dir/$file_name";
            @copy($source,$dest);
        }
    }
}

function recursive_copy($src,$dst) {
    $dir = opendir($src);
    if($dir!=false) {
        @mkdir($dst,0775,true);
        while(( $file = readdir($dir)) ) {
            if (( $file != '.' ) && ( $file != '..' )) {
                if (is_dir($src . '/' . $file)) {
                    recursive_copy($src .'/'. $file, $dst .'/'. $file);
                } else {
                    @copy($src .'/'. $file,$dst .'/'. $file);
                }
            }
        }
        closedir($dir);
    }
}


function minify_css($input) {
    if(trim($input) === "") return $input;
    $input = preg_replace('!/\*.*?\*/!s', '', $input);
    $input = preg_replace('/\n\s*\n/', "\n", $input);
    return preg_replace(
        array(
            '#("(?:[^"\\\]++|\\\.)*+"|\'(?:[^\'\\\\]++|\\\.)*+\')|\/\*(?!\!)(?>.*?\*\/)|^\s*|\s*$#s',
            '#("(?:[^"\\\]++|\\\.)*+"|\'(?:[^\'\\\\]++|\\\.)*+\'|\/\*(?>.*?\*\/))|\s*+;\s*+(})\s*+|\s*+([*$~^|]?+=|[{};,>~]|\s(?![0-9\.])|!important\b)\s*+|([[(:])\s++|\s++([])])|\s++(:)\s*+(?!(?>[^{}"\']++|"(?:[^"\\\]++|\\\.)*+"|\'(?:[^\'\\\\]++|\\\.)*+\')*+{)|^\s++|\s++\z|(\s)\s+#si',
        ),
        array(
            '$1',
            '$1$2$3$4$5$6$7',
        ),
        $input);
}

function RemoveEmptySubFolders($path) {
    $empty=true;
    foreach (glob($path.DIRECTORY_SEPARATOR."*") as $file) {
        if (is_dir($file)) {
            if (!RemoveEmptySubFolders($file)) $empty=false;
        } else {
            $empty=false;
        }
    }
    if ($empty) rmdir($path);
    return $empty;
}

function deleteDirectory($dir) {
    if (!file_exists($dir)) {
        return true;
    }
    if (!is_dir($dir)) {
        return unlink($dir);
    }
    foreach (scandir($dir) as $item) {
        if ($item == '.' || $item == '..') {
            continue;
        }
        if (!deleteDirectory($dir . DIRECTORY_SEPARATOR . $item)) {
            return false;
        }
    }
    return rmdir($dir);
}

function zip_folder($code,$file_name_zip) {
    $rootPath = realpath(dirname(__FILE__)."/export_tmp/$code/");
    $zip = new ZipArchive();
    $zip->open(dirname(__FILE__)."/export_tmp/$file_name_zip", ZipArchive::CREATE | ZipArchive::OVERWRITE);
    $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($rootPath), RecursiveIteratorIterator::LEAVES_ONLY);
    foreach ($files as $name => $file) {
        if (!$file->isDir()) {
            $filePath = $file->getRealPath();
            $relativePath = substr($filePath, strlen($rootPath) + 1);
            $zip->addFile($filePath, $relativePath);
        }
    }
    $zip->close();
}
?>

