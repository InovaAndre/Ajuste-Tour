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
$unique = false;
if(isset($_SESSION['statistics_type'])) {
    if($_SESSION['statistics_type']=="unique") {
        $unique = true;
    }
}

if(empty($id_virtualtour)) {
    switch(get_user_role($id_user)) {
        case 'administrator':
            $where = " WHERE 1=1 ";
            break;
        case 'customer':
            $where = " WHERE 1=1 AND v.id_user=$id_user ";
            break;
        case 'editor':
            $where = " WHERE 1=1 AND v.id IN () ";
            $query = "SELECT GROUP_CONCAT(id_virtualtour) as ids FROM svt_assign_virtualtours WHERE id_user=$id_user;";
            $result = $mysqli->query($query);
            if($result) {
                if($result->num_rows==1) {
                    $row=$result->fetch_array(MYSQLI_ASSOC);
                    $ids = $row['ids'];
                    $where = " WHERE 1=1 AND v.id IN ($ids) ";
                }
            }
            break;
    }
} else {
    $where = " WHERE 1=1 AND v.id = $id_virtualtour ";
}

$stats = array();
$stats['count_virtual_tours'] = 0;
$stats['count_rooms'] = 0;
$stats['count_markers'] = 0;
$stats['count_pois'] = 0;
$stats['total_visitors'] = 0;
$stats['total_online_visitors'] = 0;
$stats['visitors'] = array();
$stats['online_visitors'] = array();

$query = "SELECT COUNT(v.id) as num FROM svt_virtualtours as v $where LIMIT 1";
$result = $mysqli->query($query);
if($result) {
    if($result->num_rows==1) {
        $row=$result->fetch_array(MYSQLI_ASSOC);
        $num = $row['num'];
        $stats['count_virtual_tours'] = $num;
    }
}

$query = "SELECT COUNT(r.id) as num FROM svt_rooms as r
JOIN svt_virtualtours as v ON v.id = r.id_virtualtour
$where LIMIT 1;";
$result = $mysqli->query($query);
if($result) {
    if($result->num_rows==1) {
        $row=$result->fetch_array(MYSQLI_ASSOC);
        $num = $row['num'];
        $stats['count_rooms'] = $num;
    }
}

$query = "SELECT COUNT(m.id) as num FROM svt_markers as m
JOIN svt_rooms as r ON m.id_room = r.id
JOIN svt_virtualtours as v ON v.id = r.id_virtualtour
$where LIMIT 1;";
$result = $mysqli->query($query);
if($result) {
    if($result->num_rows==1) {
        $row=$result->fetch_array(MYSQLI_ASSOC);
        $num = $row['num'];
        $stats['count_markers'] = $num;
    }
}

$query = "SELECT COUNT(m.id) as num FROM svt_pois as m
JOIN svt_rooms as r ON m.id_room = r.id
JOIN svt_virtualtours as v ON v.id = r.id_virtualtour
$where LIMIT 1;";
$result = $mysqli->query($query);
if($result) {
    if($result->num_rows==1) {
        $row=$result->fetch_array(MYSQLI_ASSOC);
        $num = $row['num'];
        $stats['count_pois'] = $num;
    }
}

$total_visitors = 0;
if($unique==true && !empty($id_virtualtour)) {
    $total_unique = 0;
    $query = "SELECT COUNT(DISTINCT ip) as count FROM svt_access_log WHERE id_virtualtour=$id_virtualtour;";
    $result = $mysqli->query($query);
    if($result) {
        if($result->num_rows==1) {
            $row=$result->fetch_array(MYSQLI_ASSOC);
            $stats['total_visitors'] = $row['count'];
        }
    }
} else {
    $query = "SELECT v.id,UPPER(v.name) as name,COUNT(a.id) as count FROM svt_virtualtours as v
            LEFT JOIN svt_access_log as a ON v.id = a.id_virtualtour
            $where
            GROUP BY v.id
            ORDER BY count DESC;";
    $result = $mysqli->query($query);
    if($result) {
        if($result->num_rows>0) {
            while($row=$result->fetch_array(MYSQLI_ASSOC)) {
                $count = $row['count'];
                $total_visitors = $total_visitors + $count;
                $stats['visitors'][] = $row;
            }
            $stats['total_visitors'] = $total_visitors;
        }
    }
}

$total_online_visitors = 0;
$query = "SELECT v.id,COUNT(DISTINCT s.ip) as count FROM svt_virtualtours AS v
LEFT JOIN svt_visitors AS s ON s.id_virtualtour=v.id
$where
AND datetime>=(NOW() - INTERVAL 30 SECOND)
GROUP BY v.id;";
$result = $mysqli->query($query);
if($result) {
    if($result->num_rows>0) {
        while($row=$result->fetch_array(MYSQLI_ASSOC)) {
            $count = $row['count'];
            $total_online_visitors = $total_online_visitors + $count;
            $stats['online_visitors'][] = $row;
        }
        $stats['total_online_visitors'] = $total_online_visitors;
    }
}
$mysqli->query("DELETE FROM svt_visitors WHERE datetime<(NOW() - INTERVAL 1 MINUTE);");

ob_end_clean();
echo json_encode($stats);