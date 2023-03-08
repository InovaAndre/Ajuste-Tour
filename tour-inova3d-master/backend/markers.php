<?php
header('Content-Type: text/html; charset=utf-8');
session_start();
$id_user = $_SESSION['id_user'];
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
    if(isset($_SESSION['id_virtualtour_sel'])) {
        $id_virtualtour_sel = $_SESSION['id_virtualtour_sel'];
        $name_virtualtour_sel = $_SESSION['name_virtualtour_sel'];
    } else {
        $id_virtualtour_sel = $virtual_tours[0]['id'];
        $name_virtualtour_sel = $virtual_tours[0]['name'];
        $_SESSION['id_virtualtour_sel'] = $id_virtualtour_sel;
        $_SESSION['name_virtualtour_sel'] = $name_virtualtour_sel;
    }
    foreach ($virtual_tours as $virtual_tour) {
        $id_virtualtour = $virtual_tour['id'];
        $name_virtualtour = $virtual_tour['name'];
        $author_virtualtour = $virtual_tour['author'];
        $array_list_vt[] = array("id"=>$id_virtualtour,"name"=>$name_virtualtour,"author"=>$author_virtualtour);
    }
}
if(isset($_GET['id_room'])) {
    $id_room = $_GET['id_room'];
} else {
    $id_room = 0;
}
$virtual_tour = get_virtual_tour($id_virtualtour_sel,$id_user);
$code_vt = $virtual_tour['code'];
$icons_library = get_plan_permission($id_user)['enable_icons_library'];
if($user_info['role']=='editor') {
    $editor_permissions = get_editor_permissions($id_user,$id_virtualtour_sel);
    if($editor_permissions['icons_library']==0) {
        $icons_library = 0;
    }
    if($editor_permissions['create_markers']==1) {
        $create_permission=true;
    } else {
        $create_permission=false;
    }
    if($editor_permissions['edit_markers']==1) {
        $edit_permission=true;
    } else {
        $edit_permission=false;
    }
    if($editor_permissions['delete_markers']==1) {
        $delete_permission=true;
    } else {
        $delete_permission=false;
    }
} else {
    $create_permission=true;
    $edit_permission=true;
    $delete_permission=true;
}
?>

<?php include("check_plan.php"); ?>

<div class="d-sm-flex align-items-center justify-content-between mb-3">
    <h1 class="h3 mb-0 text-gray-800"><i class="fas fa-fw fa-caret-square-up text-gray-700"></i> <?php echo _("MARKERS"); ?></h1>
    <?php echo print_virtualtour_selector($array_list_vt,$id_virtualtour_sel); ?>
</div>

<?php if($virtual_tour['external']==1) : ?>
    <div class="card bg-warning text-white shadow mb-4">
        <div class="card-body">
            <?php echo _("You cannot create Markers on an external virtual tour!"); ?>
        </div>
    </div>
    <?php exit; endif; ?>

<div id="plan_marker_msg" class="card bg-warning text-white shadow mb-4 d-none">
    <div class="card-body">
        <?php echo _("You have reached the maximum number of Markers allowed from your plan!")." ".$msg_change_plan; ?>
    </div>
</div>

<div class="row">
    <div class="col-md-12 mb-4">
        <div class="card shadow mb-12">
            <div class="card-body marker_div p-0">
                <div class="col-md-12 p-0">
                    <p id="msg_sel_room" class="text-center mt-2 mb-1"><?php echo _("Select a room first!"); ?></p>
                    <p style="display: none;padding: 15px 15px 0;" id="msg_no_room"><?php echo sprintf(_('No rooms created for this Virtual Tour. Go to %s and create a new one!'),'<a href="index.php?p=rooms">'._("Rooms").'</a>'); ?></p>
                    <div style="position: relative">
                        <div class="div_panorama_container" id="panorama_markers"></div>
                        <div id="rooms_slider_m" class="rooms_slider mb-1 px-4"></div>
                        <div id="action_box">
                            <div class="marker_edit_label"></div>
                            <i title="<?php echo _("MOVE"); ?>" onclick="" class="move_action fa fa-arrows-alt <?php echo (!$edit_permission) ? 'disabled' : ''; ?>"></i>
                            <i title="<?php echo _("EDIT"); ?>" onclick="" class="edit_action fa fa-edit <?php echo (!$edit_permission) ? 'disabled' : ''; ?>"></i>
                            <i title="<?php echo _("DELETE"); ?>" onclick="" class="delete_action fa fa-trash <?php echo (!$delete_permission) ? 'disabled' : ''; ?>"></i>
                            <i title="<?php echo _("GO TO"); ?>" onclick="" class="goto_action fas fa-sign-in-alt"></i>
                        </div>
                        <div id="confirm_edit">
                            <ul style="width: calc(100% - 60px);" class="nav nav-pills justify-content-center mb-1" id="edit-tab" role="tablist">
                                <li class="nav-item">
                                    <a onclick="show_marker_apply_style(false);maximize_box_edit();" class="nav-link active" id="pills-edit-tab" data-toggle="pill" href="#pills-edit" role="tab" aria-controls="pills-edit" aria-selected="true"><i class="fas fa-cog"></i> <?php echo strtoupper(_("Settings")); ?></a>
                                </li>
                                <li class="nav-item">
                                    <a onclick="show_marker_apply_style(true);maximize_box_edit();" class="nav-link" id="pills-style-tab" data-toggle="pill" href="#pills-style" role="tab" aria-controls="pills-style" aria-selected="false"><i class="fas fa-palette"></i> <?php echo strtoupper(_("Style")); ?></a>
                                </li>
                                <i onclick="minimize_box_edit();" class="fas fa-minus minimize_box_edit"></i>
                                <span class="btn_close"><i class="fas fa-times"></i></span>
                            </ul>
                            <div class="tab-content" id="pills-tabContent">
                                <hr>
                                <div class="tab-pane fade show active" id="pills-edit" role="tabpanel" aria-labelledby="pills-edit-tab">
                                    <div class="row">
                                        <div style="margin-bottom: 5px;" class="col-md-6 text-center <?php echo ($demo) ? 'disabled_d':''; ?>">
                                            <p class="mb-0"><?php echo _("Style"); ?></p>
                                            <div class="dropdown">
                                                <button class="btn btn-primary dropdown-toggle" type="button" id="dropdown_marker_style_edit" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                    <i class="fas fa-info-circle"></i> <?php echo _("Icon"); ?>
                                                </button>
                                                <div class="dropdown-menu dropdown-menu-center" aria-labelledby="dropdown_marker_style_edit">
                                                    <a onclick="select_marker_style_edit('icon');" id="btn_edit_style_icon" class="dropdown-item" href="#"><i class="fas fa-info-circle"></i> <?php echo _("Icon"); ?></a>
                                                    <a onclick="select_marker_style_edit('embed_selection');" id="btn_edit_style_embed_selection" class="dropdown-item" href="#"><i class="far fa-square"></i> <?php echo _("Selection Area"); ?></a>
                                                </div>
                                                <button id="btn_change_marker_embed_style" onclick="change_marker_embed_style();" class="btn btn-success disabled"><i class="fas fa-arrow-right"></i> <?php echo _("Change"); ?></button>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div style="margin-bottom: 5px;" class="form-group">
                                                <label><?php echo _("LookAt"); ?> <i title="<?php echo _("moves the view in the direction of the clicked marker"); ?>" class="help_t fas fa-question-circle"></i></label>
                                                <select id="lookat" class="form-control">
                                                    <option id="0"><?php echo _("Disabled"); ?></option>
                                                    <option id="1"><?php echo _("Horizontal only"); ?></option>
                                                    <option id="2"><?php echo _("Horizontal and Vertical"); ?></option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-12">
                                            <div style="margin:0 auto;margin-bottom: 5px;width: 100%;max-width: 400px;" class="form-group">
                                                <label><?php echo _("Room Target"); ?></label>
                                                <select data-live-search="true" onchange="" id="room_target" class="form-control"></select>
                                            </div>
                                        </div>
                                        <div class="col-md-12 mt-1">
                                            <div class="form-group mb-0">
                                                <label><?php echo _("Override Initial Position"); ?> <i title="<?php echo _("Drag the view to set the starting position belongs to this marker. Only works if 'Same Azimuth' is disabled."); ?>" class="help_t fas fa-question-circle"></i></label>&nbsp;
                                                <input id="override_pos_edit" type="checkbox" />
                                            </div>
                                            <div style="width: 100%;max-width: 400px;height: 200px;margin: 0 auto;" id="panorama_pos_edit"></div>
                                        </div>
                                    </div>
                                </div>
                                <div class="tab-pane fade" id="pills-style" role="tabpanel" aria-labelledby="pills-style-tab">
                                    <div class="row">
                                        <div class="col-md-4">
                                            <div style="margin-bottom: 5px;" class="form-group">
                                                <label for="markers_style"><?php echo _("Style"); ?></label>
                                                <select onchange="change_marker_style()" id="marker_style" class="form-control">
                                                    <option id="1"><?php echo _("Icon + Room's Name"); ?></option>
                                                    <option id="2"><?php echo _("Room's Name + Icon"); ?></option>
                                                    <option id="0"><?php echo _("Only Icon"); ?></option>
                                                    <option id="3"><?php echo _("Only Room's Name"); ?></option>
                                                    <option id="4"><?php echo _("Custom Icons Library"); ?></option>
                                                    <option id="5"><?php echo _("Preview Room"); ?></option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div style="margin-bottom: 5px;" class="form-group">
                                                <label for="marker_icon"><?php echo _("Icon"); ?></label><br>
                                                <button class="btn btn-sm btn-primary" type="button" id="GetIconPicker" data-iconpicker-input="input#marker_icon" data-iconpicker-preview="i#marker_icon_preview"><?php echo _("Select Icon"); ?></button>
                                                <input readonly type="hidden" id="marker_icon" name="Icon" value="fas fa-image" required="" placeholder="" autocomplete="off" spellcheck="false">
                                                <div style="vertical-align: middle;" class="icon-preview d-inline-block ml-1" data-toggle="tooltip" title="">
                                                    <i style="font-size: 24px;" id="marker_icon_preview" class="fas fa-image"></i>
                                                </div>
                                            </div>
                                        </div>
                                        <div style="display: none" class="col-md-4">
                                            <div style="margin-bottom: 5px;" class="form-group">
                                                <label for="marker_library_icon"><?php echo _("Library Icon"); ?></label><br>
                                                <button onclick="open_modal_library_icons()" class="btn btn-sm btn-primary" type="button" id="btn_library_icon"><?php echo _("Select Library Icon"); ?></button>
                                                <input type="hidden" id="marker_library_icon" value="0" />
                                                <img id="marker_library_icon_preview" style="display: none;height:30px" src="" />
                                                <div id="marker_library_icon_preview_l" style="display: none;height:30px;vertical-align:middle;"></div>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div style="margin-bottom: 5px;" class="form-group">
                                                <label for="marker_animation"><?php echo _("Animation"); ?></label>
                                                <select onchange="change_marker_animation()" id="marker_animation" class="form-control">
                                                    <option id="none"><?php echo _("None"); ?></option>
                                                    <option id="bounce"><?php echo _("Bounce"); ?></option>
                                                    <option id="flash"><?php echo _("Flash"); ?></option>
                                                    <option id="rubberBand"><?php echo _("Rubberband"); ?></option>
                                                    <option id="shakeX"><?php echo _("Shake X"); ?></option>
                                                    <option id="shakeY"><?php echo _("Shake Y"); ?></option>
                                                    <option id="swing"><?php echo _("Swing"); ?></option>
                                                    <option id="tada"><?php echo _("Tada"); ?></option>
                                                    <option id="wobble"><?php echo _("Wobble"); ?></option>
                                                    <option id="jello"><?php echo _("Jello"); ?></option>
                                                    <option id="heartBeat"><?php echo _("Heartbeat"); ?></option>
                                                    <option id="flip"><?php echo _("Flip"); ?></option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-4">
                                            <div style="margin-bottom: 5px;" class="form-group">
                                                <label id="marker_color_label" for="marker_color"><?php echo _("Color"); ?></label>
                                                <input type="text" id="marker_color" class="form-control" value="#000000" />
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div style="margin-bottom: 5px;" class="form-group">
                                                <label for="marker_background"><?php echo _("Background"); ?></label>
                                                <input type="text" id="marker_background" class="form-control" value="rgba(255,255,255,0.7)" />
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div style="margin-bottom: 5px;" class="form-group">
                                                <label for="marker_border_px"><?php echo _("Border"); ?></label>
                                                <input oninput="change_marker_border_px();" min="0" max="10" type="number" id="marker_border_px" class="form-control" value="3" />
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div style="margin-bottom: 5px;" class="form-group">
                                                <label for="marker_css_class"><?php echo _("CSS Class"); ?></label>
                                                <input type="text" id="marker_css_class" class="form-control" value="" />
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="tooltip_type"><?php echo _("Tooltip Type"); ?></label>
                                                <select onchange="change_tooltip_type_m();" id="tooltip_type" class="form-control">
                                                    <option id="none"><?php echo _("None"); ?></option>
                                                    <option id="room_name"><?php echo _("Target Room's Name"); ?></option>
                                                    <option id="preview"><?php echo _("Target Room's Preview (Rounded)"); ?></option>
                                                    <option id="preview_square"><?php echo _("Target Room's Preview (Squared)"); ?></option>
                                                    <option id="preview_rect"><?php echo _("Target Room's Preview (Rectangular)"); ?></option>
                                                    <option id="text"><?php echo _("Custom Text"); ?></option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="tooltip_text"><?php echo _("Tooltip Text"); ?></label>
                                                <input id="tooltip_text" type="text" class="form-control" />
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <span data-toggle="modal" data-target="#modal_markers_style_apply" style="display:none;" class="btn_apply_style_all btn-primary"><?php echo _("APPLY STYLE TO ALL"); ?>&nbsp;&nbsp;<i class="fas fa-check-double"></i></span>
                                <span class="btn_confirm"><?php echo _("SAVE"); ?>&nbsp;&nbsp;<i class="fas fa-check-circle"></i></span>
                            </div>
                        </div>
                        <div id="confirm_move">
                            <div style="width: calc(100% - 30px);">
                                <b id="msg_drag_marker"><?php echo _("drag the marker to change its position"); ?></b>
                                <b tyle="width: calc(100% - 30px);" style="display:none;" id="msg_drag_embed"><?php echo _("drag the pointers to move and resize the content"); ?></b>
                            </div>
                            <div style="margin-bottom: 5px;" class="form-group">
                                <label style="margin-bottom: 0;"><?php echo _("Perspective"); ?> <i style="font-size:12px;" id="perspective_values"></i></label>
                                <input oninput="" type="range" min="0" max="70" step="1" class="form-control-range" id="rotateX">
                                <input oninput="" type="range" min="-180" max="180" step="1" class="form-control-range" id="rotateZ">
                            </div>
                            <div style="margin-bottom: 5px;" class="form-group">
                                <label style="margin-bottom: 0;"><?php echo _("Size"); ?> <i style="font-size:12px;" id="size_values"></i></label>
                                <input oninput="" type="range" step="0.1" min="0.5" max="2.0" class="form-control-range" id="size_scale">
                            </div>
                            <span class="btn_confirm"><?php echo _("SAVE"); ?>&nbsp;&nbsp;<i class="fas fa-check-circle"></i></span>
                            <span class="btn_close"><i class="fas fa-times"></i></span>
                        </div>
                        <?php if($create_permission) : ?><button title="<?php echo _("ADD MARKER"); ?>" id="btn_add_marker" style="opacity:0;position:absolute;top:10px;right:10px;z-index:10;pointer-events:none;" class="btn btn-circle btn-success"><i class="fas fa-plus"></i></button><?php endif; ?>
                        <button title="<?php echo _("EDIT POIs"); ?>" id="btn_switch_to_poi" style="opacity:0;position:absolute;top:10px;left:10px;z-index:10;pointer-events:none;" class="btn btn-circle btn-primary" onclick=""><i class="fas fa-bullseye"></i></button>
                        <button onclick="open_preview_viewer();" title="<?php echo _("PREVIEW"); ?>" id="btn_preview_modal" style="opacity:0;position:absolute;top:60px;left:10px;z-index:10;pointer-events:none;" class="btn btn-circle btn-primary" onclick=""><i class="fas fa-eye"></i></button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div id="modal_add_marker" class="modal" tabindex="-1" role="dialog" data-backdrop="static">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><?php echo _("Add Marker"); ?></h5>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div id="div_poi_select_style" class="col-md-6 text-center <?php echo ($demo) ? 'disabled_d':''; ?>">
                        <p class="mb-0"><?php echo _("Style"); ?></p>
                        <div class="dropdown">
                            <button class="btn btn-primary dropdown-toggle" type="button" id="dropdown_marker_style" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <i class="fas fa-info-circle"></i> <?php echo _("Icon"); ?>
                            </button>
                            <div class="dropdown-menu dropdown-menu-center" aria-labelledby="dropdown_marker_style">
                                <a onclick="select_marker_style('icon');" id="btn_style_icon" class="dropdown-item" href="#"><i class="fas fa-info-circle"></i> <?php echo _("Icon"); ?></a>
                                <a onclick="select_marker_style('embed_selection');" id="btn_style_embed_selection" class="dropdown-item" href="#"><i class="far fa-square"></i> <?php echo _("Selection Area"); ?></a>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 text-center">
                        <div class="form-group">
                            <label class="mb-0"><?php echo _("LookAt"); ?> <i title="<?php echo _("moves the view in the direction of the clicked marker"); ?>" class="help_t fas fa-question-circle"></i></label>
                            <select id="lookat_add" class="form-control">
                                <option <?php echo ($virtual_tour['markers_default_lookat']==0) ? 'selected' : ''; ?> id="0"><?php echo _("Disabled"); ?></option>
                                <option <?php echo ($virtual_tour['markers_default_lookat']==1) ? 'selected' : ''; ?> id="1"><?php echo _("Horizontal only"); ?></option>
                                <option <?php echo ($virtual_tour['markers_default_lookat']==2) ? 'selected' : ''; ?> id="2"><?php echo _("Horizontal and Vertical"); ?></option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-12 text-center">
                        <div id="room_target_add_div" class="form-group">
                            <label class="mb-0"><?php echo _("Room Target"); ?></label>
                            <select data-live-search="true" onchange="" id="room_target_add" class="form-control"></select>
                        </div>
                    </div>
                    <div class="col-md-12 text-center">
                        <div class="form-group mb-0">
                            <label><?php echo _("Override Initial Position"); ?> <i title="<?php echo _("Drag the view to set the starting position belongs to this marker. Only works if 'Same Azimuth' is disabled."); ?>" class="help_t fas fa-question-circle"></i></label>&nbsp;
                            <input id="override_pos_add" type="checkbox" />
                        </div>
                        <div style="width: 100%;max-width: 400px;height: 200px;margin: 0 auto;" id="panorama_pos_add"></div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button <?php echo ($demo) ? 'disabled':''; ?> id="btn_new_marker" onclick="" type="button" class="btn btn-success"><i class="fas fa-plus"></i> <?php echo _("Add"); ?></button>
                <button type="button" class="btn btn-secondary" data-dismiss="modal"><i class="fas fa-times"></i> <?php echo _("Close"); ?></button>
            </div>
        </div>
    </div>
</div>

<div id="modal_delete_marker" class="modal" tabindex="-1" role="dialog" data-backdrop="static">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><?php echo _("Delete Marker"); ?></h5>
            </div>
            <div class="modal-body">
                <p><?php echo _("Are you sure you want to delete the marker?"); ?></p>
            </div>
            <div class="modal-footer">
                <button <?php echo ($demo) ? 'disabled':''; ?> id="btn_delete_marker" onclick="" type="button" class="btn btn-danger"><i class="fas fa-trash"></i> <?php echo _("Yes, Delete"); ?></button>
                <button type="button" class="btn btn-secondary" data-dismiss="modal"><i class="fas fa-times"></i> <?php echo _("Close"); ?></button>
            </div>
        </div>
    </div>
</div>

<div id="modal_library_icons" class="modal" tabindex="-1" role="dialog" data-backdrop="static">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><?php echo _("Library Icons"); ?></h5>
            </div>
            <div class="modal-body">
                <div class="mb-3 <?php echo ($icons_library==0) ? 'd-none' : ''; ?>">
                    <form action="ajax/upload_icon_image.php" class="dropzone noselect <?php echo ($demo || $disabled_upload) ? 'disabled':''; ?>" id="gallery-dropzone-im"></form>
                </div>
                <div id="list_images_im">
                    <?php echo get_library_icons($id_virtualtour_sel,'marker'); ?>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal"><i class="fas fa-times"></i> <?php echo _("Close"); ?></button>
            </div>
        </div>
    </div>
</div>

<div id="modal_preview" class="modal" tabindex="-1" role="dialog" data-backdrop="static">
    <div class="modal-dialog" style="width: 90% !important; max-width: 90% !important;" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><?php echo _("Preview"); ?></h5>
                <button onclick="close_preview_viewer();" type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">

            </div>
        </div>
    </div>
</div>

<div id="modal_markers_style_apply" class="modal" tabindex="-1" role="dialog" data-backdrop="static">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><?php echo _("Markers Settings"); ?></h5>
            </div>
            <div class="modal-body">
                <p><?php echo _("Are you sure you want to apply these settings to all existing markers by overwriting them?"); ?></p>
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="apply_marker_style"><?php echo _("Style"); ?></label><br>
                            <input type="checkbox" id="apply_marker_style" checked />
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="apply_marker_tooltip_type"><?php echo _("Tooltip Type"); ?></label><br>
                            <input type="checkbox" id="apply_marker_tooltip_type" checked />
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="apply_marker_icon"><?php echo _("Icon"); ?></label><br>
                            <input type="checkbox" id="apply_marker_icon" checked />
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="apply_marker_color"><?php echo _("Color"); ?></label><br>
                            <input type="checkbox" id="apply_marker_color" checked />
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="apply_marker_background"><?php echo _("Background"); ?></label><br>
                            <input type="checkbox" id="apply_marker_background" checked />
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button <?php echo ($demo) ? 'disabled':''; ?> onclick="apply_default_styles('markers_e');" type="button" class="btn btn-success"><i class="fas fa-check"></i> <?php echo _("Yes, Apply"); ?></button>
                <button type="button" class="btn btn-secondary" data-dismiss="modal"><i class="fas fa-times"></i> <?php echo _("Close"); ?></button>
            </div>
        </div>
    </div>
</div>

<script>
    (function($) {
        "use strict"; // Start of use strict
        Dropzone.autoDiscover = false;
        window.id_room_marker = <?php echo $id_room; ?>;
        window.id_room_sel = null;
        window.id_user = '<?php echo $id_user; ?>';
        window.id_virtualtour = '<?php echo $id_virtualtour_sel; ?>';
        window.code_vt = '<?php echo $code_vt; ?>';
        window.markers = null;
        window.markers_initial = null;
        window.rooms_count = 0;
        window.can_create = false;
        window.viewer_initialized = false;
        window.viewer = null;
        window.video_viewer = null;
        window.viewer_pos = null;
        window.is_editing = false;
        window.marker_index_edit = null;
        window.marker_id_edit = null;
        window.panorama_image = '';
        window.currentYaw = 0;
        window.currentPitch = 0;
        window.switched_page = false;
        window.poi_embed_originals_pos = [];
        window.marker_embed_originals_pos = [];
        window.video_embeds = [];
        window.sync_virtual_staging_enabled = false;
        window.sync_poi_embed_enabled = false;
        window.sync_marker_embed_enabled = false;
        window.embed_type_sel = '';
        window.embed_type_current = '';
        window.gallery_dropzone_im = null;
        $(document).ready(function () {
            if("currentYaw" in sessionStorage) {
                window.currentYaw = parseFloat(sessionStorage.getItem('currentYaw'));
                window.currentPitch = parseFloat(sessionStorage.getItem('currentPitch'));
                sessionStorage.setItem('currentYaw','0');
                sessionStorage.setItem('currentPitch','0');
                if(window.currentYaw!=0 && window.id_room_marker!=0) {
                    window.switched_page = true;
                }
            }
            var container_h = $('#content-wrapper').height() - 255;
            $('#panorama_markers').css('height',container_h+'px');
            $('.help_t').tooltip();
            $('#action_box i').tooltip();
            check_plan(window.id_user,'marker');
            if(window.can_create) {
                $('#plan_marker_msg').addClass('d-none');
            } else {
                $('#plan_marker_msg').removeClass('d-none');
            }
            get_rooms(window.id_virtualtour,'marker');
            IconPicker.Init({
                jsonUrl: 'vendor/iconpicker/iconpicker-1.5.0.json',
                searchPlaceholder: '<?php echo _("Search Icon"); ?>',
                showAllButton: '<?php echo _("Show All"); ?>',
                cancelButton: '<?php echo _("Cancel"); ?>',
                noResultsFound: '<?php echo _("No results found."); ?>',
                borderRadius: '20px',
            });
            IconPicker.Run('#GetIconPicker', function(){
                window.markers[marker_index_edit].icon = $('#marker_icon').val();
                render_marker(window.marker_id_edit,window.marker_index_edit);
            });
            window.marker_color_spectrum = $('#marker_color').spectrum({
                type: "text",
                preferredFormat: "hex",
                showAlpha: false,
                showButtons: false,
                allowEmpty: false,
                move: function(color) {
                    window.markers[marker_index_edit].color = color.toHexString();
                    render_marker(window.marker_id_edit,window.marker_index_edit);
                },
                change: function(color) {
                    window.markers[marker_index_edit].color = color.toHexString();
                    render_marker(window.marker_id_edit,window.marker_index_edit);
                }
            });
            window.marker_background_spectrum = $('#marker_background').spectrum({
                type: "text",
                preferredFormat: "rgb",
                showAlpha: true,
                showButtons: false,
                allowEmpty: false,
                move: function(color) {
                    window.markers[marker_index_edit].background = color.toRgbString();
                    render_marker(window.marker_id_edit,window.marker_index_edit);
                },
                change: function(color) {
                    window.markers[marker_index_edit].background = color.toRgbString();
                    render_marker(window.marker_id_edit,window.marker_index_edit);
                }
            });
            $('#btn_add_marker').tooltipster({
                delay: 10,
                hideOnClick: true,
                position: 'left'
            });
            $('#btn_switch_to_poi').tooltipster({
                delay: 10,
                hideOnClick: true,
                position: 'right'
            });
            $('#btn_preview_modal').tooltipster({
                delay: 10,
                hideOnClick: true,
                position: 'right'
            });
            $('.lottie_icon_list').each(function () {
                var id = $(this).attr('data-id');
                var image = $(this).attr('data-image');
                bodymovin.loadAnimation({
                    container: document.getElementById('lottie_icon_'+id),
                    renderer: 'svg',
                    loop: true,
                    autoplay: true,
                    path: '../viewer/icons/'+image,
                    rendererSettings: {
                        progressiveLoad: true,
                    }
                });
            });
        });
        $(window).resize(function () {
            var container_h = $('#content-wrapper').height() - 255;
            $('#panorama_markers').css('height',container_h+'px');
            try {
                $('#video_viewer').css('height',container_h+'px');
            } catch (e) {}
            var poi_embed_count = $('.poi_embed').length;
            if(poi_embed_count>0) {
                setTimeout(function () {
                    adjust_poi_embed_helpers_all();
                },50);
            }
        });
        $(document).mousedown(function(e) {
            var container = $("#action_box");
            if (!container.is(e.target) && container.has(e.target).length === 0) {
                if(!window.is_editing) {
                    $('.custom-hotspot').css('opacity',1);
                    $('.center_helper').show();
                }
                container.hide();
            }
        });

        window.open_modal_library_icons = function () {
            if(window.gallery_dropzone_im==null) {
                window.gallery_dropzone_im = new Dropzone("#gallery-dropzone-im", {
                    url: "ajax/upload_icon_image.php",
                    parallelUploads: 1,
                    maxFilesize: 20,
                    timeout: 120000,
                    dictDefaultMessage: "<?php echo _("Drop files or click here to upload"); ?>",
                    dictFallbackMessage: "<?php echo _("Your browser does not support drag'n'drop file uploads."); ?>",
                    dictFallbackText: "<?php echo _("Please use the fallback form below to upload your files like in the olden days."); ?>",
                    dictFileTooBig: "<?php echo sprintf(_("File is too big (%sMiB). Max filesize: %sMiB."),'{{filesize}}','{{maxFilesize}}'); ?>",
                    dictInvalidFileType: "<?php echo _("You can't upload files of this type."); ?>",
                    dictResponseError: "<?php echo sprintf(_("Server responded with %s code."),'{{statusCode}}'); ?>",
                    dictCancelUpload: "<?php echo _("Cancel upload"); ?>",
                    dictCancelUploadConfirmation: "<?php echo _("Are you sure you want to cancel this upload?"); ?>",
                    dictRemoveFile: "<?php echo _("Remove file"); ?>",
                    dictMaxFilesExceeded: "<?php echo _("You can not upload any more files."); ?>",
                    acceptedFiles: 'image/*,application/json'
                });
                window.gallery_dropzone_im.on("addedfile", function(file) {
                    $('#list_images_im').addClass('disabled');
                });
                window.gallery_dropzone_im.on("success", function(file,rsp) {
                    add_image_to_icon_m(id_virtualtour,rsp,'marker_h');
                });
                window.gallery_dropzone_im.on("queuecomplete", function() {
                    $('#list_images_im').removeClass('disabled');
                    window.gallery_dropzone_im.removeAllFiles();
                });
            }
            $('#modal_library_icons').modal('show');
        }
    })(jQuery); // End of use strict
</script>