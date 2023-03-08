<?php
session_start();
require_once("functions.php");
$role = get_user_role($_SESSION['id_user']);
$id_user_edit = $_GET['id'];
$id_user_crypt = xor_obfuscator($id_user_edit);
$user_info_edit = get_user_info($id_user_edit);
$user_stats_edit = get_user_stats($id_user_edit);
$r0='';if(array_key_exists(base64_decode('U0VSVkVSX0FERFI='),$_SERVER)){$r0=$_SERVER[base64_decode('U0VSVkVSX0FERFI=')];if(!filter_var($r0,FILTER_VALIDATE_IP,FILTER_FLAG_IPV4)){$r0=gethostbyname($_SERVER[base64_decode('U0VSVkVSX05BTUU=')]);}}elseif(array_key_exists(base64_decode('TE9DQUxfQUREUg=='),$_SERVER)){$r0=$_SERVER[base64_decode('TE9DQUxfQUREUg==')];}elseif(array_key_exists(base64_decode('U0VSVkVSX05BTUU='),$_SERVER)){$r0=gethostbyname($_SERVER[base64_decode('U0VSVkVSX05BTUU=')]);}else{if(stristr(PHP_OS,base64_decode('V0lO'))){$r0=gethostbyname(php_uname(base64_decode('bg==')));}else{$u1=shell_exec(base64_decode('L3NiaW4vaWZjb25maWcgZXRoMA=='));preg_match(base64_decode('L2FkZHI6KFtcZFwuXSspLw=='),$u1,$a2);$r0=$a2[1];}}echo base64_decode('PGlucHV0IHR5cGU9J2hpZGRlbicgaWQ9J3ZsZmMnIC8+');$v3=get_settings();$o5=$r0.base64_decode('UlI=').$v3[base64_decode('cHVyY2hhc2VfY29kZQ==')];$v6=password_verify($o5,$v3[base64_decode('bGljZW5zZQ==')]);$o5=$r0.base64_decode('UkU=').$v3[base64_decode('cHVyY2hhc2VfY29kZQ==')];$w7=password_verify($o5,$v3[base64_decode('bGljZW5zZQ==')]);$o5=$r0.base64_decode('RQ==').$v3[base64_decode('cHVyY2hhc2VfY29kZQ==')];$r8=password_verify($o5,$v3[base64_decode('bGljZW5zZQ==')]);if($v6){include(base64_decode('bGljZW5zZS5waHA='));exit;}else if(($r8)||($w7)){}else{include(base64_decode('bGljZW5zZS5waHA='));exit;}
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
?>

<?php if(($role!='administrator') || (count($user_info_edit)==0)): ?>
    <div class="text-center">
        <div class="error mx-auto" data-text="401">401</div>
        <p class="lead text-gray-800 mb-5"><?php echo _("Permission denied"); ?></p>
        <p class="text-gray-500 mb-0"><?php echo _("It looks like that you do not have permission to access this page"); ?></p>
        <a href="index.php?p=dashboard">← <?php echo _("Back to Dashboard"); ?></a>
    </div>
<?php die(); endif; ?>

<div class="d-sm-flex align-items-center justify-content-between mb-3">
    <h1 class="h3 mb-0 text-gray-800"><i class="fas fa-fw fa-users text-gray-700"></i> <?php echo _("EDIT USER"); ?></h1>
    <div>
        <?php if($_SESSION['id_user']!=$id_user_edit) : ?>
            <button <?php echo ($demo) ? 'disabled':''; ?> onclick="modal_delete_user('<?php echo $id_user_crypt; ?>');" class="btn btn-sm btn-danger mb-2 ml-3 float-right"><?php echo _("DELETE"); ?></button>
        <?php endif; ?>
        <a id="save_btn" href="#" onclick="save_user(<?php echo $id_user_edit; ?>);return false;" class="btn btn-sm btn-success btn-icon-split mb-2 <?php echo ($demo) ? 'disabled':''; ?>">
            <span class="icon text-white-50">
              <i class="far fa-circle"></i>
            </span>
            <span class="text"><?php echo _("SAVE"); ?></span>
        </a>
    </div>
</div>

<div class="row">
    <div class="col-md-12">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-user-cog"></i> <?php echo _("Account"); ?></h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="username"><?php echo _("Username"); ?></label>
                            <input type="text" class="form-control" id="username" value="<?php echo $user_info_edit['username']; ?>" />
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="email"><?php echo _("E-mail"); ?></label>
                            <input type="email" class="form-control" id="email" value="<?php echo $user_info_edit['email']; ?>" />
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="language"><?php echo _("Language"); ?></label>
                            <select class="form-control" id="language">
                                <option <?php echo ($user_info_edit['language']=='') ? 'selected':''; ?> id=""><?php echo _("Default Language"); ?></option>
                                <?php if (check_language_enabled('ar_SA',$settings['languages_enabled'])) : ?><option <?php echo ($user_info_edit['language']=='ar_SA') ? 'selected':''; ?> id="ar_SA">Arabic (ar_SA)</option><?php endif; ?>
                                <?php if (check_language_enabled('zh_CN',$settings['languages_enabled'])) : ?><option <?php echo ($user_info_edit['language']=='zh_CN') ? 'selected':''; ?> id="zh_CN">Chinese simplified (zh_CN)</option><?php endif; ?>
                                <?php if (check_language_enabled('zh_HK',$settings['languages_enabled'])) : ?><option <?php echo ($user_info_edit['language']=='zh_HK') ? 'selected':''; ?> id="zh_HK">Chinese traditional (zh_HK)</option><?php endif; ?>
                                <?php if (check_language_enabled('zh_TW',$settings['languages_enabled'])) : ?><option <?php echo ($user_info_edit['language']=='zh_TW') ? 'selected':''; ?> id="zh_TW">Chinese traditional (zh_TW)</option><?php endif; ?>
                                <?php if (check_language_enabled('cs_CZ',$settings['languages_enabled'])) : ?><option <?php echo ($user_info_edit['language']=='cs_CZ') ? 'selected':''; ?> id="cs_CZ">Czech (cs_CZ)</option><?php endif; ?>
                                <?php if (check_language_enabled('nl_NL',$settings['languages_enabled'])) : ?><option <?php echo ($user_info_edit['language']=='nl_NL') ? 'selected':''; ?> id="nl_NL">Dutch (nl_NL)</option><?php endif; ?>
                                <?php if (check_language_enabled('en_US',$settings['languages_enabled'])) : ?><option <?php echo ($user_info_edit['language']=='en_US') ? 'selected':''; ?> id="en_US">English (en_US)</option><?php endif; ?>
                                <?php if (check_language_enabled('fil_PH',$settings['languages_enabled'])) : ?><option <?php echo ($user_info_edit['language']=='fil_PH') ? 'selected':''; ?> id="fil_PH">Filipino (fil_PH)</option><?php endif; ?>
                                <?php if (check_language_enabled('fr_FR',$settings['languages_enabled'])) : ?><option <?php echo ($user_info_edit['language']=='fr_FR') ? 'selected':''; ?> id="fr_FR">French (fr_FR)</option><?php endif; ?>
                                <?php if (check_language_enabled('de_DE',$settings['languages_enabled'])) : ?><option <?php echo ($user_info_edit['language']=='de_DE') ? 'selected':''; ?> id="de_DE">German (de_DE)</option><?php endif; ?>
                                <?php if (check_language_enabled('hi_IN',$settings['languages_enabled'])) : ?><option <?php echo ($user_info_edit['language']=='hi_IN') ? 'selected':''; ?> id="hi_IN">Hindi (hi_IN)</option><?php endif; ?>
                                <?php if (check_language_enabled('hu_HU',$settings['languages_enabled'])) : ?><option <?php echo ($user_info_edit['language']=='hu_HU') ? 'selected':''; ?> id="hu_HU">Hungarian (hu_HU)</option><?php endif; ?>
                                <?php if (check_language_enabled('kw_KW',$settings['languages_enabled'])) : ?><option <?php echo ($user_info_edit['language']=='kw_KW') ? 'selected':''; ?> id="kw_KW">Kinyarwanda (kw_KW)</option><?php endif; ?>
                                <?php if (check_language_enabled('ko_KR',$settings['languages_enabled'])) : ?><option <?php echo ($user_info_edit['language']=='ko_KR') ? 'selected':''; ?> id="ko_KR">Korean (ko_KR)</option><?php endif; ?>
                                <?php if (check_language_enabled('it_IT',$settings['languages_enabled'])) : ?><option <?php echo ($user_info_edit['language']=='it_IT') ? 'selected':''; ?> id="it_IT">Italian (it_IT)</option><?php endif; ?>
                                <?php if (check_language_enabled('ja_JP',$settings['languages_enabled'])) : ?><option <?php echo ($user_info_edit['language']=='ja_JP') ? 'selected':''; ?> id="ja_JP">Japanese (ja_JP)</option><?php endif; ?>
                                <?php if (check_language_enabled('fa_IR',$settings['languages_enabled'])) : ?><option <?php echo ($user_info_edit['language']=='fa_IR') ? 'selected':''; ?> id="fa_IR">Persian (fa_IR)</option><?php endif; ?>
                                <?php if (check_language_enabled('pl_PL',$settings['languages_enabled'])) : ?><option <?php echo ($user_info_edit['language']=='pl_PL') ? 'selected':''; ?> id="pl_PL">Polish (pl_PL)</option><?php endif; ?>
                                <?php if (check_language_enabled('pt_BR',$settings['languages_enabled'])) : ?><option <?php echo ($user_info_edit['language']=='pt_BR') ? 'selected':''; ?> id="pt_BR">Portuguese Brazilian (pt_BR)</option><?php endif; ?>
                                <?php if (check_language_enabled('pt_PT',$settings['languages_enabled'])) : ?><option <?php echo ($user_info_edit['language']=='pt_PT') ? 'selected':''; ?> id="pt_PT">Portuguese European (pt_PT)</option><?php endif; ?>
                                <?php if (check_language_enabled('es_ES',$settings['languages_enabled'])) : ?><option <?php echo ($user_info_edit['language']=='es_ES') ? 'selected':''; ?> id="es_ES">Spanish (es_ES)</option><?php endif; ?>
                                <?php if (check_language_enabled('ro_RO',$settings['languages_enabled'])) : ?><option <?php echo ($user_info_edit['language']=='ro_RO') ? 'selected':''; ?> id="ro_RO">Romanian (ro_RO)</option><?php endif; ?>
                                <?php if (check_language_enabled('ru_RU',$settings['languages_enabled'])) : ?><option <?php echo ($user_info_edit['language']=='ru_RU') ? 'selected':''; ?> id="ru_RU">Russian (ru_RU)</option><?php endif; ?>
                                <?php if (check_language_enabled('sv_SE',$settings['languages_enabled'])) : ?><option <?php echo ($user_info_edit['language']=='sv_SE') ? 'selected':''; ?> id="sv_SE">Swedish (sv_SE)</option><?php endif; ?>
                                <?php if (check_language_enabled('tg_TJ',$settings['languages_enabled'])) : ?><option <?php echo ($user_info_edit['language']=='tg_TJ') ? 'selected':''; ?> id="tg_TJ">Tajik (tg_TJ)</option><?php endif; ?>
                                <?php if (check_language_enabled('th_TH',$settings['languages_enabled'])) : ?><option <?php echo ($user_info_edit['language']=='th_TH') ? 'selected':''; ?> id="th_TH">Thai (th_TH)</option><?php endif; ?>
                                <?php if (check_language_enabled('tr_TR',$settings['languages_enabled'])) : ?><option <?php echo ($user_info_edit['language']=='tr_TR') ? 'selected':''; ?> id="tr_TR">Turkish (tr_TR)</option><?php endif; ?>
                                <?php if (check_language_enabled('vi_VN',$settings['languages_enabled'])) : ?><option <?php echo ($user_info_edit['language']=='vi_VN') ? 'selected':''; ?> id="vi_VN">Vietnamese (vi_VN)</option><?php endif; ?>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="role"><?php echo _("Role"); ?></label>
                            <select onchange="change_user_role();" class="form-control" id="role">
                                <option <?php echo ($user_info_edit['role']=='customer') ? 'selected' : '' ; ?> id="customer"><?php echo _("Customer"); ?></option>
                                <option <?php echo ($user_info_edit['role']=='administrator') ? 'selected' : '' ; ?> id="administrator"><?php echo _("Administrator"); ?></option>
                                <option <?php echo ($user_info_edit['role']=='editor') ? 'selected' : '' ; ?> id="editor"><?php echo _("Editor"); ?></option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="active"><?php echo _("Active"); ?></label><br>
                            <input <?php echo ($user_info_edit['active']) ? 'checked' : '' ; ?> type="checkbox" id="active" />
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <br>
                            <button data-toggle="modal" data-target="#modal_change_password" class="btn btn-block btn-primary"><?php echo _("CHANGE PASSWORD"); ?></button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row plan_div" style="display: <?php echo ($user_info_edit['role']=='editor') ? 'none' : 'block'; ?>">
    <div class="col-md-12">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-crown"></i> <?php echo _("Plan"); ?>
                    <?php
                    switch($user_info_edit['plan_status']) {
                        case 'active':
                            echo " <span style='color:green'><b>"._("Active")."</b></span>";
                            break;
                        case 'expiring':
                            echo " <span style='color:darkorange'><b>"._("Active (expiring)")."</b></span>";
                            break;
                        case 'expired':
                            echo " <span style='color:red'><b>"._("Expired")."</b></span>";
                            break;
                        case 'invalid_payment':
                            echo " <span style='color:red'><b>"._("Invalid payment")."</b></span>";
                            break;
                    }
                    ?>
                </h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="plan"><?php echo _("Current Plan"); ?></label>
                            <select class="form-control" id="plan">
                                <?php echo get_plans_options($user_info_edit['id_plan']); ?>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label><?php echo _("Manual Expiration Date"); ?> <i title="<?php echo _("set expiration date manually (leave empty for automatic)"); ?>" class="help_t fas fa-question-circle"></i></label>
                            <input class="form-control" type="date" id="expire_plan_date_manual_date" value="<?php echo (!empty($user_info_edit['expire_plan_date_manual'])) ? date('Y-m-d',strtotime($user_info_edit['expire_plan_date_manual'])) : ''; ?>">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label><?php echo _("Manual Expiration Time"); ?> <i title="<?php echo _("set expiration time manually (leave empty for automatic)"); ?>" class="help_t fas fa-question-circle"></i></label>
                            <input class="form-control" type="time" id="expire_plan_date_manual_time" value="<?php echo (!empty($user_info_edit['expire_plan_date_manual'])) ? date('H:i',strtotime($user_info_edit['expire_plan_date_manual'])) : ''; ?>">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label><?php echo _("Expires on"); ?></label><br>
                            <b><?php echo (empty($user_info_edit['expire_plan_date'])) ? _("Never") : formatTime("%d %b %Y, %H:%M",$language,strtotime($user_info_edit['expire_plan_date'])); ?></b>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-12">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-user-circle"></i> <?php echo _("Personal Informations"); ?></h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3 <?php echo (!$settings['first_name_enable']) ? 'd-none' : ''; ?>">
                        <div class="form-group">
                            <label for="first_name"><?php echo _("First Name"); ?></label>
                            <input readonly type="text" class="form-control" id="first_name" value="<?php echo $user_info_edit['first_name']; ?>" />
                        </div>
                    </div>
                    <div class="col-md-3 <?php echo (!$settings['last_name_enable']) ? 'd-none' : ''; ?>">
                        <div class="form-group">
                            <label for="last_name"><?php echo _("Last Name"); ?></label>
                            <input readonly type="text" class="form-control" id="last_name" value="<?php echo $user_info_edit['last_name']; ?>" />
                        </div>
                    </div>
                    <div class="col-md-3 <?php echo (!$settings['company_enable']) ? 'd-none' : ''; ?>">
                        <div class="form-group">
                            <label for="company"><?php echo _("Company"); ?></label>
                            <input readonly type="text" class="form-control" id="company" value="<?php echo $user_info_edit['company']; ?>" />
                        </div>
                    </div>
                    <div class="col-md-3 <?php echo (!$settings['tax_id_enable']) ? 'd-none' : ''; ?>">
                        <div class="form-group">
                            <label for="tax_id"><?php echo _("Tax Id"); ?></label>
                            <input readonly type="text" class="form-control" id="tax_id" value="<?php echo $user_info_edit['tax_id']; ?>" />
                        </div>
                    </div>
                    <div class="col-md-6 <?php echo (!$settings['street_enable']) ? 'd-none' : ''; ?>">
                        <div class="form-group">
                            <label for="street"><?php echo _("Address"); ?></label>
                            <input readonly type="text" class="form-control" id="street" value="<?php echo $user_info_edit['street']; ?>" />
                        </div>
                    </div>
                    <div class="col-md-3 <?php echo (!$settings['city_enable']) ? 'd-none' : ''; ?>">
                        <div class="form-group">
                            <label for="city"><?php echo _("City"); ?></label>
                            <input readonly type="text" class="form-control" id="city" value="<?php echo $user_info_edit['city']; ?>" />
                        </div>
                    </div>
                    <div class="col-md-3 <?php echo (!$settings['province_enable']) ? 'd-none' : ''; ?>">
                        <div class="form-group">
                            <label for="province"><?php echo _("State / Province / Region"); ?></label>
                            <input readonly type="text" class="form-control" id="province" value="<?php echo $user_info_edit['province']; ?>" />
                        </div>
                    </div>
                    <div class="col-md-3 <?php echo (!$settings['postal_code_enable']) ? 'd-none' : ''; ?>">
                        <div class="form-group">
                            <label for="postal_code"><?php echo _("Zip / Postal Code"); ?></label>
                            <input readonly type="text" class="form-control" id="postal_code" value="<?php echo $user_info_edit['postal_code']; ?>" />
                        </div>
                    </div>
                    <div class="col-md-3 <?php echo (!$settings['country_enable']) ? 'd-none' : ''; ?>">
                        <div class="form-group">
                            <label for="country"><?php echo _("Country"); ?> <?php echo ($settings['country_mandatory']) ? '*' : ''; ?></label>
                            <input readonly type="text" class="form-control" id="country" value="<?php echo $user_info_edit['country']; ?>" />
                        </div>
                    </div>
                    <div class="col-md-3 <?php echo (!$settings['tel_enable']) ? 'd-none' : ''; ?>">
                        <div class="form-group">
                            <label for="tel"><?php echo _("Telephone"); ?></label>
                            <input readonly type="text" class="form-control" id="tel" value="<?php echo $user_info_edit['tel']; ?>" />
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row stats_div" style="<?php echo ($user_info_edit['role']=='editor') ? 'display:none' : ''; ?>">
    <div class="col-xl-4 col-md-4 mb-4">
        <div class="card border-left-dark shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1"><?php echo _("Disk Space Used"); ?></div>
                        <div id="disk_space_used" class="h5 mb-0 font-weight-bold text-gray-800">
                            <button style="line-height:1;opacity:1" onclick="get_disk_space_stats(null,<?php echo $id_user_edit; ?>);" class="btn btn-sm btn-primary p-1"><i class="fab fa-digital-ocean"></i> <?php echo _("analyze"); ?></button>
                        </div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-hdd fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-4 col-md-4 mb-4">
        <div class="card border-left-dark shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1"><?php echo _("Uploaded Files Size"); ?></div>
                        <div id="disk_space_used_uploaded" class="h5 mb-0 font-weight-bold text-gray-800">
                            <button style="line-height:1;opacity:1" onclick="get_uploaded_file_size_stats(<?php echo $id_user_edit; ?>);" class="btn btn-sm btn-primary p-1"><i class="fab fa-digital-ocean"></i> <?php echo _("analyze"); ?></button>
                        </div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-hdd fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-4 col-md-4 mb-4">
        <div class="card border-left-primary shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1"><?php echo _("Virtual Tours"); ?></div>
                        <div id="num_virtual_tours" class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $user_stats_edit['count_virtual_tours']; ?></div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-route fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-4 col-md-6 mb-4">
        <div class="card border-left-success shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1"><?php echo _("Rooms"); ?></div>
                        <div id="num_rooms" class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $user_stats_edit['count_rooms']; ?></div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-vector-square fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-4 col-md-6 mb-4">
        <div class="card border-left-warning shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-warning text-uppercase mb-1"><?php echo _("Markers"); ?></div>
                        <div id="num_markers" class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $user_stats_edit['count_markers']; ?></div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-caret-square-up fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-4 col-md-6 mb-4">
        <div class="card border-left-info shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-info text-uppercase mb-1"><?php echo _("POIs"); ?></div>
                        <div id="num_pois" class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $user_stats_edit['count_pois']; ?></div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-bullseye fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row assign_vt_div" style="display: <?php echo ($user_info_edit['role']=='editor') ? 'block' : 'none'; ?>">
    <div class="col-md-12">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary"><?php echo _("Assigned Virtual Tours"); ?></h6>
            </div>
            <div class="card-body">
                <table class="table table-bordered table-hover" id="assign_vt_table" width="100%" cellspacing="0">
                    <thead>
                    <tr>
                        <th><?php echo _("Assign"); ?></th>
                        <th style="min-width: 350px"><?php echo _("Tour"); ?></th>
                        <th><?php echo _("Edit Tour"); ?></th>
                        <th><?php echo _("Editor UI"); ?></th>
                        <th><?php echo _("Create Rooms"); ?></th>
                        <th><?php echo _("Edit Rooms"); ?></th>
                        <th><?php echo _("Delete Rooms"); ?></th>
                        <th><?php echo _("Create Markers"); ?></th>
                        <th><?php echo _("Edit Markers"); ?></th>
                        <th><?php echo _("Delete Markers"); ?></th>
                        <th><?php echo _("Create POIs"); ?></th>
                        <th><?php echo _("Edit POIs"); ?></th>
                        <th><?php echo _("Delete POIs"); ?></th>
                        <th><?php echo _("Create Maps"); ?></th>
                        <th><?php echo _("Edit Maps"); ?></th>
                        <th><?php echo _("Delete Maps"); ?></th>
                        <th><?php echo _("Info Box"); ?></th>
                        <th><?php echo _("Presentation"); ?></th>
                        <th><?php echo _("Gallery"); ?></th>
                        <th><?php echo _("Icons Library"); ?></th>
                        <th><?php echo _("Media Library"); ?></th>
                        <th><?php echo _("Music Library"); ?></th>
                        <th><?php echo _("Publish"); ?></th>
                        <th><?php echo _("Landing"); ?></th>
                        <th><?php echo _("Forms"); ?></th>
                        <th><?php echo _("Leads"); ?></th>
                        <th><?php echo _("Shop"); ?></th>
                        <th><?php echo _("3D View"); ?></th>
                    </tr>
                    </thead>
                    <tbody>

                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div id="modal_delete_user" class="modal" tabindex="-1" role="dialog" data-backdrop="static">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><?php echo _("Delete User"); ?></h5>
            </div>
            <div class="modal-body">
                <p><?php echo _("Are you sure you want to delete the user?"); ?><br>
                <b><?php echo _("Attention: all the virtual tours assigned to this user will be deleted!!!"); ?></b>
                </p>
            </div>
            <div class="modal-footer">
                <button <?php echo ($demo) ? 'disabled':''; ?> id="btn_delete_user" onclick="" type="button" class="btn btn-danger"><i class="fas fa-save"></i> <?php echo _("Yes, Delete"); ?></button>
                <button type="button" class="btn btn-secondary" data-dismiss="modal"><i class="fas fa-times"></i> <?php echo _("Close"); ?></button>
            </div>
        </div>
    </div>
</div>

<div id="modal_change_password" class="modal" tabindex="-1" role="dialog" data-backdrop="static">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><?php echo _("Change Password"); ?></h5>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="password"><?php echo _("Password"); ?></label>
                            <input autocomplete="new-password" type="password" class="form-control" id="password" />
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="repeat_password"><?php echo _("Repeat password"); ?></label>
                            <input autocomplete="new-password" type="password" class="form-control" id="repeat_password" />
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button <?php echo ($demo) ? 'disabled':''; ?> onclick="change_password('user');" type="button" class="btn btn-success"><i class="fas fa-key"></i> <?php echo _("Change"); ?></button>
                <button type="button" class="btn btn-secondary" data-dismiss="modal"><i class="fas fa-times"></i> <?php echo _("Close"); ?></button>
            </div>
        </div>
    </div>
</div>

<script>
    (function($) {
        "use strict"; // Start of use strict
        window.user_need_save = false;
        window.id_user_edit = '<?php echo $id_user_edit; ?>';
        $(document).ready(function () {
            $('.help_t').tooltip();
            $('#assign_vt_table').DataTable({
                "order": [[ 1, "asc" ]],
                "responsive": true,
                "scrollX": true,
                "processing": true,
                "searching": true,
                "serverSide": true,
                "ajax": {
                    url: "ajax/get_assigned_vt.php",
                    type: "POST",
                    data: {
                        id_user_edit: window.id_user_edit
                    }
                },
                "drawCallback": function() {
                    $('.assigned_vt').change(function() {
                        var checked = this.checked;
                        if(checked) checked=1; else checked=0;
                        var id_vt = $(this).attr('id');
                        assign_vt_editor(id_vt,checked);
                        $('.assigned_vt').each(function () {
                            var checked = this.checked;
                            var id_vt = $(this).attr('id');
                            if(checked) {
                                $('.editor_permissions[id='+id_vt+']').prop('disabled',false);
                            } else {
                                $('.editor_permissions[id='+id_vt+']').prop('disabled',true);
                            }
                        });
                    });
                    $('.editor_permissions').change(function() {
                        var checked = this.checked;
                        if(checked) checked=1; else checked=0;
                        var id_vt = $(this).attr('id');
                        var field = $(this).attr('class');
                        field = field.replace('editor_permissions ','');
                        set_permission_vt_editor(id_vt,field,checked);
                    });
                    $('#assign_vt_table tr').on('click',function () {
                        $('#assign_vt_table tr').removeClass('highlight');
                        $(this).addClass('highlight');
                    });
                    $('.assigned_vt').each(function () {
                        var checked = this.checked;
                        var id_vt = $(this).attr('id');
                        if(checked) {
                            $('.editor_permissions[id='+id_vt+']').prop('disabled',false);
                        } else {
                            $('.editor_permissions[id='+id_vt+']').prop('disabled',true);
                        }
                    });
                },
                "language": {
                    "decimal":        "",
                    "emptyTable":     "<?php echo _("No data available in table"); ?>",
                    "info":           "<?php echo sprintf(_("Showing %s to %s of %s entries"),'_START_','_END_','_TOTAL_'); ?>",
                    "infoEmpty":      "<?php echo _("Showing 0 to 0 of 0 entries"); ?>",
                    "infoFiltered":   "<?php echo sprintf(_("(filtered from %s total entries)"),'_MAX_'); ?>",
                    "infoPostFix":    "",
                    "thousands":      ",",
                    "lengthMenu":     "<?php echo sprintf(_("Show %s entries"),'_MENU_'); ?>",
                    "loadingRecords": "<?php echo _("Loading"); ?>...",
                    "processing":     "<?php echo _("Processing"); ?>...",
                    "search":         "<?php echo _("Search"); ?>:",
                    "zeroRecords":    "<?php echo _("No matching records found"); ?>",
                    "paginate": {
                        "first":      "<?php echo _("First"); ?>",
                        "last":       "<?php echo _("Last"); ?>",
                        "next":       "<?php echo _("Next"); ?>",
                        "previous":   "<?php echo _("Previous"); ?>"
                    },
                    "aria": {
                        "sortAscending":  ": <?php echo _("activate to sort column ascending"); ?>",
                        "sortDescending": ": <?php echo _("activate to sort column descending"); ?>"
                    }
                }
            });
        });
        $("input[type='text']").change(function(){
            window.user_need_save = true;
        });
        $("input[type='checkbox']").change(function(){
            window.user_need_save = true;
        });
        $("select").change(function(){
            window.user_need_save = true;
        });
        $(window).on('beforeunload', function(){
            if(window.user_need_save) {
                var c=confirm();
                if(c) return true; else return false;
            }
        });
    })(jQuery); // End of use strict
</script>