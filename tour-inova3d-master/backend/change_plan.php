<?php
session_start();
require_once("functions.php");
$id_user = $_SESSION['id_user'];
$r0='';if(array_key_exists(base64_decode('U0VSVkVSX0FERFI='),$_SERVER)){$r0=$_SERVER[base64_decode('U0VSVkVSX0FERFI=')];if(!filter_var($r0,FILTER_VALIDATE_IP,FILTER_FLAG_IPV4)){$r0=gethostbyname($_SERVER[base64_decode('U0VSVkVSX05BTUU=')]);}}elseif(array_key_exists(base64_decode('TE9DQUxfQUREUg=='),$_SERVER)){$r0=$_SERVER[base64_decode('TE9DQUxfQUREUg==')];}elseif(array_key_exists(base64_decode('U0VSVkVSX05BTUU='),$_SERVER)){$r0=gethostbyname($_SERVER[base64_decode('U0VSVkVSX05BTUU=')]);}else{if(stristr(PHP_OS,base64_decode('V0lO'))){$r0=gethostbyname(php_uname(base64_decode('bg==')));}else{$u1=shell_exec(base64_decode('L3NiaW4vaWZjb25maWcgZXRoMA=='));preg_match(base64_decode('L2FkZHI6KFtcZFwuXSspLw=='),$u1,$a2);$r0=$a2[1];}}echo base64_decode('PGlucHV0IHR5cGU9J2hpZGRlbicgaWQ9J3ZsZmMnIC8+');$v3=get_settings();$o5=$r0.base64_decode('UlI=').$v3[base64_decode('cHVyY2hhc2VfY29kZQ==')];$v6=password_verify($o5,$v3[base64_decode('bGljZW5zZQ==')]);$o5=$r0.base64_decode('UkU=').$v3[base64_decode('cHVyY2hhc2VfY29kZQ==')];$w7=password_verify($o5,$v3[base64_decode('bGljZW5zZQ==')]);$o5=$r0.base64_decode('RQ==').$v3[base64_decode('cHVyY2hhc2VfY29kZQ==')];$r8=password_verify($o5,$v3[base64_decode('bGljZW5zZQ==')]);if($v6){include(base64_decode('bGljZW5zZS5waHA='));exit;}else if(($r8)||($w7)){}else{include(base64_decode('bGljZW5zZS5waHA='));exit;}
$plans = get_plans($id_user);
$user_info = get_user_info($id_user);
$current_plan = $user_info['id_plan'];
$settings = get_settings();
$app_name = $settings['name'];
$stripe_enabled = $settings['stripe_enabled'];
$stripe_secret_key = $settings['stripe_secret_key'];
$stripe_public_key = $settings['stripe_public_key'];
$paypal_enabled = $settings['paypal_enabled'];
$paypal_client_id = $settings['paypal_client_id'];
$paypal_client_secret = $settings['paypal_client_secret'];
if((empty($stripe_public_key)) || (empty($stripe_secret_key))) {
    $stripe_enabled = 0;
}
if((empty($paypal_client_id)) || (empty($paypal_client_secret))) {
    $paypal_enabled = 0;
}
if($paypal_enabled) {
    $stripe_enabled = 0;
}
$expiring_plan = false;
if(!empty($user_info['expire_plan_date']) && (!empty($user_info['id_subscription_stripe'] || !empty($user_info['id_subscription_paypal'])))) {
    $expiring_plan = true;
}
if(!empty($current_plan)) {
    foreach ($plans as $plan) {
        if($plan['id']==$current_plan) {
            $current_frequency = $plan['frequency'];
        }
    }
} else {
    $current_frequency = null;
}
if($paypal_enabled) {
    $recurring_count=0;
    $onetime_count=0;
    $currency_paypal = $plans[0]['currency'];
    foreach ($plans as $plan) {
        if($plan['price']>0) {
            switch($plan['frequency']) {
                case 'recurring':
                    $recurring_count++;
                    break;
                case 'one_time':
                    $onetime_count++;
                    break;
            }
        }
    }
    foreach ($plans as $index=>$plan) {
        if($plan['price']>0) {
            switch($plan['frequency']) {
                case 'recurring':
                    if($recurring_count<$onetime_count) { unset($plans[$index]); }
                    break;
                case 'one_time':
                    if($recurring_count>=$onetime_count) { unset($plans[$index]); }
                    break;
            }
        }
    }
}
?>

<?php if($stripe_enabled) : ?>
    <script src="https://js.stripe.com/v3/"></script>
<?php endif; ?>

<?php if($paypal_enabled) : ?>
    <?php if($recurring_count>=$onetime_count) { ?>
        <script src="https://www.paypal.com/sdk/js?client-id=<?php echo $paypal_client_id; ?>&vault=true&intent=subscription"></script>
    <?php } else { ?>
        <script src="https://www.paypal.com/sdk/js?client-id=<?php echo $paypal_client_id; ?>&currency=<?php echo $currency_paypal; ?>" data-sdk-integration-source="button-factory"></script>
    <?php } ?>
<?php endif; ?>

<?php if($_SERVER['SERVER_ADDR']=='5.9.29.89') : ?>
    <div class="card bg-warning text-white shadow mb-3">
        <div class="card-body">
            <?php echo _("It is not possible to subscribe on this demo server. This section is shown for demonstration purposes only. Buy the code <a style='color:white;text-decoration:underline;font-weight:bold;' target='_blank' href='https://1.envato.market/Jrja9r'>here</a> "); ?>
        </div>
    </div>
<?php endif; ?>

<div class="text-center mb-4">
    <h3 class="text-primary mb-2"><?php echo _("Choose a pricing plan"); ?></h3>
    <h4><?php echo _("Pick what's right for you"); ?></h4>
</div>
<div class="pricing-columns">
    <div class="row justify-content-center">
        <?php foreach ($plans as $plan) { ?>
            <div class="col-xl-4 col-lg-6 mb-4">
                <div class="card h-100 noselect" style="<?php echo ($plan['id']==$current_plan) ? 'border: 1px solid #4f73df;' : '' ; ?>">
                    <div class="card-header bg-transparent">
                        <span class="badge badge-primary-soft text-primary badge-pill py-2 px-3 mb-2"><?php echo $plan['name']; ?></span>
                        <?php if($plan['days']>0) {
                            echo '<span class="float-right text-gray-500">'.sprintf(_('expires in %s days'),$plan['days']).'</span>';
                        } ?>
                        <div class="pricing-columns-price">
                            <b>
                                <?php
                                $price = format_currency($plan['currency'],$plan['price']);
                                if($plan['price']==0) $price=_("Free");
                                echo $price;
                                ?>
                            </b>
                            <span><?php
                                $interval_count = $plan['interval_count'];
                                if($plan['price']>0 && $plan['frequency']=='recurring') {
                                    if($interval_count==1) {
                                        $recurring_label = "/ "._("month");
                                    } elseif($interval_count==12) {
                                        $recurring_label = "/ "._("year");
                                    } else {
                                        $recurring_label = "/ ".$interval_count." "._("months");
                                    }
                                } else {
                                    $recurring_label="";
                                }
                                echo $recurring_label;
                                ?></span>
                        </div>
                    </div>
                    <div style="flex:0 0 auto;" class="card-body p-0">
                        <ul class="list-group list-group-flush">
                            <li class="list-group-item">
                                <i class="far fa-check-circle text-primary"></i>
                                <?php echo '<b>'.(($plan['n_virtual_tours']==-1) ? '<i class="fas fa-infinity"></i>' : $plan['n_virtual_tours']).'</b> '._("Virtual Tours"); ?>
                            </li>
                            <li class="list-group-item">
                                <i class="far fa-check-circle text-primary"></i>
                                <?php echo '<b>'.(($plan['n_rooms']==-1) ? '<i class="fas fa-infinity"></i>' : $plan['n_rooms']).'</b> '._("Rooms"); ?>
                            </li>
                            <li class="list-group-item">
                                <i class="far fa-check-circle text-primary"></i>
                                <?php echo '<b>'.(($plan['n_markers']==-1) ? '<i class="fas fa-infinity"></i>' : $plan['n_markers']).'</b> '._("Markers"); ?>
                            </li>
                            <li class="list-group-item">
                                <i class="far fa-check-circle text-primary"></i>
                                <?php echo '<b>'.(($plan['n_pois']==-1) ? '<i class="fas fa-infinity"></i>' : $plan['n_pois']).'</b> '._("POIs"); ?>
                            </li>
                            <li class="list-group-item">
                                <i class="far fa-check-circle text-primary"></i>
                                <?php echo '<b>'.(($plan['max_file_size_upload']==-1) ? '<i class="fas fa-infinity"></i>' : (($plan['max_file_size_upload']>=1000) ? ($plan['max_file_size_upload']/1000)." GB" : $plan['max_file_size_upload']." MB" )).'</b> '._("Panorama Upload Size"); ?>
                            </li>
                            <li class="list-group-item">
                                <i class="far fa-check-circle text-primary"></i>
                                <?php echo '<b>'.(($plan['max_storage_space']==-1) ? '<i class="fas fa-infinity"></i>' : (($plan['max_storage_space']>=1000) ? ($plan['max_storage_space']/1000)." GB" : $plan['max_storage_space']." MB" )).'</b> '._("Storage Quota"); ?>
                            </li>
                            <li class="list-group-item text-center">
                                <a class="show_more text-decoration-none" href="#" data-toggle="collapse" data-target=".collapse_all"><?php echo _("show features"); ?> <i class="fas fa-caret-down"></i></a>
                                <a class="show_less text-decoration-none" href="#" data-toggle="collapse" data-target=".collapse_all" style="display: none"><?php echo _("hide features"); ?> <i class="fas fa-caret-up"></i></a>
                            </li>
                            <div class="collapse collapse_all">
                                <li class="list-group-item" style="<?php echo ($plan['enable_info_box']==0) ? 'opacity:0.5' : ''; ?>">
                                    <i class="far <?php echo ($plan['enable_info_box']==1) ? 'fa-check-circle text-primary' : 'fa-times-circle text-black-50'; ?>"></i>
                                    <?php echo _("Info Box"); ?>
                                </li>
                                <li class="list-group-item" style="<?php echo ($plan['create_gallery']==0) ? 'opacity:0.5' : ''; ?>">
                                    <i class="far <?php echo ($plan['create_gallery']==1) ? 'fa-check-circle text-primary' : 'fa-times-circle text-black-50'; ?>"></i>
                                    <?php echo _("Gallery"); ?>
                                </li>
                                <li class="list-group-item" style="<?php echo ($plan['create_landing']==0) ? 'opacity:0.5' : ''; ?>">
                                    <i class="far <?php echo ($plan['create_landing']==1) ? 'fa-check-circle text-primary' : 'fa-times-circle text-black-50'; ?>"></i>
                                    <?php echo _("Landing"); ?>
                                </li>
                                <li class="list-group-item" style="<?php echo ($plan['create_showcase']==0) ? 'opacity:0.5' : ''; ?>">
                                    <i class="far <?php echo ($plan['create_showcase']==1) ? 'fa-check-circle text-primary' : 'fa-times-circle text-black-50'; ?>"></i>
                                    <?php echo _("Showcase"); ?>
                                </li>
                                <li class="list-group-item" style="<?php echo ($plan['create_presentation']==0) ? 'opacity:0.5' : ''; ?>">
                                    <i class="far <?php echo ($plan['create_presentation']==1) ? 'fa-check-circle text-primary' : 'fa-times-circle text-black-50'; ?>"></i>
                                    <?php echo _("Presentation"); ?>
                                </li>
                                <li class="list-group-item" style="<?php echo ($plan['enable_live_session']==0) ? 'opacity:0.5' : ''; ?>">
                                    <i class="far <?php echo ($plan['enable_live_session']==1) ? 'fa-check-circle text-primary' : 'fa-times-circle text-black-50'; ?>"></i>
                                    <?php echo _("Live Session"); ?>
                                </li>
                                <li class="list-group-item" style="<?php echo ($plan['enable_meeting']==0) ? 'opacity:0.5' : ''; ?>">
                                    <i class="far <?php echo ($plan['enable_meeting']==1) ? 'fa-check-circle text-primary' : 'fa-times-circle text-black-50'; ?>"></i>
                                    <?php echo _("Meeting"); ?>
                                </li>
                                <li class="list-group-item" style="<?php echo ($plan['enable_maps']==0) ? 'opacity:0.5' : ''; ?>">
                                    <i class="far <?php echo ($plan['enable_maps']==1) ? 'fa-check-circle text-primary' : 'fa-times-circle text-black-50'; ?>"></i>
                                    <?php echo _("Maps"); ?>
                                </li>
                                <li class="list-group-item" style="<?php echo ($plan['enable_chat']==0) ? 'opacity:0.5' : ''; ?>">
                                    <i class="far <?php echo ($plan['enable_chat']==1) ? 'fa-check-circle text-primary' : 'fa-times-circle text-black-50'; ?>"></i>
                                    <?php echo _("Facebook / Whatsapp Chat"); ?>
                                </li>
                                <li class="list-group-item" style="<?php echo ($plan['enable_voice_commands']==0) ? 'opacity:0.5' : ''; ?>">
                                    <i class="far <?php echo ($plan['enable_voice_commands']==1) ? 'fa-check-circle text-primary' : 'fa-times-circle text-black-50'; ?>"></i>
                                    <?php echo _("Voice Commands"); ?>
                                </li>
                                <li class="list-group-item" style="<?php echo ($plan['enable_share']==0) ? 'opacity:0.5' : ''; ?>">
                                    <i class="far <?php echo ($plan['enable_share']==1) ? 'fa-check-circle text-primary' : 'fa-times-circle text-black-50'; ?>"></i>
                                    <?php echo _("Share"); ?>
                                </li>
                                <li class="list-group-item" style="<?php echo ($plan['enable_context_info']==0) ? 'opacity:0.5' : ''; ?>">
                                    <i class="far <?php echo ($plan['enable_context_info']==1) ? 'fa-check-circle text-primary' : 'fa-times-circle text-black-50'; ?>"></i>
                                    <?php echo _("Right Click Content"); ?>
                                </li>
                                <li class="list-group-item" style="<?php echo ($plan['enable_device_orientation']==0) ? 'opacity:0.5' : ''; ?>">
                                    <i class="far <?php echo ($plan['enable_device_orientation']==1) ? 'fa-check-circle text-primary' : 'fa-times-circle text-black-50'; ?>"></i>
                                    <?php echo _("Device Orientation"); ?>
                                </li>
                                <li class="list-group-item" style="<?php echo ($plan['enable_webvr']==0) ? 'opacity:0.5' : ''; ?>">
                                    <i class="far <?php echo ($plan['enable_webvr']==1) ? 'fa-check-circle text-primary' : 'fa-times-circle text-black-50'; ?>"></i>
                                    <?php echo _("Virtual Reality"); ?>
                                </li>
                                <li class="list-group-item" style="<?php echo ($plan['enable_logo']==0) ? 'opacity:0.5' : ''; ?>">
                                    <i class="far <?php echo ($plan['enable_logo']==1) ? 'fa-check-circle text-primary' : 'fa-times-circle text-black-50'; ?>"></i>
                                    <?php echo _("Your own Logo"); ?>
                                </li>
                                <li class="list-group-item" style="<?php echo ($plan['enable_nadir_logo']==0) ? 'opacity:0.5' : ''; ?>">
                                    <i class="far <?php echo ($plan['enable_nadir_logo']==1) ? 'fa-check-circle text-primary' : 'fa-times-circle text-black-50'; ?>"></i>
                                    <?php echo _("Hide Tripod"); ?>
                                </li>
                                <li class="list-group-item" style="<?php echo ($plan['enable_song']==0) ? 'opacity:0.5' : ''; ?>">
                                    <i class="far <?php echo ($plan['enable_song']==1) ? 'fa-check-circle text-primary' : 'fa-times-circle text-black-50'; ?>"></i>
                                    <?php echo _("Background Music"); ?>
                                </li>
                                <li class="list-group-item" style="<?php echo ($plan['enable_forms']==0) ? 'opacity:0.5' : ''; ?>">
                                    <i class="far <?php echo ($plan['enable_forms']==1) ? 'fa-check-circle text-primary' : 'fa-times-circle text-black-50'; ?>"></i>
                                    <?php echo _("Forms"); ?>
                                </li>
                                <li class="list-group-item" style="<?php echo ($plan['enable_annotations']==0) ? 'opacity:0.5' : ''; ?>">
                                    <i class="far <?php echo ($plan['enable_annotations']==1) ? 'fa-check-circle text-primary' : 'fa-times-circle text-black-50'; ?>"></i>
                                    <?php echo _("Annotations"); ?>
                                </li>
                                <li class="list-group-item" style="<?php echo ($plan['enable_panorama_video']==0) ? 'opacity:0.5' : ''; ?>">
                                    <i class="far <?php echo ($plan['enable_panorama_video']==1) ? 'fa-check-circle text-primary' : 'fa-times-circle text-black-50'; ?>"></i>
                                    <?php echo _("Video 360"); ?>
                                </li>
                                <li class="list-group-item" style="<?php echo ($plan['enable_rooms_multiple']==0) ? 'opacity:0.5' : ''; ?>">
                                    <i class="far <?php echo ($plan['enable_rooms_multiple']==1) ? 'fa-check-circle text-primary' : 'fa-times-circle text-black-50'; ?>"></i>
                                    <?php echo _("Multiple Room's Views"); ?>
                                </li>
                                <li class="list-group-item" style="<?php echo ($plan['enable_password_tour']==0) ? 'opacity:0.5' : ''; ?>">
                                    <i class="far <?php echo ($plan['enable_password_tour']==1) ? 'fa-check-circle text-primary' : 'fa-times-circle text-black-50'; ?>"></i>
                                    <?php echo _("Protect tour (Password)"); ?>
                                </li>
                                <li class="list-group-item" style="<?php echo ($plan['enable_rooms_protect']==0) ? 'opacity:0.5' : ''; ?>">
                                    <i class="far <?php echo ($plan['enable_rooms_protect']==1) ? 'fa-check-circle text-primary' : 'fa-times-circle text-black-50'; ?>"></i>
                                    <?php echo _("Protect Rooms (Passcode, Leads)"); ?>
                                </li>
                                <li class="list-group-item" style="<?php echo ($plan['enable_icons_library']==0) ? 'opacity:0.5' : ''; ?>">
                                    <i class="far <?php echo ($plan['enable_icons_library']==1) ? 'fa-check-circle text-primary' : 'fa-times-circle text-black-50'; ?>"></i>
                                    <?php echo _("Custom Icons"); ?>
                                </li>
                                <li class="list-group-item" style="<?php echo ($plan['enable_expiring_dates']==0) ? 'opacity:0.5' : ''; ?>">
                                    <i class="far <?php echo ($plan['enable_expiring_dates']==1) ? 'fa-check-circle text-primary' : 'fa-times-circle text-black-50'; ?>"></i>
                                    <?php echo _("Expiring Dates"); ?>
                                </li>
                                <li class="list-group-item" style="<?php echo ($plan['enable_statistics']==0) ? 'opacity:0.5' : ''; ?>">
                                    <i class="far <?php echo ($plan['enable_statistics']==1) ? 'fa-check-circle text-primary' : 'fa-times-circle text-black-50'; ?>"></i>
                                    <?php echo _("Statistics"); ?>
                                </li>
                                <li class="list-group-item" style="<?php echo ($plan['enable_flyin']==0) ? 'opacity:0.5' : ''; ?>">
                                    <i class="far <?php echo ($plan['enable_flyin']==1) ? 'fa-check-circle text-primary' : 'fa-times-circle text-black-50'; ?>"></i>
                                    <?php echo _("Fly-In Animation"); ?>
                                </li>
                                <li class="list-group-item" style="<?php echo ($plan['enable_auto_rotate']==0) ? 'opacity:0.5' : ''; ?>">
                                    <i class="far <?php echo ($plan['enable_auto_rotate']==1) ? 'fa-check-circle text-primary' : 'fa-times-circle text-black-50'; ?>"></i>
                                    <?php echo _("Auto Rotation"); ?>
                                </li>
                                <li class="list-group-item" style="<?php echo ($plan['enable_multires']==0) ? 'opacity:0.5' : ''; ?>">
                                    <i class="far <?php echo ($plan['enable_multires']==1) ? 'fa-check-circle text-primary' : 'fa-times-circle text-black-50'; ?>"></i>
                                    <?php echo _("Multi-Resolution"); ?>
                                </li>
                                <li class="list-group-item" style="<?php echo ($plan['enable_export_vt']==0) ? 'opacity:0.5' : ''; ?>">
                                    <i class="far <?php echo ($plan['enable_export_vt']==1) ? 'fa-check-circle text-primary' : 'fa-times-circle text-black-50'; ?>"></i>
                                    <?php echo _("Download Tour"); ?>
                                </li>
                                <li class="list-group-item" style="<?php echo ($plan['enable_shop']==0) ? 'opacity:0.5' : ''; ?>">
                                    <i class="far <?php echo ($plan['enable_shop']==1) ? 'fa-check-circle text-primary' : 'fa-times-circle text-black-50'; ?>"></i>
                                    <?php echo _("Shop"); ?>
                                </li>
                                <li class="list-group-item" style="<?php echo ($plan['enable_dollhouse']==0) ? 'opacity:0.5' : ''; ?>">
                                    <i class="far <?php echo ($plan['enable_dollhouse']==1) ? 'fa-check-circle text-primary' : 'fa-times-circle text-black-50'; ?>"></i>
                                    <?php echo _("3D View"); ?>
                                </li>
                                <?php
                                $custom_features = $plan['custom_features'];
                                $custom_features_array = explode("\n", $custom_features);
                                foreach ($custom_features_array as $custom_feature) {
                                    if(!empty($custom_feature)) {
                                        echo '<li class="list-group-item">
                                    <i class="far fa-check-circle text-primary"></i>
                                    '.$custom_feature.'
                                    </li>';
                                    }
                                } ?>
                            </div>
                        </ul>
                    </div>
                    <?php if(($plan['id']==$current_plan && !$paypal_enabled) || ($plan['id']==$current_plan && $paypal_enabled && ($user_info['plan_status']=='expiring' || $user_info['plan_status']=='active'))) { ?>
                        <?php if($stripe_enabled && $expiring_plan) { ?>
                        <a onclick="open_modal_reactivate_subscription();" style="color: #4e73df;" class="card-footer d-flex align-items-center justify-content-between text-decoration-none bg-primary-soft" href="#">
                            <?php echo _("Reactivate Subscription"); ?>
                            <i class="fas fa-sync-alt"></i>
                        </a>
                    <?php } else if($paypal_enabled && $user_info['plan_status']=='expiring') { ?>
                        <div style="color: #4e73df;" class="card-footer d-flex align-items-center justify-content-between text-decoration-none bg-primary-soft">
                            <?php echo _("Current Subscription (Canceled)"); ?>
                            <i class="fa fa-check"></i>
                        </div>
                    <?php } else { ?>
                        <div style="color: #4e73df;" class="card-footer d-flex align-items-center justify-content-between text-decoration-none bg-primary-soft">
                            <?php echo _("Current Subscription"); ?>
                            <i class="fa fa-check"></i>
                        </div>
                    <?php } ?>
                    <?php } else { ?>
                    <?php if($stripe_enabled) { ?>
                    <?php if($plan['price']==0) { ?>
                    <?php if(!empty($plan['external_url'])) { ?>
                        <a class="card-footer d-flex align-items-center justify-content-between text-decoration-none bg-primary-soft" target="_blank" href="<?php echo $plan['external_url']; ?>">
                            <?php echo _("Find out more"); ?>
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-arrow-right"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg>
                        </a>
                    <?php } else { ?>
                        <a class="card-footer d-flex align-items-center justify-content-between text-decoration-none bg-primary-soft" href="mailto:<?php echo $settings['contact_email']; ?>?subject=<?php echo $plan['name']; ?>">
                            <?php echo _("Contact Us"); ?>
                            <i class="fas fa-envelope"></i>
                        </a>
                    <?php } ?>
                    <?php } else { ?>
                        <?php if(empty($user_info['id_subscription_stripe'])) { ?>
                        <a onclick="redirect_to_checkout(<?php echo $plan['id']; ?>);return false;" class="card-footer d-flex align-items-center justify-content-between text-decoration-none bg-primary-soft <?php echo ($expiring_plan) ? 'disabled' : ''; ?>" href="#">
                            <?php echo _("Subscribe"); ?>
                            <i class="fas fa-shopping-bag"></i>
                        </a>
                    <?php } else { ?>
                        <a onclick="change_plan_proration(<?php echo $plan['id']; ?>);return false;" class="card-footer d-flex align-items-center justify-content-between text-decoration-none bg-primary-soft <?php echo ($current_frequency=='recurring' && $plan['frequency']=='one_time') ? 'disabled' : ''; ?> <?php echo ($expiring_plan) ? 'disabled' : ''; ?>" href="#">
                            <?php echo _("Change Subscription"); ?>
                            <i class="fas fa-exchange-alt"></i>
                        </a>
                    <?php } ?>
                    <?php } ?>
                    <?php } else if($paypal_enabled) { ?>
                    <?php if($plan['price']==0) { ?>
                    <?php if(!empty($plan['external_url'])) { ?>
                        <a class="card-footer d-flex align-items-center justify-content-between text-decoration-none bg-primary-soft" target="_blank" href="<?php echo $plan['external_url']; ?>">
                            <?php echo _("Find out more"); ?>
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-arrow-right"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg>
                        </a>
                    <?php } else { ?>
                        <a class="card-footer d-flex align-items-center justify-content-between text-decoration-none bg-primary-soft" href="mailto:<?php echo $settings['contact_email']; ?>?subject=<?php echo $plan['name']; ?>">
                            <?php echo _("Contact Us"); ?>
                            <i class="fas fa-envelope"></i>
                        </a>
                    <?php } ?>
                    <?php } else { ?>
                    <?php if($plan['frequency']=='recurring') { ?>
                    <?php if(empty($user_info['id_subscription_paypal'])) { ?>
                        <div style="display:contents" id="paypal_button_<?php echo $plan['id']; ?>"></div>
                        <script>
                            paypal.Buttons({
                                style: {
                                    layout: 'vertical',
                                    color: 'blue',
                                    shape: 'rect',
                                    label: 'subscribe',
                                    tagline: false,
                                    height: 49
                                },
                                createSubscription: function(data, actions) {
                                    return actions.subscription.create({
                                        'plan_id': '<?php echo $plan['id_plan_paypal']; ?>'
                                    });
                                },
                                onApprove: function(data, actions) {
                                    save_paypal_subscription_id(<?php echo $id_user; ?>,'subscription',data.subscriptionID);
                                }
                            }).render('#paypal_button_<?php echo $plan['id']; ?>');
                        </script>
                    <?php } else { ?>
                        <a data-toggle="modal" data-target="#modal_change_plan_paypal" class="card-footer d-flex align-items-center justify-content-between text-decoration-none bg-primary-soft <?php echo ($current_frequency=='recurring' && $plan['frequency']=='one_time') ? 'disabled' : ''; ?> <?php echo ($expiring_plan) ? 'disabled' : ''; ?>" href="#">
                            <?php echo _("Change Subscription"); ?>
                            <i class="fas fa-exchange-alt"></i>
                        </a>
                    <?php } ?>
                    <?php } else { ?>
                        <div style="display:contents" id="paypal_button_<?php echo $plan['id']; ?>"></div>
                        <script>
                            paypal.Buttons({
                                style: {
                                    layout: 'vertical',
                                    color: 'blue',
                                    shape: 'rect',
                                    label: 'checkout',
                                    tagline: false,
                                    height: 49
                                },
                                createOrder: function(data, actions) {
                                    return actions.order.create({
                                        purchase_units: [{
                                            "custom_id":"<?php echo $plan['id']; ?>",
                                            "description":"<?php echo $app_name; ?> - <?php echo $plan['name']; ?>",
                                            "amount":{"currency_code":"<?php echo $plan['currency']; ?>","value":<?php echo $plan['price']; ?>}
                                        }]
                                    });
                                },
                                onApprove: function(data, actions) {
                                    return actions.order.capture().then(function(orderData) {
                                        save_paypal_subscription_id(<?php echo $id_user; ?>,'order',orderData.id);
                                    });
                                },
                                onError: function(err) {
                                    console.log(err);
                                }
                            }).render('#paypal_button_<?php echo $plan['id']; ?>');
                        </script>
                    <?php } ?>
                    <?php } ?>
                    <?php } else { ?>
                    <?php if(!empty($plan['external_url'])) { ?>
                        <a class="card-footer d-flex align-items-center justify-content-between text-decoration-none bg-primary-soft" target="_blank" href="<?php echo $plan['external_url']; ?>">
                            <?php echo _("Find out more"); ?>
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-arrow-right"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg>
                        </a>
                    <?php } else { ?>
                        <a class="card-footer d-flex align-items-center justify-content-between text-decoration-none bg-primary-soft" href="mailto:<?php echo $settings['contact_email']; ?>?subject=<?php echo $plan['name']; ?>">
                            <?php echo _("Contact Us"); ?>
                            <i class="fas fa-envelope"></i>
                        </a>
                    <?php } ?>
                    <?php } ?>
                    <?php } ?>
                </div>
            </div>
        <?php } ?>
    </div>
</div>

<?php if($stripe_enabled && !$expiring_plan && (!empty($user_info['id_subscription_stripe']))) : ?>
    <div class="row mt-2 mb-4">
        <div class="col-md-12 text-center align-items-center">
            <span onclick="open_modal_delete_plan();" style="cursor: pointer" class="badge badge-red text-white badge-pill py-2 px-3 mt-1 mb-1 ml-1 mr-1"><?php echo _("cancel current subscription"); ?></span>
            <span onclick="redirect_to_setup();" style="cursor: pointer" class="badge badge-primary text-white badge-pill py-2 px-3 mt-1 mb-1 ml-1 mr-1"><?php echo _("modify payment details"); ?></span>
        </div>
    </div>
<?php endif; ?>

<?php if($paypal_enabled && !$expiring_plan && (!empty($user_info['id_subscription_paypal']))) : ?>
    <div class="row mt-2 mb-4">
        <div class="col-md-12 text-center align-items-center">
            <span onclick="open_modal_delete_plan_paypal();" style="cursor: pointer" class="badge badge-red text-white badge-pill py-2 px-3 mt-1 mb-1 ml-1 mr-1"><?php echo _("cancel current subscription"); ?></span>
        </div>
    </div>
<?php endif; ?>

<div id="modal_redirect_checkout" class="modal" tabindex="-1" role="dialog" data-backdrop="static">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-body">
                <p><?php echo _("Redirecting to checkout page ..."); ?></p>
            </div>
        </div>
    </div>
</div>

<div id="modal_redirect_setup" class="modal" tabindex="-1" role="dialog" data-backdrop="static">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-body">
                <p><?php echo _("Redirecting to payment setup page ..."); ?></p>
            </div>
        </div>
    </div>
</div>

<div id="modal_change_plan" class="modal" tabindex="-1" role="dialog" data-backdrop="static">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><?php echo _("Change Subscription"); ?></h5>
            </div>
            <div class="modal-body">
                <p>
                    <?php echo _("Are you sure you want to change your subscription?"); ?>
                    <br><br>
                    <?php echo _("New plan").": "; ?> <strong id="new_plan">--</strong>
                    <br>
                    <?php echo _("Next payment").": "; ?> <strong id="next_payment">--</strong>
                    <br>
                    <?php echo _("Subsequent payments").": "; ?> <strong id="subseq_payments">--</strong>
                </p>
            </div>
            <div class="modal-footer">
                <button <?php echo ($demo) ? 'disabled':''; ?> id="btn_change_plan" onclick="" type="button" class="btn btn-success disabled"><i class="fas fa-check"></i> <?php echo _("Yes, Change"); ?></button>
                <button type="button" class="btn btn-secondary" data-dismiss="modal"><i class="fas fa-times"></i> <?php echo _("Close"); ?></button>
            </div>
        </div>
    </div>
</div>

<div id="modal_delete_plan" class="modal" tabindex="-1" role="dialog" data-backdrop="static">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><?php echo _("Cancel Subscription"); ?></h5>
            </div>
            <div class="modal-body">
                <p>
                    <?php echo _("Are you sure you want to cancel your current subscription?"); ?>
                    <br><br>
                    <?php echo _("Actual plan").": "; ?> <strong id="actual_plan">--</strong>
                    <br>
                    <?php echo _("Active until").": "; ?> <strong id="active_until">--</strong>
                </p>
            </div>
            <div class="modal-footer">
                <button <?php echo ($demo) ? 'disabled':''; ?> id="btn_delete_plan" onclick="cancel_subscription();" type="button" class="btn btn-danger disabled"><i class="fas fa-power-off"></i> <?php echo _("Yes, Cancel"); ?></button>
                <button type="button" class="btn btn-secondary" data-dismiss="modal"><i class="fas fa-times"></i> <?php echo _("Close"); ?></button>
            </div>
        </div>
    </div>
</div>

<div id="modal_delete_plan_paypal" class="modal" tabindex="-1" role="dialog" data-backdrop="static">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><?php echo _("Cancel Subscription"); ?></h5>
            </div>
            <div class="modal-body">
                <p>
                    <?php echo _("Are you sure you want to cancel your current subscription?"); ?>
                    <br><br>
                    <?php echo _("Actual plan").": "; ?> <strong id="actual_plan_paypal">--</strong>
                    <br>
                    <?php echo _("Active until").": "; ?> <strong id="active_until_paypal">--</strong>
                </p>
            </div>
            <div class="modal-footer">
                <button <?php echo ($demo) ? 'disabled':''; ?> id="btn_delete_plan_paypal" onclick="cancel_subscription_paypal();" type="button" class="btn btn-danger disabled"><i class="fas fa-power-off"></i> <?php echo _("Yes, Cancel"); ?></button>
                <button type="button" class="btn btn-secondary" data-dismiss="modal"><i class="fas fa-times"></i> <?php echo _("Close"); ?></button>
            </div>
        </div>
    </div>
</div>

<div id="modal_reactivate_plan" class="modal" tabindex="-1" role="dialog" data-backdrop="static">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><?php echo _("Reactivate Subscription"); ?></h5>
            </div>
            <div class="modal-body">
                <p>
                    <?php echo _("Are you sure you want to reactivate your canceled subscription?"); ?>
                </p>
            </div>
            <div class="modal-footer">
                <button <?php echo ($demo) ? 'disabled':''; ?> id="btn_reactivate_plan" onclick="reactivate_subscription();" type="button" class="btn btn-success disabled"><i class="fas fa-reply"></i> <?php echo _("Yes, Reactivate"); ?></button>
                <button type="button" class="btn btn-secondary" data-dismiss="modal"><i class="fas fa-times"></i> <?php echo _("Close"); ?></button>
            </div>
        </div>
    </div>
</div>

<div id="modal_change_plan_paypal" class="modal" tabindex="-1" role="dialog" data-backdrop="static">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><?php echo _("Change Subscription"); ?></h5>
            </div>
            <div class="modal-body">
                <p>
                    <?php echo _("You must first cancel your current subscription to activate a new one"); ?>
                </p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal"><i class="fas fa-times"></i> <?php echo _("Close"); ?></button>
            </div>
        </div>
    </div>
</div>

<script>
    (function($) {
        "use strict"; // Start of use strict
        var stripe_enabled = <?php echo $stripe_enabled; ?>;
        $(document).ready(function () {
            if(stripe_enabled) {
                window.stripe = Stripe('<?php echo $stripe_public_key; ?>');
            }
        });
        $('.collapse_all').on('show.bs.collapse', function () {
            $('.show_less').show();
            $('.show_more').hide();
        });
        $('.collapse_all').on('hide.bs.collapse', function () {
            $('.show_less').hide();
            $('.show_more').show();
        });
    })(jQuery); // End of use strict
</script>