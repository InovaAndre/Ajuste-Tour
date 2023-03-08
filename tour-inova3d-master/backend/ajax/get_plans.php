<?php
error_reporting(E_ALL & ~E_WARNING & ~E_NOTICE & ~E_DEPRECATED);
session_start();
if($_SESSION['svt_si']!=session_id()) {
    die();
}

require(__DIR__.'/ssp.class.php');
require(__DIR__.'/../../config/config.inc.php');
require(__DIR__.'/../functions.php');

$id_map = $_GET['id_map'];
$query = "SELECT p.*,count(u.id_plan) as in_use,IF(p.frequency='recurring',((p.price*12)/p.interval_count),p.price) as order_price FROM svt_plans AS p
LEFT JOIN svt_users AS u ON u.id_plan=p.id AND u.active=1 AND u.role!='editor' AND ((u.expire_plan_date>NOW()) OR (u.`expire_plan_date` IS NULL))
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
    array( 'db' => 'name',  'dt' =>0 ),
    array( 'db' => 'n_virtual_tours',  'dt' =>1, 'formatter' => function( $d, $row ) {
        if($d<0) {
            return "<i class=\"fas fa-infinity\"></i>";
        } else {
            return $d;
        }
    }),
    array( 'db' => 'n_rooms',  'dt' =>2, 'formatter' => function( $d, $row ) {
        if($d<0) {
            return "<i class=\"fas fa-infinity\"></i>";
        } else {
            return $d;
        }
    }),
    array( 'db' => 'n_markers',  'dt' =>3, 'formatter' => function( $d, $row ) {
        if($d<0) {
            return "<i class=\"fas fa-infinity\"></i>";
        } else {
            return $d;
        }
    }),
    array( 'db' => 'n_pois',  'dt' =>4, 'formatter' => function( $d, $row ) {
        if($d<0) {
            return "<i class=\"fas fa-infinity\"></i>";
        } else {
            return $d;
        }
    }),
    array( 'db' => 'create_landing',  'dt' =>5, 'formatter' => function( $d, $row ) {
        $f=0;
        if($row['create_landing']==1) $f++;
        if($row['create_showcase']==1) $f++;
        if($row['create_gallery']==1) $f++;
        if($row['create_presentation']==1) $f++;
        if($row['enable_live_session']==1) $f++;
        if($row['enable_chat']==1) $f++;
        if($row['enable_voice_commands']==1) $f++;
        if($row['enable_share']==1) $f++;
        if($row['enable_device_orientation']==1) $f++;
        if($row['enable_webvr']==1) $f++;
        if($row['enable_logo']==1) $f++;
        if($row['enable_nadir_logo']==1) $f++;
        if($row['enable_song']==1) $f++;
        if($row['enable_forms']==1) $f++;
        if($row['enable_annotations']==1) $f++;
        if($row['enable_rooms_multiple']==1) $f++;
        if($row['enable_rooms_protect']==1) $f++;
        if($row['enable_info_box']==1) $f++;
        if($row['enable_context_info']==1) $f++;
        if($row['enable_maps']==1) $f++;
        if($row['enable_icons_library']==1) $f++;
        if($row['enable_password_tour']==1) $f++;
        if($row['enable_expiring_dates']==1) $f++;
        if($row['enable_statistics']==1) $f++;
        if($row['enable_auto_rotate']==1) $f++;
        if($row['enable_flyin']==1) $f++;
        if($row['enable_multires']==1) $f++;
        if($row['enable_meeting']==1) $f++;
        if($row['enable_export_vt']==1) $f++;
        if($row['enable_shop']==1) $f++;
        if($row['enable_dollhouse']==1) $f++;
        if($row['enable_panorama_video']==1) $f++;
        return $f;
    }),
    array( 'db' => 'days',  'dt' =>6, 'formatter' => function( $d, $row ) {
        if($d<0) {
            return "<i class=\"fas fa-infinity\"></i>";
        } else {
            return $d;
        }
    }),
    array( 'db' => 'max_storage_space',  'dt' =>7, 'formatter' => function( $d, $row ) {
        if($d<0) {
            return "<i class=\"fas fa-infinity\"></i>";
        } else {
            if($d>=1000) {
                $d=($d/1000)." GB";
            } else {
                $d=$d." MB";
            }
            return $d;
        }
    }),
    array( 'db' => 'order_price',  'dt' =>8, 'formatter' => function( $d, $row ) {
        $price = format_currency($row['currency'],$row['price']);
        if($row['price']>0 && $row['frequency']=='recurring') {
            if($row['interval_count']==1) {
                $recurring_label = " / "._("month");
            } elseif($row['interval_count']==12) {
                $recurring_label = " / "._("year");
            } else {
                $recurring_label = " / ".$row['interval_count']." "._("months");
            }
        } else {
            $recurring_label="";
        }
        if($row['price']==0) $price=_("Free");
        return $price.$recurring_label;
    }),
    array( 'db' => 'visible',  'dt' =>9, 'formatter' => function( $d, $row ) {
        if($d) {
            return "<i class='fa fa-check'></i>";
        } else {
            return "<i class='fa fa-times'></i>";
        }
    }),
    array( 'db' => 'in_use',  'dt' =>10 ),
);

$sql_details = array(
    'user' => DATABASE_USERNAME,
    'pass' => DATABASE_PASSWORD,
    'db' => DATABASE_NAME,
    'host' => DATABASE_HOST);

echo json_encode(
    SSP::simple( $_GET, $sql_details, $table, $primaryKey, $columns )
);