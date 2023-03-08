<?php
session_start();
require_once("functions.php");
$id_user = $_SESSION['id_user'];
$role = get_user_role($id_user);
$virtual_tours = get_virtual_tours($id_user);
$count_virtual_tours = count($virtual_tours);
$array_list_vt = array();
if ($count_virtual_tours==1) {
    $id_virtualtour_sel = $virtual_tours[0]['id'];
    $name_virtualtour_sel = $virtual_tours[0]['name'];
    $author_virtualtour_sel = $virtual_tours[0]['author'];
    $_SESSION['id_virtualtour_sel'] = $id_virtualtour_sel;
    $_SESSION['name_virtualtour_sel'] = $name_virtualtour_sel;
    $array_list_vt[] = array("id"=>$id_virtualtour_sel,"name"=>$name_virtualtour_sel,"author"=>$author_virtualtour_sel);
} else {
    if(isset($_GET['id_vt'])) {
        $id_virtualtour_sel = $_GET['id_vt'];
        $name_virtualtour_sel = get_virtual_tour($_GET['id_vt'],$id_user)['name'];
        $_SESSION['id_virtualtour_sel'] = $id_virtualtour_sel;
        $_SESSION['name_virtualtour_sel'] = $name_virtualtour_sel;
    } else {
        if(isset($_SESSION['id_virtualtour_sel'])) {
            $id_virtualtour_sel = $_SESSION['id_virtualtour_sel'];
            $name_virtualtour_sel = $_SESSION['name_virtualtour_sel'];
        } else {
            $id_virtualtour_sel = $virtual_tours[0]['id'];
            $name_virtualtour_sel = $virtual_tours[0]['name'];
            $author_virtualtour_sel = $virtual_tours[0]['author'];
            $_SESSION['id_virtualtour_sel'] = $id_virtualtour_sel;
            $_SESSION['name_virtualtour_sel'] = $name_virtualtour_sel;
        }
    }
    foreach ($virtual_tours as $virtual_tour) {
        $id_virtualtour = $virtual_tour['id'];
        $name_virtualtour = $virtual_tour['name'];
        $author_virtualtour = $virtual_tour['author'];
        $array_list_vt[] = array("id"=>$id_virtualtour,"name"=>$name_virtualtour,"author"=>$author_virtualtour);
    }
}
$virtual_tour = get_virtual_tour($id_virtualtour_sel,$id_user);
if (is_ssl()) { $protocol = 'https'; } else { $protocol = 'http'; }
$link = $protocol ."://". $_SERVER['SERVER_NAME'] . str_replace("backend/index.php","viewer/index.php?code=",$_SERVER['SCRIPT_NAME']);
$link_f = $protocol ."://". $_SERVER['SERVER_NAME'] . str_replace("backend/index.php","viewer/",$_SERVER['SCRIPT_NAME']);
$linkl = $protocol ."://". $_SERVER['SERVER_NAME'] . str_replace("backend/index.php","landing/index.php?code=",$_SERVER['SCRIPT_NAME']);
$linkl_f = $protocol ."://". $_SERVER['SERVER_NAME'] . str_replace("backend/index.php","landing/",$_SERVER['SCRIPT_NAME']);
$plan_permissions = get_plan_permission($id_user);
$publish = true;
if($user_info['role']=='editor') {
    $editor_permissions = get_editor_permissions($id_user,$id_virtualtour_sel);
    if($editor_permissions['publish']==0) {
        $publish = false;
    }
}
$first_room = get_fisrt_room($id_virtualtour_sel);
if(!empty($virtual_tour['password'])) {
    $virtual_tour['password']="keep_password";
}
?>

<?php include("check_plan.php"); ?>

<div class="d-sm-flex align-items-center justify-content-between mb-3">
    <h1 class="h3 mb-0 text-gray-800"><i class="fas fa-fw fa-paper-plane text-gray-700"></i> <?php echo _("PUBLISH"); ?></h1>
    <?php echo print_virtualtour_selector($array_list_vt,$id_virtualtour_sel); ?>
</div>

<?php if(!$publish): ?>
    <div class="text-center">
        <div class="error mx-auto" data-text="401">401</div>
        <p class="lead text-gray-800 mb-5"><?php echo _("Permission denied"); ?></p>
        <p class="text-gray-500 mb-0"><?php echo _("It looks like that you do not have permission to access this page"); ?></p>
        <a href="index.php?p=dashboard">← <?php echo _("Back to Dashboard"); ?></a>
    </div>
<?php die(); endif; ?>

<div class="row">
    <div class="col-md-6">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-toggle-on"></i> <?php echo _("Status"); ?></h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-12 mb-3">
                        <div class="form-group">
                            <?php echo _("Current status"); ?>: <?php echo ($virtual_tour['active'] ? '<span style="color:green">'._("Activated").'</span>' : '<span style="color:red">'._("Deactivated").'</span>' ); ?>
                        </div>
                    </div>
                    <?php if($virtual_tour['active']) { ?>
                        <div class="col-md-12">
                            <button id="btn_status" onclick="set_status_vt(0);" class="btn btn-sm btn-danger btn-block"><?php echo _("DEACTIVATE"); ?></button>
                        </div>
                    <?php } else { ?>
                        <div class="col-md-12">
                            <button id="btn_status" onclick="set_status_vt(1);" class="btn btn-sm btn-success btn-block"><?php echo _("ACTIVATE"); ?></button>
                        </div>
                    <?php } ?>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-text-width"></i> <?php echo _("Friendly URL"); ?> <i class="font-weight-normal">(<?php echo $link_f; ?><b>xxxxxxx</b>)</i></h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                            <input type="text" class="form-control" id="friendly_url" value="<?php echo $virtual_tour['friendly_url']; ?>" />
                        </div>
                    </div>
                    <div class="col-md-12">
                        <button onclick="set_friendly_url();" id="btn_friendly_url" class="btn btn-sm btn-success btn-block"><?php echo _("SET FRIENDLY URL"); ?></button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-12">
        <div class="card shadow mb-4">
            <a href="#collapsePI" class="d-block card-header py-3 collapsed <?php echo (!$plan_permissions['enable_password_tour']) ? 'disabled' : '' ; ?>" data-toggle="collapse" role="button" aria-expanded="false" aria-controls="collapsePI">
                <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-lock"></i> <?php echo _("Password Protection"); ?></h6>
            </a>
            <div class="collapse" id="collapsePI">
                <div class="card-body <?php echo (!$plan_permissions['enable_password_tour']) ? 'disabled' : '' ; ?>">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="vt_password"><?php echo _("Password"); ?> <i title="<?php echo _("leave empty to disable password protection"); ?>" class="help_t fas fa-question-circle"></i></label>
                                <input autocomplete="new-password" type="password" class="form-control bg-white" id="vt_password" value="<?php echo $virtual_tour['password']; ?>" />
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="vt_password_title"><?php echo _("Title"); ?></label>
                                <input type="text" class="form-control bg-white" id="vt_password_title" value="<?php echo $virtual_tour['password_title']; ?>" />
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="vt_password_description"><?php echo _("Description"); ?></label>
                                <textarea class="form-control" id="vt_password_description" rows="2"><?php echo $virtual_tour['password_description']; ?></textarea>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <button id="btn_protect" onclick="set_password_vt();" class="btn btn-sm btn-success btn-block"><?php echo _("SAVE"); ?></button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-12">
        <div class="card shadow mb-4">
            <a href="#collapsePI_2" class="d-block card-header py-3 collapsed <?php echo (!$plan_permissions['enable_expiring_dates']) ? 'disabled' : '' ; ?>" data-toggle="collapse" role="button" aria-expanded="false" aria-controls="collapsePI">
                <h6 class="m-0 font-weight-bold text-primary"><i class="far fa-calendar-alt"></i> <?php echo _("Expiring Dates"); ?></i></h6>
            </a>
            <div class="collapse" id="collapsePI_2">
                <div class="card-body <?php echo (!$plan_permissions['enable_expiring_dates']) ? 'disabled' : '' ; ?>">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="start_date"><?php echo _("Start Date"); ?></label>
                                <input type="date" class="form-control" id="start_date" value="<?php echo $virtual_tour['start_date']; ?>" />
                            </div>
                        </div>
                        <div class="col-md-8">
                            <div class="form-group">
                                <label for="start_url"><?php echo _("Redirect URL if < Start Date"); ?></label>
                                <input type="text" class="form-control" id="start_url" value="<?php echo $virtual_tour['start_url']; ?>" />
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="end_date"><?php echo _("End Date"); ?></label>
                                <input type="date" class="form-control" id="end_date" value="<?php echo $virtual_tour['end_date']; ?>" />
                            </div>
                        </div>
                        <div class="col-md-8">
                            <div class="form-group">
                                <label for="end_url"><?php echo _("Redirect URL if > End Date"); ?></label>
                                <input type="text" class="form-control" id="end_url" value="<?php echo $virtual_tour['end_url']; ?>" />
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <button id="btn_expires" onclick="set_expiring_dates()" class="btn btn-sm btn-primary btn-block"><?php echo _("SET EXPIRING DATES"); ?></button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-12">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-share-alt"></i> <?php echo _("Share & Embed"); ?></h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                            <label for="link"><i class="fas fa-link"></i> <?php echo _("Viewer Link"); ?></label>
                            <div class="input-group">
                                <input readonly type="text" class="form-control bg-white" id="link" value="<?php echo $link . $virtual_tour['code']; ?>" />
                                <div class="input-group-append">
                                    <button class="btn btn-primary btn-xs" data-clipboard-target="#link">
                                        <i class="far fa-clipboard"></i>
                                    </button>
                                    <button onclick="open_qr_code_modal('<?php echo $link . $virtual_tour['code']; ?>');" class="btn btn-secondary btn-xs">
                                        <i class="fas fa-qrcode"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                            <label for="link_f"><i class="fas fa-link"></i> <?php echo _("Viewer Friendly Url Link"); ?></label>
                            <div class="input-group <?php echo ($virtual_tour['friendly_url']=='') ? 'disabled' : ''; ?>">
                                <input readonly type="text" class="form-control bg-white" id="link_f" value="<?php echo $link_f . $virtual_tour['friendly_url']; ?>" />
                                <div class="input-group-append">
                                    <button class="btn btn-primary btn-xs" data-clipboard-target="#link_f">
                                        <i class="far fa-clipboard"></i>
                                    </button>
                                    <button onclick="open_qr_code_modal('<?php echo $link_f . $virtual_tour['friendly_url']; ?>');" class="btn btn-secondary btn-xs">
                                        <i class="fas fa-qrcode"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                            <label for="code"><i class="fas fa-code"></i> <?php echo _("Viewer Embed Code"); ?></label>
                            <div class="input-group">
                                <textarea id="code" class="form-control" rows="2"><iframe id="svt_iframe_<?php echo $virtual_tour['code']; ?>" allowfullscreen allow="gyroscope; accelerometer; xr; microphone *" width="100%" height="600px" frameborder="0" scrolling="no" marginheight="0" marginwidth="0" src="<?php echo $link . $virtual_tour['code']; ?>"></iframe></textarea>
                                <div class="input-group-append">
                                    <button class="btn btn-primary btn-xs" data-clipboard-target="#code">
                                        <i class="far fa-clipboard"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <hr>
                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                            <label for="linkl"><i class="fas fa-link"></i> <?php echo _("Landing Link"); ?></label>
                            <div class="input-group">
                                <input readonly type="text" class="form-control bg-white" id="linkl" value="<?php echo $linkl . $virtual_tour['code']; ?>" />
                                <div class="input-group-append">
                                    <button class="btn btn-primary btn-xs" data-clipboard-target="#linkl">
                                        <i class="far fa-clipboard"></i>
                                    </button>
                                    <button onclick="open_qr_code_modal('<?php echo $linkl . $virtual_tour['code']; ?>');" class="btn btn-secondary btn-xs">
                                        <i class="fas fa-qrcode"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                            <label for="linkl_f"><i class="fas fa-link"></i> <?php echo _("Landing Friendly Url Link"); ?></label>
                            <div class="input-group <?php echo ($virtual_tour['friendly_url']=='') ? 'disabled' : ''; ?>">
                                <input readonly type="text" class="form-control bg-white" id="linkl_f" value="<?php echo $linkl_f . $virtual_tour['friendly_url']; ?>" />
                                <div class="input-group-append">
                                    <button class="btn btn-primary btn-xs" data-clipboard-target="#linkl_f">
                                        <i class="far fa-clipboard"></i>
                                    </button>
                                    <button onclick="open_qr_code_modal('<?php echo $linkl_f . $virtual_tour['friendly_url']; ?>');" class="btn btn-secondary btn-xs">
                                        <i class="fas fa-qrcode"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                            <label for="code_f"><i class="fas fa-code"></i> <?php echo _("Landing Embed Code"); ?></label>
                            <div class="input-group">
                                <textarea id="code_f" class="form-control" rows="2"><iframe allowfullscreen allow="gyroscope; accelerometer; xr; microphone *" width="100%" height="600px" frameborder="0" scrolling="no" marginheight="0" marginwidth="0" src="<?php echo $linkl . $virtual_tour['code']; ?>"></iframe></textarea>
                                <div class="input-group-append">
                                    <button class="btn btn-primary btn-xs" data-clipboard-target="#code_f">
                                        <i class="far fa-clipboard"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-12">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-laptop-code"></i> <?php echo _("API Sample Code"); ?></h6>
            </div>
            <div class="card-body">
                <div id="api_sample" style="position: relative;width: 100%;height: 400px;"><?php echo htmlentities('<html>
<head>
    <title>API Sample</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, user-scalable=no, maximum-scale=1, minimum-scale=1">
</head>
<body>
<!-- viewer embed code !-->
<iframe id="svt_iframe_'.$virtual_tour['code'].'" allowfullscreen allow="gyroscope; accelerometer; xr; microphone *" width="100%" height="600px" frameborder="0" scrolling="no" marginheight="0" marginwidth="0" src="'.$link.$virtual_tour['code'].'"></iframe>
<br><br>
<button disabled onclick="goto_room('.$first_room['id'].');">GO TO '.strtoupper($first_room['name']).'</button> <!-- replace the id of the room !-->
<button disabled onclick="goto_next_room();">GO TO NEXT ROOM</button>
<button disabled onclick="goto_prev_room();">GO TO PREV ROOM</button>
<br><br>
<input placeholder="latitude" id="latitude" type="text"> <input placeholder="longitude" id="longitude" type="text">
<button disabled onclick="goto_room_coordinates();">GO TO COORDINATES</button>
<script>
    var id_iframe = "svt_iframe_'.$virtual_tour['code'].'";
    var iframe_svt = document.getElementById(id_iframe).contentWindow;
    window.addEventListener("message", function(evt) {
        if(evt.data.payload=="initialized") {
            //Tour initialized -> put your code here
            var buttons = document.querySelectorAll("button");
            for (var i = 0; i < buttons.length; ++i) {
                buttons[i].disabled = false;
            }
        }
    }, false);
    function goto_room(id_room) {
        //function to go to the room via its id
        iframe_svt.postMessage({"payload":"goto_room","id_room":id_room}, "*");
    }
    function goto_next_room() {
        //function to go to the next room
        iframe_svt.postMessage({"payload":"goto_next_room"}, "*");
    }
    function goto_prev_room() {
        //function to go to the previous room
        iframe_svt.postMessage({"payload":"goto_prev_room"}, "*");
    }
    function goto_room_coordinates() {
        //function to go to nearest room based on given coordinates
        var lat = document.getElementById("latitude").value;
        var lon = document.getElementById("longitude").value;
        iframe_svt.postMessage({"payload":"goto_room_coordinates","coordinates":[lat,lon]}, "*");
    }
</script>
</body>
</html>'); ?></div>
            </div>
        </div>
    </div>
</div>

<div id="modal_qrcode" class="modal" tabindex="-1" role="dialog" data-backdrop="static">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><?php echo _("QR Code"); ?></h5>
            </div>
            <div class="modal-body text-center">
                <i class="fas fa-spin fa-spinner"></i>
                <img style="width: 100%;" src="" />
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
        window.id_user = '<?php echo $id_user; ?>';
        window.id_virtualtour = '<?php echo $id_virtualtour_sel; ?>';
        $(document).ready(function () {
            $('.help_t').tooltip();
            new ClipboardJS('.btn');
            var api_sample = ace.edit('api_sample');
            api_sample.session.setMode("ace/mode/html");
            api_sample.setOption('enableLiveAutocompletion',true);
        });
    })(jQuery); // End of use strict
</script>