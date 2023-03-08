<?php
switch($user_info['plan_status']) {
    case 'active':
        $icon_plan = "<i class='fa fa-circle mt-1' style='color: green'></i>";
        break;
    case 'expiring':
        $icon_plan = "<i class='fa fa-circle mt-1' style='color: darkorange'></i>";
        break;
    case 'expired':
    case 'invalid_payment':
        $icon_plan = "<i class='fa fa-circle mt-1' style='color: red'></i>";
        break;
}
$settings = get_settings();
if(empty($_SESSION['lang'])) {
    $lang = $settings['language'];
} else {
    $lang = $_SESSION['lang'];
}
?>

<nav class="navbar navbar-expand navbar-light bg-white topbar mb-3 static-top shadow">
    <button id="sidebarToggleTop" class="btn btn-link d-md-none rounded-circle mr-3">
        <i class="fa fa-bars"></i>
    </button>
    <ul class="navbar-nav ml-auto">
        <?php if($user_info['role']!='editor') : ?>
        <?php if($user_info['id_plan']!=0) : ?>
            <li class="nav-item dropdown no-arrow">
            <a class="nav-link dropdown-toggle" href="#" id="planDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                <span class="nav-link text-gray-600 small"><span class="px-2 py-1" style="margin-top:1px;border:1px solid #c4c4c4;border-radius:20px"><?php echo $icon_plan; ?>&nbsp;&nbsp;<?php echo $user_info['plan']; ?></span></span>
            </a>
            <div style="cursor: default;" class="dropdown-list dropdown-menu dropdown-menu-right shadow animated--grow-in" aria-labelledby="planDropdown">
                <div style="pointer-events:none;" class="dropdown-item">
                    <?php echo _("Status"); ?>:&nbsp;<?php
                    switch($user_info['plan_status']) {
                        case 'active':
                            echo "<span style='color:green'><b>"._("Active")."</b></span>";
                            break;
                        case 'expiring':
                            $expires_msg = "";
                            if($user_info['expire_plan_date']) {
                                $diff_days = dateDiffInDays(date('Y-m-d',strtotime($user_info['expire_plan_date'])),date('Y-m-d',strtotime('today')));
                                $expires_msg = sprintf(_("- expires in %s days"),abs($diff_days));
                            }
                            echo "<span style='color:darkorange'><b>"._("Active")."</b></span> $expires_msg";
                            break;
                        case 'expired':
                            echo "<span style='color:red'><b>"._("Expired")."</b></span>";
                            break;
                        case 'invalid_payment':
                            echo "<span style='color:red'><b>"._("Invalid payment")."</b></span>";
                            break;
                    }
                    ?>
                </div>
                <div style="pointer-events:none;" class="dropdown-item">
                    <?php
                    if($plan_info['n_virtual_tours']>0) {
                        $perc_tours = number_format(calculatePercentage($user_stats['count_virtual_tours'],$plan_info['n_virtual_tours']),0);
                        if($perc_tours>=75 && $perc_tours<100) {
                            $perc_tours_bg = "warning";
                        } else if($perc_tours>=100) {
                            $perc_tours = 100;
                            $perc_tours_bg = "danger";
                        } else {
                            $perc_tours_bg = "success";
                        }
                    } else {
                        $perc_tours = 100;
                        $perc_tours_bg = "success";
                    }
                    if($plan_info['n_rooms']>0) {
                        $perc_rooms = number_format(calculatePercentage($user_stats['count_rooms'],$plan_info['n_rooms']),0);
                        if($perc_rooms>=75 && $perc_rooms<100) {
                            $perc_rooms_bg = "warning";
                        } else if($perc_rooms>=100) {
                            $perc_rooms = 100;
                            $perc_rooms_bg = "danger";
                        } else {
                            $perc_rooms_bg = "success";
                        }
                    } else {
                        $perc_rooms = 100;
                        $perc_rooms_bg = "success";
                    }
                    if($plan_info['n_markers']>0) {
                        $perc_markers = number_format(calculatePercentage($user_stats['count_markers'],$plan_info['n_markers']),0);
                        if($perc_markers>=75 && $perc_markers<100) {
                            $perc_markers_bg = "warning";
                        } else if($perc_markers>=100) {
                            $perc_markers = 100;
                            $perc_markers_bg = "danger";
                        } else {
                            $perc_markers_bg = "success";
                        }
                    } else {
                        $perc_markers = 100;
                        $perc_markers_bg = "success";
                    }
                    if($plan_info['n_pois']>0) {
                        $perc_pois = number_format(calculatePercentage($user_stats['count_pois'],$plan_info['n_pois']),0);
                        if($perc_pois>=75 && $perc_pois<100) {
                            $perc_pois_bg = "warning";
                        } else if($perc_pois>=100) {
                            $perc_pois = 100;
                            $perc_pois_bg = "danger";
                        } else {
                            $perc_pois_bg = "success";
                        }
                    } else {
                        $perc_pois = 100;
                        $perc_pois_bg = "success";
                    }
                    if($plan_info['max_storage_space']>0) {
                        $perc_size = number_format(calculatePercentage($user_info['storage_space'],$plan_info['max_storage_space']),0);
                        if($perc_size>=75 && $perc_size<100) {
                            $perc_size_bg = "warning";
                        } else if($perc_size>=100) {
                            $perc_size = 100;
                            $perc_size_bg = "danger";
                        } else {
                            $perc_size_bg = "success";
                        }
                        if($user_info['storage_space']>=1000) {
                            $actual_storage = ($user_info['storage_space']/1000)." GB";
                        } else {
                            $actual_storage = $user_info['storage_space']." MB";
                        }
                        if($plan_info['max_storage_space']>=1000) {
                            $max_storage = ($plan_info['max_storage_space']/1000)." GB";
                        } else {
                            $max_storage = $plan_info['max_storage_space']." MB";
                        }
                    }
                    ?>
                    <div id="progress_plan_vt" class="progress mb-1 position-relative" style="background-color:#b0b0b0;line-height:16px;">
                        <div style="width:<?php echo $perc_tours; ?>%" class="progress-bar d-inline-block bg-<?php echo $perc_tours_bg; ?>" role="progressbar" aria-valuenow="<?php echo $perc_tours; ?>" aria-valuemin="0" aria-valuemax="100"></div>
                        <div class="justify-content-center d-flex position-absolute w-100 text-white"><?php echo _("Virtual Tours"); ?>: <?php echo $user_stats['count_virtual_tours']." "._("of")."&nbsp;".(($plan_info['n_virtual_tours']<0) ? '<i style="vertical-align: middle;margin-top: 2px;" class="fas fa-infinity"></i>' : '<b>'.$plan_info['n_virtual_tours']).'</b>'; ?></div>
                    </div>
                    <div id="progress_plan_room" class="progress mb-1 position-relative" style="background-color:#b0b0b0;line-height:16px;">
                        <div style="width:<?php echo $perc_rooms; ?>%" class="progress-bar d-inline-block bg-<?php echo $perc_rooms_bg; ?>" role="progressbar" aria-valuenow="100" aria-valuemin="0" aria-valuemax="100"></div>
                        <div class="justify-content-center d-flex position-absolute w-100 text-white"><?php echo _("Rooms"); ?>: <?php echo $user_stats['count_rooms']." "._("of")."&nbsp;".(($plan_info['n_rooms']<0) ? '<i style="vertical-align: middle;margin-top: 2px;" class="fas fa-infinity"></i>' : '<b>'.$plan_info['n_rooms']).'</b>'; ?></div>
                    </div>
                    <div id="progress_plan_marker" class="progress mb-1 position-relative" style="background-color:#b0b0b0;line-height:16px;">
                        <div style="width:<?php echo $perc_markers; ?>%" class="progress-bar d-inline-block bg-<?php echo $perc_markers_bg; ?>" role="progressbar" aria-valuenow="100" aria-valuemin="0" aria-valuemax="100"></div>
                        <div class="justify-content-center d-flex position-absolute w-100 text-white"><?php echo _("Markers"); ?>: <?php echo $user_stats['count_markers']." "._("of")."&nbsp;".(($plan_info['n_markers']<0) ? '<i style="vertical-align: middle;margin-top: 2px;" class="fas fa-infinity"></i>' : '<b>'.$plan_info['n_markers']).'</b>'; ?></div>
                    </div>
                    <div id="progress_plan_poi" class="progress position-relative" style="background-color:#b0b0b0;line-height:16px;">
                        <div style="width:<?php echo $perc_pois; ?>%" class="progress-bar d-inline-block bg-<?php echo $perc_pois_bg; ?>" role="progressbar" aria-valuenow="100" aria-valuemin="0" aria-valuemax="100"></div>
                        <div class="justify-content-center d-flex position-absolute w-100 text-white"><?php echo _("POIs"); ?>: <?php echo $user_stats['count_pois']." "._("of")."&nbsp;".(($plan_info['n_pois']<0) ? '<i style="vertical-align: middle;margin-top: 2px;" class="fas fa-infinity"></i>' : '<b>'.$plan_info['n_pois']).'</b>'; ?></div>
                    </div>
                    <?php if($plan_info['max_storage_space']>0) : ?>
                    <div id="progress_plan_size" class="progress mt-1 position-relative" style="background-color:#b0b0b0;line-height:16px;">
                        <div style="width:<?php echo $perc_size; ?>%" class="progress-bar d-inline-block bg-<?php echo $perc_size_bg; ?>" role="progressbar" aria-valuenow="<?php echo $perc_size; ?>" aria-valuemin="0" aria-valuemax="100"></div>
                        <div class="justify-content-center d-flex position-absolute w-100 text-white"><?php echo _("Storage Quota"); ?>: <?php echo $actual_storage."&nbsp;/&nbsp;".'<b>'.$max_storage.'</b>'; ?></div>
                    </div>
                    <?php endif; ?>
                </div>
                <?php if($settings['change_plan']) { ?>
                <div class="dropdown-item" style="background-color:white;">
                    <a href="index.php?p=change_plan" class="btn btn-primary btn-block btn-sm">
                        <span class="align-middle"><?php echo strtoupper(_("change plan")); ?></span>&nbsp;&nbsp;&nbsp;<i class="fas fa-random align-middle"></i>
                    </a>
                </div>
                <?php } ?>
            </div>
        </li>
        <?php endif; ?>
        <?php endif; ?>
        <li class="nav-item dropdown no-arrow lang_switcher">
            <a class="nav-link dropdown-toggle" href="#" id="langDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" <?php echo ($settings['languages_count']==1) ? 'style="cursor:default;pointer-events:none;"' : ''; ?> >
                <img style="height: 14px;" src="img/flags_lang/<?php echo $lang; ?>.png" />
            </a>
            <div class="dropdown-menu dropdown-menu-right shadow animated--grow-in" aria-labelledby="langDropdown">
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
        <div class="topbar-divider d-none d-sm-block"></div>
        <li class="nav-item dropdown no-arrow">
            <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                <span class="mr-2 d-none d-lg-inline text-gray-600 small"><?php echo $user_info['username']; ?></span>&nbsp;
                <img class="img-profile rounded-circle" src="<?php echo $user_info['avatar']; ?>">
            </a>
            <div class="dropdown-menu dropdown-menu-right shadow animated--grow-in" aria-labelledby="userDropdown">
                <?php if(!empty($settings['help_url'])) : ?>
                    <a class="dropdown-item" target="_blank" href="<?php echo $settings['help_url']; ?>">
                        <i class="fas fa-question fa-sm fa-fw mr-2 text-gray-400"></i>
                        <?php echo _("Help"); ?>
                    </a>
                <?php endif; ?>
                <a class="dropdown-item" href="index.php?p=edit_profile">
                    <i class="fas fa-lock fa-sm fa-fw mr-2 text-gray-400"></i>
                    <?php echo _("Edit profile"); ?>
                </a>
                <a class="dropdown-item" href="#" data-toggle="modal" data-target="#logoutModal">
                    <i class="fas fa-sign-out-alt fa-sm fa-fw mr-2 text-gray-400"></i>
                    <?php echo _("Logout"); ?>
                </a>
            </div>
        </li>
    </ul>
</nav>
<div class="modal fade" id="logoutModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><?php echo _("Ready to Leave?"); ?></h5>
                <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <div class="modal-body"><?php echo _("Select Logout below if you are ready to end your current session."); ?></div>
            <div class="modal-footer">
                <button class="btn btn-secondary" type="button" data-dismiss="modal"><?php echo _("Cancel"); ?></button>
                <button class="btn btn-primary" onclick="logout();"><?php echo _("Logout"); ?></button>
            </div>
        </div>
    </div>
</div>
<script>
    $("#sidebarToggleTop").click(function(){
        if($('#accordionSidebar').hasClass('toggled')) {
            sessionStorage.setItem("sidebar_accord", 1);
            $(".nav-item.active .collapse").addClass('show');
            if($('#sidebar_logo_small').length) {
                $('#sidebar_logo').show();
                $('#sidebar_logo_small').hide();
            }
        } else {
            sessionStorage.setItem("sidebar_accord", 0);
            $(".collapse").removeClass('show');
            if($('#sidebar_logo_small').length) {
                $('#sidebar_logo').hide();
                $('#sidebar_logo_small').show();
            }
        }
    });
</script>