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

$settings = get_settings();
$user_info = get_user_info($_SESSION['id_user']);
$user_role = get_user_role($_SESSION['id_user']);
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

if($user_role!='administrator') {
    ob_end_clean();
    echo json_encode(array("status"=>"error","msg"=>_("unauthorized")));
    exit;
}

$id_vt = $_POST['id_virtualtour'];

$sql_insert = '';
$array_commands = [];
$code = '';
$query = "SELECT name,description,author,code,song,logo,nadir_logo,background_image,background_video,intro_desktop,intro_mobile,markers_id_icon_library,pois_id_icon_library,presentation_video FROM svt_virtualtours WHERE id=$id_vt LIMIT 1;";
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
        $name = $row['name'];
        $description = $row['description'];
        $author = $row['author'];
        $markers_id_icon_library = $row['markers_id_icon_library'];
        $pois_id_icon_library = $row['pois_id_icon_library'];
        $presentation_video = $row['presentation_video'];
        if($presentation_video!='') $presentation_video = basename($presentation_video);
        $tmp=array();
        $tmp['table']='svt_virtualtours';
        $tmp['fields']=array('id'=>$id_vt,"markers_id_icon_library"=>$markers_id_icon_library,"pois_id_icon_library"=>$pois_id_icon_library);
        $tmp['sql']=show_inserts($mysqli,'svt_virtualtours',"id=$id_vt",['id','id_user','code','ga_tracking_id','fb_page_id','id_category','snipcart_api_key'],['pois_id_icon_library','markers_id_icon_library']);
        array_push($array_commands,$tmp);
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

copy_file($song,'content');
copy_file($logo,'content');
copy_file($nadir_logo,'content');
copy_file($background_image,'content');
copy_file($background_video,'content');
copy_file($intro_desktop,'content');
copy_file($intro_mobile,'content');
copy_file($presentation_video,'content');

$query = "SELECT id,image FROM svt_gallery WHERE id_virtualtour=$id_vt;";
$result = $mysqli->query($query);
if($result) {
    if ($result->num_rows > 0) {
        while($row = $result->fetch_array(MYSQLI_ASSOC)) {
            $id_gallery = $row['id'];
            $image = $row['image'];
            copy_file($image,'gallery');
            copy_file($image,'gallery/thumb');
            $tmp=array();
            $tmp['table']='svt_gallery';
            $tmp['fields']=array();
            $tmp['sql']=show_inserts($mysqli,'svt_gallery',"id=$id_gallery",['id'],['id_virtualtour']);
            array_push($array_commands,$tmp);
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
$query = "SELECT id,map,id_room_default FROM svt_maps WHERE id_virtualtour=$id_vt;";
$result = $mysqli->query($query);
if($result) {
    if ($result->num_rows > 0) {
        while($row = $result->fetch_array(MYSQLI_ASSOC)) {
            $id_map = $row['id'];
            $map = $row['map'];
            $id_room_default = $row['id_room_default'];
            copy_file($map,'maps');
            copy_file($map,'maps/thumb');
            $tmp=array();
            $tmp['table']='svt_maps';
            $tmp['fields']=array("id"=>$id_map,"id_room_default"=>$id_room_default);
            $tmp['sql']=show_inserts($mysqli,'svt_maps',"id=$id_map",['id'],['id_virtualtour','id_room_default']);
            array_push($array_commands,$tmp);
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
$query = "SELECT id,id_map,id_poi_autoopen,type,panorama_image,panorama_video,panorama_json,thumb_image,logo FROM svt_rooms WHERE id_virtualtour=$id_vt;";
$result = $mysqli->query($query);
if($result) {
    if ($result->num_rows > 0) {
        while($row = $result->fetch_array(MYSQLI_ASSOC)) {
            $id_room = $row['id'];
            $id_map = $row['id_map'];
            $id_poi_autoopen = $row['id_poi_autoopen'];
            array_push($array_id_rooms,$id_room);
            $panorama_image = $row['panorama_image'];
            $panorama_name = explode(".",$panorama_image)[0];
            $panorama_video = $row['panorama_video'];
            $panorama_json = $row['panorama_json'];
            $thumb_image = $row['thumb_image'];
            $logo = $row['logo'];
            copy_file($panorama_image,'panoramas');
            copy_file($panorama_image,'panoramas/lowres');
            copy_file($panorama_image,'panoramas/mobile');
            copy_file($panorama_image,'panoramas/original');
            copy_file($panorama_image,'panoramas/preview');
            copy_file($panorama_image,'panoramas/thumb');
            copy_file($panorama_video,'videos');
            copy_file($panorama_json,'panoramas');
            copy_file($thumb_image,'panoramas/thumb_custom');
            copy_file($logo,'content');
            if(file_exists(dirname(__FILE__)."/../viewer/panoramas/multires/$panorama_name/")) {
                recursive_copy(dirname(__FILE__)."/../viewer/panoramas/multires/$panorama_name",dirname(__FILE__)."/export_tmp/$code/panoramas/multires/$panorama_name");
            }
            $tmp=array();
            $tmp['table']='svt_rooms';
            $tmp['fields']=array("id"=>$id_room,"id_map"=>$id_map,"id_poi_autoopen"=>$id_poi_autoopen);
            $tmp['sql']=show_inserts($mysqli,'svt_rooms',"id=$id_room",['id','access_count','transition_loading'],['id_virtualtour','id_map']);
            array_push($array_commands,$tmp);
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
$id_rooms = implode(",",$array_id_rooms);
if(!empty($id_rooms)) {
    $query = "SELECT id,id_room,panorama_image FROM svt_rooms_alt WHERE id_room IN ($id_rooms);";
    $result = $mysqli->query($query);
    if($result) {
        if ($result->num_rows > 0) {
            while($row = $result->fetch_array(MYSQLI_ASSOC)) {
                $id_room_alt = $row['id'];
                $id_room = $row['id_room'];
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
                $tmp=array();
                $tmp['table']='svt_rooms_alt';
                $tmp['fields']=array("id"=>$id_room_alt,"id_room"=>$id_room);
                $tmp['sql']=show_inserts($mysqli,'svt_rooms_alt',"id=$id_room_alt",['id'],['id_room']);
                array_push($array_commands,$tmp);
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
    $query = "SELECT id,id_room,type,content,embed_type,embed_content,id_icon_library FROM svt_pois WHERE id_room IN ($id_rooms);";
    $result = $mysqli->query($query);
    if($result) {
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_array(MYSQLI_ASSOC)) {
                $id_poi = $row['id'];
                $id_room = $row['id_room'];
                $id_icon_library = $row['id_icon_library'];
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
                $tmp=array();
                $tmp['table']='svt_pois';
                switch($type) {
                    case 'product':
                        $tmp['fields']=array("id"=>$id_poi,"id_icon_library"=>$id_icon_library,"id_room"=>$id_room,"id_product"=>$content,"id_room_alt"=>"");
                        $tmp['sql']=show_inserts($mysqli,'svt_pois',"id=$id_poi",['id','access_count'],['id_room','id_icon_library','content']);
                        break;
                    case 'switch_pano':
                        $tmp['fields']=array("id"=>$id_poi,"id_icon_library"=>$id_icon_library,"id_room"=>$id_room,"id_product"=>"","id_room_alt"=>$content);
                        $tmp['sql']=show_inserts($mysqli,'svt_pois',"id=$id_poi",['id','access_count'],['id_room','id_icon_library','content']);
                        break;
                    default:
                        $tmp['fields']=array("id"=>$id_poi,"id_icon_library"=>$id_icon_library,"id_room"=>$id_room,"id_product"=>"","id_room_alt"=>"");
                        $tmp['sql']=show_inserts($mysqli,'svt_pois',"id=$id_poi",['id','access_count'],['id_room','id_icon_library']);
                        break;
                }
                array_push($array_commands,$tmp);
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
    $query = "SELECT id,id_room,id_room_target,id_icon_library FROM svt_markers WHERE id_room IN ($id_rooms);";
    $result = $mysqli->query($query);
    if($result) {
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_array(MYSQLI_ASSOC)) {
                $id_marker = $row['id'];
                $id_room = $row['id_room'];
                $id_room_target = $row['id_room_target'];
                $id_icon_library = $row['id_icon_library'];
                $tmp=array();
                $tmp['table']='svt_markers';
                $tmp['fields']=array("id"=>$id_marker,"id_icon_library"=>$id_icon_library,"id_room"=>$id_room,"id_room_target"=>$id_room_target);
                $tmp['sql']=show_inserts($mysqli,'svt_markers',"id=$id_marker",['id'],['id_room','id_room_target','id_icon_library']);
                array_push($array_commands,$tmp);
            }
        }
    }
    $mysqli->close();
    $mysqli = new mysqli(DATABASE_HOST, DATABASE_USERNAME, DATABASE_PASSWORD, DATABASE_NAME);
    if (mysqli_connect_errno()) {
        echo mysqli_connect_error();
        exit();
    }
}
$mysqli->query("SET NAMES 'utf8';");
$query = "SELECT id,image FROM svt_icons WHERE id_virtualtour=$id_vt;";
$result = $mysqli->query($query);
if($result) {
    if ($result->num_rows > 0) {
        while($row = $result->fetch_array(MYSQLI_ASSOC)) {
            $id_icon = $row['id'];
            $image = $row['image'];
            copy_file($image,'icons');
            $tmp=array();
            $tmp['table']='svt_icons';
            $tmp['fields']=array("id"=>$id_icon);
            $tmp['sql']=show_inserts($mysqli,'svt_icons',"id=$id_icon",['id'],['id_virtualtour']);
            array_push($array_commands,$tmp);
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
$query = "SELECT id,file FROM svt_media_library WHERE id_virtualtour=$id_vt;";
$result = $mysqli->query($query);
if($result) {
    if ($result->num_rows > 0) {
        while($row = $result->fetch_array(MYSQLI_ASSOC)) {
            $id_media = $row['id'];
            $file = $row['file'];
            copy_file($file,'media');
            copy_file($file,'media/thumb');
            $tmp=array();
            $tmp['table']='svt_media_library';
            $tmp['fields']=array("id"=>$id_media);
            $tmp['sql']=show_inserts($mysqli,'svt_media_library',"id=$id_media",['id'],['id_virtualtour']);
            array_push($array_commands,$tmp);
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
$query = "SELECT id,file FROM svt_music_library WHERE id_virtualtour=$id_vt;";
$result = $mysqli->query($query);
if($result) {
    if ($result->num_rows > 0) {
        while($row = $result->fetch_array(MYSQLI_ASSOC)) {
            $id_music = $row['id'];
            $file = $row['file'];
            copy_file($file,'content');
            $tmp=array();
            $tmp['table']='svt_music_library';
            $tmp['fields']=array("id"=>$id_music);
            $tmp['sql']=show_inserts($mysqli,'svt_music_library',"id=$id_music",['id'],['id_virtualtour']);
            array_push($array_commands,$tmp);
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
$id_pois = implode(",",$array_id_pois);
if(!empty($id_pois)) {
    $query = "SELECT id,id_poi,image FROM svt_poi_embedded_gallery WHERE id_poi IN ($id_pois);";
    $result = $mysqli->query($query);
    if($result) {
        if ($result->num_rows > 0) {
            while($row = $result->fetch_array(MYSQLI_ASSOC)) {
                $id_gallery = $row['id'];
                $id_poi = $row['id_poi'];
                $image = $row['image'];
                copy_file($image,'gallery');
                copy_file($image,'gallery/thumb');
                $tmp=array();
                $tmp['table']='svt_poi_embedded_gallery';
                $tmp['fields']=array("id"=>$id_gallery,"id_poi"=>$id_poi);
                $tmp['sql']=show_inserts($mysqli,'svt_poi_embedded_gallery',"id=$id_gallery",['id'],['id_poi']);
                array_push($array_commands,$tmp);
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
    $query = "SELECT id,id_poi,image FROM svt_poi_gallery WHERE id_poi IN ($id_pois);";
    $result = $mysqli->query($query);
    if($result) {
        if ($result->num_rows > 0) {
            while($row = $result->fetch_array(MYSQLI_ASSOC)) {
                $id_gallery = $row['id'];
                $id_poi = $row['id_poi'];
                $image = $row['image'];
                copy_file($image,'gallery');
                copy_file($image,'gallery/thumb');
                $tmp=array();
                $tmp['table']='svt_poi_gallery';
                $tmp['fields']=array("id"=>$id_gallery,"id_poi"=>$id_poi);
                $tmp['sql']=show_inserts($mysqli,'svt_poi_gallery',"id=$id_gallery",['id'],['id_poi']);
                array_push($array_commands,$tmp);
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
    $query = "SELECT id,id_poi,image FROM svt_poi_objects360 WHERE id_poi IN ($id_pois);";
    $result = $mysqli->query($query);
    if($result) {
        if ($result->num_rows > 0) {
            while($row = $result->fetch_array(MYSQLI_ASSOC)) {
                $id_object360 = $row['id'];
                $id_poi = $row['id_poi'];
                $image = $row['image'];
                copy_file($image,'objects360');
                $tmp=array();
                $tmp['table']='svt_poi_objects360';
                $tmp['fields']=array("id"=>$id_object360,"id_poi"=>$id_poi);
                $tmp['sql']=show_inserts($mysqli,'svt_poi_objects360',"id=$id_object360",['id'],['id_poi']);
                array_push($array_commands,$tmp);
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
$array_id_products = array();
$query = "SELECT id FROM svt_products WHERE id_virtualtour=$id_vt;";
$result = $mysqli->query($query);
if($result) {
    if ($result->num_rows > 0) {
        while($row = $result->fetch_array(MYSQLI_ASSOC)) {
            $id_product = $row['id'];
            array_push($array_id_products,$id_product);
            $tmp=array();
            $tmp['table']='svt_products';
            $tmp['fields']=array("id"=>$id_product);
            $tmp['sql']=show_inserts($mysqli,'svt_products',"id=$id_product",['id'],['id_virtualtour']);
            array_push($array_commands,$tmp);
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
$id_products = implode(",",$array_id_products);
if(!empty($id_products)) {
    $query = "SELECT id,id_product,image FROM svt_product_images WHERE id_product IN ($id_products);";
    $result = $mysqli->query($query);
    if($result) {
        if ($result->num_rows > 0) {
            while($row = $result->fetch_array(MYSQLI_ASSOC)) {
                $id_product_image = $row['id'];
                $id_product = $row['id_product'];
                $image = $row['image'];
                copy_file($image,'products');
                copy_file($image,'products/thumb');
                $tmp=array();
                $tmp['table']='svt_product_images';
                $tmp['fields']=array("id"=>$id_product_image,"id_product"=>$id_product);
                $tmp['sql']=show_inserts($mysqli,'svt_product_images',"id=$id_product_image",['id'],['id_product']);
                array_push($array_commands,$tmp);
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
$query = "SELECT id,id_room,action FROM svt_presentations WHERE id_virtualtour=$id_vt;";
$result = $mysqli->query($query);
if($result) {
    if ($result->num_rows > 0) {
        while($row = $result->fetch_array(MYSQLI_ASSOC)) {
            $id_presentation = $row['id'];
            $id_room = $row['id_room'];
            $action = $row['action'];
            $tmp=array();
            $tmp['table']='svt_presentations';
            $tmp['fields']=array("id"=>$id_presentation,"id_room"=>$id_room);
            if($action=='goto') {
                $tmp['sql']=show_inserts($mysqli,'svt_presentations',"id=$id_presentation",['id'],['id_virtualtour','id_room','params']);
            } else {
                $tmp['sql']=show_inserts($mysqli,'svt_presentations',"id=$id_presentation",['id'],['id_virtualtour','id_room']);
            }
            array_push($array_commands,$tmp);
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
$query = "SELECT id FROM svt_presets WHERE id_virtualtour=$id_vt;";
$result = $mysqli->query($query);
if($result) {
    if ($result->num_rows > 0) {
        while($row = $result->fetch_array(MYSQLI_ASSOC)) {
            $id_preset = $row['id'];
            $tmp=array();
            $tmp['table']='svt_presets';
            $tmp['fields']=array("id"=>$id_preset);
            $tmp['sql']=show_inserts($mysqli,'svt_presets',"id=$id_preset",['id'],['id_virtualtour']);
            array_push($array_commands,$tmp);
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

$version = $settings['version'];
$array = array("version"=>$version,"commands"=>$array_commands);

$json = json_encode($array);
file_put_contents(dirname(__FILE__)."/export_tmp/$code/import.json",$json);

$file_name_zip = "B_".str_replace(" ","_",$name).".zip";

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

function show_inserts($mysqli,$table,$where=null,$exclude=[],$placeholders=[]) {
    $sql="SELECT * FROM `{$table}`".(is_null($where) ? "" : " WHERE ".$where).";";
    $result=$mysqli->query($sql);
    $fields=array();
    foreach ($result->fetch_fields() as $key=>$value) {
        if(!in_array($value->name,$exclude)) {
            $fields[$key]="`{$value->name}`";
        }
    }
    $values=array();
    while ($row=$result->fetch_array(MYSQLI_ASSOC)) {
        $temp=array();
        foreach ($row as $key=>$value) {
            if(!in_array($key,$exclude)) {
                if(in_array($key,$placeholders)) {
                    $temp[$key] = '%'.$key.'%';
                } else {
                    $temp[$key] = ($value === null ? 'NULL' : "'" . $mysqli->real_escape_string($value) . "'");
                }
            }
        }
        $values[]="(".implode(",",$temp).")";
    }
    return "INSERT INTO `{$table}` (".implode(",",$fields).") VALUES ".implode(",\n",$values).";";
}
?>

