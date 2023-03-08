<?php
error_reporting(E_ALL & ~E_WARNING & ~E_NOTICE & ~E_DEPRECATED);
ob_start();
session_start();
if($_SESSION['svt_si']!=session_id()) {
    die();
}

require_once("../functions.php");
$id_virtualtour = $_POST['id_virtualtour'];
$m = $_POST['m'];
$html = "";
switch($m) {
    case 'marker':
    case 'poi':
        $html = get_library_icons_v($id_virtualtour,$m);
        break;
    case 'marker_h':
        $html = get_library_icons($id_virtualtour,'marker');
        break;
    case 'poi_h':
        $html = get_library_icons($id_virtualtour,'poi');
        break;
}
ob_end_clean();
echo json_encode(array("html"=>$html));