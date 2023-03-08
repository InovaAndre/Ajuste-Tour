<?php
error_reporting(E_ALL & ~E_WARNING & ~E_NOTICE & ~E_DEPRECATED);
ob_start();
session_start();
if((($_SERVER['SERVER_ADDR']=='5.9.29.89') && ($_SERVER['REMOTE_ADDR']!=$_SESSION['ip_developer']) && ($_SESSION['id_user']==1)) || ($_SESSION['svt_si']!=session_id())) {
    die();
}
ini_set('max_execution_time', 9999);
require_once("../../db/connection.php");
require_once("../functions.php");

$duplicate_maps = $_POST['duplicate_maps'];
$duplicate_gallery = $_POST['duplicate_gallery'];
$duplicate_info_box = $_POST['duplicate_info_box'];
$duplicate_presentation = $_POST['duplicate_presentation'];
$duplicate_rooms = $_POST['duplicate_rooms'];
$duplicate_pois = $_POST['duplicate_pois'];
$duplicate_markers = $_POST['duplicate_markers'];
$duplicate_products = $_POST['duplicate_products'];

$settings = get_settings();
$user_info = get_user_info($_SESSION['id_user']);
if(!empty($user_info['language'])) {
    set_language($user_info['language'],$settings['language_domain']);
} else {
    set_language($settings['language'],$settings['language_domain']);
}

$id_user = $_POST['id_user'];
$id_virtualtour = $_POST['id_virtualtour'];

if(get_user_role($id_user)=='administrator') {
    $query = "SELECT * FROM svt_virtualtours WHERE id=$id_virtualtour; ";
} else {
    $query = "SELECT * FROM svt_virtualtours WHERE id_user=$id_user AND id=$id_virtualtour; ";
}
$result = $mysqli->query($query);
if($result) {
    if($result->num_rows==0) {
        ob_end_clean();
        echo json_encode(array("status"=>"unauthorized"));
        exit;
    }
}

$duplicated_label = _("duplicated");

$mysqli->query("CREATE TEMPORARY TABLE svt_virtualtour_tmp SELECT * FROM svt_virtualtours WHERE id = $id_virtualtour;");
$add_q = "";
if(!$duplicate_info_box) {
    $add_q =",info_box=NULL";
}
$mysqli->query("UPDATE svt_virtualtour_tmp SET id=(SELECT MAX(id)+1 as id FROM svt_virtualtours),name=CONCAT(name,' ($duplicated_label)'),date_created=NOW(),ga_tracking_id=NULL,friendly_url=NULL $add_q;");
$mysqli->query("INSERT INTO svt_virtualtours SELECT * FROM svt_virtualtour_tmp;");
$id_virtualtour_new = $mysqli->insert_id;
$code_new = md5($id_virtualtour_new);
$mysqli->query("UPDATE svt_virtualtours SET code='$code_new' WHERE id=$id_virtualtour_new;");
$mysqli->query("DROP TEMPORARY TABLE IF EXISTS svt_virtualtours_tmp;");

$array_rooms = array();
$array_rooms_alt = array();
$array_maps = array();
$array_products = array();
$id_room_default_mapping = array();

if($duplicate_maps) {
    $result = $mysqli->query("SELECT id,id_room_default FROM svt_maps WHERE id_virtualtour=$id_virtualtour;");
    if($result) {
        if($result->num_rows>0) {
            while($row = $result->fetch_array(MYSQLI_ASSOC)) {
                $id_map = $row['id'];
                $id_room_default = $row['id_room_default'];
                if(!empty($id_room_default) && ($duplicate_rooms)) {
                    $id_room_default_mapping[$id_map]=$id_room_default;
                }
                $mysqli->query("CREATE TEMPORARY TABLE svt_map_tmp SELECT * FROM svt_maps WHERE id = $id_map;");
                $mysqli->query("UPDATE svt_map_tmp SET id=(SELECT MAX(id)+1 as id FROM svt_maps),id_virtualtour=$id_virtualtour_new,id_room_default=NULL;");
                $mysqli->query("INSERT INTO svt_maps SELECT * FROM svt_map_tmp;");
                $id_map_new = $mysqli->insert_id;
                $array_maps[$id_map] = $id_map_new;
                $mysqli->query("DROP TEMPORARY TABLE IF EXISTS svt_map_tmp;");
            }
        }
    }
}

if($duplicate_rooms) {
    $result = $mysqli->query("SELECT id,id_map FROM svt_rooms WHERE id_virtualtour=$id_virtualtour;");
    if($result) {
        if($result->num_rows>0) {
            while($row = $result->fetch_array(MYSQLI_ASSOC)) {
                $id_room = $row['id'];
                $id_map = $row['id_map'];
                $mysqli->query("CREATE TEMPORARY TABLE svt_room_tmp SELECT * FROM svt_rooms WHERE id = $id_room;");
                if(!empty($id_map)) {
                    $id_map_new = $array_maps[$id_map];
                    $mysqli->query("UPDATE svt_room_tmp SET id=(SELECT MAX(id)+1 as id FROM svt_rooms),access_count=0,id_virtualtour=$id_virtualtour_new,id_map=$id_map_new;");
                } else {
                    $mysqli->query("UPDATE svt_room_tmp SET id=(SELECT MAX(id)+1 as id FROM svt_rooms),access_count=0,id_virtualtour=$id_virtualtour_new;");
                }
                $mysqli->query("INSERT INTO svt_rooms SELECT * FROM svt_room_tmp;");
                $id_room_new = $mysqli->insert_id;
                $array_rooms[$id_room] = $id_room_new;
                $mysqli->query("DROP TEMPORARY TABLE IF EXISTS svt_room_tmp;");
            }
        }
    }
}

foreach ($id_room_default_mapping as $id_map_t => $id_room_default_t) {
    $id_map_new = $array_maps[$id_map_t];
    $id_room_default_new = $array_rooms[$id_room_default_t];
    $mysqli->query("UPDATE svt_maps SET id_room_default=$id_room_default_new WHERE id=$id_map_new;");
}

if($duplicate_products) {
    $result = $mysqli->query("SELECT id FROM svt_products WHERE id_virtualtour=$id_virtualtour;");
    if ($result) {
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_array(MYSQLI_ASSOC)) {
                $id_product = $row['id'];
                $mysqli->query("CREATE TEMPORARY TABLE svt_products_tmp SELECT * FROM svt_products WHERE id = $id_product;");
                $mysqli->query("UPDATE svt_products_tmp SET id=(SELECT MAX(id)+1 as id FROM svt_products),id_virtualtour=$id_virtualtour_new;");
                $mysqli->query("INSERT INTO svt_products SELECT * FROM svt_products_tmp;");
                $id_product_new = $mysqli->insert_id;
                $array_products[$id_product] = $id_product_new;
                $mysqli->query("DROP TEMPORARY TABLE IF EXISTS svt_products_tmp;");
                $result_i = $mysqli->query("SELECT id FROM svt_product_images WHERE id_product=$id_product;");
                if ($result_i) {
                    if ($result_i->num_rows > 0) {
                        while ($row_i = $result_i->fetch_array(MYSQLI_ASSOC)) {
                            $id_product_image = $row_i['id'];
                            $mysqli->query("CREATE TEMPORARY TABLE svt_product_images_tmp SELECT * FROM svt_product_images WHERE id = $id_product_image;");
                            $mysqli->query("UPDATE svt_product_images_tmp SET id=(SELECT MAX(id)+1 as id FROM svt_product_images),id_product=$id_product_new;");
                            $mysqli->query("INSERT INTO svt_product_images SELECT * FROM svt_product_images_tmp;");
                            $mysqli->query("DROP TEMPORARY TABLE IF EXISTS svt_product_images_tmp;");
                        }
                    }
                }
            }
        }
    }
}

$array_pois = array();
foreach ($array_rooms as $id_room=>$id_room_new) {
    if($duplicate_markers) {
        $result = $mysqli->query("SELECT id,id_room_target FROM svt_markers WHERE id_room=$id_room;");
        if ($result) {
            if ($result->num_rows > 0) {
                while ($row = $result->fetch_array(MYSQLI_ASSOC)) {
                    $id_marker = $row['id'];
                    $id_room_target = $row['id_room_target'];
                    $id_room_target_new = $array_rooms[$id_room_target];
                    $mysqli->query("CREATE TEMPORARY TABLE svt_marker_tmp SELECT * FROM svt_markers WHERE id = $id_marker;");
                    $mysqli->query("UPDATE svt_marker_tmp SET id=(SELECT MAX(id)+1 as id FROM svt_markers),id_room=$id_room_new,id_room_target=$id_room_target_new;");
                    $mysqli->query("INSERT INTO svt_markers SELECT * FROM svt_marker_tmp;");
                    $mysqli->query("DROP TEMPORARY TABLE IF EXISTS svt_marker_tmp;");
                }
            }
        }
    }
    if($duplicate_pois) {
        $result = $mysqli->query("SELECT id,type,content FROM svt_pois WHERE id_room=$id_room;");
        if ($result) {
            if ($result->num_rows > 0) {
                while ($row = $result->fetch_array(MYSQLI_ASSOC)) {
                    $id_poi = $row['id'];
                    $type = $row['type'];
                    if($type=='product' && !$duplicate_products) continue;
                    $mysqli->query("CREATE TEMPORARY TABLE svt_poi_tmp SELECT * FROM svt_pois WHERE id = $id_poi;");
                    if($type=='product' && !empty($row['content'])) {
                        $id_product = $row['content'];
                        $id_product_new = $array_products[$id_product];
                        $mysqli->query("UPDATE svt_poi_tmp SET id=(SELECT MAX(id)+1 as id FROM svt_pois),access_count=0,id_room=$id_room_new,content='$id_product_new';");
                    } else {
                        $mysqli->query("UPDATE svt_poi_tmp SET id=(SELECT MAX(id)+1 as id FROM svt_pois),access_count=0,id_room=$id_room_new;");
                    }
                    $mysqli->query("INSERT INTO svt_pois SELECT * FROM svt_poi_tmp;");
                    $id_poi_new = $mysqli->insert_id;
                    $array_pois[$id_poi] = $id_poi_new;
                    $mysqli->query("DROP TEMPORARY TABLE IF EXISTS svt_poi_tmp;");
                }
            }
        }
    }
    if($duplicate_rooms) {
        $result = $mysqli->query("SELECT id FROM svt_rooms_alt WHERE id_room=$id_room;");
        if($result) {
            if($result->num_rows>0) {
                while($row = $result->fetch_array(MYSQLI_ASSOC)) {
                    $id_room_alt = $row['id'];
                    $mysqli->query("CREATE TEMPORARY TABLE svt_rooms_alt_tmp SELECT * FROM svt_rooms_alt WHERE id = $id_room_alt;");
                    $mysqli->query("UPDATE svt_rooms_alt_tmp SET id=(SELECT MAX(id)+1 as id FROM svt_rooms_alt),id_room=$id_room_new;");
                    $mysqli->query("INSERT INTO svt_rooms_alt SELECT * FROM svt_rooms_alt_tmp;");
                    $id_room_alt_new = $mysqli->insert_id;
                    $array_rooms_alt[$id_room_alt] = $id_room_alt_new;
                    $mysqli->query("DROP TEMPORARY TABLE IF EXISTS svt_rooms_alt_tmp;");
                }
            }
        }
    }
}

foreach ($array_pois as $id_poi=>$id_poi_new) {
    if($duplicate_pois) {
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
    }
}

if($duplicate_presentation) {
    $result = $mysqli->query("SELECT id,id_room,action,params FROM svt_presentations WHERE id_virtualtour=$id_virtualtour;");
    if($result) {
        if($result->num_rows>0) {
            while($row = $result->fetch_array(MYSQLI_ASSOC)) {
                $id_presentation = $row['id'];
                $id_room = $row['id_room'];
                $action = $row['action'];
                $params = $row['params'];
                $id_room_new = $array_rooms[$id_room];
                $params_new = $array_rooms[$params];
                $mysqli->query("CREATE TEMPORARY TABLE svt_presentation_tmp SELECT * FROM svt_presentations WHERE id = $id_presentation;");
                if($action=='goto') {
                    $mysqli->query("UPDATE svt_presentation_tmp SET id=(SELECT MAX(id)+1 as id FROM svt_presentations),id_room=$id_room_new,params=$params_new;");
                } else {
                    $mysqli->query("UPDATE svt_presentation_tmp SET id=(SELECT MAX(id)+1 as id FROM svt_presentations),id_room=$id_room_new;");
                }
                $mysqli->query("INSERT INTO svt_presentations SELECT * FROM svt_presentation_tmp;");
                $mysqli->query("DROP TEMPORARY TABLE IF EXISTS svt_presentation_tmp;");
            }
        }
    }
}

if($duplicate_gallery) {
    $mysqli->query("CREATE TEMPORARY TABLE svt_gallery_tmp SELECT * FROM svt_gallery WHERE id_virtualtour = $id_virtualtour;");
    $mysqli->query("UPDATE svt_gallery_tmp SET id=NULL,id_virtualtour=$id_virtualtour_new;");
    $mysqli->query("INSERT INTO svt_gallery SELECT * FROM svt_gallery_tmp;");
    $mysqli->query("DROP TEMPORARY TABLE IF EXISTS svt_gallery_tmp;");
}

if($duplicate_rooms) {
    $query = "SELECT list_alt,dollhouse FROM svt_virtualtours WHERE id=$id_virtualtour_new LIMIT 1;";
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
                            $list_alt_array[$key]['id'] = $array_rooms[$id_room];
                            break;
                        case 'category':
                            $childrens = array();
                            foreach ($item['children'] as $key_c => $children) {
                                if ($children['type'] == "room") {
                                    $id_room = $children['id'];
                                    $list_alt_array[$key]['children'][$key_c]['id'] = $array_rooms[$id_room];
                                }
                            }
                            break;
                    }
                }
                $list_alt = json_encode($list_alt_array);
                $mysqli->query("UPDATE svt_virtualtours SET list_alt='$list_alt' WHERE id=$id_virtualtour_new;");
            }
            if(!empty($dollhouse)) {
                $dollhouse_array = json_decode($dollhouse, true);
                $rooms_to_delete = array();
                foreach ($dollhouse_array['rooms'] as $key => $room) {
                    $id_room = $room['id'];
                    if(array_key_exists($id_room,$array_rooms)) {
                        $dollhouse_array['rooms'][$key]['id'] = $array_rooms[$id_room];
                    } else {
                        array_push($rooms_to_delete,$key);
                    }
                }
                foreach ($rooms_to_delete as $room_to_delete) {
                    array_splice($dollhouse_array['rooms'], $room_to_delete, 1);
                }
                $dollhouse = json_encode($dollhouse_array);
                $mysqli->query("UPDATE svt_virtualtours SET dollhouse='$dollhouse' WHERE id=$id_virtualtour_new;");
            }
        }
    }
}

if($duplicate_pois && $duplicate_rooms) {
    $query = "SELECT id,content FROM svt_pois WHERE type='switch_pano';";
    $result = $mysqli->query($query);
    if($result) {
        if($result->num_rows>0) {
            while($row=$result->fetch_array(MYSQLI_ASSOC)) {
                $id = $row['id'];
                $content = $row['content'];
                if(!empty($content) && $content!='0') {
                    $id_room_alt_new = $array_rooms_alt[$content];
                    $mysqli->query("UPDATE svt_pois SET content='$id_room_alt_new' WHERE id=$id_virtualtour_new;");
                }
            }
        }
    }
}

$mysqli->query("CREATE TEMPORARY TABLE svt_icon_tmp SELECT * FROM svt_icons WHERE id_virtualtour = $id_virtualtour;");
$mysqli->query("UPDATE svt_icon_tmp SET id=NULL,id_virtualtour=$id_virtualtour_new;");
$mysqli->query("INSERT INTO svt_icons SELECT * FROM svt_icon_tmp;");
$mysqli->query("DROP TEMPORARY TABLE IF EXISTS svt_icon_tmp;");

ob_end_clean();
echo json_encode(array("status"=>"ok"));

