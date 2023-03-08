<?php
error_reporting(E_ALL & ~E_WARNING & ~E_NOTICE & ~E_DEPRECATED);
session_start();
if($_SESSION['svt_si']!=session_id()) {
    die();
}

require(__DIR__.'/ssp.class.php');
require(__DIR__.'/../../config/config.inc.php');
require(__DIR__.'/../functions.php');

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
set_language($language,$settings['language_domain']);

$query = "SELECT u.*,COALESCE(p.name, '--') as plan_name FROM svt_users as u LEFT JOIN svt_plans as p ON p.id = u.id_plan";
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
    array( 'db' => 'username',  'dt' =>0, 'formatter' => function( $d, $row ) {
        if(empty($row['avatar'])) {
            $avatar='img/avatar1.png';
        } else {
            $avatar='assets/'.$row['avatar'];
        }
        return "<img style='width:20px;height:20px;border-radius:50%;margin-bottom:2px;' src='$avatar' /> ".$d;
    }),
    array( 'db' => 'email',  'dt' =>1 ),
    array( 'db' => 'role',  'dt' =>2, 'formatter' => function( $d, $row ) {
        return ucfirst($d);
    }),
    array( 'db' => 'plan_name',  'dt' =>3, 'formatter' => function( $d, $row ) {
        if(($row['role']!='editor') && ($row['id_plan']!=0)) {
            if((!empty($row['id_subscription_stripe']) && ($row['status_subscription_stripe']==0)) || (!empty($row['id_subscription_paypal']) && ($row['status_subscription_paypal']==0))) {
                return "<i class='fa fa-circle' style='color: red'></i> " . $d;
            } else {
                if(empty($row['expire_plan_date'])) {
                    return "<i class='fa fa-circle' style='color: green'></i> " . $d;
                } else {
                    if (new DateTime() > new DateTime($row['expire_plan_date'])) {
                        return "<i class='fa fa-circle' style='color: red'></i> " . $d;
                    } else{
                        return "<i class='fa fa-circle' style='color: darkorange'></i> " . $d;
                    }
                }
            }
        } else {
            return "";
        }
    }),
    array( 'db' => 'registration_date',  'dt' =>4, 'formatter' => function( $d, $row ) {
        global $language;
        $reg_date = formatTime("%d %b %Y",$language,strtotime($d));
        return $reg_date;
    }),
    array( 'db' => 'expire_plan_date',  'dt' =>5, 'formatter' => function( $d, $row ) {
        $diff_days = dateDiffInDays(date('Y-m-d',strtotime($d)),date('Y-m-d',strtotime('today')));
        if($diff_days==0) {
            return _("today");
        } else if($diff_days==-1) {
            return "1 "._("day");
        } else if ($diff_days>0) {
            return "--";
        } else {
             return abs($diff_days)." "._("days");
        }
    }),
    array( 'db' => 'active',  'dt' =>6, 'formatter' => function( $d, $row ) {
        if($d) {
            return "<i class='fa fa-check'></i>";
        } else {
            return "<i class='fa fa-times'></i>";
        }
    }),
);

$sql_details = array(
    'user' => DATABASE_USERNAME,
    'pass' => DATABASE_PASSWORD,
    'db' => DATABASE_NAME,
    'host' => DATABASE_HOST);

echo json_encode(
    SSP::simple( $_GET, $sql_details, $table, $primaryKey, $columns )
);