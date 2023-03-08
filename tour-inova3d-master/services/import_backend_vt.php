<?php
error_reporting(E_ALL & ~E_WARNING & ~E_NOTICE & ~E_DEPRECATED);
session_start();
ob_start();
if(($_SERVER['SERVER_ADDR']=='5.9.29.89') && ($_SERVER['REMOTE_ADDR']!=$_SESSION['ip_developer'])) {
    //DEMO CHECK
    die();
}
ini_set("memory_limit",-1);
ini_set('max_execution_time', 9999);
ini_set('max_input_time', 9999);
require_once(__DIR__."/../db/connection.php");
require_once(__DIR__."/../backend/functions.php");

$debug = false;

$id_user = $_SESSION['id_user'];
if(isset($_SESSION['sample_data'])) {
    $sample=true;
    $sample_name = $_SESSION['sample_name'];
    $sample_author = $_SESSION['sample_author'];
    unset($_SESSION['sample_data']);
    unset($_SESSION['sample_name']);
    unset($_SESSION['sample_author']);
} else {
    $sample=false;
}

$settings = get_settings();
$user_info = get_user_info($id_user);
if(!empty($user_info['language'])) {
    set_language($user_info['language'],$settings['language_domain']);
} else {
    set_language($settings['language'],$settings['language_domain']);
}

if (!file_exists(dirname(__FILE__).'/import_tmp/')) {
    mkdir(dirname(__FILE__).'/import_tmp/', 0775);
}

if(!$sample) {
    if (!class_exists('ZipArchive')) {
        ob_end_clean();
        echo json_encode(array("status"=>"error","msg"=>_("php zip not enabled")));
        exit;
    }
    $file_zip = dirname(__FILE__)."/import_tmp/".$_POST['file_name'];
    $file_info = pathinfo($file_zip);
    $path_import = dirname(__FILE__).DIRECTORY_SEPARATOR.'import_tmp'.DIRECTORY_SEPARATOR.$file_info['filename'].DIRECTORY_SEPARATOR;
} else {
    $path_import = dirname(__FILE__).DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'sample_data'.DIRECTORY_SEPARATOR.'B_SIMPLE_VIRTUAL_TOUR'.DIRECTORY_SEPARATOR;
}

$path_dest = str_replace(DIRECTORY_SEPARATOR.'services',DIRECTORY_SEPARATOR,dirname(__FILE__)).'viewer'.DIRECTORY_SEPARATOR;

if(!$sample) {
    $zip = new ZipArchive;
    $res = $zip->open($file_zip);
    if ($res === TRUE) {
        $zip->extractTo($path_import);
        $zip->close();
    } else {
        ob_end_clean();
        echo json_encode(array("status" => "error", "msg" => _("File not found.")));
        exit;
    }
}

$json = file_get_contents($path_import.'import.json');
$array = json_decode($json,true);
$version_import = $array['version'];
$settings = get_settings();
$version = $settings['version'];
if($version!=$version_import) {
    ob_end_clean();
    echo json_encode(array("status"=>"error","msg"=>_("the file cannot be imported: system version different from the one with which the file was exported.")));
    exit;
}

$commands = $array['commands'];
$array_mapping = array();
$icons_mapping = array();
$maps_mapping = array();
$rooms_mapping = array();
$rooms_alt_mapping = array();
$pois_mapping = array();
$markers_mapping = array();
$products_mapping = array();

$filter = array('multires','objects360');
$files = new RecursiveIteratorIterator(
    new RecursiveCallbackFilterIterator(
        new RecursiveDirectoryIterator($path_import,RecursiveDirectoryIterator::SKIP_DOTS),
        function ($fileInfo, $key, $iterator) use ($filter) {
            return $fileInfo->isFile() || !in_array($fileInfo->getBaseName(), $filter);
        }
    )
);

foreach($files as $file) {
    $file_name = $file->getFilename();
    if($file_name=='import.json') continue;
    if(array_key_exists($file_name,$array_mapping)) {
        $file_name_new = $array_mapping[$file_name];
    } else {
        $to_replace = getStringBetween($file_name,"_",".");
        $milliseconds = round(microtime(true) * 1000);
        $file_name_new = str_replace($to_replace,$milliseconds,$file_name);
        $array_mapping[$file_name]=$file_name_new;
    }
    $source_file = $file->getPathname();
    $abs_path = str_replace($path_import,'',$file->getPath());
    $dest_file = $path_dest.$abs_path.DIRECTORY_SEPARATOR.$file_name_new;
    copy($source_file,$dest_file);
    usleep(1000);
}

if(file_exists($path_import.'panoramas'.DIRECTORY_SEPARATOR.'multires')) {
    $files_multires = new RecursiveIteratorIterator(
        new RecursiveCallbackFilterIterator(
            new RecursiveDirectoryIterator($path_import.'panoramas'.DIRECTORY_SEPARATOR.'multires',RecursiveDirectoryIterator::SKIP_DOTS),
            function ($fileInfo, $key, $iterator) use ($filter) {
                return true;
            }
        )
    );
    foreach ($files_multires as $file) {
        $file_name = $file->getFilename();
        $source_file = $file->getPathname();
        $abs_path = str_replace($path_import,'',$file->getPath());
        foreach ($array_mapping as $source_name=>$dest_name) {
            if (strpos($source_name, 'pano_') === 0) {
                $source_name = getStringBetween($source_name,'_','.');
                $dest_name = getStringBetween($dest_name,'_','.');
                $abs_path = str_replace($source_name,$dest_name,$abs_path);
            }
        }
        $dest_dir = $path_dest.$abs_path.DIRECTORY_SEPARATOR;
        if(!file_exists($dest_dir)) {
            mkdir($dest_dir, 0775, true);
        }
        $dest_file = $dest_dir.$file_name;
        copy($source_file,$dest_file);
    }
}

$commands_import = get_commands($commands,'svt_virtualtours');
$query = $commands_import[0]['sql'];
$markers_id_icon_library = $commands_import[0]['fields']['markers_id_icon_library'];
$pois_id_icon_library = $commands_import[0]['fields']['pois_id_icon_library'];
$query = str_replace(["%markers_id_icon_library%","%pois_id_icon_library%"],"0",$query);
$query = query_mapping($query,$array_mapping);
if($debug) {
    $date = date('Y-m-d H:i');
    file_put_contents(realpath(dirname(__FILE__))."/log_import.txt","$date - query: $query".PHP_EOL,FILE_APPEND);
}
$result=$mysqli->query($query);
if($result) {
    $id_vt=$mysqli->insert_id;
    $code=md5($id_vt);
    $mysqli->query("UPDATE svt_virtualtours SET date_created=NOW(),id_user=$id_user,code='$code' WHERE id=$id_vt;");
    if($sample) {
        $mysqli->query("UPDATE svt_virtualtours SET name='$sample_name',author='$sample_author' WHERE id=$id_vt;");
    }
} else {
    echo json_encode(array("status"=>"error","msg"=>_("An error has occurred: ".$mysqli->error)));
    exit;
}


$commands_import = get_commands($commands,'svt_icons');
foreach ($commands_import as $command_import) {
    $id_icon = $command_import['fields']['id'];
    $query = $command_import['sql'];
    $query = str_replace("%id_virtualtour%",$id_vt,$query);
    $query = query_mapping($query,$array_mapping);
    if($debug) {
        $date = date('Y-m-d H:i');
        file_put_contents(realpath(dirname(__FILE__))."/log_import.txt","$date - query: $query".PHP_EOL,FILE_APPEND);
    }
    $result=$mysqli->query($query);
    if($result) {
        $id_icon_new = $mysqli->insert_id;
        $icons_mapping[$id_icon]=$id_icon_new;
    } else {
        if($debug) {
            $date = date('Y-m-d H:i');
            file_put_contents(realpath(dirname(__FILE__))."/log_import.txt","$date - error: ".$mysqli->error.PHP_EOL,FILE_APPEND);
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

if($markers_id_icon_library!=0) {
    $markers_id_icon_library_new = $icons_mapping[$markers_id_icon_library];
    $mysqli->query("UPDATE svt_virtualtours SET markers_id_icon_library=$markers_id_icon_library_new WHERE id=$id_vt;");
}
if($pois_id_icon_library!=0) {
    $pois_id_icon_library_new = $icons_mapping[$pois_id_icon_library];
    $mysqli->query("UPDATE svt_virtualtours SET pois_id_icon_library=$pois_id_icon_library_new WHERE id=$id_vt;");
}

$commands_import = get_commands($commands,'svt_gallery');
foreach ($commands_import as $command_import) {
    $id_icon = $command_import['fields']['id'];
    $query = $command_import['sql'];
    $query = str_replace("%id_virtualtour%",$id_vt,$query);
    $query = query_mapping($query,$array_mapping);
    if($debug) {
        $date = date('Y-m-d H:i');
        file_put_contents(realpath(dirname(__FILE__))."/log_import.txt","$date - query: $query".PHP_EOL,FILE_APPEND);
    }
    $result=$mysqli->query($query);
    if(!$result) {
        if($debug) {
            $date = date('Y-m-d H:i');
            file_put_contents(realpath(dirname(__FILE__))."/log_import.txt","$date - error: ".$mysqli->error.PHP_EOL,FILE_APPEND);
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

$commands_import = get_commands($commands,'svt_media_library');
foreach ($commands_import as $command_import) {
    $id_icon = $command_import['fields']['id'];
    $query = $command_import['sql'];
    $query = str_replace("%id_virtualtour%",$id_vt,$query);
    $query = query_mapping($query,$array_mapping);
    if($debug) {
        $date = date('Y-m-d H:i');
        file_put_contents(realpath(dirname(__FILE__))."/log_import.txt","$date - query: $query".PHP_EOL,FILE_APPEND);
    }
    $result=$mysqli->query($query);
    if(!$result) {
        if($debug) {
            $date = date('Y-m-d H:i');
            file_put_contents(realpath(dirname(__FILE__))."/log_import.txt","$date - error: ".$mysqli->error.PHP_EOL,FILE_APPEND);
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

$commands_import = get_commands($commands,'svt_music_library');
foreach ($commands_import as $command_import) {
    $id_icon = $command_import['fields']['id'];
    $query = $command_import['sql'];
    $query = str_replace("%id_virtualtour%",$id_vt,$query);
    $query = query_mapping($query,$array_mapping);
    if($debug) {
        $date = date('Y-m-d H:i');
        file_put_contents(realpath(dirname(__FILE__))."/log_import.txt","$date - query: $query".PHP_EOL,FILE_APPEND);
    }
    $result=$mysqli->query($query);
    if(!$result) {
        if($debug) {
            $date = date('Y-m-d H:i');
            file_put_contents(realpath(dirname(__FILE__))."/log_import.txt","$date - error: ".$mysqli->error.PHP_EOL,FILE_APPEND);
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

$commands_import = get_commands($commands,'svt_presets');
foreach ($commands_import as $command_import) {
    $id_icon = $command_import['fields']['id'];
    $query = $command_import['sql'];
    $query = str_replace("%id_virtualtour%",$id_vt,$query);
    $query = query_mapping($query,$array_mapping);
    if($debug) {
        $date = date('Y-m-d H:i');
        file_put_contents(realpath(dirname(__FILE__))."/log_import.txt","$date - query: $query".PHP_EOL,FILE_APPEND);
    }
    $result=$mysqli->query($query);
    if(!$result) {
        if($debug) {
            $date = date('Y-m-d H:i');
            file_put_contents(realpath(dirname(__FILE__))."/log_import.txt","$date - error: ".$mysqli->error.PHP_EOL,FILE_APPEND);
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

$commands_import = get_commands($commands,'svt_maps');
foreach ($commands_import as $command_import) {
    $id_map = $command_import['fields']['id'];
    $id_room_default = $command_import['fields']['id_room_default'];
    $query = $command_import['sql'];
    $query = str_replace("%id_virtualtour%",$id_vt,$query);
    $query = str_replace("%id_room_default%",'NULL',$query);
    $query = query_mapping($query,$array_mapping);
    if($debug) {
        $date = date('Y-m-d H:i');
        file_put_contents(realpath(dirname(__FILE__))."/log_import.txt","$date - query: $query".PHP_EOL,FILE_APPEND);
    }
    $result=$mysqli->query($query);
    if($result) {
        $id_map_new = $mysqli->insert_id;
        $maps_mapping[$id_map]=$id_map_new;
    } else {
        if($debug) {
            $date = date('Y-m-d H:i');
            file_put_contents(realpath(dirname(__FILE__))."/log_import.txt","$date - error: ".$mysqli->error.PHP_EOL,FILE_APPEND);
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

$commands_import = get_commands($commands,'svt_rooms');
foreach ($commands_import as $command_import) {
    $id_room = $command_import['fields']['id'];
    $id_map = $command_import['fields']['id_map'];
    $id_poi_autoopen = $command_import['fields']['id_poi_autoopen'];
    $query = $command_import['sql'];
    $query = str_replace("%id_virtualtour%",$id_vt,$query);
    if(!empty($id_map) && $id_map!=0) {
        $id_map_new = $maps_mapping[$id_map];
    } else {
        $id_map_new='NULL';
    }
    $query = str_replace("%id_map%",$id_map_new,$query);
    $query = query_mapping($query,$array_mapping);
    if($debug) {
        $date = date('Y-m-d H:i');
        file_put_contents(realpath(dirname(__FILE__))."/log_import.txt","$date - query: $query".PHP_EOL,FILE_APPEND);
    }
    $result=$mysqli->query($query);
    if($result) {
        $id_room_new = $mysqli->insert_id;
        $rooms_mapping[$id_room]=$id_room_new;
    } else {
        if($debug) {
            $date = date('Y-m-d H:i');
            file_put_contents(realpath(dirname(__FILE__))."/log_import.txt","$date - error: ".$mysqli->error.PHP_EOL,FILE_APPEND);
        }
    }
}

$commands_import = get_commands($commands,'svt_maps');
foreach ($commands_import as $command_import) {
    $id_map = $command_import['fields']['id'];
    $id_room_default = $command_import['fields']['id_room_default'];
    $id_map_new = $maps_mapping[$id_map];
    if(!empty($id_room_default)) {
        $id_room_default_new = $rooms_mapping[$id_room_default];
        $mysqli->query("UPDATE svt_maps SET id_room_default=$id_room_default_new WHERE id=$id_map_new;");
    }
}

$mysqli->close();
$mysqli = new mysqli(DATABASE_HOST, DATABASE_USERNAME, DATABASE_PASSWORD, DATABASE_NAME);
if (mysqli_connect_errno()) {
    echo mysqli_connect_error();
    exit();
}
$mysqli->query("SET NAMES 'utf8';");

$query = "SELECT list_alt,dollhouse FROM svt_virtualtours WHERE id=$id_vt LIMIT 1;";
$result = $mysqli->query($query);
if($result) {
    if($result->num_rows==1) {
        $row=$result->fetch_array(MYSQLI_ASSOC);
        $list_alt=$row['list_alt'];
        $dollhouse=$row['dollhouse'];
        if(!empty($list_alt)) {
            $list_alt_array = json_decode($list_alt, true);
            foreach ($list_alt_array as $key => $item) {
                switch ($item['type']) {
                    case 'room':
                        $id_room = $item['id'];
                        $list_alt_array[$key]['id'] = $rooms_mapping[$id_room];
                        break;
                    case 'category':
                        $childrens = array();
                        foreach ($item['children'] as $key_c => $children) {
                            if ($children['type'] == "room") {
                                $id_room = $children['id'];
                                $list_alt_array[$key]['children'][$key_c]['id'] = $rooms_mapping[$id_room];
                            }
                        }
                        break;
                }
            }
            $list_alt = json_encode($list_alt_array);
            $mysqli->query("UPDATE svt_virtualtours SET list_alt='$list_alt' WHERE id=$id_vt;");
        }
        if(!empty($dollhouse)) {
            $dollhouse_array = json_decode($dollhouse, true);
            $rooms_to_delete = array();
            foreach ($dollhouse_array['rooms'] as $key => $room) {
                $id_room = $room['id'];
                if(array_key_exists($id_room,$rooms_mapping)) {
                    $dollhouse_array['rooms'][$key]['id'] = $rooms_mapping[$id_room];
                } else {
                    array_push($rooms_to_delete,$key);
                }
            }
            foreach ($rooms_to_delete as $room_to_delete) {
                array_splice($dollhouse_array['rooms'], $room_to_delete, 1);
            }
            $dollhouse = json_encode($dollhouse_array);
            $mysqli->query("UPDATE svt_virtualtours SET dollhouse='$dollhouse' WHERE id=$id_vt;");
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

$commands_import = get_commands($commands,'svt_products');
foreach ($commands_import as $command_import) {
    $id_product = $command_import['fields']['id'];
    $query = $command_import['sql'];
    $query = str_replace("%id_virtualtour%",$id_vt,$query);
    if($debug) {
        $date = date('Y-m-d H:i');
        file_put_contents(realpath(dirname(__FILE__))."/log_import.txt","$date - query: $query".PHP_EOL,FILE_APPEND);
    }
    $result=$mysqli->query($query);
    if($result) {
        $id_product_new = $mysqli->insert_id;
        $products_mapping[$id_product]=$id_product_new;
    } else {
        if($debug) {
            $date = date('Y-m-d H:i');
            file_put_contents(realpath(dirname(__FILE__))."/log_import.txt","$date - error: ".$mysqli->error.PHP_EOL,FILE_APPEND);
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

$commands_import = get_commands($commands,'svt_product_images');
foreach ($commands_import as $command_import) {
    $id_product = $command_import['fields']['id_product'];
    $query = $command_import['sql'];
    if(!empty($id_product) && $id_product!=0) {
        $id_product_new = $products_mapping[$id_product];
    } else {
        $id_product_new='NULL';
    }
    $query = str_replace("%id_product%",$id_product_new,$query);
    $query = query_mapping($query,$array_mapping);
    if($debug) {
        $date = date('Y-m-d H:i');
        file_put_contents(realpath(dirname(__FILE__))."/log_import.txt","$date - query: $query".PHP_EOL,FILE_APPEND);
    }
    $result=$mysqli->query($query);
    if(!$result) {
        if($debug) {
            $date = date('Y-m-d H:i');
            file_put_contents(realpath(dirname(__FILE__))."/log_import.txt","$date - error: ".$mysqli->error.PHP_EOL,FILE_APPEND);
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

$commands_import = get_commands($commands,'svt_pois');
foreach ($commands_import as $command_import) {
    $id_poi = $command_import['fields']['id'];
    $id_room = $command_import['fields']['id_room'];
    $id_icon = $command_import['fields']['id_icon_library'];
    $id_product = $command_import['fields']['id_product'];
    $id_room_alt = $command_import['fields']['id_room_alt'];
    $query = $command_import['sql'];
    if(!empty($id_room) && $id_room!=0) {
        $id_room_new = $rooms_mapping[$id_room];
    } else {
        $id_room_new='NULL';
    }
    $query = str_replace("%id_room%",$id_room_new,$query);
    if(!empty($id_icon) && $id_icon!=0) {
        $id_icon_new = $icons_mapping[$id_icon];
    } else {
        $id_icon_new=0;
    }
    $query = str_replace("%id_icon_library%",$id_icon_new,$query);
    if(!empty($id_product) && $id_product!=0) {
        $id_product_new = $products_mapping[$id_product];
        $query = str_replace("%content%",$id_product_new,$query);
    }
    if(!empty($id_room_alt) && $id_room_alt!=0) {
        $query = str_replace("%content%",$id_room_alt,$query);
    }
    $query = query_mapping($query,$array_mapping);
    if($debug) {
        $date = date('Y-m-d H:i');
        file_put_contents(realpath(dirname(__FILE__))."/log_import.txt","$date - query: $query".PHP_EOL,FILE_APPEND);
    }
    $result=$mysqli->query($query);
    if($result) {
        $id_poi_new = $mysqli->insert_id;
        $pois_mapping[$id_poi]=$id_poi_new;
    } else {
        if($debug) {
            $date = date('Y-m-d H:i');
            file_put_contents(realpath(dirname(__FILE__))."/log_import.txt","$date - error: ".$mysqli->error.PHP_EOL,FILE_APPEND);
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

$query = "SELECT id,id_poi_autoopen FROM svt_rooms WHERE id_poi_autoopen IS NOT NULL;";
$result = $mysqli->query($query);
if($result) {
    if($result->num_rows>0) {
        while($row=$result->fetch_array(MYSQLI_ASSOC)) {
            $id = $row['id'];
            $id_poi_autoopen = $row['id_poi_autoopen'];
            $id_poi_autoopen_new = $pois_mapping[$id_poi_autoopen];
            $mysqli->query("UPDATE svt_rooms SET id_poi_autoopen=$id_poi_autoopen_new WHERE id=$id;");
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

$commands_import = get_commands($commands,'svt_rooms_alt');
foreach ($commands_import as $command_import) {
    $id_room_alt = $command_import['fields']['id'];
    $id_room = $command_import['fields']['id_room'];
    $query = $command_import['sql'];
    if(!empty($id_room) && $id_room!=0) {
        $id_room_new = $rooms_mapping[$id_room];
    } else {
        $id_room_new='NULL';
    }
    $query = str_replace("%id_room%",$id_room_new,$query);
    $query = query_mapping($query,$array_mapping);
    if($debug) {
        $date = date('Y-m-d H:i');
        file_put_contents(realpath(dirname(__FILE__))."/log_import.txt","$date - query: $query".PHP_EOL,FILE_APPEND);
    }
    $result = $mysqli->query($query);
    if($result) {
        $id_room_alt_new = $mysqli->insert_id;
        $rooms_alt_mapping[$id_room_alt]=$id_room_alt_new;
    } else {
        if($debug) {
            $date = date('Y-m-d H:i');
            file_put_contents(realpath(dirname(__FILE__))."/log_import.txt","$date - error: ".$mysqli->error.PHP_EOL,FILE_APPEND);
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

$query = "SELECT id,content FROM svt_pois WHERE type='switch_pano';";
$result = $mysqli->query($query);
if($result) {
    if($result->num_rows>0) {
        while($row=$result->fetch_array(MYSQLI_ASSOC)) {
            $id = $row['id'];
            $content = $row['content'];
            if(!empty($content) && $content!='0') {
                $id_room_alt_new = $rooms_alt_mapping[$content];
                $mysqli->query("UPDATE svt_pois SET content='$id_room_alt_new' WHERE id=$id;");
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

$commands_import = get_commands($commands,'svt_poi_gallery');
foreach ($commands_import as $command_import) {
    $id_poi = $command_import['fields']['id_poi'];
    $query = $command_import['sql'];
    if(!empty($id_poi) && $id_poi!=0) {
        $id_poi_new = $pois_mapping[$id_poi];
    } else {
        $id_poi_new='NULL';
    }
    $query = str_replace("%id_poi%",$id_poi_new,$query);
    $query = query_mapping($query,$array_mapping);
    if($debug) {
        $date = date('Y-m-d H:i');
        file_put_contents(realpath(dirname(__FILE__))."/log_import.txt","$date - query: $query".PHP_EOL,FILE_APPEND);
    }
    $result=$mysqli->query($query);
    if(!$result) {
        if($debug) {
            $date = date('Y-m-d H:i');
            file_put_contents(realpath(dirname(__FILE__))."/log_import.txt","$date - error: ".$mysqli->error.PHP_EOL,FILE_APPEND);
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

$commands_import = get_commands($commands,'svt_poi_embedded_gallery');
foreach ($commands_import as $command_import) {
    $id_poi = $command_import['fields']['id_poi'];
    $query = $command_import['sql'];
    if(!empty($id_poi) && $id_poi!=0) {
        $id_poi_new = $pois_mapping[$id_poi];
    } else {
        $id_poi_new='NULL';
    }
    $query = str_replace("%id_poi%",$id_poi_new,$query);
    $query = query_mapping($query,$array_mapping);
    if($debug) {
        $date = date('Y-m-d H:i');
        file_put_contents(realpath(dirname(__FILE__))."/log_import.txt","$date - query: $query".PHP_EOL,FILE_APPEND);
    }
    $result=$mysqli->query($query);
    if(!$result) {
        if($debug) {
            $date = date('Y-m-d H:i');
            file_put_contents(realpath(dirname(__FILE__))."/log_import.txt","$date - error: ".$mysqli->error.PHP_EOL,FILE_APPEND);
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

if(file_exists($path_import.'objects360')) {
    $files_objects360 = new RecursiveIteratorIterator(
        new RecursiveCallbackFilterIterator(
            new RecursiveDirectoryIterator($path_import.'objects360',RecursiveDirectoryIterator::SKIP_DOTS),
            function ($fileInfo, $key, $iterator) use ($filter) {
                return true;
            }
        )
    );
    foreach ($files_objects360 as $file) {
        $file_name = $file->getFilename();
        if($file_name=='import.json') continue;
        if(array_key_exists($file_name,$array_mapping)) {
            $file_name_new = $array_mapping[$file_name];
        } else {
            $id_poi = getStringBetween($file_name,"object360_","_");
            $id_poi_new = $pois_mapping[$id_poi];
            $file_name_new = str_replace("_".$id_poi."_","_".$id_poi_new."_",$file_name);
            $array_mapping[$file_name]=$file_name_new;
        }
        $source_file = $file->getPathname();
        $abs_path = str_replace($path_import,'',$file->getPath());
        $dest_file = $path_dest.$abs_path.DIRECTORY_SEPARATOR.$file_name_new;
        copy($source_file,$dest_file);
    }
}

$mysqli->close();
$mysqli = new mysqli(DATABASE_HOST, DATABASE_USERNAME, DATABASE_PASSWORD, DATABASE_NAME);
if (mysqli_connect_errno()) {
    echo mysqli_connect_error();
    exit();
}
$mysqli->query("SET NAMES 'utf8';");

$commands_import = get_commands($commands,'svt_poi_objects360');
foreach ($commands_import as $command_import) {
    $id_poi = $command_import['fields']['id_poi'];
    $query = $command_import['sql'];
    if(!empty($id_poi) && $id_poi!=0) {
        $id_poi_new = $pois_mapping[$id_poi];
    } else {
        $id_poi_new='NULL';
    }
    $query = str_replace("%id_poi%",$id_poi_new,$query);
    $query = query_mapping($query,$array_mapping);
    if($debug) {
        $date = date('Y-m-d H:i');
        file_put_contents(realpath(dirname(__FILE__))."/log_import.txt","$date - query: $query".PHP_EOL,FILE_APPEND);
    }
    $result=$mysqli->query($query);
    if(!$result) {
        if($debug) {
            $date = date('Y-m-d H:i');
            file_put_contents(realpath(dirname(__FILE__))."/log_import.txt","$date - error: ".$mysqli->error.PHP_EOL,FILE_APPEND);
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

$commands_import = get_commands($commands,'svt_markers');
foreach ($commands_import as $command_import) {
    $id_marker = $command_import['fields']['id'];
    $id_room = $command_import['fields']['id_room'];
    $id_room_target = $command_import['fields']['id_room_target'];
    $id_icon = $command_import['fields']['id_icon_library'];
    $query = $command_import['sql'];
    if(!empty($id_room) && $id_room!=0) {
        $id_room_new = $rooms_mapping[$id_room];
    } else {
        $id_room_new='NULL';
    }
    $query = str_replace("%id_room%",$id_room_new,$query);
    if(!empty($id_room_target) && $id_room_target!=0) {
        $id_room_new = $rooms_mapping[$id_room_target];
    } else {
        $id_room_new='NULL';
    }
    $query = str_replace("%id_room_target%",$id_room_new,$query);
    if(!empty($id_icon) && $id_icon!=0) {
        $id_icon_new = $icons_mapping[$id_icon];
    } else {
        $id_icon_new=0;
    }
    $query = str_replace("%id_icon_library%",$id_icon_new,$query);
    $query = query_mapping($query,$array_mapping);
    if($debug) {
        $date = date('Y-m-d H:i');
        file_put_contents(realpath(dirname(__FILE__))."/log_import.txt","$date - query: $query".PHP_EOL,FILE_APPEND);
    }
    $result=$mysqli->query($query);
    if($result) {
        $id_markers_new = $mysqli->insert_id;
        $markers_mapping[$id_marker]=$id_markers_new;
    } else {
        if($debug) {
            $date = date('Y-m-d H:i');
            file_put_contents(realpath(dirname(__FILE__))."/log_import.txt","$date - error: ".$mysqli->error.PHP_EOL,FILE_APPEND);
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

$commands_import = get_commands($commands,'svt_presentations');
foreach ($commands_import as $command_import) {
    $id_room = $command_import['fields']['id_room'];
    $query = $command_import['sql'];
    $query = str_replace("%id_virtualtour%",$id_vt,$query);
    if(!empty($id_room) && $id_room!=0) {
        $id_room_new = $rooms_mapping[$id_room];
    } else {
        $id_room_new='NULL';
    }
    $query = str_replace("%id_room%",$id_room_new,$query);
    $query = str_replace("%params%",$id_room_new,$query);
    if($debug) {
        $date = date('Y-m-d H:i');
        file_put_contents(realpath(dirname(__FILE__))."/log_import.txt","$date - query: $query".PHP_EOL,FILE_APPEND);
    }
    $result=$mysqli->query($query);
    if(!$result) {
        if($debug) {
            $date = date('Y-m-d H:i');
            file_put_contents(realpath(dirname(__FILE__))."/log_import.txt","$date - error: ".$mysqli->error.PHP_EOL,FILE_APPEND);
        }
    }
}

if(!$sample) {
    unlink($file_zip);
    rrmdir($path_import);
}

$mysqli->close();
$mysqli = new mysqli(DATABASE_HOST, DATABASE_USERNAME, DATABASE_PASSWORD, DATABASE_NAME);
if (mysqli_connect_errno()) {
    echo mysqli_connect_error();
    exit();
}
$mysqli->query("SET NAMES 'utf8';");
ob_end_clean();
echo json_encode(array("status"=>"ok"));

function get_commands($commands,$table) {
    $return = [];
    foreach ($commands as $command) {
        $table_c=$command['table'];
        if($table==$table_c) {
            array_push($return,$command);
        }
    }
    return $return;
}

function getStringBetween($str,$from,$to) {
    $sub = substr($str, strpos($str,$from)+strlen($from),strlen($str));
    return substr($sub,0,strpos($sub,$to));
}

function query_mapping($query,$array_mapping) {
    foreach ($array_mapping as $source_name=>$dest_name) {
        $query = str_replace($source_name,$dest_name,$query);
    }
    return $query;
}

function rrmdir($dir) {
    if (is_dir($dir)) {
        $objects = scandir($dir);
        foreach ($objects as $object) {
            if ($object != "." && $object != "..") {
                if (is_dir($dir. DIRECTORY_SEPARATOR .$object) && !is_link($dir."/".$object))
                    rrmdir($dir. DIRECTORY_SEPARATOR .$object);
                else
                    unlink($dir. DIRECTORY_SEPARATOR .$object);
            }
        }
        rmdir($dir);
    }
}