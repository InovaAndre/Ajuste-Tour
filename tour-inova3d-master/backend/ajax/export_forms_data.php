<?php
error_reporting(E_ALL & ~E_WARNING & ~E_NOTICE & ~E_DEPRECATED);
session_start();
if((($_SERVER['SERVER_ADDR']=='5.9.29.89') && ($_SERVER['REMOTE_ADDR']!=$_SESSION['ip_developer']) && ($_SESSION['id_user']==1)) || ($_SESSION['svt_si']!=session_id())) {
    die();
}
require_once("../functions.php");
require_once("../../db/connection.php");
if(get_user_role($_SESSION['id_user']) != 'administrator') {
    die();
}
$id_vt = $_GET['id_vt'];
$filename = "forms_data_$id_vt.csv";
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename='.$filename);
$query = "SELECT f.*,r.name as room FROM svt_forms_data as f
LEFT JOIN svt_rooms as r ON r.id=f.id_room
WHERE f.id_virtualtour=$id_vt";
$flag = false;
$output = fopen('php://output', 'w');
$result = $mysqli->query($query);
if($result) {
    if($result->num_rows>0) {
        while ($row=$result->fetch_array(MYSQLI_ASSOC)) {
            unset($row['id']);
            unset($row['id_virtualtour']);
            unset($row['id_room']);
            if (!$flag) {
                fputcsv($output, array_keys($row),";",'"');
                $flag = true;
            }
            fputcsv($output, array_values($row),";",'"');
        }
    }
}
exit;