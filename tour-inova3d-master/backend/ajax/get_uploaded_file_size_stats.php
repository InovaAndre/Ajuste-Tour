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
$stats = array();
$tmp_size = get_disk_size_stat_uploaded($id_user);
$stats['disk_space_used'] = $tmp_size[0];
$size = $tmp_size[1];
$mysqli->query("UPDATE svt_users SET storage_space=$size WHERE id=$id_user;");
ob_end_clean();
echo json_encode($stats);