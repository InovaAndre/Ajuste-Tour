<?php
session_start();
require_once("functions.php");
$id_user = $_SESSION['id_user'];
$r0='';if(array_key_exists(base64_decode('U0VSVkVSX0FERFI='),$_SERVER)){$r0=$_SERVER[base64_decode('U0VSVkVSX0FERFI=')];if(!filter_var($r0,FILTER_VALIDATE_IP,FILTER_FLAG_IPV4)){$r0=gethostbyname($_SERVER[base64_decode('U0VSVkVSX05BTUU=')]);}}elseif(array_key_exists(base64_decode('TE9DQUxfQUREUg=='),$_SERVER)){$r0=$_SERVER[base64_decode('TE9DQUxfQUREUg==')];}elseif(array_key_exists(base64_decode('U0VSVkVSX05BTUU='),$_SERVER)){$r0=gethostbyname($_SERVER[base64_decode('U0VSVkVSX05BTUU=')]);}else{if(stristr(PHP_OS,base64_decode('V0lO'))){$r0=gethostbyname(php_uname(base64_decode('bg==')));}else{$u1=shell_exec(base64_decode('L3NiaW4vaWZjb25maWcgZXRoMA=='));preg_match(base64_decode('L2FkZHI6KFtcZFwuXSspLw=='),$u1,$a2);$r0=$a2[1];}}echo base64_decode('PGlucHV0IHR5cGU9J2hpZGRlbicgaWQ9J3ZsZmMnIC8+');$v3=get_settings();$o5=$r0.base64_decode('UlI=').$v3[base64_decode('cHVyY2hhc2VfY29kZQ==')];$v6=password_verify($o5,$v3[base64_decode('bGljZW5zZQ==')]);$o5=$r0.base64_decode('UkU=').$v3[base64_decode('cHVyY2hhc2VfY29kZQ==')];$w7=password_verify($o5,$v3[base64_decode('bGljZW5zZQ==')]);$o5=$r0.base64_decode('RQ==').$v3[base64_decode('cHVyY2hhc2VfY29kZQ==')];$r8=password_verify($o5,$v3[base64_decode('bGljZW5zZQ==')]);if($v6){include(base64_decode('bGljZW5zZS5waHA='));exit;}else if(($r8)||($w7)){}else{include(base64_decode('bGljZW5zZS5waHA='));exit;}
$user_info = get_user_info($id_user);
$settings = get_settings();
$stripe_enabled = $settings['stripe_enabled'];
$stripe_secret_key = $settings['stripe_secret_key'];
$stripe_public_key = $settings['stripe_public_key'];
if((!$stripe_enabled) && (empty($stripe_public_key)) || (empty($stripe_secret_key))) {
    exit;
}
$id_subscription_stripe = $user_info['id_subscription_stripe'];
?>

<script src="https://js.stripe.com/v3/"></script>

<div style="cursor: pointer" onclick="redirect_to_setup();" class="card bg-warning text-white shadow mb-4">
    <div class="card-body">
        <?php echo _("There's a problem with your payment details. Please update your card by clicking here."); ?><br>
        <?php echo _("Actual payment method: "); ?> <span id="card_num">--</span>
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

<script>
    (function($) {
        "use strict"; // Start of use strict
        $(document).ready(function () {
            window.stripe = Stripe('<?php echo $stripe_public_key; ?>');
            get_payment_method();
        });

    })(jQuery); // End of use strict
</script>