<?php
if(!isset($settings)) {
    $settings = get_settings();
}
if(!isset($user_info)) {
    $user_info = get_user_info($_SESSION['id_user']);
}
$change_plan = $settings['change_plan'];
if($change_plan) {
    $msg_change_plan = "<a class='text-white' href='index.php?p=change_plan'><b>"._("Click here to change your plan")."</b></a>";
} else {
    $msg_change_plan = "";
}
if($user_info['plan_status']=='expired') : ?>
    <div class="card bg-warning text-white shadow mb-4">
        <div class="card-body">
            <?php echo sprintf(_('Your "%s" plan has expired!'),$user_info['plan'])." ".$msg_change_plan; ?>
        </div>
    </div>
<?php exit; endif; ?>