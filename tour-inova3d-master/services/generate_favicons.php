<?php
error_reporting(E_ALL & ~E_WARNING & ~E_NOTICE & ~E_DEPRECATED);
ini_set("memory_limit",-1);
ini_set('max_execution_time', 9999);
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
require_once(__DIR__."/../db/connection.php");
require_once(__DIR__."/../backend/functions.php");
$settings = get_settings();

if(!empty($settings['small_logo'])) {
    $logo_backend = $settings['small_logo'];
} else {
    $logo_backend = $settings['logo'];
}
$api_key = "ef6e1fd351061564ebe63d780cffa9e3cfc29a40";

if(isset($argv[1])) {
    $url = $argv[1];
    $url = str_replace("/backend/ajax","",$url);
} else {
    $currentPath = $_SERVER['PHP_SELF'];
    $pathInfo = pathinfo($currentPath);
    $hostName = $_SERVER['HTTP_HOST'];
    if (is_ssl()) { $protocol = 'https'; } else { $protocol = 'http'; }
    $url = $protocol."://".$hostName.$pathInfo['dirname'];
    $url = str_replace("/services","",$url);
}

$path = realpath(dirname(__FILE__) . '/..');

if(!empty($logo_backend)) {
    if (!file_exists($path.DIRECTORY_SEPARATOR.'favicons'.DIRECTORY_SEPARATOR.'custom'.DIRECTORY_SEPARATOR)) {
        mkdir($path.DIRECTORY_SEPARATOR.'favicons'.DIRECTORY_SEPARATOR.'custom'.DIRECTORY_SEPARATOR, 0775);
        chmod($path.DIRECTORY_SEPARATOR.'favicons'.DIRECTORY_SEPARATOR.'custom'.DIRECTORY_SEPARATOR, 0775);
    }
    $url_backend_logo = $url.'/backend/assets/'.$logo_backend;
    generate_favicons_api($api_key,$settings['name'],'../../backend/',$url_backend_logo,$path.DIRECTORY_SEPARATOR.'favicons'.DIRECTORY_SEPARATOR.'custom'.DIRECTORY_SEPARATOR);
    fix_manifest($path.DIRECTORY_SEPARATOR.'favicons'.DIRECTORY_SEPARATOR.'custom'.DIRECTORY_SEPARATOR,'../../backend/',$settings['name']);
}

$query = "SELECT code,logo,name FROM svt_virtualtours;";
$result = $mysqli->query($query);
if($result) {
    if($result->num_rows>0) {
        while($row = $result->fetch_array(MYSQLI_ASSOC)) {
            $code = $row['code'];
            $logo = $row['logo'];
            $name = $row['name'];
            if (!file_exists($path.DIRECTORY_SEPARATOR.'favicons'.DIRECTORY_SEPARATOR.'v_'.$code.DIRECTORY_SEPARATOR)) {
                mkdir($path.DIRECTORY_SEPARATOR.'favicons'.DIRECTORY_SEPARATOR.'v_'.$code.DIRECTORY_SEPARATOR, 0775);
            }
            if(!empty($logo)) {
                $url_logo = $url.'/viewer/content/'.$logo;
                generate_favicons_api($api_key,$name,'../../viewer/'.$code,$url_logo,$path.DIRECTORY_SEPARATOR.'favicons'.DIRECTORY_SEPARATOR.'v_'.$code.DIRECTORY_SEPARATOR);
            }
            fix_manifest($path.DIRECTORY_SEPARATOR.'favicons'.DIRECTORY_SEPARATOR.'v_'.$code.DIRECTORY_SEPARATOR,'../../viewer/'.$code,$name);
        }
    }
}

$query = "SELECT code,logo,name FROM svt_showcases;";
$result = $mysqli->query($query);
if($result) {
    if($result->num_rows>0) {
        while($row = $result->fetch_array(MYSQLI_ASSOC)) {
            $code = $row['code'];
            $logo = $row['logo'];
            $name = $row['name'];
            if (!file_exists($path.DIRECTORY_SEPARATOR.'favicons'.DIRECTORY_SEPARATOR.'s_'.$code.DIRECTORY_SEPARATOR)) {
                mkdir($path.DIRECTORY_SEPARATOR.'favicons'.DIRECTORY_SEPARATOR.'s_'.$code.DIRECTORY_SEPARATOR, 0775);
            }
            if(!empty($logo)) {
                $url_logo = $url.'/viewer/content/'.$logo;
                generate_favicons_api($api_key,$name,'../../showcase/'.$code,$url_logo,$path.DIRECTORY_SEPARATOR.'favicons'.DIRECTORY_SEPARATOR.'s_'.$code.DIRECTORY_SEPARATOR);
            }
            fix_manifest($path.DIRECTORY_SEPARATOR.'favicons'.DIRECTORY_SEPARATOR.'s_'.$code.DIRECTORY_SEPARATOR,'../../showcase/'.$code,$name);
        }
    }
}

function generate_favicons_api($api_key,$name,$start_url,$url_logo,$destination) {
    $json = '{
        "favicon_generation": {
            "api_key": "'.$api_key.'",
            "master_picture": {
                "type": "url",
                "url": "'.$url_logo.'"
            },
            "files_location": {
                "type": "path",
                "path": "."
            },
            "favicon_design": {
                "desktop_browser": {},
                "ios": {
                    "picture_aspect": "background_and_margin",
                    "margin": "4",
                    "background_color": "#ffffff",
                    "assets": {
                        "ios6_and_prior_icons": false,
                        "ios7_and_later_icons": true,
                        "precomposed_icons": false,
                        "declare_only_default_icon": true
                    }
                },
                "windows": {
                    "picture_aspect": "white_silhouette",
                    "background_color": "#ffffff",
                    "assets": {
                        "windows_80_ie_10_tile": true,
                        "windows_10_ie_11_edge_tiles": {
                            "small": false,
                            "medium": true,
                            "big": true,
                            "rectangle": false
                        }
                    }
                },
                "android_chrome": {
                    "picture_aspect": "shadow",
                    "assets": {
                        "legacy_icon": true,
                        "low_resolution_icons": false
                    },
                    "manifest": {
                        "name": "'.$name.'",
                        "display": "standalone",
                        "orientation": "portrait",
                        "start_url": "'.$start_url.'"
                    },
                    "theme_color": "#ffffff"
                },
                "safari_pinned_tab": {
                    "picture_aspect": "black_and_white",
                    "threshold": 60,
                    "theme_color": "#ffffff"
                }
            },
            "settings": {
                "compression": "3",
                "scaling_algorithm": "Mitchell",
                "error_on_image_too_small": false,
                "readme_file": false,
                "html_code_file": false,
                "use_path_as_is": false
            }
        }
    }';
    $url = 'https://realfavicongenerator.net/api/favicon';
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $result = curl_exec($ch);
    curl_close($ch);
    $array = json_decode($result,true);
    $files_url = $array['favicon_generation_result']['favicon']['files_urls'];
    $opts = array(
        'http'=>array(
            'method'=>"GET",
            'timeout'=>60,
            'ignore_errors'=> true,
            'header'=>"Accept-language: en\r\n" .
                "Cookie: foo=bar\r\n" .
                "User-Agent: Mozilla/5.0 (iPad; U; CPU OS 3_2 like Mac OS X; en-us) AppleWebKit/531.21.10 (KHTML, like Gecko) Version/4.0.4 Mobile/7B334b Safari/531.21.102011-10-16 20:23:10\r\n"

        )
    );
    $context = stream_context_create($opts);
    foreach ($files_url as $file_url) {
        $file_name = basename($file_url);
        $file_data = file_get_contents($file_url,false,$context);
        if($file_data==false) {
            $file_data = file_get_contents($file_url,false,$context);
        }
        file_put_contents($destination.$file_name,$file_data);
    }
}

function fix_manifest($path_dir,$url,$name) {
    if (file_exists($path_dir.'site.webmanifest')) {
        $content = file_get_contents($path_dir.'site.webmanifest');
        $array = json_decode($content,true);
        if(!array_key_exists('start_url',$array)) {
            $array['start_url'] = $url;
        }
        if(!array_key_exists('url',$array)) {
            $array['url'] = $url;
        }
        if(!array_key_exists('scope',$array)) {
            $array['scope'] = $url;
        }
        $array['display'] = 'standalone';
        $array['name'] = $name;
        $array['short_name'] = $name;
    } else {
        $content = '{"name":"","short_name":"","icons":[{"src":"","sizes":"192x192","type":"image\/png"}],"theme_color":"#ffffff","background_color":"#ffffff","start_url":"","display":"standalone","orientation":"portrait","url":"","scope":""}';
        $array = json_decode($content,true);
        $array['start_url'] = $url;
        $array['url'] = $url;
        $array['scope'] = $url;
        $array['name'] = $name;
        $array['short_name'] = $name;
    }
    $array['orientation']="any";
    if(empty($array['icons'][0]['src'])) {
       if(file_exists($path_dir."android-chrome-192x192.png")) {
           $array['icons'][0]['src']="android-chrome-192x192.png";
       } elseif(file_exists($path_dir."..".DIRECTORY_SEPARATOR."custom".DIRECTORY_SEPARATOR."android-chrome-192x192.png")) {
           $array['icons'][0]['src']="../custom/android-chrome-192x192.png";
       } elseif(file_exists($path_dir."..".DIRECTORY_SEPARATOR."android-chrome-192x192.png")) {
           $array['icons'][0]['src']="../android-chrome-192x192.png";
       }
    }
    $json = json_encode($array);
    file_put_contents($path_dir.'site.webmanifest',$json);
}