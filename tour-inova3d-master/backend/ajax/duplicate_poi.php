<?php
error_reporting(E_ALL & ~E_WARNING & ~E_NOTICE & ~E_DEPRECATED);
ob_start();
session_start();
if((($_SERVER['SERVER_ADDR']=='5.9.29.89') && ($_SERVER['REMOTE_ADDR']!=$_SESSION['ip_developer']) && ($_SESSION['id_user']==1)) || ($_SESSION['svt_si']!=session_id())) {
    die();
}
ini_set('max_execution_time', 9999);
require_once("../../db/connection.php");

$id_poi = $_POST['id_poi'];
$id_room_target = $_POST['id_room_target'];

$mysqli->query("CREATE TEMPORARY TABLE svt_poi_tmp SELECT * FROM svt_pois WHERE id = $id_poi;");
$mysqli->query("UPDATE svt_poi_tmp SET id=(SELECT MAX(id)+1 as id FROM svt_pois),access_count=0,id_room=$id_room_target;");
$mysqli->query("INSERT INTO svt_pois SELECT * FROM svt_poi_tmp;");
$id_poi_new = $mysqli->insert_id;
$mysqli->query("DROP TEMPORARY TABLE IF EXISTS svt_poi_tmp;");

$result = $mysqli->query("SELECT id FROM svt_poi_gallery WHERE id_poi=$id_poi;");
if($result) {
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_array(MYSQLI_ASSOC)) {
            $id_poi_gallery = $row['id'];
            $mysqli->query("CREATE TEMPORARY TABLE svt_poi_gallery_tmp SELECT * FROM svt_poi_gallery WHERE id = $id_poi_gallery;");
            $mysqli->query("UPDATE svt_poi_gallery_tmp SET id=(SELECT MAX(id)+1 as id FROM svt_poi_gallery),id_poi=$id_poi_new;");
            $mysqli->query("INSERT INTO svt_poi_gallery SELECT * FROM svt_poi_gallery_tmp;");
            $mysqli->query("DROP TEMPORARY TABLE IF EXISTS svt_poi_gallery_tmp;");
        }
    }
}
$result = $mysqli->query("SELECT id FROM svt_poi_embedded_gallery WHERE id_poi=$id_poi;");
if($result) {
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_array(MYSQLI_ASSOC)) {
            $id_poi_embedded_gallery = $row['id'];
            $mysqli->query("CREATE TEMPORARY TABLE svt_poi_embedded_gallery_tmp SELECT * FROM svt_poi_embedded_gallery WHERE id = $id_poi_embedded_gallery;");
            $mysqli->query("UPDATE svt_poi_embedded_gallery_tmp SET id=(SELECT MAX(id)+1 as id FROM svt_poi_embedded_gallery),id_poi=$id_poi_new;");
            $mysqli->query("INSERT INTO svt_poi_embedded_gallery SELECT * FROM svt_poi_embedded_gallery_tmp;");
            $mysqli->query("DROP TEMPORARY TABLE IF EXISTS svt_poi_embedded_gallery_tmp;");
        }
    }
}
$result = $mysqli->query("SELECT id FROM svt_poi_objects360 WHERE id_poi=$id_poi;");
if($result) {
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_array(MYSQLI_ASSOC)) {
            $id_poi_object360 = $row['id'];
            $mysqli->query("CREATE TEMPORARY TABLE svt_poi_objects360_tmp SELECT * FROM svt_poi_objects360 WHERE id = $id_poi_object360;");
            $mysqli->query("UPDATE svt_poi_objects360_tmp SET id=(SELECT MAX(id)+1 as id FROM svt_poi_objects360),id_poi=$id_poi_new;");
            $mysqli->query("INSERT INTO svt_poi_objects360 SELECT * FROM svt_poi_objects360_tmp;");
            $mysqli->query("DROP TEMPORARY TABLE IF EXISTS svt_poi_objects360_tmp;");
        }
    }
}

ob_end_clean();
echo json_encode(array("status"=>"ok","id"=>$id_poi_new));

