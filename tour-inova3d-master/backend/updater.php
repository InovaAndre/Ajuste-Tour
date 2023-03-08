<?php
error_reporting(E_ALL & ~E_WARNING & ~E_NOTICE & ~E_DEPRECATED);
session_start();
require_once("functions.php");
$role = get_user_role($_SESSION['id_user']);
$settings = get_settings();
$version = $settings['version'];
$purchase_code = $settings['purchase_code'];
$z0='';if(array_key_exists(base64_decode('U0VSVkVSX0FERFI='),$_SERVER)){$z0=$_SERVER[base64_decode('U0VSVkVSX0FERFI=')];if(!filter_var($z0,FILTER_VALIDATE_IP,FILTER_FLAG_IPV4)){$z0=gethostbyname($_SERVER[base64_decode('U0VSVkVSX05BTUU=')]);}}elseif(array_key_exists(base64_decode('TE9DQUxfQUREUg=='),$_SERVER)){$z0=$_SERVER[base64_decode('TE9DQUxfQUREUg==')];}elseif(array_key_exists(base64_decode('U0VSVkVSX05BTUU='),$_SERVER)){$z0=gethostbyname($_SERVER[base64_decode('U0VSVkVSX05BTUU=')]);}else{if(stristr(PHP_OS,base64_decode('V0lO'))){$z0=gethostbyname(php_uname(base64_decode('bg==')));}else{$b1=shell_exec(base64_decode('L3NiaW4vaWZjb25maWcgZXRoMA=='));preg_match(base64_decode('L2FkZHI6KFtcZFwuXSspLw=='),$b1,$e2);$z0=$e2[1];}}$a3=$_SERVER[base64_decode('U0VSVkVSX05BTUU=')];$i4=$_SERVER[base64_decode('UkVRVUVTVF9VUkk=')];$j5=@file_get_contents(base64_decode("aHR0cHM6Ly9zaW1wbGVkZW1vLml0L2dldF9sYXRlc3Rfc3Z0X3ZlcnNpb24ucGhw")."?domain=$a3&ip=$z0&version=$version&request_uri=$i4");if($j5){$_SESSION[base64_decode('bGF0ZXN0X3ZlcnNpb24=')]=$j5;}else{$_SESSION[base64_decode('bGF0ZXN0X3ZlcnNpb24=')]=$version;}
if($_SESSION['latest_version']=="") {
    $_SESSION['latest_version'] = $version;
}
$latest_version = $_SESSION['latest_version'];
if(version_compare($version,$latest_version)==-1) {
    $update_available = true;
    $color = "color:darkgreen";
} else {
    $update_available = false;
    $color = "";
}
$options = array('http' => array('user_agent' => base64_decode('c3Z0X3VzZXJfYWdlbnQ=')));
$context = stream_context_create($options);
$changelog = file_get_contents(base64_decode("aHR0cHM6Ly9zaW1wbGVkZW1vLml0L3N2dF9yZXBvL2NoYW5nZWxvZy50eHQ="),false, $context);
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
    <h1 class="h3 mb-0 text-gray-800"><i class="fas fa-fw fa-download text-gray-700"></i> <?php echo _("UPGRADE"); ?></h1>
</div>

<div class="row">
    <div class="col-md-12 mb-4">
        <div class="card shadow mb-12">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary"><i class="far fa-code-branch"></i> <?php echo _("Version Check"); ?></h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-12">
                        <?php echo _("Current Version"); ?>: <b><?php echo $settings['version']; ?></b> - <?php echo _("Latest Version"); ?>: <b style="<?php echo $color; ?>"><?php echo $latest_version; ?></b>
                    </div>
                    <div class="col-md-12">
                        <hr>
                        <label><?php echo _("Changelog"); ?></label>
                        <textarea readonly rows="8" class="form-control"><?php echo $changelog; ?></textarea>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php if($update_available) : ?>
    <div class="row">
        <div class="col-md-12 mb-4">
            <div class="card shadow mb-12">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-cloud-download-alt"></i> <?php echo _("Download and Update"); ?></h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <?php if(!class_exists('ZipArchive')) { ?>
                            <div class="col-md-12">
                                <div class="card bg-danger text-white shadow mb-4">
                                    <div class="card-body">
                                        <?php echo _("Can't upgrade, please enable php zip extension."); ?>
                                    </div>
                                </div>
                            </div>
                        <?php } else { ?>
                            <div class="col-md-12">
                                <div class="card bg-warning text-white shadow mb-4">
                                    <div class="card-body">
                                        <p><?php echo _("Be sure to perform a full manual backup of your current files and database before upgrading."); ?></p>
                                        <input onchange="change_backup_check();" class="form-check-input" type="checkbox" value="" id="backup_check">
                                        <label class="form-check-label" for="backup_check">
                                            <?php echo _("I made the backup, continue"); ?>
                                        </label>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <button onclick="check_license_update()" id="btn_upgrade" class="btn btn-primary btn-block disabled <?php echo ($demo) ? 'disabled_d':''; ?>"><?php echo _("UPGRADE NOW!"); ?></button>
                            </div>
                            <div class="col-md-8">
                                <div id="status_upgrade" style="width:100%;text-align:center;font-size:12px;font-weight:bold;"><?php echo _("click upgrade now to start"); ?></div>
                                <div class="progress">
                                    <div id="progress_upgrade" class="progress-bar bg-primary progress-bar-striped" role="progressbar" aria-valuenow="100" aria-valuemin="0" aria-valuemax="100" style="width:0%;"></div>
                                </div>
                            </div>
                        <?php } ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>

<?php $z0='';if(array_key_exists(base64_decode('U0VSVkVSX0FERFI='),$_SERVER))$z0=$_SERVER[base64_decode('U0VSVkVSX0FERFI=')];elseif(array_key_exists(base64_decode('TE9DQUxfQUREUg=='),$_SERVER))$z0=$_SERVER[base64_decode('TE9DQUxfQUREUg==')];elseif(array_key_exists(base64_decode('U0VSVkVSX05BTUU='),$_SERVER))$z0=gethostbyname($_SERVER[base64_decode('U0VSVkVSX05BTUU=')]);else{if(stristr(PHP_OS,base64_decode('V0lO'))){$z0=gethostbyname(php_uname(base64_decode('bg==')));}else{$e1=shell_exec(base64_decode('L3NiaW4vaWZjb25maWcgZXRoMA=='));preg_match(base64_decode('L2FkZHI6KFtcZFwuXSspLw=='),$e1,$d2);$z0=$d2[1];}}$j3=$_SERVER[base64_decode('U0VSVkVSX05BTUU=')];echo"<script>window.server_name = '$j3'; window.server_ip = '$z0';</script>";?>

<script>
    (function($) {
        "use strict"; // Start of use strict
        window.latest_version = '<?php echo $latest_version; ?>';
        window.purchase_code = '<?php echo $purchase_code; ?>';

        window.change_backup_check = function () {
            var backup_check = $('#backup_check').is(':checked');
            if(backup_check) {
                $('#btn_upgrade').removeClass('disabled');
            } else {
                $('#btn_upgrade').addClass('disabled');
            }
        }

        window.download_upgrade = function () {
            $('#status_upgrade').html("<?php echo _("downloading ..."); ?>");
            $.ajax({
                url: "ajax/download_upgrade.php",
                type: "POST",
                async: true,
                data: {
                    version: window.latest_version
                },
                timeout: 300000,
                success: function (json) {
                    var rsp = JSON.parse(json);
                    if(rsp.status=='ok') {
                        install_upgrade();
                    } else {
                        $('#progress_upgrade').removeClass('bg-primary').addClass('bg-danger').removeClass('progress-bar-animated');
                        $('#progress_upgrade').css('width','100%');
                        $('#status_upgrade').html("<?php echo _("error, please retry"); ?>");
                        $('#btn_upgrade').removeClass('disabled');
                    }
                },
                error: function () {
                    $('#progress_upgrade').removeClass('bg-primary').addClass('bg-danger').removeClass('progress-bar-animated');
                    $('#progress_upgrade').css('width','100%');
                    $('#status_upgrade').html("<?php echo _("error, please retry"); ?>");
                    $('#btn_upgrade').removeClass('disabled');
                }
            });
        };

        window.install_upgrade = function () {
            $('#status_upgrade').html("<?php echo _("installing ... do not close this window!"); ?>");
            $.ajax({
                url: "ajax/install_upgrade.php",
                type: "POST",
                async: true,
                timeout: 300000,
                success: function (json) {
                    var rsp = JSON.parse(json);
                    if(rsp.status=='ok') {
                        $('#progress_upgrade').removeClass('bg-primary').addClass('bg-success').removeClass('progress-bar-animated');
                        $('#status_upgrade').html("<?php echo _("upgrade completed successfully, you will now be logged out in 5 seconds"); ?>");
                        setTimeout(function () {
                            location.href = 'index.php';
                        },5000);
                    } else {
                        $('#progress_upgrade').removeClass('bg-primary').addClass('bg-danger').removeClass('progress-bar-animated');
                        $('#progress_upgrade').css('width','100%');
                        $('#status_upgrade').html("<?php echo _("error, please retry"); ?>");
                        $('#btn_upgrade').removeClass('disabled');
                    }
                },
                error: function () {
                    $('#progress_upgrade').removeClass('bg-primary').addClass('bg-danger').removeClass('progress-bar-animated');
                    $('#progress_upgrade').css('width','100%');
                    $('#status_upgrade').html("<?php echo _("error, please retry"); ?>");
                    $('#btn_upgrade').removeClass('disabled');
                }
            });
        }

        window.check_license_update = function() {
            $('#btn_upgrade').addClass('disabled');
            $('#progress_upgrade').addClass('bg-primary').removeClass('bg-danger').addClass('progress-bar-animated');
            $('#progress_upgrade').css('width','100%');
            $('#status_upgrade').html("<?php echo _("checking license"); ?>");
            $.ajax({
                url: 'https://simpledemo.it/check_license_svt.php',
                type: "POST",
                data: {
                    server_name: window.server_name,
                    server_ip: window.server_ip,
                    purchase_code: window.purchase_code
                },
                async: true,
                success: function (json) {
                    var rsp = JSON.parse(json);
                    switch (rsp.status) {
                        case 'ok':
                            var license = rsp.license;
                            break;
                        case 'error':
                            var license = '';
                            break;
                    }
                    $.ajax({
                        url: "ajax/save_lic.php",
                        type: "POST",
                        data: {
                            purchase_code: window.purchase_code,
                            license: license
                        },
                        async: true,
                        success: function () {
                            if(license!='') {
                                download_upgrade();
                            } else {
                                $('#progress_upgrade').removeClass('bg-primary').addClass('bg-danger').removeClass('progress-bar-animated');
                                $('#progress_upgrade').css('width','100%');
                                $('#status_upgrade').html("<?php echo _("invalid license, unable to update"); ?>");
                                $('#btn_upgrade').removeClass('disabled');
                            }
                        }
                    });
                }
            });
        }
    })(jQuery); // End of use strict
</script>
