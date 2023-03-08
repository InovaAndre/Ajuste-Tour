<?php
error_reporting(E_ALL & ~E_WARNING & ~E_NOTICE & ~E_DEPRECATED);
ob_start();
session_start();
if($_SESSION['svt_si']!=session_id()) {
    die();
}

require_once("../../db/connection.php");
require_once("../functions.php");
$id_virtualtour = $_POST['id_virtualtour'];
$id_user = $_SESSION['id_user'];

$array = array();
$permissions = array();
if(get_user_role($id_user)=="editor") {
    $editor_permissions = get_editor_permissions($id_user,$id_virtualtour);
    if($editor_permissions['edit_maps']==1) {
        $permissions['edit'] = true;
    } else {
        $permissions['edit'] = false;
    }
    if($editor_permissions['delete_maps']==1) {
        $permissions['delete'] = true;
    } else {
        $permissions['delete'] = false;
    }
} else {
    $permissions['edit'] = true;
    $permissions['delete'] = true;
}

$query = "SELECT m.*,IF(m.map_type='map',(SELECT COUNT(*) FROM svt_rooms WHERE id_virtualtour=$id_virtualtour AND lat IS NOT NULL AND lat !=''),(SELECT COUNT(*) FROM svt_rooms WHERE id_map=m.id)) as count_rooms FROM svt_maps as m 
WHERE m.id_virtualtour=$id_virtualtour ORDER BY m.map_type DESC,m.priority ASC, m.id ASC;";
$result = $mysqli->query($query);
if($result) {
    if($result->num_rows>0) {
        while($row=$result->fetch_array(MYSQLI_ASSOC)) {
            $array[]=$row;
        }
    }
}
ob_end_clean();
echo json_encode(array("maps"=>$array,"permissions"=>$permissions));