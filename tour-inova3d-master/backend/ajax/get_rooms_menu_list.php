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

if(get_user_role($id_user)=='administrator') {
    $where_user = "";
} else {
    $where_user = " AND v.id_user = $id_user ";
}

$array = array();
$query = "SELECT r.id,r.name FROM svt_rooms as r 
JOIN svt_virtualtours as v ON v.id = r.id_virtualtour
WHERE v.id = $id_virtualtour $where_user
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
        if ($list_alt == '') {
            foreach ($array as $id=>$name) {
                array_push($array2,["id"=>$id,"type"=>"room","hide"=>"0","name"=>$name]);
            }
        } else {
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
echo json_encode($array2);