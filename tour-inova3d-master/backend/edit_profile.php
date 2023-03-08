<?php
session_start();
require_once("functions.php");
$role = get_user_role($_SESSION['id_user']);
$id_user_edit = $_SESSION['id_user'];
$user_info = get_user_info($id_user_edit);
$settings = get_settings();
$hide_personal_info = '';
if((!$settings['first_name_enable']) && (!$settings['last_name_enable']) && (!$settings['company_enable']) && (!$settings['tax_id_enable']) && (!$settings['street_enable']) && (!$settings['city_enable']) && (!$settings['province_enable']) && (!$settings['postal_code_enable']) && (!$settings['country_enable']) && (!$settings['tel_enable'])) {
    $hide_personal_info = 'd-none';
}
$to_complete = check_profile_to_complete($id_user_edit);
?>

<?php if(!$to_complete) : ?>
<div class="d-sm-flex align-items-center justify-content-between mb-3">
    <h1 class="h3 mb-0 text-gray-800"><i class="fas fa-fw fa-user text-gray-700"></i> <?php echo _("EDIT PROFILE"); ?></h1>
    <a id="save_btn" href="#" onclick="save_profile(<?php echo $id_user_edit; ?>,false);return false;" class="btn btn-sm btn-success btn-icon-split mb-2 <?php echo ($demo) ? 'disabled':''; ?>">
    <span class="icon text-white-50">
      <i class="far fa-circle"></i>
    </span>
        <span class="text"><?php echo _("SAVE"); ?></span>
    </a>
</div>
<?php endif; ?>

<?php if($to_complete) : ?>
    <div class="card bg-warning text-white shadow mb-3">
        <div class="card-body">
            <?php echo _("Please complete your profile with the required fields (*) before continuing to use the application."); ?>
        </div>
    </div>
<?php endif; ?>

<div class="row <?php echo $hide_personal_info; ?>">
    <div class="col-md-12">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-user-circle"></i> <?php echo _("Personal Informations"); ?></h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3 <?php echo (!$settings['first_name_enable']) ? 'd-none' : ''; ?>">
                        <div class="form-group">
                            <label for="first_name"><?php echo _("First Name"); ?> <?php echo ($settings['first_name_mandatory']) ? '*' : ''; ?></label>
                            <input data-mandatory="<?php echo ($settings['first_name_enable'] && $settings['first_name_mandatory']) ? 'true' : 'false'; ?>" type="text" class="form-control" id="first_name" value="<?php echo $user_info['first_name']; ?>" />
                        </div>
                    </div>
                    <div class="col-md-3 <?php echo (!$settings['last_name_enable']) ? 'd-none' : ''; ?>">
                        <div class="form-group">
                            <label for="last_name"><?php echo _("Last Name"); ?> <?php echo ($settings['last_name_mandatory']) ? '*' : ''; ?></label>
                            <input data-mandatory="<?php echo ($settings['last_name_enable'] && $settings['last_name_mandatory']) ? 'true' : 'false'; ?>" type="text" class="form-control" id="last_name" value="<?php echo $user_info['last_name']; ?>" />
                        </div>
                    </div>
                    <div class="col-md-3 <?php echo (!$settings['company_enable']) ? 'd-none' : ''; ?>">
                        <div class="form-group">
                            <label for="company"><?php echo _("Company"); ?> <?php echo ($settings['company_mandatory']) ? '*' : ''; ?></label>
                            <input data-mandatory="<?php echo ($settings['company_enable'] && $settings['company_mandatory']) ? 'true' : 'false'; ?>" type="text" class="form-control" id="company" value="<?php echo $user_info['company']; ?>" />
                        </div>
                    </div>
                    <div class="col-md-3 <?php echo (!$settings['tax_id_enable']) ? 'd-none' : ''; ?>">
                        <div class="form-group">
                            <label for="tax_id"><?php echo _("Tax Id"); ?> <?php echo ($settings['tax_id_mandatory']) ? '*' : ''; ?></label>
                            <input data-mandatory="<?php echo ($settings['tax_id_enable'] && $settings['tax_id_mandatory']) ? 'true' : 'false'; ?>" type="text" class="form-control" id="tax_id" value="<?php echo $user_info['tax_id']; ?>" />
                        </div>
                    </div>
                    <div class="col-md-6 <?php echo (!$settings['street_enable']) ? 'd-none' : ''; ?>">
                        <div class="form-group">
                            <label for="street"><?php echo _("Address"); ?> <?php echo ($settings['street_mandatory']) ? '*' : ''; ?></label>
                            <input data-mandatory="<?php echo ($settings['street_enable'] && $settings['street_mandatory']) ? 'true' : 'false'; ?>" type="text" class="form-control" id="street" value="<?php echo $user_info['street']; ?>" />
                        </div>
                    </div>
                    <div class="col-md-3 <?php echo (!$settings['city_enable']) ? 'd-none' : ''; ?>">
                        <div class="form-group">
                            <label for="city"><?php echo _("City"); ?> <?php echo ($settings['city_mandatory']) ? '*' : ''; ?></label>
                            <input data-mandatory="<?php echo ($settings['city_enable'] && $settings['city_mandatory']) ? 'true' : 'false'; ?>" type="text" class="form-control" id="city" value="<?php echo $user_info['city']; ?>" />
                        </div>
                    </div>
                    <div class="col-md-3 <?php echo (!$settings['province_enable']) ? 'd-none' : ''; ?>">
                        <div class="form-group">
                            <label for="province"><?php echo _("State / Province / Region"); ?> <?php echo ($settings['province_mandatory']) ? '*' : ''; ?></label>
                            <input data-mandatory="<?php echo ($settings['province_enable'] && $settings['province_mandatory']) ? 'true' : 'false'; ?>" type="text" class="form-control" id="province" value="<?php echo $user_info['province']; ?>" />
                        </div>
                    </div>
                    <div class="col-md-3 <?php echo (!$settings['postal_code_enable']) ? 'd-none' : ''; ?>">
                        <div class="form-group">
                            <label for="postal_code"><?php echo _("Zip / Postal Code"); ?> <?php echo ($settings['postal_code_mandatory']) ? '*' : ''; ?></label>
                            <input data-mandatory="<?php echo ($settings['postal_code_enable'] && $settings['postal_code_mandatory']) ? 'true' : 'false'; ?>" type="text" class="form-control" id="postal_code" value="<?php echo $user_info['postal_code']; ?>" />
                        </div>
                    </div>
                    <div class="col-md-3 <?php echo (!$settings['country_enable']) ? 'd-none' : ''; ?>">
                        <div class="form-group">
                            <label for="country"><?php echo _("Country"); ?> <?php echo ($settings['country_mandatory']) ? '*' : ''; ?></label>
                            <select data-mandatory="<?php echo ($settings['country_enable'] && $settings['country_mandatory']) ? 'true' : 'false'; ?>" id="country" class="form-control selectpicker countrypicker" <?php echo (!empty($user_info['country'])) ? 'data-default="'.$user_info['country'].'"' : '' ; ?> data-flag="true" data-live-search="true" title="<?php echo _("Select country"); ?>"></select>
                            <script>
                                $('.countrypicker').countrypicker();
                            </script>
                        </div>
                    </div>
                    <div class="col-md-3 <?php echo (!$settings['tel_enable']) ? 'd-none' : ''; ?>">
                        <div class="form-group">
                            <label for="tel"><?php echo _("Telephone"); ?> <?php echo ($settings['tel_mandatory']) ? '*' : ''; ?></label>
                            <input data-mandatory="<?php echo ($settings['tel_enable'] && $settings['tel_mandatory']) ? 'true' : 'false'; ?>" type="text" class="form-control" id="tel" value="<?php echo $user_info['tel']; ?>" />
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php if($to_complete) : ?>
<div class="row">
    <div class="col-md-12">
        <button id="btn_save_continue_profile" onclick="save_profile(<?php echo $id_user_edit; ?>,true);" class="btn btn-block btn-success"><?php echo _("SAVE AND CONTINUE"); ?></button>
    </div>
</div>
<?php endif; ?>

<div class="row <?php echo ($to_complete) ? 'd-none' : ''; ?>">
    <div class="col-md-8">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-user-cog"></i> <?php echo _("Account"); ?></h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="username"><?php echo _("Username"); ?></label>
                            <input type="text" class="form-control" id="username" value="<?php echo $user_info['username']; ?>" />
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="email"><?php echo _("E-mail"); ?></label>
                            <input type="email" class="form-control" id="email" value="<?php echo $user_info['email']; ?>" />
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="language"><?php echo _("Language"); ?></label>
                            <select class="form-control" id="language">
                                <option <?php echo ($user_info['language']=='') ? 'selected':''; ?> id=""><?php echo _("Default Language"); ?></option>
                                <?php if (check_language_enabled('ar_SA',$settings['languages_enabled'])) : ?><option <?php echo ($user_info['language']=='ar_SA') ? 'selected':''; ?> id="ar_SA">Arabic (ar_SA)</option><?php endif; ?>
                                <?php if (check_language_enabled('zh_CN',$settings['languages_enabled'])) : ?><option <?php echo ($user_info['language']=='zh_CN') ? 'selected':''; ?> id="zh_CN">Chinese simplified (zh_CN)</option><?php endif; ?>
                                <?php if (check_language_enabled('zh_HK',$settings['languages_enabled'])) : ?><option <?php echo ($user_info['language']=='zh_HK') ? 'selected':''; ?> id="zh_HK">Chinese traditional (zh_HK)</option><?php endif; ?>
                                <?php if (check_language_enabled('zh_TW',$settings['languages_enabled'])) : ?><option <?php echo ($user_info['language']=='zh_TW') ? 'selected':''; ?> id="zh_TW">Chinese traditional (zh_TW)</option><?php endif; ?>
                                <?php if (check_language_enabled('cs_CZ',$settings['languages_enabled'])) : ?><option <?php echo ($user_info['language']=='cs_CZ') ? 'selected':''; ?> id="cs_CZ">Czech (cs_CZ)</option><?php endif; ?>
                                <?php if (check_language_enabled('nl_NL',$settings['languages_enabled'])) : ?><option <?php echo ($user_info['language']=='nl_NL') ? 'selected':''; ?> id="nl_NL">Dutch (nl_NL)</option><?php endif; ?>
                                <?php if (check_language_enabled('en_US',$settings['languages_enabled'])) : ?><option <?php echo ($user_info['language']=='en_US') ? 'selected':''; ?> id="en_US">English (en_US)</option><?php endif; ?>
                                <?php if (check_language_enabled('fil_PH',$settings['languages_enabled'])) : ?><option <?php echo ($user_info['language']=='fil_PH') ? 'selected':''; ?> id="fil_PH">Filipino (fil_PH)</option><?php endif; ?>
                                <?php if (check_language_enabled('fr_FR',$settings['languages_enabled'])) : ?><option <?php echo ($user_info['language']=='fr_FR') ? 'selected':''; ?> id="fr_FR">French (fr_FR)</option><?php endif; ?>
                                <?php if (check_language_enabled('de_DE',$settings['languages_enabled'])) : ?><option <?php echo ($user_info['language']=='de_DE') ? 'selected':''; ?> id="de_DE">German (de_DE)</option><?php endif; ?>
                                <?php if (check_language_enabled('hi_IN',$settings['languages_enabled'])) : ?><option <?php echo ($user_info['language']=='hi_IN') ? 'selected':''; ?> id="hi_IN">Hindi (hi_IN)</option><?php endif; ?>
                                <?php if (check_language_enabled('hu_HU',$settings['languages_enabled'])) : ?><option <?php echo ($user_info['language']=='hu_HU') ? 'selected':''; ?> id="hu_HU">Hungarian (hu_HU)</option><?php endif; ?>
                                <?php if (check_language_enabled('kw_KW',$settings['languages_enabled'])) : ?><option <?php echo ($user_info['language']=='kw_KW') ? 'selected':''; ?> id="kw_KW">Kinyarwanda (kw_KW)</option><?php endif; ?>
                                <?php if (check_language_enabled('ko_KR',$settings['languages_enabled'])) : ?><option <?php echo ($user_info['language']=='ko_KR') ? 'selected':''; ?> id="ko_KR">Korean (ko_KR)</option><?php endif; ?>
                                <?php if (check_language_enabled('it_IT',$settings['languages_enabled'])) : ?><option <?php echo ($user_info['language']=='it_IT') ? 'selected':''; ?> id="it_IT">Italian (it_IT)</option><?php endif; ?>
                                <?php if (check_language_enabled('ja_JP',$settings['languages_enabled'])) : ?><option <?php echo ($user_info['language']=='ja_JP') ? 'selected':''; ?> id="ja_JP">Japanese (ja_JP)</option><?php endif; ?>
                                <?php if (check_language_enabled('fa_IR',$settings['languages_enabled'])) : ?><option <?php echo ($user_info['language']=='fa_IR') ? 'selected':''; ?> id="fa_IR">Persian (fa_IR)</option><?php endif; ?>
                                <?php if (check_language_enabled('pl_PL',$settings['languages_enabled'])) : ?><option <?php echo ($user_info['language']=='pl_PL') ? 'selected':''; ?> id="pl_PL">Polish (pl_PL)</option><?php endif; ?>
                                <?php if (check_language_enabled('pt_BR',$settings['languages_enabled'])) : ?><option <?php echo ($user_info['language']=='pt_BR') ? 'selected':''; ?> id="pt_BR">Portuguese Brazilian (pt_BR)</option><?php endif; ?>
                                <?php if (check_language_enabled('pt_PT',$settings['languages_enabled'])) : ?><option <?php echo ($user_info['language']=='pt_PT') ? 'selected':''; ?> id="pt_PT">Portuguese European (pt_PT)</option><?php endif; ?>
                                <?php if (check_language_enabled('es_ES',$settings['languages_enabled'])) : ?><option <?php echo ($user_info['language']=='es_ES') ? 'selected':''; ?> id="es_ES">Spanish (es_ES)</option><?php endif; ?>
                                <?php if (check_language_enabled('ro_RO',$settings['languages_enabled'])) : ?><option <?php echo ($user_info['language']=='ro_RO') ? 'selected':''; ?> id="ro_RO">Romanian (ro_RO)</option><?php endif; ?>
                                <?php if (check_language_enabled('ru_RU',$settings['languages_enabled'])) : ?><option <?php echo ($user_info['language']=='ru_RU') ? 'selected':''; ?> id="ru_RU">Russian (ru_RU)</option><?php endif; ?>
                                <?php if (check_language_enabled('sv_SE',$settings['languages_enabled'])) : ?><option <?php echo ($user_info['language']=='sv_SE') ? 'selected':''; ?> id="sv_SE">Swedish (sv_SE)</option><?php endif; ?>
                                <?php if (check_language_enabled('tg_TJ',$settings['languages_enabled'])) : ?><option <?php echo ($user_info['language']=='tg_TJ') ? 'selected':''; ?> id="tg_TJ">Tajik (tg_TJ)</option><?php endif; ?>
                                <?php if (check_language_enabled('th_TH',$settings['languages_enabled'])) : ?><option <?php echo ($user_info['language']=='th_TH') ? 'selected':''; ?> id="th_TH">Thai (th_TH)</option><?php endif; ?>
                                <?php if (check_language_enabled('tr_TR',$settings['languages_enabled'])) : ?><option <?php echo ($user_info['language']=='tr_TR') ? 'selected':''; ?> id="tr_TR">Turkish (tr_TR)</option><?php endif; ?>
                                <?php if (check_language_enabled('vi_VN',$settings['languages_enabled'])) : ?><option <?php echo ($user_info['language']=='vi_VN') ? 'selected':''; ?> id="vi_VN">Vietnamese (vi_VN)</option><?php endif; ?>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label><?php echo _("Password"); ?></label>
                            <button data-toggle="modal" data-target="#modal_change_password" class="btn btn-block btn-primary"><?php echo _("CHANGE"); ?></button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-camera"></i> <?php echo _("Avatar"); ?></h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-12 text-center">
                        <img id="avatar_edit" src="<?php echo $user_info['avatar']; ?>" />
                    </div>
                    <div class="col-md-12 text-center">
                        <input class="d-none" type="file" id="input_avatar" accept="image/*">
                        <button id="btn_upload_avatar" onclick="upload_avatar_file();" class="btn btn-primary mt-3"><?php echo _("UPLOAD"); ?></button>
                        <button id="btn_create_avatar" onclick="create_avatar_file();" class="btn btn-success d-none"><?php echo _("CREATE"); ?></button>
                    </div>
                </div>
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
                            <input autocomplete="new-password" type="password" minlength="6" required class="form-control" id="password" />
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="repeat_password"><?php echo _("Repeat Password"); ?></label>
                            <input autocomplete="new-password" type="password" minlength="6" required class="form-control" id="repeat_password" />
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button <?php echo ($demo) ? 'disabled':''; ?> onclick="change_password();" type="button" class="btn btn-success"><i class="fas fa-key"></i> <?php echo _("Change"); ?></button>
                <button type="button" class="btn btn-secondary" data-dismiss="modal"><i class="fas fa-times"></i> <?php echo _("Close"); ?></button>
            </div>
        </div>
    </div>
</div>

<script>
    (function($) {
        "use strict"; // Start of use strict
        var avatar_crop = null;
        window.user_need_save = false;
        window.id_user_edit = '<?php echo $id_user_edit; ?>';
        $(document).ready(function () {
            $('#input_avatar').on('change', function () { readFile(this); });
        });
        function readFile(input) {
            if (input.files && input.files[0]) {
                var reader = new FileReader();
                reader.onload = function (e) {
                    avatar_crop = $('#avatar_edit').croppie({
                        url: e.target.result,
                        enableExif: true,
                        viewport: {
                            width: 160,
                            height: 160,
                            type: 'circle'
                        },
                        boundary: {
                            width: 160,
                            height: 160
                        }
                    });
                    $('#btn_upload_avatar').hide();
                    $('#btn_create_avatar').removeClass('d-none');
                }
                reader.readAsDataURL(input.files[0]);
            }
        }
        window.upload_avatar_file = function() {
            $('#input_avatar').click();
        }
        window.create_avatar_file = function() {
            avatar_crop.croppie('result','base64','viewport','jpeg',1,true).then(function(base64) {
                $('#input_avatar').off('change');
                $('#input_avatar').val('');
                $('#avatar_edit').attr('src',base64);
                $('#btn_create_avatar').addClass('d-none');
                save_profile(window.id_user_edit);
            });
        }
        $("input[type='text']").change(function(){
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