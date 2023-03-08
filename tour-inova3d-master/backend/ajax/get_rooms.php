<?php
error_reporting(E_ALL & ~E_WARNING & ~E_NOTICE & ~E_DEPRECATED);
ob_start();
session_start();
if($_SESSION['svt_si']!=session_id()) {
    die();
}

require_once("../../db/connection.php");
require_once("../functions.php");
$id_user = $_POST['id_user'];
$id_virtualtour = $_POST['id_virtualtour'];

switch(get_user_role($id_user)) {
    case 'administrator':
        $where = "";
        break;
    case 'customer':
        $where = " AND v.id_user=$id_user ";
        break;
    case 'editor':
        $where = " AND v.id IN () ";
        $query = "SELECT GROUP_CONCAT(id_virtualtour) as ids FROM svt_assign_virtualtours WHERE id_user=$id_user;";
        $result = $mysqli->query($query);
        if($result) {
            if($result->num_rows==1) {
                $row=$result->fetch_array(MYSQLI_ASSOC);
                $ids = $row['ids'];
                $where = " AND v.id IN ($ids) ";
            }
        }
        break;
}

$array_rooms = array();
$permissions = array();
if(get_user_role($id_user)=="editor") {
    $editor_permissions = get_editor_permissions($id_user,$id_virtualtour);
    if($editor_permissions['create_rooms']==1) {
        $permissions['create'] = true;
    } else {
        $permissions['create'] = false;
    }
    if($editor_permissions['edit_rooms']==1) {
        $permissions['edit'] = true;
    } else {
        $permissions['edit'] = false;
    }
    if($editor_permissions['delete_rooms']==1) {
        $permissions['delete'] = true;
    } else {
        $permissions['delete'] = false;
    }
} else {
    $permissions['create'] = true;
    $permissions['edit'] = true;
    $permissions['delete'] = true;
}

$query = "SELECT r.id,r.name,r.type,r.panorama_image,r.thumb_image,(SELECT COUNT(*) FROM svt_markers WHERE id_room=r.id) as count_markers,(SELECT COUNT(*) FROM svt_pois WHERE id_room=r.id) as count_pois,r.multires_status,v.enable_multires,r.yaw,r.pitch,r.h_pitch,r.h_roll,r.allow_pitch,r.min_pitch,r.max_pitch,r.min_yaw,r.max_yaw,r.haov,r.vaov FROM svt_rooms as r 
JOIN svt_virtualtours as v ON v.id = r.id_virtualtour
WHERE v.id = $id_virtualtour $where
GROUP BY r.id
ORDER BY r.priority ASC, r.id ASC";
$result = $mysqli->query($query);
if($result) {
    if($result->num_rows>0) {
        while($row=$result->fetch_array(MYSQLI_ASSOC)) {
            if($row['enable_multires']) {
                $room_pano = str_replace('.jpg','',$row['panorama_image']);
                $multires_config_file = __DIR__.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'viewer'.DIRECTORY_SEPARATOR.'panoramas'.DIRECTORY_SEPARATOR.'multires'.DIRECTORY_SEPARATOR.$room_pano.DIRECTORY_SEPARATOR.'config.json';
                if(file_exists($multires_config_file)) {
                    $row['multires']=1;
                } else {
                    $row['multires']=0;
                }
            } else {
                $row['multires']=0;
            }
            $thumb_image_url = "../viewer/panoramas/thumb/".$row['panorama_image'];
            if(file_exists(__DIR__.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'viewer'.DIRECTORY_SEPARATOR.'panoramas'.DIRECTORY_SEPARATOR.'preview'.DIRECTORY_SEPARATOR.$row['panorama_image'])) {
                $thumb_image_url = "../viewer/panoramas/preview/".$row['panorama_image'];
            }
            if(!empty($row['thumb_image'])) {
                if(file_exists(__DIR__.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'viewer'.DIRECTORY_SEPARATOR.'panoramas'.DIRECTORY_SEPARATOR.'thumb_custom'.DIRECTORY_SEPARATOR.$row['thumb_image'])) {
                    $thumb_image_url = "../viewer/panoramas/thumb_custom/".$row['thumb_image'];
                }
            }
            $row['thumb_image_url']=$thumb_image_url;
            $row['category']='';
            $array_rooms[]=$row;
        }
    }
}

$array = array();
$query = "SELECT r.id,r.name FROM svt_rooms as r 
JOIN svt_virtualtours as v ON v.id = r.id_virtualtour
WHERE v.id = $id_virtualtour
GROUP BY r.id
ORDER BY r.priority ASC, r.id ASC";
$result = $mysqli->query($query);
if($result) {
    if($result->num_rows>0) {
        while($row=$result->fetch_array(MYSQLI_ASSOC)) {
            $array[$row['id']]=$row['name'];
        }
    }
}

$array2 = array();
$array_id_rooms = array();
$query = "SELECT list_alt FROM svt_virtualtours WHERE id=$id_virtualtour LIMIT 1;";
$result = $mysqli->query($query);
if($result) {
    if($result->num_rows==1) {
        $row = $result->fetch_array(MYSQLI_ASSOC);
        $list_alt = $row['list_alt'];
        if (!empty($list_alt)) {
            $list_alt_array = json_decode($list_alt, true);
            foreach ($list_alt_array as $item) {
                switch ($item['type']) {
                    case 'room':
                        if(array_key_exists($item['id'],$array)) {
                            array_push($array2, ["id" => $item['id'], "type" => "room", "hide"=>$item['hide'], "name" => $array[$item['id']]]);
                        }
                        array_push($array_id_rooms,$item['id']);
                        break;
                    case 'category':
                        $childrens = array();
                        foreach ($item['children'] as $children) {
                            if ($children['type'] == "room") {
                                if(array_key_exists($children['id'],$array)) {
                                    array_push($childrens, ["id" => $children['id'], "type" => "room", "hide" => $children['hide'], "name" => $array[$children['id']]]);
                                    foreach ($array_rooms as $key_t => $room_t) {
                                        if($room_t['id']==$children['id']) {
                                            $array_rooms[$key_t]['category']=$item['cat'];
                                        }
                                    }
                                }
                                array_push($array_id_rooms, $children['id']);
                            }
                        }
                        array_push($array2, ["id" => $item['id'], "type" => "category", "name" => $item['cat'], "childrens" => $childrens]);
                        break;
                }
            }
            foreach ($array as $id=>$name) {
                if(!in_array($id,$array_id_rooms)) {
                    array_push($array2,["id"=>$id,"type"=>"room","hide"=>"0","name"=>$name]);
                }
            }
        }
    }
}

ob_end_clean();
echo json_encode(array("rooms"=>$array_rooms,"permissions"=>$permissions));