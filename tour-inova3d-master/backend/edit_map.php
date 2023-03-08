<?php
session_start();
require_once("functions.php");
$id_map = $_GET['id'];
$map = get_map($id_map,$_SESSION['id_user']);
$id_virtualtour = $map['id_virtualtour'];
if(isset($_SESSION['id_room_point_sel'])) {
    $id_room_point_sel = $_SESSION['id_room_point_sel'];
    unset($_SESSION['id_room_point_sel']);
} else {
    $id_room_point_sel = '';
}
$map_type = $map['map_type'];
switch ($map_type) {
    case 'map':
        $hide_map_field = "";
        $hide_floorplan_field = "d-none";
        break;
    case 'floorplan':
        $hide_map_field = "d-none";
        $hide_floorplan_field = "";
        break;
}
if($user_info['role']=='editor') {
    $editor_permissions = get_editor_permissions($_SESSION['id_user'],$map['id_virtualtour']);
    if($editor_permissions['edit_maps']==0) {
        $map=false;
    }
}
$settings = get_settings();
?>

<?php if(!$map): ?>
    <div class="text-center">
        <div class="error mx-auto" data-text="401">401</div>
        <p class="lead text-gray-800 mb-5"><?php echo _("Permission denied"); ?></p>
        <p class="text-gray-500 mb-0"><?php echo _("It looks like that you do not have permission to access this page"); ?></p>
        <a href="index.php?p=dashboard">← <?php echo _("Back to Dashboard"); ?></a>
    </div>
<?php die(); endif; ?>

<div class="d-sm-flex align-items-center justify-content-between mb-3">
    <h1 class="h3 mb-0 text-gray-800"><i class="fas fa-fw fa-map-marked-alt text-gray-700"></i> <?php echo _("EDIT MAP"); ?></h1>
    <a id="save_btn" href="#" onclick="save_map_settings();return false;" class="btn btn-sm btn-success btn-icon-split mb-2 <?php echo ($demo) ? 'disabled':''; ?>">
    <span class="icon text-white-50">
      <i class="far fa-circle"></i>
    </span>
        <span class="text"><?php echo _("SAVE"); ?></span>
    </a>
</div>

<div class="row">
    <div class="col-md-12">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-cog"></i> <?php echo _("Settings"); ?></h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="map_name"><?php echo _("Name"); ?></label>
                            <input type="text" id="map_name" class="form-control" value="<?php echo $map['name']; ?>" />
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="point_color"><?php echo _("Point Color"); ?></label>
                            <input type="text" id="point_color" class="form-control" value="<?php echo $map['point_color']; ?>" />
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="point_size"><?php echo _("Point Size"); ?></label>
                            <input onchange="adjust_points_size();" type="number" id="point_size" class="form-control" value="<?php echo $map['point_size']; ?>" />
                        </div>
                    </div>
                    <div class="col-md-4 <?php echo $hide_floorplan_field; ?>">
                        <div class="form-group">
                            <label for="north_degree"><?php echo _("North"); ?></label>
                            <input oninput="adjust_map_north();" min="0" max="360" step="1" type="range" id="north_degree" class="form-control" value="<?php echo $map['north_degree']; ?>" />
                        </div>
                    </div>
                    <div class="col-md-4 <?php echo $hide_floorplan_field; ?>">
                        <div class="form-group">
                            <label for="width_d"><?php echo _("Width (Desktop)"); ?></label>
                            <div class="input-group">
                                <input type="number" id="width_d" class="form-control" value="<?php echo $map['width_d']; ?>" />
                                <div class="input-group-append">
                                    <span class="input-group-text">px</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 <?php echo $hide_floorplan_field; ?>">
                        <div class="form-group">
                            <label for="width_m"><?php echo _("Width (Mobile)"); ?></label>
                            <div class="input-group">
                                <input type="number" id="width_m" class="form-control" value="<?php echo $map['width_m']; ?>" />
                                <div class="input-group-append">
                                    <span class="input-group-text">px</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 <?php echo $hide_floorplan_field; ?>">
                        <div class="form-group">
                            <label for="info_link"><?php echo _("Info - Link"); ?> <i title="<?php echo _("displays an information button on the floorplan that calls this link"); ?>" class="help_t fas fa-question-circle"></i></label>
                            <input type="text" id="info_link" class="form-control" value="<?php echo $map['info_link']; ?>" />
                        </div>
                    </div>
                    <div class="col-md-4 <?php echo $hide_floorplan_field; ?>">
                        <div class="form-group">
                            <label for="info_type"><?php echo _("Info - Open in"); ?></label>
                            <select id="info_type" class="form-control">
                                <option <?php echo ($map['info_type']=='blank') ? 'selected' : ''; ?> id="blank"><?php echo _("New Window"); ?></option>
                                <option <?php echo ($map['info_type']=='iframe') ? 'selected' : ''; ?> id="iframe"><?php echo _("Modal"); ?></option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-4 <?php echo $hide_floorplan_field; ?>">
                        <div class="form-group">
                            <label><?php echo _("Default Room"); ?> <i title="<?php echo _("when you change floorplan this room will be displayed automatically"); ?>" class="help_t fas fa-question-circle"></i></label>
                            <select data-live-search="true" onchange="" id="room_default" class="form-control"></select>
                        </div>
                    </div>
                    <div class="col-md-4 <?php echo $hide_map_field; ?>">
                        <div class="form-group">
                            <label for="zoom_level"><?php echo _("Zoom Level"); ?> <i title="<?php echo _("initial map zoom level (lower 1 - higher 20)"); ?>" class="help_t fas fa-question-circle"></i></label><br>
                            <select id="zoom_level" class="form-control">
                                <option <?php echo ($map['zoom_level']==0) ? 'selected' : ''; ?> id="0"><?php echo _("Fit all points"); ?></option>
                                <option <?php echo ($map['zoom_level']==1) ? 'selected' : ''; ?> id="1">1</option>
                                <option <?php echo ($map['zoom_level']==2) ? 'selected' : ''; ?> id="2">2</option>
                                <option <?php echo ($map['zoom_level']==3) ? 'selected' : ''; ?> id="3">3</option>
                                <option <?php echo ($map['zoom_level']==4) ? 'selected' : ''; ?> id="4">4</option>
                                <option <?php echo ($map['zoom_level']==5) ? 'selected' : ''; ?> id="5">5</option>
                                <option <?php echo ($map['zoom_level']==6) ? 'selected' : ''; ?> id="6">6</option>
                                <option <?php echo ($map['zoom_level']==7) ? 'selected' : ''; ?> id="7">7</option>
                                <option <?php echo ($map['zoom_level']==8) ? 'selected' : ''; ?> id="8">8</option>
                                <option <?php echo ($map['zoom_level']==9) ? 'selected' : ''; ?> id="9">9</option>
                                <option <?php echo ($map['zoom_level']==10) ? 'selected' : ''; ?> id="10">10</option>
                                <option <?php echo ($map['zoom_level']==11) ? 'selected' : ''; ?> id="11">11</option>
                                <option <?php echo ($map['zoom_level']==12) ? 'selected' : ''; ?> id="12">12</option>
                                <option <?php echo ($map['zoom_level']==13) ? 'selected' : ''; ?> id="13">13</option>
                                <option <?php echo ($map['zoom_level']==14) ? 'selected' : ''; ?> id="14">14</option>
                                <option <?php echo ($map['zoom_level']==15) ? 'selected' : ''; ?> id="15">15</option>
                                <option <?php echo ($map['zoom_level']==16) ? 'selected' : ''; ?> id="16">16</option>
                                <option <?php echo ($map['zoom_level']==17) ? 'selected' : ''; ?> id="17">17</option>
                                <option <?php echo ($map['zoom_level']==18) ? 'selected' : ''; ?> id="18">18</option>
                                <option <?php echo ($map['zoom_level']==19) ? 'selected' : ''; ?> id="19">19</option>
                                <option <?php echo ($map['zoom_level']==20) ? 'selected' : ''; ?> id="20">20</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-4 <?php echo $hide_map_field; ?>">
                        <div class="form-group">
                            <label for="zoom_to_point"><?php echo _("Zoom to Point"); ?> <i title="<?php echo _("when the point is clicked, the map is zoomed in on it"); ?>" class="help_t fas fa-question-circle"></i></label><br>
                            <input type="checkbox" id="zoom_to_point" <?php echo ($map['zoom_to_point']) ? 'checked' : ''; ?> />
                        </div>
                    </div>
                    <div class="col-md-4 <?php echo $hide_map_field; ?>">
                        <div class="form-group">
                            <label for="default_view"><?php echo _("Default view"); ?></label><br>
                            <select id="default_view" class="form-control">
                                <option <?php echo ($map['default_view']=='street') ? 'selected' : ''; ?> id="street"><?php echo _("Street"); ?></option>
                                <option <?php echo ($map['default_view']=='satellite') ? 'selected' : ''; ?> id="satellite"><?php echo _("Satellite"); ?></option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary"><i class="far fa-map"></i> <?php echo _("Map Image"); ?> <i style="font-size: 12px">(<?php echo _("click on point to edit, drag to change position"); ?>)</i></h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-12">
                        <p class="<?php echo $hide_floorplan_field; ?>" style="display: none" id="msg_load_map"><i class="fas fa-spin fa-circle-notch"></i>&nbsp; <?php echo _("Loading map image"); ?> ...</p>
                        <div style="position: relative">
                            <img class="<?php echo $hide_floorplan_field; ?>" id="map_image" style="width: 100%" src="" />
                            <div class="<?php echo $hide_floorplan_field; ?>" id="map_compass">
                                <img style="transform: rotate(<?php echo $map['north_degree']; ?>deg)" src="img/north.png" />
                            </div>
                            <div class="<?php echo $hide_floorplan_field; ?>" style="position: absolute;top:0;left:0;" id="pointers_div"></div>
                            <div class="<?php echo $hide_map_field; ?>" id="map_tour_div"></div>
                            <button data-toggle="modal" data-target="#modal_add_map_point" id="btn_add_point" style="opacity:0;position:absolute;top:10px;right:10px;z-index:999" class="btn btn-circle btn-primary d-inline-block float-right"><i class="fas fa-plus"></i></button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="col-md-12">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-map-marker-alt"></i> <?php echo _("Map Point"); ?></h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div id="msg_select_point" class="col-md-12">
                            <p><?php echo _("Select point from map or add a new one"); ?></p>
                        </div>
                        <div class="col-md-12 point_settings" style="display: none">
                            <img id="preview_room_image_map" src="" style="width:100%;" />
                        </div>
                        <div class="col-md-12 point_settings" style="display: none">
                            <div class="form-group">
                                <label><?php echo _("Room Target"); ?></label>
                                <input readonly type="text" id="room_target" class="form-control bg-white" value="" >
                            </div>
                        </div>
                        <div class="col-md-12 point_settings" style="display: none">
                            <div class="form-group">
                                <label><?php echo _("Position"); ?></label>
                                <input readonly type="text" id="point_pos" class="form-control bg-white" value="" >
                            </div>
                        </div>
                        <div class="col-md-12 point_settings" style="display: none">
                            <a style="pointer-events: none" id="save_btn" href="#" class="btn btn-block btn-success btn-icon-split mb-2">
                            <span class="icon text-white-50">
                              <i class="far fa-circle"></i>
                            </span>
                                <span class="text"><?php echo _("AUTO SAVE"); ?></span>
                            </a>
                            <button <?php echo ($demo) ? 'disabled':''; ?> id="btn_delete_point" onclick="" class="btn btn-block btn-danger"><?php echo _("DELETE"); ?></button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div id="modal_add_map_point" class="modal" tabindex="-1" role="dialog" data-backdrop="static">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><?php echo _("New Map Point"); ?></h5>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                            <label><?php echo _("Select Room"); ?></label>
                            <select onchange="change_preview_room_image_map(null);" data-live-search="true" id="room_select" class="form-control"></select>
                        </div>
                    </div>
                    <div class="col-md-12 text-center">
                        <img style="display: none" class="preview_room_target" src="" />
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button id="btn_add_map_point" onclick="add_map_point()" type="button" class="btn btn-success"><i class="fas fa-plus"></i> <?php echo _("Create"); ?></button>
                <button type="button" class="btn btn-secondary" data-dismiss="modal"><i class="fas fa-times"></i> <?php echo _("Close"); ?></button>
            </div>
        </div>
    </div>
</div>

<div id="modal_delete_map_point" class="modal" tabindex="-1" role="dialog" data-backdrop="static">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><?php echo _("Delete Map Point"); ?></h5>
            </div>
            <div class="modal-body">
                <p><?php echo _("Are you sure you want to delete the map point?"); ?></p>
            </div>
            <div class="modal-footer">
                <button <?php echo ($demo) ? 'disabled':''; ?> id="btn_delete_map_point" onclick="" type="button" class="btn btn-danger"><i class="fas fa-trash"></i> <?php echo _("Yes, Delete"); ?></button>
                <button type="button" class="btn btn-secondary" data-dismiss="modal"><i class="fas fa-times"></i> <?php echo _("Close"); ?></button>
            </div>
        </div>
    </div>
</div>

<script>
    (function($) {
        "use strict"; // Start of use strict
        window.id_user = '<?php echo $id_user; ?>';
        window.id_virtualtour = '<?php echo $id_virtualtour; ?>';
        window.id_room_point_sel = '<?php echo $id_room_point_sel; ?>';
        window.map_points = null;
        window.id_room_sel = null;
        window.id_map_sel = '<?php echo $id_map; ?>';
        window.ratio_w = 1;
        window.ratio_h = 1;
        window.point_color_spectrum = null;
        window.point_size = "<?php echo $map['point_size']; ?>";
        window.point_color = "<?php echo $map['point_color']; ?>";
        window.map_type = "<?php echo $map['map_type']; ?>";
        window.id_room_default = "<?php echo $map['id_room_default']; ?>";
        window.map_tour_l = null;
        window.map_need_save = false;
        var street_basemap_url = '<?php echo $settings['leaflet_street_basemap']; ?>';
        var street_subdomain = '<?php echo $settings['leaflet_street_subdomain']; ?>';
        var street_maxzoom = '<?php echo $settings['leaflet_street_maxzoom']; ?>';
        var satellite_basemap_url = '<?php echo $settings['leaflet_satellite_basemap']; ?>';
        var satellite_subdomain = '<?php echo $settings['leaflet_satellite_subdomain']; ?>';
        var satellite_maxzoom = '<?php echo $settings['leaflet_satellite_maxzoom']; ?>';
        $(document).ready(function () {
            $('.help_t').tooltip();
            if(window.map_type=='floorplan') {
                get_rooms(id_virtualtour,'map');
            }
            window.point_color_spectrum = $('#point_color').spectrum({
                type: "text",
                preferredFormat: "hex",
                showAlpha: false,
                showButtons: false,
                allowEmpty: false,
                move: function(color) {
                    $('.pointer').css('background-color',color.toHexString());
                    $('.map_tour_icon').css('border-color',color.toHexString());
                },
                change: function(color) {
                    $('.pointer').css('background-color',color.toHexString());
                    $('.map_tour_icon').css('border-color',color.toHexString());
                }
            });
            if(sessionStorage.getItem('add_point')==true) {
                $("#content-wrapper").animate({ scrollTop: $(document).height() }, 200);
                sessionStorage.setItem('add_point',false);
            }
            if(window.map_type=='map') {
                var street_subdomain_t = street_subdomain.split(",");
                var street_maxzoom_t = parseInt(street_maxzoom);
                if(street_subdomain!='') {
                    var street_basemap = L.tileLayer(street_basemap_url,{
                        maxZoom: street_maxzoom_t,
                        subdomains: street_subdomain_t
                    });
                } else {
                    var street_basemap = L.tileLayer(street_basemap_url,{
                        maxZoom: street_maxzoom_t
                    });
                }
                var satellite_subdomain_t = satellite_subdomain.split(",");
                var satellite_maxzoom_t = parseInt(satellite_maxzoom);
                if(satellite_subdomain!='') {
                    var satellite_basemap = L.tileLayer(satellite_basemap_url,{
                        maxZoom: satellite_maxzoom_t,
                        subdomains: satellite_subdomain_t
                    });
                } else {
                    var satellite_basemap = L.tileLayer(satellite_basemap_url,{
                        maxZoom: satellite_maxzoom_t
                    });
                }
                window.map_tour_l = L.map('map_tour_div', {
                    layers: [street_basemap]
                }).setView([0,0], 2);
                var baseMaps = {
                    "Street": street_basemap,
                    "Satellite": satellite_basemap
                };
                L.control.layers(baseMaps, {}, {position: 'bottomleft'}).addTo(map_tour_l);
                $('#map_tour_div').append('<i style="color:black;z-index:999;" class="fas fa-dot-circle center_helper"></i>');
            }
            get_map(id_virtualtour,id_map_sel);
        });
        $(window).resize(function () {
            if(window.map_type=='floorplan') {
                try {
                    adjust_points_position();
                } catch(e) {}
            }
        });

        $("input").change(function(){
            window.map_need_save = true;
        });

        $(window).on('beforeunload', function(){
            if(window.map_need_save) {
                var c=confirm();
                if(c) return true; else return false;
            }
        });
    })(jQuery); // End of use strict
</script>