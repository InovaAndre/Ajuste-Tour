<?php
error_reporting(E_ALL & ~E_WARNING & ~E_NOTICE & ~E_DEPRECATED);
session_start();
if(!file_exists("../config/config.inc.php")) {
    header("Location: ../install/start.php");
}
require_once("functions.php");
$session_id = session_id();
$_SESSION['svt_si']=$session_id;
$settings = get_settings();
if(isset($_GET['lang'])) {
    if(check_language_enabled($_GET['lang'],$settings['languages_enabled'])) {
        $lang = $_GET['lang'];
        $_SESSION['lang']=$lang;
        header("Location: login.php");
        exit;
    }
}
set_language($settings['language'],$settings['language_domain']);
$v = time();
$modal_register = 0;
if(isset($_SESSION['modal_register'])) {
    $modal_register = $_SESSION['modal_register'];
    unset($_SESSION['modal_register']);
}
$verification_code = "";
$email = "";
if(isset($_GET['forgot'])) {
    if(isset($_GET['verification_code'])) {
        $verification_code = $_GET['verification_code'];
    }
    if(isset($_GET['email'])) {
        $email = $_GET['email'];
    }
    $forgot = true;
} else {
    $forgot = false;
}
if(empty($_SESSION['lang'])) {
    $lang = $settings['language'];
} else {
    $lang = $_SESSION['lang'];
}
$_SESSION['theme_color']=$settings['theme_color'];
if(empty($settings['logo']) && !empty($settings['small_logo'])) {
    $settings['logo'] = $settings['small_logo'];
}
if(isset($_GET['token'])) {
    $id_user=encrypt_decrypt('decrypt',$_GET['token'],date('YmdHi'));
    if(!empty($id_user)) {
        $_SESSION['id_user']=$id_user;
        header("Location: index.php");
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta http-equiv="content-type" content="text/html;charset=UTF-8" />
    <meta charset="UTF-8">
    <meta name="description" content="">
    <meta name="author" content="">
    <title><?php echo $settings['name']; ?></title>
    <?php echo print_favicons_backend($settings['logo']); ?>
    <link href="../viewer/vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link rel='stylesheet' type="text/css" href="https://fonts.googleapis.com/css?family=<?php echo $settings['font_backend']; ?>">
    <link rel="stylesheet" href="css/sb-admin-2.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/custom.css?v=<?php echo $v; ?>" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="css/theme.php?v=<?php echo $v; ?>">
    <?php if(file_exists(__DIR__.DIRECTORY_SEPARATOR.'css'.DIRECTORY_SEPARATOR.'custom_b.css')) : ?>
        <link rel="stylesheet" type="text/css" href="css/custom_b.css?v=<?php echo $v; ?>">
    <?php endif; ?>
</head>

<body class="bg-gradient-primary">
<style>
    *{ font-family: '<?php echo $settings['font_backend']; ?>', sans-serif; }
</style>
<div class="container">
    <div class="row">
        <?php if(!empty($settings['logo'])) : ?>
            <div class="col-md-12 text-white mt-3 text-center">
                <img style="max-height:100px;max-width:200px;width:auto;height:auto" src="assets/<?php echo $settings['logo']; ?>" />
            </div>
        <?php endif; ?>
        <div class="col-md-12 text-white mt-3 text-center title_name_login">
            <h3 class="mb-0"><?php echo strtoupper($settings['name']); ?></h3>
        </div>
    </div>
    <div class="row justify-content-center mt-2">
        <div class="col-xl-10 col-lg-12 col-md-9">
            <div class="card o-hidden border-0 shadow-lg my-2">
                <div class="card-body p-0">
                    <div class="row" style="min-height: 530px;">
                        <div style="<?php echo ($settings['background']!='') ? 'background-image: url(assets/'.$settings['background'].');'  : '' ; ?>" class="col-lg-6 d-none d-lg-block bg-login-image"></div>
                        <div class="col-lg-6 pl-0">
                            <div class="p-5">
                                <li class="nav-item dropdown no-arrow lang_switcher_login">
                                    <a class="nav-link dropdown-toggle" href="#" id="langDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" <?php echo ($settings['languages_count']==1) ? 'style="cursor:default;pointer-events:none;"' : ''; ?> >
                                        <img style="height: 14px;" src="img/flags_lang/<?php echo $lang; ?>.png" />
                                    </a>
                                    <div class="dropdown-menu dropdown-menu-left shadow" aria-labelledby="langDropdown">
                                        <?php if(check_language_enabled('ar_SA',$settings['languages_enabled'])) : ?> <span style="cursor: pointer;" onclick="switch_language('ar_SA');" class="<?php echo ($lang=='ar_SA') ? 'lang_active' : ''; ?> noselect dropdown-item align-middle"><img class="mb-1" src="img/flags_lang/ar_SA.png" /> <span class="ml-2"><?php echo _("Arabic"); ?></span></span> <?php endif; ?>
                                        <?php if(check_language_enabled('zh_CN',$settings['languages_enabled'])) : ?> <span style="cursor: pointer;" onclick="switch_language('zh_CN');" class="<?php echo ($lang=='zh_CN') ? 'lang_active' : ''; ?> noselect dropdown-item align-middle"><img class="mb-1" src="img/flags_lang/zh_CN.png" /> <span class="ml-2"><?php echo _("Chinese Simplified"); ?></span></span> <?php endif; ?>
                                        <?php if(check_language_enabled('zh_HK',$settings['languages_enabled'])) : ?> <span style="cursor: pointer;" onclick="switch_language('zh_HK');" class="<?php echo ($lang=='zh_HK') ? 'lang_active' : ''; ?> noselect dropdown-item align-middle"><img class="mb-1" src="img/flags_lang/zh_HK.png" /> <span class="ml-2"><?php echo _("Chinese Traditional (Hong Kong)"); ?></span></span> <?php endif; ?>
                                        <?php if(check_language_enabled('zh_TW',$settings['languages_enabled'])) : ?> <span style="cursor: pointer;" onclick="switch_language('zh_TW');" class="<?php echo ($lang=='zh_TW') ? 'lang_active' : ''; ?> noselect dropdown-item align-middle"><img class="mb-1" src="img/flags_lang/zh_TW.png" /> <span class="ml-2"><?php echo _("Chinese Traditional (Taiwan)"); ?></span></span> <?php endif; ?>
                                        <?php if(check_language_enabled('cs_CZ',$settings['languages_enabled'])) : ?> <span style="cursor: pointer;" onclick="switch_language('cs_CZ');" class="<?php echo ($lang=='cs_CZ') ? 'lang_active' : ''; ?> noselect dropdown-item align-middle"><img class="mb-1" src="img/flags_lang/cs_CZ.png" /> <span class="ml-2"><?php echo _("Czech"); ?></span></span> <?php endif; ?>
                                        <?php if(check_language_enabled('nl_NL',$settings['languages_enabled'])) : ?> <span style="cursor: pointer;" onclick="switch_language('nl_NL');" class="<?php echo ($lang=='nl_NL') ? 'lang_active' : ''; ?> noselect dropdown-item align-middle"><img class="mb-1" src="img/flags_lang/nl_NL.png" /> <span class="ml-2"><?php echo _("Dutch"); ?></span></span> <?php endif; ?>
                                        <?php if(check_language_enabled('en_US',$settings['languages_enabled'])) : ?> <span style="cursor: pointer;" onclick="switch_language('en_US');" class="<?php echo ($lang=='en_US') ? 'lang_active' : ''; ?> noselect dropdown-item align-middle"><img class="mb-1" src="img/flags_lang/en_US.png" /> <span class="ml-2"><?php echo _("English"); ?></span></span> <?php endif; ?>
                                        <?php if(check_language_enabled('fil_PH',$settings['languages_enabled'])) : ?> <span style="cursor: pointer;" onclick="switch_language('fil_PH');" class="<?php echo ($lang=='fil_PH') ? 'lang_active' : ''; ?> noselect dropdown-item align-middle"><img class="mb-1" src="img/flags_lang/fil_PH.png" /> <span class="ml-2"><?php echo _("Filipino"); ?></span></span> <?php endif; ?>
                                        <?php if(check_language_enabled('fr_FR',$settings['languages_enabled'])) : ?> <span style="cursor: pointer;" onclick="switch_language('fr_FR');" class="<?php echo ($lang=='zh_CN') ? 'lang_active' : ''; ?> noselect dropdown-item align-middle"><img class="mb-1" src="img/flags_lang/fr_FR.png" /> <span class="ml-2"><?php echo _("French"); ?></span></span> <?php endif; ?>
                                        <?php if(check_language_enabled('de_DE',$settings['languages_enabled'])) : ?> <span style="cursor: pointer;" onclick="switch_language('de_DE');" class="<?php echo ($lang=='de_DE') ? 'lang_active' : ''; ?> noselect dropdown-item align-middle"><img class="mb-1" src="img/flags_lang/de_DE.png" /> <span class="ml-2"><?php echo _("German"); ?></span></span> <?php endif; ?>
                                        <?php if(check_language_enabled('hi_IN',$settings['languages_enabled'])) : ?> <span style="cursor: pointer;" onclick="switch_language('hi_IN');" class="<?php echo ($lang=='hi_IN') ? 'lang_active' : ''; ?> noselect dropdown-item align-middle"><img class="mb-1" src="img/flags_lang/hi_IN.png" /> <span class="ml-2"><?php echo _("Hindi"); ?></span></span> <?php endif; ?>
                                        <?php if(check_language_enabled('hu_HU',$settings['languages_enabled'])) : ?> <span style="cursor: pointer;" onclick="switch_language('hu_HU');" class="<?php echo ($lang=='hu_HU') ? 'lang_active' : ''; ?> noselect dropdown-item align-middle"><img class="mb-1" src="img/flags_lang/hu_HU.png" /> <span class="ml-2"><?php echo _("Hungarian"); ?></span></span> <?php endif; ?>
                                        <?php if(check_language_enabled('rw_RW',$settings['languages_enabled'])) : ?> <span style="cursor: pointer;" onclick="switch_language('rw_RW');" class="<?php echo ($lang=='rw_RW') ? 'lang_active' : ''; ?> noselect dropdown-item align-middle"><img class="mb-1" src="img/flags_lang/rw_RW.png" /> <span class="ml-2"><?php echo _("Kinyarwanda"); ?></span></span> <?php endif; ?>
                                        <?php if(check_language_enabled('ko_KR',$settings['languages_enabled'])) : ?> <span style="cursor: pointer;" onclick="switch_language('ko_KR');" class="<?php echo ($lang=='ko_KR') ? 'lang_active' : ''; ?> noselect dropdown-item align-middle"><img class="mb-1" src="img/flags_lang/ko_KR.png" /> <span class="ml-2"><?php echo _("Korean"); ?></span></span> <?php endif; ?>
                                        <?php if(check_language_enabled('it_IT',$settings['languages_enabled'])) : ?> <span style="cursor: pointer;" onclick="switch_language('it_IT');" class="<?php echo ($lang=='it_IT') ? 'lang_active' : ''; ?> noselect dropdown-item align-middle"><img class="mb-1" src="img/flags_lang/it_IT.png" /> <span class="ml-2"><?php echo _("Italian"); ?></span></span> <?php endif; ?>
                                        <?php if(check_language_enabled('ja_JP',$settings['languages_enabled'])) : ?> <span style="cursor: pointer;" onclick="switch_language('ja_JP');" class="<?php echo ($lang=='ja_JP') ? 'lang_active' : ''; ?> noselect dropdown-item align-middle"><img class="mb-1" src="img/flags_lang/ja_JP.png" /> <span class="ml-2"><?php echo _("Japanese"); ?></span></span> <?php endif; ?>
                                        <?php if(check_language_enabled('fa_IR',$settings['languages_enabled'])) : ?> <span style="cursor: pointer;" onclick="switch_language('fa_IR');" class="<?php echo ($lang=='fa_IR') ? 'lang_active' : ''; ?> noselect dropdown-item align-middle"><img class="mb-1" src="img/flags_lang/fa_IR.png" /> <span class="ml-2"><?php echo _("Persian"); ?></span></span> <?php endif; ?>
                                        <?php if(check_language_enabled('pl_PL',$settings['languages_enabled'])) : ?> <span style="cursor: pointer;" onclick="switch_language('pl_PL');" class="<?php echo ($lang=='pl_PL') ? 'lang_active' : ''; ?> noselect dropdown-item align-middle"><img class="mb-1" src="img/flags_lang/pl_PL.png" /> <span class="ml-2"><?php echo _("Polish"); ?></span></span> <?php endif; ?>
                                        <?php if(check_language_enabled('pt_BR',$settings['languages_enabled'])) : ?> <span style="cursor: pointer;" onclick="switch_language('pt_BR');" class="<?php echo ($lang=='pt_BR') ? 'lang_active' : ''; ?> noselect dropdown-item align-middle"><img class="mb-1" src="img/flags_lang/pt_BR.png" /> <span class="ml-2"><?php echo _("Portuguese Brazilian"); ?></span></span> <?php endif; ?>
                                        <?php if(check_language_enabled('pt_PT',$settings['languages_enabled'])) : ?> <span style="cursor: pointer;" onclick="switch_language('pt_PT');" class="<?php echo ($lang=='pt_PT') ? 'lang_active' : ''; ?> noselect dropdown-item align-middle"><img class="mb-1" src="img/flags_lang/pt_PT.png" /> <span class="ml-2"><?php echo _("Portuguese European"); ?></span></span> <?php endif; ?>
                                        <?php if(check_language_enabled('es_ES',$settings['languages_enabled'])) : ?> <span style="cursor: pointer;" onclick="switch_language('es_ES');" class="<?php echo ($lang=='es_ES') ? 'lang_active' : ''; ?> noselect dropdown-item align-middle"><img class="mb-1" src="img/flags_lang/es_ES.png" /> <span class="ml-2"><?php echo _("Spanish"); ?></span></span> <?php endif; ?>
                                        <?php if(check_language_enabled('ro_RO',$settings['languages_enabled'])) : ?> <span style="cursor: pointer;" onclick="switch_language('ro_RO');" class="<?php echo ($lang=='ro_RO') ? 'lang_active' : ''; ?> noselect dropdown-item align-middle"><img class="mb-1" src="img/flags_lang/ro_RO.png" /> <span class="ml-2"><?php echo _("Romanian"); ?></span></span> <?php endif; ?>
                                        <?php if(check_language_enabled('ru_RU',$settings['languages_enabled'])) : ?> <span style="cursor: pointer;" onclick="switch_language('ru_RU');" class="<?php echo ($lang=='ru_RU') ? 'lang_active' : ''; ?> noselect dropdown-item align-middle"><img class="mb-1" src="img/flags_lang/ru_RU.png" /> <span class="ml-2"><?php echo _("Russian"); ?></span></span> <?php endif; ?>
                                        <?php if(check_language_enabled('sv_SE',$settings['languages_enabled'])) : ?> <span style="cursor: pointer;" onclick="switch_language('sv_SE');" class="<?php echo ($lang=='sv_SE') ? 'lang_active' : ''; ?> noselect dropdown-item align-middle"><img class="mb-1" src="img/flags_lang/sv_SE.png" /> <span class="ml-2"><?php echo _("Swedish"); ?></span></span> <?php endif; ?>
                                        <?php if(check_language_enabled('tg_TJ',$settings['languages_enabled'])) : ?> <span style="cursor: pointer;" onclick="switch_language('tg_TJ');" class="<?php echo ($lang=='tg_TJ') ? 'lang_active' : ''; ?> noselect dropdown-item align-middle"><img class="mb-1" src="img/flags_lang/tg_TJ.png" /> <span class="ml-2"><?php echo _("Tajik"); ?></span></span> <?php endif; ?>
                                        <?php if(check_language_enabled('th_TH',$settings['languages_enabled'])) : ?> <span style="cursor: pointer;" onclick="switch_language('th_TH');" class="<?php echo ($lang=='th_TH') ? 'lang_active' : ''; ?> noselect dropdown-item align-middle"><img class="mb-1" src="img/flags_lang/th_TH.png" /> <span class="ml-2"><?php echo _("Thai"); ?></span></span> <?php endif; ?>
                                        <?php if(check_language_enabled('tr_TR',$settings['languages_enabled'])) : ?> <span style="cursor: pointer;" onclick="switch_language('tr_TR');" class="<?php echo ($lang=='tr_TR') ? 'lang_active' : ''; ?> noselect dropdown-item align-middle"><img class="mb-1" src="img/flags_lang/tr_TR.png" /> <span class="ml-2"><?php echo _("Turkish"); ?></span></span> <?php endif; ?>
                                        <?php if(check_language_enabled('vi_VN',$settings['languages_enabled'])) : ?> <span style="cursor: pointer;" onclick="switch_language('vi_VN');" class="<?php echo ($lang=='vi_VN') ? 'lang_active' : ''; ?> noselect dropdown-item align-middle"><img class="mb-1" src="img/flags_lang/vi_VN.png" /> <span class="ml-2"><?php echo _("Vietnamese"); ?></span></span> <?php endif; ?>
                                    </div>
                                </li>
                                <?php if($forgot) { ?>
                                    <div class="row <?php echo (!empty($verification_code)) ? 'disabled' : ''; ?>">
                                        <div class="col-md-12">
                                            <div class="form-group">
                                                <label for="email_f"><?php echo _("E-mail"); ?></label>
                                                <input type="email" class="form-control" id="email_f" value="<?php echo $email; ?>" />
                                            </div>
                                        </div>
                                        <div class="col-md-12">
                                            <button id="btn_forgot_code" onclick="send_verification_code();" class="btn btn-block btn-primary"><?php echo _("Send Verification Code"); ?></button>
                                        </div>
                                    </div>
                                    <div class="row" style="margin-top: 15px">
                                        <div class="col-md-12">
                                            <div class="form-group">
                                                <label for="forgot_code"><?php echo _("Verification code"); ?></label>
                                                <input type="text" class="form-control" id="forgot_code" value="<?php echo $verification_code; ?>" />
                                            </div>
                                        </div>
                                        <div class="col-md-12">
                                            <div class="form-group">
                                                <label for="password_f"><?php echo _("New password"); ?></label>
                                                <div class="position-relative">
                                                    <input autocomplete="new-password" type="password" class="form-control" id="password_f" />
                                                    <i onclick="show_hide_password('password_f');" style="position:absolute;top:50%;right:15px;transform:translateY(-50%);cursor:pointer;" class="fa fa-eye-slash" aria-hidden="true"></i>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-12">
                                            <div class="form-group">
                                                <label for="repeat_password_f"><?php echo _("Repeat password"); ?></label>
                                                <div class="position-relative">
                                                    <input autocomplete="new-password" type="password" class="form-control" id="repeat_password_f" />
                                                    <i onclick="show_hide_password('repeat_password_f');" style="position:absolute;top:50%;right:15px;transform:translateY(-50%);cursor:pointer;" class="fa fa-eye-slash" aria-hidden="true"></i>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-12">
                                            <button id="btn_change_password" onclick="change_password_forgot()" class="btn btn-block btn-success"><?php echo _("Change password"); ?></button>
                                        </div>
                                    </div>
                                    <div class="row" style="margin-top: 15px">
                                        <div class="col-md-12">
                                            <div class="text-center">
                                                <a class="small" href="login.php"><?php echo _("Back to Login"); ?></a>
                                            </div>
                                        </div>
                                    </div>
                                <?php } else { ?>
                                <div class="text-center">
                                    <h1 class="h4 text-gray-900 mb-4"><?php echo _("Welcome Back!"); ?></h1>
                                </div>
                                <form class="user user_login">
                                    <div class="form-group">
                                        <input tabindex="1" autofocus type="text" class="form-control form-control-user" id="username_l" aria-describedby="emailHelp" placeholder="<?php echo _("Enter Username or E-mail"); ?>">
                                    </div>
                                    <div class="form-group position-relative">
                                        <input tabindex="2" type="password" class="form-control form-control-user" id="password_l" placeholder="<?php echo _("Password"); ?>">
                                        <i onclick="show_hide_password('password_l');" style="position:absolute;top:50%;right:15px;transform:translateY(-50%);cursor:pointer;" class="fa fa-eye-slash" aria-hidden="true"></i>
                                    </div>
                                    <a tabindex="3" href="#" id="btn_login" onclick="login();return false;" class="btn btn-primary btn-user btn-block">
                                        <?php echo _("Login"); ?>
                                    </a>
                                    <?php if($settings['social_google_enable'] || $settings['social_facebook_enable'] || $settings['social_twitter_enable']) { echo "<hr>"; } ?>
                                    <?php if($settings['social_google_enable']) : ?>
                                    <a href="social_auth.php?provider=Google&reg=0" class="btn btn-google btn-user btn-block">
                                        <i class="fab fa-google fa-fw"></i> <?php echo _("Login with Google"); ?>
                                    </a>
                                    <?php endif; ?>
                                    <?php if($settings['social_facebook_enable']) : ?>
                                    <a href="social_auth.php?provider=Facebook&reg=0" class="btn btn-facebook btn-user btn-block">
                                        <i class="fab fa-facebook-f fa-fw"></i> <?php echo _("Login with Facebook"); ?>
                                    </a>
                                    <?php endif; ?>
                                    <?php if($settings['social_twitter_enable']) : ?>
                                    <a href="social_auth.php?provider=Twitter&reg=0" class="btn btn-twitter btn-user btn-block">
                                        <i class="fab fa-twitter fa-fw"></i> <?php echo _("Login with Twitter"); ?>
                                    </a>
                                    <?php endif; ?>
                                    <hr>
                                    <?php if($settings['smtp_valid']) : ?>
                                    <div class="text-center">
                                        <a class="small" href="login.php?forgot=1"><?php echo _("Forgot Password?"); ?></a>
                                    </div>
                                    <?php endif; ?>
                                    <?php if($settings['enable_registration']) : ?>
                                        <div class="text-center">
                                            <a class="small" href="register.php"><?php echo _("Create an Account"); ?></a>
                                        </div>
                                    <?php endif; ?>
                                </form>
                                <?php } ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div id="modal_register" class="modal" tabindex="-1" role="dialog" data-backdrop="static">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><?php echo _("Create Account"); ?></h5>
                </div>
                <div class="modal-body">
                    <p><?php echo sprintf(_("An account linked to the email %s was not found, do you want to register a new one?"),$_SESSION['email_log']); ?></p>
                </div>
                <div class="modal-footer">
                    <button onclick="session_register()" type="button" class="btn btn-success"><i class="fas fa-user-plus"></i> <?php echo _("Yes, Register"); ?></button>
                    <button type="button" class="btn btn-secondary" data-dismiss="modal"><i class="fas fa-times"></i> <?php echo _("Close"); ?></button>
                </div>
            </div>
        </div>
    </div>

    <div id="modal_email" class="modal" tabindex="-1" role="dialog" data-backdrop="static">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><?php echo _("Action required"); ?></h5>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="email"><?php echo _("Associate a valid e-mail address to this account."); ?></label>
                                <input type="email" class="form-control" id="email" />
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button id="btn_assoc_email" onclick="" type="button" class="btn btn-success"><?php echo _("Continue"); ?></button>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="vendor/jquery/jquery.min.js"></script>
<script src="vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="vendor/jquery-easing/jquery.easing.min.js"></script>
<script src="js/sb-admin-2.js"></script>
<script>
    window.login_labels = {
        "check_msg":"<?php echo _("Check your inbox for the verification code."); ?>",
        "error_msg":"<?php echo _("Error, retry later."); ?>",
        "password_success":"<?php echo _("Password successfully changed!"); ?>",
    };
</script>
<script src="js/function.js?v=<?php echo time(); ?>"></script>

<script>
    window.wizard_step = -1;
    var modal_register = <?php echo $modal_register; ?>;
    (function($) {
        "use strict"; // Start of use strict
        if(modal_register==1) {
            $('#modal_register').modal("show");
        }
        $(document).keyup(function(event) {
            if(!$('#modal_forgot').hasClass('show')) {
                if (event.key == "Enter") {
                    event.preventDefault();
                    $("#btn_login").trigger('click');
                }
            }
        });
    })(jQuery); // End of use strict

    function show_hide_password(id) {
        if($('#'+id).attr("type") == "text"){
            $('#'+id).attr('type', 'password');
            $('#'+id).parent().find('i').addClass( "fa-eye-slash" );
            $('#'+id).parent().find('i').removeClass( "fa-eye" );
        }else if($('#'+id).attr("type") == "password"){
            $('#'+id).attr('type', 'text');
            $('#'+id).parent().find('i').removeClass( "fa-eye-slash" );
            $('#'+id).parent().find('i').addClass( "fa-eye" );
        }
    }
</script>

</body>
</html>
