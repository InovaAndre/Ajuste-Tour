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
$id_category = $_POST['id_category'];
$id_user_f = $_POST['id_user_f'];

$settings = get_settings();
$user_info = get_user_info($id_user);
if(!isset($_SESSION['lang'])) {
    if(!empty($user_info['language'])) {
        $language = $user_info['language'];
    } else {
        $language = $settings['language'];
    }
} else {
    $language = $_SESSION['lang'];
}

$where = $where_f = "";
switch(get_user_role($id_user)) {
    case 'customer':
        $where = $where_f = " AND v.id_user=$id_user ";
        break;
    case 'editor':
        $where = $where_f = " AND v.id IN () ";
        $query = "SELECT GROUP_CONCAT(id_virtualtour) as ids FROM svt_assign_virtualtours WHERE id_user=$id_user;";
        $result = $mysqli->query($query);
        if($result) {
            if($result->num_rows==1) {
                $row=$result->fetch_array(MYSQLI_ASSOC);
                $ids = $row['ids'];
                $where = $where_f = " AND v.id IN ($ids) ";
            }
        }
        break;
}

if($id_category!=0) {
    $where .= " AND v.id_category = $id_category ";
}

if($id_user_f!=0) {
    $where .= " AND v.id_user = $id_user_f ";
}

$array_users = array();
$array_cat = array();
$query = "SELECT v.id,c.name as category_name,c.id as category_id,v.id_user,u.username FROM svt_virtualtours as v 
            LEFT JOIN svt_users as u ON u.id=v.id_user
            LEFT JOIN svt_categories as c ON c.id=v.id_category
            WHERE 1=1 $where_f
            GROUP BY v.id";
$result = $mysqli->query($query);
if($result) {
    if($result->num_rows>0) {
        while($row=$result->fetch_array(MYSQLI_ASSOC)) {
            if(!empty($row['category_id'])) {
                if(!in_array($row['category_id']."|".$row['category_name'],$array_cat)) {
                    $array_cat[] = $row['category_id']."|".$row['category_name'];
                }
            }
            if(!empty($row['id_user'])) {
                if(!in_array($row['id_user']."|".$row['username'],$array_users)) {
                    $array_users[] = $row['id_user']."|".$row['username'];
                }
            }
        }
    }
}

$array_vt = array();
$query = "SELECT v.id,c.name as category_name,c.id as category_id,v.external,UPPER(v.name) as name,v.date_created,v.author,v.id_user,u.username,v.start_date,v.end_date,v.active,u.expire_plan_date,COUNT(DISTINCT r.id) as count_rooms,COUNT(DISTINCT m.id) as count_maps,COUNT(DISTINCT g.id) as count_gallery,IF(v.info_box IS NULL OR v.info_box = '' OR v.info_box='<p><br></p>',0,1) as info_box_check
            FROM svt_virtualtours as v 
            LEFT JOIN svt_rooms as r ON r.id_virtualtour=v.id
            LEFT JOIN svt_users as u ON u.id=v.id_user
            LEFT JOIN svt_maps as m ON m.id_virtualtour=v.id
            LEFT JOIN svt_gallery as g ON g.id_virtualtour=v.id
            LEFT JOIN svt_categories as c ON c.id=v.id_category
            WHERE 1=1 $where
            GROUP BY v.id
            ORDER BY v.date_created DESC,v.id DESC;";
$result = $mysqli->query($query);
if($result) {
    if($result->num_rows>0) {
        while($row=$result->fetch_array(MYSQLI_ASSOC)) {
            if($user_info['role']=='editor') {
                $editor_permissions = get_editor_permissions($id_user,$row['id']);
                if($editor_permissions['edit_virtualtour']==1) {
                    $row['edit_permission']=true;
                } else {
                    $row['edit_permission']=false;
                }
                if($editor_permissions['edit_virtualtour_ui']==1) {
                    $row['edit_ui_permission']=true;
                } else {
                    $row['edit_ui_permission']=false;
                }
                if($editor_permissions['edit_3d_view']==1) {
                    $row['edit_3d_view_permission']=true;
                } else {
                    $row['edit_3d_view_permission']=false;
                }
            } else {
                $row['edit_permission']=true;
                $row['edit_ui_permission']=true;
                $row['edit_3d_view_permission']=true;
            }
            if($row['active']==0) {
                $row['status']=0;
            } else {
                $row['status']=1;
            }
            $row['date_created'] = formatTime("%d %b %Y",$language,strtotime($row['date_created']));
            if(!empty($row['expire_plan_date'])) {
                if (new DateTime() > new DateTime($row['expire_plan_date'])) {
                    $row['status']=0;
                }
            }
            if((!empty($row['start_date'])) && ($row['start_date']!='0000-00-00')) {
                if (new DateTime() < new DateTime($row['start_date']." 00:00:00")) {
                    $row['status']=0;
                }
                $row['start_date'] = formatTime("%d %b %Y",$language,strtotime($row['start_date']));
            } else {
                $row['start_date'] = "";
            }
            if((!empty($row['end_date'])) && ($row['end_date']!='0000-00-00')) {
                if (new DateTime() > new DateTime($row['end_date']." 23:59:59")) {
                    $row['status']=0;
                }
                $row['end_date'] = formatTime("%d %b %Y",$language,strtotime($row['end_date']));
            } else {
                $row['end_date'] = "";
            }
            if(($row['author']!=$row['username']) && (!empty($row['author']))) {
                $row['author'] = $row['username']." (".$row['author'].")";
            } else {
                $row['author'] = $row['username'];
            }
            $row['name'] = htmlentities($row['name']);
            $row['author'] = htmlentities($row['author']);
            $array_vt[]=$row;
        }
    }
}
ob_end_clean();
echo json_encode(array("vt_list"=>$array_vt,"categories"=>$array_cat,"users"=>$array_users), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);