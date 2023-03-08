<?php
error_reporting(E_ALL & ~E_WARNING & ~E_NOTICE & ~E_DEPRECATED);
session_start();
if($_SESSION['svt_si']!=session_id()) {
    die();
}

require(__DIR__.'/ssp.class.php');
require(__DIR__.'/../../config/config.inc.php');
require_once(__DIR__."/../functions.php");

$id_user = $_SESSION['id_user'];
$id_vt = $_GET['id_vt'];

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

$query = "SELECT p.*,MIN(spi.image) as image,v.snipcart_currency as currency FROM svt_products as p
LEFT JOIN svt_product_images spi on p.id = spi.id_product
JOIN svt_virtualtours as v ON v.id=p.id_virtualtour
WHERE p.id_virtualtour=$id_vt
GROUP BY p.id";
$table = "( $query ) t";
$primaryKey = 'id';

$columns = array(
    array(
        'db' => 'id',
        'dt' => 'DT_RowId',
        'formatter' => function( $d, $row ) {
            return $d;
        }
    ),
    array( 'db' => 'name',  'dt' =>0, 'formatter' => function( $d, $row ) {
        if(!empty($row['image'])) {
            $image = '../viewer/products/thumb/'.$row['image'];
            return "<img style='width:20px;height:20px;border-radius:50%;vertical-align:sub;' src='$image' /> ".$d;
        } else {
            return $d;
        }
    }),
    array( 'db' => 'price',  'dt' =>1, 'formatter' => function( $d, $row ) {
        $price = format_currency($row['currency'],$row['price']);
        return $price;
    })
);

$sql_details = array(
    'user' => DATABASE_USERNAME,
    'pass' => DATABASE_PASSWORD,
    'db' => DATABASE_NAME,
    'host' => DATABASE_HOST);

echo json_encode(
    SSP::simple( $_GET, $sql_details, $table, $primaryKey, $columns )
);