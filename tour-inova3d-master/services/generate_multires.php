<?php
error_reporting(E_ALL & ~E_WARNING & ~E_NOTICE & ~E_DEPRECATED);
session_start();
ini_set("memory_limit",-1);
ini_set('max_execution_time', 9999);
set_time_limit(9999);
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
require_once(__DIR__."/../db/connection.php");
require_once(__DIR__."/../backend/functions.php");
$mysqli->query("SET session wait_timeout=3600");
$debug = false;
$path = realpath(dirname(__FILE__) . '/..');
if(isset($_GET['check_req'])) {
    $check_req = 1;
} else {
    $check_req = 0;
}
if($check_req==1) {
    $settings = get_settings();
    $user_info = get_user_info($_SESSION['id_user']);
    if(!empty($user_info['language'])) {
        set_language($user_info['language'],$settings['language_domain']);
    } else {
        set_language($settings['language'],$settings['language_domain']);
    }
}
if(isset($argv[1])) {
    if($argv[1]==1) {
        $force_update = 1;
    } else {
        $force_update = 0;
    }
} else {
    $force_update = 0;
}
if(isset($argv[2])) {
    $id_virtualtour = $argv[2];
    $where = "AND id = $id_virtualtour";
} else {
    $where = "";
}

if($debug) {
    $date = date('Y-m-d H:i');
    file_put_contents(realpath(dirname(__FILE__))."/log_multires.txt","$date - check_req: $check_req, force_update: $force_update, id_virtualtour: $id_virtualtour".PHP_EOL,FILE_APPEND);
}

if(!isEnabled('shell_exec')) {
    echo json_encode(array("status"=>"error","msg"=>"php <b>shell_exec</b> "._("function disabled")));
    exit;
}

$command = 'dpkg-query -W -f=\'${Status}\' python3 2>&1';
$output = shell_exec($command);
if (strpos($output, 'command not found') !== false) {
    $command = 'rpm -q python3 2>&1';
    $output = shell_exec($command);
    if (strpos($output, 'not installed') !== false) {
        echo json_encode(array("status"=>"error","msg"=>_("Missing package")." <b>python3</b>.<br>"._("Execute the command")." \"apt-get install python3\" "._("on your server")."."));
        exit;
    }
} else {
    if (strpos($output, 'installed') === false) {
        echo json_encode(array("status"=>"error","msg"=>_("Missing package")." <b>python3</b>.<br>"._("Execute the command")." \"apt-get install python3\" "._("on your server")."."));
        exit;
    }
}
$command = 'dpkg-query -W -f=\'${Status}\' python3-pil 2>&1';
$output = shell_exec($command);
if (strpos($output, 'command not found') !== false) {
    $command = 'rpm -q python3-pil 2>&1';
    $output = shell_exec($command);
    if (strpos($output, 'not installed') !== false) {
        echo json_encode(array("status"=>"error","msg"=>_("Missing package")." <b>python3-pil</b>.<br>"._("Execute the command")." \"apt-get install python3-pil\" "._("on your server")."."));
        exit;
    }
} else {
    if (strpos($output, 'installed') === false) {
        echo json_encode(array("status"=>"error","msg"=>_("Missing package")." <b>python3-pil</b>.<br>"._("Execute the command")." \"apt-get install python3-pil\" "._("on your server")."."));
        exit;
    }
}
$command = 'dpkg-query -W -f=\'${Status}\' python3-numpy 2>&1';
$output = shell_exec($command);
if (strpos($output, 'command not found') !== false) {
    $command = 'rpm -q python3-numpy 2>&1';
    $output = shell_exec($command);
    if (strpos($output, 'not installed') !== false) {
        echo json_encode(array("status"=>"error","msg"=>_("Missing package")." <b>python3-numpy</b>.<br>"._("Execute the command")." \"apt-get install python3-numpy\" "._("on your server")."."));
        exit;
    }
} else {
    if (strpos($output, 'installed') === false) {
        echo json_encode(array("status"=>"error","msg"=>_("Missing package")." <b>python3-numpy</b>.<br>"._("Execute the command")." \"apt-get install python3-numpy\" "._("on your server")."."));
        exit;
    }
}
$command = 'dpkg-query -W -f=\'${Status}\' python3-pip 2>&1';
$output = shell_exec($command);
if (strpos($output, 'command not found') !== false) {
    $command = 'rpm -q python3-pip 2>&1';
    $output = shell_exec($command);
    if (strpos($output, 'not installed') !== false) {
        echo json_encode(array("status"=>"error","msg"=>_("Missing package")." <b>python3-pip</b>.<br>"._("Execute the command")." \"apt-get install python3-pip\" "._("on your server")."."));
        exit;
    }
} else {
    if (strpos($output, 'installed') === false) {
        echo json_encode(array("status"=>"error","msg"=>_("Missing package")." <b>python3-pip</b>.<br>"._("Execute the command")." \"apt-get install python3-pip\" "._("on your server")."."));
        exit;
    }
}
$command = 'dpkg-query -W -f=\'${Status}\' hugin-tools 2>&1';
$output = shell_exec($command);
if (strpos($output, 'command not found') !== false) {
    $command = 'rpm -q hugin-tools 2>&1';
    $output = shell_exec($command);
    if (strpos($output, 'not installed') !== false) {
        echo json_encode(array("status"=>"error","msg"=>_("Missing package")." <b>hugin-tools</b>.<br>"._("Execute the command")." \"apt-get install hugin-tools\" "._("on your server")."."));
        exit;
    }
} else {
    if (strpos($output, 'installed') === false) {
        echo json_encode(array("status"=>"error","msg"=>_("Missing package")." <b>hugin-tools</b>.<br>"._("Execute the command")." \"apt-get install hugin-tools\" "._("on your server")."."));
        exit;
    }
}

$command = 'pip3 list | grep -F pyshtools 2>&1';
$output = shell_exec($command);
if (strpos($output, 'pyshtools') === false) {
    echo json_encode(array("status"=>"error","msg"=>_("Missing package")." <b>pyshtools</b>.<br>"._("Execute the command")." \"sudo pip3 install pyshtools\" "._("on your server")."."));
    exit;
}

$command = 'command -v nona 2>&1';
$output = shell_exec($command);
if($output=="") {
    echo json_encode(array("status"=>"error","msg"=>_("Missing command")." nona."));
    exit;
} else {
    $path_nona = trim($output);
}

$command = 'command -v python3 2>&1';
$output = shell_exec($command);
if($output=="") {
    echo json_encode(array("status"=>"error","msg"=>_("Missing command")." python3."));
    exit;
} else {
    $path_python = trim($output);
}

if($check_req)  {
    echo json_encode(array("status"=>"ok","msg"=>_("All requirements are met.")));
    exit;
}

if (!file_exists(dirname(__FILE__).'/../viewer/panoramas/multires/')) {
    mkdir(dirname(__FILE__).'/../viewer/panoramas/multires/', 0775);
}

$array_rooms = array();
$query_vt = "SELECT id,compress_jpg FROM svt_virtualtours WHERE enable_multires=1 $where;";
$result_vt = $mysqli->query($query_vt);
if($result_vt) {
    if ($result_vt->num_rows>0) {
        while($row_vt = $result_vt->fetch_array(MYSQLI_ASSOC)) {
            $id_vt = $row_vt['id'];
            $quality = $row_vt['compress_jpg'];
            if(empty($quality)) $quality = 100;
            if($force_update==1) {
                $query = "SELECT id,panorama_image,blur,haov,vaov FROM svt_rooms WHERE id_virtualtour=$id_vt AND type='image';";
            } else {
                $query = "SELECT id,panorama_image,blur,haov,vaov FROM svt_rooms WHERE id_virtualtour=$id_vt AND multires_status=0 AND type='image';";
            }
            $result = $mysqli->query($query);
            if($result) {
                if ($result->num_rows>0) {
                    while ($row = $result->fetch_array(MYSQLI_ASSOC)) {
                        $row['t']='room';
                        $array_rooms[] = $row;
                        $id_room = $row['id'];
                        $mysqli->query("UPDATE svt_rooms SET multires_status=1 WHERE id=$id_room;");
                    }
                }
            }
            if($force_update==1) {
                $query_ra = "SELECT ra.id,ra.panorama_image,0 as blur,r.haov,r.vaov FROM svt_rooms_alt as ra JOIN svt_rooms as r ON r.id=ra.id_room WHERE ra.id_room IN (SELECT id FROM svt_rooms WHERE id_virtualtour=$id_vt);";
            } else {
                $query_ra = "SELECT ra.id,ra.panorama_image,0 as blur,r.haov,r.vaov FROM svt_rooms_alt as ra JOIN svt_rooms as r ON r.id=ra.id_room WHERE ra.id_room IN (SELECT id FROM svt_rooms WHERE id_virtualtour=$id_vt) AND ra.multires_status=0;";
            }
            $result_ra = $mysqli->query($query_ra);
            if($result_ra) {
                if ($result_ra->num_rows>0) {
                    while ($row_ra = $result_ra->fetch_array(MYSQLI_ASSOC)) {
                        $row_ra['t']='room_alt';
                        $array_rooms[] = $row_ra;
                        $id_room_alt = $row_ra['id'];
                        $mysqli->query("UPDATE svt_rooms_alt SET multires_status=1 WHERE id=$id_room_alt;");
                    }
                }
            }
        }
    }
}
foreach ($array_rooms as $room) {
    $id_room = $room['id'];
    $t = $room['t'];
    $blur = $room['blur'];
    $vaov = $room['vaov'];
    $haov = $room['haov'];
    if($blur==1) {
        $quality_t = 100;
    } else {
        $quality_t = $quality;
    }
    $pano = str_replace(".jpg","",$room['panorama_image']);
    if($force_update==1) {
        $command = "rm -R ".$path.DIRECTORY_SEPARATOR."viewer".DIRECTORY_SEPARATOR."panoramas".DIRECTORY_SEPARATOR."multires".DIRECTORY_SEPARATOR.$pano;
        shell_exec($command);
    }
    if($blur==0 && file_exists($path.DIRECTORY_SEPARATOR."viewer".DIRECTORY_SEPARATOR."panoramas".DIRECTORY_SEPARATOR."original".DIRECTORY_SEPARATOR.$pano.".jpg")) {
        $pano_path = $path.DIRECTORY_SEPARATOR."viewer".DIRECTORY_SEPARATOR."panoramas".DIRECTORY_SEPARATOR."original".DIRECTORY_SEPARATOR.$pano.".jpg";
    } else {
        $pano_path = $path.DIRECTORY_SEPARATOR."viewer".DIRECTORY_SEPARATOR."panoramas".DIRECTORY_SEPARATOR.$pano.".jpg";
    }
    $command = $path_python." ".$path.DIRECTORY_SEPARATOR."services".DIRECTORY_SEPARATOR."generate.py --output ".$path.DIRECTORY_SEPARATOR."viewer".DIRECTORY_SEPARATOR."panoramas".DIRECTORY_SEPARATOR."multires".DIRECTORY_SEPARATOR."$pano --haov $haov.0 --vaov $vaov.0 --nona $path_nona --quality $quality_t --thumbnailsize 256 $pano_path 2>&1";
    if($debug) {
        $date = date('Y-m-d H:i');
        file_put_contents(realpath(dirname(__FILE__))."/log_multires.txt",$date." - ".$command.PHP_EOL,FILE_APPEND);
    }
    $output = shell_exec($command);
    if($debug) {
        $date = date('Y-m-d H:i');
        file_put_contents(realpath(dirname(__FILE__))."/log_multires.txt",$date." - ".$output.PHP_EOL,FILE_APPEND);
    }
    if(file_exists($path.DIRECTORY_SEPARATOR."viewer".DIRECTORY_SEPARATOR."panoramas".DIRECTORY_SEPARATOR."multires".DIRECTORY_SEPARATOR.$pano.DIRECTORY_SEPARATOR."config.json")) {
        if($t=='room') {
            $query = "UPDATE svt_rooms SET multires_status=2 WHERE id=$id_room;";
        } else {
            $query = "UPDATE svt_rooms_alt SET multires_status=2 WHERE id=$id_room;";
        }
    } else {
        if($t=='room') {
            $query = "UPDATE svt_rooms SET multires_status=0 WHERE id=$id_room;";
        } else {
            $query = "UPDATE svt_rooms_alt SET multires_status=0 WHERE id=$id_room;";
        }
    }
    if($debug) {
        $date = date('Y-m-d H:i');
        file_put_contents(realpath(dirname(__FILE__))."/log_multires.txt",$date." - ".$query.PHP_EOL,FILE_APPEND);
    }
    $result = $mysqli->query($query);
    if(!$result) {
        if($debug) {
            $date = date('Y-m-d H:i');
            file_put_contents(realpath(dirname(__FILE__))."/log_multires.txt",$date." - ".$mysqli->error.PHP_EOL,FILE_APPEND);
        }
    }
}