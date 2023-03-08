<?php
error_reporting(E_ALL & ~E_WARNING & ~E_NOTICE & ~E_DEPRECATED);
session_start();
if(($_SERVER['SERVER_ADDR']=='5.9.29.89') && ($_SERVER['REMOTE_ADDR']!='87.4.143.150')) {
    $demo = true;
} else {
    $demo = false;
}
require_once("../backend/functions.php");
require_once("../db/connection.php");
require('../backend/vendor/stripe-php/init.php');
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
$id_user = $_SESSION['id_user'];
$settings = get_settings();
$user_info = get_user_info($id_user);
if(!empty($user_info['language'])) {
    set_language($user_info['language'],$settings['language_domain']);
} else {
    set_language($settings['language'],$settings['language_domain']);
}
$currentPath = $_SERVER['PHP_SELF'];
$pathInfo = pathinfo($currentPath);
$hostName = $_SERVER['HTTP_HOST'];
if (is_ssl()) { $protocol = 'https'; } else { $protocol = 'http'; }
$url = $protocol."://".$hostName.$pathInfo['dirname'];
$url = str_replace("/payments","",$url);

$key = $settings['stripe_secret_key'];
if(empty($key)) {
    exit;
}
$stripe = new \Stripe\StripeClient($key);
header('Content-Type: application/json');
$endpoint = $_POST['endpoint'];
switch ($endpoint) {
    case 'checkout_session':
        $id_plan = $_POST['id_plan'];
        $user = get_user_info($id_user);
        $plan = get_plan($id_plan);
        $id_customer_stripe = $user['id_customer_stripe'];
        $id_price_stripe = $plan['id_price_stripe'];
        $frequency = $plan['frequency'];
        $user_name = $user['username'];
        $user_email = $user['email'];
        if(empty($id_customer_stripe)) {
            $id_customer_stripe = create_customer($user_name,$user_email);
            $mysqli->query("UPDATE svt_users SET id_customer_stripe='$id_customer_stripe' WHERE id=$id_user;");
        } else {
            if(!check_if_customer_exist($id_customer_stripe)) {
                $id_customer_stripe = create_customer($user_name,$user_email);
                $mysqli->query("UPDATE svt_users SET id_customer_stripe='$id_customer_stripe' WHERE id=$id_user;");
            } else {
                modify_customer($id_customer_stripe,$user_name,$user_email);
            }
        }
        $checkout_session = create_checkout($url,$id_customer_stripe,$id_price_stripe,$id_plan,$frequency);
        echo json_encode(array("status"=>"ok","id" => $checkout_session->id));
        break;
    case 'setup_session':
        $user = get_user_info($id_user);
        $id_customer_stripe = $user['id_customer_stripe'];
        $user_name = $user['username'];
        $user_email = $user['email'];
        modify_customer($id_customer_stripe,$user_name,$user_email);
        $id_subscription_stripe = $user['id_subscription_stripe'];
        $setup_session = create_setup($url,$id_customer_stripe,$id_subscription_stripe);
        echo json_encode(array("status"=>"ok","id" => $setup_session->id));
        break;
    case 'cancel_subscription':
        if(!$demo) {
            $user = get_user_info($id_user);
            $id_subscription_stripe = $user['id_subscription_stripe'];
            $subscription = get_subscription($id_subscription_stripe);
            cancel_subscription($id_subscription_stripe);
            $end_date = $subscription->current_period_end;
            $end_date = date('Y-m-d H:i:s',$end_date);
            $result = $mysqli->query("UPDATE svt_users SET expire_plan_date='$end_date' WHERE id=$id_user;");
            if($result) {
                if($settings['notify_plan_cancels']) {
                    $query = "SELECT u.id,u.username,u.email,u.expire_plan_date,p.name as plan FROM svt_users as u LEFT JOIN svt_plans as p ON p.id=u.id_plan WHERE u.id=$id_user;";
                    $result = $mysqli->query($query);
                    if($result) {
                        if($result->num_rows>0) {
                            while($row=$result->fetch_array(MYSQLI_ASSOC)) {
                                $id_user = $row['id'];
                                $username = $row['username'];
                                $email_u = $row['email'];
                                $plan = $row['plan'];
                                $expire_plan_date = $row['expire_plan_date'];
                                $subject = _("Cancelled plan");
                                $body = _("Username").": $username<br>"._("E-Mail").": $email_u<br>"._("Plan").": $plan<br>"._("Expires on").": $expire_plan_date";
                                $subject_q = str_replace("'","\'",$subject);
                                $body_q = str_replace("'","\'",$body);
                                $mysqli->query("INSERT INTO svt_notifications(id_user,subject,body,notified) VALUES($id_user,'$subject_q','$body_q',0);");
                            }
                        }
                    }
                }
                echo json_encode(array("status"=>"ok"));
            } else {
                echo json_encode(array("status"=>"error","msg"=>"Error, retry later."));
            }
        } else {
            echo json_encode(array("status"=>"error","msg"=>"Demo mode, insufficent permission."));
        }
        break;
    case 'change_subscription':
        if(!$demo) {
            $id_plan = $_POST['id_plan'];
            $user = get_user_info($id_user);
            $plan = get_plan($id_plan);
            $query = "SELECT id_plan FROM svt_users WHERE id=$id_user LIMIT 1;";
            $result = $mysqli->query($query);
            $old_plan = "";
            if($result) {
                $row=$result->fetch_array(MYSQLI_ASSOC);
                $id_plan_old = $row['id_plan'];
                $plan_old = get_plan($id_plan_old);
                $old_plan = $plan_old['name'];
            }
            $id_customer_stripe = $user['id_customer_stripe'];
            $user_name = $user['username'];
            $user_email = $user['email'];
            modify_customer($id_customer_stripe,$user_name,$user_email);
            $id_price_stripe = $plan['id_price_stripe'];
            $id_subscription_stripe = $user['id_subscription_stripe'];
            $subscription = get_subscription($id_subscription_stripe);
            change_subscription($id_subscription_stripe,$subscription,$id_price_stripe);
            $result = $mysqli->query("UPDATE svt_users SET id_plan=$id_plan WHERE id=$id_user;");
            if($result) {
                if($settings['notify_plan_changes']) {
                    $query = "SELECT u.id,u.username,u.email,p.name as plan FROM svt_users as u LEFT JOIN svt_plans as p ON p.id=u.id_plan WHERE u.id=$id_user;";
                    $result = $mysqli->query($query);
                    if($result) {
                        if($result->num_rows>0) {
                            while($row=$result->fetch_array(MYSQLI_ASSOC)) {
                                $username = $row['username'];
                                $email_u = $row['email'];
                                $plan = $row['plan'];
                                $subject = _("Changed plan");
                                $body = _("Username").": $username<br>"._("E-Mail").": $email_u<br>"._("Old Plan").": $old_plan<br>"._("New Plan").": $plan";
                                $subject_q = str_replace("'","\'",$subject);
                                $body_q = str_replace("'","\'",$body);
                                $mysqli->query("INSERT INTO svt_notifications(id_user,subject,body,notified) VALUES($id_user,'$subject_q','$body_q',0);");
                            }
                        }
                    }
                }
                echo json_encode(array("status"=>"ok"));
            } else {
                echo json_encode(array("status"=>"error","msg"=>"Error, retry later."));
            }
        } else {
            echo json_encode(array("status"=>"error","msg"=>"Demo mode, insufficent permission."));
        }
        break;
    case 'payment_method':
        $user = get_user_info($id_user);
        $id_subscription_stripe = $user['id_subscription_stripe'];
        $subscription = get_subscription($id_subscription_stripe);
        $id_payment_method = $subscription->default_payment_method;
        $payment_method = get_payment_method($id_payment_method);
        $card = $payment_method->card->last4." (".$payment_method->card->brand.")";
        echo json_encode(array("status"=>"ok","card"=>$card));
        break;
    case 'proration':
        $id_plan = $_POST['id_plan'];
        $user = get_user_info($id_user);
        $plan = get_plan($id_plan);
        $id_customer_stripe = $user['id_customer_stripe'];
        $id_price_stripe = $plan['id_price_stripe'];
        $id_subscription_stripe = $user['id_subscription_stripe'];
        $interval_count = $plan['interval_count'];
        if($interval_count==1) {
            $recurring_label = _("month");
        } elseif($interval_count==12) {
            $recurring_label = _("year");
        } else {
            $recurring_label = $interval_count." "._("months");
        }
        $subscription = get_subscription($id_subscription_stripe);
        $invoice = get_proration($id_customer_stripe,$id_subscription_stripe,$subscription,$id_price_stripe);
        switch ($plan['currency']) {
            case 'AUD':
                $currency = "A$ ";
                $next_price = $currency.number_format($invoice->total/100,2,'.',' ')." (".date("d M Y",$invoice->next_payment_attempt).")";
                $subseq_price = $currency.number_format($plan['price'],2,'.',' ')." / ".$recurring_label;
                break;
            case 'BRL':
                $currency = "R$ ";
                $price = $currency.number_format($invoice->total/100,2,',','.')." (".date("d M Y",$invoice->next_payment_attempt).")";
                $subseq_price = $currency.number_format($plan['price'],2,',','.')." / ".$recurring_label;
                break;
            case 'CAD':
                $currency = "C$ ";
                $price = $currency.number_format($invoice->total/100,2,'.',',')." (".date("d M Y",$invoice->next_payment_attempt).")";
                $subseq_price = $currency.number_format($plan['price'],2,'.',',')." / ".$recurring_label;
                break;
            case 'CHF':
                $currency = "₣ ";
                $next_price = $currency.number_format($invoice->total/100,2,',','.')." (".date("d M Y",$invoice->next_payment_attempt).")";
                $subseq_price = $currency.number_format($plan['price'],2,',','.')." / ".$recurring_label;
                break;
            case 'CNY':
                $currency = "¥ ";
                $next_price = $currency.number_format($invoice->total/100,2,'.',',')." (".date("d M Y",$invoice->next_payment_attempt).")";
                $subseq_price = $currency.number_format($plan['price'],2,'.',',')." / ".$recurring_label;
                break;
            case 'CZK':
                $currency = "Kč ";
                $next_price = $currency.number_format($invoice->total/100,2,',','.')." (".date("d M Y",$invoice->next_payment_attempt).")";
                $subseq_price = $currency.number_format($plan['price'],2,',','.')." / ".$recurring_label;
                break;
            case 'JPY':
                $currency = "¥ ";
                $next_price = $currency.number_format($invoice->total,0,'.',',')." (".date("d M Y",$invoice->next_payment_attempt).")";
                $subseq_price = $currency.number_format($plan['price'],0,'.',',')." / ".$recurring_label;
                break;
            case 'EUR':
                $currency = "€ ";
                $next_price = $currency.number_format($invoice->total/100,2,',','.')." (".date("d M Y",$invoice->next_payment_attempt).")";
                $subseq_price = $currency.number_format($plan['price'],2,',','.')." / ".$recurring_label;
                break;
            case 'GBP':
                $currency = "£ ";
                $next_price = $currency.number_format($invoice->total/100,2,'.',',')." (".date("d M Y",$invoice->next_payment_attempt).")";
                $subseq_price = $currency.number_format($plan['price'],2,'.',',')." / ".$recurring_label;
                break;
            case 'IDR':
                $currency = "Rp ";
                $next_price = $currency.number_format($invoice->total/100,2,'.',',')." (".date("d M Y",$invoice->next_payment_attempt).")";
                $subseq_price = $currency.number_format($plan['price'],2,'.',',')." / ".$recurring_label;
                break;
            case 'INR':
                $currency = "Rs ";
                $next_price = $currency.number_format($invoice->total/100,2,'.',',')." (".date("d M Y",$invoice->next_payment_attempt).")";
                $subseq_price = $currency.number_format($plan['price'],2,'.',',')." / ".$recurring_label;
                break;
            case 'PLN':
                $currency = "zł ";
                $next_price = $currency.number_format($invoice->total/100,2,',','.')." (".date("d M Y",$invoice->next_payment_attempt).")";
                $subseq_price = $currency.number_format($plan['price'],2,',','.')." / ".$recurring_label;
                break;
            case 'SEK':
                $currency = "kr ";
                $next_price = $currency.number_format($invoice->total/100,2,',','.')." (".date("d M Y",$invoice->next_payment_attempt).")";
                $subseq_price = $currency.number_format($plan['price'],2,',','.')." / ".$recurring_label;
                break;
            case 'TRY':
                $currency = "₺ ";
                $next_price = $currency.number_format($invoice->total/100,2,'.',',')." (".date("d M Y",$invoice->next_payment_attempt).")";
                $subseq_price = $currency.number_format($plan['price'],2,'.',',')." / ".$recurring_label;
                break;
            case 'TJS':
                $currency = "SM ";
                $next_price = $currency.number_format($invoice->total/100,2,'.',',')." (".date("d M Y",$invoice->next_payment_attempt).")";
                $subseq_price = $currency.number_format($plan['price'],2,'.',',')." / ".$recurring_label;
                break;
            case 'USD':
            case 'ARS':
                $currency = "$ ";
                $next_price = $currency.number_format($invoice->total/100,2,'.',',')." (".date("d M Y",$invoice->next_payment_attempt).")";
                $subseq_price = $currency.number_format($plan['price'],2,'.',',')." / ".$recurring_label;
                break;
            case 'HKD':
                $currency = "HK$ ";
                $next_price = $currency.number_format($invoice->total/100,2,'.',',')." (".date("d M Y",$invoice->next_payment_attempt).")";
                $subseq_price = $currency.number_format($plan['price'],2,'.',',')." / ".$recurring_label;
                break;
            case 'MXN':
                $currency = "Mex$ ";
                $next_price = $currency.number_format($invoice->total/100,2,'.',',')." (".date("d M Y",$invoice->next_payment_attempt).")";
                $subseq_price = $currency.number_format($plan['price'],2,',','.')." / ".$recurring_label;
                break;
            case 'PHP':
                $currency = "₱ ";
                $next_price = $currency.number_format($invoice->total/100,2,'.',',')." (".date("d M Y",$invoice->next_payment_attempt).")";
                $subseq_price = $currency.number_format($plan['price'],2,'.',',')." / ".$recurring_label;
                break;
            case 'THB':
                $currency = "฿ ";
                $next_price = $currency.number_format($invoice->total/100,2,'.',',')." (".date("d M Y",$invoice->next_payment_attempt).")";
                $subseq_price = $currency.number_format($plan['price'],2,'.',',')." / ".$recurring_label;
                break;
            case 'RWF':
                $currency = "FRw ";
                $next_price = $currency.number_format($invoice->total,0,'',',')." (".date("d M Y",$invoice->next_payment_attempt).")";
                $subseq_price = $currency.number_format($plan['price'],0,'',',')." / ".$recurring_label;
                break;
            case 'VND':
                $currency = "₫ ";
                $next_price = $currency.number_format($invoice->total,0,'.',',')." (".date("d M Y",$invoice->next_payment_attempt).")";
                $subseq_price = $currency.number_format($plan['price'],0,'.',',')." / ".$recurring_label;
                break;
            case 'PYG':
                $currency = "₲ ";
                $next_price = $currency.number_format($invoice->total,0,'.',',')." (".date("d M Y",$invoice->next_payment_attempt).")";
                $subseq_price = $currency.number_format($plan['price'],0,'.',',')." / ".$recurring_label;
                break;
        }
        echo json_encode(array("status"=>"ok","plan"=>$plan,"next_price"=>$next_price,"subseq_price"=>$subseq_price));
        break;
    case 'reactivate_subscription':
        if(!$demo) {
            $user = get_user_info($id_user);
            $id_customer_stripe = $user['id_customer_stripe'];
            $user_name = $user['username'];
            $user_email = $user['email'];
            modify_customer($id_customer_stripe,$user_name,$user_email);
            $id_subscription_stripe = $user['id_subscription_stripe'];
            $subscription = get_subscription($id_subscription_stripe);
            reactivate_subscription($id_subscription_stripe);
            $result = $mysqli->query("UPDATE svt_users SET expire_plan_date=NULL WHERE id=$id_user;");
            if ($result) {
                echo json_encode(array("status" => "ok"));
            } else {
                echo json_encode(array("status" => "error", "msg" => "Error, retry later."));
            }
        } else {
            echo json_encode(array("status"=>"error","msg"=>"Demo mode, insufficent permission."));
        }
        break;
    case 'subscription_end_date':
        $user = get_user_info($id_user);
        $id_subscription_stripe = $user['id_subscription_stripe'];
        $subscription = get_subscription($id_subscription_stripe);
        $end_date = $subscription->current_period_end;
        $end_date = date('d M Y',$end_date);
        $id_product_stripe = $subscription->items->data[0]->price->product;
        $name_plan = get_name_plan_stripe($id_product_stripe);
        echo json_encode(array("status"=>"ok","end_date"=>$end_date,"name"=>$name_plan));
        break;
}

function check_if_customer_exist($id) {
    global $stripe;
    try {
        $response = $stripe->customers->retrieve($id, []);
        if($response['deleted']==1) {
            return false;
        } else {
            return true;
        }
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

function create_customer($name,$email) {
    global $stripe;
    try {
        $response = $stripe->customers->create([
            'name' => $name,
            'email' => $email
        ]);
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

function modify_customer($id_customer_stripe,$name,$email) {
    global $stripe;
    try {
        $stripe->customers->update($id_customer_stripe,
            ['name' => $name, 'email' => $email]
        );
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

function create_checkout($url,$id_customer_stripe,$id_price_stripe,$id_plan,$frequency) {
    global $stripe;
    $mode = "subscription";
    if($frequency=="one_time") {
        $mode = "payment";
    }
    try {
        $response = $stripe->checkout->sessions->create([
            'success_url' => $url.'/backend/index.php?p=change_plan&response=success',
            'cancel_url' => $url.'/backend/index.php?p=change_plan&response=cancel',
            'payment_method_types' => ['card'],
            'customer' => $id_customer_stripe,
            'line_items' => [
                [
                    'price' => $id_price_stripe,
                    'quantity' => 1,
                ],
            ],
            'mode' => $mode,
            'billing_address_collection' => 'required',
            'metadata' => [
                'id_plan' => $id_plan
            ]
        ]);
        return $response;
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

function create_setup($url,$id_customer_stripe,$id_subscription_stripe) {
    global $stripe;
    try {
        $response = $stripe->checkout->sessions->create([
            'payment_method_types' => ['card'],
            'mode' => 'setup',
            'customer' => $id_customer_stripe,
            'setup_intent_data' => [
                'metadata' => [
                    'customer_id' => $id_customer_stripe,
                    'subscription_id' => $id_subscription_stripe,
                ],
            ],
            'billing_address_collection' => 'required',
            'success_url' => $url.'/backend/index.php',
            'cancel_url' => $url.'/backend/index.php',
        ]);
        return $response;
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

function get_subscription($id) {
    global $stripe;
    try {
        $response = $stripe->subscriptions->retrieve($id);
        return $response;
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

function cancel_subscription($id) {
    global $stripe;
    try {
        $stripe->subscriptions->update($id, [
            "cancel_at_period_end"=> true
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

function change_subscription($id,$subscription,$id_price_stripe) {
    global $stripe;
    try {
        $stripe->subscriptions->update($id, [
                'cancel_at_period_end' => false,
                'proration_behavior' => 'create_prorations',
                'items' => [
                    [
                        'id' => $subscription->items->data[0]->id,
                        'price' => $id_price_stripe,
                    ],
                ]
            ]
        );
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

function get_payment_method($id) {
    global $stripe;
    try {
        $response = $stripe->paymentMethods->retrieve($id, []);
        return $response;
        return $response;
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

function get_proration($id_customer_stripe,$id_subscription_stripe,$subscription,$id_price_stripe) {
    global $stripe;
    try {
        $response = $stripe->invoices->upcoming([
            "customer" => $id_customer_stripe,
            "subscription" => $id_subscription_stripe,
            "subscription_items" => [
                [
                    'id' => $subscription->items->data[0]->id,
                    'price' => $id_price_stripe,
                ],
            ]
        ]);
        return $response;
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

function reactivate_subscription($id) {
    global $stripe;
    try {
        $stripe->subscriptions->update($id, [
            "cancel_at"=> ""
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