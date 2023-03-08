<?php
error_reporting(E_ALL & ~E_WARNING & ~E_NOTICE & ~E_DEPRECATED);
ob_start();
session_start();
if($_SESSION['svt_si']!=session_id()) {
    die();
}

require_once("../../db/connection.php");
$id_virtualtour = $_POST['id_virtualtour'];

$array = array();
if(empty($id_virtualtour)) {
    $query = "SELECT id,file,id_virtualtour FROM svt_music_library WHERE id_virtualtour IS NULL ORDER BY id DESC;";
} else {
    $query = "SELECT id,file,id_virtualtour FROM svt_music_library WHERE id_virtualtour=$id_virtualtour OR id_virtualtour IS NULL ORDER BY id DESC;";
}
$result = $mysqli->query($query);
if($result) {
    if($result->num_rows>0) {
        while($row=$result->fetch_array(MYSQLI_ASSOC)) {
            if(empty($row['id_virtualtour'])) $row['id_virtualtour']='';
            $path_file = __DIR__.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'viewer'.DIRECTORY_SEPARATOR.'content'.DIRECTORY_SEPARATOR.$row['file'];
            if(file_exists($path_file)) {
                $row['count']=0;
                $array[]=$row;
            }
        }
    }
}

$query = "SELECT 0 as id,song as file FROM svt_virtualtours WHERE song!='' AND song IS NOT NULL AND id=$id_virtualtour;";
$result = $mysqli->query($query);
if($result) {
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_array(MYSQLI_ASSOC)) {
            $path_file = __DIR__.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'viewer'.DIRECTORY_SEPARATOR.'content'.DIRECTORY_SEPARATOR.$row['file'];
            if(file_exists($path_file)) {
                $index = searchForFile($row['file'],$array);
                if($index!=false) {
                    $array[$index]['count']=$array[$index]['count']+1;
                } else {
                    $row['count']=1;
                    $array[]=$row;
                }
            }
        }
    }
}

$query = "SELECT 0 as id,song as file FROM svt_rooms WHERE song!='' AND song IS NOT NULL AND id_virtualtour=$id_virtualtour;";
$result = $mysqli->query($query);
if($result) {
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_array(MYSQLI_ASSOC)) {
            $path_file = __DIR__.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'viewer'.DIRECTORY_SEPARATOR.'content'.DIRECTORY_SEPARATOR.$row['file'];
            if(file_exists($path_file)) {
                $index = searchForFile($row['file'],$array);
                if($index!=false) {
                    $array[$index]['count']=$array[$index]['count']+1;
                } else {
                    $row['count']=1;
                    $array[]=$row;
                }
            }
        }
    }
}

$query = "SELECT 0 as id,content as file FROM svt_pois WHERE type IN ('audio') AND content LIKE 'content/%' AND id_room IN (SELECT id FROM svt_rooms WHERE id_virtualtour=$id_virtualtour);";
$result = $mysqli->query($query);
if($result) {
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_array(MYSQLI_ASSOC)) {
            $row['file'] = str_replace('content/','',$row['file']);
            $path_file = __DIR__.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'viewer'.DIRECTORY_SEPARATOR.'content'.DIRECTORY_SEPARATOR.$row['file'];
            if(file_exists($path_file)) {
                $index = searchForFile($row['file'],$array);
                if($index!=false) {
                    $array[$index]['count']=$array[$index]['count']+1;
                } else {
                    $row['count']=1;
                    $array[]=$row;
                }
            }
        }
    }
}

function searchForFile($file, $array) {
    foreach ($array as $key => $val) {
        if ($val['file'] === $file) {
            return $key;
        }
    }
    return false;
}

ob_end_clean();
echo json_encode($array);