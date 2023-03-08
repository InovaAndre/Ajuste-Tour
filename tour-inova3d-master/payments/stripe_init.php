<?php
error_reporting(E_ALL & ~E_WARNING & ~E_NOTICE & ~E_DEPRECATED);
session_start();
if(($_SERVER['SERVER_ADDR']=='5.9.29.89') && ($_SERVER['REMOTE_ADDR']!=$_SESSION['ip_developer']) && ($_SESSION['id_user']==1)) {
    //DEMO CHECK
    die();
}
require_once(__DIR__."/../backend/functions.php");
require_once(__DIR__."/../db/connection.php");
require(__DIR__."/../backend/vendor/stripe-php/init.php");
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

if(isset($_POST['stripe_secret_key'])) {
    $stripe_secret_key = $_POST['stripe_secret_key'];
    $stripe_public_key = $_POST['stripe_public_key'];
    if($stripe_public_key!="keep_stripe_public_key") {
        $mysqli->query("UPDATE svt_settings SET stripe_public_key='$stripe_public_key';");
    }
    if($stripe_secret_key!="keep_stripe_secret_key") {
        $mysqli->query("UPDATE svt_settings SET stripe_secret_key='$stripe_secret_key';");
    }
}

if(isset($_POST['id_plan'])) {
    $id_plan_p = $_POST['id_plan'];
} else {
    $id_plan_p = null;
}

$settings = get_settings();
$app_name = $settings['name'];
$logo = $settings['logo'];
$currentPath = $_SERVER['PHP_SELF'];
$pathInfo = pathinfo($currentPath);
$hostName = $_SERVER['HTTP_HOST'];
if (is_ssl()) { $protocol = 'https'; } else { $protocol = 'http'; }
$url = $protocol."://".$hostName.$pathInfo['dirname'];
$url_logo = "";
if(!empty($logo)) {
    $url_logo = str_replace("/payments","",$url)."/backend/assets/".$logo;
}

$url_webhook = $url."/stripe_webhooks.php";

$key = $settings['stripe_secret_key'];
if(empty($key)) {
    exit;
}

$plans_array = array();
$query = "SELECT * FROM svt_plans WHERE price > 0;";
$result = $mysqli->query($query);
if($result) {
    if($result->num_rows>0) {
        while($row = $result->fetch_array(MYSQLI_ASSOC)) {
            $id = $row['id'];
            $id_product_stripe = $row['id_product_stripe'];
            $id_price_stripe = $row['id_price_stripe'];
            $name = $row['name'];
            $price = $row['price'];
            $currency = $row['currency'];
            $frequency = $row['frequency'];
            $interval_count = $row['interval_count'];
            array_push($plans_array,array("id"=>$id,"id_product_stripe"=>$id_product_stripe,"id_price_stripe"=>$id_price_stripe,"name"=>$name,"price"=>$price,"currency"=>$currency,"frequency"=>$frequency,"interval_count"=>$interval_count));
        }
    }
}

$stripe = new \Stripe\StripeClient($key);

if(!check_webhook($url_webhook)) {
    $ch = curl_init($url_webhook);
    curl_setopt($ch, CURLOPT_NOBODY, true);
    curl_exec($ch);
    $hcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    if(strpos($hcode,'200')===false) {
        echo json_encode(array("status"=>"error","msg"=>"Webhook url $url_webhook not reachable!"));
        die();
    } else {
        create_webhook($url_webhook);
    }
}

foreach ($plans_array as $plan) {
    $id_plan = $plan['id'];
    if(($id_plan_p!=0) && ($id_plan!=$id_plan_p)) {
        continue;
    }
    $id_product_stripe = $plan['id_product_stripe'];
    $id_price_stripe = $plan['id_price_stripe'];
    $name = $plan['name'];
    if(!empty($app_name)) {
        $name = $app_name." - ".$name;
    }
    $price = $plan['price'];
    $currency = $plan['currency'];
    $frequency = $plan['frequency'];
    $interval_count = $plan['interval_count'];
    if(empty($id_product_stripe)) {
        //CREATE PRODUCT
        $id_product_stripe = create_product($name);
        $mysqli->query("UPDATE svt_plans SET id_product_stripe='$id_product_stripe' WHERE id=$id_plan;");
    } else {
        //CHECK PRODUCT
        if(!check_if_product_exist($id_product_stripe)) {
            //CREATE PRODUCT
            $id_product_stripe = create_product($name);
            $mysqli->query("UPDATE svt_plans SET id_product_stripe='$id_product_stripe' WHERE id=$id_plan;");
        } else {
            //MODIFY PRODUCT
            modify_product($id_product_stripe,$name);
        }
    }
    if(empty($id_price_stripe)) {
        //CREATE PRICE
        $id_price_stripe = create_price($id_product_stripe,$currency,$price,$frequency,$interval_count);
        $mysqli->query("UPDATE svt_plans SET id_price_stripe='$id_price_stripe' WHERE id=$id_plan;");
    } else {
        //CHECK PRICE
        if(!check_if_price_exist($id_price_stripe)) {
            //CREATE PRICE
            $id_price_stripe = create_price($id_product_stripe,$currency,$price,$frequency,$interval_count);
            $mysqli->query("UPDATE svt_plans SET id_price_stripe='$id_price_stripe' WHERE id=$id_plan;");
        } else {
            $id_price_stripe_mod = modify_price($id_price_stripe,$id_product_stripe,$currency,$price,$frequency,$interval_count);
            if($id_price_stripe_mod!=$id_price_stripe) {
                $id_price_stripe = $id_price_stripe_mod;
                $mysqli->query("UPDATE svt_plans SET id_price_stripe='$id_price_stripe' WHERE id=$id_plan;");
            }
        }
    }
}

$query = "UPDATE svt_settings SET stripe_enabled=1;";
$result = $mysqli->query($query);
if($result) {
    echo json_encode(array("status"=>"ok"));
} else {
    echo json_encode(array("status"=>"error","msg"=>"Error, retry later."));
}

function check_webhook($url_webhook) {
    global $stripe;
    try {
        $response = $stripe->webhookEndpoints->all();
        foreach ($response['data'] as $wh) {
            $events = $wh['enabled_events'];
            $url = $wh['url'];
            if(($url==$url_webhook) && (in_array('checkout.session.completed',$events)) && (in_array('invoice.payment_failed',$events)) && (in_array('customer.subscription.deleted',$events)) && (in_array('customer.subscription.updated',$events))) {
                return true;
            }
        }
        return false;
    } catch(\Stripe\Exception\CardException $e) {
        echo json_encode(array("status"=>"error","msg"=>$e->getError()->message));
        exit;
    } catch (\Stripe\Exception\RateLimitException $e) {
        echo json_encode(array("status"=>"error","msg"=>$e->getError()->message));
        exit;
    } catch (\Stripe\Exception\InvalidRequestException $e) {
        echo json_encode(array("status"=>"error","msg"=>$e->getError()->message));
        exit;
    } catch (\Stripe\Exception\AuthenticationException $e) {
        echo json_encode(array("status"=>"error","msg"=>$e->getError()->message));
        exit;
    } catch (\Stripe\Exception\ApiConnectionException $e) {
        echo json_encode(array("status"=>"error","msg"=>$e->getError()->message));
        exit;
    } catch (\Stripe\Exception\ApiErrorException $e) {
        echo json_encode(array("status"=>"error","msg"=>$e->getError()->message));
        exit;
    } catch (Exception $e) {
        echo json_encode(array("status"=>"error","msg"=>"Error, retry later."));
        exit;
    }
}

function create_webhook($url_webhook) {
    global $stripe;
    try {
        $stripe->webhookEndpoints->create([
            'url' => $url_webhook,
            'enabled_events' => [
                'checkout.session.completed',
                'invoice.payment_failed',
                'customer.subscription.deleted',
                'customer.subscription.updated'
            ],
        ]);
    } catch(\Stripe\Exception\CardException $e) {
        echo json_encode(array("status"=>"error","msg"=>$e->getError()->message));
        exit;
    } catch (\Stripe\Exception\RateLimitException $e) {
        echo json_encode(array("status"=>"error","msg"=>$e->getError()->message));
        exit;
    } catch (\Stripe\Exception\InvalidRequestException $e) {
        echo json_encode(array("status"=>"error","msg"=>$e->getError()->message));
        exit;
    } catch (\Stripe\Exception\AuthenticationException $e) {
        echo json_encode(array("status"=>"error","msg"=>$e->getError()->message));
        exit;
    } catch (\Stripe\Exception\ApiConnectionException $e) {
        echo json_encode(array("status"=>"error","msg"=>$e->getError()->message));
        exit;
    } catch (\Stripe\Exception\ApiErrorException $e) {
        echo json_encode(array("status"=>"error","msg"=>$e->getError()->message));
        exit;
    } catch (Exception $e) {
        echo json_encode(array("status"=>"error","msg"=>"Error, retry later."));
        exit;
    }
}

function check_if_product_exist($id) {
    global $stripe;
    try {
        $stripe->products->retrieve($id, []);
        return true;
    } catch(\Stripe\Exception\CardException $e) {
        echo json_encode(array("status"=>"error","msg"=>$e->getError()->message));
        exit;
    } catch (\Stripe\Exception\RateLimitException $e) {
        echo json_encode(array("status"=>"error","msg"=>$e->getError()->message));
        exit;
    } catch (\Stripe\Exception\InvalidRequestException $e) {
        return false;
    } catch (\Stripe\Exception\AuthenticationException $e) {
        echo json_encode(array("status"=>"error","msg"=>$e->getError()->message));
        exit;
    } catch (\Stripe\Exception\ApiConnectionException $e) {
        echo json_encode(array("status"=>"error","msg"=>$e->getError()->message));
        exit;
    } catch (\Stripe\Exception\ApiErrorException $e) {
        echo json_encode(array("status"=>"error","msg"=>$e->getError()->message));
        exit;
    } catch (Exception $e) {
        echo json_encode(array("status"=>"error","msg"=>"Error, retry later."));
        exit;
    }
}

function check_if_price_exist($id) {
    global $stripe;
    try {
        $stripe->prices->retrieve($id, []);
        return true;
    } catch(\Stripe\Exception\CardException $e) {
        echo json_encode(array("status"=>"error","msg"=>$e->getError()->message));
        exit;
    } catch (\Stripe\Exception\RateLimitException $e) {
        echo json_encode(array("status"=>"error","msg"=>$e->getError()->message));
        exit;
    } catch (\Stripe\Exception\InvalidRequestException $e) {
        return false;
    } catch (\Stripe\Exception\AuthenticationException $e) {
        echo json_encode(array("status"=>"error","msg"=>$e->getError()->message));
        exit;
    } catch (\Stripe\Exception\ApiConnectionException $e) {
        echo json_encode(array("status"=>"error","msg"=>$e->getError()->message));
        exit;
    } catch (\Stripe\Exception\ApiErrorException $e) {
        echo json_encode(array("status"=>"error","msg"=>$e->getError()->message));
        exit;
    } catch (Exception $e) {
        echo json_encode(array("status"=>"error","msg"=>"Error, retry later."));
        exit;
    }
}

function create_product($name) {
    global $stripe,$url_logo;
    try {
        if(!empty($url_logo)) {
            $response = $stripe->products->create([
                'name' => $name, 'images'=>[$url_logo]
            ]);
        } else {
            $response = $stripe->products->create([
                'name' => $name,
            ]);
        }
        $id = $response['id'];
        return $id;
    } catch(\Stripe\Exception\CardException $e) {
        echo json_encode(array("status"=>"error","msg"=>$e->getError()->message));
        exit;
    } catch (\Stripe\Exception\RateLimitException $e) {
        echo json_encode(array("status"=>"error","msg"=>$e->getError()->message));
        exit;
    } catch (\Stripe\Exception\InvalidRequestException $e) {
        echo json_encode(array("status"=>"error","msg"=>$e->getError()->message));
        exit;
    } catch (\Stripe\Exception\AuthenticationException $e) {
        echo json_encode(array("status"=>"error","msg"=>$e->getError()->message));
        exit;
    } catch (\Stripe\Exception\ApiConnectionException $e) {
        echo json_encode(array("status"=>"error","msg"=>$e->getError()->message));
        exit;
    } catch (\Stripe\Exception\ApiErrorException $e) {
        echo json_encode(array("status"=>"error","msg"=>$e->getError()->message));
        exit;
    } catch (Exception $e) {
        echo json_encode(array("status"=>"error","msg"=>"Error, retry later."));
        exit;
    }
}

function modify_product($id_product_stripe,$name) {
    global $stripe,$url_logo;
    try {
        if(!empty($url_logo)) {
            $stripe->products->update($id_product_stripe, [
                'name' => $name, 'images' => [$url_logo]
            ]);
        } else {
            $stripe->products->update($id_product_stripe, [
                'name' => $name
            ]);
        }
    } catch(\Stripe\Exception\CardException $e) {
        echo json_encode(array("status"=>"error","msg"=>$e->getError()->message));
        exit;
    } catch (\Stripe\Exception\RateLimitException $e) {
        echo json_encode(array("status"=>"error","msg"=>$e->getError()->message));
        exit;
    } catch (\Stripe\Exception\InvalidRequestException $e) {
        echo json_encode(array("status"=>"error","msg"=>$e->getError()->message));
        exit;
    } catch (\Stripe\Exception\AuthenticationException $e) {
        echo json_encode(array("status"=>"error","msg"=>$e->getError()->message));
        exit;
    } catch (\Stripe\Exception\ApiConnectionException $e) {
        echo json_encode(array("status"=>"error","msg"=>$e->getError()->message));
        exit;
    } catch (\Stripe\Exception\ApiErrorException $e) {
        echo json_encode(array("status"=>"error","msg"=>$e->getError()->message));
        exit;
    } catch (Exception $e) {
        echo json_encode(array("status"=>"error","msg"=>"Error, retry later."));
        exit;
    }
}

function create_price($id_product_stripe,$currency,$price,$frequency,$interval_count) {
    global $stripe;
    $currency = strtolower($currency);
    switch($currency) {
        case 'vnd':
        case 'jpy':
        case 'rwf':
        case 'pyg':
            $price = strval($price);
            break;
        default:
            $price = strval($price*100);
            break;
    }
    try {
        switch ($frequency) {
            case 'one_time':
                $response = $stripe->prices->create([
                    'unit_amount' => $price,
                    'currency' => $currency,
                    'product' => $id_product_stripe,
                ]);
                break;
            case 'recurring':
                $response = $stripe->prices->create([
                    'unit_amount' => $price,
                    'currency' => $currency,
                    'recurring' => ['interval' => 'month', 'interval_count' => $interval_count],
                    'product' => $id_product_stripe,
                ]);
                break;
        }
        $id = $response['id'];
        return $id;
    } catch(\Stripe\Exception\CardException $e) {
        echo json_encode(array("status"=>"error","msg"=>$e->getError()->message));
        exit;
    } catch (\Stripe\Exception\RateLimitException $e) {
        echo json_encode(array("status"=>"error","msg"=>$e->getError()->message));
        exit;
    } catch (\Stripe\Exception\InvalidRequestException $e) {
        echo json_encode(array("status"=>"error","msg"=>$e->getError()->message));
        exit;
    } catch (\Stripe\Exception\AuthenticationException $e) {
        echo json_encode(array("status"=>"error","msg"=>$e->getError()->message));
        exit;
    } catch (\Stripe\Exception\ApiConnectionException $e) {
        echo json_encode(array("status"=>"error","msg"=>$e->getError()->message));
        exit;
    } catch (\Stripe\Exception\ApiErrorException $e) {
        echo json_encode(array("status"=>"error","msg"=>$e->getError()->message));
        exit;
    } catch (Exception $e) {
        echo json_encode(array("status"=>"error","msg"=>"Error, retry later."));
        exit;
    }
}

function modify_price($id_price_stripe,$id_product_stripe,$currency,$price,$frequency,$interval_count) {
    global $stripe;
    $currency = strtolower($currency);
    switch($currency) {
        case 'vnd':
        case 'jpy':
        case 'rwf':
        case 'pyg':
            $price = strval($price);
            break;
        default:
            $price = strval($price*100);
            break;
    }
    try {
        $stripe_price = $stripe->prices->retrieve($id_price_stripe, []);
        $currency_exist = trim($stripe_price['currency']);
        $price_exist = trim($stripe_price['unit_amount']);
        $frquency_exist = trim($stripe_price['type']);
        $interval_count_exist = trim($stripe_price['recurring']['interval_count']);
        if(($currency_exist!=$currency) || ($price_exist!=$price) || ($interval_count_exist!=$interval_count) || ($frquency_exist!=$frequency)) {
            switch ($frequency) {
                case 'one_time':
                    $response = $stripe->prices->create([
                        'unit_amount' => $price,
                        'currency' => $currency,
                        'product' => $id_product_stripe,
                    ]);
                    break;
                case 'recurring':
                    $response = $stripe->prices->create([
                        'unit_amount' => $price,
                        'currency' => $currency,
                        'recurring' => ['interval' => 'month', 'interval_count' => $interval_count],
                        'product' => $id_product_stripe,
                    ]);
                    break;
            }
            $id = $response['id'];
            return $id;
        } else {
            return $id_price_stripe;
        }
    } catch(\Stripe\Exception\CardException $e) {
        echo json_encode(array("status"=>"error","msg"=>$e->getError()->message));
        exit;
    } catch (\Stripe\Exception\RateLimitException $e) {
        echo json_encode(array("status"=>"error","msg"=>$e->getError()->message));
        exit;
    } catch (\Stripe\Exception\InvalidRequestException $e) {
        echo json_encode(array("status"=>"error","msg"=>$e->getError()->message));
        exit;
    } catch (\Stripe\Exception\AuthenticationException $e) {
        echo json_encode(array("status"=>"error","msg"=>$e->getError()->message));
        exit;
    } catch (\Stripe\Exception\ApiConnectionException $e) {
        echo json_encode(array("status"=>"error","msg"=>$e->getError()->message));
        exit;
    } catch (\Stripe\Exception\ApiErrorException $e) {
        echo json_encode(array("status"=>"error","msg"=>$e->getError()->message));
        exit;
    } catch (Exception $e) {
        echo json_encode(array("status"=>"error","msg"=>"Error, retry later."));
        exit;
    }
}