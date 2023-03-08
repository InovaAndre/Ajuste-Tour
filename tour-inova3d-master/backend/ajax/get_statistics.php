<?php
error_reporting(E_ALL & ~E_WARNING & ~E_NOTICE & ~E_DEPRECATED);
ob_start();
session_start();
if($_SESSION['svt_si']!=session_id()) {
    die();
}

require_once("../functions.php");
require_once("../../db/connection.php");
$id_virtualtour = $_POST['id_virtualtour'];
$elem = $_POST['elem'];
$unique = false;
if(isset($_SESSION['statistics_type'])) {
    if($_SESSION['statistics_type']=="unique") {
        $unique = true;
    }
}

$settings = get_settings();
$user_info = get_user_info($_SESSION['id_user']);
if(!isset($_SESSION['lang'])) {
    if(!empty($user_info['language'])) {
        $language = $user_info['language'];
    } else {
        $language = $settings['language'];
    }
} else {
    $language = $_SESSION['lang'];
}

$stats = array();

switch ($elem) {
    case 'chart_visitor_vt':
        $stats['labels'] = array();
        $stats['data'] = array();
        if($unique) {
            $query = "SELECT date_time,COUNT(DISTINCT ip) as num FROM svt_access_log
                        WHERE id_virtualtour=$id_virtualtour AND ip IS NOT NULL AND ip!=''
                        GROUP BY MONTH(date_time),YEAR(date_time)
                        ORDER BY date_time;";
        } else {
            $query = "SELECT date_time,COUNT(*) as num FROM svt_access_log 
                    WHERE id_virtualtour=$id_virtualtour
                    GROUP BY MONTH(date_time),YEAR(date_time)
                    ORDER BY date_time;";
        }
        $result = $mysqli->query($query);
        if($result) {
            if($result->num_rows>0) {
                while($row = $result->fetch_array(MYSQLI_ASSOC)) {
                    array_push($stats['labels'],formatTime("%b %Y",$language,strtotime($row['date_time'])));
                    array_push($stats['data'],$row['num']);
                }
            }
        }
        break;
    case 'chart_rooms_access':
        $stats['labels'] = array();
        $stats['data'] = array();
        if($unique) {
            $query = "SELECT sr.name,COUNT(DISTINCT salr.ip) as num FROM svt_access_log_room AS salr
                    JOIN svt_rooms sr ON salr.id_room = sr.id
                    WHERE sr.id_virtualtour=$id_virtualtour
                    GROUP BY sr.id
                    ORDER BY sr.priority";
        } else {
            $query = "SELECT r.name,r.access_count as num 
                    FROM svt_rooms as r
                    WHERE r.id_virtualtour=$id_virtualtour 
                    GROUP BY r.id
                    ORDER BY r.priority;";
        }
        $result = $mysqli->query($query);
        if($result) {
            if($result->num_rows>0) {
                while($row = $result->fetch_array(MYSQLI_ASSOC)) {
                    array_push($stats['labels'],strtoupper($row['name']));
                    array_push($stats['data'],$row['num']);
                }
            }
        }
        break;
    case 'chart_rooms_time':
        $stats['labels'] = array();
        $stats['data'] = array();
        if($unique) {
            $query = "SELECT r.name,AVG(time) as num 
                    FROM svt_rooms_access_log as l
                    JOIN svt_rooms as r on r.id=l.id_room
                    WHERE r.id_virtualtour=$id_virtualtour AND ip IS NOT NULL AND ip!=''
                    GROUP BY l.id_room
                    ORDER BY r.priority;";
        } else {
            $query = "SELECT r.name,AVG(time) as num 
                    FROM svt_rooms_access_log as l
                    JOIN svt_rooms as r on r.id=l.id_room
                    WHERE r.id_virtualtour=$id_virtualtour 
                    GROUP BY l.id_room
                    ORDER BY r.priority;";
        }
        $result = $mysqli->query($query);
        if($result) {
            if($result->num_rows>0) {
                while($row = $result->fetch_array(MYSQLI_ASSOC)) {
                    array_push($stats['labels'],strtoupper($row['name']));
                    array_push($stats['data'],round($row['num']));
                }
            }
        }
        break;
    case 'chart_poi_views':
        $stats['pois'] = array();
        $stats['total_poi'] = 0;
        if($unique) {
            $query = "SELECT sr.name as room,COUNT(DISTINCT salp.ip) as access_count,sp.type,sp.content FROM svt_access_log_poi AS salp
                    JOIN svt_pois sp ON salp.id_poi = sp.id
                    JOIN svt_rooms sr on sp.id_room = sr.id
                    WHERE sr.id_virtualtour=$id_virtualtour
                    AND sp.type != 'switch_pano'
                    GROUP BY sp.id
                    ORDER BY COUNT(DISTINCT salp.ip) DESC;";
        } else {
            $query = "SELECT r.name as room,p.type,p.content,p.access_count
                    FROM svt_pois as p
                    JOIN svt_rooms as r ON r.id=p.id_room
                    WHERE r.id_virtualtour=$id_virtualtour 
                    AND p.type != 'switch_pano'
                    AND p.access_count>0 
                    GROUP BY p.id
                    ORDER BY p.access_count DESC;";
        }
        $result = $mysqli->query($query);
        if($result) {
            if($result->num_rows>0) {
                while($row = $result->fetch_array(MYSQLI_ASSOC)) {
                    $stats['total_poi'] = $stats['total_poi'] + $row['access_count'];
                    if(empty($row['type'])) $row['type']='';
                    switch ($row['type']) {
                        case 'product':
                            $query_p = "SELECT name FROM svt_products WHERE id=".$row['content']." LIMIT 1;";
                            $ressult_p = $mysqli->query($query_p);
                            if($ressult_p) {
                                if($ressult_p->num_rows==1) {
                                    $row_p = $ressult_p->fetch_array(MYSQLI_ASSOC);
                                    $row['content'] = $row_p['name'];
                                }
                            }
                            break;
                    }
                    array_push($stats['pois'],$row);
                }
            }
        }
        break;
}
ob_end_clean();
echo json_encode($stats);