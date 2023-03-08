<?php
session_start();
require_once("functions.php");
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
$virtualtour = get_virtual_tour($id_virtualtour_sel,$id_user);
$json_dollhouse = $virtualtour['dollhouse'];
$dollhouse = get_plan_permission($id_user)['enable_dollhouse'];
if($user_info['role']=='editor') {
    $editor_permissions = get_editor_permissions($_SESSION['id_user'],$id_virtualtour_sel);
    if($editor_permissions['edit_3d_view']==0) {
        $dollhouse=false;
    }
}
$rooms = json_encode(get_rooms_3d_view($id_virtualtour_sel));
$show_in_ui = $virtualtour['show_dollhouse'];
?>

<?php if($user_info['plan_status']=='expired') : ?>
    <div class="card bg-warning text-white shadow mb-4">
        <div class="card-body">
            <?php echo sprintf(_('Your "%s" plan has expired!'),$user_info['plan']); ?>
        </div>
    </div>
<?php exit; endif; ?>

<div class="d-sm-flex align-items-center justify-content-between mb-3">
<h1 class="h3 mb-0 text-gray-800"><i class="fas fa-fw fa-cube text-gray-700"></i> <?php echo _("EDITOR 3D VIEW"); ?> <i style="font-size:12px;vertical-align:middle;color:<?php echo ($show_in_ui>0)?'green':'orange'; ?>" <?php echo ($show_in_ui==0)?'title="'._("Not visible in the tour, enable it in the Editor UI").'"':''; ?> class="<?php echo ($show_in_ui==0)?'help_t':''; ?> show_in_ui fas fa-circle"></i></h1>
    <?php echo print_virtualtour_selector($array_list_vt,$id_virtualtour_sel); ?>
</div>

<?php if(!$dollhouse): ?>
    <div class="text-center">
        <div class="error mx-auto" data-text="401">401</div>
        <p class="lead text-gray-800 mb-5"><?php echo _("Permission denied"); ?></p>
        <p class="text-gray-500 mb-0"><?php echo _("It looks like that you do not have permission to access this page"); ?></p>
        <a href="index.php?p=dashboard">← <?php echo _("Back to Dashboard"); ?></a>
    </div>
<?php die(); endif; ?>

<?php if($virtualtour['external']==1) : ?>
    <div class="card bg-warning text-white shadow mb-4">
        <div class="card-body">
            <?php echo _("You cannot edit 3D View on an external virtual tour!"); ?>
        </div>
    </div>
<?php exit; endif; ?>

<div class="row">
    <div class="col-md-12">
        <div class="card shadow mb-2">
            <div class="card-body p-0 position-relative">
                <div class="row p-1">
                    <div class="col-md-12">
                        <button id="btn_add_room" data-toggle="modal" data-target="#modal_add_room_dollhouse" class="btn btn-sm btn-outline-secondary"><i class="fas fa-plus-square"></i> <?php echo _("Add Room"); ?></button>
                        <button id="btn_remove_room" onclick="remove_room_dollhouse();" class="btn btn-sm btn-outline-secondary disabled"><i class="fas fa-minus-square"></i> <?php echo _("Remove Room"); ?></button>
                        <button onclick="show_levels_gui();" class="btn btn-sm btn-outline-secondary"><i class="fas fa-layer-group"></i> <?php echo _("Levels"); ?></button>
                        <button onclick="show_settings_gui();" class="btn btn-sm btn-outline-secondary"><i class="fas fa-cog"></i> <?php echo _("Settings"); ?></button>
                        <a id="save_btn" href="#" onclick="save_dollhouse();" class="btn btn-sm btn-success btn-icon-split float-right <?php echo ($demo) ? 'disabled':''; ?>">
                            <span class="icon text-white-50">
                              <i class="far fa-circle"></i>
                            </span>
                            <span class="text"><?php echo _("SAVE"); ?></span>
                        </a>
                    </div>
                </div>
                <div class="row p-0 m-0">
                    <div style="min-height: 80px" class="col-md-12 p-0 m-0">
                        <div id="gui_dollhouse"></div>
                        <div id="container_dollhouse"></div>
                        <select style="display: block" onchange="select_level_dollhouse();" class="select_level_dollhouse">
                            <option selected id="all"><?php echo _("All"); ?></option>
                            <option id="0"><?php echo _("Level"); ?> 0</option>
                            <option id="1"><?php echo _("Level"); ?> 1</option>
                            <option id="2"><?php echo _("Level"); ?> 2</option>
                            <option id="3"><?php echo _("Level"); ?> 3</option>
                            <option id="4"><?php echo _("Level"); ?> 4</option>
                            <option id="5"><?php echo _("Level"); ?> 5</option>
                        </select>
                        <div class="info_dollhouse">
                            <b><?php echo _("Orbit"); ?></b> - <?php echo _("Left mouse"); ?><br><b><?php echo _("Zoom"); ?></b> - <?php echo _("Middle mouse or mousewheel"); ?><br><b><?php echo _("Pan"); ?></b> - <?php echo _("Right mouse or left mouse + ctrl/meta/shiftKey"); ?><br><b><?php echo _("Select room"); ?></b> - <?php echo _("Double click"); ?>
                        </div>
                        <i onclick="toggle_dollhouse_help();" class="help_dollhouse fas fa-question-circle"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div id="modal_add_room_dollhouse" class="modal" tabindex="-1" role="dialog" data-backdrop="static">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><?php echo _("Add Room"); ?></h5>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                            <label><?php echo _("Select Room"); ?></label>
                            <select onchange="change_preview_room_image(null);" data-live-search="true" id="room_select" class="form-control"></select>
                        </div>
                    </div>
                    <div class="col-md-12 text-center">
                        <img style="display: none" class="preview_room_target" src="" />
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button onclick="add_room_dollhouse();" type="button" class="btn btn-success <?php echo ($demo) ? 'disabled':''; ?>"><i class="fas fa-plus"></i> <?php echo _("Add"); ?></button>
                <button type="button" class="btn btn-secondary" data-dismiss="modal"><i class="fas fa-times"></i> <?php echo _("Close"); ?></button>
            </div>
        </div>
    </div>
</div>

<style>
    .lil-gui {
        --width: 300px;
    }
    .lil-gui.root {
        position: absolute;
        top: 0;
        right: 0;
        max-height: 100%;
    }
</style>

<script>
    (function($) {
        "use strict"; // Start of use strict
        window.id_user = '<?php echo $id_user; ?>';
        window.id_virtualtour = '<?php echo $id_virtualtour_sel; ?>';
        window.dollhouse_need_save = false;
        var json_dollhouse = '<?php echo $json_dollhouse; ?>';
        var json_rooms = '<?php echo $rooms; ?>';
        var container_dollhouse, camera_dollhouse, scene_dollhouse,  renderer_dollhouse, controls_dollhouse, transforms_dollhouse, domEvents_dollhouse, camera_pos_dollhouse, group_rooms_dollhouse = [];
        var array_rooms = [], rooms_dollhouse = [], levels_dollhouse = [], settings_dollhouse = [], textures_dollhouse = [], meshes_dollhouse = [], geometries_dollhouse=[], pointers_c_dollhouse=[], pointers_t_dollhouse=[];
        var GUI = lil.GUI, gui_dollhouse, gui_levels, gui_settings, current_index=0, gui_parameters = [];
        var params_gui_dollhouse = {
            offsetX: 0,
            offsetY: 0,
            offsetZ: 0,
            centerX: 0,
            centerY: 0,
            centerZ: 0,
            rotation: 0,
            x: 0,
            z: 0,
            width: 0,
            height: 0,
            depth: 0,
            cube_face_top: true,
            cube_face_bottom: true,
            cube_face_left: true,
            cube_face_right: true,
            cube_face_front: true,
            cube_face_back: true,
            level: 0,
            level_0_name: 'Level 0',
            level_0_y_pos: 0,
            level_1_name: 'Level 1',
            level_1_y_pos: 270,
            level_2_name: 'Level 2',
            level_2_y_pos: 540,
            level_3_name: 'Level 3',
            level_3_y_pos: 810,
            level_4_name: 'Level 4',
            level_4_y_pos: 1080,
            level_5_name: 'Level 5',
            level_5_y_pos: 1350,
            zoom_in: 1200,
            zoom_out: 800,
            pointer_visible: true,
            pointer_x: 0,
            pointer_z: 0,
            pointer_color: '#ffffff',
            pointer_color_active: '#000000',
            background_color: '#000000',
            background_opacity: 0.85,
            camera_position: function() { get_camera_position(); }
        };

        $(document).ready(function () {
            $('.help_t').tooltip();
            var container_h = $('#content-wrapper').height() - 200;
            $('#container_dollhouse').css('height',container_h+'px');
            initialize_dollhouse();
        });
        $(window).resize(function () {
            var container_h = $('#content-wrapper').height() - 200;
            $('#container_dollhouse').css('height',container_h+'px');
        });

        window.change_preview_room_image = function (id_room_sel) {
            if(id_room_sel==null) {
                id_room_sel = $('#room_select option:selected').attr('id');
            }
            jQuery.each(array_rooms, function(index, room) {
                var id_room = room.id;
                if(id_room==id_room_sel) {
                    var room_image = room.panorama_image;
                    $('.preview_room_target').attr('src','../viewer/panoramas/thumb/'+room_image);
                    $('.preview_room_target').show();
                    return;
                }
            });
        }

        function populate_room_select() {
            try {
                $('#room_select').selectpicker('destroy');
            } catch (e) {}
            var select_room_options = '';
            for(var i=0;i<array_rooms.length;i++) {
                var exist = false;
                for(var k=0;k<rooms_dollhouse.length;k++) {
                    if(rooms_dollhouse[k].id==array_rooms[i].id) {
                        if(rooms_dollhouse[k].removed==0) exist = true;
                    }
                }
                if(!exist) select_room_options += '<option id="'+array_rooms[i].id+'">'+array_rooms[i].name+'</option>';
            }
            if(select_room_options=='') {
                $('#btn_add_room').addClass('disabled');
            }
            $('#room_select').html(select_room_options).promise().done(function () {
                $('#room_select').selectpicker('refresh');
                change_preview_room_image(null);
            });
        }

        function initialize_dollhouse() {
            array_rooms = JSON.parse(json_rooms);
            for(var k=0;k<array_rooms.length;k++) {
                array_rooms[k].id = parseInt(array_rooms[k].id);
            }
            var rooms_to_remove = [];
            if(json_dollhouse!='') {
                var array_dollhouse = JSON.parse(json_dollhouse);
                camera_pos_dollhouse = array_dollhouse.camera;
                rooms_dollhouse = array_dollhouse.rooms;
                levels_dollhouse = array_dollhouse.levels;
                settings_dollhouse = array_dollhouse.settings;
                for(var k=0;k<rooms_dollhouse.length;k++) {
                    rooms_dollhouse[k].id = parseInt(rooms_dollhouse[k].id);
                    var rooms_exist = false;
                    for(var l=0;l<array_rooms.length;l++) {
                        if(array_rooms[l].id==rooms_dollhouse[k].id) {
                            rooms_exist = true;
                        }
                    }
                    if(!rooms_exist) {
                        rooms_to_remove.push(k);
                    } else {
                        rooms_dollhouse[k].removed=0;
                        if(rooms_dollhouse[k].pointer_visible === undefined) {
                            rooms_dollhouse[k].pointer_visible=true;
                        }
                        if(rooms_dollhouse[k].cube_face_top === undefined) {
                            rooms_dollhouse[k].cube_face_top=true;
                        }
                        if(rooms_dollhouse[k].cube_face_bottom === undefined) {
                            rooms_dollhouse[k].cube_face_bottom=true;
                        }
                        if(rooms_dollhouse[k].cube_face_left === undefined) {
                            rooms_dollhouse[k].cube_face_left=true;
                        }
                        if(rooms_dollhouse[k].cube_face_right === undefined) {
                            rooms_dollhouse[k].cube_face_right=true;
                        }
                        if(rooms_dollhouse[k].cube_face_front === undefined) {
                            rooms_dollhouse[k].cube_face_front=true;
                        }
                        if(rooms_dollhouse[k].cube_face_back === undefined) {
                            rooms_dollhouse[k].cube_face_back=true;
                        }
                    }
                }
                for(var m=0;m<rooms_to_remove.length;m++) {
                    rooms_dollhouse.splice(rooms_to_remove[m],1);
                }
            }
            for(var i=0; i<=5; i++) {
                if(levels_dollhouse[i]===undefined) {
                    levels_dollhouse[i] = {};
                    levels_dollhouse[i].id = i;
                    levels_dollhouse[i].name = params_gui_dollhouse['level_'+i+'_name'];
                    levels_dollhouse[i].y_pos = params_gui_dollhouse['level_'+i+'_y_pos'];
                }
            }
            if(settings_dollhouse.length==0 || settings_dollhouse===undefined) {
                settings_dollhouse={};
                settings_dollhouse.zoom_in = 1200;
                settings_dollhouse.zoom_out = 800;
                settings_dollhouse.pointer_color = 'ffffff';
                settings_dollhouse.pointer_color_active = '000000';
                settings_dollhouse.background_color = '000000';
                settings_dollhouse.background_opacity = 0.85;
            }
            populate_room_select();
            container_dollhouse = document.getElementById('container_dollhouse');
            camera_dollhouse = new THREE.PerspectiveCamera( 75, container_dollhouse.offsetWidth / container_dollhouse.offsetHeight, 5, 100000 );
            camera_dollhouse.position.z = 500;
            camera_dollhouse.position.y = 1200;
            scene_dollhouse = new THREE.Scene();
            renderer_dollhouse = new THREE.WebGLRenderer({
                alpha: true,
                antialias: true
            });
            var background_color = Number("0x"+settings_dollhouse.background_color);
            var background_opacity = settings_dollhouse.background_opacity;
            renderer_dollhouse.setClearColor(new THREE.Color(background_color));
            renderer_dollhouse.setClearAlpha(background_opacity);
            renderer_dollhouse.setPixelRatio(1);
            renderer_dollhouse.setSize( container_dollhouse.offsetWidth, container_dollhouse.offsetHeight );
            container_dollhouse.appendChild( renderer_dollhouse.domElement );
            controls_dollhouse = new THREE.OrbitControls(camera_dollhouse, renderer_dollhouse.domElement);
            controls_dollhouse.enableDamping = true;
            controls_dollhouse.dampingFactor = 0.1;
            controls_dollhouse.minDistance = 100;
            controls_dollhouse.maxDistance = 5000;
            transforms_dollhouse = new THREE.TransformControls( camera_dollhouse, renderer_dollhouse.domElement );
            transforms_dollhouse.showY = false;
            transforms_dollhouse.addEventListener( 'dragging-changed', function ( event ) {
                controls_dollhouse.enabled = ! event.value;
                change_transform_position();
            });
            transforms_dollhouse.addEventListener( 'change', function ( event ) {
                change_transform_position();
            });
            domEvents_dollhouse = new THREEx.DomEvents(camera_dollhouse, renderer_dollhouse.domElement);
            var gridHelper = new THREE.GridHelper( 20000, 100 );
            scene_dollhouse.add(gridHelper);
            window.addEventListener( 'resize', onWindowResize_dollhouse, false );
            if(json_dollhouse!='') {
                setTimeout(function () {
                    loading_dollhouse();
                },100);
            }
            animate_dollhouse();
        }

        window.get_camera_position = function() {
            camera_pos_dollhouse = {
                cameraPosition: camera_dollhouse.position,
                targetPosition: controls_dollhouse.target
            };
        }

        function loading_dollhouse() {
            for(var i=0; i<=5; i++) {
                if( group_rooms_dollhouse[i] === undefined ) {
                    group_rooms_dollhouse[i] = new THREE.Group();
                }
            }
            for(var i=0;i<rooms_dollhouse.length;i++) {
                var panorama = '';
                for(var k=0;k<array_rooms.length;k++) {
                    if(array_rooms[k].id==rooms_dollhouse[i].id) {
                        panorama = array_rooms[k].panorama_3d;
                        rooms_dollhouse[i].panorama = panorama;
                    }
                }
                if(panorama=='') continue;
                draw_room_dollhouse(i,true,false,rooms_dollhouse[i].cube_width,rooms_dollhouse[i].cube_height,rooms_dollhouse[i].cube_depth);
            }
            for(var i=0; i<group_rooms_dollhouse.length; i++) {
                scene_dollhouse.add(group_rooms_dollhouse[i]);
            }
            if(camera_pos_dollhouse!='' && camera_pos_dollhouse!==undefined) {
                camera_dollhouse.position.copy(camera_pos_dollhouse.cameraPosition);
                controls_dollhouse.target.copy(camera_pos_dollhouse.targetPosition);
            } else {
                var center = computeGroupCenter_dollhouse(group_rooms_dollhouse);
                try {
                    controls_dollhouse.target.set(center.x, center.y, center.z);
                } catch (e) {}
            }
        }

        function updateUvTransform() {
            window.dollhouse_need_save = true;
            var index = current_index;
            var current_width = rooms_dollhouse[index].cube_width;
            var current_height = rooms_dollhouse[index].cube_height;
            var current_depth = rooms_dollhouse[index].cube_depth;
            rooms_dollhouse[index].cube_width = parseFloat(params_gui_dollhouse.width);
            rooms_dollhouse[index].cube_height = parseFloat(params_gui_dollhouse.height);
            rooms_dollhouse[index].cube_depth = parseFloat(params_gui_dollhouse.depth);
            rooms_dollhouse[index].x_pos = parseFloat(params_gui_dollhouse.x);
            rooms_dollhouse[index].z_pos = parseFloat(params_gui_dollhouse.z);
            rooms_dollhouse[index].center_x = parseFloat(params_gui_dollhouse.centerX);
            rooms_dollhouse[index].center_y = parseFloat(params_gui_dollhouse.centerY);
            rooms_dollhouse[index].center_z = parseFloat(params_gui_dollhouse.centerZ);
            rooms_dollhouse[index].rx_offset = parseFloat(params_gui_dollhouse.offsetX);
            rooms_dollhouse[index].ry_offset = parseFloat(params_gui_dollhouse.offsetY);
            rooms_dollhouse[index].rz_offset = parseFloat(params_gui_dollhouse.offsetZ);
            rooms_dollhouse[index].rotation = parseFloat(params_gui_dollhouse.rotation);
            rooms_dollhouse[index].pointer_visible = params_gui_dollhouse.pointer_visible;
            rooms_dollhouse[index].pointer_offset_x = parseFloat(params_gui_dollhouse.pointer_x);
            rooms_dollhouse[index].pointer_offset_z = parseFloat(params_gui_dollhouse.pointer_z);
            rooms_dollhouse[index].level = params_gui_dollhouse.level;
            rooms_dollhouse[index].cube_face_top = params_gui_dollhouse.cube_face_top;
            rooms_dollhouse[index].cube_face_bottom = params_gui_dollhouse.cube_face_bottom;
            rooms_dollhouse[index].cube_face_left = params_gui_dollhouse.cube_face_left;
            rooms_dollhouse[index].cube_face_right = params_gui_dollhouse.cube_face_right;
            rooms_dollhouse[index].cube_face_front = params_gui_dollhouse.cube_face_front;
            rooms_dollhouse[index].cube_face_back = params_gui_dollhouse.cube_face_back;
            geometries_dollhouse[index].attributes.position.needsUpdate = true;
            geometries_dollhouse[index].attributes.uv.needsUpdate = true;
            draw_room_dollhouse(index,false,false,current_width,current_height,current_depth);
            var x_pointer = parseFloat(meshes_dollhouse[current_index].position.x);
            var y_pointer = 0;
            for(var i=0; i<levels_dollhouse.length;i++) {
                if(params_gui_dollhouse.level == levels_dollhouse[i].id) {
                    y_pointer = levels_dollhouse[i].y_pos;
                }
            }
            var z_pointer = parseFloat(meshes_dollhouse[current_index].position.z);
            var pointer_offset_x = parseFloat(params_gui_dollhouse.pointer_x);
            var pointer_offset_z = parseFloat(params_gui_dollhouse.pointer_z);
            pointer_offset_x = pointer_offset_x * (rooms_dollhouse[index].cube_width/2);
            pointer_offset_z = pointer_offset_z * (rooms_dollhouse[index].cube_depth/2);
            move_pointer_dollhouse(rooms_dollhouse[current_index].id,x_pointer,y_pointer,z_pointer,pointer_offset_x,pointer_offset_z,rooms_dollhouse[index].pointer_visible);
            geometries_dollhouse[index].attributes.position.needsUpdate = false;
            geometries_dollhouse[index].attributes.uv.needsUpdate = false;
        }

        function updateLevels() {
            window.dollhouse_need_save = true;
            for(var i=0; i<levels_dollhouse.length;i++) {
                levels_dollhouse[i].name = params_gui_dollhouse['level_'+i+'_name'];
                levels_dollhouse[i].y_pos = params_gui_dollhouse['level_'+i+'_y_pos'];
            }
            for(var i=0; i<rooms_dollhouse.length;i++) {
                var current_width = rooms_dollhouse[i].cube_width;
                var current_height = rooms_dollhouse[i].cube_height;
                var current_depth = rooms_dollhouse[i].cube_depth;
                geometries_dollhouse[i].attributes.position.needsUpdate = true;
                geometries_dollhouse[i].attributes.uv.needsUpdate = true;
                draw_room_dollhouse(i,false,false,current_width,current_height,current_depth);
                geometries_dollhouse[i].attributes.position.needsUpdate = false;
                geometries_dollhouse[i].attributes.uv.needsUpdate = false;
            }
        }

        function updateSettings() {
            window.dollhouse_need_save = true;
            settings_dollhouse.zoom_in = params_gui_dollhouse.zoom_in;
            settings_dollhouse.zoom_out = params_gui_dollhouse.zoom_out;
            settings_dollhouse.pointer_color = params_gui_dollhouse.pointer_color.replace("#","");
            settings_dollhouse.pointer_color_active = params_gui_dollhouse.pointer_color_active.replace("#","");
            settings_dollhouse.background_color = params_gui_dollhouse.background_color.replace("#","");
            settings_dollhouse.background_opacity = params_gui_dollhouse.background_opacity;
            for(var i=0; i<pointers_c_dollhouse.length; i++) {
                if(pointers_c_dollhouse[i].userData.id==meshes_dollhouse[0].userData.id) {
                    pointers_c_dollhouse[i].material.color.setHex('0x'+settings_dollhouse.pointer_color_active);
                    pointers_t_dollhouse[i].material.color.setHex('0x'+settings_dollhouse.pointer_color_active);
                } else {
                    pointers_c_dollhouse[i].material.color.setHex('0x'+settings_dollhouse.pointer_color);
                    pointers_t_dollhouse[i].material.color.setHex('0x'+settings_dollhouse.pointer_color);
                }
            }
            var background_color = Number("0x"+settings_dollhouse.background_color);
            var background_opacity = settings_dollhouse.background_opacity;
            renderer_dollhouse.setClearColor(new THREE.Color(background_color));
            renderer_dollhouse.setClearAlpha(background_opacity);
        }

        function initGui(index) {
            current_index = index;
            params_gui_dollhouse.x = rooms_dollhouse[index].x_pos;
            params_gui_dollhouse.z = rooms_dollhouse[index].z_pos;
            params_gui_dollhouse.centerX = rooms_dollhouse[index].center_x;
            params_gui_dollhouse.centerY = rooms_dollhouse[index].center_y;
            params_gui_dollhouse.centerZ = rooms_dollhouse[index].center_z;
            params_gui_dollhouse.offsetX = rooms_dollhouse[index].rx_offset;
            params_gui_dollhouse.offsetY = rooms_dollhouse[index].ry_offset;
            params_gui_dollhouse.offsetZ = rooms_dollhouse[index].rz_offset;
            params_gui_dollhouse.rotation = rooms_dollhouse[index].rotation;
            params_gui_dollhouse.width = rooms_dollhouse[index].cube_width;
            params_gui_dollhouse.depth = rooms_dollhouse[index].cube_depth;
            params_gui_dollhouse.height = rooms_dollhouse[index].cube_height;
            params_gui_dollhouse.pointer_visible = rooms_dollhouse[index].pointer_visible;
            params_gui_dollhouse.pointer_x = rooms_dollhouse[index].pointer_offset_x;
            params_gui_dollhouse.pointer_z = rooms_dollhouse[index].pointer_offset_z;
            params_gui_dollhouse.level = rooms_dollhouse[index].level;
            params_gui_dollhouse.cube_face_top = rooms_dollhouse[index].cube_face_top;
            params_gui_dollhouse.cube_face_bottom = rooms_dollhouse[index].cube_face_bottom;
            params_gui_dollhouse.cube_face_left = rooms_dollhouse[index].cube_face_left;
            params_gui_dollhouse.cube_face_right = rooms_dollhouse[index].cube_face_right;
            params_gui_dollhouse.cube_face_front = rooms_dollhouse[index].cube_face_front;
            params_gui_dollhouse.cube_face_back = rooms_dollhouse[index].cube_face_back;
            var room_name = '';
            for(var k=0;k<array_rooms.length;k++) {
                if(array_rooms[k].id==rooms_dollhouse[index].id) {
                    room_name =  array_rooms[k].name;
                }
            }
            try {
                gui_dollhouse.destroy();
            } catch (e) {}
            try {
                gui_levels.destroy();
            } catch (e) {}
            try {
                gui_settings.destroy();
            } catch (e) {}
            gui_dollhouse = new GUI({title: `<?php echo _("Controls"); ?> - `+room_name, container: document.getElementById('gui_dollhouse')});
            var gui_position_folder = gui_dollhouse.addFolder( `<?php echo _("Room"); ?>` );
            gui_position_folder.add( params_gui_dollhouse, 'width' ).name( `<?php echo _("Width"); ?> (cm)` ).onChange( updateUvTransform ).listen();
            gui_position_folder.add( params_gui_dollhouse, 'depth' ).name( `<?php echo _("Depth"); ?> (cm)` ).onChange( updateUvTransform ).listen();
            gui_position_folder.add( params_gui_dollhouse, 'height' ).name(`<?php echo _("Height"); ?> (cm)` ).onChange( updateUvTransform ).listen();
            gui_parameters['x'] = gui_position_folder.add( params_gui_dollhouse, 'x' ).name( `<?php echo _("Position"); ?> - X` ).onChange( updateUvTransform ).listen();
            gui_parameters['z'] = gui_position_folder.add( params_gui_dollhouse, 'z' ).name( `<?php echo _("Position"); ?> - Z` ).onChange( updateUvTransform ).listen();
            var levels = {};
            for(var i=0; i<levels_dollhouse.length;i++) {
                levels[levels_dollhouse[i].name] = levels_dollhouse[i].id;
            }
            gui_parameters['levels'] = gui_position_folder.add( params_gui_dollhouse, 'level', levels ).name( `<?php echo _("Level"); ?>` ).onChange( updateUvTransform );
            var gui_texture_folder = gui_dollhouse.addFolder( `<?php echo _("Panorama"); ?>` );
            gui_texture_folder.add( params_gui_dollhouse, 'centerX', -1, 1 ).name( `<?php echo _("Center"); ?> - X` ).onChange( updateUvTransform );
            gui_texture_folder.add( params_gui_dollhouse, 'centerY', -1, 1 ).name( `<?php echo _("Center"); ?> - Y` ).onChange( updateUvTransform );
            gui_texture_folder.add( params_gui_dollhouse, 'centerZ', -1, 1 ).name( `<?php echo _("Center"); ?> - Z` ).onChange( updateUvTransform );
            gui_texture_folder.add( params_gui_dollhouse, 'offsetX', -1, 1 ).name( `<?php echo _("Scale"); ?> - X` ).onChange( updateUvTransform );
            gui_texture_folder.add( params_gui_dollhouse, 'offsetY', -1, 1 ).name( `<?php echo _("Scale"); ?> - Y` ).onChange( updateUvTransform );
            gui_texture_folder.add( params_gui_dollhouse, 'offsetZ', -1, 1 ).name( `<?php echo _("Scale"); ?> - Z` ).onChange( updateUvTransform );
            gui_texture_folder.add( params_gui_dollhouse, 'rotation', -1, 1 ).name( `<?php echo _("Rotation"); ?>` ).onChange( updateUvTransform );
            var gui_visibility_folder = gui_dollhouse.addFolder( `<?php echo _("Visibility"); ?>` );
            gui_visibility_folder.add( params_gui_dollhouse, 'cube_face_front' ).name( `<?php echo _("Front"); ?>` ).onChange( updateUvTransform );
            gui_visibility_folder.add( params_gui_dollhouse, 'cube_face_back' ).name( `<?php echo _("Back"); ?>` ).onChange( updateUvTransform );
            gui_visibility_folder.add( params_gui_dollhouse, 'cube_face_left' ).name( `<?php echo _("Left"); ?>` ).onChange( updateUvTransform );
            gui_visibility_folder.add( params_gui_dollhouse, 'cube_face_right' ).name( `<?php echo _("Right"); ?>` ).onChange( updateUvTransform );
            gui_visibility_folder.add( params_gui_dollhouse, 'cube_face_top' ).name( `<?php echo _("Top"); ?>` ).onChange( updateUvTransform );
            gui_visibility_folder.add( params_gui_dollhouse, 'cube_face_bottom' ).name( `<?php echo _("Bottom"); ?>` ).onChange( updateUvTransform );
            var gui_pointer_folder = gui_dollhouse.addFolder( `<?php echo _("Pointer"); ?>` );
            gui_pointer_folder.add( params_gui_dollhouse, 'pointer_visible' ).name( `<?php echo _("Visible"); ?>` ).onChange( updateUvTransform );
            gui_pointer_folder.add( params_gui_dollhouse, 'pointer_x', -1, 1 ).name( `<?php echo _("Offset"); ?> - X` ).onChange( updateUvTransform );
            gui_pointer_folder.add( params_gui_dollhouse, 'pointer_z', -1, 1 ).name( `<?php echo _("Offset"); ?> - Z` ).onChange( updateUvTransform );
        }

        window.show_settings_gui = function () {
            transforms_dollhouse.detach();
            $('.select_level_dollhouse option[id="all"]').prop('selected', true);
            select_level_dollhouse();
            $('.select_level_dollhouse').prop('disabled',true);
            for(var i=0; i<meshes_dollhouse.length; i++) {
                meshes_dollhouse[i].material[0].color.setHex(0xFFFFFF);
                meshes_dollhouse[i].material[1].color.setHex(0xFFFFFF);
                meshes_dollhouse[i].material[2].color.setHex(0xFFFFFF);
                meshes_dollhouse[i].material[3].color.setHex(0xFFFFFF);
                meshes_dollhouse[i].material[4].color.setHex(0xFFFFFF);
                meshes_dollhouse[i].material[5].color.setHex(0xFFFFFF);
            }
            for(var i=0; i<pointers_c_dollhouse.length; i++) {
                pointers_c_dollhouse[i].visible=true;
                pointers_t_dollhouse[i].visible=true;
            }
            initGui_settings();
        }

        function initGui_settings() {
            try {
                gui_dollhouse.destroy();
            } catch (e) {}
            try {
                gui_levels.destroy();
            } catch (e) {}
            try {
                gui_settings.destroy();
            } catch (e) {}
            gui_settings = new GUI({title: `<?php echo _("Settings"); ?>`, container: document.getElementById('gui_dollhouse')});
            if(settings_dollhouse.zoom_in!==undefined) params_gui_dollhouse.zoom_in = settings_dollhouse.zoom_in;
            if(settings_dollhouse.zoom_out!==undefined) params_gui_dollhouse.zoom_out = settings_dollhouse.zoom_out;
            if(settings_dollhouse.pointer_color!==undefined) params_gui_dollhouse.pointer_color = '#'+settings_dollhouse.pointer_color;
            if(settings_dollhouse.pointer_color_active!==undefined) params_gui_dollhouse.pointer_color_active = '#'+settings_dollhouse.pointer_color_active;
            if(settings_dollhouse.background_color!==undefined) params_gui_dollhouse.background_color = '#'+settings_dollhouse.background_color;
            if(settings_dollhouse.background_opacity!==undefined) params_gui_dollhouse.background_opacity = settings_dollhouse.background_opacity;
            gui_settings.add( params_gui_dollhouse, 'zoom_in' ).name( `<?php echo _("Zoom In"); ?> (ms)` ).onChange( updateSettings );
            gui_settings.add( params_gui_dollhouse, 'zoom_out' ).name( `<?php echo _("Zoom Out"); ?> (ms)` ).onChange( updateSettings );
            gui_settings.addColor( params_gui_dollhouse, 'pointer_color' ).name( `<?php echo _("Pointer Color"); ?> - <?php echo _("Main"); ?>` ).onChange( updateSettings );
            gui_settings.addColor( params_gui_dollhouse, 'pointer_color_active' ).name( `<?php echo _("Pointer Color"); ?> - <?php echo _("Active"); ?>` ).onChange( updateSettings );
            gui_settings.addColor( params_gui_dollhouse, 'background_color' ).name( `<?php echo _("Background Color"); ?>` ).onChange( updateSettings );
            gui_settings.add( params_gui_dollhouse, 'background_opacity', 0, 1 ).name( `<?php echo _("Background Opacity"); ?>` ).onChange( updateSettings );
            gui_settings.add( params_gui_dollhouse, 'camera_position' ).name( `<?php echo _("Set Default Camera Position"); ?>` );
        }

        window.show_levels_gui = function () {
            transforms_dollhouse.detach();
            for(var i=0; i<meshes_dollhouse.length; i++) {
                if(rooms_dollhouse[meshes_dollhouse[i].userData.index].removed==0) {
                    meshes_dollhouse[i].material[0].color.setHex(0x7777777);
                    meshes_dollhouse[i].material[1].color.setHex(0x7777777);
                    meshes_dollhouse[i].material[2].color.setHex(0x7777777);
                    meshes_dollhouse[i].material[3].color.setHex(0x7777777);
                    meshes_dollhouse[i].material[4].color.setHex(0x7777777);
                    meshes_dollhouse[i].material[5].color.setHex(0x7777777);
                } else {
                    meshes_dollhouse[i].visible=false;
                }
            }
            for(var i=0; i<pointers_c_dollhouse.length; i++) {
                pointers_c_dollhouse[i].visible=false;
                pointers_t_dollhouse[i].visible=false;
            }
            initGui_levels();
        }

        function initGui_levels() {
            try {
                gui_dollhouse.destroy();
            } catch (e) {}
            try {
                gui_levels.destroy();
            } catch (e) {}
            try {
                gui_settings.destroy();
            } catch (e) {}
            gui_levels = new GUI({title: `<?php echo _("Levels"); ?>`, container: document.getElementById('gui_dollhouse')});
            for(var i=0; i<=5; i++) {
                if(levels_dollhouse[i]!==undefined) {
                    params_gui_dollhouse['level_'+i+'_name'] = levels_dollhouse[i].name;
                    params_gui_dollhouse['level_'+i+'_y_pos'] = levels_dollhouse[i].y_pos;
                }
                gui_levels.add( params_gui_dollhouse, 'level_'+i+'_name' ).name( `<?php echo _("Level"); ?> `+i+` - <?php echo _("Name"); ?>` ).onChange( updateLevels );
                gui_levels.add( params_gui_dollhouse, 'level_'+i+'_y_pos' ).name( `<?php echo _("Level"); ?> `+i+` - <?php echo _("Altitude"); ?>` ).onChange( updateLevels );
            }
        }

        function change_transform_position() {
            try {
                var x = parseFloat(meshes_dollhouse[current_index].position.x);
                var z = parseFloat(meshes_dollhouse[current_index].position.z);
                var width = parseFloat(params_gui_dollhouse.width);
                var depth = parseFloat(params_gui_dollhouse.depth);
                var pointer_offset_x = parseFloat(params_gui_dollhouse.pointer_x);
                var pointer_offset_z = parseFloat(params_gui_dollhouse.pointer_z);
                var x_pointer = x;
                var y_pointer = 0;
                for(var i=0; i<levels_dollhouse.length;i++) {
                    if(params_gui_dollhouse.level == levels_dollhouse[i].id) {
                        y_pointer = levels_dollhouse[i].y_pos;
                    }
                }
                var z_pointer = z;
                x = (x - (width/2)).toFixed(0);
                z = (z - (depth/2)).toFixed(0);
                params_gui_dollhouse.x = x;
                params_gui_dollhouse.z = z;
                gui_parameters['x'].updateDisplay();
                gui_parameters['z'].updateDisplay();
                gui_parameters['x'].setValue(x);
                gui_parameters['z'].setValue(z);
                pointer_offset_x = pointer_offset_x * (width/2);
                pointer_offset_z = pointer_offset_z * (depth/2);
                move_pointer_dollhouse(rooms_dollhouse[current_index].id,x_pointer,y_pointer,z_pointer,pointer_offset_x,pointer_offset_z,rooms_dollhouse[current_index].pointer_visible);
                $('.lil-gui input').blur();
            } catch (e) {
                console.log(e);
            }
            update_dollhouse();
        }

        window.remove_room_dollhouse = function() {
            var r = confirm(window.backend_labels.delete_sure_msg);
            if (r == true) {
                window.dollhouse_need_save = true;
                rooms_dollhouse[current_index].removed = 1;
                var level = rooms_dollhouse[current_index].level;
                group_rooms_dollhouse[level].remove(meshes_dollhouse[current_index]);
                transforms_dollhouse.detach();
                for(var i=0; i<pointers_c_dollhouse.length; i++) {
                    if(pointers_c_dollhouse[i].userData.id==meshes_dollhouse[current_index].userData.id) {
                        removeObject(pointers_c_dollhouse[i]);
                        removeObject(pointers_t_dollhouse[i]);
                        pointers_c_dollhouse.splice(i, 1);
                        pointers_t_dollhouse.splice(i, 1);
                    }
                }
                removeObject(meshes_dollhouse[current_index]);
                domEvents_dollhouse.removeEventListener(meshes_dollhouse[current_index], 'dblclick');
                textures_dollhouse[current_index].dispose();
                gui_dollhouse.destroy();
                $('#btn_remove_room').addClass('disabled');
                populate_room_select();
                $('#btn_add_room').removeClass('disabled');
            }
        }

        function removeObject(object) {
            if (!(object instanceof THREE.Object3D)) return false;
            object.geometry.dispose();
            if (object.material instanceof Array) {
                object.material.forEach(material => material.dispose());
            } else {
                object.material.dispose();
            }
            object.removeFromParent();
            return true;
        }

        window.add_room_dollhouse = function () {
            var id = $('#room_select option:selected').attr('id');
            var x_pos = get_max_position_x();
            var new_room_dollhouse = {};
            new_room_dollhouse['id'] = parseInt(id);
            new_room_dollhouse['level'] = 0;
            new_room_dollhouse['cube_width'] = 300;
            new_room_dollhouse['cube_height'] = 270;
            new_room_dollhouse['cube_depth'] = 300;
            new_room_dollhouse['rx_offset'] = 0;
            new_room_dollhouse['ry_offset'] = 0;
            new_room_dollhouse['rz_offset'] = 0;
            new_room_dollhouse['x_pos'] = x_pos;
            new_room_dollhouse['z_pos'] = 0;
            new_room_dollhouse['rotation'] = 0;
            new_room_dollhouse['center_x'] = 0;
            new_room_dollhouse['center_y'] = 0;
            new_room_dollhouse['center_z'] = 0;
            new_room_dollhouse['pointer_visible'] = true;
            new_room_dollhouse['pointer_offset_x'] = 0;
            new_room_dollhouse['pointer_offset_z'] = 0;
            new_room_dollhouse['cube_face_top'] = true;
            new_room_dollhouse['cube_face_bottom'] = true;
            new_room_dollhouse['cube_face_left'] = true;
            new_room_dollhouse['cube_face_right'] = true;
            new_room_dollhouse['cube_face_front'] = true;
            new_room_dollhouse['cube_face_back'] = true;
            new_room_dollhouse['removed'] = 0;
            var panorama = '';
            for(var k=0;k<array_rooms.length;k++) {
                if(array_rooms[k].id==new_room_dollhouse['id']) {
                    panorama = array_rooms[k].panorama_3d;
                    new_room_dollhouse['panorama'] = panorama;
                }
            }
            rooms_dollhouse.push(new_room_dollhouse);
            if( group_rooms_dollhouse[0] === undefined ) {
                group_rooms_dollhouse[0] = new THREE.Group();
                scene_dollhouse.add(group_rooms_dollhouse[0]);
            }
            var index = rooms_dollhouse.length-1;
            draw_room_dollhouse(index,true,true,new_room_dollhouse['cube_width'],new_room_dollhouse['cube_height'],new_room_dollhouse['cube_depth']);
            $('#modal_add_room_dollhouse').modal('hide');
            populate_room_select();
            var targetPosition = new THREE.Vector3(x_pos,0,0);
            controls_dollhouse.target.copy(targetPosition);
            window.dollhouse_need_save = true;
        }

        function draw_room_dollhouse(index,add,force,current_width,current_height,current_depth) {
            var id = rooms_dollhouse[index].id;
            var level = rooms_dollhouse[index].level;
            var name = rooms_dollhouse[index].name;
            var cube_width = rooms_dollhouse[index].cube_width;
            var cube_height = rooms_dollhouse[index].cube_height;
            var cube_depth = rooms_dollhouse[index].cube_depth;
            var rx_offset = rooms_dollhouse[index].rx_offset;
            var ry_offset = rooms_dollhouse[index].ry_offset;
            var rz_offset = rooms_dollhouse[index].rz_offset;
            var x_pos = rooms_dollhouse[index].x_pos;
            var y_pos = 0;
            for(var i=0; i<levels_dollhouse.length;i++) {
                if(level == levels_dollhouse[i].id) {
                    y_pos = levels_dollhouse[i].y_pos;
                }
            }
            var z_pos = rooms_dollhouse[index].z_pos;
            var rotation = parseFloat(rooms_dollhouse[index].rotation);
            var center_x = rooms_dollhouse[index].center_x;
            var center_y = rooms_dollhouse[index].center_y;
            var center_z = rooms_dollhouse[index].center_z;
            var pointer_visible = rooms_dollhouse[index].pointer_visible;
            var pointer_offset_x = rooms_dollhouse[index].pointer_offset_x;
            var pointer_offset_z = rooms_dollhouse[index].pointer_offset_z;
            var cube_face_top = rooms_dollhouse[index].cube_face_top;
            var cube_face_bottom = rooms_dollhouse[index].cube_face_bottom;
            var cube_face_left = rooms_dollhouse[index].cube_face_left;
            var cube_face_right = rooms_dollhouse[index].cube_face_right;
            var cube_face_front = rooms_dollhouse[index].cube_face_front;
            var cube_face_back = rooms_dollhouse[index].cube_face_back;

            pointer_offset_x = pointer_offset_x * (cube_width/2);
            pointer_offset_z = pointer_offset_z * (cube_depth/2);

            var panorama = rooms_dollhouse[index].panorama;

            center_x = cube_width * center_x;
            center_y = cube_height * center_y;
            center_z = cube_depth * center_z;
            rx_offset = cube_width * rx_offset;
            ry_offset = cube_height * ry_offset;
            rz_offset = cube_depth * rz_offset;

            var x_pos_s = x_pos + (cube_width/2);
            var y_pos_s = y_pos + (cube_height/2);
            var z_pos_s = z_pos + (cube_depth/2);

            if(add) {
                textures_dollhouse[index] = new THREE.TextureLoader().load( '../viewer/panoramas/'+panorama, function () {
                    create_pointer_dollhouse(id,index,x_pos_s,y_pos,z_pos_s,name,level,pointer_offset_x,pointer_offset_z);
                    if(force) {
                        activate_room_dollhouse(index);
                    }
                });
                textures_dollhouse[index].wrapS = THREE.RepeatWrapping;
                textures_dollhouse[index].magFilter = THREE.NearestFilter;
                textures_dollhouse[index].minFilter = THREE.NearestFilter;
                var MaxAnisotropy = renderer_dollhouse.capabilities.getMaxAnisotropy()/2;
                if(MaxAnisotropy<1) MaxAnisotropy=1;
                textures_dollhouse[index].anisotropy = MaxAnisotropy;
            }
            textures_dollhouse[index].offset.x = rotation;

            if(add) {
                geometries_dollhouse[index] = new THREE.BoxBufferGeometry(cube_width, cube_height, cube_depth, 64, 64, 64).toNonIndexed();
                geometries_dollhouse[index].scale(-1, 1, 1);
            } else {
                if((cube_width!=current_width) || (cube_height!=current_height) || (cube_depth!=current_depth)) {
                    var scale_x = cube_width / current_width;
                    var scale_y = cube_height / current_height;
                    var scale_z = cube_depth / current_depth;
                    geometries_dollhouse[index].scale(scale_x, scale_y, scale_z);
                }
            }

            var positions = geometries_dollhouse[index].attributes.position.array;
            var uvs = geometries_dollhouse[index].attributes.uv.array;

            var rx = (cube_width/2) + rx_offset;
            var ry = (cube_height/2) + ry_offset;
            var rz = (cube_depth/2) + rz_offset;

            for ( var i = 0, l = positions.length / 3; i < l; i ++ ) {
                var x = (positions[ i * 3 + 0 ]+center_x)/rx;
                var y = (positions[ i * 3 + 1 ]+center_y)/ry;
                var z = (positions[ i * 3 + 2 ]+center_z)/rz;
                var tmp_x = x;
                var tmp_z = z;
                var a = Math.sqrt(1.0/(x*x+y*y+z*z));
                x = a*x;
                y = a*y;
                z = a*z;
                var phi, theta;
                phi = Math.asin(y);
                theta = Math.atan2(x, z);
                var uvx = 1 - (theta+Math.PI)/Math.PI/2;
                var uvy = (phi+Math.PI/2)/Math.PI;
                if((tmp_x==0) && (tmp_z<0)) {
                    var p = Math.floor(i / 3);
                    if ((positions[p * 3 * 3] < 0) || (positions[(p + 1) * 3 * 3] < 0) || (positions[(p + 2) * 3 * 3] < 0)) {
                        uvx = 1;
                    }
                }
                uvs[i*2] = uvx;
                uvs[i*2+1] = uvy;
            }
            if(add) {
                if(cube_face_front) {
                    var material1 = new THREE.MeshBasicMaterial( { color:0x7777777, map: textures_dollhouse[index], transparent: true, opacity: 1 } );
                } else {
                    var material1 = new THREE.MeshBasicMaterial( { color:0x7777777, map: textures_dollhouse[index], transparent: true, opacity: 0, depthWrite: false } );
                }
                if(cube_face_back) {
                    var material2 = new THREE.MeshBasicMaterial( { color:0x7777777, map: textures_dollhouse[index], transparent: true, opacity: 1 } );
                } else {
                    var material2 = new THREE.MeshBasicMaterial( { color:0x7777777, map: textures_dollhouse[index], transparent: true, opacity: 0, depthWrite: false } );
                }
                if(cube_face_top) {
                    var material3 = new THREE.MeshBasicMaterial( { color:0x7777777, map: textures_dollhouse[index], transparent: true, opacity: 1 } );
                } else {
                    var material3 = new THREE.MeshBasicMaterial( { color:0x7777777, map: textures_dollhouse[index], transparent: true, opacity: 0, depthWrite: false } );
                }
                if(cube_face_bottom) {
                    var material4 = new THREE.MeshBasicMaterial( { color:0x7777777, map: textures_dollhouse[index], transparent: true, opacity: 1 } );
                } else {
                    var material4 = new THREE.MeshBasicMaterial( { color:0x7777777, map: textures_dollhouse[index], transparent: true, opacity: 0, depthWrite: false } );
                }
                if(cube_face_left) {
                    var material5 = new THREE.MeshBasicMaterial( { color:0x7777777, map: textures_dollhouse[index], transparent: true, opacity: 1 } );
                } else {
                    var material5 = new THREE.MeshBasicMaterial( { color:0x7777777, map: textures_dollhouse[index], transparent: true, opacity: 0, depthWrite: false } );
                }
                if(cube_face_right) {
                    var material6 = new THREE.MeshBasicMaterial( { color:0x7777777, map: textures_dollhouse[index], transparent: true, opacity: 1 } );
                } else {
                    var material6 = new THREE.MeshBasicMaterial( { color:0x7777777, map: textures_dollhouse[index], transparent: true, opacity: 0, depthWrite: false } );
                }
                meshes_dollhouse[index] = new THREE.Mesh( geometries_dollhouse[index], [material1,material2,material3,material4,material5, material6] );
                meshes_dollhouse[index].userData = { type:'room',level:level, index:index, id:id, width: cube_width, height: cube_height, depth: cube_depth, pointer_visible: pointer_visible};
            } else {
                if(cube_face_front) {
                    meshes_dollhouse[index].material[0].opacity = 1;
                    meshes_dollhouse[index].material[0].depthWrite = true;
                } else {
                    meshes_dollhouse[index].material[0].opacity = 0;
                    meshes_dollhouse[index].material[0].depthWrite = false;
                }
                if(cube_face_back) {
                    meshes_dollhouse[index].material[1].opacity = 1;
                    meshes_dollhouse[index].material[1].depthWrite = true;
                } else {
                    meshes_dollhouse[index].material[1].opacity = 0;
                    meshes_dollhouse[index].material[1].depthWrite = false;
                }
                if(cube_face_top) {
                    meshes_dollhouse[index].material[2].opacity = 1;
                    meshes_dollhouse[index].material[2].depthWrite = true;
                } else {
                    meshes_dollhouse[index].material[2].opacity = 0;
                    meshes_dollhouse[index].material[2].depthWrite = false;
                }
                if(cube_face_bottom) {
                    meshes_dollhouse[index].material[3].opacity = 1;
                    meshes_dollhouse[index].material[3].depthWrite = true;
                } else {
                    meshes_dollhouse[index].material[3].opacity = 0;
                    meshes_dollhouse[index].material[3].depthWrite = false;
                }
                if(cube_face_left) {
                    meshes_dollhouse[index].material[4].opacity = 1;
                    meshes_dollhouse[index].material[4].depthWrite = true;
                } else {
                    meshes_dollhouse[index].material[4].opacity = 0;
                    meshes_dollhouse[index].material[4].depthWrite = false;
                }
                if(cube_face_right) {
                    meshes_dollhouse[index].material[5].opacity = 1;
                    meshes_dollhouse[index].material[5].depthWrite = true;
                } else {
                    meshes_dollhouse[index].material[5].opacity = 0;
                    meshes_dollhouse[index].material[5].depthWrite = false;
                }
            }
            meshes_dollhouse[index].position.set(x_pos_s, y_pos_s, z_pos_s);
            if(add) {
                group_rooms_dollhouse[level].add(meshes_dollhouse[index]);
                domEvents_dollhouse.addEventListener(meshes_dollhouse[index], 'dblclick', function(){
                    activate_room_dollhouse(index);
                    select_level_dollhouse();
                }, false);
            } else {
                try { group_rooms_dollhouse[0].remove(meshes_dollhouse[index]); } catch (e) {}
                try { group_rooms_dollhouse[1].remove(meshes_dollhouse[index]); } catch (e) {}
                try { group_rooms_dollhouse[2].remove(meshes_dollhouse[index]); } catch (e) {}
                try { group_rooms_dollhouse[3].remove(meshes_dollhouse[index]); } catch (e) {}
                try { group_rooms_dollhouse[4].remove(meshes_dollhouse[index]); } catch (e) {}
                try { group_rooms_dollhouse[5].remove(meshes_dollhouse[index]); } catch (e) {}
                group_rooms_dollhouse[level].add(meshes_dollhouse[index]);
            }
        }

        function activate_room_dollhouse(index) {
            for(var i=0; i<meshes_dollhouse.length; i++) {
                if(rooms_dollhouse[meshes_dollhouse[i].userData.index].removed==0) {
                    meshes_dollhouse[i].material[0].color.setHex(0x7777777);
                    meshes_dollhouse[i].material[1].color.setHex(0x7777777);
                    meshes_dollhouse[i].material[2].color.setHex(0x7777777);
                    meshes_dollhouse[i].material[3].color.setHex(0x7777777);
                    meshes_dollhouse[i].material[4].color.setHex(0x7777777);
                    meshes_dollhouse[i].material[5].color.setHex(0x7777777);
                } else {
                    meshes_dollhouse[i].visible=false;
                }
            }
            for(var i=0; i<pointers_c_dollhouse.length; i++) {
                if(pointers_c_dollhouse[i].userData.id==meshes_dollhouse[index].userData.id) {
                    if(meshes_dollhouse[index].userData.pointer_visible) {
                        pointers_c_dollhouse[i].visible=true;
                        pointers_t_dollhouse[i].visible=true;
                    }
                } else {
                    pointers_c_dollhouse[i].visible=false;
                    pointers_t_dollhouse[i].visible=false;
                }
            }
            meshes_dollhouse[index].material[0].color.setHex(0xFFFFFF);
            meshes_dollhouse[index].material[1].color.setHex(0xFFFFFF);
            meshes_dollhouse[index].material[2].color.setHex(0xFFFFFF);
            meshes_dollhouse[index].material[3].color.setHex(0xFFFFFF);
            meshes_dollhouse[index].material[4].color.setHex(0xFFFFFF);
            meshes_dollhouse[index].material[5].color.setHex(0xFFFFFF);
            transforms_dollhouse.attach(meshes_dollhouse[index]);
            scene_dollhouse.add(transforms_dollhouse);
            initGui(index);
            change_transform_position();
            $('.select_level_dollhouse').prop('disabled',false);
            $('#btn_remove_room').removeClass('disabled');
        }

        function create_pointer_dollhouse(id,index,pos_x,pos_y,pos_z,name,level,pointer_offset_x,pointer_offset_z) {
            if(id==array_rooms[0].id) {
                var pointer_color = new THREE.Color().setHex('0x'+settings_dollhouse.pointer_color_active);
            } else {
                var pointer_color = new THREE.Color().setHex('0x'+settings_dollhouse.pointer_color);

            }
            var geometry = new THREE.TorusGeometry( 20, 2, 2, 32 );
            var material = new THREE.MeshBasicMaterial( { color: pointer_color, transparent: false, opacity: 0.6 } );
            var torus = new THREE.Mesh(geometry, material);
            torus.position.set(pos_x+pointer_offset_x, pos_y+2, pos_z+pointer_offset_z);
            torus.rotation.x = Math.PI / 2;
            torus.userData = { type:'pointer',level:level, id:id};
            var geometry = new THREE.CircleGeometry( 20, 32 );
            var material = new THREE.MeshBasicMaterial( { color: pointer_color, transparent: true, opacity: 0.2, side: THREE.DoubleSide } );
            var circle = new THREE.Mesh(geometry, material);
            circle.renderOrder = 1;
            circle.position.set(pos_x+pointer_offset_x, pos_y+2, pos_z+pointer_offset_z);
            circle.rotation.x = -Math.PI / 2;
            circle.userData = { type:'pointer',level:level, id:id};
            torus.name = "pointer_t_"+id;
            circle.name = "pointer_c_"+id;
            circle.visible = false;
            torus.visible = false;
            pointers_t_dollhouse.push(torus);
            pointers_c_dollhouse.push(circle);
            group_rooms_dollhouse[level].add(circle);
            group_rooms_dollhouse[level].add(torus);
        }

        function move_pointer_dollhouse(id,pos_x,pos_y,pos_z,pointer_offset_x,pointer_offset_z,visible) {
            for(var i=0;i<pointers_c_dollhouse.length;i++) {
                if(id==pointers_c_dollhouse[i].userData.id) {
                    pointers_c_dollhouse[i].visible = visible;
                    pointers_t_dollhouse[i].visible = visible;
                    pointers_c_dollhouse[i].position.set(pos_x+pointer_offset_x, pos_y+2, pos_z+pointer_offset_z);
                    pointers_t_dollhouse[i].position.set(pos_x+pointer_offset_x, pos_y+2, pos_z+pointer_offset_z);
                }
            }
        }

        function animate_dollhouse() {
            requestAnimationFrame( animate_dollhouse );
            update_dollhouse();
        }

        function update_dollhouse() {
            controls_dollhouse.update();
            renderer_dollhouse.render( scene_dollhouse, camera_dollhouse );
        }

        function onWindowResize_dollhouse() {
            camera_dollhouse.aspect = container_dollhouse.offsetWidth / container_dollhouse.offsetHeight;
            camera_dollhouse.updateProjectionMatrix();
            renderer_dollhouse.setSize( container_dollhouse.offsetWidth, container_dollhouse.offsetHeight );
        }

        var computeGroupCenter_dollhouse = (function () {
            var childBox = new THREE.Box3();
            var groupBox = new THREE.Box3();
            var invMatrixWorld = new THREE.Matrix4();
            return function (group, optionalTarget) {
                for(var i=0; i<group.length; i++) {
                    if (!optionalTarget) optionalTarget = new THREE.Vector3();
                    group[i].traverse(function (child) {
                        if (child instanceof THREE.Mesh) {
                            if (!child.geometry.boundingBox) {
                                child.geometry.computeBoundingBox();
                                childBox.copy(child.geometry.boundingBox);
                                child.updateMatrixWorld(true);
                                childBox.applyMatrix4(child.matrixWorld);
                                groupBox.min.min(childBox.min);
                                groupBox.max.max(childBox.max);
                            }
                        }
                    });
                    invMatrixWorld.copy(group[i].matrixWorld).invert();
                    groupBox.applyMatrix4(invMatrixWorld);
                    groupBox.getCenter(optionalTarget);
                }
                return optionalTarget;
            }
        })();

        function get_max_position_x() {
            var max_x = 0;
            for(var i=0; i<rooms_dollhouse.length;i++) {
                if(rooms_dollhouse[i].removed==0) {
                    var x = rooms_dollhouse[i].x_pos-rooms_dollhouse[i].cube_width;
                    if(x<max_x) {
                        max_x=x;
                    }
                }
            }
            return max_x;
        }

        window.save_dollhouse = function () {
            $('#save_btn .icon i').removeClass('far fa-circle').addClass('fas fa-circle-notch fa-spin');
            $('#save_btn').addClass("disabled");
            for(var i=0; i<rooms_dollhouse.length; i++) {
                if(rooms_dollhouse[i].removed==1) {
                    rooms_dollhouse.splice(i,1);
                }
            }
            var dollhouse_json = JSON.stringify({
                levels: levels_dollhouse,
                rooms: rooms_dollhouse,
                camera: camera_pos_dollhouse,
                settings: settings_dollhouse
            });
            $.ajax({
                url: "ajax/save_dollhouse.php",
                type: "POST",
                data: {
                    id_virtualtour: window.id_virtualtour,
                    dollhouse_json: dollhouse_json
                },
                async: true,
                success: function (json) {
                    var rsp = JSON.parse(json);
                    if(rsp.status=="ok") {
                        window.dollhouse_need_save = false;
                        $('#save_btn .icon i').removeClass('fas fa-circle-notch fa-spin').addClass('fas fa-check');
                        setTimeout(function () {
                            $('#save_btn .icon i').removeClass('fas fa-check').addClass('far fa-circle');
                            $('#save_btn').removeClass("disabled");
                        },1000);
                    } else {
                        $('#save_btn .icon i').removeClass('fas fa-circle-notch fa-spin').addClass('fas fa-times');
                        $('#save_btn').removeClass('btn-success').addClass('btn-danger');
                        setTimeout(function () {
                            $('#save_btn .icon i').removeClass('fas fa-times').addClass('far fa-circle');
                            $('#save_btn').removeClass('btn-danger').addClass('btn-success');
                            $('#save_btn').removeClass("disabled");
                        },1000);
                    }
                }
            });
        }

        window.toggle_dollhouse_help = function() {
            if($('.info_dollhouse').is(':visible')) {
                $('.info_dollhouse').fadeOut();
            } else {
                $('.info_dollhouse').fadeIn();
            }
        }

        window.select_level_dollhouse = function () {
            try {
                transforms_dollhouse.detach();
            } catch (e) {}
            try {
                gui_dollhouse.destroy();
            } catch (e) {}
            var level = $('.select_level_dollhouse option:selected').attr('id');
            for(var i=0; i<meshes_dollhouse.length; i++) {
                try { domEvents_dollhouse.removeEventListener(meshes_dollhouse[i], 'dblclick'); } catch (e) {}
            }
            if(level=='all') {
                for(var i=0; i<group_rooms_dollhouse.length; i++) {
                    setOpacity_group_dollhouse(group_rooms_dollhouse,i,1);
                }
            } else {
                for(var i=0; i<group_rooms_dollhouse.length; i++) {
                    if(i==parseInt(level)) {
                        setOpacity_group_dollhouse(group_rooms_dollhouse,i,1);
                    } else {
                        setOpacity_group_dollhouse(group_rooms_dollhouse,i,0.1);
                    }
                }
            }
            for(var i=0; i<meshes_dollhouse.length; i++) {
                if(rooms_dollhouse[meshes_dollhouse[i].userData.index].removed==0) {
                    meshes_dollhouse[i].material[0].color.setHex(0x7777777);
                    meshes_dollhouse[i].material[1].color.setHex(0x7777777);
                    meshes_dollhouse[i].material[2].color.setHex(0x7777777);
                    meshes_dollhouse[i].material[3].color.setHex(0x7777777);
                    meshes_dollhouse[i].material[4].color.setHex(0x7777777);
                    meshes_dollhouse[i].material[5].color.setHex(0x7777777);
                } else {
                    meshes_dollhouse[i].visible=false;
                }
            }
            for(var i=0; i<pointers_c_dollhouse.length; i++) {
                pointers_c_dollhouse[i].visible = false;
                pointers_t_dollhouse[i].visible = false;
            }
        }

        function setOpacity_group_dollhouse(group,level,opacity) {
            group[level].children.forEach(function(child){
                if(child.userData.type=='room') {
                    if(rooms_dollhouse[child.userData.index].removed==0) {
                        if(rooms_dollhouse[child.userData.index].cube_face_front) {
                            child.material[0].opacity = opacity;
                        } else {
                            child.material[0].opacity = 0;
                        }
                        if(rooms_dollhouse[child.userData.index].cube_face_back) {
                            child.material[1].opacity = opacity;
                        } else {
                            child.material[1].opacity = 0;
                        }
                        if(rooms_dollhouse[child.userData.index].cube_face_top) {
                            child.material[2].opacity = opacity;
                        } else {
                            child.material[2].opacity = 0;
                        }
                        if(rooms_dollhouse[child.userData.index].cube_face_bottom) {
                            child.material[3].opacity = opacity;
                        } else {
                            child.material[3].opacity = 0;
                        }
                        if(rooms_dollhouse[child.userData.index].cube_face_left) {
                            child.material[4].opacity = opacity;
                        } else {
                            child.material[4].opacity = 0;
                        }
                        if(rooms_dollhouse[child.userData.index].cube_face_right) {
                            child.material[5].opacity = opacity;
                        } else {
                            child.material[5].opacity = 0;
                        }
                        if(opacity<1) {
                            child.material[0].depthWrite = false;
                            child.material[1].depthWrite = false;
                            child.material[2].depthWrite = false;
                            child.material[3].depthWrite = false;
                            child.material[4].depthWrite = false;
                            child.material[5].depthWrite = false;
                        } else {
                            try {
                                domEvents_dollhouse.addEventListener(child, 'dblclick', function(){
                                    activate_room_dollhouse(child.userData.index);
                                }, false);
                            } catch (e) {}
                            if(rooms_dollhouse[child.userData.index].cube_face_front) {
                                child.material[0].depthWrite = true;
                            } else {
                                child.material[0].depthWrite = false;
                            }
                            if(rooms_dollhouse[child.userData.index].cube_face_back) {
                                child.material[1].depthWrite = true;
                            } else {
                                child.material[1].depthWrite = false;
                            }
                            if(rooms_dollhouse[child.userData.index].cube_face_top) {
                                child.material[2].depthWrite = true;
                            } else {
                                child.material[2].depthWrite = false;
                            }
                            if(rooms_dollhouse[child.userData.index].cube_face_bottom) {
                                child.material[3].depthWrite = true;
                            } else {
                                child.material[3].depthWrite = false;
                            }
                            if(rooms_dollhouse[child.userData.index].cube_face_left) {
                                child.material[4].depthWrite = true;
                            } else {
                                child.material[4].depthWrite = false;
                            }
                            if(rooms_dollhouse[child.userData.index].cube_face_right) {
                                child.material[5].depthWrite = true;
                            } else {
                                child.material[5].depthWrite = false;
                            }
                        }
                    } else {
                        meshes_dollhouse[child.userData.index].visible=false;
                    }
                }
            });
        };

        $(window).on('beforeunload', function(){
            if(window.dollhouse_need_save) {
                var c=confirm();
                if(c) return true; else return false;
            }
        });
    })(jQuery); // End of use strict
</script>