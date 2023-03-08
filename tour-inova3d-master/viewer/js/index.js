(function ($) {
    'use strict';
    var pano_viewer, pano_viewer_vr, video_viewer, video_viewer_vr, pano_viewer_alt;
    var index_preload_image = 0, index_preload_image_alt = 0, id_current_map = null, len = 0, progress_circle=null, interval_progress=null, step_progress=0.6;
    var first_image = "", imgs = [], imgs_alt = [], imgs_loaded = [], gallery, poi_gallery, intro_desktop = '', intro_mobile = '', sly;
    var panoramas = [], panoramas_tmp = [], array_maps = [], gallery_images = [], controls_status = [], array_presentation = [], array_id_room_nav = [], array_base64 = [];
    var html_map, html_logo, list_alt = '', nadir_logo = "", nadir_size = "small";
    var hfov = 100, min_hfov = 60, max_hfov = 100, autorotate_speed = 0, autorotate_inactivity = 0;
    var info_box = "", author = "", name_virtual_tour = "", custom_box = "";
    var song_bg = "", audio_player = new Audio(), audio_player_room = new Audio(), song_bg_volume_sel=1;
    var first_song_play = true, song_autoplay = 0, song_is_playng = false, audio_prompt = null, audio_prompt_open = false;
    var keyboard_mode = 1, vr_enabled = false, confirm_password_modal = null, wl = null;
    var voice_commands_enable = 0, arrows_nav = false, show_audio = false, show_vt_title = false, show_logo = false, show_compass = false, show_gallery = 0, show_info = 0, show_custom = 0, show_dollhouse = 0, show_icons_toggle = false, show_autorotation_toggle = false, show_nav_control = false, show_presentation = 0, show_share = false, show_device_orientation = 0, drag_device_orientation = true, show_webvr = false, show_fullscreen = false, show_map = 0, show_map_tour = 0, show_live_session = false, show_meeting = false, show_annotations = false, show_list_alt = 0, autoclose_menu = false, autoclose_list_alt = false, autoclose_slider, autoclose_map = false;
    var sameAzimuth = false, virtual_staging = false, config_alt = null;
    var interval_access_time_avg = null, access_time_avg = 0, access_time_id = 0, enable_visitor_rt = true, interval_visitor_rt = 5000, interval_auto_close_poi;
    var auto_show_slider = 0, nav_slider = 0;
    var form_lightbox = null, form_enable = false, form_content = '', product_lightbox = null;
    var slider_index = 0, index_initial = 0, current_id_panorama = null, current_panorama_type = 'image', drag_slider = false, drag_slider_start=0, drag_slider_end=0;
    var live_session_connected = false, interval_live_session = null, id_live_session = '', call_session = null, api_jitsi = null, webcam_audio = true, webcam_video=true;
    var poi_open = false, video_opened = false, map_opened = false, schedule_enabled = false;
    var interval_automatic_presentation = null, presentation_type = 'manual', auto_presentation_speed = 0;
    var whatsapp_chat = false, whatsapp_number = '', show_facebook = false;
    var transition_loading = true, transition_time = 250, transition_zoom = 20, transition_fadeout = 400, transition_effect = 'fade';
    var flyin_scene, flyin_renderer, flyin_camera, flyin_geometry, flyin_mesh, flyin_texture, flyin_material, flyin_loader;
    var interval_check_pois_schedule = null, interval_video_loading_check = null, interval_position = null, goto_timeout = null;
    var video_p = null, app_p = null, app_p_vr = null, loader_p = null, loader_p_vr = null;
    var external = 0, external_url = '';
    var map_tour = [], map_tour_points = [], map_tour_l = null, map_selector_open = false;
    var announce = null, announce_interval = null, announce_open = false;
    var preload_panoramas = 1, click_anywhere = 0, hide_markers = 0, hover_markers = 0;
    var time_version = new Date().getMilliseconds(), in_idle=false;
    var poi_embed_ids = [], marker_embed_ids = [], video_embeds = [], video360_poi = [];
    var interval_adjust_embed_helpers_all = null, count_adjust_embed_helpers_all = 0;
    var pan_speed_vt = 1, pan_speed_mobile_vt = 2, friction_vt = 0.1, friction_mobile_vt = 0.4;
    var camera_dollhouse, scene_dollhouse,  renderer_dollhouse, css_renderer_dollhouse, controls_dollhouse, group_rooms_dollhouse = [];
    var domEvents_dollhouse, dollhouse_div, count_loaded_texture_dollhouse = 0, interval_load_texture_dollhouse;
    var rooms_dollhouse = [], levels_dollhouse = [], textures_dollhouse = [], meshes_dollhouse = [], geometries_dollhouse=[], pointers_c_dollhouse=[], pointers_t_dollhouse=[];
    var dollhouse_loaded = false, json_dollhouse = '', array_dollhouse = [], can_click_pointer_dollhouse = false, is_animating_pointer_dollhouse = false, level_sel_dollhouse = 'all', camera_pos_dollhouse = '';
    var event_simulate_click = new Event('simulate_click');
    window.poi_embed_originals_pos = [];
    window.marker_embed_originals_pos = [];
    window.poi_box_open = false;
    window.sync_virtual_staging_enabled = false;
    window.changed_room_alt = false;
    window.changed_room_alt_poi = false;
    window.sync_poi_embed_enabled = false;
    window.sync_marker_embed_enabled = false;
    window.c_width = window.innerWidth || document.documentElement.clientWidth || document.body.clientWidth;
    window.c_height = window.innerHeight || document.documentElement.clientHeight || document.body.clientHeight;

    window.init_svt = function () {
        progress_circle = new ProgressBar.Circle('.progress-circle', {
            strokeWidth: 5,
            easing: 'easeInOut',
            duration: 500,
            color: '#ffffff',
            trailColor: 'rgba(255,255,255,0.15)',
            trailWidth: 5,
            text: {
                autoStyleContainer: false
            },
            step: function(state, circle) {
                var value = Math.round(circle.value() * 100);
                if (value === 0) {
                    circle.setText('');
                } else {
                    circle.setText(value);
                }
            }
        });
        progress_circle.animate(0);
        var userAgent = navigator.userAgent.toLowerCase();
        if (userAgent.indexOf(' electron/') === -1) {
            if( window.location.protocol == 'file:' ){
                alert("Due to browser security restrictions, a web server must be used locally as well.");
                window.stop();
                throw new Error("Due to browser security restrictions, a web server must be used locally as well.");
            }
        }
        var md = new MobileDetect(window.navigator.userAgent);
        if(md.mobile()==null) {
            window.is_mobile = false;
        } else {
            window.is_mobile = true;
        }
        if(window.hide_loading==0) {
            $('.loading').removeClass('hidden');
            $('#background_loading').addClass('background_opacity');
        } else {
            if(window.auto_start==1 && window.password_protected==0) {
                $('.loading').addClass('hidden');
                $('#background_loading').removeClass('background_opacity');
            }
        }
        if(window.background_video!='') {
            var loop = '';
            if(window.background_video_delay>0) {
                loop = 'loop';
            }
            var video_html = '<video id="video_loading" preload="auto" '+loop+' muted autoplay playsinline webkit-playsinline ><source src="content/'+window.background_video+'" type="video/mp4"></video>';
            $('#background_loading').html(video_html).promise().done(function () {
                if(window.background_video_delay==0) {
                    document.getElementById('video_loading').addEventListener('ended',video_loading_ended,false);
                    function video_loading_ended() {
                        window.video_loading_ended = true;
                    }
                } else {
                    window.interval_background_video_elapsed = setInterval(function () {
                        window.background_video_elapsed++;
                        if(window.background_video_elapsed>=window.background_video_delay) {
                            clearInterval(window.interval_background_video_elapsed);
                            window.video_loading_ended = true;
                        }
                    },1000);
                }
                $('#background_loading').fadeIn(100);
                if(window.auto_start) {
                    start_vt();
                } else {
                    show_icon_play();
                }
            });
        } else if(background_image!='') {
            var background_image_load = new Image();
            $(background_image_load).on('load',function () {
                $('#background_loading').css('background-image',"url('content/"+window.background_image+"')");
                $('#background_loading').fadeIn(100);
                if(window.auto_start) {
                    progress_circle.animate(0.1);
                    start_vt();
                } else {
                    show_icon_play();
                }
            }).attr('src','content/'+window.background_image);
        } else {
            if(window.auto_start) {
                progress_circle.animate(0.1);
                start_vt();
            } else {
                show_icon_play();
            }
        }
    };

    window.get_rooms = function (code) {
        $('.progress-circle').fadeIn();
        progress_circle.animate(0.2);
        if(window.export_mode==1) {
            var rsp = JSON.parse(window.rooms_json);
            if(rsp.status=='ok') {
                rooms_init(rsp);
            }
        } else {
            $.ajax({
                url: "ajax/get_rooms.php",
                type: "POST",
                data: {
                    code: code,
                    export_mode: window.export_mode,
                    preview: window.preview,
                    ip_visitor: window.ip_visitor
                },
                async: true,
                success: function (json) {
                    var rsp = JSON.parse(json);
                    if(rsp.status=='ok') {
                        rooms_init(rsp);
                    }
                }
            });
        }
    };

    function rooms_init(rsp) {
        progress_circle.animate(0.3);
        $('.loading').show();
        external = parseInt(rsp.external);
        external_url = rsp.external_url;
        if(external==1) {
            progress_circle.animate(1);
            setTimeout(function () {
                if(window.external_embed==1) {
                    $('body').append('<iframe id="external_iframe" style="display:block;position:absolute;top:0;left:0;bottom:0;right:0;width:100%;max-width:100%;height:100%;max-height:100%;border:0;opacity:0" src="'+external_url+'" width="100%" frameborder="none" allowfullscreen allow="gyroscope; accelerometer; xr; microphone *" loading="lazy"></iframe>');                    setTimeout(function () {
                        $('div').not('#external_iframe').remove();
                        $('#external_iframe').css('opacity',1);
                    },1000);
                } else {
                    location.href = external_url;
                }
            },500);
            return;
        }
        array_base64 = rsp.array_base64;
        name_virtual_tour = rsp.name_virtualtour;
        author = rsp.author;
        hfov = parseInt(rsp.hfov);
        min_hfov = parseInt(rsp.min_hfov);
        max_hfov = parseInt(rsp.max_hfov);
        song_bg = rsp.song;
        song_autoplay = parseInt(rsp.song_autoplay);
        nadir_logo = rsp.nadir_logo;
        nadir_size = rsp.nadir_size;
        autorotate_speed = parseInt(rsp.autorotate_speed);
        autorotate_inactivity = parseInt(rsp.autorotate_inactivity);
        arrows_nav = parseInt(rsp.arrows_nav);
        if ('SpeechRecognition' in window || 'webkitSpeechRecognition' in window) {
            voice_commands_enable = parseInt(rsp.voice_commands);
        } else {
            voice_commands_enable = 0;
        }
        show_audio = parseInt(rsp.show_audio);
        show_vt_title = parseInt(rsp.show_vt_title);
        show_logo = parseInt(rsp.show_logo);
        show_compass = parseInt(rsp.compass);
        show_gallery = parseInt(rsp.show_gallery);
        show_info = parseInt(rsp.show_info);
        show_custom = parseInt(rsp.show_custom);
        show_dollhouse = parseInt(rsp.show_dollhouse);
        json_dollhouse = rsp.dollhouse;
        show_icons_toggle = parseInt(rsp.show_icons_toggle);
        show_autorotation_toggle = parseInt(rsp.show_autorotation_toggle);
        show_nav_control = parseInt(rsp.show_nav_control);
        show_presentation = parseInt(rsp.show_presentation);
        show_share = parseInt(rsp.show_share);
        if (window.DeviceOrientationEvent && location.protocol == 'https:' && (navigator.userAgent.toLowerCase().indexOf('mobi') >= 0 || /Pacific Build.+OculusBrowser.+SamsungBrowser.+MobileVR/i.test(window.navigator.userAgent))) {
            show_device_orientation = parseInt(rsp.show_device_orientation);
            drag_device_orientation = parseInt(rsp.drag_device_orientation);
            show_webvr = parseInt(rsp.show_webvr);
        } else {
            show_device_orientation = 0;
            show_webvr = 0;
        }
        if ('fullscreen' in document || 'mozFullScreen' in document || 'webkitIsFullScreen' in document || 'msFullscreenElement' in document) {
            show_fullscreen = parseInt(rsp.show_fullscreen);
        } else {
            show_fullscreen = 0;
        }
        show_map = parseInt(rsp.show_map);
        show_map_tour = parseInt(rsp.show_map_tour);
        show_live_session = parseInt(rsp.live_session);
        if(window.live_session_force) { show_live_session=true; }
        show_meeting = parseInt(rsp.meeting);
        if(window.meeting_force) { show_meeting=true; }
        show_annotations = parseInt(rsp.show_annotations);
        show_list_alt = parseInt(rsp.show_list_alt);
        list_alt = rsp.list_alt;
        sameAzimuth = parseInt(rsp.sameAzimuth);
        auto_show_slider = parseInt(rsp.auto_show_slider);
        nav_slider = parseInt(rsp.nav_slider);
        form_enable = parseInt(rsp.form_enable);
        form_content = rsp.form_content;
        intro_desktop = rsp.intro_desktop;
        intro_mobile = rsp.intro_mobile;
        presentation_type = rsp.presentation_type;
        auto_presentation_speed = parseInt(rsp.auto_presentation_speed);
        whatsapp_chat = parseInt(rsp.whatsapp_chat);
        show_facebook = parseInt(rsp.show_facebook);
        whatsapp_number = rsp.whatsapp_number;
        transition_time = parseInt(rsp.transition_time);
        transition_fadeout = parseInt(rsp.transition_fadeout);
        transition_zoom = parseInt(rsp.transition_zoom);
        transition_loading = parseInt(rsp.transition_loading);
        transition_effect = rsp.transition_effect;
        keyboard_mode = parseInt(rsp.keyboard_mode);
        preload_panoramas = parseInt(rsp.preload_panoramas);
        click_anywhere = parseInt(rsp.click_anywhere);
        hide_markers = parseInt(rsp.hide_markers);
        hover_markers = parseInt(rsp.hover_markers);
        autoclose_menu = parseInt(rsp.autoclose_menu);
        autoclose_list_alt = parseInt(rsp.autoclose_list_alt);
        autoclose_slider = parseInt(rsp.autoclose_slider);
        autoclose_map = parseInt(rsp.autoclose_map);
        pan_speed_vt = parseFloat(rsp.pan_speed);
        pan_speed_mobile_vt = parseFloat(rsp.pan_speed_mobile);
        friction_vt = parseFloat(rsp.friction);
        friction_mobile_vt = parseFloat(rsp.friction_mobile);
        enable_visitor_rt = parseInt(rsp.enable_visitor_rt);
        interval_visitor_rt = parseInt(rsp.interval_visitor_rt);
        if(window.preview==1) {
            show_audio = 0;
            song_bg = '';
            show_list_alt = 0;
            auto_show_slider = 2;
            show_map = 0;
            show_map_tour = 0;
            intro_desktop = '';
            intro_mobile = '';
            show_annotations = 0;
            arrows_nav = 0;
            show_logo = 0;
            show_fullscreen = 0;
            voice_commands_enable = 0;
            show_info = 0;
            show_custom = 0;
            show_dollhouse = 0;
            show_gallery = 0;
            show_facebook = 0;
            whatsapp_chat = 0;
            show_compass = 0;
            show_icons_toggle = 0;
            show_autorotation_toggle = 0;
            show_presentation = 0;
            show_share = 0;
            form_enable = 0;
            show_meeting = 0;
            show_live_session = 0;
            show_webvr = 0;
            show_device_orientation = 0;
            show_nav_control = 0;
            show_vt_title = 0;
        }
        if(window.is_mobile) {
            if(intro_mobile!='') {
                $('.intro_img img').attr('src','content/'+intro_mobile);
            }
        } else {
            if(intro_desktop!='') {
                $('.intro_img img').attr('src','content/'+intro_desktop);
            }
        }
        if(rsp.rooms.length==1) {
            arrows_nav=0;
        }
        if(nav_slider==2) {
            $('.list_slider #list_left').addClass('hidden');
            $('.list_slider #list_right').addClass('hidden');
        }
        jQuery.each(rsp.rooms, function(index, room) {
            panoramas[index] = {};
            panoramas[index].id = room.id;
            panoramas[index].name = room.name;
            panoramas[index].logo = room.logo;
            panoramas[index].type = room.type;
            panoramas[index].filters = room.filters;
            panoramas[index].effect = room.effect;
            panoramas[index].virtual_staging = parseInt(room.virtual_staging);
            panoramas[index].main_view_tooltip = room.main_view_tooltip;
            panoramas[index].multires = parseInt(room.multires);
            panoramas[index].multires_dir = room.multires_dir;
            panoramas[index].multires_config = room.multires_config;
            panoramas[index].panorama_blob = '';
            panoramas[index].background_color = room.background_color.split(',');
            panoramas[index].id_poi_autoopen = room.id_poi_autoopen;
            panoramas[index].panorama_url = room.panorama_url;
            panoramas[index].panorama_json = "panoramas/"+room.panorama_json;
            panoramas[index].panorama_3d = "panoramas/"+room.panorama_3d;
            if(window.is_mobile) {
                if(parseInt(room.pano_mobile)==1) {
                    panoramas[index].panorama_image = "panoramas/mobile/"+room.panorama_image;
                } else {
                    panoramas[index].panorama_image = "panoramas/"+room.panorama_image;
                }
            } else {
                panoramas[index].panorama_image = "panoramas/"+room.panorama_image;
            }
            panoramas[index].panorama_video = "videos/"+room.panorama_video;
            if(room.thumb_image!='') {
                panoramas[index].thumb_image = "panoramas/thumb_custom/"+room.thumb_image;
            } else {
                panoramas[index].thumb_image = "panoramas/preview/"+room.panorama_image;
            }
            panoramas[index].array_rooms_alt = {};
            jQuery.each(room.array_rooms_alt, function(index_alt, room_alt) {
                panoramas[index].array_rooms_alt[index_alt] = {};
                panoramas[index].array_rooms_alt[index_alt].id = parseInt(room_alt.id);
                panoramas[index].array_rooms_alt[index_alt].poi = parseInt(room_alt.poi);
                panoramas[index].array_rooms_alt[index_alt].view_tooltip = room_alt.view_tooltip;
                panoramas[index].array_rooms_alt[index_alt].multires = parseInt(room_alt.multires);
                panoramas[index].array_rooms_alt[index_alt].multires_dir = room_alt.multires_dir;
                panoramas[index].array_rooms_alt[index_alt].multires_config = room_alt.multires_config;
                if(window.is_mobile) {
                    if(parseInt(room_alt.pano_mobile)==1) {
                        panoramas[index].array_rooms_alt[index_alt].panorama_image = "panoramas/mobile/"+room_alt.panorama_image;
                    } else {
                        panoramas[index].array_rooms_alt[index_alt].panorama_image = "panoramas/"+room_alt.panorama_image;
                    }
                } else {
                    panoramas[index].array_rooms_alt[index_alt].panorama_image = "panoramas/"+room_alt.panorama_image;
                }
                panoramas[index].array_rooms_alt[index_alt].thumb_image = "panoramas/preview/"+room_alt.panorama_image;
                panoramas[index].array_rooms_alt[index_alt].panorama_blob = '';
            });
            panoramas[index].northOffset = parseInt(room.northOffset);
            panoramas[index].pitch = parseInt(room.pitch);
            panoramas[index].yaw = parseInt(room.yaw);
            panoramas[index].hfov = parseInt(room.hfov);
            panoramas[index].h_pitch = parseInt(room.h_pitch);
            panoramas[index].h_roll = parseInt(room.h_roll);
            panoramas[index].allow_pitch = parseInt(room.allow_pitch);
            panoramas[index].allow_hfov = parseInt(room.allow_hfov);
            panoramas[index].min_pitch = parseInt(room.min_pitch);
            panoramas[index].max_pitch = parseInt(room.max_pitch);
            panoramas[index].min_yaw = parseInt(room.min_yaw);
            panoramas[index].max_yaw = parseInt(room.max_yaw);
            panoramas[index].haov = parseInt(room.haov);
            panoramas[index].vaov = parseInt(room.vaov);
            panoramas[index].id_map = parseInt(room.id_map);
            panoramas[index].map_north = 0;
            panoramas[index].map_top = parseInt(room.map_top);
            panoramas[index].map_left = parseInt(room.map_left);
            panoramas[index].lat = room.lat;
            panoramas[index].lon = room.lon;
            panoramas[index].visible_list = parseInt(room.visible_list);
            panoramas[index].song = room.song;
            panoramas[index].song_bg_volume = parseFloat(room.song_bg_volume);
            panoramas[index].audio_track_enable = parseInt(room.audio_track_enable);
            panoramas[index].annotation_title = room.annotation_title;
            panoramas[index].annotation_description = room.annotation_description;
            panoramas[index].protect_type = room.protect_type;
            panoramas[index].passcode_title = room.passcode_title;
            panoramas[index].passcode_description = room.passcode_description;
            panoramas[index].protect_send_email = room.protect_send_email;
            if(room.protect_send_email) {
                panoramas[index].protect_email = room.protect_email;
            } else {
                panoramas[index].protect_email = '';
            }
            panoramas[index].protected = parseInt(room.protected);
            panoramas[index].transition_override = parseInt(room.transition_override);
            if(panoramas[index].transition_override==1) {
                panoramas[index].transition_time = parseInt(room.transition_time);
                panoramas[index].transition_zoom = parseInt(room.transition_zoom);
                panoramas[index].transition_fadeout = parseInt(room.transition_fadeout);
                panoramas[index].transition_effect = room.transition_effect;
            }
            panoramas[index].hotSpots = [];
            panoramas[index].hotSpots_vr = [];
            panoramas[index].hotSpots_alt = [];
            if(nadir_logo!='') {
                panoramas[index].hotSpots.push({
                    "type": "nadir",
                    "view_type": 0,
                    "object": "nadir",
                    "transform3d": false,
                    "pitch": -90,
                    "yaw": 0,
                    "rotateX": 0,
                    "rotateZ": 0,
                    "scale": true,
                    "cssClass": "nadir-hotspot-"+nadir_size,
                    "createTooltipFunc": hotspot_nadir,
                    "createTooltipArgs": nadir_logo
                });
                panoramas[index].hotSpots_alt.push({
                    "type": "nadir",
                    "view_type": 0,
                    "object": "nadir",
                    "transform3d": false,
                    "pitch": -90,
                    "yaw": 0,
                    "rotateX": 0,
                    "rotateZ": 0,
                    "scale": true,
                    "cssClass": "nadir-hotspot-"+nadir_size+" custom-hotspot_alt",
                    "createTooltipFunc": hotspot_nadir,
                    "createTooltipArgs": nadir_logo
                });
                panoramas[index].hotSpots_vr.push({
                    "type": "nadir",
                    "view_type": 0,
                    "object": "nadir",
                    "transform3d": false,
                    "pitch": -90,
                    "yaw": 0,
                    "rotateX": 0,
                    "rotateZ": 0,
                    "scale": true,
                    "cssClass": "nadir-hotspot-small_vr",
                    "createTooltipFunc": hotspot_nadir,
                    "createTooltipArgs": nadir_logo
                });
            }
            jQuery.each(room.markers, function(index_m, marker_m) {
                if(marker_m.object=='poi' && marker_m.schedule!='') {
                    schedule_enabled = true;
                }
                switch(marker_m.object) {
                    case 'marker':
                        if(marker_m.embed_type=='') {
                            panoramas[index].hotSpots.push({
                                "id": marker_m.id,
                                "type": marker_m.type,
                                "view_type": 0,
                                "object": "marker",
                                "transform3d": false,
                                "tooltip_type": marker_m.tooltip_type,
                                "pitch": parseFloat(marker_m.pitch),
                                "yaw": parseFloat(marker_m.yaw),
                                "rotateX": parseInt(marker_m.rotateX),
                                "rotateZ": parseInt(marker_m.rotateZ),
                                "size_scale": parseFloat(marker_m.size_scale),
                                "animation": marker_m.animation,
                                "cssClass": "custom-hotspot",
                                "createTooltipFunc": hotspot,
                                "createTooltipArgs": marker_m,
                                "clickHandlerFunc": goto,
                                "clickHandlerArgs": [marker_m.id_room_target,parseInt(marker_m.pitch),parseInt(marker_m.yaw),marker_m.pitch_room_target,marker_m.yaw_room_target,parseInt(marker_m.lookat)]
                            });
                            panoramas[index].hotSpots_alt.push({
                                "id": marker_m.id,
                                "type": marker_m.type,
                                "view_type": 0,
                                "object": "marker",
                                "transform3d": false,
                                "tooltip_type": marker_m.tooltip_type,
                                "pitch": parseFloat(marker_m.pitch),
                                "yaw": parseFloat(marker_m.yaw),
                                "rotateX": parseInt(marker_m.rotateX),
                                "rotateZ": parseInt(marker_m.rotateZ),
                                "size_scale": parseFloat(marker_m.size_scale),
                                "animation": marker_m.animation,
                                "cssClass": "custom-hotspot custom-hotspot_alt",
                                "createTooltipFunc": hotspot,
                                "createTooltipArgs": marker_m,
                                "clickHandlerFunc": goto,
                                "clickHandlerArgs": [marker_m.id_room_target,parseInt(marker_m.pitch),parseInt(marker_m.yaw),marker_m.pitch_room_target,marker_m.yaw_room_target,parseInt(marker_m.lookat)]
                            });
                            panoramas[index].hotSpots_vr.push({
                                "id": marker_m.id,
                                "type": marker_m.type,
                                "view_type": 0,
                                "object": "marker",
                                "transform3d": false,
                                "tooltip_type": marker_m.tooltip_type,
                                "pitch": parseFloat(marker_m.pitch),
                                "yaw": parseFloat(marker_m.yaw),
                                "rotateX": parseInt(marker_m.rotateX),
                                "rotateZ": parseInt(marker_m.rotateZ),
                                "size_scale": parseFloat(marker_m.size_scale),
                                "animation": "none",
                                "cssClass": "custom-hotspot custom-hotspot_vr",
                                "createTooltipFunc": hotspot,
                                "createTooltipArgs": marker_m,
                                "clickHandlerFunc": goto,
                                "clickHandlerArgs": [marker_m.id_room_target,parseInt(marker_m.pitch),parseInt(marker_m.yaw),marker_m.pitch_room_target,marker_m.yaw_room_target,parseInt(marker_m.lookat)]
                            });
                        } else {
                            panoramas[index].hotSpots.push({
                                "id": marker_m.id,
                                "type": marker_m.embed_type,
                                "view_type": 0,
                                "object": "marker_embed",
                                "transform3d": parseInt(marker_m.transform3d),
                                "tooltip_type": marker_m.tooltip_type,
                                "pitch": parseFloat(marker_m.pitch),
                                "yaw": parseFloat(marker_m.yaw),
                                "rotateX": 0,
                                "rotateZ": 0,
                                "size_scale": 1,
                                "cssClass": "hotspot-embed",
                                "animation": "none",
                                "createTooltipFunc": hotspot_embed_m,
                                "createTooltipArgs": marker_m,
                                "clickHandlerFunc": goto,
                                "clickHandlerArgs": [marker_m.id_room_target,parseInt(marker_m.pitch),parseInt(marker_m.yaw),marker_m.pitch_room_target,marker_m.yaw_room_target,parseInt(marker_m.lookat)]
                            });
                            panoramas[index].hotSpots_alt.push({
                                "id": marker_m.id,
                                "type": marker_m.embed_type,
                                "view_type": 0,
                                "object": "marker_embed",
                                "transform3d": parseInt(marker_m.transform3d),
                                "tooltip_type": marker_m.tooltip_type,
                                "pitch": parseFloat(marker_m.pitch),
                                "yaw": parseFloat(marker_m.yaw),
                                "rotateX": 0,
                                "rotateZ": 0,
                                "size_scale": 1,
                                "animation": "none",
                                "cssClass": "hotspot-embed custom-hotspot_alt",
                                "createTooltipFunc": hotspot_embed_m,
                                "createTooltipArgs": marker_m,
                                "clickHandlerFunc": goto,
                                "clickHandlerArgs": [marker_m.id_room_target,parseInt(marker_m.pitch),parseInt(marker_m.yaw),marker_m.pitch_room_target,marker_m.yaw_room_target,parseInt(marker_m.lookat)]
                            });
                            panoramas[index].hotSpots_vr.push({
                                "id": marker_m.id,
                                "type": marker_m.embed_type,
                                "view_type": 0,
                                "object": "marker",
                                "transform3d": false,
                                "tooltip_type": marker_m.tooltip_type,
                                "pitch": parseFloat(marker_m.pitch),
                                "yaw": parseFloat(marker_m.yaw),
                                "rotateX": 0,
                                "rotateZ": 0,
                                "size_scale": 1,
                                "animation": "none",
                                "cssClass": "custom-hotspot custom-hotspot_vr",
                                "createTooltipFunc": hotspot_embed_m,
                                "createTooltipArgs": marker_m,
                                "clickHandlerFunc": goto,
                                "clickHandlerArgs": [marker_m.id_room_target,parseInt(marker_m.pitch),parseInt(marker_m.yaw),marker_m.pitch_room_target,marker_m.yaw_room_target,parseInt(marker_m.lookat)]
                            });
                            var marker_embed_helpers = marker_m.embed_coords.split("|");
                            marker_embed_helpers[0] = marker_embed_helpers[0].split(",");
                            marker_embed_helpers[1] = marker_embed_helpers[1].split(",");
                            marker_embed_helpers[2] = marker_embed_helpers[2].split(",");
                            marker_embed_helpers[3] = marker_embed_helpers[3].split(",");
                            jQuery.each(marker_embed_helpers, function(index_h, marker_embed_helper) {
                                panoramas[index].hotSpots.push({
                                    "type": 'pointer',
                                    "view_type": 0,
                                    "object": "marker_embed_helper",
                                    "transform3d": false,
                                    "pitch": parseFloat(marker_embed_helper[0]),
                                    "yaw": parseFloat(marker_embed_helper[1]),
                                    "size_scale": 1,
                                    "rotateX": 0,
                                    "rotateZ": 0,
                                    "draggable": false,
                                    "cssClass": "hotspot-helper",
                                    "createTooltipFunc": hotspot_embed_helper_m,
                                    "createTooltipArgs": [marker_m.id,(index_h+1)]
                                });
                            });
                        }
                        break;
                    case 'poi':
                        switch(marker_m.embed_type) {
                            case 'image':
                            case 'text':
                            case 'selection':
                                switch(marker_m.type) {
                                    case 'image':
                                    case 'gallery':
                                    case 'video360':
                                    case 'audio':
                                    case 'html':
                                    case 'html_sc':
                                    case 'form':
                                    case 'google_maps':
                                    case 'object360':
                                    case 'object3d':
                                    case 'download':
                                    case 'video':
                                    case 'link':
                                    case 'link_ext':
                                    case 'lottie':
                                        panoramas[index].hotSpots.push({
                                            "id": marker_m.id,
                                            "type": marker_m.embed_type,
                                            "view_type": parseInt(marker_m.view_type),
                                            "object": "poi_embed",
                                            "transform3d": parseInt(marker_m.transform3d),
                                            "tooltip_type": marker_m.tooltip_type,
                                            "pitch": parseFloat(marker_m.pitch),
                                            "yaw": parseFloat(marker_m.yaw),
                                            "rotateX": parseInt(marker_m.rotateX),
                                            "rotateZ": parseInt(marker_m.rotateZ),
                                            "size_scale": parseFloat(marker_m.size_scale),
                                            "animation": "none",
                                            "cssClass": "hotspot-embed",
                                            "createTooltipFunc": hotspot_embed,
                                            "createTooltipArgs": marker_m,
                                            "clickHandlerFunc": view_content,
                                            "clickHandlerArgs": marker_m
                                        });
                                        break;
                                    case 'switch_pano':
                                        panoramas[index].hotSpots.push({
                                            "id": marker_m.id,
                                            "type": marker_m.embed_type,
                                            "view_type": parseInt(marker_m.view_type),
                                            "object": "poi_embed",
                                            "transform3d": parseInt(marker_m.transform3d),
                                            "tooltip_type": marker_m.tooltip_type,
                                            "pitch": parseFloat(marker_m.pitch),
                                            "yaw": parseFloat(marker_m.yaw),
                                            "rotateX": parseInt(marker_m.rotateX),
                                            "rotateZ": parseInt(marker_m.rotateZ),
                                            "size_scale": parseFloat(marker_m.size_scale),
                                            "animation": "none",
                                            "cssClass": "hotspot-embed",
                                            "createTooltipFunc": hotspot_embed,
                                            "createTooltipArgs": marker_m,
                                            "clickHandlerFunc": change_room_alt_poi,
                                            "clickHandlerArgs": parseInt(marker_m.content)
                                        });
                                        break;
                                    default:
                                        panoramas[index].hotSpots.push({
                                            "id": marker_m.id,
                                            "type": marker_m.embed_type,
                                            "view_type": parseInt(marker_m.view_type),
                                            "object": "poi_embed",
                                            "transform3d": parseInt(marker_m.transform3d),
                                            "tooltip_type": marker_m.tooltip_type,
                                            "pitch": parseFloat(marker_m.pitch),
                                            "yaw": parseFloat(marker_m.yaw),
                                            "rotateX": parseInt(marker_m.rotateX),
                                            "rotateZ": parseInt(marker_m.rotateZ),
                                            "size_scale": parseFloat(marker_m.size_scale),
                                            "animation": "none",
                                            "cssClass": "hotspot-embed",
                                            "createTooltipFunc": hotspot_embed,
                                            "createTooltipArgs": marker_m
                                        });
                                        break;
                                }
                                if(marker_m.transform3d==1) {
                                    var poi_embed_helpers = marker_m.embed_coords.split("|");
                                    poi_embed_helpers[0] = poi_embed_helpers[0].split(",");
                                    poi_embed_helpers[1] = poi_embed_helpers[1].split(",");
                                    poi_embed_helpers[2] = poi_embed_helpers[2].split(",");
                                    poi_embed_helpers[3] = poi_embed_helpers[3].split(",");
                                    jQuery.each(poi_embed_helpers, function(index_h, poi_embed_helper) {
                                        panoramas[index].hotSpots.push({
                                            "type": 'pointer',
                                            "view_type": 0,
                                            "object": "poi_embed_helper",
                                            "transform3d": false,
                                            "pitch": parseFloat(poi_embed_helper[0]),
                                            "yaw": parseFloat(poi_embed_helper[1]),
                                            "size_scale": 1,
                                            "rotateX": 0,
                                            "rotateZ": 0,
                                            "draggable": false,
                                            "cssClass": "hotspot-helper",
                                            "createTooltipFunc": hotspot_embed_helper,
                                            "createTooltipArgs": [marker_m.id,(index_h+1)]
                                        });
                                    });
                                }
                                break;
                            case 'gallery':
                            case 'video':
                            case 'video_transparent':
                            case 'video_chroma':
                            case 'link':
                                switch(marker_m.type) {
                                    default:
                                        panoramas[index].hotSpots.push({
                                            "id": marker_m.id,
                                            "type": marker_m.embed_type,
                                            "view_type": parseInt(marker_m.view_type),
                                            "object": "poi_embed",
                                            "transform3d": parseInt(marker_m.transform3d),
                                            "tooltip_type": marker_m.tooltip_type,
                                            "pitch": parseFloat(marker_m.pitch),
                                            "yaw": parseFloat(marker_m.yaw),
                                            "rotateX": parseInt(marker_m.rotateX),
                                            "rotateZ": parseInt(marker_m.rotateZ),
                                            "size_scale": parseFloat(marker_m.size_scale),
                                            "cssClass": "hotspot-embed",
                                            "animation": "none",
                                            "createTooltipFunc": hotspot_embed,
                                            "createTooltipArgs": marker_m,
                                            "clickHandlerFunc": empty_function,
                                            "clickHandlerArgs": null,
                                        });
                                        if(marker_m.transform3d==1) {
                                            var poi_embed_helpers = marker_m.embed_coords.split("|");
                                            poi_embed_helpers[0] = poi_embed_helpers[0].split(",");
                                            poi_embed_helpers[1] = poi_embed_helpers[1].split(",");
                                            poi_embed_helpers[2] = poi_embed_helpers[2].split(",");
                                            poi_embed_helpers[3] = poi_embed_helpers[3].split(",");
                                            jQuery.each(poi_embed_helpers, function (index_h, poi_embed_helper) {
                                                panoramas[index].hotSpots.push({
                                                    "type": 'pointer',
                                                    "view_type": 0,
                                                    "object": "poi_embed_helper",
                                                    "transform3d": false,
                                                    "pitch": parseFloat(poi_embed_helper[0]),
                                                    "yaw": parseFloat(poi_embed_helper[1]),
                                                    "size_scale": 1,
                                                    "rotateX": 0,
                                                    "rotateZ": 0,
                                                    "draggable": false,
                                                    "cssClass": "hotspot-helper",
                                                    "createTooltipFunc": hotspot_embed_helper,
                                                    "createTooltipArgs": [marker_m.id, (index_h + 1)]
                                                });
                                            });
                                        }
                                        break;
                                }
                                break;
                            default:
                                switch(marker_m.type) {
                                    case 'image':
                                    case 'gallery':
                                    case 'video360':
                                    case 'audio':
                                    case 'html':
                                    case 'html_sc':
                                    case 'form':
                                    case 'google_maps':
                                    case 'object360':
                                    case 'object3d':
                                    case 'download':
                                    case 'video':
                                    case 'link':
                                    case 'link_ext':
                                    case 'lottie':
                                    case 'product':
                                        panoramas[index].hotSpots.push({
                                            "id": marker_m.id,
                                            "type": marker_m.type,
                                            "view_type": parseInt(marker_m.view_type),
                                            "object": "poi",
                                            "transform3d": false,
                                            "tooltip_type": marker_m.tooltip_type,
                                            "pitch": parseFloat(marker_m.pitch),
                                            "yaw": parseFloat(marker_m.yaw),
                                            "rotateX": parseInt(marker_m.rotateX),
                                            "rotateZ": parseInt(marker_m.rotateZ),
                                            "size_scale": parseFloat(marker_m.size_scale),
                                            "animation": marker_m.animation,
                                            "cssClass": "custom-hotspot-content",
                                            "createTooltipFunc": hotspot_content,
                                            "createTooltipArgs": marker_m,
                                            "clickHandlerFunc": view_content,
                                            "clickHandlerArgs": marker_m
                                        });
                                        panoramas[index].hotSpots_alt.push({
                                            "id": marker_m.id,
                                            "type": marker_m.type,
                                            "view_type": parseInt(marker_m.view_type),
                                            "object": "poi",
                                            "transform3d": false,
                                            "tooltip_type": marker_m.tooltip_type,
                                            "pitch": parseFloat(marker_m.pitch),
                                            "yaw": parseFloat(marker_m.yaw),
                                            "rotateX": parseInt(marker_m.rotateX),
                                            "rotateZ": parseInt(marker_m.rotateZ),
                                            "size_scale": parseFloat(marker_m.size_scale),
                                            "animation": marker_m.animation,
                                            "cssClass": "custom-hotspot-content custom-hotspot_alt",
                                            "createTooltipFunc": hotspot_content,
                                            "createTooltipArgs": marker_m,
                                            "clickHandlerFunc": view_content,
                                            "clickHandlerArgs": marker_m
                                        });
                                        break;
                                    case 'switch_pano':
                                        panoramas[index].hotSpots.push({
                                            "id": marker_m.id,
                                            "type": marker_m.type,
                                            "view_type": parseInt(marker_m.view_type),
                                            "object": "poi",
                                            "transform3d": false,
                                            "tooltip_type": marker_m.tooltip_type,
                                            "pitch": parseFloat(marker_m.pitch),
                                            "yaw": parseFloat(marker_m.yaw),
                                            "rotateX": parseInt(marker_m.rotateX),
                                            "rotateZ": parseInt(marker_m.rotateZ),
                                            "size_scale": parseFloat(marker_m.size_scale),
                                            "animation": marker_m.animation,
                                            "cssClass": "custom-hotspot-content",
                                            "createTooltipFunc": hotspot_content,
                                            "createTooltipArgs": marker_m,
                                            "clickHandlerFunc": change_room_alt_poi,
                                            "clickHandlerArgs": parseInt(marker_m.content)
                                        });
                                        panoramas[index].hotSpots_alt.push({
                                            "id": marker_m.id,
                                            "type": marker_m.type,
                                            "view_type": parseInt(marker_m.view_type),
                                            "object": "poi",
                                            "transform3d": false,
                                            "tooltip_type": marker_m.tooltip_type,
                                            "pitch": parseFloat(marker_m.pitch),
                                            "yaw": parseFloat(marker_m.yaw),
                                            "rotateX": parseInt(marker_m.rotateX),
                                            "rotateZ": parseInt(marker_m.rotateZ),
                                            "size_scale": parseFloat(marker_m.size_scale),
                                            "animation": marker_m.animation,
                                            "cssClass": "custom-hotspot-content custom-hotspot_alt",
                                            "createTooltipFunc": hotspot_content,
                                            "createTooltipArgs": marker_m,
                                            "clickHandlerFunc": change_room_alt_poi,
                                            "clickHandlerArgs": parseInt(marker_m.content)
                                        });
                                        break;
                                    default:
                                        panoramas[index].hotSpots.push({
                                            "id": marker_m.id,
                                            "type": marker_m.type,
                                            "view_type": parseInt(marker_m.view_type),
                                            "object": "poi",
                                            "transform3d": false,
                                            "tooltip_type": marker_m.tooltip_type,
                                            "pitch": parseFloat(marker_m.pitch),
                                            "yaw": parseFloat(marker_m.yaw),
                                            "rotateX": parseInt(marker_m.rotateX),
                                            "rotateZ": parseInt(marker_m.rotateZ),
                                            "size_scale": parseFloat(marker_m.size_scale),
                                            "animation": marker_m.animation,
                                            "cssClass": "custom-hotspot-content",
                                            "createTooltipFunc": hotspot_content,
                                            "createTooltipArgs": marker_m,
                                            "clickHandlerFunc": '',
                                            "clickHandlerArgs": ''
                                        });
                                        panoramas[index].hotSpots_alt.push({
                                            "id": marker_m.id,
                                            "type": marker_m.type,
                                            "view_type": parseInt(marker_m.view_type),
                                            "object": "poi",
                                            "transform3d": false,
                                            "tooltip_type": marker_m.tooltip_type,
                                            "pitch": parseFloat(marker_m.pitch),
                                            "yaw": parseFloat(marker_m.yaw),
                                            "rotateX": parseInt(marker_m.rotateX),
                                            "rotateZ": parseInt(marker_m.rotateZ),
                                            "size_scale": parseFloat(marker_m.size_scale),
                                            "animation": marker_m.animation,
                                            "cssClass": "custom-hotspot-content custom-hotspot_alt",
                                            "createTooltipFunc": hotspot_content,
                                            "createTooltipArgs": marker_m,
                                            "clickHandlerFunc": '',
                                            "clickHandlerArgs": ''
                                        });
                                        break;
                                }
                                break;
                        }
                        break;
                }
            });
        });
        var i_slider = 0;
        for(var i=0; i < panoramas.length; i++) {
            var id = panoramas[i].id;
            var img = panoramas[i].panorama_image;
            var thumb = panoramas[i].thumb_image;
            var nome = panoramas[i].name;
            var visible_list = panoramas[i].visible_list;
            if(ObjectLength(panoramas[i].array_rooms_alt)>0) {
                jQuery.each(panoramas[i].array_rooms_alt, function(index_alt, room_alt) {
                    var img_alt = room_alt.panorama_image;
                    imgs_alt.push(img_alt);
                });
            }
            if(!panoramas[i].multires) { imgs.push(img+'|'+id); }
            if(visible_list) {
                if(panoramas[i].protected && !live_session_connected && !vr_enabled) {
                    var img_s = '<img style=\'filter:blur(16px);\' src="'+thumb+'">';
                } else {
                    var img_s = '<img src="'+thumb+'">';
                }
                $('.list_slider .slidee').append('<li data-index_id="'+i_slider+'" data-id="'+id+'" class="disabled pointer_list pointer_list_'+id+'">'+img_s+'<span class="noselect room_name_slider"><i class="fas fa-spin fa-circle-notch"></i> '+nome+'</span><div class="stat_visitors_rt_rooms"><i class="fas fa-user"></i>&nbsp;<span id="count_visitors_rt_room_'+id+'">0</span></div></li>');
                i_slider++;
            }
        }
        len = imgs.length;
        if(window.initial_id_room!='') {
            index_initial = get_id_viewer(parseInt(window.initial_id_room));
            slider_index = index_initial;
        }
        if(window.lat_panorama!='' && window.lon_panorama!='') {
            var index_tmp = goto_room_coordinates(window.lat_panorama,window.lon_panorama,false);
            if(index_tmp!=false) {
                index_initial = index_tmp;
                slider_index = index_initial;
            }
        }
        get_maps();
    }

    window.empty_function = function () {};

    function view_protect_form(cb,id) {
        var protect_type = panoramas[id].protect_type;
        if(panoramas[id].protected && !live_session_connected && !vr_enabled) {
            $('.panorama').css('filter','blur(16px)');
            $('#background_pano').css('filter','blur(16px)');
            $('.custom-hotspot-content').addClass("hidden_p");
            $('.custom-hotspot').addClass("hidden_p");
            $('.custom-hotspot img').addClass("hidden_p");
            switch(protect_type) {
                case 'passcode':
                    $('.passcode_div h2').html(panoramas[id].passcode_title.toUpperCase());
                    $('.passcode_div p').html(panoramas[id].passcode_description);
                    if(!cb) {
                        setTimeout(function () {
                            $('.passcode_div').show();
                        },2000);
                    } else {
                        $('.passcode_div').show();
                    }
                    break;
                case 'leads':
                    $('.leads_div h2').html(panoramas[id].passcode_title.toUpperCase());
                    $('.leads_div p').html(panoramas[id].passcode_description);
                    $('#protect_email').val(panoramas[id].protect_email);
                    if(!cb) {
                        setTimeout(function () {
                            $('.leads_div').show();
                        },2000);
                    } else {
                        $('.leads_div').show();
                    }
                    break;
            }
        } else {
            $('.panorama').css('filter','none');
            apply_room_filters(panoramas[id].filters,'pano');
            $('.custom-hotspot-content').removeClass("hidden_p");
            $('.custom-hotspot').removeClass("hidden_p");
            $('.custom-hotspot img').removeClass("hidden_p");
            $('.passcode_div').hide();
            $('.leads_div').hide();
        }
    }

    window.close_protect_form = function () {
        if(array_id_room_nav.length>1) {
            var id_room = array_id_room_nav[array_id_room_nav.length-2];
            goto('',[id_room,null,null,null]);
        }
    }

    window.check_passcode = function () {
        var passcode = $('#passcode').val();
        var id = get_id_viewer(current_id_panorama);
        var id_room = panoramas[id].id;
        if(passcode!='') {
            $('#btn_check_passcode').addClass("disabled");
            $.ajax({
                url: "ajax/check_passcode.php",
                type: "POST",
                data: {
                    id_room: id_room,
                    passcode: passcode
                },
                async: false,
                success: function (json) {
                    var rsp = JSON.parse(json);
                    if(rsp.status=='ok') {
                        $('#btn_check_passcode').css('color','green');
                        setTimeout(function () {
                            panoramas[id].protected = 0;
                            $('.panorama').css('filter','none');
                            apply_room_filters(panoramas[id].filters,'pano');
                            $('.pointer_list_'+id_room+' img').css('filter','none');
                            $('.custom-hotspot-content').removeClass("hidden_p");
                            $('.custom-hotspot').removeClass("hidden_p");
                            $('.custom-hotspot img').removeClass("hidden_p");
                            $('.arrows_nav').removeClass("hidden_p");
                            $('.passcode_div').hide();
                            $('#btn_check_passcode').css('color','black');
                            $('#btn_check_passcode').removeClass("disabled");
                        },250);
                    } else {
                        $('#btn_check_passcode').css('color','red');
                        setTimeout(function () {
                            $('#btn_check_passcode').css('color','black');
                            $('#btn_check_passcode').removeClass("disabled");
                        },250);
                        $('#passcode').val('');
                        $('#passcode').focus();
                    }
                }
            });
        }
    }

    $(".form_leads").submit(function(e){
        var lead_name = $('#lead_name').val();
        var lead_email = $('#lead_email').val();
        var lead_phone = $('#lead_phone').val();
        $('#btn_check_leads').addClass("disabled");
        $.ajax({
            url: "ajax/store_lead.php",
            type: "POST",
            data: {
                id_virtualtour: window.id_virtualtour,
                name: lead_name,
                email: lead_email,
                phone: lead_phone
            },
            async: false,
            success: function (json) {
                var rsp = JSON.parse(json);
                if(rsp.status=='ok') {
                    var email = $('#protect_email').val();
                    if(email!='') {
                        var index = get_id_viewer(current_id_panorama);
                        var room_name = panoramas[index].name;
                        $.ajax({
                            url: "../backend/ajax/send_email.php",
                            type: "POST",
                            data: {
                                type: 'lead',
                                email: email,
                                room_name: room_name,
                                lead_name: lead_name,
                                lead_email: lead_email,
                                lead_phone: lead_phone
                            },
                            timeout: 15000,
                            async: true,
                            success: function (json) {

                            }
                        });
                    }
                    $('#btn_check_leads').css('color','green');
                    setTimeout(function () {
                        for(var i=0; i < panoramas.length; i++) {
                            var protect_type = panoramas[i].protect_type;
                            if(protect_type=='leads') {
                                panoramas[i].protected = 0;
                                var id_room = panoramas[i].id;
                                $('.pointer_list_'+id_room+' img').css('filter','none');
                            }
                        }
                        $('.panorama').css('filter','none');
                        $('#background_pano').css('filter','none');
                        $('.custom-hotspot-content').removeClass("hidden_p");
                        $('.custom-hotspot').removeClass("hidden_p");
                        $('.custom-hotspot img').removeClass("hidden_p");
                        $('.arrows_nav').removeClass("hidden_p");
                        $('.leads_div').hide();
                        $('#btn_check_leads').css('color','black');
                        $('#btn_check_leads').removeClass("disabled");
                    },250);
                } else {
                    $('#btn_check_leads').css('color','red');
                    setTimeout(function () {
                        $('#btn_check_leads').css('color','black');
                        $('#btn_check_leads').removeClass("disabled");
                    },250);
                }
            }
        });
        e.preventDefault();
    });

    window.check_password_vt = function () {
        var password = $('#vt_password').val();
        if(password!='') {
            $.ajax({
                url: "ajax/check_password_vt.php",
                type: "POST",
                data: {
                    code: window.code,
                    password: password
                },
                async: false,
                success: function (json) {
                    var rsp = JSON.parse(json);
                    if(rsp.status=='ok') {
                        $('.progress-circle').fadeIn();
                        $('.protect').fadeOut();
                        if(window.hide_loading==1) {
                            $('.loading').addClass('hidden');
                            $('#background_loading').removeClass('background_opacity');
                        }
                        get_rooms(window.code);
                    } else {
                        $('#vt_password').val('');
                    }
                }
            });
        }
    }

    window.start_vt = function () {
        $('#icon_play').hide();
        if(window.password_protected && window.export_mode==0) {
            show_password_input();
        } else {
            if(window.hide_loading==1) {
                $('.loading').addClass('hidden');
                $('#background_loading').removeClass('background_opacity');
            }
            get_rooms(code);
        }
    }

    window.show_password_input = function () {
        $('.progress-circle').fadeOut();
        $('.protect').fadeIn();
        $('.loading').removeClass('hidden');
        $('#background_loading').addClass('background_opacity');
        $(document).keyup(function(event) {
            if($('.protect').is(':visible')) {
                if (event.key == "Enter") {
                    event.preventDefault();
                    check_password_vt();
                }
            }
        });
    }

    window.show_icon_play = function () {
        $('.progress-circle').fadeIn();
        $('#icon_play').fadeIn();
        $('.loading').removeClass('hidden');
        $('#background_loading').addClass('background_opacity');
    }

    function IsNaN(o) {
        return typeof(o) === 'number' && isNaN(o);
    }

    window.zoom_map = function () {
        if(show_map!=4) {
            if($(".map").hasClass("map_zoomed")) {
                $(".map").removeClass('map_zoomed');
                $('#map_zoomed_background').hide();
                $('.map_zoom_control i').addClass('fa-expand-alt').removeClass('fa-compress-alt');
            } else {
                $(".map").addClass('map_zoomed');
                $('#map_zoomed_background').show();
                $('.map_zoom_control i').removeClass('fa-expand-alt').addClass('fa-compress-alt');
            }
            resize_maps();
        } else {
            toggle_map();
        }
    }

    function hexToRgb(hex) {
        var hex=hex.replace('#', '');
        var bigint = parseInt(hex, 16);
        var r = (bigint >> 16) & 255;
        var g = (bigint >> 8) & 255;
        var b = bigint & 255;
        return r + "," + g + "," + b;
    }

    function percentage(partialValue, totalValue) {
        return (100 * partialValue) / totalValue;
    }

    function loading_config() {
        if(window.logo!='') {
            if(window.link_logo!='') {
                html_logo = "<a href='"+window.link_logo+"' target='_blank'><img class='noselect' draggable=\"false\" src=\"content/"+logo+"\"></a>";
            } else {
                html_logo = "<img class='noselect' draggable=\"false\" src=\"content/"+logo+"\">";
                $('.logo').addClass('poi_not_selectable');
            }
        }
        if(array_maps.length>0) {
            html_map = '';
            for(var i=0; i < array_maps.length; i++) {
                var map_width_m = array_maps[i].width_m;
                var map_width_d = array_maps[i].width_d;
                if((window.innerWidth<540) || (window.innerHeight<540)) {
                    var map_width = map_width_m;
                } else {
                    var map_width = map_width_d;
                }
                if(array_maps[i].info_link!='') {
                    var info_map = '<i onclick="open_info_map(\''+array_maps[i].info_link+'\',\''+array_maps[i].info_type+'\');" class="fas fa-info-circle info_map_btn"></i>&nbsp;&nbsp;';
                } else {
                    var info_map = '';
                }
                html_map += "<div data-id='"+array_maps[i].id+"' class='all_maps map_"+array_maps[i].id+"'>";
                html_map += "<img data-map_width_d='"+map_width_d+"' data-map_width_m='"+map_width_m+"' style='width:"+map_width+"px;' class='map_image' draggable=\"false\" src=\"maps/"+array_maps[i].map+"\">";
                html_map += "<span class='noselect'>"+info_map+array_maps[i].name+"</span>";
                html_map += '<div onclick="open_map_selector();" class="map_selector_control small-element">\n' +
                    '    <i class="fas fa-layer-group"></i>\n' +
                    '</div>';
                html_map += '<div onclick="zoom_map();" class="map_zoom_control">\n' +
                    '    <i class="fas fa-expand-alt"></i>\n' +
                    '</div>';
                html_map += '<div onclick="toggle_map();" class="map_close_control">\n' +
                    '    <i class="fas fa-times"></i>\n' +
                    '</div>';
                html_map += "</div>";
            }
            html_map += "<div class='map_selector'>\n" +
                "  <ul>\n";
            for(var i=0; i < array_maps.length; i++) {
                html_map += "    <li onclick='change_map("+array_maps[i].id+");'><a class='noselect'>"+array_maps[i].name+"</a></li>\n";
            }
            html_map += "  </ul>\n" +
                "</div>";
            for(var i=0; i < panoramas.length; i++) {
                var bg_color = '#000000';
                var point_size = 20;
                var map_ratio = 1.0;
                for(var k=0; k < array_maps.length; k++) {
                    if(array_maps[k].id==panoramas[i].id_map) {
                        panoramas[i].map_north = array_maps[k].north_degree;
                        map_ratio = parseFloat(array_maps[k].map_ratio);
                    }
                }
                if(!IsNaN(panoramas[i].map_top)) {
                    for(var k=0; k < array_maps.length; k++) {
                        if(panoramas[i].id_map==array_maps[k].id) {
                            bg_color = array_maps[k].point_color;
                            point_size = array_maps[k].point_size;
                        }
                    }
                    var scale = point_size/15;
                    var map_left = percentage((panoramas[i].map_left+(point_size/2))-2,300);
                    var map_top = percentage((panoramas[i].map_top+(point_size/2))-2,300/map_ratio);
                    var rgb = hexToRgb(bg_color);
                    html_map += "<div data-scale='"+scale+"' style='transform: rotate(0deg) scale("+scale+");top:"+map_top+"%;left:"+map_left+"%;' class='disabled pointer pointer_map_"+panoramas[i].id_map+" pointer_"+panoramas[i].id+"'><i style='margin-top:10px;font-size:21px !important;vertical-align:top;' class=\"fas fa-spin fa-circle-notch\"></i><div style='background: rgb("+rgb+");background: linear-gradient(-45deg, rgba("+rgb+",0) 10%, rgba("+rgb+",1) 100%);' class=\"view_direction__arrow\"></div><div style='background: "+bg_color+";' title=\""+panoramas[i].name+"\" data-id='"+panoramas[i].id+"' class=\"view_direction__center\"></div></div>";
                }
            }
        }
        if(array_maps.length>0) {
            var image_map = array_maps[0].map;
            var id_map_image = panoramas[index_initial].id_map;
            if(!isNaN(id_map_image)) {
                for(var i=0; i < array_maps.length; i++) {
                    if(array_maps[i].id==id_map_image) {
                        image_map = array_maps[i].map;
                    }
                }
            }
            var image_map_load = new Image();
            $(image_map_load).on('load',function () {
                preload_first_image();
            }).on('error',function () {
                preload_first_image();
            }).attr('src','maps/'+image_map);
        } else {
            preload_first_image();
        }
    }

    $(document).on("mousedown touchstart",function(e){
        if(!audio_prompt_open && !announce_open) {
            $('.intro_img').css('display','none');
        }
        var map = $('.map');
        if (!map.is(e.target) && map.has(e.target).length === 0) {
            close_map_selector();
        }
        var menu = $('.menu_controls');
        if (!menu.is(e.target) && menu.has(e.target).length === 0) {
            close_menu_controls();
        }
        var map_zoomed_background = $('#map_zoomed_background');
        if (map_zoomed_background.is(e.target)) {
            zoom_map();
        }
    });

    document.addEventListener('keyup', event => {
        $('.intro_img').css('display','none');
        if ((event.code === 'Space') && (keyboard_mode != 0)) {
            if(!live_session_connected) {
                if (!poi_open) {
                    $('.pnlm-pointer.hotspot_hover').trigger('click');
                    $('.pnlm-pointer.hotspot_hover a').trigger('click');
                }
            }
        }
        if (event.code === "Escape") {
            if(!live_session_connected) {
                poi_open = false;
                $('.pnlm-container').focus();
                $('.pnlm-container').trigger('click');
            }
        }
    });

    window.change_map = function(id) {
        $('.all_maps').hide();
        $('.pointer').hide();
        $('.map_'+id).show();
        $('.pointer_map_'+id).show();
        close_map_selector();
        resize_maps();
        for(var k=0; k < array_maps.length; k++) {
            if(array_maps[k].id==id) {
                var id_room_default = array_maps[k].id_room_default;
                if(id_room_default!='' && current_id_panorama!=id_room_default) {
                    goto('',[id_room_default,null,null,null,null]);
                }
                return;
            }
        }
    }

    window.open_map_selector = function () {
        if((array_maps.length>1) && (map_selector_open==false)) {
            $('.map_image').addClass('darker_img');
            $('.pointer').css('visibility','hidden');
            $('.all_maps span').hide();
            $('.map_selector_control').hide();
            $('.map_selector').css('display','flex');
            $('.map_zoom_control').hide();
            $('.map_close_control').hide();
            map_selector_open = true;
        }
    }

    window.close_map_selector = function () {
        if(map_selector_open) {
            $('.map_image').removeClass('darker_img');
            $('.pointer').css('visibility', 'visible');
            $('.all_maps span').show();
            $('.map_selector_control').show();
            $('.map_selector').hide();
            $('.map_zoom_control').show();
            $('.map_close_control').show();
            map_selector_open = false;
        }
    }

    function loadXHR(url,id,index) {
        return new Promise(function(resolve) {
            try {
                var xhr = new XMLHttpRequest();
                xhr.open("GET", url);
                xhr.responseType = "blob";
                xhr.onerror = function() {resolve("",id,index)};
                xhr.onload = function() {
                    if (xhr.status === 200) {resolve([xhr.response,id,index])}
                    else {resolve("",id,index)}
                };
                xhr.send();
            }
            catch(err) {resolve("",id,index)}
        });
    }

    function preload_first_image() {
        if(panoramas[index_initial].multires && !window.flyin) {
            progress_circle.animate(0.9);
            setTimeout(function () {
                initialize();
            },500);
        } else {
            var img = panoramas[index_initial].panorama_image;
            var id = panoramas[index_initial].id;
            progress_circle.animate(0.5);
            interval_progress = setInterval(function () {
                if(step_progress>=0.9) {
                    clearInterval(interval_progress);
                } else {
                    progress_circle.animate(step_progress);
                    step_progress = step_progress + 0.1;
                }
            },500);
            first_image = new Image();
            $(first_image).on('load',function () {
                loadXHR(img,id,0).then(function(response) {
                    if(response[0]!='') {
                        var blob_url = URL.createObjectURL(response[0]);
                        panoramas[index_initial].panorama_blob = blob_url;
                    }
                    clearInterval(interval_progress);
                    progress_circle.animate(0.9);
                    imgs_loaded.push(response[1]);
                    $('.marker_img_'+id).removeClass('fas fa-spin fa-circle-notch');
                    $('.marker_img_'+id).parent().parent().removeClass('disabled');
                    $('.marker_img_'+id).parent().removeClass('disabled');
                    $('.marker_img_'+id).each(function () {
                        var icon = $(this)[0].getAttribute('data-icon');
                        $(this).addClass(icon);
                    });
                    $('.pointer_list_'+id+' .fa-spin').remove();
                    $('.pointer_list_'+id).removeClass('disabled');
                    $('.pointer_'+id+' i').remove();
                    $('.pointer_'+id).removeClass('disabled');
                    $(".arrows_nav").find("[data-roomtarget='" + id + "']").removeClass('disabled');
                    $(".controls_arrows").find("[data-roomtarget='" + id + "']").removeClass('disabled');
                    $('.list_alt_'+id).removeClass('disabled');
                    initialize();
                });
            }).attr('src',img);
        }
    }

    function preload_next_image() {
        if(preload_panoramas==1) {
            if (index_preload_image < imgs.length) {
                var index = 0;
                var id = 0;
                var img = '';
                for (var t = 0; t < panoramas.length; t++) {
                    if (imgs[index_preload_image] == panoramas[t].panorama_image+'|'+panoramas[t].id) {
                        index = t;
                        id = panoramas[t].id;
                        img = imgs[index_preload_image].replace('|'+id,'');
                        break;
                    }
                }
                loadXHR(img, id, index).then(function (response) {
                    var id = response[1];
                    var i = response[2];
                    if (response[0] != '') {
                        var blob_url = URL.createObjectURL(response[0]);
                        panoramas[i].panorama_blob = blob_url;
                    }
                    imgs_loaded.push(id);
                    $('.marker_img_' + id).removeClass('fas fa-spin fa-circle-notch');
                    $('.marker_img_' + id).parent().parent().removeClass('disabled');
                    $('.marker_img_' + id).parent().removeClass('disabled');
                    $('.marker_img_' + id).each(function () {
                        var icon = $(this)[0].getAttribute('data-icon');
                        $(this).addClass(icon);
                    });
                    $('.pointer_list_' + id + ' .fa-spin').remove();
                    $('.pointer_list_' + id).removeClass('disabled');
                    $('.pointer_' + id + ' i').remove();
                    $('.pointer_' + id).removeClass('disabled');
                    $(".arrows_nav").find("[data-roomtarget='" + id + "']").removeClass('disabled');
                    $(".controls_arrows").find("[data-roomtarget='" + id + "']").removeClass('disabled');
                    $('.list_alt_' + id).removeClass('disabled');
                    index_preload_image++;
                    preload_next_image();
                });
            } else {
                if (imgs_alt.length > 0) {
                    preload_next_image_alt();
                }
            }
        }
    }

    function preload_next_image_alt() {
        if(index_preload_image_alt<imgs_alt.length) {
            var index = 0;
            var id = 0;
            var img = '';
            for(var t=0;t<panoramas.length;t++) {
                if(ObjectLength(panoramas[t].array_rooms_alt)>0) {
                    jQuery.each(panoramas[t].array_rooms_alt, function(index_alt, room_alt) {
                        if(room_alt.panorama_image==imgs_alt[index_preload_image_alt]) {
                            index = index_alt;
                            img = imgs_alt[index_preload_image_alt];
                            id = t;
                        }
                    });
                }
            }
            loadXHR(img,id,index).then(function(response) {
                if(response[0]!='') {
                    var blob_url = URL.createObjectURL(response[0]);
                    var index1 = response[1];
                    var index2 = response[2];
                    panoramas[index1].array_rooms_alt[index2].panorama_blob = blob_url;
                }
                index_preload_image_alt++;
                preload_next_image_alt();
            });
        }
    }

    function preload_images() {
        for(var i=0; i < panoramas.length; i++) {
            var id = panoramas[i].id;
            if(panoramas[i].multires || preload_panoramas==0) {
                imgs_loaded.push(id);
                $('.marker_img_'+id).removeClass('fas fa-spin fa-circle-notch');
                $('.marker_img_'+id).parent().parent().removeClass('disabled');
                $('.marker_img_'+id).parent().removeClass('disabled');
                $('.marker_img_'+id).each(function () {
                    var icon = $(this)[0].getAttribute('data-icon');
                    $(this).addClass(icon);
                });
                $('.pointer_list_'+id+' .fa-spin').remove();
                $('.pointer_list_'+id).removeClass('disabled');
                $('.pointer_'+id+' i').remove();
                $('.pointer_'+id).removeClass('disabled');
                $(".arrows_nav").find("[data-roomtarget='" + id + "']").removeClass('disabled');
                $(".controls_arrows").find("[data-roomtarget='" + id + "']").removeClass('disabled');
                $('.list_alt_'+id).removeClass('disabled');
            }
        }
        preload_next_image();
    }

    function add_custom_controls() {
        if(song_bg!='') {
            $('.song_control').show();
        } else {
            $('.song_control').hide();
            for(var i=0; i < panoramas.length; i++) {
                var song = panoramas[i].song;
                var audio_track_enable = panoramas[i].audio_track_enable;
                if((song!='') || (audio_track_enable)) {
                    $('.song_control').show();
                }
            }
        }
        var info_control = false;
        var voice_control = false;
        var gallery_control = false;
        if(show_info>0) {
            info_control = true;
        }
        if(voice_commands_enable>0) {
            if(peer_id=='') {
                voice_control = true;
                initialize_speech();
            }
        }
        if(gallery_images.length>0) {
            if(show_gallery>0) {
                gallery_control = true;
            }
        }
        if(show_compass) {
            $('.controls_btn.compass_control').css('display','inline-block');
        } else {
            $('.compass_control').addClass('hidden');
        }
        if(info_control) {
            $('.controls_btn.info_control').css('display','inline-block');
        } else {
            $('.info_control').addClass('hidden');
        }
        if(show_custom>0) {
            $('.controls_btn.custom_control').css('display','inline-block');
        } else {
            $('.custom_control').addClass('hidden');
        }
        if(show_dollhouse>0) {
            $('.controls_btn.dollhouse_control').css('display','inline-block');
        } else {
            $('.dollhouse_control').addClass('hidden');
        }
        if(!voice_control) {
            $('.voice_control').addClass('hidden');
        }
        if(gallery_control) {
            $('.controls_btn.gallery_control').css('display','inline-block');
        } else {
            $('.gallery_control').addClass('hidden');
        }
        if((whatsapp_chat) && (whatsapp_number!='')) {
            $('.controls_btn.whatsapp_control').css('display','inline-block');
            if(window.is_mobile) {
                var link_whatsapp = 'https://api.whatsapp.com/send?phone='+whatsapp_number;
            } else {
                var link_whatsapp = 'https://web.whatsapp.com/send?phone='+whatsapp_number;
            }
            $('.controls_btn.whatsapp_control').attr('href',link_whatsapp);
            $('.dropdown .whatsapp_control').attr('onclick',"open_whatsapp('"+link_whatsapp+"')");
        } else {
            $('.whatsapp_control').addClass('hidden');
        }
        if((array_presentation.length>0) || (presentation_type=='automatic') || (presentation_type=='video')) {
            $('.presentation_control').show();
        } else {
            $('.presentation_control').hide();
        }
        if(form_enable) {
            try {
                form_content = JSON.parse(form_content);
                var title = form_content[0].title;
                $('#mform_name').html(title);
            } catch (e) {}
            try {
                $('.controls_btn.form_control').attr('title',title);
            } catch (e) {}
            $('.controls_btn.form_control').css('display','inline-block');
        } else {
            $('.form_control').addClass('hidden');
        }
        $('.tooltip').tooltipster({
            theme: 'tooltipster-borderless',
            animation: 'grow',
            delay: 0,
            arrow: false
        });
        $('.pnlm-orientation-button').hide();
        if(!show_audio) {
            $('.song_control').addClass('hidden');
            controls_status['song']=false;
        }
        if(!show_vt_title) {
            $('.name_vt').addClass('hidden');
            $('.room_vt').addClass('hidden');
            $('.author_vt').addClass('hidden');
        }
        if(!show_logo) {
            $('.logo').addClass('hidden');
        }
        if(!show_icons_toggle) {
            $('.icons_control').addClass('hidden');
        }
        if(!show_autorotation_toggle || autorotate_speed==0) {
            $('.autorotate_control').addClass('hidden');
            controls_status['auto_rotate']=false;
        }
        if(!show_nav_control) {
            $(".nav_control").addClass('hidden');
        } else {
            if(autorotate_speed==0) {
                $(".nav_control .nav_rotate").addClass('disabled');
            } else {
                $(".nav_control .nav_rotate").addClass('active_rotate');
            }
            $('.nav_control').show();
            $(".nav_control").draggable({ containment: "#vt_container" });
        }
        if(show_presentation==0) {
            $('.presentation_control').addClass('hidden');
        }
        if(show_share>0) {
            $('.controls_btn.share_control').css('display','inline-block');
        } else {
            $('.share_control').addClass('hidden');
        }
        if(show_device_orientation==0) {
            $('.orient_control').addClass('hidden');
        }
        if(show_webvr) {
            $('.controls_btn.vr_control').css('display','inline-block');
        } else {
            $('.vr_control').addClass('hidden');
        }
        if(!show_annotations) {
            $('.annotations_control').addClass('hidden');
        }
        if(!show_facebook) {
            $('.facebook_control').addClass('hidden');
        }
        if(!whatsapp_chat) {
            $('.whatsapp_control').addClass('hidden');
        }
        if(array_maps.length==0) {
            show_map=0;
        }
        if(map_tour.length==0) {
            show_map_tour=0;
        }
        switch (show_map) {
            case 0:
                $('.map_control').addClass('hidden');
                break;
            case 1:
            case 2:
            case 3:
            case 4:
                if(controls_status['map']) {
                    $('.map_control').addClass('active_control');
                    $('.map_control i').removeClass('icon-map_off').addClass('icon-map_on');
                } else {
                    $('.map_control').removeClass('active_control');
                    $('.map_control i').removeClass('icon-map_on').addClass('icon-map_off');
                }
                break;
        }
        if((show_fullscreen) && (show_map!=0) && (show_map_tour!=0)) {
            $('.fullscreen_control').show();
            $('.fullscreen_control').css('right','14px');
            $('.map_control').show();
            $('.map_control').css('right','41px');
            $('.map_tour_control').show();
            $('.map_tour_control').css('right','75px');
        } else if((!show_fullscreen) && (show_map!=0) && (show_map_tour!=0)) {
            $('.fullscreen_control').addClass('hidden');
            $('.map_control').show();
            $('.map_control').css('right','12px');
            $('.map_tour_control').show();
            $('.map_tour_control').css('right','45px');
        } else if((show_fullscreen) && (show_map==0) && (show_map_tour!=0)) {
            $('.fullscreen_control').show();
            $('.fullscreen_control').css('right','14px');
            $('.map_control').addClass('hidden');
            $('.map_tour_control').show();
            $('.map_tour_control').css('right','45px');
        } else if((show_fullscreen) && (show_map!=0) && (show_map_tour==0)) {
            $('.fullscreen_control').show();
            $('.fullscreen_control').css('right','14px');
            $('.map_control').show();
            $('.map_control').css('right','43px');
            $('.map_tour_control').addClass('hidden');
        } else if((!show_fullscreen) && (show_map==0) && (show_map_tour!=0)) {
            $('.fullscreen_control').addClass('hidden');
            $('.map_control').addClass('hidden');
            $('.map_tour_control').show();
            $('.map_tour_control').css('right','14px');
        } else if((!show_fullscreen) && (show_map!=0) && (show_map_tour==0)) {
            $('.fullscreen_control').addClass('hidden');
            $('.map_control').show();
            $('.map_control').css('right','14px');
            $('.map_tour_control').addClass('hidden');
        } else if((show_fullscreen) && (show_map==0) && (show_map_tour==0)) {
            $('.fullscreen_control').show();
            $('.fullscreen_control').css('right','14px');
            $('.map_control').addClass('hidden');
            $('.map_tour_control').addClass('hidden');
        } else {
            $('.fullscreen_control').addClass('hidden');
            $('.map_control').addClass('hidden');
            $('.map_tour_control').addClass('hidden');
        }
        if (show_map_tour==2) {
            $('.map_tour_control i').removeClass('far').addClass('fas');
            $('.map_tour_control').addClass('active_control');
            open_map_tour();
        }
        if(auto_show_slider==2) {
            $('.list_control').addClass('hidden');
        }
        if (location.protocol == 'https:') {
            if(show_live_session) {
                $('.controls_btn.live_control').css('display','inline-block');
                if(peer_id!='') {
                    $('.live_control').addClass('hidden');
                }
            } else {
                $('.live_control').addClass('hidden');
            }
        } else {
            $('.live_control').addClass('hidden');
        }
        if(show_meeting) {
            $('.controls_btn.meeting_control').css('display','inline-block');
        } else {
            $('.meeting_control').addClass('hidden');
        }
        var all_menu_hidden = true;
        $('.dropdown p').each(function () {
            if(($(this).is(':visible')) && (!$(this).hasClass('hidden'))) {
                all_menu_hidden = false;
            }
        });
        if(all_menu_hidden) {
            $('.menu_controls').addClass('hidden');
            $('.song_control').css('left','8px');
        }
        switch (show_list_alt) {
            case 0:
                $('.list_alt_menu').addClass('hidden');
                break;
            case 1:
            case 2:
                if(all_menu_hidden) {
                    $('.list_alt_menu').css('left','3px');
                    $('.song_control').css('left','45px');
                } else {
                    $('.list_alt_menu').css('left','40px');
                    $('.song_control').css('left','75px');
                }
                var id_open_cat = 0;
                for(var i=0;i<list_alt.length;i++) {
                    var id = list_alt[i].id;
                    var name = list_alt[i].name;
                    var type = list_alt[i].type;
                    var hide = parseInt(list_alt[i].hide);
                    switch (type) {
                        case 'room':
                            if(hide==0) {
                                if(id==panoramas[index_initial].id) {
                                    var icon = 'fas fa-dot-circle';
                                } else {
                                    var icon = 'far fa-circle';
                                }
                                $('.list_alt_menu .dropdown').append('<p class="disabled list_alt_'+id+'" onclick="goto(\'\',['+id+',null,null,null,null]);"><i class="'+icon+'"></i>&nbsp;&nbsp;&nbsp;'+name+'</p>');
                            }
                            break;
                        case 'category':
                            $('.list_alt_menu .dropdown').append('<p class="cat cat_'+id+'" onclick="open_cat_list_alt(\''+id+'\')"><i class="fas fa-chevron-right"></i>&nbsp;&nbsp;&nbsp;'+name+'</p>');
                            var childrens = list_alt[i].childrens;
                            for(var k=0;k<childrens.length;k++) {
                                if(childrens[k]['hide']==0) {
                                    if(childrens[k]['id']==panoramas[index_initial].id) {
                                        var icon = 'fas fa-dot-circle';
                                        id_open_cat = id;
                                    } else {
                                        var icon = 'far fa-circle';
                                    }
                                    $('.list_alt_menu .dropdown').append('<p style="display: none" data-cat="'+id+'" class="disabled children list_alt_'+childrens[k]['id']+' cat_parent_'+id+'" onclick="goto(\'\',['+childrens[k]['id']+',null,null,null,null]);"><i style="margin-left: 15px;" class="'+icon+'"></i>&nbsp;&nbsp;&nbsp;'+childrens[k]['name']+'</p>');
                                }
                            }
                            break;
                    }
                }
                $('.list_alt_menu').show();
                if(id_open_cat!=0) {
                    open_cat_list_alt(id_open_cat);
                }
                if(show_list_alt==2) click_list_alt_menu();
                break;
        }
    }

    window.open_whatsapp = function(link) {
        window.open(link,'_blank');
    }

    window.open_info_map = function (link,type) {
        switch (type) {
            case 'blank':
                window.open(link,'_blank');
                break;
            case 'iframe':
                var custom_box_html = '<div style="padding:0 !important;width:90%;height:90%;overflow:hidden;" class="info_map_content"><iframe style="width:100%;height:100%;border:0;" src="'+link+'"></iframe></div>';
                $.fancybox.open({
                    src  : custom_box_html,
                    type : 'html',
                    touch: false,
                    smallBtn: false,
                    clickOutside: false
                });
                break;
        }
    }

    window.open_cat_list_alt = function(id) {
        $('.list_alt_menu .dropdown .children').hide();
        if(id==0) {
            $('.cat i').removeClass('fa-chevron-down').addClass('fa-chevron-right');
        } else if($('.cat_'+id+' i').hasClass('fa-chevron-right')) {
            $('.cat_parent_'+id).show();
            $('.cat_'+id+' i').removeClass('fa-chevron-right').addClass('fa-chevron-down');
            $(".cat i:not('.cat_"+id+" i')").removeClass('fa-chevron-down').addClass('fa-chevron-right');
        } else {
            $('.cat_'+id+' i').removeClass('fa-chevron-down').addClass('fa-chevron-right');
        }
    }

    function initialize() {
        controls_status['fullscreen']=false;
        controls_status['orient']=false;
        if(song_autoplay>0) {
            controls_status['song']=true;
        } else {
            controls_status['song']=false;
        }
        controls_status['map']=false;
        controls_status['icons']=true;
        controls_status['auto_rotate']=true;
        controls_status['list']=false;
        controls_status['share']=false;
        controls_status['hide']=false;
        controls_status['presentation']=false;
        current_id_panorama = panoramas[index_initial].id;
        if(!transition_loading) {
            $('#loading_pano').addClass('hidden');
        }
        audio_player.src = "";
        audio_player_room.src = "";
        if (typeof audio_player_room.loop == 'boolean') {
            audio_player_room.loop = true;
        } else {
            audio_player_room.addEventListener('ended', function() {
                this.currentTime = 0;
                this.play();
            }, false);
        }
        if(panoramas[index_initial].song!='') {
            audio_player_room.src = "content/"+panoramas[index_initial].song;
            audio_player_room.load();
            audio_player_room.play();
        }
        if(song_bg!='') {
            audio_player.src = "content/"+song_bg;
        }
        if (typeof audio_player.loop == 'boolean') {
            audio_player.loop = true;
        } else {
            audio_player.addEventListener('ended', function() {
                this.currentTime = 0;
                this.play();
            }, false);
        }
        audio_player.load();
        audio_player.onplay = function() {
            first_song_play = false;
            unmute_audio(true,true);
            $('.song_control').addClass('active_control');
            $('.song_control i').addClass('fa-volume-down').removeClass('fa-volume-mute');
            controls_status['song']=true;
        };

        var check_room_song = false;
        if(show_audio) {
            for(var i=0; i < panoramas.length; i++) {
                var song = panoramas[i].song;
                var audio_track_enable = panoramas[i].audio_track_enable;
                if((song!='') || (audio_track_enable)) {
                    check_room_song = true;
                }
            }
        }

        if((song_autoplay>0) && (song_bg!='' || check_room_song) && (peer_id=='')) {
            $('.song_control').addClass('active_control');
            $('.song_control i').addClass('fa-volume-down').removeClass('fa-volume-mute');
            audio_player.play().catch(function(error) {
                switch(song_autoplay) {
                    case 2:
                        $(document).on("mousedown touchstart",function(e){
                            if(first_song_play) {
                                if(panoramas[index_initial].audio_track_enable) {
                                    unmute_audio(true,true);
                                } else {
                                    unmute_audio(true,false);
                                }
                                $('.song_control').addClass('active_control');
                                $('.song_control i').addClass('fa-volume-down').removeClass('fa-volume-mute');
                                first_song_play = false;
                                audio_prompt_open = false;
                                controls_status['song']=true;
                                try {
                                    audio_player.play().catch(function () {});
                                } catch (e) {}
                                if(panoramas[index_initial].song!='') {
                                    audio_player_room.play().catch(function() {});;
                                }
                            }
                        });
                        break;
                    case 1:
                        audio_prompt = $.confirm({
                            lazyOpen: true,
                            theme: 'modern,audio_prompt',
                            useBootstrap: false,
                            closeIcon: false,
                            typeAnimated: true,
                            title: window.viewer_labels.enable_audio,
                            content: '',
                            boxWidth: '250px',
                            buttons: {
                                yes: {
                                    text: window.viewer_labels.yes,
                                    btnClass: 'btn-green',
                                    action: function(){
                                        if(panoramas[index_initial].audio_track_enable) {
                                            unmute_audio(true,true);
                                        } else {
                                            unmute_audio(true,false);
                                        }
                                        $('.song_control').addClass('active_control');
                                        $('.song_control i').addClass('fa-volume-down').removeClass('fa-volume-mute');
                                        first_song_play = false;
                                        audio_prompt_open = false;
                                        controls_status['song']=true;
                                        try {
                                            audio_player.play().catch(function() {});
                                        } catch (e) {}
                                        try {
                                            audio_player_room.play().catch(function() {});;
                                        } catch (e) {}
                                    }
                                },
                                cancel : {
                                    text: window.viewer_labels.no,
                                    action: function(){
                                        mute_audio(true,true);
                                        $('.song_control').removeClass('active_control');
                                        $('.song_control i').addClass('fa-volume-mute').removeClass('fa-volume-down');
                                        first_song_play = false;
                                        audio_prompt_open = false;
                                        controls_status['song']=false;
                                        try {
                                            audio_player.pause();
                                        } catch (e) {}
                                        try {
                                            audio_player_room.pause();
                                        } catch (e) {}
                                    }
                                },
                            }
                        });
                        break;
                }
            });
        } else {
            mute_audio(true,true);
            $('.song_control').removeClass('active_control');
            $('.song_control i').addClass('fa-volume-mute').removeClass('fa-volume-down');
            first_song_play = false;
            controls_status['song']=false;
            try {
                audio_player.pause();
            } catch (e) {}
        }

        if(array_maps.length>0) {
            $('.map').append(html_map);
            if(array_maps.length>1) {
                $('.map_selector_control').show();
            } else {
                $('.map_selector_control').hide();
            }
            $('.view_direction__center').tooltipster({
                animation: 'grow',
                delay: 0,
                arrow: false,
                theme: 'tooltipster-borderless',
                side: 'bottom'
            });
            $('.all_maps').hide();
            $('.pointer').hide();
            id_current_map = panoramas[index_initial].id_map;
            if(!isNaN(id_current_map)) {
                $('.map_'+id_current_map).show();
                $('.pointer_map_'+id_current_map).show();
            } else {
                id_current_map = array_maps[0].id;
                $('.map_'+id_current_map).show();
                $('.pointer_map_'+id_current_map).show();
            }
            jQuery.each(imgs_loaded, function(index, id_load) {
                $('.pointer_'+id_load+' i').remove();
                $('.pointer_'+id_load).removeClass('disabled');
            });
            $('.view_direction__center').click(function () {
                var id_room_target = $(this).data('id');
                goto('',[id_room_target,null,null,null,null]);
            });
        }
        $('.view_direction__arrow').hide();
        if(window.initial_yaw!='') {
            var yaw_i = parseFloat(window.initial_yaw);
        } else {
            var yaw_i = null;
        }
        if(window.initial_pitch!='') {
            var pitch_i = parseFloat(window.initial_pitch);
        } else {
            var pitch_i = null;
        }
        initialize_room(index_initial,false,false,pitch_i,yaw_i,null, null,false);
        progress_circle.animate(1);
        if(window.background_video!='') {
            $('.progress-circle').animate({opacity: 0}, 1500);
            interval_video_loading_check = setInterval(function() {
                if(window.video_loading_ended) {
                    clearInterval(interval_video_loading_check);
                    if(window.flyin) {
                        init_flyin(panoramas[index_initial].panorama_image,panoramas[index_initial].yaw,panoramas[index_initial].pitch);
                    } else {
                        complete_init();
                    }
                }
            },1000);
        } else {
            setTimeout(function () {
                $('.progress-circle').animate({opacity: 0}, 1500);
                if(window.flyin) {
                    init_flyin(panoramas[index_initial].panorama_image,panoramas[index_initial].yaw,panoramas[index_initial].pitch);
                } else {
                    complete_init();
                }
            },1000);
        }
    }

    function complete_init() {
        if(announce!=null) view_announce();
        $('.loading').animate({
            opacity: 0
        }, { duration: 500, queue: false });
        setTimeout(function () {
            $('.loading').css('z-index',0);
            $('.loading').hide();
            $('#background_loading').hide();
        },500);
        if(audio_prompt!=null) {
            audio_prompt_open = true;
            audio_prompt.open();
        }
        $('.panorama').css('z-index','unset');
        if(panoramas[index_initial].type=='image' || is_iOS() || panoramas[index_initial].type=='hls' || panoramas[index_initial].type=='lottie') {
            $('#panorama_viewer').animate({
                opacity: 1
            }, { duration: 250, queue: false });
            $('#panorama_viewer').css('z-index',10);
        } else {
            $('#video_viewer').animate({
                opacity: 1
            }, { duration: 250, queue: false });
            $('#video_viewer').css('z-index',10);
        }
        $('.header_vt').show();
        var annotation_title = panoramas[index_initial].annotation_title;
        var annotation_description = panoramas[index_initial].annotation_description;
        if(((annotation_title!='') || (annotation_description!='')) && (show_annotations)) {
            var a_both = 0;
            if(annotation_title!='') {
                $('.annotation_title').html(annotation_title);
                a_both++;
            }
            if(annotation_description!='') {
                $('.annotation_description').html(annotation_description);
                a_both++;
            }
            if(a_both==2) {
                $('.annotation hr').show();
            } else {
                $('.annotation hr').hide();
            }
            $('.annotation').show();
        } else {
            $('.annotation').hide();
        }
        if(array_maps.length>0) {
            switch (show_map) {
                case 0:
                    controls_status['map']=false;
                    $('.map').hide();
                    break;
                case 1:
                    if(window.is_mobile) {
                        $('.map').hide();
                        controls_status['map']=false;
                    } else {
                        $('.map').show();
                        controls_status['map']=true;
                    }
                    break;
                case 2:
                    $('.map').hide();
                    controls_status['map']=false;
                    break;
                case 3:
                    $('.map').show();
                    controls_status['map']=true;
                    break;
                case 4:
                    $('.map').hide();
                    $(".map").addClass('map_zoomed');
                    $('.map_zoom_control').hide();
                    $('.map_close_control').css('right','2px');
                    controls_status['map']=false;
                    break;
            }
        } else {
            $('.map').hide();
            controls_status['map']=false;
        }
        $('.menu_controls').show();
        add_custom_controls();
        $(document).trigger('resize');
        if(logo!='' && show_logo) {
            $('.logo').append(html_logo);
            $('.logo').show();
        }
        adjust_elements_positions();
        $('.fullscreen_control').show();
        switch(arrows_nav) {
            case 0:
                $('.arrows_nav').hide();
                break;
            case 1:
                $('.arrows_nav').show();
                break;
            case 2:
                if(window.is_mobile) {
                    $('.arrows_nav').hide();
                } else {
                    $('.arrows_nav').show();
                }
                break;
        }
        $('.pointer_'+panoramas[index_initial].id).animate({
            opacity: 1
        }, { duration: 250, queue: false });
        var poi_embed_count = $('.poi_embed').length;
        if(poi_embed_count>0) {
            init_poi_embed();
        }
        var marker_embed_count = $('.marker_embed').length;
        if(marker_embed_count>0) {
            init_marker_embed();
        }
        setTimeout(function () {
            if(auto_show_slider!=2) {
                sly = new Sly('.list_slider', {
                    horizontal: 0,
                    itemNav: 'centered',
                    smart: 1,
                    mouseDragging: 1,
                    touchDragging: 1,
                    releaseSwing: 1,
                    scrollBy: 1,
                    speed: 300,
                    elasticBounds: 1,
                    dragHandle: 1,
                    dynamicHandle: 1,
                    clickBar: 1,
                    startAt: index_initial,
                });
                sly.init();
            }
            $('.pointer_list').removeClass('active');
            $('.pointer_list_'+panoramas[index_initial].id).addClass('active');
            $('.pointer_list').on('mousedown',function () {
                drag_slider_start = new Date().getTime();
                drag_slider = false;
            });
            $('.pointer_list').on('mousemove',function () {
                drag_slider_end = new Date().getTime();
                drag_slider = true;
            });
            $('.pointer_list').on('click',function () {
                var diff_drag = drag_slider_end - drag_slider_start;
                if(drag_slider == false || diff_drag < 50) {
                    var id_room_target = parseInt($(this).data('id'));
                    goto('',[id_room_target,null,null,null,null]);
                }
            });
            $('.pointer_list').on('touchstart',function () {
                drag_slider_start = new Date().getTime();
                drag_slider = false;
            });
            $('.pointer_list').on('touchmove',function () {
                drag_slider_end = new Date().getTime();
                drag_slider = true;
            });
            $('.pointer_list').on('touchend',function () {
                var diff_drag = drag_slider_end - drag_slider_start;
                if(drag_slider == false || diff_drag < 50) {
                    var id_room_target = parseInt($(this).data('id'));
                    goto('',[id_room_target,null,null,null,null]);
                }
            });
            $('#list_right').on('click',function () {
                switch (nav_slider) {
                    case 0:
                        var len = panoramas.length;
                        var index = get_id_viewer(current_id_panorama);
                        do {
                            var next_panorama = panoramas[(index+1)%len];
                            var visible_list = next_panorama.visible_list;
                            index++;
                        } while(visible_list==0);
                        var id_room_target = next_panorama.id;
                        goto('',[id_room_target,null,null,null,null]);
                        break;
                    case 1:
                        if(auto_show_slider!=2) sly.nextPage();
                        break;
                }
            });
            $('#list_left').on('click',function () {
                switch (nav_slider) {
                    case 0:
                        var len = panoramas.length;
                        var index = get_id_viewer(current_id_panorama);
                        do {
                            var prev_panorama = panoramas[(index+len-1)%len];
                            var visible_list = prev_panorama.visible_list;
                            index--;
                        } while(visible_list==0);
                        var id_room_target = prev_panorama.id;
                        goto('',[id_room_target,null,null,null,null]);
                        break;
                    case 1:
                        if(auto_show_slider!=2) sly.prevPage();
                        break;
                }
            });
            switch(auto_show_slider) {
                case 0:
                    $('.list_control').show();
                    controls_status['list']=true;
                    break;
                case 1:
                    $('.list_control').show();
                    $('.list_slider').css('z-index',1000);
                    $('.list_slider').css('opacity',1);
                    $('.list_control').addClass('active_control');
                    reposition_bottom_controls(true,false,0);
                    $('.list_control i').removeClass('fa-chevron-up').addClass('fa-chevron-down');
                    $('.list_control_alt').removeClass('fa-chevron-up').addClass('fa-chevron-down');
                    controls_status['list']=true;
                    break;
                case 2:
                    $('.list_control').addClass('hidden');
                    controls_status['list']=false;
                    break;
            }
            if(show_device_orientation==2 && window.is_mobile) {
                if (typeof DeviceMotionEvent !== 'undefined' &&
                    typeof DeviceMotionEvent.requestPermission === 'function') {
                    DeviceOrientationEvent.requestPermission().then(response => {
                        if(response == "granted") {
                            toggle_orient();
                        }
                    }).catch(function () {
                        $.confirm({
                            lazyOpen: false,
                            theme: 'modern,deivce_motion_prompt',
                            useBootstrap: false,
                            closeIcon: false,
                            typeAnimated: true,
                            title: window.viewer_labels.enable_device_motion,
                            content: '',
                            boxWidth: '250px',
                            buttons: {
                                yes: {
                                    text: window.viewer_labels.yes,
                                    btnClass: 'btn-green',
                                    action: function(){
                                        $('.orient_control').addClass('active_control');
                                        $('.orient_control .fa-circle').removeClass('not_active').addClass('active');
                                        controls_status['orient']=true;
                                        if(current_panorama_type=='image' || is_iOS() || current_panorama_type=='hls' || current_panorama_type=='lottie') {
                                            pano_viewer.startOrientation();
                                        } else {
                                            video_viewer.pnlmViewer.startOrientation();
                                        }
                                        close_menu_controls();
                                        if(drag_device_orientation==0) $('.pnlm-dragfix').css('pointer-events','none');
                                    }
                                },
                                cancel : {
                                    text: window.viewer_labels.no,
                                    action: function(){
                                        $('.orient_control').removeClass('active_control');
                                        $('.orient_control .fa-circle').removeClass('active').addClass('not_active');
                                        controls_status['orient']=false;
                                        if(current_panorama_type=='image' || is_iOS() || current_panorama_type=='hls' || current_panorama_type=='lottie') {
                                            pano_viewer.stopOrientation();
                                        } else {
                                            video_viewer.pnlmViewer.stopOrientation();
                                        }
                                    }
                                },
                            }
                        });
                    });
                }
            }
            if(show_share>0) {
                $("#vt_container").floatingSocialShare({
                    place: "content-left",
                    counter: false,
                    twitter_counter: false,
                    buttons: [
                        "mail", "facebook", "linkedin",
                        "twitter", "vk", "telegram",
                        "viber", "whatsapp"
                    ],
                    title: name_virtual_tour,
                    url: window.location.href,
                    text: {
                        'default': 'share with ',
                        'facebook': 'share with facebook',
                        'twitter': 'tweet'
                    },
                    text_title_case: false,
                    description: $('meta[name="description"]').attr("content"),
                    media: $('meta[property="og:image"]').attr("content"),
                    target: true,
                    popup: true,
                    popup_width: 800,
                    popup_height: 600
                });
                $('#floatingSocialShare .content-left').append('<i onclick="toggle_share()" class="fa fa-times-circle"></i>');
            }
            if(show_share>0) {
                if (window.is_mobile) {
                    var link_whatsapp = $('.whatsapp').attr('href');
                    link_whatsapp = link_whatsapp.replace('web.whatsapp', 'api.whatsapp');
                    $('.whatsapp').attr('href', link_whatsapp);
                }
            }
            if((whatsapp_chat) && (whatsapp_number!='')) {
                $('.whatsapp_control').show();
                $('.whatsapp_control').css({'opacity':1,'pointer-events':'initial'});
            }
            $('.facebook_control').css({'opacity':1,'pointer-events':'initial'});
            if(show_compass) {
                $('.compass_control').css({'opacity':1,'pointer-events':'initial'});
            }
            if(show_presentation) {
                $('.presentation_control').css({'opacity':1,'pointer-events':'initial'});
            }
            if(show_info) {
                $('.info_control').css({'opacity':1,'pointer-events':'initial'});
            }
            if(show_custom) {
                $('.custom_control').css({'opacity':1,'pointer-events':'initial'});
            }
            if(show_dollhouse>0) {
                $('.dollhouse_control').css({'opacity':1,'pointer-events':'initial'});
            }
            if(show_gallery) {
                $('.gallery_control').css({'opacity':1,'pointer-events':'initial'});
            }
            if(show_share>0) {
                $('.share_control').css({'opacity':1,'pointer-events':'initial'});
            }
            if(show_live_session) {
                $('.live_control').css({'opacity':1,'pointer-events':'initial'});
            }
            if(show_meeting) {
                $('.meeting_control').css({'opacity':1,'pointer-events':'initial'});
            }
            if(show_webvr) {
                $('.vr_control').css({'opacity':1,'pointer-events':'initial'});
            }
            if(form_enable) {
                $('.form_control').css({'opacity':1,'pointer-events':'initial'});
            }
            if(auto_show_slider!=2 || arrows_nav!=0) {
                $('.controls_arrows').css({'opacity':1,'pointer-events':'initial'});
                $('.controls_arrows').show();
                if($('.controls_arrows .prev_arrow').is(':visible')) {
                    $('.controls_arrows i:not(.hidden):first-child').css('margin-right','-9px');
                    $('.controls_arrows i:not(.hidden):nth-child(2)').css('margin-right','-9px');
                }
            }
            if(window.peer_id!='') {
                init_peer();
            } else {
                if(window.is_mobile) {
                    if(intro_mobile!='') {
                        $('.intro_img').css('display','flex');
                    }
                } else {
                    if(intro_desktop!='') {
                        $('.intro_img').css('display','flex');
                    }
                }
                setTimeout(function () {
                    $('.intro_img').fadeOut();
                },5000);
            }
            get_info_box();
            get_custom_box();
            preload_images();
            if(array_presentation.length>0 && show_presentation==2)  {
                start_presentation();
            }
            if(window.meeting==2) {
                toggle_meeting();
            }
            if(gallery_images.length>0 && show_gallery==2) {
                open_gallery();
            }
            if(window.background_video!='') {
                setTimeout(function () {
                    var video_loading = $("#video_loading").get(0);
                    video_loading.pause();
                    video_loading.currentTime = 0;
                    $("#video_loading").hide();
                },3000);
            }
            $('.snipcart-checkout').css({'opacity':1,'pointer-events':'initial'});
            if($('.list_slider').css('opacity')==1) {
                reposition_bottom_controls(true, false,0);
            } else {
                reposition_bottom_controls(false, false,0);
            }
            $('#controls_bottom_center,#controls_bottom_left,#controls_bottom_right,.voice_control').css('opacity',1);
            if(window.flyin_enabled==1 && autorotate_speed>0) {
                if(current_panorama_type=='image' || is_iOS() || current_panorama_type=='hls' || current_panorama_type=='lottie') {
                    try {
                        pano_viewer.setAutoRotateInactivityDelay(autorotate_inactivity);
                        setTimeout(function () {
                            pano_viewer.startAutoRotate(autorotate_speed);
                        },autorotate_inactivity);
                    } catch (e) {}
                } else {
                    try {
                        video_viewer.pnlmViewer.setAutoRotateInactivityDelay(autorotate_inactivity);
                        setTimeout(function () {
                            video_viewer.pnlmViewer.startAutoRotate(autorotate_speed);
                        },autorotate_inactivity);
                    } catch (e) {}
                }
            }
            if(show_dollhouse>0 && json_dollhouse!='') {
                init_dollhouse();
            }
            if((window.export_mode==0) && (window.preview==0) && window.ip_visitor!='') {
                store_visitor_init();
            }
            if($.trim($("#custom_html").html())!='') {
                $('#custom_html').fadeIn();
            }
            idleTimeout();
            fix_colors_menu();
            exec_store_visitor(true);
            window.virtual_tour_initialized = true;
            parent.postMessage({"payload":'initialized'}, "*");
        },500);
        if(autoclose_menu) {
            $('body').on('click', '.menu_controls .dropdown p', function () {
                close_menu_controls();
            });
        }
        if(autoclose_list_alt) {
            $('body').on('click', '.list_alt_menu .dropdown p:not(.cat)', function () {
                close_list_alt_menu();
            });
        }
        if(autoclose_slider) {
            $('body').on('click', '.pointer_list', function () {
                close_list();
            });
        }
        if(autoclose_map) {
            $('body').on('click', '.pointer', function () {
                if(show_map==4) {
                    toggle_map();
                } else {
                    if($(".map").hasClass("map_zoomed")) {
                        $(".map").removeClass('map_zoomed');
                        $('#map_zoomed_background').hide();
                        $('.map_zoom_control i').addClass('fa-expand-alt').removeClass('fa-compress-alt');
                        resize_maps();
                    }
                }
            });
        }
    }

    window.share_on = function (w) {
        if(show_share==2) {
            if(current_panorama_type=='image' || is_iOS() || current_panorama_type=='hls' || current_panorama_type=='lottie') {
                var current_yaw = parseFloat(pano_viewer.getYaw());
                var current_pitch = parseFloat(pano_viewer.getPitch());
            } else {
                var current_yaw = parseFloat(video_viewer.pnlmViewer.getYaw());
                var current_pitch = parseFloat(video_viewer.pnlmViewer.getPitch());
            }
            var url_vt = window.base_url+'viewer/index.php?code='+window.code+'&room='+current_id_panorama+'&yaw='+current_yaw+'&pitch='+current_pitch;
            var url_share = $('#floatingSocialShare .'+w).attr('data-url');
            var new_href = url_share.replace("{url}", encodeURIComponent(url_vt))
                .replace("{title}", encodeURIComponent(document.title))
                .replace("{description}", encodeURIComponent($('meta[name="description"]').attr("content") || ""))
                .replace("{media}", encodeURIComponent($('meta[property="og:image"]').attr("content") || ""));
            $('#floatingSocialShare .'+w).attr('href',new_href);
        }
        return true;
    }

    window.adjust_elements_positions = function () {
        var logo_h = $('.logo')[0].getBoundingClientRect().height;
        var annotation_h = $('.annotation')[0].getBoundingClientRect().height;
        if(($('.menu_controls').hasClass('hidden')) && ($('.list_alt_menu').hasClass('hidden')) && ($('.song_control').hasClass('hidden'))) {
            var top_left = 5;
            $('.logo_top_left').css('top','5px');
        } else {
            var top_left = 45;
        }
        if(($('.map_control').hasClass('hidden')) && ($('.map_tour_control').hasClass('hidden')) && ($('.fullscreen_control').hasClass('hidden'))) {
            var top_right = 5;
            $('.logo_top_right').css('top','5px');
        } else {
            var top_right = 45;
        }
        $('.map_top_right').css('top',top_right+'px');
        $('.annotation_top_right').css('top',top_right+'px');
        $('.map_top_left').css('top',top_left+'px');
        $('.annotation_top_left').css('top',top_left+'px');
        if($('.logo').hasClass('logo_top_right') && $('.logo').is(':visible') && $('.logo').css('opacity')==1) {
            top_right += logo_h+5;
            $('.map_top_right').css('top',top_right+'px');
            $('.annotation_top_right').css('top',top_right+'px');
        }
        if($('.annotation').hasClass('annotation_top_right') && $('.annotation').is(':visible') && $('.annotation').css('opacity')==1) {
            top_right += annotation_h+5;
            $('.map_top_right').css('top',top_right+'px');
        }
        if($('.logo').hasClass('logo_top_left') && $('.logo').is(':visible') && $('.logo').css('opacity')==1) {
            top_left += logo_h+5;
            $('.map_top_left').css('top',top_left+'px');
            $('.annotation_top_left').css('top',top_left+'px');
        }
        if($('.annotation').hasClass('annotation_top_left') && $('.annotation').is(':visible') && $('.annotation').css('opacity')==1) {
            top_left += annotation_h+5;
            $('.map_top_left').css('top',top_left+'px');
        }
    }

    window.toggle_fullscreen = function () {
        if($('.fullscreen_control').hasClass('active_control')) {
            $('.fullscreen_control').removeClass('active_control');
            $('.fullscreen_control i').removeClass('fa-compress').addClass('fa-expand');
            controls_status['fullscreen']=false;
        } else {
            $('.fullscreen_control').addClass('active_control');
            $('.fullscreen_control i').removeClass('fa-expand').addClass('fa-compress');
            controls_status['fullscreen']=true;
        }
        if(current_panorama_type=='image' || is_iOS() || current_panorama_type=='hls' || current_panorama_type=='lottie') {
            pano_viewer.toggleFullscreen();
        } else {
            video_viewer.pnlmViewer.toggleFullscreen();
        }
        window.scrollTo(0, 0);
    }

    window.toggle_jitsi_fullscreen = function () {
        if($('#btn_jitsi_fullscreen').hasClass('active_control')) {
            $('#btn_jitsi_fullscreen').removeClass('active_control');
            $('#btn_jitsi_fullscreen').removeClass('fa-compress').addClass('fa-expand');
            $('#jitsi_div').removeClass('visible_jitsi_meet_fullscreen').addClass('visible_jitsi_meet');
        } else {
            $('#btn_jitsi_fullscreen').addClass('active_control');
            $('#btn_jitsi_fullscreen').removeClass('fa-expand').addClass('fa-compress');
            $('#jitsi_div').removeClass('visible_jitsi_meet').addClass('visible_jitsi_meet_fullscreen');
        }
    }

    window.toggle_jitsi_hide = function () {
        if($('#btn_jitsi_hide').hasClass('active_control')) {
            $('#jitsi_show').hide();
            $('#btn_jitsi_hide').removeClass('active_control');
            $('#jitsi_div').addClass('visible_jitsi_meet');
            if($('#vt_container').hasClass('open_map_tour')) {
                $('#vt_container').addClass('open_jitsi_meet_map_tour');
                $('#vt_container').removeClass('open_map_tour');
            } else {
                $('#vt_container').addClass('open_jitsi_meet');
            }
        } else {
            $('#jitsi_show').show();
            $('#btn_jitsi_hide').addClass('active_control');
            $('#btn_jitsi_fullscreen').removeClass('fa-compress').addClass('fa-expand');
            $('#btn_jitsi_fullscreen').removeClass('active_control');
            $('#jitsi_div').removeClass('visible_jitsi_meet').removeClass('visible_jitsi_meet_fullscreen');
            $('#btn_jitsi_fullscreen').removeClass('fa-compress').addClass('fa-expand');
            if($('#vt_container').hasClass('open_jitsi_meet_map_tour')) {
                $('#vt_container').removeClass('open_jitsi_meet_map_tour');
                $('#vt_container').addClass('open_map_tour');
            } else {
                $('#vt_container').removeClass('open_jitsi_meet');
            }
        }
        interval_adjust_embed_helpers_all = setInterval(function () {
            adjust_poi_embed_helpers_all();
            adjust_marker_embed_helpers_all();
            count_adjust_embed_helpers_all++;
            if(count_adjust_embed_helpers_all>=2000) {
                clearInterval(interval_adjust_embed_helpers_all);
            }
        },1);
    }

    window.close_live = function () {
        $.confirm({
            useBootstrap: false,
            closeIcon: false,
            type: 'red',
            typeAnimated: true,
            title: window.viewer_labels.lsc_title,
            content: window.viewer_labels.lsc_content,
            buttons: {
                end_call: {
                    text: window.viewer_labels.lsc_endcall,
                    btnClass: 'btn-red',
                    action: function(){
                        try {
                            window.stream_sender.getTracks().forEach(function(track) { track.stop(); })
                        } catch (e) {}
                        try {
                            call_session.close();
                        } catch (e) {}
                        $('#webcam_my').hide();
                        peer.destroy();
                        live_session_connected = false;
                        clearInterval(interval_live_session);
                        $('.live_call').hide();
                        $('.live_control').removeClass('active_control');
                        exit_sender_viewer();
                    }
                },
                cancel : {
                    text: window.viewer_labels.cancel
                },
            }
        });
    }

    window.close_live_receiver = function () {
        $.confirm({
            useBootstrap: false,
            closeIcon: false,
            type: 'red',
            typeAnimated: true,
            title: window.viewer_labels.lsc_title,
            content: window.viewer_labels.lsc_content2,
            buttons: {
                end_call: {
                    text: window.viewer_labels.lsc_endcall,
                    btnClass: 'btn-red',
                    action: function(){
                        try {
                            window.stream_sender.getTracks().forEach(function(track) { track.stop(); })
                        } catch (e) {}
                        try {
                            call_session.close();
                        } catch (e) {}
                        $('#webcam_my').hide();
                        peer.destroy();
                        $('.live_call').hide();
                    }
                },
                cancel : {
                    text: window.viewer_labels.cancel
                },
            }
        });
    }

    window.toggle_live = function () {
        if($('.live_control').hasClass('active_control')) {
            close_live();
        } else {
            if(window.livesession_protected==1) {
                confirm_password_modal = $.confirm({
                    useBootstrap: false,
                    closeIcon: false,
                    type: 'blue',
                    typeAnimated: true,
                    title: window.viewer_labels.password_livesession,
                    content: '<div style="margin: 0;overflow: hidden" class="input_material"><input autocomplete="off" id="password_livesession" type="text" /><span class="highlight"></span><span class="bar"></span></div>',
                    buttons: {
                        check: {
                            text: window.viewer_labels.check,
                            btnClass: 'btn-green',
                            action: function(){
                                $('#password_livesession').removeClass('error_input');
                                var password_livesession = $('#password_livesession').val();
                                if(password_livesession!='') {
                                    $('.jconfirm-buttons .btn').addClass('disabled');
                                    $.ajax({
                                        url: "ajax/check_password.php",
                                        type: "POST",
                                        data: {
                                            id_virtualtour: window.id_virtualtour,
                                            password: password_livesession,
                                            type: 'livesession'
                                        },
                                        async: true,
                                        success: function (json) {
                                            $('.jconfirm-buttons .btn').removeClass('disabled');
                                            var rsp = JSON.parse(json);
                                            if(rsp.status=='ok') {
                                                confirm_password_modal.close();
                                                toggle_live_enable();
                                            } else {
                                                $('#password_livesession').addClass('error_input');
                                            }
                                        }
                                    });
                                } else {
                                    $('#password_livesession').addClass('error_input');
                                }
                                return false;
                            }
                        },
                        cancel : {
                            text: window.viewer_labels.cancel
                        },
                    }
                });
            } else {
                toggle_live_enable();
            }
        }
    }

    function toggle_live_enable() {
        $('.live_control').addClass('active_control');
        close_menu_controls();
        close_list_alt_menu();
        init_peer();
    }

    window.toggle_meeting = function () {
        if($('.meeting_control').hasClass('active_control')) {
            close_menu_controls();
            close_jitsi_meet();
        } else {
            if(window.meeting_protected==1) {
                confirm_password_modal = $.confirm({
                    useBootstrap: false,
                    closeIcon: false,
                    type: 'blue',
                    typeAnimated: true,
                    title: window.viewer_labels.password_meeting,
                    content: '<div style="margin: 0;overflow: hidden" class="input_material"><input autocomplete="off" id="password_meeting" type="text" /><span class="highlight"></span><span class="bar"></span></div>',
                    buttons: {
                        check: {
                            text: window.viewer_labels.check,
                            btnClass: 'btn-green',
                            action: function(){
                                $('#password_meeting').removeClass('error_input');
                                var password_meeting = $('#password_meeting').val();
                                if(password_meeting!='') {
                                    $('.jconfirm-buttons .btn').addClass('disabled');
                                    $.ajax({
                                        url: "ajax/check_password.php",
                                        type: "POST",
                                        data: {
                                            id_virtualtour: window.id_virtualtour,
                                            password: password_meeting,
                                            type: 'meeting'
                                        },
                                        async: true,
                                        success: function (json) {
                                            $('.jconfirm-buttons .btn').removeClass('disabled');
                                            var rsp = JSON.parse(json);
                                            if(rsp.status=='ok') {
                                                confirm_password_modal.close();
                                                toggle_meeting_enable();
                                            } else {
                                                $('#password_meeting').addClass('error_input');
                                            }
                                        }
                                    });
                                } else {
                                    $('#password_meeting').addClass('error_input');
                                }
                                return false;
                            }
                        },
                        cancel : {
                            text: window.viewer_labels.cancel
                        },
                    }
                });
            } else {
                toggle_meeting_enable();
            }
        }
    }

    function toggle_meeting_enable() {
        $('.meeting_control:not(".controls_btn") span').html(window.viewer_labels.exit_meeting);
        $('.controls_btn.meeting_control').attr('title',window.viewer_labels.exit_meeting);
        $('.controls_btn.meeting_control').tooltipster('content', window.viewer_labels.exit_meeting);
        $('.meeting_control:not(".controls_btn") i').css('color','red');
        $('.meeting_control').addClass('active_control');
        $('.live_control').addClass('disabled');
        if(voice_commands_enable>0) {
            $('.voice_control').hide();
            $('#controls_bottom_left').css('padding-left','0px');
            try {
                SpeechKITT.hide();
                if (annyang) { annyang.pause(); }
            } catch (e) {}
        }
        close_menu_controls();
        open_jitsi_meet();
    }

    window.close_jitsi_meet = function () {
        api_jitsi.dispose();
        $('.meeting_control:not(".controls_btn") span').html(window.viewer_labels.join_meeting);
        $('.controls_btn.meeting_control').attr('title',window.viewer_labels.join_meeting);
        $('.controls_btn.meeting_control').tooltipster('content', window.viewer_labels.join_meeting);
        $('.meeting_control:not(".controls_btn") i').css('color','green');
        $('.meeting_control').removeClass('active_control');
        $('#jitsi_show').hide();
        $('#btn_jitsi_fullscreen').removeClass('fa-compress').addClass('fa-expand');
        if(voice_commands_enable>0) {
            $('.voice_control').show();
            var pl = 32;
            if(window.c_width<=480) {
                pl = 29;
            }
            if (window.c_width <= 360) {
                pl = 27;
            }
            $('#controls_bottom_left').css('padding-left',pl+'px');
            try {
                SpeechKITT.show();
            } catch (e) {}
            if(voice_commands_enable==2) {
                try {
                    if (annyang) { annyang.resume(); }
                } catch (e) {}
            }
        }
        $('.live_control').removeClass('disabled');
        close_menu_controls();
        if($('#vt_container').hasClass('open_jitsi_meet_map_tour')) {
            $('#vt_container').removeClass('open_jitsi_meet_map_tour');
            $('#vt_container').addClass('open_map_tour');
        } else {
            $('#vt_container').removeClass('open_jitsi_meet');
        }
        $('#jitsi_div').removeClass('visible_jitsi_meet');
        interval_adjust_embed_helpers_all = setInterval(function () {
            adjust_poi_embed_helpers_all();
            adjust_marker_embed_helpers_all();
            count_adjust_embed_helpers_all++;
            if(count_adjust_embed_helpers_all>=2000) {
                clearInterval(interval_adjust_embed_helpers_all);
            }
        },1);
    }

    window.toggle_orient = function () {
        if($('.orient_control').hasClass('active_control')) {
            $('.orient_control').removeClass('active_control');
            $('.orient_control .fa-circle').removeClass('active').addClass('not_active');
            controls_status['orient']=false;
            if(current_panorama_type=='image' || is_iOS() || current_panorama_type=='hls' || current_panorama_type=='lottie') {
                pano_viewer.stopOrientation();
            } else {
                video_viewer.pnlmViewer.stopOrientation();
            }
            if(drag_device_orientation==0) {
                $('.pnlm-dragfix').css('pointer-events','initial');
            }
        } else {
            $('.orient_control').addClass('active_control');
            $('.orient_control .fa-circle').removeClass('not_active').addClass('active');
            controls_status['orient']=true;
            if(current_panorama_type=='image' || is_iOS() || current_panorama_type=='hls' || current_panorama_type=='lottie') {
                pano_viewer.startOrientation();
            } else {
                video_viewer.pnlmViewer.startOrientation();
            }
            close_menu_controls();
            if(drag_device_orientation==0) {
                $('.pnlm-dragfix').css('pointer-events','none');
            }
        }
    }

    window.open_gallery = function () {
        if(current_panorama_type=='image' || is_iOS() || current_panorama_type=='hls' || current_panorama_type=='lottie') {
            pano_viewer.stopOrientation();
        } else {
            video_viewer.pnlmViewer.stopOrientation();
        }
        try {
            $('#gallery_container').nanogallery2('destroy');
        } catch (e) {}
        gallery = $("#gallery_container").nanogallery2({
            imageTransition: 'slideAppear',
            thumbnailHeight:  150,
            thumbnailWidth:   150,
            items: gallery_images,
            allowHTMLinData: true,
            viewerHideToolsDelay: 30000,
            viewerToolbar:    {
                display:    true,
                autoMinimize: false,
                standard:   'label'
            },
            viewerTools:    {
                topLeft:    'pageCounter, playPauseButton',
                topRight:   'zoomButton, closeButton'
            }
        });
        gallery.nanogallery2('displayItem', '-1/1');
        $('.nGY2Viewer .closeButton').on('click',function () {
            if(live_session_connected) {
                try {
                    peer_conn.send({type:'close_gallery'});
                } catch (e) {}
            }
            restart_autorotate();
        });
        if(live_session_connected) {
            try {
                peer_conn.send({type:'open_gallery'});
            } catch (e) {}
        }
    }

    window.open_product_gallery = function (id_product) {
        var product_images = [];
        var index = 1, index_active = 1;
        $('#carouselIndicators_'+id_product+' .carousel-item img').each(function() {
            var src = $(this).attr('src');
            var src_t = src.replace('products/','products/thumb/');
            var active = $(this).parent().hasClass('active');
            if(active) {
                index_active = index;
            }
            product_images.push({
                "ID":index,
                "kind":'image',
                "src":src,
                "srct":src_t,
            });
            index++;
        });
        try {
            $('#gallery_container').nanogallery2('destroy');
        } catch (e) {}
        gallery = $("#gallery_container").nanogallery2({
            imageTransition: 'slideAppear',
            thumbnailHeight:  150,
            thumbnailWidth:   150,
            items: product_images,
            allowHTMLinData: true,
            viewerHideToolsDelay: 30000,
            viewerToolbar:    {
                display:    true,
                autoMinimize: false,
                standard:   'label'
            },
            viewerTools:    {
                topLeft:    'pageCounter, playPauseButton',
                topRight:   'zoomButton, closeButton'
            }
        });
        gallery.nanogallery2('displayItem', '-1/'+index_active);
    }

    window.toggle_map = function () {
        if($('.map_control').hasClass('active_control')) {
            $('.map').hide();
            $('#map_zoomed_background').hide();
            $('.map_control').removeClass('active_control');
            $('.map_control i').removeClass('icon-map_on').addClass('icon-map_off');
            controls_status['map']=false;
        } else {
            $('.map').show();
            if($('.map').hasClass('map_zoomed')) {
                $('#map_zoomed_background').show();
            }
            $('.map_control').addClass('active_control');
            $('.map_control i').removeClass('icon-map_off').addClass('icon-map_on');
            controls_status['map']=true;
        }
        resize_maps();
    }

    window.toggle_tour_map = function () {
        if($('.map_tour_control').hasClass('active_control')) {
            $('.map_tour_control i').removeClass('fas').addClass('far');
            $('.map_tour_control').removeClass('active_control');
            close_map_tour();
        } else {
            $('.map_tour_control i').removeClass('far').addClass('fas');
            $('.map_tour_control').addClass('active_control');
            open_map_tour();
        }
    }

    window.close_list = function () {
        $('.list_control').removeClass('active_control');
        reposition_bottom_controls(false,false,300);
        $('.list_control i').removeClass('fa-chevron-down').addClass('fa-chevron-up');
        $('.list_control_alt').removeClass('fa-chevron-down').addClass('fa-chevron-up');
        controls_status['list']=false;
    }

    window.toggle_list = function () {
        if($('.list_control').hasClass('active_control')) {
            $('.list_control').removeClass('active_control');
            reposition_bottom_controls(false,false,300);
            $('.list_control i').removeClass('fa-chevron-down').addClass('fa-chevron-up');
            $('.list_control_alt').removeClass('fa-chevron-down').addClass('fa-chevron-up');
            controls_status['list']=false;
        } else {
            $('.list_slider').css('z-index',1000);
            $('.list_slider').css('opacity',1);
            $('.list_control').addClass('active_control');
            reposition_bottom_controls(true,false,300);
            $('.list_control i').removeClass('fa-chevron-up').addClass('fa-chevron-down');
            $('.list_control_alt').removeClass('fa-chevron-up').addClass('fa-chevron-down');
            controls_status['list']=true;
        }
    }

    function reposition_bottom_controls(open,force,anim_duration) {
        window.c_width = window.innerWidth || document.documentElement.clientWidth || document.body.clientWidth;
        var f_h = parseInt($('.list_slider').height());
        var width_blc = $('#controls_bottom_left')[0].getBoundingClientRect().width+20;
        var width_brc = $('#controls_bottom_right')[0].getBoundingClientRect().width+20;
        var half_width = window.c_width/2;
        if($('.list_control').is(':visible')) {
            var bottom = 6;
            var bottom_h = 32;
            if((width_blc>=half_width) || (width_brc>=half_width)) {
                bottom = 32;
            }
        } else {
            var bottom = 6;
            var bottom_h = 6;
        }
        if(open) {
            if(force) {
                $(".list_control").animate({
                    bottom: f_h+'px',
                }, {duration: anim_duration, queue: false});
                $("#controls_bottom_left,#controls_bottom_right,.voice_control").animate({
                    bottom: (f_h+bottom)+'px',
                }, {duration: anim_duration, queue: false});
                $("#controls_bottom_center").animate({
                    bottom: (f_h+bottom_h)+'px',
                }, {duration: anim_duration, queue: false});
                $(".list_slider").animate({
                    bottom: '0px',
                }, {duration: anim_duration, queue: false});
                $(".nav_control").animate({
                    bottom: (f_h+46)+'px',
                }, {duration: anim_duration, queue: false});
                $("#floatingSocialShare").animate({
                    bottom: (f_h+46)+'px',
                }, {duration: anim_duration, queue: false});
            } else {
                if(current_panorama_type=='image' || is_iOS() || current_panorama_type=='hls' || current_panorama_type=='lottie') {
                    $(".list_control").animate({
                        bottom: f_h+'px',
                    }, {duration: anim_duration, queue: false});
                    $("#controls_bottom_left,#controls_bottom_right,.voice_control").animate({
                        bottom: (f_h+bottom)+'px',
                    }, {duration: anim_duration, queue: false});
                    $("#controls_bottom_center").animate({
                        bottom: (f_h+bottom_h)+'px',
                    }, {duration: anim_duration, queue: false});
                    $(".list_slider").animate({
                        bottom: '0px',
                    }, {duration: anim_duration, queue: false});
                    $(".nav_control").animate({
                        bottom: (f_h+46)+'px',
                    }, {duration: anim_duration, queue: false});
                    $("#floatingSocialShare").animate({
                        bottom: (f_h+46)+'px',
                    }, {duration: anim_duration, queue: false});
                }  else {
                    $(".list_control").animate({
                        bottom: (f_h+30)+'px',
                    }, {duration: anim_duration, queue: false});
                    $("#controls_bottom_left,#controls_bottom_right,.voice_control").animate({
                        bottom: (f_h+30+bottom)+'px',
                    }, {duration: anim_duration, queue: false});
                    $("#controls_bottom_center").animate({
                        bottom: (f_h+30+bottom_h)+'px',
                    }, {duration: anim_duration, queue: false});
                    $(".list_slider").animate({
                        bottom: '30px',
                    }, {duration: anim_duration, queue: false});
                    $(".nav_control").animate({
                        bottom: ((f_h+30+bottom_h)+46)+'px',
                    }, {duration: anim_duration, queue: false});
                    $("#floatingSocialShare").animate({
                        bottom: ((f_h+30)+46)+'px',
                    }, {duration: anim_duration, queue: false});
                }
            }
        } else {
            if(force) {
                $(".list_control").animate({
                    bottom: '0px',
                }, {duration: anim_duration, queue: false});
                $("#controls_bottom_left,#controls_bottom_right,.voice_control").animate({
                    bottom: bottom+'px',
                }, {duration: anim_duration, queue: false});
                $("#controls_bottom_center").animate({
                    bottom: bottom_h+'px',
                }, {duration: anim_duration, queue: false});
                $(".list_slider").animate({
                    bottom: '-'+f_h+'px',
                }, {duration: anim_duration, queue: false});
                $(".nav_control").animate({
                    bottom: '46px',
                }, {duration: anim_duration, queue: false});
                $("#floatingSocialShare").animate({
                    bottom: '46px',
                }, {duration: anim_duration, queue: false});
            } else {
                if(current_panorama_type=='image' || is_iOS() || current_panorama_type=='hls' || current_panorama_type=='lottie') {
                    $(".list_control").animate({
                        bottom: '0px',
                    }, {duration: anim_duration, queue: false});
                    $("#controls_bottom_left,#controls_bottom_right,.voice_control").animate({
                        bottom: bottom+'px',
                    }, {duration: anim_duration, queue: false});
                    $("#controls_bottom_center").animate({
                        bottom: bottom_h+'px',
                    }, {duration: anim_duration, queue: false});
                    $(".list_slider").animate({
                        bottom: '-'+f_h+'px',
                    }, {duration: anim_duration, queue: false});
                    $(".nav_control").animate({
                        bottom: '46px',
                    }, {duration: anim_duration, queue: false});
                    $("#floatingSocialShare").animate({
                        bottom: '46px',
                    }, {duration: anim_duration, queue: false});
                } else {
                    $(".list_control").animate({
                        bottom: '30px',
                    }, {duration: anim_duration, queue: false});
                    $("#controls_bottom_left,#controls_bottom_right,.voice_control").animate({
                        bottom: (bottom+30)+'px',
                    }, {duration: anim_duration, queue: false});
                    $("#controls_bottom_center").animate({
                        bottom: (bottom_h+30)+'px',
                    }, {duration: anim_duration, queue: false});
                    $(".list_slider").animate({
                        bottom: '-'+(f_h-30)+'px',
                    }, {duration: anim_duration, queue: false});
                    $(".nav_control").animate({
                        bottom: '76px',
                    }, {duration: anim_duration, queue: false});
                    $("#floatingSocialShare").animate({
                        bottom: '76px',
                    }, {duration: anim_duration, queue: false});
                }
            }
            setTimeout(function() {
                $('.list_slider').css('z-index',0);
                $('.list_slider').css('opacity',0);
            },300);
        }
    }

    window.toggle_share = function () {
        if($('.share_control').hasClass('active_control')) {
            $('#floatingSocialShare').css('z-index',0);
            $('#floatingSocialShare').hide();
            $('.share_control').removeClass('active_control');
            $('.share_control .fa-circle').removeClass('active').addClass('not_active');
            if($('.list_slider').css('opacity')==1) {
                reposition_bottom_controls(true,false,0);
            } else {
                reposition_bottom_controls(false,false,0);
            }
            controls_status['share']=false;
        } else {
            $('#floatingSocialShare').css('z-index',1001);
            $('#floatingSocialShare').show();
            $('.share_control').addClass('active_control');
            $('.share_control .fa-circle').removeClass('not_active').addClass('active');
            if($('.list_slider').css('opacity')==1) {
                reposition_bottom_controls(true,false,0);
            } else {
                reposition_bottom_controls(false,false,0);
            }
            controls_status['share']=true;
        }
    }

    window.toggle_icons = function () {
        if($('.icons_control').hasClass('active_control')) {
            $('.custom-hotspot').addClass('hidden_icons');
            $('.custom-hotspot img').addClass('hidden_icons');
            $('.custom-hotspot-content').addClass('hidden_icons');
            $('.poi_embed').addClass('hidden_icons');
            $('.marker_embed').addClass('hidden_icons');
            $('.div_poi_wrapper').removeClass('pulse_icon_hover');
            $('.div_poi_wrapper').removeClass('pulse_image_hover');
            $('.div_marker_wrapper').removeClass('pulse_icon_hover');
            $('.div_marker_wrapper').removeClass('pulse_image_hover');
            $('.pnlm-pointer').removeClass('hotspot_hover');
            $('.icons_control').removeClass('active_control');
            $('.icons_control .fa-circle').removeClass('active').addClass('not_active');
            controls_status['icons']=false;
        } else {
            $('.custom-hotspot').removeClass('hidden_icons');
            $('.custom-hotspot img').removeClass('hidden_icons');
            $('.custom-hotspot-content').removeClass('hidden_icons');
            $('.poi_embed').removeClass('hidden_icons');
            $('.marker_embed').removeClass('hidden_icons');
            $('.icons_control').addClass('active_control');
            $('.icons_control .fa-circle').removeClass('not_active').addClass('active');
            controls_status['icons']=true;
        }
    }

    window.toggle_autorotate = function () {
        if($('.autorotate_control').hasClass('active_control')) {
            if(current_panorama_type=='image' || is_iOS() || current_panorama_type=='hls' || current_panorama_type=='lottie') {
                pano_viewer.stopAutoRotate();
            } else {
                video_viewer.pnlmViewer.stopAutoRotate();
            }
            $('.nav_rotate').removeClass('active_rotate');
            $('.autorotate_control').removeClass('active_control');
            $('.autorotate_control .fa-circle').removeClass('active').addClass('not_active');
            controls_status['auto_rotate']=false;
        } else {
            if(current_panorama_type=='image' || is_iOS() || current_panorama_type=='hls' || current_panorama_type=='lottie') {
                pano_viewer.startAutoRotate();
            } else {
                video_viewer.pnlmViewer.startAutoRotate();
            }
            $('.nav_rotate').addClass('active_rotate');
            $('.autorotate_control').addClass('active_control');
            $('.autorotate_control .fa-circle').removeClass('not_active').addClass('active');
            controls_status['auto_rotate']=true;
        }
    }

    window.toggle_annotations = function () {
        if($('.annotations_control').hasClass('active_control')) {
            $('.annotation').css({'opacity':0,'pointer-events':'none'});
            $('.annotations_control').removeClass('active_control');
            $('.annotations_control .fa-circle').removeClass('active').addClass('not_active');
            controls_status['annotations']=false;
        } else {
            $('.annotation').css({'opacity':1,'pointer-events':'initial'});
            $('.annotations_control').addClass('active_control');
            $('.annotations_control .fa-circle').removeClass('not_active').addClass('active');
            controls_status['annotations']=true;
        }
        adjust_elements_positions();
    }

    function audio_isPlaying() {
        return !audio_player.paused && !audio_player.ended && 0 < audio_player.currentTime;
    }

    function set_audio_volume(volume) {
        try {
            audio_player.volume = volume;
        } catch (e) {}
    }

    function mute_audio(song,video) {
        if(is_iOS() || current_panorama_type=='hls') {
            if(song) {
                audio_player.muted = true;
                audio_player_room.muted = true;
            }
            if(video) {
                try {
                    video_p.volume = 0;
                } catch (e) {}
                try {
                    video_p.muted = true;
                } catch (e) {}
                try {
                    var video_elem = document.getElementById('video_viewer_html5_api');
                    video_elem.muted = true;
                } catch (e) {}
            }
        } else {
            if(song) {
                audio_player.volume = 0;
                audio_player_room.volume = 0;
            }
            if(video) {
                try {
                    var video_elem = document.getElementById('video_viewer_html5_api');
                    video_elem.volume = 0;
                    video_elem.muted = true;
                } catch (e) {}
                try {
                    video_viewer.muted(true);
                } catch (e) {}
            }
        }
    }

    function unmute_audio(song,video) {
        if(is_iOS() || current_panorama_type=='hls') {
            if(song) {
                audio_player.muted = false;
                audio_player.volume = song_bg_volume_sel;
                audio_player_room.muted = false;
                audio_player_room.volume = 1.0;
            }
            if(video) {
                try {
                    video_p.volume = 1;
                } catch (e) {}
                try {
                    video_p.muted = false;
                } catch (e) {}
                try {
                    var video_elem = document.getElementById('video_viewer_html5_api');
                    video_elem.muted = false;
                } catch (e) {}
            }
        } else {
            if(song) {
                audio_player.volume = song_bg_volume_sel;
                audio_player_room.volume = 1.0;
            }
            if(video) {
                try {
                    var video_elem = document.getElementById('video_viewer_html5_api');
                    video_elem.volume = 1;
                    video_elem.muted = false;
                } catch (e) {}
                try {
                    video_viewer.muted(false);
                } catch (e) {}
            }
        }
    }

    window.toggle_song = function () {
        if($('.song_control').hasClass('active_control')) {
            mute_audio(true,true);
            $('.song_control').removeClass('active_control');
            $('.song_control i').removeClass('fa-volume-down').addClass('fa-volume-mute');
            controls_status['song']=false;
        } else {
            var index = get_id_viewer(current_id_panorama);
            if(panoramas[index].audio_track_enable) {
                if(panoramas[index].song!='') {
                    unmute_audio(true,false);
                } else {
                    mute_audio(true,false);
                }
            } else {
                unmute_audio(true,false);
            }
            unmute_audio(false,true);
            $('.song_control').addClass('active_control');
            $('.song_control i').addClass('fa-volume-down').removeClass('fa-volume-mute');
            controls_status['song']=true;
            if(!audio_isPlaying()) {
                try {
                    audio_player.play();
                } catch (e) {}
            }
        }
    }

    window.view_facebook_messenger = function () {
        if($('.fb_iframe_widget iframe').hasClass('fb_customer_chat_bounce_in_v2')) {
            $('#fb-root').css('opacity',0);
            FB.CustomerChat.hideDialog();
        } else {
            $('#fb-root').css('opacity',1);
            FB.CustomerChat.showDialog();
        }
    }

    window.view_info_box = function () {
        poi_open = true;
        var info_box_html = '<div class="bootstrap-iso info_box_content">'+info_box+'</div>';
        $.fancybox.open({
            src  : info_box_html,
            type : 'html',
            touch: false
        });
    }

    window.view_custom_box = function () {
        poi_open = true;
        if(custom_box.includes('iframe')) {
            var style = "padding:0 !important;width:90%;height:90%;";
        } else {
            var style = '';
        }
        var custom_box_html = '<div style="'+style+'" class="custom_box_content">' + custom_box + '</div>';
        $.fancybox.open({
            src  : custom_box_html,
            type : 'html',
            touch: false,
            smallBtn: false,
            clickOutside: false
        });
    }

    window.view_form = function() {
        poi_open = true;
        var html_content = '';
        var content = form_content;
        try {
            var title = content[0].title;
            var button = content[0].button;
            var response = content[0].response;
            var send_email = content[0].send_email;
            if(send_email) {
                var email = content[0].email;
            } else {
                var email = '';
            }
            if(button=='') button = 'SUBMIT';
            var description = content[0].description;
            html_content += '<div><form method="post" action="#" class="form_main" style="text-align: center;">'
            if(title!='') {
                html_content += '<h2 style="margin-bottom: 5px">'+title.toUpperCase()+'</h2>';
            }
            if(description!='') {
                html_content += '<p>'+description+'</p><br>';
            }
            for(var i=1;i<=10;i++) {
                if(!('type' in content[i])) content[i]['type']='text';
                if(content[i]['enabled']) {
                    if(content[i]['required']) {
                        var required_tag = 'required';
                        var required_label = '*';
                    } else {
                        var required_tag = '';
                        var required_label = '';
                    }
                    switch(content[i]['type']) {
                        case 'select':
                            var labels = content[i]['label'].split(';');
                            html_content += '<div class="select">\n' +
                                ' <select id="form_field_'+i+'" name="form_field_'+i+'" data-chosen="" onchange="this.dataset.chosen = this.value;" class="select-text" '+required_tag+'>\n' +
                                '   <option value="" selected></option>\n';
                            for(var k=1;k<labels.length;k++) {
                                html_content += '<option value="'+labels[k]+'">'+labels[k]+'</option>\n';
                            }
                            html_content += '</select>\n' +
                                ' <span class="select-highlight"></span>\n' +
                                ' <span class="select-bar"></span>\n' +
                                ' <label class="select-label">'+labels[0]+' '+required_label+'</label>\n' +
                                '</div>';
                            var label = labels[0];
                            break;
                        case 'checkbox':
                            html_content += '<div><label class="pure-material-checkbox">\n' +
                                '<input type="checkbox" '+required_tag+' id="form_field_'+i+'" name="form_field_'+i+'">\n' +
                                '<span>'+content[i]['label']+' '+required_label+'</span>\n' +
                                '</label></div>';
                            var label = content[i]['label'];
                            break;
                        default:
                            html_content += '<div class="input_material">\n' +
                                '      <input placeholder=" " '+required_tag+' id="form_field_'+i+'" name="form_field_'+i+'" type="'+content[i]['type']+'" /><span class="highlight"></span><span class="bar"></span>\n' +
                                '      <label>'+content[i]['label']+' '+required_label+'</label>\n' +
                                '    </div>';
                            var label = content[i]['label'];
                            break;
                    }
                    html_content += '<input type="hidden" name="form_label_'+i+'" value="'+label+'" />';
                    html_content += '<br>';
                }
            }
            html_content += '<input type="hidden" name="id_room" value="0" />';
            html_content += '<input type="hidden" name="title" value="'+title+'" />';
            html_content += '<input type="hidden" name="email" value="'+email+'" />';
            html_content += '<button class="button_material" type="submit" style="margin-top: 10px">'+button+'</button>';
            html_content += '</form></div>';
            form_lightbox = $.fancybox.open({
                src  : html_content,
                type : 'html',
                touch: false
            });
            $(".form_main").submit(function(e){
                var form_data = $(this).serialize();
                confirm_main_form(form_data,response);
                e.preventDefault();
            });
        } catch (e) {}
    }

    $(document).on('afterClose.fb', function( e, instance, slide ) {
        poi_open = false;
        if(!live_session_connected) {
            if(video_opened) {
                if(controls_status['song']) {
                    if(current_panorama_type=='video') {
                        unmute_audio(false,true);
                    } else {
                        unmute_audio(true,false);
                    }
                }
                video_opened = false;
            }
        } else {
            try {
                peer_conn.send({type:'close_content'});
            } catch (e) {}
        }
    });

    window.open_video = function (id) {
        video_opened = true;
        mute_audio(true,true);
        if(live_session_connected) {
            try {
                peer_conn.send({type:'view_video',id:id});
            } catch (e) {}
        }
    }

    function resize_poi_box(id) {
        var box_w = $('.box_poi_'+id)[0].getBoundingClientRect().width;
        var box_h = $('.box_poi_'+id)[0].getBoundingClientRect().height;
        var poi_w = $('.hotspot_'+id)[0].getBoundingClientRect().width;
        var poi_h = $('.hotspot_'+id)[0].getBoundingClientRect().height;
        var scale = parseFloat($('.box_poi_'+id).attr('data-scale'));
        var fix_top_pos = 10;
        var fix_bottom_pos = 25;
        var fix_left_right_pos = 15;
        var top_top_pos = -6;
        switch(scale) {
            case 0.5:
                fix_top_pos = 20;
                fix_bottom_pos = 40;
                fix_left_right_pos = 18.5;
                top_top_pos = 1.5;
                break;
            case 0.6:
                fix_top_pos = 18;
                fix_bottom_pos = 37;
                fix_left_right_pos = 18;
                top_top_pos = 0;
                break;
            case 0.7:
                fix_top_pos = 16;
                fix_bottom_pos = 34;
                fix_left_right_pos = 17.5;
                top_top_pos = -1.5;
                break;
            case 0.8:
                fix_top_pos = 14;
                fix_bottom_pos = 31;
                fix_left_right_pos = 17;
                top_top_pos = -3;
                break;
            case 0.9:
                fix_top_pos = 12;
                fix_bottom_pos = 28;
                fix_left_right_pos = 16.5;
                top_top_pos = -1.5;
                break;
            case 1:
                fix_top_pos = 10;
                fix_bottom_pos = 25;
                fix_left_right_pos = 15;
                top_top_pos = -6;
                break;
            case 1.1:
                fix_top_pos = 8;
                fix_bottom_pos = 22;
                fix_left_right_pos = 14.5;
                top_top_pos = -4.5;
                break;
            case 1.2:
                fix_top_pos = 6;
                fix_bottom_pos = 19;
                fix_left_right_pos = 14;
                top_top_pos = 13.5;
                top_top_pos = -9;
                break;
            case 1.3:
                fix_top_pos = 4;
                fix_bottom_pos = 16;
                fix_left_right_pos = 13.5;
                top_top_pos = -7.5;
                break;
            case 1.4:
                fix_top_pos = 2;
                fix_bottom_pos = 13;
                fix_left_right_pos = 13;
                top_top_pos = -11;
                break;
            case 1.5:
                fix_top_pos = 0;
                fix_bottom_pos = 10;
                fix_left_right_pos = 12.5;
                top_top_pos = -12.5;
                break;
            case 1.6:
                fix_top_pos = -2;
                fix_bottom_pos = 7;
                fix_left_right_pos = 12;
                top_top_pos = -14;
                break;
            case 1.7:
                fix_top_pos = -4;
                fix_bottom_pos = 4;
                fix_left_right_pos = 11.5;
                top_top_pos = -15.5;
                break;
            case 1.8:
                fix_top_pos = -6;
                fix_bottom_pos = 1;
                fix_left_right_pos = 11;
                top_top_pos = -17;
                break;
            case 1.9:
                fix_top_pos = -8;
                fix_bottom_pos = -2;
                fix_left_right_pos = 10.5;
                top_top_pos = -18.5;
                break;
            case 2:
                fix_top_pos = -10;
                fix_bottom_pos = -5;
                fix_left_right_pos = 10;
                top_top_pos = -20;
                break;
        }
        switch($('.box_poi_'+id).attr('data-box-pos')) {
            case 'right':
                var top_pos = (box_h/2)+(poi_h/2)+fix_top_pos;
                var left_pos = fix_left_right_pos+(poi_w/2);
                $('.box_poi_'+id+' .box-arrow-border').css({'top':'calc(50% - 5px)','left':'-14px','transform':'rotate(-90deg)'});
                $('.box_poi_'+id).css({'opacity':1,'pointer-events':'initial','top':top_pos+'px','left':left_pos+'px'});
                break;
            case 'left':
                var left_pos = (fix_left_right_pos+(poi_w/2)+box_w)*-1;
                var left_pos_arrow = box_w-6;
                var top_pos = (box_h/2)+(poi_h/2)+fix_top_pos;
                $('.box_poi_'+id+' .box-arrow-border').css({'top':'calc(50% - 5px)','left':left_pos_arrow+'px','transform':'rotate(90deg)'});
                $('.box_poi_'+id).css({'opacity':1,'pointer-events':'initial','top':top_pos+'px','left':left_pos+'px'});
                break;
            case 'bottom':
                var top_pos = fix_bottom_pos+box_h+poi_h;
                var left_pos = (box_w/2)*-1;
                $('.box_poi_'+id+' .box-arrow-border').css({'top':'-9px','left':'calc(50% - 10px)','transform':'rotate(0deg)'});
                $('.box_poi_'+id).css({'opacity':1,'pointer-events':'initial','top':top_pos+'px','left':left_pos+'px'});
                break;
            case 'top':
                var top_pos_arrow = box_h-1;
                var left_pos = (box_w/2)*-1;
                $('.box_poi_'+id+' .box-arrow-border').css({'top':top_pos_arrow+'px','left':'calc(50% - 10px)','transform':'rotate(180deg)'});
                $('.box_poi_'+id).css({'opacity':1,'pointer-events':'initial','top':top_top_pos+'px','left':left_pos+'px'});
                break;
        }
    }

    function resize_pois_box() {
        $('.box_poi').each(function () {
            if($(this).css('opacity')==1) {
                var id = $(this).attr('data-id');
                resize_poi_box(id);
            }
        });
    }

    function view_content(hotSpotDiv, args) {
        if(args.view_type==0) {
            set_poi_statistics('',args.id);
        }
        if((args.content=='') || (args.content=='<p><br></p>')) return;
        poi_open = true;
        if(live_session_connected) {
            try {
                peer_conn.send({type:'view_content',args:args});
            } catch (e) {}
        }
        if(args.title==null) args.title = '';
        if(args.description==null) args.description = '';
        if((args.title!='') && (args.description!='')) {
            var caption = '<b>'+args.title+'</b><br><i>'+args.description+'</i>';
        } else if((args.title!='') && (args.description=='')) {
            var caption = '<b>'+args.title+'</b>';
        } else if((args.title=='') && (args.description!='')) {
            var caption = '<i>'+args.description+'</i>';
        } else {
            var caption = '';
        }
        var view_type = parseInt(args.view_type);
        switch(args.type) {
            case 'image':
                switch(view_type) {
                    case 0:
                        $.fancybox.open({
                            src  : args.content,
                            type : 'image',
                            caption : caption,
                        });
                        break;
                    case 1:
                    case 2:
                        view_poi_box(args.id,false);
                        break;
                }
                break;
            case 'gallery':
                switch(view_type) {
                    case 0:
                        try {
                            $('#gallery_container').nanogallery2('destroy');
                        } catch (e) {}
                        poi_gallery = $("#gallery_container").nanogallery2({
                            imageTransition: 'slideAppear',
                            thumbnailHeight:  150,
                            thumbnailWidth:   150,
                            items: args.content,
                            allowHTMLinData: true,
                            viewerHideToolsDelay: 30000,
                            viewerToolbar:    {
                                display:    true,
                                autoMinimize: false,
                                standard:   'label'
                            },
                            viewerTools:    {
                                topLeft:    'pageCounter, playPauseButton',
                                topRight:   'zoomButton, closeButton'
                            }
                        });
                        poi_gallery.nanogallery2('displayItem', '-1/1');
                        $('.nGY2Viewer .closeButton').on('click',function () {
                            if(live_session_connected) {
                                try {
                                    peer_conn.send({type:'close_gallery'});
                                } catch (e) {}
                            }
                            restart_autorotate();
                        });
                        break;
                    case 1:
                    case 2:
                        view_poi_box(args.id,false);
                        break;
                }
                break;
            case 'link':
            case 'video':
                switch(view_type) {
                    case 1:
                    case 2:
                        view_poi_box(args.id,false);
                        break;
                }
                break;
            case 'html':
            case 'html_sc':
                switch(view_type) {
                    case 0:
                        $.fancybox.open({
                            src  : args.content,
                            type : 'html',
                            caption : caption,
                        });
                        break;
                    case 1:
                    case 2:
                        view_poi_box(args.id,false);
                        break;
                }
                break;
            case 'object360':
                switch(view_type) {
                    case 0:
                        var html = '<div class="poi_object360_content"><div' +
                            '   class="cloudimage-360"' +
                            '   data-folder="objects360/"' +
                            '   data-filename="'+args.content.name_images+'?v='+time_version+'"' +
                            '   data-amount="'+args.content.count_images+'"' +
                            '   data-bottom-circle data-hide-360-logo data-spin-reverse' +
                            '></div>';
                        $.fancybox.open({
                            src  : html,
                            type : 'html',
                            touch: false,
                            smallBtn: false,
                            clickOutside: false,
                            caption : caption,
                            afterShow : function() {
                                window.CI360.init();
                            }
                        });
                        break;
                    case 1:
                    case 2:
                        view_poi_box(args.id,false);
                        break;
                }
                break;
            case 'product':
                switch(view_type) {
                    case 0:
                        var html = get_product_html(args.product,args.product_images,args.id);
                        product_lightbox = $.fancybox.open({
                            src  : html,
                            type : 'html',
                            touch: false,
                            smallBtn: false,
                            clickOutside: false,
                            caption : caption,
                            afterShow : function() {
                                $("#product_"+args.id+" .carousel").swipe({
                                    swipe: function (event, direction, distance, duration, fingerCount, fingerData) {
                                        if (direction == 'left') $(this).carousel('next');
                                        if (direction == 'right') $(this).carousel('prev');
                                    },
                                    allowPageScroll: "vertical"
                                });
                            }
                        });
                        break;
                    case 1:
                    case 2:
                        view_poi_box(args.id,false);
                        break;
                }
                break;
            case 'object3d':
                var array_files = args.content.split(",");
                var glb_file = '', usdz_file = '';
                jQuery.each(array_files, function(index_s, file_s) {
                    if(file_s.split('.').pop().toLowerCase()=='glb') {
                        glb_file = file_s;
                    }
                    if(file_s.split('.').pop().toLowerCase()=='gltf') {
                        glb_file = file_s;
                    }
                    if(file_s.split('.').pop().toLowerCase()=='usdz') {
                        usdz_file = file_s;
                    }
                });
                switch(view_type) {
                    case 0:
                        switch(args.params) {
                            case 'floor':
                            case 'wall':
                                if(usdz_file!='') {
                                    var html = '<div class="poi_object3d_content"><model-viewer src="'+glb_file+'" ios-src="'+usdz_file+'" alt="" ar ar-modes="webxr scene-viewer quick-look" ar-placement="'+args.params+'" environment-image="neutral" shadow-intensity="1" auto-rotate camera-controls></model-viewer></div>';
                                } else {
                                    var html = '<div class="poi_object3d_content"><model-viewer src="'+glb_file+'" alt="" ar ar-modes="webxr scene-viewer quick-look" ar-placement="'+args.params+'" environment-image="neutral" shadow-intensity="1" auto-rotate camera-controls></model-viewer></div>';
                                }
                                break;
                            default:
                                var html = '<div class="poi_object3d_content"><model-viewer src="'+glb_file+'" alt="" environment-image="neutral" shadow-intensity="1" auto-rotate camera-controls></model-viewer></div>';
                                break;
                        }
                        $.fancybox.open({
                            src  : html,
                            type : 'html',
                            touch: false,
                            smallBtn: false,
                            clickOutside: false,
                            caption : caption,
                            afterShow : function() {}
                        });
                        break;
                    case 1:
                    case 2:
                        view_poi_box(args.id,false);
                        break;
                }
                break;
            case 'lottie':
                switch(view_type) {
                    case 0:
                        var html = '<div id="poi_lottie_'+args.id+'" class="poi_lottie_content"></div>';
                        $.fancybox.open({
                            src  : html,
                            type : 'html',
                            touch: false,
                            smallBtn: false,
                            clickOutside: false,
                            afterShow : function() {
                                bodymovin.loadAnimation({
                                    container: document.getElementById('poi_lottie_'+args.id),
                                    renderer: 'svg',
                                    loop: true,
                                    autoplay: true,
                                    path: args.content,
                                    rendererSettings: {
                                        progressiveLoad: true,
                                    }
                                });
                            }
                        });
                        break;
                    case 1:
                    case 2:
                        view_poi_box(args.id,false);
                        break;
                }
                break;
            case 'google_maps':
                switch(view_type) {
                    case 0:
                        var gm_map = args.content.split('|')[0];
                        var gm_street = args.content.split('|')[1];
                        var content = '<div class="poi_google_maps_content">'+gm_map+' '+gm_street+'</div>';
                        $.fancybox.open({
                            src  : content,
                            type : 'html',
                            touch: false,
                            smallBtn: false,
                            caption : caption,
                            afterShow : function() {
                                if((gm_map=='') || (gm_street=='')) {
                                    $('.poi_google_maps_content iframe:nth-child(1)').addClass('poi_google_maps_full_width');
                                }
                            }
                        });
                        break;
                    case 1:
                    case 2:
                        view_poi_box(args.id,false);
                        break;
                }
                break;
            case 'audio':
                if($('.audio_poi_'+args.id).css('opacity')=='0') {
                    $('.audio_poi_'+args.id).css('opacity',1);
                    $('.audio_poi_'+args.id).css('pointer-events','initial');
                    $('.audio_poi_'+args.id+' audio')[0].play();
                    $('.audio_poi_'+args.id+' audio')[0].addEventListener("playing", function(){
                        if(controls_status['song']) {
                            set_audio_volume(args.song_bg_volume);
                        }
                    });
                    $('.audio_poi_'+args.id+' audio')[0].addEventListener("ended", function(){
                        if(!live_session_connected) {
                            if(controls_status['song']) {
                                set_audio_volume(1.0);
                            }
                        }
                    });
                } else {
                    $('.audio_poi_'+args.id).css('opacity',0);
                    $('.audio_poi_'+args.id).css('pointer-events','none');
                    $('.audio_poi_'+args.id+' audio')[0].pause();
                    if(!live_session_connected) {
                        if(controls_status['song']) {
                            set_audio_volume(1.0);
                        }
                    }
                }
                break;
            case 'video360':
                switch(view_type) {
                    case 0:
                        var html_content = '';
                        html_content += '<div style="padding:32px;width:calc(100% - 20px);height:calc(100% - 20px);">' +
                            '<video style="width:100%;height:100%" crossorigin="anonymous" preload controls playsinline webkit-playsinline class="video-js vjs-default-skin vjs-big-play-centered" id="video360_'+args.id+'">' +
                            '<source src="'+args.content+'" type="video/mp4">' +
                            '</video>' +
                            '</div>';
                        $.fancybox.open({
                            src  : html_content,
                            type : 'html',
                            caption : caption,
                            touch : false,
                            beforeShow: function () {
                                try {
                                    video360_poi[args.id].dispose();
                                } catch (e) {}
                            },
                            afterShow: function () {
                                video360_poi[args.id] = videojs('video360_'+args.id,{
                                    autoplay: false,
                                    controlBar: {
                                        pictureInPictureToggle: false
                                    }
                                });
                                video360_poi[args.id].vr({projection: '360'});
                                video360_poi[args.id].pause();

                                video360_poi[args.id].on('play',function () {
                                    mute_audio(true,true);
                                });
                                video360_poi[args.id].on('end',function () {
                                    if(!live_session_connected) {
                                        if(controls_status['song']) {
                                            unmute_audio(true,true);
                                        }
                                    }
                                });
                            },
                            beforeClose: function() {
                                video360_poi[args.id].dispose();
                                if(!live_session_connected) {
                                    if(controls_status['song']) {
                                        unmute_audio(true,true);
                                    }
                                }
                            }
                        });
                        break;
                    case 1:
                    case 2:
                        view_poi_box(args.id,false);
                        break;
                }
                break;
            case 'form':
                switch(view_type) {
                    case 0:
                        try {
                            var content = JSON.parse(args.content);
                            var response = content[0].response;
                            var html_content = '<div>'+parse_form_content(content,args.id_room)+'</div>';
                            form_lightbox = $.fancybox.open({
                                src  : html_content,
                                type : 'html',
                                touch: false
                            });
                            $(".form_poi").submit(function(e){
                                var form_data = $(this).serialize();
                                confirm_poi_form(form_data,response,null);
                                e.preventDefault();
                            });
                        } catch (e) {}
                        break;
                    case 1:
                    case 2:
                        view_poi_box(args.id,false);
                        var content = JSON.parse(args.content);
                        var response = content[0].response;
                        $(".form_poi").submit(function(e){
                            var form_data = $(this).serialize();
                            confirm_poi_form(form_data,response,args.id);
                            e.preventDefault();
                        });
                        break;
                }
                break;
        }
        $(document).on('afterClose.fb', function( e, instance, slide ) {
            clearTimeout(interval_auto_close_poi);
            restart_autorotate();
        });
        switch(view_type) {
            case 0:
                if(args.auto_close!=0) {
                    clearTimeout(interval_auto_close_poi);
                    interval_auto_close_poi = setTimeout(function() {
                        $.fancybox.close(true);
                        restart_autorotate();
                    },args.auto_close);
                }
                break;
            case 1:
            case 2:
                if(args.auto_close!=0) {
                    clearTimeout(interval_auto_close_poi);
                    interval_auto_close_poi = setTimeout(function() {
                        close_poi_box(args.id);
                    },args.auto_close);
                }
                break;
        }
    }

    function restart_autorotate() {
        if(autorotate_speed>0 && controls_status['auto_rotate']) {
            if(current_panorama_type=='image' || is_iOS() || current_panorama_type=='hls' || current_panorama_type=='lottie') {
                try {
                    pano_viewer.setAutoRotateInactivityDelay(autorotate_inactivity);
                    setTimeout(function () {
                        pano_viewer.startAutoRotate(autorotate_speed);
                    },autorotate_inactivity);
                } catch (e) {}
            } else {
                try {
                    video_viewer.pnlmViewer.setAutoRotateInactivityDelay(autorotate_inactivity);
                    setTimeout(function () {
                        video_viewer.pnlmViewer.startAutoRotate(autorotate_speed);
                    },autorotate_inactivity);
                } catch (e) {}
            }
        }
    }

    function close_poi_box(id) {
        $('.tooltip_poi_'+id).removeClass('hidden_pb');
        $('.box_poi_'+id).css({'opacity':0,'pointer-events':'none'});
        window.poi_box_open = false;
        restart_autorotate();
    }

    function view_poi_box(id,keep_open) {
        if(current_panorama_type=='image' || is_iOS() || current_panorama_type=='hls' || current_panorama_type=='lottie') {
            pano_viewer.resize();
        } else {
            video_viewer.pnlmViewer.resize();
        }
        if($('.box_poi_'+id).css('opacity')==1 && keep_open==false) {
            $('.tooltip_poi_'+id).removeClass('hidden_pb');
            $('.box_poi_'+id).css({'opacity':0,'pointer-events':'none'});
            window.poi_box_open = false;
            clearTimeout(interval_auto_close_poi);
        } else {
            if($('.box_poi_'+id).css('opacity')==0) {
                set_poi_statistics('',id);
            }
            $('.tooltip_poi_'+id).addClass('hidden_pb');
            resize_poi_box(id);
            window.poi_box_open = true;
        }
        setTimeout(function() {
            $(document).trigger('resize');
        },200);
    }

    function get_product_html(product,product_images,id) {
        var html = '<div id="product_'+id+'" class="product-wrapper bootstrap-iso">\n' +
            '<div class="container-fluid">\n' +
            '<div class="row">\n';
        var col = 12;
        var text_center = 'text-center';
        var first_image = '';
        var margin_auto = 'm-auto';
        if(product_images.length>0) {
            if(product_images.length==1) {
                var image_d = 'd-none';
            } else {
                var image_d = '';
            }
            col = 6;
            text_center = '';
            first_image = product_images[0].src;
            margin_auto = '';
            html += '<div class="col-12 col-sm-12 col-md-6 product-slider">\n' +
                '<div id="carouselIndicators_'+id+'" class="carousel slide" data-ride="carousel">\n' +
                '<i onclick="open_product_gallery('+id+');" class="zoom_product fas fa-search-plus"></i>\n' +
                '<ol class="carousel-indicators '+image_d+'">\n';
            jQuery.each(product_images, function(index, product_image) {
                var image = product_image.src_thumb;
                var active = '';
                if(index==0) active='active';
                html += '<li data-target="#carouselIndicators_'+id+'" data-slide-to="'+index+'" class="'+active+'"><img src="'+image+'" class="d-block w-100"></li>\n';
            });
            html += '</ol>\n' +
                '<div class="carousel-inner">\n';
            jQuery.each(product_images, function(index, product_image) {
                var image = product_image.src;
                var active = '';
                if(index==0) active='active';
                html +='<div class="carousel-item '+active+'">\n' +
                    '<img draggable="false" src="'+image+'" class="d-block">\n' +
                    '</div>\n';
            });
            html += '<a class="carousel-control-prev '+image_d+'" href="#carouselIndicators_'+id+'" role="button" data-slide="prev">\n' +
                '   <span class="carousel-control-prev-icon" aria-hidden="true"></span>\n' +
                '   <span class="sr-only">Previous</span>\n' +
                '</a>\n' +
                '<a class="carousel-control-next '+image_d+'" href="#carouselIndicators_'+id+'" role="button" data-slide="next">\n' +
                '   <span class="carousel-control-next-icon" aria-hidden="true"></span>\n' +
                '   <span class="sr-only">Next</span>\n' +
                '</a>\n' +
                '</div>\n</div>\n</div>\n';
        }
        html += '<div class="col-12 col-sm-12 col-md-'+col+' '+text_center+' product-content">\n' +
            '<div class="product-title">\n' +
            '   <span>'+product.name+'</span>\n' +
            '</div>\n' +
            '<div class="product-price">\n' + product.price_html + '</div>\n' +
            '<div class="product-description">\n' + product.description + '</div>\n';
        switch(product.purchase_type) {
            case 'link':
                html += '<div class="btn-wrapper">\n' +
                    '<a class="'+margin_auto+'" href="'+product.link+'" target="_blank"><span class="btn"><i class="fas fa-shopping-cart"></i>&nbsp;&nbsp;'+window.viewer_labels.buy+'</span></a>\n' +
                    '</div>\n';
                break;
            case 'cart':
                if(product.price>0) {
                    html += '<div class="btn-wrapper">\n' +
                        '<button onclick="close_product(id);" class="snipcart-add-item btn '+margin_auto+'" data-item-has-taxes-included="true" data-item-url="'+window.base_url+'services/product_json.php?id='+product.id+'" data-item-id="'+product.id+'" data-item-price="'+product.price+'" data-item-description="'+product.description.replace( /(<([^>]+)>)/ig, '')+'" data-item-image="'+first_image+'" data-item-name="'+product.name+'"><i class="fas fa-shopping-cart"></i>&nbsp;&nbsp;'+window.viewer_labels.add_to_cart+'</button>\n' +
                        '</div>\n';
                }
                break;
        }
        html += '</div>\n</div>\n</div>\n</div>\n';
        return html;
    }

    window.close_product = function(id) {
        close_all_poi_box();
        try {
            product_lightbox.close();
        } catch (e) {}
        restart_autorotate();
    }

    function parse_form_content(content,id_room) {
        var html_content = '';
        var title = content[0].title;
        var button = content[0].button;
        if(button=='') button = 'SUBMIT';
        var description = content[0].description;
        var send_email = content[0].send_email;
        if(send_email) {
            var email = content[0].email;
        } else {
            var email = '';
        }
        html_content += '<form method="post" action="#" class="form_poi" style="text-align: center;">'
        if(title!='') {
            html_content += '<h2 style="margin-bottom: 5px">'+title.toUpperCase()+'</h2>';
        }
        if(description!='') {
            html_content += '<p>'+description+'</p><br>';
        }
        for(var i=1;i<=10;i++) {
            if(!('type' in content[i])) content[i]['type']='text';
            if(content[i]['enabled']) {
                if(content[i]['required']) {
                    var required_tag = 'required';
                    var required_label = '*';
                } else {
                    var required_tag = '';
                    var required_label = '';
                }
                switch(content[i]['type']) {
                    case 'select':
                        var labels = content[i]['label'].split(';');
                        html_content += '<div class="select">\n' +
                            ' <select id="form_field_'+i+'" name="form_field_'+i+'" data-chosen="" onchange="this.dataset.chosen = this.value;" class="select-text" '+required_tag+'>\n' +
                            '   <option value="" selected></option>\n';
                        for(var k=1;k<labels.length;k++) {
                            html_content += '<option value="'+labels[k]+'">'+labels[k]+'</option>\n';
                        }
                        html_content += '</select>\n' +
                            ' <span class="select-highlight"></span>\n' +
                            ' <span class="select-bar"></span>\n' +
                            ' <label class="select-label">'+labels[0]+' '+required_label+'</label>\n' +
                            '</div>';
                        break;
                        var label = labels[0];
                    case 'checkbox':
                        html_content += '<div><label class="pure-material-checkbox">\n' +
                            '<input type="checkbox" '+required_tag+' id="form_field_'+i+'" name="form_field_'+i+'">\n' +
                            '<span>'+content[i]['label']+' '+required_label+'</span>\n' +
                            '</label></div>';
                        break;
                        var label = content[i]['label'];
                    default:
                        html_content += '<div class="input_material">\n' +
                            '      <input placeholder=" " '+required_tag+' id="form_field_'+i+'" name="form_field_'+i+'" type="'+content[i]['type']+'" /><span class="highlight"></span><span class="bar"></span>\n' +
                            '      <label>'+content[i]['label']+' '+required_label+'</label>\n' +
                            '    </div>';
                        var label = content[i]['label'];
                        break;
                }
                html_content += '<input type="hidden" name="form_label_'+i+'" value="'+label+'" />';
                html_content += '<br>';
            }
        }
        html_content += '<input type="hidden" name="id_room" value="'+id_room+'" />';
        html_content += '<input type="hidden" name="title" value="'+title+'" />';
        html_content += '<input type="hidden" name="email" value="'+email+'" />';
        html_content += '<button class="button_material" type="submit" style="margin-top: 10px">'+button+'</button>';
        html_content += '</form>';
        return html_content;
    }

    window.confirm_poi_form = function(form_data,response,id) {
        var btn_c = $('.form_poi button').html();
        $('.form_poi button').addClass("disabled");
        $('.form_poi button').html("<i class='fas fa-circle-notch fa-spin'></i>");
        $.ajax({
            url: "ajax/store_form_data.php",
            type: "POST",
            data: {
                id_virtualtour: window.id_virtualtour,
                form_data: form_data
            },
            async: true,
            success: function (json) {
                var rsp = JSON.parse(json);
                if(rsp.status=='ok') {
                    $('.form_poi button').addClass("button_success_form");
                    $('.form_poi button').html("<i class='fas fa-check'></i>");
                    setTimeout(function () {
                        $('.form_poi button').removeClass("disabled");
                        $('.form_poi button').removeClass("button_success_form");
                        if(id==null) {
                            form_lightbox.close();
                        } else {
                            view_poi_box(id,false);
                        }
                        if(response!='') {
                            $.alert({
                                theme: 'modern',
                                useBootstrap: false,
                                boxWidth: '300px',
                                type: 'green',
                                title: '',
                                content: response,
                            });
                        }
                    },1000);
                    if(rsp.email!='') {
                        $.ajax({
                            url: "../backend/ajax/send_email.php",
                            type: "POST",
                            data: {
                                type: 'form',
                                email: rsp.email,
                                form_data: form_data
                            },
                            timeout: 15000,
                            async: true,
                            success: function (json) {

                            }
                        });
                    }
                } else {
                    $('.form_poi button').html(btn_c);
                    $('.form_poi button').removeClass("disabled");
                }
            }
        });
    }

    window.confirm_main_form = function(form_data,response) {
        var btn_c = $('.form_main button').html();
        $('.form_main button').addClass("disabled");
        $('.form_main button').html("<i class='fas fa-circle-notch fa-spin'></i>");
        $.ajax({
            url: "ajax/store_form_data.php",
            type: "POST",
            data: {
                id_virtualtour: window.id_virtualtour,
                form_data: form_data
            },
            async: true,
            success: function (json) {
                var rsp = JSON.parse(json);
                if(rsp.status=='ok') {
                    $('.form_main button').addClass("button_success_form");
                    $('.form_main button').html("<i class='fas fa-check'></i>");
                    setTimeout(function () {
                        $('.form_main button').removeClass("disabled");
                        $('.form_main button').removeClass("button_success_form");
                        form_lightbox.close();
                        if(response!='') {
                            $.alert({
                                theme: 'modern',
                                useBootstrap: false,
                                boxWidth: '300px',
                                type: 'green',
                                title: '',
                                content: response,
                            });
                        }
                    },1000);
                    if(rsp.email!='') {
                        $.ajax({
                            url: "../backend/ajax/send_email.php",
                            type: "POST",
                            data: {
                                type: 'form',
                                email: rsp.email,
                                form_data: form_data
                            },
                            timeout: 15000,
                            async: true,
                            success: function (json) {

                            }
                        });
                    }
                } else {
                    $('.form_main button').html(btn_c);
                    $('.form_main button').removeClass("disabled");
                }
            }
        });
    }

    window.set_poi_statistics = function(hotSpotDiv,id) {
        close_all_poi_box();
        if(current_panorama_type=='image' || is_iOS() || current_panorama_type=='hls' || current_panorama_type=='lottie') {
            pano_viewer.stopAutoRotate();
        } else {
            video_viewer.pnlmViewer.stopAutoRotate();
        }
        if(window.export_mode==0 && window.preview==0) {
            $.ajax({
                url: "ajax/set_statistics.php",
                type: "POST",
                data: {
                    type: 'poi',
                    id: id,
                    ip_visitor: window.ip_visitor
                },
                async: true
            });
        }
    }

    function hotspot_nadir(hotSpotDiv, args) {
        if(vr_enabled) {
            hotSpotDiv.classList.add('nadir-hotspot-small_vr');
        }
        hotSpotDiv.classList.add('noselect');
        hotSpotDiv.style = "background-image:url(content/"+args+");background-size:cover;";
    }

    function getYoutubeId(url) {
        const regExp = /^.*(youtu.be\/|v\/|u\/\w\/|embed\/|watch\?v=|&v=)([^#&?]*).*/;
        const match = url.match(regExp);
        return (match && match[2].length === 11) ? match[2] : null;
    }

    function getVimeoId(url) {
        var m = url.match(/^.+vimeo.com\/(.*\/)?([^#\?]*)/);
        return m ? m[2] || m[1] : null;
    }

    var start_drag, end_drag, drag_p;

    function hotspot_visitor(hotSpotDiv, args) {
        hotSpotDiv.setAttribute('data-id', args.id);
        hotSpotDiv.setAttribute('draggable',false);
        hotSpotDiv.classList.add('poi_not_selectable');
        hotSpotDiv.classList.add('noselect');
        var div_wrapper = document.createElement('div');
        div_wrapper.classList.add('div_visitor_wrapper');
        div_wrapper.style.backgroundColor = args.color;
        var i = document.createElement('i');
        i.className = 'fas fa-user';
        div_wrapper.appendChild(i);
        hotSpotDiv.appendChild(div_wrapper);
    }

    function hotspot_content(hotSpotDiv, args) {
        if(args.type=='form' && window.export_mode==1) {
            return;
        }
        hotSpotDiv.setAttribute('draggable',false);
        hotSpotDiv.classList.add('noselect');
        hotSpotDiv.classList.add('hotspot_'+args.id);
        hotSpotDiv.style.zIndex = args.zIndex;
        if(args.css_class!='') {
            var array_css_class = args.css_class.split(" ");
            jQuery.each(array_css_class, function(index_c, css_class) {
                hotSpotDiv.classList.add(css_class);
            });
        }
        if(args.type=='' || args.type==null) {
            hotSpotDiv.classList.add('poi_not_selectable');
        }
        hotSpotDiv.setAttribute('data-id', args.id);
        if(args.title==null) args.title = '';
        if(args.description==null) args.description = '';
        if((args.title!='') && (args.description!='')) {
            var caption = '<b>'+args.title+'</b><br><i>'+args.description+'</i>';
        } else if((args.title!='') && (args.description=='')) {
            var caption = '<b>'+args.title+'</b>';
        } else if((args.title=='') && (args.description!='')) {
            var caption = '<i>'+args.description+'</i>';
        } else {
            var caption = '';
        }
        if(args.view_type==2) {
            hotSpotDiv.addEventListener("mouseover", function (e) {
                if(controls_status['icons']==true && !window.is_mobile) {
                    var id = e.target.getAttribute('data-id');
                    if(id !== null) {
                        view_poi_box(id,true);
                    }
                }
            });
            hotSpotDiv.addEventListener("mouseenter", function (e) {
                if(controls_status['icons']==true && !window.is_mobile) {
                    var id = e.target.getAttribute('data-id');
                    if(id !== null) {
                        close_all_poi_box();
                        view_poi_box(id,true);
                    }
                }
            });
        } else {
            switch(args.tooltip_type) {
                case 'text':
                    if(args.tooltip_text !== null && args.tooltip_text !== '' && args.tooltip_text !== '<p><br></p>') {
                        var tooltip = document.createElement('div');
                        tooltip.classList.add('tooltip_poi_'+args.id);
                        tooltip.classList.add('tooltip_text');
                        tooltip.innerHTML = args.tooltip_text;
                        hotSpotDiv.parentNode.appendChild(tooltip);
                        hotSpotDiv.addEventListener("mouseover", function (e) {
                            if(controls_status['icons']==true && !window.is_mobile) {
                                var id = e.target.getAttribute('data-id');
                                if(id !== null) {
                                    $('.tooltip_poi_'+id).css('opacity',1);
                                }
                            }
                        });
                        hotSpotDiv.addEventListener("mouseenter", function (e) {
                            if(controls_status['icons']==true && !window.is_mobile) {
                                var id = e.target.getAttribute('data-id');
                                if(id !== null) {
                                    $('.tooltip_poi_'+id).css('opacity',1);
                                }
                            }
                        });
                        hotSpotDiv.addEventListener("mouseleave", function (e) {
                            if(controls_status['icons']==true && !window.is_mobile) {
                                var id = e.target.getAttribute('data-id');
                                if(id !== null) {
                                    $('.tooltip_poi_'+id).css('opacity',0);
                                }
                            }
                        });
                    }
                    break;
            }
        }
        if(args.type=='audio') {
            var html_audio_poi = '<audio playsinline controls controlsList="nodownload">\n' +
                '<source src="'+args.content+'" type="audio/mpeg">\n' +
                'Your browser does not support the audio element.\n' +
                '</audio>';
            var audio_poi_player = document.createElement('div');
            audio_poi_player.classList.add("audio_poi_"+args.id);
            audio_poi_player.classList.add('audio_poi');
            audio_poi_player.innerHTML = html_audio_poi;
            hotSpotDiv.parentNode.appendChild(audio_poi_player);
        }
        var style_t = parseInt(args.style);
        var view_type = parseInt(args.view_type);
        var div_wrapper = document.createElement('div');
        div_wrapper.classList.add('div_poi_wrapper');
        if(args.animation!='none') {
            div_wrapper.classList.add('animate__animated');
            div_wrapper.classList.add('animate__slow');
            div_wrapper.classList.add('animate__'+args.animation);
            div_wrapper.classList.add('animate__infinite');
        }
        var lottie = false;
        switch (style_t) {
            case 0:
                if(args.animation=='none') {
                    div_wrapper.classList.add('pulse_icon');
                }
                div_wrapper.style.background = args.background;
                var i = document.createElement('i');
                i.className = args.icon;
                i.style = "margin: 0 auto;vertical-align:middle;font-size:24px;color:"+args.color;
                switch (args.type) {
                    case 'download':
                        var a = document.createElement('a');
                        a.style = 'text-decoration:none;';
                        a.addEventListener('mousedown', function(e) {
                            start_drag = new Date().getTime();
                            drag_p = false;
                        }, false);
                        a.addEventListener('mousemove', function(e) {
                            end_drag = new Date().getTime();
                            drag_p = true;
                        }, false);
                        a.addEventListener('mouseup', function(e) {
                            var diff_drag = end_drag - start_drag;
                            if(drag_p == false || diff_drag < 200) {
                                a.href = args.content;
                                a.download = '';
                            } else {
                                a.removeAttribute('href');
                                a.removeAttribute('download');
                            }
                        }, false);
                        a.appendChild(i);
                        div_wrapper.appendChild(a);
                        break;
                    case 'link':
                        var a = document.createElement('a');
                        a.style = 'text-decoration:none;';
                        a.title = args.title;
                        a.id = "link_"+args.id;
                        if(view_type==0) {
                            a.addEventListener('mousedown', function(e) {
                                start_drag = new Date().getTime();
                                drag_p = false;
                            }, false);
                            a.addEventListener('mousemove', function(e) {
                                end_drag = new Date().getTime();
                                drag_p = true;
                            }, false);
                            a.addEventListener('mouseup', function(e) {
                                var diff_drag = end_drag - start_drag;
                                if(drag_p == false || diff_drag < 200) {
                                    a.href = args.content;
                                    a.setAttribute('data-fancybox','');
                                    a.setAttribute('data-caption',caption);
                                    a.setAttribute('data-type','iframe');
                                    a.setAttribute('data-options','{"type" : "iframe", "iframe" : {"preload" : false}}');
                                } else {
                                    a.href = '#';
                                    a.removeAttribute('data-fancybox');
                                    a.removeAttribute('data-caption');
                                    a.removeAttribute('data-type');
                                    a.removeAttribute('data-options');
                                }
                            }, false);
                            a.addEventListener('simulate_click', function (e) {
                                a.href = args.content;
                                a.setAttribute('data-fancybox','');
                                a.setAttribute('data-caption',caption);
                                a.setAttribute('data-type','iframe');
                                a.setAttribute('data-options','{"type" : "iframe", "iframe" : {"preload" : false}}');
                                a.click();
                            }, false);
                        }
                        a.appendChild(i);
                        div_wrapper.appendChild(a);
                        break;
                    case 'link_ext':
                        var a = document.createElement('a');
                        a.style = 'text-decoration:none;';
                        a.addEventListener('mousedown', function(e) {
                            start_drag = new Date().getTime();
                            drag_p = false;
                        }, false);
                        a.addEventListener('mousemove', function(e) {
                            end_drag = new Date().getTime();
                            drag_p = true;
                        }, false);
                        a.addEventListener('mouseup', function(e) {
                            var diff_drag = end_drag - start_drag;
                            if(drag_p == false || diff_drag < 200) {
                                a.href = args.content;
                                a.target = args.target;
                            } else {
                                a.removeAttribute('href');
                                a.removeAttribute('target');
                            }
                        }, false);
                        a.appendChild(i);
                        div_wrapper.appendChild(a);
                        break;
                    case 'video':
                        var a = document.createElement('a');
                        a.style = 'text-decoration:none;';
                        a.title = args.title;
                        if(view_type==0) {
                            a.id = "video_"+args.id;
                            a.addEventListener('mousedown', function(e) {
                                start_drag = new Date().getTime();
                                drag_p = false;
                            }, false);
                            a.addEventListener('mousemove', function(e) {
                                end_drag = new Date().getTime();
                                drag_p = true;
                            }, false);
                            a.addEventListener('mouseup', function(e) {
                                var diff_drag = end_drag - start_drag;
                                if(drag_p == false || diff_drag < 200) {
                                    a.href = args.content;
                                    a.onclick = function() {
                                        open_video("video_"+args.id);
                                    };
                                    a.setAttribute('data-fancybox', '');
                                    a.setAttribute('data-caption', caption);
                                } else {
                                    a.href = '#';
                                    a.onclick = function() {};
                                    a.removeAttribute('data-fancybox');
                                    a.removeAttribute('data-caption');
                                    e.preventDefault();
                                    e.stopImmediatePropagation();
                                }
                            }, false);
                            a.addEventListener('simulate_click', function (e) {
                                a.href = args.content;
                                a.onclick = function() {
                                    open_video("video_"+args.id);
                                };
                                a.setAttribute('data-fancybox', '');
                                a.setAttribute('data-caption', caption);
                                a.click();
                            }, false);
                        }
                        a.appendChild(i);
                        div_wrapper.appendChild(a);
                        break;
                    default:
                        div_wrapper.appendChild(i);
                        break;
                }
                break;
            case 1:
                if(args.animation=='none') {
                    div_wrapper.classList.add('pulse_image');
                }
                var ext = args.img_icon_library.split('.').pop().toLowerCase();
                if(ext=='json') {
                    lottie = true;
                    hotSpotDiv.classList.add('lottie_icon');
                    var div = document.createElement('div');
                    div.innerHTML = '<div id="lottie_p_'+args.id+'" style="height:50px;width:50px;vertical-align:middle"></div>';
                    switch (args.type) {
                        case 'download':
                            var a = document.createElement('a');
                            a.style = 'text-decoration:none;';
                            a.addEventListener('mousedown', function(e) {
                                start_drag = new Date().getTime();
                                drag_p = false;
                            }, false);
                            a.addEventListener('mousemove', function(e) {
                                end_drag = new Date().getTime();
                                drag_p = true;
                            }, false);
                            a.addEventListener('mouseup', function(e) {
                                var diff_drag = end_drag - start_drag;
                                if(drag_p == false || diff_drag < 200) {
                                    a.href = args.content;
                                    a.download = '';
                                } else {
                                    a.removeAttribute('href');
                                    a.removeAttribute('download');
                                }
                            }, false);
                            a.appendChild(div);
                            hotSpotDiv.appendChild(a);
                            break;
                        case 'link':
                            var a = document.createElement('a');
                            a.style = 'text-decoration:none;';
                            a.title = args.title;
                            a.id = "link_"+args.id;
                            if(view_type==0) {
                                a.addEventListener('mousedown', function(e) {
                                    start_drag = new Date().getTime();
                                    drag_p = false;
                                }, false);
                                a.addEventListener('mousemove', function(e) {
                                    end_drag = new Date().getTime();
                                    drag_p = true;
                                }, false);
                                a.addEventListener('mouseup', function(e) {
                                    var diff_drag = end_drag - start_drag;
                                    if(drag_p == false || diff_drag < 200) {
                                        a.href = args.content;
                                        a.setAttribute('data-fancybox','');
                                        a.setAttribute('data-caption',caption);
                                        a.setAttribute('data-type','iframe');
                                        a.setAttribute('data-options','{"type" : "iframe", "iframe" : {"preload" : false}}');
                                    } else {
                                        a.href = '#';
                                        a.removeAttribute('data-fancybox');
                                        a.removeAttribute('data-caption');
                                        a.removeAttribute('data-type');
                                        a.removeAttribute('data-options');
                                    }
                                }, false);
                                a.addEventListener('simulate_click', function (e) {
                                    a.href = args.content;
                                    a.setAttribute('data-fancybox','');
                                    a.setAttribute('data-caption',caption);
                                    a.setAttribute('data-type','iframe');
                                    a.setAttribute('data-options','{"type" : "iframe", "iframe" : {"preload" : false}}');
                                    a.click();
                                }, false);
                            }
                            a.appendChild(div);
                            hotSpotDiv.appendChild(a);
                            break;
                        case 'link_ext':
                            var a = document.createElement('a');
                            a.style = 'text-decoration:none;';
                            a.addEventListener('mousedown', function(e) {
                                start_drag = new Date().getTime();
                                drag_p = false;
                            }, false);
                            a.addEventListener('mousemove', function(e) {
                                end_drag = new Date().getTime();
                                drag_p = true;
                            }, false);
                            a.addEventListener('mouseup', function(e) {
                                var diff_drag = end_drag - start_drag;
                                if(drag_p == false || diff_drag < 200) {
                                    a.href = args.content;
                                    a.target = args.target;
                                } else {
                                    a.removeAttribute('href');
                                    a.removeAttribute('target');
                                }
                            }, false);
                            a.appendChild(div);
                            hotSpotDiv.appendChild(a);
                            break;
                        case 'video':
                            var a = document.createElement('a');
                            a.style = 'text-decoration:none;';
                            a.title = args.title;
                            if(view_type==0) {
                                a.id = "video_" + args.id;
                                a.addEventListener('mousedown', function(e) {
                                    start_drag = new Date().getTime();
                                    drag_p = false;
                                }, false);
                                a.addEventListener('mousemove', function(e) {
                                    end_drag = new Date().getTime();
                                    drag_p = true;
                                }, false);
                                a.addEventListener('mouseup', function(e) {
                                    var diff_drag = end_drag - start_drag;
                                    if(drag_p == false || diff_drag < 200) {
                                        a.href = args.content;
                                        a.onclick = function() {
                                            open_video("video_"+args.id);
                                        };
                                        a.setAttribute('data-fancybox', '');
                                        a.setAttribute('data-caption', caption);
                                    } else {
                                        a.href = '#';
                                        a.onclick = function() {};
                                        a.removeAttribute('data-fancybox');
                                        a.removeAttribute('data-caption');
                                        e.preventDefault();
                                        e.stopImmediatePropagation();
                                    }
                                }, false);
                                a.addEventListener('simulate_click', function (e) {
                                    a.href = args.content;
                                    a.onclick = function() {
                                        open_video("video_"+args.id);
                                    };
                                    a.setAttribute('data-fancybox', '');
                                    a.setAttribute('data-caption', caption);
                                    a.click();
                                }, false);
                            }
                            a.appendChild(div);
                            hotSpotDiv.appendChild(a);
                            break;
                        default:
                            hotSpotDiv.appendChild(div);
                            break;
                    }
                    bodymovin.loadAnimation({
                        container: document.getElementById('lottie_p_'+args.id),
                        renderer: 'svg',
                        loop: true,
                        autoplay: true,
                        path: 'icons/'+args.img_icon_library,
                        rendererSettings: {
                            progressiveLoad: true,
                        }
                    });
                } else {
                    var img = document.createElement('img');
                    img.src = array_base64[args.img_icon_library];
                    img.style = "width:50px;margin: 0 auto;vertical-align:middle;opacity:1;";
                    switch (args.type) {
                        case 'download':
                            var a = document.createElement('a');
                            a.style = 'text-decoration:none;';
                            a.addEventListener('mousedown', function(e) {
                                start_drag = new Date().getTime();
                                drag_p = false;
                            }, false);
                            a.addEventListener('mousemove', function(e) {
                                end_drag = new Date().getTime();
                                drag_p = true;
                            }, false);
                            a.addEventListener('mouseup', function(e) {
                                var diff_drag = end_drag - start_drag;
                                if(drag_p == false || diff_drag < 200) {
                                    a.href = args.content;
                                    a.download = '';
                                } else {
                                    a.removeAttribute('href');
                                    a.removeAttribute('download');
                                }
                            }, false);
                            a.appendChild(img);
                            div_wrapper.appendChild(a);
                            break;
                        case 'link':
                            var a = document.createElement('a');
                            a.style = 'text-decoration:none;';
                            a.title = args.title;
                            a.id = "link_"+args.id;
                            if(view_type==0) {
                                a.addEventListener('mousedown', function(e) {
                                    start_drag = new Date().getTime();
                                    drag_p = false;
                                }, false);
                                a.addEventListener('mousemove', function(e) {
                                    end_drag = new Date().getTime();
                                    drag_p = true;
                                }, false);
                                a.addEventListener('mouseup', function(e) {
                                    var diff_drag = end_drag - start_drag;
                                    if(drag_p == false || diff_drag < 200) {
                                        a.href = args.content;
                                        a.setAttribute('data-fancybox','');
                                        a.setAttribute('data-caption',caption);
                                        a.setAttribute('data-type','iframe');
                                        a.setAttribute('data-options','{"type" : "iframe", "iframe" : {"preload" : false}}');
                                    } else {
                                        a.href = '#';
                                        a.removeAttribute('data-fancybox');
                                        a.removeAttribute('data-caption');
                                        a.removeAttribute('data-type');
                                        a.removeAttribute('data-options');
                                    }
                                }, false);
                                a.addEventListener('simulate_click', function (e) {
                                    a.href = args.content;
                                    a.setAttribute('data-fancybox','');
                                    a.setAttribute('data-caption',caption);
                                    a.setAttribute('data-type','iframe');
                                    a.setAttribute('data-options','{"type" : "iframe", "iframe" : {"preload" : false}}');
                                    a.click();
                                }, false);
                            }
                            a.appendChild(img);
                            div_wrapper.appendChild(a);
                            break;
                        case 'link_ext':
                            var a = document.createElement('a');
                            a.style = 'text-decoration:none;';
                            a.addEventListener('mousedown', function(e) {
                                start_drag = new Date().getTime();
                                drag_p = false;
                            }, false);
                            a.addEventListener('mousemove', function(e) {
                                end_drag = new Date().getTime();
                                drag_p = true;
                            }, false);
                            a.addEventListener('mouseup', function(e) {
                                var diff_drag = end_drag - start_drag;
                                if(drag_p == false || diff_drag < 200) {
                                    a.href = args.content;
                                    a.target = args.target;
                                } else {
                                    a.removeAttribute('href');
                                    a.removeAttribute('target');
                                }
                            }, false);
                            a.appendChild(img);
                            div_wrapper.appendChild(a);
                            break;
                        case 'video':
                            var a = document.createElement('a');
                            a.style = 'text-decoration:none;';
                            a.title = args.title;
                            if(view_type==0) {
                                a.id = "video_" + args.id;
                                a.addEventListener('mousedown', function(e) {
                                    start_drag = new Date().getTime();
                                    drag_p = false;
                                }, false);
                                a.addEventListener('mousemove', function(e) {
                                    end_drag = new Date().getTime();
                                    drag_p = true;
                                }, false);
                                a.addEventListener('mouseup', function(e) {
                                    var diff_drag = end_drag - start_drag;
                                    if(drag_p == false || diff_drag < 200) {
                                        a.href = args.content;
                                        a.onclick = function() {
                                            open_video("video_"+args.id);
                                        };
                                        a.setAttribute('data-fancybox', '');
                                        a.setAttribute('data-caption', caption);
                                    } else {
                                        a.href = '#';
                                        a.onclick = function() {};
                                        a.removeAttribute('data-fancybox');
                                        a.removeAttribute('data-caption');
                                        e.preventDefault();
                                        e.stopImmediatePropagation();
                                    }
                                }, false);
                                a.addEventListener('simulate_click', function (e) {
                                    a.href = args.content;
                                    a.onclick = function() {
                                        open_video("video_"+args.id);
                                    };
                                    a.setAttribute('data-fancybox', '');
                                    a.setAttribute('data-caption', caption);
                                    a.click();
                                }, false);
                            }
                            a.appendChild(img);
                            div_wrapper.appendChild(a);
                            break;
                        default:
                            div_wrapper.appendChild(img);
                            break;
                    }
                }
                break;
            case 2:
                if(args.animation=='none') {
                    div_wrapper.classList.add('pulse_icon');
                }
                div_wrapper.style.background = args.background;
                var i = document.createElement('i');
                i.className = args.icon;
                i.style = "margin: 0 auto;vertical-align:middle;font-size:24px;color:"+args.color;
                var span = document.createElement('span');
                if(args.label != '') {
                    span.innerHTML = '&nbsp;'+args.label.toUpperCase();
                }
                span.style = "vertical-align:middle;color:"+args.color;
                switch (args.type) {
                    case 'download':
                        var a = document.createElement('a');
                        a.style = 'text-decoration:none;';
                        a.addEventListener('mousedown', function(e) {
                            start_drag = new Date().getTime();
                            drag_p = false;
                        }, false);
                        a.addEventListener('mousemove', function(e) {
                            end_drag = new Date().getTime();
                            drag_p = true;
                        }, false);
                        a.addEventListener('mouseup', function(e) {
                            var diff_drag = end_drag - start_drag;
                            if(drag_p == false || diff_drag < 200) {
                                a.href = args.content;
                                a.download = '';
                            } else {
                                a.removeAttribute('href');
                                a.removeAttribute('download');
                            }
                        }, false);
                        a.appendChild(i);
                        a.appendChild(span);
                        div_wrapper.appendChild(a);
                        break;
                    case 'link':
                        var a = document.createElement('a');
                        a.style = 'text-decoration:none;';
                        a.title = args.title;
                        a.id = "link_"+args.id;
                        if(view_type==0) {
                            a.addEventListener('mousedown', function(e) {
                                start_drag = new Date().getTime();
                                drag_p = false;
                            }, false);
                            a.addEventListener('mousemove', function(e) {
                                end_drag = new Date().getTime();
                                drag_p = true;
                            }, false);
                            a.addEventListener('mouseup', function(e) {
                                var diff_drag = end_drag - start_drag;
                                if(drag_p == false || diff_drag < 200) {
                                    a.href = args.content;
                                    a.setAttribute('data-fancybox','');
                                    a.setAttribute('data-caption',caption);
                                    a.setAttribute('data-type','iframe');
                                    a.setAttribute('data-options','{"type" : "iframe", "iframe" : {"preload" : false}}');
                                } else {
                                    a.href = '#';
                                    a.removeAttribute('data-fancybox');
                                    a.removeAttribute('data-caption');
                                    a.removeAttribute('data-type');
                                    a.removeAttribute('data-options');
                                }
                            }, false);
                            a.addEventListener('simulate_click', function (e) {
                                a.href = args.content;
                                a.setAttribute('data-fancybox','');
                                a.setAttribute('data-caption',caption);
                                a.setAttribute('data-type','iframe');
                                a.setAttribute('data-options','{"type" : "iframe", "iframe" : {"preload" : false}}');
                                a.click();
                            }, false);
                        }
                        a.appendChild(i);
                        a.appendChild(span);
                        div_wrapper.appendChild(a);
                        break;
                    case 'link_ext':
                        var a = document.createElement('a');
                        a.style = 'text-decoration:none;';
                        a.addEventListener('mousedown', function(e) {
                            start_drag = new Date().getTime();
                            drag_p = false;
                        }, false);
                        a.addEventListener('mousemove', function(e) {
                            end_drag = new Date().getTime();
                            drag_p = true;
                        }, false);
                        a.addEventListener('mouseup', function(e) {
                            var diff_drag = end_drag - start_drag;
                            if(drag_p == false || diff_drag < 200) {
                                a.href = args.content;
                                a.target = args.target;
                            } else {
                                a.removeAttribute('href');
                                a.removeAttribute('target');
                            }
                        }, false);
                        a.appendChild(i);
                        a.appendChild(span);
                        div_wrapper.appendChild(a);
                        break;
                    case 'video':
                        var a = document.createElement('a');
                        a.style = 'text-decoration:none;';
                        a.title = args.title;
                        if(view_type==0) {
                            a.id = "video_" + args.id;
                            a.addEventListener('mousedown', function(e) {
                                start_drag = new Date().getTime();
                                drag_p = false;
                            }, false);
                            a.addEventListener('mousemove', function(e) {
                                end_drag = new Date().getTime();
                                drag_p = true;
                            }, false);
                            a.addEventListener('mouseup', function(e) {
                                var diff_drag = end_drag - start_drag;
                                if(drag_p == false || diff_drag < 200) {
                                    a.href = args.content;
                                    a.onclick = function() {
                                        open_video("video_"+args.id);
                                    };
                                    a.setAttribute('data-fancybox', '');
                                    a.setAttribute('data-caption', caption);
                                } else {
                                    a.href = '#';
                                    a.onclick = function() {};
                                    a.removeAttribute('data-fancybox');
                                    a.removeAttribute('data-caption');
                                    e.preventDefault();
                                    e.stopImmediatePropagation();
                                }
                            }, false);
                            a.addEventListener('simulate_click', function (e) {
                                a.href = args.content;
                                a.onclick = function() {
                                    open_video("video_"+args.id);
                                };
                                a.setAttribute('data-fancybox', '');
                                a.setAttribute('data-caption', caption);
                                a.click();
                            }, false);
                        }
                        a.appendChild(i);
                        a.appendChild(span);
                        div_wrapper.appendChild(a);
                        break;
                    default:
                        div_wrapper.appendChild(i);
                        div_wrapper.appendChild(span);
                        break;
                }
                break;
            case 3:
                if(args.animation=='none') {
                    div_wrapper.classList.add('pulse_icon');
                }
                div_wrapper.style.background = args.background;
                var span = document.createElement('span');
                if(args.label != '') {
                    span.innerHTML = args.label.toUpperCase()+'&nbsp;';
                }
                span.style = "vertical-align:middle;color:"+args.color;
                var i = document.createElement('i');
                i.className = args.icon;
                i.style = "margin: 0 auto;vertical-align:middle;font-size:24px;color:"+args.color;
                switch (args.type) {
                    case 'download':
                        var a = document.createElement('a');
                        a.style = 'text-decoration:none;';
                        a.addEventListener('mousedown', function(e) {
                            start_drag = new Date().getTime();
                            drag_p = false;
                        }, false);
                        a.addEventListener('mousemove', function(e) {
                            end_drag = new Date().getTime();
                            drag_p = true;
                        }, false);
                        a.addEventListener('mouseup', function(e) {
                            var diff_drag = end_drag - start_drag;
                            if(drag_p == false || diff_drag < 200) {
                                a.href = args.content;
                                a.download = '';
                            } else {
                                a.removeAttribute('href');
                                a.removeAttribute('download');
                            }
                        }, false);
                        a.appendChild(span);
                        a.appendChild(i);
                        div_wrapper.appendChild(a);
                        break;
                    case 'link':
                        var a = document.createElement('a');
                        a.id = "link_"+args.id;
                        a.style = 'text-decoration:none;';
                        a.title = args.title;
                        if(view_type==0) {
                            a.addEventListener('mousedown', function(e) {
                                start_drag = new Date().getTime();
                                drag_p = false;
                            }, false);
                            a.addEventListener('mousemove', function(e) {
                                end_drag = new Date().getTime();
                                drag_p = true;
                            }, false);
                            a.addEventListener('mouseup', function(e) {
                                var diff_drag = end_drag - start_drag;
                                if(drag_p == false || diff_drag < 200) {
                                    a.href = args.content;
                                    a.setAttribute('data-fancybox','');
                                    a.setAttribute('data-caption',caption);
                                    a.setAttribute('data-type','iframe');
                                    a.setAttribute('data-options','{"type" : "iframe", "iframe" : {"preload" : false}}');
                                } else {
                                    a.href = '#';
                                    a.removeAttribute('data-fancybox');
                                    a.removeAttribute('data-caption');
                                    a.removeAttribute('data-type');
                                    a.removeAttribute('data-options');
                                }
                            }, false);
                            a.addEventListener('simulate_click', function (e) {
                                a.href = args.content;
                                a.setAttribute('data-fancybox','');
                                a.setAttribute('data-caption',caption);
                                a.setAttribute('data-type','iframe');
                                a.setAttribute('data-options','{"type" : "iframe", "iframe" : {"preload" : false}}');
                                a.click();
                            }, false);
                        }
                        a.appendChild(span);
                        a.appendChild(i);
                        div_wrapper.appendChild(a);
                        break;
                    case 'link_ext':
                        var a = document.createElement('a');
                        a.style = 'text-decoration:none;';
                        a.addEventListener('mousedown', function(e) {
                            start_drag = new Date().getTime();
                            drag_p = false;
                        }, false);
                        a.addEventListener('mousemove', function(e) {
                            end_drag = new Date().getTime();
                            drag_p = true;
                        }, false);
                        a.addEventListener('mouseup', function(e) {
                            var diff_drag = end_drag - start_drag;
                            if(drag_p == false || diff_drag < 200) {
                                a.href = args.content;
                                a.target = args.target;
                            } else {
                                a.removeAttribute('href');
                                a.removeAttribute('target');
                            }
                        }, false);
                        a.appendChild(span);
                        a.appendChild(i);
                        div_wrapper.appendChild(a);
                        break;
                    case 'video':
                        var a = document.createElement('a');
                        a.id = "video_"+args.id;
                        a.style = 'text-decoration:none;';
                        a.title = args.title;
                        if(view_type==0) {
                            a.addEventListener('mousedown', function(e) {
                                start_drag = new Date().getTime();
                                drag_p = false;
                            }, false);
                            a.addEventListener('mousemove', function(e) {
                                end_drag = new Date().getTime();
                                drag_p = true;
                            }, false);
                            a.addEventListener('mouseup', function(e) {
                                var diff_drag = end_drag - start_drag;
                                if(drag_p == false || diff_drag < 200) {
                                    a.href = args.content;
                                    a.onclick = function() {
                                        open_video("video_"+args.id);
                                    };
                                    a.setAttribute('data-fancybox', '');
                                    a.setAttribute('data-caption', caption);
                                } else {
                                    a.href = '#';
                                    a.onclick = function() {};
                                    a.removeAttribute('data-fancybox');
                                    a.removeAttribute('data-caption');
                                    e.preventDefault();
                                    e.stopImmediatePropagation();
                                }
                            }, false);
                            a.addEventListener('simulate_click', function (e) {
                                a.href = args.content;
                                a.onclick = function() {
                                    open_video("video_"+args.id);
                                };
                                a.setAttribute('data-fancybox', '');
                                a.setAttribute('data-caption', caption);
                                a.click();
                            }, false);
                        }
                        a.appendChild(span);
                        a.appendChild(i);
                        div_wrapper.appendChild(a);
                        break;
                    default:
                        div_wrapper.appendChild(span);
                        div_wrapper.appendChild(i);
                        break;
                }
                break;
            case 4:
                if(args.animation=='none') {
                    div_wrapper.classList.add('pulse_icon');
                }
                div_wrapper.style.background = args.background;
                var span = document.createElement('span');
                if(args.label != '') {
                    span.innerHTML = args.label.toUpperCase();
                }
                span.style = "font-size:14px;vertical-align:middle;color:"+args.color;
                switch (args.type) {
                    case 'download':
                        var a = document.createElement('a');
                        a.style = 'text-decoration:none;';
                        a.addEventListener('mousedown', function(e) {
                            start_drag = new Date().getTime();
                            drag_p = false;
                        }, false);
                        a.addEventListener('mousemove', function(e) {
                            end_drag = new Date().getTime();
                            drag_p = true;
                        }, false);
                        a.addEventListener('mouseup', function(e) {
                            var diff_drag = end_drag - start_drag;
                            if(drag_p == false || diff_drag < 200) {
                                a.href = args.content;
                                a.download = '';
                            } else {
                                a.removeAttribute('href');
                                a.removeAttribute('download');
                            }
                        }, false);
                        a.appendChild(span);
                        div_wrapper.appendChild(a);
                        break;
                    case 'link':
                        var a = document.createElement('a');
                        a.id = "link_"+args.id;
                        a.style = 'text-decoration:none;';
                        a.title = args.title;
                        if(view_type==0) {
                            a.addEventListener('mousedown', function(e) {
                                start_drag = new Date().getTime();
                                drag_p = false;
                            }, false);
                            a.addEventListener('mousemove', function(e) {
                                end_drag = new Date().getTime();
                                drag_p = true;
                            }, false);
                            a.addEventListener('mouseup', function(e) {
                                var diff_drag = end_drag - start_drag;
                                if(drag_p == false || diff_drag < 200) {
                                    a.href = args.content;
                                    a.setAttribute('data-fancybox','');
                                    a.setAttribute('data-caption',caption);
                                    a.setAttribute('data-type','iframe');
                                    a.setAttribute('data-options','{"type" : "iframe", "iframe" : {"preload" : false}}');
                                } else {
                                    a.href = '#';
                                    a.removeAttribute('data-fancybox');
                                    a.removeAttribute('data-caption');
                                    a.removeAttribute('data-type');
                                    a.removeAttribute('data-options');
                                }
                            }, false);
                            a.addEventListener('simulate_click', function (e) {
                                a.href = args.content;
                                a.setAttribute('data-fancybox','');
                                a.setAttribute('data-caption',caption);
                                a.setAttribute('data-type','iframe');
                                a.setAttribute('data-options','{"type" : "iframe", "iframe" : {"preload" : false}}');
                                a.click();
                            }, false);
                        }
                        a.appendChild(span);
                        div_wrapper.appendChild(a);
                        break;
                    case 'link_ext':
                        var a = document.createElement('a');
                        a.style = 'text-decoration:none;';
                        a.addEventListener('mousedown', function(e) {
                            start_drag = new Date().getTime();
                            drag_p = false;
                        }, false);
                        a.addEventListener('mousemove', function(e) {
                            end_drag = new Date().getTime();
                            drag_p = true;
                        }, false);
                        a.addEventListener('mouseup', function(e) {
                            var diff_drag = end_drag - start_drag;
                            if(drag_p == false || diff_drag < 200) {
                                a.href = args.content;
                                a.target = args.target;
                            } else {
                                a.removeAttribute('href');
                                a.removeAttribute('target');
                            }
                        }, false);
                        a.appendChild(span);
                        div_wrapper.appendChild(a);
                        break;
                    case 'video':
                        var a = document.createElement('a');
                        a.id = "video_"+args.id;
                        a.style = 'text-decoration:none;';
                        a.title = args.title;
                        if(view_type==0) {
                            a.addEventListener('mousedown', function(e) {
                                start_drag = new Date().getTime();
                                drag_p = false;
                            }, false);
                            a.addEventListener('mousemove', function(e) {
                                end_drag = new Date().getTime();
                                drag_p = true;
                            }, false);
                            a.addEventListener('mouseup', function(e) {
                                var diff_drag = end_drag - start_drag;
                                if(drag_p == false || diff_drag < 200) {
                                    a.href = args.content;
                                    a.onclick = function() {
                                        open_video("video_"+args.id);
                                    };
                                    a.setAttribute('data-fancybox', '');
                                    a.setAttribute('data-caption', caption);
                                } else {
                                    a.href = '#';
                                    a.onclick = function() {};
                                    a.removeAttribute('data-fancybox');
                                    a.removeAttribute('data-caption');
                                    e.preventDefault();
                                    e.stopImmediatePropagation();
                                }
                            }, false);
                            a.addEventListener('simulate_click', function (e) {
                                a.href = args.content;
                                a.onclick = function() {
                                    open_video("video_"+args.id);
                                };
                                a.setAttribute('data-fancybox', '');
                                a.setAttribute('data-caption', caption);
                                a.click();
                            }, false);
                        }
                        a.appendChild(span);
                        div_wrapper.appendChild(a);
                        break;
                    default:
                        div_wrapper.appendChild(span);
                        break;
                }
                break;
        }
        if(lottie==false) hotSpotDiv.appendChild(div_wrapper);
        embed_pois_contents(hotSpotDiv,view_type,args,caption);
    }

    function embed_pois_contents(hotSpotDiv,view_type,args,caption) {
        switch (view_type) {
            case 1:
            case 2:
                var div = document.createElement('div');
                div.classList.add('box_poi');
                div.classList.add("box_poi_"+args.id);
                div.setAttribute('data-id',args.id);
                div.setAttribute('data-box-pos',args.box_pos);
                div.setAttribute('data-scale',args.size_scale);
                var div_html = '<div class="box-arrow-border"><div class="box-arrow-background"></div></div>';
                div_html += '<div data-simplebar class="box-content">';
                if(caption!='') {
                    div_html += '<p class="box-caption">'+caption+'</p><hr class="box-hr">';
                }
                switch (args.type) {
                    case 'image':
                        div_html += '<img style="width:100%;" src="'+args.content+'" />';
                        break;
                    case 'gallery':
                        div_html += '<div id="poi_gallery_container_'+args.id+'" class="poi_gallery_container"></div>';
                        break;
                    case 'link':
                        div_html += '<iframe style="border:none;padding:0;margin:0;width:100%;height:300px;" src="'+args.content+'"></iframe>';
                        break;
                    case 'video':
                        if(args.content.includes("youtu")) {
                            var id_y = getYoutubeId(args.content);
                            if(id_y!=null) {
                                div_html += '<iframe class="youtube_video" width="100%" height="200px" src="//www.youtube.com/embed/'+id_y+'" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>';
                            }
                        } else if(args.content.includes("vimeo")) {
                            var id_v = getVimeoId(args.content);
                            if(id_v!=null) {
                                div_html += '<iframe class="vimeo_video" width="100%" height="200px" src="https://player.vimeo.com/video/'+id_v+'" frameborder="0" allow="autoplay; fullscreen; picture-in-picture" allowfullscreen></iframe>';
                            }
                        } else {
                            div_html += '<video webkit-playsinline playsinline controls width="100%"><source src="'+args.content+'" type="video/mp4"></video>';
                        }
                        break;
                    case 'html':
                    case 'html_sc':
                        div_html += '<div style="border:none;padding:0;margin:0;width:100%;color:black;">'+args.content+'</div>';
                        break;
                    case 'form':
                        try {
                            var content = JSON.parse(args.content);
                            var response = content[0].response;
                            var html_content = parse_form_content(content,args.id_room);
                            div_html += '<div style="border:none;padding:0;margin:0;width:100%;">'+html_content+'</div>';
                        } catch (e) {}
                        break;
                    case 'google_maps':
                        var gm_map = args.content.split('|')[0];
                        var gm_street = args.content.split('|')[1];
                        div_html += '<div class="poi_google_maps_content_poi_box">'+gm_map+' '+gm_street+'</div>';
                        break;
                    case 'video360':
                        div_html += '<div style="width:100%;">' +
                            '<video style="width:100%;height:auto" crossorigin="anonymous" controls playsinline webkit-playsinline class="video-js vjs-default-skin vjs-big-play-centered vjs-fluid" id="video360_'+args.id+'">' +
                            '<source src="'+args.content+'" type="video/mp4">' +
                            '</video>' +
                            '</div>';
                        break;
                    case 'object360':
                        div_html += '<div style="width:100%;" class="poi_object360_content"><div' +
                            '   class="cloudimage-360"' +
                            '   data-folder="objects360/"' +
                            '   data-filename="'+args.content.name_images+'?v='+time_version+'"' +
                            '   data-amount="'+args.content.count_images+'"' +
                            '   data-bottom-circle data-hide-360-logo data-spin-reverse' +
                            '></div>';
                        break;
                    case 'object3d':
                        var array_files = args.content.split(",");
                        var glb_file = '', usdz_file = '';
                        jQuery.each(array_files, function(index_s, file_s) {
                            if(file_s.split('.').pop().toLowerCase()=='glb') {
                                glb_file = file_s;
                            }
                            if(file_s.split('.').pop().toLowerCase()=='gltf') {
                                glb_file = file_s;
                            }
                            if(file_s.split('.').pop().toLowerCase()=='usdz') {
                                usdz_file = file_s;
                            }
                        });
                        switch(args.params) {
                            case 'floor':
                            case 'wall':
                                if(usdz_file!='') {
                                    div_html += '<div style="height: 250px" class="poi_object3d_content"><model-viewer src="'+glb_file+'" ios-src="'+usdz_file+'" alt="" ar ar-modes="webxr scene-viewer quick-look" ar-placement="'+args.params+'" environment-image="neutral" shadow-intensity="1" auto-rotate camera-controls></model-viewer></div>';
                                } else {
                                    div_html += '<div style="height: 250px" class="poi_object3d_content"><model-viewer src="'+glb_file+'" alt="" ar ar-modes="webxr scene-viewer quick-look" ar-placement="'+args.params+'" environment-image="neutral" shadow-intensity="1" auto-rotate camera-controls></model-viewer></div>';
                                }
                                break;
                            default:
                                div_html += '<div style="height: 250px" class="poi_object3d_content"><model-viewer src="'+glb_file+'" alt="" environment-image="neutral" shadow-intensity="1" auto-rotate camera-controls></model-viewer></div>';
                                break;
                        }
                        break;
                    case 'lottie':
                        div_html += '<div style="height: 250px" id="poi_lottie_'+args.id+'" class="poi_lottie_content"></div>';
                        break;
                    case 'product':
                        div_html += '<div style="border:none;padding:0;margin:0;width:100%;" id="poi_product_'+args.id+'" class="poi_product_content">'+get_product_html(args.product,args.product_images,args.id)+'</div>';
                        break;
                }
                div_html += '</div>';
                div.innerHTML = div_html;
                hotSpotDiv.parentNode.appendChild(div);
                switch(args.type) {
                    case 'gallery':
                        poi_gallery = $('#poi_gallery_container_'+args.id).nanogallery2({
                            imageTransition: 'slideAppear',
                            thumbnailHeight: 150,
                            thumbnailWidth: 'auto',
                            items: args.content,
                            allowHTMLinData: true,
                            viewerHideToolsDelay: 30000,
                            viewerToolbar: {
                                display: true,
                                autoMinimize: false,
                                standard: 'label'
                            },
                            viewerTools: {
                                topLeft: 'pageCounter, playPauseButton',
                                topRight: 'zoomButton, closeButton'
                            }
                        });
                        break;
                    case 'video360':
                        video360_poi[args.id] = videojs('video360_'+args.id,{
                            controlBar: {
                                pictureInPictureToggle: false
                            }
                        });
                        video360_poi[args.id].vr({projection: '360'});
                        break;
                    case 'object360':
                        window.CI360.init();
                        break;
                    case 'lottie':
                        bodymovin.loadAnimation({
                            container: document.getElementById('poi_lottie_'+args.id),
                            renderer: 'svg',
                            loop: true,
                            autoplay: true,
                            path: args.content,
                            rendererSettings: {
                                progressiveLoad: true,
                            }
                        });
                        break;
                    case 'product':
                        $("#product_"+args.id+" .carousel").swipe({
                            swipe: function (event, direction, distance, duration, fingerCount, fingerData) {
                                if (direction == 'left') $(this).carousel('next');
                                if (direction == 'right') $(this).carousel('prev');
                            },
                            allowPageScroll: "vertical"
                        });
                        break;
                }
                break;
        }
    }

    function hotspot(hotSpotDiv, args) {
        if(vr_enabled) { var markers_show_room_t = 0; } else { var markers_show_room_t = parseInt(args.show_room); }
        hotSpotDiv.setAttribute('draggable',false);
        hotSpotDiv.classList.add('custom-tooltip');
        hotSpotDiv.classList.add('noselect');
        if(hide_markers==1) {
            hotSpotDiv.classList.add('hidden_m');
        }
        hotSpotDiv.classList.add('marker_'+args.id);
        if(args.css_class!='') {
            var array_css_class = args.css_class.split(" ");
            jQuery.each(array_css_class, function(index_c, css_class) {
                hotSpotDiv.classList.add(css_class);
            });
        }
        hotSpotDiv.setAttribute('data-id', args.id);
        hotSpotDiv.addEventListener("mouseover", function (e) {
            if(controls_status['icons']==true && !window.is_mobile) {
                var id = e.target.getAttribute('data-id');
                if(id !== null) {
                    $('.tooltip_marker_'+id).css('opacity',1);
                }
            }
        });
        hotSpotDiv.addEventListener("mouseenter", function (e) {
            if(controls_status['icons']==true && !window.is_mobile) {
                var id = e.target.getAttribute('data-id');
                if(id !== null) {
                    $('.tooltip_marker_'+id).css('opacity',1);
                }
            }
        });
        hotSpotDiv.addEventListener("mouseleave", function (e) {
            if(controls_status['icons']==true && !window.is_mobile) {
                var id = e.target.getAttribute('data-id');
                if(id !== null) {
                    $('.tooltip_marker_'+id).css('opacity',0);
                }
            }
        });
        if(markers_show_room_t==5) {
            if(args.tooltip_type=='preview' || args.tooltip_type=='preview_square' || args.tooltip_type=='preview_rect') {
                args.tooltip_type = 'room_name';
            }
        }
        switch(args.tooltip_type) {
            case 'text':
                if(args.tooltip_text !== null && args.tooltip_text !== '' && args.tooltip_text !== '<p><br></p>') {
                    var tooltip = document.createElement('div');
                    tooltip.classList.add('tooltip_marker_'+args.id);
                    tooltip.classList.add('tooltip_text');
                    tooltip.innerHTML = args.tooltip_text.toUpperCase();
                    hotSpotDiv.parentNode.appendChild(tooltip);
                }
                break;
            case 'room_name':
                var tooltip = document.createElement('div');
                tooltip.classList.add('tooltip_marker_'+args.id);
                tooltip.classList.add('tooltip_text');
                tooltip.innerHTML = args.name_room_target.toUpperCase();
                hotSpotDiv.parentNode.appendChild(tooltip);
                break;
            case 'preview':
                var index = get_id_viewer(args.id_room_target);
                var image = panoramas[index].thumb_image;
                var tooltip = document.createElement('div');
                tooltip.classList.add('tooltip_marker_'+args.id);
                tooltip.classList.add('tooltip_preview');
                tooltip.innerHTML = '<div style="width:100%;height:100%;border-radius:50px;background-image: url('+image+');background-size: cover;background-position: center;"></div>';
                hotSpotDiv.parentNode.appendChild(tooltip);
                break;
            case 'preview_square':
                var index = get_id_viewer(args.id_room_target);
                var image = panoramas[index].thumb_image;
                var tooltip = document.createElement('div');
                tooltip.classList.add('tooltip_marker_'+args.id);
                tooltip.classList.add('tooltip_preview_square');
                tooltip.innerHTML = '<div style="width:100%;height:100%;background-image: url('+image+');background-size: cover;background-position: center;"></div>';
                hotSpotDiv.parentNode.appendChild(tooltip);
                break;
            case 'preview_rect':
                var index = get_id_viewer(args.id_room_target);
                var image = panoramas[index].thumb_image;
                var tooltip = document.createElement('div');
                tooltip.classList.add('tooltip_marker_'+args.id);
                tooltip.classList.add('tooltip_preview_rect');
                tooltip.innerHTML = '<div style="width:100%;height:100%;background-image: url('+image+');background-size: cover;background-position: center;"></div>';
                hotSpotDiv.parentNode.appendChild(tooltip);
                break;
        }
        var div_wrapper = document.createElement('div');
        div_wrapper.classList.add('div_marker_wrapper');
        if(args.animation!='none' && !vr_enabled) {
            div_wrapper.classList.add('animate__animated');
            div_wrapper.classList.add('animate__slow');
            div_wrapper.classList.add('animate__'+args.animation);
            div_wrapper.classList.add('animate__infinite');
        }
        var lottie = false;
        switch (markers_show_room_t) {
            case 0:
                if(args.animation=='none') {
                    div_wrapper.classList.add('pulse_icon');
                }
                div_wrapper.style.background = args.background;
                var i = document.createElement('i');
                i.setAttribute('data-icon', args.icon);
                if(imgs_loaded.indexOf(args.id_room_target) !== -1) {
                    i.className = args.icon+" marker_img_"+args.id_room_target;
                } else {
                    i.className = "fas fa-spin fa-circle-notch marker_img_"+args.id_room_target;
                }
                i.style = "margin: 0 auto;vertical-align:middle;font-size:24px;color:"+args.color;
                div_wrapper.appendChild(i);
                break;
            case 1:
                if(args.animation=='none') {
                    div_wrapper.classList.add('pulse_icon');
                }
                div_wrapper.style.background = args.background;
                var i = document.createElement('i');
                i.setAttribute('data-icon', args.icon);
                if(imgs_loaded.indexOf(args.id_room_target) !== -1) {
                    i.className = args.icon+" marker_img_"+args.id_room_target;
                } else {
                    i.className = "fas fa-spin fa-circle-notch marker_img_"+args.id_room_target;
                }
                i.style = "margin: 0 auto;vertical-align:middle;font-size:24px;color:"+args.color;
                div_wrapper.appendChild(i);
                var span = document.createElement('span');
                span.innerHTML = '&nbsp;'+args.name_room_target.toUpperCase();
                span.style = "vertical-align:middle;color:"+args.color;
                div_wrapper.appendChild(span);
                break;
            case 2:
                if(args.animation=='none') {
                    div_wrapper.classList.add('pulse_icon');
                }
                div_wrapper.style.background = args.background;
                var span = document.createElement('span');
                span.innerHTML = args.name_room_target.toUpperCase()+'&nbsp;';
                span.style = "vertical-align:middle;color:"+args.color;
                div_wrapper.appendChild(span);
                var i = document.createElement('i');
                i.setAttribute('data-icon', args.icon);
                if(imgs_loaded.indexOf(args.id_room_target) !== -1) {
                    i.className = args.icon+" marker_img_"+args.id_room_target;
                } else {
                    i.className = "fas fa-spin fa-circle-notch marker_img_"+args.id_room_target;
                }
                i.style = "margin: 0 auto;vertical-align:middle;font-size:24px;color:"+args.color;
                div_wrapper.appendChild(i);
                break;
            case 3:
                if(args.animation=='none') {
                    div_wrapper.classList.add('pulse_icon');
                }
                div_wrapper.style.background = args.background;
                var span = document.createElement('span');
                span.innerHTML = args.name_room_target.toUpperCase();
                span.classList.add("marker_img_"+args.id_room_target);
                span.style = "font-size:14px;vertical-align:middle;color:"+args.color;
                div_wrapper.appendChild(span);
                break;
            case 4:
                if(args.animation=='none') {
                    div_wrapper.classList.add('pulse_image');
                }
                var ext = args.img_icon_library.split('.').pop().toLowerCase();
                if(ext=='json') {
                    lottie = true;
                    hotSpotDiv.classList.add('lottie_icon');
                    var div = document.createElement('div');
                    div.classList.add("marker_img_"+args.id_room_target);
                    div.innerHTML = '<div id="lottie_m_'+args.id+'" style="height:50px;width:50px;vertical-align:middle"></div>';
                    hotSpotDiv.appendChild(div);
                    bodymovin.loadAnimation({
                        container: document.getElementById('lottie_m_'+args.id),
                        renderer: 'svg',
                        loop: true,
                        autoplay: true,
                        path: 'icons/'+args.img_icon_library,
                        rendererSettings: {
                            progressiveLoad: true,
                        }
                    });
                } else {
                    var img = document.createElement('img');
                    img.src = array_base64[args.img_icon_library];
                    img.classList.add("marker_img_"+args.id_room_target);
                    img.style = "width:50px;margin: 0 auto;;vertical-align:middle;";
                    div_wrapper.appendChild(img);
                }
                break;
            case 5:
                div_wrapper.classList.add('pulse_image');
                div_wrapper.classList.add('marker_preview');
                var index = get_id_viewer(args.id_room_target);
                var image = panoramas[index].thumb_image;
                var div = document.createElement('div');
                div.classList.add("marker_img_"+args.id_room_target);
                div.style = 'width:48px;height:48px;border-radius:48px;background-image: url('+image+');background-size: cover;background-position: center;';
                div_wrapper.appendChild(div);
                break;
        }
        if(lottie==false) hotSpotDiv.appendChild(div_wrapper);
        if(imgs_loaded.indexOf(args.id_room_target) == -1) {
            hotSpotDiv.classList.add('disabled');
        }
    }

    window.goto_next_room = function () {
        var len = panoramas.length;
        var index = get_id_viewer(current_id_panorama);
        var next_panorama = panoramas[(index+1)%len];
        goto("",[next_panorama.id,null,null,null,null]);
    }

    window.goto_prev_room = function () {
        var len = panoramas.length;
        var index = get_id_viewer(current_id_panorama);
        var prev_panorama = panoramas[(index+len-1)%len];
        goto("",[prev_panorama.id,null,null,null,null]);
    }

    window.change_room_alt = function (id) {
        window.sync_virtual_staging_enabled=false;
        window.changed_room_alt = true;
        $('#loading_pano').show();
        $('#loading_pano').css('opacity',0.8);
        pano_viewer.stopAutoRotate();
        pano_viewer.stopMovement();
        var yaw = parseFloat(pano_viewer.getYaw());
        var pitch = parseFloat(pano_viewer.getPitch());
        var hfov = parseFloat(pano_viewer.getHfov());
        var index = get_id_viewer(current_id_panorama);
        if(panoramas[index].virtual_staging==1) {
            virtual_staging = true;
        } else {
            virtual_staging = false;
        }
        if(virtual_staging) {
            if(pano_viewer_alt!=null && pano_viewer_alt!==undefined) {
                pano_viewer.setYaw(pano_viewer_alt.getYaw());
                pano_viewer.setPitch(pano_viewer_alt.getPitch());
                pano_viewer.setHfov(pano_viewer_alt.getHfov());
                var dataURL = pano_viewer_alt.getRenderer().render(pano_viewer.getPitch() / 180 * Math.PI,
                    pano_viewer.getYaw() / 180 * Math.PI,
                    pano_viewer.getHfov() / 180 * Math.PI,
                    {'returnImage': 'image/jpeg'});
            } else {
                var dataURL = pano_viewer.getRenderer().render(pano_viewer.getPitch() / 180 * Math.PI,
                    pano_viewer.getYaw() / 180 * Math.PI,
                    pano_viewer.getHfov() / 180 * Math.PI,
                    {'returnImage': 'image/jpeg'});
            }
        } else {
            var dataURL = pano_viewer.getRenderer().render(pano_viewer.getPitch() / 180 * Math.PI,
                pano_viewer.getYaw() / 180 * Math.PI,
                pano_viewer.getHfov() / 180 * Math.PI,
                {'returnImage': 'image/jpeg'});
        }
        $('#background_pano').off('load');
        $('#background_pano').on('load',function () {
            $('#background_pano').show();
            $('#background_pano').css('z-index',11);
            setTimeout(function () {
                $('#panorama_viewer').css('opacity',0);
                $('#panorama_viewer_alt').css('opacity',0);
                $('#video_viewer').css('opacity',0);
                $('#vs_slider').fadeOut();
                $('#vs_grab').fadeOut();
                if(id==null) {
                    if(panoramas[index].multires) {
                        initialize_room(index,true,false,pitch,yaw,hfov,null,false);
                    } else {
                        var img = panoramas[index].panorama_image;
                        var tmp_image = new Image();
                        $(tmp_image).on('load',function () {
                            initialize_room(index,true,false,pitch,yaw,hfov,null,false);
                        }).attr('src',img);
                    }
                } else {
                    if(panoramas[index].array_rooms_alt[id].multires) {
                        initialize_room(index,true,false,pitch,yaw,hfov,id,false);
                    } else {
                        var img = panoramas[index].array_rooms_alt[id].panorama_image;
                        var tmp_image = new Image();
                        $(tmp_image).on('load',function () {
                            initialize_room(index,true,false,pitch,yaw,hfov,id,false);
                        }).attr('src',img);
                    }
                }
            },50);
        }).attr('src',dataURL);
    }

    window.change_room_alt_poi = function (hotspotDiv,id) {
        window.sync_virtual_staging_enabled=false;
        window.changed_room_alt_poi = true;
        $('#loading_pano').hide();
        $('#loading_pano').css('opacity',0);
        pano_viewer.stopAutoRotate();
        pano_viewer.stopMovement();
        var yaw = parseFloat(pano_viewer.getYaw());
        var pitch = parseFloat(pano_viewer.getPitch());
        var hfov = parseFloat(pano_viewer.getHfov());
        var index = get_id_viewer(current_id_panorama);
        var dataURL = pano_viewer.getRenderer().render(pano_viewer.getPitch() / 180 * Math.PI,
            pano_viewer.getYaw() / 180 * Math.PI,
            pano_viewer.getHfov() / 180 * Math.PI,
            {'returnImage': 'image/jpeg'});
        $('#background_pano').off('load');
        $('#background_pano').on('load',function () {
            $('#background_pano').show();
            $('#background_pano').css('z-index',11);
            setTimeout(function () {
                $('#panorama_viewer').css('opacity',0);
                $('#panorama_viewer_alt').css('opacity',0);
                $('#video_viewer').css('opacity',0);
                $('#vs_slider').fadeOut();
                $('#vs_grab').fadeOut();
                if(id==null || id==0) {
                    if(panoramas[index].multires) {
                        initialize_room(index,true,false,pitch,yaw,hfov,null,false);
                    } else {
                        var img = panoramas[index].panorama_image;
                        var tmp_image = new Image();
                        $(tmp_image).on('load',function () {
                            initialize_room(index,true,false,pitch,yaw,hfov,null,false);
                        }).attr('src',img);
                    }
                } else {
                    var index_ara = null;
                    jQuery.each(panoramas[index].array_rooms_alt, function(index_alt, room_alt) {
                        if(room_alt.id==id) {
                            index_ara=index_alt;
                        }
                    });
                    if(index_ara!=null) {
                        if(panoramas[index].array_rooms_alt[index_ara].multires) {
                            initialize_room(index,true,false,pitch,yaw,hfov,index_ara,false);
                        } else {
                            var img = panoramas[index].array_rooms_alt[index_ara].panorama_image;
                            var tmp_image = new Image();
                            $(tmp_image).on('load',function () {
                                initialize_room(index,true,false,pitch,yaw,hfov,index_ara,false);
                            }).attr('src',img);
                        }
                    } else {
                        if(panoramas[index].multires) {
                            initialize_room(index,true,false,pitch,yaw,hfov,null);
                        } else {
                            var img = panoramas[index].panorama_image;
                            var tmp_image = new Image();
                            $(tmp_image).on('load',function () {
                                initialize_room(index,true,false,pitch,yaw,hfov,null,false);
                            }).attr('src',img);
                        }
                    }
                }
            },50);
        }).attr('src',dataURL);
    }

    function initialize_room(id,cb,click_m,pitch_m,yaw_m,hfov_m,id_room_alt,dh) {
        setTimeout(function () {
            window.sync_poi_embed_enabled = false;
            window.sync_marker_embed_enabled = false;
            $('#draggable_container').empty();
            array_id_room_nav.push(panoramas[id].id);
            var len = panoramas.length;
            var prev_panorama = panoramas[(id+len-1)%len];
            var next_panorama = panoramas[(id+1)%len];
            $('.prev_arrow').attr('onclick','goto("",['+prev_panorama.id+',null,null,null,null]);');
            $('.prev_arrow').attr('data-roomtarget',prev_panorama.id);
            $('.prev_arrow').attr('title',prev_panorama.name);
            $('.next_arrow').attr('onclick','goto("",['+next_panorama.id+',null,null,null,null]);');
            $('.next_arrow').attr('data-roomtarget',next_panorama.id);
            $('.next_arrow').attr('title',next_panorama.name);
            $(".arrows").addClass('disabled');
            if(imgs_loaded.indexOf(prev_panorama.id) !== -1) {
                $(".arrows_nav").find("[data-roomtarget='" + prev_panorama.id + "']").removeClass('disabled');
                $(".controls_arrows").find("[data-roomtarget='" + prev_panorama.id + "']").removeClass('disabled');
            }
            if(imgs_loaded.indexOf(next_panorama.id) !== -1) {
                $(".arrows_nav").find("[data-roomtarget='" + next_panorama.id + "']").removeClass('disabled');
                $(".controls_arrows").find("[data-roomtarget='" + prev_panorama.id + "']").removeClass('disabled');
            }
            try {
                $('.prev_arrow').tooltipster('destroy');
            } catch (e) {}
            $('.arrows_nav .prev_arrow').tooltipster({
                theme: 'tooltipster-borderless',
                side: 'right',
                animation: 'grow',
                delay: 0,
                arrow: false
            });
            $('.controls_arrows .prev_arrow').tooltipster({
                theme: 'tooltipster-borderless',
                side: 'top',
                animation: 'grow',
                delay: 0,
                arrow: false
            });
            try {
                $('.next_arrow').tooltipster('destroy');
            } catch (e) {}
            $('.arrows_nav .next_arrow').tooltipster({
                theme: 'tooltipster-borderless',
                side: 'left',
                animation: 'grow',
                delay: 0,
                arrow: false
            });
            $('.controls_arrows .next_arrow').tooltipster({
                theme: 'tooltipster-borderless',
                side: 'top',
                animation: 'grow',
                delay: 0,
                arrow: false
            });
            var view_name = '';
            if(id_room_alt==null) {
                if(panoramas[id].main_view_tooltip!='') {
                    view_name = " ("+panoramas[id].main_view_tooltip+")";
                }
            } else {
                if(panoramas[id].array_rooms_alt[id_room_alt].view_tooltip!='') {
                    view_name = " ("+panoramas[id].array_rooms_alt[id_room_alt].view_tooltip+")";
                }
            }
            if(panoramas[id].logo!='') {
                $('.name_vt').html(name_virtual_tour);
                $('.room_vt').html("<img class='logo_room_vt' src='content/"+panoramas[id].logo+"' />&nbsp;&nbsp;"+view_name);
            } else {
                $('.name_vt').html(name_virtual_tour);
                $('.room_vt').html(panoramas[id].name+view_name);
            }
            if(author!='') {
                $('.author_vt').html(window.viewer_labels.by+' '+author);
            }
            $('.rooms_alt').removeClass("disabled");
            if(id_room_alt==null) {
                if(ObjectLength(panoramas[id].array_rooms_alt)>0) {
                    var main_img = panoramas[id].thumb_image;
                    var main_view_tooltip = panoramas[id].main_view_tooltip;
                    var class_tooltip = 'tooltip_view';
                    if(main_view_tooltip=='') class_tooltip='';
                    var html = "<img title=\""+main_view_tooltip+"\" class='active rooms_alt "+class_tooltip+"' onclick='change_room_alt(null);' src='"+main_img+"' />";
                    var num_rooms_alt = 0;
                    jQuery.each(panoramas[id].array_rooms_alt, function(index_alt, room_alt) {
                        if(room_alt.poi==0) {
                            num_rooms_alt++;
                            class_tooltip = 'tooltip_view';
                            if(room_alt.view_tooltip=='') class_tooltip='';
                            html += "<img title=\""+room_alt.view_tooltip+"\" class='rooms_alt "+class_tooltip+" rooms_alt_"+index_alt+"' onclick='change_room_alt("+index_alt+");' src='"+room_alt.thumb_image+"' />";
                        }
                    });
                    if(num_rooms_alt>0) {
                        $('.rooms_view_sel').html(html).promise().done(function () {
                            $('.tooltip_view').tooltipster({
                                theme: 'tooltipster-borderless',
                                side: 'bottom',
                                animation: 'grow',
                                delay: 0,
                                arrow: false
                            });
                        });
                    }
                } else {
                    $('.rooms_view_sel').empty();
                }
            } else {
                $('.rooms_alt').removeClass("active");
                $('.rooms_alt_'+id_room_alt).addClass("active");
            }
            if(panoramas[id].allow_pitch==1) {
                var minPitch = parseInt(panoramas[id].min_pitch)-34;
                var maxPitch = parseInt(panoramas[id].max_pitch)+34;
                if(pitch_m!=null) {
                    var pitch = parseInt(pitch_m);
                } else {
                    var pitch = parseInt(panoramas[id].pitch);
                }
            } else {
                var minPitch = 0;
                var maxPitch = 0;
                var pitch = 0;
            }
            if(vr_enabled) {
                var orientationOnByDefault = true;
                var min_hfov_f = 110;
                var max_hfov_f = 110;
                var hfov_f = 110;
                var draggable = false;
                var autorotate_speed_f = 0;
                if(yaw_m!=null) {
                    var workingYaw = parseInt(yaw_m);
                } else {
                    var workingYaw = parseInt(panoramas[id].yaw);
                }
            } else {
                if(controls_status['orient']) {
                    var orientationOnByDefault = true;
                } else {
                    var orientationOnByDefault = false;
                }
                var draggable = true;
                var min_hfov_f = min_hfov;
                var max_hfov_f = max_hfov;
                var hfov_f = parseInt(panoramas[id].hfov);
                var autorotate_speed_f = autorotate_speed;
            }
            if(controls_status['presentation'] && !presentation_type=='automatic') {
                autorotate_speed_f = 0;
            }
            if(sameAzimuth && click_m) {
                if(current_panorama_type=='image' || is_iOS() || current_panorama_type=='hls' || current_panorama_type=='lottie') {
                    var current_yaw = parseFloat(pano_viewer.getYaw());
                    var current_north = parseFloat(pano_viewer.getNorthOffset());
                } else {
                    var current_yaw = parseFloat(video_viewer.pnlmViewer.getYaw());
                    var current_north = parseFloat(video_viewer.pnlmViewer.getNorthOffset());
                }
                var workingYaw = current_yaw + (current_north || 0) - (parseInt(panoramas[id].northOffset) || 0);
                pitch = 0;
            } else {
                if(yaw_m!=null) {
                    var workingYaw = parseInt(yaw_m);
                } else {
                    var workingYaw = parseInt(panoramas[id].yaw);
                }
            }
            if((controls_status['presentation']) && (presentation_type=='automatic')) {
                pitch = 0;
                hfov_f = hfov;
                autorotate_speed_f = auto_presentation_speed;
            }
            if(hfov_m!=null) {
                hfov_f=hfov_m;
                workingYaw=yaw_m;
                pitch=pitch_m;
            }
            if((panoramas[id].allow_hfov==0) && (!vr_enabled)) {
                min_hfov_f = hfov_f;
                max_hfov_f = hfov_f;
            }
            if(window.flyin && autorotate_speed>0) {
                var autorotate_inactivity_f = -1;
            } else {
                var autorotate_inactivity_f = autorotate_inactivity;
            }
            try {
                pano_viewer.destroy();
            } catch (e) {}
            pano_viewer = null;
            try {
                pano_viewer_alt.destroy();
            } catch (e) {}
            pano_viewer_alt = null;
            try {
                video_viewer.pnlmViewer.destroy();
                video_viewer.dispose();
            } catch (e) {}
            video_viewer = null;
            try {
                app_p.destroy(true);
                PIXI.utils.destroyTextureCache();
            } catch (e) {}
            app_p = null;
            if(vr_enabled) {
                try {
                    pano_viewer_vr.destroy();
                } catch (e) {}
                try {
                    video_viewer_vr.pnlmViewer.destroy();
                    video_viewer_vr.dispose();
                    video_viewer_vr = null;
                } catch (e) {}
                try {
                    app_p_vr.destroy(true);
                    PIXI.utils.destroyTextureCache();
                    app_p_vr = null;
                } catch (e) {}
            }
            if(window.innerHeight > window.innerWidth){
                if(window.innerWidth<500) {
                    var touchPanSpeedCoeffFactor = pan_speed_mobile_vt;
                    var friction = friction_mobile_vt;
                } else {
                    var touchPanSpeedCoeffFactor = pan_speed_mobile_vt/2;
                    var friction = friction_mobile_vt/2;
                }
            } else {
                var touchPanSpeedCoeffFactor = pan_speed_vt;
                var friction = friction_vt;
            }
            switch(panoramas[id].type) {
                case 'image':
                    current_panorama_type = 'image';
                    $('#video_viewer').css('opacity',0);
                    $('#video_viewer').css('z-index',0);
                    if(id_room_alt!=null) {
                        var multires = panoramas[id].array_rooms_alt[id_room_alt].multires;
                        var multires_config = panoramas[id].array_rooms_alt[id_room_alt].multires_config;
                        if(panoramas[id].array_rooms_alt[id_room_alt].panorama_blob!='') {
                            var panorama_image = panoramas[id].array_rooms_alt[id_room_alt].panorama_blob;
                        } else {
                            var panorama_image = panoramas[id].array_rooms_alt[id_room_alt].panorama_image;
                        }
                        var stats = false;
                    } else {
                        var multires = panoramas[id].multires;
                        var multires_config = panoramas[id].multires_config;
                        if(panoramas[id].panorama_blob!='') {
                            var panorama_image = panoramas[id].panorama_blob;
                        } else {
                            var panorama_image = panoramas[id].panorama_image;
                        }
                        var stats = true;
                    }
                    if(multires) {
                        pano_viewer = pannellum.viewer('panorama_viewer', {
                            "id_room": panoramas[id].id,
                            "type": "multires",
                            "multiRes": multires_config,
                            "backgroundColor": panoramas[id].background_color,
                            "autoLoad": true,
                            "disableKeyboardCtrl": keyboard_mode,
                            "showZoomCtrl": false,
                            "showFullscreenCtrl": false,
                            "orientationOnByDefault": orientationOnByDefault,
                            "draggable": draggable,
                            "autoRotate": autorotate_speed_f,
                            "autoRotateStopDelay": 10,
                            "autoRotateInactivityDelay": autorotate_inactivity_f,
                            "friction": friction,
                            "touchPanSpeedCoeffFactor": touchPanSpeedCoeffFactor,
                            "compass": true,
                            "northOffset": parseInt(panoramas[id].northOffset),
                            "map_north": parseInt(panoramas[id].map_north),
                            "horizonPitch": parseInt(panoramas[id].h_pitch),
                            "horizonRoll": parseInt(panoramas[id].h_roll),
                            "pitch": pitch,
                            "yaw": workingYaw,
                            "multiResMinHfov": false,
                            "hfov": hfov_f,
                            "minHfov": min_hfov_f,
                            "maxHfov" : max_hfov_f,
                            "minPitch": minPitch,
                            "maxPitch" : maxPitch,
                            "minYaw": parseInt(panoramas[id].min_yaw),
                            "maxYaw" : parseInt(panoramas[id].max_yaw),
                            "haov": parseInt(panoramas[id].haov),
                            "vaov": parseInt(panoramas[id].vaov),
                            "hotSpots": panoramas[id].hotSpots,
                        });
                        setTimeout(function () {
                            player_initialized(cb,id,stats,id_room_alt,dh);
                            register_viewer_listeners(pano_viewer);
                        },100);
                    } else {
                        pano_viewer = pannellum.viewer('panorama_viewer', {
                            "id_room": panoramas[id].id,
                            "type": "equirectangular",
                            "panorama": panorama_image,
                            "backgroundColor": panoramas[id].background_color,
                            "autoLoad": true,
                            "disableKeyboardCtrl": keyboard_mode,
                            "showZoomCtrl": false,
                            "showFullscreenCtrl": false,
                            "orientationOnByDefault": orientationOnByDefault,
                            "draggable": draggable,
                            "autoRotate": autorotate_speed_f,
                            "autoRotateStopDelay": 10,
                            "autoRotateInactivityDelay": autorotate_inactivity_f,
                            "friction": friction,
                            "touchPanSpeedCoeffFactor": touchPanSpeedCoeffFactor,
                            "compass": true,
                            "northOffset": parseInt(panoramas[id].northOffset),
                            "map_north": parseInt(panoramas[id].map_north),
                            "horizonPitch": parseInt(panoramas[id].h_pitch),
                            "horizonRoll": parseInt(panoramas[id].h_roll),
                            "pitch": pitch,
                            "yaw": workingYaw,
                            "multiResMinHfov": false,
                            "hfov": hfov_f,
                            "minHfov": min_hfov_f,
                            "maxHfov" : max_hfov_f,
                            "minPitch": minPitch,
                            "maxPitch" : maxPitch,
                            "minYaw": parseInt(panoramas[id].min_yaw),
                            "maxYaw" : parseInt(panoramas[id].max_yaw),
                            "haov": parseInt(panoramas[id].haov),
                            "vaov": parseInt(panoramas[id].vaov),
                            "hotSpots": panoramas[id].hotSpots,
                        });
                        pano_viewer.on('load',function () {
                            setTimeout(function () {
                                player_initialized(cb,id,stats,id_room_alt,dh);
                                register_viewer_listeners(pano_viewer);
                            },50);
                        });
                    }
                    if(virtual_staging) {
                        var multires = panoramas[id].multires;
                        var multires_config = panoramas[id].multires_config;
                        if(panoramas[id].panorama_blob!='') {
                            var panorama_image = panoramas[id].panorama_blob;
                        } else {
                            var panorama_image = panoramas[id].panorama_image;
                        }
                        if(multires) {
                            config_alt = {
                                "id_room": panoramas[id].id,
                                "type": "multires",
                                "multiRes": multires_config,
                                "backgroundColor": panoramas[id].background_color,
                                "autoLoad": true,
                                "disableKeyboardCtrl": keyboard_mode,
                                "showZoomCtrl": false,
                                "showFullscreenCtrl": false,
                                "orientationOnByDefault": orientationOnByDefault,
                                "draggable": draggable,
                                "autoRotate": autorotate_speed_f,
                                "autoRotateStopDelay": 10,
                                "autoRotateInactivityDelay": autorotate_inactivity_f,
                                "friction": friction,
                                "touchPanSpeedCoeffFactor": touchPanSpeedCoeffFactor,
                                "compass": true,
                                "northOffset": parseInt(panoramas[id].northOffset),
                                "map_north": parseInt(panoramas[id].map_north),
                                "horizonPitch": parseInt(panoramas[id].h_pitch),
                                "horizonRoll": parseInt(panoramas[id].h_roll),
                                "pitch": pitch,
                                "yaw": workingYaw,
                                "multiResMinHfov": false,
                                "hfov": hfov_f,
                                "minHfov": min_hfov_f,
                                "maxHfov" : max_hfov_f,
                                "minPitch": minPitch,
                                "maxPitch" : maxPitch,
                                "minYaw": parseInt(panoramas[id].min_yaw),
                                "maxYaw" : parseInt(panoramas[id].max_yaw),
                                "haov": parseInt(panoramas[id].haov),
                                "vaov": parseInt(panoramas[id].vaov),
                                "hotSpots": panoramas[id].hotSpots_alt,
                            };
                        } else {
                            config_alt = {
                                "id_room": panoramas[id].id,
                                "type": "equirectangular",
                                "panorama": panorama_image,
                                "backgroundColor": panoramas[id].background_color,
                                "autoLoad": true,
                                "disableKeyboardCtrl": keyboard_mode,
                                "showZoomCtrl": false,
                                "showFullscreenCtrl": false,
                                "orientationOnByDefault": orientationOnByDefault,
                                "draggable": draggable,
                                "autoRotate": autorotate_speed_f,
                                "autoRotateStopDelay": 10,
                                "autoRotateInactivityDelay": autorotate_inactivity_f,
                                "friction": friction,
                                "touchPanSpeedCoeffFactor": touchPanSpeedCoeffFactor,
                                "compass": true,
                                "northOffset": parseInt(panoramas[id].northOffset),
                                "map_north": parseInt(panoramas[id].map_north),
                                "horizonPitch": parseInt(panoramas[id].h_pitch),
                                "horizonRoll": parseInt(panoramas[id].h_roll),
                                "pitch": pitch,
                                "yaw": workingYaw,
                                "multiResMinHfov": false,
                                "hfov": hfov_f,
                                "minHfov": min_hfov_f,
                                "maxHfov" : max_hfov_f,
                                "minPitch": minPitch,
                                "maxPitch" : maxPitch,
                                "minYaw": parseInt(panoramas[id].min_yaw),
                                "maxYaw" : parseInt(panoramas[id].max_yaw),
                                "haov": parseInt(panoramas[id].haov),
                                "vaov": parseInt(panoramas[id].vaov),
                                "hotSpots": panoramas[id].hotSpots_alt,
                            };
                        }
                    }
                    break;
                case 'video':
                    if(is_iOS()) {
                        try {
                            loader_p.reset();
                        } catch (e) {}
                        try {
                            video_p.remove();
                        } catch (e) {}
                        $("#canvas_p").empty();
                        current_panorama_type = 'video';
                        $('#video_viewer').css('opacity',0);
                        $('#video_viewer').css('z-index',0);
                        var setup_video_p = (loader, resources) => {
                            PIXI.utils.sayHello("WebGL");
                            app_p = new PIXI.Application({
                                antialias: false,
                                transparent: false,
                                resolution: 1,
                                width: resources.background.texture.width,
                                height: resources.background.texture.height
                            });
                            $("#canvas_p").append(app_p.view);
                            let bg = new PIXI.Sprite(resources.background.texture);
                            /*bg.anchor.y = 1;
                            bg.scale.y = -1;*/
                            app_p.stage.addChild(bg);
                            video_p = document.createElement('video');
                            video_p.id = 'video_viewer';
                            video_p.crossOrigin = 'anonymous';
                            video_p.preload = 'auto';
                            video_p.autoplay = true;
                            video_p.muted = true;
                            video_p.loop = true;
                            video_p.oncanplay = function() {
                                video_p.play();
                            };
                            video_p.setAttribute('playsinline','');
                            video_p.setAttribute('webkit-playsinline','');
                            video_p.src = panoramas[id].panorama_video;
                            const sprite = PIXI.Sprite.from(video_p);
                            /*sprite.anchor.y = 1;
                            sprite.scale.y = -1;*/
                            app_p.stage.addChild(sprite);
                            let canvas = $('#canvas_p canvas')[0];
                            pano_viewer = pannellum.viewer('panorama_viewer', {
                                "id_room": panoramas[id].id,
                                "type": "equirectangular",
                                "panorama": canvas,
                                "backgroundColor": panoramas[id].background_color,
                                "dynamic": true,
                                "dynamicUpdate": true,
                                "autoLoad": true,
                                "disableKeyboardCtrl": keyboard_mode,
                                "showZoomCtrl": false,
                                "showFullscreenCtrl": false,
                                "orientationOnByDefault": orientationOnByDefault,
                                "draggable": draggable,
                                "autoRotate": autorotate_speed_f,
                                "autoRotateStopDelay": 10,
                                "autoRotateInactivityDelay": autorotate_inactivity_f,
                                "friction": friction,
                                "touchPanSpeedCoeffFactor": touchPanSpeedCoeffFactor,
                                "compass": true,
                                "northOffset": parseInt(panoramas[id].northOffset),
                                "map_north": parseInt(panoramas[id].map_north),
                                "horizonPitch": parseInt(panoramas[id].h_pitch),
                                "horizonRoll": parseInt(panoramas[id].h_roll),
                                "pitch": pitch,
                                "yaw": workingYaw,
                                "multiResMinHfov": false,
                                "hfov": hfov_f,
                                "minHfov": min_hfov_f,
                                "maxHfov" : max_hfov_f,
                                "minPitch": minPitch,
                                "maxPitch" : maxPitch,
                                "minYaw": parseInt(panoramas[id].min_yaw),
                                "maxYaw" : parseInt(panoramas[id].max_yaw),
                                "haov": parseInt(panoramas[id].haov),
                                "vaov": parseInt(panoramas[id].vaov),
                                "hotSpots": panoramas[id].hotSpots,
                            });
                            setTimeout(function () {
                                player_initialized(cb,id,stats,null,dh);
                                pano_viewer.on('touchend',function() {
                                    if(panoramas[id].audio_track_enable) {
                                        video_p.muted = false;
                                    }
                                });
                                register_viewer_listeners(pano_viewer);
                                if(vr_enabled) {
                                    $("#canvas_p_vr").empty();
                                    $('#video_viewer_vr').show();
                                    PIXI.utils.sayHello("WebGL");
                                    app_p_vr = new PIXI.Application({
                                        antialias: false,
                                        transparent: false,
                                        resolution: 1,
                                        width: resources.background.texture.width,
                                        height: resources.background.texture.height
                                    });
                                    $("#canvas_p_vr").append(app_p_vr.view);
                                    let bg = new PIXI.Sprite(resources.background.texture);
                                    app_p_vr.stage.addChild(bg);
                                    const sprite = PIXI.Sprite.from(video_p);
                                    app_p_vr.stage.addChild(sprite);
                                    let canvas = $('#canvas_p canvas')[0];
                                    pano_viewer_vr = pannellum.viewer('panorama_viewer_vr', {
                                        "id_room": panoramas[id].id,
                                        "type": "equirectangular",
                                        "panorama": canvas,
                                        "dynamic": true,
                                        "dynamicUpdate": true,
                                        "autoLoad": true,
                                        "disableKeyboardCtrl": keyboard_mode,
                                        "showZoomCtrl": false,
                                        "showFullscreenCtrl": false,
                                        "orientationOnByDefault": orientationOnByDefault,
                                        "draggable": draggable,
                                        "autoRotate": autorotate_speed_f,
                                        "autoRotateStopDelay": 10,
                                        "autoRotateInactivityDelay": autorotate_inactivity_f,
                                        "friction": friction,
                                        "touchPanSpeedCoeffFactor": touchPanSpeedCoeffFactor,
                                        "compass": true,
                                        "northOffset": parseInt(panoramas[id].northOffset),
                                        "horizonPitch": parseInt(panoramas[id].h_pitch),
                                        "horizonRoll": parseInt(panoramas[id].h_roll),
                                        "pitch": pitch,
                                        "yaw": workingYaw,
                                        "hfov": hfov_f,
                                        "minHfov": min_hfov_f,
                                        "maxHfov" : max_hfov_f,
                                        "minPitch": minPitch,
                                        "maxPitch" : maxPitch,
                                        "minYaw": parseInt(panoramas[id].min_yaw),
                                        "maxYaw" : parseInt(panoramas[id].max_yaw),
                                        "haov": parseInt(panoramas[id].haov),
                                        "vaov": parseInt(panoramas[id].vaov),
                                        "hotSpots": panoramas[id].hotSpots_vr,
                                    });
                                    setTimeout(function () {
                                        $('#video_viewer_vr').animate({
                                            opacity: 1
                                        }, { duration: 250, queue: false });
                                        $('#video_viewer_vr').css('z-index',10);
                                        $('#panorama_viewer_vr').css('opacity',1);
                                        $('#background_pano').fadeOut();
                                        $('#background_pano_vr').fadeOut();
                                        $('.pnlm-controls-container').hide();
                                        $('.map').hide();
                                        $('#floatingSocialShare').hide();
                                        $('.custom-hotspot-content').css('opacity',0);
                                        $('.custom-hotspot-content').css('pointer-events','none');
                                        $('.pnlm-orientation-button').hide();
                                        $('.compass_control').hide();
                                        clearInterval(interval_position);
                                        interval_position = setInterval(function () {
                                            check_vr_pos();
                                        },250);
                                        setTimeout(function () {
                                            $('.loading_vr').fadeOut();
                                        },500);
                                    },200);
                                }
                            },200);
                        };
                        if(loader_p==null) loader_p = PIXI.Loader.shared;
                        loader_p.add("background", panoramas[id].panorama_image).load(setup_video_p);
                    } else {
                        var vh = window.innerHeight * 0.01;
                        document.documentElement.style.setProperty('--vh', `${vh}px`);
                        if(vr_enabled) {
                            $('#div_panoramas').append('<video playsinline webkit-playsinline id="video_viewer" class="video-js vjs-default-skin vjs-big-play-centered" style="display: none;position: absolute;top: 0;left: 0;width: 50%;height: calc(var(--vh, 1vh) * 100);opacity: 0" preload="none" crossorigin="anonymous"><source src="'+panoramas[id].panorama_video+'" type="video/mp4"/></video>');
                        } else {
                            $('#div_panoramas').append('<video playsinline webkit-playsinline id="video_viewer" class="video-js vjs-default-skin vjs-big-play-centered" style="display: none;position: absolute;top: 0;left: 0;width: 100%;height: calc(var(--vh, 1vh) * 100);opacity: 0" preload="none" crossorigin="anonymous"><source src="'+panoramas[id].panorama_video+'" type="video/mp4"/></video>');
                        }
                        current_panorama_type = 'video';
                        $('#video_viewer').show();
                        $('#panorama_viewer').css('opacity',0);
                        $('#panorama_viewer').css('z-index',0);
                        if(vr_enabled) {
                            var controls_video = false;
                        } else {
                            var controls_video = true;
                        }
                        video_viewer = videojs('video_viewer', {
                            loop: true,
                            autoload: true,
                            autoplay: true,
                            muted: true,
                            controls: controls_video,
                            inactivityTimeout: 0,
                            controlBar: {
                                fullscreenToggle: false,
                                pictureInPictureToggle: false
                            },
                            plugins: {
                                pannellum: {
                                    "id_room": panoramas[id].id,
                                    "autoLoad": true,
                                    "disableKeyboardCtrl": keyboard_mode,
                                    "showZoomCtrl": false,
                                    "showFullscreenCtrl": false,
                                    "orientationOnByDefault": orientationOnByDefault,
                                    "draggable": draggable,
                                    "autoRotate": autorotate_speed_f,
                                    "autoRotateStopDelay": 10,
                                    "autoRotateInactivityDelay": autorotate_inactivity_f,
                                    "friction": friction,
                                    "touchPanSpeedCoeffFactor": touchPanSpeedCoeffFactor,
                                    "compass": true,
                                    "northOffset": parseInt(panoramas[id].northOffset),
                                    "map_north": parseInt(panoramas[id].map_north),
                                    "horizonPitch": parseInt(panoramas[id].h_pitch),
                                    "horizonRoll": parseInt(panoramas[id].h_roll),
                                    "pitch": pitch,
                                    "yaw": workingYaw,
                                    "hfov": hfov_f,
                                    "minHfov": min_hfov_f,
                                    "maxHfov" : max_hfov_f,
                                    "minPitch": minPitch,
                                    "maxPitch" : maxPitch,
                                    "minYaw": parseInt(panoramas[id].min_yaw),
                                    "maxYaw" : parseInt(panoramas[id].max_yaw),
                                    "haov": parseInt(panoramas[id].haov),
                                    "vaov": parseInt(panoramas[id].vaov),
                                    "hotSpots": panoramas[id].hotSpots,
                                }
                            }
                        });
                        video_viewer.load();
                        video_viewer.on('ready', function() {
                            video_viewer.play();
                            var video_is_playing = false;
                            $(document).on("mousedown touchstart",function(e){
                                if(!video_is_playing) {
                                    var video_elem = document.getElementById('video_viewer_html5_api');
                                    video_elem.play();
                                    video_is_playing = true;
                                }
                            });
                            video_viewer.pnlmViewer.on('load',function () {
                                setTimeout(function () {
                                    player_initialized(cb,id,true,null,dh);
                                    register_viewer_listeners(video_viewer.pnlmViewer);
                                    if(vr_enabled) {
                                        var vh = window.innerHeight * 0.01;
                                        document.documentElement.style.setProperty('--vh', `${vh}px`);
                                        $('#div_panoramas').append('<video id="video_viewer_vr" class="video-js vjs-default-skin vjs-big-play-centered" style="display: none;position: absolute;width: 50%;top: 0;left: 50%;height: calc(var(--vh, 1vh) * 100);opacity: 0" muted preload="none" crossorigin="anonymous"><source src="'+panoramas[id].panorama_video+'" type="video/mp4"/></video>');
                                        $('#video_viewer_vr').show();
                                        video_viewer_vr = videojs('video_viewer_vr', {
                                            loop: true,
                                            autoload: true,
                                            plugins: {
                                                pannellum: {
                                                    "id_room": panoramas[id].id,
                                                    "autoLoad": true,
                                                    "disableKeyboardCtrl": keyboard_mode,
                                                    "showZoomCtrl": false,
                                                    "showFullscreenCtrl": false,
                                                    "orientationOnByDefault": orientationOnByDefault,
                                                    "draggable": draggable,
                                                    "autoRotate": autorotate_speed_f,
                                                    "autoRotateStopDelay": 10,
                                                    "autoRotateInactivityDelay": autorotate_inactivity_f,
                                                    "friction": friction,
                                                    "touchPanSpeedCoeffFactor": touchPanSpeedCoeffFactor,
                                                    "compass": true,
                                                    "northOffset": parseInt(panoramas[id].northOffset),
                                                    "horizonPitch": parseInt(panoramas[id].h_pitch),
                                                    "horizonRoll": parseInt(panoramas[id].h_roll),
                                                    "pitch": pitch,
                                                    "yaw": workingYaw,
                                                    "hfov": hfov_f,
                                                    "minHfov": min_hfov_f,
                                                    "maxHfov" : max_hfov_f,
                                                    "minPitch": minPitch,
                                                    "maxPitch" : maxPitch,
                                                    "minYaw": parseInt(panoramas[id].min_yaw),
                                                    "maxYaw" : parseInt(panoramas[id].max_yaw),
                                                    "haov": parseInt(panoramas[id].haov),
                                                    "vaov": parseInt(panoramas[id].vaov),
                                                    "hotSpots": panoramas[id].hotSpots_vr,
                                                }
                                            }
                                        });
                                        video_viewer_vr.load();
                                        video_viewer_vr.on('ready', function() {
                                            video_viewer.currentTime(0);
                                            video_viewer_vr.play();
                                            video_viewer.play();
                                            video_viewer_vr.pnlmViewer.on('load',function () {
                                                $('#video_viewer_vr').animate({
                                                    opacity: 1
                                                }, { duration: 250, queue: false });
                                                $('#video_viewer_vr').css('z-index',10);
                                                $('#background_pano').fadeOut();
                                                $('#background_pano_vr').fadeOut();
                                                $('.pnlm-controls-container').hide();
                                                $('.map').hide();
                                                $('#floatingSocialShare').hide();
                                                $('.custom-hotspot-content').css('opacity',0);
                                                $('.custom-hotspot-content').css('pointer-events','none');
                                                $('.pnlm-orientation-button').hide();
                                                $('.compass_control').hide();
                                                clearInterval(interval_position);
                                                interval_position = setInterval(function () {
                                                    check_vr_pos();
                                                },250);
                                                setTimeout(function () {
                                                    $('.loading_vr').fadeOut();
                                                },500);
                                            });
                                        });
                                    }
                                },50);
                            });
                        });
                    }
                    break;
                case 'hls':
                    try {
                        loader_p.reset();
                    } catch (e) {}
                    try {
                        video_p.remove();
                    } catch (e) {}
                    $("#canvas_p").empty();
                    current_panorama_type = 'hls';
                    $('#video_viewer').css('opacity',0);
                    $('#video_viewer').css('z-index',0);
                    var setup_video_p = (loader, resources) => {
                        PIXI.utils.sayHello("WebGL");
                        app_p = new PIXI.Application({
                            antialias: false,
                            transparent: false,
                            resolution: 1,
                            width: resources.background.texture.width,
                            height: resources.background.texture.height
                        });
                        $("#canvas_p").append(app_p.view);
                        let bg = new PIXI.Sprite(resources.background.texture);
                        app_p.stage.addChild(bg);
                        video_p = document.createElement('video');
                        video_p.id = 'video_viewer';
                        video_p.crossOrigin = 'anonymous';
                        video_p.preload = 'auto';
                        video_p.autoplay = true;
                        video_p.muted = true;
                        video_p.loop = true;
                        video_p.setAttribute('playsinline','');
                        video_p.setAttribute('webkit-playsinline','');
                        if (Hls.isSupported()) {
                            var hls = new Hls();
                            hls.loadSource(panoramas[id].panorama_url);
                            hls.attachMedia(video_p);
                            hls.on(Hls.Events.MANIFEST_PARSED,function() {
                                video_p.play();
                            });
                        } else if (video_p.canPlayType("application/vnd.apple.mpegurl")) {
                            video_p.src = panoramas[id].panorama_url;
                            video_p.addEventListener('loadedmetadata',function() {
                                video_p.play();
                            });
                        }
                        const sprite = PIXI.Sprite.from(video_p);
                        app_p.stage.addChild(sprite);
                        let canvas = $('#canvas_p canvas')[0];
                        pano_viewer = pannellum.viewer('panorama_viewer', {
                            "id_room": panoramas[id].id,
                            "type": "equirectangular",
                            "panorama": canvas,
                            "backgroundColor": panoramas[id].background_color,
                            "dynamic": true,
                            "dynamicUpdate": true,
                            "autoLoad": true,
                            "disableKeyboardCtrl": keyboard_mode,
                            "showZoomCtrl": false,
                            "showFullscreenCtrl": false,
                            "orientationOnByDefault": orientationOnByDefault,
                            "draggable": draggable,
                            "autoRotate": autorotate_speed_f,
                            "autoRotateStopDelay": 10,
                            "autoRotateInactivityDelay": autorotate_inactivity_f,
                            "friction": friction,
                            "touchPanSpeedCoeffFactor": touchPanSpeedCoeffFactor,
                            "compass": true,
                            "northOffset": parseInt(panoramas[id].northOffset),
                            "map_north": parseInt(panoramas[id].map_north),
                            "horizonPitch": parseInt(panoramas[id].h_pitch),
                            "horizonRoll": parseInt(panoramas[id].h_roll),
                            "pitch": pitch,
                            "yaw": workingYaw,
                            "multiResMinHfov": false,
                            "hfov": hfov_f,
                            "minHfov": min_hfov_f,
                            "maxHfov" : max_hfov_f,
                            "minPitch": minPitch,
                            "maxPitch" : maxPitch,
                            "minYaw": parseInt(panoramas[id].min_yaw),
                            "maxYaw" : parseInt(panoramas[id].max_yaw),
                            "haov": parseInt(panoramas[id].haov),
                            "vaov": parseInt(panoramas[id].vaov),
                            "hotSpots": panoramas[id].hotSpots,
                        });
                        setTimeout(function () {
                            player_initialized(cb,id,stats,null,dh);
                            pano_viewer.on('touchend',function() {
                                if(panoramas[id].audio_track_enable) {
                                    video_p.muted = false;
                                }
                            });
                            register_viewer_listeners(pano_viewer);
                            if(vr_enabled) {
                                try {
                                    loader_p_vr.reset();
                                } catch (e) {}
                                $("#canvas_p_vr").empty();
                                $('#video_viewer_vr').show();
                                PIXI.utils.sayHello("WebGL");
                                app_p_vr = new PIXI.Application({
                                    antialias: false,
                                    transparent: false,
                                    resolution: 1,
                                    width: resources.background.texture.width,
                                    height: resources.background.texture.height
                                });
                                $("#canvas_p_vr").append(app_p_vr.view);
                                let bg = new PIXI.Sprite(resources.background.texture);
                                app_p_vr.stage.addChild(bg);
                                const sprite = PIXI.Sprite.from(video_p);
                                app_p_vr.stage.addChild(sprite);
                                let canvas = $('#canvas_p canvas')[0];
                                pano_viewer_vr = pannellum.viewer('panorama_viewer_vr', {
                                    "id_room": panoramas[id].id,
                                    "type": "equirectangular",
                                    "panorama": canvas,
                                    "dynamic": true,
                                    "dynamicUpdate": true,
                                    "autoLoad": true,
                                    "disableKeyboardCtrl": keyboard_mode,
                                    "showZoomCtrl": false,
                                    "showFullscreenCtrl": false,
                                    "orientationOnByDefault": orientationOnByDefault,
                                    "draggable": draggable,
                                    "autoRotate": autorotate_speed_f,
                                    "autoRotateStopDelay": 10,
                                    "autoRotateInactivityDelay": autorotate_inactivity_f,
                                    "friction": friction,
                                    "touchPanSpeedCoeffFactor": touchPanSpeedCoeffFactor,
                                    "compass": true,
                                    "northOffset": parseInt(panoramas[id].northOffset),
                                    "horizonPitch": parseInt(panoramas[id].h_pitch),
                                    "horizonRoll": parseInt(panoramas[id].h_roll),
                                    "pitch": pitch,
                                    "yaw": workingYaw,
                                    "hfov": hfov_f,
                                    "minHfov": min_hfov_f,
                                    "maxHfov" : max_hfov_f,
                                    "minPitch": minPitch,
                                    "maxPitch" : maxPitch,
                                    "minYaw": parseInt(panoramas[id].min_yaw),
                                    "maxYaw" : parseInt(panoramas[id].max_yaw),
                                    "haov": parseInt(panoramas[id].haov),
                                    "vaov": parseInt(panoramas[id].vaov),
                                    "hotSpots": panoramas[id].hotSpots_vr,
                                });
                                setTimeout(function () {
                                    $('#video_viewer_vr').animate({
                                        opacity: 1
                                    }, { duration: 250, queue: false });
                                    $('#video_viewer_vr').css('z-index',10);
                                    $('#panorama_viewer_vr').css('opacity',1);
                                    $('#background_pano').fadeOut();
                                    $('#background_pano_vr').fadeOut();
                                    $('.pnlm-controls-container').hide();
                                    $('.map').hide();
                                    $('#floatingSocialShare').hide();
                                    $('.custom-hotspot-content').css('opacity',0);
                                    $('.custom-hotspot-content').css('pointer-events','none');
                                    $('.pnlm-orientation-button').hide();
                                    $('.compass_control').hide();
                                    clearInterval(interval_position);
                                    interval_position = setInterval(function () {
                                        check_vr_pos();
                                    },250);
                                    setTimeout(function () {
                                        $('.loading_vr').fadeOut();
                                    },500);
                                },200);
                            }
                        },200);
                    };
                    if(loader_p==null) loader_p = PIXI.Loader.shared;
                    loader_p.add("background", panoramas[id].panorama_image).load(setup_video_p);
                    break;
                case 'lottie':
                    var img_lottie = new Image();
                    img_lottie.onload = function() {
                        var canvas = document.createElement('canvas');
                        canvas.width = this.width;
                        canvas.height = this.height;
                        var lottie_context = canvas.getContext('2d');
                        lottie_context.drawImage(img_lottie, 0, 0);
                        var lottie_pano = bodymovin.loadAnimation({
                            renderer: 'canvas',
                            loop: true,
                            autoplay: true,
                            path: panoramas[id].panorama_json,
                            rendererSettings: {
                                context: lottie_context,
                                progressiveLoad: true,
                            }
                        });
                        pano_viewer = pannellum.viewer('panorama_viewer', {
                            "id_room": panoramas[id].id,
                            "type": "equirectangular",
                            "panorama": canvas,
                            "backgroundColor": panoramas[id].background_color,
                            "dynamic": true,
                            "dynamicUpdate": true,
                            "autoLoad": true,
                            "disableKeyboardCtrl": keyboard_mode,
                            "showZoomCtrl": false,
                            "showFullscreenCtrl": false,
                            "orientationOnByDefault": orientationOnByDefault,
                            "draggable": draggable,
                            "autoRotate": autorotate_speed_f,
                            "autoRotateStopDelay": 10,
                            "autoRotateInactivityDelay": autorotate_inactivity_f,
                            "friction": friction,
                            "touchPanSpeedCoeffFactor": touchPanSpeedCoeffFactor,
                            "compass": true,
                            "northOffset": parseInt(panoramas[id].northOffset),
                            "map_north": parseInt(panoramas[id].map_north),
                            "horizonPitch": parseInt(panoramas[id].h_pitch),
                            "horizonRoll": parseInt(panoramas[id].h_roll),
                            "pitch": pitch,
                            "yaw": workingYaw,
                            "multiResMinHfov": false,
                            "hfov": hfov_f,
                            "minHfov": min_hfov_f,
                            "maxHfov" : max_hfov_f,
                            "minPitch": minPitch,
                            "maxPitch" : maxPitch,
                            "minYaw": parseInt(panoramas[id].min_yaw),
                            "maxYaw" : parseInt(panoramas[id].max_yaw),
                            "haov": parseInt(panoramas[id].haov),
                            "vaov": parseInt(panoramas[id].vaov),
                            "hotSpots": panoramas[id].hotSpots,
                        });
                        setTimeout(function () {
                            player_initialized(cb,id,stats,null,dh);
                            register_viewer_listeners(pano_viewer);
                        },200);
                    }
                    img_lottie.src = panoramas[id].panorama_image;
                    break;
            }

            if(vr_enabled) {
                switch(panoramas[id].type) {
                    case 'image':
                        if(panoramas[id].multires) {
                            pano_viewer_vr = pannellum.viewer('panorama_viewer_vr', {
                                "id_room": panoramas[id].id,
                                "type": "multires",
                                "multiRes": panoramas[id].multires_config,
                                "backgroundColor": panoramas[id].background_color,
                                "autoLoad": true,
                                "disableKeyboardCtrl": keyboard_mode,
                                "showZoomCtrl": false,
                                "showFullscreenCtrl": false,
                                "orientationOnByDefault": orientationOnByDefault,
                                "draggable": draggable,
                                "autoRotate": autorotate_speed_f,
                                "autoRotateStopDelay": 10,
                                "autoRotateInactivityDelay": autorotate_inactivity_f,
                                "compass": true,
                                "northOffset": panoramas[id].northOffset,
                                "horizonPitch": parseInt(panoramas[id].h_pitch),
                                "horizonRoll": parseInt(panoramas[id].h_roll),
                                "pitch": pitch,
                                "yaw": workingYaw,
                                "multiResMinHfov": false,
                                "hfov": hfov_f,
                                "minHfov": min_hfov_f,
                                "maxHfov" : max_hfov_f,
                                "minPitch": minPitch,
                                "maxPitch" : maxPitch,
                                "minYaw": parseInt(panoramas[id].min_yaw),
                                "maxYaw" : parseInt(panoramas[id].max_yaw),
                                "haov": parseInt(panoramas[id].haov),
                                "vaov": parseInt(panoramas[id].vaov),
                                "hotSpots": panoramas[id].hotSpots_vr,
                            });
                            setTimeout(function () {
                                apply_room_filters(panoramas[id].filters,'vr');
                                $('#panorama_viewer_vr').animate({
                                    opacity: 1
                                }, { duration: 250, queue: false });
                                $('#panorama_viewer_vr').css('z-index',10);
                                $('#background_pano').fadeOut();
                                $('#background_pano_vr').fadeOut();
                                $('.pnlm-controls-container').hide();
                                $('.map').hide();
                                $('#floatingSocialShare').hide();
                                $('.custom-hotspot-content').css('opacity',0);
                                $('.custom-hotspot-content').css('pointer-events','none');
                                $('.pnlm-orientation-button').hide();
                                $('.compass_control').hide();
                                clearInterval(interval_position);
                                interval_position = setInterval(function () {
                                    check_vr_pos();
                                },250);
                                setTimeout(function () {
                                    $('.loading_vr').fadeOut();
                                },500);
                            },200);
                        } else {
                            pano_viewer_vr = pannellum.viewer('panorama_viewer_vr', {
                                "id_room": panoramas[id].id,
                                "type": "equirectangular",
                                "panorama": panoramas[id].panorama_image,
                                "backgroundColor": panoramas[id].background_color,
                                "autoLoad": true,
                                "disableKeyboardCtrl": keyboard_mode,
                                "showZoomCtrl": false,
                                "showFullscreenCtrl": false,
                                "orientationOnByDefault": orientationOnByDefault,
                                "draggable": draggable,
                                "autoRotate": autorotate_speed_f,
                                "autoRotateStopDelay": 10,
                                "autoRotateInactivityDelay": autorotate_inactivity_f,
                                "compass": true,
                                "northOffset": panoramas[id].northOffset,
                                "horizonPitch": parseInt(panoramas[id].h_pitch),
                                "horizonRoll": parseInt(panoramas[id].h_roll),
                                "pitch": pitch,
                                "yaw": workingYaw,
                                "multiResMinHfov": false,
                                "hfov": hfov_f,
                                "minHfov": min_hfov_f,
                                "maxHfov" : max_hfov_f,
                                "minPitch": minPitch,
                                "maxPitch" : maxPitch,
                                "minYaw": parseInt(panoramas[id].min_yaw),
                                "maxYaw" : parseInt(panoramas[id].max_yaw),
                                "haov": parseInt(panoramas[id].haov),
                                "vaov": parseInt(panoramas[id].vaov),
                                "hotSpots": panoramas[id].hotSpots_vr,
                            });
                            pano_viewer_vr.on('load',function () {
                                apply_room_filters(panoramas[id].filters,'vr');
                                $('#panorama_viewer_vr').animate({
                                    opacity: 1
                                }, { duration: 250, queue: false });
                                $('#panorama_viewer_vr').css('z-index',10);
                                $('#background_pano').fadeOut();
                                $('#background_pano_vr').fadeOut();
                                $('.pnlm-controls-container').hide();
                                $('.map').hide();
                                $('#floatingSocialShare').hide();
                                $('.custom-hotspot-content').css('opacity',0);
                                $('.custom-hotspot-content').css('pointer-events','none');
                                $('.pnlm-orientation-button').hide();
                                $('.compass_control').hide();
                                clearInterval(interval_position);
                                interval_position = setInterval(function () {
                                    check_vr_pos();
                                },250);
                                setTimeout(function () {
                                    $('.loading_vr').fadeOut();
                                },500);
                            });
                        }
                        break;
                    case 'lottie':
                        var img_lottie = new Image();
                        img_lottie.onload = function() {
                            var canvas = document.createElement('canvas');
                            canvas.width = this.width;
                            canvas.height = this.height;
                            var lottie_context = canvas.getContext('2d');
                            lottie_context.drawImage(img_lottie, 0, 0);
                            var lottie_pano = bodymovin.loadAnimation({
                                renderer: 'canvas',
                                loop: true,
                                autoplay: true,
                                path: panoramas[id].panorama_json,
                                rendererSettings: {
                                    context: lottie_context,
                                    progressiveLoad: true,
                                }
                            });
                            pano_viewer_vr = pannellum.viewer('panorama_viewer_vr', {
                                "id_room": panoramas[id].id,
                                "type": "equirectangular",
                                "panorama": canvas,
                                "dynamic": true,
                                "dynamicUpdate": true,
                                "autoLoad": true,
                                "disableKeyboardCtrl": keyboard_mode,
                                "showZoomCtrl": false,
                                "showFullscreenCtrl": false,
                                "orientationOnByDefault": orientationOnByDefault,
                                "draggable": draggable,
                                "autoRotate": autorotate_speed_f,
                                "autoRotateStopDelay": 10,
                                "autoRotateInactivityDelay": autorotate_inactivity_f,
                                "friction": friction,
                                "touchPanSpeedCoeffFactor": touchPanSpeedCoeffFactor,
                                "compass": true,
                                "northOffset": parseInt(panoramas[id].northOffset),
                                "horizonPitch": parseInt(panoramas[id].h_pitch),
                                "horizonRoll": parseInt(panoramas[id].h_roll),
                                "pitch": pitch,
                                "yaw": workingYaw,
                                "hfov": hfov_f,
                                "minHfov": min_hfov_f,
                                "maxHfov" : max_hfov_f,
                                "minPitch": minPitch,
                                "maxPitch" : maxPitch,
                                "minYaw": parseInt(panoramas[id].min_yaw),
                                "maxYaw" : parseInt(panoramas[id].max_yaw),
                                "haov": parseInt(panoramas[id].haov),
                                "vaov": parseInt(panoramas[id].vaov),
                                "hotSpots": panoramas[id].hotSpots_vr,
                            });
                            setTimeout(function () {
                                $('#panorama_viewer_vr').animate({
                                    opacity: 1
                                }, { duration: 250, queue: false });
                                $('#panorama_viewer_vr').css('z-index',10);
                                $('#background_pano').fadeOut();
                                $('#background_pano_vr').fadeOut();
                                $('.pnlm-controls-container').hide();
                                $('.map').hide();
                                $('#floatingSocialShare').hide();
                                $('.custom-hotspot-content').css('opacity',0);
                                $('.custom-hotspot-content').css('pointer-events','none');
                                $('.pnlm-orientation-button').hide();
                                $('.compass_control').hide();
                                clearInterval(interval_position);
                                interval_position = setInterval(function () {
                                    check_vr_pos();
                                },250);
                                setTimeout(function () {
                                    $('.loading_vr').fadeOut();
                                },500);
                            },200);
                        };
                        img_lottie.src = panoramas[id].panorama_image;
                        break;
                }
            }
        },0);
    }

    var drag_v = false, start_drag_v, end_drag_v;
    function register_viewer_listeners(viewer) {
        document.addEventListener('mousedown', function() {
            start_drag_v = new Date().getTime();
            drag_v = false;
        });
        document.addEventListener('mousemove', function(event) {
            end_drag_v = new Date().getTime();
            drag_v = true;
            if(click_anywhere==1 && hide_markers==1 && hover_markers==1) {
                viewer_move_listener(event);
            }
        });
        viewer.on('mouseup', viewer_click_listener);
    }

    function viewer_click_listener(event) {
        var diff_drag_v = end_drag_v - start_drag_v;
        if(drag_v == false || diff_drag_v < 100) {
            if(click_anywhere==1) {
                if(event.target.className!='pnlm-dragfix') return;
                if(current_panorama_type=='image' || is_iOS() || current_panorama_type=='hls' || current_panorama_type=='lottie') {
                    var coords = pano_viewer.mouseEventToCoords(event);
                } else {
                    var coords = video_viewer.pnlmViewer.mouseEventToCoords(event);
                }
                try {
                    var pitch = parseFloat(coords[0]);
                    var yaw = parseFloat(coords[1]);
                    find_nearest_marker(yaw,pitch);
                } catch (e) {}
            }
            if(window.poi_box_open) {
                restart_autorotate();
            }
            close_all_poi_box();
        }
    }

    function viewer_move_listener(event) {
        if(click_anywhere==1) {
            if(current_panorama_type=='image' || is_iOS() || current_panorama_type=='hls' || current_panorama_type=='lottie') {
                var coords = pano_viewer.mouseEventToCoords(event);
            } else {
                var coords = video_viewer.pnlmViewer.mouseEventToCoords(event);
            }
            try {
                var pitch = parseFloat(coords[0]);
                var yaw = parseFloat(coords[1]);
                view_nearest_marker(yaw,pitch);
            } catch (e) {}
        }
    }

    function close_all_poi_box() {
        $('.box_poi').css({'opacity':0,'pointer-events':'none'});
        window.poi_box_open = false;
        $('.tooltip_text').removeClass('hidden_pb');
        $('.box_poi video').each(function () {
            $(this).trigger('pause');
        });
        $('.box_poi .youtube_video, .box_poi .vimeo_video').each(function () {
            var src = $(this).attr('src');
            $(this).attr('src',src);
        });
    }

    function find_nearest_marker(yaw,pitch) {
        var index = get_id_viewer(current_id_panorama);
        var array_coord = [];
        jQuery.each(panoramas[index].hotSpots, function(index, hotspot) {
            if(hotspot.object=='marker' || hotspot.object=='marker_embed') {
                var h_yaw = parseFloat(hotspot.yaw);
                var h_pitch = parseFloat(hotspot.pitch);
                var dist = Math.abs(yaw % 360 - h_yaw % 360);
                dist = Math.min(dist, 360 - dist);
                var dist_coord = Math.sqrt(Math.pow(h_yaw - yaw, 2) + Math.pow(h_pitch - pitch, 2));
                if(dist<=20) {
                    array_coord.push({x:h_yaw,y:h_pitch,dist_coord:dist_coord,hotspot:hotspot});
                }
            }
        });
        if(array_coord.length>0) {
            array_coord.sort((a, b) => a.dist_coord - b.dist_coord);
            goto('',array_coord[0].hotspot.clickHandlerArgs)
        }
    }

    function view_nearest_marker(yaw,pitch) {
        var index = get_id_viewer(current_id_panorama);
        var array_coord = [];
        jQuery.each(panoramas[index].hotSpots, function(index, hotspot) {
            if(hotspot.object=='marker' || hotspot.object=='marker_embed') {
                var h_yaw = parseFloat(hotspot.yaw);
                var h_pitch = parseFloat(hotspot.pitch);
                var id = hotspot.id;
                $('.marker_embed_'+id).addClass('hidden_m');
                $('.marker_'+id).addClass('hidden_m');
                var dist = Math.abs(yaw % 360 - h_yaw % 360);
                dist = Math.min(dist, 360 - dist);
                var dist_coord = Math.sqrt(Math.pow(h_yaw - yaw, 2) + Math.pow(h_pitch - pitch, 2));
                if(dist<=20) {
                    array_coord.push({x:h_yaw,y:h_pitch,dist_coord:dist_coord,id:id});
                }
            }
        });
        if(array_coord.length>0) {
            array_coord.sort((a, b) => a.dist_coord - b.dist_coord);
            $('.marker_'+array_coord[0].id).removeClass('hidden_m');
            $('.marker_embed_'+array_coord[0].id).removeClass('hidden_m');
        }
    }

    window.apply_room_filters = function(filters,w) {
        $('#background_pano').css('filter','none');
        if(filters!='') {
            filters = JSON.parse(filters);
            var filter = '';
            if(filters.brightness!=100) {
                filter += 'brightness('+filters.brightness+'%) ';
            }
            if(filters.contrast!=100) {
                filter += 'contrast('+filters.contrast+'%) ';
            }
            if(filters.saturate!=100) {
                filter += 'saturate('+filters.saturate+'%) ';
            }
            if(filters.grayscale!=0) {
                filter += 'grayscale('+filters.grayscale+'%) ';
            }
            switch(w) {
                case 'pano':
                    if(current_panorama_type=='image' || is_iOS() || current_panorama_type=='hls' || current_panorama_type=='lottie') {
                        var canvas = pano_viewer.getRenderer().getCanvas();
                    } else {
                        var canvas = video_viewer.pnlmViewer.getRenderer().getCanvas();
                    }
                    canvas.style.filter = filter;
                    break;
                case 'vr':
                    if(current_panorama_type=='image' || is_iOS() || current_panorama_type=='hls' || current_panorama_type=='lottie') {
                        var canvas_vr = pano_viewer_vr.getRenderer().getCanvas();
                    } else {
                        var canvas_vr = video_viewer_vr.pnlmViewer.getRenderer().getCanvas();
                    }
                    canvas_vr.style.filter = filter;
                    break;
            }
            $('#background_pano').css('filter',filter);
        }
    }

    function init_vs_drag() {
        $("#vs_grab").draggable({
            axis: "x",
            drag: function( event, ui ) {
                var width = $("#vt_container").width();
                var margin_left = width*0.05;
                var margin_right = width*0.95;
                if(ui.position.left<=margin_left) ui.position.left=margin_left;
                if(ui.position.left>=margin_right) ui.position.left=margin_right;
                var grab_width = $('#vs_grab').width();
                var slider_width = $('#vs_slider').width();
                var pos_left = ui.position.left+(grab_width/2)-(slider_width/2);
                var perc_left = 100 - (((width - pos_left) / width) * 100);
                $('#vs_slider').css('left',perc_left+'%');
                $('#vs_before').css('width',perc_left+'%');
            }
        });
    }

    function initialize_virtual_staging(id,id_room_alt) {
        window.sync_virtual_staging_enabled=false;
        if(id_room_alt!=null && virtual_staging) {
            $('#vs_before').show();
            $('#vs_slider').fadeIn();
            $('#vs_grab').fadeIn();
            init_vs_drag();
            $('#panorama_viewer_alt').css('z-index',0);
            $('#panorama_viewer_alt').css('opacity',0);
            pano_viewer_alt = pannellum.viewer('panorama_viewer_alt', config_alt);
            var multires = panoramas[id].multires;
            if(multires) {
                setTimeout(function () {
                    sync_virtual_staging(id);
                },200)
            } else {
                pano_viewer_alt.on('load',function () {
                    sync_virtual_staging(id);
                });
            }
        } else {
            $('#vs_before').hide();
            $('#vs_slider').fadeOut();
            $('#vs_grab').fadeOut();
        }
    }

    function sync_virtual_staging(id) {
        $('.pnlm-controls-container').hide();
        $('#panorama_viewer').css('z-index',10);
        $('#panorama_viewer').css('opacity',1);
        $('#panorama_viewer_alt').css('z-index',10);
        $('#panorama_viewer_alt').css('opacity',1);
        if(panoramas[id].transition_override==1) {
            var transition_fadeout_r = panoramas[id].transition_fadeout;
            var transition_effect_r = panoramas[id].transition_effect;
        } else {
            var transition_fadeout_r = transition_fadeout;
            var transition_effect_r = transition_effect;
        }
        switch (transition_effect_r) {
            case 'drop':
                var effect_options = {direction: "down"};
                break;
            case 'blind':
                var effect_options = {direction: "down"};
                break;
            case 'scale':
                var effect_options = {percent: 10};
                break;
            default:
                var effect_options = {};
                break;
        }
        $('#background_pano').effect(transition_effect_r, effect_options, transition_fadeout_r,function () {
            var filters = $('#background_pano').css('filter');
            $('#background_pano').removeAttr('style');
            $('#background_pano').css('filter',filters);
            $('#background_pano').css('z-index',0);
            if(!panoramas[id].protected) {
                var filters = $('#background_pano').css('filter');
                filters = filters.replace('blur(16px)','');
                $('#background_pano').css('filter',filters);
            }
        });
        if (!controls_status['icons']) {
            $('.custom-hotspot').addClass('hidden_icons');
            $('.custom-hotspot img').addClass('hidden_icons');
            $('.custom-hotspot-content').addClass('hidden_icons');
            $('.poi_embed').addClass('hidden_icons');
            $('.marker_embed').addClass('hidden_icons');
        } else {
            $('.custom-hotspot').removeClass('hidden_icons');
            $('.custom-hotspot img').removeClass('hidden_icons');
            $('.custom-hotspot-content').removeClass('hidden_icons');
            $('.poi_embed').removeClass('hidden_icons');
            $('.marker_embed').removeClass('hidden_icons');
        }
        window.sync_virtual_staging_enabled=true;
    }

    window.sync_virtual_staging_view = function () {
        var width = $("#vt_container").width();
        $('#panorama_viewer_alt').css('width',width+'px');
        var yaw = parseFloat(pano_viewer.getYaw());
        var pitch = parseFloat(pano_viewer.getPitch());
        var hfov = parseFloat(pano_viewer.getHfov());
        try {
            pano_viewer_alt.lookAt(pitch,yaw,hfov,false);
        } catch (e) {}
    }

    function player_initialized(cb,id,stats,id_room_alt,dh) {
        if(!controls_status['auto_rotate']) {
            if(current_panorama_type=='image' || is_iOS() || current_panorama_type=='hls' || current_panorama_type=='lottie') {
                pano_viewer.stopAutoRotate();
            } else {
                video_viewer.pnlmViewer.stopAutoRotate();
            }
        }
        initialize_virtual_staging(id,id_room_alt);
        if(schedule_enabled) {
            check_pois_schedule(panoramas[id].id);
            clearInterval(interval_check_pois_schedule);
            interval_check_pois_schedule = setInterval(function () {
                check_pois_schedule(panoramas[id].id);
            },60000);
        }
        view_protect_form(cb,id);
        if(map_tour_l!=null) {
            select_map_tour_point();
        }
        if(cb) {
            adjust_ratio_hfov();
            if(current_panorama_type=='image' || is_iOS() || current_panorama_type=='hls' || current_panorama_type=='lottie') {
                if(id_room_alt==null || virtual_staging==false) {
                    $('#panorama_viewer').css('z-index',10);
                    $('#panorama_viewer').css('opacity',1);
                }
            } else {
                $('#video_viewer').css('z-index',10);
                $('#video_viewer').css('opacity',1);
            }
            if(!vr_enabled) {
                if(id_room_alt==null || virtual_staging==false) {
                    if(panoramas[id].transition_override==1) {
                        var transition_fadeout_r = panoramas[id].transition_fadeout;
                        var transition_effect_r = panoramas[id].transition_effect;
                    } else {
                        var transition_fadeout_r = transition_fadeout;
                        var transition_effect_r = transition_effect;
                    }
                    switch (transition_effect_r) {
                        case 'drop':
                            var effect_options = {direction: "down"};
                            break;
                        case 'scale':
                            var effect_options = {percent: 10};
                            break;
                        default:
                            var effect_options = {};
                            break;
                    }
                    if(window.changed_room_alt) {
                        transition_effect_r = 'fade';
                        transition_fadeout_r = 0;
                    }
                    if(window.changed_room_alt_poi) {
                        transition_effect_r = 'fade';
                        transition_fadeout_r = 0;
                    }
                    if(dh) {
                        transition_effect_r = 'fade';
                        transition_fadeout_r = 0;
                    }
                    $('#background_pano').effect(transition_effect_r, effect_options, transition_fadeout_r,function () {
                        var filters = $('#background_pano').css('filter');
                        $('#background_pano').removeAttr('style');
                        $('#background_pano').css('filter',filters);
                        $('#background_pano').css('z-index',0);
                        if(!panoramas[id].protected) {
                            var filters = $('#background_pano').css('filter');
                            filters = filters.replace('blur(16px)','');
                            $('#background_pano').css('filter',filters);
                        }
                    });
                }
            }
            if(array_maps.length>0) {
                if(controls_status['map']) {
                    $('.all_maps').hide();
                    $('.pointer').hide();
                    var id_map_target = panoramas[id].id_map;
                    if(!isNaN(id_map_target)) {
                        id_current_map = id_map_target;
                        $('.map_'+id_map_target).show();
                        $('.pointer_map_'+id_map_target).show();
                    } else {
                        $('.map_'+id_current_map).show();
                        $('.pointer_map_'+id_current_map).show();
                    }
                    resize_maps();
                }
            }
            if(panoramas[id].visible_list) {
                slider_index = $('.pointer_list_'+panoramas[id].id).attr('data-index_id');
                try {
                    if(auto_show_slider!=2) sly.toCenter(slider_index);
                } catch (e) {}
            }
            var annotation_title = panoramas[id].annotation_title;
            var annotation_description = panoramas[id].annotation_description;
            if(((annotation_title!='') || (annotation_description!='')) && (show_annotations)) {
                var a_both = 0;
                if(annotation_title!='') {
                    $('.annotation_title').html(annotation_title);
                    a_both++;
                }
                if(annotation_description!='') {
                    $('.annotation_description').html(annotation_description);
                    a_both++;
                }
                if(a_both==2) {
                    $('.annotation hr').show();
                } else {
                    $('.annotation hr').hide();
                }
                $('.annotation').show();
            } else {
                $('.annotation').hide();
            }
            audio_player_room.pause();
            song_bg_volume_sel = 1.0;
            if(controls_status['song']) {
                set_audio_volume(1.0);
            }
            if(panoramas[id].audio_track_enable) {
                unmute_audio(true,true);
                set_audio_volume(panoramas[id].song_bg_volume);
                song_bg_volume_sel = panoramas[id].song_bg_volume;
            }
            if(panoramas[id].song!='') {
                mute_audio(true,true);
                audio_player_room.src = "content/"+encodeURIComponent(panoramas[id].song);
                audio_player_room.load();
                audio_player_room.play();
                if(is_iOS()) {
                    audio_player_room.muted = false;
                } else {
                    audio_player_room.volume = 1;
                }
                set_audio_volume(panoramas[id].song_bg_volume);
                song_bg_volume_sel = panoramas[id].song_bg_volume;
            }
            if(!controls_status['song']) {
                mute_audio(true,true);
            }
            if(!vr_enabled) {
                var poi_embed_count = $('.poi_embed').length;
                if(poi_embed_count>0) {
                    init_poi_embed();
                }
            }
            var marker_embed_count = $('.marker_embed').length;
            if(marker_embed_count>0) {
                init_marker_embed();
            }
        }
        $('.panorama').css('z-index','unset');
        poi_open = false;
        if (!controls_status['icons']) {
            $('.custom-hotspot').addClass('hidden_icons');
            $('.custom-hotspot img').addClass('hidden_icons');
            $('.custom-hotspot-content').addClass('hidden_icons');
            $('.marker_embed').addClass('hidden_icons');
            if(!controls_status['presentation']) {
                $('.poi_embed').addClass('hidden_icons');
            }
        } else {
            $('.custom-hotspot').removeClass('hidden_icons');
            $('.custom-hotspot img').removeClass('hidden_icons');
            $('.custom-hotspot-content').removeClass('hidden_icons');
            $('.marker_embed').removeClass('hidden_icons');
            $('.poi_embed').removeClass('hidden_icons');
            if(vr_enabled) {
                $('.custom-hotspot-content').addClass('hidden_icons');
            } else {
                $('.custom-hotspot-content').removeClass('hidden_icons');
            }
            setTimeout(function () {
                if(current_panorama_type=='image' || is_iOS() || current_panorama_type=='hls' || current_panorama_type=='lottie') {
                    pano_viewer.resize();
                } else {
                    video_viewer.pnlmViewer.resize();
                }
            },50);
        }
        if(show_compass && !vr_enabled) {
            $('.compass_control').css('display','inline-block');
        } else {
            $('.compass_control').hide();
        }
        if(controls_status['presentation']) {
            if(presentation_type=='automatic') {
                automatic_presentation_steps(id);
            } else {
                if(current_panorama_type=='image' || is_iOS() || current_panorama_type=='hls' || current_panorama_type=='lottie') {
                    pano_viewer.stopAutoRotate();
                } else {
                    video_viewer.pnlmViewer.stopAutoRotate();
                }
            }
            $('.compass_control').hide();
            $('.pnlm-dragfix').css('pointer-events','none');
        }
        if(!vr_enabled) {
            $('#loading_pano').css('opacity',0);
            $('#loading_pano').hide();
            $('.pointer_'+panoramas[id].id+' .view_direction__arrow').show();
            if(!live_session_connected) {
                clearInterval(interval_position);
                interval_position = setInterval(function () {
                    check_pano_pos();
                },500);
            }
        }
        init_effect(panoramas[id].effect);
        if($('.list_slider').css('opacity')==1) {
            reposition_bottom_controls(true,false,0);
        } else {
            reposition_bottom_controls(false,false,0);
        }
        $('.pnlm-orientation-button').hide();
        $('#compass_icon').attr('onclick','set_initial_pos('+id+');');
        if(!controls_status['presentation'] && stats && !window.changed_room_alt && !window.changed_room_alt_poi) {
            if(window.export_mode==0 && window.preview==0) {
                $.ajax({
                    url: "ajax/set_statistics.php",
                    type: "POST",
                    data: {
                        type: 'room',
                        id: panoramas[id].id,
                        ip_visitor: window.ip_visitor
                    },
                    async: true
                });
            }
        }
        window.changed_room_alt = false;
        window.changed_room_alt_poi = false;
        if(!live_session_connected && !vr_enabled && !controls_status['presentation']) {
            if(panoramas[id].id_poi_autoopen!=null) {
                setTimeout(function () {
                    $('.hotspot_'+panoramas[id].id_poi_autoopen).trigger('click');
                    $('.hotspot_'+panoramas[id].id_poi_autoopen+' a').trigger('click');
                    try { $('.hotspot_'+panoramas[id].id_poi_autoopen)[0].dispatchEvent(event_simulate_click); } catch (e) {}
                    try { $('.hotspot_'+panoramas[id].id_poi_autoopen+' a')[0].dispatchEvent(event_simulate_click); } catch (e) {}
                },600);
            }
        }
        adjust_elements_positions();
        if(stats) {
            clearInterval(interval_access_time_avg);
            access_time_avg = 0;
            access_time_id = panoramas[id].id;
            interval_access_time_avg = setInterval(function () {
                access_time_avg = access_time_avg + 1;
            }, 500);
        }
        try {
            var pointer_color_active = Number("0x"+array_dollhouse.settings.pointer_color_active);
            var pointer_color = Number("0x"+array_dollhouse.settings.pointer_color);
            for(var i=0; i<pointers_c_dollhouse.length; i++) {
                var id_dh = pointers_c_dollhouse[i].userData.id;
                if(panoramas[id].id==id_dh) {
                    pointers_c_dollhouse[i].material.color.setHex(pointer_color_active);
                    pointers_t_dollhouse[i].material.color.setHex(pointer_color_active);
                } else {
                    pointers_c_dollhouse[i].material.color.setHex(pointer_color);
                    pointers_t_dollhouse[i].material.color.setHex(pointer_color);
                }
            }
        } catch (e) {}
        if(!dollhouse_open) {
            set_dollhouse_position(panoramas[id].id);
        }
        $('.pnlm-container').focus();
        $('.pnlm-container').trigger('click');
        if(live_session_connected) {
            clearInterval(interval_live_session);
            interval_live_session = setInterval(function () {
                if(current_panorama_type=='image' || is_iOS() || current_panorama_type=='hls' || current_panorama_type=='lottie') {
                    var yaw = parseFloat(pano_viewer.getYaw());
                    var pitch = parseFloat(pano_viewer.getPitch());
                    var hfov = parseFloat(pano_viewer.getHfov());
                } else {
                    var yaw = parseFloat(video_viewer.pnlmViewer.getYaw());
                    var pitch = parseFloat(video_viewer.pnlmViewer.getPitch());
                    var hfov = parseFloat(video_viewer.pnlmViewer.getHfov());
                }
                try {
                    peer_conn.send({type:'lookAt',yaw:yaw,pitch:pitch,hfov:hfov});
                } catch (e) {}
            },1000);
        } else {
            if(!controls_status['presentation'] && !vr_enabled) {
                $('.rooms_view_sel').show();
            }
        }
        if(cb && (window.export_mode==0) && (window.preview==0) && window.ip_visitor!='') {
            exec_store_visitor(true);
        }
    }

    function init_effect(type) {
        try {
            reset_effects();
            switch(type) {
                case 'snow':
                    $('.pnlm-dragfix').append('<canvas class="snow_effect"></canvas>');
                    init_snow();
                    $('.snow_effect').fadeIn();
                    break;
                case 'rain':
                    $('.pnlm-dragfix').append('<canvas class="rain_effect"></canvas>');
                    init_rain();
                    $('.rain_effect').fadeIn();
                    break;
                case 'fireworks':
                    $('.pnlm-dragfix').append('<canvas class="fireworks_effect"></canvas>');
                    init_fireworks();
                    $('.fireworks_effect').fadeIn();
                    break;
                case 'fog':
                    $('.pnlm-dragfix').append('<canvas class="fog_effect"></canvas>');
                    init_fog();
                    $('.fog_effect').fadeIn();
                    break;
                case 'confetti':
                    $('.pnlm-dragfix').append('<canvas class="confetti_effect"></canvas>');
                    init_confetti();
                    $('.confetti_effect').fadeIn();
                    break;
                case 'sparkle':
                    $('.pnlm-dragfix').append('<canvas class="sparkle_effect"></canvas>');
                    $('.sparkle_effect').show();
                    init_sparkle();
                    break;
                default:
                    break;
            }
        } catch (e) {}
    }

    window.set_initial_pos = function(id) {
        if(peer_id=='') {
            if(!vr_enabled) {
                if(current_panorama_type=='image' || is_iOS() || current_panorama_type=='hls' || current_panorama_type=='lottie') {
                    pano_viewer.lookAt(parseInt(panoramas[id].pitch),parseInt(panoramas[id].yaw),parseInt(hfov));
                } else {
                    video_viewer.pnlmViewer.lookAt(parseInt(panoramas[id].pitch),parseInt(panoramas[id].yaw),parseInt(hfov));
                }
            }
        }
    }

    function check_vr_pos() {
        if(current_panorama_type=='image' || is_iOS() || current_panorama_type=='hls' || current_panorama_type=='lottie') {
            var yaw = parseFloat(pano_viewer_vr.getYaw());
            var pitch = parseFloat(pano_viewer_vr.getPitch());
        } else {
            var yaw = parseFloat(video_viewer_vr.pnlmViewer.getYaw());
            var pitch = parseFloat(video_viewer_vr.pnlmViewer.getPitch());
        }
        var index = get_id_viewer(current_id_panorama);
        var in_marker = false;
        jQuery.each(panoramas[index].hotSpots_vr, function(index, hotspot_vr) {
            var h_yaw = hotspot_vr.yaw;
            var h_pitch = hotspot_vr.pitch;
            if(((yaw<=h_yaw+5) && (yaw>=h_yaw-5)) && ((pitch<=h_pitch+5) && (pitch>=h_pitch-5))) {
                in_marker = true;
                $('.cursor_vr').addClass('cursor_vr_active');
                $('.cursor_vr').addClass('fa-pulse');
                if(goto_timeout == null) {
                    goto_timeout = setTimeout(function () {
                        goto_timeout = null;
                        goto('',hotspot_vr.clickHandlerArgs)
                    },2000);
                }
            }
        });
        if(!in_marker) {
            clearTimeout(goto_timeout);
            goto_timeout = null;
            if(interval_position == null) {
                interval_position = setInterval(function () {
                    check_vr_pos();
                },250);
            }
            $('.cursor_vr').removeClass('cursor_vr_active');
            $('.cursor_vr').removeClass('fa-pulse');
        }
    }

    function check_pano_pos() {
        if(controls_status['icons']) {
            if(current_panorama_type=='image' || is_iOS() || current_panorama_type=='hls' || current_panorama_type=='lottie') {
                var yaw = parseFloat(pano_viewer.getYaw());
                var pitch = parseFloat(pano_viewer.getPitch());
            } else {
                var yaw = parseFloat(video_viewer.pnlmViewer.getYaw());
                var pitch = parseFloat(video_viewer.pnlmViewer.getPitch());
            }
            var index = get_id_viewer(current_id_panorama);
            $('.div_poi_wrapper').removeClass('pulse_icon_hover');
            $('.div_poi_wrapper').removeClass('pulse_image_hover');
            $('.div_marker_wrapper').removeClass('pulse_icon_hover');
            $('.div_marker_wrapper').removeClass('pulse_image_hover');
            $('.pnlm-pointer').removeClass('hotspot_hover');
            var array_coord = [];
            jQuery.each(panoramas[index].hotSpots, function(index, hotspot) {
                if(hotspot.object!='poi_embed') {
                    var h_yaw = hotspot.yaw;
                    var h_pitch = hotspot.pitch;
                    var dist_yaw = Math.abs(yaw % 360 - h_yaw % 360);
                    dist_yaw = Math.min(dist_yaw, 360 - dist_yaw);
                    var dist_pitch = Math.abs(pitch % 360 - h_pitch % 360);
                    dist_pitch = Math.min(dist_pitch, 360 - dist_pitch);
                    if((dist_yaw<=20) && (dist_pitch<=20)) {
                        array_coord.push({x:h_yaw,y:h_pitch,hotspot:hotspot});
                    }
                }
            });
            if(array_coord.length>0) {
                sortByDistance(array_coord, {x: yaw, y: pitch});
                if(array_coord[0].hotspot.type=='marker') {
                    if(array_coord[0].hotspot.animation=='none') {
                        if($('.marker_'+array_coord[0].hotspot.id+' .div_marker_wrapper').hasClass('pulse_icon')) {
                            $('.marker_'+array_coord[0].hotspot.id+' .div_marker_wrapper').addClass('pulse_icon_hover');
                        } else {
                            $('.marker_'+array_coord[0].hotspot.id+' .div_marker_wrapper').addClass('pulse_image_hover');
                        }
                    }
                    $('.marker_'+array_coord[0].hotspot.id).addClass('hotspot_hover');
                } else {
                    if(array_coord[0].hotspot.animation=='none') {
                        if ($('.hotspot_' + array_coord[0].hotspot.id + ' .div_poi_wrapper').hasClass('pulse_icon')) {
                            $('.hotspot_' + array_coord[0].hotspot.id + ' .div_poi_wrapper').addClass('pulse_icon_hover');
                        } else {
                            $('.hotspot_' + array_coord[0].hotspot.id + ' .div_poi_wrapper').addClass('pulse_image_hover');
                        }
                    }
                    $('.hotspot_'+array_coord[0].hotspot.id).addClass('hotspot_hover');
                }
            }
        }
    }

    const distance = (coor1, coor2) => {
        var dist = Math.abs(coor1 % 360 - coor2 % 360);
        return Math.min(dist, 360 - dist);
    };
    const sortByDistance = (coordinates, point) => {
        const sorter = (a, b) => distance(a, point) - distance(b, point);
        coordinates.sort(sorter);
    };

    window.goto = function(hotSpotDiv, args, dh=false) {
        $('.passcode_div').hide();
        $('.leads_div').hide();
        $('.rooms_view_sel').hide();
        if(live_session_connected) {
            clearInterval(interval_live_session);
            try {
                peer_conn.send({type:'goto',args:args});
            } catch (e) {}
        }
        current_id_panorama = args[0];
        var id = get_id_viewer(current_id_panorama);
        if(panoramas[id].transition_override==1) {
            var transition_time_r = panoramas[id].transition_time;
            var transition_zoom_r = panoramas[id].transition_zoom;
        } else {
            var transition_time_r = transition_time;
            var transition_zoom_r = transition_zoom;
        }
        if(dh) {
            transition_time_r=0;
            transition_zoom_r=0;
        }
        var pitch_m = args[3];
        var yaw_m = args[4];
        if(args[5] === undefined) {
            var lookat = 0;
        } else {
            lookat = args[5];
        }
        if(pitch_m=='') {
            pitch_m = null;
        }
        if(yaw_m=='') {
            yaw_m = null;
        }
        if(args[1]!=null) {
            switch(lookat) {
                case 0:
                    if(current_panorama_type=='image' || is_iOS() || current_panorama_type=='hls' || current_panorama_type=='lottie') {
                        var pitch_t = parseFloat(pano_viewer.getPitch());
                        var yaw_t = parseFloat(pano_viewer.getYaw());
                    } else {
                        var pitch_t = parseFloat(video_viewer.pnlmViewer.getPitch());
                        var yaw_t = parseFloat(video_viewer.pnlmViewer.getYaw());
                    }
                    break;
                case 1:
                    var pitch_t = 0;
                    var yaw_t = args[2];
                    break;
                case 2:
                    var pitch_t = args[1];
                    var yaw_t = args[2];
                    break;
            }
            if(current_panorama_type=='image' || is_iOS() || current_panorama_type=='hls' || current_panorama_type=='lottie') {
                if(transition_time_r>0) {
                    var c_hfov = parseInt(pano_viewer.getHfov());
                    var c_transition_zoom = c_hfov-transition_zoom_r;
                    pano_viewer.lookAt(pitch_t,yaw_t,c_transition_zoom,transition_time_r,function () {
                        fade_background(current_id_panorama,true,pitch_m,yaw_m,dh);
                    });
                } else {
                    fade_background(current_id_panorama,true,pitch_m,yaw_m,dh);
                }
            } else {
                if(transition_time_r>0) {
                    var c_hfov = parseInt(video_viewer.pnlmViewer.getHfov());
                    var c_transition_zoom = c_hfov - transition_zoom_r;
                    video_viewer.pnlmViewer.lookAt(pitch_t, yaw_t, c_transition_zoom, transition_time_r, function () {
                        try {
                            video_viewer.pause();
                        } catch (e) {}
                        fade_background(current_id_panorama, true,pitch_m,yaw_m,dh);
                    });
                } else {
                    try {
                        video_viewer.pause();
                    } catch (e) {}
                    fade_background(current_id_panorama, true,pitch_m,yaw_m,dh);
                }
            }
        } else {
            try {
                video_viewer.pause();
            } catch (e) {}
            fade_background(current_id_panorama,false,pitch_m,yaw_m,dh);
        }
        if($('.list_slider').css('opacity')==1) {
            reposition_bottom_controls(true,true,0);
        } else {
            reposition_bottom_controls(false,true,0);
        }
        if(access_time_id!=0) {
            clearInterval(interval_access_time_avg);
            if(!controls_status['presentation']) {
                if(window.export_mode==0 && window.preview==0) {
                    $.ajax({
                        url: "ajax/set_statistics.php",
                        type: "POST",
                        data: {
                            type: 'room_time',
                            id: access_time_id,
                            access_time_avg: access_time_avg,
                            ip_visitor: window.ip_visitor
                        },
                        async: true
                    });
                }
            }
            access_time_avg = 0;
        }
    }

    function ObjectLength( object ) {
        var length = 0;
        for( var key in object ) {
            if( object.hasOwnProperty(key) ) {
                ++length;
            }
        }
        return length;
    };

    function fade_background(current_id_panorama,click_m,pitch_m,yaw_m,dh) {
        var id = get_id_viewer(current_id_panorama);
        if(current_panorama_type=='image' || is_iOS() || current_panorama_type=='hls' || current_panorama_type=='lottie') {
            var dataURL = pano_viewer.getRenderer().render(pano_viewer.getPitch() / 180 * Math.PI,
                pano_viewer.getYaw() / 180 * Math.PI,
                pano_viewer.getHfov() / 180 * Math.PI,
                {'returnImage': 'image/jpeg'});
        } else {
            var dataURL = video_viewer.pnlmViewer.getRenderer().render(video_viewer.pnlmViewer.getPitch() / 180 * Math.PI,
                video_viewer.pnlmViewer.getYaw() / 180 * Math.PI,
                video_viewer.pnlmViewer.getHfov() / 180 * Math.PI,
                {'returnImage': 'image/jpeg'});
        }
        if(vr_enabled) {
            $('#background_pano_vr').off('load');
            $('#background_pano_vr').on('load',function () {
                $('#background_pano_vr').show();
                setTimeout(function () {
                    $('#panorama_viewer_vr').css('opacity',0);
                    $('#video_viewer_vr').css('opacity',0);
                },50);
            }).attr('src',dataURL);
        }
        $('#background_pano').off('load');
        $('#background_pano').on('load',function () {
            $('#background_pano').show();
            $('#background_pano').css('z-index',11);
            if(!vr_enabled) {
                $('#loading_pano').show();
                $('#loading_pano').css('opacity',0.8);
            }
            setTimeout(function () {
                $('#panorama_viewer').css('opacity',0);
                $('#video_viewer').css('opacity',0);
                initialize_room(id,true,click_m,pitch_m,yaw_m,null,null,dh);
                $('.pointer').animate({
                    opacity: 0.4
                }, { duration: 250, queue: false });
                $('.pointer_'+current_id_panorama).animate({
                    opacity: 1
                }, { duration: 250, queue: false });
                $('.view_direction__arrow').hide();
                $('.pointer_list').removeClass('active');
                $('.pointer_list_'+current_id_panorama).addClass('active');
                $('.list_alt_menu .dropdown p:not(.cat) i').each(function () {
                    if($(this).hasClass('fas fa-dot-circle')) {
                        $(this).removeClass('fas fa-dot-circle').addClass('fas fa-circle');
                    }
                });
                $('.list_alt_'+current_id_panorama+' i').removeClass('fas fa-circle').removeClass('far fa-circle').addClass('fas fa-dot-circle');
                if($('.list_alt_'+current_id_panorama).hasClass('children')) {
                    var id_cat = $('.list_alt_'+current_id_panorama).attr('data-cat');
                    $('.cat i').removeClass('fa-chevron-down').addClass('fa-chevron-right');
                    if($('.cat_'+id_cat+' i').hasClass('fa-chevron-right')) {
                        open_cat_list_alt(id_cat);
                    }
                } else {
                    open_cat_list_alt(0);
                }
            },50);
        }).attr('src',dataURL);
    }

    function adjust_ratio_hfov() {
        if(!live_session_connected) {
            if(!vr_enabled) {
                var c_w = $(window).width();
                var c_h = $(window).height();
                var ratio_panorama = c_w / c_h;
                if(c_w <= 414) {
                    var ratio_hfov = window.hfov_mobile_ratio / ratio_panorama;
                } else {
                    var ratio_hfov = 1.7771428571428571 / ratio_panorama;
                }
                if(ratio_hfov<1) ratio_hfov=1;
                var min_hfov_t = min_hfov / ratio_hfov;
                var max_hfov_t = max_hfov / ratio_hfov;
                var id = get_id_viewer(current_id_panorama);
                try {
                    var hfov_t = panoramas[id].hfov / ratio_hfov;
                } catch (e) {}
                try {
                    if(panoramas[id].allow_hfov==0) {
                        min_hfov_t = hfov_t;
                        max_hfov_t = hfov_t;
                    }
                } catch (e) {}
                if(current_panorama_type=='image' || is_iOS() || current_panorama_type=='hls' || current_panorama_type=='lottie') {
                    try {
                        pano_viewer.setHfovBounds([min_hfov_t,max_hfov_t]);
                        pano_viewer.setHfov(hfov_t,false);
                    } catch (e) {}
                    try {
                        pano_viewer_alt.setHfovBounds([min_hfov_t,max_hfov_t]);
                        pano_viewer_alt.setHfov(hfov_t,false);
                    } catch (e) {}
                } else {
                    try {
                        video_viewer.pnlmViewer.setHfovBounds([min_hfov_t,max_hfov_t]);
                        video_viewer.pnlmViewer.setHfov(hfov_t,false);
                    } catch (e) {}
                }
            }
        }
    }

    $(window).resize(function () {
        window.c_width = window.innerWidth || document.documentElement.clientWidth || document.body.clientWidth;
        window.c_height = window.innerHeight || document.documentElement.clientHeight || document.body.clientHeight;
        var vh = window.innerHeight * 0.01;
        document.documentElement.style.setProperty('--vh', `${vh}px`);
        adjust_ratio_hfov();
        try {
            if(auto_show_slider!=2) sly.reload();
        } catch (e) {}
        if(window.innerHeight > window.innerWidth){
            if(window.innerWidth<500) {
                var touchPanSpeedCoeffFactor = pan_speed_mobile_vt;
                var friction = friction_mobile_vt;
            } else {
                var touchPanSpeedCoeffFactor = pan_speed_mobile_vt/2;
                var friction = friction_mobile_vt/2;
            }
        } else {
            var touchPanSpeedCoeffFactor = pan_speed_vt;
            var friction = friction_vt;
        }
        if(current_panorama_type=='image' || is_iOS() || current_panorama_type=='hls' || current_panorama_type=='lottie') {
            try {
                pano_viewer.setFriction(friction);
                pano_viewer.setTouchPanSpeedCoeffFactor(touchPanSpeedCoeffFactor);
            } catch (e) {}
        } else {
            try {
                video_viewer.pnlmViewer.setFriction(friction);
                video_viewer.pnlmViewer.setTouchPanSpeedCoeffFactor(touchPanSpeedCoeffFactor);
            } catch (e) {}
        }
        if(window.innerWidth<540) {
            var info_control = false;
            var voice_control = false;
            var gallery_control = false;
            if(show_info>0) {
                info_control = true;
            }
            if(voice_commands_enable>0) {
                voice_control = true;
            }
            if(gallery_images.length>0) {
                if(show_gallery>0) {
                    gallery_control = true;
                }
            }
        }
        if(window.flyin) {
            try {
                var width = document.querySelector("#vt_container").offsetWidth;
                var height = document.querySelector("#vt_container").offsetHeight;
                flyin_renderer.setSize(width, height);
                flyin_camera.aspect = width / height;
                flyin_camera.updateProjectionMatrix();
            } catch (e) {}
        }
        try {
            $('#vs_grab').css('top','calc(50% - 15px)');
            var width = $("#vt_container").width();
            $('#panorama_viewer_alt').css('width',width+'px');
            var perc_left = $('#vs_slider')[0].style.left.replace('%','');
            if(perc_left!='') {
                perc_left = parseFloat(perc_left) / 100;
                var grab_width = $('#vs_grab').width();
                var slider_width = $('#vs_slider').width();
                var new_pos_left = (width * perc_left)-(grab_width/2)+(slider_width/2);
                $('#vs_grab').css('left',new_pos_left+'px');
            }
            init_vs_drag();
        } catch (e) {}
        if(map_tour_l!=null) {
            setTimeout(function () {
                map_tour_l.invalidateSize();
            },100);
        }
        var poi_embed_count = $('.poi_embed').length;
        if(poi_embed_count>0) {
            setTimeout(function () {
                adjust_poi_embed_helpers_all();
            },50);
        }
        var marker_embed_count = $('.marker_embed').length;
        if(marker_embed_count>0) {
            setTimeout(function () {
                adjust_marker_embed_helpers_all();
            },50);
        }
        resize_maps();
        resize_pois_box();
        if(voice_commands_enable>0) {
            var pl = 32;
            if (window.c_width <= 480) {
                pl = 29;
            }
            if (window.c_width <= 400) {
                pl = 27;
            }
            $('#controls_bottom_left').css('padding-left', pl + 'px');
        }
        if($('.list_slider').css('opacity')==1) {
            reposition_bottom_controls(true, false,0);
        } else {
            reposition_bottom_controls(false, false,0);
        }
        adjust_elements_positions();
    });

    window.resize_maps = function () {
        $('.map').css({'width':'','height':''});
        $('.map_zoomed').css({'width':'','height':''});
        $('.map_image').each(function () {
            if($(this).parent().is(':visible')) {
                if($(this).parent().parent().hasClass('map_zoomed')) {
                    var width = $(this).css('width');
                    var height = $(this).css('height');
                    $(this).parent().parent().css({'width':width,'height':height});
                } else {
                    var map_width_d = $(this).attr('data-map_width_d');
                    var map_width_m = $(this).attr('data-map_width_m');
                    if((window.innerWidth<540) || (window.innerHeight<540)) {
                        $(this).css('width',map_width_m+'px');
                    } else {
                        $(this).css('width',map_width_d+'px');
                    }
                }
                var id_map = $(this).parent().attr('data-id');
                var point_size = 20;
                for(var k=0; k < array_maps.length; k++) {
                    if(id_map==array_maps[k].id) {
                        point_size = array_maps[k].point_size;
                    }
                }
                var scale = (point_size/15)*(parseInt($(this).css('width'))/300);
                $('.pointer_map_'+id_map).attr('data-scale',scale);
                change_pointer_map_scale(scale,id_map);
            }
        });
    }

    function change_pointer_map_scale(newScale,id_map){
        Array.from(document.getElementsByClassName('pointer_map_'+id_map)).forEach(
            function(element, index, array) {
                element.style.transform = element.style.transform.replace(/scale\([0-9|\.]*\)/, 'scale(' + newScale + ')');
            }
        );
    }

    $(document).bind('webkitfullscreenchange mozfullscreenchange fullscreenchange MSFullscreenChange', function() {
        var isFullScreen = document.fullScreen || document.mozFullScreen || document.webkitIsFullScreen || (document.msFullscreenElement != null);
        setTimeout(function () {
            if(current_panorama_type=='image' || is_iOS() || current_panorama_type=='hls' || current_panorama_type=='lottie') {
                pano_viewer.resize();
            } else {
                video_viewer.pnlmViewer.resize();
            }
            resize_pois_box();
        },100);
        if (isFullScreen) {
            $('.fullscreen_control').addClass('active_control');
            controls_status['fullscreen']=true;
        } else {
            $('.fullscreen_control').removeClass('active_control');
            controls_status['fullscreen']=false;
        }
    });

    function get_gallery() {
        if(window.export_mode==1) {
            var rsp = JSON.parse(window.gallery_json);
            if(rsp.length>0) {
                initialize_gallery(rsp);
            }
            loading_config();
        } else {
            $.ajax({
                url: "ajax/get_gallery.php",
                type: "POST",
                data: {
                    id_virtualtour: window.id_virtualtour
                },
                async: true,
                success: function (json) {
                    var rsp = JSON.parse(json);
                    if(rsp.length>0) {
                        initialize_gallery(rsp);
                    }
                    loading_config();
                },
                error: function () {
                    loading_config();
                }
            });
        }
    }

    function get_info_box() {
        if(window.export_mode==1) {
            var rsp = JSON.parse(window.info_box_json);
            info_box = rsp.info_box;
            if(info_box!='') {
                $('.info_control').removeClass("disabled");
                if(show_info==2) {
                    view_info_box();
                }
            }
        } else {
            $.ajax({
                url: "ajax/get_info_box.php",
                type: "POST",
                data: {
                    id_virtualtour: window.id_virtualtour
                },
                async: true,
                success: function (json) {
                    var rsp = JSON.parse(json);
                    info_box = rsp.info_box;
                    if(info_box!='') {
                        $('.info_control').removeClass("disabled");
                        if(show_info==2) {
                            view_info_box();
                        }
                    }
                }
            });
        }
    }

    function get_custom_box() {
        if(window.export_mode==1) {
            var rsp = JSON.parse(window.custom_box_json);
            custom_box = rsp.custom_box;
            if(custom_box!='') {
                $('.custom_control').removeClass("disabled");
                if(show_custom==2) {
                    view_custom_box();
                }
            }
        } else {
            $.ajax({
                url: "ajax/get_custom_box.php",
                type: "POST",
                data: {
                    id_virtualtour: window.id_virtualtour
                },
                async: true,
                success: function (json) {
                    var rsp = JSON.parse(json);
                    custom_box = rsp.custom_box;
                    if(custom_box!='') {
                        $('.custom_control').removeClass("disabled");
                        if(show_custom==2) {
                            view_custom_box();
                        }
                    }
                }
            });
        }
    }

    function initialize_gallery(array_images) {
        jQuery.each(array_images, function(index, image) {
            if((image.title!='') || (image.description!='')) {
                gallery_images.push({
                    "ID":(index+1),
                    "kind":'image',
                    "src":"gallery/"+image.image,
                    "srct":"gallery/thumb/"+image.image,
                    "title":"<div><h4>"+image.title+"</h4><p>"+image.description+"</p></div>",
                });
            } else {
                gallery_images.push({
                    "ID":(index+1),
                    "kind":'image',
                    "src":"gallery/"+image.image,
                    "srct":"gallery/thumb/"+image.image,
                });
            }
        });
    }

    function get_maps() {
        if(window.export_mode==1) {
            var rsp = JSON.parse(window.maps_json);
            if (rsp.status == 'ok') {
                array_maps = rsp.maps;
                map_tour = rsp.map_tour;
                map_tour_points = rsp.map_tour_points;
            }
            get_presentation();
        } else {
            $.ajax({
                url: "ajax/get_maps.php",
                type: "POST",
                data: {
                    id_virtualtour: window.id_virtualtour
                },
                async: true,
                success: function (json) {
                    var rsp = JSON.parse(json);
                    if (rsp.status == 'ok') {
                        array_maps = rsp.maps;
                        map_tour = rsp.map_tour;
                        map_tour_points = rsp.map_tour_points;
                    }
                    get_presentation();
                },
                error: function () {
                    get_presentation();
                }
            });
        }
    }

    function get_presentation() {
        if(window.export_mode==1) {
            var rsp = JSON.parse(window.presentation_json);
            if (rsp.status == 'ok') {
                array_presentation = rsp.presentation;
            }
            get_advertisement();
        } else {
            $.ajax({
                url: "ajax/get_presentation.php",
                type: "POST",
                data: {
                    id_virtualtour: window.id_virtualtour
                },
                async: true,
                success: function (json) {
                    var rsp = JSON.parse(json);
                    if (rsp.status == 'ok') {
                        array_presentation = rsp.presentation;
                    }
                    get_advertisement();
                },
                error: function () {
                    get_advertisement();
                }
            });
        }
    }

    function get_advertisement() {
        if(window.export_mode==1) {
            var rsp = JSON.parse(window.advertisement_json);
            if (rsp.status == 'ok') {
                announce = rsp.announce;
            }
            get_gallery();
        } else {
            $.ajax({
                url: "ajax/get_announce.php",
                type: "POST",
                data: {
                    id_virtualtour: window.id_virtualtour
                },
                async: true,
                success: function (json) {
                    var rsp = JSON.parse(json);
                    if (rsp.status == 'ok') {
                        announce = rsp.announce;
                    }
                    get_gallery();
                },
                error: function () {
                    get_gallery();
                }
            });
        }
    }

    window.play_ad_video = function () {
        var video = document.getElementById('ad_video');
        video.play();
        if(announce.countdown==-1) {
            $('#announce_close_ad').html(window.viewer_labels.wait_video);
            $('#announce_close_ad').attr('onclick','');
            $('#announce_close_ad').css({'cursor':'default','pointer-events':'none'});
        } else if(announce.countdown==0) {
            $('#announce_close_ad').html(window.viewer_labels.close_ad+' <i class="fas fa-angle-double-right"></i>');
            $('#announce_close_ad').css({'cursor':'pointer','pointer-events':'initial'});
            $('#announce_close_ad').on('click',function () {
                $.fancybox.close();
            });
        } else {
            $('#announce_close_ad').html(window.viewer_labels.close_ad+' <span id="announce_time_left">'+announce.countdown+'</span>').promise().done(function () {
                announce_interval = setInterval(function () {
                    announce.countdown = parseInt(announce.countdown)-1;
                    $('#announce_time_left').html(announce.countdown);
                    if(parseInt(announce.countdown)<=0) {
                        clearInterval(announce_interval);
                        $('#announce_close_ad').css('cursor','pointer');
                        $('#announce_time_left').html('<i class="fas fa-angle-double-right"></i>');
                        $('#announce_close_ad').on('click',function () {
                            $.fancybox.close();
                        });
                    }
                },1000);
            });
        }
    }

    function view_announce() {
        var close_ad_html = '<div class="noselect" id="announce_close_ad">'+window.viewer_labels.close_ad+' <span id="announce_time_left">'+announce.countdown+'</span></div>';
        var play_video_html = '<div onclick="play_ad_video();" style="cursor:pointer;pointer-events:initial;" class="noselect" id="announce_close_ad"><i class="fas fa-play"></i> '+window.viewer_labels.play_video+'</div>';
        if(announce.link=='') {
            switch(announce.type) {
                case 'image':
                    var announce_html = '<div class="noselect" id="announce_div"><img src="content/'+announce.image+'" /><br>'+close_ad_html+'</div>';
                    break;
                case 'video':
                    var announce_html = '<div class="noselect" id="announce_div"><video id="ad_video" playsinline ><source src="content/'+announce.video+'" type="video/mp4" /></video><br>'+play_video_html+'</div>';
                    break;
                case 'iframe':
                    var announce_html = '<div class="noselect" id="announce_div"><div class="blocker"></div><iframe scrolling="no" src="'+announce.iframe_link+'"></iframe><br>'+close_ad_html+'</div>';
                    break;
            }
        } else {
            switch(announce.type) {
                case 'image':
                    var announce_html = '<div class="noselect" id="announce_div"><a href="'+announce.link+'" target="_blank"><img src="content/'+announce.image+'" /></a><br>'+close_ad_html+'</div>';
                    break;
                case 'video':
                    var announce_html = '<div class="noselect" id="announce_div"><a href="'+announce.link+'" target="_blank"><video id="ad_video" playsinline ><source src="content/'+announce.video+'" type="video/mp4" /></video></a><br>'+play_video_html+'</div>';
                    break;
                case 'iframe':
                    var announce_html = '<div class="noselect" id="announce_div"><a class="linkwrap" href="'+announce.link+'" target="_blank"><div class="blocker"></div><iframe scrolling="no" src="'+announce.iframe_link+'"></iframe></a><br>'+close_ad_html+'</div>';
                    break;
            }
        }
        announce_open = true;
        $.fancybox.open({
            src  : announce_html,
            type : 'html',
            touch: false,
            modal: true,
            clickSlide: false,
            clickOutside: false,
            afterShow: function () {
                switch(announce.type) {
                    case 'image':
                    case 'iframe':
                        if(parseInt(announce.countdown)==0) {
                            $('#announce_close_ad').css('cursor','pointer');
                            $('#announce_time_left').html('<i class="fas fa-angle-double-right"></i>');
                            $('#announce_close_ad').on('click',function () {
                                announce_open = false;
                                $.fancybox.close();
                            });
                        } else {
                            announce_interval = setInterval(function () {
                                announce.countdown = parseInt(announce.countdown)-1;
                                $('#announce_time_left').html(announce.countdown);
                                if(parseInt(announce.countdown)<=0) {
                                    clearInterval(announce_interval);
                                    $('#announce_close_ad').css('cursor','pointer');
                                    $('#announce_time_left').html('<i class="fas fa-angle-double-right"></i>');
                                    $('#announce_close_ad').on('click',function () {
                                        $.fancybox.close();
                                    });
                                }
                            },1000);
                        }
                        break;
                    case 'video':
                        var video = document.getElementById('ad_video');
                        if(announce.countdown!=-1) {
                            video.loop = true;
                        }
                        var promise = video.play();
                        if (promise !== undefined) {
                            promise.then(_ => {
                                if(announce.countdown==-1) {
                                    $('#announce_close_ad').html(window.viewer_labels.wait_video);
                                    $('#announce_close_ad').attr('onclick','');
                                    $('#announce_close_ad').css({'cursor':'default','pointer-events':'none'});
                                } else if(announce.countdown==0) {
                                    $('#announce_close_ad').html(window.viewer_labels.close_ad+' <i class="fas fa-angle-double-right"></i>');
                                    $('#announce_close_ad').css({'cursor':'pointer','pointer-events':'initial'});
                                    $('#announce_close_ad').on('click',function () {
                                        $.fancybox.close();
                                    });
                                } else {
                                    $('#announce_close_ad').html(window.viewer_labels.close_ad+' <span id="announce_time_left">'+announce.countdown+'</span>').promise().done(function () {
                                        announce_interval = setInterval(function () {
                                            announce.countdown = parseInt(announce.countdown)-1;
                                            $('#announce_time_left').html(announce.countdown);
                                            if(parseInt(announce.countdown)<=0) {
                                                clearInterval(announce_interval);
                                                $('#announce_close_ad').css('cursor','pointer');
                                                $('#announce_time_left').html('<i class="fas fa-angle-double-right"></i>');
                                                $('#announce_close_ad').on('click',function () {
                                                    $.fancybox.close();
                                                });
                                            }
                                        },1000);
                                    });
                                }
                            }).catch(error => {

                            });
                        }
                        video.addEventListener("ended", function() {
                            if(announce.countdown==-1) {
                                $('#announce_close_ad').html(window.viewer_labels.close_ad+' <i class="fas fa-angle-double-right"></i>');
                                $('#announce_close_ad').css({'cursor':'pointer','pointer-events':'initial'});
                                $('#announce_close_ad').on('click',function () {
                                    $.fancybox.close();
                                });
                            }
                        }, true);
                        break;
                }
            }
        });
    }

    window.start_presentation = function () {
        if(controls_status['song']) {
            song_is_playng = true;
        } else {
            song_is_playng = false;
        }
        if(controls_status['map']) {
            map_opened = true;
        } else {
            map_opened = false;
        }
        if(current_panorama_type=='image' || is_iOS() || current_panorama_type=='hls' || current_panorama_type=='lottie') {
            pano_viewer.stopAutoRotate();
        } else {
            video_viewer.pnlmViewer.stopAutoRotate();
        }
        controls_status['presentation']=true;
        $('.panorama').css('pointer-events','none');
        $('.intro_img').css('display','none');
        $('.custom-hotspot').addClass('hidden_icons');
        $('.custom-hotspot img').addClass('hidden_icons');
        $('.custom-hotspot-content').addClass('hidden_icons');
        $('.marker_embed').addClass('hidden_icons');
        $('#floatingSocialShare').css('z-index',0);
        $('#floatingSocialShare').hide();
        $('.list_slider').css('z-index',0);
        $('.list_slider').css('opacity',0);
        $('.list_control').removeClass('active_control');
        $('.list_control i').removeClass('fa-chevron-down').addClass('fa-chevron-up');
        reposition_bottom_controls(false,false,0);
        $('.map').hide();
        $('.pnlm-controls').hide();
        $('#dialog').show();
        $('#dialog').css('z-index',999);
        $('.pnlm-dragfix').css('pointer-events','none');
        $('.controls_control').css('pointer-events','none');
        $('.arrows_nav').hide();
        $('.vr_control_right').hide();
        $('.fullscreen_control').css({'opacity':0,'pointer-events':'none'});
        $('.list_control').css({'opacity':0,'pointer-events':'none'});
        $('.map_control').css({'opacity':0,'pointer-events':'none'});
        $('.map_tour_control').css({'opacity':0,'pointer-events':'none'});
        $('.song_control').css({'opacity':0,'pointer-events':'none'});
        $('.nav_control').css({'opacity':0,'pointer-events':'none'});
        $('#controls_bottom_left').css({'opacity':0,'pointer-events':'none'});
        $('#controls_bottom_center').css({'opacity':0,'pointer-events':'none'});
        $('#controls_bottom_right').css({'opacity':0,'pointer-events':'none'});
        $('.logo').css('pointer-events','none');
        $('#btn_stop_presentation').show();
        $('.menu_controls').hide();
        $('.list_alt_menu').hide();
        $('.rooms_view_sel').hide();
        $('.snipcart-checkout').hide();
        if(voice_commands_enable>0) {
            try {
                SpeechKITT.hide();
                if (annyang) { annyang.pause(); }
            } catch (e) {}
        }
        controls_status['orient']=false;
        if(current_panorama_type=='image' || is_iOS() || current_panorama_type=='hls' || current_panorama_type=='lottie') {
            pano_viewer.stopOrientation();
        } else {
            video_viewer.pnlmViewer.stopOrientation();
        }
        unmute_audio(true,false);
        audio_player.currentTime = 0;
        if(!audio_isPlaying()) audio_player.play();
        controls_status['song']=true;
        controls_status['map']=false;
        controls_status['icons']=false;
        controls_status['list']=false;
        controls_status['share']=false;
        $('.map_tour_control i').removeClass('fas').addClass('far');
        $('.map_tour_control').removeClass('active_control');
        close_map_tour();
        if(presentation_type=='automatic') {
            var index = get_id_viewer(current_id_panorama);
            goto('',[panoramas[index].id,null,null,null,null]);
        } else {
            presentation_steps(0);
        }
    }

    function automatic_presentation_steps(index) {
        if(controls_status['presentation']) {
            if(current_panorama_type=='image' || is_iOS() || current_panorama_type=='hls' || current_panorama_type=='lottie') {
                var yaw = parseInt(pano_viewer.getYaw());
                pano_viewer.startAutoRotate(auto_presentation_speed);
                setTimeout(function () {
                    interval_automatic_presentation = setInterval(function () {
                        var c_yaw = parseInt(pano_viewer.getYaw());
                        if(yaw==c_yaw) {
                            pano_viewer.stopAutoRotate();
                            clearInterval(interval_automatic_presentation);
                            var len = panoramas.length;
                            index = (index+1)%len;
                            goto('',[panoramas[index].id,null,null,null,null]);
                        }
                    },50);
                },2000);
            } else {
                var yaw = parseInt(video_viewer.pnlmViewer.getYaw());
                video_viewer.pnlmViewer.startAutoRotate(auto_presentation_speed);
                setTimeout(function () {
                    interval_automatic_presentation = setInterval(function () {
                        var c_yaw = parseFloat(video_viewer.pnlmViewer.getYaw());
                        if(yaw==c_yaw) {
                            video_viewer.pnlmViewer.stopAutoRotate();
                            clearInterval(interval_automatic_presentation);
                            var len = panoramas.length;
                            index = (index+1)%len;
                            goto('',[panoramas[index].id,null,null,null,null]);
                        }
                    },50);
                },2000);
            }
        }
    }

    window.stop_presentation = function () {
        controls_status['presentation']=false;
        try {
            clearInterval(interval_automatic_presentation);
        } catch (e) {}
        try {
            typed.stop();
            typed.destroy();
        } catch (e) {}
        $('.panorama').css('pointer-events','initial');
        $('.custom-hotspot').removeClass('hidden_icons');
        $('.custom-hotspot img').removeClass('hidden_icons');
        $('.custom-hotspot-content').removeClass('hidden_icons');
        $('.poi_embed').removeClass('hidden_icons');
        $('.marker_embed').removeClass('hidden_icons');
        $('.controls_control').show();
        $('.presentation_control').show();
        $('.compass_control').css('display','inline-block');
        $('#dialog').hide();
        $('#dialog').css('z-index',0);
        $('.fullscreen_control').css({'opacity':1,'pointer-events':'initial'});
        $('.list_control').css({'opacity':1,'pointer-events':'initial'});
        $('.map_control').css({'opacity':1,'pointer-events':'initial'});
        $('.map_tour_control').css({'opacity':1,'pointer-events':'initial'});
        $('.song_control').css({'opacity':1,'pointer-events':'initial'});
        $('.nav_control').css({'opacity':1,'pointer-events':'initial'});
        $('#controls_bottom_left').css({'opacity':1,'pointer-events':'initial'});
        $('#controls_bottom_center').css({'opacity':1,'pointer-events':'initial'});
        $('#controls_bottom_right').css({'opacity':1,'pointer-events':'initial'});
        $('.logo').css('pointer-events','initial');
        $('.pnlm-dragfix').css('pointer-events','initial');
        $('.nav_control').css('pointer-events','initial');
        $('.rooms_view_sel').show();
        $('.snipcart-checkout').show();
        switch(arrows_nav) {
            case 0:
                $('.arrows_nav').hide();
                break;
            case 1:
                $('.arrows_nav').show();
                break;
            case 2:
                if(window.is_mobile) {
                    $('.arrows_nav').hide();
                } else {
                    $('.arrows_nav').show();
                }
                break;
        }
        if(voice_commands_enable>0) {
            try {
                SpeechKITT.show();
            } catch (e) {}
            if(voice_commands_enable==2) {
                try {
                    if (annyang) { annyang.resume(); }
                } catch (e) {}
            }
        }
        controls_status['orient']=false;
        if(show_map!=0) {
            if(map_opened) {
                $('.map').show();
                $('.map_control').addClass('active_control');
                $('.map_control i').removeClass('icon-map_off').addClass('icon-map_on');
                controls_status['map']=true;
            }
        }
        controls_status['icons']=true;
        controls_status['list']=false;
        controls_status['share']=false;
        if(song_is_playng) {
            controls_status['song']=true;
            $('.song_control').addClass('active_control');
            $('.song_control i').addClass('fa-volume-down').removeClass('fa-volume-mute');
            unmute_audio(true,false);
        } else {
            controls_status['song']=false;
            $('.song_control').removeClass('active_control');
            $('.song_control i').removeClass('fa-volume-down').addClass('fa-volume-mute');
            mute_audio(true,false);
        }
        $('#btn_stop_presentation').hide();
        $('.menu_controls').show();
        $('.list_alt_menu').show();
        if(presentation_type=='automatic') {
            if(current_panorama_type=='image' || is_iOS() || current_panorama_type=='hls' || current_panorama_type=='lottie') {
                pano_viewer.stopAutoRotate();
            } else {
                video_viewer.pnlmViewer.stopAutoRotate();
            }
        }
    }

    function get_id_viewer(id) {
        for(var i=0; i<panoramas.length; i++) {
            if(id==panoramas[i].id) {
                return i;
            }
        }
        return 0;
    }

    function presentation_steps(index) {
        if(controls_status['presentation']) {
            if(typeof array_presentation[index] === 'undefined') {
                stop_presentation();
                return;
            }
            var action = array_presentation[index].action;
            var params = array_presentation[index].params;
            var sleep_ms = array_presentation[index].sleep;
            switch (action) {
                case 'goto':
                    goto('',[params,null,null,null,null]);
                    setTimeout(function () {
                        presentation_steps(index+1);
                    },sleep_ms+2000);
                    break;
                case 'type':
                    type(params,function () {
                        setTimeout(function () {
                            presentation_steps(index+1);
                        },sleep_ms);
                    });
                    break;
                case 'lookAt':
                    if(current_panorama_type=='image' || is_iOS() || current_panorama_type=='hls' || current_panorama_type=='lottie') {
                        pano_viewer.lookAt(parseInt(params[0]),parseInt(params[1]),parseInt(params[2]),parseInt(params[3]),function () {
                            setTimeout(function () {
                                presentation_steps(index+1);
                            },sleep_ms);
                        });
                    } else {
                        video_viewer.pnlmViewer.lookAt(parseInt(params[0]),parseInt(params[1]),parseInt(params[2]),parseInt(params[3]),function () {
                            setTimeout(function () {
                                presentation_steps(index+1);
                            },sleep_ms);
                        });
                    }
                    break;
            }
        }
    }

    var typed;
    function type ( stringArray, onComplete ) {
        onComplete = onComplete || function(){};
        typed = new Typed( "#typed", {
            strings: stringArray,
            typeSpeed: 50,
            showCursor: false,
            startDelay: 0,
            onComplete: onComplete
        });
    }

    function lock (orientation) {
        if (document.documentElement.requestFullscreen) {
            document.documentElement.requestFullscreen();
        } else if (document.documentElement.mozRequestFullScreen) {
            document.documentElement.mozRequestFullScreen();
        } else if (document.documentElement.webkitRequestFullscreen) {
            document.documentElement.webkitRequestFullscreen();
        } else if (document.documentElement.msRequestFullscreen) {
            document.documentElement.msRequestFullscreen();
        }
        try {
            screen.orientation.lock(orientation);
        } catch (e) {}
    }

    function unlock () {
        try {
            screen.orientation.unlock();
        } catch (e) {}
        if (document.exitFullscreen) {
            document.exitFullscreen();
        } else if (document.webkitExitFullscreen) {
            document.webkitExitFullscreen();
        } else if (document.mozCancelFullScreen) {
            document.mozCancelFullScreen();
        } else if (document.msExitFullscreen) {
            document.msExitFullscreen();
        }
    }

    window.enable_vr = function () {
        if(controls_status['map']) {
            map_opened = true;
        } else {
            map_opened = false;
        }
        if ('wakeLock' in navigator) {
            try {
                wl = navigator.wakeLock.request('screen');
            } catch (e) {}
        }
        $('.loading_vr').css('display','flex');
        $('.arrows_nav').hide();
        $('#panorama_viewer').css('width','50%');
        $('#panorama_viewer_vr').show();
        $('#video_viewer').css('width','50%');
        $('#video_viewer_vr').show();
        $('#background_pano').css('width','50%');
        $('.fullscreen_control').css({'opacity':0,'pointer-events':'none'});
        $('.list_control').css({'opacity':0,'pointer-events':'none'});
        $('.map_control').css({'opacity':0,'pointer-events':'none'});
        $('.map_tour_control').css({'opacity':0,'pointer-events':'none'});
        $('.song_control').css({'opacity':0,'pointer-events':'none'});
        $('.nav_control').css({'opacity':0,'pointer-events':'none'});
        $('#controls_bottom_left').css({'opacity':0,'pointer-events':'none'});
        $('#controls_bottom_center').css({'opacity':0,'pointer-events':'none'});
        $('#controls_bottom_right').css({'opacity':0,'pointer-events':'none'});
        $('.annotation').css({'opacity':0,'pointer-events':'none'});
        $('.logo').css('pointer-events','none');
        $('.logo').hide();
        $('.cursor_vr').show();
        $('.rooms_view_sel').hide();
        $('.snipcart-checkout').hide();
        $('.visitors_rt_stats').hide();
        $('.stat_visitors_rt_rooms').hide();
        if(voice_commands_enable>0) {
            try {
                SpeechKITT.hide();
                if (annyang) { annyang.pause(); }
            } catch (e) {}
        }
        vr_enabled = true;
        $('.menu_controls').hide();
        $('.list_alt_menu').hide();
        $('.header_vt').css('width','50%');
        $('.header_vt_vr').show();
        $('#btn_stop_vr').show();
        $('#btn_stop_vr_2').show();
        $('.map_tour_control i').removeClass('fas').addClass('far');
        $('.map_tour_control').removeClass('active_control');
        $('.list_slider').css('z-index',0);
        $('.list_slider').css('opacity',0);
        $('.list_control').removeClass('active_control');
        reposition_bottom_controls(false,false,0);
        $('.list_control i').removeClass('fa-chevron-down').addClass('fa-chevron-up');
        close_map_tour();
        $('.orient_control').addClass('active_control');
        $('.orient_control .fa-circle').removeClass('not_active').addClass('active');
        controls_status['orient']=true;
        if(current_panorama_type=='image' || is_iOS() || current_panorama_type=='hls' || current_panorama_type=='lottie') {
            pano_viewer.startOrientation();
        } else {
            video_viewer.pnlmViewer.startOrientation();
        }
        jQuery.each(panoramas, function(index_p, panorama) {
            jQuery.each(panorama.hotSpots, function(index_h, hotspot) {
                if(hotspot.object=='marker_embed') {
                    var tmp = [];
                    tmp['index_p'] = index_p;
                    tmp['index_h'] = index_h;
                    tmp['hotspot'] = Object.assign({}, panoramas[index_p].hotSpots[index_h]);
                    tmp['hotspot'].createTooltipArgs = Object.assign({}, panoramas[index_p].hotSpots[index_h].createTooltipArgs);
                    panoramas_tmp.push(tmp);
                    panoramas[index_p].hotSpots[index_h].object = 'marker';
                    panoramas[index_p].hotSpots[index_h].cssClass = 'custom-hotspot custom-hotspot_vr';
                    panoramas[index_p].hotSpots[index_h].createTooltipArgs.background = 'rgba(255,255,255,0.7)';
                    panoramas[index_p].hotSpots[index_h].createTooltipArgs.color = '#000000';
                }
            });
        });
        setTimeout(function () {
            lock('landscape-primary');
            goto('',[current_id_panorama,null,null,null,null]);
        },250);
    }

    window.disable_vr = function () {
        if ('wakeLock' in navigator) {
            try {
                wl.release();
                wl=null;
            } catch (e) {}
        }
        if ('orientation' in screen) {
            try {
                screen.orientation.unlock();
            } catch (e) {}
        }
        $('.loading_vr').css('display','flex');
        if(current_panorama_type=='image' || is_iOS() || current_panorama_type=='hls' || current_panorama_type=='lottie') {
            $('#panorama_viewer').css('width','100%');
            $('#panorama_viewer_vr').hide();
        } else {
            $('#video_viewer').css('width','100%');
            $('#video_viewer_vr').hide();
        }
        $('#background_pano').css('width','100%');
        $('.cursor_vr').hide();
        $('.fullscreen_control').css({'opacity':1,'pointer-events':'initial'});
        $('.list_control').css({'opacity':1,'pointer-events':'initial'});
        $('.map_control').css({'opacity':1,'pointer-events':'initial'});
        $('.map_tour_control').css({'opacity':1,'pointer-events':'initial'});
        $('.song_control').css({'opacity':1,'pointer-events':'initial'});
        $('.nav_control').css({'opacity':1,'pointer-events':'initial'});
        $('#controls_bottom_left').css({'opacity':1,'pointer-events':'initial'});
        $('#controls_bottom_center').css({'opacity':1,'pointer-events':'initial'});
        $('#controls_bottom_right').css({'opacity':1,'pointer-events':'initial'});
        $('.annotation').css({'opacity':1,'pointer-events':'initial'});
        $('.logo').css('pointer-events','initial');
        $('.logo').show();
        $('.pnlm-controls-container').show();
        $('.rooms_view_sel').show();
        $('.snipcart-checkout').show();
        if(enable_visitor_rt) {
            $('.visitors_rt_stats').show();
            $('.stat_visitors_rt_rooms').show();
        }
        if(voice_commands_enable>0) {
            try {
                SpeechKITT.show();
            } catch (e) {}
            if(voice_commands_enable==2) {
                try {
                    if (annyang) { annyang.resume(); }
                } catch (e) {}
            }
        }
        if(show_map!=0) {
            if(map_opened) {
                $('.map').show();
                $('.map_control').addClass('active_control');
                $('.map_control i').removeClass('icon-map_off').addClass('icon-map_on');
                controls_status['map']=true;
            }
        }
        controls_status['icons']=true;
        controls_status['list']=false;
        controls_status['share']=false;
        if(arrows_nav) {
            $('.arrows_nav').show();
        }
        if(show_info>0) {
            $('.info_control').show();
        } else {
            $('.info_control').hide();
        }
        if(show_custom>0) {
            $('.custom_control').show();
        } else {
            $('.custom_control').hide();
        }
        if(show_dollhouse>0) {
            $('.dollhouse_control').show();
        } else {
            $('.dollhouse_control').hide();
        }
        clearInterval(interval_position);
        if(current_panorama_type=='image' || is_iOS() || current_panorama_type=='hls' || current_panorama_type=='lottie') {
            try {
                pano_viewer_vr.off('load');
                pano_viewer_vr.destroy();
            } catch (e) {}
        } else {
            try {
                video_viewer_vr.pnlmViewer.destroy();
                video_viewer_vr.dispose();
            } catch (e) {}
        }
        vr_enabled = false;
        $('.menu_controls').show();
        $('.list_alt_menu').show();
        $('.header_vt').css('width','100%');
        $('.header_vt_vr').hide();
        $('#btn_stop_vr').hide();
        $('#btn_stop_vr_2').hide();
        $('.orient_control').removeClass('active_control');
        $('.orient_control .fa-circle').removeClass('active').addClass('not_active');
        controls_status['orient']=false;
        if(current_panorama_type=='image' || is_iOS() || current_panorama_type=='hls' || current_panorama_type=='lottie') {
            pano_viewer.stopOrientation();
        } else {
            video_viewer.pnlmViewer.stopOrientation();
        }
        jQuery.each(panoramas_tmp, function(index_tmp, panorama_tmp) {
            var index_p = panorama_tmp['index_p'];
            var index_h = panorama_tmp['index_h'];
            var hotspot_tmp = panorama_tmp['hotspot'];
            panoramas[index_p].hotSpots[index_h] = Object.assign({}, hotspot_tmp);
            panoramas[index_p].hotSpots[index_h].createTooltipArgs = Object.assign({}, hotspot_tmp.createTooltipArgs);
        });
        goto('',[current_id_panorama,null,null,null,null]);
        setTimeout(function () {
            $('.loading_vr').hide();
            unlock();
        },1000);
    }

    window.initialize_speech = function () {
        if (annyang) {
            if(window.export_mode==1) {
                var rsp = JSON.parse(window.voice_commands_json);
                initialize_voice_commands(rsp);
            } else {
                $.ajax({
                    url: "ajax/get_voice_commands.php",
                    type: "POST",
                    data: {
                        id_virtualtour: window.id_virtualtour
                    },
                    async: false,
                    success: function (json) {
                        var rsp = JSON.parse(json);
                        initialize_voice_commands(rsp);
                    }
                });
            }
        }
    }

    function initialize_voice_commands(rsp) {
        if(rsp.status=='ok') {
            var commands = [];
            var voice_commands = rsp.voice_commands[0];
            annyang.setLanguage(voice_commands.language);
            commands.push({
                phrase : voice_commands.help_cmd,
                callback : function() {
                    annyang.pause();
                    SpeechKITT.setInstructionsText(voice_commands.help_msg_1);
                    setTimeout(function () {
                        SpeechKITT.setInstructionsText(voice_commands.help_msg_2);
                        setTimeout(function () {
                            SpeechKITT.setInstructionsText(voice_commands.listening_msg);
                            annyang.resume();
                        },4000);
                    },4000);
                }
            });
            commands.push({
                phrase : voice_commands.next_cmd,
                callback : function() {
                    annyang.pause();
                    $('#skitt-ui').addClass('ok');
                    $('#skitt-toggle-button').addClass('ok');
                    SpeechKITT.setInstructionsText(voice_commands.next_msg);
                    var len = panoramas.length;
                    var index = get_id_viewer(current_id_panorama);
                    var next_panorama = panoramas[(index+1)%len];
                    goto('',[next_panorama.id,null,null,null,null]);
                    setTimeout(function () {
                        $('#skitt-ui').removeClass('ok');
                        $('#skitt-toggle-button').removeClass('ok');
                        SpeechKITT.setInstructionsText(voice_commands.listening_msg);
                        annyang.resume();
                    },1500);
                }
            });
            commands.push({
                phrase : voice_commands.prev_cmd,
                callback : function() {
                    annyang.pause();
                    $('#skitt-ui').addClass('ok');
                    $('#skitt-toggle-button').addClass('ok');
                    SpeechKITT.setInstructionsText(voice_commands.prev_msg);
                    var len = panoramas.length;
                    var index = get_id_viewer(current_id_panorama);
                    var prev_panorama = panoramas[(index+len-1)%len];
                    goto('',[prev_panorama.id,null,null,null,null]);
                    setTimeout(function () {
                        $('#skitt-ui').removeClass('ok');
                        $('#skitt-toggle-button').removeClass('ok');
                        SpeechKITT.setInstructionsText(voice_commands.listening_msg);
                        annyang.resume();
                    },1500);
                }
            });
            commands.push({
                phrase : voice_commands.left_cmd,
                callback : function() {
                    annyang.pause();
                    $('#skitt-ui').addClass('ok');
                    $('#skitt-toggle-button').addClass('ok');
                    SpeechKITT.setInstructionsText(voice_commands.left_msg);
                    if(current_panorama_type=='image' || is_iOS() || current_panorama_type=='hls' || current_panorama_type=='lottie') {
                        var yaw_s = pano_viewer.getYaw();
                        var pitch_s = pano_viewer.getPitch();
                        var hfov_s = pano_viewer.getHfov();
                        yaw_s = yaw_s - 90;
                        pano_viewer.lookAt(pitch_s,yaw_s,hfov_s,2000);
                    } else {
                        var yaw_s = video_viewer.pnlmViewer.getYaw();
                        var pitch_s = video_viewer.pnlmViewer.getPitch();
                        var hfov_s = video_viewer.pnlmViewer.getHfov();
                        yaw_s = yaw_s - 90;
                        video_viewer.pnlmViewer.lookAt(pitch_s,yaw_s,hfov_s,2000);
                    }
                    setTimeout(function () {
                        $('#skitt-ui').removeClass('ok');
                        $('#skitt-toggle-button').removeClass('ok');
                        SpeechKITT.setInstructionsText(voice_commands.listening_msg);
                        annyang.resume();
                    },1500);
                }
            });
            commands.push({
                phrase : voice_commands.right_cmd,
                callback : function() {
                    annyang.pause();
                    $('#skitt-ui').addClass('ok');
                    $('#skitt-toggle-button').addClass('ok');
                    SpeechKITT.setInstructionsText(voice_commands.right_msg);
                    if(current_panorama_type=='image' || is_iOS() || current_panorama_type=='hls' || current_panorama_type=='lottie') {
                        var yaw_s = pano_viewer.getYaw();
                        var pitch_s = pano_viewer.getPitch();
                        var hfov_s = pano_viewer.getHfov();
                        yaw_s = yaw_s + 90;
                        pano_viewer.lookAt(pitch_s,yaw_s,hfov_s,2000);
                    } else {
                        var yaw_s = video_viewer.pnlmViewer.getYaw();
                        var pitch_s = video_viewer.pnlmViewer.getPitch();
                        var hfov_s = video_viewer.pnlmViewer.getHfov();
                        yaw_s = yaw_s + 90;
                        video_viewer.pnlmViewer.lookAt(pitch_s,yaw_s,hfov_s,2000);
                    }
                    setTimeout(function () {
                        $('#skitt-ui').removeClass('ok');
                        $('#skitt-toggle-button').removeClass('ok');
                        SpeechKITT.setInstructionsText(voice_commands.listening_msg);
                        annyang.resume();
                    },1500);
                }
            });
            commands.push({
                phrase : voice_commands.up_cmd,
                callback : function() {
                    annyang.pause();
                    $('#skitt-ui').addClass('ok');
                    $('#skitt-toggle-button').addClass('ok');
                    SpeechKITT.setInstructionsText(voice_commands.up_msg);
                    if(current_panorama_type=='image' || is_iOS() || current_panorama_type=='hls' || current_panorama_type=='lottie') {
                        var yaw_s = pano_viewer.getYaw();
                        var pitch_s = pano_viewer.getPitch();
                        var hfov_s = pano_viewer.getHfov();
                        pitch_s = pitch_s + 45;
                        pano_viewer.lookAt(pitch_s,yaw_s,hfov_s,2000);
                    } else {
                        var yaw_s = video_viewer.pnlmViewer.getYaw();
                        var pitch_s = video_viewer.pnlmViewer.getPitch();
                        var hfov_s = video_viewer.pnlmViewer.getHfov();
                        pitch_s = pitch_s + 45;
                        video_viewer.pnlmViewer.lookAt(pitch_s,yaw_s,hfov_s,2000);
                    }
                    setTimeout(function () {
                        $('#skitt-ui').removeClass('ok');
                        $('#skitt-toggle-button').removeClass('ok');
                        SpeechKITT.setInstructionsText(voice_commands.listening_msg);
                        annyang.resume();
                    },1500);
                }
            });
            commands.push({
                phrase : voice_commands.down_cmd,
                callback : function() {
                    annyang.pause();
                    $('#skitt-ui').addClass('ok');
                    $('#skitt-toggle-button').addClass('ok');
                    SpeechKITT.setInstructionsText(voice_commands.down_msg);
                    if(current_panorama_type=='image' || is_iOS() || current_panorama_type=='hls' || current_panorama_type=='lottie') {
                        var yaw_s = pano_viewer.getYaw();
                        var pitch_s = pano_viewer.getPitch();
                        var hfov_s = pano_viewer.getHfov();
                        pitch_s = pitch_s - 45;
                        pano_viewer.lookAt(pitch_s,yaw_s,hfov_s,2000);
                    } else {
                        var yaw_s = video_viewer.pnlmViewer.getYaw();
                        var pitch_s = video_viewer.pnlmViewer.getPitch();
                        var hfov_s = video_viewer.pnlmViewer.getHfov();
                        pitch_s = pitch_s - 45;
                        video_viewer.pnlmViewer.lookAt(pitch_s,yaw_s,hfov_s,2000);
                    }
                    setTimeout(function () {
                        $('#skitt-ui').removeClass('ok');
                        $('#skitt-toggle-button').removeClass('ok');
                        SpeechKITT.setInstructionsText(voice_commands.listening_msg);
                        annyang.resume();
                    },1500);
                }
            });
            annyang.addCommandsWithDynamicText(commands);
            annyang.addCallback('start', function() {
                controls_status['song']=false;
                $('.song_control').removeClass('active_control');
                $('.song_control i').removeClass('fa-volume-down').addClass('fa-volume-mute');
                mute_audio(true,true);
            });
            annyang.addCallback('end', function() {
                controls_status['song']=true;
                $('.song_control').addClass('active_control');
                $('.song_control i').addClass('fa-volume-down').removeClass('fa-volume-mute');
                unmute_audio(true,false);
            });
            annyang.addCallback('resultNoMatch', function(userSaid, commandText, phrases) {
                annyang.pause();
                $('#skitt-ui').addClass('error');
                $('#skitt-toggle-button').addClass('error');
                SpeechKITT.setInstructionsText(voice_commands.error_msg);
                setTimeout(function () {
                    $('#skitt-ui').removeClass('error');
                    $('#skitt-toggle-button').removeClass('error');
                    SpeechKITT.setInstructionsText(voice_commands.listening_msg);
                    annyang.resume();
                },3000);
            });
            SpeechKITT.annyang();
            SpeechKITT.setStartCommand(function() {
                annyang.start({ autoRestart: true, continuous: true, paused: false });
            });
            if(window.export_mode==1) {
                SpeechKITT.setStylesheet('css/skflat.css?v=15');
            } else {
                SpeechKITT.setStylesheet('vendor/SpeechKITT/themes/flat.css?v=15');
            }
            SpeechKITT.setInstructionsText(voice_commands.initial_msg);
            $('.voice_control').show();
            var pl = 32;
            if(window.c_width<=480) {
                pl = 29;
            }
            if (window.c_width <= 360) {
                pl = 27;
            }
            $('#controls_bottom_left').css('padding-left',pl+'px');
            SpeechKITT.render();
            $("#skitt-ui").appendTo(".voice_control");
            if(voice_commands_enable==2) {
                annyang.resume();
            }
        }
    }

    window.click_menu_controls = function () {
        $('.menu_controls .dropdown')[0].classList.toggle('down');
        $('.menu_controls .arrow')[0].classList.toggle('gone');
        if ($('.menu_controls .dropdown')[0].classList.contains('down')) {
            close_list_alt_menu();
        }
    }

    window.click_list_alt_menu = function () {
        $('.list_alt_menu .dropdown')[0].classList.toggle('down');
        $('.list_alt_menu .arrow')[0].classList.toggle('gone');
    }

    window.close_menu_controls = function () {
        $('.menu_controls .dropdown')[0].classList.remove('down');
        $('.menu_controls .arrow')[0].classList.remove('gone');
    }

    window.close_list_alt_menu = function () {
        $('.list_alt_menu .dropdown')[0].classList.remove('down');
        $('.list_alt_menu .arrow')[0].classList.remove('gone');
    }

    window.init_peer = function() {
        if(peer_id=='') {
            init_sender_viewer();
            $('.live_status span').html(window.viewer_labels.ls_initializing);
            $('#btn_live_end').hide();
            $('#btn_link_session').hide();
            $('#btn_live_status').css('color','black');
            $('.live_call').show();
            $(".video_my_wrapper").draggable({ containment: "#vt_container" });
            $(".video_remote_wrapper").draggable({ containment: "#vt_container" });
            if(peer_turn_host!='') {
                if(peer_turn_u!='' && peer_turn_p!='') {
                    window.peer = new Peer(null,{
                        config: {'iceServers': [
                                {url: 'stun:'+peer_turn_host+':'+peer_turn_port},
                                {url: 'turn:'+peer_turn_host+':'+peer_turn_port, username:peer_turn_u, credential:peer_turn_p},
                            ]},
                        host: peer_server_host,
                        port: peer_server_port,
                        path: peer_server_path,
                        secure: true,
                        debug: 2
                    });
                } else {
                    window.peer = new Peer(null,{
                        config: {'iceServers': [
                                {url: 'stun:'+peer_turn_host+':'+peer_turn_port},
                                {url: 'turn:'+peer_turn_host+':'+peer_turn_port},
                            ]},
                        host: peer_server_host,
                        port: peer_server_port,
                        path: peer_server_path,
                        secure: true,
                        debug: 2
                    });
                }
            } else {
                window.peer = new Peer(null,{
                    host: peer_server_host,
                    port: peer_server_port,
                    path: peer_server_path,
                    secure: true,
                    debug: 2
                });
            }
            peer.on('open', function(id) {
                id_live_session = id;
                $.confirm({
                    useBootstrap: false,
                    closeIcon: false,
                    type: 'blue',
                    theme: 'modern',
                    typeAnimated: true,
                    title: window.viewer_labels.lsc_title,
                    content: window.viewer_labels.ls_webcam_msg,
                    buttons: {
                        video: {
                            text: '<i class="fas fa-video"></i> '+window.viewer_labels.ls_video_audio,
                            action: function(){
                                webcam_audio = true;
                                webcam_video = true;
                                init_sender();
                                $('.live_status span').html(window.viewer_labels.ls_awaiting);
                                $('#btn_live_end').show();
                                $('#btn_link_session').show();
                                $('#btn_live_status').css('color','orange');
                                open_live_link_modal();
                            }
                        },
                        audio : {
                            text: '<i class="fas fa-microphone"></i> '+window.viewer_labels.ls_audio,
                            action: function(){
                                webcam_audio = true;
                                webcam_video = false;
                                init_sender();
                                $('.live_status span').html(window.viewer_labels.ls_awaiting);
                                $('#btn_live_end').show();
                                $('#btn_link_session').show();
                                $('#btn_live_status').css('color','orange');
                                open_live_link_modal();
                            }
                        },
                    }
                });
            });
            peer.on('error', function (err) {
                if(err.toString().indexOf('concurrent') !== -1) {
                    setTimeout(function () {
                        init_peer();
                    },2000);
                } else if(err.toString().indexOf('not get an ID') !== -1) {
                    setTimeout(function () {
                        init_peer();
                    },2000);
                } else {
                    alert(err);
                    try {
                        window.stream_sender.getTracks().forEach(function(track) { track.stop(); })
                    } catch (e) {}
                    try {
                        call_session.close();
                    } catch (e) {}
                    $('#webcam_my').hide();
                    peer.destroy();
                    live_session_connected = false;
                    clearInterval(interval_live_session);
                    $('.live_call').hide();
                    $('.live_control').removeClass('active_control');
                    exit_sender_viewer();
                }
            });
        } else {
            init_receiver_viewer();
            $('.live_status').css('width','120px');
            $('.live_status').css('left','calc(50% - 60px)');
            $('.live_call').show();
            $(".video_my_wrapper").draggable({ containment: "#vt_container" });
            $(".video_remote_wrapper").draggable({ containment: "#vt_container" });
            if(peer_turn_host!='') {
                if(peer_turn_u!='' && peer_turn_p!='') {
                    window.peer = new Peer(null,{
                        config: {'iceServers': [
                                {url: 'stun:'+peer_turn_host+':'+peer_turn_port},
                                {url: 'turn:'+peer_turn_host+':'+peer_turn_port, username:peer_turn_u, credential:peer_turn_p},
                            ]},
                        host: peer_server_host,
                        port: peer_server_port,
                        path: peer_server_path,
                        secure: true,
                        debug: 2
                    });
                } else {
                    window.peer = new Peer(null,{
                        config: {'iceServers': [
                                {url: 'stun:'+peer_turn_host+':'+peer_turn_port},
                                {url: 'turn:'+peer_turn_host+':'+peer_turn_port},
                            ]},
                        host: peer_server_host,
                        port: peer_server_port,
                        path: peer_server_path,
                        secure: true,
                        debug: 2
                    });
                }
            } else {
                window.peer = new Peer(null,{
                    host: peer_server_host,
                    port: peer_server_port,
                    path: peer_server_path,
                    secure: true,
                    debug: 2
                });
            }
            peer.on('open', function(id) {
                $.confirm({
                    useBootstrap: false,
                    closeIcon: false,
                    type: 'blue',
                    theme: 'modern',
                    typeAnimated: true,
                    title: window.viewer_labels.lsc_title,
                    content: window.viewer_labels.ls_webcam_msg,
                    buttons: {
                        video: {
                            text: '<i class="fas fa-video"></i> '+window.viewer_labels.ls_video_audio,
                            action: function(){
                                webcam_audio = true;
                                webcam_video = true;
                                $('.live_status span').html(window.viewer_labels.ls_connecting);
                                $('#btn_live_status').css('color','orange');
                                init_receiver();
                            }
                        },
                        audio : {
                            text: '<i class="fas fa-microphone"></i> '+window.viewer_labels.ls_audio,
                            action: function(){
                                webcam_audio = true;
                                webcam_video = false;
                                $('.live_status span').html(window.viewer_labels.ls_connecting);
                                $('#btn_live_status').css('color','orange');
                                init_receiver();
                            }
                        },
                    }
                });
            });
            peer.on('error', function (err) {
                if(err.toString().indexOf('concurrent') !== -1) {
                    setTimeout(function () {
                        init_peer();
                    },2000);
                } else if(err.toString().indexOf('not get an ID') !== -1) {
                    setTimeout(function () {
                        init_peer();
                    },2000);
                } else if(err.toString().indexOf('Lost connection') !== -1) {
                    setTimeout(function () {
                        init_peer();
                    },2000);
                } else if(err.toString().indexOf('not connect to peer') !== -1) {
                    $('.live_status span').html(window.viewer_labels.ls_invalid);
                    $('#btn_live_status').css('color','red');
                    setTimeout(function () {
                        try {
                            window.stream_sender.getTracks().forEach(function(track) { track.stop(); })
                        } catch (e) {}
                        try {
                            call_session.close();
                        } catch (e) {}
                        $('#webcam_my').hide();
                        peer.destroy();
                        $('.live_call').hide();
                        exit_receiver_viewer();
                    },5000);
                }
            });
        }
    }

    window.open_live_link_modal = function () {
        var link_live = window.url_vt+"index.php?code="+window.code+"&peer_id="+id_live_session;
        var link_live_share = link_live.replace("&","%26");
        var html_live = '<div class="modal_live_link">' +
            '<span style="margin: 0 auto;">'+window.viewer_labels.ls_link_msg+'</span><br><br>' +
            '<textarea id="live_link" rows="3" style="width:100%;padding:5px;text-align:center" readonly>'+link_live+'</textarea><br>'+
            '<i data-clipboard-target="#live_link" class="fas fa-clipboard"></i> <a target="_blank" href="mailto:?body='+link_live_share+'"><i class="fas fa-envelope"></i></a> <a target="_blank" href="https://web.whatsapp.com/send?text='+link_live_share+'"><i class="fab fa-whatsapp"></i></a>'+
            '</div>';
        $.fancybox.open({
            clickSlide: false,
            clickOutside: false,
            smallBtn: false,
            touch: false,
            src  : html_live,
            type : 'html',
            afterShow : function () {
                new ClipboardJS('.modal_live_link i');
            }
        });
    }

    function check_getUserMedia() {
        if (navigator.mediaDevices === undefined) {
            navigator.mediaDevices = {};
        }
        if (navigator.mediaDevices.getUserMedia === undefined) {
            navigator.mediaDevices.getUserMedia = function(constraints) {
                var getUserMedia = navigator.webkitGetUserMedia || navigator.mozGetUserMedia;
                if (!getUserMedia) {
                    return Promise.reject(new Error('getUserMedia is not implemented in this browser'));
                }
                return new Promise(function(resolve, reject) {
                    getUserMedia.call(navigator, constraints, resolve, reject);
                });
            }
        }
    }

    function init_sender() {
        check_getUserMedia();
        navigator.mediaDevices.getUserMedia({
            video: webcam_video,
            audio: webcam_audio
        }).then(function(stream) {
            $('#webcam_my').show();
            if ("srcObject" in window.webcam_my) {
                window.webcam_my.srcObject = stream;
            } else {
                window.webcam_my.src = window.URL.createObjectURL(stream);
            }
            window.webcam_my.volume = 0;
            window.webcam_my.onloadedmetadata = function(e) {
                window.webcam_my.play();
            };
            window.stream_sender = stream;
            peer.on('call', function(call) {
                call_session = call;
                call.answer(stream);
                call.on('stream', function(remoteStream) {
                    $('#webcam_remote').show();
                    if ("srcObject" in window.webcam_remote) {
                        window.webcam_remote.srcObject = remoteStream;
                    } else {
                        window.webcam_remote.src = window.URL.createObjectURL(remoteStream);
                    }
                    window.webcam_remote.onloadedmetadata = function(e) {
                        window.webcam_remote.play();
                    };
                });
            });
        }).catch((err) => {
            console.log('Failed to get local stream:' + err);
        });
        window.peer.on('connection', function(conn) {
            if (window.peer_conn && window.peer_conn.open) {
                conn.on('open', function() {
                    setTimeout(function() { conn.close(); }, 500);
                });
                return;
            }
            window.peer_conn = conn;
            window.peer_conn.on('data', function(data) {
                switch(data.type) {
                    case 'chat':
                        if(!$('.floating-chat').hasClass('expand')) {
                            $('.floating-chat').addClass('blink');
                        }
                        receiveMessage(data.message);
                        break;
                }
            });
            window.peer_conn.on('close', function() {
                live_session_connected = false;
                clearInterval(interval_live_session);
                $('.live_status span').html(window.viewer_labels.ls_awaiting);
                $('#webcam_remote').hide();
                $('#btn_live_status').css('color','orange');
                window.peer_conn = null;
            });
            $.fancybox.close(true);
            setTimeout(function() {
                live_chat.addClass('enter');
                live_chat.click(openLiveChat);
                $('#webcam_remote').show();
                $('.live_status span').html(window.viewer_labels.ls_connected);
                $('#btn_live_status').css('color','green');
                live_session_connected = true;
                goto('',[panoramas[0].id,null,null,null,null]);
            }, 1000);
        });
    }

    function createEmptyVideoTrack({ width, height }) {
        const canvas = Object.assign(document.createElement('canvas'), { width, height });
        canvas.getContext('2d').fillRect(0, 0, width, height);
        const stream = canvas.captureStream();
        const track = stream.getVideoTracks()[0];
        return Object.assign(track, { enabled: false });
    };

    function init_receiver() {
        check_getUserMedia();
        window.peer_conn = window.peer.connect(peer_id, {
            reliable: true
        });
        window.peer_conn.on('open', function() {
            navigator.mediaDevices.getUserMedia({
                video: webcam_video,
                audio: webcam_audio
            }).then(function(stream) {
                if ("srcObject" in window.webcam_remote) {
                    window.webcam_remote.srcObject = stream;
                } else {
                    window.webcam_remote.src = window.URL.createObjectURL(stream);
                }
                window.webcam_remote.volume = 0;
                window.webcam_remote.onloadedmetadata = function(e) {
                    window.webcam_remote.play();
                };
                if(!webcam_video) {
                    let w = 640;
                    let h = 480;
                    let canvas = Object.assign(document.createElement("canvas"), { w, h });
                    canvas.getContext('2d').fillRect(0, 0, w, h);
                    let blackStream = canvas.captureStream();
                    stream.addTrack(blackStream.getVideoTracks()[0]);
                    $('#webcam_my').hide();
                } else {
                    $('#webcam_my').show();
                }
                window.stream_sender = stream;
                var call = peer.call(peer_id, window.stream_sender);
                call_session = call;
                call.on('stream', function(remoteStream) {
                    $('#webcam_remote').show();
                    if ("srcObject" in window.webcam_my) {
                        window.webcam_my.srcObject = remoteStream;
                    } else {
                        window.webcam_my.src = window.URL.createObjectURL(remoteStream);
                    }
                    window.webcam_my.onloadedmetadata = function(e) {
                        window.webcam_my.play();
                    };
                });
            }).catch(() => {
                console.log('Failed to get local stream');
                var videoTrack = createEmptyVideoTrack({ width: 500, height: 500 })
                var empty_stream = new MediaStream([ videoTrack]);
                var call = peer.call(peer_id, empty_stream);
                call_session = call;
                call.on('stream', function(remoteStream) {
                    $('#webcam_remote').show();
                    window.webcam_my.srcObject = remoteStream;
                });
            });
            setTimeout(function() {
                live_chat.addClass('enter');
                live_chat.click(openLiveChat);
                $('.live_status span').html(window.viewer_labels.ls_connected);
                $('#btn_live_status').css('color','green');
                $('#btn_live_end').attr('onclick','close_live_receiver()');
                $('#btn_live_end').show();
                live_session_connected = true;
            }, 1000);
        });
        window.peer_conn.on('data', function(data) {
            switch(data.type) {
                case 'chat':
                    if(!$('.floating-chat').hasClass('expand')) {
                        $('.floating-chat').addClass('blink');
                    }
                    receiveMessage(data.message);
                    break;
                case 'goto':
                    goto('',data.args);
                    break;
                case'lookAt':
                    if(current_panorama_type=='image' || is_iOS() || current_panorama_type=='hls' || current_panorama_type=='lottie') {
                        pano_viewer.lookAt(parseInt(data.pitch),parseInt(data.yaw),parseInt(data.hfov));
                    } else {
                        video_viewer.pnlmViewer.lookAt(parseInt(data.pitch),parseInt(data.yaw),parseInt(data.hfov));
                    }
                    break;
                case 'view_content':
                    switch(data.args.type) {
                        case 'link':
                            $('#link_'+data.args.id).trigger('click');
                            break;
                        case 'video':
                            $('#video_'+data.args.id).trigger('click');
                            break;
                        default:
                            view_content('',data.args);
                            break;
                    }
                    break;
                case 'close_content':
                    $.fancybox.close(true);
                    break;
                case 'view_video':
                    $('#'+data.id).trigger('click');
                    break;
                case 'open_gallery':
                    open_gallery();
                    break;
                case 'close_gallery':
                    try {
                        gallery.nanogallery2('closeViewer');
                    } catch (e) {}
                    try {
                        poi_gallery.nanogallery2('closeViewer');
                    } catch (e) {}
                    break;
                case 'open_map_tour':
                    $('.map_tour_control i').removeClass('far').addClass('fas');
                    $('.map_tour_control').addClass('active_control');
                    open_map_tour();
                    break;
                case 'close_map_tour':
                    $('.map_tour_control i').removeClass('fas').addClass('far');
                    $('.map_tour_control').removeClass('active_control');
                    close_map_tour();
                    break;
            }
        });
        window.peer_conn.on('disconnected', function () {
            console.log('Connection lost. Please reconnect');
        });
        window.peer_conn.on('close', function() {
            console.log('Connection closed');
            $('#btn_live_end').hide();
            $('.live_status span').html(window.viewer_labels.ls_connection_closed);
            $('#btn_live_status').css('color','red');
            setTimeout(function () {
                try {
                    window.stream_sender.getTracks().forEach(function(track) { track.stop(); })
                } catch (e) {}
                try {
                    call_session.close();
                } catch (e) {}
                $('#webcam_my').hide();
                peer.destroy();
                $('.live_call').hide();
                exit_receiver_viewer();
            },2000);
        });
        window.peer_conn.on('error', function (err) {
            console.log(err);
        });
    }

    function init_receiver_viewer() {
        $('.msg_lock').show();
        $('#div_panoramas').addClass('locked');
        $('.list_slider').css('pointer-events','none');
        $('.annotation').addClass('hidden');
        $('#controls_bottom_left').css({'opacity':0,'pointer-events':'none'});
        $('#controls_bottom_center').css({'opacity':0,'pointer-events':'none'});
        $('#controls_bottom_right').css({'opacity':0,'pointer-events':'none'});
        $('.list_control').addClass('hidden');
        $('.song_control').addClass('hidden');
        $('.arrows_nav').addClass('hidden');
        mute_audio(true,true);
        try {
            audio_player.pause();
        } catch (e) {}
        controls_status['song']=false;
        $('.song_control').removeClass('active_control');
        $('.song_control i').removeClass('fa-volume-down').addClass('fa-volume-mute');
    }

    function exit_receiver_viewer() {
        $('#div_panoramas').removeClass('locked');
        $('.list_slider').css('pointer-events','initial');
        $('#controls_bottom_left').css({'opacity':1,'pointer-events':'initial'});
        $('#controls_bottom_center').css({'opacity':1,'pointer-events':'initial'});
        $('#controls_bottom_right').css({'opacity':1,'pointer-events':'initial'});
        $('.list_control').removeClass('hidden');
        $('.song_control').removeClass('hidden');
        $('.arrows_nav').removeClass('hidden');
        $('.annotation').removeClass('hidden');
        $('.msg_lock').hide();
    }

    function init_sender_viewer() {
        $('.menu_controls').css('pointer-events','none');
        $('.annotation').addClass('hidden');
        $('#controls_bottom_left').css({'opacity':0,'pointer-events':'none'});
        $('#controls_bottom_center').css({'opacity':0,'pointer-events':'none'});
        $('#controls_bottom_right').css({'opacity':0,'pointer-events':'none'});
        $('.list_control').addClass('hidden');
        $('.list_slider').css('z-index',0);
        $('.list_slider').css('opacity',0);
        $('.list_control').removeClass('active_control');
        reposition_bottom_controls(false,false,0);
        $('.list_control i').removeClass('fa-chevron-down').addClass('fa-chevron-up');
        controls_status['list']=false;
        $('#floatingSocialShare').css('z-index',0);
        $('#floatingSocialShare').hide();
        $('.share_control').removeClass('active_control');
        $('.share_control .fa-circle').removeClass('active').addClass('not_active');
        controls_status['share']=false;
        $('.song_control').addClass('hidden');
        mute_audio(true,true);
        try {
            audio_player.pause();
        } catch (e) {}
        $('.song_control').removeClass('active_control');
        $('.song_control i').removeClass('fa-volume-down').addClass('fa-volume-mute');
        controls_status['song']=false;
        if(voice_commands_enable>0) {
            try {
                SpeechKITT.hide();
                if (annyang) { annyang.pause(); }
            } catch (e) {}
        }
    }

    function exit_sender_viewer() {
        $('.menu_controls').css('pointer-events','initial');
        $('#controls_bottom_left').css({'opacity':1,'pointer-events':'initial'});
        $('#controls_bottom_center').css({'opacity':1,'pointer-events':'initial'});
        $('#controls_bottom_right').css({'opacity':1,'pointer-events':'initial'});
        $('.list_control').removeClass('hidden');
        $('.song_control').removeClass('hidden');
        $('.annotation').removeClass('hidden');
        if(voice_commands_enable>0) {
            try {
                SpeechKITT.show();
            } catch (e) {}
            if(voice_commands_enable==2) {
                try {
                    if (annyang) { annyang.resume(); }
                } catch (e) {}
            }
        }
    }

    function openLiveChat() {
        $('.floating-chat').removeClass('blink');
        var messages = live_chat.find('.messages');
        var textInput = live_chat.find('.text-box');
        live_chat.find('>i').hide();
        live_chat.addClass('expand');
        live_chat.find('.chat').addClass('enter');
        textInput.keydown(onChatEnter).prop("disabled", false).focus();
        live_chat.off('click', openLiveChat);
        live_chat.find('.header button').click(closeLiveChat);
        live_chat.find('#sendMessage').click(sendNewMessage);
        messages.scrollTop(messages.prop("scrollHeight"));
    }

    function closeLiveChat() {
        live_chat.find('.chat').removeClass('enter').hide();
        live_chat.find('>i').show();
        live_chat.removeClass('expand');
        live_chat.find('.header button').off('click', closeLiveChat);
        live_chat.find('#sendMessage').off('click', sendNewMessage);
        live_chat.find('.text-box').off('keydown', onChatEnter).prop("disabled", true).blur();
        setTimeout(function() {
            live_chat.find('.chat').removeClass('enter').show()
            live_chat.click(openLiveChat);
        }, 500);
    }

    function sendNewMessage() {
        var userInput = $('.text-box');
        var newMessage = userInput.html().replace(/\<div\>|\<br.*?\>/ig, '\n').replace(/\<\/div\>/g, '').trim().replace(/\n/g, '<br>');
        if (!newMessage) return;
        var newMessage_s = urlify(newMessage);
        var messagesContainer = $('.messages');
        messagesContainer.append([
            '<li class="self">',
            newMessage_s,
            '</li>'
        ].join(''));
        userInput.html('');
        userInput.focus();
        messagesContainer.finish().animate({
            scrollTop: messagesContainer.prop("scrollHeight")
        }, 250);
        try {
            peer_conn.send({type:'chat',message:newMessage});
        } catch (e) {}
    }

    function receiveMessage(message) {
        var message = message.replace(/\<div\>|\<br.*?\>/ig, '\n').replace(/\<\/div\>/g, '').trim().replace(/\n/g, '<br>');
        var message_s = urlify(message);
        var messagesContainer = $('.messages');
        messagesContainer.append([
            '<li class="other">',
            message_s,
            '</li>'
        ].join(''));
        messagesContainer.finish().animate({
            scrollTop: messagesContainer.prop("scrollHeight")
        }, 250);
    }

    function onChatEnter(event) {
        if (event.keyCode == 13) {
            sendNewMessage();
            event.preventDefault();
        }
    }

    function urlify(text) {
        var urlRegex = /(https?:\/\/[^\s]+)/g;
        return text.replace(urlRegex, function(url) {
            return '<a style="color: white;text-decoration: underline;" target="_blank" href="' + url + '">' + url + '</a>';
        })
    }

    function init_flyin(pano,yaw,pitch) {
        try {
            $('#flyin').show();
            flyin_renderer = new THREE.WebGLRenderer({ antialiasing: true, alpha: true });
            THREE.Cache.enabled = true;
            flyin_renderer.setSize(document.querySelector("#vt_container").offsetWidth, document.querySelector("#vt_container").offsetHeight);
            flyin_renderer.setClearColor( 0x000000, 0 );
            document.getElementById('flyin').appendChild(flyin_renderer.domElement);
            flyin_scene = new THREE.Scene();
            flyin_camera = new THREE.PerspectiveCamera(50, document.querySelector("#vt_container").offsetWidth / document.querySelector("#vt_container").offsetHeight, 1, 1000);
            flyin_camera.position.set(0, 550, -15);
            flyin_camera.lookAt(new THREE.Vector3(0,0,-15));
            flyin_scene.add( flyin_camera );
            flyin_scene.add( new THREE.AmbientLight(0x444444));
            var light = new THREE.PointLight(0xffffff, 0.8);
            flyin_camera.add( light );
            flyin_geometry = new THREE.SphereGeometry(50, 256, 256);
            flyin_loader = new THREE.TextureLoader();
            flyin_renderer.setAnimationLoop(animate_flyin);
            flyin_texture = new THREE.Texture(first_image);
            flyin_texture.needsUpdate = true;
            flyin_texture.wrapS = THREE.RepeatWrapping;
            flyin_texture.repeat.x = - 1;
            flyin_material = new THREE.MeshBasicMaterial({
                map: flyin_texture,
                color: 0xffffff,
                side: THREE.BackSide
            });
            flyin_mesh = new THREE.Mesh( flyin_geometry, flyin_material );
            flyin_mesh.position.set(0, 0, -15);
            rotateObject(flyin_mesh,-pitch,(yaw+90),0);
            flyin_scene.add( flyin_mesh );
            setTimeout(function() {
                $('.loading').fadeOut(1000);
                $('#flyin').animate({
                    opacity: 1.0
                }, { duration: 500, queue: false });
                setTimeout(function () {
                    var targetPosition = new THREE.Vector3(0, 0, 0);
                    var duration = 2750;
                    tweenCamera(targetPosition, duration);
                },250);
            },250);
            setTimeout(function () {
                $('#flyin').animate({
                    top: 0
                }, { duration: 2000, queue: false });
            },500);
        } catch (e) {
            flyin_renderer.setAnimationLoop(null);
            window.flyin = 0;
            complete_init();
            flyin_geometry.dispose();
            flyin_geometry=null;
            flyin_texture.dispose();
            flyin_texture=null;
            flyin_material.dispose();
            flyin_material=null;
            flyin_renderer.setSize(0,0);
            flyin_renderer.renderLists.dispose();
            flyin_renderer.dispose();
            flyin_renderer=null;
            $('#flyin').remove();
        }
    }

    function animate_flyin() {
        TWEEN.update();
        flyin_renderer.render (flyin_scene, flyin_camera);
    }

    function rotateObject(object, degreeX=0, degreeY=0, degreeZ=0) {
        object.rotateX(THREE.Math.degToRad(degreeX));
        object.rotateY(THREE.Math.degToRad(degreeY));
        object.rotateZ(THREE.Math.degToRad(degreeZ));
    }

    function tweenCamera( targetPosition, duration ) {
        var position = new THREE.Vector3().copy(flyin_camera.position);
        var flyin_tween = new TWEEN.Tween(position)
            .to(targetPosition, duration)
            .easing(TWEEN.Easing.Quadratic.InOut)
            .onUpdate( function () {
                flyin_camera.position.copy(position);
                flyin_camera.lookAt(new THREE.Vector3(0,0,-15));
            } )
            .onComplete( function () {
                flyin_camera.position.copy(targetPosition);
                flyin_camera.lookAt(new THREE.Vector3(0,0,-15));
                flyin_renderer.setAnimationLoop(null);
                window.flyin = 0;
                complete_init();
                setTimeout(function () {
                    $('#flyin').fadeOut(500,function() {
                        flyin_geometry.dispose();
                        flyin_geometry=null;
                        flyin_texture.dispose();
                        flyin_texture=null;
                        flyin_material.dispose();
                        flyin_material=null;
                        flyin_renderer.setSize(0,0);
                        flyin_renderer.renderLists.dispose();
                        flyin_renderer.dispose();
                        flyin_renderer=null;
                        $('#flyin').remove();
                    });
                },500);
            } )
            .start();
    }

    window.check_pois_schedule = function (id_room) {
        if(window.export_mode==0) {
            $.ajax({
                url: "ajax/check_pois_schedule.php",
                type: "POST",
                data: {
                    id_room: id_room
                },
                async: false,
                success: function (json) {
                    var pois = JSON.parse(json);
                    jQuery.each(pois, function(id, visible) {
                        if(parseInt(visible)==0) {
                            $('.hotspot_'+id).addClass("hidden_s");
                        } else {
                            $('.hotspot_'+id).removeClass("hidden_s");
                        }
                        if(id in video_embeds) {
                            video_embeds[id].pause();
                        }
                    });
                }
            });
        }
    }

    function is_iOS() {
        return ['iPad Simulator', 'iPhone Simulator', 'iPod Simulator', 'iPad', 'iPhone', 'iPod'].includes(navigator.platform) || (navigator.userAgent.includes("Mac") && "ontouchend" in document)
    }

    window.open_jitsi_meet = function () {
        if($('#vt_container').hasClass('open_map_tour')) {
            $('#vt_container').addClass('open_jitsi_meet_map_tour');
            $('#vt_container').removeClass('open_map_tour');
        } else {
            $('#vt_container').addClass('open_jitsi_meet');
        }
        $('#jitsi_div').addClass('visible_jitsi_meet');
        var options = {
            roomName: window.name_app_vt,
            width: '100%',
            height: '100%',
            parentNode: document.querySelector('#jitsi_div'),
            configOverwrite: {
                disableDeepLinking: true,
                enableWelcomePage: false,
                disableInviteFunctions: true,
                apiLogLevels: ['warn', 'error']
            },
            interfaceConfigOverwrite: {
                MOBILE_APP_PROMO: false,
                DEFAULT_LOGO_URL: '',
                DEFAULT_WELCOME_PAGE_LOGO_URL: '',
                JITSI_WATERMARK_LINK: '',
                SHOW_CHROME_EXTENSION_BANNER: false,
                SHOW_BRAND_WATERMARK: false,
                SHOW_JITSI_WATERMARK: false,
                SHOW_POWERED_BY: false,
                SHOW_PROMOTIONAL_CLOSE_PAGE: false,
                TOOLBAR_BUTTONS: [
                    'microphone', 'camera', 'desktop' , 'closedcaptions', 'fullscreen',
                    'fodeviceselection', 'hangup', 'chat', 'etherpad', 'settings', 'raisehand',
                    'videoquality', 'filmstrip', 'tileview', 'videobackgroundblur', 'mute-everyone',
                ],
            },
        }
        api_jitsi = new JitsiMeetExternalAPI(jitsi_domain, options);
        api_jitsi.addListener('videoConferenceLeft', function() {
            close_jitsi_meet();
        });
        interval_adjust_embed_helpers_all = setInterval(function () {
            adjust_poi_embed_helpers_all();
            adjust_marker_embed_helpers_all();
            count_adjust_embed_helpers_all++;
            if(count_adjust_embed_helpers_all>=2000) {
                clearInterval(interval_adjust_embed_helpers_all);
            }
        },1);
    }

    window.open_map_tour = function () {
        if($('#vt_container').hasClass('open_jitsi_meet')) {
            $('#vt_container').addClass('open_jitsi_meet_map_tour');
            $('#vt_container').removeClass('open_jitsi_meet');
        } else {
            $('#vt_container').addClass('open_map_tour');
        }
        $('#map_tour_div').addClass('visible_map_tour');
        if(map_tour_l==null) {
            initialize_map_tour();
        }
        setTimeout(function () {
            $(document).trigger('resize');
        },350);
        if(live_session_connected) {
            try {
                peer_conn.send({type:'open_map_tour'});
            } catch (e) {}
        }
        interval_adjust_embed_helpers_all = setInterval(function () {
            adjust_poi_embed_helpers_all();
            adjust_marker_embed_helpers_all();
            count_adjust_embed_helpers_all++;
            if(count_adjust_embed_helpers_all>=2000) {
                clearInterval(interval_adjust_embed_helpers_all);
            }
        },1);
    }

    window.close_map_tour = function () {
        if($('#vt_container').hasClass('open_jitsi_meet_map_tour')) {
            $('#vt_container').removeClass('open_jitsi_meet_map_tour');
            $('#vt_container').addClass('open_jitsi_meet');
        } else {
            $('#vt_container').removeClass('open_map_tour');
        }
        $('#map_tour_div').removeClass('visible_map_tour');
        setTimeout(function () {
            $(document).trigger('resize');
        },350);
        if(live_session_connected) {
            try {
                peer_conn.send({type:'close_map_tour'});
            } catch (e) {}
        }
        interval_adjust_embed_helpers_all = setInterval(function () {
            adjust_poi_embed_helpers_all();
            adjust_marker_embed_helpers_all();
            count_adjust_embed_helpers_all++;
            if(count_adjust_embed_helpers_all>=2000) {
                clearInterval(interval_adjust_embed_helpers_all);
            }
        },1);
    }

    function initialize_map_tour() {
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
        map_tour_l = L.map('map_tour_div', {
            layers: [street_basemap]
        });
        var baseMaps = {
            "Street": street_basemap,
            "Satellite": satellite_basemap
        };
        L.control.layers(baseMaps, {}, {position: 'topright'}).addTo(map_tour_l);
        switch(map_tour.default_view) {
            case 'street':
                baseMaps["Street"].addTo(map_tour_l);
                break;
            case 'satellite':
                baseMaps["Satellite"].addTo(map_tour_l);
                break;
        }
        L.control.locate({
            setView: 'once',
            flyTo: true,
            keepCurrentZoomLevel: true,
            icon: 'fas fa-map-marker-alt',
            strings: {
                title: ""
            }
        }).addTo(map_tour_l);
        var point_size = parseInt(map_tour.point_size);
        var bounds = new L.LatLngBounds();
        var point_na = true,first_lat=0,first_lon=0;
        jQuery.each(map_tour_points, function(index, map_tour_point) {
            var id = parseInt(map_tour_point.id);
            var name = map_tour_point.name;
            var lat = parseFloat(map_tour_point.lat);
            var lon = parseFloat(map_tour_point.lon);
            var class_active = '';
            if(first_lat==0) first_lat=lat;
            if(first_lon==0) first_lon=lon;
            if(current_id_panorama==id) {
                map_tour_l.setView([lat, lon], parseInt(map_tour.zoom_level));
                class_active = 'map_tour_icon_active';
                point_na = false;
            }
            var icon = new L.DivIcon({
                html: "<div id='map_tour_arrow_"+id+"' class=\"view_direction_m__arrow\"></div><div onclick='goto(\"\",["+id+",null,null,null,null]);' title=\""+name+"\" id='map_tour_icon_"+id+"' class='map_tour_icon tooltip_map_tour "+class_active+"' style='background-image: url("+map_tour_point.icon+");'></div>",
                iconSize: [point_size, point_size],
                iconAnchor: [(point_size/2), (point_size/2)]
            });
            L.marker([lat, lon], {icon: icon}).addTo(map_tour_l);
            bounds.extend([lat, lon]);
        });
        if(point_na) {
            var first_zoom=parseInt(map_tour.zoom_level);
            if(parseInt(map_tour.zoom_level)==0) first_zoom=4;
            map_tour_l.setView([first_lat,first_lon],first_zoom);
        }
        if(parseInt(map_tour.zoom_level)==0) {
            map_tour_l.fitBounds(bounds, {padding: [50,50]});
        }
        $('#map_tour_arrow_'+current_id_panorama).show();
        $('.map_tour_icon').css('width',point_size+'px');
        $('.map_tour_icon').css('height',point_size+'px');
        var border = parseInt($('.map_tour_icon').css('borderLeftWidth'),10);
        $('.map_tour_icon').css('border',border+'px solid '+map_tour.point_color);
        $('.map_tour_icon').parent().removeClass('map_tour_icon_top');
        $('#map_tour_icon_'+current_id_panorama).parent().addClass('map_tour_icon_top');
        $('.view_direction_m__arrow').css('top',(point_size/2)+(border/2)+'px');
        $('.view_direction_m__arrow').css('left',(point_size/2)+(border/2)+'px');
        $('.view_direction_m__arrow').css('border-radius','0 0 '+(point_size*2)+'px');
        $('.view_direction_m__arrow').css('width',(point_size*2)+'px');
        $('.view_direction_m__arrow').css('height',(point_size*2)+'px');
        $('.tooltip_map_tour').tooltipster({
            theme: 'tooltipster-borderless',
            animation: 'grow',
            delay: 0,
            arrow: false
        });
    }

    function select_map_tour_point() {
        $('.map_tour_icon').removeClass('map_tour_icon_active');
        $('.map_tour_icon').parent().removeClass('map_tour_icon_top');
        $('.view_direction_m__arrow').hide();
        jQuery.each(map_tour_points, function(index, map_tour_point) {
            var id = parseInt(map_tour_point.id);
            var lat = parseFloat(map_tour_point.lat);
            var lon = parseFloat(map_tour_point.lon);
            if(current_id_panorama==id) {
                if(parseInt(map_tour.zoom_to_point) && parseInt(map_tour.zoom_level)>0) {
                    map_tour_l.setView([lat, lon], parseInt(map_tour.zoom_level));
                } else {
                    map_tour_l.setView([lat, lon]);
                }
                $('#map_tour_icon_'+id).addClass('map_tour_icon_active');
                $('#map_tour_icon_'+id).parent().addClass('map_tour_icon_top');
                $('#map_tour_arrow_'+id).show();
            }
        });
    }

    window.store_visitor_init = function() {
        store_visitor(true);
        if(enable_visitor_rt) {
            $('.visitors_rt_stats').show();
            $('.stat_visitors_rt_rooms').show();
        }
    }

    function store_visitor(first_exec) {
        if(first_exec) {
            var interval = 0;
        } else {
            var interval = interval_visitor_rt;
        }
        setTimeout(function() {
            exec_store_visitor(false);
        },interval);
    }

    function exec_store_visitor(force) {
        var id = get_id_viewer(current_id_panorama);
        var id_room = panoramas[id].id;
        if(enable_visitor_rt) {
            try {
                if(current_panorama_type=='image' || is_iOS() || current_panorama_type=='hls' || current_panorama_type=='lottie') {
                    var yaw = pano_viewer.getYaw();
                    var pitch = pano_viewer.getPitch();
                } else {
                    var yaw = video_viewer.pnlmViewer.getYaw();
                    var pitch = video_viewer.pnlmViewer.getPitch();
                }
            } catch (e) {
                var yaw = 0;
                var pitch = 0;
            }
        } else {
            var yaw = 0;
            var pitch = 0;
        }
        if(!in_idle) {
            $.ajax({
                url: "ajax/store_visitor.php",
                type: "POST",
                data: {
                    id_virtualtour: window.id_virtualtour,
                    ip_visitor: window.ip_visitor,
                    id_visitor: window.id_visitor,
                    id_room: id_room,
                    yaw: yaw,
                    pitch: pitch,
                    enable_visitor_rt: enable_visitor_rt,
                    interval_visitor_rt: interval_visitor_rt
                },
                async: true,
                success: function (json) {
                    if(enable_visitor_rt && !vr_enabled && !live_session_connected) {
                        var rsp = JSON.parse(json);
                        $('#visitors_here').html(rsp.here);
                        $('#visitors_total').html(rsp.total);
                        $('.stat_visitors_rt_rooms span').html(0);
                        $('.stat_visitors_rt_rooms').hide();
                        jQuery.each(rsp.visitors_count, function(id_room, count) {
                            $('#count_visitors_rt_room_'+id_room).html(count);
                            if(count>0) {
                                $('#count_visitors_rt_room_'+id_room).parent().show();
                            }
                        });
                        handle_visitor_rt(rsp.visitors);
                        if(pano_viewer_alt!=null && pano_viewer_alt!==undefined) {
                            handle_visitor_rt_alt(rsp.visitors);
                        }
                    }
                    if(!force) store_visitor(false);
                },
                error: function () {
                    if(!force) store_visitor(false);
                }
            });
        } else {
            if(!force) store_visitor(false);
        }
    }

    function handle_visitor_rt(visitors) {
        var array_exist_visitors = [];
        $('.hotspot-visitor').each(function () {
            var id = $(this).attr('data-id');
            array_exist_visitors.push(id);
        });
        var array_current_visitors = [];
        if(current_panorama_type=='image' || is_iOS() || current_panorama_type=='hls' || current_panorama_type=='lottie') {
            var pano_visitors = pano_viewer;
        } else {
            var pano_visitors = video_viewer.pnlmViewer;
        }
        jQuery.each(visitors, function(index, visitor) {
            var id = visitor.id;
            array_current_visitors.push(id);
            var yaw = parseFloat(visitor.yaw);
            var pitch = parseFloat(visitor.pitch);
            if(array_exist_visitors.includes(id)) {
                pano_visitors.modifyHotSpot("visitor_"+id,yaw,pitch);
            } else {
                pano_visitors.addHotSpot({
                    "id": "visitor_"+id,
                    "type": 'visitor',
                    "object": "visitor",
                    "transform3d": false,
                    "pitch": pitch,
                    "yaw": yaw,
                    "size_scale": 1,
                    "rotateX": 0,
                    "rotateZ": 0,
                    "draggable": false,
                    "cssClass": "hotspot-visitor",
                    "createTooltipFunc": hotspot_visitor,
                    "createTooltipArgs": visitor,
                });
            }
        });
        jQuery.each(array_exist_visitors, function(index, exist_visitor) {
            if(!array_current_visitors.includes(exist_visitor)) {
                try { pano_visitors.removeHotSpot("visitor_"+exist_visitor); } catch (e) {}
            }
        });
        pano_visitors.resize();
    }

    function handle_visitor_rt_alt(visitors) {
        var array_exist_visitors = [];
        $('.hotspot-visitor-alt').each(function () {
            var id = $(this).attr('data-id');
            array_exist_visitors.push(id);
        });
        var array_current_visitors = [];
        jQuery.each(visitors, function(index, visitor) {
            var id = visitor.id;
            array_current_visitors.push(id);
            var yaw = parseFloat(visitor.yaw);
            var pitch = parseFloat(visitor.pitch);
            if(array_exist_visitors.includes(id)) {
                pano_viewer_alt.modifyHotSpot("visitor_alt_"+id,yaw,pitch);
            } else {
                pano_viewer_alt.addHotSpot({
                    "id": "visitor_alt_"+id,
                    "type": 'visitor',
                    "object": "visitor",
                    "transform3d": false,
                    "pitch": pitch,
                    "yaw": yaw,
                    "size_scale": 1,
                    "rotateX": 0,
                    "rotateZ": 0,
                    "draggable": false,
                    "cssClass": "hotspot-visitor-alt",
                    "createTooltipFunc": hotspot_visitor,
                    "createTooltipArgs": visitor,
                });
            }
        });
        jQuery.each(array_exist_visitors, function(index, exist_visitor) {
            if(!array_current_visitors.includes(exist_visitor)) {
                try { pano_viewer_alt.removeHotSpot("visitor_alt_"+exist_visitor); } catch (e) {}
            }
        });
        pano_viewer_alt.resize();
    }

    function init_poi_embed() {
        window.sync_poi_embed_enabled = true;
        poi_embed_ids = [];
        $('.poi_embed').each(function () {
            var id = $(this).attr('data-id');
            poi_embed_ids.push(id);
            var initialized = $(this).attr('data-initialized');
            var transform3d = $(this).attr('data-transform3d');
            if(initialized==0 && transform3d==1) {
                $(this).attr('data-initialized',1);
                $(this).css('visibility','visible');
                poi_embed_make_transformable('.poi_embed_'+id, id, function(element, H) {});
            }
        });
        adjust_poi_embed_helpers_all();
    }

    function init_marker_embed() {
        window.sync_marker_embed_enabled = true;
        marker_embed_ids = [];
        $('.marker_embed').each(function () {
            var id = $(this).attr('data-id');
            marker_embed_ids.push(id);
            var initialized = $(this).attr('data-initialized');
            var transform3d = $(this).attr('data-transform3d');
            if(initialized==0 && transform3d==1) {
                $(this).attr('data-initialized',1);
                $(this).css('visibility','visible');
                marker_embed_make_transformable('.marker_embed_'+id, id, function(element, H) {});
            }
        });
        adjust_marker_embed_helpers_all();
    }

    Math.getDistance = function( x1, y1, x2, y2 ) {
        var  xs = x2 - x1, ys = y2 - y1;
        xs *= xs;
        ys *= ys;
        return Math.sqrt( xs + ys );
    };

    window.adjust_poi_embed_helpers_all = function () {
        for(var i=0; i<poi_embed_ids.length;i++) {
            var id = poi_embed_ids[i];
            var initialized = $('.poi_embed_'+id).attr('data-initialized');
            var transform3d = $('.poi_embed_'+id).attr('data-transform3d');
            var type = $('.poi_embed_'+id).attr('data-type');
            if(transform3d==0) {
                $('.poi_embed_'+id).css({'top':0,'left':0});
                switch(type) {
                    case 'image':
                        $('.poi_embed_'+id+' img').css('opacity',1);
                        break;
                    case 'video':
                    case 'video_transparent':
                        if($('.poi_embed_'+id).css('visibility')=='visible') {
                            $('#video_embed_'+id).css('opacity',1);
                            $('.poi_embed_'+id+' video').css('opacity',1);
                        }
                        break;
                    case 'video_chroma':
                        if($('.poi_embed_'+id).css('visibility')=='visible') {
                            $('#canvas_chroma_'+id).css('opacity',1);
                        }
                        break;
                    case 'gallery':
                        $('.poi_embed_'+id+' .poi_embed_gallery').css('opacity',1);
                        break;
                    case 'link':
                        $('.poi_embed_'+id+' .poi_embed_link').css('opacity',1);
                        $('.poi_embed_'+id+' iframe').css('opacity',1);
                        break;
                    case 'text':
                        $('.poi_embed_'+id+' div').css('opacity',1);
                        break;
                    case 'selection':
                        $('.poi_embed_'+id+' .poi_embed_selection').css('opacity',1);
                        break;
                }
            } else if(initialized==1) {
                var pos_1 = ($('#poi_embded_helper_'+id+'_1').position());
                pos_1.top=pos_1.top+8;
                pos_1.left=pos_1.left+8;
                $('#draggable_'+id+'_1').css({'top':pos_1.top+'px','left':pos_1.left+'px'});
                var pos_2 = ($('#poi_embded_helper_'+id+'_2').position());
                pos_2.top=pos_2.top+8;
                pos_2.left=pos_2.left+8;
                $('#draggable_'+id+'_2').css({'top':pos_2.top+'px','left':pos_2.left+'px'});
                var pos_3 = ($('#poi_embded_helper_'+id+'_3').position());
                pos_3.top=pos_3.top+8;
                pos_3.left=pos_3.left+8;
                $('#draggable_'+id+'_3').css({'top':pos_3.top+'px','left':pos_3.left+'px'});
                var pos_4 = ($('#poi_embded_helper_'+id+'_4').position());
                pos_4.top=pos_4.top+8;
                pos_4.left=pos_4.left+8;
                $('#draggable_'+id+'_4').css({'top':pos_4.top+'px','left':pos_4.left+'px'});
                if(pos_1.left!=8 && pos_1.top!=8 && pos_2.left!=8 && pos_2.top!=8 && pos_3.left!=8 && pos_3.top!=8 && pos_4.left!=8 && pos_4.top!=8) {
                    if((pos_1.left<=(c_width*1.5) && pos_3.left>=(-c_width*0.5)) && (pos_1.top<=(c_height*1.5) && pos_2.top>=(-c_height*0.5))) {
                        poi_embed_apply_transform(id, $('.poi_embed_'+id), poi_embed_originals_pos[id], [[pos_1.left, pos_1.top],[pos_2.left, pos_2.top],[pos_3.left, pos_3.top],[pos_4.left, pos_4.top]]);
                        var current_transform = $('.poi_embed_'+id).css('transform');
                        if(current_transform!="none" && $('#poi_embded_helper_'+id+'_1').position().top!=0 && $('#poi_embded_helper_'+id+'_2').position().top!=0 && $('#poi_embded_helper_'+id+'_3').position().top!=0 && $('#poi_embded_helper_'+id+'_4').position().top!=0) {
                            switch(type) {
                                case 'image':
                                    $('.poi_embed_'+id+' img').css('opacity',1);
                                    break;
                                case 'video':
                                case 'video_transparent':
                                    $('#video_embed_'+id).css('opacity',1);
                                    $('.poi_embed_'+id+' video').css('opacity',1);
                                    break;
                                case 'video_chroma':
                                    $('#canvas_chroma_'+id).css('opacity',1);
                                    break;
                                case 'gallery':
                                    $('.poi_embed_'+id+' .poi_embed_gallery').css('opacity',1);
                                    break;
                                case 'link':
                                    $('.poi_embed_'+id+' .poi_embed_link').css('opacity',1);
                                    $('.poi_embed_'+id+' iframe').css('opacity',1);
                                    break;
                                case 'text':
                                    $('.poi_embed_'+id+' div').css('opacity',1);
                                    break;
                                case 'selection':
                                    $('.poi_embed_'+id+' .poi_embed_selection').css('opacity',1);
                                    break;
                            }
                        } else {
                            switch(type) {
                                case 'image':
                                    $('.poi_embed_'+id+' img').css('opacity',0);
                                    break;
                                case 'video':
                                    $('#video_embed_'+id).css('opacity',0);
                                    $('.poi_embed_'+id+' video').css('opacity',0);
                                    break;
                                case 'gallery':
                                    $('.poi_embed_'+id+' .poi_embed_gallery').css('opacity',0);
                                    break;
                                case 'link':
                                    $('.poi_embed_'+id+' .poi_embed_link').css('opacity',0);
                                    $('.poi_embed_'+id+' iframe').css('opacity',0);
                                    break;
                                case 'text':
                                    $('.poi_embed_'+id+' div').css('opacity',0);
                                    break;
                                case 'selection':
                                    $('.poi_embed_'+id+' .poi_embed_selection').css('opacity',0);
                                    break;
                            }
                        }
                    } else {
                        switch(type) {
                            case 'image':
                                $('.poi_embed_'+id+' img').css('opacity',0);
                                break;
                            case 'video':
                                $('#video_embed_'+id).css('opacity',0);
                                $('.poi_embed_'+id+' video').css('opacity',0);
                                break;
                            case 'gallery':
                                $('.poi_embed_'+id+' .poi_embed_gallery').css('opacity',0);
                                break;
                            case 'link':
                                $('.poi_embed_'+id+' .poi_embed_link').css('opacity',0);
                                $('.poi_embed_'+id+' iframe').css('opacity',0);
                                break;
                            case 'text':
                                $('.poi_embed_'+id+' div').css('opacity',0);
                                break;
                            case 'selection':
                                $('.poi_embed_'+id+' .poi_embed_selection').css('opacity',0);
                                break;
                        }
                    }
                }
            }
        }
    }

    window.adjust_marker_embed_helpers_all = function () {
        for(var i=0; i<marker_embed_ids.length;i++) {
            var id = marker_embed_ids[i];
            var initialized = $('.marker_embed_'+id).attr('data-initialized');
            var transform3d = $('.marker_embed_'+id).attr('data-transform3d');
            var type = $('.marker_embed_'+id).attr('data-type');
            if(transform3d==0) {
                $('.marker_embed_'+id).css({'top':0,'left':0});
                switch(type) {
                    case 'selection':
                        $('.marker_embed_'+id+' .marker_embed_selection').css('opacity',1);
                        break;
                }
            } else if(initialized==1) {
                var pos_1 = ($('#marker_embded_helper_'+id+'_1').position());
                pos_1.top=pos_1.top+8;
                pos_1.left=pos_1.left+8;
                $('#draggable_'+id+'_1').css({'top':pos_1.top+'px','left':pos_1.left+'px'});
                var pos_2 = ($('#marker_embded_helper_'+id+'_2').position());
                pos_2.top=pos_2.top+8;
                pos_2.left=pos_2.left+8;
                $('#draggable_'+id+'_2').css({'top':pos_2.top+'px','left':pos_2.left+'px'});
                var pos_3 = ($('#marker_embded_helper_'+id+'_3').position());
                pos_3.top=pos_3.top+8;
                pos_3.left=pos_3.left+8;
                $('#draggable_'+id+'_3').css({'top':pos_3.top+'px','left':pos_3.left+'px'});
                var pos_4 = ($('#marker_embded_helper_'+id+'_4').position());
                pos_4.top=pos_4.top+8;
                pos_4.left=pos_4.left+8;
                $('#draggable_'+id+'_4').css({'top':pos_4.top+'px','left':pos_4.left+'px'});
                if(pos_1.left!=8 && pos_1.top!=8 && pos_2.left!=8 && pos_2.top!=8 && pos_3.left!=8 && pos_3.top!=8 && pos_4.left!=8 && pos_4.top!=8) {
                    if((pos_1.left<=(c_width*1.5) && pos_3.left>=(-c_width*0.5)) && (pos_1.top<=(c_height*1.5) && pos_2.top>=(-c_height*0.5))) {
                        marker_embed_apply_transform(id, $('.marker_embed_'+id), marker_embed_originals_pos[id], [[pos_1.left, pos_1.top],[pos_2.left, pos_2.top],[pos_3.left, pos_3.top],[pos_4.left, pos_4.top]]);
                        var current_transform = $('.marker_embed_'+id).css('transform');
                        if(current_transform!="none" && $('#marker_embded_helper_'+id+'_1').position().top!=0 && $('#marker_embded_helper_'+id+'_2').position().top!=0 && $('#marker_embded_helper_'+id+'_3').position().top!=0 && $('#marker_embded_helper_'+id+'_4').position().top!=0) {
                            switch(type) {
                                case 'selection':
                                    $('.marker_embed_'+id+' .marker_embed_selection').css('opacity',1);
                                    break;
                            }
                        }
                    } else {
                        switch(type) {
                            case 'selection':
                                $('.marker_embed_'+id+' .marker_embed_selection').css('opacity',0);
                                break;
                        }
                    }
                }
            }
        }
    }

    function hotspot_embed(hotSpotDiv, args) {
        var id = args.id;
        var type = args.embed_type;
        var view_type = parseInt(args.view_type);
        var size = args.embed_size.split(",");
        if(args.transform3d==1) {
            var width = size[0]+'px';
            var height = size[1]+'px';
        } else {
            var width = '100%';
            var height = '100%';
            hotSpotDiv.style = 'width:'+size[0]+'px;height:'+size[1]+'px;';
        }
        hotSpotDiv.setAttribute('draggable',false);
        hotSpotDiv.classList.add('noselect');
        hotSpotDiv.classList.add('poi_embed');
        hotSpotDiv.classList.add('poi_embed_'+id);
        hotSpotDiv.classList.add('hotspot_'+id);
        hotSpotDiv.style.zIndex = args.zIndex;
        if(args.css_class!='') {
            var array_css_class = args.css_class.split(" ");
            jQuery.each(array_css_class, function(index_c, css_class) {
                hotSpotDiv.classList.add(css_class);
            });
        }
        hotSpotDiv.setAttribute('data-id',id);
        hotSpotDiv.setAttribute('data-type',type);
        hotSpotDiv.setAttribute('data-transform3d',args.transform3d);
        hotSpotDiv.setAttribute('data-initialized',0);
        if(args.view_type==2) {
            hotSpotDiv.addEventListener("mouseover", function (e) {
                if(controls_status['icons']==true && !window.is_mobile) {
                    var id = e.target.getAttribute('data-id');
                    if(id !== null) {
                        view_poi_box(id,true);
                    }
                }
            });
            hotSpotDiv.addEventListener("mouseenter", function (e) {
                if(controls_status['icons']==true && !window.is_mobile) {
                    var id = e.target.getAttribute('data-id');
                    if(id !== null) {
                        close_all_poi_box();
                        view_poi_box(id,true);
                    }
                }
            });
        } else {
            switch(args.tooltip_type) {
                case 'text':
                    if(args.tooltip_text !== null && args.tooltip_text !== '' && args.tooltip_text !== '<p><br></p>') {
                        var tooltip = document.createElement('div');
                        tooltip.classList.add('tooltip_poi_embed_'+args.id);
                        tooltip.classList.add('tooltip_text_embed');
                        tooltip.innerHTML = args.tooltip_text;
                        hotSpotDiv.appendChild(tooltip);
                        hotSpotDiv.addEventListener("mouseover", function (e) {
                            if(controls_status['icons']==true && !window.is_mobile) {
                                var id = e.target.getAttribute('data-id');
                                if(id !== null) {
                                    $('.tooltip_poi_embed_'+id).css('opacity',1);
                                }
                            }
                        });
                        hotSpotDiv.addEventListener("mouseenter", function (e) {
                            if(controls_status['icons']==true && !window.is_mobile) {
                                var id = e.target.getAttribute('data-id');
                                if(id !== null) {
                                    $('.tooltip_poi_embed_'+id).css('opacity',1);
                                }
                            }
                        });
                        hotSpotDiv.addEventListener("mouseleave", function (e) {
                            if(controls_status['icons']==true && !window.is_mobile) {
                                var id = e.target.getAttribute('data-id');
                                if(id !== null) {
                                    $('.tooltip_poi_embed_'+id).css('opacity',0);
                                }
                            }
                        });
                    }
                    break;
            }
        }
        switch(type) {
            case 'image':
                var img = document.createElement('img');
                img.setAttribute('draggable',false);
                img.src = args.embed_content;
                if(args.type!='' && args.type!=null) {
                    img.classList.add('highlight_poi_embed');
                } else {
                    hotSpotDiv.classList.add('poi_not_selectable');
                }
                img.style = "width:"+width+";height:"+height+";margin: 0 auto;vertical-align:middle;opacity:0;";
                if(args.title==null) args.title = '';
                if(args.description==null) args.description = '';
                if((args.title!='') && (args.description!='')) {
                    var caption = '<b>'+args.title+'</b><br><i>'+args.description+'</i>';
                } else if((args.title!='') && (args.description=='')) {
                    var caption = '<b>'+args.title+'</b>';
                } else if((args.title=='') && (args.description!='')) {
                    var caption = '<i>'+args.description+'</i>';
                } else {
                    var caption = '';
                }
                switch (args.type) {
                    case 'download':
                        var a = document.createElement('a');
                        a.style = 'text-decoration:none;';
                        a.addEventListener('mousedown', function(e) {
                            start_drag = new Date().getTime();
                            drag_p = false;
                        }, false);
                        a.addEventListener('mousemove', function(e) {
                            end_drag = new Date().getTime();
                            drag_p = true;
                        }, false);
                        a.addEventListener('mouseup', function(e) {
                            var diff_drag = end_drag - start_drag;
                            if(drag_p == false || diff_drag < 200) {
                                a.href = args.content;
                                a.download = '';
                            } else {
                                a.removeAttribute('href');
                                a.removeAttribute('download');
                            }
                        }, false);
                        a.appendChild(img);
                        hotSpotDiv.appendChild(a);
                        break;
                    case 'link':
                        var a = document.createElement('a');
                        a.id = "link_"+args.id;
                        a.style = 'text-decoration:none;';
                        a.title = args.title;
                        if(view_type==0) {
                            a.addEventListener('mousedown', function(e) {
                                start_drag = new Date().getTime();
                                drag_p = false;
                            }, false);
                            a.addEventListener('mousemove', function(e) {
                                end_drag = new Date().getTime();
                                drag_p = true;
                            }, false);
                            a.addEventListener('mouseup', function(e) {
                                var diff_drag = end_drag - start_drag;
                                if(drag_p == false || diff_drag < 200) {
                                    a.href = args.content;
                                    a.setAttribute('data-fancybox','');
                                    a.setAttribute('data-caption',caption);
                                    a.setAttribute('data-type','iframe');
                                    a.setAttribute('data-options','{"type" : "iframe", "iframe" : {"preload" : false}}');
                                } else {
                                    a.href = '#';
                                    a.removeAttribute('data-fancybox');
                                    a.removeAttribute('data-caption');
                                    a.removeAttribute('data-type');
                                    a.removeAttribute('data-options');
                                }
                            }, false);
                            a.addEventListener('simulate_click', function (e) {
                                a.href = args.content;
                                a.setAttribute('data-fancybox','');
                                a.setAttribute('data-caption',caption);
                                a.setAttribute('data-type','iframe');
                                a.setAttribute('data-options','{"type" : "iframe", "iframe" : {"preload" : false}}');
                                a.click();
                            }, false);
                        }
                        a.appendChild(img);
                        hotSpotDiv.appendChild(a);
                        break;
                    case 'link_ext':
                        var a = document.createElement('a');
                        a.style = 'text-decoration:none;';
                        a.addEventListener('mousedown', function(e) {
                            start_drag = new Date().getTime();
                            drag_p = false;
                        }, false);
                        a.addEventListener('mousemove', function(e) {
                            end_drag = new Date().getTime();
                            drag_p = true;
                        }, false);
                        a.addEventListener('mouseup', function(e) {
                            var diff_drag = end_drag - start_drag;
                            if(drag_p == false || diff_drag < 200) {
                                a.href = args.content;
                                a.target = args.target;
                            } else {
                                a.removeAttribute('href');
                                a.removeAttribute('target');
                            }
                        }, false);
                        a.appendChild(img);
                        hotSpotDiv.appendChild(a);
                        break;
                    case 'video':
                        var a = document.createElement('a');
                        a.id = "video_"+args.id;
                        a.style = 'text-decoration:none;';
                        a.title = args.title;
                        if(view_type==0) {
                            a.addEventListener('mousedown', function(e) {
                                start_drag = new Date().getTime();
                                drag_p = false;
                            }, false);
                            a.addEventListener('mousemove', function(e) {
                                end_drag = new Date().getTime();
                                drag_p = true;
                            }, false);
                            a.addEventListener('mouseup', function(e) {
                                var diff_drag = end_drag - start_drag;
                                if(drag_p == false || diff_drag < 200) {
                                    a.href = args.content;
                                    a.onclick = function() {
                                        open_video("video_"+args.id);
                                    };
                                    a.setAttribute('data-fancybox', '');
                                    a.setAttribute('data-caption', caption);
                                } else {
                                    a.href = '#';
                                    a.onclick = function() {};
                                    a.removeAttribute('data-fancybox');
                                    a.removeAttribute('data-caption');
                                    e.preventDefault();
                                    e.stopImmediatePropagation();
                                }
                            }, false);
                            a.addEventListener('simulate_click', function (e) {
                                a.href = args.content;
                                a.onclick = function() {
                                    open_video("video_"+args.id);
                                };
                                a.setAttribute('data-fancybox', '');
                                a.setAttribute('data-caption', caption);
                                a.click();
                            }, false);
                        }
                        a.appendChild(img);
                        hotSpotDiv.appendChild(a);
                        break;
                    default:
                        hotSpotDiv.appendChild(img);
                        break;
                }
                break;
            case 'text':
                var div = document.createElement('div');
                if(args.type=='' || args.type==null) {
                    hotSpotDiv.classList.add('poi_not_selectable');
                }
                div.setAttribute('draggable',false);
                div.classList.add('poi_embed_text');
                var embed_content = args.embed_content.split(' border-width')[0];
                var style = 'border-width'+args.embed_content.split(' border-width')[1];
                div.innerHTML = embed_content;
                var bg_color = args.background;
                var color = args.color;
                div.style = "width:"+width+";height:"+height+";margin: 0 auto;vertical-align:middle;opacity:0;background-color:"+bg_color+";border-color:"+color+";border-width:2px;"+style;
                if(args.title==null) args.title = '';
                if(args.description==null) args.description = '';
                if((args.title!='') && (args.description!='')) {
                    var caption = '<b>'+args.title+'</b><br><i>'+args.description+'</i>';
                } else if((args.title!='') && (args.description=='')) {
                    var caption = '<b>'+args.title+'</b>';
                } else if((args.title=='') && (args.description!='')) {
                    var caption = '<i>'+args.description+'</i>';
                } else {
                    var caption = '';
                }
                switch (args.type) {
                    case 'download':
                        var a = document.createElement('a');
                        a.style = 'text-decoration:none;';
                        a.addEventListener('mousedown', function(e) {
                            start_drag = new Date().getTime();
                            drag_p = false;
                        }, false);
                        a.addEventListener('mousemove', function(e) {
                            end_drag = new Date().getTime();
                            drag_p = true;
                        }, false);
                        a.addEventListener('mouseup', function(e) {
                            var diff_drag = end_drag - start_drag;
                            if(drag_p == false || diff_drag < 200) {
                                a.href = args.content;
                                a.download = '';
                            } else {
                                a.removeAttribute('href');
                                a.removeAttribute('download');
                            }
                        }, false);
                        a.appendChild(div);
                        hotSpotDiv.appendChild(a);
                        break;
                    case 'link':
                        var a = document.createElement('a');
                        a.id = "link_"+args.id;
                        a.style = 'text-decoration:none;';
                        a.title = args.title;
                        if(view_type==0) {
                            a.addEventListener('mousedown', function(e) {
                                start_drag = new Date().getTime();
                                drag_p = false;
                            }, false);
                            a.addEventListener('mousemove', function(e) {
                                end_drag = new Date().getTime();
                                drag_p = true;
                            }, false);
                            a.addEventListener('mouseup', function(e) {
                                var diff_drag = end_drag - start_drag;
                                if(drag_p == false || diff_drag < 200) {
                                    a.href = args.content;
                                    a.setAttribute('data-fancybox','');
                                    a.setAttribute('data-caption',caption);
                                    a.setAttribute('data-type','iframe');
                                    a.setAttribute('data-options','{"type" : "iframe", "iframe" : {"preload" : false}}');
                                } else {
                                    a.href = '#';
                                    a.removeAttribute('data-fancybox');
                                    a.removeAttribute('data-caption');
                                    a.removeAttribute('data-type');
                                    a.removeAttribute('data-options');
                                }
                            }, false);
                            a.addEventListener('simulate_click', function (e) {
                                a.href = args.content;
                                a.setAttribute('data-fancybox','');
                                a.setAttribute('data-caption',caption);
                                a.setAttribute('data-type','iframe');
                                a.setAttribute('data-options','{"type" : "iframe", "iframe" : {"preload" : false}}');
                                a.click();
                            }, false);
                        }
                        a.appendChild(div);
                        hotSpotDiv.appendChild(a);
                        break;
                    case 'link_ext':
                        var a = document.createElement('a');
                        a.style = 'text-decoration:none;';
                        a.addEventListener('mousedown', function(e) {
                            start_drag = new Date().getTime();
                            drag_p = false;
                        }, false);
                        a.addEventListener('mousemove', function(e) {
                            end_drag = new Date().getTime();
                            drag_p = true;
                        }, false);
                        a.addEventListener('mouseup', function(e) {
                            var diff_drag = end_drag - start_drag;
                            if(drag_p == false || diff_drag < 200) {
                                a.href = args.content;
                                a.target = args.target;
                            } else {
                                a.removeAttribute('href');
                                a.removeAttribute('target');
                            }
                        }, false);
                        a.appendChild(div);
                        hotSpotDiv.appendChild(a);
                        break;
                    case 'video':
                        var a = document.createElement('a');
                        a.id = "video_"+args.id;
                        a.style = 'text-decoration:none;';
                        a.title = args.title;
                        if(view_type==0) {
                            a.addEventListener('mousedown', function(e) {
                                start_drag = new Date().getTime();
                                drag_p = false;
                            }, false);
                            a.addEventListener('mousemove', function(e) {
                                end_drag = new Date().getTime();
                                drag_p = true;
                            }, false);
                            a.addEventListener('mouseup', function(e) {
                                var diff_drag = end_drag - start_drag;
                                if(drag_p == false || diff_drag < 200) {
                                    a.href = args.content;
                                    a.onclick = function() {
                                        open_video("video_"+args.id);
                                    };
                                    a.setAttribute('data-fancybox', '');
                                    a.setAttribute('data-caption', caption);
                                } else {
                                    a.href = '#';
                                    a.onclick = function() {};
                                    a.removeAttribute('data-fancybox');
                                    a.removeAttribute('data-caption');
                                    e.preventDefault();
                                    e.stopImmediatePropagation();
                                }
                            }, false);
                            a.addEventListener('simulate_click', function (e) {
                                a.href = args.content;
                                a.onclick = function() {
                                    open_video("video_"+args.id);
                                };
                                a.setAttribute('data-fancybox', '');
                                a.setAttribute('data-caption', caption);
                                a.click();
                            }, false);
                        }
                        a.appendChild(div);
                        hotSpotDiv.appendChild(a);
                        break;
                    default:
                        hotSpotDiv.appendChild(div);
                        break;
                }
                break;
            case 'selection':
                var div = document.createElement('div');
                div.setAttribute('draggable',false);
                var bg_color = args.background;
                var color = args.color;
                div.style = "width:"+width+";height:"+height+";margin: 0 auto;vertical-align:middle;opacity:0;background-color:"+bg_color+";border-color:"+color+";-webkit-box-shadow:none;-moz-box-shadow:none;box-shadow:none;"+args.embed_content;
                div.id = "poi_embed_selection_"+args.id;
                div.classList.add('poi_embed_selection');
                div.setAttribute('data-id',args.id);
                if(args.type=='' || args.type==null) {
                    hotSpotDiv.classList.add('poi_not_selectable');
                } else {
                    div.addEventListener("mouseover", function (e) {
                        $('#poi_embed_selection_'+e.target.getAttribute('data-id')).css('-webkit-box-shadow','0 0 10px 4px '+color);
                        $('#poi_embed_selection_'+e.target.getAttribute('data-id')).css('-moz-box-shadow','0 0 10px 4px '+color);
                        $('#poi_embed_selection_'+e.target.getAttribute('data-id')).css('box-shadow','0 0 10px 4px '+color);
                    });
                    div.addEventListener("mouseenter", function (e) {
                        $('#poi_embed_selection_'+e.target.getAttribute('data-id')).css('-webkit-box-shadow','0 0 10px 4px '+color);
                        $('#poi_embed_selection_'+e.target.getAttribute('data-id')).css('-moz-box-shadow','0 0 10px 4px '+color);
                        $('#poi_embed_selection_'+e.target.getAttribute('data-id')).css('box-shadow','0 0 10px 4px '+color);
                    });
                    div.addEventListener("mouseleave", function (e) {
                        $('#poi_embed_selection_'+e.target.getAttribute('data-id')).css('-webkit-box-shadow','none');
                        $('#poi_embed_selection_'+e.target.getAttribute('data-id')).css('-moz-box-shadow','none');
                        $('#poi_embed_selection_'+e.target.getAttribute('data-id')).css('box-shadow','none');
                    });
                }
                if(args.title==null) args.title = '';
                if(args.description==null) args.description = '';
                if((args.title!='') && (args.description!='')) {
                    var caption = '<b>'+args.title+'</b><br><i>'+args.description+'</i>';
                } else if((args.title!='') && (args.description=='')) {
                    var caption = '<b>'+args.title+'</b>';
                } else if((args.title=='') && (args.description!='')) {
                    var caption = '<i>'+args.description+'</i>';
                } else {
                    var caption = '';
                }
                switch (args.type) {
                    case 'download':
                        var a = document.createElement('a');
                        a.style = 'text-decoration:none;';
                        a.addEventListener('mousedown', function(e) {
                            start_drag = new Date().getTime();
                            drag_p = false;
                        }, false);
                        a.addEventListener('mousemove', function(e) {
                            end_drag = new Date().getTime();
                            drag_p = true;
                        }, false);
                        a.addEventListener('mouseup', function(e) {
                            var diff_drag = end_drag - start_drag;
                            if(drag_p == false || diff_drag < 200) {
                                a.href = args.content;
                                a.download = '';
                            } else {
                                a.removeAttribute('href');
                                a.removeAttribute('download');
                            }
                        }, false);
                        a.appendChild(div);
                        hotSpotDiv.appendChild(a);
                        break;
                    case 'link':
                        var a = document.createElement('a');
                        a.id = "link_"+args.id;
                        a.style = 'text-decoration:none;';
                        a.title = args.title;
                        if(view_type==0) {
                            a.addEventListener('mousedown', function(e) {
                                start_drag = new Date().getTime();
                                drag_p = false;
                            }, false);
                            a.addEventListener('mousemove', function(e) {
                                end_drag = new Date().getTime();
                                drag_p = true;
                            }, false);
                            a.addEventListener('mouseup', function(e) {
                                var diff_drag = end_drag - start_drag;
                                if(drag_p == false || diff_drag < 200) {
                                    a.href = args.content;
                                    a.setAttribute('data-fancybox','');
                                    a.setAttribute('data-caption',caption);
                                    a.setAttribute('data-type','iframe');
                                    a.setAttribute('data-options','{"type" : "iframe", "iframe" : {"preload" : false}}');
                                } else {
                                    a.href = '#';
                                    a.removeAttribute('data-fancybox');
                                    a.removeAttribute('data-caption');
                                    a.removeAttribute('data-type');
                                    a.removeAttribute('data-options');
                                }
                            }, false);
                            a.addEventListener('simulate_click', function (e) {
                                a.href = args.content;
                                a.setAttribute('data-fancybox','');
                                a.setAttribute('data-caption',caption);
                                a.setAttribute('data-type','iframe');
                                a.setAttribute('data-options','{"type" : "iframe", "iframe" : {"preload" : false}}');
                                a.click();
                            }, false);
                        }
                        a.appendChild(div);
                        hotSpotDiv.appendChild(a);
                        break;
                    case 'link_ext':
                        var a = document.createElement('a');
                        a.style = 'text-decoration:none;';
                        a.addEventListener('mousedown', function(e) {
                            start_drag = new Date().getTime();
                            drag_p = false;
                        }, false);
                        a.addEventListener('mousemove', function(e) {
                            end_drag = new Date().getTime();
                            drag_p = true;
                        }, false);
                        a.addEventListener('mouseup', function(e) {
                            var diff_drag = end_drag - start_drag;
                            if(drag_p == false || diff_drag < 200) {
                                a.href = args.content;
                                a.target = args.target;
                            } else {
                                a.removeAttribute('href');
                                a.removeAttribute('target');
                            }
                        }, false);
                        a.appendChild(div);
                        hotSpotDiv.appendChild(a);
                        break;
                    case 'video':
                        var a = document.createElement('a');
                        a.id = "video_"+args.id;
                        a.style = 'text-decoration:none;';
                        a.title = args.title;
                        if(view_type==0) {
                            a.addEventListener('mousedown', function(e) {
                                start_drag = new Date().getTime();
                                drag_p = false;
                            }, false);
                            a.addEventListener('mousemove', function(e) {
                                end_drag = new Date().getTime();
                                drag_p = true;
                            }, false);
                            a.addEventListener('mouseup', function(e) {
                                var diff_drag = end_drag - start_drag;
                                if(drag_p == false || diff_drag < 200) {
                                    a.href = args.content;
                                    a.onclick = function() {
                                        open_video("video_"+args.id);
                                    };
                                    a.setAttribute('data-fancybox', '');
                                    a.setAttribute('data-caption', caption);
                                } else {
                                    a.href = '#';
                                    a.onclick = function() {};
                                    a.removeAttribute('data-fancybox');
                                    a.removeAttribute('data-caption');
                                    e.preventDefault();
                                    e.stopImmediatePropagation();
                                }
                            }, false);
                            a.addEventListener('simulate_click', function (e) {
                                a.href = args.content;
                                a.onclick = function() {
                                    open_video("video_"+args.id);
                                };
                                a.setAttribute('data-fancybox', '');
                                a.setAttribute('data-caption', caption);
                                a.click();
                            }, false);
                        }
                        a.appendChild(div);
                        hotSpotDiv.appendChild(a);
                        break;
                    default:
                        hotSpotDiv.appendChild(div);
                        break;
                }
                break;
            case 'gallery':
                var div = document.createElement('div');
                div.classList.add('glide');
                div.classList.add('poi_embed_gallery');
                div.classList.add('poi_embed_gallery_'+args.id);
                div.style = "width:"+width+";height:"+height+";margin: 0 auto;vertical-align:middle;opacity:0;";
                var html = '<div class="glide__track" data-glide-el="track"><ul class="glide__slides">';
                jQuery.each(args.embed_content, function(index, image) {
                    html += '<li style="height:'+height+'px" class="glide__slide"><img style="object-fit: contain;width: 100%;height: 100%" src="'+image+'" /></li>';
                });
                html += '</ul>' +
                    '<div class="glide__arrows" data-glide-el="controls">' +
                    '    <i class="glide__arrow glide__arrow--left fas fa-chevron-left" data-glide-dir="<"></i>' +
                    '    <i class="glide__arrow glide__arrow--right fas fa-chevron-right" data-glide-dir=">"></i>' +
                    '  </div>' +
                    '</div>';
                div.innerHTML=html;
                hotSpotDiv.appendChild(div);
                new Glide('.poi_embed_gallery_'+args.id,{
                    type: 'carousel',
                    startAt: 0,
                    perView: 1,
                    hoverpause: true,
                    autoplay: parseInt(args.embed_gallery_autoplay)*1000
                }).mount();
                break;
            case 'video_chroma':
                var embed_video_muted = parseInt(args.embed_video_muted);
                var embed_video_autoplay = parseInt(args.embed_video_autoplay);
                var video = document.createElement('video');
                video.setAttribute('draggable',false);
                video.id = "video_embed_"+id;
                video.classList.add('noselect');
                video.setAttribute("preload", "auto");
                video.setAttribute("loop", "");
                if(embed_video_muted==1) {
                    video.setAttribute("muted", "");
                }
                video.setAttribute('playsinline', '');
                video.setAttribute('webkit-playsinline','');
                video.style = "width:"+width+";height:"+height+";margin: 0 auto;vertical-align:middle;opacity:0;";
                video.src = args.embed_content+'#t=0';
                var canvas = document.createElement('canvas');
                canvas.id = "canvas_chroma_"+id;
                canvas.setAttribute('width', width.replace('px',''));
                canvas.setAttribute('height', height.replace('px',''));
                canvas.style = "width:"+width+";height:"+height+";margin: 0 auto;vertical-align:middle;opacity:0;position:absolute;top:0;left:0;";
                hotSpotDiv.appendChild(canvas);
                var ctx_chroma = canvas.getContext('2d');
                var c_chroma_tmp = document.createElement('canvas');
                c_chroma_tmp.setAttribute('width', width.replace('px',''));
                c_chroma_tmp.setAttribute('height', height.replace('px',''));
                var ctx_chroma_tmp = c_chroma_tmp.getContext('2d');
                video.addEventListener('play', (event) => {
                    $('.poi_embed_'+id+' .div_play_btn').hide();
                    $('.poi_embed_'+id).css('pointer-events','none');
                    remove_background_video_chroma(video,ctx_chroma_tmp,ctx_chroma,width.replace('px',''),height.replace('px',''),args.params,false);
                });
                video.addEventListener('loadeddata', function() {
                    remove_background_video_chroma(video,ctx_chroma_tmp,ctx_chroma,width.replace('px',''),height.replace('px',''),args.params,true);
                });
                video.addEventListener('loadedmetadata', function() {
                    remove_background_video_chroma(video,ctx_chroma_tmp,ctx_chroma,width.replace('px',''),height.replace('px',''),args.params,true);
                    if(embed_video_autoplay==1) {
                        _checkAutoPlay(video.play(),id);
                    } else {
                        _callback_onAutoplayBlocked(id);
                    }
                });
                hotSpotDiv.appendChild(video);
                var div = document.createElement('div');
                div.classList.add('div_play_btn');
                div.innerHTML = '<i onclick="play_video_transparent('+id+');" class="far fa-play-circle"></i>';
                hotSpotDiv.appendChild(div);
                break;
            case 'video_transparent':
                var embed_video_muted = parseInt(args.embed_video_muted);
                var embed_video_autoplay = parseInt(args.embed_video_autoplay);
                var video = document.createElement('video');
                video.setAttribute('draggable',false);
                video.id = "video_embed_"+id;
                video.classList.add('noselect');
                video.setAttribute("preload", "auto");
                video.setAttribute("loop", "");
                if(embed_video_muted==1) {
                    video.setAttribute("muted", "");
                }
                if(embed_video_autoplay==1) {
                    video.setAttribute("autoplay", "");
                }
                video.setAttribute('playsinline', '');
                video.setAttribute('webkit-playsinline','');
                video.style = "width:"+width+";height:"+height+";margin: 0 auto;vertical-align:middle;opacity:0;";
                var array_videos = args.embed_content.split(",");
                var mov_source = '', webm_source = '';
                jQuery.each(array_videos, function(index_s, video_s) {
                    if(video_s.split('.').pop().toLowerCase()=='mov') {
                        mov_source = video_s;
                    }
                    if(video_s.split('.').pop().toLowerCase()=='webm') {
                        webm_source = video_s;
                    }
                });
                if(mov_source!='' && webm_source!='') {
                    if(supportsHEVCAlpha()) {
                        video.src = mov_source+'#t=2';
                    } else {
                        video.src = webm_source+'#t=2';
                    }
                } else if(mov_source!='') {
                    video.src = mov_source+'#t=2';
                } else if(webm_source!='') {
                    video.src = webm_source+'#t=2';
                }
                video.addEventListener('play', (event) => {
                    $('.poi_embed_'+id+' .div_play_btn').hide();
                    $('.poi_embed_'+id).css('pointer-events','none');
                });
                hotSpotDiv.appendChild(video);
                var div = document.createElement('div');
                div.classList.add('div_play_btn');
                div.innerHTML = '<i onclick="play_video_transparent('+id+');" class="far fa-play-circle"></i>';
                hotSpotDiv.appendChild(div);
                if(embed_video_autoplay==1) {
                    _checkAutoPlay(video.play(),id);
                } else {
                    _callback_onAutoplayBlocked(id);
                }
                break;
            case 'video':
                var embed_video_muted = parseInt(args.embed_video_muted);
                var embed_video_autoplay = parseInt(args.embed_video_autoplay);
                var video = document.createElement('video');
                video.setAttribute('draggable',false);
                video.id = "video_embed_"+id;
                video.classList.add('video-js');
                video.classList.add('vjs-default-skin');
                video.classList.add('vjs-big-play-centered');
                video.classList.add('vjs-fluid');
                video.classList.add('noselect');
                video.setAttribute("preload", "auto");
                video.setAttribute("loop", "");
                if(embed_video_muted==1) {
                    video.setAttribute("muted", "");
                }
                if(embed_video_autoplay==1) {
                    video.setAttribute("autoplay", "");
                }
                video.setAttribute("controls", "");
                video.setAttribute('playsinline', '');
                video.setAttribute('webkit-playsinline','');
                video.style = "width:"+width+";height:"+height+";margin: 0 auto;vertical-align:middle;opacity:0;";
                if(args.embed_content.includes("youtu")) {
                    video.setAttribute('data-setup','{ "techOrder":["youtube"],"sources":[{"type":"video/youtube","src":"'+args.embed_content+'"}],"youtube":{"rel":0,"modestbranding":1,"controls":0,"iv_load_policy":3,"loop":1,"playsinline":1,"showinfo":0,"fs":0,"disablekb":1,"autoplay":'+embed_video_autoplay+',"muted":'+embed_video_muted+'}}');
                } else {
                    var source = document.createElement('source');
                    source.src = args.embed_content+'#t=2';
                    source.type = 'video/mp4';
                    video.appendChild(source);
                }
                hotSpotDiv.appendChild(video);
                try {
                    if (typeof video_embeds[id] !== 'undefined') {
                        video_embeds[id].dispose();
                    }
                } catch (e) {}
                video_embeds[id] = videojs('video_embed_'+id, {
                    controlBar: {
                        fullscreenToggle: false,
                        pictureInPictureToggle: false
                    }
                }, function() {
                    $('.poi_embed_'+id+' video').css({'width':'100%','height':'100%'});
                });
                $('.poi_embed_'+id).on('touchstart click', function (e) {
                    if(e.target.className=='vjs-icon-placeholder') {
                        return;
                    }
                    if(video_embeds[id].paused()) {
                        video_embeds[id].play();
                    } else {
                        video_embeds[id].pause();
                    }
                });
                if(navigator.userAgent.toLowerCase().indexOf('mobi') >= 0) {
                    $('.poi_embed_'+id+' .vjs-control-bar').css('display','none');
                    $('.poi_embed_'+id+' .vjs-big-play-button').css('font-size','1em');
                } else {
                    video_embeds[id].one('play', function () {
                        this.currentTime(0);
                    });
                }
                if(parseInt(args.embed_video_autoplay)==1) {
                    video_embeds[id].oncanplay = function () {
                        video_embeds[id].play();
                    };
                }
                break;
            case 'link':
                var div = document.createElement('div');
                div.classList.add('poi_embed_link');
                div.classList.add('poi_embed_link_'+id);
                div.style = "width:"+width+";height:"+height+";margin: 0 auto;vertical-align:middle;opacity:0;";
                var html = '<iframe frameborder="0" marginheight="0" marginwidth="0" style="border:none;" width="'+width+'px" height="'+height+'px" src="'+args.embed_content+'"></iframe>';
                div.innerHTML=html;
                hotSpotDiv.appendChild(div);
                break;
        }
        embed_pois_contents(hotSpotDiv,view_type,args,caption);
    }

    function remove_background_video_chroma(video_chroma,ctx_chroma_tmp,ctx_chroma,width,height,params,force=false) {
        if (!force && (video_chroma.paused || video_chroma.ended)) { return;}
        ctx_chroma_tmp.drawImage(video_chroma, 0, 0, width , height );
        let frame = ctx_chroma_tmp.getImageData(0, 0, width , height );
        var params_array = params.split(",");
        var bg_r = parseInt(params_array[0]);
        var bg_g = parseInt(params_array[1]);
        var bg_b = parseInt(params_array[2]);
        var t = parseInt(params_array[3]);
        for (let i = 0; i < frame.data.length /4; i++) {
            let r = frame.data[i * 4 + 0];
            let g = frame.data[i * 4 + 1];
            let b = frame.data[i * 4 + 2];
            if (r > (bg_r-t) && r < (bg_r+t) && g > (bg_g-t) && g < (bg_g+t) && b > (bg_b-t) && b < (bg_b+t)) {
                frame.data[i * 4 + 3] = 0;
            }
        }
        ctx_chroma.putImageData(frame, 0, 0);
        if(!force) setTimeout(function () {
            remove_background_video_chroma(video_chroma,ctx_chroma_tmp,ctx_chroma,width,height,params,false);
        }, 0);
    }

    function hotspot_embed_m(hotSpotDiv, args) {
        if(vr_enabled && args.embed_type=='selection') {
            hotspot(hotSpotDiv, args);
            return;
        }
        var id = args.id;
        var type = args.embed_type;
        var size = args.embed_size.split(",");
        var width = size[0];
        var height = size[1];
        hotSpotDiv.setAttribute('draggable',false);
        hotSpotDiv.classList.add('noselect');
        hotSpotDiv.classList.add('marker_embed');
        hotSpotDiv.classList.add('marker_embed_'+id);
        hotSpotDiv.classList.add('hotspot_'+id);
        if(args.css_class!='') {
            var array_css_class = args.css_class.split(" ");
            jQuery.each(array_css_class, function(index_c, css_class) {
                hotSpotDiv.classList.add(css_class);
            });
        }
        hotSpotDiv.setAttribute('data-id',id);
        hotSpotDiv.setAttribute('data-type',type);
        hotSpotDiv.setAttribute('data-transform3d',args.transform3d);
        hotSpotDiv.setAttribute('data-initialized',0);
        hotSpotDiv.classList.add('custom-tooltip');
        if(hide_markers==1) {
            hotSpotDiv.classList.add('hidden_m');
        }
        hotSpotDiv.classList.add('marker_'+args.id);
        hotSpotDiv.setAttribute('data-id', args.id);
        hotSpotDiv.addEventListener("mouseover", function (e) {
            if(controls_status['icons']==true && !window.is_mobile) {
                var id = e.target.getAttribute('data-id');
                if(id !== null) {
                    $('.tooltip_marker_'+id).css('opacity',1);
                }
            }
        });
        hotSpotDiv.addEventListener("mouseenter", function (e) {
            if(controls_status['icons']==true && !window.is_mobile) {
                var id = e.target.getAttribute('data-id');
                if(id !== null) {
                    $('.tooltip_marker_'+id).css('opacity',1);
                }
            }
        });
        hotSpotDiv.addEventListener("mouseleave", function (e) {
            if(controls_status['icons']==true && !window.is_mobile) {
                var id = e.target.getAttribute('data-id');
                if(id !== null) {
                    $('.tooltip_marker_'+id).css('opacity',0);
                }
            }
        });
        var markers_show_room_t = parseInt(args.show_room);
        if(vr_enabled) markers_show_room_t = 0;
        if(markers_show_room_t==5) {
            if(args.tooltip_type=='preview' || args.tooltip_type=='preview_square' || args.tooltip_type=='preview_rect') {
                args.tooltip_type = 'room_name';
            }
        }
        switch(args.tooltip_type) {
            case 'text':
                if(args.tooltip_text !== null && args.tooltip_text !== '' && args.tooltip_text !== '<p><br></p>') {
                    var tooltip = document.createElement('div');
                    tooltip.classList.add('tooltip_marker_'+args.id);
                    tooltip.classList.add('tooltip_text_embed_m');
                    tooltip.innerHTML = args.tooltip_text.toUpperCase();
                    hotSpotDiv.appendChild(tooltip);
                }
                break;
            case 'room_name':
                var tooltip = document.createElement('div');
                tooltip.classList.add('tooltip_marker_'+args.id);
                tooltip.classList.add('tooltip_text_embed_m');
                tooltip.innerHTML = args.name_room_target.toUpperCase();
                hotSpotDiv.appendChild(tooltip);
                break;
            case 'preview':
                var index = get_id_viewer(args.id_room_target);
                var image = panoramas[index].thumb_image;
                var tooltip = document.createElement('div');
                tooltip.classList.add('tooltip_marker_'+args.id);
                tooltip.classList.add('tooltip_preview_m');
                tooltip.innerHTML = '<div style="width:100%;height:100%;border-radius:50px;background-image: url('+image+');background-size: cover;background-position: center;"></div>';
                hotSpotDiv.appendChild(tooltip);
                break;
            case 'preview_square':
                var index = get_id_viewer(args.id_room_target);
                var image = panoramas[index].thumb_image;
                var tooltip = document.createElement('div');
                tooltip.classList.add('tooltip_marker_'+args.id);
                tooltip.classList.add('tooltip_preview_square_m');
                tooltip.innerHTML = '<div style="width:100%;height:100%;background-image: url('+image+');background-size: cover;background-position: center;"></div>';
                hotSpotDiv.appendChild(tooltip);
                break;
            case 'preview_rect':
                var index = get_id_viewer(args.id_room_target);
                var image = panoramas[index].thumb_image;
                var tooltip = document.createElement('div');
                tooltip.classList.add('tooltip_marker_'+args.id);
                tooltip.classList.add('tooltip_preview_rect_m');
                tooltip.innerHTML = '<div style="width:100%;height:100%;background-image: url('+image+');background-size: cover;background-position: center;"></div>';
                hotSpotDiv.appendChild(tooltip);
                break;
        }
        switch(type) {
            case 'selection':
                var div = document.createElement('div');
                div.setAttribute('draggable',false);
                var bg_color = args.background;
                var color = args.color;
                div.style = "width:"+width+"px;height:"+height+"px;margin: 0 auto;vertical-align:middle;opacity:0;background-color:"+bg_color+";border-color:"+color+";-webkit-box-shadow:none;-moz-box-shadow:none;box-shadow:none;"+args.embed_content;
                div.id = "marker_embed_selection_"+args.id;
                div.classList.add('marker_embed_selection');
                div.setAttribute('data-id',args.id);
                if(args.type=='' || args.type==null) {
                    hotSpotDiv.classList.add('poi_not_selectable');
                } else {
                    div.addEventListener("mouseover", function (e) {
                        $('#marker_embed_selection_'+e.target.getAttribute('data-id')).css('-webkit-box-shadow','0 0 20px '+color);
                        $('#marker_embed_selection_'+e.target.getAttribute('data-id')).css('-moz-box-shadow','0 0 20px '+color);
                        $('#marker_embed_selection_'+e.target.getAttribute('data-id')).css('box-shadow','0 0 20px '+color);
                    });
                    div.addEventListener("mouseenter", function (e) {
                        $('#marker_embed_selection_'+e.target.getAttribute('data-id')).css('-webkit-box-shadow','0 0 20px '+color);
                        $('#marker_embed_selection_'+e.target.getAttribute('data-id')).css('-moz-box-shadow','0 0 20px '+color);
                        $('#marker_embed_selection_'+e.target.getAttribute('data-id')).css('box-shadow','0 0 20px '+color);
                    });
                    div.addEventListener("mouseleave", function (e) {
                        $('#marker_embed_selection_'+e.target.getAttribute('data-id')).css('-webkit-box-shadow','none');
                        $('#marker_embed_selection_'+e.target.getAttribute('data-id')).css('-moz-box-shadow','none');
                        $('#marker_embed_selection_'+e.target.getAttribute('data-id')).css('box-shadow','none');
                    });
                }
                hotSpotDiv.appendChild(div);
                break;
        }
    }

    function supportsHEVCAlpha() {
        const navigator = window.navigator;
        const ua = navigator.userAgent.toLowerCase();
        const hasMediaCapabilities = !!(navigator.mediaCapabilities && navigator.mediaCapabilities.decodingInfo);
        const isSafari = ((ua.indexOf('safari') != -1) && (!(ua.indexOf('chrome')!= -1) && (ua.indexOf('version/')!= -1)));
        return isSafari && hasMediaCapabilities;
    }

    function _checkAutoPlay(p,id) {
        var s = window['Promise'] ? window['Promise'].toString() : '';
        if (s.indexOf('function Promise()') !== -1 || s.indexOf('function ZoneAwarePromise()') !== -1) {
            p.catch(function() {
                _callback_onAutoplayBlocked(id);
            });
        }
    }

    function _callback_onAutoplayBlocked(id) {
        $('.poi_embed_'+id).css('pointer-events','none');
        $('#video_embed_'+id).css('pointer-events','none');
        $('#canvas_chroma_'+id).css('pointer-events','none');
        $('.poi_embed_'+id+' .div_play_btn').show();
    }

    window.play_video_transparent = function (id) {
        var video = $('.poi_embed_'+id+' video')[0];
        video.play();
        $('.poi_embed_'+id+' .div_play_btn').hide();
    }

    function hotspot_embed_helper(hotSpotDiv, args) {
        hotSpotDiv.setAttribute('draggable',false);
        hotSpotDiv.classList.add('noselect');
        hotSpotDiv.classList.add('poi_embded_helper');
        hotSpotDiv.setAttribute('id','poi_embded_helper_'+args[0]+'_'+args[1]);
        var icon = document.createElement('i');
        icon.classList.add('fas');
        icon.classList.add('fa-circle');
        icon.style = 'font-size:16px;cursor:pointer;color:red;opacity:0.6;';
        hotSpotDiv.append(icon);
    }

    function hotspot_embed_helper_m(hotSpotDiv, args) {
        hotSpotDiv.setAttribute('draggable',false);
        hotSpotDiv.classList.add('noselect');
        hotSpotDiv.classList.add('marker_embded_helper');
        hotSpotDiv.setAttribute('id','marker_embded_helper_'+args[0]+'_'+args[1]);
        var icon = document.createElement('i');
        icon.classList.add('fas');
        icon.classList.add('fa-circle');
        icon.style = 'font-size:16px;cursor:pointer;color:red;opacity:0.6;';
        hotSpotDiv.append(icon);
    }

    window.poi_embed_get_transform = function(from, to) {
        var A, H, b, h, i, k, k_i, l, lhs, m, ref, rhs;
        A = [];
        for (i = k = 0; k < 4; i = ++k) {
            A.push([from[i].x, from[i].y, 1, 0, 0, 0, -from[i].x * to[i].x, -from[i].y * to[i].x]);
            A.push([0, 0, 0, from[i].x, from[i].y, 1, -from[i].x * to[i].y, -from[i].y * to[i].y]);
        }
        b = [];
        for (i = l = 0; l < 4; i = ++l) {
            b.push(to[i].x);
            b.push(to[i].y);
        }
        h = numeric.solve(A, b);
        H = [[h[0], h[1], 0, h[2]], [h[3], h[4], 0, h[5]], [0, 0, 1, 0], [h[6], h[7], 0, 1]];
        for (i = m = 0; m < 4; i = ++m) {
            lhs = numeric.dot(H, [from[i].x, from[i].y, 0, 1]);
            k_i = lhs[3];
            rhs = numeric.dot(k_i, [to[i].x, to[i].y, 0, 1]);
        }
        return H;
    };

    window.poi_embed_apply_transform = function(id_p, element, originalPos, targetPos, callback) {
        var H, from, i, j, p, to;
        from = (function() {
            var k, len, results;
            results = [];
            for (k = 0, len = originalPos.length; k < len; k++) {
                p = originalPos[k];
                results.push({
                    x: p[0] - originalPos[0][0],
                    y: p[1] - originalPos[0][1]
                });
            }
            return results;
        })();
        to = (function() {
            var k, len, results;
            results = [];
            for (k = 0, len = targetPos.length; k < len; k++) {
                p = targetPos[k];
                results.push({
                    x: p[0] - originalPos[0][0],
                    y: p[1] - originalPos[0][1]
                });
            }
            return results;
        })();
        H = poi_embed_get_transform(from, to);
        $(element).css({
            'transform': `matrix3d(${((function() {
                var k, results;
                results = [];
                for (i = k = 0; k < 4; i = ++k) {
                    results.push((function() {
                        var l, results1;
                        results1 = [];
                        for (j = l = 0; l < 4; j = ++l) {
                            results1.push(H[j][i].toFixed(20));
                        }
                        return results1;
                    })());
                }
                return results;
            })()).join(',')})`,
            'transform-origin': '0 0'
        });
        return typeof callback === "function" ? callback(element, H) : void 0;
    };

    window.marker_embed_apply_transform = function(id_m, element, originalPos, targetPos, callback) {
        var H, from, i, j, p, to;
        from = (function() {
            var k, len, results;
            results = [];
            for (k = 0, len = originalPos.length; k < len; k++) {
                p = originalPos[k];
                results.push({
                    x: p[0] - originalPos[0][0],
                    y: p[1] - originalPos[0][1]
                });
            }
            return results;
        })();
        to = (function() {
            var k, len, results;
            results = [];
            for (k = 0, len = targetPos.length; k < len; k++) {
                p = targetPos[k];
                results.push({
                    x: p[0] - originalPos[0][0],
                    y: p[1] - originalPos[0][1]
                });
            }
            return results;
        })();
        H = poi_embed_get_transform(from, to);
        $(element).css({
            'transform': `matrix3d(${((function() {
                var k, results;
                results = [];
                for (i = k = 0; k < 4; i = ++k) {
                    results.push((function() {
                        var l, results1;
                        results1 = [];
                        for (j = l = 0; l < 4; j = ++l) {
                            results1.push(H[j][i].toFixed(20));
                        }
                        return results1;
                    })());
                }
                return results;
            })()).join(',')})`,
            'transform-origin': '0 0'
        });
        return typeof callback === "function" ? callback(element, H) : void 0;
    };

    window.poi_embed_make_transformable = function(selector, id, callback) {
        return $(selector).each(function(i, element) {
            var controlPoints, originalPos, p, position;
            $(element).css('transform', '');
            controlPoints = (function() {
                var k, len, ref, results;
                ref = ['left top', 'left bottom', 'right top', 'right bottom'];
                results = [];
                for (k = 0, len = ref.length; k < len; k++) {
                    position = ref[k];
                    results.push($('<div class="draggable_poi_embed" id="draggable_'+id+'_'+(k+1)+'">').css({
                        position: 'absolute',
                        zIndex: 100000
                    }).appendTo('#draggable_container').position({
                        at: position,
                        of: element,
                        collision: 'none'
                    }));
                }
                return results;
            })();
            originalPos = (function() {
                var k, len, results;
                results = [];
                for (k = 0, len = controlPoints.length; k < len; k++) {
                    p = controlPoints[k];
                    results.push([p.position().left, p.position().top]);
                }
                return results;
            })();
            poi_embed_originals_pos[id] = originalPos;
            return element;
        });
    };

    window.marker_embed_make_transformable = function(selector, id, callback) {
        return $(selector).each(function(i, element) {
            var controlPoints, originalPos, p, position;
            $(element).css('transform', '');
            controlPoints = (function() {
                var k, len, ref, results;
                ref = ['left top', 'left bottom', 'right top', 'right bottom'];
                results = [];
                for (k = 0, len = ref.length; k < len; k++) {
                    position = ref[k];
                    results.push($('<div class="draggable_marker_embed" id="draggable_'+id+'_'+(k+1)+'">').css({
                        position: 'absolute',
                        zIndex: 100000
                    }).appendTo('#draggable_container').position({
                        at: position,
                        of: element,
                        collision: 'none'
                    }));
                }
                return results;
            })();
            originalPos = (function() {
                var k, len, results;
                results = [];
                for (k = 0, len = controlPoints.length; k < len; k++) {
                    p = controlPoints[k];
                    results.push([p.position().left, p.position().top]);
                }
                return results;
            })();
            marker_embed_originals_pos[id] = originalPos;
            return element;
        });
    };

    window.addEventListener("message", api_message, false);

    function api_message(evt) {
        switch(evt.data.payload) {
            case 'goto_room':
                var id = evt.data.id_room;
                goto('',[id,null,null,null,null]);
                break;
            case 'goto_next_room':
                goto_next_room();
                break;
            case 'goto_prev_room':
                goto_prev_room();
                break;
            case 'goto_room_coordinates':
                var coordinates = evt.data.coordinates;
                var lat = coordinates[0];
                var lon = coordinates[1];
                goto_room_coordinates(lat,lon,true);
                break;
        }
    }

    function goto_room_coordinates(lat,lon,api) {
        var min_distance = 99999999;
        var index_panorama = null;
        if(lat!=null && lat.length>0 && lon!=null && lon.length>0) {
            lat = lat.replace(/,/g, '.');
            lon = lon.replace(/,/g, '.');
            lat = parseFloat(lat);
            lon = parseFloat(lon);
            jQuery.each(panoramas, function(index, panorama) {
                var lat_p = panorama.lat;
                var lon_p = panorama.lon;
                if(lat_p!=null && lat_p.length>0 && lon_p!=null && lon_p.length>0) {
                    lat_p = parseFloat(lat_p);
                    lon_p = parseFloat(lon_p);
                    var distance = getDistanceFromLatLonInKm(lat,lon,lat_p,lon_p);
                    if(distance<=min_distance) {
                        min_distance = distance;
                        index_panorama = index;
                    }
                }
            });
        }
        if(index_panorama!=null) {
            if(api) {
                goto('',[panoramas[index_panorama].id,null,null,null,null]);
            } else {
                return index_panorama;
            }
        } else {
            if(api) {
                goto('',[panoramas[index_initial].id,null,null,null,null]);
            } else {
                return false;
            }
        }
    }

    function getDistanceFromLatLonInKm(lat1,lon1,lat2,lon2) {
        var R = 6371;
        var dLat = deg2rad(lat2-lat1);
        var dLon = deg2rad(lon2-lon1);
        var a =
            Math.sin(dLat/2) * Math.sin(dLat/2) +
            Math.cos(deg2rad(lat1)) * Math.cos(deg2rad(lat2)) *
            Math.sin(dLon/2) * Math.sin(dLon/2);
        var c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));
        var d = R * c;
        return d;
    }

    function deg2rad(deg) {
        return deg * (Math.PI/180);
    }

    window.nav_control_cmd = function (cmd) {
        switch (cmd) {
            case 'up':
                if(current_panorama_type=='image' || is_iOS() || current_panorama_type=='hls' || current_panorama_type=='lottie') {
                    var yaw_s = pano_viewer.getYaw();
                    var pitch_s = pano_viewer.getPitch();
                    var hfov_s = pano_viewer.getHfov();
                    pitch_s = pitch_s + 20;
                    pano_viewer.lookAt(pitch_s,yaw_s,hfov_s,500);
                } else {
                    var yaw_s = video_viewer.pnlmViewer.getYaw();
                    var pitch_s = video_viewer.pnlmViewer.getPitch();
                    var hfov_s = video_viewer.pnlmViewer.getHfov();
                    pitch_s = pitch_s + 20;
                    video_viewer.pnlmViewer.lookAt(pitch_s,yaw_s,hfov_s,500);
                }
                break;
            case 'down':
                if(current_panorama_type=='image' || is_iOS() || current_panorama_type=='hls' || current_panorama_type=='lottie') {
                    var yaw_s = pano_viewer.getYaw();
                    var pitch_s = pano_viewer.getPitch();
                    var hfov_s = pano_viewer.getHfov();
                    pitch_s = pitch_s - 20;
                    pano_viewer.lookAt(pitch_s,yaw_s,hfov_s,500);
                } else {
                    var yaw_s = video_viewer.pnlmViewer.getYaw();
                    var pitch_s = video_viewer.pnlmViewer.getPitch();
                    var hfov_s = video_viewer.pnlmViewer.getHfov();
                    pitch_s = pitch_s - 20;
                    video_viewer.pnlmViewer.lookAt(pitch_s,yaw_s,hfov_s,500);
                }
                break;
            case 'left':
                if(current_panorama_type=='image' || is_iOS() || current_panorama_type=='hls' || current_panorama_type=='lottie') {
                    var yaw_s = pano_viewer.getYaw();
                    var pitch_s = pano_viewer.getPitch();
                    var hfov_s = pano_viewer.getHfov();
                    yaw_s = yaw_s - 20;
                    pano_viewer.lookAt(pitch_s,yaw_s,hfov_s,500);
                } else {
                    var yaw_s = video_viewer.pnlmViewer.getYaw();
                    var pitch_s = video_viewer.pnlmViewer.getPitch();
                    var hfov_s = video_viewer.pnlmViewer.getHfov();
                    yaw_s = yaw_s - 20;
                    video_viewer.pnlmViewer.lookAt(pitch_s,yaw_s,hfov_s,500);
                }
                break;
            case 'right':
                if(current_panorama_type=='image' || is_iOS() || current_panorama_type=='hls' || current_panorama_type=='lottie') {
                    var yaw_s = pano_viewer.getYaw();
                    var pitch_s = pano_viewer.getPitch();
                    var hfov_s = pano_viewer.getHfov();
                    yaw_s = yaw_s + 20;
                    pano_viewer.lookAt(pitch_s,yaw_s,hfov_s,500);
                } else {
                    var yaw_s = video_viewer.pnlmViewer.getYaw();
                    var pitch_s = video_viewer.pnlmViewer.getPitch();
                    var hfov_s = video_viewer.pnlmViewer.getHfov();
                    yaw_s = yaw_s + 20;
                    video_viewer.pnlmViewer.lookAt(pitch_s,yaw_s,hfov_s,500);
                }
                break;
            case 'rotate':
                if($('.nav_rotate').hasClass('active_rotate')) {
                    if(current_panorama_type=='image' || is_iOS() || current_panorama_type=='hls' || current_panorama_type=='lottie') {
                        pano_viewer.stopAutoRotate();
                    } else {
                        video_viewer.pnlmViewer.stopAutoRotate();
                    }
                    $('.nav_rotate').removeClass('active_rotate');
                    $('.autorotate_control').removeClass('active_control');
                    $('.autorotate_control .fa-circle').removeClass('active').addClass('not_active');
                    controls_status['auto_rotate']=false;
                } else {
                    if(current_panorama_type=='image' || is_iOS() || current_panorama_type=='hls' || current_panorama_type=='lottie') {
                        pano_viewer.startAutoRotate();
                    } else {
                        video_viewer.pnlmViewer.startAutoRotate();
                    }
                    $('.nav_rotate').addClass('active_rotate');
                    $('.autorotate_control').addClass('active_control');
                    $('.autorotate_control .fa-circle').removeClass('not_active').addClass('active');
                    controls_status['auto_rotate']=true;
                }
                break;
        }
    }

    function idleTimeout() {
        var t;
        window.onload = resetTimer;
        window.onmousemove = resetTimer;
        window.onmousedown = resetTimer;
        window.ontouchstart = resetTimer;
        window.ontouchmove = resetTimer;
        window.onclick = resetTimer;
        window.onkeydown = resetTimer;
        window.addEventListener('scroll', resetTimer, true);
        function timeout_elapsed() {
            in_idle=true;
        }
        function resetTimer() {
            in_idle=false;
            clearTimeout(t);
            t = setTimeout(timeout_elapsed, 300000);
        }
    }

    window.fix_colors_menu = function() {
        var first = null;
        var last = null;
        $('.menu_controls').find("p:not(.hidden)").sort(function(a, b) {
            return parseInt(a.style.order) > parseInt(b.style.order) ? 1 : -1;
        }).each(function () {
            if(first==null) {
                first=$(this);
            }
            last=$(this);
        });
        if(first!=null) {
            var bg_first = first.css('background-color');
            $('.menu_controls .arrow').css('border-bottom','10px solid '+bg_first);
            $('#menu_controls_mt').css('background-color',bg_first);
        }
        if(last!=null) {
            var bg_last = last.css('background-color');
            $('#menu_controls_md').css('background-color',bg_last);
        }
    }

    var computeGroupCenter_dollhouse;
    function init_dollhouse() {
        computeGroupCenter_dollhouse = (function () {
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
        array_dollhouse = JSON.parse(json_dollhouse);
        camera_pos_dollhouse = array_dollhouse.camera;
        rooms_dollhouse = array_dollhouse.rooms;
        levels_dollhouse = array_dollhouse.levels;
        var array_levels_used = [], count_levels_used = 0;
        for(var i=0;i<rooms_dollhouse.length; i++) {
            var level = rooms_dollhouse[i].level;
            if(!array_levels_used.includes(level)) {
                array_levels_used.push(level);
                count_levels_used++;
            }
            if(rooms_dollhouse[i].pointer_visible === undefined) {
                rooms_dollhouse[i].pointer_visible=true;
            }
            if(rooms_dollhouse[i].cube_face_top === undefined) {
                rooms_dollhouse[i].cube_face_top=true;
            }
            if(rooms_dollhouse[i].cube_face_bottom === undefined) {
                rooms_dollhouse[i].cube_face_bottom=true;
            }
            if(rooms_dollhouse[i].cube_face_left === undefined) {
                rooms_dollhouse[i].cube_face_left=true;
            }
            if(rooms_dollhouse[i].cube_face_right === undefined) {
                rooms_dollhouse[i].cube_face_right=true;
            }
            if(rooms_dollhouse[i].cube_face_front === undefined) {
                rooms_dollhouse[i].cube_face_front=true;
            }
            if(rooms_dollhouse[i].cube_face_back === undefined) {
                rooms_dollhouse[i].cube_face_back=true;
            }
        }
        if(count_levels_used>1) {
            var levels_html = '<ul>';
            levels_html += '<li><a onclick="select_level_dollhouse(\'all\',\''+window.viewer_labels.all+'\');return false;" href="#">'+window.viewer_labels.all+'</a></li>';
            for(var i=0;i<levels_dollhouse.length;i++) {
                var id = levels_dollhouse[i].id;
                if(array_levels_used.includes(id)) {
                    var name = levels_dollhouse[i].name;
                    levels_html += '<li><a onclick="select_level_dollhouse(\''+id+'\',\''+name+'\');return false;" href="#">'+name+'</a></li>';
                }
            }
            levels_html += '</ul>';
            document.getElementById('select_level_dollhouse').innerHTML = levels_html;
        } else {
            $('#button_level_dollhouse').addClass('hidden');
        }
        dollhouse_div = document.getElementById('dollhouse');
        var container_dollhouse = document.getElementById('container_dollhouse');
        camera_dollhouse = new THREE.PerspectiveCamera( 75, dollhouse_div.offsetWidth / dollhouse_div.offsetHeight, 5, 100000 );
        camera_dollhouse.position.z = 500;
        camera_dollhouse.position.y = 1200;
        scene_dollhouse = new THREE.Scene();
        renderer_dollhouse = new THREE.WebGLRenderer({
            alpha: true,
            antialias: true
        });
        var background_color = Number("0x"+array_dollhouse.settings.background_color);
        var background_opacity = array_dollhouse.settings.background_opacity;
        renderer_dollhouse.setClearColor(new THREE.Color(background_color));
        renderer_dollhouse.setClearAlpha(background_opacity);
        renderer_dollhouse.setPixelRatio(1);
        renderer_dollhouse.setSize( dollhouse_div.offsetWidth, dollhouse_div.offsetHeight );
        container_dollhouse.appendChild( renderer_dollhouse.domElement );
        controls_dollhouse = new THREE.OrbitControls(camera_dollhouse, renderer_dollhouse.domElement);
        controls_dollhouse.enableDamping = true;
        controls_dollhouse.dampingFactor = 0.1;
        controls_dollhouse.minDistance = 100;
        controls_dollhouse.maxDistance = 5000;
        domEvents_dollhouse = new THREEx.DomEvents(camera_dollhouse, renderer_dollhouse.domElement);
        window.addEventListener( 'resize', onWindowResize_dollhouse, false );
        loading_dollhouse();
        animate_dollhouse();
    }

    window.toggle_dollhouse_help = function() {
        if(window.is_mobile) {
            if($('#info_dollhouse_mobile').is(':visible')) {
                $('#info_dollhouse_mobile').fadeOut();
            } else {
                $('#info_dollhouse_mobile').fadeIn();
            }
        } else {
            if($('#info_dollhouse_pc').is(':visible')) {
                $('#info_dollhouse_pc').fadeOut();
            } else {
                $('#info_dollhouse_pc').fadeIn();
            }
        }
    }

    function loading_dollhouse() {
        for(var i=0;i<rooms_dollhouse.length;i++) {
            var id_room = rooms_dollhouse[i].id;
            var index = get_id_viewer(id_room);
            var panorama_image = panoramas[index].panorama_3d;
            textures_dollhouse[i] = new THREE.TextureLoader().load( panorama_image, function () {
                count_loaded_texture_dollhouse++;
            });
        }
        interval_load_texture_dollhouse = setInterval(function () {
            if(count_loaded_texture_dollhouse>=rooms_dollhouse.length) {
                clearInterval(interval_load_texture_dollhouse);
                for(var i=0; i<=5; i++) {
                    if( group_rooms_dollhouse[i] === undefined ) {
                        group_rooms_dollhouse[i] = new THREE.Group();
                    }
                }
                for(var i=0;i<rooms_dollhouse.length;i++) {
                    draw_room_dollhouse(i);
                }
                render_labels_dollhouse();
                for(var i=0; i<group_rooms_dollhouse.length; i++) {
                    scene_dollhouse.add(group_rooms_dollhouse[i]);
                }
                if(camera_pos_dollhouse!='' && camera_pos_dollhouse!==undefined) {
                    camera_dollhouse.position.copy(camera_pos_dollhouse.cameraPosition);
                    controls_dollhouse.target.copy(camera_pos_dollhouse.targetPosition);
                } else {
                    var center = computeGroupCenter_dollhouse(group_rooms_dollhouse);
                    controls_dollhouse.target.set(center.x, center.y, center.z);
                }
                var container_dollhouse = document.getElementById('container_dollhouse');
                container_dollhouse.addEventListener('mousemove', onDocumentMouseMove_pointer_dollhouse, false );
                document.getElementById('button_level_dollhouse').style.display = 'block';
                $('.dollhouse_control').removeClass('disabled');
                set_dollhouse_position(current_id_panorama);
                setTimeout(function () {
                    dollhouse_loaded = true;
                },5000);
                if(show_dollhouse==2) {
                    view_dollhouse();
                }
            }
        },100);
    }

    function setOpacity_group_dollhouse(group,level,opacity) {
        group[level].children.forEach(function(child){
            if(child.userData.type=='room') {
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
            }
        });
        for(var i=0; i<pointers_c_dollhouse.length; i++) {
            if(pointers_c_dollhouse[i].userData.level==level) {
                if(opacity<1) {
                    pointers_c_dollhouse[i].visible = false;
                    pointers_t_dollhouse[i].visible = false;
                } else {
                    if(pointers_c_dollhouse[i].userData.visible) {
                        pointers_c_dollhouse[i].visible = true;
                        pointers_t_dollhouse[i].visible = true;
                    }
                }
            }
        }
    };

    function onWindowResize_dollhouse() {
        camera_dollhouse.aspect = dollhouse_div.offsetWidth / dollhouse_div.offsetHeight;
        camera_dollhouse.updateProjectionMatrix();
        renderer_dollhouse.setSize( dollhouse_div.offsetWidth, dollhouse_div.offsetHeight );
        if(css_renderer_dollhouse!=null) {
            css_renderer_dollhouse.setSize( dollhouse_div.offsetWidth, dollhouse_div.offsetHeight );
        }
    }

    function animate_dollhouse() {
        if(dollhouse_open || !dollhouse_loaded) {
            requestAnimationFrame( animate_dollhouse );
            update_dollhouse();
        }
    }

    function update_dollhouse() {
        controls_dollhouse.update();
        TWEEN.update();
        renderer_dollhouse.render( scene_dollhouse, camera_dollhouse );
        if(css_renderer_dollhouse!=null) {
            css_renderer_dollhouse.render(scene_dollhouse, camera_dollhouse);
        }
    }

    function adjust_camera_dollhouse_rotation(rotation, target) {
        var target_new = new THREE.Vector3().copy(target);
        rotation = rotation % 360;
        rotation = (rotation + 360) % 360;
        if(rotation>=0 && rotation<=90) {
            var ratio_x = rotation/90;
            var x = (ratio_x*100)*-1;
            var z = 100-(ratio_x*100);
            target_new.x += x;
            target_new.z += z;
        }
        if(rotation>90 && rotation<=180) {
            var ratio_x = (180-rotation)/90;
            var x = (ratio_x*100)*-1;
            var z = (100-(ratio_x*100))*-1;
            target_new.x += x;
            target_new.z += z;
        }
        if(rotation>180 && rotation<=270) {
            var ratio_x = (270-rotation)/90;
            var x = 100-(ratio_x*100);
            var z = (ratio_x*100)*-1;
            target_new.x += x;
            target_new.z += z;
        }
        if(rotation>270 && rotation<=360) {
            var ratio_x = (360-rotation)/90;
            var x = (ratio_x*100);
            var z = 100-(ratio_x*100);
            target_new.x += x;
            target_new.z += z;
        }
        return target_new;
    }

    function tweenCamera_dollhouse(targetPosition, duration, rotation, id) {
        var list_label_remove = [];
        scene_dollhouse.traverse(function(obj) {
            if (obj.name=="label_pointer_dollhouse") {
                list_label_remove.push(obj);
            }
        });
        for (var i=0; i<list_label_remove.length; i++) {
            scene_dollhouse.remove(list_label_remove[i]);
        }
        css_renderer_dollhouse = null;
        document.getElementById('css_container_dollhouse').innerHTML='';
        controls_dollhouse.enabled = false;
        is_animating_pointer_dollhouse = true;
        var position = new THREE.Vector3().copy(camera_dollhouse.position);
        var target = new THREE.Vector3().copy(targetPosition);
        target = adjust_camera_dollhouse_rotation(rotation,target);
        for(var i=0; i<pointers_c_dollhouse.length; i++) {
            pointers_c_dollhouse[i].visible = false;
            pointers_t_dollhouse[i].visible = false;
        }
        TWEEN.removeAll();
        new TWEEN.Tween(position).to(targetPosition, duration)
            .easing(TWEEN.Easing.Circular.Out)
            .onUpdate(function () {
                camera_dollhouse.position.copy(position);
            }).onComplete(function () {
            camera_dollhouse.position.copy(targetPosition);
            controls_dollhouse.enabled = true;
            is_animating_pointer_dollhouse = false;
        }).start();
        new TWEEN.Tween(controls_dollhouse.target).to(target, duration)
            .easing(TWEEN.Easing.Circular.Out)
            .onUpdate(function () {
                camera_dollhouse.lookAt(controls_dollhouse.target);
            }).onComplete(function () {
            camera_dollhouse.lookAt(target);
        }).start();
    }

    function tweenCamera_dollhouse_out(duration) {
        controls_dollhouse.enabled = false;
        is_animating_pointer_dollhouse = true;
        var position = new THREE.Vector3().copy(camera_dollhouse.position);
        var position_controls = new THREE.Vector3().copy(controls_dollhouse.target);
        if(camera_pos_dollhouse!='' && camera_pos_dollhouse!==undefined) {
            var target = new THREE.Vector3().copy(camera_pos_dollhouse.cameraPosition);
            var target_controls = new THREE.Vector3().copy(camera_pos_dollhouse.targetPosition);
        } else {
            var center = computeGroupCenter_dollhouse(group_rooms_dollhouse);
            var target = new THREE.Vector3(center.x-600, center.y+1200, center.z-600);
            var target_controls = new THREE.Vector3(center.x, center.y, center.z);
        }
        for(var i=0; i<pointers_c_dollhouse.length; i++) {
            pointers_c_dollhouse[i].visible = pointers_c_dollhouse[i].userData.visible;
            pointers_t_dollhouse[i].visible = pointers_t_dollhouse[i].userData.visible;
        }
        TWEEN.removeAll();
        new TWEEN.Tween(position).to(target, duration)
            .easing(TWEEN.Easing.Circular.In)
            .onUpdate(function () {
                camera_dollhouse.position.copy(position);
            }).onComplete(function () {
            camera_dollhouse.position.copy(target);
            controls_dollhouse.enabled = true;
            is_animating_pointer_dollhouse = false;
        }).start();
        new TWEEN.Tween(position_controls).to(target_controls, duration)
            .easing(TWEEN.Easing.Circular.In)
            .onUpdate(function () {
                controls_dollhouse.target.copy(position_controls);
            }).onComplete(function () {
            controls_dollhouse.target.copy(target_controls);
        }).start();
    }

    window.select_level_dollhouse = function(level,name) {
        document.getElementById('button_level_dollhouse').innerHTML = '<i class="fas fa-layer-group"></i>&nbsp;&nbsp;'+name;
        level_sel_dollhouse = level;
        if(level=='all') {
            for(var i=0; i<group_rooms_dollhouse.length; i++) {
                setOpacity_group_dollhouse(group_rooms_dollhouse,i,1);
            }
        } else {
            for(var i=0; i<group_rooms_dollhouse.length; i++) {
                if(i==level) {
                    setOpacity_group_dollhouse(group_rooms_dollhouse,i,1);
                } else {
                    setOpacity_group_dollhouse(group_rooms_dollhouse,i,0.1);
                }
            }
        }
    }

    function draw_room_dollhouse(index) {
        var id = rooms_dollhouse[index].id;
        var index_panorama = get_id_viewer(id);
        var level = rooms_dollhouse[index].level;
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
        var rotation = rooms_dollhouse[index].rotation;
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
        var yaw = panoramas[index_panorama].yaw;
        if(rotation!=0) {
            var rotation_target = yaw-(rotation*360);
        } else {
            var rotation_target = yaw;
        }
        center_x = cube_width * center_x;
        center_y = cube_height * center_y;
        center_z = cube_depth * center_z;
        rx_offset = cube_width * rx_offset;
        ry_offset = cube_height * ry_offset;
        rz_offset = cube_depth * rz_offset;
        var x_pos_s = x_pos + (cube_width/2);
        var y_pos_s = y_pos + (cube_height/2);
        var z_pos_s = z_pos + (cube_depth/2);
        create_pointer_dollhouse(id,x_pos_s,y_pos,z_pos_s,level,cube_height,rotation_target,pointer_offset_x,pointer_offset_z,pointer_visible);
        textures_dollhouse[index].wrapS = THREE.RepeatWrapping;
        textures_dollhouse[index].magFilter = THREE.NearestFilter;
        textures_dollhouse[index].minFilter = THREE.NearestFilter;
        var MaxAnisotropy = renderer_dollhouse.capabilities.getMaxAnisotropy()/2;
        if(MaxAnisotropy<1) MaxAnisotropy=1;
        textures_dollhouse[index].anisotropy = MaxAnisotropy;
        textures_dollhouse[index].offset.x = rotation;
        geometries_dollhouse[index] = new THREE.BoxBufferGeometry(cube_width, cube_height, cube_depth, 64, 64, 64).toNonIndexed();
        geometries_dollhouse[index].scale(-1, 1, 1);
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
        if(cube_face_front) {
            var material1 = new THREE.MeshBasicMaterial( { map: textures_dollhouse[index], transparent: true, opacity: 1 } );
        } else {
            var material1 = new THREE.MeshBasicMaterial( { map: textures_dollhouse[index], transparent: true, opacity: 0, depthWrite: false } );
        }
        if(cube_face_back) {
            var material2 = new THREE.MeshBasicMaterial( { map: textures_dollhouse[index], transparent: true, opacity: 1 } );
        } else {
            var material2 = new THREE.MeshBasicMaterial( { map: textures_dollhouse[index], transparent: true, opacity: 0, depthWrite: false } );
        }
        if(cube_face_top) {
            var material3 = new THREE.MeshBasicMaterial( { map: textures_dollhouse[index], transparent: true, opacity: 1 } );
        } else {
            var material3 = new THREE.MeshBasicMaterial( { map: textures_dollhouse[index], transparent: true, opacity: 0, depthWrite: false } );
        }
        if(cube_face_bottom) {
            var material4 = new THREE.MeshBasicMaterial( { map: textures_dollhouse[index], transparent: true, opacity: 1 } );
        } else {
            var material4 = new THREE.MeshBasicMaterial( { map: textures_dollhouse[index], transparent: true, opacity: 0, depthWrite: false } );
        }
        if(cube_face_left) {
            var material5 = new THREE.MeshBasicMaterial( { map: textures_dollhouse[index], transparent: true, opacity: 1 } );
        } else {
            var material5 = new THREE.MeshBasicMaterial( { map: textures_dollhouse[index], transparent: true, opacity: 0, depthWrite: false } );
        }
        if(cube_face_right) {
            var material6 = new THREE.MeshBasicMaterial( { map: textures_dollhouse[index], transparent: true, opacity: 1 } );
        } else {
            var material6 = new THREE.MeshBasicMaterial( { map: textures_dollhouse[index], transparent: true, opacity: 0, depthWrite: false } );
        }
        meshes_dollhouse[index] = new THREE.Mesh( geometries_dollhouse[index], [material1,material2,material3,material4,material5, material6] );
        meshes_dollhouse[index].userData = { type:'room',level:level, index:index, id:id};
        meshes_dollhouse[index].position.set(x_pos_s, y_pos_s, z_pos_s);
        group_rooms_dollhouse[level].add(meshes_dollhouse[index]);
    }

    function onDocumentMouseMove_pointer_dollhouse(event) {
        var mouse = new THREE.Vector2();
        mouse.x = ( (event.clientX - dollhouse_div.offsetLeft) / dollhouse_div.clientWidth ) * 2 - 1;
        mouse.y = ( (event.clientY - dollhouse_div.offsetTop) / dollhouse_div.clientHeight ) * -2 + 1;
        var raycaster = new THREE.Raycaster();
        raycaster.setFromCamera( mouse, camera_dollhouse );
        var objects_raycast = [];
        for(var i=0; i<scene_dollhouse.children.length; i++) {
            if(scene_dollhouse.children[i].type=='Group') {
                for(var k=0; k<scene_dollhouse.children[i].children.length; k++) {
                    if(level_sel_dollhouse=='all' || scene_dollhouse.children[i].children[k].userData.level==level_sel_dollhouse) {
                        objects_raycast.push(scene_dollhouse.children[i].children[k]);
                    }
                }
            }
        }
        var intersects = raycaster.intersectObjects( objects_raycast );
        for(var i=0; i<pointers_c_dollhouse.length; i++) {
            pointers_c_dollhouse[i].material.opacity=0.2;
            pointers_t_dollhouse[i].material.opacity=0.6;
        }
        var labels_pointer = document.getElementsByClassName('label_pointer_dollhouse');
        for(var i=0; i<labels_pointer.length; i++) {
            labels_pointer[i].classList.remove('label_pointer_dollhouse_active');
        }
        document.body.style.cursor = 'default';
        can_click_pointer_dollhouse = false;
        if(intersects.length > 0) {
            if(intersects[0].object.userData.type !== undefined) {
                if(intersects[0].object.userData.type == 'pointer') {
                    var id = intersects[0].object.userData.id;
                    var torus = scene_dollhouse.getObjectByName("pointer_t_"+id);
                    var circle = scene_dollhouse.getObjectByName("pointer_c_"+id);
                    if(circle.visible) {
                        torus.material.opacity=1;
                        circle.material.opacity=0.4;
                        try {
                            document.getElementById('label_pointer_dollhouse_'+id).classList.add('label_pointer_dollhouse_active');
                        } catch (e) {}
                        document.body.style.cursor = 'pointer';
                        can_click_pointer_dollhouse = true;
                    }
                }
            }
        }
    }

    function create_pointer_dollhouse(id,pos_x,pos_y,pos_z,level,cube_height,rotation,pointer_offset_x,pointer_offset_z,visible) {
        var geometry = new THREE.TorusGeometry( 20, 2, 2, 32 );
        if(current_id_panorama==id) {
            var color = Number("0x"+array_dollhouse.settings.pointer_color_active);
        } else {
            var color = Number("0x"+array_dollhouse.settings.pointer_color);
        }
        var material = new THREE.MeshBasicMaterial( { color: color, transparent: false, opacity: 0.6 } );
        var torus = new THREE.Mesh(geometry, material);
        torus.position.set(pos_x+pointer_offset_x, pos_y+2, pos_z+pointer_offset_z);
        torus.rotation.x = Math.PI / 2;
        torus.userData = { type:'pointer',level:level, id:id, visible:visible};
        var geometry = new THREE.CircleGeometry( 20, 32 );
        var material = new THREE.MeshBasicMaterial( { color: color, transparent: true, opacity: 0.2, side: THREE.DoubleSide } );
        var circle = new THREE.Mesh(geometry, material);
        circle.renderOrder = 1;
        circle.position.set(pos_x+pointer_offset_x, pos_y+2, pos_z+pointer_offset_z);
        circle.rotation.x = -Math.PI / 2;
        circle.userData = { type:'pointer',level:level, id:id, visible:visible};
        torus.name = "pointer_t_"+id;
        circle.name = "pointer_c_"+id;
        if(visible) {
            domEvents_dollhouse.addEventListener(torus, 'click', function(event){
                click_pointer_dollhouse(id,pos_x,pos_y+(cube_height/2),pos_z,rotation);
            }, false);
            domEvents_dollhouse.addEventListener(torus, 'touchend', function(event){
                click_pointer_dollhouse(id,pos_x,pos_y+(cube_height/2),pos_z,rotation);
            }, false);
            domEvents_dollhouse.addEventListener(circle, 'click', function(event){
                click_pointer_dollhouse(id,pos_x,pos_y+(cube_height/2),pos_z,rotation);
            }, false);
            domEvents_dollhouse.addEventListener(circle, 'touchend', function(event){
                click_pointer_dollhouse(id,pos_x,pos_y+(cube_height/2),pos_z,rotation);
            }, false);
        }
        torus.visible = visible;
        circle.visible = visible;
        pointers_t_dollhouse.push(torus);
        pointers_c_dollhouse.push(circle);
        group_rooms_dollhouse[level].add(circle);
        group_rooms_dollhouse[level].add(torus);
    }

    function render_labels_dollhouse() {
        var css_container = document.getElementById( 'css_container_dollhouse' );
        css_renderer_dollhouse = new THREE.CSS2DRenderer();
        css_renderer_dollhouse.setSize( dollhouse_div.offsetWidth, dollhouse_div.offsetHeight );
        css_renderer_dollhouse.domElement.style.position = 'absolute';
        css_renderer_dollhouse.domElement.style.top = '0px';
        css_renderer_dollhouse.domElement.style.pointerEvents = 'none';
        css_container.appendChild( css_renderer_dollhouse.domElement );
        for(var i=0; i<rooms_dollhouse.length; i++) {
            var id = parseInt(rooms_dollhouse[i].id);
            var index_panorama = get_id_viewer(id);
            var name = panoramas[index_panorama].name;
            var level = rooms_dollhouse[i].level;
            var cube_width = rooms_dollhouse[i].cube_width;
            var cube_height = rooms_dollhouse[i].cube_height;
            var cube_depth = rooms_dollhouse[i].cube_depth;
            var rotation = rooms_dollhouse[i].rotation;
            var x_pos = rooms_dollhouse[i].x_pos;
            var y_pos = 0;
            for(var l=0; l<levels_dollhouse.length;l++) {
                if(level == levels_dollhouse[l].id) {
                    y_pos = levels_dollhouse[l].y_pos;
                }
            }
            var z_pos = rooms_dollhouse[i].z_pos;
            var pointer_offset_x = rooms_dollhouse[i].pointer_offset_x;
            var pointer_offset_z = rooms_dollhouse[i].pointer_offset_z;
            pointer_offset_x = pointer_offset_x * (cube_width/2);
            pointer_offset_z = pointer_offset_z * (cube_depth/2);
            var x_pos_s = x_pos + (cube_width/2);
            var z_pos_s = z_pos + (cube_depth/2);
            var y_pos_s = y_pos + (cube_height/2);
            var yaw = panoramas[index_panorama].yaw;
            if(rotation!=0) {
                var rotation_target = yaw-(rotation*360);
            } else {
                var rotation_target = yaw;
            }
            var label_div = document.createElement( 'div' );
            label_div.id = 'label_pointer_dollhouse_'+id;
            label_div.id_room = id;
            label_div.x_pos = x_pos_s;
            label_div.y_pos = y_pos_s;
            label_div.z_pos = z_pos_s;
            label_div.rotation = rotation_target;
            label_div.className = 'label_pointer_dollhouse';
            label_div.textContent = name;
            label_div.addEventListener('click', function(evt){
                click_pointer_dollhouse(evt.currentTarget.id_room,evt.currentTarget.x_pos,evt.currentTarget.y_pos,evt.currentTarget.z_pos,evt.currentTarget.rotation);
            }, false);
            label_div.addEventListener('touchend', function(evt){
                click_pointer_dollhouse(evt.currentTarget.id_room,evt.currentTarget.x_pos,evt.currentTarget.y_pos,evt.currentTarget.z_pos,evt.currentTarget.rotation);
            }, false);
            var label = new THREE.CSS2DObject(label_div);
            label.name = 'label_pointer_dollhouse';
            label.position.set(x_pos_s+pointer_offset_x, y_pos+6, z_pos_s+pointer_offset_z);
            scene_dollhouse.add(label);
        }
    }

    window.view_dollhouse = function () {
        dollhouse_open = true;
        animate_dollhouse();
        if(current_panorama_type=='image' || is_iOS() || current_panorama_type=='hls' || current_panorama_type=='lottie') {
            pano_viewer.stopAutoRotate();
        } else {
            video_viewer.pnlmViewer.stopAutoRotate();
        }
        set_dollhouse_position(current_id_panorama);
        $('#dollhouse').css('z-index',99990);
        $('#dollhouse').animate({opacity: 1}, 400);
        for(var i=0; i<pointers_c_dollhouse.length; i++) {
            pointers_c_dollhouse[i].visible = pointers_c_dollhouse[i].userData.visible;
            pointers_t_dollhouse[i].visible = pointers_t_dollhouse[i].userData.visible;
        }
        select_level_dollhouse('all',window.viewer_labels.all);
        tweenCamera_dollhouse_out(array_dollhouse.settings.zoom_out);
        setTimeout(function () {
            render_labels_dollhouse();
        },array_dollhouse.settings.zoom_out+100);
        $('.info_dollhouse').hide();
    }

    window.close_dollhose = function () {
        var fadeout = 400;
        if(array_dollhouse.settings.zoom_out<800) {
            fadeout = array_dollhouse.settings.zoom_out - array_dollhouse.settings.zoom_out*0.5;
        }
        dollhouse_open = false;
        $('#dollhouse').animate({opacity: 0}, fadeout);
        setTimeout(function () {
            $('#dollhouse').css('z-index',-1);
        },fadeout);
    }

    function click_pointer_dollhouse(id,x_pos,y_pos,z_pos,rotation) {
        if(window.is_mobile) can_click_pointer_dollhouse=true;
        if(can_click_pointer_dollhouse && !is_animating_pointer_dollhouse) {
            var pointer_color_active = Number("0x"+array_dollhouse.settings.pointer_color_active);
            var pointer_color = Number("0x"+array_dollhouse.settings.pointer_color);
            try {
                for(var i=0; i<pointers_c_dollhouse.length; i++) {
                    var id_dh = pointers_c_dollhouse[i].userData.id;
                    if(id==id_dh) {
                        pointers_c_dollhouse[i].material.color.setHex(pointer_color_active);
                        pointers_t_dollhouse[i].material.color.setHex(pointer_color_active);
                    } else {
                        pointers_c_dollhouse[i].material.color.setHex(pointer_color);
                        pointers_t_dollhouse[i].material.color.setHex(pointer_color);
                    }
                }
            } catch (e) {}
            var targetPosition = new THREE.Vector3(x_pos,y_pos,z_pos);
            var duration = array_dollhouse.settings.zoom_in;
            var duration2 = duration - (duration*0.2);
            tweenCamera_dollhouse(targetPosition,duration,rotation,id);
            goto('',[id,null,null,null,null],true);
            setTimeout(function () {
                close_dollhose();
            },duration2);
        }
    }

    function set_dollhouse_position(id_room) {
        for(var i=0; i<rooms_dollhouse.length; i++) {
            var id = rooms_dollhouse[i].id;
            if(id==id_room) {
                var x_pos = rooms_dollhouse[i].x_pos;
                var y_pos = rooms_dollhouse[i].y_pos;
                var z_pos = rooms_dollhouse[i].z_pos;
                var cube_width = rooms_dollhouse[i].cube_width;
                var cube_height = rooms_dollhouse[i].cube_height;
                var cube_depth = rooms_dollhouse[i].cube_depth;
                var rotation = rooms_dollhouse[i].rotation;
                var x_pos_s = x_pos + (cube_width/2);
                var z_pos_s = z_pos + (cube_depth/2);
                var y_pos_s = y_pos + (cube_height/2);
                if(current_panorama_type=='image' || is_iOS() || current_panorama_type=='hls' || current_panorama_type=='lottie') {
                    var current_yaw = parseFloat(pano_viewer.getYaw());
                } else {
                    var current_yaw = parseFloat(video_viewer.pnlmViewer.getYaw());
                }
                if(rotation!=0) {
                    var rotation_target = current_yaw-(rotation*360);
                } else {
                    var rotation_target = current_yaw;
                }
                var targetPosition = new THREE.Vector3(x_pos_s,y_pos_s,z_pos_s);
                tweenCamera_dollhouse(targetPosition,0,rotation_target,id);
            }
        }
    }

    var popupCenter = ({url, title, w, h}) => {
        const dualScreenLeft = window.screenLeft !==  undefined ? window.screenLeft : window.screenX;
        const dualScreenTop = window.screenTop !==  undefined   ? window.screenTop  : window.screenY;
        const width = window.innerWidth ? window.innerWidth : document.documentElement.clientWidth ? document.documentElement.clientWidth : screen.width;
        const height = window.innerHeight ? window.innerHeight : document.documentElement.clientHeight ? document.documentElement.clientHeight : screen.height;
        const systemZoom = width / window.screen.availWidth;
        const left = (width - w) / 2 / systemZoom + dualScreenLeft
        const top = (height - h) / 2 / systemZoom + dualScreenTop
        const newWindow = window.open(url, title, `scrollbars=yes,width=${w / systemZoom}, height=${h / systemZoom}, top=${top}, left=${left}`)
        if (window.focus) newWindow.focus();
    }

    window.open_screencast_app = function() {
        $('#record_button').hide();
        popupCenter({url: window.url_screencast, title: 'screencast', w: 900, h: 600});
    }

})(jQuery);