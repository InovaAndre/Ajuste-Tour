<?php
error_reporting(E_ALL & ~E_WARNING & ~E_NOTICE & ~E_DEPRECATED);
session_start();
require_once("functions.php");
$role = get_user_role($_SESSION['id_user']);
$settings = get_settings();
$voice_commands = get_voice_commands();
if (is_ssl()) { $protocol = 'https'; } else { $protocol = 'http'; }
$callback_url = $protocol ."://". $_SERVER['SERVER_NAME'] . str_replace("backend/index.php","backend/social_auth.php",$_SERVER['SCRIPT_NAME']);
$domain = $_SERVER['SERVER_NAME'];
$cronjob_dir = str_replace("/backend","/services/cron.php",dirname(__FILE__));
?>

<?php if($role!='administrator'): ?>
    <div class="text-center">
        <div class="error mx-auto" data-text="401">401</div>
        <p class="lead text-gray-800 mb-5"><?php echo _("Permission denied"); ?></p>
        <p class="text-gray-500 mb-0"><?php echo _("It looks like that you do not have permission to access this page"); ?></p>
        <a href="index.php?p=dashboard">← <?php echo _("Back to Dashboard"); ?></a>
    </div>
<?php die(); endif; ?>

<div class="d-sm-flex align-items-center justify-content-between mb-3">
    <h1 class="h3 mb-0 text-gray-800"><i class="fas fa-fw fa-cogs text-gray-700"></i> <?php echo _("SETTINGS"); ?></h1>
    <a id="save_btn" href="#" onclick="save_settings(false);return false;" class="btn btn-sm btn-success btn-icon-split mb-2  <?php echo ($_SESSION['input_license']==1) ? 'd-none' : ''; ?> <?php echo ($demo) ? 'disabled':''; ?>">
    <span class="icon text-white-50">
      <i class="far fa-circle"></i>
    </span>
        <span class="text"><?php echo _("SAVE"); ?></span>
    </a>
</div>

<?php if($_SESSION['input_license']==1) : ?>
    <div class="card bg-warning text-white shadow mb-3">
        <div class="card-body">
            <?php echo _("Please enter a valid purchase code to continue using the application."); ?>
        </div>
    </div>
<?php endif; ?>

<ul class="nav bg-white nav-pills nav-fill mb-2 <?php echo ($_SESSION['input_license']==1) ? 'd-none' : ''; ?>">
    <li class="nav-item">
        <a class="nav-link" data-toggle="pill" href="#license_tab"><?php echo strtoupper(_("LICENSE")); ?></a>
    </li>
    <li class="nav-item">
        <a class="nav-link <?php echo ($_SESSION['input_license']==0) ? 'active' : ''; ?>" data-toggle="pill" href="#settings_tab"><?php echo strtoupper(_("GENERAL")); ?></a>
    </li>
    <li class="nav-item">
        <a class="nav-link" data-toggle="pill" href="#whitelabel_tab"><?php echo strtoupper(_("STYLE")); ?></a>
    </li>
    <li class="nav-item">
        <a class="nav-link" data-toggle="pill" href="#style_tab"><?php echo strtoupper(_("CUSTOM CSS / JS")); ?></a>
    </li>
    <li class="nav-item">
        <a class="nav-link" data-toggle="pill" href="#voice_commands_tab"><?php echo strtoupper(_("VOICE COMMANDS")); ?></a>
    </li>
    <li class="nav-item">
        <a class="nav-link" data-toggle="pill" href="#categories_tab"><?php echo strtoupper(_("CATEGORIES")); ?></a>
    </li>
    <li class="nav-item">
        <a class="nav-link" data-toggle="pill" href="#mail_tab"><?php echo strtoupper(_("MAIL")); ?></a>
    </li>
    <li class="nav-item">
        <a class="nav-link" data-toggle="pill" href="#notify_tab"><?php echo strtoupper(_("NOTIFICATIONS")); ?></a>
    </li>
    <li class="nav-item">
        <a class="nav-link" data-toggle="pill" href="#social_tab"><?php echo strtoupper(_("SOCIAL")); ?></a>
    </li>
    <li id="registration_li" class="nav-item d-none">
        <a class="nav-link" data-toggle="pill" href="#registration_tab"><?php echo strtoupper(_("REGISTRATION")); ?></a>
    </li>
    <li id="payments_li" class="nav-item d-none">
        <a class="nav-link" data-toggle="pill" href="#payments_tab"><?php echo strtoupper(_("PAYMENTS")); ?></a>
    </li>
</ul>

<div class="tab-content">
    <div class="tab-pane <?php echo ($_SESSION['input_license']==1) ? 'active' : ''; ?>" id="license_tab">
        <div class="row">
            <div class="col-md-12 mb-4">
                <div class="card shadow mb-12">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-key"></i> <?php echo _("License"); ?></h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="purchase_code"><?php echo _("Purchase Code"); ?> <a target="_blank" href="https://help.market.envato.com/hc/en-us/articles/202822600-Where-Is-My-Purchase-Code-">(<?php echo _("Where i can find it?"); ?>)</a></label>
                                    <input type="text" class="form-control" id="purchase_code" value="<?php echo $settings['purchase_code']; ?>" />
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label style="color: white">.</label>
                                    <button id="btn_check_license" onclick="check_license()" class="btn btn-primary btn-block"><?php echo _("CHECK"); ?></button>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <label><?php echo _("Status"); ?></label><br>
                                <div id="license_status" class="mt-2">
                                    <?php
                                    if($settings['purchase_code']=='') {
                                        echo "<i class=\"fas fa-circle\"></i> Unchecked";
                                    } else {
                                        if($settings['license']=='') {
                                            echo "<i style='color: red' class=\"fas fa-circle\"></i> Invalid License";
                                        } else {
                                            $y0='';if(array_key_exists(base64_decode('U0VSVkVSX0FERFI='),$_SERVER)){$y0=$_SERVER[base64_decode('U0VSVkVSX0FERFI=')];if(!filter_var($y0,FILTER_VALIDATE_IP,FILTER_FLAG_IPV4)){$y0=gethostbyname($_SERVER[base64_decode('U0VSVkVSX05BTUU=')]);}}elseif(array_key_exists(base64_decode('TE9DQUxfQUREUg=='),$_SERVER)){$y0=$_SERVER[base64_decode('TE9DQUxfQUREUg==')];}elseif(array_key_exists(base64_decode('U0VSVkVSX05BTUU='),$_SERVER)){$y0=gethostbyname($_SERVER[base64_decode('U0VSVkVSX05BTUU=')]);}else{if(stristr(PHP_OS,base64_decode('V0lO'))){$y0=gethostbyname(php_uname(base64_decode('bg==')));}else{$q1=shell_exec(base64_decode('L3NiaW4vaWZjb25maWcgZXRoMA=='));preg_match(base64_decode('L2FkZHI6KFtcZFwuXSspLw=='),$q1,$f2);$y0=$f2[1];}}$k4=$settings;$q3=$y0.base64_decode('UlI=').$k4[base64_decode('cHVyY2hhc2VfY29kZQ==')];$w5=password_verify($q3,$k4[base64_decode('bGljZW5zZQ==')]);$q3=$y0.base64_decode('UkU=').$k4[base64_decode('cHVyY2hhc2VfY29kZQ==')];$j6=password_verify($q3,$k4[base64_decode('bGljZW5zZQ==')]);$q3=$y0.base64_decode('RQ==').$k4[base64_decode('cHVyY2hhc2VfY29kZQ==')];$u7=password_verify($q3,$k4[base64_decode('bGljZW5zZQ==')]);if($w5||$j6){echo base64_decode('PGkgc3R5bGU9J2NvbG9yOiBncmVlbicgY2xhc3M9ImZhcyBmYS1jaXJjbGUiPjwvaT4gVmFsaWQsIFJlZ3VsYXIgTGljZW5zZQ==');}else if($u7){echo base64_decode('PGkgc3R5bGU9J2NvbG9yOiBncmVlbicgY2xhc3M9ImZhcyBmYS1jaXJjbGUiPjwvaT4gVmFsaWQsIEV4dGVuZGVkIExpY2Vuc2U=');}else{echo base64_decode('PGkgc3R5bGU9J2NvbG9yOiByZWQnIGNsYXNzPSJmYXMgZmEtY2lyY2xlIj48L2k+IEludmFsaWQgTGljZW5zZQ==');}
                                        }
                                    }
                                    ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="tab-pane <?php echo ($_SESSION['input_license']==0) ? 'active' : ''; ?>" id="settings_tab">
        <div class="row">
            <div class="col-md-12 mb-4">
                <div class="card shadow mb-12">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-cog"></i> <?php echo _("Misc"); ?></h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="contact_mail"><?php echo _("Contact E-Mail"); ?></label>
                                    <input type="text" class="form-control" id="contact_mail" value="<?php echo $settings['contact_email']; ?>" />
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="enable_external_vt"><?php echo _("Enable External Tours"); ?></label><br>
                                    <input <?php echo ($settings['enable_external_vt']) ? 'checked':''; ?> type="checkbox" id="enable_external_vt" />
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="enable_wizard"><?php echo _("Enable Tour Creation Wizard"); ?></label><br>
                                    <input <?php echo ($settings['enable_wizard']) ? 'checked':''; ?> type="checkbox" id="enable_wizard" />
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="help_url"><?php echo _("Help Link"); ?></label>
                                    <input type="text" class="form-control" id="help_url" value="<?php echo $settings['help_url']; ?>" />
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="furl_blacklist"><?php echo _("Friendly Urls Blacklist"); ?></label>
                                    <input type="text" class="form-control" id="furl_blacklist" placeholder="<?php echo _("Enter friendly urls separated by comma"); ?>" value="<?php echo $settings['furl_blacklist']; ?>" />
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12 mb-4">
                <div class="card shadow mb-12">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-map"></i> <?php echo _("Map"); ?></h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="leaflet_street_basemap">Leaflet <?php echo _("Street Url"); ?></label>
                                    <input type="text" class="form-control" id="leaflet_street_basemap" value="<?php echo $settings['leaflet_street_basemap']; ?>" />
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="leaflet_street_subdomain">Leaflet <?php echo _("Street Subdomain"); ?></label>
                                    <input type="text" class="form-control" id="leaflet_street_subdomain" value="<?php echo $settings['leaflet_street_subdomain']; ?>" />
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="leaflet_street_maxzoom">Leaflet <?php echo _("Street Max Zoom"); ?></label>
                                    <input type="text" class="form-control" id="leaflet_street_maxzoom" value="<?php echo $settings['leaflet_street_maxzoom']; ?>" />
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="leaflet_satellite_basemap">Leaflet <?php echo _("Satellite Url"); ?></label>
                                    <input type="text" class="form-control" id="leaflet_satellite_basemap" value="<?php echo $settings['leaflet_satellite_basemap']; ?>" />
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="leaflet_satellite_subdomain">Leaflet <?php echo _("Satellite Subdomain"); ?></label>
                                    <input type="text" class="form-control" id="leaflet_satellite_subdomain" value="<?php echo $settings['leaflet_satellite_subdomain']; ?>" />
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="leaflet_satellite_maxzoom">Leaflet <?php echo _("Satellite Max Zoom"); ?></label>
                                    <input type="text" class="form-control" id="leaflet_satellite_maxzoom" value="<?php echo $settings['leaflet_satellite_maxzoom']; ?>" />
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12 mb-4">
                <div class="card shadow mb-12">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-handshake"></i> <?php echo _("Live Session"); ?> / <?php echo _("Meeting"); ?></h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="peerjs_host">Peerjs <?php echo _("Server Host"); ?></label>
                                    <input type="text" class="form-control" id="peerjs_host" value="<?php echo $settings['peerjs_host']; ?>" />
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="peerjs_port">Peerjs <?php echo _("Server Port"); ?></label>
                                    <input type="text" class="form-control" id="peerjs_port" value="<?php echo $settings['peerjs_port']; ?>" />
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="peerjs_path">Peerjs <?php echo _("Server Path"); ?></label>
                                    <input type="text" class="form-control" id="peerjs_path" value="<?php echo $settings['peerjs_path']; ?>" />
                                </div>
                            </div>
                            <div class="col-md-3">
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="turn_host">TURN/STUN <?php echo _("Host"); ?></label>
                                    <input type="text" class="form-control" id="turn_host" value="<?php echo $settings['turn_host']; ?>" />
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="turn_port">TURN/STUN <?php echo _("Port"); ?></label>
                                    <input type="text" class="form-control" id="turn_port" value="<?php echo $settings['turn_port']; ?>" />
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="turn_username">TURN <?php echo _("Username"); ?></label>
                                    <input type="text" class="form-control" id="turn_username" value="<?php echo $settings['turn_username']; ?>" />
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="turn_password">TURN <?php echo _("Password"); ?></label>
                                    <input type="text" class="form-control" id="turn_password" value="<?php echo $settings['turn_password']; ?>" />
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="jitsi_domain">Jitsi <?php echo _("Server Domain"); ?></label>
                                    <input type="text" class="form-control" id="jitsi_domain" value="<?php echo $settings['jitsi_domain']; ?>" />
                                </div>
                            </div>
                            <div class="col-md-12">
                                To create your servers please refer to these 2 links: <a href="https://github.com/peers/peerjs-server" target="_blank">PeerJs Server</a> - <a href="https://ourcodeworld.com/articles/read/1175/how-to-create-and-configure-your-own-stun-turn-server-with-coturn-in-ubuntu-18-04" target="_blank">TURN/STUN Server</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-6 mb-4">
                <div class="card shadow mb-12">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-folder-plus"></i> <?php echo _("Template"); ?></h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="id_vt_template"><?php echo _("Virtual Tour"); ?> <i title="<?php echo _("virtual tour used as template when create a new one"); ?>" class="help_t fas fa-question-circle"></i></label>
                                    <select class="form-control" id="id_vt_template">
                                        <option <?php echo (empty($settings['id_vt_template'])) ? 'selected' : ''; ?> id="0"><?php echo _("None"); ?></option>
                                        <?php echo get_virtual_tours_options($settings['id_vt_template']); ?>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6 mb-4">
                <div class="card shadow mb-12">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-file-import"></i> <?php echo _("Sample"); ?></h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="enable_sample"><?php echo _("Enable"); ?></label><br>
                                    <input <?php echo ($settings['enable_sample']) ? 'checked':''; ?> type="checkbox" id="enable_sample" />
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="id_vt_sample"><?php echo _("Virtual Tour"); ?> <i title="<?php echo _("virtual tour used as sample data"); ?>" class="help_t fas fa-question-circle"></i></label>
                                    <select class="form-control" id="id_vt_sample">
                                        <option <?php echo (empty($settings['id_vt_sample'])) ? 'selected' : ''; ?> id="0"><?php echo _("Included (SVT demo)"); ?></option>
                                        <?php echo get_virtual_tours_options($settings['id_vt_sample']); ?>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12 mb-4">
                <div class="card shadow mb-12">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-directions"></i> <?php echo _("Presentation"); ?></h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="enable_screencast"><?php echo _("Enable Screencast"); ?></label><br>
                                    <input <?php echo ($settings['enable_screencast']) ? 'checked':''; ?> type="checkbox" id="enable_screencast" />
                                </div>
                            </div>
                            <div class="col-md-8">
                                <div class="form-group">
                                    <label for="url_screencast"><?php echo _("Url Screencast App"); ?> <i title="<?php echo _("link to the screencast web app that allows you to record your screen"); ?>" class="help_t fas fa-question-circle"></i></label>
                                    <input type="text" class="form-control" id="url_screencast" value="<?php echo $settings['url_screencast']; ?>" />
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12 mb-4">
                <div class="card shadow mb-12">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-bolt"></i> <?php echo _("Multiresolution"); ?></h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="multires"><?php echo _("Type"); ?></label>
                                    <select onchange="change_multires();" class="form-control" id="multires">
                                        <option <?php echo ($settings['multires']=='local') ? 'selected' : ''; ?> id="local"><?php echo _("Local"); ?></option>
                                        <option <?php echo ($settings['multires']=='cloud') ? 'selected' : ''; ?> id="cloud"><?php echo _("External"); ?></option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group ">
                                    <label for="multires_cloud_url"><?php echo _("External URL"); ?></label>
                                    <input <?php echo ($settings['multires']=='local') ? 'disabled' : ''; ?> type="text" id="multires_cloud_url" class="form-control" value="<?php echo $settings['multires_cloud_url']; ?>" />
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label><?php echo _("Multi resolution check"); ?> <i title="<?php echo _("check if your system can generate multi resolution panoramas"); ?>" class="help_t fas fa-question-circle"></i></label><br>
                                    <button id="btn_check_multires_req" onclick="check_multires_req();" class="btn btn-block btn-primary"><?php echo _("Check Requirements"); ?></button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12 mb-4">
                <div class="card shadow mb-12">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-language"></i> <?php echo _("Localization"); ?></h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="language"><?php echo _("Default Language"); ?></label>
                                    <select class="form-control" id="language">
                                        <option <?php echo ($settings['language']=='ar_SA') ? 'selected':''; ?> id="ar_SA">Arabic (ar_SA)</option>
                                        <option <?php echo ($settings['language']=='zh_CN') ? 'selected':''; ?> id="zh_CN">Chinese simplified (zh_CN)</option>
                                        <option <?php echo ($settings['language']=='zh_HK') ? 'selected':''; ?> id="zh_HK">Chinese traditional (zh_HK)</option>
                                        <option <?php echo ($settings['language']=='zh_TW') ? 'selected':''; ?> id="zh_TW">Chinese traditional (zh_TW)</option>
                                        <option <?php echo ($settings['language']=='cs_CZ') ? 'selected':''; ?> id="cs_CZ">Czech (cs_CZ)</option>
                                        <option <?php echo ($settings['language']=='nl_NL') ? 'selected':''; ?> id="nl_NL">Dutch (nl_NL)</option>
                                        <option <?php echo ($settings['language']=='en_US') ? 'selected':''; ?> id="en_US">English (en_US)</option>
                                        <option <?php echo ($settings['language']=='fil_PH') ? 'selected':''; ?> id="fil_PH">Filipino (fil_PH)</option>
                                        <option <?php echo ($settings['language']=='fr_FR') ? 'selected':''; ?> id="fr_FR">French (fr_FR)</option>
                                        <option <?php echo ($settings['language']=='de_DE') ? 'selected':''; ?> id="de_DE">German (de_DE)</option>
                                        <option <?php echo ($settings['language']=='hi_IN') ? 'selected':''; ?> id="hi_IN">Hindi (hi_IN)</option>
                                        <option <?php echo ($settings['language']=='hu_HU') ? 'selected':''; ?> id="hu_HU">Hungarian (hu_HU)</option>
                                        <option <?php echo ($settings['language']=='rw_RW') ? 'selected':''; ?> id="rw_RW">Kinyarwanda (rw_RW)</option>
                                        <option <?php echo ($settings['language']=='ko_KR') ? 'selected':''; ?> id="ko_KR">Korean (ko_KR)</option>
                                        <option <?php echo ($settings['language']=='it_IT') ? 'selected':''; ?> id="it_IT">Italian (it_IT)</option>
                                        <option <?php echo ($settings['language']=='ja_JP') ? 'selected':''; ?> id="ja_JP">Japanese (ja_JP)</option>
                                        <option <?php echo ($settings['language']=='fa_IR') ? 'selected':''; ?> id="fa_IR">Persian (fa_IR)</option>
                                        <option <?php echo ($settings['language']=='pl_PL') ? 'selected':''; ?> id="pl_PL">Polish (pl_PL)</option>
                                        <option <?php echo ($settings['language']=='pt_BR') ? 'selected':''; ?> id="pt_BR">Portuguese Brazilian (pt_BR)</option>
                                        <option <?php echo ($settings['language']=='pt_PT') ? 'selected':''; ?> id="pt_PT">Portuguese European (pt_PT)</option>
                                        <option <?php echo ($settings['language']=='es_ES') ? 'selected':''; ?> id="es_ES">Spanish (es_ES)</option>
                                        <option <?php echo ($settings['language']=='ro_RO') ? 'selected':''; ?> id="ro_RO">Romanian (ro_RO)</option>
                                        <option <?php echo ($settings['language']=='ru_RU') ? 'selected':''; ?> id="ru_RU">Russian (ru_RU)</option>
                                        <option <?php echo ($settings['language']=='sv_SE') ? 'selected':''; ?> id="sv_SE">Swedish (sv_SE)</option>
                                        <option <?php echo ($settings['language']=='tg_TJ') ? 'selected':''; ?> id="tg_TJ">Tajik (tg_TJ)</option>
                                        <option <?php echo ($settings['language']=='th_TH') ? 'selected':''; ?> id="th_TH">Thai (th_TH)</option>
                                        <option <?php echo ($settings['language']=='tr_TR') ? 'selected':''; ?> id="tr_TR">Turkish (tr_TR)</option>
                                        <option <?php echo ($settings['language']=='vi_VN') ? 'selected':''; ?> id="vi_VN">Vietnamese (vi_VN)</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="language_domain"><?php echo _("Translation Type"); ?></label>
                                    <select class="form-control" id="language_domain">
                                        <option <?php echo ($settings['language_domain']=='default') ? 'selected':''; ?> id="default_lang">Default</option>
                                        <option <?php echo ($settings['language_domain']=='custom') ? 'selected':''; ?> id="custom_lang">Custom</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="languages_enabled"><?php echo _("Languages Enabled"); ?></label>
                                    <select style="height: 125px" multiple class="form-control selectpicker" id="languages_enabled" data-actions-box="true" data-selected-text-format="count > 3" data-count-selected-text="{0} <?php echo _("items selected"); ?>" data-deselect-all-text="<?php echo _("Deselect All"); ?>" data-select-all-text="<?php echo _("Select All"); ?>" data-none-selected-text="<?php echo _("Nothing selected"); ?>" data-none-results-text="<?php echo _("No results matched"); ?> {0}">
                                        <option <?php echo (check_language_enabled('ar_SA',$settings['languages_enabled'])) ? 'selected':''; ?> id="ls_ar_SA">Arabic (ar_SA)</option>
                                        <option <?php echo (check_language_enabled('zh_CN',$settings['languages_enabled'])) ? 'selected':''; ?> id="ls_zh_CN">Chinese simplified (zh_CN)</option>
                                        <option <?php echo (check_language_enabled('zh_HK',$settings['languages_enabled'])) ? 'selected':''; ?> id="ls_zh_HK">Chinese traditional (zh_HK)</option>
                                        <option <?php echo (check_language_enabled('zh_TW',$settings['languages_enabled'])) ? 'selected':''; ?> id="ls_zh_TW">Chinese traditional (zh_TW)</option>
                                        <option <?php echo (check_language_enabled('cs_CZ',$settings['languages_enabled'])) ? 'selected':''; ?> id="ls_cs_CZ">Czech (cs_CZ)</option>
                                        <option <?php echo (check_language_enabled('nl_NL',$settings['languages_enabled'])) ? 'selected':''; ?> id="ls_nl_NL">Dutch (nl_NL)</option>
                                        <option <?php echo (check_language_enabled('en_US',$settings['languages_enabled'])) ? 'selected':''; ?> id="ls_en_US">English (en_US)</option>
                                        <option <?php echo (check_language_enabled('fil_PH',$settings['languages_enabled'])) ? 'selected':''; ?> id="ls_fil_PH">Filipino (fil_PH)</option>
                                        <option <?php echo (check_language_enabled('fr_FR',$settings['languages_enabled'])) ? 'selected':''; ?> id="ls_fr_FR">French (fr_FR)</option>
                                        <option <?php echo (check_language_enabled('de_DE',$settings['languages_enabled'])) ? 'selected':''; ?> id="ls_de_DE">German (de_DE)</option>
                                        <option <?php echo (check_language_enabled('hi_IN',$settings['languages_enabled'])) ? 'selected':''; ?> id="ls_hi_IN">Hindi (hi_IN)</option>
                                        <option <?php echo (check_language_enabled('hu_HU',$settings['languages_enabled'])) ? 'selected':''; ?> id="ls_hu_HU">Hungarian (hu_HU)</option>
                                        <option <?php echo (check_language_enabled('rw_RW',$settings['languages_enabled'])) ? 'selected':''; ?> id="ls_rw_RW">Kinyarwanda (rw_RW)</option>
                                        <option <?php echo (check_language_enabled('ko_KR',$settings['languages_enabled'])) ? 'selected':''; ?> id="ls_ko_KR">Korean (ko_KR)</option>
                                        <option <?php echo (check_language_enabled('it_IT',$settings['languages_enabled'])) ? 'selected':''; ?> id="ls_it_IT">Italian (it_IT)</option>
                                        <option <?php echo (check_language_enabled('ja_JP',$settings['languages_enabled'])) ? 'selected':''; ?> id="ls_ja_JP">Japanese (ja_JP)</option>
                                        <option <?php echo (check_language_enabled('fa_IR',$settings['languages_enabled'])) ? 'selected':''; ?> id="ls_fa_IR">Persian (fa_IR)</option>
                                        <option <?php echo (check_language_enabled('pl_PL',$settings['languages_enabled'])) ? 'selected':''; ?> id="ls_pl_PL">Polish (pl_PL)</option>
                                        <option <?php echo (check_language_enabled('pt_BR',$settings['languages_enabled'])) ? 'selected':''; ?> id="ls_pt_BR">Portuguese Brazilian (pt_BR)</option>
                                        <option <?php echo (check_language_enabled('pt_PT',$settings['languages_enabled'])) ? 'selected':''; ?> id="ls_pt_PT">Portuguese European (pt_PT)</option>
                                        <option <?php echo (check_language_enabled('es_ES',$settings['languages_enabled'])) ? 'selected':''; ?> id="ls_es_ES">Spanish (es_ES)</option>
                                        <option <?php echo (check_language_enabled('ro_RO',$settings['languages_enabled'])) ? 'selected':''; ?> id="ls_ro_RO">Romanian (ro_RO)</option>
                                        <option <?php echo (check_language_enabled('ru_RU',$settings['languages_enabled'])) ? 'selected':''; ?> id="ls_ru_RU">Russian (ru_RU)</option>
                                        <option <?php echo (check_language_enabled('sv_SE',$settings['languages_enabled'])) ? 'selected':''; ?> id="ls_sv_SE">Swedish (sv_SE)</option>
                                        <option <?php echo (check_language_enabled('tg_TJ',$settings['languages_enabled'])) ? 'selected':''; ?> id="ls_tg_TJ">Tajik (tg_TJ)</option>
                                        <option <?php echo (check_language_enabled('th_TH',$settings['languages_enabled'])) ? 'selected':''; ?> id="ls_th_TH">Thai (th_TH)</option>
                                        <option <?php echo (check_language_enabled('tr_TR',$settings['languages_enabled'])) ? 'selected':''; ?> id="ls_tr_TR">Turkish (tr_TR)</option>
                                        <option <?php echo (check_language_enabled('vi_VN',$settings['languages_enabled'])) ? 'selected':''; ?> id="ls_vi_VN">Vietnamese (vi_VN)</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-12">
                                <p>
                                    If you want to edit translation file you need to follow this instructions:<br>
                                    1) NEW CUSTOM TRANSLATION: Copy the file <i>locale/lang_code/LC_MESSAGES/<b>default.po</b></i> to your computer and rename it to <b>custom.po</b><br>
                                    or<br>
                                    1) EXISTING CUSTOM TRANSLATION: Execute this command <b>msgmerge --update locale/lang_code/LC_MESSAGES/custom.po locale/svt.pot</b> to merge the new strings with your existing <b>custom.po</b> translation file<br>
                                    2) Edit the file <b>custom.po</b> with a text editor or with a POEditor like <a target="_blank" href="https://poedit.net/">this one</a><br>
                                    3) Compile and generate the file <b>custom.mo</b> with the POEditor or with this command <b>msgfmt custom.po --output-file=custom.mo</b><br>
                                    4) Copy the files <b>custom.po</b> and <b>custom.mo</b> to <i>locale/lang_code/LC_MESSAGES/</i><br>
                                    5) Change Translation Type to <b>Custom</b><br>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="tab-pane" id="whitelabel_tab">
        <div class="row">
            <div class="col-md-12 mb-4">
                <div class="card shadow mb-12">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary"><i class="fab fa-sketch"></i> <?php echo _("Branding"); ?></h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="name"><?php echo _("Application Name"); ?></label>
                                    <input type="text" class="form-control" id="name" value="<?php echo $settings['name']; ?>" />
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="name"><?php echo _("Theme Color"); ?></label>
                                    <input type="text" class="form-control" id="theme_color" value="<?php echo $settings['theme_color']; ?>" />
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="font_backend"><?php echo _("Font Backend"); ?></label><br>
                                    <input type="text" class="form-control" id="font_backend" value="<?php echo $settings['font_backend']; ?>" />
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="welcome_msg"><?php echo _("Welcome Message"); ?> <i title="<?php echo _("leave empty for default welcome message"); ?>" class="help_t fas fa-question-circle"></i></label>
                                    <div id="welcome_msg"><?php echo $settings['welcome_msg']; ?></div>
                                </div>
                            </div>
                            <div class="col-md-6 mb-4">
                                <label><?php echo _("Logo"); ?></label>
                                <div style="background-color:#4e73df;display:none;width:calc(100% - 24px);margin:0 auto;" id="div_image_logo" class="col-md-12 text-center">
                                    <img style="width:100%;max-width:300px" src="assets/<?php echo $settings['logo']; ?>" />
                                </div>
                                <div style="display: none" id="div_delete_logo" class="col-md-12 mt-4">
                                    <button <?php echo ($demo) ? 'disabled':''; ?> onclick="delete_b_logo();" class="btn btn-block btn-danger"><?php echo _("DELETE IMAGE"); ?></button>
                                </div>
                                <div style="display: none" id="div_upload_logo">
                                    <form id="frm" action="ajax/upload_b_logo_image.php" method="POST" enctype="multipart/form-data">
                                        <div class="row">
                                            <div class="col-md-12">
                                                <div class="input-group">
                                                    <div class="custom-file">
                                                        <input type="file" class="custom-file-input" id="txtFile" name="txtFile" />
                                                        <label class="custom-file-label text-left" for="txtFile"><?php echo _("Choose file"); ?></label>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-12">
                                                <div class="form-group">
                                                    <input <?php echo ($demo) ? 'disabled':''; ?> type="submit" class="btn btn-block btn-success" id="btnUpload" value="<?php echo _("Upload Logo Image"); ?>" />
                                                </div>
                                            </div>
                                            <div class="col-md-12">
                                                <div class="preview text-center">
                                                    <div id="progress_l" class="progress mb-3 mb-sm-3 mb-lg-0 mb-xl-0" style="height: 2.35rem;display: none">
                                                        <div class="progress-bar" id="progressBar" role="progressbar" aria-valuemin="0" aria-valuemax="100" style="width:0%;">
                                                            0%
                                                        </div>
                                                    </div>
                                                    <div style="display: none;padding: .38rem;" class="alert alert-danger" id="error"></div>
                                                </div>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                            <div class="col-md-6 mb-4">
                                <label><?php echo _("Logo Small"); ?></label>
                                <div style="background-color:#4e73df;display:none;width:calc(100% - 24px);margin:0 auto;" id="div_image_logo_s" class="col-md-12 text-center">
                                    <img style="width:100%;max-width:100px;" src="assets/<?php echo $settings['small_logo']; ?>" />
                                </div>
                                <div style="display: none" id="div_delete_logo_s" class="col-md-12 mt-4">
                                    <button <?php echo ($demo) ? 'disabled':''; ?> onclick="delete_b_logo_s();" class="btn btn-block btn-danger"><?php echo _("DELETE IMAGE"); ?></button>
                                </div>
                                <div style="display: none" id="div_upload_logo_s">
                                    <form id="frm_s" action="ajax/upload_b_logo_image.php" method="POST" enctype="multipart/form-data">
                                        <div class="row">
                                            <div class="col-md-12">
                                                <div class="input-group">
                                                    <div class="custom-file">
                                                        <input type="file" class="custom-file-input" id="txtFile_s" name="txtFile_s" />
                                                        <label class="custom-file-label text-left" for="txtFile_s"><?php echo _("Choose file"); ?></label>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-12">
                                                <div class="form-group">
                                                    <input <?php echo ($demo) ? 'disabled':''; ?> type="submit" class="btn btn-block btn-success" id="btnUpload_s" value="<?php echo _("Upload Logo Image"); ?>" />
                                                </div>
                                            </div>
                                            <div class="col-md-12">
                                                <div class="preview text-center">
                                                    <div id="progress_l_s" class="progress mb-3 mb-sm-3 mb-lg-0 mb-xl-0" style="height: 2.35rem;display: none">
                                                        <div class="progress-bar" id="progressBar_s" role="progressbar" aria-valuemin="0" aria-valuemax="100" style="width:0%;">
                                                            0%
                                                        </div>
                                                    </div>
                                                    <div style="display: none;padding: .38rem;" class="alert alert-danger" id="error_s"></div>
                                                </div>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                            <div class="col-md-6 mb-4">
                                <label><?php echo _("Login image"); ?></label>
                                <div style="display: none" id="div_image_bg" class="col-md-12">
                                    <img style="width: 100%" src="assets/<?php echo $settings['background']; ?>" />
                                </div>
                                <div style="display: none" id="div_delete_bg" class="col-md-12 mt-4">
                                    <button <?php echo ($demo) ? 'disabled':''; ?> onclick="delete_b_bg();" class="btn btn-block btn-danger"><?php echo _("DELETE IMAGE"); ?></button>
                                </div>
                                <div style="display: none" id="div_upload_bg">
                                    <form id="frm_b" action="ajax/upload_b_background_image.php" method="POST" enctype="multipart/form-data">
                                        <div class="row">
                                            <div class="col-md-12">
                                                <div class="input-group">
                                                    <div class="custom-file">
                                                        <input type="file" class="custom-file-input" id="txtFile_b" name="txtFile_b" />
                                                        <label class="custom-file-label text-left" for="txtFile_b"><?php echo _("Choose file"); ?></label>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-12">
                                                <div class="form-group">
                                                    <input <?php echo ($demo) ? 'disabled':''; ?> type="submit" class="btn btn-block btn-success" id="btnUpload_b" value="<?php echo _("Upload Login Image"); ?>" />
                                                </div>
                                            </div>
                                            <div class="col-md-12">
                                                <div class="preview text-center">
                                                    <div id="progress_bl" class="progress mb-3 mb-sm-3 mb-lg-0 mb-xl-0" style="height: 2.35rem;display: none">
                                                        <div class="progress-bar" id="progressBar_b" role="progressbar" aria-valuemin="0" aria-valuemax="100" style="width:0%;">
                                                            0%
                                                        </div>
                                                    </div>
                                                    <div style="display: none;padding: .38rem;" class="alert alert-danger" id="error_b"></div>
                                                </div>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                            <div class="col-md-6 mb-4">
                                <label><?php echo _("Registration image"); ?></label>
                                <div style="display: none" id="div_image_bg_reg" class="col-md-12">
                                    <img style="width: 100%" src="assets/<?php echo $settings['background_reg']; ?>" />
                                </div>
                                <div style="display: none" id="div_delete_bg_reg" class="col-md-12 mt-4">
                                    <button <?php echo ($demo) ? 'disabled':''; ?> onclick="delete_b_bg_reg();" class="btn btn-block btn-danger"><?php echo _("DELETE IMAGE"); ?></button>
                                </div>
                                <div style="display: none" id="div_upload_bg_reg">
                                    <form id="frm_b_reg" action="ajax/upload_b_background_image.php" method="POST" enctype="multipart/form-data">
                                        <div class="row">
                                            <div class="col-md-12">
                                                <div class="input-group">
                                                    <div class="custom-file">
                                                        <input type="file" class="custom-file-input" id="txtFile_b_reg" name="txtFile_b_reg" />
                                                        <label class="custom-file-label text-left" for="txtFile_b_reg"><?php echo _("Choose file"); ?></label>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-12">
                                                <div class="form-group">
                                                    <input <?php echo ($demo) ? 'disabled':''; ?> type="submit" class="btn btn-block btn-success" id="btnUpload_b_reg" value="<?php echo _("Upload Registration Image"); ?>" />
                                                </div>
                                            </div>
                                            <div class="col-md-12">
                                                <div class="preview text-center">
                                                    <div id="progress_br" class="progress mb-3 mb-sm-3 mb-lg-0 mb-xl-0" style="height: 2.35rem;display: none">
                                                        <div class="progress-bar" id="progressBar_b_reg" role="progressbar" aria-valuemin="0" aria-valuemax="100" style="width:0%;">
                                                            0%
                                                        </div>
                                                    </div>
                                                    <div style="display: none;padding: .38rem;" class="alert alert-danger" id="error_b_reg"></div>
                                                </div>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12 mb-4">
                <div class="card shadow mb-12">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-ellipsis-h"></i> <?php echo _("Footer"); ?></h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="footer_link_1"><?php echo _("Name Item 1"); ?></label><br>
                                    <input type="text" class="form-control" id="footer_link_1" value="<?php echo $settings['footer_link_1']; ?>" />
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="footer_value_1"><?php echo _("Content Item 1"); ?> <i title="<?php echo _("insert a textual content or a link to an external site"); ?>" class="help_t fas fa-question-circle"></i></label><br>
                                    <div id="footer_value_1"><?php echo $settings['footer_value_1']; ?></div>
                                </div>
                            </div>
                        </div>
                        <hr>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="footer_link_2"><?php echo _("Name Item 2"); ?></label><br>
                                    <input type="text" class="form-control" id="footer_link_2" value="<?php echo $settings['footer_link_2']; ?>" />
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="footer_value_2"><?php echo _("Content Item 2"); ?> <i title="<?php echo _("insert a textual content or a link to an external site"); ?>" class="help_t fas fa-question-circle"></i></label><br>
                                    <div id="footer_value_2"><?php echo $settings['footer_value_2']; ?></div>
                                </div>
                            </div>
                        </div>
                        <hr>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="footer_link_3"><?php echo _("Name Item 3"); ?></label><br>
                                    <input type="text" class="form-control" id="footer_link_3" value="<?php echo $settings['footer_link_3']; ?>" />
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="footer_value_3"><?php echo _("Content Item 3"); ?> <i title="<?php echo _("insert a textual content or a link to an external site"); ?>" class="help_t fas fa-question-circle"></i></label><br>
                                    <div id="footer_value_3"><?php echo $settings['footer_value_3']; ?></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="tab-pane" id="style_tab">
        <div class="row">
            <div class="col-md-12 mb-4">
                <div class="card shadow mb-12">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary"><i class="fab fa-css3-alt"></i> <?php echo _("Custom Viewer CSS"); ?></h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-12 mb-1">
                                <select onchange="change_editor_css();" class="form-control" id="css_name">
                                    <option id="css_custom"><?php echo _("General (affects all virtual tours)"); ?></option>
                                    <?php echo get_virtual_tours_options_css(); ?>
                                </select>
                            </div>
                            <div class="col-md-12">
                                <div style="position: relative;width: 100%;height: 400px;" class="editors_css" id="custom"><?php echo get_editor_css_content('custom'); ?></div>
                                <?php echo get_virtual_tours_editors_css(); ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12 mb-4">
                <div class="card shadow mb-12">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary"><i class="fab fa-css3-alt"></i> <?php echo _("Custom Backend CSS"); ?></h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-12">
                                <div style="position: relative;width: 100%;height: 400px;" class="editors_css" id="custom_b"><?php echo get_editor_css_content('custom_b'); ?></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12 mb-4">
                <div class="card shadow mb-12">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary"><i class="fab fa-js-square"></i> <?php echo _("Custom Viewer JS"); ?></h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-12 mb-1">
                                <select onchange="change_editor_js();" class="form-control" id="js_name">
                                    <option id="js_custom"><?php echo _("General (affects all virtual tours)"); ?></option>
                                    <?php echo get_virtual_tours_options_js(); ?>
                                </select>
                            </div>
                            <div class="col-md-12">
                                <div style="position: relative;width: 100%;height: 400px;" class="editors_js" id="custom_js"><?php echo get_editor_js_content('custom'); ?></div>
                                <?php echo get_virtual_tours_editors_js(); ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="tab-pane" id="mail_tab">
        <?php
        if($settings['smtp_valid']) {
            $smtp_valid = "<i style='color: green' class=\"fas fa-circle\"></i> "._("Valid");
        } else {
            $smtp_valid = "<i style='color: red' class=\"fas fa-circle\"></i> "._("Invalid");
        }
        ?>
        <div class="row">
            <div class="col-md-12 mb-4">
                <div class="card shadow mb-12">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary float-left"><i class="fas fa-envelope"></i> <?php echo _("Mail Server Settings"); ?></h6> <span id="validate_mail" class="float-right"><?php echo $smtp_valid; ?></span>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="smtp_server"><?php echo _("SMTP Server"); ?></label>
                                    <input type="text" class="form-control" id="smtp_server" value="<?php echo $settings['smtp_server']; ?>" />
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label for="smtp_port"><?php echo _("SMTP Port"); ?></label>
                                    <input type="number" class="form-control" id="smtp_port" value="<?php echo $settings['smtp_port']; ?>" />
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="smtp_secure"><?php echo _("SMTP Secure"); ?></label>
                                    <select class="form-control" id="smtp_secure">
                                        <option <?php echo ($settings['smtp_secure']=='none') ? 'selected':''; ?> id="none"><?php echo _("None"); ?></option>
                                        <option <?php echo ($settings['smtp_secure']=='ssl') ? 'selected':''; ?> id="ssl">SSL</option>
                                        <option <?php echo ($settings['smtp_secure']=='tls') ? 'selected':''; ?> id="tls">TLS</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="smtp_auth"><?php echo _("SMTP Auth"); ?></label><br>
                                    <input <?php echo ($settings['smtp_auth']) ? 'checked':''; ?> type="checkbox" id="smtp_auth" />
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="smtp_username"><?php echo _("SMTP Auth - Username"); ?></label>
                                    <input type="text" class="form-control" id="smtp_username" value="<?php echo $settings['smtp_username']; ?>" />
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="smtp_password"><?php echo _("SMTP Auth - Password"); ?></label>
                                    <input type="password" class="form-control" id="smtp_password" value="<?php echo ($settings['smtp_password']!='') ? 'keep_password' : ''; ?>" />
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="smtp_from_email"><?php echo _("From E-Mail"); ?></label>
                                    <input type="text" class="form-control" id="smtp_from_email" value="<?php echo $settings['smtp_from_email']; ?>" />
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="smtp_from_name"><?php echo _("From Name"); ?></label>
                                    <input type="text" class="form-control" id="smtp_from_name" value="<?php echo $settings['smtp_from_name']; ?>" />
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <button id="btn_validate_mail" onclick="save_settings(true);" class="btn btn-primary btn-block"><?php echo _("VALIDATE MAIL SETTINGS"); ?></button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12 mb-4">
                <div class="card shadow mb-12">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary float-left"><i class="fas fa-envelope-open-text"></i> <?php echo _("Mail Texts"); ?></h6></span>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label for="mail_activate_subject"><?php echo _("Activation mail - Subject"); ?></label>
                                        <input type="text" class="form-control" id="mail_activate_subject" value="<?php echo $settings['mail_activate_subject']; ?>" />
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label for="mail_activate_body"><?php echo _("Activation mail - Body"); ?> <i title="<?php echo _("mandatory variables in the mail text: "); ?> %LINK%" class="help_t fas fa-exclamation-circle"></i></label>
                                        <div id="mail_activate_body"><?php echo $settings['mail_activate_body']; ?></div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label for="mail_forgot_subject"><?php echo _("Forgot password mail - Subject"); ?></label>
                                        <input type="text" class="form-control" id="mail_forgot_subject" value="<?php echo $settings['mail_forgot_subject']; ?>" />
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label for="mail_forgot_body"><?php echo _("Forgot password mail - Body"); ?> <i title="<?php echo _("mandatory variables in the mail text: "); ?> %LINK% , %VERFIFICATION_CODE%" class="help_t fas fa-exclamation-circle"></i></label>
                                        <div id="mail_forgot_body"><?php echo $settings['mail_forgot_body']; ?></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="tab-pane" id="notify_tab">
        <div class="row">
            <div class="col-md-12 mb-4">
                <div class="card shadow mb-12">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-bell"></i> <?php echo _("Notifications"); ?></h6>
                    </div>
                    <div class="card-body">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="cronjob"><?php echo _("Cron Job"); ?> <i title="<?php echo _("you have to run this php script as a cronjob every minute on your server for notifications requiring it to work"); ?>" class="help_t fas fa-exclamation-circle"></i></label>
                                <input readonly type="text" class="form-control" id="cronjob" value="<?php echo $cronjob_dir; ?>" />
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="notify_email"><?php echo _("E-Mail"); ?></label>
                                <input type="text" class="form-control" id="notify_email" value="<?php echo $settings['notify_email']; ?>" />
                            </div>
                        </div>
                        <div class="col-md-12">
                            <table id="table_notifications" class="table table-bordered">
                                <thead>
                                <tr>
                                    <th scope="col"><?php echo _("Type"); ?></th>
                                    <th scope="col"><?php echo _("Require Cron"); ?></th>
                                    <th scope="col"><?php echo _("Enabled"); ?></th>
                                </tr>
                                </thead>
                                <tr>
                                    <td><?php echo _("A new user is registered"); ?></td>
                                    <td><i class="fas fa-times"></i></td>
                                    <td><input id="notify_registrations" type="checkbox" <?php echo ($settings['notify_registrations']) ? 'checked' : ''; ?> /></td>
                                </tr>
                                <tr>
                                    <td><?php echo _("The Plan has expired"); ?></td>
                                    <td><i class="fas fa-check"></i></td>
                                    <td><input id="notify_plan_expires" type="checkbox" <?php echo ($settings['notify_plan_expires']) ? 'checked' : ''; ?> /></td>
                                </tr>
                                <tr>
                                    <td><?php echo _("The Plan is changed"); ?></td>
                                    <td><i class="fas fa-check"></i></td>
                                    <td><input id="notify_plan_changes" type="checkbox" <?php echo ($settings['notify_plan_changes']) ? 'checked' : ''; ?> /></td>
                                </tr>
                                <tr>
                                    <td><?php echo _("The Plan is canceled"); ?></td>
                                    <td><i class="fas fa-check"></i></td>
                                    <td><input id="notify_plan_cancels" type="checkbox" <?php echo ($settings['notify_plan_cancels']) ? 'checked' : ''; ?> /></td>
                                </tr>
                                <tr>
                                    <td><?php echo _("A Tour is created"); ?></td>
                                    <td><i class="fas fa-times"></i></td>
                                    <td><input id="notify_vt_create" type="checkbox" <?php echo ($settings['notify_vt_create']) ? 'checked' : ''; ?> /></td>
                                </tr>
                                <tbody>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="tab-pane" id="social_tab">
        <div class="row">
            <div class="col-md-12 mb-4">
                <div class="card shadow mb-12">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-comments"></i> <?php echo _("Social Integration"); ?></h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-12">
                                <p><?php echo _("To make the integration with social providers work, you need to create login applications and retrieve credentials in their respective developer panels. Where required, enter the following parameters to enable the integrations."); ?></p>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label><?php echo _("Callback Url"); ?></label>
                                    <input type="text" readonly class="form-control" value="<?php echo $callback_url; ?>" />
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label><?php echo _("Whitelist Domain"); ?></label>
                                    <input type="text" readonly class="form-control" value="<?php echo $domain; ?>" />
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="social_google_enable"><?php echo _("Google - Enable"); ?></label><br>
                                    <input <?php echo ($settings['social_google_enable']) ? 'checked':''; ?> type="checkbox" id="social_google_enable" />
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="social_google_id"><?php echo _("Google - Id"); ?></label>
                                    <input type="password" class="form-control" id="social_google_id" value="<?php echo ($settings['social_google_id']!='') ? 'keep_password' : ''; ?>" />
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="social_google_secret"><?php echo _("Google - Secret"); ?></label>
                                    <input type="password" class="form-control" id="social_google_secret" value="<?php echo ($settings['social_google_secret']!='') ? 'keep_password' : ''; ?>" />
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="social_facebook_enable"><?php echo _("Facebook - Enable"); ?></label><br>
                                    <input <?php echo ($settings['social_facebook_enable']) ? 'checked':''; ?> type="checkbox" id="social_facebook_enable" />
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="social_facebook_id"><?php echo _("Facebook - Id"); ?></label>
                                    <input type="password" class="form-control" id="social_facebook_id" value="<?php echo ($settings['social_facebook_id']!='') ? 'keep_password' : ''; ?>" />
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="social_facebook_secret"><?php echo _("Facebook - Secret"); ?></label>
                                    <input type="password" class="form-control" id="social_facebook_secret" value="<?php echo ($settings['social_facebook_secret']!='') ? 'keep_password' : ''; ?>" />
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="social_twitter_enable"><?php echo _("Twitter - Enable"); ?></label><br>
                                    <input <?php echo ($settings['social_twitter_enable']) ? 'checked':''; ?> type="checkbox" id="social_twitter_enable" />
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="social_twitter_id"><?php echo _("Twitter - Id"); ?></label>
                                    <input type="password" class="form-control" id="social_twitter_id" value="<?php echo ($settings['social_twitter_id']!='') ? 'keep_password' : ''; ?>" />
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="social_twitter_secret"><?php echo _("Twitter - Secret"); ?></label>
                                    <input type="password" class="form-control" id="social_twitter_secret" value="<?php echo ($settings['social_twitter_secret']!='') ? 'keep_password' : ''; ?>" />
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="tab-pane" id="registration_tab">
        <div class="row">
            <div class="col-md-12">
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-registered"></i> <?php echo _("Registration Settings"); ?></h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="enable_registration"><?php echo _("Enable"); ?> <i title="<?php echo _("enables registration form for users"); ?>" class="help_t fas fa-question-circle"></i></label><br>
                                    <input type="checkbox" id="enable_registration" <?php echo ($settings['enable_registration'])?'checked':''; ?> />
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="validate_email"><?php echo _("Validate Email"); ?> <i title="<?php echo _("send an email to new users to validate their account"); ?>" class="help_t fas fa-question-circle"></i></label><br>
                                    <input type="checkbox" id="validate_email" <?php echo ($settings['validate_email'])?'checked':''; ?> />
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="change_plan"><?php echo _("Change Plan"); ?> <i title="<?php echo _("enables change plan for users"); ?>" class="help_t fas fa-question-circle"></i></label><br>
                                    <input type="checkbox" id="change_plan" <?php echo ($settings['change_plan'])?'checked':''; ?> />
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="default_plan"><?php echo _("Default Plan"); ?> <i title="<?php echo _("default plan assigned to new registered users"); ?>" class="help_t fas fa-question-circle"></i></label>
                                    <select class="form-control" id="default_plan">
                                        <?php echo get_plans_options($settings['default_id_plan']); ?>
                                    </select>
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
                        <table id="table_fields" class="table table-bordered">
                            <thead>
                            <tr>
                                <th scope="col"><?php echo _("Field"); ?></th>
                                <th scope="col"><?php echo _("Enable"); ?></th>
                                <th scope="col"><?php echo _("Mandatory"); ?></th>
                            </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td><?php echo _("First Name"); ?></td>
                                    <td><input id="first_name_enable" type="checkbox" <?php echo ($settings['first_name_enable']) ? 'checked' : '' ; ?> /></td>
                                    <td><input id="first_name_mandatory" type="checkbox" <?php echo ($settings['first_name_mandatory']) ? 'checked' : '' ; ?> /></td>
                                </tr>
                                <tr>
                                    <td><?php echo _("Last Name"); ?></td>
                                    <td><input id="last_name_enable" type="checkbox" <?php echo ($settings['last_name_enable']) ? 'checked' : '' ; ?> /></td>
                                    <td><input id="last_name_mandatory" type="checkbox" <?php echo ($settings['last_name_mandatory']) ? 'checked' : '' ; ?> /></td>
                                </tr>
                                <tr>
                                    <td><?php echo _("Company"); ?></td>
                                    <td><input id="company_enable" type="checkbox" <?php echo ($settings['company_enable']) ? 'checked' : '' ; ?> /></td>
                                    <td><input id="company_mandatory" type="checkbox" <?php echo ($settings['company_mandatory']) ? 'checked' : '' ; ?> /></td>
                                </tr>
                                <tr>
                                    <td><?php echo _("Tax Id"); ?></td>
                                    <td><input id="tax_id_enable" type="checkbox" <?php echo ($settings['tax_id_enable']) ? 'checked' : '' ; ?> /></td>
                                    <td><input id="tax_id_mandatory" type="checkbox" <?php echo ($settings['tax_id_mandatory']) ? 'checked' : '' ; ?> /></td>
                                </tr>
                                <tr>
                                    <td><?php echo _("Address"); ?></td>
                                    <td><input id="street_enable" type="checkbox" <?php echo ($settings['street_enable']) ? 'checked' : '' ; ?> /></td>
                                    <td><input id="street_mandatory" type="checkbox" <?php echo ($settings['street_mandatory']) ? 'checked' : '' ; ?> /></td>
                                </tr>
                                <tr>
                                    <td><?php echo _("City"); ?></td>
                                    <td><input id="city_enable" type="checkbox" <?php echo ($settings['city_enable']) ? 'checked' : '' ; ?> /></td>
                                    <td><input id="city_mandatory" type="checkbox" <?php echo ($settings['city_mandatory']) ? 'checked' : '' ; ?> /></td>
                                </tr>
                                <tr>
                                    <td><?php echo _("State / Province / Region"); ?></td>
                                    <td><input id="province_enable" type="checkbox" <?php echo ($settings['province_enable']) ? 'checked' : '' ; ?> /></td>
                                    <td><input id="province_mandatory" type="checkbox" <?php echo ($settings['province_mandatory']) ? 'checked' : '' ; ?> /></td>
                                </tr>
                                <tr>
                                    <td><?php echo _("Zip / Postal Code"); ?></td>
                                    <td><input id="postal_code_enable" type="checkbox" <?php echo ($settings['postal_code_enable']) ? 'checked' : '' ; ?> /></td>
                                    <td><input id="postal_code_mandatory" type="checkbox" <?php echo ($settings['postal_code_mandatory']) ? 'checked' : '' ; ?> /></td>
                                </tr>
                                <tr>
                                    <td><?php echo _("Country"); ?></td>
                                    <td><input id="country_enable" type="checkbox" <?php echo ($settings['country_enable']) ? 'checked' : '' ; ?> /></td>
                                    <td><input id="country_mandatory" type="checkbox" <?php echo ($settings['country_mandatory']) ? 'checked' : '' ; ?> /></td>
                                </tr>
                                <tr>
                                    <td><?php echo _("Telephone"); ?></td>
                                    <td><input id="tel_enable" type="checkbox" <?php echo ($settings['tel_enable']) ? 'checked' : '' ; ?> /></td>
                                    <td><input id="tel_mandatory" type="checkbox" <?php echo ($settings['tel_mandatory']) ? 'checked' : '' ; ?> /></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="tab-pane" id="payments_tab">
        <div class="col-md-12 mb-4">
            <div class="card shadow mb-12">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary"><i class="fab fa-stripe-s"></i> Stripe</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-2">
                            <div class="form-group">
                                <label for="stripe_enabled"><?php echo _("Enable"); ?> <i title="<?php echo _("enable this payment method (you need to initialize first)"); ?>" class="help_t fas fa-question-circle"></i></label><br>
                                <input <?php echo ($settings['stripe_enabled']==0)?'disabled':''; ?> type="checkbox" id="stripe_enabled" <?php echo ($settings['stripe_enabled'])?'checked':''; ?> />
                            </div>
                        </div>
                        <div class="col-md-2"></div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="stripe_public_key"><?php echo _("Public API Key"); ?></label>
                                <input autocomplete="new-password" class="form-control" type="password" id="stripe_public_key" value="<?php echo ($settings['stripe_public_key']!='') ? 'keep_stripe_public_key' : ''; ?>" />
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="stripe_secret_key"><?php echo _("Secret API Key"); ?></label>
                                <input autocomplete="new-password" class="form-control" type="password" id="stripe_secret_key" value="<?php echo ($settings['stripe_secret_key']!='') ? 'keep_stripe_secret_key' : ''; ?>" />
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label style="opacity:0;">.</label><br>
                                <button onclick="stripe_initialize(0);" id="btn_check_stripe" class="btn btn-block btn-primary"><?php echo _("Initialize"); ?>&nbsp;&nbsp;<i class="fas fa-arrow-right"></i></button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-12 mb-4">
            <div class="card shadow mb-12">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary"><i class="fab fa-paypal"></i> PayPal</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-2">
                            <div class="form-group">
                                <label for="paypal_enabled"><?php echo _("Enable"); ?> <i title="<?php echo _("enable this payment method (you need to initialize first)"); ?>" class="help_t fas fa-question-circle"></i></label><br>
                                <input <?php echo ($settings['paypal_enabled']==0)?'disabled':''; ?> type="checkbox" id="paypal_enabled" <?php echo ($settings['paypal_enabled'])?'checked':''; ?> />
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label for="paypal_live"><?php echo _("Live"); ?> <i title="<?php echo _("if not selected, use the paypal sandbox for testing"); ?>" class="help_t fas fa-question-circle"></i></label><br>
                                <input type="checkbox" id="paypal_live" <?php echo ($settings['paypal_live'])?'checked':''; ?> />
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="paypal_client_id"><?php echo _("Client Id"); ?></label>
                                <input autocomplete="new-password" class="form-control" type="password" id="paypal_client_id" value="<?php echo ($settings['paypal_client_id']!='') ? 'keep_paypal_client_id' : ''; ?>" />
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="paypal_client_secret"><?php echo _("Client Secret"); ?></label>
                                <input autocomplete="new-password" class="form-control" type="password" id="paypal_client_secret" value="<?php echo ($settings['paypal_client_secret']!='') ? 'keep_paypal_client_secret' : ''; ?>" />
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label style="opacity:0;">.</label><br>
                                <button onclick="paypal_initialize(0);" id="btn_check_paypal" class="btn btn-block btn-primary"><?php echo _("Initialize"); ?>&nbsp;&nbsp;<i class="fas fa-arrow-right"></i></button>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <?php echo _("if you change from sandbox to live check that you have entered your live API credentials, check Live and Initialize again"); ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="tab-pane" id="voice_commands_tab">
        <div class="row">
            <div class="col-md-12">
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-language"></i> <?php echo _("Language"); ?></h6>
                    </div>
                    <div class="card-body">
                        <p><?php echo _("Voice commands works with all browsers that implement the Speech Recognition interface of the Web Speech API."); ?></p>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <input type="text" class="form-control" id="language_vc" placeholder="<?php echo _("Enter language code"); ?>" value="<?php echo $voice_commands['language']; ?>" />
                                </div>
                            </div>
                            <div class="col-md-8">
                                <a href="#" data-toggle="modal" data-target="#modal_languages">
                                    <?php echo _("Languages Supported"); ?>
                                </a>
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
                        <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-microphone"></i> <?php echo _("Commands"); ?></h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="initial_msg"><?php echo _("Welcome message"); ?></label>
                                    <input type="text" class="form-control" id="initial_msg" value="<?php echo $voice_commands['initial_msg']; ?>" />
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="listening_msg"><?php echo _("Listening message"); ?></label>
                                    <input type="text" class="form-control" id="listening_msg" value="<?php echo $voice_commands['listening_msg']; ?>" />
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="error_msg"><?php echo _("Error message"); ?></label>
                                    <input type="text" class="form-control" id="error_msg" value="<?php echo $voice_commands['error_msg']; ?>" />
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="help_cmd"><b><?php echo _("Help command"); ?></b> (<?php echo _("show help message"); ?>)</label>
                                    <input type="text" class="form-control" id="help_cmd" value="<?php echo $voice_commands['help_cmd']; ?>" />
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="help_msg_1"><?php echo _("Help response"); ?> 1</label>
                                    <input type="text" class="form-control" id="help_msg_1" value="<?php echo $voice_commands['help_msg_1']; ?>" />
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="help_msg_2"><?php echo _("Help response"); ?> 2</label>
                                    <input type="text" class="form-control" id="help_msg_2" value="<?php echo $voice_commands['help_msg_2']; ?>" />
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="next_cmd"><b><?php echo _("Next command"); ?></b> (<?php echo _("go to next room"); ?>)</label>
                                    <input type="text" class="form-control" id="next_cmd" value="<?php echo $voice_commands['next_cmd']; ?>" />
                                </div>
                            </div>
                            <div class="col-md-8">
                                <div class="form-group">
                                    <label for="next_msg"><?php echo _("Next response"); ?></label>
                                    <input type="text" class="form-control" id="next_msg" value="<?php echo $voice_commands['next_msg']; ?>" />
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="prev_cmd"><b><?php echo _("Previous command"); ?></b> (<?php echo _("go to previous room"); ?>)</label>
                                    <input type="text" class="form-control" id="prev_cmd" value="<?php echo $voice_commands['prev_cmd']; ?>" />
                                </div>
                            </div>
                            <div class="col-md-8">
                                <div class="form-group">
                                    <label for="prev_msg"><?php echo _("Previous response"); ?></label>
                                    <input type="text" class="form-control" id="prev_msg" value="<?php echo $voice_commands['prev_msg']; ?>" />
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="left_cmd"><b><?php echo _("Left command"); ?></b> (<?php echo _("looking left"); ?>)</label>
                                    <input type="text" class="form-control" id="left_cmd" value="<?php echo $voice_commands['left_cmd']; ?>" />
                                </div>
                            </div>
                            <div class="col-md-8">
                                <div class="form-group">
                                    <label for="left_msg"><?php echo _("Left response"); ?></label>
                                    <input type="text" class="form-control" id="left_msg" value="<?php echo $voice_commands['left_msg']; ?>" />
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="right_cmd"><b><?php echo _("Right command"); ?></b> (<?php echo _("looking right"); ?>)</label>
                                    <input type="text" class="form-control" id="right_cmd" value="<?php echo $voice_commands['right_cmd']; ?>" />
                                </div>
                            </div>
                            <div class="col-md-8">
                                <div class="form-group">
                                    <label for="right_msg"><?php echo _("Right response"); ?></label>
                                    <input type="text" class="form-control" id="right_msg" value="<?php echo $voice_commands['right_msg']; ?>" />
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="up_cmd"><b><?php echo _("Up command"); ?></b> (<?php echo _("looking up"); ?>)</label>
                                    <input type="text" class="form-control" id="up_cmd" value="<?php echo $voice_commands['up_cmd']; ?>" />
                                </div>
                            </div>
                            <div class="col-md-8">
                                <div class="form-group">
                                    <label for="up_msg"><?php echo _("Up response"); ?></label>
                                    <input type="text" class="form-control" id="up_msg" value="<?php echo $voice_commands['up_msg']; ?>" />
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="down_cmd"><b><?php echo _("Down command"); ?></b> (<?php echo _("looking down"); ?>)</label>
                                    <input type="text" class="form-control" id="down_cmd" value="<?php echo $voice_commands['down_cmd']; ?>" />
                                </div>
                            </div>
                            <div class="col-md-8">
                                <div class="form-group">
                                    <label for="down_msg"><?php echo _("Down response"); ?></label>
                                    <input type="text" class="form-control" id="down_msg" value="<?php echo $voice_commands['down_msg']; ?>" />
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="tab-pane" id="categories_tab">
        <div class="row">
            <div class="col-md-12">
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-th-list"></i> <?php echo _("Categories"); ?></h6>
                    </div>
                    <div class="card-body">
                        <table id="table_categories" class="table table-bordered">
                            <thead>
                                <tr>
                                    <th scope="col">#</th>
                                    <th scope="col"><?php echo _("Name"); ?></th>
                                    <th scope="col"><?php echo _("Actions"); ?></th>
                                </tr>
                            </thead>
                            <tbody>
                            </tbody>
                            <tfoot>
                            <tr>
                                <td></td>
                                <td><input id='cat_new' type='text' class='form-control' value=''></td>
                                <td><button id="btn_add_category" onclick="add_category_s()" class="btn btn-sm btn-success"><i class="fas fa-plus"></i> <?php echo _("new category"); ?></button></td>
                            </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div id="modal_delete_category" class="modal" tabindex="-1" role="dialog" data-backdrop="static">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><?php echo _("Delete Category"); ?></h5>
            </div>
            <div class="modal-body">
                <p><?php echo _("Are you sure you want to delete this category?"); ?></p>
            </div>
            <div class="modal-footer">
                <button <?php echo ($demo) ? 'disabled':''; ?> id="btn_delete_category" onclick="" type="button" class="btn btn-danger"><i class="fas fa-trash"></i> <?php echo _("Yes, Delete"); ?></button>
                <button type="button" class="btn btn-secondary" data-dismiss="modal"><i class="fas fa-times"></i> <?php echo _("Close"); ?></button>
            </div>
        </div>
    </div>
</div>

<div id="modal_stripe_init" class="modal" tabindex="-1" role="dialog" data-backdrop="static">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-body">
                <p><?php echo _("Initializing and synchronizing changes ..."); ?></p>
            </div>
        </div>
    </div>
</div>

<div id="modal_languages" class="modal" tabindex="-1" role="dialog" data-backdrop="static">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><?php echo _("Supported Languages"); ?></h5>
            </div>
            <div class="modal-body">
                Afrikaans <b>af</b> -
                Basque <b>eu</b> -
                Bulgarian <b>bg</b> -
                Catalan <b>ca</b> -
                Arabic (Egypt) <b>ar-EG</b> -
                Arabic (Jordan) <b>ar-JO</b> -
                Arabic (Kuwait) <b>ar-KW</b> -
                Arabic (Lebanon) <b>ar-LB</b> -
                Arabic (Qatar) <b>ar-QA</b> -
                Arabic (UAE) <b>ar-AE</b> -
                Arabic (Morocco) <b>ar-MA</b> -
                Arabic (Iraq) <b>ar-IQ</b> -
                Arabic (Algeria) <b>ar-DZ</b> -
                Arabic (Bahrain) <b>ar-BH</b> -
                Arabic (Lybia) <b>ar-LY</b> -
                Arabic (Oman) <b>ar-OM</b> -
                Arabic (Saudi Arabia) <b>ar-SA</b> -
                Arabic (Tunisia) <b>ar-TN</b> -
                Arabic (Yemen) <b>ar-YE</b> -
                Czech <b>cs</b> -
                Dutch <b>nl-NL</b> -
                English (Australia) <b>en-AU</b> -
                English (Canada) <b>en-CA</b> -
                English (India) <b>en-IN</b> -
                English (New Zealand) <b>en-NZ</b> -
                English (South Africa) <b>en-ZA</b> -
                English(UK) <b>en-GB</b> -
                English(US) <b>en-US</b> -
                Finnish <b>fi</b> -
                French <b>fr-FR</b> -
                Galician <b>gl</b> -
                German <b>de-DE</b> -
                Greek <b>el-GR</b> -
                Hebrew <b>he</b> -
                Hungarian <b>hu</b> -
                Icelandic <b>is</b> -
                Italian <b>it-IT</b> -
                Indonesian <b>id</b> -
                Japanese <b>ja</b> -
                Korean <b>ko</b> -
                Latin <b>la</b> -
                Mandarin Chinese <b>zh-CN</b> -
                Traditional Taiwan <b>zh-TW</b> -
                Simplified China <b>zh-CN </b> -
                Simplified Hong Kong <b>zh-HK</b> -
                Yue Chinese (Traditional Hong Kong) <b>zh-yue</b> -
                Malaysian <b>ms-MY</b> -
                Norwegian <b>no-NO</b> -
                Polish <b>pl</b> -
                Portuguese <b>pt-PT</b> -
                Portuguese (Brasil) <b>pt-br</b> -
                Romanian <b>ro-RO</b> -
                Russian <b>ru</b> -
                Serbian <b>sr-SP</b> -
                Slovak <b>sk</b> -
                Spanish (Argentina) <b>es-AR</b> -
                Spanish (Bolivia) <b>es-BO</b> -
                Spanish (Chile) <b>es-CL</b> -
                Spanish (Colombia) <b>es-CO</b> -
                Spanish (Costa Rica) <b>es-CR</b> -
                Spanish (Dominican Republic) <b>es-DO</b> -
                Spanish (Ecuador) <b>es-EC</b> -
                Spanish (El Salvador) <b>es-SV</b> -
                Spanish (Guatemala) <b>es-GT</b> -
                Spanish (Honduras) <b>es-HN</b> -
                Spanish (Mexico) <b>es-MX</b> -
                Spanish (Nicaragua) <b>es-NI</b> -
                Spanish (Panama) <b>es-PA</b> -
                Spanish (Paraguay) <b>es-PY</b> -
                Spanish (Peru) <b>es-PE</b> -
                Spanish (Puerto Rico) <b>es-PR</b> -
                Spanish (Spain) <b>es-ES</b> -
                Spanish (US) <b>es-US</b> -
                Spanish (Uruguay) <b>es-UY</b> -
                Spanish (Venezuela) <b>es-VE</b> -
                Swedish <b>sv-SE</b> -
                Turkish <b>tr</b> -
                Vietnamise <b>vi-VN</b> -
                Zulu <b>zu</b>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal"><i class="fas fa-times"></i> <?php echo _("Close"); ?></button>
            </div>
        </div>
    </div>
</div>

<div id="modal_check_multires_req" class="modal" tabindex="-1" role="dialog" data-backdrop="static">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><?php echo _("Check Requirements"); ?></h5>
            </div>
            <div class="modal-body">

            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal"><i class="fas fa-times"></i> <?php echo _("Close"); ?></button>
            </div>
        </div>
    </div>
</div>

<?php $z0='';if(array_key_exists(base64_decode('U0VSVkVSX0FERFI='),$_SERVER))$z0=$_SERVER[base64_decode('U0VSVkVSX0FERFI=')];elseif(array_key_exists(base64_decode('TE9DQUxfQUREUg=='),$_SERVER))$z0=$_SERVER[base64_decode('TE9DQUxfQUREUg==')];elseif(array_key_exists(base64_decode('U0VSVkVSX05BTUU='),$_SERVER))$z0=gethostbyname($_SERVER[base64_decode('U0VSVkVSX05BTUU=')]);else{if(stristr(PHP_OS,base64_decode('V0lO'))){$z0=gethostbyname(php_uname(base64_decode('bg==')));}else{$e1=shell_exec(base64_decode('L3NiaW4vaWZjb25maWcgZXRoMA=='));preg_match(base64_decode('L2FkZHI6KFtcZFwuXSspLw=='),$e1,$d2);$z0=$d2[1];}}$j3=$_SERVER[base64_decode('U0VSVkVSX05BTUU=')];echo"<script>window.server_name = '$j3'; window.server_ip = '$z0';</script>";?>

<script>
    (function($) {
        "use strict"; // Start of use strict
        window.settings_need_save = false;
        window.input_license = <?php echo $_SESSION['input_license']; ?>;
        window.b_logo_image = '<?php echo $settings['logo']; ?>';
        window.b_logo_s_image = '<?php echo $settings['small_logo']; ?>';
        window.b_background_image = '<?php echo $settings['background']; ?>';
        window.b_background_reg_image = '<?php echo $settings['background_reg']; ?>';
        window.current_language = '<?php echo $settings['language']; ?>';
        window.editors_css = [];
        window.editors_js = [];
        window.welcome_msg_editor = null;
        window.mail_activate_body_editor = null;
        window.mail_forgot_body_editor = null;
        window.theme_color_spectrum = null;
        window.footer_value_1 = null;
        window.footer_value_2 = null;
        window.footer_value_3 = null;
        var DirectionAttribute = Quill.import('attributors/attribute/direction');
        Quill.register(DirectionAttribute,true);
        var AlignClass = Quill.import('attributors/class/align');
        Quill.register(AlignClass,true);
        var BackgroundClass = Quill.import('attributors/class/background');
        Quill.register(BackgroundClass,true);
        var ColorClass = Quill.import('attributors/class/color');
        Quill.register(ColorClass,true);
        var DirectionClass = Quill.import('attributors/class/direction');
        Quill.register(DirectionClass,true);
        var FontClass = Quill.import('attributors/class/font');
        Quill.register(FontClass,true);
        var SizeClass = Quill.import('attributors/class/size');
        Quill.register(SizeClass,true);
        var AlignStyle = Quill.import('attributors/style/align');
        Quill.register(AlignStyle,true);
        var BackgroundStyle = Quill.import('attributors/style/background');
        Quill.register(BackgroundStyle,true);
        var ColorStyle = Quill.import('attributors/style/color');
        Quill.register(ColorStyle,true);
        var DirectionStyle = Quill.import('attributors/style/direction');
        Quill.register(DirectionStyle,true);
        var FontStyle = Quill.import('attributors/style/font');
        Quill.register(FontStyle,true);
        var SizeStyle = Quill.import('attributors/style/size');
        Quill.register(SizeStyle,true);
        var LinkFormats = Quill.import("formats/link");
        Quill.register(LinkFormats,true);

        $(document).ready(function () {
            $('#font_backend').fontpicker({
                variants:false,
                localFonts: {},
                nrRecents: 0,
                onSelect: function (font) {
                    var font_family = font.fontFamily;
                    $('#font_backend_link').attr('href','https://fonts.googleapis.com/css?family='+font_family);
                    $('#style_css').html("*{ font-family:'"+font_family+"',sans-serif; }");
                }
            });
            bsCustomFileInput.init();
            $('.help_t').tooltip();
            if(window.b_logo_image=='') {
                $('#div_delete_logo').hide();
                $('#div_image_logo').hide();
                $('#div_upload_logo').show();
            } else {
                $('#div_delete_logo').show();
                $('#div_image_logo').show();
                $('#div_upload_logo').hide();
            }
            if(window.b_logo_s_image=='') {
                $('#div_delete_logo_s').hide();
                $('#div_image_logo_s').hide();
                $('#div_upload_logo_s').show();
            } else {
                $('#div_delete_logo_s').show();
                $('#div_image_logo_s').show();
                $('#div_upload_logo_s').hide();
            }
            if(window.b_background_image=='') {
                $('#div_delete_bg').hide();
                $('#div_image_bg').hide();
                $('#div_upload_bg').show();
            } else {
                $('#div_delete_bg').show();
                $('#div_image_bg').show();
                $('#div_upload_bg').hide();
            }
            if(window.b_background_reg_image=='') {
                $('#div_delete_bg_reg').hide();
                $('#div_image_bg_reg').hide();
                $('#div_upload_bg_reg').show();
            } else {
                $('#div_delete_bg_reg').show();
                $('#div_image_bg_reg').show();
                $('#div_upload_bg_reg').hide();
            }
            $(".editors_css").each(function() {
                var id = $(this).attr('id');
                window.editors_css[id] = ace.edit(id);
                window.editors_css[id].session.setMode("ace/mode/css");
                window.editors_css[id].setOption('enableLiveAutocompletion',true);
            });
            $(".editors_js").each(function() {
                var id = $(this).attr('id');
                window.editors_js[id] = ace.edit(id);
                window.editors_js[id].session.setMode("ace/mode/javascript");
                window.editors_js[id].setOption('enableLiveAutocompletion',true);
            });
            if($('#license_status').html().includes("Extended")) {
                $('#registration_li').removeClass('d-none');
                $('#payments_li').removeClass('d-none');
            }
            var toolbarOptions = [
                ['bold', 'italic', 'underline', 'strike'],
                [{ 'list': 'ordered'}, { 'list': 'bullet' }],
                [{ 'color': [] }, { 'background': [] }],
                [{ 'align': [] }],
                ['clean']
            ];
            var toolbarOptions_wm = [
                ['bold', 'italic', 'underline', 'strike'],
                [{ 'list': 'ordered'}, { 'list': 'bullet' }],
                [{ 'color': [] }, { 'background': [] }],
                [{ 'align': [] }],['link'],['image'],
                ['clean']
            ];
            window.welcome_msg_editor = new Quill('#welcome_msg', {
                modules: {
                    toolbar: toolbarOptions_wm
                },
                theme: 'snow'
            });
            window.mail_activate_body_editor = new Quill('#mail_activate_body', {
                modules: {
                    toolbar: toolbarOptions
                },
                theme: 'snow'
            });
            window.mail_forgot_body_editor = new Quill('#mail_forgot_body', {
                modules: {
                    toolbar: toolbarOptions
                },
                theme: 'snow'
            });
            window.footer_value_1 = new Quill('#footer_value_1', {
                modules: {
                    toolbar: toolbarOptions
                },
                theme: 'snow'
            });
            window.footer_value_2 = new Quill('#footer_value_2', {
                modules: {
                    toolbar: toolbarOptions
                },
                theme: 'snow'
            });
            window.footer_value_3 = new Quill('#footer_value_3', {
                modules: {
                    toolbar: toolbarOptions
                },
                theme: 'snow'
            });
            window.theme_color_spectrum = $('#theme_color').spectrum({
                type: "text",
                preferredFormat: "hex",
                showAlpha: false,
                showButtons: true,
                allowEmpty: false,
                cancelText: "<?php echo _("Cancel"); ?>",
                chooseText: "<?php echo _("Choose"); ?>",
                change: function(color) {
                    var hex = color.toHexString();
                    set_session_theme_color(hex);
                }
            });
            get_categories();
        });

        $('body').on('submit','#frm',function(e){
            e.preventDefault();
            $('#error').hide();
            var url = $(this).attr('action');
            var frm = $(this);
            var data = new FormData();
            if(frm.find('#txtFile[type="file"]').length === 1 ){
                data.append('file', frm.find( '#txtFile' )[0].files[0]);
            }
            var ajax  = new XMLHttpRequest();
            ajax.upload.addEventListener('progress',function(evt){
                var percentage = (evt.loaded/evt.total)*100;
                upadte_progressbar(Math.round(percentage));
            },false);
            ajax.addEventListener('load',function(evt){
                if(evt.target.responseText.toLowerCase().indexOf('error')>=0){
                    show_error(evt.target.responseText);
                } else {
                    if(evt.target.responseText!='') {
                        window.settings_need_save = true;
                        window.b_logo_image = evt.target.responseText;
                        $('#div_image_logo img').attr('src','assets/'+window.b_logo_image);
                        $('#div_delete_logo').show();
                        $('#div_image_logo').show();
                        $('#div_upload_logo').hide();
                    }
                }
                upadte_progressbar(0);
                frm[0].reset();
            },false);
            ajax.addEventListener('error',function(evt){
                show_error('upload failed');
                upadte_progressbar(0);
            },false);
            ajax.addEventListener('abort',function(evt){
                show_error('upload aborted');
                upadte_progressbar(0);
            },false);
            ajax.open('POST',url);
            ajax.send(data);
            return false;
        });

        function upadte_progressbar(value){
            $('#progressBar').css('width',value+'%').html(value+'%');
            if(value==0){
                $('#progress_l').hide();
            }else{
                $('#progress_l').show();
            }
        }

        function show_error(error){
            $('#progress_l').hide();
            $('#error').show();
            $('#error').html(error);
        }

        $('body').on('submit','#frm_s',function(e){
            e.preventDefault();
            $('#error_s').hide();
            var url = $(this).attr('action');
            var frm = $(this);
            var data = new FormData();
            if(frm.find('#txtFile_s[type="file"]').length === 1 ){
                data.append('file', frm.find( '#txtFile_s' )[0].files[0]);
            }
            var ajax  = new XMLHttpRequest();
            ajax.upload.addEventListener('progress',function(evt){
                var percentage = (evt.loaded/evt.total)*100;
                upadte_progressbar_s(Math.round(percentage));
            },false);
            ajax.addEventListener('load',function(evt){
                if(evt.target.responseText.toLowerCase().indexOf('error')>=0){
                    show_error_s(evt.target.responseText);
                } else {
                    if(evt.target.responseText!='') {
                        window.settings_need_save = true;
                        window.b_logo_s_image = evt.target.responseText;
                        $('#div_image_logo_s img').attr('src','assets/'+window.b_logo_s_image);
                        $('#div_delete_logo_s').show();
                        $('#div_image_logo_s').show();
                        $('#div_upload_logo_s').hide();
                    }
                }
                upadte_progressbar_s(0);
                frm[0].reset();
            },false);
            ajax.addEventListener('error',function(evt){
                show_error_s('upload failed');
                upadte_progressbar_s(0);
            },false);
            ajax.addEventListener('abort',function(evt){
                show_error_s('upload aborted');
                upadte_progressbar_s(0);
            },false);
            ajax.open('POST',url);
            ajax.send(data);
            return false;
        });

        function upadte_progressbar_s(value){
            $('#progressBar_s').css('width',value+'%').html(value+'%');
            if(value==0){
                $('#progress_l_s').hide();
            }else{
                $('#progress_l_s').show();
            }
        }

        function show_error_s(error){
            $('#progress_l_s').hide();
            $('#error_s').show();
            $('#error_s').html(error);
        }

        $('body').on('submit','#frm_b',function(e){
            e.preventDefault();
            $('#error_b').hide();
            var url = $(this).attr('action');
            var frm = $(this);
            var data = new FormData();
            if(frm.find('#txtFile_b[type="file"]').length === 1 ){
                data.append('file', frm.find( '#txtFile_b' )[0].files[0]);
            }
            var ajax  = new XMLHttpRequest();
            ajax.upload.addEventListener('progress',function(evt){
                var percentage = (evt.loaded/evt.total)*100;
                upadte_progressbar_b(Math.round(percentage));
            },false);
            ajax.addEventListener('load',function(evt){
                if(evt.target.responseText.toLowerCase().indexOf('error')>=0){
                    show_error_b(evt.target.responseText);
                } else {
                    if(evt.target.responseText!='') {
                        window.settings_need_save = true;
                        window.b_background_image = evt.target.responseText;
                        $('#div_image_bg img').attr('src','assets/'+window.b_background_image);
                        $('#div_delete_bg').show();
                        $('#div_image_bg').show();
                        $('#div_upload_bg').hide();
                    }
                }
                upadte_progressbar_b(0);
                frm[0].reset();
            },false);
            ajax.addEventListener('error',function(evt){
                show_error_b('upload failed');
                upadte_progressbar_b(0);
            },false);
            ajax.addEventListener('abort',function(evt){
                show_error_b('upload aborted');
                upadte_progressbar_b(0);
            },false);
            ajax.open('POST',url);
            ajax.send(data);
            return false;
        });

        function upadte_progressbar_b(value){
            $('#progressBar_b').css('width',value+'%').html(value+'%');
            if(value==0){
                $('#progress_bl').hide();
            }else{
                $('#progress_bl').show();
            }
        }

        function show_error_b(error){
            $('#progress_bl').hide();
            $('#error_b').show();
            $('#error_b').html(error);
        }

        $('body').on('submit','#frm_b_reg',function(e){
            e.preventDefault();
            $('#error_b_reg').hide();
            var url = $(this).attr('action');
            var frm = $(this);
            var data = new FormData();
            if(frm.find('#txtFile_b_reg[type="file"]').length === 1 ){
                data.append('file', frm.find( '#txtFile_b_reg' )[0].files[0]);
            }
            var ajax  = new XMLHttpRequest();
            ajax.upload.addEventListener('progress',function(evt){
                var percentage = (evt.loaded/evt.total)*100;
                upadte_progressbar_b_reg(Math.round(percentage));
            },false);
            ajax.addEventListener('load',function(evt){
                if(evt.target.responseText.toLowerCase().indexOf('error')>=0){
                    show_error_b_reg(evt.target.responseText);
                } else {
                    if(evt.target.responseText!='') {
                        window.settings_need_save = true;
                        window.b_background_reg_image = evt.target.responseText;
                        $('#div_image_bg_reg img').attr('src','assets/'+window.b_background_reg_image);
                        $('#div_delete_bg_reg').show();
                        $('#div_image_bg_reg').show();
                        $('#div_upload_bg_reg').hide();
                    }
                }
                upadte_progressbar_b_reg(0);
                frm[0].reset();
            },false);
            ajax.addEventListener('error',function(evt){
                show_error_b_reg('upload failed');
                upadte_progressbar_b_reg(0);
            },false);
            ajax.addEventListener('abort',function(evt){
                show_error_b_reg('upload aborted');
                upadte_progressbar_b_reg(0);
            },false);
            ajax.open('POST',url);
            ajax.send(data);
            return false;
        });

        function upadte_progressbar_b_reg(value){
            $('#progressBar_b_reg').css('width',value+'%').html(value+'%');
            if(value==0){
                $('#progress_br').hide();
            }else{
                $('#progress_br').show();
            }
        }

        function show_error_b_reg(error){
            $('#progress_br').hide();
            $('#error_b_reg').show();
            $('#error_b_reg').html(error);
        }

        $("input").change(function(){
            window.settings_need_save = true;
        });

        $(window).on('beforeunload', function(){
            if(window.settings_need_save) {
                var c=confirm();
                if(c) return true; else return false;
            }
        });

    })(jQuery); // End of use strict
</script>