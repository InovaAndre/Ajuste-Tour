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
$stats = array();
$stats['disk_space_used'] = get_disk_size_stat($id_user,$id_virtualtour);
ob_end_clean();
echo json_encode($stats);