<?php
error_reporting(E_ALL & ~E_WARNING & ~E_NOTICE & ~E_DEPRECATED);
require_once("../../db/connection.php");
$type = $_POST['type'];
$id = $_POST['id'];
$ip_visitor = $_POST['ip_visitor'];

switch ($type) {
    case 'poi':
        $mysqli->query("UPDATE svt_pois SET access_count=access_count+1 WHERE id=$id;");
        $mysqli->query("INSERT INTO svt_access_log_poi(id_poi,date_time,ip) VALUES($id,NOW(),'$ip_visitor');");
        break;
    case 'room':
        $mysqli->query("UPDATE svt_rooms SET access_count=access_count+1 WHERE id=$id;");
        $mysqli->query("INSERT INTO svt_access_log_room(id_room,date_time,ip) VALUES($id,NOW(),'$ip_visitor');");
        break;
    case 'room_time':
        $access_time_avg = $_POST['access_time_avg'];
        $mysqli->query("INSERT INTO svt_rooms_access_log(id_room,time,ip) VALUES($id,$access_time_avg,'$ip_visitor');");
        break;
}