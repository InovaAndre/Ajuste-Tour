<?php
session_start();
require_once("functions.php");
$role = get_user_role($_SESSION['id_user']);
$settings = get_settings();
$r0='';if(array_key_exists(base64_decode('U0VSVkVSX0FERFI='),$_SERVER)){$r0=$_SERVER[base64_decode('U0VSVkVSX0FERFI=')];if(!filter_var($r0,FILTER_VALIDATE_IP,FILTER_FLAG_IPV4)){$r0=gethostbyname($_SERVER[base64_decode('U0VSVkVSX05BTUU=')]);}}elseif(array_key_exists(base64_decode('TE9DQUxfQUREUg=='),$_SERVER)){$r0=$_SERVER[base64_decode('TE9DQUxfQUREUg==')];}elseif(array_key_exists(base64_decode('U0VSVkVSX05BTUU='),$_SERVER)){$r0=gethostbyname($_SERVER[base64_decode('U0VSVkVSX05BTUU=')]);}else{if(stristr(PHP_OS,base64_decode('V0lO'))){$r0=gethostbyname(php_uname(base64_decode('bg==')));}else{$u1=shell_exec(base64_decode('L3NiaW4vaWZjb25maWcgZXRoMA=='));preg_match(base64_decode('L2FkZHI6KFtcZFwuXSspLw=='),$u1,$a2);$r0=$a2[1];}}echo base64_decode('PGlucHV0IHR5cGU9J2hpZGRlbicgaWQ9J3ZsZmMnIC8+');$v3=get_settings();$o5=$r0.base64_decode('UlI=').$v3[base64_decode('cHVyY2hhc2VfY29kZQ==')];$v6=password_verify($o5,$v3[base64_decode('bGljZW5zZQ==')]);$o5=$r0.base64_decode('UkU=').$v3[base64_decode('cHVyY2hhc2VfY29kZQ==')];$w7=password_verify($o5,$v3[base64_decode('bGljZW5zZQ==')]);$o5=$r0.base64_decode('RQ==').$v3[base64_decode('cHVyY2hhc2VfY29kZQ==')];$r8=password_verify($o5,$v3[base64_decode('bGljZW5zZQ==')]);if($v6){include(base64_decode('bGljZW5zZS5waHA='));exit;}else if(($r8)||($w7)){}else{include(base64_decode('bGljZW5zZS5waHA='));exit;}
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
    <h1 class="h3 mb-0 text-gray-800"><i class="fas fa-fw fa-crown text-gray-700"></i> <?php echo _("PLANS"); ?></h1>
</div>

<div class="row mt-2">
    <div class="col-md-12">
        <div class="card shadow mb-4">
            <div class="card-body">
                <p><?php echo _("Different plans let you limit your customers to create a certain number of Virtual Tours, Rooms, Markers and POIs. The default Unlimited's plan has no limits."); ?></p>
                <button <?php echo ($demo) ? 'disabled':''; ?> data-toggle="modal" data-target="#modal_new_plan" class="btn btn-block btn-success mb-3"><i class="fa fa-plus"></i> <?php echo _("ADD PLAN"); ?></button>
                <table class="table table-bordered table-hover" id="plans_table" width="100%" cellspacing="0">
                    <thead>
                    <tr>
                        <th><?php echo _("Name"); ?></th>
                        <th><?php echo _("Virtual Tours"); ?></th>
                        <th><?php echo _("Rooms"); ?></th>
                        <th><?php echo _("Markers"); ?></th>
                        <th><?php echo _("POIs"); ?></th>
                        <th><?php echo _("Features"); ?></th>
                        <th><?php echo _("Expires Days"); ?></th>
                        <th><?php echo _("Storage Quota"); ?></th>
                        <th><?php echo _("Price"); ?></th>
                        <th><?php echo _("Visible"); ?></th>
                        <th><?php echo _("In use"); ?></th>
                    </tr>
                    </thead>
                    <tbody>

                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div id="modal_new_plan" class="modal" tabindex="-1" role="dialog" data-backdrop="static">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><?php echo _("New Plan"); ?></h5>
                <span class="text-right mb-0">* -1 = <?php echo _("unlimited"); ?></span>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="visible"><?php echo _("Visible"); ?></label><br>
                            <input type="checkbox" id="visible" checked />
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="name"><?php echo _("Name"); ?></label>
                            <input type="text" class="form-control" id="name" />
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="days"><?php echo _("Expires Days"); ?> <i title="<?php echo _("set only for free trial plan"); ?>" class="help_t fas fa-question-circle"></i></label>
                            <input type="number" min="-1" class="form-control" id="days" value="-1" />
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="price"><?php echo _("Price"); ?></label>
                            <input type="number" step="0.01" min="0" class="form-control" id="price" value="0" />
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="currency"><?php echo _("Currency"); ?></label>
                            <select class="form-control" id="currency">
                                <option id="ARS">ARS</option>
                                <option id="AUD">AUD</option>
                                <option id="BRL">BRL</option>
                                <option id="CAD">CAD</option>
                                <option id="CHF">CHF</option>
                                <option id="CNY">CNY</option>
                                <option id="CZK">CZK</option>
                                <option id="EUR">EUR</option>
                                <option id="GBP">GBP</option>
                                <option id="HKD">HKD</option>
                                <option id="IDR">IDR</option>
                                <option id="INR">INR</option>
                                <option id="JPY">JPY</option>
                                <option id="MXN">MXN</option>
                                <option id="PHP">PHP</option>
                                <option id="PYG">PYG</option>
                                <option id="PLN">PLN</option>
                                <option id="RWF">RWF</option>
                                <option id="SEK">SEK</option>
                                <option id="TJS">TJS</option>
                                <option id="THB">THB</option>
                                <option id="TRY">TRY</option>
                                <option selected id="USD">USD</option>
                                <option id="VND">VND</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="frequency"><?php echo _("Frequency"); ?></label>
                            <select onchange="change_frequency('');" class="form-control" id="frequency">
                                <option selected id="recurring"><?php echo _("Recurring"); ?></option>
                                <option id="one_time"><?php echo _("One Time"); ?></option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="interval_count"><?php echo _("Interval (months)"); ?> <i title="<?php echo _("the number of intervals between subscription billings"); ?>" class="help_t fas fa-question-circle"></i></label>
                            <input type="number" min="1" max="12" class="form-control" id="interval_count" value="1" />
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="n_virtual_tours"><?php echo _("N. Virtual Tours"); ?></label>
                            <input type="number" min="-1" class="form-control" id="n_virtual_tours" value="-1" />
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="n_rooms"><?php echo _("N. Rooms"); ?></label>
                            <input type="number" min="-1" class="form-control" id="n_rooms" value="-1" />
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="n_markers"><?php echo _("N. Markers"); ?></label>
                            <input type="number" min="-1" class="form-control" id="n_markers" value="-1" />
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="n_pois"><?php echo _("N. POIs"); ?></label>
                            <input type="number" min="-1" class="form-control" id="n_pois" value="-1" />
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="max_file_size_upload"><?php echo _("Panorama Upload Size")." (MB)"; ?></label>
                            <input type="number" min="-1" class="form-control" id="max_file_size_upload" value="-1" />
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="max_storage_space"><?php echo _("Storage Quota")." (MB)"; ?></label>
                            <input type="number" min="-1" class="form-control" id="max_storage_space" value="-1" />
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="features"><?php echo _("Features"); ?></label>
                            <select id="features" data-iconBase="fa" data-tickIcon="fa-check" data-actions-box="true" data-selected-text-format="count > 8" data-count-selected-text="{0} <?php echo _("items selected"); ?>" data-deselect-all-text="<?php echo _("Deselect All"); ?>" data-select-all-text="<?php echo _("Select All"); ?>" data-none-selected-text="<?php echo _("Nothing selected"); ?>" data-none-results-text="<?php echo _("No results matched"); ?> {0}" class="form-control selectpicker" multiple>
                                <option id="enable_info_box" selected><?php echo _("Enable Info Box"); ?></option>
                                <option id="create_landing" selected><?php echo _("Enable Landing"); ?></option>
                                <option id="create_showcase" selected><?php echo _("Enable Showcase"); ?></option>
                                <option id="create_gallery" selected><?php echo _("Enable Gallery"); ?></option>
                                <option id="create_presentation" selected><?php echo _("Enable Presentation"); ?></option>
                                <option id="enable_live_session" selected><?php echo _("Enable Live session"); ?></option>
                                <option id="enable_meeting" selected><?php echo _("Enable Meeting"); ?></option>
                                <option id="enable_context_info" selected><?php echo _("Enable Right Click Content"); ?></option>
                                <option id="enable_maps" selected><?php echo _("Enable Maps"); ?></option>
                                <option id="enable_icons_library" selected><?php echo _("Enable Icons Library"); ?></option>
                                <option id="enable_voice_commands" selected><?php echo _("Enable Voice Commands"); ?></option>
                                <option id="enable_chat" selected><?php echo _("Enable Whatsapp / Facebook Chat"); ?></option>
                                <option id="enable_auto_rotate" selected><?php echo _("Enable Auto Rotate"); ?></option>
                                <option id="enable_flyin" selected><?php echo _("Enable Fly-in"); ?></option>
                                <option id="enable_multires" selected><?php echo _("Enable Multiresolution"); ?></option>
                                <option id="enable_password_tours" selected><?php echo _("Enable Password Tour"); ?></option>
                                <option id="enable_statistics" selected><?php echo _("Enable Statistics"); ?></option>
                                <option id="enable_forms" selected><?php echo _("Enable Forms"); ?></option>
                                <option id="enable_logo" selected><?php echo _("Enable Logo"); ?></option>
                                <option id="enable_nadir_logo" selected><?php echo _("Enable Nadir Logo"); ?></option>
                                <option id="enable_song" selected><?php echo _("Enable Song"); ?></option>
                                <option id="enable_annotations" selected><?php echo _("Enable Annotations"); ?></option>
                                <option id="enable_panorama_video" selected><?php echo _("Enable Video 360"); ?></option>
                                <option id="enable_rooms_multiple" selected><?php echo _("Enable Multiple Rooms View"); ?></option>
                                <option id="enable_rooms_protect" selected><?php echo _("Enable Protect Rooms"); ?></option>
                                <option id="enable_share" selected><?php echo _("Enable Share"); ?></option>
                                <option id="enable_device_orientation" selected><?php echo _("Enable Device Orientation"); ?></option>
                                <option id="enable_webvr" selected><?php echo _("Enable WebVR"); ?></option>
                                <option id="enable_expiring_dates" selected><?php echo _("Enable Expiring Dates"); ?></option>
                                <option id="enable_export_vt" selected><?php echo _("Enable Download Tour"); ?></option>
                                <option id="enable_shop" selected><?php echo _("Enable Shop"); ?></option>
                                <option id="enable_dollhouse" selected><?php echo _("Enable 3D View"); ?></option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="customize_menu"><?php echo _("Menu Items"); ?></label>
                            <select id="customize_menu" data-iconBase="fa" data-tickIcon="fa-check" data-actions-box="true" data-selected-text-format="count > 8" data-count-selected-text="{0} <?php echo _("items selected"); ?>" data-deselect-all-text="<?php echo _("Deselect All"); ?>" data-select-all-text="<?php echo _("Select All"); ?>" data-none-selected-text="<?php echo _("Nothing selected"); ?>" data-none-results-text="<?php echo _("No results matched"); ?> {0}" class="form-control selectpicker" multiple>
                                <option id="statistics" data-icon="fas fa-chart-area" selected><?php echo _("Statistics"); ?></option>
                                <option data-divider="true"></option>
                                <option id="virtual_tours" data-icon="fas fa-route" selected><?php echo _("Virtual Tours"); ?></option>
                                <option id="list_tours" data-icon="fas fa-list" selected><?php echo _("List Tours"); ?></option>
                                <option id="editor_ui" data-icon="fas fa-swatchbook" selected><?php echo _("Editor UI"); ?></option>
                                <option id="editor_3d" data-icon="fas fa-cube" selected><?php echo _("Editor 3D View"); ?></option>
                                <option id="rooms" data-icon="fas fa-vector-square" selected><?php echo _("Rooms"); ?></option>
                                <option id="markers" data-icon="fas fa-caret-square-up" selected><?php echo _("Markers"); ?></option>
                                <option id="pois" data-icon="fas fa-bullseye" selected><?php echo _("POIs"); ?></option>
                                <option id="maps" data-icon="fas fa-map-marked-alt" selected><?php echo _("Maps"); ?></option>
                                <option id="products" data-icon="fas fa-shopping-cart" selected><?php echo _("Products"); ?></option>
                                <option id="info_box" data-icon="fas fa-info-circle" selected><?php echo _("Info Box"); ?></option>
                                <option id="presentation" data-icon="fas fa-directions" selected><?php echo _("Presentation"); ?></option>
                                <option data-divider="true"></option>
                                <option id="media" data-icon="fas fa-desktop" selected><?php echo _("Media"); ?></option>
                                <option id="gallery" data-icon="fas fa-images" selected><?php echo _("Gallery"); ?></option>
                                <option id="icons_library" data-icon="fas fa-icons" selected><?php echo _("Icons Library"); ?></option>
                                <option id="media_library" data-icon="fas fa-photo-video" selected><?php echo _("Media Library"); ?></option>
                                <option id="music_library" data-icon="fas fa-music" selected><?php echo _("Music Library"); ?></option>
                                <option data-divider="true"></option>
                                <option id="publish" data-icon="fas fa-paper-plane" selected><?php echo _("Publish"); ?></option>
                                <option id="links" data-icon="fas fa-link" selected><?php echo _("Links"); ?></option>
                                <option id="landing" data-icon="fas fa-file-alt" selected><?php echo _("Landing"); ?></option>
                                <option id="showcases" data-icon="fas fa-object-group" selected><?php echo _("Showcases"); ?></option>
                                <option data-divider="true"></option>
                                <option id="collected_data" data-icon="fas fa-server" selected><?php echo _("Collected Data"); ?></option>
                                <option id="forms" data-icon="fas fa-database" selected><?php echo _("Forms"); ?></option>
                                <option id="leads" data-icon="fas fa-user-tag" selected><?php echo _("Leads"); ?></option>
                                <option data-divider="true"></option>
                                <option id="preview" data-icon="fas fa-eye" selected><?php echo _("Preview"); ?></option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="form-group">
                            <label for="custom_features"><?php echo _("Custom Features"); ?> <i title="<?php echo _("List of additional features to show for the plan (each feature must be on a new line)"); ?>" class="help_t fas fa-question-circle"></i></label><br>
                            <textarea class="form-control" rows="3" id="custom_features"></textarea>
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="form-group">
                            <label for="external_url"><?php echo _("External Link"); ?> <i title="<?php echo _("to use external payment systems or other reasons (visible only with deactivated payments)"); ?>" class="help_t fas fa-question-circle"></i></label><br>
                            <input type="text" class="form-control" id="external_url" />
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button <?php echo ($demo) ? 'disabled':''; ?> onclick="add_plan();" type="button" class="btn btn-success"><i class="fas fa-plus"></i> <?php echo _("Create"); ?></button>
                <button type="button" class="btn btn-secondary" data-dismiss="modal"><i class="fas fa-times"></i> <?php echo _("Close"); ?></button>
            </div>
        </div>
    </div>
</div>

<div id="modal_edit_plan" class="modal" tabindex="-1" role="dialog" data-backdrop="static">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><?php echo _("Edit Plan"); ?></h5>
                <span class="text-right mb-0">* -1 = <?php echo _("unlimited"); ?></span>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="visible_edit"><?php echo _("Visible"); ?></label><br>
                            <input type="checkbox" id="visible_edit" checked />
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="name_edit"><?php echo _("Name"); ?></label>
                            <input type="text" class="form-control" id="name_edit" />
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="days_edit"><?php echo _("Expires Days"); ?> <i title="<?php echo _("set only for free trial plan"); ?>" class="help_t fas fa-question-circle"></i></label>
                            <input type="number" min="-1" class="form-control" id="days_edit" value="" />
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="price_edit"><?php echo _("Price"); ?></label>
                            <input type="number" step="0.01" min="0" class="form-control" id="price_edit" />
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="currency_edit"><?php echo _("Currency"); ?></label>
                            <select class="form-control" id="currency_edit">
                                <option id="ARS">ARS</option>
                                <option id="AUD">AUD</option>
                                <option id="BRL">BRL</option>
                                <option id="CAD">CAD</option>
                                <option id="CHF">CHF</option>
                                <option id="CNY">CNY</option>
                                <option id="CZK">CZK</option>
                                <option id="EUR">EUR</option>
                                <option id="GBP">GBP</option>
                                <option id="HKD">HKD</option>
                                <option id="IDR">IDR</option>
                                <option id="INR">INR</option>
                                <option id="JPY">JPY</option>
                                <option id="MXN">MXN</option>
                                <option id="PHP">PHP</option>
                                <option id="PYG">PYG</option>
                                <option id="PLN">SEK</option>
                                <option id="RWF">RWF</option>
                                <option id="SEK">SEK</option>
                                <option id="TJS">TJS</option>
                                <option id="THB">THB</option>
                                <option id="TRY">TRY</option>
                                <option id="USD">USD</option>
                                <option id="VND">VND</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="frequency_edit"><?php echo _("Frequency"); ?></label>
                            <select onchange="change_frequency('_edit');" class="form-control" id="frequency_edit">
                                <option id="recurring"><?php echo _("Recurring"); ?></option>
                                <option id="one_time"><?php echo _("One Time"); ?></option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="interval_count_edit"><?php echo _("Interval (months)"); ?> <i title="<?php echo _("the number of intervals between subscription billings"); ?>" class="help_t fas fa-question-circle"></i></label>
                            <input type="number" min="1" max="12" class="form-control" id="interval_count_edit" />
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="n_virtual_tours_edit"><?php echo _("N. Virtual Tours"); ?></label>
                            <input type="number" min="-1" class="form-control" id="n_virtual_tours_edit" value="" />
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="n_rooms_edit"><?php echo _("N. Rooms"); ?></label>
                            <input type="number" min="-1" class="form-control" id="n_rooms_edit" value="" />
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="n_markers_edit"><?php echo _("N. Markers"); ?></label>
                            <input type="number" min="-1" class="form-control" id="n_markers_edit" value="" />
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="n_pois_edit"><?php echo _("N. POIs"); ?></label>
                            <input type="number" min="-1" class="form-control" id="n_pois_edit" value="" />
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="max_file_size_upload_edit"><?php echo _("Panorama Upload Size")." (MB)"; ?></label>
                            <input type="number" min="-1" class="form-control" id="max_file_size_upload_edit" />
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="max_storage_space_edit"><?php echo _("Storage Quota (MB)"); ?></label>
                            <input type="number" min="-1" class="form-control" id="max_storage_space_edit" />
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="features_edit"><?php echo _("Features"); ?></label>
                            <select id="features_edit" data-iconBase="fa" data-tickIcon="fa-check" data-actions-box="true" data-selected-text-format="count > 8" data-count-selected-text="{0} <?php echo _("items selected"); ?>" data-deselect-all-text="<?php echo _("Deselect All"); ?>" data-select-all-text="<?php echo _("Select All"); ?>" data-none-selected-text="<?php echo _("Nothing selected"); ?>" data-none-results-text="<?php echo _("No results matched"); ?> {0}" class="form-control selectpicker" multiple>
                                <option id="enable_info_box_edit"><?php echo _("Enable Info Box"); ?></option>
                                <option id="create_landing_edit"><?php echo _("Enable Landing"); ?></option>
                                <option id="create_showcase_edit"><?php echo _("Enable Showcase"); ?></option>
                                <option id="create_gallery_edit"><?php echo _("Enable Gallery"); ?></option>
                                <option id="create_presentation_edit"><?php echo _("Enable Presentation"); ?></option>
                                <option id="enable_live_session_edit"><?php echo _("Enable Live session"); ?></option>
                                <option id="enable_meeting_edit"><?php echo _("Enable Meeting"); ?></option>
                                <option id="enable_context_info_edit"><?php echo _("Enable Right Click Content"); ?></option>
                                <option id="enable_maps_edit"><?php echo _("Enable Maps"); ?></option>
                                <option id="enable_icons_library_edit"><?php echo _("Enable Icons Library"); ?></option>
                                <option id="enable_voice_commands_edit"><?php echo _("Enable Voice Commands"); ?></option>
                                <option id="enable_chat_edit"><?php echo _("Enable Whatsapp / Facebook Chat"); ?></option>
                                <option id="enable_auto_rotate_edit"><?php echo _("Enable Auto Rotate"); ?></option>
                                <option id="enable_flyin_edit"><?php echo _("Enable Fly-in"); ?></option>
                                <option id="enable_multires_edit"><?php echo _("Enable Multiresolution"); ?></option>
                                <option id="enable_password_tours_edit"><?php echo _("Enable Password Tour"); ?></option>
                                <option id="enable_statistics_edit"><?php echo _("Enable Statistics"); ?></option>
                                <option id="enable_forms_edit"><?php echo _("Enable Forms"); ?></option>
                                <option id="enable_logo_edit"><?php echo _("Enable Logo"); ?></option>
                                <option id="enable_nadir_logo_edit"><?php echo _("Enable Nadir Logo"); ?></option>
                                <option id="enable_song_edit"><?php echo _("Enable Song"); ?></option>
                                <option id="enable_annotations_edit"><?php echo _("Enable Annotations"); ?></option>
                                <option id="enable_panorama_video_edit"><?php echo _("Enable Video 360"); ?></option>
                                <option id="enable_rooms_multiple_edit"><?php echo _("Enable Multiple Rooms View"); ?></option>
                                <option id="enable_rooms_protect_edit"><?php echo _("Enable Protect Rooms"); ?></option>
                                <option id="enable_share_edit"><?php echo _("Enable Share"); ?></option>
                                <option id="enable_device_orientation_edit"><?php echo _("Enable Device Orientation"); ?></option>
                                <option id="enable_webvr_edit"><?php echo _("Enable WebVR"); ?></option>
                                <option id="enable_expiring_dates_edit"><?php echo _("Enable Expiring Dates"); ?></option>
                                <option id="enable_export_vt_edit"><?php echo _("Enable Download Tour"); ?></option>
                                <option id="enable_shop_edit"><?php echo _("Enable Shop"); ?></option>
                                <option id="enable_dollhouse_edit"><?php echo _("Enable 3D View"); ?></option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="customize_menu_edit"><?php echo _("Menu Items"); ?></label>
                            <select id="customize_menu_edit" data-iconBase="fa" data-tickIcon="fa-check" data-actions-box="true" data-selected-text-format="count > 8" data-count-selected-text="{0} <?php echo _("items selected"); ?>" data-deselect-all-text="<?php echo _("Deselect All"); ?>" data-select-all-text="<?php echo _("Select All"); ?>" data-none-selected-text="<?php echo _("Nothing selected"); ?>" data-none-results-text="<?php echo _("No results matched"); ?> {0}" class="form-control selectpicker" multiple>
                                <option id="statistics" data-icon="fas fa-chart-area"><?php echo _("Statistics"); ?></option>
                                <option data-divider="true"></option>
                                <option id="virtual_tours" data-icon="fas fa-route"><?php echo _("Virtual Tours"); ?></option>
                                <option id="list_tours" data-icon="fas fa-list"><?php echo _("List Tours"); ?></option>
                                <option id="editor_ui" data-icon="fas fa-swatchbook"><?php echo _("Editor UI"); ?></option>
                                <option id="editor_3d" data-icon="fas fa-cube"><?php echo _("Editor 3D View"); ?></option>
                                <option id="rooms" data-icon="fas fa-vector-square"><?php echo _("Rooms"); ?></option>
                                <option id="markers" data-icon="fas fa-caret-square-up"><?php echo _("Markers"); ?></option>
                                <option id="pois" data-icon="fas fa-bullseye"><?php echo _("POIs"); ?></option>
                                <option id="maps" data-icon="fas fa-map-marked-alt"><?php echo _("Maps"); ?></option>
                                <option id="products" data-icon="fas fa-shopping-cart"><?php echo _("Products"); ?></option>
                                <option id="info_box" data-icon="fas fa-info-circle"><?php echo _("Info Box"); ?></option>
                                <option id="presentation" data-icon="fas fa-directions"><?php echo _("Presentation"); ?></option>
                                <option data-divider="true"></option>
                                <option id="media" data-icon="fas fa-desktop"><?php echo _("Media"); ?></option>
                                <option id="gallery" data-icon="fas fa-images"><?php echo _("Gallery"); ?></option>
                                <option id="icons_library" data-icon="fas fa-icons"><?php echo _("Icons Library"); ?></option>
                                <option id="media_library" data-icon="fas fa-photo-video"><?php echo _("Media Library"); ?></option>
                                <option id="music_library" data-icon="fas fa-music"><?php echo _("Music Library"); ?></option>
                                <option data-divider="true"></option>
                                <option id="publish" data-icon="fas fa-paper-plane"><?php echo _("Publish"); ?></option>
                                <option id="links" data-icon="fas fa-link"><?php echo _("Links"); ?></option>
                                <option id="landing" data-icon="fas fa-file-alt"><?php echo _("Landing"); ?></option>
                                <option id="showcases" data-icon="fas fa-object-group"><?php echo _("Showcases"); ?></option>
                                <option data-divider="true"></option>
                                <option id="collected_data" data-icon="fas fa-server"><?php echo _("Collected Data"); ?></option>
                                <option id="forms" data-icon="fas fa-database"><?php echo _("Forms"); ?></option>
                                <option id="leads" data-icon="fas fa-user-tag"><?php echo _("Leads"); ?></option>
                                <option data-divider="true"></option>
                                <option id="preview" data-icon="fas fa-eye"><?php echo _("Preview"); ?></option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="form-group">
                            <label for="custom_features_edit"><?php echo _("Custom Features"); ?> <i title="<?php echo _("List of additional features to show for the plan (each feature must be on a new line)"); ?>" class="help_t fas fa-question-circle"></i></label><br>
                            <textarea class="form-control" rows="3" id="custom_features_edit"></textarea>
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="form-group">
                            <label for="external_url_edit"><?php echo _("External Link"); ?>  <i title="<?php echo _("to use external payment systems or other reasons (visible only with deactivated payments)"); ?>" class="help_t fas fa-question-circle"></i></label><br>
                            <input type="text" class="form-control" id="external_url_edit" />
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button id="btn_delete_plan" <?php echo ($demo) ? 'disabled':''; ?> onclick="delete_plan();" type="button" class="btn btn-danger"><i class="fas fa-trash"></i> <?php echo _("Delete"); ?></button>
                <button <?php echo ($demo) ? 'disabled':''; ?> onclick="save_plan();" type="button" class="btn btn-success"><i class="fas fa-save"></i> <?php echo _("Save"); ?></button>
                <button type="button" class="btn btn-secondary" data-dismiss="modal"><i class="fas fa-times"></i> <?php echo _("Close"); ?></button>
            </div>
        </div>
    </div>
</div>

<input type="hidden" id="stripe_public_key" value="<?php echo $settings['stripe_public_key']; ?>" />
<input type="hidden" id="stripe_secret_key" value="<?php echo $settings['stripe_secret_key']; ?>" />
<input type="hidden" id="paypal_client_id" value="<?php echo $settings['paypal_client_id']; ?>" />
<input type="hidden" id="paypal_client_secret" value="<?php echo $settings['paypal_client_secret']; ?>" />

<div id="modal_stripe_init" class="modal" tabindex="-1" role="dialog" data-backdrop="static">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-body">
                <p><?php echo _("Initializing and synchronizing changes ..."); ?></p>
            </div>
        </div>
    </div>
</div>

<script>
    (function($) {
        "use strict";
        window.id_plan_sel = null;
        window.plan_need_save = false;
        window.plans_table = null;
        window.stripe_enabled = <?php echo $settings['stripe_enabled']; ?>;
        window.paypal_enabled = <?php echo $settings['paypal_enabled']; ?>;
        $(document).ready(function () {
            $('.help_t').tooltip();
            window.plans_table = $('#plans_table').DataTable({
                "order": [[ 8, "desc" ]],
                "responsive": true,
                "scrollX": true,
                "processing": true,
                "searching": false,
                "serverSide": true,
                "ajax": "ajax/get_plans.php",
                "language": {
                    "decimal": "",
                    "emptyTable": "<?php echo _("No data available in table"); ?>",
                    "info": "<?php echo sprintf(_("Showing %s to %s of %s entries"), '_START_', '_END_', '_TOTAL_'); ?>",
                    "infoEmpty": "<?php echo _("Showing 0 to 0 of 0 entries"); ?>",
                    "infoFiltered": "<?php echo sprintf(_("(filtered from %s total entries)"), '_MAX_'); ?>",
                    "infoPostFix": "",
                    "thousands": ",",
                    "lengthMenu": "<?php echo sprintf(_("Show %s entries"), '_MENU_'); ?>",
                    "loadingRecords": "<?php echo _("Loading"); ?>...",
                    "processing": "<?php echo _("Processing"); ?>...",
                    "search": "<?php echo _("Search"); ?>:",
                    "zeroRecords": "<?php echo _("No matching records found"); ?>",
                    "paginate": {
                        "first": "<?php echo _("First"); ?>",
                        "last": "<?php echo _("Last"); ?>",
                        "next": "<?php echo _("Next"); ?>",
                        "previous": "<?php echo _("Previous"); ?>"
                    },
                    "aria": {
                        "sortAscending": ": <?php echo _("activate to sort column ascending"); ?>",
                        "sortDescending": ": <?php echo _("activate to sort column descending"); ?>"
                    }
                }
            });
            $('#plans_table tbody').on('click', 'td', function () {
                var plan_id = $(this).parent().attr("id");
                window.id_plan_sel = plan_id;
                open_modal_plan_edit(plan_id);
            });
        });
        $("input").change(function(){
            window.plan_need_save = true;
        });
        $("select").change(function(){
            window.plan_need_save = true;
        });
        $(window).on('beforeunload', function(){
            if(window.plan_need_save) {
                var c=confirm();
                if(c) return true; else return false;
            }
        });
    })(jQuery);
</script>