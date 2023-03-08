(function($) {
    "use strict"; // Start of use strict
    var wizard_tour = null, wizard_tour_open = false, wizard_steps = [];
    var drag_slider = false, drag_slider_start=0, drag_slider_end=0;
    window.login = function () {
        $('#username_l').removeClass("error-highlight");
        $('#password_l').removeClass("error-highlight");
        var username = $('#username_l').val();
        var password = $('#password_l').val();
        var btn_label = $('#btn_login').html();
        $('#btn_login').html('<i class="fas fa-circle-notch fa-spin"></i>');
        $('#btn_login').addClass("disabled");
        $.ajax({
            url: "ajax/login.php",
            type: "POST",
            data: {
                username_svt: username,
                password_svt: password
            },
            async: true,
            success: function (json) {
                var rsp = JSON.parse(json);
                if(rsp.status=='ok') {
                    if(rsp.email=='set') {
                        $('#btn_assoc_email').attr('onclick','associate_email('+rsp.id+')');
                        $('#modal_email').modal('show');
                    } else {
                        location.href="index.php";
                    }
                } else {
                    if(rsp.status=='incorrect_username') {
                        $('#username_l').addClass("error-highlight");
                    }
                    if(rsp.status=='incorrect_password') {
                        $('#password_l').addClass("error-highlight");
                    }
                    $('#btn_login').html(btn_label);
                    $('#btn_login').removeClass("disabled");
                }
            }
        });
    }

    window.session_register = function () {
        $('#modal_register button').addClass("disabled");
        $.ajax({
            url: "ajax/session_register.php",
            type: "POST",
            async: true,
            success: function (json) {
                location.href = 'register.php';
            }
        });
    }

    window.register_account = function () {
        var complete = true;
        var username = $('#username_r').val();
        var email = $('#email_r').val();
        var password = $('#password_r').val();
        var password2 = $('#password2_r').val();
        if(username=='') {
            complete = false;
            $('#username_r').addClass("error-highlight");
        } else {
            $('#username_r').removeClass("error-highlight");
        }
        if(email=='') {
            complete = false;
            $('#email_r').addClass("error-highlight");
        } else {
            $('#email_r').removeClass("error-highlight");
        }
        if(password=='') {
            complete = false;
            $('#password_r').addClass("error-highlight");
        } else {
            $('#password_r').removeClass("error-highlight");
        }
        if(password2=='') {
            complete = false;
            $('#password2_r').addClass("error-highlight");
        } else {
            $('#password2_r').removeClass("error-highlight");
        }
        if((password!='') && (password2!='')) {
            if(password!=password2) {
                complete = false;
                $('#password_r').addClass("error-highlight");
                $('#password2_r').addClass("error-highlight");
            } else {
                $('#password_r').removeClass("error-highlight");
                $('#password2_r').removeClass("error-highlight");
            }
        }
        if(complete) {
            $('#btn_register').addClass("disabled");
            $.ajax({
                url: "ajax/register.php",
                type: "POST",
                data: {
                    username_svt: username,
                    email_svt: email,
                    password_svt: password
                },
                async: true,
                success: function (json) {
                    var rsp = JSON.parse(json);
                    if(rsp.status=='ok') {
                        var id_user = rsp.id_user;
                        if(parseInt(rsp.validate_email)==1) {
                            var hash = rsp.hash;
                            $.ajax({
                                url: "ajax/send_email.php",
                                type: "POST",
                                data: {
                                    type: 'activate',
                                    email: email,
                                    hash: hash
                                },
                                timeout: 15000,
                                async: true,
                                success: function (json) {
                                    var rsp = JSON.parse(json);
                                    $.ajax({
                                        url: "ajax/send_email.php",
                                        type: "POST",
                                        data: {
                                            type: 'notify',
                                            email: '',
                                            id_user: id_user
                                        },
                                        timeout: 15000,
                                        async: true,
                                        success: function () {
                                            if (rsp.status == "ok") {
                                                $('#modal_activate').modal("show");
                                            } else {
                                                alert(rsp.msg);
                                            }
                                        },
                                        error: function(){
                                            if (rsp.status == "ok") {
                                                $('#modal_activate').modal("show");
                                            } else {
                                                alert(rsp.msg);
                                            }
                                        },
                                    });
                                },
                                error: function(){},
                            });
                        } else {
                            $.ajax({
                                url: "ajax/send_email.php",
                                type: "POST",
                                data: {
                                    type: 'notify',
                                    email: '',
                                    id_user: id_user
                                },
                                timeout: 15000,
                                async: true,
                                success: function () {
                                    location.href="index.php";
                                },
                                error: function(){
                                    location.href="index.php";
                                },
                            });
                        }
                    } else {
                        alert(rsp.msg);
                        $('#btn_register').removeClass("disabled");
                    }
                },
                error: function () {
                    alert(window.register_labels.error_msg);
                    $('#btn_register').removeClass("disabled");
                }
            });
        }
    }

    window.close_modal_activation = function () {
        $('#modal_activate').modal("hide");
        location.href="login.php";
    }

    window.send_verification_code = function() {
        var email = $('#email_f').val();
        if(email=='') {
            $('#email_f').addClass("error-highlight");
        } else {
            $('#btn_forgot_code').addClass('disabled');
            $.ajax({
                url: "ajax/send_email.php",
                type: "POST",
                data: {
                    type: 'forgot',
                    email: email
                },
                timeout: 15000,
                async: true,
                success: function (json) {
                    var rsp = JSON.parse(json);
                    if (rsp.status == "ok") {
                        alert(window.login_labels.check_msg);
                    } else {
                        alert(rsp.msg);
                        $('#btn_forgot_code').removeClass('disabled');
                    }
                },
                error: function(){
                    alert(window.login_labels.error_msg);
                    $('#btn_forgot_code').removeClass('disabled');
                },
            });
        }
    }

    window.change_password_forgot = function() {
        var complete = true;
        var forgot_code = $('#forgot_code').val();
        var password = $('#password_f').val();
        var password2 = $('#repeat_password_f').val();

        if(forgot_code=='') {
            complete = false;
            $('#forgot_code').addClass("error-highlight");
        } else {
            $('#forgot_code').removeClass("error-highlight");
        }
        if(password=='') {
            complete = false;
            $('#password_f').addClass("error-highlight");
        } else {
            $('#password_f').removeClass("error-highlight");
        }
        if(password2=='') {
            complete = false;
            $('#repeat_password_f').addClass("error-highlight");
        } else {
            $('#repeat_password_f').removeClass("error-highlight");
        }
        if((password!='') && (password2!='')) {
            if(password!=password2) {
                complete = false;
                $('#password_f').addClass("error-highlight");
                $('#repeat_password_f').addClass("error-highlight");
            } else {
                $('#password_f').removeClass("error-highlight");
                $('#repeat_password_f').removeClass("error-highlight");
            }
        }

        if(complete) {
            $.ajax({
                url: "ajax/change_password_forgot.php",
                type: "POST",
                data: {
                    forgot_code: forgot_code,
                    password: password,
                },
                async: true,
                success: function (json) {
                    var rsp = JSON.parse(json);
                    if (rsp.status == "ok") {
                        alert(window.login_labels.password_success);
                        location.href = 'login.php';
                    } else {
                        alert(rsp.msg);
                    }
                }
            });
        }
    }

    window.associate_email = function(user_id) {
        var email = $('#email').val();
        if(email=='') {
            $('#email').addClass("error-highlight");
        } else {
            $('#btn_assoc_email').addClass('disabled');
            $('#email').removeClass("error-highlight");
            $.ajax({
                url: "ajax/associate_email.php",
                type: "POST",
                data: {
                    user_id: user_id,
                    email: email
                },
                async: true,
                success: function (json) {
                    var rsp = JSON.parse(json);
                    if(rsp.status=='ok') {
                        $('#btn_assoc_email').removeClass('disabled');
                        $('#modal_email').modal('hide');
                        location.href="index.php";
                    } else {
                        $('#email').addClass("error-highlight");
                        $('#btn_assoc_email').removeClass('disabled');
                        alert(rsp.msg);
                    }
                }
            });
        }
    }

    window.logout = function () {
        $.ajax({
            url: "ajax/logout.php",
            type: "POST",
            async: true,
            success: function (json) {
                location.href="login.php";
            }
        });
    }

    window.get_dashboard_stats = function (id_vt) {
        $('#list_visitors i').show();
         $.ajax({
            url: "ajax/get_dashboard_stats.php",
            type: "POST",
            data: {
                id_user: window.id_user,
                id_virtualtour: id_vt
            },
            async: true,
            success: function (json) {
                var rsp = JSON.parse(json);
                $('#num_rooms').html(rsp.count_rooms);
                $('#num_markers').html(rsp.count_markers);
                $('#num_pois').html(rsp.count_pois);
                $('#total_visitors').html(rsp.total_visitors);
                $('#total_online_visitors').html(rsp.total_online_visitors);
                if(id_vt==null) {
                    if(rsp.count_virtual_tours==0) {
                        $('#list_visitors i').hide();
                        $('#no_vt_msg').show();
                    }
                    $('#num_virtual_tours').html(rsp.count_virtual_tours);
                    var visitors = rsp.visitors;
                    var online_visitors = rsp.online_visitors;
                    var html_visitors = "";
                    jQuery.each(visitors, function(index, visitor) {
                        var vt_id = visitor.id;
                        var vt_name = visitor.name;
                        var count = visitor.count;
                        var perc = count / rsp.total_visitors * 100;
                        perc = Math.round(perc);
                        html_visitors += '<h4 style="margin-bottom:1px;" class="small font-weight-bold"><a href="index.php?p=statistics&id_vt='+vt_id+'">'+vt_name+'</a> <span class="float-right"><i class="fas fa-chart-line"></i> <b>'+count+'</b>&nbsp;&nbsp;<span id="online_visitors_'+vt_id+'"><i class="fas fa-eye"></i> <span>--</span></span></span></h4>\n' +
                            '                <div class="progress mb-2">\n' +
                            '                    <div class="progress-bar bg-primary" role="progressbar" style="width: '+perc+'%" aria-valuenow="'+perc+'" aria-valuemin="0" aria-valuemax="100"></div>\n' +
                            '                </div>';
                    });
                    if(html_visitors!='') {
                        $('#list_visitors').html(html_visitors).promise().done(function() {
                            jQuery.each(online_visitors, function(index, online_visitor) {
                                var vt_id = online_visitor.id;
                                var count = online_visitor.count;
                                $('#online_visitors_'+vt_id+' span').html(count);
                                if(count>0) {
                                    $('#online_visitors_'+vt_id).css('color','#4e73df');
                                } else {
                                    $('#online_visitors_'+vt_id).css('color','black');
                                }
                            });
                        });
                    }
                    $('#disk_space_used button').css('opacity',1);
                } else {
                    get_disk_space_stats(id_vt,null);
                }
            }
        });
    };

    window.get_disk_space_stats = function (id_vt,id_user_s) {
        $('#disk_space_used').html('<i class="fas fa-circle-notch fa-spin"></i>');
        var id_user_t=id_user_s;
        if(id_user_t==null) id_user_t=window.id_user;
        $.ajax({
            url: "ajax/get_disk_space_stats.php",
            type: "POST",
            data: {
                id_user: id_user_t,
                id_virtualtour: id_vt
            },
            async: true,
            success: function (json) {
                var rsp = JSON.parse(json);
                $('#disk_space_used').html(rsp.disk_space_used);
            }
        });
    };

    window.get_uploaded_file_size_stats = function (id_user_s) {
        $('#disk_space_used_uploaded').html('<i class="fas fa-circle-notch fa-spin"></i>');
        var id_user_t=id_user_s;
        if(id_user_t==null) id_user_t=window.id_user;
        $.ajax({
            url: "ajax/get_uploaded_file_size_stats.php",
            type: "POST",
            data: {
                id_user: id_user_t
            },
            async: true,
            success: function (json) {
                var rsp = JSON.parse(json);
                $('#disk_space_used_uploaded').html(rsp.disk_space_used);
            }
        });
    };

    window.get_virtual_tours = function (id_category,id_user_sel) {
        $.ajax({
            url: "ajax/get_virtual_tours.php",
            type: "POST",
            data: {
                id_user: window.id_user,
                id_category: id_category,
                id_user_f: id_user_sel
            },
            async: true,
            success: function (json) {
                var rsp = JSON.parse(json);
                parse_virtual_tour_list(rsp.vt_list,rsp.categories,id_category,rsp.users,id_user_sel);
            }
        });
    };

    window.get_maps = function () {
        $.ajax({
            url: "ajax/get_maps.php",
            type: "POST",
            data: {
                id_virtualtour: window.id_virtualtour
            },
            async: true,
            success: function (json) {
                var rsp = JSON.parse(json);
                var maps = rsp.maps;
                var permissions = rsp.permissions;
                parse_map_list(maps,permissions);
            }
        });
    };

    window.get_rooms = function (id_virtualtour,p) {
        $.ajax({
            url: "ajax/get_rooms.php",
            type: "POST",
            data: {
                id_user: window.id_user,
                id_virtualtour: id_virtualtour
            },
            async: true,
            success: function (json) {
                var rsp = JSON.parse(json);
                window.rooms = rsp.rooms;
                var permissions = rsp.permissions;
                switch(p) {
                    case 'list':
                        parse_room_list(window.rooms,permissions);
                        break;
                    case 'marker':
                        parse_room_marker(window.rooms);
                        break;
                    case 'poi':
                        parse_room_poi(window.rooms);
                        break;
                    case 'map':
                        get_option_rooms_target('room_default',0,window.id_room_default,null,null);
                        break;
                }
            }
        });
    };

    window.get_rooms_menu_list  = function (id_virtualtour) {
        $.ajax({
            url: "ajax/get_rooms_menu_list.php",
            type: "POST",
            data: {
                id_user: window.id_user,
                id_virtualtour: id_virtualtour
            },
            async: true,
            success: function (json) {
                var rsp = JSON.parse(json);
                parse_rooms_menu_list(rsp);
            }
        });
    };

    window.get_presentation = function (id_virtualtour) {
        $.ajax({
            url: "ajax/get_presentation.php",
            type: "POST",
            data: {
                id_virtualtour: id_virtualtour
            },
            async: true,
            success: function (json) {
                var rsp = JSON.parse(json);
                array_presentation = rsp;
                parse_presentation_list();
            }
        });
    };


    window.get_map = function (id_virtualtour,id_map) {
        $('#msg_load_map').show();
        $.ajax({
            url: "ajax/get_map.php",
            type: "POST",
            data: {
                id_virtualtour: id_virtualtour,
                id_map: id_map,
                map_type: window.map_type
            },
            async: true,
            success: function (json) {
                var rsp = JSON.parse(json);
                switch (window.map_type) {
                    case 'floorplan':
                        if(rsp.map!="") {
                            window.map_points = rsp.map_points;
                            window.id_map_sel = rsp.id_map;
                            var tmpImg = new Image();
                            tmpImg.src = '../viewer/maps/'+rsp.map;
                            tmpImg.onload = function() {
                                $('#msg_load_map').hide();
                                $('#map_image').attr('src','../viewer/maps/'+rsp.map);
                                parse_map_points(window.map_points);
                                if(rsp.all_points) {
                                    $('#btn_add_point').prop("disabled",true);
                                    $('#btn_add_point').css('opacity',0.3);
                                } else {
                                    $('#btn_add_point').prop("disabled",false);
                                    $('#btn_add_point').css('opacity',1);
                                }
                            };
                        }
                        break;
                    case 'map':
                        window.map_points = rsp.map_points;
                        window.id_map_sel = rsp.id_map;
                        parse_map_points_l(window.map_points);
                        if(rsp.all_points) {
                            $('#btn_add_point').prop("disabled",true);
                            $('#btn_add_point').css('opacity',0.3);
                        } else {
                            $('#btn_add_point').prop("disabled",false);
                            $('#btn_add_point').css('opacity',1);
                        }
                        break;
                }
            }
        });
    };

    window.preview = function(id,container_h) {
        $.ajax({
            url: "ajax/get_code.php",
            type: "POST",
            data: {
                id_virtualtour: id
            },
            async: true,
            success: function (json) {
                var rsp = JSON.parse(json);
                var code = rsp.code;
                if(code!="") {
                    if(rsp.count_rooms>0 || rsp.external==1) {
                        $('#msg_no_room').hide();
                        $('#iframe_div').show();
                        $("#iframe_div").html("<iframe allowfullscreen " +
                            "width=\"100%\" height=\""+container_h+"px\" frameborder=\"0\" scrolling=\"no\" " +
                            "marginheight=\"0\" marginwidth=\"0\" " +
                            "src=\"../viewer/index.php?code="+code+"\"" +
                            "></iframe>");
                    } else {
                        $('#msg_no_room').show();
                        $('#iframe_div').hide();
                    }
                }
            }
        });
    }

    window.add_map_point = function () {
        var id_room = $('#room_select option:selected').attr('id');
        if(window.map_type=='map') {
            var center = window.map_tour_l.getCenter();
            var lat = center.lat;
            var lon = center.lng;
        } else {
            var lat = null;
            var lon = null;
        }
        $.ajax({
            url: "ajax/add_map_point.php",
            type: "POST",
            data: {
                id_room: id_room,
                id_map: window.id_map_sel,
                map_type: window.map_type,
                lat: lat,
                lon: lon
            },
            async: true,
            success: function (json) {
                var rsp = JSON.parse(json);
                if (rsp.status == "ok") {
                    sessionStorage.setItem('add_point',true);
                    if(window.map_type=='map') {
                        var zoom = window.map_tour_l.getZoom();
                        sessionStorage.setItem('zoom',zoom);
                    }
                    location.reload();
                }
            }
        });
    }

    window.save_map_settings = function () {
        var map_name = $('#map_name').val();
        var point_color = $('#point_color').val();
        var point_size = $('#point_size').val();
        var width_d = $('#width_d').val();
        var width_m = $('#width_m').val();
        var north_degree = $('#north_degree').val();
        var zoom_level = $('#zoom_level option:selected').attr('id');
        var zoom_to_point = $('#zoom_to_point').is(':checked');
        var default_view = $('#default_view option:selected').attr('id');
        var info_link = $('#info_link').val();
        var info_type = $('#info_type option:selected').attr('id');
        var id_room_default = $('#room_default option:selected').attr('id');
        if(map_name!='') {
            $('#save_btn .icon i').removeClass('far fa-circle').addClass('fas fa-circle-notch fa-spin');
            $('#save_btn').addClass("disabled");
            $.ajax({
                url: "ajax/save_map_settings.php",
                type: "POST",
                data: {
                    id_map: window.id_map_sel,
                    map_name: map_name,
                    point_color: point_color,
                    point_size: point_size,
                    north_degree: north_degree,
                    zoom_level: zoom_level,
                    zoom_to_point: zoom_to_point,
                    width_d: width_d,
                    width_m: width_m,
                    default_view: default_view,
                    info_link: info_link,
                    info_type: info_type,
                    id_room_default: id_room_default
                },
                async: true,
                success: function (json) {
                    var rsp = JSON.parse(json);
                    if(rsp.status=="ok") {
                        window.map_need_save = false;
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
    }

    window.save_map_point = function () {
        var position = $('#point_pos').val();
        $('#save_btn .icon i').removeClass('far fa-circle').addClass('fas fa-circle-notch fa-spin');
        $.ajax({
            url: "ajax/save_map_point.php",
            type: "POST",
            data: {
                id_room: window.id_room_sel,
                map_type: window.map_type,
                position: position
            },
            async: true,
            success: function (json) {
                var rsp = JSON.parse(json);
                if(rsp.status=="ok") {
                    $('#save_btn .icon i').removeClass('fas fa-circle-notch fa-spin').addClass('fas fa-check');
                    setTimeout(function () {
                        $('#save_btn .icon i').removeClass('fas fa-check').addClass('far fa-circle');
                    },1000);
                } else {
                    $('#save_btn .icon i').removeClass('fas fa-circle-notch fa-spin').addClass('fas fa-times');
                    $('#save_btn').removeClass('btn-success').addClass('btn-danger');
                    setTimeout(function () {
                        $('#save_btn .icon i').removeClass('fas fa-times').addClass('far fa-circle');
                        $('#save_btn').removeClass('btn-danger').addClass('btn-success');
                    },1000);
                }
            }
        });
    }

    function parse_map_points(points) {
        var html_points = '',html_map_select = '';
        jQuery.each(points, function(index, point) {
            var map_id = point.id;
            var map_top = point.map_top;
            var map_left = point.map_left;
            var map_name = point.name;
            var point_color = point.point_color;
            if(map_top!=null) {
                if(point.id_map == window.id_map_sel) {
                    html_points += '<div id="pointer_'+map_id+'" style="top: '+map_top+'px; left: '+map_left+'px; opacity: 0.3;background-color: '+point_color+';" class="pointer"></div>';
                }
            } else {
                html_map_select += '<option id="'+map_id+'">'+map_name+'</option>';
            }
        });
        $('#room_select').empty();
        $('#room_select').html(html_map_select).promise().done(function () {
            $('#room_select').selectpicker('refresh');
            change_preview_room_image_map(null);
        });
        $('#pointers_div').html(html_points).promise().done(function () {
            $('.pointer').css('visibility','hidden');
            $('.pointer').css('width',window.point_size+'px');
            $('.pointer').css('height',window.point_size+'px');
            $( ".pointer" ).draggable({
                containment: '#pointers_div',
                drag: function(event) {
                    var top = $(this).position().top;
                    var left = $(this).position().left;
                    var id = $(this).attr('id').replace('pointer_','');
                    set_point_map_pos(id,top,left);
                    click_map_point(id);
                },
                start: function (event, ui) {
                    $(this).addClass('dragging');
                    $(".pointer").css('opacity',0.3);
                    $(this).css('opacity',1);
                    var id = $(this).attr('id').replace('pointer_','');
                    click_map_point(id);
                },
                stop: function (event, ui) {
                    $(this).removeClass('dragging');
                    var id = $(this).attr('id').replace('pointer_','');
                    click_map_point(id);
                    save_map_point();
                }
            });
            $('.pointer').click(function (event) {
                if (!$(this).hasClass('dragging')) {
                    var top = $(this).position().top;
                    var left = $(this).position().left;
                    $(".pointer").css('opacity',0.3);
                    $(this).css('opacity',1);
                    var id = $(this).attr('id').replace('pointer_','');
                    set_point_map_pos(id,top,left);
                    click_map_point(id);
                }
            });
            setTimeout(function () {
                adjust_points_position();
                $('.pointer').css('visibility','visible');
            },100);
            if(window.id_room_point_sel!='') {
                $('#pointer_'+window.id_room_point_sel).trigger('click');
            }
        });
    }

    function parse_map_points_l(points) {
        var html_map_select = '';
        var point_size = $('#point_size').val();
        if((point_size=='') || (point_size<=0)) {
            point_size=20;
        }
        point_size = parseInt(point_size);
        point_size = point_size*2;
        var bounds = new L.LatLngBounds();
        var points_exist = false;
        var lat_new = '', lon_new = '';
        jQuery.each(points, function(index, point) {
            var id = point.id;
            var name = point.name;
            var lat = point.lat;
            var lon = point.lon;
            if(lat!=null && lat!='') {
                points_exist = true;
                lat = parseFloat(lat);
                lon = parseFloat(lon);
                if(id==window.id_room_point_sel) {
                    lat_new=lat;
                    lon_new=lon;
                }
                var icon = new L.DivIcon({
                    html: "<div id='map_tour_icon_"+id+"' class='map_tour_icon' style='background-image: url("+point.icon+");'></div>",
                    iconSize: [point_size, point_size],
                    iconAnchor: [(point_size/2), (point_size/2)]
                });
                var marker = L.marker([lat, lon], {
                    id: id,
                    icon: icon,
                    draggable: true,
                    autoPan: true
                });
                marker.on("click", function(e) {
                    var marker = e.target;
                    var id = marker.options.id;
                    click_map_point_l(id);
                    var position = marker.getLatLng();
                    var lat = position.lat;
                    var lon = position.lng;
                    $('#point_pos').val(lat+','+lon);
                });
                marker.on("drag", function(e) {
                    var marker = e.target;
                    var id = marker.options.id;
                    click_map_point_l(id);
                    var position = marker.getLatLng();
                    var lat = position.lat;
                    var lon = position.lng;
                    $('#point_pos').val(lat+','+lon);
                });
                marker.on("dragend", function(e) {
                    var marker = e.target;
                    var id = marker.options.id;
                    var position = marker.getLatLng();
                    var lat = position.lat;
                    var lon = position.lng;
                    $('#point_pos').val(lat+','+lon);
                    save_map_point();
                });
                marker.addTo(window.map_tour_l);
                bounds.extend(marker.getLatLng());
            } else {
                html_map_select += '<option id="'+id+'">'+name+'</option>';
            }
        });
        $('.map_tour_icon').css('width',point_size+'px');
        $('.map_tour_icon').css('height',point_size+'px');
        $('.map_tour_icon').css('border-color',window.point_color);
        if(points_exist) {
            if(window.id_room_point_sel!='') {
                var zoom = sessionStorage.getItem('zoom');
                if(zoom === null) {
                    zoom = 16;
                } else {
                    sessionStorage.removeItem('zoom');
                }
                window.map_tour_l.setView([lat_new, lon_new], zoom);
            } else {
                window.map_tour_l.fitBounds(bounds, {padding: [50,50]});
            }
        }
        $('#room_select').empty();
        $('#room_select').html(html_map_select).promise().done(function () {
            $('#room_select').selectpicker('refresh');
            change_preview_room_image_map(null);
        });
        if(window.id_room_point_sel!='') {
            $.each(window.map_tour_l._marker, function (ml) {
                var id = ml.options.id;
                if(id == parseInt(window.id_room_point_sel)) {
                    ml.trigger('click');
                }
            });
        }
    }

    function set_point_map_pos(id,top,left) {
        top = Math.round(top / ratio_w);
        left = Math.round(left / ratio_h);
        $('#point_pos').val(top+","+left);
        jQuery.each(window.map_points, function(index, point) {
            var id_point = point.id;
            if(id==id_point) {
                point.map_left = left;
                point.map_top = top;
            }
        });
    }

    function click_map_point(id) {
        if(window.id_room_sel!=id) {
            set_room_target_map(id);
            $('#btn_delete_point').attr('onclick','modal_delete_map_point('+id+');');
            $('#msg_select_point').hide();
            $('.point_settings').show();
        }
        window.id_room_sel = id;
    }

    window.click_map_point_l = function(id) {
        if(window.id_room_sel!=id) {
            set_room_target_map(id);
            $('.map_tour_icon').removeClass('map_tour_icon_active');
            $('#map_tour_icon_'+id).addClass('map_tour_icon_active');
            $('.map_tour_icon').parent().removeClass('map_tour_icon_top');
            $('#map_tour_icon_'+id).parent().addClass('map_tour_icon_top');
            $('#btn_delete_point').attr('onclick','modal_delete_map_point('+id+');');
            $('#msg_select_point').hide();
            $('.point_settings').show();
        }
        window.id_room_sel = id;
    }

    function parse_presentation_list() {
        var html = '';
        var first_row = true;
        var priority_1_old = 0;
        window.array_id_rooms = [];
        jQuery.each(array_presentation, function(index, presentation) {
            var id = presentation.id;
            var action = presentation.action;
            var room_id = presentation.id_room;
            var priority_1 = presentation.priority_1;
            if(priority_1_old==0) {
                priority_1_old = priority_1;
            }
            if(!window.array_id_rooms.includes(room_id)) {
                window.array_id_rooms.push(room_id);
            }
            var room_image = presentation.panorama_image;
            var room_name = presentation.room_name;
            var params = presentation.params;
            var sleep_ms = presentation.sleep;
            switch (action) {
                case'goto':
                    if(first_row) {
                        var mt = 0;
                        first_row = false;
                    } else {
                        var mt = 4;
                    }
                    var ml = 0;
                    var py = 2;
                    var b_color = 'warning';
                    var classn = 'p_room';
                    var priority_click_up = 'change_presentation_priority(1,\'up\','+id+');';
                    var priority_click_down = 'change_presentation_priority(1,\'down\','+id+');';
                    var text = '<i class="far fa-arrow-alt-circle-up"></i> <b>'+room_name+'</b>&nbsp;&nbsp;&nbsp;<i class="far fa-pause-circle"></i> '+sleep_ms+'ms';
                    break;
                case 'lookAt':
                    var mt = 1;
                    var ml = 4;
                    var py = 1;
                    var b_color = 'primary';
                    var classn = 'p_subs p_sub_'+room_id;
                    var priority_click_up = 'change_presentation_priority(2,\'up\','+id+');';
                    var priority_click_down = 'change_presentation_priority(2,\'down\','+id+');';
                    room_image = '';
                    var text = '<i class="fas fa-bullseye"></i> '+params+'&nbsp;&nbsp;&nbsp;<i class="far fa-pause-circle"></i> '+sleep_ms+'ms';
                    break;
                case 'type':
                    var mt = 1;
                    var ml = 4;
                    var py = 1;
                    var b_color = 'info';
                    var classn = 'p_subs p_sub_'+room_id;
                    var priority_click_up = 'change_presentation_priority(2,\'up\','+id+');';
                    var priority_click_down = 'change_presentation_priority(2,\'down\','+id+');';
                    room_image = '';
                    var text = '<i class="far fa-comment-dots"></i> '+params+ '&nbsp;&nbsp;&nbsp;<i class="far fa-pause-circle"></i> '+sleep_ms+'ms';
                    break;
            }

            if(priority_1!=priority_1_old) {
                html += '<div onclick="open_modal_p_action('+array_presentation[index-1].id_room+');" style="cursor: pointer" class="card ml-4 mt-1 py-1 bg-info text-white">\n' +
                    '            <div class="card-body" style="padding-top: 0;padding-bottom: 0;">\n' +
                    '                <div class="row">\n' +
                    '                    <div class="col-md-12 text-center">\n' +
                    '                        <i class="fas fa-plus-circle"></i> <span>'+window.backend_labels.add_action+'</span>\n' +
                    '                    </div>\n' +
                    '                </div>\n' +
                    '            </div>\n' +
                    '        </div>';
                priority_1_old = priority_1;
            }

            html += '<div class="'+classn+' noselect card mt-'+mt+' ml-'+ml+' py-'+py+' border-left-'+b_color+'">\n' +
                '            <div class="card-body" style="padding-top: 0;padding-bottom: 0;">\n' +
                '                <div class="row">\n' +
                '                    <div onclick="presentation_elem_edit('+index+');" style="cursor: pointer" class="col-md-10 text-center text-sm-center text-md-left text-lg-left">\n';
            if(room_image!='') {
                html += '                        <div class="d-inline-block align-middle"><img style="height: 40px;" src="../viewer/panoramas/thumb/'+room_image+'" /></div>\n';
            }
            html += '                        <div class="noselect d-inline-block ml-2 align-middle text-left">'+text+'</div>\n' +
                '                    </div>\n'+
                '                    <div class="col-md-2 align-self-center text-center text-sm-center text-md-right text-lg-right">\n';
            html += '                        <i onclick="'+priority_click_up+'" class="icon_order fas fa-caret-up"></i>&nbsp;&nbsp;<i onclick="'+priority_click_down+'" class="icon_order fas fa-caret-down"></i>';
            html += '                 </div>\n' +
                '                </div>\n' +
                '            </div>\n' +
                '        </div>';
            if((index==(array_presentation.length-1))) {
                html += '<div onclick="open_modal_p_action('+room_id+');" style="cursor: pointer" class="card ml-4 mt-1 py-1 bg-info text-white">\n' +
                    '            <div class="card-body" style="padding-top: 0;padding-bottom: 0;">\n' +
                    '                <div class="row">\n' +
                    '                    <div class="col-md-12 text-center">\n' +
                    '                        <i class="fas fa-plus-circle"></i> <span>'+window.backend_labels.add_action+'</span>\n' +
                    '                    </div>\n' +
                    '                </div>\n' +
                    '            </div>\n' +
                    '        </div>';
            }
        });

        html += '<div onclick="open_modal_p_room(null)" style="cursor: pointer" class="card mt-4 py-2 bg-warning text-white">\n' +
            '            <div class="card-body" style="padding-top: 0;padding-bottom: 0;">\n' +
            '                <div class="row">\n' +
            '                    <div class="col-md-12 text-center">\n' +
            '                        <i class="fas fa-plus-circle"></i> <span>'+window.backend_labels.add_room+'</span>\n' +
            '                    </div>\n' +
            '                </div>\n' +
            '            </div>\n' +
            '        </div>';

        $('#presentation_list').html(html).promise().done(function () {
            var scrollpos = localStorage.getItem('scrollpos');
            if (scrollpos) {
                $('#content-wrapper').scrollTo(0, parseInt(scrollpos));
                localStorage.removeItem('scrollpos');
            }
            $('.p_room:first .fa-caret-up').addClass('disabled');
            $('.p_room:last .fa-caret-down').addClass('disabled');
            jQuery.each(window.array_id_rooms, function(index, id_room) {
                $('.p_sub_'+id_room+':first .fa-caret-up').addClass('disabled');
                $('.p_sub_'+id_room+':last .fa-caret-down').addClass('disabled');
            });
        });
    }

    window.change_presentation_priority = function (priority,direction,id) {
        $.ajax({
            url: "ajax/change_presentation_priority.php",
            type: "POST",
            data: {
                id_virtualtour: window.id_virtualtour,
                id: id,
                priority: priority,
                direction: direction
            },
            async: true,
            success: function (rsp) {
                localStorage.setItem('scrollpos', parseInt(window.scrollY));
                location.reload();
            }
        });
    }

    window.change_p_action = function() {
        var p_action = $('#p_action option:selected').attr('id');
        switch (p_action) {
            case '0':
                $('#div_type').hide();
                $('#div_lookAt').hide();
                $('#btn_add_p_action').addClass('disabled');
                break;
            case 'type':
                $('#div_type').show();
                $('#div_lookAt').hide();
                $('#btn_add_p_action').removeClass('disabled');
                break;
            case 'lookAt':
                $('#div_type').hide();
                $('#div_lookAt').show();
                $('#btn_add_p_action').removeClass('disabled');
                init_p_viewer(null,null,null);
                break;
        }
    }

    function init_p_viewer(yaw,pitch,hfov) {
        $.ajax({
            url: "ajax/get_room.php",
            type: "POST",
            data: {
                id: window.id_p_room,
            },
            async: true,
            success: function (rsp) {
                var room = JSON.parse(rsp);
                if(yaw==null) yaw = room.yaw;
                if(pitch==null) pitch = room.pitch;
                if(hfov==null) hfov = window.p_hfov;
                if(parseInt(room.allow_pitch)==1) {
                    var minPitch = parseInt(room.min_pitch)-34;
                    var maxPitch = parseInt(room.max_pitch)+34;
                } else {
                    var minPitch = 0;
                    var maxPitch = 0;
                    pitch = 0;
                }
                p_viewer = pannellum.viewer('p_lookAt', {
                    "type": "equirectangular",
                    "panorama": '../viewer/panoramas/'+room.panorama_image,
                    "autoLoad": true,
                    "showFullscreenCtrl": false,
                    "showControls": false,
                    "horizonPitch": parseInt(room.h_pitch),
                    "horizonRoll": parseInt(room.h_roll),
                    "hfov": parseInt(hfov),
                    "minHfov": parseInt(window.p_min_hfov),
                    "maxHfov": parseInt(window.p_max_hfov),
                    "yaw": parseFloat(yaw),
                    "pitch": parseFloat(pitch),
                    "minPitch": minPitch,
                    "maxPitch" : maxPitch,
                    "minYaw": parseInt(room.min_yaw),
                    "maxYaw" : parseInt(room.max_yaw),
                    "haov": parseInt(room.haov),
                    "vaov": parseInt(room.vaov),
                    "compass": false,
                    "friction": 1,
                    "strings": {
                        "loadingLabel": window.backend_labels.loading+"...",
                    },
                });
                p_viewer.on('load', function () {
                    p_viewer_initialized = true;
                    adjust_ratio_hfov('p_lookAt',p_viewer,hfov,window.p_min_hfov,window.p_max_hfov);
                });
                p_viewer.on('animatefinished',function () {
                    var yaw = parseFloat(p_viewer.getYaw());
                    var pitch = parseFloat(p_viewer.getPitch());
                    var hfov = parseInt(p_viewer.getHfov());
                    var c_w = parseFloat($('#p_lookAt').css('width').replace('px',''));
                    var c_h = parseFloat($('#p_lookAt').css('height').replace('px',''));
                    var ratio_panorama = c_w / c_h;
                    var ratio_hfov = 1.7771428571428571 / ratio_panorama;
                    hfov = hfov * ratio_hfov;
                    hfov = Math.round(hfov)+1;
                    $('#div_lookAt p').html(yaw+','+pitch+' ('+hfov+')');
                    window.p_params = pitch+','+yaw+','+hfov;
                });
            }
        });
    }

    window.open_modal_p_room = function (index) {
        if(index==null) {
            $('#p_sleep_r').val(0);
            jQuery.each(window.array_id_rooms, function(index, id_room) {
                $("#p_room option[id='"+id_room+"']").prop("disabled", true);
            });
            $("#p_room").val($("#p_room :not([disabled]):first").val());
            $("#p_room").prop('disabled',false);
            $('#btn_add_p_room').html(window.backend_labels.add);
            $('#btn_add_p_room').attr('onclick','add_presentation_room();');
            $('#btn_delete_p_room').hide();
        } else {
            $('#p_sleep_r').val(array_presentation[index].sleep);
            $('#btn_remove_p_room').attr('onclick','delete_p_room('+array_presentation[index].id_room+')');
            $('#btn_delete_p_room').show();
        }
        $('#modal_presentation_room').modal('show');
    }

    window.open_modal_p_action = function (id_room) {
        window.id_p_room = id_room;
        try {
            p_viewer.destroy();
        } catch (e) {}
        $('#btn_add_p_action').html(window.backend_labels.add);
        $('#btn_add_p_action').attr('onclick','add_presentation_action();');
        $("#p_action").prop('disabled',false);
        $('#btn_add_p_action').addClass('disabled');
        $('#div_type').hide();
        $('#div_lookAt').hide();
        $("#p_action option").prop("selected", false);
        $("#p_action").val($("#p_action option:first").val());
        $('#p_animation').val(1000);
        $('#p_sleep_l').val(0);
        $('#p_sleep_t').val(0);
        $('#p_text').val('');
        $('#btn_delete_p_action').hide();
        $('#modal_presentation_action').modal('show');
    }

    window.presentation_elem_edit = function (index) {
        window.id_p_room = array_presentation[index].id_room;
        if(array_presentation[index].action=='goto') {
            $("#p_room option").prop("selected", false);
            $("#p_room option[id='"+window.id_p_room+"']").prop("selected", true);
            $("#p_room").prop('disabled',true);
            $('#btn_add_p_room').html(window.backend_labels.save);
            $('#btn_add_p_room').attr('onclick','edit_presentation_room('+array_presentation[index].id+');');
            open_modal_p_room(index);
        } else {
            try {
                p_viewer.destroy();
            } catch (e) {}
            $('#btn_add_p_action').html(window.backend_labels.save);
            $('#btn_add_p_action').attr('onclick','edit_presentation_action('+array_presentation[index].id+');');
            $('#btn_add_p_action').removeClass('disabled');
            $("#p_action").prop('disabled',true);
            $("#p_action option").prop("selected", false);
            switch (array_presentation[index].action) {
                case "type":
                    $('#div_type').show();
                    $('#div_lookAt').hide();
                    $("#p_action option[id='type']").prop("selected", true);
                    $('#p_sleep_t').val(array_presentation[index].sleep);
                    $('#p_text').val(array_presentation[index].text);
                    break;
                case 'lookAt':
                    $('#div_type').hide();
                    $('#div_lookAt').show();
                    $("#p_action option[id='lookAt']").prop("selected", true);
                    $('#p_animation').val(array_presentation[index].animation);
                    $('#p_sleep_l').val(array_presentation[index].sleep);
                    init_p_viewer(array_presentation[index].yaw,array_presentation[index].pitch,array_presentation[index].hfov);
                    break;
            }
            $('#btn_remove_p_action').attr('onclick','delete_p_action('+array_presentation[index].id+')');
            $('#btn_delete_p_action').show();
            $('#modal_presentation_action').modal('show');
        }
    }

    window.add_presentation_action = function () {
        $('#modal_presentation_action button').addClass("disabled");
        var action = $('#p_action option:selected').attr('id');
        switch (action) {
            case "type":
                var sleep = $('#p_sleep_t').val();
                var params = $('#p_text').val();
                break;
            case 'lookAt':
                var sleep = $('#p_sleep_l').val();
                var animation = $('#p_animation').val();
                if(animation=='') animation=0;
                var params = window.p_params+','+animation;
                break;
        }
        $.ajax({
            url: "ajax/add_presentation_action.php",
            type: "POST",
            data: {
                id_virtualtour: window.id_virtualtour,
                id_room: window.id_p_room,
                sleep: sleep,
                action: action,
                params: params
            },
            async: true,
            success: function (json) {
                var rsp = JSON.parse(json);
                $('#modal_presentation_action button').removeClass("disabled");
                if(rsp.status=='ok') {
                    $('#modal_presentation_action').modal("hide");
                    localStorage.setItem('scrollpos', parseInt(window.scrollY));
                    location.reload();
                }
            }
        });
    }

    window.edit_presentation_action = function (id) {
        $('#modal_presentation_action button').addClass("disabled");
        var action = $('#p_action option:selected').attr('id');
        switch (action) {
            case "type":
                var sleep = $('#p_sleep_t').val();
                var params = $('#p_text').val();
                break;
            case 'lookAt':
                var sleep = $('#p_sleep_l').val();
                var animation = $('#p_animation').val();
                if(animation=='') animation=0;
                var params = window.p_params+','+animation;
                break;
        }
        $.ajax({
            url: "ajax/save_presentation_action.php",
            type: "POST",
            data: {
                id: id,
                sleep: sleep,
                params: params
            },
            async: true,
            success: function (json) {
                var rsp = JSON.parse(json);
                $('#modal_presentation_action button').removeClass("disabled");
                if(rsp.status=='ok') {
                    $('#modal_presentation_action').modal("hide");
                    localStorage.setItem('scrollpos', parseInt(window.scrollY));
                    location.reload();
                }
            }
        });
    }

    window.save_presentation = function () {
        var auto_presentation_speed = $('#auto_presentation_speed').val();
        var presentation_type = $('#presentation_type option:selected').attr('id');
        var presentation_video = $('#presentation_video').val();
        $('#save_btn .icon i').removeClass('far fa-circle').addClass('fas fa-circle-notch fa-spin');
        $('#save_btn').addClass("disabled");
        $.ajax({
            url: "ajax/save_presentation.php",
            type: "POST",
            data: {
                id_virtualtour: id_virtualtour,
                auto_presentation_speed: auto_presentation_speed,
                presentation_type: presentation_type,
                presentation_video: presentation_video
            },
            async: true,
            success: function (json) {
                var rsp = JSON.parse(json);
                if(rsp.status=="ok") {
                    window.presentation_need_save = false;
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

    window.add_presentation_room = function () {
        $('#modal_presentation_room button').addClass("disabled");
        var id_room = $('#p_room option:selected').attr('id');
        var sleep = $('#p_sleep_r').val();
        $.ajax({
            url: "ajax/add_presentation_room.php",
            type: "POST",
            data: {
                id_virtualtour: window.id_virtualtour,
                id_room: id_room,
                sleep: sleep
            },
            async: true,
            success: function (json) {
                var rsp = JSON.parse(json);
                $('#modal_presentation_room button').removeClass("disabled");
                if(rsp.status=='ok') {
                    $('#modal_presentation_room').modal("hide");
                    localStorage.setItem('scrollpos', parseInt(window.scrollY));
                    location.reload();
                }
            }
        });
    }

    window.edit_presentation_room = function (id) {
        $('#modal_presentation_room button').addClass("disabled");
        var sleep = $('#p_sleep_r').val();
        $.ajax({
            url: "ajax/save_presentation_room.php",
            type: "POST",
            data: {
                id: id,
                sleep: sleep
            },
            async: true,
            success: function (json) {
                var rsp = JSON.parse(json);
                $('#modal_presentation_room button').removeClass("disabled");
                if(rsp.status=='ok') {
                    $('#modal_presentation_room').modal("hide");
                    localStorage.setItem('scrollpos', parseInt(window.scrollY));
                    location.reload();
                }
            }
        });
    }

    window.delete_p_action = function (id) {
        $('#modal_delete_p_action button').addClass("disabled");
        $.ajax({
            url: "ajax/delete_presentation_action.php",
            type: "POST",
            data: {
                id_virtualtour: window.id_virtualtour,
                id: id
            },
            async: true,
            success: function (json) {
                var rsp = JSON.parse(json);
                $('#modal_delete_p_action button').removeClass("disabled");
                if(rsp.status=='ok') {
                    $('#modal_delete_p_action').modal("hide");
                    $('#modal_presentation_action').modal("hide");
                    localStorage.setItem('scrollpos', parseInt(window.scrollY));
                    location.reload();
                }
            }
        });
    }

    window.delete_p_room = function (id_room) {
        $('#modal_delete_p_room button').addClass("disabled");
        $.ajax({
            url: "ajax/delete_presentation_room.php",
            type: "POST",
            data: {
                id_virtualtour: window.id_virtualtour,
                id_room: id_room
            },
            async: true,
            success: function (json) {
                var rsp = JSON.parse(json);
                $('#modal_delete_p_room button').removeClass("disabled");
                if(rsp.status=='ok') {
                    $('#modal_delete_p_room').modal("hide");
                    $('#modal_presentation_room').modal("hide");
                    localStorage.setItem('scrollpos', parseInt(window.scrollY));
                    location.reload();
                }
            }
        });
    }

    window.filter_vt = function () {
        var id_category = $('#filter_category option:selected').attr('id');
        var id_user = $('#filter_user option:selected').attr('id');
        if(id_category==0 && id_user==0) {
            location.href = 'index.php?p=virtual_tours';
        } else if(id_category!=0 && id_user==0) {
            location.href = 'index.php?p=virtual_tours&cat='+id_category;
        } else if(id_category==0 && id_user!=0) {
            location.href = 'index.php?p=virtual_tours&user='+id_user;
        } else {
            location.href = 'index.php?p=virtual_tours&cat='+id_category+'&user='+id_user;
        }
    }

    function parse_virtual_tour_list(virtual_tours,categories,id_category_sel,users,id_user_sel) {
        var html = '';
        var search_val = sessionStorage.getItem('search_vt');
        if(search_val==null) search_val='';
        var option_categories = '';
        jQuery.each(categories, function(index, category) {
            var res = category.split("|");
            var id_category = res[0];
            var name_category = res[1];
            if(id_category_sel==id_category) {
                option_categories += "<option selected id='"+id_category+"'>"+name_category+"</option>";
            } else {
                option_categories += "<option id='"+id_category+"'>"+name_category+"</option>";
            }
        });
        var option_users = '';
        jQuery.each(users, function(index, user) {
            var res = user.split("|");
            var id_user_f = res[0];
            var name_user = res[1];
            if(id_user_sel==id_user_f) {
                option_users += "<option selected id='"+id_user_f+"'>"+name_user+"</option>";
            } else {
                option_users += "<option id='"+id_user_f+"'>"+name_user+"</option>";
            }
        });
        if(window.user_role=='administrator') {
            var display_user_f = '';
            var col_sm_f = 4;
            var col_md_f = 2;
        } else {
            var display_user_f = 'd-none';
            var col_sm_f = 6;
            var col_md_f = 3;
        }
        html += '<div class="row">' +
            '<div class="col-lg-6 col-md-6 col-sm-12 col-12">' +
            '   <div class="input-group mb-3">' +
            '       <span class="input-group-prepend">' +
            '           <div class="input-group-text bg-white border-right-0"><i class="fa fa-search"></i></div>' +
            '       </span>' +
            '       <input onkeyup="persist_search(\'vt\')" class="search_input form-control py-2" type="text" value="'+search_val+'" placeholder="'+window.backend_labels.search_vt+'" autocomplete="new-password" >' +
            '       <span class="input-group-append">' +
            '           <div onclick="clear_search(\'vt\');" style="cursor: pointer;" class="input-group-text bg-gray-300 border-left-0"><i class="fa fa-times"></i></div>' +
            '       </span>' +
            '   </div>' +
            '</div>' +
            '<div class="col-lg-'+col_md_f+' col-md-'+col_md_f+' col-sm-'+col_sm_f+' col-'+col_sm_f+' mb-3">' +
            '   <select onchange="filter_vt();" id="filter_category" class="form-control">' +
            '       <option id="0">'+window.backend_labels.all_categories+'</option>' +
                        option_categories +
            '   </select>' +
            '</div>' +
            '<div class="col-lg-'+col_md_f+' col-md-'+col_md_f+' col-sm-'+col_sm_f+' col-'+col_sm_f+' mb-3 '+display_user_f+'">' +
            '   <select onchange="filter_vt();" id="filter_user" class="form-control">' +
            '       <option id="0">'+window.backend_labels.all_users+'</option>' +
                        option_users +
            '   </select>' +
            '</div>' +
            '   <div class="col-lg-'+col_md_f+' col-md-'+col_md_f+' col-sm-'+col_sm_f+' col-'+col_sm_f+' mb-3">\n' +
            '       <input id="view_type_toggle" type="checkbox" checked data-toggle="toggle" data-width="100%" data-on="'+window.backend_labels.list+'" data-off="'+window.backend_labels.grid+'" data-onstyle="light" data-offstyle="light">\n' +
            '   </div>\n' +
            '</div>';
        jQuery.each(virtual_tours, function(index, virtual_tour) {
            var id = virtual_tour.id;
            var external = parseInt(virtual_tour.external);
            var name = virtual_tour.name;
            var author = virtual_tour.author;
            var id_user = virtual_tour.id_user;
            var date_created = virtual_tour.date_created;
            var count_rooms = virtual_tour.count_rooms;
            var count_maps = virtual_tour.count_maps;
            var count_gallery = virtual_tour.count_gallery;
            var info_box_check = virtual_tour.info_box_check;
            var edit_permission = virtual_tour.edit_permission;
            var edit_ui_permission = virtual_tour.edit_ui_permission;
            var edit_3d_view = virtual_tour.edit_3d_view_permission;
            if(window.user_role=='administrator') {
                author = "<a style='text-decoration:none;' href='index.php?p=edit_user&id="+id_user+"'>"+author+"</a>";
            }
            if(external==1) {
                var disabled = 'disabled';
            } else {
                var disabled = '';
            }
            if(count_rooms>0) {
                var badge_room_style = 'badge-secondary';
            } else {
                var badge_room_style = 'badge-light';
            }
            if(count_maps>0) {
                var badge_map_style = 'badge-secondary';
            } else {
                var badge_map_style = 'badge-light';
            }
            if(count_gallery>0) {
                var badge_gallery_style = 'badge-secondary';
            } else {
                var badge_gallery_style = 'badge-light';
            }
            if(info_box_check==1) {
                var count_info = 1;
                var badge_info_style = 'badge-secondary';
            } else {
                var count_info = 0;
                var badge_info_style = 'badge-light';
            }
            var html_exp = '',html_status = '';
            var start_date = virtual_tour.start_date;
            var end_date = virtual_tour.end_date;
            if((start_date!='') || (end_date!='')) {
                html_exp = ' <span style="font-size:12px">('+start_date+' - '+end_date+')</span>';
            }
            if(parseInt(virtual_tour.status)==1) {
                html_status = '<i style="color: green" class="fas fa-circle"></i>';
            } else {
                html_status = '<i style="color: red" class="fas fa-circle"></i>';
            }
            var btn_duplicate = '';
            if(parseInt(window.can_create)==1) {
                btn_duplicate = '<a title="'+window.backend_labels.duplicate.toUpperCase()+'" href="#" onclick="modal_duplicate_virtualtour('+id+');return false;" class="btn btn-secondary btn-circle '+disabled+'">\n' +
                    '<i class="fas fa-copy"></i>\n' +
                    '</a>\n';
            }
            var btn_export = '';
            if(parseInt(window.enable_export_vt)==1) {
                btn_export = '<a title="'+window.backend_labels.export_vt.toUpperCase()+'" href="#" onclick="modal_export_virtualtour('+id+');return false;" class="btn btn-secondary btn-circle '+disabled+'">\n' +
                    '<i class="fas fa-download"></i>\n' +
                    '</a>\n';
            }
            var btn_edit = '';
            var btn_edit_ui = '';
            var btn_edit_3d_view = '';
            var btn_rename = '';
            var input_rename = '';
            if(edit_permission) {
                btn_edit = '<a title="'+window.backend_labels.edit.toUpperCase()+'" href="index.php?p=edit_virtual_tour&id='+id+'" class="btn btn-warning btn-circle">\n' +
                    '<i class="fas fa-edit"></i>\n' +
                    '</a>\n';
                btn_rename = '<i id="btn_view_rename_'+id+'" onclick="view_rename_vt('+id+');" class="btn_view_rename fas fa-pen"></i>';
                btn_rename += '<i id="btn_save_rename_'+id+'" onclick="save_rename_vt('+id+');" class="btn_save_rename fas fa-check d-none"></i>&nbsp;&nbsp;';
                btn_rename += '<i id="btn_close_rename_'+id+'" onclick="close_rename_vt('+id+');" class="btn_close_rename fas fa-times d-none"></i>';
                input_rename = '<input id="rename_input_'+id+'" class="rename_input d-none" type="text" value="'+name+'" />';
            }
            if(edit_ui_permission) {
                btn_edit_ui = '<a title="'+window.backend_labels.editor_ui.toUpperCase()+'" href="index.php?p=edit_virtual_tour_ui&id_vt='+id+'" class="btn btn-warning btn-circle '+disabled+'">\n' +
                    '<i class="fas fa-swatchbook"></i>\n' +
                    '</a>\n';
            }
            if(edit_3d_view) {
                btn_edit_3d_view = '<a title="'+window.backend_labels.dollhouse.toUpperCase()+'" href="index.php?p=dollhouse&id_vt='+id+'" class="btn btn-warning btn-circle '+disabled+'">\n' +
                    '<i class="fas fa-cube"></i>\n' +
                    '</a>\n';
            }
            html += '<div class="div_vt card mb-2 py-2">\n' +
                '            <div class="card-body" style="padding-top: 0;padding-bottom: 0;">\n' +
                '                <div class="row vt">\n' +
                '                    <div class="vt_content col text-center text-sm-center text-md-left text-lg-left">\n' +
                '                        <b class="vt_name" id="vt_name_'+id+'">'+name+'</b>'+input_rename+'&nbsp;&nbsp;'+btn_rename+'<br><span style="font-size:12px">'+html_status+' '+date_created+' - '+author+'</span>\n' + html_exp +
                '                    </div>\n' +
                '                    <div class="vt_buttons col-md-auto pt-1 text-center text-sm-center text-md-right text-lg-right">\n' + btn_edit + btn_edit_ui + btn_edit_3d_view +
                '                        <a title="'+window.backend_labels.rooms.toUpperCase()+'" href="index.php?p=rooms&id_vt='+id+'" class="btn btn-info btn-circle position-relative '+disabled+'">\n' +
                '                            <i class="fas fa-vector-square"></i>\n' +
                '                             <span style="top:-8px;right:-5px" class="badge badge-pill '+badge_room_style+' position-absolute">'+count_rooms+'</span>\n' +
                '                        </a>\n' +
                '                        <a title="'+window.backend_labels.maps.toUpperCase()+'" href="index.php?p=maps&id_vt='+id+'" class="btn btn-info btn-circle position-relative '+disabled+'">\n' +
                '                            <i class="fas fa-map-marked-alt"></i>\n' +
                '                             <span style="top:-8px;right:-5px" class="badge badge-pill '+badge_map_style+' position-absolute">'+count_maps+'</span>\n' +
                '                        </a>\n' +
                '                        <a title="'+window.backend_labels.gallery.toUpperCase()+'" href="index.php?p=gallery&id_vt='+id+'" class="btn btn-info btn-circle position-relative '+disabled+'">\n' +
                '                            <i class="fas fa-images"></i>\n' +
                '                             <span style="top:-8px;right:-5px" class="badge badge-pill '+badge_gallery_style+' position-absolute">'+count_gallery+'</span>\n' +
                '                        </a>\n' +
                '                        <a title="'+window.backend_labels.info_box.toUpperCase()+'" href="index.php?p=info&id_vt='+id+'" class="btn btn-info btn-circle position-relative '+disabled+'">\n' +
                '                            <i class="fas fa-info-circle"></i>\n' +
                '                             <span style="top:-8px;right:-5px" class="badge badge-pill '+badge_info_style+' position-absolute">'+count_info+'</span>\n' +
                '                        </a>\n' +
                '                        <a title="'+window.backend_labels.preview.toUpperCase()+'" href="index.php?p=preview&id_vt='+id+'" class="btn btn-dark btn-circle">\n' +
                '                            <i class="fas fa-eye"></i>\n' +
                '                        </a>\n' +
                '                        <a title="'+window.backend_labels.publish.toUpperCase()+'" href="index.php?p=publish&id_vt='+id+'" class="btn btn-dark btn-circle">\n' +
                '                            <i class="fas fa-paper-plane"></i>\n' +
                '                        </a>\n' + btn_export + btn_duplicate +
                '                        <a title="'+window.backend_labels.delete.toUpperCase()+'" href="#" onclick="modal_delete_virtualtour('+id+');return false;" class="btn btn-danger btn-circle">\n' +
                '                            <i class="fas fa-trash"></i>\n' +
                '                        </a>\n' +
                '                    </div>\n' +
                '                </div>\n' +
                '            </div>\n' +
                '        </div>';
        });
        $('#virtual_tours_list').html(html).promise().done(function () {
            $('#view_type_toggle').bootstrapToggle();
            $('#view_type_toggle').change(function() {
                var checked = $(this).prop('checked');
                if(!checked) {
                    localStorage.setItem('vt_view_type', 'grid');
                    $('.div_vt').addClass('div_vt_grid');
                    $('#virtual_tours_list').css('margin','0 -5px');
                    $('#virtual_tours_list .vt .vt_content').removeClass('text-lg-left').removeClass('text-md-left');
                    $('#virtual_tours_list .vt .vt_buttons').removeClass('text-lg-right').removeClass('text-md-right');
                    $('#virtual_tours_list .vt .vt_buttons a').addClass('btn-sm');
                    $('#virtual_tours_list .vt .vt_content').addClass('col-12');
                    $('#virtual_tours_list .vt .vt_buttons').addClass('col-12').addClass('mt-1');
                    $('#virtual_tours_list .vt .vt_buttons').addClass('col-auto').removeClass('col-md-auto');
                } else {
                    localStorage.setItem('vt_view_type', 'list');
                    $('.div_vt').removeClass('div_vt_grid');
                    $('#virtual_tours_list').css('margin','0');
                    $('#virtual_tours_list .vt .vt_content').addClass('text-lg-left').addClass('text-md-left');
                    $('#virtual_tours_list .vt .vt_buttons').addClass('text-lg-right').addClass('text-md-right');
                    $('#virtual_tours_list .vt .vt_buttons a').removeClass('btn-sm');
                    $('#virtual_tours_list .vt .vt_content').removeClass('col-12');
                    $('#virtual_tours_list .vt .vt_buttons').removeClass('col-12').removeClass('mt-1');
                    $('#virtual_tours_list .vt .vt_buttons').removeClass('col-auto').addClass('col-md-auto');
                }
            });
            if ("vt_view_type" in localStorage) {
                var view_type = localStorage.getItem('vt_view_type');
                switch (view_type) {
                    case 'list':
                        $('#view_type_toggle').bootstrapToggle('on');
                        break;
                    case 'grid':
                        $('#view_type_toggle').bootstrapToggle('off');
                        break;
                }
            }
            if(window.user_role=='editor') {
                $('.fa-trash').parent().hide();
                $('.fa-copy').parent().hide();
                $('.fa-paper-plane').parent().hide();
            }
            $('#virtual_tours_list').searchable({
                selector      : '.div_vt',
                childSelector : 'div',
                searchField   : '.search_input',
                searchType    : 'default',
                clearOnLoad   : false
            });
            $('#virtual_tours_list .btn').tooltipster({
                delay: 10,
                hideOnClick: true
            });
        });
    }

    window.view_rename_vt = function(id) {
        $('#btn_view_rename_'+id).addClass('d-none');
        $('#btn_save_rename_'+id).removeClass('d-none');
        $('#btn_close_rename_'+id).removeClass('d-none');
        $('#rename_input_'+id).removeClass('d-none');
        $('#vt_name_'+id).addClass('d-none');
    }

    window.save_rename_vt = function(id) {
        var name = $('#rename_input_'+id).val();
        if(name!='') {
            $('#vt_name_'+id).html(name);
            $.ajax({
                url: "ajax/rename_vt.php",
                type: "POST",
                data: {
                    id: id,
                    name: name
                },
                async: true,
                success: function (json) {
                    $('#btn_view_rename_'+id).removeClass('d-none');
                    $('#btn_save_rename_'+id).addClass('d-none');
                    $('#btn_close_rename_'+id).addClass('d-none');
                    $('#rename_input_'+id).addClass('d-none');
                    $('#vt_name_'+id).removeClass('d-none');
                }
            });
        }
    }

    window.close_rename_vt = function(id) {
        $('#btn_view_rename_'+id).removeClass('d-none');
        $('#btn_save_rename_'+id).addClass('d-none');
        $('#btn_close_rename_'+id).addClass('d-none');
        $('#rename_input_'+id).addClass('d-none');
        $('#vt_name_'+id).removeClass('d-none');
    }

    function parse_map_list(maps,permissions) {
        var html = '', html_search='';
        var search_val = sessionStorage.getItem('search_map');
        if(search_val==null) search_val='';
        if(maps.length>0) {
            html_search = '<div class="row">\n' +
                '<div class="col-lg-10 col-md-8 col-sm-8 col-6">\n' +
                '    <div class="input-group mb-3">\n' +
                '       <span class="input-group-prepend">\n' +
                '           <div class="input-group-text bg-white border-right-0"><i class="fa fa-search"></i></div>\n' +
                '       </span>\n' +
                '       <input onkeyup="persist_search(\'map\')" class="search_input form-control py-2" type="text" value="'+search_val+'" placeholder="'+window.backend_labels.search_map+'" autocomplete="new-password" >\n' +
                '       <span class="input-group-append">\n' +
                '           <div onclick="clear_search(\'map\');" style="cursor: pointer;" class="input-group-text bg-gray-300 border-left-0"><i class="fa fa-times"></i></div>\n' +
                '       </span>\n' +
                '   </div>\n' +
                '</div>\n' +
                '   <div class="col-lg-2 col-md-4 col-sm-4 col-6">\n' +
                '       <input id="view_type_toggle" type="checkbox" checked data-toggle="toggle" data-width="100%" data-on="'+window.backend_labels.list+'" data-off="'+window.backend_labels.grid+'" data-onstyle="light" data-offstyle="light">\n' +
                '   </div>\n' +
                '</div>\n';
        }
        jQuery.each(maps, function(index, map) {
            var id = map.id;
            var name = map.name;
            var image = map.map;
            var map_type = map.map_type;
            var count = map.count_rooms;
            var btn_rename = '<i id="btn_view_rename_'+id+'" onclick="view_rename_map('+id+');" class="btn_view_rename fas fa-pen"></i>';
            btn_rename += '<i id="btn_save_rename_'+id+'" onclick="save_rename_map('+id+');" class="btn_save_rename fas fa-check d-none"></i>&nbsp;&nbsp;';
            btn_rename += '<i id="btn_close_rename_'+id+'" onclick="close_rename_map('+id+');" class="btn_close_rename fas fa-times d-none"></i>';
            var input_rename = '<input id="rename_input_'+id+'" class="rename_input d-none" type="text" value="'+name+'" />';
            switch (map_type) {
                case 'floorplan':
                    html += '<div class="div_map card mb-2 py-2">\n' +
                        '            <div class="card-body" style="padding-top: 0;padding-bottom: 0;">\n' +
                        '                <div data-id="'+id+'" class="row maph">\n' +
                        '                    <div class="map_content col-md-8 text-center text-sm-center text-md-left text-lg-left">\n' +
                        '                        <div style="width: 70px;overflow: hidden;" class="d-inline-block align-middle"><img style="height: 40px;" src="../viewer/maps/thumb/'+image+'"></div>\n' +
                        '                        <div class="map_info d-inline-block align-middle ml-2 text-left"><b class="map_name" id="map_name_'+id+'">'+window.icon_show_ui_map+' '+name+'</b>'+input_rename+'&nbsp;&nbsp;'+btn_rename+'<br><span style="font-size:12px">'+count+' '+window.backend_labels.rooms_assigned+'</span></div>\n' +
                        '                    </div>\n' +
                        '                    <div class="map_buttons col-md-4 pt-1 text-center text-sm-center text-md-right text-lg-right">\n' +
                        '                        <a title="'+window.backend_labels.drag_change_pos.toUpperCase()+'" style="background-color: white; border: 1px solid black; cursor: pointer" class="handle btn btn-primary btn-circle">\n' +
                        '                           <i style="color: black" class="fas fa-arrows-alt"></i>\n' +
                        '                        </a>\n' +
                        '                        <a title="'+window.backend_labels.edit.toUpperCase()+'" href="index.php?p=edit_map&id='+id+'" class="btn btn-warning btn-circle">\n' +
                        '                            <i class="fas fa-edit"></i>\n' +
                        '                        </a>\n' +
                        '                        <a title="'+window.backend_labels.delete.toUpperCase()+'" href="#" onclick="modal_delete_map('+id+');return false;" class="btn btn-danger btn-circle">\n' +
                        '                            <i class="fas fa-trash"></i>\n' +
                        '                        </a>\n' +
                        '                    </div>\n' +
                        '                </div>\n' +
                        '            </div>\n' +
                        '        </div>';
                    break;
                case 'map':
                    html += '<div class="div_map card mb-2 py-2">\n' +
                        '            <div class="card-body" style="padding-top: 0;padding-bottom: 0;">\n' +
                        '                <div data-id="'+id+'" class="row maph">\n' +
                        '                    <div class="map_content col-md-8 text-center text-sm-center text-md-left text-lg-left">\n' +
                        '                        <div style="width: 70px;overflow: hidden;" class="d-inline-block align-middle"><img style="height: 40px;" src="img/map.jpg"></div>\n' +
                        '                        <div class="map_info d-inline-block align-middle ml-2 text-left"><b class="map_name" id="map_name_'+id+'">'+window.icon_show_ui_map_tour+' '+name+'</b>'+input_rename+'&nbsp;&nbsp;'+btn_rename+'<br><span style="font-size:12px">'+count+' '+window.backend_labels.rooms_assigned+'</span></div>\n' +
                        '                    </div>\n' +
                        '                    <div class="map_buttons col-md-4 pt-1 text-center text-sm-center text-md-right text-lg-right">\n' +
                        '                        <a title="'+window.backend_labels.edit.toUpperCase()+'" href="index.php?p=edit_map&id='+id+'" class="btn btn-warning btn-circle">\n' +
                        '                            <i class="fas fa-edit"></i>\n' +
                        '                        </a>\n' +
                        '                        <a title="'+window.backend_labels.delete.toUpperCase()+'" href="#" onclick="modal_delete_map('+id+');return false;" class="btn btn-danger btn-circle">\n' +
                        '                            <i class="fas fa-trash"></i>\n' +
                        '                        </a>\n' +
                        '                    </div>\n' +
                        '                </div>\n' +
                        '            </div>\n' +
                        '        </div>';
                    break;
            }
        });
        $('#maps_list').html(html).promise().done(function () {
            $('.help_t').tooltip();
            $('#search_div').html(html_search).promise().done(function () {
                $('#view_type_toggle').bootstrapToggle();
                $('#view_type_toggle').change(function() {
                    var checked = $(this).prop('checked');
                    if(!checked) {
                        localStorage.setItem('map_view_type', 'grid');
                        $('.div_map').addClass('div_map_grid');
                        $('#maps_list').css('margin','0 -5px');
                        $('#maps_list .maph .map_content').removeClass('text-lg-left').removeClass('text-md-left');
                        $('#maps_list .maph .map_buttons').removeClass('text-lg-right').removeClass('text-md-right');
                        $('#maps_list .maph .map_buttons a').addClass('btn-sm');
                        $('#maps_list .maph .map_content').removeClass('col-md-8').addClass('col-12');
                        $('#maps_list .maph .map_buttons').removeClass('col-md-4').addClass('col-12').addClass('mt-1');
                        $('#maps_list .maph .map_buttons').addClass('col-auto').removeClass('col-md-auto');
                        $('#maps_list .maph .map_content .map_info').removeClass('text-left').addClass('text-center');
                        $('#maps_list .maph .map_content .map_info').css('width','100%');
                    } else {
                        localStorage.setItem('map_view_type', 'list');
                        $('.div_map').removeClass('div_map_grid');
                        $('#maps_list').css('margin','0');
                        $('#maps_list .maph .map_content').addClass('text-lg-left').addClass('text-md-left');
                        $('#maps_list .maph .map_buttons').addClass('text-lg-right').addClass('text-md-right');
                        $('#maps_list .maph .map_buttons a').removeClass('btn-sm');
                        $('#maps_list .maph .map_content').removeClass('col-12').addClass('col-md-8');
                        $('#maps_list .maph .map_buttons').removeClass('col-12').addClass('col-md-4').removeClass('mt-1');
                        $('#maps_list .maph .map_buttons').removeClass('col-auto').addClass('col-md-auto');
                        $('#maps_list .maph .map_content .map_info').addClass('text-left').removeClass('text-center');
                        $('#maps_list .maph .map_content .map_info').css('width','auto');
                    }
                });
                if ("map_view_type" in localStorage) {
                    var view_type = localStorage.getItem('map_view_type');
                    switch (view_type) {
                        case 'list':
                            $('#view_type_toggle').bootstrapToggle('on');
                            break;
                        case 'grid':
                            $('#view_type_toggle').bootstrapToggle('off');
                            break;
                    }
                }
                if(!permissions['edit']) {
                    $('#maps_list .fa-edit').parent().hide();
                }
                if(!permissions['delete']) {
                    $('#maps_list .fa-trash').parent().hide();
                }
                if(window.user_role=='editor') {
                    $('#maps_list .fa-arrows-alt').parent().hide();
                }
                $('#maps_list').searchable({
                    selector      : '.div_map',
                    childSelector : 'div',
                    searchField   : '.search_input',
                    searchType    : 'default',
                    clearOnLoad   : false
                });
                $('#maps_list .btn').tooltipster({
                    delay: 10,
                    hideOnClick: true
                });
                var el = document.getElementById('maps_list');
                Sortable.create(el,{
                    handle: '.handle',
                    filter: ".no_drag",
                    onMove: function (e) { return e.related.className.indexOf('no_drag') === -1;  },
                    onEnd: function (evt) {
                        var array_maps_priority = [];
                        $('#maps_list .maph').each(function () {
                            var id = $(this).attr('data-id');
                            array_maps_priority.push(id);
                        });
                        change_maps_order(array_maps_priority);
                    },
                });
            });
        });
    }

    window.view_rename_map = function(id) {
        $('#btn_view_rename_'+id).addClass('d-none');
        $('#btn_save_rename_'+id).removeClass('d-none');
        $('#btn_close_rename_'+id).removeClass('d-none');
        $('#rename_input_'+id).removeClass('d-none');
        $('#map_name_'+id).addClass('d-none');
    }

    window.save_rename_map = function(id) {
        var name = $('#rename_input_'+id).val();
        if(name!='') {
            $('#map_name_'+id).html(name);
            $.ajax({
                url: "ajax/rename_map.php",
                type: "POST",
                data: {
                    id: id,
                    name: name
                },
                async: true,
                success: function (json) {
                    $('#btn_view_rename_'+id).removeClass('d-none');
                    $('#btn_save_rename_'+id).addClass('d-none');
                    $('#btn_close_rename_'+id).addClass('d-none');
                    $('#rename_input_'+id).addClass('d-none');
                    $('#map_name_'+id).removeClass('d-none');
                }
            });
        }
    }

    window.close_rename_map = function(id) {
        $('#btn_view_rename_'+id).removeClass('d-none');
        $('#btn_save_rename_'+id).addClass('d-none');
        $('#btn_close_rename_'+id).addClass('d-none');
        $('#rename_input_'+id).addClass('d-none');
        $('#map_name_'+id).removeClass('d-none');
    }

    function parse_room_marker(rooms) {
        var html = '';
        if(rooms.length==0) {
            $('#msg_no_room').show();
            $('#msg_sel_room').hide();
        } else {
            $('#msg_no_room').hide();
            window.rooms_count = rooms.length;
            jQuery.each(rooms, function(index, room) {
                var id = room.id;
                var name = room.name;
                var image = room.panorama_image;
                var thumb_image = room.thumb_image_url;
                var count_markers = room.count_markers;
                html += '<div data-id="'+id+'" data-image="'+image+'" class="text-center py-1 room_'+id+'">\n' +
                    '   <img class="room_image d-inline-block" src="'+thumb_image+'">\n' +
                    '   <div class="room_quick_btn d-inline-block disabled_qb">' +
                    '       <div data-action="quick_add" data-id="'+id+'" data-image="'+image+'" title="'+window.backend_labels.markers_quick_add+'" class="room_quick_add"><i class="fas fa-arrow-up"></i></div>' +
                    '       <div data-action="add" data-id="'+id+'" data-image="'+image+'" title="'+window.backend_labels.markers_add+'" class="room_add"><i class="fas fa-plus"></i></div>' +
                    '   </div>\n' +
                    '   <div class="room_name d-block">'+name+'</div>\n' +
                    '   <div class="room_counter d-block"><b id="count_marker_'+id+'">'+count_markers+'</b> '+window.backend_labels.markers+'</div>\n' +
                    '</div>';
            });
            $('.rooms_slider').html(html).promise().done(function () {
                $('.rooms_slider .room_quick_add').tooltipster({
                    delay: 10,
                    hideOnClick: true,
                    position: 'top'
                });
                $('.rooms_slider .room_add').tooltipster({
                    delay: 10,
                    hideOnClick: true,
                    position: 'top'
                });
                $('.rooms_slider').on('init', function(event, slick){
                    $('.rooms_slider').css('opacity',1);
                    if(window.id_room_marker!=0) {
                        jQuery.each(window.rooms, function(index, room) {
                            var id = room.id;
                            var image = room.panorama_image;
                            if(id==window.id_room_marker) {
                                select_room_marker(id,image,null);
                            }
                        });
                    }
                });
                var initial_slide = 0;
                if(window.id_room_marker!=0) {
                    jQuery.each(window.rooms, function(index, room) {
                        var id = room.id;
                        if(id==window.id_room_marker) {
                            initial_slide = index;
                        }
                    });
                }
                setTimeout(function () {
                    $('.rooms_slider').slick({
                        infinite: false,
                        draggable: true,
                        swipe: true,
                        touchMove: true,
                        dots: false,
                        slidesToShow: 6,
                        slidesToScroll: 6,
                        responsive: [
                            {
                                breakpoint: 1530,
                                settings: {
                                    slidesToShow: 5,
                                    slidesToScroll: 5
                                }
                            },
                            {
                                breakpoint: 1050,
                                settings: {
                                    slidesToShow: 4,
                                    slidesToScroll: 4
                                }
                            },
                            {
                                breakpoint: 830,
                                settings: {
                                    slidesToShow: 3,
                                    slidesToScroll: 3
                                }
                            },
                            {
                                breakpoint: 400,
                                settings: {
                                    slidesToShow: 2,
                                    slidesToScroll: 2
                                }
                            }
                        ]
                    });
                    $('.rooms_slider').slick("slickGoTo", parseInt(initial_slide), true);

                    $('.rooms_slider .slick-slide').on('mousedown',function () {
                        drag_slider_start = new Date().getTime();
                        drag_slider = false;
                    });
                    $('.rooms_slider .slick-slide').on('mousemove',function () {
                        drag_slider_end = new Date().getTime();
                        drag_slider = true;
                    });
                    $('.rooms_slider .slick-slide').on('click',function (event) {
                        var diff_drag = drag_slider_end - drag_slider_start;
                        if(drag_slider == false || diff_drag < 100) {
                            if (event.target.getAttribute("data-action") !== null) {
                                var id = $(this).attr('data-id');
                                var image = $(this).attr('data-image');
                                var action = event.target.getAttribute("data-action");
                                switch(action) {
                                    case 'quick_add':
                                        new_marker(window.id_room_sel,window.panorama_image,id);
                                        break;
                                    case 'add':
                                        add_marker(window.id_room_sel,window.panorama_image,id);
                                        break;
                                }
                            } else {
                                var id = $(this).attr('data-id');
                                var image = $(this).attr('data-image');
                                select_room_marker(id,image,null);
                            }
                        }
                    });
                    $('.rooms_slider .slick-slide').on('touchstart',function () {
                        drag_slider_start = new Date().getTime();
                        drag_slider = false;
                    });
                    $('.rooms_slider .slick-slide').on('touchmove',function () {
                        drag_slider_end = new Date().getTime();
                        drag_slider = true;
                    });
                    $('.rooms_slider .slick-slide').on('touchend',function (event) {
                        var diff_drag = drag_slider_end - drag_slider_start;
                        if(drag_slider == false || diff_drag < 100) {
                            if (event.target.getAttribute("data-action") !== null) {
                                var id = $(this).attr('data-id');
                                var image = $(this).attr('data-image');
                                var action = event.target.getAttribute("data-action");
                                switch(action) {
                                    case 'quick_add':
                                        new_marker(window.id_room_sel,image,id);
                                        break;
                                    case 'add':
                                        add_marker(window.id_room_sel,image,id);
                                        break;
                                }
                            } else {
                                var id = $(this).attr('data-id');
                                var image = $(this).attr('data-image');
                                select_room_marker(id,image,null);
                            }
                        }
                    });

                    if(window.wizard_step!=-1) {
                        create_wizard(window.wizard_step);
                        wizard_tour_open = true;
                        wizard_tour.start();
                    }
                },200);
            });
        }
    }

    function parse_room_poi(rooms) {
        var html = '';
        if(rooms.length==0) {
            $('#msg_no_room').show();
            $('#msg_sel_room').hide();
        } else {
            $('#msg_no_room').hide();
            jQuery.each(rooms, function(index, room) {
                var id = room.id;
                var name = room.name;
                var image = room.panorama_image;
                var thumb_image = room.thumb_image_url;
                var count_markers = room.count_pois;
                html += '<div data-id="'+id+'" data-image="'+image+'" class="text-center py-1 room_'+id+'">\n' +
                    '   <img class="room_image m-auto" src="'+thumb_image+'">\n' +
                    '   <div class="room_name d-block">'+name+'</div>\n' +
                    '   <div class="room_counter d-block"><b id="count_poi_'+id+'">'+count_markers+'</b> '+window.backend_labels.pois+'</div>\n' +
                    '</div>';
            });
            $('.rooms_slider').html(html).promise().done(function () {
                $('.rooms_slider').on('init', function(event, slick){
                    $('.rooms_slider').css('opacity',1);
                    if(window.id_room_poi!=0) {
                        jQuery.each(window.rooms, function(index, room) {
                            var id = room.id;
                            var image = room.panorama_image;
                            if(id==window.id_room_poi) {
                                select_room_poi(id,image,null);
                            }
                        });
                    }
                });
                var initial_slide = 0;
                if(window.id_room_poi!=0) {
                    jQuery.each(window.rooms, function(index, room) {
                        var id = room.id;
                        if(id==window.id_room_poi) {
                            initial_slide = index;
                        }
                    });
                }
                setTimeout(function () {
                    $('.rooms_slider').slick({
                        infinite: false,
                        draggable: true,
                        swipe: true,
                        touchMove: true,
                        dots: false,
                        slidesToShow: 6,
                        slidesToScroll: 6,
                        responsive: [
                            {
                                breakpoint: 1530,
                                settings: {
                                    slidesToShow: 5,
                                    slidesToScroll: 5
                                }
                            },
                            {
                                breakpoint: 1050,
                                settings: {
                                    slidesToShow: 4,
                                    slidesToScroll: 4
                                }
                            },
                            {
                                breakpoint: 830,
                                settings: {
                                    slidesToShow: 3,
                                    slidesToScroll: 3
                                }
                            },
                            {
                                breakpoint: 400,
                                settings: {
                                    slidesToShow: 2,
                                    slidesToScroll: 2
                                }
                            }
                        ]
                    });
                    $('.rooms_slider').slick("slickGoTo", parseInt(initial_slide), true);

                    $('.rooms_slider .slick-slide').on('mousedown',function () {
                        drag_slider_start = new Date().getTime();
                        drag_slider = false;
                    });
                    $('.rooms_slider .slick-slide').on('mousemove',function () {
                        drag_slider_end = new Date().getTime();
                        drag_slider = true;
                    });
                    $('.rooms_slider .slick-slide').on('click',function () {
                        var diff_drag = drag_slider_end - drag_slider_start;
                        if(drag_slider == false || diff_drag < 100) {
                            var id = $(this).attr('data-id');
                            var image = $(this).attr('data-image');
                            select_room_poi(id,image,null);
                        }
                    });
                    $('.rooms_slider .slick-slide').on('touchstart',function () {
                        drag_slider_start = new Date().getTime();
                        drag_slider = false;
                    });
                    $('.rooms_slider .slick-slide').on('touchmove',function () {
                        drag_slider_end = new Date().getTime();
                        drag_slider = true;
                    });
                    $('.rooms_slider .slick-slide').on('touchend',function () {
                        var diff_drag = drag_slider_end - drag_slider_start;
                        if(drag_slider == false || diff_drag < 100) {
                            var id = $(this).attr('data-id');
                            var image = $(this).attr('data-image');
                            select_room_poi(id,image,null);
                        }
                    });
                },200);
            });
        }
    }

    window.open_preview_viewer = function () {
        $('#modal_preview .modal-body').append('<iframe style="width:100%;height:80vh;;" allowfullscreen allow="gyroscope; accelerometer; xr; microphone *" frameborder="0" scrolling="no" marginheight="0" marginwidth="0" src="../viewer/index.php?code='+window.code_vt+'&room='+window.id_room_sel+'&preview=1"></iframe>');
        $('#modal_preview').modal('show');
    }

    window.close_preview_viewer = function () {
        $('#modal_preview .modal-body').html('');
    }

    window.select_room_marker = function (id_room,image,id_marker_add) {
        if(id_room!=window.id_room_sel && !window.switched_page) {
            window.currentPitch = 0;
            window.currentYaw = 0;
        }
        window.id_room_sel = id_room;
        window.switched_page = false;
        $('.slick-slide').removeClass("selected_room");
        $('.room_'+id_room).addClass("selected_room");
        $('#btn_add_marker').attr('onclick','add_marker('+id_room+',\''+image+'\')');
        $('#btn_add_marker').css('opacity',0);
        $('#btn_switch_to_poi').attr('onclick','switch_to_poi('+id_room+')');
        $('#btn_switch_to_poi').css('opacity',0);
        $('#btn_preview_modal').css('opacity',0);
        $('#msg_sel_room').hide();
        $("#content-wrapper").animate({ scrollTop: $(document).height() }, 200);
        $('.rooms_slider .room_quick_btn').removeClass('disabled_qb');
        get_markers(id_room,image,id_marker_add);
    }

    window.switch_to_poi = function(id_room) {
        var yaw = parseFloat(viewer.getYaw());
        var pitch = parseFloat(viewer.getPitch());
        sessionStorage.setItem('currentYaw',yaw.toString());
        sessionStorage.setItem('currentPitch',pitch.toString());
        location.href = 'index.php?p=pois&id_room='+id_room;
    }

    window.select_room_poi = function (id_room,image,id_poi_add) {
        if(id_room!=window.id_room_sel && !window.switched_page) {
            window.currentPitch = 0;
            window.currentYaw = 0;
        }
        window.id_room_sel = id_room;
        window.switched_page = false;
        $('.slick-slide').removeClass("selected_room");
        $('.room_'+id_room).addClass("selected_room");
        $('#btn_add_poi').attr('onclick','add_poi('+id_room+',\''+image+'\');');
        $('#btn_add_poi').css('opacity',0);
        $('#btn_switch_to_marker').attr('onclick','switch_to_marker('+id_room+')');
        $('#btn_switch_to_marker').css('opacity',0);
        $('#btn_preview_modal').css('opacity',0);
        $('#msg_sel_room').hide();
        $("#content-wrapper").animate({ scrollTop: $(document).height() }, 200);
        get_pois(id_room,image,id_poi_add);
    }

    window.switch_to_marker = function(id_room) {
        var yaw = parseFloat(viewer.getYaw());
        var pitch = parseFloat(viewer.getPitch());
        sessionStorage.setItem('currentYaw',yaw.toString());
        sessionStorage.setItem('currentPitch',pitch.toString());
        location.href = 'index.php?p=markers&id_room='+id_room;
    }

    function get_markers(id_room,image,id_marker_add) {
        $.ajax({
            url: "ajax/get_markers.php",
            type: "POST",
            data: {
                id_room: id_room
            },
            async: true,
            success: function (json) {
                var rsp = JSON.parse(json);
                window.markers = rsp.markers;
                var array_room = rsp.room;
                window.markers_initial = JSON.parse(JSON.stringify(window.markers));
                initialize_pano_markers(id_room,image,id_marker_add,array_room);
            }
        });
    }

    window.add_marker = function (id_room,image,id_room_target=0) {
        $('#override_pos_add').prop('checked',false);
        $('#btn_new_marker').addClass("disabled");
        $('#btn_new_marker').attr('onclick','new_marker('+id_room+',\''+image+'\')');
        select_marker_style('icon');
        $('#modal_add_marker').modal('show');
        get_option_rooms_target('room_target_add',id_room,id_room_target,null,null);
    }

    window.select_marker_style = function (style_sel) {
        $('#dropdown_marker_style').html($('#btn_style_'+style_sel).html());
        switch(style_sel) {
            case 'icon':
                window.embed_type_sel = '';
                break;
            case 'embed_selection':
                window.embed_type_sel = 'selection';
                break;
        }
    }

    window.select_marker_style_edit = function (style_sel) {
        $('#dropdown_marker_style_edit').html($('#btn_edit_style_'+style_sel).html());
        switch(style_sel) {
            case 'icon':
                window.embed_type_sel = '';
                break;
            case 'embed_selection':
                window.embed_type_sel = 'selection';
                break;
        }
        if(window.embed_type_current!=window.embed_type_sel) {
            $('#btn_change_marker_embed_style').removeClass('disabled');
        } else {
            $('#btn_change_marker_embed_style').addClass('disabled');
        }
    }

    window.change_marker_embed_style = function() {
        $('#btn_change_marker_embed_style').addClass('disabled');
        confirm_edit_marker(window.marker_id_edit,window.marker_index_edit);
        $.ajax({
            url: "ajax/change_marker_embed_style.php",
            type: "POST",
            data: {
                id: window.marker_id_edit,
                yaw: window.markers[window.marker_index_edit].yaw,
                pitch: window.markers[window.marker_index_edit].pitch,
                embed_type: window.embed_type_sel
            },
            async: true,
            success: function (json) {
                jQuery.each(window.rooms, function(index, room) {
                    var id = room.id;
                    var image = room.panorama_image;
                    if(id==window.markers[window.marker_index_edit].id_room) {
                        setTimeout(function () {
                            try {
                                var yaw = parseFloat(window.viewer.getYaw());
                                var pitch = parseFloat(window.viewer.getPitch());
                            } catch (e) {
                                var yaw = 0;
                                var pitch = 0;
                            }
                            window.currentYaw = yaw;
                            window.currentPitch = pitch;
                            select_room_marker(id,image,null);
                        },250);
                    }
                });
            }
        });
    }

    window.add_poi = function (id_room,image) {
        window.new_poi_id_room = id_room;
        window.new_poi_image = image;
        select_poi_style('icon');
        $('#modal_add_poi').modal('show');
    }

    window.select_poi_style = function (style_sel) {
        $('#dropdown_poi_style').html($('#btn_style_'+style_sel).html());
        switch(style_sel) {
            case 'icon':
                $('#div_poi_select_content button').removeClass('disabled');
                window.embed_type_sel = '';
                break;
            case 'embed_image':
                $('#div_poi_select_content button').removeClass('disabled');
                window.embed_type_sel = 'image';
                break;
            case 'embed_video':
                $('#div_poi_select_content button').addClass('disabled');
                $('#div_poi_select_content button').first().removeClass('disabled');
                window.embed_type_sel = 'video';
                break;
            case 'embed_video_transparent':
                $('#div_poi_select_content button').addClass('disabled');
                $('#div_poi_select_content button').first().removeClass('disabled');
                window.embed_type_sel = 'video_transparent';
                break;
            case 'embed_video_chroma':
                $('#div_poi_select_content button').addClass('disabled');
                $('#div_poi_select_content button').first().removeClass('disabled');
                window.embed_type_sel = 'video_chroma';
                break;
            case 'embed_gallery':
                $('#div_poi_select_content button').addClass('disabled');
                $('#div_poi_select_content button').first().removeClass('disabled');
                window.embed_type_sel = 'gallery';
                break;
            case 'embed_link':
                $('#div_poi_select_content button').addClass('disabled');
                $('#div_poi_select_content button').first().removeClass('disabled');
                window.embed_type_sel = 'link';
                break;
            case 'embed_text':
                $('#div_poi_select_content button').removeClass('disabled');
                window.embed_type_sel = 'text';
                break;
            case 'embed_selection':
                $('#div_poi_select_content button').removeClass('disabled');
                window.embed_type_sel = 'selection';
                break;
        }
    }

    window.select_poi_content_edit = function (content_sel) {
        $('#dropdown_poi_content_edit').html($('#btn_edit_content_' + content_sel).html());
        switch(content_sel) {
            case 'none':
                content_sel='';
                break;
        }
        window.content_sel = content_sel;
        if(window.content_current!=window.content_sel) {
            $('#btn_change_poi_content').removeClass('disabled');
        } else {
            $('#btn_change_poi_content').addClass('disabled');
        }
    }

    window.select_poi_style_edit = function (style_sel) {
        $('#dropdown_poi_style_edit').html($('#btn_edit_style_'+style_sel).html());
        $('#dropdown_poi_content_edit').removeClass('disabled');
        switch(style_sel) {
            case 'icon':
                window.embed_type_sel = '';
                break;
            case 'embed_image':
                window.embed_type_sel = 'image';
                break;
            case 'embed_video':
                $('#dropdown_poi_content_edit').addClass('disabled');
                select_poi_content_edit('none');
                $('#btn_change_poi_content').addClass('disabled');
                window.embed_type_sel = 'video';
                break;
            case 'embed_video_transparent':
                $('#dropdown_poi_content_edit').addClass('disabled');
                select_poi_content_edit('none');
                $('#btn_change_poi_content').addClass('disabled');
                window.embed_type_sel = 'video_transparent';
                break;
            case 'embed_video_chroma':
                $('#dropdown_poi_content_edit').addClass('disabled');
                select_poi_content_edit('none');
                $('#btn_change_poi_content').addClass('disabled');
                window.embed_type_sel = 'video_chroma';
                break;
            case 'embed_gallery':
                $('#dropdown_poi_content_edit').addClass('disabled');
                select_poi_content_edit('none');
                $('#btn_change_poi_content').addClass('disabled');
                window.embed_type_sel = 'gallery';
                break;
            case 'embed_link':
                $('#dropdown_poi_content_edit').addClass('disabled');
                select_poi_content_edit('none');
                $('#btn_change_poi_content').addClass('disabled');
                window.embed_type_sel = 'link';
                break;
            case 'embed_text':
                window.embed_type_sel = 'text';
                break;
            case 'embed_selection':
                window.embed_type_sel = 'selection';
                break;
        }
        if(window.embed_type_current!=window.embed_type_sel) {
            $('#btn_change_poi_embed_style').removeClass('disabled');
        } else {
            $('#btn_change_poi_embed_style').addClass('disabled');
        }
    }

    window.change_poi_embed_style = function() {
        var retVal = confirm(window.backend_labels.change_poi_style_msg);
        if( retVal == true ) {
            $('#btn_change_poi_embed_style').addClass('disabled');
            confirm_edit_poi(window.poi_id_edit,window.poi_index_edit);
            $.ajax({
                url: "ajax/change_poi_embed_style.php",
                type: "POST",
                data: {
                    id: window.poi_id_edit,
                    yaw: window.pois[window.poi_index_edit].yaw,
                    pitch: window.pois[window.poi_index_edit].pitch,
                    embed_type: window.embed_type_sel
                },
                async: true,
                success: function (json) {
                    jQuery.each(window.rooms, function(index, room) {
                        var id = room.id;
                        var image = room.panorama_image;
                        if(id==window.pois[window.poi_index_edit].id_room) {
                            setTimeout(function () {
                                try {
                                    var yaw = parseFloat(window.viewer.getYaw());
                                    var pitch = parseFloat(window.viewer.getPitch());
                                } catch (e) {
                                    var yaw = 0;
                                    var pitch = 0;
                                }
                                window.currentYaw = yaw;
                                window.currentPitch = pitch;
                                select_room_poi(id,image,null);
                            },250);
                        }
                    });
                }
            });
        }
    }

    window.change_poi_content = function() {
        var retVal = confirm(window.backend_labels.change_poi_style_msg);
        if( retVal == true ) {
            $('#btn_change_poi_content').addClass('disabled');
            confirm_edit_poi(window.poi_id_edit,window.poi_index_edit);
            $.ajax({
                url: "ajax/change_poi_content.php",
                type: "POST",
                data: {
                    id: window.poi_id_edit,
                    content_type: window.content_sel
                },
                async: true,
                success: function (json) {
                    jQuery.each(window.rooms, function(index, room) {
                        var id = room.id;
                        var image = room.panorama_image;
                        if(id==window.pois[window.poi_index_edit].id_room) {
                            setTimeout(function () {
                                try {
                                    var yaw = parseFloat(window.viewer.getYaw());
                                    var pitch = parseFloat(window.viewer.getPitch());
                                } catch (e) {
                                    var yaw = 0;
                                    var pitch = 0;
                                }
                                window.currentYaw = yaw;
                                window.currentPitch = pitch;
                                select_room_poi(id,image,null);
                            },250);
                        }
                    });
                }
            });
        }
    }

    var poi_embed_ids = [];
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
                poi_embed_make_transformable('.poi_embed_'+id, id, function(element, H) {});
            }
        });
        adjust_poi_embed_helpers_all();
    }

    var marker_embed_ids = [];
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
        var c_width = $('.div_panorama_container').width();
        var c_height = $('.div_panorama_container').height();
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
                        $('.poi_embed_'+id+' div').css('opacity',1);
                        $('.poi_embed_'+id+' iframe').css('opacity',1);
                        break;
                    case 'text':
                    case 'selection':
                        $('.poi_embed_'+id+' div').css('opacity',1);
                        break;
                }
            } else if(initialized==1) {
                var id = $('.poi_embed_'+id).attr('data-id');
                var pos_1 = ($('#poi_embded_helper_'+id+'_1').offset());
                pos_1.top=pos_1.top+8;
                pos_1.left=pos_1.left+8;
                $('#draggable_'+id+'_1').css({'top':pos_1.top+'px','left':pos_1.left+'px'});
                var pos_2 = ($('#poi_embded_helper_'+id+'_2').offset());
                pos_2.top=pos_2.top+8;
                pos_2.left=pos_2.left+8;
                $('#draggable_'+id+'_2').css({'top':pos_2.top+'px','left':pos_2.left+'px'});
                var pos_3 = ($('#poi_embded_helper_'+id+'_3').offset());
                pos_3.top=pos_3.top+8;
                pos_3.left=pos_3.left+8;
                $('#draggable_'+id+'_3').css({'top':pos_3.top+'px','left':pos_3.left+'px'});
                var pos_4 = ($('#poi_embded_helper_'+id+'_4').offset());
                pos_4.top=pos_4.top+8;
                pos_4.left=pos_4.left+8;
                $('#draggable_'+id+'_4').css({'top':pos_4.top+'px','left':pos_4.left+'px'});
                if(pos_1.left!=8 && pos_1.top!=8 && pos_2.left!=8 && pos_2.top!=8 && pos_3.left!=8 && pos_3.top!=8 && pos_4.left!=8 && pos_4.top!=8) {
                    $('.poi_embed_'+id).removeClass('hidden');
                    if((pos_1.left<=(c_width*1.5) && pos_3.left>=(-c_width*0.5)) && (pos_1.top<=(c_height*1.5) && pos_2.top>=(-c_height*0.5))) {
                        $('.poi_embed_'+id).show();
                        poi_embed_apply_transform($('.poi_embed_'+id), poi_embed_originals_pos[id], [[pos_1.left, pos_1.top],[pos_2.left, pos_2.top],[pos_3.left, pos_3.top],[pos_4.left, pos_4.top]]);
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
                                    $('.poi_embed_'+id+' div').css('opacity',1);
                                    $('.poi_embed_'+id+' iframe').css('opacity',1);
                                    break;
                                case 'text':
                                case 'selection':
                                    $('.poi_embed_'+id+' div').css('opacity',1);
                                    break;
                            }
                            $('.poi_embed_'+id+' .empty_embed').css('opacity',0.5);
                        } else {
                            $('.poi_embed_'+id).addClass('hidden');
                            $('.poi_embed_'+id).hide();
                        }
                    } else {
                        $('.poi_embed_'+id).hide();
                        $('.poi_embed_'+id+' .empty_embed').css('opacity',0);
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
                                $('.poi_embed_'+id+' div').css('opacity',0);
                                $('.poi_embed_'+id+' iframe').css('opacity',0);
                                break;
                            case 'text':
                            case 'selection':
                                $('.poi_embed_'+id+' div').css('opacity',0);
                                break;
                        }
                    }
                } else {
                    $('.poi_embed_'+id).addClass('hidden');
                    $('.poi_embed_'+id).hide();
                }
            }
        }
    }

    window.adjust_marker_embed_helpers_all = function () {
        var c_width = $('.div_panorama_container').width();
        var c_height = $('.div_panorama_container').height();
        for(var i=0; i<marker_embed_ids.length;i++) {
            var id = marker_embed_ids[i];
            var initialized = $('.marker_embed_'+id).attr('data-initialized');
            var transform3d = $('.marker_embed_'+id).attr('data-transform3d');
            var type = $('.marker_embed_'+id).attr('data-type');
            if(transform3d==0) {
                $('.marker_embed_'+id).css({'top':0,'left':0});
                switch(type) {
                    case 'selection':
                        $('.marker_embed_'+id+' div').css('opacity',1);
                        break;
                }
            } else if(initialized==1) {
                var id = $('.marker_embed_'+id).attr('data-id');
                var pos_1 = ($('#marker_embded_helper_'+id+'_1').offset());
                pos_1.top=pos_1.top+8;
                pos_1.left=pos_1.left+8;
                $('#draggable_'+id+'_1').css({'top':pos_1.top+'px','left':pos_1.left+'px'});
                var pos_2 = ($('#marker_embded_helper_'+id+'_2').offset());
                pos_2.top=pos_2.top+8;
                pos_2.left=pos_2.left+8;
                $('#draggable_'+id+'_2').css({'top':pos_2.top+'px','left':pos_2.left+'px'});
                var pos_3 = ($('#marker_embded_helper_'+id+'_3').offset());
                pos_3.top=pos_3.top+8;
                pos_3.left=pos_3.left+8;
                $('#draggable_'+id+'_3').css({'top':pos_3.top+'px','left':pos_3.left+'px'});
                var pos_4 = ($('#marker_embded_helper_'+id+'_4').offset());
                pos_4.top=pos_4.top+8;
                pos_4.left=pos_4.left+8;
                $('#draggable_'+id+'_4').css({'top':pos_4.top+'px','left':pos_4.left+'px'});
                if(pos_1.left!=8 && pos_1.top!=8 && pos_2.left!=8 && pos_2.top!=8 && pos_3.left!=8 && pos_3.top!=8 && pos_4.left!=8 && pos_4.top!=8) {
                    $('.marker_embed_'+id).removeClass('hidden');
                    if((pos_1.left<=(c_width*1.5) && pos_3.left>=(-c_width*0.5)) && (pos_1.top<=(c_height*1.5) && pos_2.top>=(-c_height*0.5))) {
                        $('.marker_embed_'+id).show();
                        poi_embed_apply_transform($('.marker_embed_'+id), marker_embed_originals_pos[id], [[pos_1.left, pos_1.top],[pos_2.left, pos_2.top],[pos_3.left, pos_3.top],[pos_4.left, pos_4.top]]);
                        var current_transform = $('.marker_embed_'+id).css('transform');
                        if(current_transform!="none" && $('#marker_embded_helper_'+id+'_1').position().top!=0 && $('#marker_embded_helper_'+id+'_2').position().top!=0 && $('#marker_embded_helper_'+id+'_3').position().top!=0 && $('#marker_embded_helper_'+id+'_4').position().top!=0) {
                            $('.marker_embed_'+id+' .empty_embed').css('opacity',0.5);
                            switch(type) {
                                case 'selection':
                                    $('.marker_embed_'+id+' div').css('opacity',1);
                                    break;
                            }
                        } else {
                            $('.marker_embed_'+id).addClass('hidden');
                            $('.marker_embed_'+id).hide();
                        }
                    } else {
                        $('.marker_embed_'+id).hide();
                        $('.marker_embed_'+id+' .empty_embed').css('opacity',0);
                        switch(type) {
                            case 'selection':
                                $('.marker_embed_'+id+' div').css('opacity',0);
                                break;
                        }
                    }
                } else {
                    $('.marker_embed_'+id).addClass('hidden');
                    $('.marker_embed_'+id).hide();
                }
            }
        }
    }

    var poi_embed_galleries = [];
    function hotspot_embed(hotSpotDiv, args) {
        var id = args.id;
        var type = args.embed_type;
        if(args.transform3d==1) {
            var size = args.embed_size.split(",");
            var width = size[0]+'px';
            var height = size[1]+'px';
        } else {
            var width = '100%';
            var height = '100%';
        }
        hotSpotDiv.style.zIndex = args.zIndex;
        hotSpotDiv.setAttribute('draggable',false);
        hotSpotDiv.classList.add('noselect');
        hotSpotDiv.classList.add('poi_embed');
        hotSpotDiv.classList.add('poi_embed_'+id);
        hotSpotDiv.classList.add('hotspot_'+id);
        hotSpotDiv.setAttribute('data-id',id);
        hotSpotDiv.setAttribute('data-type',type);
        hotSpotDiv.setAttribute('data-transform3d',args.transform3d);
        hotSpotDiv.setAttribute('data-initialized',0);
        if(args.embed_content!='') {
            switch(type) {
                case 'image':
                    var img = document.createElement('img');
                    img.setAttribute('draggable',false);
                    if(args.embed_content.startsWith("http") || args.embed_content.startsWith("//")) {
                        img.src = args.embed_content;
                    } else {
                        img.src = '../viewer/'+args.embed_content;
                    }
                    if(args.transform3d==0) {
                        if(args.embed_size=='') {
                            var img_tmp = new Image();
                            img_tmp.onload = function () {
                                var height = img_tmp.height;
                                var width = img_tmp.width;
                                var ratio = width / height;
                                if(width>=height) {
                                    hotSpotDiv.style = "width:300px;height:" + (300/ratio) + "px;";
                                    args.embed_size = '300,'+(300/ratio);
                                } else {
                                    hotSpotDiv.style = "width:100px;height:" + (100/ratio) + "px;";
                                    args.embed_size = '100,'+(100/ratio);
                                }
                                adjust_poi_embed_helpers_all();
                            }
                            img_tmp.src = img.src;
                        } else {
                            var size = args.embed_size.split(",");
                            var width = size[0]+'px';
                            var height = size[1]+'px';
                            hotSpotDiv.style = "width:"+width+";height:"+height;
                        }
                    }
                    img.style = "width:"+width+";height:"+height+";margin: 0 auto;vertical-align:middle;opacity:0;";
                    hotSpotDiv.appendChild(img);
                    adjust_poi_embed_helpers_all();
                    break;
                case 'text':
                    var div = document.createElement('div');
                    div.setAttribute('draggable',false);
                    div.classList.add('poi_embed_text');
                    hotSpotDiv.classList.add('no_outline');
                    var embed_content = args.embed_content.split(' border-width')[0];
                    var style = 'border-width'+args.embed_content.split(' border-width')[1];
                    div.innerHTML = embed_content;
                    var bg_color = args.background;
                    var color = args.color;
                    div.style = "width:"+width+";height:"+height+";margin: 0 auto;vertical-align:middle;opacity:0;background-color:"+bg_color+";border-color:"+color+";border-width:2px;"+style;
                    hotSpotDiv.appendChild(div);
                    break;
                case 'selection':
                    var div = document.createElement('div');
                    div.setAttribute('draggable',false);
                    div.classList.add('poi_embed_selection');
                    hotSpotDiv.classList.add('no_outline');
                    var bg_color = args.background;
                    var color = args.color;
                    div.style = "width:"+width+";height:"+height+";margin: 0 auto;vertical-align:middle;opacity:0;background-color:"+bg_color+";border-color:"+color+";"+args.embed_content;
                    hotSpotDiv.appendChild(div);
                    break;
                case 'gallery':
                    var div = document.createElement('div');
                    div.classList.add('glide');
                    div.classList.add('poi_embed_gallery');
                    div.classList.add('poi_embed_gallery_'+args.id);
                    div.style = "width:"+width+";height:"+height+";margin: 0 auto;vertical-align:middle;opacity:0;";
                    var html = '<div class="glide__track" data-glide-el="track"><ul class="glide__slides">';
                    html += '<li style="height:'+height+'px" class="glide__slide"><img style="object-fit: contain;width: 100%;height: 100%" src="'+args.embed_content+'" /></li>';
                    html += '</ul>' +
                        '<div class="glide__arrows" data-glide-el="controls">' +
                        '    <i class="glide__arrow glide__arrow--left fas fa-chevron-left" data-glide-dir="<"></i>' +
                        '    <i class="glide__arrow glide__arrow--right fas fa-chevron-right" data-glide-dir=">"></i>' +
                        '  </div>' +
                        '</div>';
                    div.innerHTML=html;
                    hotSpotDiv.appendChild(div);
                    poi_embed_galleries[args.id] = new Glide('.poi_embed_gallery_'+args.id,{
                        type: 'carousel',
                        startAt: 0,
                        perView: 1
                    });
                    poi_embed_galleries[args.id].mount();
                    break;
                case 'video_chroma':
                    var video = document.createElement('video');
                    video.setAttribute('draggable',false);
                    video.id = "video_embed_"+id;
                    video.classList.add('noselect');
                    video.crossOrigin = 'anonymous';
                    video.preload = 'auto';
                    video.muted = true;
                    video.loop = true;
                    video.setAttribute('playsinline', '');
                    video.setAttribute('webkit-playsinline','');
                    video.style = "width:"+width+";height:"+height+";margin: 0 auto;vertical-align:middle;opacity:0;";
                    video.src = '../viewer/'+args.embed_content+'#t=2';
                    window.video_chroma = video;
                    var canvas = document.createElement('canvas');
                    canvas.id = "canvas_chroma_"+id;
                    canvas.setAttribute('width', width.replace('px',''));
                    canvas.setAttribute('height', height.replace('px',''));
                    canvas.style = "width:"+width+";height:"+height+";margin: 0 auto;vertical-align:middle;opacity:0;position:absolute;top:0;left:0;";
                    hotSpotDiv.appendChild(canvas);
                    var ctx_chroma = canvas.getContext('2d');
                    window.ctx_chroma = ctx_chroma;
                    var c_chroma_tmp = document.createElement('canvas');
                    c_chroma_tmp.id = 'c_chroma_tmp_'+id;
                    c_chroma_tmp.setAttribute('width', width.replace('px',''));
                    c_chroma_tmp.setAttribute('height', height.replace('px',''));
                    var ctx_chroma_tmp = c_chroma_tmp.getContext('2d');
                    window.ctx_chroma_tmp = ctx_chroma_tmp;
                    window.width_chroma = width.replace('px','');
                    window.height_chroma = height.replace('px','');
                    video.addEventListener('loadeddata', function() {
                        remove_background_video_chroma(video,ctx_chroma_tmp,ctx_chroma,width.replace('px',''),height.replace('px',''),args.params,true);
                        $('#btn_background_removal').removeClass('disabled');
                    });
                    video.addEventListener('play', function() {
                        remove_background_video_chroma(video,ctx_chroma_tmp,ctx_chroma,width.replace('px',''),height.replace('px',''),null,false);
                    });
                    hotSpotDiv.appendChild(video);
                    video.load();
                    break;
                case 'video_transparent':
                    var video = document.createElement('video');
                    video.setAttribute('draggable',false);
                    video.id = "video_embed_"+id;
                    video.setAttribute("preload", "auto");
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
                            video.src = '../viewer/'+mov_source+'#t=2';
                        } else {
                            video.src = '../viewer/'+webm_source+'#t=2';
                        }
                    } else if(mov_source!='') {
                        video.src = '../viewer/'+mov_source+'#t=2';
                    } else if(webm_source!='') {
                        video.src = '../viewer/'+webm_source+'#t=2';
                    }
                    hotSpotDiv.appendChild(video);
                    break;
                case 'video':
                    var video = document.createElement('video');
                    video.setAttribute('draggable',false);
                    video.id = "video_embed_"+id;
                    video.classList.add('video-js');
                    video.classList.add('vjs-default-skin');
                    video.classList.add('vjs-big-play-centered');
                    video.classList.add('vjs-fluid');
                    video.setAttribute("preload", "metadata");
                    video.setAttribute('playsinline', '');
                    video.setAttribute('webkit-playsinline','');
                    video.style = "width:"+width+";height:"+height+";margin: 0 auto;vertical-align:middle;opacity:0;";
                    if(args.embed_content.includes("youtu")) {
                        video.setAttribute('data-setup','{ "techOrder":["youtube"],"sources":[{"type":"video/youtube","src":"'+args.embed_content+'"}],"youtube":{"iv_load_policy":3,"loop":1,"playsinline":1,"showinfo":0,"fs":0,"disablekb":1,"autoplay":0}}');
                    } else {
                        var source = document.createElement('source');
                        if(args.embed_content.startsWith("http") || args.embed_content.startsWith("//")) {
                            source.src = args.embed_content+'#t=2';
                        } else {
                            source.src = '../viewer/'+args.embed_content+'#t=2';
                        }
                        source.type = 'video/mp4';
                        video.appendChild(source);
                    }
                    if(args.transform3d==0) {
                        if(args.embed_size=='') {
                            video.addEventListener( "loadedmetadata", function (e) {
                                var width = this.videoWidth, height = this.videoHeight;
                                var ratio = width / height;
                                if(width>=height) {
                                    hotSpotDiv.style = "width:300px;height:" + (300/ratio) + "px;";
                                    args.embed_size = '300,'+(300/ratio);
                                } else {
                                    hotSpotDiv.style = "width:200px;height:" + (200/ratio) + "px;";
                                    args.embed_size = '200,'+(200/ratio);
                                }
                                adjust_poi_embed_helpers_all();
                            }, false );
                        } else {
                            var size = args.embed_size.split(",");
                            var width = size[0]+'px';
                            var height = size[1]+'px';
                            hotSpotDiv.style = "width:"+width+";height:"+height;
                        }
                    }
                    hotSpotDiv.appendChild(video);
                    adjust_poi_embed_helpers_all();
                    try {
                        if (typeof video_embeds[id] !== 'undefined') {
                            video_embeds[id].dispose();
                        }
                    } catch (e) {}
                    video_embeds[id] = videojs('video_embed_'+id, {controls: false}, function() {
                        $('.poi_embed_'+id+' video').css({'width':'100%','height':'100%'});
                    });
                    break;
                case 'link':
                    var div = document.createElement('div');
                    div.classList.add('poi_embed_link');
                    div.classList.add('poi_embed_link_'+args.id);
                    div.style = "width:"+width+";height:"+height+";margin: 0 auto;vertical-align:middle;opacity:0;";
                    var html = '<iframe frameborder="0" marginheight="0" marginwidth="0" style="border:none;" width="'+width+'px" height="'+height+'px" src="'+args.embed_content+'"></iframe>';
                    div.innerHTML=html;
                    hotSpotDiv.appendChild(div);
                    break;
            }
        } else {
            var div = document.createElement('div');
            div.setAttribute('draggable',false);
            div.classList.add('empty_embed');
            div.style = "width:"+width+";height:"+height+";margin: 0 auto;vertical-align:middle;background-color:rgba(255,255,255,0.6);opacity:0;";
            hotSpotDiv.appendChild(div);
        }
    }

    window.remove_background_video_chroma = function(video_chroma,ctx_chroma_tmp,ctx_chroma,width,height,params,force=false) {
        if (!force && (video_chroma.paused || video_chroma.ended)) { return; }
        if(params==null) params=window.pois[poi_index_edit].params;
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
            remove_background_video_chroma(video_chroma,ctx_chroma_tmp,ctx_chroma,width,height,null,false);
        }, 0);
    }

    function hotspot_embed_m(hotSpotDiv, args) {
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
        hotSpotDiv.setAttribute('data-id',id);
        hotSpotDiv.setAttribute('data-type',type);
        hotSpotDiv.setAttribute('data-transform3d',args.transform3d);
        hotSpotDiv.setAttribute('data-initialized',0);
        if(args.embed_content!='') {
            switch(type) {
                case 'selection':
                    var div = document.createElement('div');
                    div.setAttribute('draggable', false);
                    div.classList.add('marker_embed_selection');
                    hotSpotDiv.classList.add('no_outline');
                    var bg_color = args.background;
                    var color = args.color;
                    div.style = "width:" + width + "px;height:" + height + "px;margin: 0 auto;vertical-align:middle;opacity:0;background-color:" + bg_color + ";border-color:" + color + ";" + args.embed_content;
                    hotSpotDiv.appendChild(div);
                    break;
            }
        } else {
            var div = document.createElement('div');
            div.setAttribute('draggable',false);
            div.classList.add('empty_embed');
            div.style = "width:"+width+"px;height:"+height+"px;margin: 0 auto;vertical-align:middle;background-color:rgba(255,255,255,0.6);;opacity:0;";
            hotSpotDiv.appendChild(div);
        }
    }

    function supportsHEVCAlpha() {
        const navigator = window.navigator;
        const ua = navigator.userAgent.toLowerCase();
        const hasMediaCapabilities = !!(navigator.mediaCapabilities && navigator.mediaCapabilities.decodingInfo);
        const isSafari = ((ua.indexOf('safari') != -1) && (!(ua.indexOf('chrome')!= -1) && (ua.indexOf('version/')!= -1)));
        return isSafari && hasMediaCapabilities;
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

    function hotspot_embed_move(hotSpotDiv, args) {
        hotSpotDiv.setAttribute('draggable',false);
        hotSpotDiv.classList.add('noselect');
        hotSpotDiv.classList.add('poi_embded_move');
        hotSpotDiv.setAttribute('id','poi_embded_move_'+args);
        var icon = document.createElement('i');
        icon.classList.add('fas');
        icon.classList.add('fa-arrows-alt');
        icon.style = 'font-size:16px;cursor:pointer;color:red;background-color:white;border-radius:50%;padding:5px;opacity:0.6;';
        hotSpotDiv.append(icon);
    }

    function hotspot_embed_move_m(hotSpotDiv, args) {
        hotSpotDiv.setAttribute('draggable',false);
        hotSpotDiv.classList.add('noselect');
        hotSpotDiv.classList.add('marker_embded_move');
        hotSpotDiv.setAttribute('id','marker_embded_move_'+args);
        var icon = document.createElement('i');
        icon.classList.add('fas');
        icon.classList.add('fa-arrows-alt');
        icon.style = 'font-size:16px;cursor:pointer;color:red;background-color:white;border-radius:50%;padding:5px;opacity:0.6;';
        hotSpotDiv.append(icon);
    }

    function drag_marker_move(event, args) {
        var index = args;
        var coords = window.viewer.mouseEventToCoords(event);
        var yaw = parseFloat(coords[1]);
        var pitch = parseFloat(coords[0]);
        window.markers[index].yaw = yaw;
        window.markers[index].pitch = pitch;
        switch(event.type) {
            case 'mousedown':
            case 'touchstart':
            case 'pointerdown':
                $('#confirm_move').css('height','34px');
                break;
            case 'mouseup':
            case 'touchend':
            case 'pointerup':
                $('#confirm_move').css('height','auto');
                break;
        }
        pauseEvent(event);
    }

    function drag_poi_move(event, args) {
        var index = args;
        var coords = window.viewer.mouseEventToCoords(event);
        var yaw = parseFloat(coords[1]);
        var pitch = parseFloat(coords[0]);
        window.pois[index].yaw = yaw;
        window.pois[index].pitch = pitch;
        switch(event.type) {
            case 'mousedown':
            case 'touchstart':
            case 'pointerdown':
                $('#confirm_move').css('height','34px');
                break;
            case 'mouseup':
            case 'touchend':
            case 'pointerup':
                $('#confirm_move').css('height','auto');
                break;
        }
        pauseEvent(event);
    }

    function pauseEvent(e){
        if(e.stopPropagation) e.stopPropagation();
        if(e.preventDefault) e.preventDefault();
        e.cancelBubble=true;
        e.returnValue=false;
        return false;
    }

    var interval_drag_helper;
    function drag_embed_helper(event, args) {
        var poi = args[0];
        var id = poi.id;
        var index = args[1];
        switch(event.type) {
            case 'mousedown':
            case 'touchstart':
            case 'pointerdown':
                $('#confirm_move').css('height','34px');
                $('.poi_embed_'+id).css({'outline':'1px dashed red','opacity':0.6});
                window.viewer.setUpdate(true);
                interval_drag_helper = setInterval(function () {
                    adjust_poi_embed_helpers_all();
                },10);
                break;
            case 'mouseup':
            case 'touchend':
            case 'pointerup':
                $('#confirm_move').css('height','auto');
                $('.poi_embed_'+id).css({'outline':'none','opacity':1.0});
                window.viewer.setUpdate(false);
                clearInterval(interval_drag_helper);
                var coords = window.viewer.mouseEventToCoords(event);
                var yaw = parseFloat(coords[1]);
                var pitch = parseFloat(coords[0]);
                var poi_embed_helpers = poi.embed_coords.split("|");
                poi_embed_helpers[0] = poi_embed_helpers[0].split(",");
                poi_embed_helpers[1] = poi_embed_helpers[1].split(",");
                poi_embed_helpers[2] = poi_embed_helpers[2].split(",");
                poi_embed_helpers[3] = poi_embed_helpers[3].split(",");
                poi_embed_helpers[index][0] = pitch;
                poi_embed_helpers[index][1] = yaw;
                var embed_coords = poi_embed_helpers[0][0]+","+poi_embed_helpers[0][1]+"|"+poi_embed_helpers[1][0]+","+poi_embed_helpers[1][1]+"|"+poi_embed_helpers[2][0]+","+poi_embed_helpers[2][1]+"|"+poi_embed_helpers[3][0]+","+poi_embed_helpers[3][1];
                poi.embed_coords = embed_coords;
                var pos_1 = ($('#poi_embded_helper_'+id+'_1').offset());
                var pos_2 = ($('#poi_embded_helper_'+id+'_2').offset());
                var pos_3 = ($('#poi_embded_helper_'+id+'_3').offset());
                var pos_4 = ($('#poi_embded_helper_'+id+'_4').offset());
                var center_left = ((parseFloat(pos_1.left) + parseFloat(pos_2.left) + parseFloat(pos_3.left) + parseFloat(pos_4.left)) / 4) + 8;
                var center_top = ((parseFloat(pos_1.top) + parseFloat(pos_2.top) + parseFloat(pos_3.top) + parseFloat(pos_4.top)) / 4) + 8;
                var event_center = [];
                event_center.clientX = center_left;
                event_center.clientY = center_top;
                var coords_center = window.viewer.mouseEventToCoords(event_center);
                poi.yaw = parseFloat(coords_center[1]);
                poi.pitch = parseFloat(coords_center[0]);
                var w = Math.getDistance(pos_1.left,pos_1.top,pos_3.left,pos_3.top);
                var h = Math.getDistance(pos_1.left,pos_1.top,pos_2.left,pos_2.top);
                poi.embed_size = w+","+h;
                jQuery.each(window.pois, function(index, poi_a) {
                    if(id==poi_a.id) {
                        window.pois[index].yaw = poi.yaw;
                        window.pois[index].pitch = poi.pitch;
                        window.pois[index].embed_coords = poi.embed_coords;
                        window.pois[index].embed_size = poi.embed_size;
                        switch(window.pois[index].embed_type) {
                            case 'gallery':
                                try {
                                    poi_embed_galleries[id].destroy();
                                } catch (e) {}
                                render_poi(id,index);
                                $('#poi_embded_move_'+id).css({'opacity':1,'pointer-events':'initial'});
                                $('#poi_embded_helper_'+id+'_1').css({'opacity':1,'pointer-events':'initial'});
                                $('#poi_embded_helper_'+id+'_2').css({'opacity':1,'pointer-events':'initial'});
                                $('#poi_embded_helper_'+id+'_3').css({'opacity':1,'pointer-events':'initial'});
                                $('#poi_embded_helper_'+id+'_4').css({'opacity':1,'pointer-events':'initial'});
                                break;
                            case 'link':
                            case 'text':
                            case 'selection':
                                render_poi(id,index);
                                $('#poi_embded_move_'+id).css({'opacity':1,'pointer-events':'initial'});
                                $('#poi_embded_helper_'+id+'_1').css({'opacity':1,'pointer-events':'initial'});
                                $('#poi_embded_helper_'+id+'_2').css({'opacity':1,'pointer-events':'initial'});
                                $('#poi_embded_helper_'+id+'_3').css({'opacity':1,'pointer-events':'initial'});
                                $('#poi_embded_helper_'+id+'_4').css({'opacity':1,'pointer-events':'initial'});
                                break;
                            default:
                                window.viewer.removeHotSpot("p"+id.toString()+"_move");
                                window.viewer.addHotSpot({
                                    "id": "p"+window.pois[index].id+"_move",
                                    "type": 'pointer',
                                    "object": "poi_embed_helper",
                                    "transform3d": false,
                                    "pitch": parseFloat(window.pois[index].pitch),
                                    "yaw": parseFloat(window.pois[index].yaw),
                                    "size_scale": 1,
                                    "rotateX": 0,
                                    "rotateZ": 0,
                                    "draggable": true,
                                    "cssClass": "hotspot-helper",
                                    "createTooltipFunc": hotspot_embed_move,
                                    "createTooltipArgs": window.pois[index].id,
                                    "dragHandlerFunc": drag_embed_move,
                                    "dragHandlerArgs": [window.pois[index].id,index]
                                });
                                $('#poi_embded_move_'+id).css({'opacity':1,'pointer-events':'initial'});
                                break;
                        }
                    }
                });
                break;
        }
    }

    var interval_drag_helper_m;
    function drag_embed_helper_m(event, args) {
        var marker = args[0];
        var id = marker.id;
        var index = args[1];
        switch(event.type) {
            case 'mousedown':
            case 'touchstart':
            case 'pointerdown':
                $('#confirm_move').css('height','34px');
                $('.marker_embed_'+id).css({'outline':'1px dashed red','opacity':0.6});
                window.viewer.setUpdate(true);
                interval_drag_helper_m = setInterval(function () {
                    adjust_marker_embed_helpers_all();
                },10);
                break;
            case 'mouseup':
            case 'touchend':
            case 'pointerup':
                $('#confirm_move').css('height','auto');
                $('.marker_embed_'+id).css({'outline':'none','opacity':1.0});
                window.viewer.setUpdate(false);
                clearInterval(interval_drag_helper_m);
                var coords = window.viewer.mouseEventToCoords(event);
                var yaw = parseFloat(coords[1]);
                var pitch = parseFloat(coords[0]);
                var marker_embed_helpers = marker.embed_coords.split("|");
                marker_embed_helpers[0] = marker_embed_helpers[0].split(",");
                marker_embed_helpers[1] = marker_embed_helpers[1].split(",");
                marker_embed_helpers[2] = marker_embed_helpers[2].split(",");
                marker_embed_helpers[3] = marker_embed_helpers[3].split(",");
                marker_embed_helpers[index][0] = pitch;
                marker_embed_helpers[index][1] = yaw;
                var embed_coords = marker_embed_helpers[0][0]+","+marker_embed_helpers[0][1]+"|"+marker_embed_helpers[1][0]+","+marker_embed_helpers[1][1]+"|"+marker_embed_helpers[2][0]+","+marker_embed_helpers[2][1]+"|"+marker_embed_helpers[3][0]+","+marker_embed_helpers[3][1];
                marker.embed_coords = embed_coords;
                var pos_1 = ($('#marker_embded_helper_'+id+'_1').offset());
                var pos_2 = ($('#marker_embded_helper_'+id+'_2').offset());
                var pos_3 = ($('#marker_embded_helper_'+id+'_3').offset());
                var pos_4 = ($('#marker_embded_helper_'+id+'_4').offset());
                var center_left = ((parseFloat(pos_1.left) + parseFloat(pos_2.left) + parseFloat(pos_3.left) + parseFloat(pos_4.left)) / 4) + 8;
                var center_top = ((parseFloat(pos_1.top) + parseFloat(pos_2.top) + parseFloat(pos_3.top) + parseFloat(pos_4.top)) / 4) + 8;
                var event_center = [];
                event_center.clientX = center_left;
                event_center.clientY = center_top;
                var coords_center = window.viewer.mouseEventToCoords(event_center);
                marker.yaw = parseFloat(coords_center[1]);
                marker.pitch = parseFloat(coords_center[0]);
                var w = Math.getDistance(pos_1.left,pos_1.top,pos_3.left,pos_3.top);
                var h = Math.getDistance(pos_1.left,pos_1.top,pos_2.left,pos_2.top);
                marker.embed_size = w+","+h;
                jQuery.each(window.markers, function(index, marker_a) {
                    if(id==marker_a.id) {
                        window.markers[index].yaw = marker.yaw;
                        window.markers[index].pitch = marker.pitch;
                        window.markers[index].embed_coords = marker.embed_coords;
                        window.markers[index].embed_size = marker.embed_size;
                        switch(window.markers[index].embed_type) {
                            case 'selection':
                                render_marker(id,index);
                                $('#marker_embded_move_'+id).css({'opacity':1,'pointer-events':'initial'});
                                $('#marker_embded_helper_'+id+'_1').css({'opacity':1,'pointer-events':'initial'});
                                $('#marker_embded_helper_'+id+'_2').css({'opacity':1,'pointer-events':'initial'});
                                $('#marker_embded_helper_'+id+'_3').css({'opacity':1,'pointer-events':'initial'});
                                $('#marker_embded_helper_'+id+'_4').css({'opacity':1,'pointer-events':'initial'});
                                break;
                            default:
                                window.viewer.removeHotSpot("m"+id.toString()+"_move");
                                window.viewer.addHotSpot({
                                    "id": "m"+window.markers[index].id+"_move",
                                    "type": 'pointer',
                                    "object": "marker_embed_helper",
                                    "transform3d": false,
                                    "pitch": parseFloat(window.markers[index].pitch),
                                    "yaw": parseFloat(window.markers[index].yaw),
                                    "size_scale": 1,
                                    "rotateX": 0,
                                    "rotateZ": 0,
                                    "draggable": true,
                                    "cssClass": "hotspot-helper",
                                    "createTooltipFunc": hotspot_embed_move_m,
                                    "createTooltipArgs": window.markers[index].id,
                                    "dragHandlerFunc": drag_embed_move_m,
                                    "dragHandlerArgs": [window.markers[index].id,index]
                                });
                                $('#marker_embded_move_'+id).css({'opacity':1,'pointer-events':'initial'});
                                break;
                        }
                    }
                });
                break;
        }
    }

    var yaw_i,pitch_i;
    function drag_embed_move(event, args) {
        var id = args[0];
        var index = args[1];
        switch(event.type) {
            case 'mousedown':
            case 'touchstart':
            case 'pointerdown':
                $('#confirm_move').css('height','34px');
                $('.poi_embed_'+id).css('outline','1px dashed red');
                var coords = window.viewer.mouseEventToCoords(event);
                yaw_i = parseFloat(coords[1]);
                pitch_i = parseFloat(coords[0]);
                break;
            case 'mouseup':
            case 'touchend':
            case 'pointerup':
                $('#confirm_move').css('height','auto');
                $('.poi_embed_'+id).css('outline','none');
                var coords = window.viewer.mouseEventToCoords(event);
                var yaw_c = parseFloat(coords[1]);
                var pitch_c = parseFloat(coords[0]);
                window.pois[index].yaw = yaw_c;
                window.pois[index].pitch = pitch_c;
                var yaw_diff = yaw_c-yaw_i;
                var pitch_diff = pitch_c-pitch_i;
                var poi_embed_helpers = window.pois[index].embed_coords.split("|");
                poi_embed_helpers[0] = poi_embed_helpers[0].split(",");
                poi_embed_helpers[1] = poi_embed_helpers[1].split(",");
                poi_embed_helpers[2] = poi_embed_helpers[2].split(",");
                poi_embed_helpers[3] = poi_embed_helpers[3].split(",");
                poi_embed_helpers[0][0] = parseFloat(poi_embed_helpers[0][0]) + pitch_diff;
                poi_embed_helpers[0][1] = parseFloat(poi_embed_helpers[0][1]) + yaw_diff;
                poi_embed_helpers[1][0] = parseFloat(poi_embed_helpers[1][0]) + pitch_diff;
                poi_embed_helpers[1][1] = parseFloat(poi_embed_helpers[1][1]) + yaw_diff;
                poi_embed_helpers[2][0] = parseFloat(poi_embed_helpers[2][0]) + pitch_diff;
                poi_embed_helpers[2][1] = parseFloat(poi_embed_helpers[2][1]) + yaw_diff;
                poi_embed_helpers[3][0] = parseFloat(poi_embed_helpers[3][0]) + pitch_diff;
                poi_embed_helpers[3][1] = parseFloat(poi_embed_helpers[3][1]) + yaw_diff;
                var embed_coords = poi_embed_helpers[0][0]+","+poi_embed_helpers[0][1]+"|"+poi_embed_helpers[1][0]+","+poi_embed_helpers[1][1]+"|"+poi_embed_helpers[2][0]+","+poi_embed_helpers[2][1]+"|"+poi_embed_helpers[3][0]+","+poi_embed_helpers[3][1];
                window.pois[index].embed_coords = embed_coords;
                render_poi(id,index);
                $('#poi_embded_move_'+id).css({'opacity':1,'pointer-events':'initial'});
                $('#poi_embded_helper_'+id+'_1').css({'opacity':1,'pointer-events':'initial'});
                $('#poi_embded_helper_'+id+'_2').css({'opacity':1,'pointer-events':'initial'});
                $('#poi_embded_helper_'+id+'_3').css({'opacity':1,'pointer-events':'initial'});
                $('#poi_embded_helper_'+id+'_4').css({'opacity':1,'pointer-events':'initial'});
                break;

        }
    }

    var yaw_i_m,pitch_i_m;
    function drag_embed_move_m(event, args) {
        var id = args[0];
        var index = args[1];
        switch(event.type) {
            case 'mousedown':
            case 'touchstart':
            case 'pointerdown':
                $('#confirm_move').css('height','34px');
                $('.marker_embed_'+id).css('outline','1px dashed red');
                var coords = window.viewer.mouseEventToCoords(event);
                yaw_i_m = parseFloat(coords[1]);
                pitch_i_m = parseFloat(coords[0]);
                break;
            case 'mouseup':
            case 'touchend':
            case 'pointerup':
                $('#confirm_move').css('height','auto');
                $('.marker_embed_'+id).css('outline','none');
                var coords = window.viewer.mouseEventToCoords(event);
                var yaw_c = parseFloat(coords[1]);
                var pitch_c = parseFloat(coords[0]);
                window.markers[index].yaw = yaw_c;
                window.markers[index].pitch = pitch_c;
                var yaw_diff = yaw_c-yaw_i_m;
                var pitch_diff = pitch_c-pitch_i_m;
                var marker_embed_helpers = window.markers[index].embed_coords.split("|");
                marker_embed_helpers[0] = marker_embed_helpers[0].split(",");
                marker_embed_helpers[1] = marker_embed_helpers[1].split(",");
                marker_embed_helpers[2] = marker_embed_helpers[2].split(",");
                marker_embed_helpers[3] = marker_embed_helpers[3].split(",");
                marker_embed_helpers[0][0] = parseFloat(marker_embed_helpers[0][0]) + pitch_diff;
                marker_embed_helpers[0][1] = parseFloat(marker_embed_helpers[0][1]) + yaw_diff;
                marker_embed_helpers[1][0] = parseFloat(marker_embed_helpers[1][0]) + pitch_diff;
                marker_embed_helpers[1][1] = parseFloat(marker_embed_helpers[1][1]) + yaw_diff;
                marker_embed_helpers[2][0] = parseFloat(marker_embed_helpers[2][0]) + pitch_diff;
                marker_embed_helpers[2][1] = parseFloat(marker_embed_helpers[2][1]) + yaw_diff;
                marker_embed_helpers[3][0] = parseFloat(marker_embed_helpers[3][0]) + pitch_diff;
                marker_embed_helpers[3][1] = parseFloat(marker_embed_helpers[3][1]) + yaw_diff;
                var embed_coords = marker_embed_helpers[0][0]+","+marker_embed_helpers[0][1]+"|"+marker_embed_helpers[1][0]+","+marker_embed_helpers[1][1]+"|"+marker_embed_helpers[2][0]+","+marker_embed_helpers[2][1]+"|"+marker_embed_helpers[3][0]+","+marker_embed_helpers[3][1];
                window.markers[index].embed_coords = embed_coords;
                render_marker(id,index);
                $('#marker_embded_move_'+id).css({'opacity':1,'pointer-events':'initial'});
                $('#marker_embded_helper_'+id+'_1').css({'opacity':1,'pointer-events':'initial'});
                $('#marker_embded_helper_'+id+'_2').css({'opacity':1,'pointer-events':'initial'});
                $('#marker_embded_helper_'+id+'_3').css({'opacity':1,'pointer-events':'initial'});
                $('#marker_embded_helper_'+id+'_4').css({'opacity':1,'pointer-events':'initial'});
                break;

        }
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

    window.poi_embed_apply_transform = function(element, originalPos, targetPos, callback) {
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
                    }).appendTo('.div_panorama_container').position({
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
                    results.push([p.offset().left, p.offset().top]);
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
                    }).appendTo('.div_panorama_container').position({
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
                    results.push([p.offset().left, p.offset().top]);
                }
                return results;
            })();
            marker_embed_originals_pos[id] = originalPos;
            return element;
        });
    };

    function initialize_pano_pois(id_room,image,id_poi_add,array_room) {
        if(window.currentYaw==0) {
            window.currentYaw = parseInt(array_room.yaw);
        }
        if(window.currentPitch==0) {
            window.currentPitch = parseInt(array_room.pitch);
        }
        window.panorama_image = image;
        $('#panorama_pois').show();
        var hotSpots = [];
        var index_poi_add = null;
        jQuery.each(window.pois, function(index, poi) {
            if(id_poi_add!=null) {
                if(id_poi_add==poi.id) {
                    index_poi_add = index;
                }
            }
            if(poi.what=='poi') {
                if(poi.embed_type!='' && poi.transform3d==1) {
                    hotSpots.push({
                        "id": "p"+poi.id,
                        "type": poi.embed_type,
                        "object": "poi_embed",
                        "transform3d": parseInt(poi.transform3d),
                        "tooltip_type": "",
                        "pitch": parseFloat(poi.pitch),
                        "yaw": parseFloat(poi.yaw),
                        "rotateX": 0,
                        "rotateZ": 0,
                        "size_scale": 1,
                        "cssClass": "hotspot-embed",
                        "createTooltipFunc": hotspot_embed,
                        "createTooltipArgs": poi,
                        "clickHandlerFunc": click_edit_poi,
                        "clickHandlerArgs": poi.id
                    });
                    if(poi.transform3d==1) {
                        var poi_embed_helpers = poi.embed_coords.split("|");
                        poi_embed_helpers[0] = poi_embed_helpers[0].split(",");
                        poi_embed_helpers[1] = poi_embed_helpers[1].split(",");
                        poi_embed_helpers[2] = poi_embed_helpers[2].split(",");
                        poi_embed_helpers[3] = poi_embed_helpers[3].split(",");
                        jQuery.each(poi_embed_helpers, function(index_h, poi_embed_helper) {
                            hotSpots.push({
                                "id": "p"+poi.id+"_"+(index_h+1),
                                "type": 'pointer',
                                "object": "poi_embed_helper",
                                "transform3d": false,
                                "pitch": parseFloat(poi_embed_helper[0]),
                                "yaw": parseFloat(poi_embed_helper[1]),
                                "size_scale": 1,
                                "rotateX": 0,
                                "rotateZ": 0,
                                "draggable": true,
                                "cssClass": "hotspot-helper",
                                "createTooltipFunc": hotspot_embed_helper,
                                "createTooltipArgs": [poi.id,(index_h+1)],
                                "dragHandlerFunc": drag_embed_helper,
                                "dragHandlerArgs": [poi,index_h]
                            });
                        });
                        hotSpots.push({
                            "id": "p"+poi.id+"_move",
                            "type": 'pointer',
                            "object": "poi_embed_helper",
                            "transform3d": false,
                            "pitch": parseFloat(poi.pitch),
                            "yaw": parseFloat(poi.yaw),
                            "size_scale": 1,
                            "rotateX": 0,
                            "rotateZ": 0,
                            "draggable": true,
                            "cssClass": "hotspot-helper",
                            "createTooltipFunc": hotspot_embed_move,
                            "createTooltipArgs": poi.id,
                            "dragHandlerFunc": drag_embed_move,
                            "dragHandlerArgs": [poi.id,index]
                        });
                    }
                } else if(poi.embed_type!='' && poi.transform3d==0) {
                    hotSpots.push({
                        "id": "p"+poi.id,
                        "type": poi.embed_type,
                        "object": "poi_embed",
                        "transform3d": parseInt(poi.transform3d),
                        "tooltip_type": "",
                        "pitch": parseFloat(poi.pitch),
                        "yaw": parseFloat(poi.yaw),
                        "rotateX": parseInt(poi.rotateX),
                        "rotateZ": parseInt(poi.rotateZ),
                        "size_scale": parseFloat(poi.size_scale),
                        "cssClass": "hotspot-embed",
                        "createTooltipFunc": hotspot_embed,
                        "createTooltipArgs": poi,
                        "clickHandlerFunc": click_edit_poi,
                        "clickHandlerArgs": poi.id
                    });
                } else {
                    hotSpots.push({
                        "id": poi.id,
                        "type": 'poi',
                        "transform3d": false,
                        "pitch": parseFloat(poi.pitch),
                        "yaw": parseFloat(poi.yaw),
                        "rotateX": parseInt(poi.rotateX),
                        "rotateZ": parseInt(poi.rotateZ),
                        "size_scale": parseFloat(poi.size_scale),
                        "cssClass": "custom-hotspot-content",
                        "createTooltipFunc": hotspot_p,
                        "createTooltipArgs": poi,
                        "clickHandlerFunc": click_edit_poi,
                        "clickHandlerArgs": poi.id
                    });
                }
            } else {
                if(poi.embed_type!='') {
                    hotSpots.push({
                        "id": "m"+poi.id,
                        "type": poi.embed_type,
                        "object": "marker_embed",
                        "transform3d": parseInt(poi.transform3d),
                        "tooltip_type": "",
                        "pitch": parseFloat(poi.pitch),
                        "yaw": parseFloat(poi.yaw),
                        "rotateX": 0,
                        "rotateZ": 0,
                        "size_scale": 1,
                        "cssClass": "hotspot-embed",
                        "createTooltipFunc": hotspot_embed_m,
                        "createTooltipArgs": poi
                    });
                    var marker_embed_helpers = poi.embed_coords.split("|");
                    marker_embed_helpers[0] = marker_embed_helpers[0].split(",");
                    marker_embed_helpers[1] = marker_embed_helpers[1].split(",");
                    marker_embed_helpers[2] = marker_embed_helpers[2].split(",");
                    marker_embed_helpers[3] = marker_embed_helpers[3].split(",");
                    jQuery.each(marker_embed_helpers, function (index_h, marker_embed_helper) {
                        hotSpots.push({
                            "id": "m"+poi.id + "_" + (index_h + 1),
                            "type": 'pointer',
                            "object": "marker_embed_helper",
                            "transform3d": false,
                            "pitch": parseFloat(marker_embed_helper[0]),
                            "yaw": parseFloat(marker_embed_helper[1]),
                            "size_scale": 1,
                            "rotateX": 0,
                            "rotateZ": 0,
                            "draggable": true,
                            "cssClass": "hotspot-helper",
                            "createTooltipFunc": hotspot_embed_helper_m,
                            "createTooltipArgs": [poi.id, (index_h + 1)]
                        });
                    });
                    hotSpots.push({
                        "id": "m"+poi.id + "_move",
                        "type": 'pointer',
                        "object": "marker_embed_helper",
                        "transform3d": false,
                        "pitch": parseFloat(poi.pitch),
                        "yaw": parseFloat(poi.yaw),
                        "size_scale": 1,
                        "rotateX": 0,
                        "rotateZ": 0,
                        "draggable": true,
                        "cssClass": "hotspot-helper",
                        "createTooltipFunc": hotspot_embed_move_m,
                        "createTooltipArgs": poi.id
                    });
                } else {
                    hotSpots.push({
                        "type": 'marker',
                        "transform3d": false,
                        "pitch": parseFloat(poi.pitch),
                        "yaw": parseFloat(poi.yaw),
                        "rotateX": parseInt(poi.rotateX),
                        "rotateZ": parseInt(poi.rotateZ),
                        "size_scale": parseFloat(poi.size_scale),
                        "cssClass": "custom-hotspot",
                        "createTooltipFunc": hotspot_m,
                        "createTooltipArgs": poi
                    });
                }
            }
        });
        try {
            window.video_viewer.dispose();
        } catch (e) {}
        try {
            window.viewer.destroy();
        } catch (e) {}
        $('#panorama_pois').empty();
        if(array_room.room_type == 'video') {
            $('#panorama_pois').append('<video controls playsinline webkit-playsinline id="video_viewer" class="video-js vjs-default-skin vjs-big-play-centered" style="width: 100%;height: 600px;margin: 0 auto;" muted preload="none" crossorigin="anonymous"><source src="../viewer/videos/'+array_room.panorama_video+'" type="video/mp4"/></video>');
            var container_h = $('#content-wrapper').height() - 280;
            $('#video_viewer').css('height',container_h+'px');
            window.video_viewer = videojs('video_viewer', {
                loop: true,
                autoload: true,
                muted: true,
                controls: true,
                plugins: {
                    pannellum: {
                        "autoLoad": true,
                        "showFullscreenCtrl": false,
                        "showControls": false,
                        "horizonPitch": parseInt(array_room.h_pitch),
                        "horizonRoll": parseInt(array_room.h_roll),
                        "hfov": 100,
                        "minHfov": 70,
                        "maxHfov": 120,
                        "yaw": parseFloat(window.currentYaw),
                        "pitch": parseFloat(window.currentPitch),
                        "haov": parseInt(array_room.haov),
                        "vaov": parseInt(array_room.vaov),
                        "hotSpots": hotSpots,
                        "friction": 1,
                        "strings": {
                            "loadingLabel": window.backend_labels.loading+"...",
                        },
                    }
                }
            });
            window.video_viewer.load();
            window.video_viewer.on('ready', function() {
                window.video_viewer.play();
                window.viewer = window.video_viewer.pnlmViewer;
                window.viewer.on('load',function () {
                    viewer_poi_initialized(id_poi_add,index_poi_add);
                });
            });
        } else {
            if(parseInt(array_room.multires)) {
                var multires_config = JSON.parse(array_room.multires_config);
                window.viewer = pannellum.viewer('panorama_pois', {
                    "type": "multires",
                    "multiRes": multires_config,
                    "backgroundColor": [1,1,1],
                    "autoLoad": true,
                    "showFullscreenCtrl": false,
                    "showControls": false,
                    "multiResMinHfov": true,
                    "horizonPitch": parseInt(array_room.h_pitch),
                    "horizonRoll": parseInt(array_room.h_roll),
                    "hfov": 100,
                    "minHfov": 70,
                    "maxHfov": 120,
                    "yaw": parseFloat(window.currentYaw),
                    "pitch": parseFloat(window.currentPitch),
                    "haov": parseInt(array_room.haov),
                    "vaov": parseInt(array_room.vaov),
                    "friction": 1,
                    "hotSpots": hotSpots
                });
                setTimeout(function () {
                    viewer_poi_initialized(id_poi_add,index_poi_add);
                },200);
            } else {
                window.viewer = pannellum.viewer('panorama_pois', {
                    "type": "equirectangular",
                    "panorama": "../viewer/panoramas/"+image,
                    "autoLoad": true,
                    "showFullscreenCtrl": false,
                    "showControls": false,
                    "multiResMinHfov": true,
                    "horizonPitch": parseInt(array_room.h_pitch),
                    "horizonRoll": parseInt(array_room.h_roll),
                    "hfov": 100,
                    "minHfov": 70,
                    "maxHfov": 120,
                    "yaw": parseFloat(window.currentYaw),
                    "pitch": parseFloat(window.currentPitch),
                    "haov": parseInt(array_room.haov),
                    "vaov": parseInt(array_room.vaov),
                    "friction": 1,
                    "hotSpots": hotSpots,
                    "strings": {
                        "loadingLabel": window.backend_labels.loading+"...",
                    },
                });
                window.viewer.on("load",function () {
                    viewer_poi_initialized(id_poi_add,index_poi_add);
                });
            }
            window.viewer.on('animatefinished',function () {
                var yaw = parseFloat(viewer.getYaw());
                var pitch = parseFloat(viewer.getPitch());
                window.currentYaw = yaw;
                window.currentPitch = pitch;
            });
        }
    }

    function initialize_pano_markers(id_room,image,id_marker_add,array_room) {
        if(window.currentYaw==0) {
            window.currentYaw = parseInt(array_room.yaw);
        }
        if(window.currentPitch==0) {
            window.currentPitch = parseInt(array_room.pitch);
        }
        window.panorama_image = image;
        $('#panorama_markers').show();
        var hotSpots = [];
        var index_marker_add = null;
        jQuery.each(window.markers, function(index_m, marker_m) {
            if(id_marker_add!=null) {
                if(id_marker_add==marker_m.id) {
                    index_marker_add = index_m;
                }
            }
            if(marker_m.what=='marker') {
                if(marker_m.embed_type!='') {
                    hotSpots.push({
                        "id": "m"+marker_m.id,
                        "type": marker_m.embed_type,
                        "object": "marker_embed",
                        "transform3d": parseInt(marker_m.transform3d),
                        "tooltip_type": "",
                        "pitch": parseFloat(marker_m.pitch),
                        "yaw": parseFloat(marker_m.yaw),
                        "rotateX": 0,
                        "rotateZ": 0,
                        "size_scale": 1,
                        "cssClass": "hotspot-embed",
                        "createTooltipFunc": hotspot_embed_m,
                        "createTooltipArgs": marker_m,
                        "clickHandlerFunc": click_edit_marker,
                        "clickHandlerArgs": marker_m.id
                    });
                    if(marker_m.transform3d==1) {
                        var marker_embed_helpers = marker_m.embed_coords.split("|");
                        marker_embed_helpers[0] = marker_embed_helpers[0].split(",");
                        marker_embed_helpers[1] = marker_embed_helpers[1].split(",");
                        marker_embed_helpers[2] = marker_embed_helpers[2].split(",");
                        marker_embed_helpers[3] = marker_embed_helpers[3].split(",");
                        jQuery.each(marker_embed_helpers, function(index_h, marker_embed_helper) {
                            hotSpots.push({
                                "id": "m"+marker_m.id+"_"+(index_h+1),
                                "type": 'pointer',
                                "object": "marker_embed_helper",
                                "transform3d": false,
                                "pitch": parseFloat(marker_embed_helper[0]),
                                "yaw": parseFloat(marker_embed_helper[1]),
                                "size_scale": 1,
                                "rotateX": 0,
                                "rotateZ": 0,
                                "draggable": true,
                                "cssClass": "hotspot-helper",
                                "createTooltipFunc": hotspot_embed_helper_m,
                                "createTooltipArgs": [marker_m.id,(index_h+1)],
                                "dragHandlerFunc": drag_embed_helper_m,
                                "dragHandlerArgs": [marker_m,index_h]
                            });
                        });
                        hotSpots.push({
                            "id": "m"+marker_m.id+"_move",
                            "type": 'pointer',
                            "object": "marker_embed_helper",
                            "transform3d": false,
                            "pitch": parseFloat(marker_m.pitch),
                            "yaw": parseFloat(marker_m.yaw),
                            "size_scale": 1,
                            "rotateX": 0,
                            "rotateZ": 0,
                            "draggable": true,
                            "cssClass": "hotspot-helper",
                            "createTooltipFunc": hotspot_embed_move_m,
                            "createTooltipArgs": marker_m.id,
                            "dragHandlerFunc": drag_embed_move_m,
                            "dragHandlerArgs": [marker_m.id,index_m]
                        });
                    }
                } else {
                    hotSpots.push({
                        "id": marker_m.id,
                        "type": 'marker',
                        "transform3d": false,
                        "pitch": parseFloat(marker_m.pitch),
                        "yaw": parseFloat(marker_m.yaw),
                        "rotateX": parseInt(marker_m.rotateX),
                        "rotateZ": parseInt(marker_m.rotateZ),
                        "size_scale": parseFloat(marker_m.size_scale),
                        "cssClass": "custom-hotspot",
                        "createTooltipFunc": hotspot_m,
                        "createTooltipArgs": marker_m,
                        "clickHandlerFunc": click_edit_marker,
                        "clickHandlerArgs": marker_m.id
                    });
                }
            } else {
                if(marker_m.embed_type!='') {
                    hotSpots.push({
                        "id": "p"+marker_m.id,
                        "type": marker_m.embed_type,
                        "object": "poi_embed",
                        "transform3d": parseInt(marker_m.transform3d),
                        "tooltip_type": "",
                        "pitch": parseFloat(marker_m.pitch),
                        "yaw": parseFloat(marker_m.yaw),
                        "rotateX": 0,
                        "rotateZ": 0,
                        "size_scale": 1,
                        "cssClass": "hotspot-embed",
                        "createTooltipFunc": hotspot_embed,
                        "createTooltipArgs": marker_m,
                    });
                    if(marker_m.transform3d==1) {
                        var poi_embed_helpers = marker_m.embed_coords.split("|");
                        poi_embed_helpers[0] = poi_embed_helpers[0].split(",");
                        poi_embed_helpers[1] = poi_embed_helpers[1].split(",");
                        poi_embed_helpers[2] = poi_embed_helpers[2].split(",");
                        poi_embed_helpers[3] = poi_embed_helpers[3].split(",");
                        jQuery.each(poi_embed_helpers, function(index_h, poi_embed_helper) {
                            hotSpots.push({
                                "id": "p"+marker_m.id+"_"+(index_h+1),
                                "type": 'pointer',
                                "object": "poi_embed_helper",
                                "transform3d": false,
                                "pitch": parseFloat(poi_embed_helper[0]),
                                "yaw": parseFloat(poi_embed_helper[1]),
                                "size_scale": 1,
                                "rotateX": 0,
                                "rotateZ": 0,
                                "draggable": true,
                                "cssClass": "hotspot-helper",
                                "createTooltipFunc": hotspot_embed_helper,
                                "createTooltipArgs": [marker_m.id,(index_h+1)],
                            });
                        });
                        hotSpots.push({
                            "id": "p"+marker_m.id+"_move",
                            "type": 'pointer',
                            "object": "poi_embed_helper",
                            "transform3d": false,
                            "pitch": parseFloat(marker_m.pitch),
                            "yaw": parseFloat(marker_m.yaw),
                            "size_scale": 1,
                            "rotateX": 0,
                            "rotateZ": 0,
                            "draggable": true,
                            "cssClass": "hotspot-helper",
                            "createTooltipFunc": hotspot_embed_move,
                            "createTooltipArgs": marker_m.id
                        });
                    }
                } else {
                    hotSpots.push({
                        "type": 'poi',
                        "transform3d": false,
                        "pitch": parseFloat(marker_m.pitch),
                        "yaw": parseFloat(marker_m.yaw),
                        "rotateX": parseInt(marker_m.rotateX),
                        "rotateZ": parseInt(marker_m.rotateZ),
                        "size_scale": parseFloat(marker_m.size_scale),
                        "cssClass": "custom-hotspot-content",
                        "createTooltipFunc": hotspot_p,
                        "createTooltipArgs": marker_m,
                    });
                }
            }
        });
        try {
            window.video_viewer.dispose();
        } catch (e) {}
        try {
            window.viewer.destroy();
        } catch (e) {}
        $('#panorama_markers').empty();
        if(array_room.room_type == 'video') {
            $('#panorama_markers').append('<video controls playsinline webkit-playsinline id="video_viewer" class="video-js vjs-default-skin vjs-big-play-centered" style="width: 100%;height: 600px;margin: 0 auto;" muted preload="none" crossorigin="anonymous"><source src="../viewer/videos/'+array_room.panorama_video+'" type="video/mp4"/></video>');
            var container_h = $('#content-wrapper').height() - 280;
            $('#video_viewer').css('height',container_h+'px');
            window.video_viewer = videojs('video_viewer', {
                loop: true,
                autoload: true,
                muted: true,
                controls: true,
                plugins: {
                    pannellum: {
                        "autoLoad": true,
                        "showFullscreenCtrl": false,
                        "showControls": false,
                        "horizonPitch": parseInt(array_room.h_pitch),
                        "horizonRoll": parseInt(array_room.h_roll),
                        "hfov": 100,
                        "minHfov": 70,
                        "maxHfov": 120,
                        "yaw": parseFloat(window.currentYaw),
                        "pitch": parseFloat(window.currentPitch),
                        "haov": parseInt(array_room.haov),
                        "vaov": parseInt(array_room.vaov),
                        "hotSpots": hotSpots,
                        "friction": 1,
                        "strings": {
                            "loadingLabel": window.backend_labels.loading+"...",
                        },
                    }
                }
            });
            window.video_viewer.load();
            window.video_viewer.on('ready', function() {
                window.video_viewer.play();
                window.viewer = window.video_viewer.pnlmViewer;
                window.viewer.on('load',function () {
                    viewer_marker_initialized(id_marker_add,index_marker_add);
                });
            });
        } else {
            if(parseInt(array_room.multires)) {
                var multires_config = JSON.parse(array_room.multires_config);
                window.viewer = pannellum.viewer('panorama_markers', {
                    "type": "multires",
                    "multiRes": multires_config,
                    "backgroundColor": [1,1,1],
                    "autoLoad": true,
                    "showFullscreenCtrl": false,
                    "showControls": false,
                    "multiResMinHfov": true,
                    "horizonPitch": parseInt(array_room.h_pitch),
                    "horizonRoll": parseInt(array_room.h_roll),
                    "hfov": 100,
                    "minHfov": 70,
                    "maxHfov": 120,
                    "yaw": parseFloat(window.currentYaw),
                    "pitch": parseFloat(window.currentPitch),
                    "haov": parseInt(array_room.haov),
                    "vaov": parseInt(array_room.vaov),
                    "friction": 1,
                    "hotSpots": hotSpots,
                    "strings": {
                        "loadingLabel": window.backend_labels.loading+"...",
                    },
                });
                setTimeout(function () {
                    viewer_marker_initialized(id_marker_add,index_marker_add);
                },200);
            } else {
                window.viewer = pannellum.viewer('panorama_markers', {
                    "type": "equirectangular",
                    "panorama": "../viewer/panoramas/"+image,
                    "autoLoad": true,
                    "showFullscreenCtrl": false,
                    "showControls": false,
                    "multiResMinHfov": true,
                    "horizonPitch": parseInt(array_room.h_pitch),
                    "horizonRoll": parseInt(array_room.h_roll),
                    "hfov": 100,
                    "minHfov": 70,
                    "maxHfov": 120,
                    "yaw": parseFloat(window.currentYaw),
                    "pitch": parseFloat(window.currentPitch),
                    "haov": parseInt(array_room.haov),
                    "vaov": parseInt(array_room.vaov),
                    "friction": 1,
                    "hotSpots": hotSpots,
                    "strings": {
                        "loadingLabel": window.backend_labels.loading+"...",
                    },
                });
                window.viewer.on("load",function () {
                    viewer_marker_initialized(id_marker_add,index_marker_add);
                });
            }
            window.viewer.on('animatefinished',function () {
                var yaw = parseFloat(window.viewer.getYaw());
                var pitch = parseFloat(window.viewer.getPitch());
                window.currentYaw = yaw;
                window.currentPitch = pitch;
            });
        }
    }

    function viewer_poi_initialized(id_poi_add,index_poi_add) {
        window.viewer_initialized = true;
        check_plan(window.id_user,'poi');
        if(window.can_create) {
            $('#plan_poi_msg').addClass('d-none');
            $('#btn_add_poi').css({'opacity':1,'pointer-events':'initial'});
        } else {
            $('#plan_poi_msg').removeClass('d-none');
            $('#btn_add_poi').css({'opacity':0.3,'pointer-events':'none'});
        }
        $('#btn_switch_to_marker').css({'opacity':1,'pointer-events':'initial'});
        $('#btn_preview_modal').css({'opacity':1,'pointer-events':'initial'});
        $('.div_panorama_container').append('<i class="fas fa-dot-circle center_helper"></i>');
        if(id_poi_add!=null) {
            move_p(id_poi_add,index_poi_add,id_poi_add);
            $('.center_helper').hide();
        } else {
            $('.center_helper').show();
        }
        var poi_embed_count = $('.poi_embed').length;
        if(poi_embed_count>0) {
            init_poi_embed();
        } else {
            window.sync_poi_embed_enabled = false;
        }
        var marker_embed_count = $('.marker_embed').length;
        if(marker_embed_count>0) {
            init_marker_embed();
        } else {
            window.sync_marker_embed_enabled = false;
        }
        setTimeout(function () {
            try {
                window.viewer.reisze();
            } catch (e) {}
            try {
                window.video_viewer.pnlmViewer.resize();
            } catch (e) {}
        },100);
        $(window).trigger('resize');
    }

    function viewer_marker_initialized(id_marker_add,index_marker_add) {
        window.viewer_initialized = true;
        if(window.rooms_count>1) {
            check_plan(window.id_user,'marker');
            if(window.can_create) {
                $('#plan_marker_msg').addClass('d-none');
                $('#btn_add_marker').css({'opacity':1,'pointer-events':'initial'});
            } else {
                $('#plan_marker_msg').removeClass('d-none');
                $('#btn_add_marker').css({'opacity':0.3,'pointer-events':'none'});
            }
        } else {
            $('#btn_add_marker').css({'opacity':0.3,'pointer-events':'none'});
        }
        $('#btn_switch_to_poi').css({'opacity':1,'pointer-events':'initial'});
        $('#btn_preview_modal').css({'opacity':1,'pointer-events':'initial'});
        $('#panorama_markers').append('<i class="fas fa-dot-circle center_helper"></i>');
        if(id_marker_add!=null && window.wizard_step==-1) {
            move_m(id_marker_add,index_marker_add);
            $('.center_helper').hide();
        } else {
            $('.center_helper').show();
        }
        var poi_embed_count = $('.poi_embed').length;
        if(poi_embed_count>0) {
            init_poi_embed();
        } else {
            window.sync_poi_embed_enabled = false;
        }
        var marker_embed_count = $('.marker_embed').length;
        if(marker_embed_count>0) {
            init_marker_embed();
        } else {
            window.sync_marker_embed_enabled = false;
        }
        setTimeout(function () {
            try {
                window.viewer.reisze();
            } catch (e) {}
            try {
                window.video_viewer.pnlmViewer.resize();
            } catch (e) {}
        },100);
        $(window).trigger('resize');
        if(id_marker_add==null && window.wizard_step!=-1) {
            $('#btn_add_marker').addClass('disabled');
            $('#btn_switch_to_poi').addClass('disabled');
            $('#btn_preview_modal').addClass('disabled');
            Shepherd.activeTour.next();
        } else if(id_marker_add!=null && window.wizard_step!=-1) {
            Shepherd.activeTour.next();
        }
    }

    window.click_edit_marker = function(hotSpotDiv,args) {
        if(!window.is_editing) {
            $('.center_helper').hide();
            $('.custom-hotspot').css('opacity',0.5);
            $('.hotspot_'+args).css('opacity',1);
            jQuery.each(window.markers, function(index_m, marker_m) {
                if(marker_m.what=='marker') {
                    if(parseInt(marker_m.id)==parseInt(args)) {
                        var yaw = parseFloat(marker_m.yaw);
                        var pitch = parseFloat(marker_m.pitch);
                        window.viewer.lookAt(pitch,yaw,100,200,function () {
                            setTimeout(function () {
                                adjust_marker_embed_helpers_all();
                            },200);
                            $('.move_action').attr('onclick','move_m('+args+','+index_m+')');
                            $('.edit_action').attr('onclick','edit_m('+args+','+index_m+')');
                            $('.delete_action').attr('onclick','modal_delete_marker('+args+','+window.id_room_sel+',\''+window.panorama_image+'\')');
                            $('.goto_action').attr('onclick','goto_m('+args+','+index_m+')');
                            var marker_edit_label = '';
                            switch(marker_m.embed_type) {
                                case 'selection':
                                    marker_edit_label += '<i class="far fa-square"></i> <i class="fas fa-caret-right"></i> ';
                                    break;
                                default:
                                    marker_edit_label += '<i class="fas fa-info-circle"></i> <i class="fas fa-caret-right"></i> ';
                                    break;
                            }
                            $('.marker_edit_label').html(marker_edit_label+' '+marker_m.name_room_target);
                            $('#action_box').show();
                        });
                        return;
                    }
                }
            });
        }
    }

    window.click_edit_poi = function(hotSpotDiv,args) {
        if(!window.is_editing) {
            $('.center_helper').hide();
            $('.custom-hotspot-content').css('opacity',0.5);
            $('.hotspot-embed').css('opacity',0.5);
            $('.hotspot_'+args).css('opacity',1);
            $('.edit_action').removeClass('disabled');
            jQuery.each(window.pois, function(index, poi) {
                if(poi.what=='poi') {
                    if(parseInt(poi.id)==parseInt(args)) {
                        var yaw = parseFloat(poi.yaw);
                        var pitch = parseFloat(poi.pitch);
                        window.viewer.lookAt(pitch,yaw,100,200,function () {
                            setTimeout(function () {
                                adjust_poi_embed_helpers_all();
                            },200);
                            $('.move_action').attr('onclick','move_p('+args+','+index+',null)');
                            $('.edit_action').attr('onclick','edit_p('+args+','+index+',null)');
                            $('.duplicate_action').attr('onclick','modal_duplicate_poi('+args+','+window.id_room_sel+')');
                            $('.delete_action').attr('onclick','modal_delete_poi('+args+','+window.id_room_sel+',\''+window.panorama_image+'\')');
                            var poi_edit_label = '';
                            switch(poi.embed_type) {
                                case 'image':
                                case 'video':
                                case 'video_transparent':
                                case 'video_chroma':
                                case 'gallery':
                                case 'link':
                                case 'text':
                                    poi_edit_label += '<i class="fab fa-gg-circle"></i> <i class="fas fa-caret-right"></i> ';
                                    break;
                                case 'selection':
                                    poi_edit_label += '<i class="far fa-square"></i> <i class="fas fa-caret-right"></i> ';
                                    break;
                                default:
                                    poi_edit_label += '<i class="fas fa-info-circle"></i> <i class="fas fa-caret-right"></i> ';
                                    break;
                            }
                            switch(poi.type) {
                                case 'image':
                                    poi_edit_label += '<i class="fas fa-image"></i> '+window.backend_labels.image;
                                    break;
                                case 'gallery':
                                    poi_edit_label += '<i class="fas fa-images"></i> '+window.backend_labels.image_gallery;
                                    break;
                                case 'video':
                                    poi_edit_label += '<i class="fab fa-youtube"></i> '+window.backend_labels.video;
                                    break;
                                case 'audio':
                                    poi_edit_label += '<i class="fas fa-music"></i> '+window.backend_labels.audio;
                                    break;
                                case 'video360':
                                    poi_edit_label += '<i class="fas fa-video"></i> '+window.backend_labels.video360;
                                    break;
                                case 'link':
                                    poi_edit_label += '<i class="fas fa-link"></i> '+window.backend_labels.link;
                                    break;
                                case 'link_ext':
                                    poi_edit_label += '<i class="fas fa-external-link-alt"></i> '+window.backend_labels.link_ext;
                                    break;
                                case 'html':
                                    poi_edit_label += '<i class="fas fa-heading"></i> '+window.backend_labels.html;
                                    break;
                                case 'html_sc':
                                    poi_edit_label += '<i class="fas fa-code"></i> '+window.backend_labels.html_sc;
                                    break;
                                case 'download':
                                    poi_edit_label += '<i class="fas fa-download"></i> '+window.backend_labels.download;
                                    break;
                                case 'form':
                                    poi_edit_label += '<i class="fab fa-wpforms"></i> '+window.backend_labels.form;
                                    break;
                                case 'google_maps':
                                    poi_edit_label += '<i class="fas fa-map"></i> '+window.backend_labels.google_maps;
                                    break;
                                case 'object360':
                                    poi_edit_label += '<i class="fas fa-compact-disc"></i> '+window.backend_labels.object360;
                                    break;
                                case 'object3d':
                                    poi_edit_label += '<i class="fas fa-cube"></i> '+window.backend_labels.object3d;
                                    break;
                                case 'lottie':
                                    poi_edit_label += '<i class="fab fa-deviantart"></i> Lottie';
                                    break;
                                case 'product':
                                    poi_edit_label += '<i class="fas fa-shopping-cart"></i> '+window.backend_labels.product;
                                    break;
                                case 'switch_pano':
                                    poi_edit_label += '<i class="fas fa-sync-alt"></i> '+window.backend_labels.switch_pano;
                                    break;
                                default:
                                    switch(poi.embed_type) {
                                        case 'image':
                                            poi_edit_label = '<i class="fas fa-image"></i> '+window.backend_labels.embed_image;
                                            break;
                                        case 'video':
                                            poi_edit_label = '<i class="fas fa-video"></i> '+window.backend_labels.embed_video;
                                            break;
                                        case 'video_transparent':
                                            poi_edit_label = '<i class="fas fa-video"></i> '+window.backend_labels.embed_video_transparent;
                                            break;
                                        case 'video_chroma':
                                            poi_edit_label = '<i class="fas fa-video"></i> '+window.backend_labels.embed_video_chroma;
                                            break;
                                        case 'gallery':
                                            poi_edit_label = '<i class="fas fa-images"></i> '+window.backend_labels.embed_gallery;
                                            break;
                                        case 'link':
                                            poi_edit_label = '<i class="fas fa-images"></i> '+window.backend_labels.embed_link;
                                            break;
                                        case 'text':
                                            poi_edit_label = '<i class="fas fa-heading"></i> '+window.backend_labels.embed_text;
                                            break;
                                        case 'selection':
                                            poi_edit_label = '<i class="far fa-square"></i> '+window.backend_labels.embed_selection;
                                            break;
                                        default:
                                            poi_edit_label += '<i class="fas fa-ban"></i> '+window.backend_labels.none;
                                            break;
                                    }
                                    break;
                            }
                            $('.poi_edit_label').html(poi_edit_label);
                            $('#action_box').show();
                        });
                        return;
                    }
                }
            });
        }
    }

    window.check_schedule = function () {
        if($('#enable_schedule').is(':checked')) {
            $('#confirm_schedule input').prop("disabled",false);
        } else {
            $('#confirm_schedule input').prop("disabled",true);
        }
        $('#enable_schedule').prop("disabled",false);
    }

    window.show_poi_confirm = function (show) {
        if(show) {
            $('.btn_confirm').show();
        } else {
            $('.btn_confirm').hide();
        }
    }

    window.show_marker_apply_style = function (show) {
        if(show && window.markers[marker_index_edit].embed_type=='') {
            $('.btn_apply_style_all').show();
        } else {
            $('.btn_apply_style_all').hide();
        }
    }

    window.show_poi_apply_style = function (show) {
        if(show && window.pois[poi_index_edit].embed_type=='') {
            $('.btn_apply_style_all').show();
        } else {
            $('.btn_apply_style_all').hide();
        }
    }

    window.change_switch_panorama_default = function() {
        if($('#switch_panorama_default').is(':checked')) {
            $('#frm_sp_edit').addClass('disabled');
            $('#poi_content').parent().parent().addClass('disabled');
        } else {
            $('#frm_sp_edit').removeClass('disabled');
            $('#poi_content').parent().parent().removeClass('disabled');
        }
    }

    window.edit_p = function(id,index,id_poi_add) {
        window.poi_index_edit = index;
        window.poi_id_edit = id;
        show_poi_confirm(true);
        $('.btn_apply_style_all').hide();
        $('#action_box').hide();
        $('.rooms_slider').addClass('disabled');
        $('#btn_add_poi').addClass('disabled');
        $('#btn_switch_to_marker').addClass('disabled');
        $('#btn_preview_modal').addClass('disabled');
        $('#confirm_edit .btn_confirm').attr('onclick','confirm_edit_poi('+id+','+index+')');
        $('#confirm_edit .btn_close').attr('onclick','exit_edit_poi('+id+','+index+')');
        var yaw = parseFloat(window.pois[index].yaw);
        var pitch = parseFloat(window.pois[index].pitch)+20;
        if(window.pois[index].embed_type=='') {
            window.viewer.lookAt(pitch,yaw,100,200);
        }
        $('#id_poi_autoopen').parent().parent().removeClass('col-md-12').addClass('col-md-3');
        $('#div_form_edit').hide();
        $('#frm_edit').hide();
        $('#frm_d_edit').hide();
        $('#frm_v_edit').hide();
        $('#frm_a_edit').hide();
        $('#frm_g_edit').hide();
        $('#frm_j_edit').hide();
        $('#frm_sp_edit').hide();
        $('#poi_content').parent().parent().hide();
        $('#poi_target').parent().hide();
        $('#poi_song_bg_volume').parent().hide();
        $('#poi_title').parent().hide();
        $('#poi_description').parent().hide();
        $('#poi_content_html').parent().hide();
        $('#poi_content_html_sc').parent().hide();
        $('#btn_poi_gallery').parent().hide();
        $('#btn_poi_object360').parent().hide();
        $('#poi_gm_map').parent().hide();
        $('#poi_gm_street').parent().hide();
        $('#poi_content_product_div').hide();
        $('#id_poi_autoopen').parent().hide();
        $('#auto_close').parent().hide();
        $('#view_type').parent().hide();
        $('#box_pos').parent().hide();
        $('#poi_gm_map').val('');
        $('#poi_gm_street').val('');
        $('#poi_content').prop('readonly',false);
        $('#switch_panorama_default').parent().hide();
        window.content_current = window.pois[index].type;
        if(window.pois[index].type=='') {
            select_poi_content_edit('none');
        } else {
            select_poi_content_edit(window.pois[index].type);
        }
        switch(window.pois[index].type) {
            case 'image':
                $('#frm_edit').show();
                $('#poi_content').parent().parent().show();
                $('#poi_title').parent().show();
                $('#poi_description').parent().show();
                $('#id_poi_autoopen').parent().show();
                $('#auto_close').parent().show();
                $('#view_type').parent().show();
                $('#box_pos').parent().show();
                $('#content_label').html(window.backend_labels.content_image);
                $('#poi_content').val(window.pois[index].content);
                $('#poi_title').val(window.pois[index].title);
                $('#poi_description').val(window.pois[index].description);
                break;
            case 'gallery':
                $('#btn_poi_gallery').parent().show();
                $('#id_poi_autoopen').parent().show();
                $('#auto_close').parent().show();
                $('#view_type').parent().show();
                $('#box_pos').parent().show();
                break;
            case 'video':
                $('#frm_v_edit').show();
                $('#poi_content').parent().parent().show();
                $('#poi_title').parent().show();
                $('#poi_description').parent().show();
                $('#id_poi_autoopen').parent().show();
                $('#auto_close').parent().show();
                $('#view_type').parent().show();
                $('#box_pos').parent().show();
                $('#content_label').html(window.backend_labels.content_video);
                $('#poi_content').val(window.pois[index].content);
                $('#poi_title').val(window.pois[index].title);
                $('#poi_description').val(window.pois[index].description);
                break;
            case 'audio':
                $('#frm_a_edit').show();
                $('#poi_content').parent().parent().show();
                $('#id_poi_autoopen').parent().show();
                $('#id_poi_autoopen').parent().parent().removeClass('col-md-3').addClass('col-md-12');
                $('#poi_song_bg_volume').parent().show();
                $('#content_label').html(window.backend_labels.content_audio);
                $('#poi_content').val(window.pois[index].content);
                $('#poi_song_bg_volume').val(parseFloat(window.pois[index].song_bg_volume));
                $('#poi_title').val('');
                $('#poi_description').val('');
                break;
            case 'video360':
                $('#frm_v_edit').show();
                $('#poi_content').parent().parent().show();
                $('#poi_title').parent().show();
                $('#poi_description').parent().show();
                $('#id_poi_autoopen').parent().show();
                $('#auto_close').parent().show();
                $('#view_type').parent().show();
                $('#box_pos').parent().show();
                $('#content_label').html(window.backend_labels.content_video360);
                $('#poi_content').val(window.pois[index].content);
                $('#poi_title').val(window.pois[index].title);
                $('#poi_description').val(window.pois[index].description);
                break;
            case 'link':
                $('#poi_content').parent().parent().show();
                $('#poi_title').parent().show();
                $('#poi_description').parent().show();
                $('#id_poi_autoopen').parent().show();
                $('#auto_close').parent().show();
                $('#view_type').parent().show();
                $('#box_pos').parent().show();
                $('#content_label').html(window.backend_labels.content_link_emb);
                $('#poi_content').val(window.pois[index].content);
                $('#poi_title').val(window.pois[index].title);
                $('#poi_description').val(window.pois[index].description);
                break;
            case 'link_ext':
                $('#poi_content').parent().parent().show();
                $('#poi_target').parent().show();
                $('#id_poi_autoopen').parent().parent().removeClass('col-md-3').addClass('col-md-12');
                $('#content_label').html(window.backend_labels.content_link_ext);
                $('#poi_content').val(window.pois[index].content);
                $("#poi_target option").prop("selected", false);
                $("#poi_target option[id='"+window.pois[index].target+"']").prop("selected", true);
                $('#poi_title').val('');
                $('#poi_description').val('');
                break;
            case 'html':
                $('#poi_title').parent().show();
                $('#poi_description').parent().show();
                $('#id_poi_autoopen').parent().show();
                $('#view_type').parent().show();
                $('#box_pos').parent().show();
                $('#auto_close').parent().show();
                $('#poi_content_html').parent().show();
                $('#poi_content_html').parent().find('.ql-toolbar').remove();
                $('#poi_content_html').html(window.pois[index].content).promise().done(function () {
                    var toolbarOptions = [
                        ['bold', 'italic', 'underline', 'strike'],
                        [{ 'size': ['12px','14px','16px','18px','24px','28px','32px','40px','48px','56px','64px','72px'] }],
                        [{ 'list': 'ordered'}, { 'list': 'bullet' }],
                        [{ 'color': [] }, { 'background': [] }],
                        [{ 'align': [] }],
                        ['clean']
                    ];
                    window.html_editor = new Quill('#poi_content_html', {
                        modules: {
                            toolbar: toolbarOptions
                        },
                        theme: 'snow'
                    });
                });
                $('#poi_title').val(window.pois[index].title);
                $('#poi_description').val(window.pois[index].description);
                break;
            case 'html_sc':
                $('#poi_title').parent().show();
                $('#poi_description').parent().show();
                $('#poi_content_html_sc').parent().show();
                $('#id_poi_autoopen').parent().show();
                $('#auto_close').parent().show();
                $('#view_type').parent().show();
                $('#box_pos').parent().show();
                try {
                    window.poi_content_html_sc.destroy();
                } catch (e) {}
                window.poi_content_html_sc = ace.edit('poi_content_html_sc');
                window.poi_content_html_sc.session.setMode("ace/mode/html");
                window.poi_content_html_sc.setOption('enableLiveAutocompletion',true);
                window.poi_content_html_sc.setValue(window.pois[index].content,-1);
                $('#poi_title').val(window.pois[index].title);
                $('#poi_description').val(window.pois[index].description);
                break;
            case 'download':
                $('#frm_d_edit').show();
                $('#poi_content').parent().parent().show();
                $('#id_poi_autoopen').parent().parent().removeClass('col-md-3').addClass('col-md-12');
                $('#content_label').html(window.backend_labels.content_file);
                $('#poi_content').val(window.pois[index].content);
                $('#poi_title').val('');
                $('#poi_description').val('');
                break;
            case 'form':
                var content = window.pois[index].content;
                try {
                    content = JSON.parse(content);
                    $('#form_title').val(content[0].title);
                    $('#form_button').val(content[0].button);
                    $('#form_response').val(content[0].response);
                    $('#form_description').val(content[0].description);
                    if(content[0]['send_email']) {
                        $('#form_send_email').prop('checked',true);
                    } else {
                        $('#form_send_email').prop('checked',false);
                    }
                    $('#form_email').val(content[0].email);
                    for(var i=1;i<=10;i++) {
                        if(content[i]['enabled']) {
                            $('#form_field_'+i).prop('checked',true);
                        } else {
                            $('#form_field_'+i).prop('checked',false);
                        }
                        if(content[i]['required']) {
                            $('#form_field_required_'+i).prop('checked',true);
                        } else {
                            $('#form_field_required_'+i).prop('checked',false);
                        }
                        $('#form_field_type_'+i+' option[id=\''+content[i]['type']+'\']').prop('selected',true);
                        $('#form_field_label_'+i).val(content[i]['label']);
                    }
                } catch (e) {}
                $('#poi_title').val('');
                $('#poi_description').val('');
                $('#id_poi_autoopen').parent().show();
                $('#auto_close').parent().show();
                $('#view_type').parent().show();
                $('#box_pos').parent().show();
                $('#div_form_edit').show();
                break;
            case 'google_maps':
                $('#poi_title').parent().show();
                $('#poi_description').parent().show();
                $('#id_poi_autoopen').parent().show();
                $('#auto_close').parent().show();
                $('#view_type').parent().show();
                $('#box_pos').parent().show();
                $('#poi_gm_map').parent().show();
                $('#poi_gm_street').parent().show();
                if((window.pois[index].content == null) || (window.pois[index].content.length === 0)) {
                    var gm_map = '';
                    var gm_street = '';
                } else {
                    var gm_map = window.pois[index].content.split('|')[0];
                    var gm_street = window.pois[index].content.split('|')[1];
                }
                $('#poi_gm_map').val(gm_map);
                $('#poi_gm_street').val(gm_street);
                $('#poi_content').val('');
                $('#poi_title').val(window.pois[index].title);
                $('#poi_description').val(window.pois[index].description);
                break;
            case 'object360':
                $('#poi_title').parent().show();
                $('#poi_description').parent().show();
                $('#btn_poi_object360').parent().show();
                $('#id_poi_autoopen').parent().show();
                $('#auto_close').parent().show();
                $('#view_type').parent().show();
                $('#box_pos').parent().show();
                $('#poi_title').val(window.pois[index].title);
                $('#poi_description').val(window.pois[index].description);
                break;
            case 'object3d':
                $('#frm_g_edit').show();
                $('#poi_content').parent().parent().show();
                $('#poi_title').parent().show();
                $('#poi_description').parent().show();
                $('#id_poi_autoopen').parent().show();
                $('#auto_close').parent().show();
                $('#view_type').parent().show();
                $('#box_pos').parent().show();
                $("#params_ar option").prop("selected", false);
                $("#params_ar option[id='"+window.pois[index].params+"']").prop("selected", true);
                $('#content_label').html(window.backend_labels.content_object3d);
                $('#poi_content').val(window.pois[index].content);
                $('#poi_title').val(window.pois[index].title);
                $('#poi_description').val(window.pois[index].description);
                break;
            case 'lottie':
                $('#frm_j_edit').show();
                $('#poi_content').parent().parent().show();
                $('#poi_title').parent().show();
                $('#poi_description').parent().show();
                $('#id_poi_autoopen').parent().show();
                $('#auto_close').parent().show();
                $('#view_type').parent().show();
                $('#box_pos').parent().show();
                $('#content_label').html(window.backend_labels.content_lottie);
                $('#poi_content').val(window.pois[index].content);
                $('#poi_title').val(window.pois[index].title);
                $('#poi_description').val(window.pois[index].description);
                break;
            case 'product':
                $('#poi_content_product_div').show();
                $('#poi_title').parent().show();
                $('#poi_description').parent().show();
                $('#id_poi_autoopen').parent().show();
                $('#auto_close').parent().show();
                $('#view_type').parent().show();
                $('#box_pos').parent().show();
                $('#poi_title').val(window.pois[index].title);
                $('#poi_description').val(window.pois[index].description);
                if(window.pois[index].content!='') {
                    $("#poi_content_product option").prop("selected", false);
                    $("#poi_content_product option[id='"+window.pois[index].content+"']").prop("selected", true);
                    $('#poi_content_product').selectpicker('refresh');
                    $('#poi_content_product').selectpicker('val', window.pois[index].content);
                } else {
                    $('#poi_content_product').selectpicker('refresh');
                }
                break;
            case 'switch_pano':
                $('#content_label').html(window.backend_labels.content_panorama_image);
                $('#switch_panorama_id').val(window.pois[index].content);
                $('#poi_content').val(window.pois[index].switch_panorama_image);
                $('#poi_content').prop('readonly',true);
                $('#poi_content').parent().parent().show();
                $('#frm_sp_edit').show();
                $('#switch_panorama_default').parent().show();
                if(window.pois[index].content!='' && parseInt(window.pois[index].content)==0) {
                    $('#switch_panorama_default').prop('checked',true);
                    $('#frm_sp_edit').addClass('disabled');
                    $('#poi_content').parent().parent().addClass('disabled');
                } else {
                    $('#switch_panorama_default').prop('checked',false);
                    $('#frm_sp_edit').removeClass('disabled');
                    $('#poi_content').parent().parent().removeClass('disabled');
                }
                break;
        }
        if(window.id_poi_autoopen==id) {
            $('#id_poi_autoopen').prop('checked',true);
        } else {
            $('#id_poi_autoopen').prop('checked',false);
        }
        $("#view_type option").prop("selected", false);
        $("#view_type option[id='"+window.pois[index].view_type+"']").prop("selected", true);
        $("#box_pos option").prop("selected", false);
        $("#box_pos option[id='"+window.pois[index].box_pos+"']").prop("selected", true);
        if(window.pois[index].view_type==0) {
            $('#box_pos').prop('disabled',true);
        } else {
            $('#box_pos').prop('disabled',false);
        }
        $('#auto_close').val(window.pois[index].auto_close);
        $('#frm_edit_e').hide();
        $('#frm_v_edit_e').hide();
        $('#frm_v_edit_e_s').hide();
        $('#poi_embed_content').show();
        $('#poi_embed_content').parent().hide();
        $('#poi_embed_content_html').hide();
        $('#poi_style').parent().parent().parent().show();
        $('#poi_color').parent().parent().show();
        $('#poi_border_px').parent().parent().hide();
        $('#poi_background').parent().parent().show();
        $('#poi_label').parent().parent().show();
        $('#btn_poi_embed_gallery').parent().parent().parent().hide();
        $('#poi_css_class').parent().parent().removeClass('col-md-6').addClass('col-md-4');
        $('#poi_embed_content_html').parent().find('.ql-toolbar').remove();
        $('#btn_background_removal').parent().parent().hide();
        $('#embed_video_autoplay').parent().parent().removeClass('col-md-4').addClass('col-md-6');
        $('#embed_video_muted').parent().parent().removeClass('col-md-4').addClass('col-md-6');
        $('#poi_color_label').html(window.backend_labels.color);
        window.embed_type_current = window.pois[index].embed_type;
        switch(window.pois[index].embed_type) {
            case 'image':
                select_poi_style_edit('embed_image');
                $('#frm_edit_e').show();
                $('#poi_embed_content').parent().show();
                $('#embed_content_label').html(window.backend_labels.content_image_embed);
                $('#poi_embed_content').val(window.pois[index].embed_content);
                $('#poi_style').parent().parent().parent().hide();
                $('#poi_color').parent().parent().hide();
                $('#poi_background').parent().parent().hide();
                $('#poi_label').parent().parent().hide();
                $('#poi_css_class').parent().parent().removeClass('col-md-4').addClass('col-md-6');
                break;
            case 'gallery':
                select_poi_style_edit('embed_gallery');
                $('#embed_gallery_autoplay').val(parseInt(window.pois[index].embed_gallery_autoplay));
                $('#btn_poi_embed_gallery').parent().parent().parent().show();
                $('#poi_style').parent().parent().parent().hide();
                $('#poi_color').parent().parent().hide();
                $('#poi_background').parent().parent().hide();
                $('#poi_label').parent().parent().hide();
                $('#poi_css_class').parent().parent().removeClass('col-md-4').addClass('col-md-6');
                break;
            case 'video':
                select_poi_style_edit('embed_video');
                if(parseInt(window.pois[index].embed_video_autoplay)==1) {
                    $('#embed_video_autoplay').prop('checked',true);
                } else {
                    $('#embed_video_autoplay').prop('checked',false);
                }
                if(parseInt(window.pois[index].embed_video_muted)==1) {
                    $('#embed_video_muted').prop('checked',true);
                } else {
                    $('#embed_video_muted').prop('checked',false);
                }
                $('#label_mp4').show();
                $('#label_webm_mov').hide();
                window.video_ext_sel = 'mp4';
                $('.ml_btn').attr('onclick',"open_modal_media_library('videos','poi_embed_content');return false;");
                $('#frm_v_edit_e').attr('action','ajax/upload_content_video.php?e=mp4');
                $('#frm_v_edit_e').show();
                $('#frm_v_edit_e_s').show();
                $('#poi_embed_content').parent().show();
                $('#embed_content_label').html(window.backend_labels.content_video_embed);
                $('#poi_embed_content').val(window.pois[index].embed_content);
                $('#poi_style').parent().parent().parent().hide();
                $('#poi_color').parent().parent().hide();
                $('#poi_background').parent().parent().hide();
                $('#poi_label').parent().parent().hide();
                $('#poi_css_class').parent().parent().removeClass('col-md-4').addClass('col-md-6');
                break;
            case 'video_chroma':
                select_poi_style_edit('embed_video_chroma');
                if(parseInt(window.pois[index].embed_video_autoplay)==1) {
                    $('#embed_video_autoplay').prop('checked',true);
                } else {
                    $('#embed_video_autoplay').prop('checked',false);
                }
                if(parseInt(window.pois[index].embed_video_muted)==1) {
                    $('#embed_video_muted').prop('checked',true);
                } else {
                    $('#embed_video_muted').prop('checked',false);
                }
                $('#label_mp4').show();
                $('#label_webm_mov').hide();
                window.video_ext_sel = 'mp4';
                $('.ml_btn').attr('onclick',"open_modal_media_library('videos','poi_embed_content');return false;");
                $('#frm_v_edit_e').attr('action','ajax/upload_content_video.php?e=mp4');
                $('#frm_v_edit_e').show();
                $('#frm_v_edit_e_s').show();
                $('#poi_embed_content').parent().show();
                $('#embed_content_label').html(window.backend_labels.content_video_embed_chroma);
                $('#poi_embed_content').val(window.pois[index].embed_content);
                var params = window.pois[index].params;
                if(params=='') { params = '0,255,0,0'; }
                var array_params = params.split(",");
                var bg_color = 'rgb('+array_params[0]+','+array_params[1]+','+array_params[2]+')';
                $('#chroma_color').val(bg_color);
                $("#chroma_color").spectrum("set", bg_color);
                $('#chroma_tolerance').val(array_params[3]);
                $('#poi_style').parent().parent().parent().hide();
                $('#poi_color').parent().parent().hide();
                $('#poi_background').parent().parent().hide();
                $('#poi_label').parent().parent().hide();
                $('#poi_css_class').parent().parent().removeClass('col-md-4').addClass('col-md-6');
                $('#btn_background_removal').parent().parent().show();
                $('#embed_video_autoplay').parent().parent().removeClass('col-md-6').addClass('col-md-4');
                $('#embed_video_muted').parent().parent().removeClass('col-md-6').addClass('col-md-4');
                break;
            case 'video_transparent':
                select_poi_style_edit('embed_video_transparent');
                if(parseInt(window.pois[index].embed_video_autoplay)==1) {
                    $('#embed_video_autoplay').prop('checked',true);
                } else {
                    $('#embed_video_autoplay').prop('checked',false);
                }
                if(parseInt(window.pois[index].embed_video_muted)==1) {
                    $('#embed_video_muted').prop('checked',true);
                } else {
                    $('#embed_video_muted').prop('checked',false);
                }
                $('#label_mp4').hide();
                $('#label_webm_mov').show();
                window.video_ext_sel = 'webm_mov';
                $('.ml_btn').attr('onclick',"open_modal_media_library('videos_transparent','poi_embed_content');return false;");
                $('#frm_v_edit_e').attr('action','ajax/upload_content_video.php?e=webm_mov');
                $('#frm_v_edit_e').show();
                $('#frm_v_edit_e_s').show();
                $('#poi_embed_content').parent().show();
                $('#embed_content_label').html(window.backend_labels.content_video_embed_transparent);
                $('#poi_embed_content').val(window.pois[index].embed_content);
                $('#poi_style').parent().parent().parent().hide();
                $('#poi_color').parent().parent().hide();
                $('#poi_background').parent().parent().hide();
                $('#poi_label').parent().parent().hide();
                $('#poi_css_class').parent().parent().removeClass('col-md-4').addClass('col-md-6');
                break;
            case 'selection':
                select_poi_style_edit('embed_selection');
                $('#frm_edit_e').hide();
                $('#poi_embed_content').parent().hide();
                $('#poi_style').parent().parent().parent().hide();
                $('#poi_color').parent().parent().show();
                $('#poi_background').parent().parent().show();
                $('#poi_border_px').parent().parent().show();
                $('#poi_border_px').val(window.pois[index].embed_content.replace('border-width:','').replace('px;',''));
                $('#poi_label').parent().parent().hide();
                $('#poi_css_class').parent().parent().removeClass('col-md-4').addClass('col-md-6');
                $('#poi_color_label').html(window.backend_labels.border_color);
                break;
            case 'link':
                select_poi_style_edit('embed_link');
                $('#frm_edit_e').hide();
                $('#poi_embed_content').parent().show();
                $('#embed_content_label').html(window.backend_labels.content_link_emb);
                $('#poi_embed_content').val(window.pois[index].embed_content);
                $('#poi_style').parent().parent().parent().hide();
                $('#poi_color').parent().parent().hide();
                $('#poi_background').parent().parent().hide();
                $('#poi_label').parent().parent().hide();
                $('#poi_css_class').parent().parent().removeClass('col-md-4').addClass('col-md-6');
                break;
            case 'text':
                select_poi_style_edit('embed_text');
                $('#frm_edit_e').hide();
                $('#poi_embed_content').parent().show();
                $('#embed_content_label').html(window.backend_labels.content_text_emb);
                $('#poi_embed_content').hide();
                $('#poi_embed_content_html').show();
                $('#poi_embed_content_html').html(window.pois[index].embed_content.split(' border-width:')[0]).promise().done(function () {
                    var toolbarOptions = [
                        ['bold', 'italic', 'underline', 'strike'],
                        [{ 'size': ['12px','14px','16px','18px','24px','28px','32px','40px','48px','56px','64px','72px'] }],
                        [{ 'list': 'ordered'}, { 'list': 'bullet' }],
                        [{ 'color': [] }, { 'background': [] }],
                        [{ 'align': [] }],
                        ['clean']
                    ];
                    window.poi_embed_content_html_editor = new Quill('#poi_embed_content_html', {
                        modules: {
                            toolbar: toolbarOptions
                        },
                        theme: 'snow'
                    });
                    window.poi_embed_content_html_editor.on('text-change', function(delta, oldDelta, source) {
                        window.pois[index].embed_content = window.poi_embed_content_html_editor.root.innerHTML;
                        render_poi(id,index);
                    });
                });
                $('#poi_style').parent().parent().parent().hide();
                $('#poi_color').parent().parent().show();
                $('#poi_background').parent().parent().show();
                $('#poi_border_px').parent().parent().show();
                var border_width = window.pois[index].embed_content.split(' border-width:')[1].replace('px;','');
                if(border_width=='') border_width=0;
                $('#poi_border_px').val(border_width);
                $('#poi_label').parent().parent().hide();
                $('#poi_css_class').parent().parent().removeClass('col-md-4').addClass('col-md-6');
                $('#poi_color_label').html(window.backend_labels.border_color);
                break;
            default:
                select_poi_style_edit('icon');
                break;
        }
        $('#poi_color').val(window.pois[index].color);
        $("#poi_color").spectrum("set", window.pois[index].color);
        $('#poi_background').val(window.pois[index].background);
        $("#poi_background").spectrum("set", window.pois[index].background);
        $('#poi_css_class').val(window.pois[index].css_class);
        $('#poi_icon_preview')[0].className = window.pois[index].icon;
        $('#poi_icon').val(window.pois[index].icon);
        $('#poi_label').val(window.pois[index].label);
        $("#poi_style option").prop("selected", false);
        $("#poi_style option[id='"+window.pois[index].style+"']").prop("selected", true);
        $("#poi_animation option").prop("selected", false);
        $("#poi_animation option[id='"+window.pois[index].animation+"']").prop("selected", true);
        if((window.pois[index].style==0) || (window.pois[index].style==1)) {
            $("#poi_label").prop('disabled',true);
        }
        $("#tooltip_type").prop("selected", false);
        $("#tooltip_type option[id='"+window.pois[index].tooltip_type+"']").prop("selected", true);
        if(window.pois[index].tooltip_type=='none') {
            $('#tooltip_text_html').addClass('disabled');
        } else {
            $('#tooltip_text_html').removeClass('disabled');
        }
        $('#tooltip_text_html').parent().find('.ql-toolbar').remove();
        $('#tooltip_text_html').html(window.pois[index].tooltip_text).promise().done(function () {
            var toolbarOptions = [
                ['bold', 'italic', 'underline', 'strike'],
                [{ 'list': 'ordered'}, { 'list': 'bullet' }],
                [{ 'color': [] }, { 'background': [] }],
                [{ 'align': [] }],
                ['clean']
            ];
            window.tooltip_text_editor = new Quill('#tooltip_text_html', {
                modules: {
                    toolbar: toolbarOptions
                },
                theme: 'snow'
            });
        });
        $('#poi_library_icon').val(window.pois[index].id_icon_library);
        if(window.pois[index].style==1) {
            var image = window.pois[index].img_icon_library;
            var ext = image.split('.').pop().toLowerCase();
            if(ext=='json') {
                var html_image = '<div id="lottie_preview_'+window.poi_id_edit+'" style="height:30px;width:30px;vertical-align:middle"></div>';
                $('#poi_library_icon_preview_l').html(html_image).promise().done(function () {
                    $('#poi_library_icon_preview_l').css('display','inline-block');
                    bodymovin.loadAnimation({
                        container: document.getElementById('lottie_preview_'+window.poi_id_edit),
                        renderer: 'svg',
                        loop: true,
                        autoplay: true,
                        path: '../viewer/icons/'+image,
                        rendererSettings: {
                            progressiveLoad: true,
                        }
                    });
                });
                $('#poi_library_icon_preview').hide();
            } else {
                $('#poi_library_icon_preview').attr('src','../viewer/icons/'+image);
                $('#poi_library_icon_preview').show();
                $('#poi_library_icon_preview_l').hide();
            }
        }
        var schedule = window.pois[index].schedule;
        if(schedule!='' && schedule!=null) {
            schedule = JSON.parse(schedule);
            $('#enable_schedule').prop('checked',true);
            $('#date_from').val(schedule.from_date);
            $('#date_to').val(schedule.to_date);
            $('#hour_from').val(schedule.from_hour);
            $('#hour_to').val(schedule.to_hour);
            var days = schedule.days;
            var days_array = days.split(",");
            if(days_array[0]==1) $("#days_1").prop('checked',true); else $("#days_1").prop('checked',false);
            if(days_array[1]==1) $("#days_2").prop('checked',true); else $("#days_2").prop('checked',false);
            if(days_array[2]==1) $("#days_3").prop('checked',true); else $("#days_3").prop('checked',false);
            if(days_array[3]==1) $("#days_4").prop('checked',true); else $("#days_4").prop('checked',false);
            if(days_array[4]==1) $("#days_5").prop('checked',true); else $("#days_5").prop('checked',false);
            if(days_array[5]==1) $("#days_6").prop('checked',true); else $("#days_6").prop('checked',false);
            if(days_array[6]==1) $("#days_7").prop('checked',true); else $("#days_7").prop('checked',false);
            $('#confirm_schedule input').prop("disabled",false);
        } else {
            $('#enable_schedule').prop('checked',false);
            $('#date_from').val('');
            $('#date_to').val('');
            $('#hour_from').val('');
            $('#hour_to').val('');
            $("#days_1").prop('checked',false);
            $("#days_2").prop('checked',false);
            $("#days_3").prop('checked',false);
            $("#days_4").prop('checked',false);
            $("#days_5").prop('checked',false);
            $("#days_6").prop('checked',false);
            $("#days_7").prop('checked',false);
            $('#confirm_schedule input').prop("disabled",true);
        }
        $('#enable_schedule').prop("disabled",false);
        if(window.pois[index].embed_type=='') {
            change_poi_style();
        }
        if(window.pois[index].type=='') {
            $('#edit-tab a[href="#pills-edit"]').addClass('disabled');
        } else {
            $('#edit-tab a[href="#pills-edit"]').removeClass('disabled');
        }
        if(id_poi_add!=null && window.pois[index].embed_type!='') {
            $('#edit-tab a[href="#pills-style"]').tab('show');
            show_poi_apply_style(true);
        } else {
            if(window.pois[index].type=='') {
                $('#edit-tab a[href="#pills-style"]').tab('show');
                show_poi_apply_style(true);
            } else {
                $('#edit-tab a[href="#pills-edit"]').tab('show');
            }
        }
        $('#confirm_edit').show();
        maximize_box_edit();
        window.is_editing = true;
    }

    window.open_background_removal = function() {
        $('#confirm_edit').hide();
        $('#confirm_background_removal').show();
        $('.poi_embed_'+poi_id_edit).css('pointer-events','none');
        window.video_chroma.play();
    }

    window.close_background_removal = function() {
        $('#confirm_edit').show();
        $('#confirm_background_removal').hide();
        var params = window.pois_initial[poi_index_edit].params;
        if(params=='') { params = '0,255,0,0'; }
        var array_params = params.split(",");
        var bg_color = 'rgb('+array_params[0]+','+array_params[1]+','+array_params[2]+')';
        $('#chroma_color').val(bg_color);
        $("#chroma_color").spectrum("set", bg_color);
        $('#chroma_tolerance').val(array_params[3]);
        remove_background_video_chroma(video_chroma,ctx_chroma_tmp,ctx_chroma,width_chroma,height_chroma,null,true);
        $('.poi_embed_'+poi_id_edit).css('pointer-events','initial');
        window.video_chroma.pause();
        window.video_chroma.currentTime=0;
    }

    window.confirm_background_removal = function() {
        $('#confirm_edit').show();
        $('#confirm_background_removal').hide();
        $('.poi_embed_'+poi_id_edit).css('pointer-events','initial');
        window.video_chroma.pause();
        window.video_chroma.currentTime=0;
    }

    window.change_poi_border_px = function () {
        switch(window.pois[window.poi_index_edit].embed_type) {
            case 'selection':
                window.pois[window.poi_index_edit].embed_content = 'border-width:'+$('#poi_border_px').val()+'px;';
                break;
            case 'text':
                window.pois[window.poi_index_edit].embed_content = window.poi_embed_content_html_editor.root.innerHTML+' border-width:'+$('#poi_border_px').val()+'px;';
                break;
        }
        render_poi(window.poi_id_edit,window.poi_index_edit);
    }

    window.change_marker_border_px = function () {
        switch(window.markers[window.marker_index_edit].embed_type) {
            case 'selection':
                window.markers[window.marker_index_edit].embed_content = 'border-width:'+$('#marker_border_px').val()+'px;';
                break;
        }
        render_marker(window.marker_id_edit,window.marker_index_edit);
    }

    window.edit_poi_gallery = function() {
        $("#modal_images_gallery .modal-body").html('').promise().done(function () {
            $("#modal_images_gallery .modal-body").load('poi_gallery.php?id_poi='+window.poi_id_edit);
            $('#modal_images_gallery').modal('show');
        });
    }

    window.edit_poi_embed_gallery = function() {
        $("#modal_images_gallery .modal-body").html('').promise().done(function () {
            $("#modal_images_gallery .modal-body").load('poi_embed_gallery.php?id_poi='+window.poi_id_edit);
            $('#modal_images_gallery').modal('show');
        });
    }

    window.edit_poi_object360 = function() {
        $("#modal_images_gallery .modal-body").html('').promise().done(function () {
            $("#modal_images_gallery .modal-body").load('poi_object360.php?id_poi='+window.poi_id_edit);
            $('#modal_images_gallery').modal('show');
        });
    }

    window.edit_m = function(id,index) {
        window.marker_index_edit = index;
        window.marker_id_edit = id;
        $('.btn_apply_style_all').hide();
        $('#action_box').hide();
        $('.rooms_slider').addClass('disabled');
        $('#btn_add_marker').addClass('disabled');
        $('#btn_switch_to_poi').addClass('disabled');
        $('#btn_preview_modal').addClass('disabled');
        $('#confirm_edit .btn_confirm').addClass("disabled");
        $('#confirm_edit .btn_confirm').attr('onclick','confirm_edit_marker('+id+','+index+')');
        $('#confirm_edit .btn_close').attr('onclick','exit_edit_marker('+id+','+index+')');
        var yaw = parseFloat(window.markers[index].yaw);
        var pitch = parseFloat(window.markers[index].pitch)+20;
        if(window.markers[index].embed_type=='') {
            window.viewer.lookAt(pitch,yaw,100,200);
        }
        var yaw_m = window.markers[index].yaw_room_target;
        var pitch_m = window.markers[index].pitch_room_target;
        if((yaw_m=='') && (pitch_m=='')) {
            $('#override_pos_edit').prop('checked',false);
        } else {
            $('#override_pos_edit').prop('checked',true);
        }
        get_option_rooms_target('room_target',window.id_room_sel,window.markers[index].id_room_target,id,index);
        window.embed_type_current = window.markers[index].embed_type;
        switch(window.markers[index].embed_type) {
            case 'selection':
                select_marker_style_edit('embed_selection');
                $('#marker_style').parent().parent().parent().hide();
                $('#marker_color').parent().parent().show();
                $('#marker_background').parent().parent().show();
                $('#marker_border_px').parent().parent().show();
                $('#marker_border_px').val(window.markers[index].embed_content.replace('border-width:','').replace('px;',''));
                $('#marker_css_class').parent().parent().removeClass('col-md-4').addClass('col-md-6');
                $('#marker_color_label').html(window.backend_labels.border_color);
                break;
            default:
                select_marker_style_edit('icon');
                $('#marker_style').parent().parent().parent().show();
                $('#marker_color').parent().parent().show();
                $('#marker_background').parent().parent().show();
                $('#marker_border_px').parent().parent().hide();
                $('#marker_border_px').val('');
                $('#marker_css_class').parent().parent().removeClass('col-md-6').addClass('col-md-4');
                $('#marker_color_label').html(window.backend_labels.color);
                break;
        }
        $("#lookat").prop("selected", false);
        $("#lookat option[id='"+window.markers[index].lookat+"']").prop("selected", true);
        $('#marker_color').val(window.markers[index].color);
        $("#marker_color").spectrum("set", window.markers[index].color);
        $('#marker_background').val(window.markers[index].background);
        $("#marker_background").spectrum("set", window.markers[index].background);
        $('#marker_css_class').val(window.markers[index].css_class);
        $('#marker_icon_preview')[0].className = window.markers[index].icon;
        $('#marker_icon').val(window.markers[index].icon);
        $("#marker_style").prop("selected", false);
        $("#marker_style option[id='"+window.markers[index].show_room+"']").prop("selected", true);
        $("#marker_animation option").prop("selected", false);
        $("#marker_animation option[id='"+window.markers[index].animation+"']").prop("selected", true);
        $("#tooltip_type").prop("selected", false);
        $("#tooltip_type option[id='"+window.markers[index].tooltip_type+"']").prop("selected", true);
        $("#tooltip_text").val(window.markers[index].tooltip_text);
        if(window.markers[index].tooltip_type=='none') {
            $("#tooltip_text").prop('disabled',true);
        }
        $('#marker_library_icon').val(window.markers[index].id_icon_library);
        if(window.markers[index].show_room==4) {
            var image = window.markers[index].img_icon_library;
            var ext = image.split('.').pop().toLowerCase();
            if(ext=='json') {
                var html_image = '<div id="lottie_preview_'+window.marker_id_edit+'" style="height:30px;width:30px;vertical-align:middle"></div>';
                $('#marker_library_icon_preview_l').html(html_image).promise().done(function () {
                    $('#marker_library_icon_preview_l').css('display','inline-block');
                    bodymovin.loadAnimation({
                        container: document.getElementById('lottie_preview_'+window.marker_id_edit),
                        renderer: 'svg',
                        loop: true,
                        autoplay: true,
                        path: '../viewer/icons/'+image,
                        rendererSettings: {
                            progressiveLoad: true,
                        }
                    });
                });
                $('#marker_library_icon_preview').hide();
            } else {
                $('#marker_library_icon_preview').attr('src','../viewer/icons/'+image);
                $('#marker_library_icon_preview').show();
                $('#marker_library_icon_preview_l').hide();
            }
        }
        if(window.markers[index].embed_type=='') {
            change_marker_style();
        }
        $('#edit-tab a[href="#pills-edit"]').tab('show');
        $('#confirm_edit').show();
        window.is_editing = true;
    }

    window.goto_m = function(id,index) {
        var id_room_target = window.markers[index].id_room_target;
        window.is_editing = false;
        $('#action_box').hide();
        var id = $('.room_'+id_room_target).attr('data-id');
        var image = $('.room_'+id_room_target).attr('data-image');
        select_room_marker(id,image,null);
    }

    window.move_m = function(id,index) {
        $('.center_helper').hide();
        $('#action_box').hide();
        $('.rooms_slider').addClass('disabled');
        $('#btn_add_marker').addClass('disabled');
        $('#btn_switch_to_poi').addClass('disabled');
        $('#btn_preview_modal').addClass('disabled');
        window.is_editing = true;
        if(window.markers[index].embed_type!='') {
            window.is_editing = true;
            $('#msg_drag_marker').hide();
            $('#msg_drag_embed').show();
            $('#marker_embded_move_'+id).css({'opacity':1,'pointer-events':'initial'});
            $('#marker_embded_helper_'+id+'_1').css({'opacity':1,'pointer-events':'initial'});
            $('#marker_embded_helper_'+id+'_2').css({'opacity':1,'pointer-events':'initial'});
            $('#marker_embded_helper_'+id+'_3').css({'opacity':1,'pointer-events':'initial'});
            $('#marker_embded_helper_'+id+'_4').css({'opacity':1,'pointer-events':'initial'});
            $('#rotateX').parent().hide();
            $('#size_scale').parent().hide();
        } else {
            $('#msg_drag_marker').show();
            $('#msg_drag_embed').hide();
            $('#rotateX').parent().show();
            $('#size_scale').parent().show();
            $('#rotateX').val(window.markers[index].rotateX);
            $('#rotateZ').val(window.markers[index].rotateZ);
            $('#size_scale').val(window.markers[index].size_scale);
            $('#perspective_values').html('('+window.markers[index].rotateX+','+window.markers[index].rotateZ+')');
            $('#size_values').html('('+window.markers[index].size_scale+')');
            $('#rotateX').attr('oninput','adjust_perspective_m('+id+','+index+')');
            $('#rotateZ').attr('oninput','adjust_perspective_m('+id+','+index+')');
            $('#size_scale').attr('oninput','adjust_size_scale_m('+id+','+index+')');
            window.viewer.removeHotSpot(id.toString());
            window.viewer.addHotSpot({
                "id": window.markers[index].id,
                "type": 'marker',
                "transform3d": false,
                "pitch": parseFloat(window.markers[index].pitch),
                "yaw": parseFloat(window.markers[index].yaw),
                "draggable": true,
                "rotateX": parseInt(window.markers[index].rotateX),
                "rotateZ": parseInt(window.markers[index].rotateZ),
                "size_scale": parseFloat(window.markers[index].size_scale),
                "cssClass": "custom-hotspot",
                "createTooltipFunc": hotspot_m,
                "createTooltipArgs": window.markers[index],
                "dragHandlerFunc": drag_marker_move,
                "dragHandlerArgs": index
            });
            setTimeout(function () {
                window.viewer.resize();
                $('.hotspot_'+id).addClass('grabbable');
            },100);
        }
        $('#confirm_move .btn_confirm').attr('onclick','confirm_move_marker('+id+','+index+')');
        $('#confirm_move .btn_close').attr('onclick','exit_move_marker('+id+','+index+')');
        $('#confirm_move').show();
    }

    window.move_p = function(id,index,index_poi_add) {
        window.poi_index_edit = index;
        window.poi_id_edit = id;
        $('.center_helper').hide();
        $('#action_box').hide();
        $('.rooms_slider').addClass('disabled');
        $('#btn_add_poi').addClass('disabled');
        $('#btn_switch_to_marker').addClass('disabled');
        $('#btn_preview_modal').addClass('disabled');
        show_poi_confirm(true);
        window.is_editing = true;
        $('#transform3d').parent().hide();
        if(window.pois[index].transform3d==1) {
            $('#transform3d').prop('checked',true);
        } else {
            $('#transform3d').prop('checked',false);
        }
        switch(window.pois[index].embed_type) {
            case 'image':
            case 'video':
                if(index_poi_add==null) {
                    $('#transform3d').parent().show();
                }
                break;
        }
        if(window.pois[index].embed_type!='' && window.pois[index].transform3d==1) {
            $('#msg_drag_poi').hide();
            $('#msg_drag_embed').show();
            window.viewer.removeHotSpot("p"+id.toString());
            window.viewer.removeHotSpot("p"+id.toString()+"_1");
            window.viewer.removeHotSpot("p"+id.toString()+"_2");
            window.viewer.removeHotSpot("p"+id.toString()+"_3");
            window.viewer.removeHotSpot("p"+id.toString()+"_4");
            window.viewer.removeHotSpot("p"+id.toString()+"_move");
            window.viewer.addHotSpot({
                "id": "p"+window.pois[index].id,
                "type": window.pois[index].embed_type,
                "object": "poi_embed",
                "transform3d": parseInt(window.pois[index].transform3d),
                "tooltip_type": "",
                "pitch": parseFloat(window.pois[index].pitch),
                "yaw": parseFloat(window.pois[index].yaw),
                "rotateX": 0,
                "rotateZ": 0,
                "size_scale": 1,
                "cssClass": "hotspot-embed",
                "createTooltipFunc": hotspot_embed,
                "createTooltipArgs": window.pois[index],
                "clickHandlerFunc": click_edit_poi,
                "clickHandlerArgs": window.pois[index].id
            });
            var poi_embed_helpers = window.pois[index].embed_coords.split("|");
            poi_embed_helpers[0] = poi_embed_helpers[0].split(",");
            poi_embed_helpers[1] = poi_embed_helpers[1].split(",");
            poi_embed_helpers[2] = poi_embed_helpers[2].split(",");
            poi_embed_helpers[3] = poi_embed_helpers[3].split(",");
            jQuery.each(poi_embed_helpers, function(index_h, poi_embed_helper) {
                window.viewer.addHotSpot({
                    "id": "p"+window.pois[index].id+"_"+(index_h+1),
                    "type": 'pointer',
                    "object": "poi_embed_helper",
                    "transform3d": false,
                    "pitch": parseFloat(poi_embed_helper[0]),
                    "yaw": parseFloat(poi_embed_helper[1]),
                    "size_scale": 1,
                    "rotateX": 0,
                    "rotateZ": 0,
                    "draggable": true,
                    "cssClass": "hotspot-helper",
                    "createTooltipFunc": hotspot_embed_helper,
                    "createTooltipArgs": [window.pois[index].id,(index_h+1)],
                    "dragHandlerFunc": drag_embed_helper,
                    "dragHandlerArgs": [window.pois[index],index_h]
                });
            });
            window.viewer.addHotSpot({
                "id": "p"+window.pois[index].id+"_move",
                "type": 'pointer',
                "object": "poi_embed_helper",
                "transform3d": false,
                "pitch": parseFloat(window.pois[index].pitch),
                "yaw": parseFloat(window.pois[index].yaw),
                "size_scale": 1,
                "rotateX": 0,
                "rotateZ": 0,
                "draggable": true,
                "cssClass": "hotspot-helper",
                "createTooltipFunc": hotspot_embed_move,
                "createTooltipArgs": window.pois[index].id,
                "dragHandlerFunc": drag_embed_move,
                "dragHandlerArgs": [window.pois[index].id,index]
            });
            init_poi_embed();
            $('#poi_embded_move_'+id).css({'opacity':1,'pointer-events':'initial'});
            $('#poi_embded_helper_'+id+'_1').css({'opacity':1,'pointer-events':'initial'});
            $('#poi_embded_helper_'+id+'_2').css({'opacity':1,'pointer-events':'initial'});
            $('#poi_embded_helper_'+id+'_3').css({'opacity':1,'pointer-events':'initial'});
            $('#poi_embded_helper_'+id+'_4').css({'opacity':1,'pointer-events':'initial'});
            $('#rotateX').parent().hide();
            $('#size_scale').parent().hide();
        } else {
            $('#msg_drag_poi').show();
            $('#msg_drag_embed').hide();
            $('#rotateX').parent().show();
            $('#size_scale').parent().show();
            $('#rotateX').val(window.pois[index].rotateX);
            $('#rotateZ').val(window.pois[index].rotateZ);
            $('#size_scale').val(window.pois[index].size_scale);
            $('#perspective_values').html('('+window.pois[index].rotateX+','+window.pois[index].rotateZ+')');
            $('#size_values').html('('+window.pois[index].size_scale+')');
            $('#rotateX').attr('oninput','adjust_perspective_p('+id+','+index+')');
            $('#rotateZ').attr('oninput','adjust_perspective_p('+id+','+index+')');
            $('#size_scale').attr('oninput','adjust_size_scale_p('+id+','+index+')');
            if(window.pois[index].embed_type!='' && window.pois[index].transform3d==0) {
                window.viewer.removeHotSpot("p"+id.toString());
                window.viewer.addHotSpot({
                    "id": "p"+window.pois[index].id,
                    "type": window.pois[index].embed_type,
                    "object": "poi_embed",
                    "draggable": true,
                    "transform3d": parseInt(window.pois[index].transform3d),
                    "tooltip_type": "",
                    "pitch": parseFloat(window.pois[index].pitch),
                    "yaw": parseFloat(window.pois[index].yaw),
                    "rotateX": parseInt(window.pois[index].rotateX),
                    "rotateZ": parseInt(window.pois[index].rotateZ),
                    "size_scale": parseFloat(window.pois[index].size_scale),
                    "cssClass": "hotspot-embed",
                    "createTooltipFunc": hotspot_embed,
                    "createTooltipArgs": window.pois[index],
                    "dragHandlerFunc": drag_poi_move,
                    "dragHandlerArgs": index
                });
                adjust_poi_embed_helpers_all();
            } else {
                window.viewer.removeHotSpot(id.toString());
                window.viewer.addHotSpot({
                    "id": window.pois[index].id,
                    "type": 'poi',
                    "transform3d": false,
                    "draggable": true,
                    "pitch": parseFloat(window.pois[index].pitch),
                    "yaw": parseFloat(window.pois[index].yaw),
                    "rotateX": parseInt(window.pois[index].rotateX),
                    "rotateZ": parseInt(window.pois[index].rotateZ),
                    "size_scale": parseFloat(window.pois[index].size_scale),
                    "cssClass": "custom-hotspot-content",
                    "createTooltipFunc": hotspot_p,
                    "createTooltipArgs": window.pois[index],
                    "dragHandlerFunc": drag_poi_move,
                    "dragHandlerArgs": index
                });
            }
            setTimeout(function () {
                window.viewer.resize();
                $('.hotspot_'+id).addClass('grabbable');
            },100);
        }
        $('#btn_change_zindex_left').attr('onclick','change_zindex_p_down('+id+','+index+')');
        $('#btn_change_zindex_right').attr('onclick','change_zindex_p_up('+id+','+index+')');
        $('#zIndex_value').html(window.pois[index].zIndex);
        $('#confirm_move .btn_confirm').attr('onclick','confirm_move_poi('+id+','+index+','+index_poi_add+')');
        $('#confirm_move .btn_close').attr('onclick','exit_move_poi('+id+','+index+')');
        $('#confirm_move').show();
    }

    window.adjust_perspective_m = function(id,index) {
        var rotateX = $('#rotateX').val();
        var rotateZ = $('#rotateZ').val();
        window.markers[index].rotateX = rotateX;
        window.markers[index].rotateZ = rotateZ;
        window.viewer.removeHotSpot(id.toString());
        window.viewer.addHotSpot({
            "id": window.markers[index].id,
            "type": 'marker',
            "transform3d": false,
            "pitch": parseFloat(window.markers[index].pitch),
            "yaw": parseFloat(window.markers[index].yaw),
            "draggable": true,
            "rotateX": parseInt(window.markers[index].rotateX),
            "rotateZ": parseInt(window.markers[index].rotateZ),
            "size_scale": parseFloat(window.markers[index].size_scale),
            "cssClass": "custom-hotspot",
            "createTooltipFunc": hotspot_m,
            "createTooltipArgs": window.markers[index],
            "dragHandlerFunc": drag_marker_move,
            "dragHandlerArgs": index
        });
        $('.hotspot_'+id).addClass('grabbable');
        $('#perspective_values').html('('+window.markers[index].rotateX+','+window.markers[index].rotateZ+')');
    }

    window.change_zindex_p_down = function (id,index) {
        var zindex = parseInt($('#zIndex_value').html());
        zindex = zindex - 1;
        if(zindex<=1) zindex=1;
        $('#zIndex_value').html(zindex);
        window.pois[index].zIndex = zindex;
        $('.hotspot_'+id).css('z-index',zindex);
    }

    window.change_zindex_p_up = function (id,index) {
        var zindex = parseInt($('#zIndex_value').html());
        zindex = zindex + 1;
        $('#zIndex_value').html(zindex);
        window.pois[index].zIndex = zindex;
        $('.hotspot_'+id).css('z-index',zindex);
    }

    window.adjust_perspective_p = function(id,index) {
        var rotateX = $('#rotateX').val();
        var rotateZ = $('#rotateZ').val();
        window.pois[index].rotateX = rotateX;
        window.pois[index].rotateZ = rotateZ;
        if(window.pois[index].embed_type!='' && window.pois[index].transform3d==0) {
            window.viewer.removeHotSpot("p"+id.toString());
            window.viewer.addHotSpot({
                "id": "p"+window.pois[index].id,
                "type": window.pois[index].embed_type,
                "object": "poi_embed",
                "transform3d": parseInt(window.pois[index].transform3d),
                "tooltip_type": "",
                "draggable": true,
                "pitch": parseFloat(window.pois[index].pitch),
                "yaw": parseFloat(window.pois[index].yaw),
                "rotateX": parseInt(window.pois[index].rotateX),
                "rotateZ": parseInt(window.pois[index].rotateZ),
                "size_scale": parseFloat(window.pois[index].size_scale),
                "cssClass": "hotspot-embed",
                "createTooltipFunc": hotspot_embed,
                "createTooltipArgs": window.pois[index],
                "dragHandlerFunc": drag_poi_move,
                "dragHandlerArgs": index
            });
            adjust_poi_embed_helpers_all();
        } else {
            window.viewer.removeHotSpot(id.toString());
            window.viewer.addHotSpot({
                "id": window.pois[index].id,
                "type": 'poi',
                "transform3d": false,
                "draggable": true,
                "pitch": parseFloat(window.pois[index].pitch),
                "yaw": parseFloat(window.pois[index].yaw),
                "rotateX": parseInt(window.pois[index].rotateX),
                "rotateZ": parseInt(window.pois[index].rotateZ),
                "size_scale": parseFloat(window.pois[index].size_scale),
                "cssClass": "custom-hotspot-content",
                "createTooltipFunc": hotspot_p,
                "createTooltipArgs": window.pois[index],
                "dragHandlerFunc": drag_poi_move,
                "dragHandlerArgs": index
            });
        }
        $('.hotspot_'+id).addClass('grabbable');
        $('#perspective_values').html('('+window.pois[index].rotateX+','+window.pois[index].rotateZ+')');
    }

    window.adjust_size_scale_m = function(id,index) {
        var size_scale = $('#size_scale').val();
        window.markers[index].size_scale = size_scale;
        window.viewer.removeHotSpot(id.toString());
        window.viewer.addHotSpot({
            "id": window.markers[index].id,
            "type": 'marker',
            "transform3d": false,
            "pitch": parseFloat(window.markers[index].pitch),
            "yaw": parseFloat(window.markers[index].yaw),
            "draggable": true,
            "rotateX": parseInt(window.markers[index].rotateX),
            "rotateZ": parseInt(window.markers[index].rotateZ),
            "size_scale": parseFloat(window.markers[index].size_scale),
            "cssClass": "custom-hotspot",
            "createTooltipFunc": hotspot_m,
            "createTooltipArgs": window.markers[index],
            "dragHandlerFunc": drag_marker_move,
            "dragHandlerArgs": index
        });
        $('.hotspot_'+id).addClass('grabbable');
        $('#size_values').html('('+window.markers[index].size_scale+')');
    }

    window.adjust_size_scale_p = function(id,index) {
        var size_scale = $('#size_scale').val();
        window.pois[index].size_scale = size_scale;
        if(window.pois[index].embed_type!='' && window.pois[index].transform3d==0) {
            window.viewer.removeHotSpot("p"+id.toString());
            window.viewer.addHotSpot({
                "id": "p"+window.pois[index].id,
                "type": window.pois[index].embed_type,
                "object": "poi_embed",
                "transform3d": parseInt(window.pois[index].transform3d),
                "draggable": true,
                "tooltip_type": "",
                "pitch": parseFloat(window.pois[index].pitch),
                "yaw": parseFloat(window.pois[index].yaw),
                "rotateX": parseInt(window.pois[index].rotateX),
                "rotateZ": parseInt(window.pois[index].rotateZ),
                "size_scale": parseFloat(window.pois[index].size_scale),
                "cssClass": "hotspot-embed",
                "createTooltipFunc": hotspot_embed,
                "createTooltipArgs": window.pois[index],
                "dragHandlerFunc": drag_poi_move,
                "dragHandlerArgs": index
            });
            adjust_poi_embed_helpers_all();
        } else {
            window.viewer.removeHotSpot(id.toString());
            window.viewer.addHotSpot({
                "id": window.pois[index].id,
                "type": 'poi',
                "transform3d": false,
                "draggable": true,
                "pitch": parseFloat(window.pois[index].pitch),
                "yaw": parseFloat(window.pois[index].yaw),
                "rotateX": parseInt(window.pois[index].rotateX),
                "rotateZ": parseInt(window.pois[index].rotateZ),
                "size_scale": parseFloat(window.pois[index].size_scale),
                "cssClass": "custom-hotspot-content",
                "createTooltipFunc": hotspot_p,
                "createTooltipArgs": window.pois[index],
                "dragHandlerFunc": drag_poi_move,
                "dragHandlerArgs": index
            });
        }
        $('#size_values').html('('+window.pois[index].size_scale+')');
    }

    window.adjust_map_north = function () {
        var north_degree = $('#north_degree').val();
        $('#map_compass img').css('transform','rotate('+north_degree+'deg)');
    }

    window.confirm_move_marker = function (id,index) {
        if(window.markers[index].embed_type!='') {
            var yaw = window.markers[index].yaw;
            var pitch = window.markers[index].pitch;
            var rotateX = 0;
            var rotateZ = 0;
            var size_scale = 1;
            var embed_coords = window.markers[index].embed_coords;
            var embed_size = window.markers[index].embed_size;
        } else {
            var yaw = window.markers[index].yaw;
            var pitch = window.markers[index].pitch;
            var rotateX = $('#rotateX').val();
            var rotateZ = $('#rotateZ').val();
            var size_scale = $('#size_scale').val();
            var embed_coords = '';
            var embed_size = '';
        }
        window.markers[index].yaw = yaw;
        window.markers[index].pitch = pitch;
        window.markers[index].rotateX = rotateX;
        window.markers[index].rotateZ = rotateZ;
        window.markers[index].size_scale = size_scale;
        if(window.markers[index].embed_type=='') {
            render_marker(id,index);
            setTimeout(function () {
                window.viewer.resize();
            },100);
        }
        window.is_editing = false;
        window.markers_initial[index] = {...window.markers[index]};
        save_marker_pos(id,yaw,pitch,rotateX,rotateZ,size_scale,embed_coords,embed_size);
        $('.rooms_slider').removeClass('disabled');
        $('#btn_add_marker').removeClass('disabled');
        $('#btn_switch_to_poi').removeClass('disabled');
        $('#btn_preview_modal').removeClass('disabled');
        $('.custom-hotspot').css({'opacity':1,'pointer-events':'initial'});
        $('.marker_embded_helper').css({'opacity':0,'pointer-events':'none'});
        $('.marker_embded_move').css({'opacity':0,'pointer-events':'none'});
        $('.marker_embed').css({'opacity':1,'pointer-events':'initial'});
        $('#confirm_move').hide();
        $('.center_helper').show();
    }

    window.exit_move_marker = function (id,index) {
        window.markers[index] = {...window.markers_initial[index]};
        render_marker(id,index);
        setTimeout(function () {
            window.viewer.resize();
        },100);
        window.is_editing = false;
        $('.rooms_slider').removeClass('disabled');
        $('#btn_add_marker').removeClass('disabled');
        $('#btn_switch_to_poi').removeClass('disabled');
        $('#btn_preview_modal').removeClass('disabled');
        $('.custom-hotspot').css({'opacity':1,'pointer-events':'initial'});
        $('.marker_embded_helper').css({'opacity':0,'pointer-events':'none'});
        $('.marker_embded_move').css({'opacity':0,'pointer-events':'none'});
        $('.marker_embed').css({'opacity':1,'pointer-events':'initial'});
        $('#confirm_move').hide();
        $('.center_helper').show();
    }

    window.confirm_move_poi = function (id,index,index_poi_add) {
        if(window.pois[index].embed_type!='' && window.pois[index].transform3d==1) {
            var yaw = window.pois[index].yaw;
            var pitch = window.pois[index].pitch;
            var rotateX = 0;
            var rotateZ = 0;
            var size_scale = 1;
            var embed_coords = window.pois[index].embed_coords;
            var embed_size = window.pois[index].embed_size;
        } else if(window.pois[index].embed_type!='' && window.pois[index].transform3d==0) {
            var yaw = window.pois[index].yaw;
            var pitch = window.pois[index].pitch;
            var rotateX = $('#rotateX').val();
            var rotateZ = $('#rotateZ').val();
            var size_scale = $('#size_scale').val();
            var embed_coords = window.pois[index].embed_coords;
            var embed_size = window.pois[index].embed_size;
        } else {
            var yaw = window.pois[index].yaw;
            var pitch = window.pois[index].pitch;
            var rotateX = $('#rotateX').val();
            var rotateZ = $('#rotateZ').val();
            var size_scale = $('#size_scale').val();
            var embed_coords = '';
            var embed_size = '';
        }
        window.pois[index].yaw = yaw;
        window.pois[index].pitch = pitch;
        window.pois[index].size_scale = size_scale;
        window.pois[index].rotateX = rotateX;
        window.pois[index].rotateZ = rotateZ;
        if(window.pois[index].embed_type=='' || window.pois[index].transform3d==0) {
            render_poi(id,index);
            setTimeout(function () {
                window.viewer.resize();
            },100);
        }
        window.is_editing = false;
        window.pois_initial[index] = {...window.pois[index]};
        save_poi_pos(id,yaw,pitch,rotateX,rotateZ,size_scale,embed_coords,embed_size,window.pois[index].transform3d,window.pois[index].zIndex);
        $('.rooms_slider').removeClass('disabled');
        $('#btn_add_poi').removeClass('disabled');
        $('#btn_switch_to_marker').removeClass('disabled');
        $('#btn_preview_modal').removeClass('disabled');
        $('.custom-hotspot-content').css({'opacity':1,'pointer-events':'initial'});
        $('.poi_embded_helper').css({'opacity':0,'pointer-events':'none'});
        $('.poi_embded_move').css({'opacity':0,'pointer-events':'none'});
        $('.poi_embed').css({'opacity':1,'pointer-events':'initial'});
        $('#confirm_move').hide();
        $('.center_helper').show();
        if(index_poi_add!=null) {
            edit_p(id,index,id);
        }
    }

    window.exit_move_poi = function (id,index) {
        window.pois[index] = {...window.pois_initial[index]};
        render_poi(id,index);
        setTimeout(function () {
            window.viewer.resize();
        },100);
        window.is_editing = false;
        $('.rooms_slider').removeClass('disabled');
        $('#btn_add_poi').removeClass('disabled');
        $('#btn_switch_to_marker').removeClass('disabled');
        $('#btn_preview_modal').removeClass('disabled');
        $('.custom-hotspot-content').css({'opacity':1,'pointer-events':'initial'});
        $('.poi_embded_helper').css({'opacity':0,'pointer-events':'none'});
        $('.poi_embded_move').css({'opacity':0,'pointer-events':'none'});
        $('.poi_embed').css({'opacity':1,'pointer-events':'initial'});
        $('#confirm_move').hide();
        $('.center_helper').show();
    }

    window.confirm_edit_marker = function (id,index) {
        if($('#override_pos_edit').is(':checked')) {
            var yaw_m = window.viewer_pos.getYaw();
            var pitch_m = window.viewer_pos.getPitch();
        } else {
            var yaw_m = '';
            var pitch_m = '';
        }
        var embed_content = '';
        var embed_type = window.markers[index].embed_type;
        var lookat = $('#lookat option:selected').attr('id');
        var show_room = $('#marker_style option:selected').attr('id');
        var tooltip_type = $('#tooltip_type option:selected').attr('id');
        var tooltip_text = $('#tooltip_text').val();
        var color = $('#marker_color').val();
        var background = $('#marker_background').val();
        var css_class = $('#marker_css_class').val();
        var icon = $('#marker_icon').val();
        if(show_room==4) {
            var id_icon_library = $('#marker_library_icon').val();
            if(id_icon_library==0) {
                alert(window.backend_labels.select_icon_msg);
                return;
            }
        } else {
            var id_icon_library = 0;
        }
        switch(window.markers[index].embed_type) {
            case 'selection':
                embed_content = 'border-width:'+$('#marker_border_px').val()+'px;';
                break;
            default:
                break;
        }
        window.markers[index].yaw_room_target = yaw_m;
        window.markers[index].pitch_room_target = pitch_m;
        window.markers[index].id_icon_library = id_icon_library;
        window.markers[index].show_room = show_room;
        window.markers[index].tooltip_type = tooltip_type;
        window.markers[index].tooltip_text = tooltip_text;
        window.markers[index].css_class = css_class;
        window.markers[index].embed_content = embed_content;
        window.markers[index].lookat = lookat;
        window.markers_initial[index] = {...window.markers[index]};
        window.is_editing = false;
        var id_room_target = window.markers[index].id_room_target;
        save_marker_edit(id,id_room_target,yaw_m,pitch_m,lookat);
        save_marker_style(id,show_room,color,background,icon,id_icon_library,tooltip_type,tooltip_text,css_class,embed_content,window.markers[index].animation);
        $('.rooms_slider').removeClass('disabled');
        $('#btn_add_marker').removeClass('disabled');
        $('#btn_switch_to_poi').removeClass('disabled');
        $('#btn_preview_modal').removeClass('disabled');
        var yaw = parseFloat(window.markers[index].yaw);
        var pitch = parseFloat(window.markers[index].pitch);
        window.viewer.lookAt(pitch,yaw,100,200);
        $('.poi_embded_helper').css({'opacity':0,'pointer-events':'none'});
        $('.poi_embded_move').css({'opacity':0,'pointer-events':'none'});
        $('.poi_embed').css({'opacity':1,'pointer-events':'initial'});
        $('#confirm_edit').hide();
        if(window.markers[index].embed_type!='') {
            render_marker(id,index);
        }
    }

    window.exit_edit_marker = function (id,index) {
        window.markers[index] = {...window.markers_initial[index]};
        render_marker(id,index);
        window.is_editing = false;
        $('.rooms_slider').removeClass('disabled');
        $('#btn_add_marker').removeClass('disabled');
        $('#btn_switch_to_poi').removeClass('disabled');
        $('#btn_preview_modal').removeClass('disabled');
        var yaw = parseFloat(window.markers[index].yaw);
        var pitch = parseFloat(window.markers[index].pitch);
        window.currentYaw = yaw;
        window.currentPitch = pitch;
        window.viewer.lookAt(pitch,yaw,100,200);
        $('.poi_embded_helper').css({'opacity':0,'pointer-events':'none'});
        $('.poi_embded_move').css({'opacity':0,'pointer-events':'none'});
        $('.poi_embed').css({'opacity':1,'pointer-events':'initial'});
        $('#confirm_edit').hide();
    }

    window.confirm_edit_poi = function (id,index) {
        window.is_editing = false;
        var content = '';
        var params = '';
        var embed_content = '';
        var target = '';
        var c_title = '';
        var c_description = '';
        var song_bg_volume = 0.3;
        var type = window.pois[index].type;
        var embed_type = window.pois[index].embed_type;
        switch (type) {
            case 'image':
                c_title = $('#poi_title').val();
                c_description = $('#poi_description').val();
                content = $('#poi_content').val();
                break;
            case 'video':
                c_title = $('#poi_title').val();
                c_description = $('#poi_description').val();
                content = $('#poi_content').val();
                break;
            case 'video360':
                c_title = $('#poi_title').val();
                c_description = $('#poi_description').val();
                content = $('#poi_content').val();
                break;
            case 'link':
                c_title = $('#poi_title').val();
                c_description = $('#poi_description').val();
                content = $('#poi_content').val();
                break;
            case 'gallery':
                content = '';
                break;
            case 'html':
                c_title = $('#poi_title').val();
                c_description = $('#poi_description').val();
                content = window.html_editor.root.innerHTML;
                break;
            case 'html_sc':
                c_title = $('#poi_title').val();
                c_description = $('#poi_description').val();
                content = window.poi_content_html_sc.getValue();
                break;
            case 'form':
                content = [{},{},{},{},{},{},{},{},{},{},{}];
                var title = $('#form_title').val();
                var button = $('#form_button').val();
                var response = $('#form_response').val();
                var description = $('#form_description').val();
                content[0]['title'] = title;
                content[0]['button'] = button;
                content[0]['response'] = response;
                content[0]['description'] = description;
                content[0]['send_email'] = $('#form_send_email').is(':checked');
                content[0]['email'] = $('#form_email').val();
                for(var i=1;i<=10;i++) {
                    content[i]['enabled'] = $('#form_field_'+i).is(':checked');
                    content[i]['required'] = $('#form_field_required_'+i).is(':checked');
                    content[i]['type'] = $('#form_field_type_'+i+' option:selected').attr('id');
                    content[i]['label'] = $('#form_field_label_'+i).val();
                }
                content = JSON.stringify(content);
                break;
            case 'link_ext':
                target = $('#poi_target option:selected').attr('id');
                content = $('#poi_content').val();
                break;
            case 'google_maps':
                c_title = $('#poi_title').val();
                c_description = $('#poi_description').val();
                content = $('#poi_gm_map').val()+"|"+$('#poi_gm_street').val();
                break;
            case 'object360':
                c_title = $('#poi_title').val();
                c_description = $('#poi_description').val();
                content = '';
                break;
            case 'object3d':
                c_title = $('#poi_title').val();
                c_description = $('#poi_description').val();
                content = $('#poi_content').val();
                params = $('#params_ar option:selected').attr('id');
                break;
            case 'audio':
                song_bg_volume = $('#poi_song_bg_volume').val();
                content = $('#poi_content').val();
                break;
            case 'lottie':
                c_title = $('#poi_title').val();
                c_description = $('#poi_description').val();
                content = $('#poi_content').val();
                break;
            case 'product':
                c_title = $('#poi_title').val();
                c_description = $('#poi_description').val();
                content = $('#poi_content_product option:selected').attr('id');
                break;
            case 'switch_pano':
                if($('#switch_panorama_default').is(':checked')) {
                    content = 0;
                } else {
                    content = $('#switch_panorama_id').val();
                }
                break;
            default:
                content = $('#poi_content').val();
                break;
        }
        var view_type = parseInt($('#view_type option:selected').attr('id'));
        var box_pos = $('#box_pos option:selected').attr('id');
        var auto_close = $('#auto_close').val();
        var color = $('#poi_color').val();
        var background = $('#poi_background').val();
        var css_class = $('#poi_css_class').val();
        var icon = $('#poi_icon').val();
        var label = $('#poi_label').val();
        var style = $('#poi_style option:selected').attr('id');
        var tooltip_type = $('#tooltip_type option:selected').attr('id');
        var tooltip_text = window.tooltip_text_editor.root.innerHTML;
        if(style==1) {
            var id_icon_library = $('#poi_library_icon').val();
            if(id_icon_library==0) {
                alert(window.backend_labels.select_icon_msg);
                return;
            }
        } else {
            var id_icon_library = 0;
        }
        var embed_video_autoplay = 1;
        var embed_video_muted = 1;
        var embed_gallery_autoplay = 0;
        var embed_content = $('#poi_embed_content').val();
        switch(window.pois[index].embed_type) {
            case 'video':
            case 'video_transparent':
                if($('#embed_video_autoplay').is(':checked')) embed_video_autoplay=1; else embed_video_autoplay=0;
                if($('#embed_video_muted').is(':checked')) embed_video_muted=1; else embed_video_muted=0;
                break;
            case 'video_chroma':
                if($('#embed_video_autoplay').is(':checked')) embed_video_autoplay=1; else embed_video_autoplay=0;
                if($('#embed_video_muted').is(':checked')) embed_video_muted=1; else embed_video_muted=0;
                var chroma_color = $('#chroma_color').val().replace("rgb(","").replace(")","");
                var chroma_tolerance = $('#chroma_tolerance').val();
                params = chroma_color+','+chroma_tolerance;
                break;
            case 'gallery':
                embed_gallery_autoplay = $('#embed_gallery_autoplay').val();
                embed_content = get_first_poi_embed_gallery_image(id);
                break;
            case 'text':
                embed_content = window.poi_embed_content_html_editor.root.innerHTML+' border-width:'+$('#poi_border_px').val()+'px;';
                break;
            case 'selection':
                embed_content = 'border-width:'+$('#poi_border_px').val()+'px;';
                break;
            default:
                break;
        }
        if($('#enable_schedule').is(':checked')) {
            var date_from = $('#date_from').val();
            var date_to = $('#date_to').val();
            var hour_from = $('#hour_from').val();
            var hour_to = $('#hour_to').val();
            var days_1 = ($("#days_1").is(':checked')) ? 1 : 0;
            var days_2 = ($("#days_2").is(':checked')) ? 1 : 0;
            var days_3 = ($("#days_3").is(':checked')) ? 1 : 0;
            var days_4 = ($("#days_4").is(':checked')) ? 1 : 0;
            var days_5 = ($("#days_5").is(':checked')) ? 1 : 0;
            var days_6 = ($("#days_6").is(':checked')) ? 1 : 0;
            var days_7 = ($("#days_7").is(':checked')) ? 1 : 0;
            var schedule = {};
            schedule['from_date'] = date_from;
            schedule['to_date'] = date_to;
            schedule['from_hour'] = hour_from;
            schedule['to_hour'] = hour_to;
            schedule['days'] = days_1+","+days_2+","+days_3+","+days_4+","+days_5+","+days_6+","+days_7;
            schedule = JSON.stringify(schedule);
        } else {
            schedule = '';
        }
        if(window.pois[index].embed_type!='' && window.pois[index].transform3d==0) {
            window.pois[index].embed_size = '';
            switch(window.pois[index].embed_type) {
                case 'image':
                    var img_tmp = new Image();
                    img_tmp.onload = function () {
                        var height = img_tmp.height;
                        var width = img_tmp.width;
                        var ratio = width / height;
                        if(width>=height) {
                            window.pois[index].embed_size = '300,'+(300/ratio);
                        } else {
                            window.pois[index].embed_size = '100,'+(100/ratio);
                        }
                        save_poi_pos(id,window.pois[index].yaw,window.pois[index].pitch,window.pois[index].rotateX,window.pois[index].rotateZ,window.pois[index].size_scale,window.pois[index].embed_coords,window.pois[index].embed_size,window.pois[index].transform3d,window.pois[index].zIndex);
                        render_poi(id,index);
                        adjust_poi_embed_helpers_all();
                    }
                    if(embed_content.startsWith("http") || embed_content.startsWith("//")) {
                        img_tmp.src = embed_content;
                    } else {
                        img_tmp.src = '../viewer/'+embed_content;
                    }
                    break;
                case 'video':
                    var video = document.createElement('video');
                    video.addEventListener( "loadedmetadata", function (e) {
                        var width = this.videoWidth, height = this.videoHeight;
                        var ratio = width / height;
                        if(width>=height) {
                            window.pois[index].embed_size = '300,'+(300/ratio);
                        } else {
                            window.pois[index].embed_size = '200,'+(200/ratio);
                        }
                        save_poi_pos(id,window.pois[index].yaw,window.pois[index].pitch,window.pois[index].rotateX,window.pois[index].rotateZ,window.pois[index].size_scale,window.pois[index].embed_coords,window.pois[index].embed_size,window.pois[index].transform3d,window.pois[index].zIndex);
                        render_poi(id,index);
                        adjust_poi_embed_helpers_all();
                    }, false );
                    video.preload = 'metadata';
                    video.src = '../viewer/'+embed_content;
                    video.load();
                    break;
            }
        }
        window.pois[index].target = target;
        window.pois[index].content = content;
        window.pois[index].params = params;
        window.pois[index].title = c_title;
        window.pois[index].description = c_description;
        window.pois[index].view_type = view_type;
        window.pois[index].box_pos = box_pos;
        window.pois[index].auto_close = auto_close;
        window.pois[index].id_icon_library = id_icon_library;
        window.pois[index].style = style;
        window.pois[index].background = background;
        window.pois[index].color = color;
        window.pois[index].label = label;
        window.pois[index].css_class = css_class;
        window.pois[index].tooltip_type = tooltip_type;
        window.pois[index].tooltip_text = tooltip_text;
        window.pois[index].embed_content = embed_content;
        window.pois[index].embed_video_autoplay = embed_video_autoplay;
        window.pois[index].embed_gallery_autoplay = embed_gallery_autoplay;
        window.pois[index].embed_video_muted = embed_video_muted;
        window.pois[index].schedule = schedule;
        window.pois[index].song_bg_volume = song_bg_volume;
        window.pois_initial[index] = {...window.pois[index]};
        if($('#id_poi_autoopen').is(':checked')) {
            window.id_poi_autoopen = id;
        } else {
            if(window.id_poi_autoopen==id) {
                window.id_poi_autoopen = '';
            }
        }
        save_poi_edit(id,type,content,c_title,c_description,target,view_type,box_pos,song_bg_volume,params,auto_close);
        save_poi_style(id,color,background,icon,style,id_icon_library,label,tooltip_type,tooltip_text,css_class,embed_content,embed_video_autoplay,embed_video_muted,embed_gallery_autoplay,window.pois[index].embed_type,window.pois[index].animation);
        save_poi_schedule(id,schedule);
        $('.rooms_slider').removeClass('disabled');
        $('#btn_add_poi').removeClass('disabled');
        $('#btn_switch_to_marker').removeClass('disabled');
        $('#btn_preview_modal').removeClass('disabled');
        var yaw = parseFloat(window.pois[index].yaw);
        var pitch = parseFloat(window.pois[index].pitch);
        window.viewer.lookAt(pitch,yaw,100,200);
        $('.custom-hotspot-content').css('opacity',1);
        $('.poi_embded_helper').css({'opacity':0,'pointer-events':'none'});
        $('.poi_embded_move').css({'opacity':0,'pointer-events':'none'});
        $('.poi_embed').css({'opacity':1,'pointer-events':'initial'});
        $('#confirm_edit').hide();
        if(window.pois[index].embed_type!='') {
            render_poi(id,index);
        }
    }

    window.exit_edit_poi = function (id,index) {
        window.pois[index] = {...window.pois_initial[index]};
        render_poi(id,index);
        window.is_editing = false;
        $('.rooms_slider').removeClass('disabled');
        $('#btn_add_poi').removeClass('disabled');
        $('#btn_switch_to_marker').removeClass('disabled');
        $('#btn_preview_modal').removeClass('disabled');
        var yaw = parseFloat(window.pois[index].yaw);
        var pitch = parseFloat(window.pois[index].pitch);
        window.currentYaw = yaw;
        window.currentPitch = pitch;
        window.viewer.lookAt(pitch,yaw,100,200);
        $('.custom-hotspot-content').css('opacity',1);
        $('.hotspot-embed').css('opacity',1);
        $('#confirm_edit').hide();
    }

    function get_first_poi_embed_gallery_image(id) {
        var image = '';
        $.ajax({
            url: "ajax/get_first_poi_embed_gallery_image.php",
            type: "POST",
            data: {
                id_poi: id
            },
            async: false,
            success: function (rsp) {
                image = rsp;
            }
        });
        if(image!='') image='../viewer/gallery/'+image;
        return image;
    }

    function hotspot_p(hotSpotDiv, args) {
        hotSpotDiv.classList.add('hotspot_'+args.id);
        hotSpotDiv.classList.add('noselect');
        if(args.what=='marker') {
            hotSpotDiv.style.opacity = 0.2;
        }
        hotSpotDiv.style.zIndex = args.zIndex;
        var div_wrapper = document.createElement('div');
        div_wrapper.classList.add('div_poi_wrapper');
        if(args.animation!='none') {
            div_wrapper.classList.add('animate__animated');
            div_wrapper.classList.add('animate__slow');
            div_wrapper.classList.add('animate__'+args.animation);
            div_wrapper.classList.add('animate__infinite');
        }
        var lottie = false;
        var style_t = parseInt(args.style);
        switch (style_t) {
            case 0:
                div_wrapper.style.background = args.background;
                var i = document.createElement('i');
                i.className = args.icon;
                i.style = "margin: 0 auto;vertical-align:middle;font-size:24px;color:"+args.color;
                div_wrapper.appendChild(i);
                break;
            case 1:
                var ext = args.img_icon_library.split('.').pop().toLowerCase();
                if(ext=='json') {
                    lottie = true;
                    hotSpotDiv.classList.add('lottie_icon');
                    var div = document.createElement('div');
                    div.innerHTML = '<div id="lottie_p_'+args.id+'" style="height:50px;width:50px;vertical-align:middle"></div>';
                    hotSpotDiv.appendChild(div);
                    bodymovin.loadAnimation({
                        container: document.getElementById('lottie_p_'+args.id),
                        renderer: 'svg',
                        loop: true,
                        autoplay: true,
                        path: '../viewer/icons/'+args.img_icon_library,
                        rendererSettings: {
                            progressiveLoad: true,
                        }
                    });
                } else {
                    var img = document.createElement('img');
                    img.src = args.base64_icon_library;
                    img.style = "width:50px";
                    img.draggable = false;
                    div_wrapper.appendChild(img);
                }
                break;
            case 2:
                div_wrapper.style.background = args.background;
                var i = document.createElement('i');
                i.className = args.icon;
                i.style = "margin: 0 auto;vertical-align:middle;font-size:24px;color:"+args.color;
                div_wrapper.appendChild(i);
                var span = document.createElement('span');
                span.innerHTML = '&nbsp;'+args.label.toUpperCase();
                span.style = "vertical-align:middle;color:"+args.color;
                div_wrapper.appendChild(span);
                break;
            case 3:
                div_wrapper.style.background = args.background;
                var span = document.createElement('span');
                span.innerHTML = args.label.toUpperCase()+'&nbsp;';
                span.style = "vertical-align:middle;color:"+args.color;
                div_wrapper.appendChild(span);
                var i = document.createElement('i');
                i.className = args.icon;
                i.style = "margin: 0 auto;vertical-align:middle;font-size:24px;color:"+args.color;
                div_wrapper.appendChild(i);
                break;
            case 4:
                div_wrapper.style.background = args.background;
                var span = document.createElement('span');
                span.innerHTML = args.label.toUpperCase();
                span.style = "font-size:14px;vertical-align:middle;color:"+args.color;
                div_wrapper.appendChild(span);
                break;
        }
        if(lottie==false) hotSpotDiv.appendChild(div_wrapper);
    }

    function hotspot_m(hotSpotDiv, args) {
        hotSpotDiv.classList.add('custom-tooltip');
        hotSpotDiv.classList.add('hotspot_'+args.id);
        hotSpotDiv.classList.add('noselect');
        var div_wrapper = document.createElement('div');
        div_wrapper.classList.add('div_marker_wrapper');
        if(args.animation!='none') {
            div_wrapper.classList.add('animate__animated');
            div_wrapper.classList.add('animate__slow');
            div_wrapper.classList.add('animate__'+args.animation);
            div_wrapper.classList.add('animate__infinite');
        }
        var lottie = false;
        var show_room_t = parseInt(args.show_room);
        switch (show_room_t) {
            case 0:
                div_wrapper.style.background = args.background;
                var i = document.createElement('i');
                i.className = args.icon+" marker_img_"+args.id_room_target;
                i.style = "margin: 0 auto;vertical-align:middle;font-size:24px;color:"+args.color;
                div_wrapper.appendChild(i);
                break;
            case 1:
                div_wrapper.style.background = args.background;
                var i = document.createElement('i');
                i.className = args.icon+" marker_img_"+args.id_room_target;
                i.style = "margin: 0 auto;vertical-align:middle;font-size:24px;color:"+args.color;
                div_wrapper.appendChild(i);
                var span = document.createElement('span');
                span.innerHTML = '&nbsp;'+args.name_room_target.toUpperCase();
                span.style = "vertical-align:middle;color:"+args.color;
                div_wrapper.appendChild(span);
                break;
            case 2:
                div_wrapper.style.background = args.background;
                var span = document.createElement('span');
                span.innerHTML = args.name_room_target.toUpperCase()+'&nbsp;';
                span.style = "vertical-align:middle;color:"+args.color;
                div_wrapper.appendChild(span);
                var i = document.createElement('i');
                i.className = args.icon+" marker_img_"+args.name_room_target;
                i.style = "margin: 0 auto;vertical-align:middle;font-size:24px;color:"+args.color;
                div_wrapper.appendChild(i);
                break;
            case 3:
                div_wrapper.style.background = args.background;
                var span = document.createElement('span');
                span.innerHTML = args.name_room_target.toUpperCase();
                span.style = "font-size:14px;vertical-align:middle;color:"+args.color;
                div_wrapper.appendChild(span);
                break;
            case 4:
                var ext = args.img_icon_library.split('.').pop().toLowerCase();
                if(ext=='json') {
                    lottie = true;
                    hotSpotDiv.classList.add('lottie_icon');
                    var div = document.createElement('div');
                    div.innerHTML = '<div id="lottie_m_'+args.id+'" style="height:50px;width:50px;vertical-align:middle"></div>';
                    hotSpotDiv.appendChild(div);
                    bodymovin.loadAnimation({
                        container: document.getElementById('lottie_m_'+args.id),
                        renderer: 'svg',
                        loop: true,
                        autoplay: true,
                        path: '../viewer/icons/'+args.img_icon_library,
                        rendererSettings: {
                            progressiveLoad: true,
                        }
                    });
                } else {
                    var img = document.createElement('img');
                    img.src = args.base64_icon_library;
                    img.style = "width:50px";
                    img.draggable = false;
                    div_wrapper.appendChild(img);
                }
                break;
            case 5:
                var image = args.base64_marker_preview;
                var div = document.createElement('div');
                div.classList.add('marker_preview');
                div.style = 'width:48px;height:48px;border-radius:48px;background-image: url('+image+');background-size: cover;background-position: center;';
                div_wrapper.appendChild(div);
                break;
        }
        if(lottie==false) hotSpotDiv.appendChild(div_wrapper);
    }

    function get_pois(id_room,image,id_poi_add) {
        $.ajax({
            url: "ajax/get_pois.php",
            type: "POST",
            data: {
                id_room: id_room
            },
            async: true,
            success: function (json) {
                var rsp = JSON.parse(json);
                window.pois = rsp.pois;
                var array_room = rsp.room;
                window.id_poi_autoopen = array_room.id_poi_autoopen;
                window.pois_initial = JSON.parse(JSON.stringify(window.pois));
                initialize_pano_pois(id_room,image,id_poi_add,array_room);
            }
        });
    }

    window.change_marker_room_target = function (id,index,id_select) {
        $('#confirm_edit .btn_confirm').addClass("disabled");
        $('#btn_new_marker').addClass("disabled");
        var name_room_target = $('#'+id_select).val();
        var id_room_target = $('#'+id_select+' option:selected').attr('id');
        if(id_select=='room_target') {
            window.markers[index].name_room_target = name_room_target;
            window.markers[index].id_room_target = id_room_target;
            render_marker(id,index);
        }
        change_preview_room_image(id_room_target,id_select,index);
    }

    function change_preview_room_image(id_room_target,id_select,index_marker) {
        jQuery.each(window.rooms, function(index, room) {
            var id_room = room.id;
            if(id_room==id_room_target) {
                var room_image = room.panorama_image;
                $('.preview_room_target').attr('src','../viewer/panoramas/thumb/'+room_image);
                $('.preview_room_target').show();
                if(index_marker!=null) {
                    var yaw_m = window.markers[index_marker].yaw_room_target;
                    var pitch_m = window.markers[index_marker].pitch_room_target;
                    if((yaw_m=='') && (pitch_m=='')) {
                        var yaw = parseFloat(room.yaw);
                        var pitch = parseFloat(room.pitch);
                    } else {
                        var yaw = parseFloat(window.markers[index_marker].yaw_room_target);
                        var pitch = parseFloat(window.markers[index_marker].pitch_room_target);
                    }
                } else {
                    var yaw = parseFloat(room.yaw);
                    var pitch = parseFloat(room.pitch);
                }
                var h_pitch = room.h_pitch;
                var h_roll = room.h_roll;
                if(parseInt(room.allow_pitch)==1) {
                    var min_pitch = room.min_pitch;
                    var max_pitch = room.max_pitch;
                } else {
                    var min_pitch = 0;
                    var max_pitch = 0;
                    pitch = 0;
                }
                var min_yaw = room.min_yaw;
                var max_yaw = room.max_yaw;
                var haov = room.haov;
                var vaov = room.vaov;
                var panorama_image = '../viewer/panoramas/lowres/'+room_image;
                if(id_select=='room_target_add') {
                    load_viewer_pos('panorama_pos_add',id_room,panorama_image,yaw,pitch,h_pitch,h_roll,min_pitch,max_pitch,haov,vaov,min_yaw,max_yaw);
                } else {
                    load_viewer_pos('panorama_pos_edit',id_room,panorama_image,yaw,pitch,h_pitch,h_roll,min_pitch,max_pitch,haov,vaov,min_yaw,max_yaw);
                }
                return;
            }
        });
    }

    window.change_preview_room_image_map = function (id_room_sel) {
        if(id_room_sel==null) {
            id_room_sel = $('#room_select option:selected').attr('id');
        }
        jQuery.each(window.map_points, function(index, map_point) {
            var id_room = map_point.id;
            if(id_room==id_room_sel) {
                var room_image = map_point.panorama_image;
                $('.preview_room_target').attr('src','../viewer/panoramas/thumb/'+room_image);
                $('.preview_room_target').show();
                return;
            }
        });
    }

    window.change_marker_style_v = function() {
        var style = $('#markers_style option:selected').attr('id');
        switch(style) {
            case 'custom_icon':
                $('#m_icon').addClass('d-none').addClass('disabled');
                $('#m_custom_icon').removeClass('d-none').removeClass('disabled');
                $('#markers_color').addClass('disabled');
                $('#markers_background').addClass('disabled');
                break;
            case 'only_room':
                $('#m_icon').removeClass('d-none').addClass('disabled');
                $('#m_custom_icon').addClass('d-none').addClass('disabled');
                $('#markers_color').removeClass('disabled');
                $('#markers_background').removeClass('disabled');
                break;
            case 'preview_room':
                $('#m_icon').removeClass('d-none').addClass('disabled');
                $('#m_custom_icon').addClass('d-none').addClass('disabled');
                $('#markers_color').addClass('disabled');
                $('#markers_background').addClass('disabled');
                break;
            default:
                $('#m_icon').removeClass('d-none').removeClass('disabled');
                $('#m_custom_icon').addClass('d-none').addClass('disabled');
                $('#markers_color').removeClass('disabled');
                $('#markers_background').removeClass('disabled');
                break;
        }
        $('.marker_final_preview').addClass('d-none');
        $('#marker_'+style).removeClass('d-none');
    }

    window.change_poi_style_v = function() {
        var style = $('#pois_style option:selected').attr('id');
        switch(style) {
            case 'custom_icon':
                $('#p_icon').addClass('d-none').addClass('disabled');
                $('#p_custom_icon').removeClass('d-none').removeClass('disabled');
                $('#pois_color').addClass('disabled');
                $('#pois_background').addClass('disabled');
                break;
            case 'only_label':
                $('#p_icon').removeClass('d-none').addClass('disabled');;
                $('#p_custom_icon').addClass('d-none').addClass('disabled');
                $('#pois_color').removeClass('disabled');
                $('#pois_background').removeClass('disabled');
                break;
            default:
                $('#p_icon').removeClass('d-none').removeClass('disabled');
                $('#p_custom_icon').addClass('d-none').addClass('disabled');
                $('#pois_color').removeClass('disabled');
                $('#pois_background').removeClass('disabled');
                break;
        }
        $('.poi_final_preview').addClass('d-none');
        $('#poi_'+style).removeClass('d-none');
    }

    window.change_marker_style = function() {
        var show_room = parseInt($('#marker_style option:selected').attr('id'));
        switch(show_room) {
            case 3:
                $('#marker_library_icon').parent().parent().addClass('disabled').hide();
                $('#marker_icon').parent().parent().addClass('disabled').show();
                $('#marker_color').parent().parent().removeClass('disabled');
                $('#marker_background').parent().parent().removeClass('disabled');
                window.markers[window.marker_index_edit].show_room = show_room;
                render_marker(window.marker_id_edit,window.marker_index_edit);
                break;
            case 4:
                $('#marker_icon').parent().parent().addClass('disabled').hide();
                $('#marker_color').parent().parent().addClass('disabled');
                $('#marker_background').parent().parent().addClass('disabled');
                $('#marker_library_icon').parent().parent().removeClass('disabled').show();
                var id_icon_library = parseInt($('#marker_library_icon').val());
                if(id_icon_library!=0) {
                    render_marker(window.marker_id_edit,window.marker_index_edit);
                } else {
                    $('#marker_library_icon_preview').hide();
                    $('#marker_library_icon_preview_l').hide();
                }
                break;
            case 5:
                $('#marker_library_icon').parent().parent().addClass('disabled').hide();
                $('#marker_icon').parent().parent().addClass('disabled').show();
                $('#marker_color').parent().parent().addClass('disabled');
                $('#marker_background').parent().parent().addClass('disabled');
                window.markers[window.marker_index_edit].show_room = show_room;
                render_marker(window.marker_id_edit,window.marker_index_edit);
                break;
            default:
                $('#marker_library_icon').parent().parent().addClass('disabled').hide();
                $('#marker_icon').parent().parent().removeClass('disabled').show();
                $('#marker_color').parent().parent().removeClass('disabled');
                $('#marker_background').parent().parent().removeClass('disabled');
                window.markers[window.marker_index_edit].show_room = show_room;
                render_marker(window.marker_id_edit,window.marker_index_edit);
                break;
        }
    }

    window.change_poi_style = function() {
        var style = parseInt($('#poi_style option:selected').attr('id'));
        switch(style) {
            case 1:
                $('#poi_icon').parent().parent().addClass('disabled').hide();
                $('#poi_color').parent().parent().addClass('disabled');
                $('#poi_background').parent().parent().addClass('disabled');
                $('#poi_library_icon').parent().parent().removeClass('disabled').show();
                var id_icon_library = parseInt($('#poi_library_icon').val());
                if(id_icon_library!=0) {
                    render_poi(window.poi_id_edit,window.poi_index_edit);
                } else {
                    $('#poi_library_icon_preview').hide();
                    $('#poi_library_icon_preview_l').hide();
                }
                break;
            case 4:
                $('#poi_library_icon').parent().parent().addClass('disabled').hide();
                $('#poi_icon').parent().parent().addClass('disabled').show();
                $('#poi_color').parent().parent().removeClass('disabled');
                $('#poi_background').parent().parent().removeClass('disabled');
                window.pois[window.poi_index_edit].style = style;
                render_poi(window.poi_id_edit,window.poi_index_edit);
                break;
            default:
                $('#poi_library_icon').parent().parent().addClass('disabled').hide();
                $('#poi_icon').parent().parent().removeClass('disabled').show();
                $('#poi_color').parent().parent().removeClass('disabled');
                $('#poi_background').parent().parent().removeClass('disabled');
                window.pois[window.poi_index_edit].style = style;
                render_poi(window.poi_id_edit,window.poi_index_edit);
                break;
        }
        if((style==0) || (style==1)) {
            $('#poi_label').prop('disabled',true);
        } else {
            $('#poi_label').prop('disabled',false);
        }
    }

    window.change_poi_animation = function () {
        var animation = $('#poi_animation option:selected').attr('id');
        window.pois[window.poi_index_edit].animation = animation;
        render_poi(window.poi_id_edit,window.poi_index_edit);
    }

    window.change_marker_animation = function () {
        var animation = $('#marker_animation option:selected').attr('id');
        window.markers[window.marker_index_edit].animation = animation;
        render_marker(window.marker_id_edit,window.marker_index_edit);
    }

    window.change_tooltip_type_m = function () {
        var tooltip_type = $('#tooltip_type option:selected').attr('id');
        if(tooltip_type=='text') {
            $('#tooltip_text').prop('disabled',false);
        } else {
            $('#tooltip_text').prop('disabled',true);
        }
    }

    window.change_tooltip_type_p = function () {
        var tooltip_type = $('#tooltip_type option:selected').attr('id');
        if(tooltip_type=='text') {
            $('#tooltip_text_html').removeClass('disabled');
        } else {
            $('#tooltip_text_html').addClass('disabled');
        }
    }

    window.select_icon_library = function(p,id,image,base64) {
        switch(p){
            case 'marker':
                window.markers[window.marker_index_edit].show_room = 4;
                window.markers[window.marker_index_edit].img_icon_library = image;
                window.markers[window.marker_index_edit].base64_icon_library = base64;
                $('#marker_library_icon').val(id);
                var ext = image.split('.').pop().toLowerCase();
                if(ext=='json') {
                    var html_image = '<div id="lottie_preview_'+window.marker_id_edit+'" style="height:30px;width:30px;vertical-align:middle"></div>';
                    $('#marker_library_icon_preview_l').html(html_image).promise().done(function () {
                        $('#marker_library_icon_preview_l').css('display','inline-block');
                        bodymovin.loadAnimation({
                            container: document.getElementById('lottie_preview_'+window.marker_id_edit),
                            renderer: 'svg',
                            loop: true,
                            autoplay: true,
                            path: '../viewer/icons/'+image,
                            rendererSettings: {
                                progressiveLoad: true,
                            }
                        });
                    });
                    $('#marker_library_icon_preview').hide();
                } else {
                    $('#marker_library_icon_preview').attr('src','../viewer/icons/'+image);
                    $('#marker_library_icon_preview').show();
                    $('#marker_library_icon_preview_l').hide();
                }
                render_marker(window.marker_id_edit,window.marker_index_edit);
                break;
            case 'poi':
                window.pois[window.poi_index_edit].style = 1;
                window.pois[window.poi_index_edit].img_icon_library = image;
                window.pois[window.poi_index_edit].base64_icon_library = base64;
                $('#poi_library_icon').val(id);
                var ext = image.split('.').pop().toLowerCase();
                if(ext=='json') {
                    var html_image = '<div id="lottie_preview_'+window.poi_id_edit+'" style="height:30px;width:30px;vertical-align:middle"></div>';
                    $('#poi_library_icon_preview_l').html(html_image).promise().done(function () {
                        $('#poi_library_icon_preview_l').css('display','inline-block');
                        bodymovin.loadAnimation({
                            container: document.getElementById('lottie_preview_'+window.poi_id_edit),
                            renderer: 'svg',
                            loop: true,
                            autoplay: true,
                            path: '../viewer/icons/'+image,
                            rendererSettings: {
                                progressiveLoad: true,
                            }
                        });
                    });
                    $('#poi_library_icon_preview').hide();
                } else {
                    $('#poi_library_icon_preview').attr('src','../viewer/icons/'+image);
                    $('#poi_library_icon_preview').show();
                    $('#poi_library_icon_preview_l').hide();
                }
                render_poi(window.poi_id_edit,window.poi_index_edit);
                break;
        }
        $('#modal_library_icons').modal('hide');
    }

    window.select_icon_library_v = function(p,id,image) {
        switch(p){
            case 'marker':
                $('#marker_library_icon').val(id);
                var ext = image.split('.').pop().toLowerCase();
                if(ext=='json') {
                    var html_image = '<div id="lottie_preview_m_'+id+'" style="height:30px;width:30px;vertical-align:middle"></div>';
                    $('#marker_library_icon_preview_l').html(html_image).promise().done(function () {
                        $('#marker_library_icon_preview_l').css('display','inline-block');
                        bodymovin.loadAnimation({
                            container: document.getElementById('lottie_preview_m_'+id),
                            renderer: 'svg',
                            loop: true,
                            autoplay: true,
                            path: '../viewer/icons/'+image,
                            rendererSettings: {
                                progressiveLoad: true,
                            }
                        });
                    });
                    var html_image = '<div id="lottie_preview_m" style="height:50px;width:50px;vertical-align:middle"></div>';
                    $('#marker_lottie_pewview').html(html_image).promise().done(function () {
                        $('#marker_lottie_pewview').css('display','inline-block');
                        bodymovin.loadAnimation({
                            container: document.getElementById('lottie_preview_m'),
                            renderer: 'svg',
                            loop: true,
                            autoplay: true,
                            path: '../viewer/icons/'+image,
                            rendererSettings: {
                                progressiveLoad: true,
                            }
                        });
                    });
                    $('#marker_lottie_pewview').removeClass('d-none');
                    $('#marker_custom_icon img').addClass('d-none');
                    $('#marker_library_icon_preview').hide();
                } else {
                    $('#marker_library_icon_preview').attr('src','../viewer/icons/'+image);
                    $('#marker_custom_icon img').attr('src','../viewer/icons/'+image);
                    $('#marker_lottie_pewview').addClass('d-none');
                    $('#marker_custom_icon img').removeClass('d-none');
                    $('#marker_library_icon_preview').show();
                    $('#marker_library_icon_preview_l').hide();
                }
                $('#modal_library_icons_markers').modal('hide');
                break;
            case 'poi':
                $('#poi_library_icon').val(id);
                var ext = image.split('.').pop().toLowerCase();
                if(ext=='json') {
                    var html_image = '<div id="lottie_preview_p_'+id+'" style="height:30px;width:30px;vertical-align:middle"></div>';
                    $('#poi_library_icon_preview_l').html(html_image).promise().done(function () {
                        $('#poi_library_icon_preview_l').css('display','inline-block');
                        bodymovin.loadAnimation({
                            container: document.getElementById('lottie_preview_p_'+id),
                            renderer: 'svg',
                            loop: true,
                            autoplay: true,
                            path: '../viewer/icons/'+image,
                            rendererSettings: {
                                progressiveLoad: true,
                            }
                        });
                        var html_image = '<div id="lottie_preview_p" style="height:50px;width:50px;vertical-align:middle"></div>';
                        $('#poi_lottie_pewview').html(html_image).promise().done(function () {
                            $('#poi_lottie_pewview').css('display','inline-block');
                            bodymovin.loadAnimation({
                                container: document.getElementById('lottie_preview_p'),
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
                    $('#poi_lottie_pewview').removeClass('d-none');
                    $('#poi_custom_icon img').addClass('d-none');
                    $('#poi_library_icon_preview').hide();
                } else {
                    $('#poi_library_icon_preview').attr('src','../viewer/icons/'+image);
                    $('#poi_custom_icon img').attr('src','../viewer/icons/'+image);
                    $('#poi_lottie_pewview').addClass('d-none');
                    $('#poi_custom_icon img').removeClass('d-none');
                    $('#poi_library_icon_preview').show();
                    $('#poi_library_icon_preview_l').hide();
                }
                $('#modal_library_icons_pois').modal('hide');
                break;
        }
    }

    window.render_marker = function(id,index) {
        if(window.markers[index].embed_type!='') {
            window.viewer.removeHotSpot("m"+id.toString());
            window.viewer.removeHotSpot("m"+id.toString()+"_1");
            window.viewer.removeHotSpot("m"+id.toString()+"_2");
            window.viewer.removeHotSpot("m"+id.toString()+"_3");
            window.viewer.removeHotSpot("m"+id.toString()+"_4");
            window.viewer.removeHotSpot("m"+id.toString()+"_move");
            window.viewer.addHotSpot({
                "id": "m"+window.markers[index].id,
                "type": window.markers[index].embed_type,
                "object": "marker_embed",
                "transform3d": parseInt(window.markers[index].transform3d),
                "tooltip_type": "",
                "pitch": parseFloat(window.markers[index].pitch),
                "yaw": parseFloat(window.markers[index].yaw),
                "rotateX": 0,
                "rotateZ": 0,
                "size_scale": 1,
                "cssClass": "hotspot-embed",
                "createTooltipFunc": hotspot_embed_m,
                "createTooltipArgs": window.markers[index],
                "clickHandlerFunc": click_edit_marker,
                "clickHandlerArgs": window.markers[index].id
            });
            var marker_embed_helpers = window.markers[index].embed_coords.split("|");
            marker_embed_helpers[0] = marker_embed_helpers[0].split(",");
            marker_embed_helpers[1] = marker_embed_helpers[1].split(",");
            marker_embed_helpers[2] = marker_embed_helpers[2].split(",");
            marker_embed_helpers[3] = marker_embed_helpers[3].split(",");
            jQuery.each(marker_embed_helpers, function(index_h, marker_embed_helper) {
                window.viewer.addHotSpot({
                    "id": "m"+window.markers[index].id+"_"+(index_h+1),
                    "type": 'pointer',
                    "object": "marker_embed_helper",
                    "transform3d": false,
                    "pitch": parseFloat(marker_embed_helper[0]),
                    "yaw": parseFloat(marker_embed_helper[1]),
                    "size_scale": 1,
                    "rotateX": 0,
                    "rotateZ": 0,
                    "draggable": true,
                    "cssClass": "hotspot-helper",
                    "createTooltipFunc": hotspot_embed_helper_m,
                    "createTooltipArgs": [window.markers[index].id,(index_h+1)],
                    "dragHandlerFunc": drag_embed_helper_m,
                    "dragHandlerArgs": [window.markers[index],index_h]
                });
            });
            window.viewer.addHotSpot({
                "id": "m"+window.markers[index].id+"_move",
                "type": 'pointer',
                "object": "marker_embed_helper",
                "transform3d": false,
                "pitch": parseFloat(window.markers[index].pitch),
                "yaw": parseFloat(window.markers[index].yaw),
                "size_scale": 1,
                "rotateX": 0,
                "rotateZ": 0,
                "draggable": true,
                "cssClass": "hotspot-helper",
                "createTooltipFunc": hotspot_embed_move_m,
                "createTooltipArgs": window.markers[index].id,
                "dragHandlerFunc": drag_embed_move_m,
                "dragHandlerArgs": [window.markers[index].id,index]
            });
            init_marker_embed();
        } else {
            window.viewer.removeHotSpot(id.toString());
            window.viewer.addHotSpot({
                "id": window.markers[index].id,
                "type": 'marker',
                "transform3d": false,
                "draggable": false,
                "pitch": parseFloat(window.markers[index].pitch),
                "yaw": parseFloat(window.markers[index].yaw),
                "rotateX": parseInt(window.markers[index].rotateX),
                "rotateZ": parseInt(window.markers[index].rotateZ),
                "size_scale": parseFloat(window.markers[index].size_scale),
                "cssClass": "custom-hotspot",
                "createTooltipFunc": hotspot_m,
                "createTooltipArgs": window.markers[index],
                "clickHandlerFunc": click_edit_marker,
                "clickHandlerArgs": parseInt(window.markers[index].id)
            });
        }
    }

    window.render_poi = function(id,index) {
        window.viewer.removeHotSpot(id.toString());
        window.viewer.removeHotSpot("p"+id.toString());
        window.viewer.removeHotSpot("p"+id.toString()+"_1");
        window.viewer.removeHotSpot("p"+id.toString()+"_2");
        window.viewer.removeHotSpot("p"+id.toString()+"_3");
        window.viewer.removeHotSpot("p"+id.toString()+"_4");
        window.viewer.removeHotSpot("p"+id.toString()+"_move");
        if(window.pois[index].embed_type!='' && window.pois[index].transform3d==1) {
            window.viewer.addHotSpot({
                "id": "p"+window.pois[index].id,
                "type": window.pois[index].embed_type,
                "object": "poi_embed",
                "transform3d": parseInt(window.pois[index].transform3d),
                "tooltip_type": "",
                "pitch": parseFloat(window.pois[index].pitch),
                "yaw": parseFloat(window.pois[index].yaw),
                "rotateX": 0,
                "rotateZ": 0,
                "size_scale": 1,
                "cssClass": "hotspot-embed",
                "createTooltipFunc": hotspot_embed,
                "createTooltipArgs": window.pois[index],
                "clickHandlerFunc": click_edit_poi,
                "clickHandlerArgs": window.pois[index].id
            });
            var poi_embed_helpers = window.pois[index].embed_coords.split("|");
            poi_embed_helpers[0] = poi_embed_helpers[0].split(",");
            poi_embed_helpers[1] = poi_embed_helpers[1].split(",");
            poi_embed_helpers[2] = poi_embed_helpers[2].split(",");
            poi_embed_helpers[3] = poi_embed_helpers[3].split(",");
            jQuery.each(poi_embed_helpers, function(index_h, poi_embed_helper) {
                window.viewer.addHotSpot({
                    "id": "p"+window.pois[index].id+"_"+(index_h+1),
                    "type": 'pointer',
                    "object": "poi_embed_helper",
                    "transform3d": false,
                    "pitch": parseFloat(poi_embed_helper[0]),
                    "yaw": parseFloat(poi_embed_helper[1]),
                    "size_scale": 1,
                    "rotateX": 0,
                    "rotateZ": 0,
                    "draggable": true,
                    "cssClass": "hotspot-helper",
                    "createTooltipFunc": hotspot_embed_helper,
                    "createTooltipArgs": [window.pois[index].id,(index_h+1)],
                    "dragHandlerFunc": drag_embed_helper,
                    "dragHandlerArgs": [window.pois[index],index_h]
                });
            });
            window.viewer.addHotSpot({
                "id": "p"+window.pois[index].id+"_move",
                "type": 'pointer',
                "object": "poi_embed_helper",
                "transform3d": false,
                "pitch": parseFloat(window.pois[index].pitch),
                "yaw": parseFloat(window.pois[index].yaw),
                "size_scale": 1,
                "rotateX": 0,
                "rotateZ": 0,
                "draggable": true,
                "cssClass": "hotspot-helper",
                "createTooltipFunc": hotspot_embed_move,
                "createTooltipArgs": window.pois[index].id,
                "dragHandlerFunc": drag_embed_move,
                "dragHandlerArgs": [window.pois[index].id,index]
            });
            init_poi_embed();
        } else {
            if(window.pois[index].embed_type!='' && window.pois[index].transform3d==0) {
                window.viewer.addHotSpot({
                    "id": "p"+window.pois[index].id,
                    "type": window.pois[index].embed_type,
                    "object": "poi_embed",
                    "transform3d": parseInt(window.pois[index].transform3d),
                    "pitch": parseFloat(window.pois[index].pitch),
                    "yaw": parseFloat(window.pois[index].yaw),
                    "rotateX": parseInt(window.pois[index].rotateX),
                    "rotateZ": parseInt(window.pois[index].rotateZ),
                    "size_scale": parseFloat(window.pois[index].size_scale),
                    "cssClass": "hotspot-embed",
                    "createTooltipFunc": hotspot_embed,
                    "createTooltipArgs": window.pois[index],
                    "clickHandlerFunc": click_edit_poi,
                    "clickHandlerArgs": window.pois[index].id
                });
                adjust_poi_embed_helpers_all();
            } else {
                window.viewer.addHotSpot({
                    "id": window.pois[index].id,
                    "type": 'poi',
                    "transform3d": false,
                    "pitch": parseFloat(window.pois[index].pitch),
                    "yaw": parseFloat(window.pois[index].yaw),
                    "rotateX": parseInt(window.pois[index].rotateX),
                    "rotateZ": parseInt(window.pois[index].rotateZ),
                    "size_scale": parseFloat(window.pois[index].size_scale),
                    "cssClass": "custom-hotspot-content",
                    "createTooltipFunc": hotspot_p,
                    "createTooltipArgs": window.pois[index],
                    "clickHandlerFunc": click_edit_poi,
                    "clickHandlerArgs": window.pois[index].id
                });
            }
        }
    }

    window.render_poi_move = function(id,index) {
        window.viewer.removeHotSpot(id.toString());
        window.viewer.removeHotSpot("p"+id.toString());
        window.viewer.removeHotSpot("p"+id.toString()+"_1");
        window.viewer.removeHotSpot("p"+id.toString()+"_2");
        window.viewer.removeHotSpot("p"+id.toString()+"_3");
        window.viewer.removeHotSpot("p"+id.toString()+"_4");
        window.viewer.removeHotSpot("p"+id.toString()+"_move");
        if(window.pois[index].transform3d==1) {
            window.viewer.addHotSpot({
                "id": "p"+window.pois[index].id,
                "type": window.pois[index].embed_type,
                "object": "poi_embed",
                "transform3d": parseInt(window.pois[index].transform3d),
                "tooltip_type": "",
                "pitch": parseFloat(window.pois[index].pitch),
                "yaw": parseFloat(window.pois[index].yaw),
                "rotateX": 0,
                "rotateZ": 0,
                "size_scale": 1,
                "cssClass": "hotspot-embed",
                "createTooltipFunc": hotspot_embed,
                "createTooltipArgs": window.pois[index]
            });
            var poi_embed_helpers = window.pois[index].embed_coords.split("|");
            poi_embed_helpers[0] = poi_embed_helpers[0].split(",");
            poi_embed_helpers[1] = poi_embed_helpers[1].split(",");
            poi_embed_helpers[2] = poi_embed_helpers[2].split(",");
            poi_embed_helpers[3] = poi_embed_helpers[3].split(",");
            jQuery.each(poi_embed_helpers, function(index_h, poi_embed_helper) {
                window.viewer.addHotSpot({
                    "id": "p"+window.pois[index].id+"_"+(index_h+1),
                    "type": 'pointer',
                    "object": "poi_embed_helper",
                    "transform3d": false,
                    "pitch": parseFloat(poi_embed_helper[0]),
                    "yaw": parseFloat(poi_embed_helper[1]),
                    "size_scale": 1,
                    "rotateX": 0,
                    "rotateZ": 0,
                    "draggable": true,
                    "cssClass": "hotspot-helper",
                    "createTooltipFunc": hotspot_embed_helper,
                    "createTooltipArgs": [window.pois[index].id,(index_h+1)],
                    "dragHandlerFunc": drag_embed_helper,
                    "dragHandlerArgs": [window.pois[index],index_h]
                });
            });
            window.viewer.addHotSpot({
                "id": "p"+window.pois[index].id+"_move",
                "type": 'pointer',
                "object": "poi_embed_helper",
                "transform3d": false,
                "pitch": parseFloat(window.pois[index].pitch),
                "yaw": parseFloat(window.pois[index].yaw),
                "size_scale": 1,
                "rotateX": 0,
                "rotateZ": 0,
                "draggable": true,
                "cssClass": "hotspot-helper",
                "createTooltipFunc": hotspot_embed_move,
                "createTooltipArgs": window.pois[index].id,
                "dragHandlerFunc": drag_embed_move,
                "dragHandlerArgs": [window.pois[index].id,index]
            });
        } else {
            window.viewer.addHotSpot({
                "id": "p"+window.pois[index].id,
                "type": window.pois[index].embed_type,
                "object": "poi_embed",
                "draggable": true,
                "transform3d": parseInt(window.pois[index].transform3d),
                "pitch": parseFloat(window.pois[index].pitch),
                "yaw": parseFloat(window.pois[index].yaw),
                "rotateX": parseInt(window.pois[index].rotateX),
                "rotateZ": parseInt(window.pois[index].rotateZ),
                "size_scale": parseFloat(window.pois[index].size_scale),
                "cssClass": "hotspot-embed",
                "createTooltipFunc": hotspot_embed,
                "createTooltipArgs": window.pois[index],
                "dragHandlerFunc": drag_poi_move,
                "dragHandlerArgs": index
            });
        }
        init_poi_embed();
    }

    window.get_option_rooms_target = function(id_select,id,id_room_target,id_marker,index_marker) {
        var html_option_rooms_target = "";
        var first_id_room = null;
        if(id_select=='room_default') {
            html_option_rooms_target += '<option id="0">'+window.backend_labels.none+'</option>';
        }
        jQuery.each(window.rooms, function(index, room) {
            var id_room = room.id;
            var name = room.name;
            if(id!=id_room) {
                if(first_id_room==null) {
                    first_id_room = id_room;
                }
                if(id_room==id_room_target) {
                    html_option_rooms_target += '<option selected id="'+id_room+'">'+name+'</option>';
                } else {
                    html_option_rooms_target += '<option id="'+id_room+'">'+name+'</option>';
                }
                return;
            }
        });
        $('#'+id_select).empty();
        $('#'+id_select).html(html_option_rooms_target).promise().done(function () {
            switch(id_select) {
                case 'room_default':
                    break;
                case 'room_target_add':
                    $('#'+id_select).attr('onchange','change_marker_room_target('+id_marker+','+index_marker+',\''+id_select+'\')');
                    if(id_room_target==0) {
                        change_preview_room_image(first_id_room,id_select,index_marker);
                    } else {
                        change_preview_room_image(id_room_target,id_select,index_marker);
                    }
                    break;
                default:
                    $('#'+id_select).attr('onchange','change_marker_room_target('+id_marker+','+index_marker+',\''+id_select+'\')');
                    change_preview_room_image(id_room_target,id_select,index_marker);
                    break;
            }
            $('#'+id_select).selectpicker('refresh');
            var val = $('#'+id_select).selectpicker('val');
            $('#'+id_select).selectpicker('val', val);
        });
    }

    window.get_option_rooms_duplicate = function(id_select,id) {
        var html_option_rooms_target = "";
        jQuery.each(window.rooms, function(index, room) {
            var id_room = room.id;
            var name = room.name;
            var panorama_image = room.panorama_image;
            if(id_room==id) {
                html_option_rooms_target += '<option data-image="'+panorama_image+'" selected id="'+id_room+'">'+name+'</option>';
            } else {
                html_option_rooms_target += '<option data-image="'+panorama_image+'" id="' + id_room + '">' + name + '</option>';
            }
        });
        $('#'+id_select).empty();
        $('#'+id_select).html(html_option_rooms_target).promise().done(function () {
            $('#'+id_select).selectpicker('refresh');
            var val = $('#'+id_select).selectpicker('val');
            $('#'+id_select).selectpicker('val', val);
        });
    }

    window.set_room_target_map = function(id) {
        jQuery.each(window.map_points, function(index, point) {
            var id_room = point.id;
            var name = point.name;
            if(id_room==id) {
                $('#room_target').val(name);
                $('#preview_room_image_map').attr('src','../viewer/panoramas/thumb/'+point.panorama_image);
            }
        });
    }

    window.adjust_points_size = function () {
        var point_size = $('#point_size').val();
        if((point_size=='') || (point_size<=0)) {
            point_size=20;
        }
        point_size = parseInt(point_size);
        switch (window.map_type) {
            case 'floorplan':
                $('.pointer').css('width',point_size+'px');
                $('.pointer').css('height',point_size+'px');
                break;
            case 'map':
                point_size = point_size*2;
                $.each(window.map_tour_l._marker, function (ml) {
                    var icon = ml.options.icon;
                    icon.options.iconSize = [point_size, point_size];
                    icon.options.iconAnchor = [(point_size/2), (point_size/2)];
                    ml.setIcon(icon);
                });
                $('.map_tour_icon').css('width',point_size+'px');
                $('.map_tour_icon').css('height',point_size+'px');
                break;
        }
    }

    window.adjust_points_position = function () {
        var image_w = $('#map_image').width();
        var image_h = $('#map_image').height();
        $('#pointers_div').css('width',image_w+'px');
        $('#pointers_div').css('height',image_h+'px');
        var ratio = image_w / image_h;
        var ratio_w = image_w / 300;
        var ratio_h = image_h / ((image_w / ratio_w) / ratio);
        window.ratio_w = ratio_w;
        window.ratio_h = ratio_h;
        jQuery.each(window.map_points, function(index, point) {
            var id_point = point.id;
            var map_left = point.map_left;
            var map_top = point.map_top;
            var pos_left = map_left * ratio_w;
            var pos_top = map_top * ratio_h;
            $('#pointer_'+id_point).css('transform','scale('+ratio_w+')');
            $('#pointer_'+id_point).css('top',pos_top+'px');
            $('#pointer_'+id_point).css('left',pos_left+'px');
        });
    }

    window.adjust_marker_position = function () {
        var image_w = $('#panorama_image').width();
        var image_h = $('#panorama_image').height();
        $('#markers_div').css('width',image_w+'px');
        $('#markers_div').css('height',image_h+'px');
        var half_x = image_w / 2;
        var half_y = image_h / 2;
        var ratio_x = half_x / 180;
        var ratio_y = half_y / 90;
        if(image_w>=1100) {
            var scale = 0.7;
        } else if((image_w<1100) & (image_w>=500)) {
            var scale = 0.5;
        } else {
            var scale = 0.3;
        }
        jQuery.each(window.markers, function(index, marker) {
            var id_marker = marker.id;
            var yaw = marker.yaw;
            var pitch = marker.pitch * -1;
            var marker_w = $('#marker_'+id_marker).width();
            var marker_h = $('#marker_'+id_marker).height();
            var pos_left = ((yaw*ratio_x)+half_x)-(marker_w/1.77);
            var pos_top = ((pitch*ratio_y)+half_y)-(marker_h/1.77);
            $('#marker_'+id_marker).css('transform','translate('+pos_left+'px,'+pos_top+'px) scale('+scale+')');
        });
    }

    window.adjust_poi_position = function () {
        var image_w = $('#panorama_image').width();
        var image_h = $('#panorama_image').height();
        $('#markers_div').css('width',image_w+'px');
        $('#markers_div').css('height',image_h+'px');
        var half_x = image_w / 2;
        var half_y = image_h / 2;
        var ratio_x = half_x / 180;
        var ratio_y = half_y / 90;
        if(image_w>=1100) {
            var scale = 1;
        } else if((image_w<1100) & (image_w>=500)) {
            var scale = 0.6;
        } else {
            var scale = 0.3;
        }
        jQuery.each(window.pois, function(index, poi) {
            var id_poi = poi.id;
            var yaw = poi.yaw;
            var pitch = poi.pitch * -1;
            var poi_w = $('#poi_'+id_poi).width();
            var poi_h = $('#poi_'+id_poi).height();
            var pos_left = ((yaw*ratio_x)+half_x)-(poi_w/1.3);
            var pos_top = ((pitch*ratio_y)+half_y)-(poi_h/1.3);
            $('#poi_'+id_poi).css('transform','translate('+pos_left+'px,'+pos_top+'px) scale('+scale+')');
        });
    }

    window.persist_search = function (type) {
        sessionStorage.setItem('search_'+type,$('.search_input').val());
    }

    window.clear_search = function (type) {
        sessionStorage.setItem('search_'+type,'');
        $('.search_input').val('');
        $('.search_input').trigger('change');
    }

    window.filter_0_markers = function () {
        $('.search_input').val('0 '+window.backend_labels.markers);
        sessionStorage.setItem('search_room',$('.search_input').val());
        $('.search_input').trigger('change');
    }

    function parse_room_list(rooms,permissions) {
        var html = '', html_search = '';
        var search_val = sessionStorage.getItem('search_room');
        if(search_val==null) search_val='';
        if(rooms.length>0) {
            html_search = '<div class="row">\n' +
                '   <div class="col-lg-6 col-md-6 col-sm-12 col-12">\n' +
                '       <div class="input-group mb-3">\n' +
                '       <span class="input-group-prepend">\n' +
                '           <div class="input-group-text bg-white border-right-0"><i class="fa fa-search"></i></div>\n' +
                '       </span>\n' +
                '       <input onkeyup="persist_search(\'room\')" class="search_input form-control py-2" type="text" value="'+search_val+'" placeholder="'+window.backend_labels.search_room+'" autocomplete="new-password" >\n' +
                '       <span class="input-group-append">\n' +
                '           <div onclick="clear_search(\'room\');" style="cursor: pointer;" class="input-group-text bg-gray-300 border-left-0"><i class="fa fa-times"></i></div>\n' +
                '       </span>\n' +
                '   </div>\n' +
                '  </div>\n' +
                '   <div class="col-lg-4 col-md-4 col-sm-8 col-8">\n' +
                '      <button data-toggle="modal" data-target="#modal_list_alt" class="btn btn-block btn-outline-secondary mb-3"><i class="fas fa-layer-group"></i>&nbsp;&nbsp;'+window.backend_labels.rooms_list+'</button>\n' +
                '  </div>\n' +
                '   <div class="col-lg-2 col-md-2 col-sm-4 col-4">\n' +
                '       <input id="view_type_toggle" type="checkbox" checked data-toggle="toggle" data-width="100%" data-on="'+window.backend_labels.list+'" data-off="'+window.backend_labels.grid+'" data-onstyle="light" data-offstyle="light">\n' +
                '   </div>\n' +
                '</div>';
        }
        jQuery.each(rooms, function(index, room) {
            var id = room.id;
            var name = room.name;
            var category = room.category;
            var image = room.panorama_image;
            var thumb_image = room.thumb_image_url;
            var count_markers = room.count_markers;
            var count_pois = room.count_pois;
            if(category!='') {
                var category_add = '&nbsp;&nbsp;&nbsp;<i class="fas fa-layer-group"></i> '+category;
            } else {
                var category_add = "";
            }
            if(count_markers>0) {
                var badge_marker_style = 'badge-secondary';
            } else {
                var badge_marker_style = 'badge-light';
            }
            if(count_pois>0) {
                var badge_poi_style = 'badge-secondary';
            } else {
                var badge_poi_style = 'badge-light';
            }
            var type = room.type;
            switch (type) {
                case 'image':
                    if(parseInt(room.multires)) {
                        type = '<i id="type_'+id+'" style="font-size: 14px" class="fas fa-images"></i>';
                    } else {
                        type = '<i id="type_'+id+'" style="font-size: 14px" class="fas fa-image"></i>';
                    }
                    if(parseInt(room.multires_status)==1) {
                        type = '<i id="type_'+id+'" style="font-size: 14px" class="fas fa-spin fa-cog"></i>';
                    }
                    break;
                case 'video':
                    type = '<i id="type_'+id+'" style="font-size: 14px" class="fas fa-video"></i>';
                    break;
                case 'hls':
                    type = '<i id="type_'+id+'" style="font-size: 14px" class="fas fa-film"></i>';
                    break;
                case 'lottie':
                    type = '<i id="type_'+id+'" style="font-size: 14px" class="fab fa-deviantart"></i>';
                    break;
            }
            var btn_duplicate = '';
            if(parseInt(window.can_create)==1 && permissions['create']) {
                btn_duplicate = '<a title="'+window.backend_labels.duplicate.toUpperCase()+'" href="#" onclick="modal_duplicate_room('+id+');return false;" class="btn btn-secondary btn-circle">\n' +
                    '<i class="fas fa-copy"></i>\n' +
                    '</a>\n';
            }
            var btn_rename = '<i id="btn_view_rename_'+id+'" onclick="view_rename_room('+id+');" class="btn_view_rename fas fa-pen"></i>';
            btn_rename += '<i id="btn_save_rename_'+id+'" onclick="save_rename_room('+id+');" class="btn_save_rename fas fa-check d-none"></i>&nbsp;&nbsp;';
            btn_rename += '<i id="btn_close_rename_'+id+'" onclick="close_rename_room('+id+');" class="btn_close_rename fas fa-times d-none"></i>';
            var input_rename = '<input id="rename_input_'+id+'" class="rename_input d-none" type="text" value="'+name+'" />';
            html += '<div class="div_room card mb-2 py-2">\n' +
                '            <div id="room_list" class="card-body" style="padding-top: 0;padding-bottom: 0;">\n' +
                '                <div data-id="'+id+'" class="row room">\n' +
                '                    <div class="room_content col text-center text-sm-center text-md-left text-lg-left">\n' +
                '                        <div style="width: 70px;overflow: hidden;" class="d-inline-block align-middle"><img style="height: 40px;" src="'+thumb_image+'" /></div>\n' +
                '                        <div class="room_info d-inline-block ml-2 align-middle text-left">'+type+' <b class="room_name" id="room_name_'+id+'">'+name+'</b>'+input_rename+'&nbsp;&nbsp;'+btn_rename+'<br><i style="font-size:12px"><i class="fas fa-caret-square-up"></i> '+count_markers+' '+window.backend_labels.markers+'&nbsp;&nbsp;&nbsp;<i class="fas fa-bullseye"></i> '+count_pois+' '+window.backend_labels.pois+''+category_add+'</i></div>\n' +
                '                    </div>\n' +
                '                    <div class="room_buttons col-md-auto pt-1 text-center text-sm-center text-md-right text-lg-right">\n' +
                '                        <a title="'+window.backend_labels.drag_change_pos.toUpperCase()+'" style="background-color: white; border: 1px solid black; cursor: pointer" class="handle btn btn-primary btn-circle">\n' +
                '                           <i style="color: black" class="fas fa-arrows-alt"></i>\n' +
                '                        </a>\n' +
                '                        <a title="'+window.backend_labels.edit.toUpperCase()+'" href="index.php?p=edit_room&id='+id+'" class="btn btn-warning btn-circle">\n' +
                '                            <i class="fas fa-edit"></i>\n' +
                '                        </a>\n' +
                '                        <a title="'+window.backend_labels.markers.toUpperCase()+'" href="index.php?p=markers&id_room='+id+'" class="btn btn-info btn-circle position-relative">\n' +
                '                            <i class="fas fa-caret-square-up"></i>\n' +
                '                             <span style="top:-8px;right:-5px" class="badge badge-pill '+badge_marker_style+' position-absolute">'+count_markers+'</span>\n' +
                '                        </a>\n' +
                '                        <a title="'+window.backend_labels.pois.toUpperCase()+'" href="index.php?p=pois&id_room='+id+'" class="btn btn-info btn-circle position-relative">\n' +
                '                            <i class="fas fa-bullseye"></i>\n' +
                '                             <span style="top:-8px;right:-5px" class="badge badge-pill '+badge_poi_style+' position-absolute">'+count_pois+'</span>\n' +
                '                        </a>\n' + btn_duplicate +
                '                        <a title="'+window.backend_labels.delete.toUpperCase()+'" href="#" onclick="modal_delete_room('+id+');return false;" class="btn btn-danger btn-circle">\n' +
                '                            <i class="fas fa-trash"></i>\n' +
                '                        </a>\n' +
                '                    </div>\n' +
                '                </div>\n' +
                '            </div>\n' +
                '        </div>';
        });
        $('#rooms_list').html(html).promise().done(function () {
            $('#search_div').html(html_search).promise().done(function () {
                $('#view_type_toggle').bootstrapToggle();
                $('#view_type_toggle').change(function() {
                    var checked = $(this).prop('checked');
                    if(!checked) {
                        localStorage.setItem('room_view_type', 'grid');
                        $('.div_room').addClass('div_room_grid');
                        $('#rooms_list').css('margin','0 -5px');
                        $('#room_list .room .room_content').removeClass('text-lg-left').removeClass('text-md-left');
                        $('#room_list .room .room_buttons').removeClass('text-lg-right').removeClass('text-md-right');
                        $('#room_list .room .room_buttons a').addClass('btn-sm');
                        $('#room_list .room .room_content').addClass('col-12');
                        $('#room_list .room .room_buttons').addClass('col-12').addClass('mt-1');
                        $('#room_list .room .room_buttons').addClass('col-auto').removeClass('col-md-auto');
                        $('#room_list .room .room_content .room_info').removeClass('text-left').addClass('text-center');
                        $('#room_list .room .room_content .room_info').css('width','100%');
                    } else {
                        localStorage.setItem('room_view_type', 'list');
                        $('.div_room').removeClass('div_room_grid');
                        $('#rooms_list').css('margin','0');
                        $('#room_list .room .room_content').addClass('text-lg-left').addClass('text-md-left');
                        $('#room_list .room .room_buttons').addClass('text-lg-right').addClass('text-md-right');
                        $('#room_list .room .room_buttons a').removeClass('btn-sm');
                        $('#room_list .room .room_content').removeClass('col-12');
                        $('#room_list .room .room_buttons').removeClass('col-12').removeClass('mt-1');
                        $('#room_list .room .room_buttons').removeClass('col-auto').addClass('col-md-auto');
                        $('#room_list .room .room_content .room_info').addClass('text-left').removeClass('text-center');
                        $('#room_list .room .room_content .room_info').css('width','auto');
                    }
                });
                if ("room_view_type" in localStorage) {
                    var view_type = localStorage.getItem('room_view_type');
                    switch (view_type) {
                        case 'list':
                            $('#view_type_toggle').bootstrapToggle('on');
                            break;
                        case 'grid':
                            $('#view_type_toggle').bootstrapToggle('off');
                            break;
                    }
                }
                if(!permissions['edit']) {
                    $('#rooms_list .fa-edit').parent().hide();
                }
                if(!permissions['delete']) {
                    $('#rooms_list .fa-trash').parent().hide();
                }
                if(window.user_role=='editor') {
                    $('#rooms_list .fa-arrows-alt').parent().hide();
                }
                $('#rooms_list').searchable({
                    selector      : '.div_room',
                    childSelector : 'div',
                    searchField   : '.search_input',
                    searchType    : 'default',
                    clearOnLoad   : false
                });
                $('#rooms_list .btn').tooltipster({
                    delay: 10,
                    hideOnClick: true
                });
                var el = document.getElementById('rooms_list');
                Sortable.create(el,{
                    handle: '.handle',
                    filter: ".no_drag",
                    onMove: function (e) { return e.related.className.indexOf('no_drag') === -1;  },
                    onEnd: function (evt) {
                        var array_rooms_priority = [];
                        $('#rooms_list .room').each(function () {
                            var id = $(this).attr('data-id');
                            array_rooms_priority.push(id);
                        });
                        change_rooms_order(array_rooms_priority);
                    },
                });
            });
        });
    }

    window.view_rename_room = function(id) {
        $('#btn_view_rename_'+id).addClass('d-none');
        $('#btn_save_rename_'+id).removeClass('d-none');
        $('#btn_close_rename_'+id).removeClass('d-none');
        $('#rename_input_'+id).removeClass('d-none');
        $('#room_name_'+id).addClass('d-none');
        $('#type_'+id).addClass('d-none');
    }

    window.save_rename_room = function(id) {
        var name = $('#rename_input_'+id).val();
        if(name!='') {
            $('#room_name_'+id).html(name);
            $.ajax({
                url: "ajax/rename_room.php",
                type: "POST",
                data: {
                    id: id,
                    name: name
                },
                async: true,
                success: function (json) {
                    $('#btn_view_rename_'+id).removeClass('d-none');
                    $('#btn_save_rename_'+id).addClass('d-none');
                    $('#btn_close_rename_'+id).addClass('d-none');
                    $('#rename_input_'+id).addClass('d-none');
                    $('#room_name_'+id).removeClass('d-none');
                    $('#type_'+id).removeClass('d-none');
                }
            });
        }
    }

    window.close_rename_room = function(id) {
        $('#btn_view_rename_'+id).removeClass('d-none');
        $('#btn_save_rename_'+id).addClass('d-none');
        $('#btn_close_rename_'+id).addClass('d-none');
        $('#rename_input_'+id).addClass('d-none');
        $('#room_name_'+id).removeClass('d-none');
        $('#type_'+id).removeClass('d-none');
    }

    function parse_rooms_menu_list(menu_list) {
        var html = '<div class="col-md-12"><div class="dd"><ol class="dd-list">';
        jQuery.each(menu_list, function(index, item) {
            var id = item.id;
            var name = item.name;
            if(name.length>35) {
                name = name.substring(0, 35)+'...';
            }
            var type = item.type;
            var hide = item.hide;
            switch (type) {
                case 'room':
                    if(hide==0) {
                        html += '<li class="dd-item dd-nochildren" data-hide="0" data-type="room" data-id="'+id+'"><div class="dd-handle dd3-handle"></div><div class="dd3-content"><span style="opacity: 1.0"><i class="fas fa-fw fa-vector-square"></i> '+name+'</span> <i onclick="hide_menu_item(\''+id+'\')" style="float: right;cursor: pointer" class="fas fa-eye"></i></div></li>';
                    } else {
                        html += '<li class="dd-item dd-nochildren" data-hide="1" data-type="room" data-id="'+id+'"><div class="dd-handle dd3-handle"></div><div class="dd3-content"><span style="opacity: 0.5"><i class="fas fa-fw fa-vector-square"></i> '+name+'</span> <i onclick="show_menu_item(\''+id+'\')" style="float: right;cursor: pointer" class="fas fa-eye-slash"></i></div></li>';
                    }
                    break;
                case 'category':
                    html += '<li class="dd-item" data-cat="'+name+'" data-type="category" data-id="'+id+'"><div class="dd-handle dd3-handle"></div><div class="dd3-content"><i class="fas fa-stream" style="vertical-align: super;"></i> <div class="category_editable" style="width: 70%;padding-left: 5px;display: inline-block;background-color: white;border: 1px solid lightgray;" contenteditable="true">'+name+'</div> <i onclick="remove_menu_item(\''+id+'\')" style="float: right;cursor: pointer" class="fas fa-trash"></i></div>';
                    var childrens = item.childrens;
                    if(childrens.length > 0) {
                        html += '<ol class="dd-list">';
                        for(var i=0;i<childrens.length;i++) {
                            if(childrens[i]['name'].length>35) {
                                childrens[i]['name'] = childrens[i]['name'].substring(0, 35)+'...';
                            }
                            if(childrens[i]['hide']==0) {
                                html += '<li class="dd-item dd-nochildren" data-hide="0" data-type="room" data-id="'+childrens[i]['id']+'"><div class="dd-handle dd3-handle"></div><div class="dd3-content"><span style="opacity: 1.0"><i class="fas fa-fw fa-vector-square"></i> '+childrens[i]['name']+'</span> <i onclick="hide_menu_item(\''+childrens[i]['id']+'\')" style="float: right;cursor: pointer" class="fas fa-eye"></i></div></li>';
                            } else {
                                html += '<li class="dd-item dd-nochildren" data-hide="1" data-type="room" data-id="'+childrens[i]['id']+'"><div class="dd-handle dd3-handle"></div><div class="dd3-content"><span style="opacity: 0.5"><i class="fas fa-fw fa-vector-square"></i> '+childrens[i]['name']+'</span> <i onclick="show_menu_item(\''+childrens[i]['id']+'\')" style="float: right;cursor: pointer" class="fas fa-eye-slash"></i></div></li>';
                            }
                        }
                        html += '</ol>';
                    }
                    html += '</li>';
                    break;
            }
        });
        html += '</ol></div></div>';
        $('.list_div').html(html).promise().done(function () {
            $('.dd3-content div').on('input',function () {
                var cat = $(this).html();
                $(this).parent().parent().attr('data-cat',cat);
                $(this).parent().parent().data('cat',cat);
                save_list_alt();
            });
            $('.dd').nestable({
                scroll: false,
                maxDepth: 2,
                callback: function(l,e){
                    check_category_deletable();
                    save_list_alt();
                }
            });
            check_category_deletable();
            $('.add_cat_div').show();
        });
    }

    function check_category_deletable() {
        $(".dd-item[data-type='category']").each(function () {
            if($(this).find('ol').hasClass('dd-list')) {
                $(this).find('i.fa-trash').addClass('disabled');
            } else {
                $(this).find('i.fa-trash').removeClass('disabled');
            }
        });
    }

    function save_list_alt() {
        var list_alt = $('.dd').nestable('serialize');
        list_alt = JSON.stringify(list_alt);
        $.ajax({
            url: "ajax/save_list_alt.php",
            type: "POST",
            data: {
                id_virtualtour: window.id_virtualtour,
                list_alt: list_alt,
            },
            async: true,
            success: function (json) {

            }
        });
    }

    window.add_menu_list_cat = function () {
        var last_id_cat = $(".dd-item[data-type='category']").length;
        if(last_id_cat==0) {
            last_id_cat = 1;
        } else {
            last_id_cat++;
        }
        var add_cat = $('#add_cat').val();
        if(add_cat != '') {
            $(".dd > ol").append("<li class='dd-item' data-cat=\""+add_cat+"\" data-type='category' data-id='c"+last_id_cat+"'><div class='dd-handle dd3-handle'></div><div class='dd3-content'><i class=\"fas fa-stream\" style='vertical-align: super;'></i> <div class=\"category_editable\" style=\"width: 70%;padding-left: 5px;display: inline-block;background-color: white;border: 1px solid lightgray;\" contenteditable=\"true\">"+add_cat+"</div> <i onclick=\"remove_menu_item('c"+last_id_cat+"')\" style=\"float: right;cursor: pointer\" class=\"fas fa-trash\"></i></div></li>");
            $('.dd3-content div').off('input');
            $('.dd3-content div').on('input',function () {
                var cat = $(this).html();
                $(this).parent().parent().attr('data-cat',cat);
                $(this).parent().parent().data('cat',cat);
                save_list_alt();
            });
        }
        $('#add_cat').val('');
    }

    window.remove_menu_item = function(id) {
        $('.dd').nestable('remove', id);
        save_list_alt();
    }

    window.hide_menu_item = function(id) {
        $(".dd-item[data-id='"+id+"']").find('i.fa-eye').first().removeClass('fa-eye').addClass('fa-eye-slash');
        $(".dd-item[data-id='"+id+"']").find('span').first().css('opacity',0.5);
        $(".dd-item[data-id='"+id+"']").attr('data-hide','1');
        $(".dd-item[data-id='"+id+"']").data('hide','1');
        $(".dd-item[data-id='"+id+"']").find('i.fa-eye-slash').first().attr('onclick','show_menu_item(\''+id+'\')');
        save_list_alt();
    }

    window.show_menu_item = function(id) {
        $(".dd-item[data-id='"+id+"']").find('i.fa-eye-slash').first().removeClass('fa-eye-slash').addClass('fa-eye');
        $(".dd-item[data-id='"+id+"']").find('span').first().css('opacity',1.0);
        $(".dd-item[data-id='"+id+"']").attr('data-hide','0');
        $(".dd-item[data-id='"+id+"']").data('hide','0');
        $(".dd-item[data-id='"+id+"']").find('i.fa-eye').first().attr('onclick','hide_menu_item(\''+id+'\')');
        save_list_alt();
    }

    window.change_room_type = function() {
        var type = $('#type_pano option:selected').attr('id');
        switch (type) {
            case 'image':
                $('#hls_div').hide();
                $('#lottie_div').hide();
                $('#label_panorama_type').html(window.backend_labels.panorama_image);
                $('#room_upload_div #msg_accept_files').html('<i>'+window.backend_labels.panorama_image_msg+'</i>');
                $('#frm').attr('action', 'ajax/upload_room_image.php');
                break;
            case 'video':
                $('#hls_div').hide();
                $('#lottie_div').hide();
                $('#label_panorama_type').html(window.backend_labels.panorama_video);
                $('#room_upload_div #msg_accept_files').html('<i>'+window.backend_labels.panorama_video_msg+'</i>');
                $('#frm').attr('action', 'ajax/upload_room_video.php');
                break;
            case 'hls':
                $('#hls_div').show();
                $('#lottie_div').hide();
                $('#label_panorama_type').html(window.backend_labels.panorama_hls);
                $('#room_upload_div #msg_accept_files').html('<i>'+window.backend_labels.panorama_hls_msg+'</i>');
                $('#frm').attr('action', 'ajax/upload_room_image.php');
                break;
            case 'lottie':
                $('#hls_div').hide();
                $('#lottie_div').show();
                $('#label_panorama_type').html(window.backend_labels.panorama_lottie);
                $('#room_upload_div #msg_accept_files').html('<i>'+window.backend_labels.panorama_lottie_msg+'</i>');
                $('#frm').attr('action', 'ajax/upload_room_image.php');
                break;
        }
        $('#frm')[0].reset();
        $('#preview_lottie').html('');
        $('#preview_image img').attr('src','');
        $('#preview_image').hide();
        $('#btn_create_room').prop("disabled",true);
    }

    window.add_showcase = function () {
        var complete = true;
        var name = $('#name').val();
        if(name=='') {
            complete = false;
            $('#name').addClass("error-highlight");
        } else {
            $('#name').removeClass("error-highlight");
        }
        if(complete) {
            $('#modal_new_showcase button').addClass("disabled");
            $.ajax({
                url: "ajax/add_showcase.php",
                type: "POST",
                data: {
                    name: name
                },
                async: true,
                success: function (json) {
                    var rsp = JSON.parse(json);
                    if (rsp.status == "ok") {
                        $('#modal_new_showcase button').removeClass("disabled");
                        $('#modal_new_showcase').modal("hide");
                        location.href='index.php?p=edit_showcase&id='+rsp.id;
                    } else {
                        alert(rsp.msg);
                        $('#modal_new_showcase button').removeClass("disabled");
                    }
                }
            });
        }
    };

    window.add_product = function () {
        var complete = true;
        var name = $('#name').val();
        var price = $('#price').val();
        if(name=='') {
            complete = false;
            $('#name').addClass("error-highlight");
        } else {
            $('#name').removeClass("error-highlight");
        }
        if(complete) {
            $('#modal_new_product button').addClass("disabled");
            $.ajax({
                url: "ajax/add_product.php",
                type: "POST",
                data: {
                    id_virtualtour: window.id_virtualtour,
                    name: name,
                    price: price
                },
                async: true,
                success: function (json) {
                    var rsp = JSON.parse(json);
                    if (rsp.status == "ok") {
                        $('#modal_new_product button').removeClass("disabled");
                        $('#modal_new_product').modal("hide");
                        location.href='index.php?p=edit_product&id='+rsp.id;
                    } else {
                        alert(rsp.msg);
                        $('#modal_new_product button').removeClass("disabled");
                    }
                }
            });
        }
    };

    window.add_advertisement = function () {
        var complete = true;
        var name = $('#name').val();
        if(name=='') {
            complete = false;
            $('#name').addClass("error-highlight");
        } else {
            $('#name').removeClass("error-highlight");
        }
        if(complete) {
            $('#modal_new_advertisement button').addClass("disabled");
            $.ajax({
                url: "ajax/add_announce.php",
                type: "POST",
                data: {
                    name: name
                },
                async: true,
                success: function (json) {
                    var rsp = JSON.parse(json);
                    if (rsp.status == "ok") {
                        $('#modal_new_advertisement button').removeClass("disabled");
                        $('#modal_new_advertisement').modal("hide");
                        location.href='index.php?p=edit_advertisement&id='+rsp.id;
                    } else {
                        alert(rsp.msg);
                        $('#modal_new_advertisement button').removeClass("disabled");
                    }
                }
            });
        }
    };

    window.add_user = function () {
        var complete = true;
        var username = $('#username').val();
        var email = $('#email').val();
        var role = $('#role option:selected').attr('id');
        var password = $('#password').val();
        var password2 = $('#repeat_password').val();
        if(username=='') {
            complete = false;
            $('#username').addClass("error-highlight");
        } else {
            $('#username').removeClass("error-highlight");
        }
        if(email=='') {
            complete = false;
            $('#email').addClass("error-highlight");
        } else {
            $('#email').removeClass("error-highlight");
        }
        if(password=='') {
            complete = false;
            $('#password').addClass("error-highlight");
        } else {
            $('#password').removeClass("error-highlight");
        }
        if(password2=='') {
            complete = false;
            $('#repeat_password').addClass("error-highlight");
        } else {
            $('#repeat_password').removeClass("error-highlight");
        }
        if((password!='') && (password2!='')) {
            if(password!=password2) {
                complete = false;
                $('#password').addClass("error-highlight");
                $('#repeat_password').addClass("error-highlight");
            } else {
                $('#password').removeClass("error-highlight");
                $('#repeat_password').removeClass("error-highlight");
            }
        }
        if(complete) {
            $('#modal_new_user button').addClass("disabled");
            $.ajax({
                url: "ajax/add_user.php",
                type: "POST",
                data: {
                    username_svt: username,
                    email_svt: email,
                    password_svt: password,
                    role_svt: role
                },
                async: true,
                success: function (json) {
                    var rsp = JSON.parse(json);
                    if (rsp.status == "ok") {
                        $('#modal_new_user button').removeClass("disabled");
                        $('#modal_new_user').modal("hide");
                        location.reload();
                    } else {
                        alert(rsp.msg);
                        $('#modal_new_user button').removeClass("disabled");
                    }
                }
            });
        }
    };

    window.change_password = function () {
        var complete = true;
        var password = $('#password').val();
        var password2 = $('#repeat_password').val();
        if(password=='') {
            complete = false;
            $('#password').addClass("error-highlight");
        } else {
            $('#password').removeClass("error-highlight");
        }
        if(password2=='') {
            complete = false;
            $('#repeat_password').addClass("error-highlight");
        } else {
            $('#repeat_password').removeClass("error-highlight");
        }
        if((password!='') && (password2!='')) {
            if(password!=password2) {
                complete = false;
                $('#password').addClass("error-highlight");
                $('#repeat_password').addClass("error-highlight");
            } else {
                $('#password').removeClass("error-highlight");
                $('#repeat_password').removeClass("error-highlight");
            }
        }
        if(complete) {
            $('#modal_change_password button').addClass("disabled");
            $.ajax({
                url: "ajax/change_password.php",
                type: "POST",
                data: {
                    id_svt: window.id_user_edit,
                    password_svt: password,
                },
                async: true,
                success: function (json) {
                    var rsp = JSON.parse(json);
                    if (rsp.status == "ok") {
                        window.user_need_save = false;
                        $('#modal_change_password button').removeClass("disabled");
                        $('#modal_change_password').modal("hide");
                        $('#password').val('');
                        $('#repeat_password').val('');
                    } else {
                        $('#modal_change_password button').removeClass("disabled");
                    }
                }
            });
        }
    };

    window.open_modal_plan_edit = function(id) {
        $.ajax({
            url: "ajax/get_plan.php",
            type: "POST",
            data: {
                id: id
            },
            async: true,
            success: function (json) {
                var rsp = JSON.parse(json);
                $('#name_edit').val(rsp.name);
                $('#n_virtual_tours_edit').val(rsp.n_virtual_tours);
                $('#n_rooms_edit').val(rsp.n_rooms);
                $('#n_markers_edit').val(rsp.n_markers);
                $('#n_pois_edit').val(rsp.n_pois);
                $('#days_edit').val(rsp.days);
                $('#price_edit').val(rsp.price);
                $("#frequency_edit option[id='"+rsp.frequency+"']").prop("selected", true);
                $('#interval_count_edit').val(rsp.interval_count);
                change_frequency('_edit');
                $('#max_file_size_upload_edit').val(rsp.max_file_size_upload);
                $('#max_storage_space_edit').val(rsp.max_storage_space);
                $("#currency_edit option").prop("selected", false);
                $("#currency_edit option[id='"+rsp.currency+"']").prop("selected", true);
                $('#features_edit #create_landing_edit').prop('selected',(rsp.create_landing==1)?true:false);
                $('#features_edit #create_showcase_edit').prop('selected',(rsp.create_showcase==1)?true:false);
                $('#features_edit #create_gallery_edit').prop('selected',(rsp.create_gallery==1)?true:false);
                $('#features_edit #create_presentation_edit').prop('selected',(rsp.create_presentation==1)?true:false);
                $('#features_edit #enable_live_session_edit').prop('selected',(rsp.enable_live_session==1)?true:false);
                $('#features_edit #enable_meeting_edit').prop('selected',(rsp.enable_meeting==1)?true:false);
                $('#features_edit #enable_info_box_edit').prop('selected',(rsp.enable_info_box==1)?true:false);
                $('#features_edit #enable_context_info_edit').prop('selected',(rsp.enable_context_info==1)?true:false);
                $('#features_edit #enable_maps_edit').prop('selected',(rsp.enable_maps==1)?true:false);
                $('#features_edit #enable_icons_library_edit').prop('selected',(rsp.enable_icons_library==1)?true:false);
                $('#features_edit #enable_voice_commands_edit').prop('selected',(rsp.enable_voice_commands==1)?true:false);
                $('#features_edit #enable_chat_edit').prop('selected',(rsp.enable_chat==1)?true:false);
                $('#features_edit #enable_auto_rotate_edit').prop('selected',(rsp.enable_auto_rotate==1)?true:false);
                $('#features_edit #enable_flyin_edit').prop('selected',(rsp.enable_flyin==1)?true:false);
                $('#features_edit #enable_multires_edit').prop('selected',(rsp.enable_multires==1)?true:false);
                $('#features_edit #enable_password_tours_edit').prop('selected',(rsp.enable_password_tour==1)?true:false);
                $('#features_edit #enable_expiring_dates_edit').prop('selected',(rsp.enable_expiring_dates==1)?true:false);
                $('#features_edit #enable_export_vt_edit').prop('selected',(rsp.enable_export_vt==1)?true:false);
                $('#features_edit #enable_statistics_edit').prop('selected',(rsp.enable_statistics==1)?true:false);
                $('#features_edit #enable_forms_edit').prop('selected',(rsp.enable_forms==1)?true:false);
                $('#features_edit #enable_logo_edit').prop('selected',(rsp.enable_logo==1)?true:false);
                $('#features_edit #enable_nadir_logo_edit').prop('selected',(rsp.enable_nadir_logo==1)?true:false);
                $('#features_edit #enable_song_edit').prop('selected',(rsp.enable_song==1)?true:false);
                $('#features_edit #enable_annotations_edit').prop('selected',(rsp.enable_annotations==1)?true:false);
                $('#features_edit #enable_panorama_video_edit').prop('selected',(rsp.enable_panorama_video==1)?true:false);
                $('#features_edit #enable_rooms_multiple_edit').prop('selected',(rsp.enable_rooms_multiple==1)?true:false);
                $('#features_edit #enable_rooms_protect_edit').prop('selected',(rsp.enable_rooms_protect==1)?true:false);
                $('#features_edit #enable_share_edit').prop('selected',(rsp.enable_share==1)?true:false);
                $('#features_edit #enable_device_orientation_edit').prop('selected',(rsp.enable_device_orientation==1)?true:false);
                $('#features_edit #enable_webvr_edit').prop('selected',(rsp.enable_webvr==1)?true:false);
                $('#features_edit #enable_shop_edit').prop('selected',(rsp.enable_shop==1)?true:false);
                $('#features_edit #enable_dollhouse_edit').prop('selected',(rsp.enable_dollhouse==1)?true:false);
                $('#features_edit').selectpicker('refresh');
                var count_usage = rsp.count_usage;
                if(count_usage>0) {
                    $('#btn_delete_plan').addClass("disabled");
                } else {
                    $('#btn_delete_plan').removeClass("disabled");
                }
                if(rsp.visible==1) {
                    $('#visible_edit').prop('checked',true);
                } else {
                    $('#visible_edit').prop('checked',false);
                }
                $('#external_url_edit').val(rsp.external_url);
                $('#custom_features_edit').val(rsp.custom_features);
                var customize_menu = rsp.customize_menu;
                if(customize_menu!='') {
                    var menu_items = JSON.parse(customize_menu);
                    jQuery.each(menu_items,function (menu_item,enabled) {
                        $('#customize_menu_edit #'+menu_item).prop('selected',enabled);
                    });
                    $('#customize_menu_edit').selectpicker('refresh');
                }
                $('#modal_edit_plan').modal("show");
            }
        });
    };

    window.save_plan = function () {
        var complete = true;
        var name = $('#name_edit').val();
        var n_virtual_tours = $('#n_virtual_tours_edit').val();
        var n_rooms = $('#n_rooms_edit').val();
        var n_markers = $('#n_markers_edit').val();
        var n_pois = $('#n_pois_edit').val();
        var days = $('#days_edit').val();
        var max_file_size_upload = $('#max_file_size_upload_edit').val();
        var max_storace_space = $('#max_storage_space_edit').val();
        var create_landing = $('#features_edit option[id="create_landing_edit"]').is(':selected');
        var create_showcase = $('#features_edit option[id="create_showcase_edit"]').is(':selected');
        var create_gallery = $('#features_edit option[id="create_gallery_edit"]').is(':selected');
        var create_presentation = $('#features_edit option[id="create_presentation_edit"]').is(':selected');
        var enable_live_session = $('#features_edit option[id="enable_live_session_edit"]').is(':selected');
        var enable_meeting = $('#features_edit option[id="enable_meeting_edit"]').is(':selected');
        var enable_chat = $('#features_edit option[id="enable_chat_edit"]').is(':selected');
        var enable_voice_commands = $('#features_edit option[id="enable_voice_commands_edit"]').is(':selected');
        var enable_share = $('#features_edit option[id="enable_share_edit"]').is(':selected');
        var enable_device_orientation = $('#features_edit option[id="enable_device_orientation_edit"]').is(':selected');
        var enable_webvr = $('#features_edit option[id="enable_webvr_edit"]').is(':selected');
        var enable_logo = $('#features_edit option[id="enable_logo_edit"]').is(':selected');
        var enable_nadir_logo = $('#features_edit option[id="enable_nadir_logo_edit"]').is(':selected');
        var enable_song = $('#features_edit option[id="enable_song_edit"]').is(':selected');
        var enable_forms = $('#features_edit option[id="enable_forms_edit"]').is(':selected');
        var enable_annotations = $('#features_edit option[id="enable_annotations_edit"]').is(':selected');
        var enable_panorama_video = $('#features_edit option[id="enable_panorama_video_edit"]').is(':selected');
        var enable_rooms_multiple = $('#features_edit option[id="enable_rooms_multiple_edit"]').is(':selected');
        var enable_rooms_protect = $('#features_edit option[id="enable_rooms_protect_edit"]').is(':selected');
        var enable_info_box = $('#features_edit option[id="enable_info_box_edit"]').is(':selected');
        var enable_context_info = $('#features_edit option[id="enable_context_info_edit"]').is(':selected');
        var enable_maps = $('#features_edit option[id="enable_maps_edit"]').is(':selected');
        var enable_icons_library = $('#features_edit option[id="enable_icons_library_edit"]').is(':selected');
        var enable_password_tour = $('#features_edit option[id="enable_password_tours_edit"]').is(':selected');
        var enable_expiring_dates = $('#features_edit option[id="enable_expiring_dates_edit"]').is(':selected');
        var enable_export_vt = $('#features_edit option[id="enable_export_vt_edit"]').is(':selected');
        var enable_statistics = $('#features_edit option[id="enable_statistics_edit"]').is(':selected');
        var enable_auto_rotate = $('#features_edit option[id="enable_auto_rotate_edit"]').is(':selected');
        var enable_flyin = $('#features_edit option[id="enable_flyin_edit"]').is(':selected');
        var enable_multires = $('#features_edit option[id="enable_multires_edit"]').is(':selected');
        var enable_shop = $('#features_edit option[id="enable_shop_edit"]').is(':selected');
        var enable_dollhouse = $('#features_edit option[id="enable_dollhouse_edit"]').is(':selected');
        var price = $('#price_edit').val();
        var frequency = $('#frequency_edit option:selected').attr('id');
        var interval_count = $('#interval_count_edit').val();
        var currency = $('#currency_edit option:selected').attr('id');
        var visible = $('#visible_edit').is(':checked');
        var external_url = $('#external_url_edit').val();
        var custom_features = $('#custom_features_edit').val();
        var customize_menu_edit_t = $('#customize_menu_edit option');
        var customize_menu = {};
        $(customize_menu_edit_t).each(function(index, elem){
            var id = $(this).attr('id');
            if(id !== undefined) {
                if($(this).is(':selected')) {
                    customize_menu[id]=1;
                } else {
                    customize_menu[id]=0;
                }
            }
        });
        var customize_menu_json = JSON.stringify(customize_menu);
        if(name=='') {
            complete = false;
            $('#name_edit').addClass("error-highlight");
        } else {
            $('#name_edit').removeClass("error-highlight");
        }
        if(complete) {
            $('#modal_edit_plan button').addClass("disabled");
            $.ajax({
                url: "ajax/save_plan.php",
                type: "POST",
                data: {
                    id: window.id_plan_sel,
                    name: name,
                    n_virtual_tours: n_virtual_tours,
                    n_rooms: n_rooms,
                    n_markers: n_markers,
                    n_pois: n_pois,
                    days: days,
                    price: price,
                    currency: currency,
                    custom_features: custom_features,
                    create_landing: create_landing,
                    create_showcase: create_showcase,
                    create_gallery: create_gallery,
                    create_presentation: create_presentation,
                    enable_live_session: enable_live_session,
                    enable_meeting: enable_meeting,
                    max_file_size_upload: max_file_size_upload,
                    max_storace_space: max_storace_space,
                    enable_chat: enable_chat,
                    enable_voice_commands: enable_voice_commands,
                    enable_share: enable_share,
                    enable_device_orientation: enable_device_orientation,
                    enable_webvr: enable_webvr,
                    enable_logo: enable_logo,
                    enable_nadir_logo: enable_nadir_logo,
                    enable_song: enable_song,
                    enable_forms: enable_forms,
                    enable_annotations: enable_annotations,
                    enable_panorama_video: enable_panorama_video,
                    enable_rooms_multiple: enable_rooms_multiple,
                    enable_rooms_protect: enable_rooms_protect,
                    enable_info_box: enable_info_box,
                    enable_context_info: enable_context_info,
                    enable_maps: enable_maps,
                    enable_icons_library: enable_icons_library,
                    enable_password_tour: enable_password_tour,
                    enable_expiring_dates: enable_expiring_dates,
                    enable_export_vt: enable_export_vt,
                    enable_statistics: enable_statistics,
                    enable_auto_rotate: enable_auto_rotate,
                    enable_flyin: enable_flyin,
                    enable_multires: enable_multires,
                    enable_shop: enable_shop,
                    enable_dollhouse: enable_dollhouse,
                    visible: visible,
                    external_url: external_url,
                    frequency: frequency,
                    interval_count: interval_count,
                    customize_menu: customize_menu_json
                },
                async: true,
                success: function (json) {
                    var rsp = JSON.parse(json);
                    if (rsp.status == "ok") {
                        $('#modal_edit_plan button').removeClass("disabled");
                        $('#modal_edit_plan').modal("hide");
                        window.plan_need_save = false;
                        window.plans_table.ajax.reload();
                        if(window.stripe_enabled) {
                            stripe_initialize(window.id_plan_sel);
                        }
                        if(window.paypal_enabled) {
                            paypal_initialize(window.id_plan_sel);
                        }
                        if(max_storace_space!=-1) {
                            $.ajax({
                                url: "../services/calculate_storage_space.php",
                                type: "POST",
                                async: true,
                                success: function (json) {}
                            });
                        }
                    } else {
                        $('#modal_edit_plan button').removeClass("disabled");
                    }
                }
            });
        }
    };

    window.delete_plan = function () {
        var retVal = confirm(window.backend_labels.delete_sure_msg);
        if( retVal == true ) {
            $('#modal_edit_plan button').addClass("disabled");
            $.ajax({
                url: "ajax/delete_plan.php",
                type: "POST",
                data: {
                    id: window.id_plan_sel
                },
                async: true,
                success: function (json) {
                    var rsp = JSON.parse(json);
                    if (rsp.status == "ok") {
                        $('#modal_edit_plan button').removeClass("disabled");
                        $('#modal_edit_plan').modal("hide");
                        window.plan_need_save = false;
                        window.plans_table.ajax.reload();
                    } else {
                        alert(rsp.msg);
                        $('#modal_edit_plan button').removeClass("disabled");
                    }
                }
            });
        } else {
            return false;
        }
    };

    window.add_plan = function () {
        var complete = true;
        var name = $('#name').val();
        var n_virtual_tours = $('#n_virtual_tours').val();
        var n_rooms = $('#n_rooms').val();
        var n_markers = $('#n_markers').val();
        var n_pois = $('#n_pois').val();
        var max_file_size_upload = $('#max_file_size_upload').val();
        var max_storace_space = $('#max_storage_space').val();
        var create_landing = $('#features option[id="create_landing"]').is(':selected');
        var create_showcase = $('#features option[id="create_showcase"]').is(':selected');
        var create_gallery = $('#features option[id="create_gallery"]').is(':selected');
        var create_presentation = $('#features option[id="create_presentation"]').is(':selected');
        var enable_live_session = $('#features option[id="enable_live_session"]').is(':selected');
        var enable_meeting = $('#features option[id="enable_meeting"]').is(':selected');
        var enable_chat = $('#features option[id="enable_chat"]').is(':selected');
        var enable_voice_commands = $('#features option[id="enable_voice_commands"]').is(':selected');
        var enable_share = $('#features option[id="enable_share"]').is(':selected');
        var enable_device_orientation = $('#features option[id="enable_device_orientation"]').is(':selected');
        var enable_webvr = $('#features option[id="enable_webvr"]').is(':selected');
        var enable_logo = $('#features option[id="enable_logo"]').is(':selected');
        var enable_nadir_logo = $('#features option[id="enable_nadir_logo"]').is(':selected');
        var enable_song = $('#features option[id="enable_song"]').is(':selected');
        var enable_forms = $('#features option[id="enable_forms"]').is(':selected');
        var enable_annotations = $('#features option[id="enable_annotations"]').is(':selected');
        var enable_panorama_video = $('#features option[id="enable_panorama_video"]').is(':selected');
        var enable_rooms_multiple = $('#features option[id="enable_rooms_multiple"]').is(':selected');
        var enable_rooms_protect = $('#features option[id="enable_rooms_protect"]').is(':selected');
        var enable_info_box = $('#features option[id="enable_info_box"]').is(':selected');
        var enable_context_info = $('#features option[id="enable_context_info"]').is(':selected');
        var enable_maps = $('#features option[id="enable_maps"]').is(':selected');
        var enable_icons_library = $('#features option[id="enable_icons_library"]').is(':selected');
        var enable_password_tour = $('#features option[id="enable_password_tours"]').is(':selected');
        var enable_expiring_dates = $('#features option[id="enable_expiring_dates"]').is(':selected');
        var enable_export_vt = $('#features option[id="enable_export_vt"]').is(':selected');
        var enable_statistics = $('#features option[id="enable_statistics"]').is(':selected');
        var enable_auto_rotate = $('#features option[id="enable_auto_rotate"]').is(':selected');
        var enable_flyin = $('#features option[id="enable_flyin"]').is(':selected');
        var enable_multires = $('#features option[id="enable_multires"]').is(':selected');
        var enable_shop = $('#features option[id="enable_shop"]').is(':selected');
        var enable_dollhouse = $('#features option[id="enable_dollhouse"]').is(':selected');
        var days = $('#days').val();
        var price = $('#price').val();
        var frequency = $('#frequency option:selected').attr('id');
        var interval_count = $('#interval_count').val();
        var currency = $('#currency option:selected').attr('id');
        var visible = $('#visible').is(':checked');
        var external_url = $('#external_url').val();
        var custom_features = $('#custom_features').val();
        var customize_menu_edit_t = $('#customize_menu option');
        var customize_menu = {};
        $(customize_menu_edit_t).each(function(index, elem){
            var id = $(this).attr('id');
            if(id !== undefined) {
                if($(this).is(':selected')) {
                    customize_menu[id]=1;
                } else {
                    customize_menu[id]=0;
                }
            }
        });
        var customize_menu_json = JSON.stringify(customize_menu);
        if(name=='') {
            complete = false;
            $('#name').addClass("error-highlight");
        } else {
            $('#name').removeClass("error-highlight");
        }
        if(complete) {
            $('#modal_new_plan button').addClass("disabled");
            $.ajax({
                url: "ajax/add_plan.php",
                type: "POST",
                data: {
                    name: name,
                    n_virtual_tours: n_virtual_tours,
                    n_rooms: n_rooms,
                    n_markers: n_markers,
                    n_pois: n_pois,
                    days: days,
                    price: price,
                    currency: currency,
                    custom_features: custom_features,
                    create_landing: create_landing,
                    create_showcase: create_showcase,
                    create_gallery: create_gallery,
                    create_presentation: create_presentation,
                    enable_live_session: enable_live_session,
                    enable_meeting: enable_meeting,
                    max_file_size_upload: max_file_size_upload,
                    max_storace_space: max_storace_space,
                    enable_chat: enable_chat,
                    enable_voice_commands: enable_voice_commands,
                    enable_share: enable_share,
                    enable_device_orientation: enable_device_orientation,
                    enable_webvr: enable_webvr,
                    enable_logo: enable_logo,
                    enable_nadir_logo: enable_nadir_logo,
                    enable_song: enable_song,
                    enable_forms: enable_forms,
                    enable_annotations: enable_annotations,
                    enable_panorama_video: enable_panorama_video,
                    enable_rooms_multiple: enable_rooms_multiple,
                    enable_rooms_protect: enable_rooms_protect,
                    enable_info_box: enable_info_box,
                    enable_context_info: enable_context_info,
                    enable_maps: enable_maps,
                    enable_icons_library: enable_icons_library,
                    enable_password_tour: enable_password_tour,
                    enable_expiring_dates: enable_expiring_dates,
                    enable_export_vt: enable_export_vt,
                    enable_statistics: enable_statistics,
                    enable_auto_rotate: enable_auto_rotate,
                    enable_flyin: enable_flyin,
                    enable_multires: enable_multires,
                    enable_shop: enable_shop,
                    enable_dollhouse: enable_dollhouse,
                    visible: visible,
                    external_url: external_url,
                    frequency: frequency,
                    interval_count: interval_count,
                    customize_menu: customize_menu_json
                },
                async: true,
                success: function (json) {
                    var rsp = JSON.parse(json);
                    if (rsp.status == "ok") {
                        $('#modal_new_plan button').removeClass("disabled");
                        $('#modal_new_plan').modal("hide");
                        window.plan_need_save = false;
                        window.plans_table.ajax.reload();
                        if(window.stripe_enabled) {
                            stripe_initialize(rsp.id);
                        }
                        if(window.paypal_enabled) {
                            paypal_initialize(rsp.id);
                        }
                        if(max_storace_space!=-1) {
                            $.ajax({
                                url: "../services/calculate_storage_space.php",
                                type: "POST",
                                async: true,
                                success: function (json) {}
                            });
                        }
                    } else {
                        $('#modal_new_plan button').removeClass("disabled");
                    }
                }
            });
        }
    };

    window.stripe_initialize = function (id_plan) {
        var stripe_public_key = $('#stripe_public_key').val();
        var stripe_secret_key = $('#stripe_secret_key').val();
        $('#modal_stripe_init').modal("show");
        var postData = new FormData();
        postData.append('id_plan', id_plan);
        postData.append('stripe_secret_key', stripe_secret_key);
        postData.append('stripe_public_key', stripe_public_key);
        fetch('../payments/stripe_init.php', {
            method: 'POST',
            body: postData
        }).then(function(response) {
            return response.json();
        }).then(function(rsp) {
            $('#modal_stripe_init').modal("hide");
            if(rsp.status=="ok") {
                $('#stripe_enabled').prop("disabled",false);
                $('#stripe_enabled').prop("checked",true);
                $('#paypal_enabled').prop("disabled",true);
                $('#paypal_enabled').prop("checked",false);
                $('#btn_check_stripe').addClass("btn-success");
                setTimeout(function () {
                    $('#btn_check_stripe').removeClass("btn-success");
                },1000);
            } else {
                alert(rsp.msg);
                $('#stripe_enabled').prop("disabled",true);
                $('#stripe_enabled').prop("checked",false);
                $('#btn_check_stripe').addClass("btn-danger");
                setTimeout(function () {
                    $('#btn_check_stripe').removeClass("btn-danger");
                },1000);
            }
        }).catch(function(error) {
            $('#modal_stripe_init').modal("hide");
            $('#btn_check_stripe').addClass("btn-danger");
            setTimeout(function () {
                $('#btn_check_stripe').removeClass("btn-danger");
            },1000);
        });
    }

    window.paypal_initialize = function (id_plan) {
        var paypal_live = $('#paypal_live').is(':checked');
        var paypal_client_id = $('#paypal_client_id').val();
        var paypal_client_secret = $('#paypal_client_secret').val();
        $('#modal_stripe_init').modal("show");
        var postData = new FormData();
        postData.append('id_plan', id_plan);
        postData.append('paypal_live', paypal_live);
        postData.append('paypal_client_id', paypal_client_id);
        postData.append('paypal_client_secret', paypal_client_secret);
        fetch('../payments/paypal_init.php', {
            method: 'POST',
            body: postData
        }).then(function(response) {
            return response.json();
        }).then(function(rsp) {
            $('#modal_stripe_init').modal("hide");
            if(rsp.status=="ok") {
                $('#paypal_enabled').prop("disabled",false);
                $('#paypal_enabled').prop("checked",true);
                $('#stripe_enabled').prop("disabled",true);
                $('#stripe_enabled').prop("checked",false);
                $('#btn_check_paypal').addClass("btn-success");
                setTimeout(function () {
                    $('#btn_check_paypal').removeClass("btn-success");
                },1000);
            } else {
                alert(rsp.msg);
                $('#paypal_enabled').prop("disabled",true);
                $('#paypal_enabled').prop("checked",false);
                $('#btn_check_paypal').addClass("btn-danger");
                setTimeout(function () {
                    $('#btn_check_paypal').removeClass("btn-danger");
                },1000);
            }
        }).catch(function(error) {
            $('#modal_stripe_init').modal("hide");
            $('#btn_check_paypal').addClass("btn-danger");
            setTimeout(function () {
                $('#btn_check_paypal').removeClass("btn-danger");
            },1000);
        });
    }

    window.save_virtualtour = function() {
        var complete = true;
        var name = $('#name').val();
        var author = $('#author').val();
        var id_user = $('#user option:selected').attr('id');
        var id_category = $('#category option:selected').attr('id');
        var hfov = parseInt($('#hfov').val());
        var min_hfov = parseInt($('#min_hfov').val());
        var max_hfov = parseInt($('#max_hfov').val());
        var hfov_mobile_ratio = parseFloat($('#hfov_mobile_ratio').val());
        var pan_speed = parseFloat($('#pan_speed').val());
        var pan_speed_mobile = parseFloat($('#pan_speed_mobile').val());
        var friction = parseFloat($('#friction').val());
        var friction_mobile = parseFloat($('#friction_mobile').val());
        var quality_viewer = parseFloat($('#quality_viewer').val());
        var song_autoplay = $('#song_autoplay option:selected').attr('id');
        var flyin = $('#flyin').is(':checked');
        var nadir_size = $('#size_nadir_logo option:selected').attr('id');
        var autorotate_inactivity = $('#autorotate_inactivity').val();
        var autorotate_speed = $('#autorotate_speed').val();
        var auto_start = $('#auto_start').is(':checked');
        var hide_loading = !$('#hide_loading').is(':checked');
        var background_video_delay = $('#background_video_delay').val();
        var sameAzimuth = $('#sameAzimuth').is(':checked');
        var description = $('#description').val();
        var external_url = $('#external_url').val();
        var ga_tracking_id = $('#ga_tracking_id').val();
        var fb_page_id = $('#fb_page_id').val();
        var compress_jpg = $('#compress_jpg').val();
        var max_width_compress = $('#max_width_compress').val();
        var link_logo = $('#link_logo').val();
        var enable_multires = $('#enable_multires').is(':checked');
        var preload_panoramas = $('#preload_panoramas').is(':checked');
        var whatsapp_number = $('#whatsapp_number').val();
        var transition_time = $('#transition_time').val();
        var transition_fadeout = $('#transition_fadeout').val();
        var transition_zoom = $('#transition_zoom').val();
        var transition_loading = $('#transition_loading').is(':checked');
        var transition_effect = $('#transition_effect option:selected').attr('id');
        var markers_default_lookat = $('#markers_default_lookat option:selected').attr('id');
        var click_anywhere = $('#click_anywhere').is(':checked');
        var hide_markers = $('#hide_markers').is(':checked');
        if(click_anywhere==false) hide_markers=false;
        var hover_markers = $('#hover_markers').is(':checked');
        if(hide_markers==false) hover_markers=false;
        var language = $('#language option:selected').attr('id');
        var keyboard_mode = $('#keyboard_mode option:selected').attr('id');
        var password_meeting = $('#password_meeting').val();
        var password_livesession = $('#password_livesession').val();
        var note = $('#note').val();
        var snipcart_api_key = $('#snipcart_api_key').val();
        var snipcart_currency = $('#snipcart_currency option:selected').attr('id');
        var enable_visitor_rt = $('#enable_visitor_rt').is(':checked');
        var interval_visitor_rt = $('#interval_visitor_rt').val();
        var custom_html = window.custom_vt_html.getValue();
        var context_info = window.context_info_editor.root.innerHTML;
        if(name=='') {
            complete = false;
            $('#name').addClass("error-highlight");
        } else {
            $('#name').removeClass("error-highlight");
        }
        if(window.external==1) {
            if(external_url=='') {
                complete = false;
                $('#external_url').addClass("error-highlight");
            } else {
                $('#external_url').removeClass("error-highlight");
            }
        }
        if(complete) {
            $('#save_btn .icon i').removeClass('far fa-circle').addClass('fas fa-circle-notch fa-spin');
            $('#save_btn').addClass("disabled");
            $.ajax({
                url: "ajax/save_virtual_tour.php",
                type: "POST",
                data: {
                    id_virtualtour: id_virtualtour,
                    name: name,
                    id_user: id_user,
                    author: author,
                    hfov: hfov,
                    min_hfov: min_hfov,
                    max_hfov: max_hfov,
                    hfov_mobile_ratio: hfov_mobile_ratio,
                    pan_speed: pan_speed,
                    pan_speed_mobile: pan_speed_mobile,
                    friction: friction,
                    friction_mobile: friction_mobile,
                    quality_viewer: quality_viewer,
                    song: window.song,
                    song_autoplay: song_autoplay,
                    flyin: flyin,
                    background_image: window.background_image,
                    background_video: window.background_video,
                    background_video_delay: background_video_delay,
                    logo: window.logo,
                    link_logo: link_logo,
                    nadir_logo: window.nadir_logo,
                    nadir_size: nadir_size,
                    intro_mobile: window.intro_mobile,
                    intro_desktop: window.intro_desktop,
                    autorotate_speed: autorotate_speed,
                    autorotate_inactivity: autorotate_inactivity,
                    auto_start: auto_start,
                    hide_loading: hide_loading,
                    sameAzimuth: sameAzimuth,
                    description: description,
                    external_url: external_url,
                    ga_tracking_id: ga_tracking_id,
                    compress_jpg: compress_jpg,
                    max_width_compress: max_width_compress,
                    fb_page_id: fb_page_id,
                    enable_multires: enable_multires,
                    preload_panoramas: preload_panoramas,
                    whatsapp_number: whatsapp_number,
                    transition_time: transition_time,
                    transition_fadeout: transition_fadeout,
                    transition_zoom: transition_zoom,
                    transition_loading: transition_loading,
                    transition_effect: transition_effect,
                    click_anywhere: click_anywhere,
                    hide_markers: hide_markers,
                    hover_markers: hover_markers,
                    note: note,
                    language: language,
                    id_category: id_category,
                    keyboard_mode: keyboard_mode,
                    password_meeting: password_meeting,
                    password_livesession: password_livesession,
                    snipcart_api_key: snipcart_api_key,
                    snipcart_currency: snipcart_currency,
                    enable_visitor_rt: enable_visitor_rt,
                    interval_visitor_rt: interval_visitor_rt,
                    markers_default_lookat: markers_default_lookat,
                    custom_html: custom_html,
                    context_info: context_info
                },
                async: true,
                success: function (json) {
                    var rsp = JSON.parse(json);
                    if(rsp.status=="ok") {
                        window.vt_need_save = false;
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
    }

    window.save_settings = function (validate_mail) {
        var complete = true;
        var purchase_code = $('#purchase_code').val();
        var name = $('#name').val();
        var theme_color = $('#theme_color').val();
        var font_backend = $('#font_backend').val();
        var welcome_msg = window.welcome_msg_editor.root.innerHTML;
        var language = $('#language option:selected').attr('id');
        var language_domain = $('#language_domain option:selected').attr('id');
        var languages_enabled_t = $('#languages_enabled option');
        var languages_enabled = {};
        $(languages_enabled_t).each(function(index, elem){
            var id = $(this).attr('id').replace('ls_','');
            if($(this).is(':selected')) {
                languages_enabled[id]=1;
            } else {
                languages_enabled[id]=0;
            }
        });
        var languages_enabled_json = JSON.stringify(languages_enabled);
        var furl_blacklist = $('#furl_blacklist').val();
        var contact_mail = $('#contact_mail').val();
        var help_url = $('#help_url').val();
        var enable_external_vt = $('#enable_external_vt').is(':checked');
        var enable_wizard = $('#enable_wizard').is(':checked');
        var enable_sample = $('#enable_sample').is(':checked');
        var id_vt_sample = $('#id_vt_sample option:selected').attr('id');
        var id_vt_template = $('#id_vt_template option:selected').attr('id');
        var smtp_server = $('#smtp_server').val();
        var smtp_port = $('#smtp_port').val();
        var smtp_auth = $('#smtp_auth').is(':checked');
        var smtp_secure = $('#smtp_secure option:selected').attr('id');
        var smtp_username = $('#smtp_username').val();
        var smtp_password = $('#smtp_password').val();
        var smtp_from_email = $('#smtp_from_email').val();
        var smtp_from_name = $('#smtp_from_name').val();
        var css_array = {};
        $(".editors_css").each(function() {
            var id = $(this).attr('id');
            css_array[id]=window.editors_css[id].getValue();
        });
        var js_array = {};
        $(".editors_js").each(function() {
            var id = $(this).attr('id');
            js_array[id.replace("_js","")]=window.editors_js[id].getValue();
        });
        var css_array_json = JSON.stringify(css_array);
        var js_array_json = JSON.stringify(js_array);
        var social_google_enable = $('#social_google_enable').is(':checked');
        var social_facebook_enable = $('#social_facebook_enable').is(':checked');
        var social_twitter_enable = $('#social_twitter_enable').is(':checked');
        var social_google_id = $('#social_google_id').val();
        var social_google_secret = $('#social_google_secret').val();
        var social_facebook_id = $('#social_facebook_id').val();
        var social_facebook_secret = $('#social_facebook_secret').val();
        var social_twitter_id = $('#social_twitter_id').val();
        var social_twitter_secret = $('#social_twitter_secret').val();
        var enable_registration = $('#enable_registration').is(':checked');
        var change_plan = $('#change_plan').is(':checked');
        var validate_email = $('#validate_email').is(':checked');
        var default_id_plan = $('#default_plan option:selected').attr('id');
        var stripe_enabled = $('#stripe_enabled').is(':checked');
        var stripe_secret_key = $('#stripe_secret_key').val();
        var stripe_public_key = $('#stripe_public_key').val();
        var paypal_enabled = $('#paypal_enabled').is(':checked');
        var paypal_live = $('#paypal_live').is(':checked');
        var paypal_client_id = $('#paypal_client_id').val();
        var paypal_client_secret = $('#paypal_client_secret').val();
        var mail_activate_subject = $('#mail_activate_subject').val();
        var mail_activate_body = window.mail_activate_body_editor.root.innerHTML;
        var mail_forgot_subject = $('#mail_forgot_subject').val();
        var mail_forgot_body = window.mail_forgot_body_editor.root.innerHTML;
        var language_vc = $('#language_vc').val();
        var initial_msg = $('#initial_msg').val();
        var listening_msg = $('#listening_msg').val();
        var error_msg = $('#error_msg').val();
        var help_cmd = $('#help_cmd').val();
        var help_msg_1 = $('#help_msg_1').val();
        var help_msg_2 = $('#help_msg_2').val();
        var next_cmd = $('#next_cmd').val();
        var next_msg = $('#next_msg').val();
        var prev_cmd = $('#prev_cmd').val();
        var prev_msg = $('#prev_msg').val();
        var left_cmd = $('#left_cmd').val();
        var left_msg = $('#left_msg').val();
        var right_cmd = $('#right_cmd').val();
        var right_msg = $('#right_msg').val();
        var up_cmd = $('#up_cmd').val();
        var up_msg = $('#up_msg').val();
        var down_cmd = $('#down_cmd').val();
        var down_msg = $('#down_msg').val();
        $('#voice_commands_tab input').each(function () {
            if($(this).val()=='') {
                complete = false;
                $(this).addClass("error-highlight");
            } else {
                $(this).removeClass("error-highlight");
            }
        });
        var first_name_enable = $('#first_name_enable').is(':checked');
        var last_name_enable = $('#last_name_enable').is(':checked');
        var company_enable = $('#company_enable').is(':checked');
        var tax_id_enable = $('#tax_id_enable').is(':checked');
        var street_enable = $('#street_enable').is(':checked');
        var city_enable = $('#city_enable').is(':checked');
        var province_enable = $('#province_enable').is(':checked');
        var postal_code_enable = $('#postal_code_enable').is(':checked');
        var country_enable = $('#country_enable').is(':checked');
        var tel_enable = $('#tel_enable').is(':checked');
        var first_name_mandatory = $('#first_name_mandatory').is(':checked');
        var last_name_mandatory = $('#last_name_mandatory').is(':checked');
        var company_mandatory = $('#company_mandatory').is(':checked');
        var tax_id_mandatory = $('#tax_id_mandatory').is(':checked');
        var street_mandatory = $('#street_mandatory').is(':checked');
        var city_mandatory = $('#city_mandatory').is(':checked');
        var province_mandatory = $('#province_mandatory').is(':checked');
        var postal_code_mandatory = $('#postal_code_mandatory').is(':checked');
        var country_mandatory = $('#country_mandatory').is(':checked');
        var tel_mandatory = $('#tel_mandatory').is(':checked');
        var peerjs_host = $('#peerjs_host').val();
        var peerjs_port = $('#peerjs_port').val();
        var peerjs_path = $('#peerjs_path').val();
        var turn_host = $('#turn_host').val();
        var turn_port = $('#turn_port').val();
        var turn_username = $('#turn_username').val();
        var turn_password = $('#turn_password').val();
        var jitsi_domain = $('#jitsi_domain').val();
        var leaflet_street_basemap = $('#leaflet_street_basemap').val();
        var leaflet_satellite_basemap = $('#leaflet_satellite_basemap').val();
        var leaflet_street_subdomain = $('#leaflet_street_subdomain').val();
        var leaflet_street_maxzoom = $('#leaflet_street_maxzoom').val();
        var leaflet_satellite_subdomain = $('#leaflet_satellite_subdomain').val();
        var leaflet_satellite_maxzoom = $('#leaflet_satellite_maxzoom').val();
        var footer_link_1 = $('#footer_link_1').val();
        var footer_link_2 = $('#footer_link_2').val();
        var footer_link_3 = $('#footer_link_3').val();
        var footer_value_1 = window.footer_value_1.root.innerHTML;
        var footer_value_2 = window.footer_value_2.root.innerHTML;
        var footer_value_3 = window.footer_value_3.root.innerHTML;
        var multires = $('#multires option:selected').attr('id');
        var multires_cloud_url = $('#multires_cloud_url').val();
        var enable_screencast = $('#enable_screencast').is(':checked');
        var url_screencast = $('#url_screencast').val();
        var notify_email = $('#notify_email').val();
        var notify_registrations = $('#notify_registrations').is(':checked');
        var notify_plan_expires = $('#notify_plan_expires').is(':checked');
        var notify_plan_changes = $('#notify_plan_changes').is(':checked');
        var notify_plan_cancels = $('#notify_plan_cancels').is(':checked');
        var notify_vt_create = $('#notify_vt_create').is(':checked');
        if(name=='') {
            complete = false;
            $('#name').addClass("error-highlight");
        } else {
            $('#name').removeClass("error-highlight");
        }
        if(!mail_activate_body.includes('%LINK%')) {
            complete = false;
            $('#mail_activate_body').addClass("error-highlight");
        } else {
            $('#mail_activate_body').removeClass("error-highlight");
        }
        if((!mail_forgot_body.includes('%LINK%')) || (!mail_forgot_body.includes('%VERIFICATION_CODE%'))) {
            complete = false;
            $('#mail_forgot_body').addClass("error-highlight");
        } else {
            $('#mail_forgot_body').removeClass("error-highlight");
        }
        if(complete) {
            if(validate_mail) {
                $('#btn_validate_mail').addClass("disabled");
            }
            $('#save_btn .icon i').removeClass('far fa-circle').addClass('fas fa-circle-notch fa-spin');
            $('#save_btn').addClass("disabled");
            $.ajax({
                url: "ajax/save_settings.php",
                type: "POST",
                data: {
                    purchase_code: purchase_code,
                    name: name,
                    theme_color: theme_color,
                    font_backend: font_backend,
                    welcome_msg: welcome_msg,
                    logo: window.b_logo_image,
                    small_logo: window.b_logo_s_image,
                    background: window.b_background_image,
                    background_reg: window.b_background_reg_image,
                    smtp_server: smtp_server,
                    smtp_port: smtp_port,
                    smtp_secure: smtp_secure,
                    smtp_auth: smtp_auth,
                    smtp_username: smtp_username,
                    smtp_password: smtp_password,
                    smtp_from_email: smtp_from_email,
                    smtp_from_name: smtp_from_name,
                    furl_blacklist: furl_blacklist,
                    language: language,
                    language_domain: language_domain,
                    languages_enabled: languages_enabled_json,
                    css_array: css_array_json,
                    js_array: js_array_json,
                    contact_mail: contact_mail,
                    help_url: help_url,
                    enable_external_vt: enable_external_vt,
                    enable_wizard: enable_wizard,
                    enable_sample: enable_sample,
                    id_vt_sample: id_vt_sample,
                    id_vt_template: id_vt_template,
                    social_google_enable: social_google_enable,
                    social_facebook_enable: social_facebook_enable,
                    social_twitter_enable: social_twitter_enable,
                    social_google_id: social_google_id,
                    social_google_secret: social_google_secret,
                    social_facebook_id: social_facebook_id,
                    social_facebook_secret: social_facebook_secret,
                    social_twitter_id: social_twitter_id,
                    social_twitter_secret: social_twitter_secret,
                    enable_registration: enable_registration,
                    default_id_plan: default_id_plan,
                    change_plan: change_plan,
                    validate_email: validate_email,
                    stripe_enabled: stripe_enabled,
                    stripe_secret_key: stripe_secret_key,
                    stripe_public_key: stripe_public_key,
                    paypal_enabled: paypal_enabled,
                    paypal_live: paypal_live,
                    paypal_client_id: paypal_client_id,
                    paypal_client_secret: paypal_client_secret,
                    mail_activate_subject: mail_activate_subject,
                    mail_activate_body: mail_activate_body,
                    mail_forgot_subject: mail_forgot_subject,
                    mail_forgot_body: mail_forgot_body,
                    voice_commands: {
                        language: language_vc,
                        initial_msg: initial_msg,
                        listening_msg: listening_msg,
                        error_msg: error_msg,
                        help_cmd: help_cmd,
                        help_msg_1: help_msg_1,
                        help_msg_2: help_msg_2,
                        next_cmd: next_cmd,
                        next_msg: next_msg,
                        prev_cmd: prev_cmd,
                        prev_msg: prev_msg,
                        left_cmd: left_cmd,
                        left_msg: left_msg,
                        right_cmd: right_cmd,
                        right_msg: right_msg,
                        up_cmd: up_cmd,
                        up_msg: up_msg,
                        down_cmd: down_cmd,
                        down_msg: down_msg
                    },
                    first_name_enable: first_name_enable,
                    last_name_enable: last_name_enable,
                    company_enable: company_enable,
                    tax_id_enable: tax_id_enable,
                    street_enable: street_enable,
                    city_enable: city_enable,
                    province_enable: province_enable,
                    postal_code_enable: postal_code_enable,
                    country_enable: country_enable,
                    tel_enable: tel_enable,
                    first_name_mandatory: first_name_mandatory,
                    last_name_mandatory: last_name_mandatory,
                    company_mandatory: company_mandatory,
                    tax_id_mandatory: tax_id_mandatory,
                    street_mandatory: street_mandatory,
                    city_mandatory: city_mandatory,
                    province_mandatory: province_mandatory,
                    postal_code_mandatory: postal_code_mandatory,
                    country_mandatory: country_mandatory,
                    tel_mandatory: tel_mandatory,
                    peerjs_host: peerjs_host,
                    peerjs_port: peerjs_port,
                    peerjs_path: peerjs_path,
                    turn_host: turn_host,
                    turn_port: turn_port,
                    turn_username: turn_username,
                    turn_password: turn_password,
                    jitsi_domain: jitsi_domain,
                    url_street: leaflet_street_basemap,
                    url_sat: leaflet_satellite_basemap,
                    sub_street: leaflet_street_subdomain,
                    sub_sat: leaflet_satellite_subdomain,
                    zoom_street: leaflet_street_maxzoom,
                    zoom_sat: leaflet_satellite_maxzoom,
                    footer_link_1: footer_link_1,
                    footer_link_2: footer_link_2,
                    footer_link_3: footer_link_3,
                    footer_value_1: footer_value_1,
                    footer_value_2: footer_value_2,
                    footer_value_3: footer_value_3,
                    multires: multires,
                    multires_cloud_url: multires_cloud_url,
                    enable_screencast: enable_screencast,
                    url_screencast: url_screencast,
                    notify_email: notify_email,
                    notify_registrations: notify_registrations,
                    notify_plan_expires: notify_plan_expires,
                    notify_plan_changes: notify_plan_changes,
                    notify_plan_cancels: notify_plan_cancels,
                    notify_vt_create: notify_vt_create
                },
                async: true,
                success: function (json) {
                    var rsp = JSON.parse(json);
                    if (rsp.status == "ok") {
                        window.settings_need_save = false;
                        $('#save_btn .icon i').removeClass('fas fa-circle-notch fa-spin').addClass('fas fa-check');
                        setTimeout(function () {
                            $('#save_btn .icon i').removeClass('fas fa-check').addClass('far fa-circle');
                            $('#save_btn').removeClass("disabled");
                        }, 1000);
                        if(validate_mail) {
                            $.ajax({
                                url: "ajax/send_email.php",
                                type: "POST",
                                data: {
                                    type: 'validate',
                                    email: smtp_from_email
                                },
                                timeout: 15000,
                                async: true,
                                success: function (json) {
                                    var rsp = JSON.parse(json);
                                    if (rsp.status == "ok") {
                                        $('#validate_mail').html('<i style="color: green" class="fas fa-circle"></i> '+window.backend_labels.valid);
                                    } else {
                                        alert(rsp.msg);
                                        $('#validate_mail').html('<i style="color: red" class="fas fa-circle"></i> '+window.backend_labels.invalid);
                                    }
                                    $('#btn_validate_mail').removeClass("disabled");
                                },
                                error: function(){
                                    alert('Timeout');
                                    $('#validate_mail').html('<i style="color: red" class="fas fa-circle"></i> '+window.backend_labels.invalid);
                                    $('#btn_validate_mail').removeClass("disabled");
                                },
                            });
                        } else {
                            if(window.current_language!=language) {
                                location.reload();
                            }
                        }
                    } else {
                        $('#save_btn .icon i').removeClass('fas fa-circle-notch fa-spin').addClass('fas fa-times');
                        $('#save_btn').removeClass('btn-success').addClass('btn-danger');
                        setTimeout(function () {
                            $('#save_btn .icon i').removeClass('fas fa-times').addClass('far fa-circle');
                            $('#save_btn').removeClass('btn-danger').addClass('btn-success');
                            $('#save_btn').removeClass("disabled");
                        }, 1000);
                        if(validate_mail) {
                            $('#btn_validate_mail').removeClass("disabled");
                        }
                    }
                }
            });
        }
    }

    window.add_virtualtour_sample = function () {
        $('#modal_sample_tour').modal("show");
    }

    window.close_virtualtour_sample = function () {
        $('#modal_sample_tour').modal("hide");
    }

    window.close_virtualtour_import = function () {
        $('#modal_import_tour').modal("hide");
    }

    window.add_virtualtour = function (create_and_edit) {
        window.create_and_edit = create_and_edit;
        var complete = true;
        var name = $('#name').val();
        var author = $('#author').val();
        var external = $('#external option:selected').attr('id');
        var sample_data = $('#sample_data').is(':checked');
        if(name=='') {
            complete = false;
            $('#name').addClass("error-highlight");
        } else {
            $('#name').removeClass("error-highlight");
        }
        if(complete) {
            if(sample_data) {
                add_virtualtour_sample();
                return;
            }
            if(create_and_edit) {
                var btn_html = $('#btn_create_edit_tour').html();
                $('#btn_create_edit_tour').html('<i class="fas fa-circle-notch fa-spin"></i>');
            } else {
                var btn_html = $('#btn_create_tour').html();
                $('#btn_create_tour').html('<i class="fas fa-circle-notch fa-spin"></i>');
            }
            $('#modal_new_virtualtour button').addClass("disabled");
            $.ajax({
                url: "ajax/add_virtual_tour.php",
                type: "POST",
                data: {
                    id_user: window.id_user,
                    name: name,
                    author: author,
                    external: external
                },
                async: true,
                success: function (json) {
                    var rsp = JSON.parse(json);
                    if (rsp.status == "ok") {
                        $.ajax({
                            url: "ajax/send_email.php",
                            type: "POST",
                            data: {
                                type: 'vt_create',
                                id_user: id_user,
                                id_vt: rsp.id
                            },
                            timeout: 15000,
                            async: true,
                            success: function () {}
                        });
                        if(window.create_and_edit) {
                            if (rsp.id != "") {
                                location.href="index.php?p=edit_virtual_tour&id="+rsp.id;
                            } else {
                                if(create_and_edit) {
                                    $('#btn_create_edit_tour').html(btn_html);
                                } else {
                                    $('#btn_create_tour').html(btn_html);
                                }
                                $('#modal_new_virtualtour button').removeClass("disabled");
                            }
                        } else {
                            if(window.wizard_step!=-1) {
                                location.href = 'index.php?p=virtual_tours&wstep=5';
                            } else {
                                location.reload();
                            }
                        }
                    } else {
                        if(create_and_edit) {
                            $('#btn_create_edit_tour').html(btn_html);
                        } else {
                            $('#btn_create_tour').html(btn_html);
                        }
                        $('#modal_new_virtualtour button').removeClass("disabled");
                    }
                }
            });
        }
    };

    window.change_map_type = function () {
        var map_type = $('#map_type option:selected').attr('id');
        switch(map_type) {
            case 'map':
                $('#frm').addClass('disabled');
                $('#btn_create_map').prop('disabled',false);
                break;
            case 'floorplan':
                $('#frm').removeClass('disabled');
                $('#btn_create_map').prop('disabled',true);
                break;
        }
    }

    window.add_map = function () {
        var complete = true;
        var name = $('#name').val();
        var map_image = $('#preview_image img').attr('src');
        var map_type = $('#map_type option:selected').attr('id');
        if(name=='') {
            complete = false;
            $('#name').addClass("error-highlight");
        } else {
            $('#name').removeClass("error-highlight");
        }
        if(complete) {
            $('#modal_new_map button').addClass("disabled");
            $.ajax({
                url: "ajax/add_map.php",
                type: "POST",
                data: {
                    id_virtualtour: window.id_virtualtour,
                    name: name,
                    map_image: map_image,
                    map_type: map_type
                },
                async: true,
                success: function (json) {
                    var rsp = JSON.parse(json);
                    if (rsp.status == "ok") {
                        $('#modal_new_map button').removeClass("disabled");
                        $('#modal_new_map').modal("hide");
                        var id_map = rsp.id;
                        location.href = "index.php?p=edit_map&id="+id_map;
                    } else {
                        $('#modal_new_map button').removeClass("disabled");
                    }
                }
            });
        }
    };

    window.add_room = function () {
        var complete = true;
        var name = $('#name').val();
        var panorama_image = $('#preview_image img').attr('src');
        var type_pano = $('#type_pano option:selected').attr('id');
        if(name=='') {
            complete = false;
            $('#name').addClass("error-highlight");
        } else {
            $('#name').removeClass("error-highlight");
        }
        var panorama_url = $('#panorama_url').val();
        if(type_pano=='hls') {
            if(panorama_url=='') {
                complete = false;
                $('#panorama_url').addClass("error-highlight");
            } else {
                $('#panorama_url').removeClass("error-highlight");
            }
        }
        if(complete) {
            $('#modal_new_room .btn').addClass("disabled");
            var btn_html =  $('#btn_create_room').html();
            $('#btn_create_room').html('<i class="fas fa-circle-notch fa-spin"></i>');
            $.ajax({
                url: "ajax/add_room.php",
                type: "POST",
                data: {
                    id_virtualtour: window.id_virtualtour,
                    name: name,
                    type_pano: type_pano,
                    panorama_image: panorama_image,
                    panorama_video: window.panorama_video,
                    panorama_url: panorama_url,
                    panorama_json: window.panorama_json
                },
                async: true,
                success: function (json) {
                    var rsp = JSON.parse(json);
                    $('#btn_create_room').html(btn_html);
                    if (rsp.status == "ok") {
                        $('#modal_new_room .btn').removeClass("disabled");
                        $('#modal_new_room').modal("hide");
                        var id_room = rsp.id;
                        if(window.wizard_step!=-1) {
                            if(window.wizard_step==12) {
                                location.href = "index.php?p=edit_room&id="+id_room+"&wstep=16";
                            } else {
                                location.href = "index.php?p=edit_room&id="+id_room+"&wstep=10";
                            }
                        } else {
                            location.href = "index.php?p=edit_room&id="+id_room;
                        }
                    } else {
                        $('#modal_new_room button').removeClass("disabled");
                    }
                }
            });
        }
    };

    window.add_bulk_room = function (id_virtualtour,panorama_image,name) {
        $.ajax({
            url: "ajax/add_room.php",
            type: "POST",
            data: {
                id_virtualtour: id_virtualtour,
                name: name,
                panorama_image: panorama_image
            },
            async: true,
            success: function (json) {
                window.rooms_created++;
                $('#rooms_created').html(window.rooms_created);
            }
        });
    };

    window.add_bulk_map = function (id_virtualtour,map_image,name) {
        $.ajax({
            url: "ajax/add_map.php",
            type: "POST",
            data: {
                id_virtualtour: id_virtualtour,
                name: name,
                map_image: map_image,
                map_type: 'floorplan'
            },
            async: true,
            success: function (json) {
                window.maps_created++;
                $('#maps_created').html(window.maps_created);
            }
        });
    };

    window.save_user = function(id_user_edit) {
        var complete = true;
        var username = $('#username').val();
        var email = $('#email').val();
        var role = $('#role option:selected').attr('id');
        var plan = $('#plan option:selected').attr('id');
        var language = $('#language option:selected').attr('id');
        var active = $('#active').is(':checked');
        var expire_plan_date_manual_date = $('#expire_plan_date_manual_date').val();
        var expire_plan_date_manual_time = $('#expire_plan_date_manual_time').val();
        if(username=='') {
            complete = false;
            $('#username').addClass("error-highlight");
        } else {
            $('#username').removeClass("error-highlight");
        }
        if(email=='') {
            complete = false;
            $('#email').addClass("error-highlight");
        } else {
            $('#email').removeClass("error-highlight");
        }
        if(complete) {
            $('#save_btn .icon i').removeClass('far fa-circle').addClass('fas fa-circle-notch fa-spin');
            $('#save_btn').addClass("disabled");
            $.ajax({
                url: "ajax/save_user.php",
                type: "POST",
                data: {
                    id_svt: id_user_edit,
                    username_svt: username,
                    email_svt: email,
                    role_svt: role,
                    plan_svt: plan,
                    language_svt: language,
                    active_svt: active,
                    expire_plan_date_manual_date_svt: expire_plan_date_manual_date,
                    expire_plan_date_manual_time_svt: expire_plan_date_manual_time
                },
                async: true,
                success: function (json) {
                    var rsp = JSON.parse(json);
                    if(rsp.status=="ok") {
                        window.user_need_save = false;
                        if(rsp.reload==1) {
                            location.reload();
                        } else {
                            $('#save_btn .icon i').removeClass('fas fa-circle-notch fa-spin').addClass('fas fa-check');
                            setTimeout(function () {
                                $('#save_btn .icon i').removeClass('fas fa-check').addClass('far fa-circle');
                                $('#save_btn').removeClass("disabled");
                            },1000);
                        }
                    } else {
                        alert(rsp.msg);
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
    }

    window.save_showcase = function(id_showcase) {
        $('#friendly_url').removeClass("error-highlight");
        var complete = true;
        var name = $('#name').val();
        var friendly_url = $('#friendly_url').val();
        var bg_color = $('#bg_color').val();
        var header_html = window.editor_html_h.getValue();
        var footer_html = window.editor_html_f.getValue();
        var custom_css = window.editor_css.getValue();
        if(name=='') {
            complete = false;
            $('#name').addClass("error-highlight");
        } else {
            $('#name').removeClass("error-highlight");
        }
        var list_s_vt = [];
        var list_s_type = [];
        $('.list_s_vt input[type=checkbox]').each(function () {
            var id = this.id;
            if(this.checked) {
                list_s_vt.push(id);
                list_s_type.push($('#t_'+id+' option:selected').attr('id'));
            }
        });
        if(complete) {
            $('#save_btn .icon i').removeClass('far fa-circle').addClass('fas fa-circle-notch fa-spin');
            $('#save_btn').addClass("disabled");
            $.ajax({
                url: "ajax/save_showcase.php",
                type: "POST",
                data: {
                    id: id_showcase,
                    name: name,
                    friendly_url: friendly_url,
                    bg_color: bg_color,
                    logo: window.s_logo_image,
                    banner: window.s_banner_image,
                    list_s_vt: list_s_vt,
                    list_s_type: list_s_type,
                    header_html: header_html,
                    footer_html: footer_html,
                    custom_css: custom_css
                },
                async: true,
                success: function (json) {
                    var rsp = JSON.parse(json);
                    if(rsp.status=="ok") {
                        window.showcase_need_save = false;
                        $('#save_btn .icon i').removeClass('fas fa-circle-notch fa-spin').addClass('fas fa-check');
                        setTimeout(function () {
                            $('#save_btn .icon i').removeClass('fas fa-check').addClass('far fa-circle');
                            $('#save_btn').removeClass("disabled");
                        },1000);
                    } else {
                        if(rsp.status=='error_furl') {
                            $('#friendly_url').addClass("error-highlight");
                        }
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
    }

    window.save_advertisement = function(id_advertisement) {
        var complete = true;
        var name = $('#name').val();
        if(name=='') {
            complete = false;
            $('#name').addClass("error-highlight");
        } else {
            $('#name').removeClass("error-highlight");
        }
        var link = $('#link').val();
        var countdown = $('#countdown').val();
        var type = $('#type option:selected').attr('id');
        var iframe_link = $('#iframe_link').val();
        var auto_assign = $('#auto_assign').is(':checked');
        var list_p_vt = [];
        $('.list_p_vt input[type=checkbox]').each(function () {
            if(this.checked) {
                list_p_vt.push(this.id);
            }
        });
        var list_s_vt = [];
        $('.list_s_vt input[type=checkbox]').each(function () {
            if(this.checked) {
                list_s_vt.push(this.id);
            }
        });
        if(complete) {
            $('#save_btn .icon i').removeClass('far fa-circle').addClass('fas fa-circle-notch fa-spin');
            $('#save_btn').addClass("disabled");
            $.ajax({
                url: "ajax/save_announce.php",
                type: "POST",
                data: {
                    id: id_advertisement,
                    name: name,
                    link: link,
                    countdown: countdown,
                    auto_assign: auto_assign,
                    type: type,
                    image: window.image_advertisement,
                    video: window.video_advertisement,
                    iframe_link: iframe_link,
                    list_p_vt: list_p_vt,
                    list_s_vt: list_s_vt
                },
                async: true,
                success: function (json) {
                    var rsp = JSON.parse(json);
                    if(rsp.status=="ok") {
                        window.advertisement_need_save = false;
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
    }

    window.save_product = function(id_product) {
        var complete = true;
        var name = $('#name').val();
        var price = $('#price').val();
        var description = window.description_editor.root.innerHTML;
        var link = $('#link').val();
        var purchase_type = $('#type option:selected').attr('id').replace('t_','');
        if(name=='') {
            complete = false;
            $('#name').addClass("error-highlight");
        } else {
            $('#name').removeClass("error-highlight");
        }
        if(complete) {
            $('#save_btn .icon i').removeClass('far fa-circle').addClass('fas fa-circle-notch fa-spin');
            $('#save_btn').addClass("disabled");
            $.ajax({
                url: "ajax/save_product.php",
                type: "POST",
                data: {
                    id: id_product,
                    name: name,
                    price: price,
                    description: description,
                    link: link,
                    purchase_type: purchase_type
                },
                async: true,
                success: function (json) {
                    var rsp = JSON.parse(json);
                    if(rsp.status=="ok") {
                        window.product_need_save = false;
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
    }

    window.modal_delete_showcase = function(id) {
        $('#btn_delete_showcase').attr('onclick','delete_showcase(\''+id+'\');');
        $('#modal_delete_showcase').modal("show");
    }

    window.delete_showcase = function (id) {
        $('#modal_delete_showcase button').addClass("disabled");
        $.ajax({
            url: "ajax/delete_showcase.php",
            type: "POST",
            data: {
                id_showcase: id
            },
            async: true,
            success: function (json) {
                var rsp = JSON.parse(json);
                if (rsp.status == "ok") {
                    $('#modal_delete_showcase button').removeClass("disabled");
                    $('#modal_delete_showcase').modal("hide");
                    location.href = 'index.php?p=showcases';
                } else {
                    $('#modal_delete_showcase button').removeClass("disabled");
                }
            }
        });
    };

    window.modal_delete_advertisement = function(id) {
        $('#btn_delete_advertisement').attr('onclick','delete_advertisement(\''+id+'\');');
        $('#modal_delete_advertisement').modal("show");
    }

    window.delete_advertisement = function (id) {
        $('#modal_delete_advertisement button').addClass("disabled");
        $.ajax({
            url: "ajax/delete_announce.php",
            type: "POST",
            data: {
                id_advertisement: id
            },
            async: true,
            success: function (json) {
                var rsp = JSON.parse(json);
                if (rsp.status == "ok") {
                    $('#modal_delete_advertisement button').removeClass("disabled");
                    $('#modal_delete_advertisement').modal("hide");
                    location.href = 'index.php?p=advertisements';
                } else {
                    $('#modal_delete_advertisement button').removeClass("disabled");
                }
            }
        });
    };

    window.modal_delete_product = function(id) {
        $('#btn_delete_product').attr('onclick','delete_product(\''+id+'\');');
        $('#modal_delete_product').modal("show");
    }

    window.delete_product = function (id) {
        $('#modal_delete_product button').addClass("disabled");
        $.ajax({
            url: "ajax/delete_product.php",
            type: "POST",
            data: {
                id_product: id
            },
            async: true,
            success: function (json) {
                var rsp = JSON.parse(json);
                if (rsp.status == "ok") {
                    $('#modal_delete_product button').removeClass("disabled");
                    $('#modal_delete_product').modal("hide");
                    location.href = 'index.php?p=products';
                } else {
                    $('#modal_delete_product button').removeClass("disabled");
                }
            }
        });
    };

    window.modal_delete_user = function(id) {
        $('#btn_delete_user').attr('onclick','delete_user(\''+id+'\');');
        $('#modal_delete_user').modal("show");
    }

    window.delete_user = function (id) {
        $('#modal_delete_user button').addClass("disabled");
        $.ajax({
            url: "ajax/delete_user.php",
            type: "POST",
            data: {
                id_user: id
            },
            async: true,
            success: function (json) {
                var rsp = JSON.parse(json);
                if (rsp.status == "ok") {
                    $('#modal_delete_user button').removeClass("disabled");
                    $('#modal_delete_user').modal("hide");
                    location.href = 'index.php?p=users';
                } else {
                    $('#modal_delete_user button').removeClass("disabled");
                }
            }
        });
    };

    window.save_profile = function(id_user_edit,complete_profile) {
        var complete = true;
        var username = $('#username').val();
        var email = $('#email').val();
        var avatar = $('#avatar_edit').attr('src');
        var language = $('#language option:selected').attr('id');
        var first_name = $('#first_name').val();
        var last_name = $('#last_name').val();
        var company = $('#company').val();
        var tax_id = $('#tax_id').val();
        var street = $('#street').val();
        var city = $('#city').val();
        var province = $('#province').val();
        var postal_code = $('#postal_code').val();
        var country = $('#country option:selected').val();
        var tel = $('#tel').val();
        if(username=='') {
            complete = false;
            $('#username').addClass("error-highlight");
        } else {
            $('#username').removeClass("error-highlight");
        }
        if(email=='') {
            complete = false;
            $('#email').addClass("error-highlight");
        } else {
            $('#email').removeClass("error-highlight");
        }
        if($('#first_name').attr('data-mandatory')=='true' && first_name=='') {
            complete = false;
            $('#first_name').addClass("error-highlight");
        } else {
            $('#first_name').removeClass("error-highlight");
        }
        if($('#last_name').attr('data-mandatory')=='true' && last_name=='') {
            complete = false;
            $('#last_name').addClass("error-highlight");
        } else {
            $('#last_name').removeClass("error-highlight");
        }
        if($('#company').attr('data-mandatory')=='true' && company=='') {
            complete = false;
            $('#company').addClass("error-highlight");
        } else {
            $('#company').removeClass("error-highlight");
        }
        if($('#tax_id').attr('data-mandatory')=='true' && tax_id=='') {
            complete = false;
            $('#tax_id').addClass("error-highlight");
        } else {
            $('#tax_id').removeClass("error-highlight");
        }
        if($('#street').attr('data-mandatory')=='true' && street=='') {
            complete = false;
            $('#street').addClass("error-highlight");
        } else {
            $('#street').removeClass("error-highlight");
        }
        if($('#city').attr('data-mandatory')=='true' && city=='') {
            complete = false;
            $('#city').addClass("error-highlight");
        } else {
            $('#city').removeClass("error-highlight");
        }
        if($('#province').attr('data-mandatory')=='true' && province=='') {
            complete = false;
            $('#province').addClass("error-highlight");
        } else {
            $('#province').removeClass("error-highlight");
        }
        if($('#postal_code').attr('data-mandatory')=='true' && postal_code=='') {
            complete = false;
            $('#postal_code').addClass("error-highlight");
        } else {
            $('#postal_code').removeClass("error-highlight");
        }
        if($('#country').attr('data-mandatory')=='true' && country=='') {
            complete = false;
            $('button[data-id="country"]').addClass("error-highlight");
        } else {
            $('button[data-id="country"]').removeClass("error-highlight");
        }
        if($('#tel').attr('data-mandatory')=='true' && tel=='') {
            complete = false;
            $('#tel').addClass("error-highlight");
        } else {
            $('#tel').removeClass("error-highlight");
        }
        if(complete) {
            $('#save_btn .icon i').removeClass('far fa-circle').addClass('fas fa-circle-notch fa-spin');
            $('#save_btn').addClass("disabled");
            $('#btn_save_continue_profile').addClass('disabled');
            $.ajax({
                url: "ajax/save_profile.php",
                type: "POST",
                data: {
                    id_svt: id_user_edit,
                    username_svt: username,
                    email_svt: email,
                    language_svt: language,
                    avatar_svt: avatar,
                    first_name: first_name,
                    last_name: last_name,
                    company: company,
                    tax_id: tax_id,
                    street: street,
                    city: city,
                    province: province,
                    postal_code: postal_code,
                    country: country,
                    tel: tel
                },
                async: true,
                success: function (json) {
                    var rsp = JSON.parse(json);
                    if(rsp.status=="ok") {
                        window.user_need_save = false;
                        if(complete_profile) {
                            location.href = 'index.php';
                        } else {
                            $('#save_btn .icon i').removeClass('fas fa-circle-notch fa-spin').addClass('fas fa-check');
                            setTimeout(function () {
                                $('#save_btn .icon i').removeClass('fas fa-check').addClass('far fa-circle');
                                $('#save_btn').removeClass("disabled");
                                if(rsp.reload_page==1) {
                                    location.href = 'index.php?p=edit_profile';
                                }
                            },200);
                        }
                    } else {
                        if(!complete_profile) {
                            $('#save_btn .icon i').removeClass('fas fa-circle-notch fa-spin').addClass('fas fa-times');
                            $('#save_btn').removeClass('btn-success').addClass('btn-danger');
                            setTimeout(function () {
                                $('#save_btn .icon i').removeClass('fas fa-times').addClass('far fa-circle');
                                $('#save_btn').removeClass('btn-danger').addClass('btn-success');
                                $('#save_btn').removeClass("disabled");
                            }, 1000);
                        } else {
                            $('#btn_save_continue_profile').removeClass('disabled');
                        }
                    }
                }
            });
        }
    }

    window.save_room = function(goto,apply_preset_to_vt) {
        var complete = true;
        var name = $('#name').val();
        var yaw_pitch = $('#yaw_pitch').val();
        var northOffset = $('#northOffset').val();
        var panorama_image = $('#panorama_image').attr('src');
        var allow_pitch = $('#allow_pitch').is(':checked');
        var allow_hfov = $('#allow_hfov').is(':checked');
        var visible_list = $('#visible_list').is(':checked');
        var annotation_title = $('#annotation_title').val();
        var annotation_description = $('#annotation_description').val();
        var min_pitch = $('#min_pitch').val();
        var max_pitch = $('#max_pitch').val();
        var min_yaw = $('#min_yaw').val();
        var max_yaw = $('#max_yaw').val();
        var haov = $('#haov').val();
        var vaov = $('#vaov').val();
        var hfov = $('#hfov').val();
        var h_pitch = $('#h_pitch').val();
        var h_roll = $('#h_roll').val();
        var protect_type = $('#protect_type option:selected').attr('id');
        var passcode_title = $('#passcode_title').val();
        var passcode_description = $('#passcode_description').val();
        var passcode = $('#passcode_code').val();
        var transition_override = $('#transition_override').is(':checked');
        if(transition_override) transition_override=1; else transition_override=0;
        var transition_time = $('#transition_time').val();
        var transition_fadeout = $('#transition_fadeout').val();
        var transition_zoom = $('#transition_zoom').val();
        var transition_effect = $('#transition_effect option:selected').attr('id');
        var brightness = $('#brightness').val();
        var contrast = $('#contrast').val();
        var saturate = $('#saturate').val();
        var grayscale = $('#grayscale').val();
        var effect = $('#effect option:selected').attr('id');
        var virtual_staging = parseInt($('#virtual_staging option:selected').attr('id'));
        var main_view_tooltip = $('#main_view_tooltip').val();
        var protect_send_email = $('#protect_send_email').is(':checked');
        var protect_email = $('#protect_email').val();
        if(window.room_type=='video' || window.room_type=='hls') {
            var audio_track_enable = $('#audio_track_enable').is(':checked');
        } else {
            var audio_track_enable = 0;
        }
        var song_bg_volume = $('#song_bg_volume').val();
        if(name=='') {
            complete = false;
            $('#name').addClass("error-highlight");
        } else {
            $('#name').removeClass("error-highlight");
        }
        if(complete) {
            $('#save_btn .icon i').removeClass('far fa-circle').addClass('fas fa-circle-notch fa-spin');
            $('#save_btn').addClass("disabled");
            if(goto=='blur') {
                $('#btn_edit_blur').addClass("disabled");
            }
            $.ajax({
                url: "ajax/save_room.php",
                type: "POST",
                data: {
                    id_virtualtour: window.id_virtualtour,
                    id_room: id_room,
                    name: name,
                    logo: window.logo,
                    yaw_pitch: yaw_pitch,
                    northOffset: northOffset,
                    change_image: change_image,
                    change_video: change_video,
                    panorama_image: panorama_image,
                    panorama_video: panorama_video,
                    allow_pitch: allow_pitch,
                    allow_hfov: allow_hfov,
                    visible_list: visible_list,
                    min_pitch: min_pitch,
                    max_pitch: max_pitch,
                    min_yaw: min_yaw,
                    max_yaw: max_yaw,
                    h_pitch: h_pitch,
                    h_roll: h_roll,
                    haov: haov,
                    vaov: vaov,
                    hfov: hfov,
                    audio_track_enable: audio_track_enable,
                    song: window.song,
                    song_bg_volume: song_bg_volume,
                    annotation_title: annotation_title,
                    annotation_description: annotation_description,
                    protect_type: protect_type,
                    passcode_title: passcode_title,
                    passcode_description: passcode_description,
                    passcode: passcode,
                    transition_time: transition_time,
                    transition_fadeout: transition_fadeout,
                    transition_zoom: transition_zoom,
                    transition_override: transition_override,
                    transition_effect: transition_effect,
                    brightness: brightness,
                    contrast: contrast,
                    saturate: saturate,
                    grayscale: grayscale,
                    effect: effect,
                    thumb_image: window.thumb_image,
                    virtual_staging: virtual_staging,
                    main_view_tooltip: main_view_tooltip,
                    background_color: window.background_color,
                    apply_preset_to_vt: apply_preset_to_vt,
                    protect_send_email: protect_send_email,
                    protect_email: protect_email
                },
                async: true,
                success: function (json) {
                    var rsp = JSON.parse(json);
                    if(rsp.status=="ok") {
                        window.room_need_save = false;
                        if(goto=='blur') {
                            location.href = 'index.php?p=edit_blur&id='+id_room;
                        } else {
                            if(apply_preset_to_vt) {
                                $('#modal_apply_preset_tour button').removeClass('disabled');
                                $('#modal_apply_preset_tour').modal('hide');
                            }
                            $('#save_btn .icon i').removeClass('fas fa-circle-notch fa-spin').addClass('fas fa-check');
                            setTimeout(function () {
                                $('#save_btn .icon i').removeClass('fas fa-check').addClass('far fa-circle');
                                $('#save_btn').removeClass("disabled");
                            },1000);
                        }
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
    }

    window.save_marker_pos = function(id_marker,yaw,pitch,rotateX,rotateZ,size_scale,embed_coords,embed_size) {
        $.ajax({
            url: "ajax/save_marker_pos.php",
            type: "POST",
            data: {
                id: id_marker,
                yaw: yaw,
                pitch: pitch,
                rotateX: rotateX,
                rotateZ: rotateZ,
                size_scale: size_scale,
                embed_coords: embed_coords,
                embed_size: embed_size
            },
            async: true
        });
    }

    window.save_marker_style = function(id_marker,show_room,color,background,icon,id_icon_library,tooltip_type,tooltip_text,css_class,embed_content,animation) {
        $.ajax({
            url: "ajax/save_marker_style.php",
            type: "POST",
            data: {
                id: id_marker,
                show_room: show_room,
                color: color,
                background: background,
                icon: icon,
                id_icon_library: id_icon_library,
                tooltip_type: tooltip_type,
                tooltip_text: tooltip_text,
                css_class: css_class,
                embed_content: embed_content,
                animation: animation
            },
            async: false
        });
    }

    window.save_poi_pos = function(id_poi,yaw,pitch,rotateX,rotateZ,size_scale,embed_coords,embed_size,transform3d,zindex) {
        $.ajax({
            url: "ajax/save_poi_pos.php",
            type: "POST",
            data: {
                id: id_poi,
                yaw: yaw,
                pitch: pitch,
                rotateX: rotateX,
                rotateZ: rotateZ,
                size_scale: size_scale,
                embed_coords: embed_coords,
                embed_size: embed_size,
                transform3d: transform3d,
                zindex: zindex
            },
            async: true
        });
    }

    window.save_poi_style = function(id_poi,color,background,icon,style,id_icon_library,label,tooltip_type,tooltip_text,css_class,embed_content,embed_video_autoplay,embed_video_muted,embed_gallery_autoplay,embed_type,animation) {
        if(embed_type=='gallery') embed_content='';
        $.ajax({
            url: "ajax/save_poi_style.php",
            type: "POST",
            data: {
                id: id_poi,
                color: color,
                background: background,
                icon: icon,
                label: label,
                style: style,
                id_icon_library: id_icon_library,
                tooltip_type: tooltip_type,
                tooltip_text: tooltip_text,
                css_class: css_class,
                embed_content: embed_content,
                embed_video_autoplay: embed_video_autoplay,
                embed_video_muted: embed_video_muted,
                embed_gallery_autoplay: embed_gallery_autoplay,
                animation: animation
            },
            async: false
        });
    }

    window.save_poi_schedule = function(id_poi,schedule) {
        $.ajax({
            url: "ajax/save_poi_schedule.php",
            type: "POST",
            data: {
                id: id_poi,
                schedule: schedule
            },
            async: true
        });
    }

    window.save_poi_edit = function(id_poi,type,content,title,description,target,view_type,box_pos,song_bg_volume,params,auto_close) {
        $.ajax({
            url: "ajax/save_poi_edit.php",
            type: "POST",
            data: {
                id: id_poi,
                type: type,
                content: content,
                title: title,
                description: description,
                target: target,
                id_room: window.id_room_sel,
                id_poi_autoopen: window.id_poi_autoopen,
                view_type: view_type,
                box_pos: box_pos,
                song_bg_volume: song_bg_volume,
                params: params,
                auto_close: auto_close
            },
            async: false
        });
    }

    window.save_marker_edit = function(id_marker,id_room_target,yaw,pitch,lookat) {
        $.ajax({
            url: "ajax/save_marker_edit.php",
            type: "POST",
            data: {
                id: id_marker,
                id_room_target: id_room_target,
                yaw: yaw,
                pitch: pitch,
                lookat: lookat
            },
            async: false
        });
    }

    window.new_marker = function(id_room,image,id_room_target=0) {
        if(id_room_target==0) {
            id_room_target = $('#room_target_add option:selected').attr('id');
        }
        if($('#override_pos_add').is(':checked')) {
            var yaw_m = parseFloat(window.viewer_pos.getYaw());
            var pitch_m = parseFloat(window.viewer_pos.getPitch());
        } else {
            var yaw_m = '';
            var pitch_m = '';
        }
        $('#modal_add_marker button').prop("disabled",true);
        try {
            var yaw = parseFloat(window.viewer.getYaw());
            var pitch = parseFloat(window.viewer.getPitch());
        } catch (e) {
            var yaw = 0;
            var pitch = 0;
        }
        var lookat = $('#lookat_add option:selected').attr('id');
        window.currentYaw = yaw;
        window.currentPitch = pitch;
        $.ajax({
            url: "ajax/add_marker.php",
            type: "POST",
            data: {
                id_virtualtour: window.id_virtualtour,
                id_room: id_room,
                id_room_target: id_room_target,
                yaw: yaw,
                pitch: pitch,
                yaw_m: yaw_m,
                pitch_m: pitch_m,
                embed_type: window.embed_type_sel,
                lookat: lookat
            },
            async: true,
            success: function (json) {
                var rsp = JSON.parse(json);
                if(rsp.status=="ok") {
                    $('#modal_add_marker').modal("hide");
                    $('#modal_add_marker button').prop("disabled", false);
                    var count_marker = parseInt($('#count_marker_'+id_room).html());
                    count_marker = count_marker + 1;
                    $('#count_marker_'+id_room).html(count_marker);
                    select_room_marker(id_room,image,rsp.id);
                } else {
                    $('#modal_add_marker button').prop("disabled", false);
                }
            }
        });
    }

    window.new_poi = function(type) {
        var id_room = window.new_poi_id_room;
        var image = window.new_poi_image;
        $('#modal_add_poi button').prop("disabled",true);
        try {
            var yaw = parseFloat(window.viewer.getYaw());
            var pitch = parseFloat(window.viewer.getPitch());
        } catch (e) {
            var yaw = 0;
            var pitch = 0;
        }
        window.currentYaw = yaw;
        window.currentPitch = pitch;
        $.ajax({
            url: "ajax/add_poi.php",
            type: "POST",
            data: {
                id_virtualtour: window.id_virtualtour,
                id_room: id_room,
                yaw: yaw,
                pitch: pitch,
                type: type,
                embed_type: window.embed_type_sel
            },
            async: true,
            success: function (json) {
                var rsp = JSON.parse(json);
                if(rsp.status=="ok") {
                    $('#modal_add_poi').modal("hide");
                    $('#modal_add_poi button').prop("disabled", false);
                    var count_poi = parseInt($('#count_poi_'+id_room).html());
                    count_poi = count_poi + 1;
                    $('#count_poi_'+id_room).html(count_poi);
                    select_room_poi(id_room,image,rsp.id);
                } else {
                    $('#modal_add_poi button').prop("disabled", false);
                }
            }
        });
    }

    window.modal_delete_map = function(id) {
        $('#btn_delete_map').attr('onclick','delete_map('+id+');');
        $('#modal_delete_map').modal("show");
    }

    window.modal_delete_virtualtour = function(id) {
        $('#btn_delete_virtualtour').attr('onclick','delete_virtualtour('+id+');');
        $('#modal_delete_virtualtour').modal("show");
    }

    window.modal_duplicate_virtualtour = function(id) {
        $('#btn_duplicate_virtualtour').attr('onclick','duplicate_virtualtour('+id+');');
        $('#modal_duplicate_virtualtour').modal("show");
    }

    window.modal_export_virtualtour = function(id) {
        $('#btn_export_virtualtour').attr('onclick','export_virtualtour('+id+');');
        $('#btn_export_virtualtour_b').attr('onclick','export_virtualtour_b('+id+');');
        $('#modal_export_virtualtour').modal("show");
    }

    window.modal_duplicate_room = function(id) {
        $('#btn_duplicate_room').attr('onclick','duplicate_room('+id+');');
        $('#modal_duplicate_room').modal("show");
    }

    window.modal_delete_room = function(id) {
        $('#btn_delete_room').attr('onclick','delete_room('+id+');');
        $('#modal_delete_room').modal("show");
    }

    window.modal_delete_marker = function(id,id_room,image) {
        $('#btn_delete_marker').attr('onclick','delete_marker('+id+','+id_room+',\''+image+'\')');
        $('#modal_delete_marker').modal("show");
    }

    window.modal_delete_poi = function(id,id_room,image) {
        $('#btn_delete_poi').attr('onclick','delete_poi('+id+','+id_room+',\''+image+'\')');
        $('#modal_edit_poi').modal('hide');
        $('#modal_delete_poi').modal("show");
    }

    window.modal_duplicate_poi = function(id,id_room) {
        get_option_rooms_duplicate('room_target',id_room);
        $('#btn_duplicate_poi').attr('onclick','duplicate_poi('+id+')');
        $('#modal_edit_poi').modal('hide');
        $('#modal_duplicate_poi').modal("show");
    }

    window.modal_delete_map_point = function(id) {
        $('#btn_delete_map_point').attr('onclick','delete_map_point('+id+');');
        $('#modal_delete_map_point').modal("show");
    }

    window.delete_virtualtour = function (id) {
        $('#modal_delete_virtualtour button').addClass("disabled");
        $.ajax({
            url: "ajax/delete_virtual_tour.php",
            type: "POST",
            data: {
                id_user: window.id_user,
                id_virtualtour: id
            },
            async: true,
            success: function (json) {
                var rsp = JSON.parse(json);
                if (rsp.status == "ok") {
                    $('#modal_delete_virtualtour button').removeClass("disabled");
                    $('#modal_delete_virtualtour').modal("hide");
                    location.reload();
                } else {
                    $('#modal_delete_virtualtour button').removeClass("disabled");
                }
            }
        });
    };

    window.import_tour = function (file_name) {
        $('#modal_import_tour .modal-footer .btn').addClass('disabled');
        var btn_html =  $('#btn_import_tour').html();
        $('#btn_import_tour').html('<i class="fas fa-circle-notch fa-spin"></i>');
        $.ajax({
            url: "../services/import_backend_vt.php",
            type: "POST",
            timeout: 300000,
            async: true,
            data: {
                file_name: file_name
            },
            success: function (json) {
                var rsp = JSON.parse(json);
                if(rsp.status=='ok') {
                    $('#modal_import_tour').modal('hide');
                    location.reload();
                } else {
                    $('#btn_import_tour').html(btn_html);
                    $('#modal_import_tour .modal-footer .btn').removeClass('disabled');
                    alert(rsp.msg);
                }
            }
        });
    }

    window.create_sample_tour = function () {
        $('#modal_sample_tour button').addClass("disabled");
        $('#btn_create_sample_tour').html('<i class="fas fa-circle-notch fa-spin"></i>');
        var name = $('#name').val();
        var author = $('#author').val();
        $.ajax({
            url: "../services/create_sample_tour.php",
            type: "POST",
            timeout: 300000,
            async: true,
            data: {
                name: name,
                author: author
            },
            success: function (json) {
                var rsp = JSON.parse(json);
                $('#modal_sample_tour button').removeClass("disabled");
                $('#modal_sample_tour').modal("hide");
                $.ajax({
                    url: "ajax/send_email.php",
                    type: "POST",
                    data: {
                        type: 'vt_create',
                        id_user: id_user,
                        id_vt: rsp.id
                    },
                    timeout: 15000,
                    async: true,
                    success: function () {}
                });
                if(window.create_and_edit) {
                    if (rsp.id != "") {
                        location.href="index.php?p=edit_virtual_tour&id="+rsp.id;
                    } else {
                        location.reload();
                    }
                } else {
                    location.reload();
                }
            }
        });
    }

    window.change_vt_type = function () {
        var external = $('#external option:selected').attr("id");
        if(external=="1") {
            $('#btn_virtualtour_sample').addClass("disabled");
        } else {
            $('#btn_virtualtour_sample').removeClass("disabled");
        }
    }

    window.duplicate_virtualtour = function (id) {
        $('#modal_duplicate_virtualtour button').addClass("disabled");
        var html_btn = $('#btn_duplicate_virtualtour').html();
        $('#btn_duplicate_virtualtour').html('<i class="fas fa-circle-notch fa-spin"></i>');
        var duplicate_info_box = $('#duplicate_info_box').is(':checked')?1:0;
        var duplicate_maps = $('#duplicate_maps').is(':checked')?1:0;
        var duplicate_gallery = $('#duplicate_gallery').is(':checked')?1:0;
        var duplicate_presentation = $('#duplicate_presentation').is(':checked')?1:0;
        var duplicate_rooms = $('#duplicate_rooms').is(':checked')?1:0;
        var duplicate_markers = $('#duplicate_markers').is(':checked')?1:0;
        var duplicate_pois = $('#duplicate_pois').is(':checked')?1:0;
        var duplicate_products = $('#duplicate_products').is(':checked')?1:0;
        $.ajax({
            url: "ajax/duplicate_virtual_tour.php",
            type: "POST",
            timeout: 300000,
            data: {
                id_user: window.id_user,
                id_virtualtour: id,
                duplicate_info_box: duplicate_info_box,
                duplicate_maps: duplicate_maps,
                duplicate_gallery: duplicate_gallery,
                duplicate_presentation: duplicate_presentation,
                duplicate_rooms: duplicate_rooms,
                duplicate_markers: duplicate_markers,
                duplicate_pois: duplicate_pois,
                duplicate_products: duplicate_products
            },
            async: true,
            success: function (json) {
                var rsp = JSON.parse(json);
                if (rsp.status == "ok") {
                    $('#modal_duplicate_virtualtour button').removeClass("disabled");
                    $('#modal_duplicate_virtualtour').modal("hide");
                    location.reload();
                } else {
                    $('#btn_duplicate_virtualtour').html(html_btn);
                    $('#modal_duplicate_virtualtour button').removeClass("disabled");
                }
            }
        });
    };

    window.export_virtualtour = function (id) {
        $('#modal_export_virtualtour button').addClass("disabled");
        var html_btn = $('#btn_export_virtualtour').html();
        $('#btn_export_virtualtour').html('<i class="fas fa-circle-notch fa-spin"></i>');
        $.ajax({
            url: "../services/export_vt.php",
            type: "POST",
            timeout: 300000,
            data: {
                id_virtualtour: id
            },
            async: true,
            success: function (json) {
                $('#btn_export_virtualtour').html(html_btn);
                $('#modal_export_virtualtour button').removeClass("disabled");
                var rsp = JSON.parse(json);
                if (rsp.status == "ok") {
                    $('#modal_export_virtualtour').modal("hide");
                    var zip = rsp.zip;
                    var url="../services/export_tmp/"+zip;
                    window.open(url, '_self');
                }
            },
            error: function () {
                $('#btn_export_virtualtour').html(html_btn);
                $('#modal_export_virtualtour button').removeClass("disabled");
            }
        });
    };

    window.export_virtualtour_b = function (id) {
        $('#modal_export_virtualtour button').addClass("disabled");
        var html_btn = $('#btn_export_virtualtour_b').html();
        $('#btn_export_virtualtour_b').html('<i class="fas fa-circle-notch fa-spin"></i>');
        $.ajax({
            url: "../services/export_backend_vt.php",
            type: "POST",
            timeout: 300000,
            data: {
                id_virtualtour: id
            },
            async: true,
            success: function (json) {
                $('#btn_export_virtualtour_b').html(html_btn);
                $('#modal_export_virtualtour button').removeClass("disabled");
                var rsp = JSON.parse(json);
                if (rsp.status == "ok") {
                    $('#modal_export_virtualtour').modal("hide");
                    var zip = rsp.zip;
                    var url="../services/export_tmp/"+zip;
                    window.open(url, '_self');
                }
            },
            error: function () {
                $('#btn_export_virtualtour_b').html(html_btn);
                $('#modal_export_virtualtour button').removeClass("disabled");
            }
        });
    };

    window.duplicate_room = function (id) {
        $('#modal_duplicate_room button').addClass("disabled");
        var html_btn = $('#btn_duplicate_room').html();
        $('#btn_duplicate_room').html('<i class="fas fa-circle-notch fa-spin"></i>');
        var duplicate_pois = $('#duplicate_pois').is(':checked')?1:0;
        $.ajax({
            url: "ajax/duplicate_room.php",
            type: "POST",
            timeout: 300000,
            data: {
                id_room: id,
                duplicate_pois: duplicate_pois
            },
            async: true,
            success: function (json) {
                var rsp = JSON.parse(json);
                if (rsp.status == "ok") {
                    $('#modal_duplicate_room button').removeClass("disabled");
                    $('#modal_duplicate_room').modal("hide");
                    location.reload();
                } else {
                    $('#btn_duplicate_room').html(html_btn);
                    $('#modal_duplicate_room button').removeClass("disabled");
                }
            }
        });
    };

    window.delete_room = function (id) {
        $('#modal_delete_room button').addClass("disabled");
        $.ajax({
            url: "ajax/delete_room.php",
            type: "POST",
            data: {
                id_user: window.id_user,
                id_room: id
            },
            async: true,
            success: function (json) {
                var rsp = JSON.parse(json);
                if (rsp.status == "ok") {
                    $('#modal_delete_room button').removeClass("disabled");
                    $('#modal_delete_room').modal("hide");
                    location.reload();
                } else {
                    $('#modal_delete_room button').removeClass("disabled");
                }
            }
        });
    };

    window.delete_marker = function (id,id_room,image) {
        $('#modal_delete_marker button').addClass("disabled");
        $.ajax({
            url: "ajax/delete_marker.php",
            type: "POST",
            data: {
                id_marker: id
            },
            async: true,
            success: function (json) {
                var rsp = JSON.parse(json);
                if (rsp.status == "ok") {
                    $('#modal_delete_marker button').removeClass("disabled");
                    $('#modal_delete_marker').modal("hide");
                    var count_marker = parseInt($('#count_marker_'+id_room).html());
                    count_marker = count_marker - 1;
                    $('#count_marker_'+id_room).html(count_marker);
                    try {
                        var yaw = parseFloat(window.viewer.getYaw());
                        var pitch = parseFloat(window.viewer.getPitch());
                    } catch (e) {
                        var yaw = 0;
                        var pitch = 0;
                    }
                    window.currentYaw = yaw;
                    window.currentPitch = pitch;
                    select_room_marker(id_room,image,null);
                } else {
                    $('#modal_delete_marker button').removeClass("disabled");
                }
            }
        });
    };

    window.duplicate_poi = function (id) {
        $('#modal_duplicate_poi button').addClass("disabled");
        var id_room_target = $('#room_target option:selected').attr('id');
        var image = $('#room_target option:selected').attr('data-image');
        $.ajax({
            url: "ajax/duplicate_poi.php",
            type: "POST",
            data: {
                id_poi: id,
                id_room_target: id_room_target
            },
            async: true,
            success: function (json) {
                var rsp = JSON.parse(json);
                if (rsp.status == "ok") {
                    $('#modal_duplicate_poi button').removeClass("disabled");
                    $('#modal_duplicate_poi').modal("hide");
                    var count_poi = parseInt($('#count_poi_'+id_room_target).html());
                    count_poi = count_poi + 1;
                    $('#count_poi_'+id_room_target).html(count_poi);
                    try {
                        var yaw = parseFloat(window.viewer.getYaw());
                        var pitch = parseFloat(window.viewer.getPitch());
                    } catch (e) {
                        var yaw = 0;
                        var pitch = 0;
                    }
                    window.currentYaw = yaw;
                    window.currentPitch = pitch;
                    select_room_poi(id_room_target,image,rsp.id);
                } else {
                    $('#modal_duplicate_poi button').removeClass("disabled");
                }
            }
        });
    }

    window.delete_poi = function (id,id_room,image) {
        $('#modal_delete_poi button').addClass("disabled");
        $.ajax({
            url: "ajax/delete_poi.php",
            type: "POST",
            data: {
                id_poi: id
            },
            async: true,
            success: function (json) {
                var rsp = JSON.parse(json);
                if (rsp.status == "ok") {
                    $('#modal_delete_poi button').removeClass("disabled");
                    $('#modal_delete_poi').modal("hide");
                    var count_poi = parseInt($('#count_poi_'+id_room).html());
                    count_poi = count_poi - 1;
                    $('#count_poi_'+id_room).html(count_poi);
                    try {
                        var yaw = parseFloat(window.viewer.getYaw());
                        var pitch = parseFloat(window.viewer.getPitch());
                    } catch (e) {
                        var yaw = 0;
                        var pitch = 0;
                    }
                    window.currentYaw = yaw;
                    window.currentPitch = pitch;
                    select_room_poi(id_room,image,null);
                } else {
                    $('#modal_delete_poi button').removeClass("disabled");
                }
            }
        });
    };

    window.delete_map_point = function (id) {
        $('#modal_delete_map_point button').addClass("disabled");
        $.ajax({
            url: "ajax/delete_map_point.php",
            type: "POST",
            data: {
                id_room: id,
                map_type: window.map_type
            },
            async: true,
            success: function (json) {
                var rsp = JSON.parse(json);
                if (rsp.status == "ok") {
                    $('#modal_delete_map_point button').removeClass("disabled");
                    $('#modal_delete_map_point').modal("hide");
                    sessionStorage.setItem('add_point',true);
                    location.reload();
                } else {
                    $('#modal_delete_map_point button').removeClass("disabled");
                }
            }
        });
    };

    window.delete_map = function (id_map) {
        $('#modal_delete_map button').addClass("disabled");
        $.ajax({
            url: "ajax/delete_map.php",
            type: "POST",
            data: {
                id_virtualtour: window.id_virtualtour,
                id_map: id_map
            },
            async: true,
            success: function (json) {
                var rsp = JSON.parse(json);
                if (rsp.status == "ok") {
                    $('#modal_delete_map button').removeClass("disabled");
                    $('#modal_delete_map').modal("hide");
                    location.reload();
                } else {
                    $('#modal_delete_map button').removeClass("disabled");
                }
            }
        });
    };

    window.change_exist_logo = function() {
        var logo = $('#exist_logo option:selected').attr('id');
        if(logo==0) {
            window.logo = '';
            window.vt_need_save = true;
            $('#div_delete_logo').hide();
            $('#div_image_logo').hide();
            $('#div_upload_logo').show();
            $('#div_exist_logo').show();
            $('#div_link_logo').hide();
            $('#div_image_logo img').attr('src','');
        } else {
            window.logo = logo;
            window.vt_need_save = true;
            $('#div_delete_logo').hide();
            $('#div_image_logo').show();
            $('#div_upload_logo').hide();
            $('#div_exist_logo').show();
            $('#div_link_logo').show();
            $('#div_image_logo img').attr('src','../viewer/content/'+logo);
        }
    }

    window.change_exist_introd = function() {
        var introd = $('#exist_introd option:selected').attr('id');
        if(introd==0) {
            window.intro_desktop = '';
            window.vt_need_save = true;
            $('#div_delete_introd').hide();
            $('#div_image_introd').hide();
            $('#div_upload_introd').show();
            $('#div_exist_introd').show();
            $('#div_image_introd img').attr('src','');
        } else {
            window.intro_desktop = introd;
            window.vt_need_save = true;
            $('#div_delete_introd').hide();
            $('#div_image_introd').show();
            $('#div_upload_introd').hide();
            $('#div_exist_introd').show();
            $('#div_image_introd img').attr('src','../viewer/content/'+introd);
        }
    }

    window.change_exist_introm = function() {
        var introm = $('#exist_introm option:selected').attr('id');
        if(introm==0) {
            window.intro_mobile = '';
            window.vt_need_save = true;
            $('#div_delete_introm').hide();
            $('#div_image_introm').hide();
            $('#div_upload_introm').show();
            $('#div_exist_introm').show();
            $('#div_image_introm img').attr('src','');
        } else {
            window.intro_mobile = introm;
            window.vt_need_save = true;
            $('#div_delete_introm').hide();
            $('#div_image_introm').show();
            $('#div_upload_introm').hide();
            $('#div_exist_introm').show();
            $('#div_image_introm img').attr('src','../viewer/content/'+introm);
        }
    }

    window.change_exist_bg = function() {
        var logo = $('#exist_bg option:selected').attr('id');
        if(logo==0) {
            window.background_image = '';
            window.vt_need_save = true;
            $('#div_delete_bg').hide();
            $('#div_image_bg').hide();
            $('#div_upload_bg').show();
            $('#div_exist_bg').show();
            $('#div_image_bg img').attr('src','');
        } else {
            window.background_image = logo;
            window.vt_need_save = true;
            $('#div_delete_bg').hide();
            $('#div_image_bg').show();
            $('#div_upload_bg').hide();
            $('#div_exist_bg').show();
            $('#div_image_bg img').attr('src','../viewer/content/'+logo);
        }
    }

    window.change_exist_video_bg = function() {
        var video = $('#exist_video_bg option:selected').attr('id');
        if(video==0) {
            window.background_video = '';
            window.vt_need_save = true;
            $('#div_delete_video_bg').hide();
            $('#div_video_bg').hide();
            $('#div_upload_video_bg').show();
            $('#div_exist_video_bg').show();
            $('#div_video_bg video source').attr('src','');
            $('#div_video_bg video').get(0).load();
            $('#background_video_delay').prop('disabled',true);
        } else {
            window.background_video = video;
            window.vt_need_save = true;
            $('#div_delete_video_bg').hide();
            $('#div_video_bg').show();
            $('#div_upload_video_bg').hide();
            $('#div_exist_video_bg').show();
            $('#div_video_bg video source').attr('src','../viewer/content/'+video+'#t=2');
            $('#div_video_bg video').get(0).load();
            $('#background_video_delay').prop('disabled',false);
        }
    }

    window.change_exist_nadir_logo = function() {
        var logo = $('#exist_nadir_logo option:selected').attr('id');
        if(logo==0) {
            window.nadir_logo = '';
            window.vt_need_save = true;
            $('#div_delete_nadir_logo').hide();
            $('#div_image_nadir_logo').hide();
            $('#div_upload_nadir_logo').show();
            $('#div_exist_nadir_logo').show();
            $('#div_size_nadir_logo').hide();
            $('#div_image_nadir_logo img').attr('src','');
        } else {
            window.nadir_logo = logo;
            window.vt_need_save = true;
            $('#div_delete_nadir_logo').hide();
            $('#div_image_nadir_logo').show();
            $('#div_upload_nadir_logo').hide();
            $('#div_exist_nadir_logo').show();
            $('#div_size_nadir_logo').show();
            $('#div_image_nadir_logo img').attr('src','../viewer/content/'+logo);
        }
    }

    window.change_exist_song = function() {
        var song = $('#exist_song option:selected').attr('id');
        if(song==0) {
            window.song = '';
            window.vt_need_save = true;
            $('#div_delete_song').hide();
            $('#div_player_song').hide();
            $('#div_upload_song').show();
            $('#div_exist_song').show();
            $('#div_player_song audio').attr('src','');
        } else {
            window.song = song;
            window.vt_need_save = true;
            $('#div_delete_song').hide();
            $('#div_player_song').show();
            $('#div_upload_song').hide();
            $('#div_exist_song').show();
            $('#div_player_song audio').attr('src','../viewer/content/'+song);
        }
    }

    window.delete_song = function() {
        window.song = '';
        window.vt_need_save = true;
        $('#div_delete_song').hide();
        $('#div_player_song').hide();
        $('#div_upload_song').show();
        $('#div_exist_song').show();
        $('#div_player_song audio').attr('src','');
    }

    window.delete_room_song = function() {
        window.song = '';
        window.room_need_save = true;
        $('#div_delete_song').hide();
        $('#div_player_song').hide();
        $('#div_upload_song').show();
        $('#div_exist_song').show();
        $('#div_player_song audio').attr('src','');
    }

    window.delete_logo = function () {
        window.logo = '';
        window.vt_need_save = true;
        $('#div_delete_logo').hide();
        $('#div_image_logo').hide();
        $('#div_upload_logo').show();
        $('#div_exist_logo').show();
        $('#div_link_logo').hide();
        $('#div_image_logo img').attr('src','');
    }

    window.delete_introd = function () {
        window.intro_desktop = '';
        window.vt_need_save = true;
        $('#div_delete_introd').hide();
        $('#div_image_introd').hide();
        $('#div_upload_introd').show();
        $('#div_exist_introd').show();
        $('#div_image_introd img').attr('src','');
    }

    window.delete_introm = function () {
        window.intro_mobile = '';
        window.vt_need_save = true;
        $('#div_delete_introm').hide();
        $('#div_image_introm').hide();
        $('#div_upload_introm').show();
        $('#div_exist_introm').show();
        $('#div_image_introm img').attr('src','');
    }

    window.delete_bg = function () {
        window.background_image = '';
        window.vt_need_save = true;
        $('#div_delete_bg').hide();
        $('#div_image_bg').hide();
        $('#div_upload_bg').show();
        $('#div_exist_bg').show();
        $('#div_image_bg img').attr('src','');
    }

    window.delete_video_bg = function () {
        window.background_video = '';
        window.vt_need_save = true;
        $('#div_delete_video_bg').hide();
        $('#div_video_bg').hide();
        $('#div_upload_video_bg').show();
        $('#div_exist_video_bg').show();
        $('#div_video_bg video source').attr('src','');
        $('#div_video_bg video').get(0).load();
        $('#background_video_delay').prop('disabled',true);
    }

    window.delete_b_bg = function () {
        window.b_background_image = '';
        window.settings_need_save = true;
        $('#div_delete_bg').hide();
        $('#div_image_bg').hide();
        $('#div_upload_bg').show();
        $('#div_image_bg img').attr('src','');
    }

    window.delete_b_bg_reg = function () {
        window.b_background_reg_image = '';
        window.settings_need_save = true;
        $('#div_delete_bg_reg').hide();
        $('#div_image_bg_reg').hide();
        $('#div_upload_bg_reg').show();
        $('#div_image_bg_reg img').attr('src','');
    }

    window.delete_s_banner = function () {
        window.s_banner_image = '';
        window.showcase_need_save = true;
        $('#div_delete_banner').hide();
        $('#div_image_banner').hide();
        $('#div_upload_banner').show();
        $('#div_image_banner img').attr('src','');
    }

    window.delete_b_logo = function () {
        window.b_logo_image = '';
        window.settings_need_save = true;
        $('#div_delete_logo').hide();
        $('#div_image_logo').hide();
        $('#div_upload_logo').show();
        $('#div_image_logo img').attr('src','');
    }

    window.delete_room_logo = function () {
        window.logo = '';
        window.room_need_save = true;
        $('#div_delete_logo').hide();
        $('#div_image_logo').hide();
        $('#div_upload_logo').show();
        $('#div_image_logo img').attr('src','');
    }

    window.delete_b_logo_s = function () {
        window.b_logo_s_image = '';
        window.settings_need_save = true;
        $('#div_delete_logo_s').hide();
        $('#div_image_logo_s').hide();
        $('#div_upload_logo_s').show();
        $('#div_image_logo_s img').attr('src','');
    }

    window.delete_s_logo = function () {
        window.s_logo_image = '';
        window.showcase_need_save = true;
        $('#div_delete_logo').hide();
        $('#div_image_logo').hide();
        $('#div_upload_logo').show();
        $('#div_image_logo img').attr('src','');
    }

    window.delete_ad_logo = function () {
        window.image_advertisement = '';
        window.advertisement_need_save = true;
        $('#div_delete_logo').hide();
        $('#div_image_logo').hide();
        $('#div_upload_logo').show();
        $('#div_image_logo img').attr('src','');
    }

    window.delete_ad_video = function () {
        window.video_advertisement = '';
        window.advertisement_need_save = true;
        $('#div_delete_video').hide();
        $('#div_video').hide();
        $('#div_upload_video').show();
        $('#div_video video').attr('src','');
    }

    window.delete_nadir_logo = function () {
        window.nadir_logo = '';
        window.vt_need_save = true;
        $('#div_delete_nadir_logo').hide();
        $('#div_image_nadir_logo').hide();
        $('#div_size_nadir_logo').hide();
        $('#div_upload_nadir_logo').show();
        $('#div_exist_nadir_logo').show();
        $('#div_image_nadir_logo img').attr('src','');
    }

    window.get_rooms_alt_images = function (id_room) {
        $.ajax({
            url: "ajax/get_rooms_alt_images.php",
            type: "POST",
            data: {
                id_room: id_room
            },
            async: true,
            success: function (json) {
                var rsp = JSON.parse(json);
                if(rsp.length>0) {
                    parse_rooms_alt_images(rsp);
                    $('#virtual_staging').removeClass('disabled');
                    $('#main_view_tooltip').removeClass('disabled');
                } else {
                    $('#list_rooms_alt').html("");
                    $('#virtual_staging').addClass('disabled');
                    $('#main_view_tooltip').addClass('disabled');
                }
            }
        });
    }

    window.get_gallery_images = function(id_virtualtour) {
        $.ajax({
            url: "ajax/get_gallery_images.php",
            type: "POST",
            data: {
                id_virtualtour: id_virtualtour
            },
            async: true,
            success: function (json) {
                var rsp = JSON.parse(json);
                if(rsp.length>0) {
                    parse_gallery_images(rsp);
                } else {
                    $('#list_images').html("<p>"+window.backend_labels.no_image_msg+"</p>");
                }
            }
        });
    }

    window.get_product_images = function(id_product) {
        $.ajax({
            url: "ajax/get_product_images.php",
            type: "POST",
            data: {
                id_product: id_product
            },
            async: true,
            success: function (json) {
                var rsp = JSON.parse(json);
                if(rsp.length>0) {
                    parse_product_images(rsp);
                } else {
                    $('#list_images').html("<p>"+window.backend_labels.no_image_msg+"</p>");
                }
            }
        });
    }

    window.get_media_library_files = function(id_virtualtour) {
        $.ajax({
            url: "ajax/get_media_library_files.php",
            type: "POST",
            data: {
                id_virtualtour: id_virtualtour,
                type: 'all'
            },
            async: true,
            success: function (json) {
                var rsp = JSON.parse(json);
                if(rsp.length>0) {
                    parse_media_library_files(rsp);
                } else {
                    $('#list_files').html("<p>"+window.backend_labels.no_files_msg+"</p>");
                }
            }
        });
    }

    window.get_music_library_files = function(id_virtualtour) {
        $.ajax({
            url: "ajax/get_music_library_files.php",
            type: "POST",
            data: {
                id_virtualtour: id_virtualtour
            },
            async: true,
            success: function (json) {
                var rsp = JSON.parse(json);
                if(rsp.length>0) {
                    parse_music_library_files(rsp);
                } else {
                    $('#list_files').html("<p>"+window.backend_labels.no_files_msg+"</p>");
                }
            }
        });
    }

    window.get_poi_gallery_images = function(id_poi) {
        $.ajax({
            url: "ajax/get_poi_gallery_images.php",
            type: "POST",
            data: {
                id_poi: id_poi
            },
            async: true,
            success: function (json) {
                var rsp = JSON.parse(json);
                if(rsp.length>0) {
                    parse_poi_gallery_images(rsp);
                } else {
                    $('#list_images').html("<p>"+window.backend_labels.no_image_msg+"</p>");
                }
            }
        });
    }

    window.get_poi_embed_gallery_images = function(id_poi) {
        $.ajax({
            url: "ajax/get_poi_embed_gallery_images.php",
            type: "POST",
            data: {
                id_poi: id_poi
            },
            async: true,
            success: function (json) {
                var rsp = JSON.parse(json);
                if(rsp.length>0) {
                    parse_poi_embed_gallery_images(rsp);
                } else {
                    $('#list_images').html("<p>"+window.backend_labels.no_image_msg+"</p>");
                }
            }
        });
    }

    window.get_poi_object360_images = function(id_poi) {
        $.ajax({
            url: "ajax/get_poi_object360_images.php",
            type: "POST",
            data: {
                id_poi: id_poi
            },
            async: true,
            success: function (json) {
                var rsp = JSON.parse(json);
                if(rsp.length>0) {
                    parse_poi_object360_images(rsp);
                } else {
                    $('#list_images').html("<p>"+window.backend_labels.no_image_msg+"</p>");
                }
            }
        });
    }

    window.get_icon_images = function(id_virtualtour) {
        $.ajax({
            url: "ajax/get_icon_images.php",
            type: "POST",
            data: {
                id_virtualtour: id_virtualtour
            },
            async: true,
            success: function (json) {
                var rsp = JSON.parse(json);
                if(rsp.length>0) {
                    parse_icon_images(rsp);
                } else {
                    $('#list_images').html("<p>"+window.backend_labels.no_icon_msg+"</p>");
                }
            }
        });
    }

    window.get_icon_images_m = function(id_virtualtour,m) {
        $.ajax({
            url: "ajax/get_icon_images_m.php",
            type: "POST",
            data: {
                id_virtualtour: id_virtualtour,
                m: m
            },
            async: true,
            success: function (json) {
                var rsp = JSON.parse(json);
                switch(m) {
                    case 'marker':
                    case 'marker_h':
                        $('#list_images_im').html(rsp.html).promise().done(function() {
                            switch (m) {
                                case 'marker':
                                    $('.lottie_icon_marker_list').each(function () {
                                        var id = $(this).attr('data-id');
                                        var image = $(this).attr('data-image');
                                        bodymovin.loadAnimation({
                                            container: document.getElementById('lottie_icon_marker_'+id),
                                            renderer: 'svg',
                                            loop: true,
                                            autoplay: true,
                                            path: '../viewer/icons/'+image,
                                            rendererSettings: {
                                                progressiveLoad: true,
                                            }
                                        });
                                    });
                                    break;
                                case 'marker_h':
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
                                    break;
                            }
                        });
                        break;
                    case 'poi':
                    case 'poi_h':
                        $('#list_images_ip').html(rsp.html).promise().done(function() {
                            switch (m) {
                                case 'poi':
                                    $('.lottie_icon_poi_list').each(function () {
                                        var id = $(this).attr('data-id');
                                        var image = $(this).attr('data-image');
                                        bodymovin.loadAnimation({
                                            container: document.getElementById('lottie_icon_poi_'+id),
                                            renderer: 'svg',
                                            loop: true,
                                            autoplay: true,
                                            path: '../viewer/icons/'+image,
                                            rendererSettings: {
                                                progressiveLoad: true,
                                            }
                                        });
                                    });
                                    break;
                                case 'poi_h':
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
                                    break;
                            }
                        });
                        break;
                }
            }
        });
    }

    window.preview_poi_content = function (id) {
        var content = $('#'+id).val();
        if(content!='') {
            var ext = content.split('.').pop().toLowerCase();
            switch (ext) {
                case 'mp3':
                    if(content.startsWith('http')) {
                        var url = content;
                    } else {
                        var url = '../viewer/'+content;
                    }
                    content = '<div><audio controls preload="auto" playsinline webkit-playsinline><source src="'+url+'" type="audio/mpeg"></audio></div>';
                    $.fancybox.open({
                        src: content,
                        type: 'html'
                    });
                    break;
                case 'glb':
                case 'gltf':
                case 'usdz':
                    var array_files = content.split(",");
                    var glb_file = '';
                    jQuery.each(array_files, function(index_s, file_s) {
                        if(file_s.split('.').pop().toLowerCase()=='glb') {
                            glb_file = file_s;
                        }
                        if(file_s.split('.').pop().toLowerCase()=='gltf') {
                            glb_file = file_s;
                        }
                    });
                    if(content.startsWith('http')) {
                        var url = content;
                    } else {
                        var url = '../viewer/'+glb_file;
                    }
                    var html = '<div class="poi_object3d_content"><model-viewer src="'+url+'" alt="" environment-image="neutral" auto-rotate camera-controls></model-viewer></div>';
                    $.fancybox.open({
                        src  : html,
                        type : 'html',
                        touch: false,
                        smallBtn: false,
                        clickOutside: false,
                        afterShow : function() {}
                    });
                    break;
                case 'json':
                    if(content.startsWith('http')) {
                        var url = content;
                    } else {
                        var url = '../viewer/'+content;
                    }
                    var html = '<div class="poi_lottie_content"></div>';
                    $.fancybox.open({
                        src  : html,
                        type : 'html',
                        touch: false,
                        smallBtn: false,
                        clickOutside: false,
                        afterShow : function() {
                            bodymovin.loadAnimation({
                                container: document.getElementsByClassName('poi_lottie_content')[0],
                                renderer: 'svg',
                                loop: true,
                                autoplay: true,
                                path: url,
                                rendererSettings: {
                                    progressiveLoad: true,
                                }
                            });
                        }
                    });
                    break;
                default:
                    if(content.startsWith('http')) {
                        $.fancybox.open({
                            src: content,
                            type: 'iframe'
                        });
                    } else {
                        content = '../viewer/'+content;
                        $.fancybox.open({
                            src: content
                        });
                    }
                    break;
            }
        }
    }

    function parse_rooms_alt_images(rooms_alt_images) {
        var html = '';
        window.rooms_alt_images = rooms_alt_images;
        jQuery.each(rooms_alt_images, function(index, image) {
            var id = image.id;
            var image = image.panorama_image;
            html += '<div data-id="'+id+'" style="position: relative;" class="image_room_alt float-left mb-2 ml-1 mr-1"><div class="remove_image_room_alt"><i onclick="edit_view_tooltip('+index+');" style="font-size: 22px;color: white;cursor: pointer" class="fas fa-heading"></i>&nbsp;&nbsp;&nbsp;<i onclick="remove_image_room_alt('+id+');" style="font-size: 22px;color: white;cursor: pointer" class="fas fa-trash-alt"></i></div><img draggable="false" style="height: 70px;" src="../viewer/panoramas/thumb/'+image+'" /></div>';
        });
        $('#list_rooms_alt').html(html);
    }

    function parse_gallery_images(images_array) {
        var html = '';
        window.gallery_images = images_array;
        jQuery.each(images_array, function(index, image) {
            var id = image.id;
            var image = image.image;
            html += '<div data-id="'+id+'" style="position: relative;cursor: move" class="image_gallery float-left mb-2 ml-1 mr-1"><div class="remove_image_gallery"><i onclick="edit_image_gallery('+index+');" style="font-size: 22px;color: white;cursor: pointer" class="fas fa-heading"></i>&nbsp;&nbsp;&nbsp;<i onclick="remove_image_gallery('+id+');" style="font-size: 22px;color: white;cursor: pointer" class="fas fa-trash-alt"></i></div><img draggable="false" style="height: 70px;" src="../viewer/gallery/thumb/'+image+'" /></div>';
        });
        $('#list_images').html(html).promise().done(function () {
            var el = document.getElementById('list_images');
            Sortable.create(el,{
                onEnd: function (evt) {
                    var array_images_priority = [];
                    $('#list_images .image_gallery').each(function () {
                        var id = $(this).attr('data-id');
                        array_images_priority.push(id);
                    });
                    change_image_gallery_order(array_images_priority);
                },
            });
        });
    }

    function parse_product_images(images_array) {
        var html = '';
        window.product_images = images_array;
        jQuery.each(images_array, function(index, image) {
            var id = image.id;
            var image = image.image;
            html += '<div data-id="'+id+'" style="position: relative;cursor: move" class="image_product float-left mb-2 ml-1 mr-1"><div class="remove_image_product"><i onclick="remove_image_product('+id+');" style="font-size: 22px;color: white;cursor: pointer" class="fas fa-trash-alt"></i></div><img draggable="false" style="height: 70px;" src="../viewer/products/thumb/'+image+'" /></div>';
        });
        $('#list_images').html(html).promise().done(function () {
            var el = document.getElementById('list_images');
            Sortable.create(el,{
                onEnd: function (evt) {
                    var array_images_priority = [];
                    $('#list_images .image_product').each(function () {
                        var id = $(this).attr('data-id');
                        array_images_priority.push(id);
                    });
                    change_image_product_order(array_images_priority);
                },
            });
        });
    }

    function parse_media_library_files(files_array) {
        var html = '';
        window.media_library_files = files_array;
        jQuery.each(files_array, function(index, file_t) {
            var id = parseInt(file_t.id);
            var file = file_t.file;
            var from = file_t.from;
            var count = file_t.count;
            var id_vt = file_t.id_virtualtour;
            switch(from) {
                case 'media_library':
                    var src = '../viewer/media/'+file;
                    var src_t = '../viewer/media/thumb/'+file;
                    break;
                case 'content':
                    var src = '../viewer/content/'+file;
                    var src_t = '../viewer/content/thumb/'+file;
                    break;
            }
            if(file.includes('.mp4') || file.includes('.mov') || file.includes('.webm')) {
                var icon = '<span class="icon_type"><i class="fa fa-video"></i> '+count+'</span>';
                var html_file = '<video draggable="false" preload="auto" height="100px" src="'+src+'#t=2" playsinline webkit-playsinline muted style="height: 100px;"></video>';
            } else {
                var icon = '<span class="icon_type"><i class="fa fa-image"></i> '+count+'</span>';
                var html_file = '<img draggable="false" style="height: 100px;" src="'+src_t+'" />';
            }
            if(id==0 || count>0 || window.id_virtualtour!=id_vt) {
                html += '<div style="display:inline-block;position:relative;vertical-align:top;" class="file_media_library mb-2 ml-1 mr-1">'+icon+''+html_file+'</div>';
            } else {
                html += '<div style="display:inline-block;position:relative;vertical-align:top;" class="file_media_library mb-2 ml-1 mr-1">'+icon+'<div class="remove_file_media_library"><i onclick="remove_media_library_file('+id+');" style="font-size: 22px;color: white;cursor: pointer" class="fas fa-trash-alt"></i></div>'+html_file+'</div>';
            }
        });
        $('#list_files').html(html).promise().done(function () {
            $("#list_files img").each(function(){
                var image = $(this);
                if(image.context.naturalWidth == 0 || image.readyState == 'uninitialized'){
                    $(image).parent().hide();
                }
            });
        });
    }

    function parse_music_library_files(files_array) {
        var html = '';
        window.music_library_files = files_array;
        jQuery.each(files_array, function(index, file_t) {
            var id = parseInt(file_t.id);
            var file = file_t.file;
            var count = file_t.count;
            var id_vt = file_t.id_virtualtour;
            var src = '../viewer/content/'+file;
            var file_name = file.split('.').slice(0, -1).join('.');
            var icon = '<span class="icon_type noselect"><i class="fa fa-music"></i> '+count+'</span>';
            var name = '<span class="name_file noselect">'+file_name+'</span>';
            var html_file = '<audio draggable="false" controls preload="auto" playsinline webkit-playsinline><source src="'+src+'" type="audio/mpeg"></audio>';
            if(id==0 || count>0 || window.id_virtualtour!=id_vt) {
                html += '<div style="display:inline-block;position:relative;vertical-align:top;" class="file_music_library col-md-4 mb-2">'+icon+''+name+''+html_file+'</div>';
            } else {
                html += '<div style="display:inline-block;position:relative;vertical-align:top;" class="file_music_library col-md-4 mb-2">'+icon+''+name+'<i onclick="remove_music_library_file('+id+');" style="font-size: 12px;cursor: pointer" class="remove_file_music_library fas fa-trash-alt"></i>'+html_file+'</div>';
            }
        });
        $('#list_files').html(html);
    }

    function parse_poi_gallery_images(images_array) {
        var html = '';
        window.gallery_images = images_array;
        jQuery.each(images_array, function(index, image) {
            var id = image.id;
            var image = image.image;
            html += '<div data-id="'+id+'" style="position: relative;cursor: move" class="image_gallery float-left mb-2 ml-1 mr-1"><div class="remove_image_gallery"><i onclick="edit_image_poi_gallery('+index+');" style="font-size: 22px;color: white;cursor: pointer" class="fas fa-heading"></i>&nbsp;&nbsp;&nbsp;<i onclick="remove_image_poi_gallery('+id+');" style="font-size: 22px;color: white;cursor: pointer" class="fas fa-trash-alt"></i></div><img draggable="false" style="height: 70px;" src="../viewer/gallery/thumb/'+image+'" /></div>';
        });
        $('#list_images').html(html).promise().done(function () {
            var el = document.getElementById('list_images');
            Sortable.create(el,{
                onEnd: function (evt) {
                    var array_images_priority = [];
                    $('#list_images .image_gallery').each(function () {
                        var id = $(this).attr('data-id');
                        array_images_priority.push(id);
                    });
                    change_poi_image_gallery_order(array_images_priority);
                },
            });
        });
    }

    function parse_poi_embed_gallery_images(images_array) {
        var html = '';
        window.gallery_images = images_array;
        jQuery.each(images_array, function(index, image) {
            var id = image.id;
            var image = image.image;
            html += '<div data-id="'+id+'" style="position: relative;cursor: move" class="image_gallery float-left mb-2 ml-1 mr-1"><div class="remove_image_embed_gallery"><i onclick="remove_image_poi_embed_gallery('+id+');" style="font-size: 22px;color: white;cursor: pointer" class="fas fa-trash-alt"></i></div><img draggable="false" style="height: 70px;" src="../viewer/gallery/thumb/'+image+'" /></div>';
        });
        $('#list_images').html(html).promise().done(function () {
            var el = document.getElementById('list_images');
            Sortable.create(el,{
                onEnd: function (evt) {
                    var array_images_priority = [];
                    $('#list_images .image_gallery').each(function () {
                        var id = $(this).attr('data-id');
                        array_images_priority.push(id);
                    });
                    change_poi_image_embed_gallery_order(array_images_priority);
                },
            });
        });
    }

    function parse_poi_object360_images(images_array) {
        var html = '';
        window.object360_images = images_array;
        jQuery.each(images_array, function(index, image) {
            var id = image.id;
            var image = image.image;
            var millieconds = new Date().getMilliseconds();
            html += '<div data-id="'+id+'" style="position: relative;cursor: move" class="image_object360 float-left mb-2 ml-1 mr-1"><div class="remove_image_object360"><i onclick="remove_image_poi_object360('+id+');" style="font-size: 22px;color: white;cursor: pointer" class="fas fa-trash-alt"></i></div><img draggable="false" style="height: 70px;" src="../viewer/objects360/'+image+'?v='+millieconds+'" /></div>';
        });
        $('#list_images').html(html).promise().done(function () {
            var array_images_priority = [];
            $('#list_images .image_object360').each(function () {
                var id = $(this).attr('data-id');
                array_images_priority.push(id);
            });
            change_poi_image_object360_order(array_images_priority);
            var el = document.getElementById('list_images');
            Sortable.create(el,{
                onEnd: function (evt) {
                    var array_images_priority = [];
                    $('#list_images .image_object360').each(function () {
                        var id = $(this).attr('data-id');
                        array_images_priority.push(id);
                    });
                    change_poi_image_object360_order(array_images_priority);
                },
            });
        });
    }

    function parse_icon_images(images_array) {
        var html = '';
        jQuery.each(images_array, function(index, image) {
            var id = image.id;
            var id_vt = image.id_virtualtour;
            var image = image.image;
            var ext = image.split('.').pop().toLowerCase();
            if(ext=='json') {
                var html_image = '<div class="lottie_icon_list" data-id="'+id+'" data-image="'+image+'" id="lottie_icon_'+id+'" style="height:100px;width:100px;vertical-align:middle"></div>';
            } else {
                var html_image = '<img draggable="false" style="height: 100px;" src="../viewer/icons/'+image+'" />';
            }
            if(window.id_virtualtour!=id_vt) {
                html += '<div data-id="'+id+'" style="position: relative" class="image_icon float-left mb-2 ml-1 mr-1">'+html_image+'</div>';
            } else {
                html += '<div data-id="'+id+'" style="position: relative" class="image_icon float-left mb-2 ml-1 mr-1"><div class="remove_image_icon"><i onclick="remove_image_icon('+id+');" style="font-size: 24px;color: white;cursor: pointer;" class="fas fa-trash-alt"></i></div>'+html_image+'</div>';
            }
        });
        $('#list_images').html(html).promise().done(function () {
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
    }

    window.open_modal_media_library = function (type,target) {
        $('#modal_media_library .modal-body').empty();
        $('#modal_media_library').modal('show');
        $.ajax({
            url: "ajax/get_media_library_files.php",
            type: "POST",
            data: {
                id_virtualtour: id_virtualtour,
                type: type
            },
            async: true,
            success: function (json) {
                var html = '';
                var files_array = JSON.parse(json);
                if(files_array.length==0) {
                    html = "<p>"+window.backend_labels.no_files_msg+"</p>";
                } else {
                    jQuery.each(files_array, function(index, file_t) {
                        var file = file_t.file;
                        var from = file_t.from;
                        switch(from) {
                            case 'media_library':
                                var src = '../viewer/media/'+file;
                                var src_t = '../viewer/media/thumb/'+file;
                                break;
                            case 'content':
                                var src = '../viewer/content/'+file;
                                var src_t = '../viewer/content/thumb/'+file;
                                break;
                        }
                        if(file.includes('.mp4') || file.includes('.mov') || file.includes('.webm')) {
                            var html_file = '<video preload="auto" src="'+src+'#t=2" playsinline webkit-playsinline muted style="height: 100px;"></video>';
                        } else {
                            var html_file = '<img style="height: 100px;" src="'+src_t+'" />';
                        }
                        html += '<div onclick="select_media_library_file(\''+file+'\',\''+type+'\',\''+from+'\',\''+target+'\');" style="display:inline-block;position:relative;vertical-align:top;cursor:pointer;" class="file_media_library mb-2 ml-1 mr-1">'+html_file+'</div>';
                    });
                }
                $('#modal_media_library .modal-body').html(html);
            }
        });
    }

    window.open_modal_music_library = function (target) {
        $('#modal_music_library .modal-body').empty();
        $('#modal_music_library').modal('show');
        $.ajax({
            url: "ajax/get_music_library_files.php",
            type: "POST",
            data: {
                id_virtualtour: id_virtualtour
            },
            async: true,
            success: function (json) {
                var html = '';
                var files_array = JSON.parse(json);
                if(files_array.length==0) {
                    html = "<p>"+window.backend_labels.no_files_msg+"</p>";
                } else {
                    jQuery.each(files_array, function(index, file_t) {
                        var file = file_t.file;
                        var src = '../viewer/content/'+file;
                        var file_name = file.split('.').slice(0, -1).join('.');
                        var name = '<span class="name_file noselect">'+file_name+'</span>';
                        var html_file = '<audio controls preload="auto" playsinline webkit-playsinline><source src="'+src+'" type="audio/mpeg"></audio>';
                        html += '<div style="display:inline-block;position:relative;vertical-align:top;" class="file_music_library col-md-4 mb-2">'+name+'<i onclick="select_music_library_file(\''+file+'\',\''+target+'\');" style="font-size: 12px;cursor: pointer" class="remove_file_music_library fas fa-check-circle"></i>'+html_file+'</div>';
                    });
                }
                $('#modal_music_library .modal-body').html(html);
            }
        });
    }

    window.select_music_library_file = function(file,target) {
        var src = 'content/'+file;
        $('#'+target).val(src);
        $('#modal_music_library').modal('hide');
    }

    window.select_media_library_file = function(file,type,from,target) {
        switch(from) {
            case 'media_library':
                var src = 'media/'+file;
                break;
            case 'content':
                var src = 'content/'+file;
                break;
        }
        switch(type) {
            case 'all':
                switch(target) {
                    case 'html':
                        window.poi_content_html_sc.session.insert(window.poi_content_html_sc.getCursorPosition(), src);
                        break;
                    case 'html_vt':
                        window.custom_vt_html.session.insert(window.custom_vt_html.getCursorPosition(), src);
                        break;
                }
                break;
            case 'videos_transparent':
                var exists_videos = $('#'+target).val();
                var array_videos = exists_videos.split(",");
                var mov_video = '', webm_video = '';
                jQuery.each(array_videos, function(index_s, video_s) {
                    if(video_s.split('.').pop().toLowerCase()=='mov') {
                        mov_video = video_s;
                    }
                    if(video_s.split('.').pop().toLowerCase()=='webm') {
                        webm_video = video_s;
                    }
                });
                if(src.split('.').pop().toLowerCase()=='mov') {
                    mov_video = src;
                }
                if(src.split('.').pop().toLowerCase()=='webm') {
                    webm_video = src;
                }
                if(webm_video!='' && mov_video!='') {
                    var poi_embed_content = webm_video+','+mov_video;
                } else if(webm_video!='' && mov_video=='') {
                    var poi_embed_content = webm_video;
                } else if(webm_video=='' && mov_video!='') {
                    var poi_embed_content = mov_video;
                }
                $('#'+target).val(poi_embed_content);
                break;
            default:
                $('#'+target).val(src);
                break;
        }
        $('#modal_media_library').modal('hide');
        if(window.pois[poi_index_edit].embed_type!='') {
            switch(window.pois[poi_index_edit].embed_type) {
                case 'image':
                case 'video':
                case 'video_chroma':
                    window.pois[poi_index_edit].embed_content = src;
                    break;
                case 'videos_transparent':
                    window.pois[poi_index_edit].embed_content = poi_embed_content;
                    break;
            }
            render_poi(poi_id_edit,poi_index_edit);
        }
    }

    window.close_modal = function (id) {
        $('#'+id).modal('hide');
    }

    function change_rooms_order(array_rooms_priority) {
        var array_rooms_priority = JSON.stringify(array_rooms_priority);
        $.ajax({
            url: "ajax/change_rooms_order.php",
            type: "POST",
            data: {
                id_virtualtour: id_virtualtour,
                array_rooms_priority: array_rooms_priority
            },
            async: false,
            success: function (json) {

            }
        });
    }

    function change_maps_order(array_maps_priority) {
        var array_maps_priority = JSON.stringify(array_maps_priority);
        $.ajax({
            url: "ajax/change_maps_order.php",
            type: "POST",
            data: {
                id_virtualtour: id_virtualtour,
                array_maps_priority: array_maps_priority
            },
            async: false,
            success: function (json) {

            }
        });
    }

    function change_image_gallery_order(array_images_priority) {
        var array_images_priority = JSON.stringify(array_images_priority);
        $.ajax({
            url: "ajax/change_image_gallery_order.php",
            type: "POST",
            data: {
                id_virtualtour: id_virtualtour,
                array_images_priority: array_images_priority
            },
            async: false,
            success: function (json) {

            }
        });
    }

    function change_image_product_order(array_images_priority) {
        var array_images_priority = JSON.stringify(array_images_priority);
        $.ajax({
            url: "ajax/change_image_product_order.php",
            type: "POST",
            data: {
                id_product: id_product,
                array_images_priority: array_images_priority
            },
            async: false,
            success: function (json) {

            }
        });
    }

    function change_poi_image_gallery_order(array_images_priority) {
        var array_images_priority = JSON.stringify(array_images_priority);
        $.ajax({
            url: "ajax/change_poi_image_gallery_order.php",
            type: "POST",
            data: {
                id_poi: window.id_poi,
                array_images_priority: array_images_priority
            },
            async: false,
            success: function (json) {

            }
        });
    }

    function change_poi_image_embed_gallery_order(array_images_priority) {
        var array_images_priority = JSON.stringify(array_images_priority);
        $.ajax({
            url: "ajax/change_poi_image_embed_gallery_order.php",
            type: "POST",
            data: {
                id_poi: window.id_poi,
                array_images_priority: array_images_priority
            },
            async: false,
            success: function (json) {

            }
        });
    }

    function change_poi_image_object360_order(array_images_priority) {
        var array_images_priority = JSON.stringify(array_images_priority);
        $.ajax({
            url: "ajax/change_poi_image_object360_order.php",
            type: "POST",
            data: {
                id_poi: window.id_poi,
                array_images_priority: array_images_priority
            },
            async: false,
            success: function (json) {

            }
        });
    }

    window.add_image_to_gallery = function(id_virtualtour,image) {
        $.ajax({
            url: "ajax/add_image_to_gallery.php",
            type: "POST",
            data: {
                id_virtualtour: id_virtualtour,
                image: image
            },
            async: true,
            success: function (json) {
                get_gallery_images(id_virtualtour);
            }
        });
    }

    window.add_image_to_product = function(id_product,image) {
        $.ajax({
            url: "ajax/add_image_to_product.php",
            type: "POST",
            data: {
                id_product: id_product,
                image: image
            },
            async: true,
            success: function (json) {
                get_product_images(id_product);
            }
        });
    }

    window.add_file_to_media_library = function(id_virtualtour,file) {
        $.ajax({
            url: "ajax/add_file_to_media_library.php",
            type: "POST",
            data: {
                id_virtualtour: id_virtualtour,
                file: file
            },
            async: true,
            success: function (json) {
                get_media_library_files(id_virtualtour);
            }
        });
    }

    window.add_file_to_music_library = function(id_virtualtour,file) {
        $.ajax({
            url: "ajax/add_file_to_music_library.php",
            type: "POST",
            data: {
                id_virtualtour: id_virtualtour,
                file: file
            },
            async: true,
            success: function (json) {
                get_music_library_files(id_virtualtour);
            }
        });
    }

    window.add_image_to_poi_gallery = function(id_poi,image) {
        $.ajax({
            url: "ajax/add_image_to_poi_gallery.php",
            type: "POST",
            data: {
                id_poi: id_poi,
                image: image
            },
            async: true,
            success: function (json) {
                get_poi_gallery_images(id_poi);
            }
        });
    }

    window.add_image_to_poi_embed_gallery = function(id_poi,image) {
        $.ajax({
            url: "ajax/add_image_to_poi_embed_gallery.php",
            type: "POST",
            data: {
                id_poi: id_poi,
                image: image
            },
            async: true,
            success: function (json) {
                get_poi_embed_gallery_images(id_poi);
            }
        });
    }

    window.add_image_to_poi_object360 = function(id_poi,image) {
        $.ajax({
            url: "ajax/add_image_to_poi_object360.php",
            type: "POST",
            data: {
                id_poi: id_poi,
                image: image
            },
            async: true,
            success: function (json) {
                get_poi_object360_images(id_poi);
            }
        });
    }

    window.add_image_to_icon = function(id_virtualtour,image) {
        $.ajax({
            url: "ajax/add_image_to_icon.php",
            type: "POST",
            data: {
                id_virtualtour: id_virtualtour,
                image: image
            },
            async: true,
            success: function (json) {
                get_icon_images(id_virtualtour);
            }
        });
    }

    window.add_image_to_icon_m = function(id_virtualtour,image,m) {
        $.ajax({
            url: "ajax/add_image_to_icon.php",
            type: "POST",
            data: {
                id_virtualtour: id_virtualtour,
                image: image
            },
            async: true,
            success: function (json) {
                get_icon_images_m(id_virtualtour,m);
            }
        });
    }


    window.edit_view_tooltip = function (index) {
        var view_tooltip = window.rooms_alt_images[index].view_tooltip;
        $('#view_tooltip').val(view_tooltip);
        $('#modal_view_tooltip #btn_save_view_tooltip').attr('onclick','save_view_tooltip('+index+')');
        $('#modal_view_tooltip').modal("show");
    }

    window.edit_image_gallery = function (index) {
        var title = window.gallery_images[index].title;
        var description = window.gallery_images[index].description;
        $('#title').val(title);
        $('#description').val(description);
        $('#modal_caption #btn_save_caption').attr('onclick','save_gallery_caption('+index+')');
        $('#modal_caption').modal("show");
    }

    window.edit_image_poi_gallery = function (index) {
        var title = window.gallery_images[index].title;
        var description = window.gallery_images[index].description;
        $('#title').val(title);
        $('#description').val(description);
        $('#modal_caption #btn_save_caption').attr('onclick','save_poi_gallery_caption('+index+')');
        $('#modal_caption').modal("show");
    }

    window.save_view_tooltip = function(index) {
        $('#modal_view_tooltip #btn_save_view_tooltip').addClass('disabled');
        var view_tooltip = $('#view_tooltip').val();
        var id = window.rooms_alt_images[index].id;
        $.ajax({
            url: "ajax/save_view_tooltip.php",
            type: "POST",
            data: {
                id: id,
                view_tooltip: view_tooltip
            },
            async: true,
            success: function (json) {
                window.rooms_alt_images[index].view_tooltip = view_tooltip;
                $('#modal_view_tooltip #btn_save_view_tooltip').removeClass('disabled');
                $('#modal_view_tooltip').modal("hide");
            }
        });
    }

    window.save_gallery_caption = function(index) {
        $('#modal_caption #btn_save_caption').addClass('disabled');
        var title = $('#title').val();
        var description = $('#description').val();
        var id = window.gallery_images[index].id;
        $.ajax({
            url: "ajax/save_gallery_caption.php",
            type: "POST",
            data: {
                id: id,
                title: title,
                description: description
            },
            async: true,
            success: function (json) {
                window.gallery_images[index].title = title;
                window.gallery_images[index].description = description;
                $('#modal_caption #btn_save_caption').removeClass('disabled');
                $('#modal_caption').modal("hide");
            }
        });
    }

    window.save_poi_gallery_caption = function(index) {
        $('#modal_caption #btn_save_caption').addClass('disabled');
        var title = $('#title').val();
        var description = $('#description').val();
        var id = window.gallery_images[index].id;
        $.ajax({
            url: "ajax/save_poi_gallery_caption.php",
            type: "POST",
            data: {
                id: id,
                title: title,
                description: description
            },
            async: true,
            success: function (json) {
                window.gallery_images[index].title = title;
                window.gallery_images[index].description = description;
                $('#modal_caption #btn_save_caption').removeClass('disabled');
                $('#modal_caption').modal("hide");
            }
        });
    }

    window.remove_image_room_alt = function(id) {
        var retVal = confirm(window.backend_labels.delete_sure_msg);
        if( retVal == true ) {
            $.ajax({
                url: "ajax/delete_image_to_room_alt.php",
                type: "POST",
                data: {
                    id: id
                },
                async: false,
                success: function (json) {
                    get_rooms_alt_images(window.id_room);
                }
            });
        } else {
            return false;
        }
    }

    window.remove_image_gallery = function(id) {
        var retVal = confirm(window.backend_labels.delete_sure_msg);
        if( retVal == true ) {
            $.ajax({
                url: "ajax/delete_image_to_gallery.php",
                type: "POST",
                data: {
                    id: id
                },
                async: false,
                success: function (json) {
                    get_gallery_images(window.id_virtualtour);
                }
            });
        } else {
            return false;
        }
    }

    window.remove_image_product = function(id) {
        var retVal = confirm(window.backend_labels.delete_sure_msg);
        if( retVal == true ) {
            $.ajax({
                url: "ajax/delete_image_to_product.php",
                type: "POST",
                data: {
                    id: id
                },
                async: false,
                success: function (json) {
                    get_product_images(window.id_virtualtour);
                }
            });
        } else {
            return false;
        }
    }

    window.remove_media_library_file = function(id) {
        var retVal = confirm(window.backend_labels.delete_sure_msg);
        if( retVal == true ) {
            $.ajax({
                url: "ajax/delete_file_to_media_library.php",
                type: "POST",
                data: {
                    id: id
                },
                async: false,
                success: function (json) {
                    get_media_library_files(window.id_virtualtour);
                }
            });
        } else {
            return false;
        }
    }

    window.remove_music_library_file = function(id) {
        var retVal = confirm(window.backend_labels.delete_sure_msg);
        if( retVal == true ) {
            $.ajax({
                url: "ajax/delete_file_to_music_library.php",
                type: "POST",
                data: {
                    id: id
                },
                async: false,
                success: function (json) {
                    get_music_library_files(window.id_virtualtour);
                }
            });
        } else {
            return false;
        }
    }

    window.remove_image_poi_gallery = function(id) {
        var retVal = confirm(window.backend_labels.delete_sure_msg);
        if( retVal == true ) {
            $.ajax({
                url: "ajax/delete_image_to_poi_gallery.php",
                type: "POST",
                data: {
                    id: id
                },
                async: false,
                success: function (json) {
                    get_poi_gallery_images(window.id_poi);
                }
            });
        } else {
            return false;
        }
    }

    window.remove_image_poi_embed_gallery = function(id) {
        var retVal = confirm(window.backend_labels.delete_sure_msg);
        if( retVal == true ) {
            $.ajax({
                url: "ajax/delete_image_to_poi_embed_gallery.php",
                type: "POST",
                data: {
                    id: id
                },
                async: false,
                success: function (json) {
                    get_poi_embed_gallery_images(window.id_poi);
                }
            });
        } else {
            return false;
        }
    }

    window.remove_image_poi_object360 = function(id) {
        var retVal = confirm(window.backend_labels.delete_sure_msg);
        if( retVal == true ) {
            $.ajax({
                url: "ajax/delete_image_to_poi_object360.php",
                type: "POST",
                data: {
                    id: id
                },
                async: false,
                success: function (json) {
                    get_poi_object360_images(window.id_poi);
                }
            });
        } else {
            return false;
        }
    }

    window.remove_image_icon = function(id) {
        var retVal = confirm(window.backend_labels.delete_sure_msg);
        if( retVal == true ) {
            $.ajax({
                url: "ajax/delete_image_to_icon.php",
                type: "POST",
                data: {
                    id: id
                },
                async: false,
                success: function (json) {
                    get_icon_images(window.id_virtualtour);
                }
            });
        } else {
            return false;
        }
    }

    window.check_plan = function(id_user,object) {
        $.ajax({
            url: "ajax/check_plan.php",
            type: "POST",
            data: {
                id_user: id_user,
                object: object
            },
            async: false,
            success: function (json) {
                var rsp = JSON.parse(json);
                window.can_create = rsp.can_create;
            }
        });
    }

    window.set_friendly_url = function() {
        var friendly_url = $('#friendly_url').val();
        $('#btn_friendly_url').addClass('disabled');
        $.ajax({
            url: "ajax/set_friendly_url.php",
            type: "POST",
            data: {
                id_virtualtour: window.id_virtualtour,
                friendly_url: friendly_url
            },
            async: true,
            success: function (json) {
                var rsp = JSON.parse(json);
                if (rsp.status == "ok") {
                    $('#friendly_url').removeClass('error-highlight');
                    location.reload();
                } else {
                    $('#btn_friendly_url').removeClass('disabled');
                    $('#friendly_url').addClass('error-highlight');
                }
            }
        });
    }

    window.set_expiring_dates = function() {
        $('#btn_expires').addClass('disabled');
        var start_date = $('#start_date').val();
        var end_date = $('#end_date').val();
        var start_url = $('#start_url').val();
        var end_url = $('#end_url').val();
        $.ajax({
            url: "ajax/set_expiring_dates.php",
            type: "POST",
            data: {
                id_virtualtour: window.id_virtualtour,
                start_date: start_date,
                end_date: end_date,
                start_url: start_url,
                end_url: end_url
            },
            async: true,
            success: function (json) {
                var rsp = JSON.parse(json);
                if (rsp.status == "ok") {
                    location.reload();
                } else {
                    $('#btn_expires').removeClass('disabled');
                }
            }
        });
    }

    window.set_status_vt = function(status) {
        $('#btn_status').addClass('disabled');
        $.ajax({
            url: "ajax/set_status_vt.php",
            type: "POST",
            data: {
                id_virtualtour: window.id_virtualtour,
                status: status
            },
            async: true,
            success: function (json) {
                var rsp = JSON.parse(json);
                if (rsp.status == "ok") {
                    location.reload();
                } else {
                    $('#btn_status').removeClass('disabled');
                }
            }
        });
    }

    window.set_password_vt = function() {
        var password = $('#vt_password').val();
        var password_title = $('#vt_password_title').val();
        var password_description = $('#vt_password_description').val();
        $('#btn_protect').addClass('disabled');
        $.ajax({
            url: "ajax/set_password_vt.php",
            type: "POST",
            data: {
                id_virtualtour: window.id_virtualtour,
                password: password,
                password_title: password_title,
                password_description: password_description
            },
            async: true,
            success: function (json) {
                var rsp = JSON.parse(json);
                $('#btn_protect').removeClass('disabled');
            }
        });
    }

    window.check_license = function() {
        $('#btn_check_license').addClass("disabled");
        var purchase_code = $('#purchase_code').val();
        $.ajax({
            url: 'https://simpledemo.it/check_license_svt.php',
            type: "POST",
            data: {
                server_name: window.server_name,
                server_ip: window.server_ip,
                purchase_code: purchase_code
            },
            async: true,
            success: function (json) {
                var rsp = JSON.parse(json);
                switch (rsp.status) {
                    case 'ok':
                        $('#license_status').html("<i style='color: green;' class=\"fas fa-circle\"></i> "+rsp.msg);
                        var license = rsp.license;
                        if(rsp.msg.indexOf('Extended') >= 0){
                            $('#registration_li').removeClass('d-none');
                            $('#payments_li').removeClass('d-none');
                        }
                        break;
                    case 'error':
                        $('#license_status').html("<i style='color: red;' class=\"fas fa-circle\"></i> "+rsp.msg);
                        var license = '';
                        break;
                }
                $('#btn_check_license').removeClass("disabled");
                $.ajax({
                    url: "ajax/save_lic.php",
                    type: "POST",
                    data: {
                        purchase_code: purchase_code,
                        license: license
                    },
                    async: true,
                    success: function () {
                        if(window.input_license==1 && license!='') {
                            window.settings_need_save=false;
                            location.href='index.php';
                        }
                    }
                });
            }
        });
    }

    window.set_session_vt = function(id) {
        $.ajax({
            url: "ajax/set_session_vt.php",
            type: "POST",
            data: {
                id_virtualtour: id
            },
            async: true,
            success: function (json) {
                var rsp = JSON.parse(json);
                if (rsp.status == "ok") {
                    var url = window.location.href;
                    url = removeParam('id_vt',url);
                    url = removeParam('id_room',url);
                    location.href = url;
                }
            }
        });
    }

    window.set_session_theme_color = function(theme_color) {
        if(theme_color!='') {
            $.ajax({
                url: "ajax/set_session_theme_color.php",
                type: "POST",
                data: {
                    theme_color: theme_color
                },
                async: true,
                success: function (json) {
                    var current_href = $('#css_theme').attr("href").split('?')[0];
                    $('#css_theme').attr("href", current_href + "?v=" + new Date().getMilliseconds());
                }
            });
        }
    }

    window.change_virtualtour = function() {
        var id = $('#virtualtour_selector option:selected').attr('id');
        $.ajax({
            url: "ajax/set_session_vt.php",
            type: "POST",
            data: {
                id_virtualtour: id
            },
            async: true,
            success: function (json) {
                var rsp = JSON.parse(json);
                if (rsp.status == "ok") {
                    var url = window.location.href;
                    url = removeParam('id_vt',url);
                    url = removeParam('id_room',url);
                    url = url.replace(/#\s*$/, "");
                    location.href = url;
                    return false;
                }
            }
        });
    }

    function removeParam(key, sourceURL) {
        var rtn = sourceURL.split("?")[0],
            param,
            params_arr = [],
            queryString = (sourceURL.indexOf("?") !== -1) ? sourceURL.split("?")[1] : "";
        if (queryString !== "") {
            params_arr = queryString.split("&");
            for (var i = params_arr.length - 1; i >= 0; i -= 1) {
                param = params_arr[i].split("=")[0];
                if (param === key) {
                    params_arr.splice(i, 1);
                }
            }
            rtn = rtn + "?" + params_arr.join("&");
        }
        return rtn;
    }

    $(document).ready(function(){$("#users_table").length&&!$("#vlfc").length&&(location.href="index.php?p=license");$("#plans_table").length&&!$("#vlfc").length&&(location.href="index.php?p=license");$("#plan").length&&!$("#vlfc").length&&(location.href="index.php?p=license")});

    window.adjust_ratio_hfov = function (id_panorama,viewer,hfov,min_hfov,max_hfov) {
        var c_w = parseFloat($('#'+id_panorama).css('width').replace('px',''));
        var c_h = parseFloat($('#'+id_panorama).css('height').replace('px',''));
        var ratio_panorama = c_w / c_h;
        var ratio_hfov = 1.7771428571428571 / ratio_panorama;
        var min_hfov_t = min_hfov / ratio_hfov;
        var max_hfov_t = max_hfov / ratio_hfov;
        var hfov_t = hfov / ratio_hfov;
        viewer.setHfovBounds([min_hfov_t,max_hfov_t]);
        viewer.setHfov(hfov_t,false);
    }

    window.get_statistics = function (elem) {
        $.ajax({
            url: "ajax/get_statistics.php",
            type: "POST",
            data: {
                id_virtualtour: window.id_virtualtour,
                elem: elem
            },
            async: true,
            success: function (json) {
                var rsp = JSON.parse(json);
                switch (elem) {
                    case 'chart_visitor_vt':
                        new Chart(document.getElementById("chart_visitor_vt"), {
                            type: 'line',
                            data: {
                                labels: rsp.labels,
                                datasets: [{
                                    data: rsp.data,
                                    backgroundColor: window.theme_color,
                                    borderColor: window.theme_color,
                                    fill: false
                                }]
                            },
                            options: {
                                maintainAspectRatio: false,
                                legend: {
                                    display: false
                                },
                                scales: {
                                    xAxes: [{
                                        ticks: {
                                            beginAtZero: true
                                        }
                                    }]
                                }
                            }
                        });
                        break;
                    case 'chart_rooms_access':
                        new Chart(document.getElementById("chart_rooms_access"), {
                            type: 'horizontalBar',
                            data: {
                                labels: rsp.labels,
                                datasets: [{
                                    data: rsp.data,
                                    backgroundColor: window.theme_color,
                                    borderColor: window.theme_color,
                                    fill: false
                                }]
                            },
                            options: {
                                legend: {
                                    display: false
                                },
                                scales: {
                                    xAxes: [{
                                        ticks: {
                                            beginAtZero: true
                                        }
                                    }]
                                }
                            }
                        });
                        break;
                    case 'chart_rooms_time':
                        new Chart(document.getElementById("chart_rooms_time"), {
                            type: 'horizontalBar',
                            data: {
                                labels: rsp.labels,
                                datasets: [{
                                    data: rsp.data,
                                    backgroundColor: window.theme_color,
                                    borderColor: window.theme_color,
                                    fill: false
                                }]
                            },
                            options: {
                                legend: {
                                    display: false
                                },
                                scales: {
                                    xAxes: [{
                                        ticks: {
                                            beginAtZero: true
                                        }
                                    }]
                                }
                            }
                        });
                        break;
                    case 'chart_poi_views':
                        var total_access = rsp.total_poi;
                        var html_pois = '';
                        jQuery.each(rsp.pois, function(index, poi) {
                            var room = poi.room;
                            var type = poi.type;
                            var content = poi.content;
                            content = $($.parseHTML(content)).text();
                            switch (type) {
                                case 'image':
                                    type = window.backend_labels.image;
                                    type = type.substr(0,1).toUpperCase()+type.substr(1);
                                    var name_poi = room+" - <b>"+type+"</b> - <i>"+content+"</i>";
                                    break;
                                case 'video':
                                    type = window.backend_labels.video;
                                    type = type.substr(0,1).toUpperCase()+type.substr(1);
                                    var name_poi = room+" - <b>"+type+"</b> - <i>"+content+"</i>";
                                    break;
                                case 'video360':
                                    type = window.backend_labels.video360;
                                    type = type.substr(0,1).toUpperCase()+type.substr(1);
                                    var name_poi = room+" - <b>"+type+"</b> - <i>"+content+"</i>";
                                    break;
                                case 'download':
                                    type = window.backend_labels.download;
                                    type = type.substr(0,1).toUpperCase()+type.substr(1);
                                    var name_poi = room+" - <b>"+type+"</b> - <i>"+content+"</i>";
                                    break;
                                case 'gallery':
                                    type = window.backend_labels.image_gallery;
                                    type = type.substr(0,1).toUpperCase()+type.substr(1);
                                    var name_poi = room+" - <b>"+type+"</b>";
                                    break;
                                case 'audio':
                                    type = window.backend_labels.audio;
                                    type = type.substr(0,1).toUpperCase()+type.substr(1);
                                    var name_poi = room+" - <b>"+type+"</b> - <i>"+content+"</i>";
                                    break;
                                case 'link':
                                    type = window.backend_labels.link;
                                    type = type.substr(0,1).toUpperCase()+type.substr(1);
                                    var name_poi = room+" - <b>"+type+"</b> - <i>"+content+"</i>";
                                    break;
                                case 'link_ext':
                                    type = window.backend_labels.link_ext;
                                    type = type.substr(0,1).toUpperCase()+type.substr(1);
                                    var name_poi = room+" - <b>"+type+"</b> - <i>"+content+"</i>";
                                    break;
                                case 'html':
                                    type = window.backend_labels.html;
                                    if(content.length>100) {
                                        content = content.substring(0, 100)+"...";
                                    }
                                    type = type.substr(0,1).toUpperCase()+type.substr(1);
                                    var name_poi = room+" - <b>"+type+"</b> - <i>"+content+"</i>";
                                    break;
                                case 'html_sc':
                                    type = window.backend_labels.html_sc;
                                    if(content.length>100) {
                                        content = content.substring(0, 100)+"...";
                                    }
                                    type = type.substr(0,1).toUpperCase()+type.substr(1);
                                    var name_poi = room+" - <b>"+type+"</b> - <i>"+content+"</i>";
                                    break;
                                case 'form':
                                    type = window.backend_labels.form;
                                    content = JSON.parse(content)[0].title;
                                    type = type.substr(0,1).toUpperCase()+type.substr(1);
                                    var name_poi = room+" - <b>"+type+"</b> - <i>"+content+"</i>";
                                    break;
                                case 'google_maps':
                                    type = window.backend_labels.google_maps;
                                    type = type.substr(0,1).toUpperCase()+type.substr(1);
                                    var name_poi = room+" - <b>"+type+"</b>";
                                    break;
                                case 'object360':
                                    type = window.backend_labels.object360;
                                    type = type.substr(0,1).toUpperCase()+type.substr(1);
                                    var name_poi = room+" - <b>"+type+"</b>";
                                    break;
                                case 'object3d':
                                    type = window.backend_labels.object3d;
                                    type = type.substr(0,1).toUpperCase()+type.substr(1);
                                    var name_poi = room+" - <b>"+type+"</b>";
                                    break;
                                case 'lottie':
                                    var name_poi = room+" - <b>Lottie</b>";
                                    break;
                                case 'product':
                                    type = window.backend_labels.product;
                                    type = type.substr(0,1).toUpperCase()+type.substr(1);
                                    var name_poi = room+" - <b>"+type+"</b> - <i>"+content+"</i>";
                                    break;
                            }
                            var count = poi.access_count;
                            var perc = count / total_access * 100;
                            perc = Math.round(perc);
                            html_pois += '<h4 style="margin-bottom:1px;" class="small font-weight-bold">'+name_poi+' <span class="float-right"><b>'+count+'</b>/'+total_access+'</span></h4>\n' +
                                '                <div class="progress mb-2">\n' +
                                '                    <div class="progress-bar bg-primary" role="progressbar" style="width: '+perc+'%" aria-valuenow="'+perc+'" aria-valuemin="0" aria-valuemax="100"></div>\n' +
                                '                </div>';
                        });
                        $('#chart_poi_views').html(html_pois);
                        break;
                }
            }
        });
    }

    window.save_landing = function(id,html) {
        $.ajax({
            url: "../../ajax/save_landing.php",
            type: "POST",
            data: {
                id_virtualtour: id,
                html: html
            },
            async: true,
            success: function (json) {
                var rsp = JSON.parse(json);
                if (rsp.status == "ok") {
                    $('#landing_saving').hide();
                    $('#landing_editor').show();
                }
            }
        });
    }

    window.save_info = function(id,html) {
        $.ajax({
            url: "../../ajax/save_info.php",
            type: "POST",
            data: {
                id_virtualtour: id,
                html: html
            },
            async: true,
            success: function (json) {
                var rsp = JSON.parse(json);
                if (rsp.status == "ok") {
                    $('#info_saving').hide();
                    $('#info_editor').show();
                }
            }
        });
    }

    window.change_user_role = function () {
        var role = $('#role option:selected').attr('id');
        if(role=='editor') {
            $('.stats_div').hide();
            $('.plan_div').hide();
            $('.assign_vt_div').show();
        } else {
            $('.stats_div').show();
            $('.plan_div').show();
            $('.assign_vt_div').hide();
        }
    }

    window.check_multires_req = function () {
        $('#btn_check_multires_req').addClass('disabled');
        var multires = $('#multires option:selected').attr('id');
        var multires_cloud_url = $('#multires_cloud_url').val();
        switch(multires) {
            case 'local':
                var url = '../services/generate_multires.php';
                break;
            case 'cloud':
                var url = '../services/generate_multires_cloud.php';
                break;
        }
        $.ajax({
            url: url,
            type: "GET",
            data: {
                check_req: 1,
                multires_cloud_url: multires_cloud_url
            },
            async: true,
            success: function (json) {
                $('#btn_check_multires_req').removeClass('disabled');
                var rsp = JSON.parse(json);
                if (rsp.status == "ok") {
                    $('#modal_check_multires_req .modal-body').css('color','green');
                } else {
                    $('#modal_check_multires_req .modal-body').css('color','red');
                }
                $('#modal_check_multires_req .modal-body').html(rsp.msg);
                $('#modal_check_multires_req').modal('show');
            }
        });
    }

    window.regenerate_panoramas = function () {
        $('#btn_regenerate_panoramas').addClass('disabled');
        var compress_jpg = $('#compress_jpg').val();
        var max_width_compress = $('#max_width_compress').val();
        var enable_multires = $('#enable_multires').is(':checked');
        if(enable_multires) {
            switch(window.multires) {
                case 'local':
                    var url = '../services/generate_multires.php';
                    break;
                case 'cloud':
                    var url = '../services/generate_multires_cloud.php';
                    break;
            }
            $.ajax({
                url: url,
                type: "GET",
                data: {
                    check_req: 1
                },
                async: true,
                success: function (json) {
                    $('#btn_check_multires_req').removeClass('disabled');
                    var rsp = JSON.parse(json);
                    if (rsp.status == "ok") {
                        $.ajax({
                            url: "ajax/regenerate_multires.php",
                            type: "POST",
                            data: {
                                id_virtualtour: window.id_virtualtour,
                                compress_jpg: compress_jpg,
                                max_width_compress: max_width_compress,
                                enable_multires: enable_multires
                            },
                            async: true,
                            success: function (json) {
                                $('#btn_regenerate_panoramas').removeClass('disabled');
                                $('#modal_regenerate_multires .modal-body .error_msg').hide();
                                $('#modal_regenerate_multires .modal-body .ok_msg').show();
                                $('#modal_regenerate_multires').modal('show');
                            }
                        });
                    } else {
                        $('#modal_regenerate_multires .modal-body .error_msg').show();
                        $('#modal_regenerate_multires .modal-body .ok_msg').hide();
                        $('#modal_regenerate_multires').modal('show');
                    }
                }
            });
        } else {
            $('#modal_regenerate_panoramas').modal('show');
            $.ajax({
                url: "../services/resize_panoramas.php",
                type: "POST",
                data: {
                    id_virtualtour: window.id_virtualtour,
                    compress_jpg: compress_jpg,
                    max_width_compress: max_width_compress,
                    enable_multires: enable_multires
                },
                async: true,
                success: function (json) {
                    $('#btn_regenerate_panoramas').removeClass('disabled');
                    $('#modal_regenerate_panoramas').modal('hide');
                }
            });
        }
    }

    window.change_editor_css = function () {
        var id = $('#css_name option:selected').attr('id').replace('css_','');
        $('.editors_css').hide();
        $('#custom_b').show();
        $('#'+id).show();
    }

    window.change_editor_js = function () {
        var id = $('#js_name option:selected').attr('id').replace('js_custom','custom_js');
        $('.editors_js').hide();
        $('#'+id).show();
    }

    window.redirect_to_setup = function() {
        $('#modal_redirect_setup').modal('show');
        var postData = new FormData();
        postData.append('endpoint','setup_session');
        fetch('../payments/stripe_api.php', {
            method: 'POST',
            body: postData
        }).then(function(response) {
            return response.json();
        }).then(function(session) {
            if(session.status=='ok') {
                return stripe.redirectToCheckout({ sessionId: session.id });
            } else {
                $('#modal_redirect_setup').modal('hide');
                alert(session.msg);
            }
        }).then(function(result) {
            if (result.error) {
                $('#modal_redirect_setup').modal('hide');
                alert(result.error.message);
            }
        }).catch(function(error) {
            $('#modal_redirect_setup').modal('hide');
        });
    }

    window.get_payment_method = function() {
        var postData = new FormData();
        postData.append('endpoint', 'payment_method');
        fetch('../payments/stripe_api.php', {
            method: 'POST',
            body: postData
        }).then(function(response) {
            return response.json();
        }).then(function(result) {
            if(result.status=='ok') {
                $('#card_num').html("&bull;&bull;&bull;&bull; &bull;&bull;&bull;&bull; &bull;&bull;&bull;&bull; "+result.card);
            }
        });
    }

    window.redirect_to_checkout = function(id_plan) {
        $('#modal_redirect_checkout').modal('show');
        var postData = new FormData();
        postData.append('endpoint','checkout_session');
        postData.append('id_plan', id_plan);
        fetch('../payments/stripe_api.php', {
            method: 'POST',
            body: postData
        }).then(function(response) {
            return response.json();
        }).then(function(session) {
            if(session.status=='ok') {
                return stripe.redirectToCheckout({ sessionId: session.id });
            } else {
                $('#modal_redirect_checkout').modal('hide');
                alert(session.msg);
            }
        }).then(function(result) {
            if (result.error) {
                $('#modal_redirect_checkout').modal('hide');
                alert(result.error.message);
            }
        }).catch(function(error) {
            $('#modal_redirect_checkout').modal('hide');
        });
    }

    window.change_plan_proration = function(id_plan) {
        $('#new_plan').html("--");
        $('#next_payment').html("--");
        $('#subseq_payments').html("--");
        $('#btn_change_plan').addClass("disabled");
        $('#btn_change_plan').attr('onclick','change_subscription('+id_plan+')');
        $('#modal_change_plan').modal('show');
        var postData = new FormData();
        postData.append('endpoint', 'proration');
        postData.append('id_plan', id_plan);
        fetch('../payments/stripe_api.php', {
            method: 'POST',
            body: postData
        }).then(function(response) {
            return response.json();
        }).then(function(result) {
            if(result.status=='ok') {
                $('#new_plan').html(result.plan.name);
                $('#next_payment').html(result.next_price);
                $('#subseq_payments').html(result.subseq_price);
                $('#btn_change_plan').removeClass("disabled");
            } else {
                $('#modal_change_plan').modal('hide');
                alert(result.msg);
            }
        }).catch(function(error) {
            $('#modal_change_plan').modal('hide');
        });
    }

    window.change_subscription = function(id_plan) {
        $('#btn_change_plan').addClass("disabled");
        var postData = new FormData();
        postData.append('endpoint','change_subscription');
        postData.append('id_plan', id_plan);
        fetch('../payments/stripe_api.php', {
            method: 'POST',
            body: postData
        }).then(function(response) {
            return response.json();
        }).then(function(result) {
            if(result.status=='ok') {
                location.reload();
            } else {
                $('#modal_change_plan').modal('hide');
                alert(result.msg);
            }
        }).catch(function(error) {
            $('#modal_change_plan').modal('hide');
        });
    }

    window.open_modal_delete_plan = function () {
        $('#actual_plan').html("--");
        $('#active_until').html("--");
        $('#btn_delete_plan').addClass("disabled");
        $('#modal_delete_plan').modal('show');
        var postData = new FormData();
        postData.append('endpoint', 'subscription_end_date');
        fetch('../payments/stripe_api.php', {
            method: 'POST',
            body: postData
        }).then(function(response) {
            return response.json();
        }).then(function(result) {
            if(result.status=='ok') {
                $('#actual_plan').html(result.name);
                $('#active_until').html(result.end_date);
                $('#btn_delete_plan').removeClass("disabled");
            } else {
                $('#modal_delete_plan').modal('hide');
                alert(result.msg);
            }
        }).catch(function(error) {
            $('#modal_delete_plan').modal('hide');
        });
    }

    window.open_modal_delete_plan_paypal = function () {
        $('#actual_plan_paypal').html("--");
        $('#active_until_paypal').html("--");
        $('#btn_delete_plan_paypal').addClass("disabled");
        $('#modal_delete_plan_paypal').modal('show');
        $.ajax({
            url: "ajax/get_paypal_subscription.php",
            type: "POST",
            async: true,
            success: function (json) {
                var rsp = JSON.parse(json);
                if(rsp.status=="ok") {
                    $('#actual_plan_paypal').html(rsp.name);
                    $('#active_until_paypal').html(rsp.end_date);
                    $('#btn_delete_plan_paypal').removeClass("disabled");
                }
            }
        });
    }

    window.cancel_subscription = function() {
        $('#btn_delete_plan').addClass("disabled");
        var postData = new FormData();
        postData.append('endpoint', 'cancel_subscription');
        fetch('../payments/stripe_api.php', {
            method: 'POST',
            body: postData
        }).then(function(response) {
            return response.json();
        }).then(function(result) {
            if(result.status=='ok') {
                location.reload();
            } else {
                $('#modal_delete_plan').modal('hide');
                alert(result.msg);
            }
        }).catch(function(error) {
            $('#modal_delete_plan').modal('hide');
        });
    }

    window.cancel_subscription_paypal = function() {
        $('#btn_delete_plan_paypal').addClass("disabled");
        $.ajax({
            url: "ajax/cancel_paypal_subscription.php",
            type: "POST",
            async: true,
            success: function (json) {
                var rsp = JSON.parse(json);
                if(rsp.status=="ok") {
                    location.reload();
                } else {
                    $('#modal_delete_plan_paypal').modal('hide');
                    alert(rsp.msg);
                }
            }
        });
    }

    window.open_modal_reactivate_subscription = function () {
        $('#btn_reactivate_plan').removeClass("disabled");
        $('#modal_reactivate_plan').modal('show');
    }

    window.reactivate_subscription = function() {
        $('#btn_reactivate_plan').addClass("disabled");
        var postData = new FormData();
        postData.append('endpoint', 'reactivate_subscription');
        fetch('../payments/stripe_api.php', {
            method: 'POST',
            body: postData
        }).then(function(response) {
            return response.json();
        }).then(function(result) {
            if(result.status=='ok') {
                location.reload();
            } else {
                $('#modal_reactivate_plan').modal('hide');
                alert(result.msg);
            }
        }).catch(function(error) {
            $('#modal_reactivate_plan').modal('hide');
        });
    }

    window.apply_default_styles = function(p) {
        var element = p;
        $('#modal_'+p+'_style_apply button').addClass("disabled");
        switch(p) {
            case 'markers':
                var icon = $('#markers_icon').val();
                var id_icon_library = $('#marker_library_icon').val();
                var color = $('#markers_color').val();
                var background = $('#markers_background').val();
                var style = $('#markers_style option:selected').attr('id');
                var tooltip_type = $('#markers_tooltip_type option:selected').attr('id');
                var apply_style = $('#apply_marker_style').is(':checked')?1:0;
                var apply_tooltip_type = $('#apply_marker_tooltip_type').is(':checked')?1:0;
                var apply_icon = $('#apply_marker_icon').is(':checked')?1:0;
                var apply_color = $('#apply_marker_color').is(':checked')?1:0;
                var apply_background = $('#apply_marker_background').is(':checked')?1:0;
                switch(style) {
                    case 'room_and_icon':
                        style = 2;
                        break;
                    case 'icon_and_room':
                        style = 1;
                        break;
                    case 'only_icon':
                        style = 0;
                        break;
                    case 'only_room':
                        style = 3;
                        break;
                    case 'custom_icon':
                        style = 4;
                        break;
                    case 'preview_room':
                        style = 5;
                        break;
                }
                break;
            case 'markers_e':
                element = 'markers';
                var icon = $('#marker_icon').val();
                var id_icon_library = $('#marker_library_icon').val();
                var color = $('#marker_color').val();
                var background = $('#marker_background').val();
                var style = $('#marker_style option:selected').attr('id');
                var tooltip_type = $('#tooltip_type option:selected').attr('id');
                var apply_style = $('#apply_marker_style').is(':checked')?1:0;
                var apply_tooltip_type = $('#apply_marker_tooltip_type').is(':checked')?1:0;
                var apply_icon = $('#apply_marker_icon').is(':checked')?1:0;
                var apply_color = $('#apply_marker_color').is(':checked')?1:0;
                var apply_background = $('#apply_marker_background').is(':checked')?1:0;
                switch(style) {
                    case 'room_and_icon':
                        style = 2;
                        break;
                    case 'icon_and_room':
                        style = 1;
                        break;
                    case 'only_icon':
                        style = 0;
                        break;
                    case 'only_room':
                        style = 3;
                        break;
                    case 'custom_icon':
                        style = 4;
                        break;
                    case 'preview_room':
                        style = 5;
                        break;
                }
                break;
            case 'pois':
                var icon = $('#pois_icon').val();
                var id_icon_library = $('#poi_library_icon').val();
                var color = $('#pois_color').val();
                var background = $('#pois_background').val();
                var style = $('#pois_style option:selected').attr('id');
                var tooltip_type = $('#pois_tooltip_type option:selected').attr('id');
                var apply_style = $('#apply_poi_style').is(':checked')?1:0;
                var apply_tooltip_type = $('#apply_poi_tooltip_type').is(':checked')?1:0;
                var apply_icon = $('#apply_poi_icon').is(':checked')?1:0;
                var apply_color = $('#apply_poi_color').is(':checked')?1:0;
                var apply_background = $('#apply_poi_background').is(':checked')?1:0;
                switch(style) {
                    case 'label_and_icon':
                        style = 3;
                        break;
                    case 'icon_and_label':
                        style = 2;
                        break;
                    case 'only_icon':
                        style = 0;
                        break;
                    case 'only_label':
                        style = 4;
                        break;
                    case 'custom_icon':
                        style = 1;
                        break;
                }
                break;
            case 'pois_e':
                element = 'pois';
                var icon = $('#poi_icon').val();
                var id_icon_library = $('#poi_library_icon').val();
                var color = $('#poi_color').val();
                var background = $('#poi_background').val();
                var style = $('#poi_style option:selected').attr('id');
                var tooltip_type = $('#tooltip_type option:selected').attr('id');
                var apply_style = $('#apply_poi_style').is(':checked')?1:0;
                var apply_tooltip_type = $('#apply_poi_tooltip_type').is(':checked')?1:0;
                var apply_icon = $('#apply_poi_icon').is(':checked')?1:0;
                var apply_color = $('#apply_poi_color').is(':checked')?1:0;
                var apply_background = $('#apply_poi_background').is(':checked')?1:0;
                switch(style) {
                    case 'label_and_icon':
                        style = 3;
                        break;
                    case 'icon_and_label':
                        style = 2;
                        break;
                    case 'only_icon':
                        style = 0;
                        break;
                    case 'only_label':
                        style = 4;
                        break;
                    case 'custom_icon':
                        style = 1;
                        break;
                }
                break;
        }
        if(apply_style==1 || apply_tooltip_type==1 || apply_icon==1 || apply_color==1 || apply_background==1) {
            $.ajax({
                url: "ajax/apply_default_styles.php",
                type: "POST",
                data: {
                    id_virtualtour: window.id_virtualtour,
                    p: element,
                    icon: icon,
                    id_icon_library: id_icon_library,
                    color: color,
                    background: background,
                    style: style,
                    tooltip_type: tooltip_type,
                    apply_style: apply_style,
                    apply_tooltip_type: apply_tooltip_type,
                    apply_icon: apply_icon,
                    apply_color: apply_color,
                    apply_background: apply_background
                },
                async: true,
                success: function (json) {
                    var rsp = JSON.parse(json);
                    if (rsp.status == "ok") {
                        $('#modal_'+element+'_style_apply').modal('hide');
                        switch(p) {
                            case 'markers_e':
                                if(window.id_room_sel!=0) {
                                    jQuery.each(window.rooms, function(index, room) {
                                        var id = room.id;
                                        var image = room.panorama_image;
                                        if(id==window.id_room_sel) {
                                            exit_edit_marker(marker_id_edit,marker_index_edit);
                                            setTimeout(function() {
                                                select_room_marker(id,image,null);
                                            },210);
                                        }
                                    });
                                }
                                break;
                            case 'pois_e':
                                if(window.id_room_sel!=0) {
                                    jQuery.each(window.rooms, function(index, room) {
                                        var id = room.id;
                                        var image = room.panorama_image;
                                        if(id==window.id_room_sel) {
                                            exit_edit_poi(poi_id_edit,poi_index_edit);
                                            setTimeout(function() {
                                                select_room_poi(id,image,null);
                                            },210);
                                        }
                                    });
                                }
                                break;
                        }
                    } else {
                        alert(rsp.msg);
                    }
                    $('#modal_'+element+'_style_apply button').removeClass("disabled");
                }
            });
        }
    }

    window.load_viewer_pos = function (elem,id_room,panorama_image,yaw,pitch,h_pitch,h_roll,min_pitch,max_pitch,haov,vaov,min_yaw,max_yaw) {
        try {
            window.viewer_pos.destroy();
        } catch (e) {}
        window.viewer_pos = pannellum.viewer(elem, {
            "id_room": parseInt(id_room),
            "type": "equirectangular",
            "panorama": panorama_image,
            "autoLoad": true,
            "showFullscreenCtrl": false,
            "showControls": false,
            "horizonPitch": parseInt(h_pitch),
            "horizonRoll": parseInt(h_roll),
            "hfov": 100,
            "minHfov": 100,
            "maxHfov": 100,
            "yaw": yaw,
            "pitch": pitch,
            "minPitch": parseInt(min_pitch),
            "maxPitch" : parseInt(max_pitch),
            "minYaw": parseInt(min_yaw),
            "maxYaw" : parseInt(max_yaw),
            "haov": parseInt(haov),
            "vaov": parseInt(vaov),
            "compass": false,
            "friction": 1,
            "strings": {
                "loadingLabel": window.backend_labels.loading+"...",
            },
        });
        window.viewer_pos.on('load', function () {
            $('#confirm_edit .btn_confirm').removeClass("disabled");
            $('#btn_new_marker').removeClass("disabled");
        });
        window.viewer_pos.on('mousedown', function () {
            switch(elem) {
                case 'panorama_pos_add':
                    $('#override_pos_add').prop('checked',true);
                    break;
                case 'panorama_pos_edit':
                    $('#override_pos_edit').prop('checked',true);
                    break;
            }
        });
    }

    window.change_protect_type = function () {
        var protect_type = $('#protect_type option:selected').attr('id');
        switch (protect_type) {
            case 'none':
                $('#passcode_title').prop("disabled",true);
                $('#passcode_description').prop("disabled",true);
                $('#passcode_code').prop("disabled",true);
                $('#protect_send_email').prop("disabled",true);
                $('#protect_email').prop("disabled",true);
                break;
            case 'passcode':
                $('#passcode_title').prop("disabled",false);
                $('#passcode_description').prop("disabled",false);
                $('#passcode_code').prop("disabled",false);
                $('#protect_send_email').prop("disabled",true);
                $('#protect_email').prop("disabled",true);
                break;
            case 'leads':
                $('#passcode_title').prop("disabled",false);
                $('#passcode_description').prop("disabled",false);
                $('#passcode_code').prop("disabled",true);
                $('#protect_send_email').prop("disabled",false);
                $('#protect_email').prop("disabled",false);
                break;
        }
    }

    window.close_edit_thumbnail = function() {
        $('#div_thumbnail').hide();
        $('#div_panorama').show();
        $('#btn_edit_thumbnail').removeClass('disabled');
        $('#frm_thumb').removeClass('disabled');
    }

    window.edit_thumbnail = function() {
        $('#btn_edit_thumbnail').addClass('disabled');
        $('#frm_thumb').addClass('disabled');
        if(window.room_type=='image' || window.room_type=='hls' || window.room_type=='lottie') {
            var dataURL = window.viewer.getRenderer().render(window.viewer.getPitch() / 180 * Math.PI,
                window.viewer.getYaw() / 180 * Math.PI,
                window.viewer.getHfov() / 180 * Math.PI,
                {'returnImage': 'image/jpeg'});
        } else {
            var dataURL = window.viewer_video.pnlmViewer.getRenderer().render(window.viewer_video.pnlmViewer.getPitch() / 180 * Math.PI,
                window.viewer_video.pnlmViewer.getYaw() / 180 * Math.PI,
                window.viewer_video.pnlmViewer.getHfov() / 180 * Math.PI,
                {'returnImage': 'image/jpeg'});
        }
        $('#div_panorama').hide();
        $('#div_thumbnail').show();
        window.cropper_thumb.replace(dataURL);
    }

    window.initialize_cropper_thumbnail = function(dataURL) {
        $('#frm_thumb').removeClass('disabled');
        if(window.cropper_thumb==null) {
            var $img = $("#panorama_image_edit");
            $img.on('load',function(){
                setTimeout(function () {
                    var image = document.getElementById('panorama_image_edit');
                    window.cropper_thumb = new Cropper(image, {
                        zoomable: false,
                        zoomOnTouch: false,
                        zoomOnWheel: false,
                        aspectRatio: 16 / 9,
                    });
                    $img.off('load');
                    $('#btn_edit_thumbnail').removeClass('disabled');
                },200);
            });
            $img.attr('src',dataURL);
        }
    }

    window.crop_thumbnail = function () {
        $('#frm_thumb').addClass('disabled');
        $('#div_thumbnail button').addClass('disabled');
        var crop_data = window.cropper_thumb.getData();
        var image = $('#panorama_image_edit').attr('src');
        $.ajax({
            url: "ajax/crop_thumbnail.php",
            type: "POST",
            data: {
                id_room: window.id_room,
                image: image,
                crop_data: crop_data
            },
            async: true,
            success: function (json) {
                var rsp = JSON.parse(json);
                if (rsp.status == "ok") {
                    window.thumb_image = rsp.thumb_image;
                    $('#thumb_image').attr('src','../viewer/panoramas/thumb_custom/'+rsp.thumb_image);
                    $('#div_thumbnail').hide();
                    $('#div_panorama').show();
                    $('#btn_edit_thumbnail').removeClass('disabled');
                    $('#frm_thumb').removeClass('disabled');
                }
                $('#div_thumbnail button').removeClass('disabled');
            }
        });
    }

    window.reset_statistics = function () {
        $('#modal_reset_statistics button').addClass("disabled");
        $.ajax({
            url: "ajax/reset_statistics.php",
            type: "POST",
            data: {
                id_user: window.id_user,
                id_virtualtour: window.id_virtualtour
            },
            async: true,
            success: function (json) {
                var rsp = JSON.parse(json);
                if (rsp.status == "ok") {
                    $('#modal_reset_statistics button').removeClass("disabled");
                    $('#modal_reset_statistics').modal("hide");
                    location.reload();
                } else {
                    $('#modal_reset_statistics button').removeClass("disabled");
                }
            }
        });
    }

    window.reset_leads = function () {
        $('#modal_reset_leads button').addClass("disabled");
        $.ajax({
            url: "ajax/reset_leads.php",
            type: "POST",
            data: {
                id_user: window.id_user,
                id_virtualtour: window.id_virtualtour
            },
            async: true,
            success: function (json) {
                var rsp = JSON.parse(json);
                if (rsp.status == "ok") {
                    $('#modal_reset_leads button').removeClass("disabled");
                    $('#modal_reset_leads').modal("hide");
                    location.reload();
                } else {
                    $('#modal_reset_leads button').removeClass("disabled");
                }
            }
        });
    }

    window.reset_forms_data = function () {
        $('#modal_reset_forms_data button').addClass("disabled");
        $.ajax({
            url: "ajax/reset_forms_data.php",
            type: "POST",
            data: {
                id_user: window.id_user,
                id_virtualtour: window.id_virtualtour
            },
            async: true,
            success: function (json) {
                var rsp = JSON.parse(json);
                if (rsp.status == "ok") {
                    $('#modal_reset_forms_data button').removeClass("disabled");
                    $('#modal_reset_forms_data').modal("hide");
                    location.reload();
                } else {
                    $('#modal_reset_forms_data button').removeClass("disabled");
                }
            }
        });
    }

    window.show_grid_position = function() {
        $('.grid_position').show();
    }

    window.hide_grid_position = function() {
        $('.grid_position').hide();
    }

    window.show_btn_toggle_effects = function () {
        $('#btn_toggle_effetcs').show();
    }

    window.hide_btn_toggle_effects = function () {
        $('#btn_toggle_effetcs').hide();
    }

    window.show_btn_screenshot = function () {
        $('#btn_screenshot').show();
    }

    window.hide_btn_screenshot = function () {
        $('#btn_screenshot').hide();
    }

    window.toggle_effects = function() {
        if(room_type=='video') {
            var canvas = viewer_video.pnlmViewer.getRenderer().getCanvas();
        } else {
            var canvas = viewer.getRenderer().getCanvas();
        }
        if($('#btn_toggle_effetcs i').hasClass('active')) {
            $('#btn_toggle_effetcs i').removeClass('active');
            $('.snow_effect').hide();
            $('.rain_effect').hide();
            $('.fog_effect').hide();
            $('.fireworks_effect').hide();
            $('.confetti_effect').hide();
            $('.sparkle_effect').hide();
            canvas.style.filter='';
        } else {
            $('#btn_toggle_effetcs i').addClass('active');
            $('.snow_effect').show();
            $('.rain_effect').show();
            $('.fog_effect').show();
            $('.fireworks_effect').show();
            $('.confetti_effect').show();
            $('.sparkle_effect').show();
            var brightness = $('#brightness').val();
            $('#brightness_val').html(brightness+'%');
            var contrast = $('#contrast').val();
            $('#contrast_val').html(contrast+'%');
            var saturate = $('#saturate').val();
            $('#saturate_val').html(saturate+'%');
            var grayscale = $('#grayscale').val();
            $('#grayscale_val').html(grayscale+'%');
            var filter = '';
            if(brightness!=100) {
                filter += 'brightness('+brightness+'%) ';
            }
            if(contrast!=100) {
                filter += 'contrast('+contrast+'%) ';
            }
            if(saturate!=100) {
                filter += 'saturate('+saturate+'%) ';
            }
            if(grayscale!=0) {
                filter += 'grayscale('+grayscale+'%) ';
            }
            canvas.style.filter = filter;
        }
    }

    window.add_category = function () {
        var name = $('#category_name').val();
        if(name!='') {
            $('#modal_add_category button').addClass("disabled");
            $.ajax({
                url: "ajax/add_category.php",
                type: "POST",
                data: {
                    name: name
                },
                async: true,
                success: function (json) {
                    var rsp = JSON.parse(json);
                    if (rsp.status == "ok") {
                        $('#modal_add_category button').removeClass("disabled");
                        $('#modal_add_category').modal("hide");
                        var id = rsp.id;
                        $('#category').append("<option id='"+id+"' selected>"+name+"</option>");
                    } else {
                        $('#modal_add_category button').removeClass("disabled");
                    }
                }
            });
        }
    }

    window.edit_category = function(id) {
        var name = $('#cat_'+id).val();
        if(name!='') {
            $.ajax({
                url: "ajax/edit_category.php",
                type: "POST",
                data: {
                    id: id,
                    name: name
                },
                async: false,
                success: function (json) {
                    var rsp = JSON.parse(json);
                    if (rsp.status == "ok") {
                        $('#cat_'+id).addClass('success-highlight');
                        setTimeout(function() {
                            $('#cat_'+id).removeClass('success-highlight');
                        },500);
                    } else {
                        $('#cat_'+id).addClass('error-highlight');
                        setTimeout(function() {
                            $('#cat_'+id).removeClass('error-highlight');
                        },500);
                    }
                }
            });
        }
    }

    window.modal_delete_category = function(id) {
        $('#btn_delete_category').attr('onclick','delete_category('+id+');');
        $('#modal_delete_category').modal("show");
    }

    window.delete_category = function (id) {
        $('#modal_delete_category button').addClass("disabled");
        $.ajax({
            url: "ajax/delete_category.php",
            type: "POST",
            data: {
                id: id
            },
            async: true,
            success: function (json) {
                var rsp = JSON.parse(json);
                if (rsp.status == "ok") {
                    $('#modal_delete_category button').removeClass("disabled");
                    $('#modal_delete_category').modal("hide");
                    $('#ca_tr_'+id).remove();
                } else {
                    $('#modal_delete_category button').removeClass("disabled");
                }
            }
        });
    };

    window.add_category_s = function () {
        var name = $('#cat_new').val();
        if(name!='') {
            $('#btn_add_category').addClass("disabled");
            $.ajax({
                url: "ajax/add_category.php",
                type: "POST",
                data: {
                    name: name
                },
                async: true,
                success: function (json) {
                    var rsp = JSON.parse(json);
                    if (rsp.status == "ok") {
                        get_categories();
                        $('#btn_add_category').removeClass("disabled");
                        $('#cat_new').val('');
                    }
                }
            });
        }
    }

    window.get_categories = function() {
        $.ajax({
            url: "ajax/get_categories.php",
            type: "POST",
            async: true,
            success: function (json) {
                var rsp = JSON.parse(json);
                if (rsp.status == "ok") {
                    $('#table_categories tbody').html(rsp.html);
                }
            }
        });
    }

    window.change_keyboard_mode = function() {
        var keyborad_mode = $('#keyboard_mode option:selected').attr('id');
        $('#keyboard_msg_0').addClass('d-none');
        $('#keyboard_msg_1').addClass('d-none');
        $('#keyboard_msg_2').addClass('d-none');
        $('#keyboard_msg_'+keyborad_mode).removeClass('d-none');
    }

    window.change_frequency = function(id) {
        var frequency = $('#frequency'+id+' option:selected').attr('id');
        switch (frequency) {
            case 'one_time':
                $('#interval_count'+id).prop('disabled',true);
                break;
            case 'recurring':
                $('#interval_count'+id).prop('disabled',false);
                break;
        }
    }

    window.open_qr_code_modal = function (link) {
        var qrcode_src = "https://chart.googleapis.com/chart?chs=400x400&chld=L|1&cht=qr&choe=UTF-8&chl="+link;
        $('#modal_qrcode').modal('show');
        $('#modal_qrcode i').show();
        $('#modal_qrcode img').hide();
        $('#modal_qrcode img').on("load", function() {
            $('#modal_qrcode i').hide();
            $('#modal_qrcode img').show();
        }).attr("src", qrcode_src);
    }

    window.switch_language = function (lang) {
        $.ajax({
            url: "ajax/switch_language.php",
            type: "POST",
            data: {
                lang: lang
            },
            async: false,
            success: function () {
                location.reload();
            }
        });
    }

    window.assign_vt_editor = function (id,checked) {
        $.ajax({
            url: "ajax/assign_vt_editor.php",
            type: "POST",
            data: {
                id_vt: id,
                id_user: window.id_user_edit,
                checked: checked
            },
            async: false,
            success: function() {
                if(checked==1) {
                    $('#assign_vt_table').DataTable().ajax.reload();
                }
            }
        });
    }

    window.set_permission_vt_editor = function (id,field,checked) {
        $.ajax({
            url: "ajax/set_permission_vt_editor.php",
            type: "POST",
            data: {
                id_vt: id,
                id_user: window.id_user_edit,
                field: field,
                checked: checked
            },
            async: true,
            success: function() {}
        });
    }

    window.change_poi_view_type = function () {
        var view_type = $('#view_type option:selected').attr('id');
        switch (view_type) {
            case '0':
                $('#box_pos').prop('disabled',true);
                break;
            case '1':
            case '2':
                $('#box_pos').prop('disabled',false);
                break;
        }
    }

    window.change_form_field_type = function(id) {
        var type = $('#form_field_type_'+id+' option:selected').attr('id');
        switch(type) {
            case 'select':
                $('#form_field_label_'+id).attr('placeholder','Label;Option1;Option2;Option3;...');
                break;
            default:
                $('#form_field_label_'+id).attr('placeholder','');
                break;
        }
    }

    window.session_library = function (w) {
        $.ajax({
            url: "ajax/set_session_library.php",
            type: "POST",
            async: true,
            data: {
              w: w
            },
            success: function (json) {
                location.reload();
            }
        });
    }

    window.session_statistics = function (w) {
        $.ajax({
            url: "ajax/set_session_statistics.php",
            type: "POST",
            async: true,
            data: {
                w: w
            },
            success: function (json) {
                location.reload();
            }
        });
    }

    window.save_preset = function (type) {
        var id_preset = $('#presets option:selected').attr('id');
        if(id_preset==0) {
            $('#name_preset').val('');
            $('#btn_add_new_preset').attr('onclick','add_preset(\''+type+'\');')
            $('#modal_new_preset').modal('show');
        } else {
            $('#modal_save_preset').modal('show');
        }
    }

    window.save_exist_preset = function (type) {
        $('#modal_save_preset button').addClass('disabled');
        var id_preset = $('#presets option:selected').attr('id');
        var allow_pitch = $('#allow_pitch').is(':checked');
        var allow_hfov = $('#allow_hfov').is(':checked');
        var min_pitch = $('#min_pitch').val();
        var max_pitch = $('#max_pitch').val();
        var min_yaw = $('#min_yaw').val();
        var max_yaw = $('#max_yaw').val();
        var haov = $('#haov').val();
        var vaov = $('#vaov').val();
        var hfov = $('#hfov').val();
        var h_pitch = $('#h_pitch').val();
        var h_roll = $('#h_roll').val();
        var background_color = window.background_color;
        var value = {};
        value['allow_pitch'] = allow_pitch;
        value['allow_hfov'] = allow_hfov;
        value['min_pitch'] = min_pitch;
        value['max_pitch'] = max_pitch;
        value['min_yaw'] = min_yaw;
        value['max_yaw'] = max_yaw;
        value['haov'] = haov;
        value['vaov'] = vaov;
        value['hfov'] = hfov;
        value['h_pitch'] = h_pitch;
        value['h_roll'] = h_roll;
        value['background_color'] = background_color;
        var value_json = JSON.stringify(value);
        $.ajax({
            url: "ajax/save_preset.php",
            type: "POST",
            async: true,
            data: {
                id: id_preset,
                type: type,
                value: value_json
            },
            success: function (json) {
                $("#presets option[id='"+id_preset+"']").attr('data-value',value_json);
                $('#modal_save_preset button').removeClass('disabled');
                $('#modal_save_preset').modal('hide');
            }
        });
    }

    window.add_preset = function (type) {
        var name = $('#name_preset').val();
        if(name!='') {
            $('#modal_new_preset button').addClass('disabled');
            var allow_pitch = $('#allow_pitch').is(':checked');
            var allow_hfov = $('#allow_hfov').is(':checked');
            var min_pitch = $('#min_pitch').val();
            var max_pitch = $('#max_pitch').val();
            var min_yaw = $('#min_yaw').val();
            var max_yaw = $('#max_yaw').val();
            var haov = $('#haov').val();
            var vaov = $('#vaov').val();
            var hfov = $('#hfov').val();
            var h_pitch = $('#h_pitch').val();
            var h_roll = $('#h_roll').val();
            var background_color = window.background_color;
            var value = {};
            value['allow_pitch'] = allow_pitch;
            value['allow_hfov'] = allow_hfov;
            value['min_pitch'] = min_pitch;
            value['max_pitch'] = max_pitch;
            value['min_yaw'] = min_yaw;
            value['max_yaw'] = max_yaw;
            value['haov'] = haov;
            value['vaov'] = vaov;
            value['hfov'] = hfov;
            value['h_pitch'] = h_pitch;
            value['h_roll'] = h_roll;
            value['background_color'] = background_color;
            var value_json = JSON.stringify(value);
            $.ajax({
                url: "ajax/add_preset.php",
                type: "POST",
                async: true,
                data: {
                    id_virtualtour: window.id_virtualtour,
                    name: name,
                    type: type,
                    value: value_json
                },
                success: function (json) {
                    var rsp = JSON.parse(json);
                    if(rsp.status=='ok') {
                        var id=rsp.id;
                        $('#presets').append("<option data-value='"+value_json+"' id='"+id+"'>"+name+"</option>");
                        $("#presets option").prop("selected", false);
                        $("#presets option[id='"+id+"']").prop("selected", true);
                        $('#btn_apply_preset_room').removeClass('disabled');
                        $('#btn_apply_preset_tour').removeClass('disabled');
                        $('#btn_delete_preset').removeClass('disabled');
                    }
                    $('#modal_new_preset button').removeClass('disabled');
                    $('#modal_new_preset').modal("hide");
                }
            });
        }
    }

    window.change_preset = function () {
        var id_preset = $('#presets option:selected').attr('id');
        if(id_preset==0) {
            $('#btn_apply_preset_room').addClass('disabled');
            $('#btn_apply_preset_tour').addClass('disabled');
            $('#btn_delete_preset').addClass('disabled');
        } else {
            $('#btn_apply_preset_room').removeClass('disabled');
            $('#btn_apply_preset_tour').removeClass('disabled');
            $('#btn_delete_preset').removeClass('disabled');
        }
    }

    window.delete_preset = function (type) {
        var id_preset = $('#presets option:selected').attr('id');
        var retVal = confirm(window.backend_labels.delete_sure_msg);
        if( retVal == true ) {
            $('#btn_apply_preset_room').addClass('disabled');
            $('#btn_apply_preset_tour').addClass('disabled');
            $('#btn_delete_preset').addClass("disabled");
            $.ajax({
                url: "ajax/delete_preset.php",
                type: "POST",
                data: {
                    id: id_preset
                },
                async: true,
                success: function (json) {
                    var rsp = JSON.parse(json);
                    if (rsp.status == "ok") {
                        $("#presets option[id='"+id_preset+"']").remove();
                        $("#presets option").prop("selected", false);
                        $("#presets option[id='0']").prop("selected", true);
                    }
                }
            });
        } else {
            return false;
        }
    };

    window.change_duplicate_items_vt = function () {
        var duplicate_rooms = $('#duplicate_rooms').is(':checked');
        if(!duplicate_rooms) {
            $('#duplicate_markers').prop('checked',false);
            $('#duplicate_markers').prop('disabled',true);
            $('#duplicate_pois').prop('checked',false);
            $('#duplicate_pois').prop('disabled',true);
            $('#duplicate_presentation').prop('checked',false);
            $('#duplicate_presentation').prop('disabled',true);
        } else {
            $('#duplicate_markers').prop('disabled',false);
            $('#duplicate_pois').prop('disabled',false);
            $('#duplicate_presentation').prop('disabled',false);
        }
    };

    window.change_transform3d = function () {
        var transform3d = $('#transform3d').is(':checked');
        if(transform3d) {
            window.pois[window.poi_index_edit].transform3d = 1;
            var yaw = parseFloat(window.pois[window.poi_index_edit].yaw);
            var pitch = parseFloat(window.pois[window.poi_index_edit].pitch);
            var embed_size = window.pois[window.poi_index_edit].embed_size;
            var tmp = embed_size.split(",");
            var ew = parseFloat(tmp[0])/30;
            var eh = parseFloat(tmp[1])/30;
            var poi_embed_helpers = [[0,0],[0,0],[0,0],[0,0]];
            poi_embed_helpers[0][0] = pitch + eh;
            poi_embed_helpers[0][1] = yaw - ew;
            poi_embed_helpers[1][0] = pitch - eh;
            poi_embed_helpers[1][1] = yaw - ew;
            poi_embed_helpers[2][0] = pitch + eh;
            poi_embed_helpers[2][1] = yaw + ew;
            poi_embed_helpers[3][0] = pitch - eh;
            poi_embed_helpers[3][1] = yaw + ew;
            var embed_coords = poi_embed_helpers[0][0]+","+poi_embed_helpers[0][1]+"|"+poi_embed_helpers[1][0]+","+poi_embed_helpers[1][1]+"|"+poi_embed_helpers[2][0]+","+poi_embed_helpers[2][1]+"|"+poi_embed_helpers[3][0]+","+poi_embed_helpers[3][1];
            window.pois[window.poi_index_edit].embed_coords = embed_coords;
        } else {
            window.pois[window.poi_index_edit].transform3d = 0;
            window.pois[window.poi_index_edit].embed_size = window.pois_initial[window.poi_index_edit].embed_size;
        }
        render_poi_move(window.poi_id_edit,window.poi_index_edit);
        move_p(window.poi_id_edit,window.poi_index_edit,null);
    }

    window.minimize_box_edit = function () {
        $('.tab-content').toggle();
    }

    window.maximize_box_edit = function () {
        if(!$('.tab-content').is(':visible')) {
            setTimeout(function () {
                $('.tab-content').show();
            },150);
        }
    }

    window.change_product_type = function () {
        var type = $('#type option:selected').attr('id');
        switch (type) {
            case 't_none':
            case 't_cart':
                $('#link').prop('disabled',true);
                break;
            case 't_link':
                $('#link').prop('disabled',false);
                break;
        }
    }

    function wizard_progress_bar(index) {
        const currentStepElement = wizard_tour.currentStep.el;
        const header = currentStepElement.querySelector('.shepherd-content');
        const progress = document.createElement('div');
        const innerBar = document.createElement('span');
        const progressPercentage = ((index)/25)*100 + '%';
        progress.className='shepherd-progress-bar';
        innerBar.style.width=progressPercentage;
        progress.appendChild(innerBar);
        header.insertBefore(progress, currentStepElement.querySelector('.shepherd-text'));
        $('.shepherd-header').append('<button onclick="close_wizard()" aria-label="Close Tour" class="shepherd-cancel-icon" type="button"><span aria-hidden="true">×</span></button>');
    }

    window.close_wizard = function() {
        var r = confirm(window.backend_labels.wizard_close);
        if (r == true) {
            wizard_tour.cancel();
            location.href = 'index.php?p=dashboard';
        }
    }

    window.create_wizard = function (initial_index) {
        wizard_tour = new Shepherd.Tour({
            defaultStepOptions: {
                cancelIcon: {
                    enabled: false
                },
                scrollTo: { behavior: 'smooth', block: 'center' },
            },
            useModalOverlay: true
        });
        if(initial_index<1) {
            wizard_steps[0] = wizard_tour.addStep({
                title: '1 - ' + window.backend_labels.wizard_title_1,
                text: window.backend_labels.wizard_text_1,
                attachTo: {
                    element: '#virtual_tours_menu_item',
                    on: 'right'
                },
                modalOverlayOpeningRadius: 5,
                advanceOn: {selector: '#virtual_tours_menu_item', event: 'click'},
                popperOptions: {
                    modifiers: [{ name: 'offset', options: { offset: [0, 15] } }]
                },
                when: {
                    show() {
                        wizard_progress_bar(1);
                    }
                },
            });
        }
        if(initial_index<2) {
            wizard_steps[1] = wizard_tour.addStep({
                title: '2 - ' + window.backend_labels.wizard_title_1,
                text: window.backend_labels.wizard_text_2,
                attachTo: {
                    element: '#list_tour_menu_item',
                    on: 'right'
                },
                modalOverlayOpeningRadius: 5,
                beforeShowPromise: function() {
                    return new Promise(function(resolve) {
                        setTimeout(function () {
                            resolve();
                        },250);
                    });
                },
                popperOptions: {
                    modifiers: [{name: 'offset', options: {offset: [0, 15]}}]
                },
                when: {
                    show() {
                        wizard_progress_bar(2);
                    }
                },
            });
        }
        if(initial_index<3) {
            wizard_steps[2] = wizard_tour.addStep({
                title: '3 - ' + window.backend_labels.wizard_title_1,
                text: window.backend_labels.wizard_text_3,
                attachTo: {
                    element: '#add_vt_form',
                    on: 'bottom'
                },
                modalOverlayOpeningRadius: 5,
                canClickTarget: false,
                buttons: [
                    {
                        text: 'Continue',
                        action: wizard_tour.next
                    }
                ],
                popperOptions: {
                    modifiers: [{name: 'offset', options: {offset: [0, 15]}}]
                },
                when: {
                    show() {
                        wizard_progress_bar(3);
                    }
                },
            });
        }
        if(initial_index<4) {
            wizard_steps[3] = wizard_tour.addStep({
                title: '4 - ' + window.backend_labels.wizard_title_1,
                text: window.backend_labels.wizard_text_4,
                attachTo: {
                    element: '#name_div',
                    on: 'bottom'
                },
                modalOverlayOpeningPadding: 10,
                modalOverlayOpeningRadius: 5,
                scrollTo: false,
                buttons: [{
                    text: window.backend_labels.wizard_continue,
                    action: function () {
                        if($('#name').val()=='') {
                            $('#name').focus();
                            return false;
                        } else {
                            Shepherd.activeTour.next();
                        }
                    }
                }],
                when: {
                    show: function() {
                        wizard_progress_bar(4);
                        setTimeout(function () {
                            $('#name').focus();
                        },400);
                    }
                },
                popperOptions: {
                    modifiers: [{name: 'offset', options: {offset: [0, 25]}}]
                }
            });
        }
        if(initial_index<5) {
            wizard_steps[4] = wizard_tour.addStep({
                title: '5 - ' + window.backend_labels.wizard_title_1,
                text: window.backend_labels.wizard_text_5,
                attachTo: {
                    element: '#btn_create_tour',
                    on: 'left'
                },
                modalOverlayOpeningRadius: 5,
                scrollTo: false,
                popperOptions: {
                    modifiers: [{name: 'offset', options: {offset: [0, 25]}}]
                },
                when: {
                    show() {
                        wizard_progress_bar(5);
                    }
                },
            });
        }
        if(initial_index<6) {
            wizard_steps[5] = wizard_tour.addStep({
                title: '6 - ' + window.backend_labels.wizard_title_2,
                text: window.backend_labels.wizard_text_6,
                attachTo: {
                    element: '#room_menu_item',
                    on: 'right'
                },
                modalOverlayOpeningRadius: 5,
                popperOptions: {
                    modifiers: [{name: 'offset', options: {offset: [0, 25]}}]
                },
                when: {
                    show() {
                        wizard_progress_bar(6);
                    }
                },
            });
        }
        if(initial_index<7) {
            wizard_steps[6] = wizard_tour.addStep({
                title: '7 - ' + window.backend_labels.wizard_title_2,
                text: window.backend_labels.wizard_text_7,
                attachTo: {
                    element: '#btn_modal_create_room',
                    on: 'left'
                },
                scrollTo: false,
                modalOverlayOpeningRadius: 20,
                advanceOn: {selector: '#btn_modal_create_room', event: 'click'},
                popperOptions: {
                    modifiers: [{name: 'offset', options: {offset: [0, 30]}}]
                },
                when: {
                    show() {
                        wizard_progress_bar(7);
                    }
                },
            });
        }
        if(initial_index<8) {
            wizard_steps[7] = wizard_tour.addStep({
                title: '8 - ' + window.backend_labels.wizard_title_2,
                text: window.backend_labels.wizard_text_8,
                attachTo: {
                    element: '#name_div',
                    on: 'bottom'
                },
                modalOverlayOpeningPadding: 5,
                modalOverlayOpeningRadius: 5,
                scrollTo: false,
                beforeShowPromise: function() {
                    return new Promise(function(resolve) {
                        setTimeout(function () {
                            resolve();
                        },250);
                    });
                },
                buttons: [{
                    text: window.backend_labels.wizard_continue,
                    action: function () {
                        if($('#name').val()=='') {
                            $('#name').focus();
                            return false;
                        } else {
                            Shepherd.activeTour.next();
                        }
                    }
                }],
                when: {
                    show: function() {
                        wizard_progress_bar(8);
                        setTimeout(function () {
                            $('#name').focus();
                        },400);
                    }
                },
                popperOptions: {
                    modifiers: [{name: 'offset', options: {offset: [0, 20]}}]
                }
            });
        }
        if(initial_index<9) {
            wizard_steps[8] = wizard_tour.addStep({
                title: '9 - ' + window.backend_labels.wizard_title_2,
                text: window.backend_labels.wizard_text_9,
                attachTo: {
                    element: '#frm',
                    on: 'top'
                },
                scrollTo: false,
                modalOverlayOpeningPadding: 5,
                modalOverlayOpeningRadius: 5,
                popperOptions: {
                    modifiers: [{name: 'offset', options: {offset: [0, 20]}}]
                },
                when: {
                    show() {
                        wizard_progress_bar(9);
                    }
                },
            });
        }
        if(initial_index<10) {
            wizard_steps[9] = wizard_tour.addStep({
                title: '10 - ' + window.backend_labels.wizard_title_2,
                text: window.backend_labels.wizard_text_10,
                attachTo: {
                    element: '#btn_create_room',
                    on: 'top'
                },
                scrollTo: true,
                modalOverlayOpeningRadius: 5,
                popperOptions: {
                    modifiers: [{name: 'offset', options: {offset: [0, 15]}}]
                },
                when: {
                    show() {
                        wizard_progress_bar(10);
                    }
                },
            });
        }
        if(initial_index<11) {
            wizard_steps[10] = wizard_tour.addStep({
                title: '11 - ' + window.backend_labels.wizard_title_2,
                text: window.backend_labels.wizard_text_11,
                scrollTo: false,
                modalOverlayOpeningRadius: 5,
                buttons: [{
                    text: window.backend_labels.wizard_continue,
                    action: wizard_tour.next
                }],
                popperOptions: {
                    modifiers: [{name: 'offset', options: {offset: [0, 0]}}]
                },
                when: {
                    show() {
                        wizard_progress_bar(11);
                    }
                },
            });
        }
        if(initial_index<12) {
            wizard_steps[11] = wizard_tour.addStep({
                title: '12 - ' + window.backend_labels.wizard_title_3,
                text: window.backend_labels.wizard_text_6,
                attachTo: {
                    element: '#room_menu_item',
                    on: 'right'
                },
                modalOverlayOpeningRadius: 5,
                popperOptions: {
                    modifiers: [{name: 'offset', options: {offset: [0, 25]}}]
                },
                when: {
                    show() {
                        wizard_progress_bar(12);
                    }
                },
            });
        }
        if(initial_index<13) {
            wizard_steps[12] = wizard_tour.addStep({
                title: '13 - ' + window.backend_labels.wizard_title_3,
                text: window.backend_labels.wizard_text_7,
                attachTo: {
                    element: '#btn_modal_create_room',
                    on: 'left'
                },
                scrollTo: false,
                modalOverlayOpeningRadius: 20,
                advanceOn: {selector: '#btn_modal_create_room', event: 'click'},
                popperOptions: {
                    modifiers: [{name: 'offset', options: {offset: [0, 30]}}]
                },
                when: {
                    show() {
                        wizard_progress_bar(13);
                    }
                },
            });
        }
        if(initial_index<14) {
            wizard_steps[13] = wizard_tour.addStep({
                title: '14 - ' + window.backend_labels.wizard_title_3,
                text: window.backend_labels.wizard_text_8,
                attachTo: {
                    element: '#name_div',
                    on: 'bottom'
                },
                scrollTo: false,
                modalOverlayOpeningPadding: 5,
                modalOverlayOpeningRadius: 5,
                beforeShowPromise: function() {
                    return new Promise(function(resolve) {
                        setTimeout(function () {
                            resolve();
                        },250);
                    });
                },
                buttons: [{
                    text: window.backend_labels.wizard_continue,
                    action: function () {
                        if($('#name').val()=='') {
                            $('#name').focus();
                            return false;
                        } else {
                            Shepherd.activeTour.next();
                        }
                    }
                }],
                when: {
                    show: function() {
                        wizard_progress_bar(14);
                        setTimeout(function () {
                            $('#name').focus();
                        },400);
                    }
                },
                popperOptions: {
                    modifiers: [{name: 'offset', options: {offset: [0, 20]}}]
                }
            });
        }
        if(initial_index<15) {
            wizard_steps[14] = wizard_tour.addStep({
                title: '15 - ' + window.backend_labels.wizard_title_3,
                text: window.backend_labels.wizard_text_9,
                attachTo: {
                    element: '#frm',
                    on: 'top'
                },
                scrollTo: false,
                modalOverlayOpeningPadding: 5,
                modalOverlayOpeningRadius: 5,
                popperOptions: {
                    modifiers: [{name: 'offset', options: {offset: [0, 20]}}]
                },
                when: {
                    show() {
                        wizard_progress_bar(15);
                    }
                },
            });
        }
        if(initial_index<16) {
            wizard_steps[15] = wizard_tour.addStep({
                title: '16 - ' + window.backend_labels.wizard_title_3,
                text: window.backend_labels.wizard_text_10,
                scrollTo: true,
                attachTo: {
                    element: '#btn_create_room',
                    on: 'top'
                },
                modalOverlayOpeningRadius: 5,
                popperOptions: {
                    modifiers: [{name: 'offset', options: {offset: [0, 15]}}]
                },
                when: {
                    show() {
                        wizard_progress_bar(16);
                    }
                },
            });
        }
        if(initial_index<17) {
            wizard_steps[16] = wizard_tour.addStep({
                title: '17 - ' + window.backend_labels.wizard_title_3,
                text: window.backend_labels.wizard_text_17,
                scrollTo: false,
                modalOverlayOpeningRadius: 5,
                buttons: [{
                    text: window.backend_labels.wizard_continue,
                    action: wizard_tour.next
                }],
                popperOptions: {
                    modifiers: [{name: 'offset', options: {offset: [0, 0]}}]
                },
                when: {
                    show() {
                        wizard_progress_bar(17);
                    }
                },
            });
        }
        if(initial_index<18) {
            wizard_steps[17] = wizard_tour.addStep({
                title: '18 - ' + window.backend_labels.wizard_title_4,
                text: window.backend_labels.wizard_text_18,
                attachTo: {
                    element: '#markers_menu_item',
                    on: 'right'
                },
                modalOverlayOpeningRadius: 5,
                popperOptions: {
                    modifiers: [{name: 'offset', options: {offset: [0, 25]}}]
                },
                when: {
                    show() {
                        wizard_progress_bar(18);
                    }
                },
            });
        }
        if(initial_index<19) {
            wizard_steps[18] = wizard_tour.addStep({
                title: '19 - ' + window.backend_labels.wizard_title_4,
                text:  window.backend_labels.wizard_text_19,
                attachTo: {
                    element: '#rooms_slider_m .slick-slide:first-child',
                    on: 'bottom'
                },
                modalOverlayOpeningRadius: 5,
                popperOptions: {
                    modifiers: [{name: 'offset', options: {offset: [0, 15]}}]
                },
                when: {
                    show() {
                        wizard_progress_bar(19);
                    }
                },
            });
        }
        if(initial_index<20) {
            wizard_steps[19] = wizard_tour.addStep({
                title: '20 - ' + window.backend_labels.wizard_title_4,
                text:  window.backend_labels.wizard_text_20,
                attachTo: {
                    element: '#panorama_markers .pnlm-render-container canvas',
                    on: 'bottom'
                },
                modalOverlayOpeningRadius: 5,
                buttons: [{
                    text: window.backend_labels.wizard_continue,
                    action: wizard_tour.next
                }],
                popperOptions: {
                    modifiers: [{name: 'offset', options: {offset: [0, 15]}}]
                },
                when: {
                    show() {
                        wizard_progress_bar(20);
                    }
                },
            });
        }
        if(initial_index<21) {
            wizard_steps[20] = wizard_tour.addStep({
                title: '21 - ' + window.backend_labels.wizard_title_4,
                text: window.backend_labels.wizard_text_7,
                attachTo: {
                    element: '#btn_add_marker',
                    on: 'left'
                },
                modalOverlayOpeningRadius: 20,
                when: {
                    show: function() {
                        wizard_progress_bar(21);
                        $('#btn_add_marker').removeClass('disabled');
                    }
                },
                advanceOn: {selector: '#btn_add_marker', event: 'click'},
                popperOptions: {
                    modifiers: [{name: 'offset', options: {offset: [0, 15]}}]
                }
            });
        }
        if(initial_index<22) {
            wizard_steps[21] = wizard_tour.addStep({
                title: '22 - ' + window.backend_labels.wizard_title_4,
                text:  window.backend_labels.wizard_text_22,
                attachTo: {
                    element: '#room_target_add_div',
                    on: 'bottom'
                },
                canClickTarget: false,
                modalOverlayOpeningPadding: 5,
                modalOverlayOpeningRadius: 5,
                buttons: [{
                    text: window.backend_labels.wizard_continue,
                    action: wizard_tour.next
                }],
                beforeShowPromise: function() {
                    return new Promise(function(resolve) {
                        setTimeout(function () {
                            resolve();
                        },250);
                    });
                },
                popperOptions: {
                    modifiers: [{name: 'offset', options: {offset: [0, 20]}}]
                },
                when: {
                    show() {
                        wizard_progress_bar(22);
                    }
                },
            });
        }
        if(initial_index<23) {
            wizard_steps[22] = wizard_tour.addStep({
                title: '23 - ' + window.backend_labels.wizard_title_4,
                text:  window.backend_labels.wizard_text_23,
                attachTo: {
                    element: '#btn_new_marker',
                    on: 'left'
                },
                modalOverlayOpeningRadius: 5,
                popperOptions: {
                    modifiers: [{name: 'offset', options: {offset: [0, 15]}}]
                },
                when: {
                    show() {
                        wizard_progress_bar(23);
                    }
                },
            });
        }
        if(initial_index<24) {
            wizard_steps[23] = wizard_tour.addStep({
                title: '24 - ' + window.backend_labels.wizard_title_4,
                text:  window.backend_labels.wizard_text_24,
                attachTo: {
                    element: '.div_marker_wrapper:first-child',
                    on: 'bottom'
                },
                scrollTo: false,
                modalOverlayOpeningPadding: 10,
                modalOverlayOpeningRadius: 20,
                buttons: [{
                    text: window.backend_labels.wizard_continue,
                    action: wizard_tour.next
                }],
                popperOptions: {
                    modifiers: [{name: 'offset', options: {offset: [0, 25]}}]
                },
                when: {
                    show() {
                        wizard_progress_bar(24);
                    }
                },
            });
        }
        if(initial_index<25) {
            wizard_steps[24] = wizard_tour.addStep({
                title: '25 - ' + window.backend_labels.wizard_title_5,
                text:  window.backend_labels.wizard_text_25,
                attachTo: {
                    element: '#preview_menu_item',
                    on: 'right'
                },
                scrollTo: true,
                modalOverlayOpeningRadius: 5,
                popperOptions: {
                    modifiers: [{name: 'offset', options: {offset: [0, 15]}}]
                },
                when: {
                    show() {
                        wizard_progress_bar(25);
                    }
                },
            });
        }
    }
    $(document).ready(function() {
        if(window.wizard_step!=-1 && window.wizard_step!=18) {
            create_wizard(window.wizard_step);
            wizard_tour_open = true;
            wizard_tour.start();
        }
    });

    window.save_vt_ui = function (save,preset_save,apply_preset) {
        setTimeout(function () {
            fix_colors_menu();
        },200);
        var show_vt_title = $('#show_vt_title').is(':checked');
        if(show_vt_title) {
            $('.name_vt, .room_vt').removeClass('ui_disabled');
        } else {
            $('.name_vt, .room_vt').addClass('ui_disabled');
        }
        var show_audio = $('#show_audio').is(':checked');
        if(show_audio) {
            $('.song_control').removeClass('ui_disabled');
        } else {
            $('.song_control').addClass('ui_disabled');
        }
        var show_fullscreen = $('#show_fullscreen').is(':checked');
        if(show_fullscreen) {
            $('.fullscreen_control').removeClass('ui_disabled');
        } else {
            $('.fullscreen_control').addClass('ui_disabled');
        }
        var show_map = parseInt($('#show_map option:selected').attr('id'));
        switch (show_map) {
            case 0:
                $('.map_control').addClass('ui_disabled');
                $('.map img').addClass('ui_disabled');
                $('.map_control i').removeClass('icon-map_on').addClass('icon-map_off');
                break;
            case 1:
            case 2:
                $('.map_control').removeClass('ui_disabled');
                $('.map img').removeClass('ui_disabled');
                $('.map_control i').removeClass('icon-map_on').addClass('icon-map_off');
                break;
            case 3:
                $('.map_control').removeClass('ui_disabled');
                $('.map img').removeClass('ui_disabled');
                $('.map_control i').removeClass('icon-map_off').addClass('icon-map_on');
                break;
            case 4:
                $('.map_control').removeClass('ui_disabled');
                $('.map img').addClass('ui_disabled');
                $('.map_control i').removeClass('icon-map_on').addClass('icon-map_off');
                break;
        }
        var show_map_tour = parseInt($('#show_map_tour option:selected').attr('id'));
        switch (show_map_tour) {
            case 0:
                $('.map_tour_control').addClass('ui_disabled');
                $('.map_tour_control i').removeClass('fas').addClass('far');
                break;
            case 1:
                $('.map_tour_control').removeClass('ui_disabled');
                $('.map_tour_control i').removeClass('fas').addClass('far');
                break;
            case 2:
                $('.map_tour_control').removeClass('ui_disabled');
                $('.map_tour_control i').removeClass('far').addClass('fas');
                break;
        }
        var show_list_alt = parseInt($('#show_list_alt option:selected').attr('id'));
        switch (show_list_alt) {
            case 0:
                $('.list_alt_menu').addClass('ui_disabled');
                break;
            case 1:
                $('.list_alt_menu').removeClass('ui_disabled');
                break;
            case 2:
                $('.list_alt_menu').removeClass('ui_disabled');
                break;
        }
        var show_annotations = $('#show_annotations').is(':checked');
        if(show_annotations) {
            $('.annotation').removeClass('ui_disabled');
            $('.annotations_control').removeClass('ui_disabled');
        } else {
            $('.annotation').addClass('ui_disabled');
            $('.annotations_control').addClass('ui_disabled');
        }
        var show_logo = $('#show_logo').is(':checked');
        if(show_logo) {
            $('.logo img').removeClass('ui_disabled');
        } else {
            $('.logo img').addClass('ui_disabled');
        }
        var show_nav_control = $('#show_nav_control').is(':checked');
        if(show_nav_control) {
            $('.nav_control').removeClass('ui_disabled');
        } else {
            $('.nav_control').addClass('ui_disabled');
        }
        var voice_commands = parseInt($('#voice_commands option:selected').attr('id'));
        switch (voice_commands) {
            case 0:
                $('#skitt-ui').addClass('ui_disabled');
                break;
            case 1:
            case 2:
                $('#skitt-ui').removeClass('ui_disabled');
                break;
        }
        var show_custom = parseInt($('#show_custom option:selected').attr('id'));
        switch (show_custom) {
            case 0:
                $('.custom_control').addClass('ui_disabled');
                break;
            case 1:
            case 2:
                $('.custom_control').removeClass('ui_disabled');
                break;
        }
        var show_info = parseInt($('#show_info option:selected').attr('id'));
        switch (show_info) {
            case 0:
                $('.info_control').addClass('ui_disabled');
                break;
            case 1:
            case 2:
                $('.info_control').removeClass('ui_disabled');
                break;
        }
        var show_dollhouse = parseInt($('#show_dollhouse option:selected').attr('id'));
        switch (show_dollhouse) {
            case 0:
                $('.dollhouse_control').addClass('ui_disabled');
                break;
            case 1:
            case 2:
                $('.dollhouse_control').removeClass('ui_disabled');
                break;
        }
        var show_gallery = parseInt($('#show_gallery option:selected').attr('id'));
        switch (show_gallery) {
            case 0:
                $('.gallery_control').addClass('ui_disabled');
                break;
            case 1:
            case 2:
                $('.gallery_control').removeClass('ui_disabled');
                break;
        }
        var compass = $('#compass').is(':checked');
        if(compass) {
            $('.compass_control').removeClass('ui_disabled');
        } else {
            $('.compass_control').addClass('ui_disabled');
        }
        var fb_messenger = $('#fb_messenger').is(':checked');
        if(fb_messenger) {
            $('.facebook_control').removeClass('ui_disabled');
        } else {
            $('.facebook_control').addClass('ui_disabled');
        }
        var whatsapp_chat = $('#whatsapp_chat').is(':checked');
        if(whatsapp_chat) {
            $('.whatsapp_control').removeClass('ui_disabled');
        } else {
            $('.whatsapp_control').addClass('ui_disabled');
        }
        var arrows_nav = parseInt($('#arrows_nav option:selected').attr('id'));
        switch (arrows_nav) {
            case 0:
                $('.prev_arrow').addClass('ui_disabled');
                $('.next_arrow').addClass('ui_disabled');
                break;
            case 1:
            case 2:
                $('.prev_arrow').removeClass('ui_disabled');
                $('.next_arrow').removeClass('ui_disabled');
                break;
        }
        var auto_show_slider = parseInt($('#auto_show_slider option:selected').attr('id'));
        switch (auto_show_slider) {
            case 2:
                $('.list_control').addClass('ui_disabled');
                $('.list_control_alt').addClass('ui_disabled');
                $('.list_control i').removeClass('fa-chevron-down').addClass('fa-chevron-up');
                $('.list_control_alt').removeClass('fa-chevron-down').addClass('fa-chevron-up');
                $('.list_slider ul').addClass('ui_disabled');
                $('.list_slider li').addClass('ui_disabled');
                $('.list_slider #list_left').addClass('ui_disabled');
                $('.list_slider #list_right').addClass('ui_disabled');
                break;
            case 1:
                $('.list_control').removeClass('ui_disabled');
                $('.list_control_alt').removeClass('ui_disabled');
                $('.list_control i').removeClass('fa-chevron-up').addClass('fa-chevron-down');
                $('.list_control_alt').removeClass('fa-chevron-up').addClass('fa-chevron-down');
                $('.list_slider ul').removeClass('ui_disabled');
                $('.list_slider li').removeClass('ui_disabled');
                $('.list_slider #list_left').removeClass('ui_disabled');
                $('.list_slider #list_right').removeClass('ui_disabled');
                break;
            case 0:
                $('.list_control').removeClass('ui_disabled');
                $('.list_control_alt').removeClass('ui_disabled');
                $('.list_control i').removeClass('fa-chevron-down').addClass('fa-chevron-up');
                $('.list_control_alt').removeClass('fa-chevron-down').addClass('fa-chevron-up');
                $('.list_slider ul').removeClass('ui_disabled');
                $('.list_slider li').removeClass('ui_disabled');
                $('.list_slider #list_left').removeClass('ui_disabled');
                $('.list_slider #list_right').removeClass('ui_disabled');
                break;
        }
        var nav_slider = parseInt($('#nav_slider option:selected').attr('id'));
        switch (nav_slider) {
            case 2:
                $('.list_slider #list_left').addClass('ui_disabled');
                $('.list_slider #list_right').addClass('ui_disabled');
                break;
            case 0:
            case 1:
                if(auto_show_slider!=2) {
                    $('.list_slider #list_left').removeClass('ui_disabled');
                    $('.list_slider #list_right').removeClass('ui_disabled');
                }
                break;
        }
        var show_icons_toggle = $('#show_icons_toggle').is(':checked');
        if(show_icons_toggle) {
            $('.icons_control').removeClass('ui_disabled');
        } else {
            $('.icons_control').addClass('ui_disabled');
        }
        var show_autorotation_toggle = $('#show_autorotation_toggle').is(':checked');
        if(show_autorotation_toggle) {
            $('.autorotate_control').removeClass('ui_disabled');
        } else {
            $('.autorotate_control').addClass('ui_disabled');
        }
        var show_device_orientation = parseInt($('#show_device_orientation option:selected').attr('id'));
        switch (show_device_orientation) {
            case 0:
                $('.orient_control').addClass('ui_disabled');
                $('.orient_control .fa-circle').removeClass('active').addClass('not_active');
                break;
            case 1:
                $('.orient_control').removeClass('ui_disabled');
                $('.orient_control .fa-circle').removeClass('active').addClass('not_active');
                break;
            case 2:
                $('.orient_control').removeClass('ui_disabled');
                $('.orient_control .fa-circle').removeClass('not_active').addClass('active');
                break;
        }
        var drag_device_orientation = $('#drag_device_orientation').is(':checked');
        var show_webvr = $('#show_webvr').is(':checked');
        if(show_webvr) {
            $('.vr_control').removeClass('ui_disabled');
        } else {
            $('.vr_control').addClass('ui_disabled');
        }
        var show_presentation = parseInt($('#show_presentation option:selected').attr('id'));
        switch (show_presentation) {
            case 0:
                $('.presentation_control').addClass('ui_disabled');
                break;
            case 1:
            case 2:
                $('.presentation_control').removeClass('ui_disabled');
                $('.presentation_control').removeClass('ui_disabled');
                break;
        }
        var show_share = $('#show_share option:selected').attr('id');
        if(show_share>0) {
            $('.share_control').removeClass('ui_disabled');
        } else {
            $('.share_control').addClass('ui_disabled');
        }
        var show_main_form = $('#show_main_form').is(':checked');
        if(show_main_form) {
            $('.form_control').removeClass('ui_disabled');
        } else {
            $('.form_control').addClass('ui_disabled');
        }
        var meeting = parseInt($('#meeting option:selected').attr('id'));
        switch (meeting) {
            case 0:
                $('.meeting_control').addClass('ui_disabled');
                break;
            case 1:
            case 2:
                $('.meeting_control').removeClass('ui_disabled');
                $('.meeting_control').removeClass('ui_disabled');
                break;
        }
        var live_session = $('#live_session').is(':checked');
        if(live_session) {
            $('.live_control').removeClass('ui_disabled');
        } else {
            $('.live_control').addClass('ui_disabled');
        }
        var logo_height = $('#logo_height').val();
        if(logo_height=='') logo_height=40;
        $('.logo img').css('height',logo_height+'px');
        var logo_position = $('#logo_position option:selected').attr('id');
        $('.logo').removeClass('logo_top_left').removeClass('logo_top_right').addClass('logo_'+logo_position);
        var annotation_position = $('#annotation_position option:selected').attr('id');
        $('.annotation').removeClass('annotation_top_left').removeClass('annotation_top_right').addClass('annotation_'+annotation_position);
        var map_position = $('#map_position option:selected').attr('id');
        $('.map').removeClass('map_top_left').removeClass('map_top_right').addClass('map_'+map_position);
        var autoclose_menu = $('#autoclose_menu').is(':checked');
        var autoclose_list_alt = $('#autoclose_list_alt').is(':checked');
        var autoclose_slider = $('#autoclose_slider').is(':checked');
        var autoclose_map = $('#autoclose_map').is(':checked');
        var array_colors = {};
        $('.color_picker').each(function () {
           var id_cp = $(this).attr('id');
           array_colors[id_cp]=$(this).val();
        });
        $('.color_picker_bg').each(function () {
            var id_cp = $(this).attr('id');
            array_colors[id_cp]=$(this).val();
        });
        var array_positions = {};
        $('.position_select').each(function () {
            var id_pos = $(this).attr('id');
            array_positions[id_pos]=$("#"+id_pos+' option:selected').attr('id');
        });
        var annotation_position = $('#annotation_position option:selected').attr('id');
        var map_position = $('#map_position option:selected').attr('id');
        var logo_position = $('#logo_position option:selected').attr('id');
        var logo_height = $('#logo_height').val();
        var array_orders = {};
        $('#controls_bottom_left div').each(function () {
            var id = $(this).attr('id');
            if(id!=null && id!='skitt-ui' && !$(this).hasClass('ui_hidden')) {
                array_orders[id]=$(this).css('order');
            }
        });
        $('#controls_bottom_center div').each(function () {
            var id = $(this).attr('id');
            if(id!=null && id!='skitt-ui' && !$(this).hasClass('ui_hidden')) {
                array_orders[id]=$(this).css('order');
            }
        });
        $('#controls_bottom_right div').each(function () {
            var id = $(this).attr('id');
            if(id!=null && id!='skitt-ui' && !$(this).hasClass('ui_hidden')) {
                array_orders[id]=$(this).css('order');
            }
        });
        $('.menu_controls .dropdown p').each(function () {
            var id = $(this).attr('id');
            if(id!=null && id!='skitt-ui' && !$(this).hasClass('ui_hidden')) {
                array_orders[id]=$(this).css('order');
            }
        });
        var array_icons = {};
        $('.icon_picker_value').each(function () {
            var id = $(this).attr('id').replace("_icon","");
            if(id!=null) {
                array_icons[id]=$(this).val();
            }
        });
        var content = [{},{},{},{},{},{},{},{},{},{},{}];
        content[0]['title'] = $('#form_title').val();
        content[0]['button'] = $('#form_button').val();
        content[0]['response'] = $('#form_response').val();
        content[0]['description'] = $('#form_description').val();
        content[0]['send_email'] = $('#form_send_email').is(':checked');
        content[0]['email'] = $('#form_email').val();
        for(var i=1;i<=10;i++) {
            content[i]['enabled'] = $('#form_field_'+i).is(':checked');
            content[i]['required'] = $('#form_field_required_'+i).is(':checked');
            content[i]['type'] = $('#form_field_type_'+i+' option:selected').attr('id');
            content[i]['label'] = $('#form_field_label_'+i).val();
        }
        var form_content = JSON.stringify(content);
        var form_enable = show_main_form;
        var custom_title = $('#custom_title').val();
        var custom_content = window.custom_content_html.getValue();
        var markers_icon = $('#markers_icon').val();
        var markers_id_icon_library = $('#marker_library_icon').val();
        var markers_color = $('#markers_color').val();
        var markers_background = $('#markers_background').val();
        var markers_style = $('#markers_style option:selected').attr('id');
        var markers_tooltip_type = $('#markers_tooltip_type option:selected').attr('id');
        var pois_icon = $('#pois_icon').val();
        var pois_id_icon_library = $('#poi_library_icon').val();
        var pois_color = $('#pois_color').val();
        var pois_background = $('#pois_background').val();
        var pois_style = $('#pois_style option:selected').attr('id');
        var pois_tooltip_type = $('#pois_tooltip_type option:selected').attr('id');
        switch(markers_style) {
            case 'room_and_icon':
                var markers_show_room = 2;
                break;
            case 'icon_and_room':
                var markers_show_room = 1;
                break;
            case 'only_icon':
                var markers_show_room = 0;
                break;
            case 'only_room':
                var markers_show_room = 3;
                break;
            case 'custom_icon':
                var markers_show_room = 4;
                break;
            case 'preview_room':
                var markers_show_room = 5;
                break;
        }
        switch(pois_style) {
            case 'label_and_icon':
                var pois_style_t = 3;
                break;
            case 'icon_and_label':
                var pois_style_t = 2;
                break;
            case 'only_icon':
                var pois_style_t = 0;
                break;
            case 'only_label':
                var pois_style_t = 4;
                break;
            case 'custom_icon':
                var pois_style_t = 1;
                break;
        }
        var font_viewer = $('#font_viewer').val();
        toggle_menu_control_disabled_ui();
        adjust_elements_positions();
        if(preset_save) {
            apply_preset=0;
            var id_preset = parseInt($('#presets_editor_ui option:selected').attr('id'));
            if(id_preset!=0) {
                var r = confirm(window.backend_labels.confirm_save_preset);
                if (r == false) {
                    save = false;
                    return;
                }
            }
            var name_preset = $('#preset_new_name').val();
            var preset_public = $('#preset_public').is(':checked');
            if(id_preset==0 && name_preset=='') {
                $('#preset_new_name').addClass('error-highlight');
                save = false;
                return;
            } else {
                $('#preset_new_name').removeClass('error-highlight');
            }
        } else if(apply_preset) {
            apply_preset=1;
            var id_preset = parseInt($('#presets_editor_ui option:selected').attr('id'));
            var name_preset = '';
            var preset_public = 0;
        } else {
            apply_preset=0;
            var id_preset = null;
            var name_preset = '';
            var preset_public = 0;
        }
        if(save) {
            $('#modal_presets button').addClass('disabled');
            $('#save_btn .icon i').removeClass('far fa-circle').addClass('fas fa-circle-notch fa-spin');
            $('#save_btn').addClass("disabled");
            $.ajax({
                url: "ajax/save_virtual_tour_ui.php",
                type: "POST",
                data: {
                    id_virtualtour: window.id_virtualtour,
                    id_user: window.id_user,
                    arrows_nav: arrows_nav,
                    voice_commands: voice_commands,
                    compass: compass,
                    auto_show_slider: auto_show_slider,
                    nav_slider: nav_slider,
                    show_list_alt: show_list_alt,
                    show_custom: show_custom,
                    show_info: show_info,
                    show_dollhouse: show_dollhouse,
                    show_gallery: show_gallery,
                    show_icons_toggle: show_icons_toggle,
                    show_autorotation_toggle: show_autorotation_toggle,
                    show_nav_control: show_nav_control,
                    show_presentation: show_presentation,
                    show_main_form: show_main_form,
                    show_share: show_share,
                    show_device_orientation: show_device_orientation,
                    drag_device_orientation: drag_device_orientation,
                    show_webvr: show_webvr,
                    show_audio: show_audio,
                    show_vt_title: show_vt_title,
                    show_fullscreen: show_fullscreen,
                    show_map: show_map,
                    show_map_tour: show_map_tour,
                    live_session: live_session,
                    meeting: meeting,
                    show_annotations: show_annotations,
                    autoclose_menu: autoclose_menu,
                    autoclose_list_alt: autoclose_list_alt,
                    autoclose_slider: autoclose_slider,
                    autoclose_map: autoclose_map,
                    show_logo: show_logo,
                    fb_messenger: fb_messenger,
                    whatsapp_chat: whatsapp_chat,
                    array_colors: array_colors,
                    array_positions: array_positions,
                    array_orders: array_orders,
                    array_icons: array_icons,
                    annotation_position: annotation_position,
                    map_position: map_position,
                    logo_position: logo_position,
                    logo_height: logo_height,
                    form_enable: form_enable,
                    form_content: form_content,
                    custom_title: custom_title,
                    custom_content: custom_content,
                    markers_icon: markers_icon,
                    markers_id_icon_library: markers_id_icon_library,
                    markers_color: markers_color,
                    markers_background: markers_background,
                    markers_show_room: markers_show_room,
                    markers_tooltip_type: markers_tooltip_type,
                    pois_icon: pois_icon,
                    pois_id_icon_library: pois_id_icon_library,
                    pois_color: pois_color,
                    pois_background: pois_background,
                    pois_style: pois_style_t,
                    pois_tooltip_type: pois_tooltip_type,
                    font_viewer: font_viewer,
                    id_preset: id_preset,
                    name_preset: name_preset,
                    preset_public: preset_public,
                    apply_preset: apply_preset
                },
                async: true,
                success: function (json) {
                    var rsp = JSON.parse(json);
                    if(rsp.status=="ok") {
                        if(apply_preset==1) {
                            window.ui_need_save = false;
                            location.reload();
                        } else if(preset_save) {
                            if(id_preset==0) {
                                var id_new_preset = rsp.id_preset;
                                get_editor_ui_presets(id_new_preset);
                            } else {
                                get_editor_ui_presets(id_preset);
                            }
                            $('#modal_presets button').removeClass('disabled');
                            $('#save_btn .icon i').removeClass('fas fa-circle-notch fa-spin').addClass('fas fa-check');
                            setTimeout(function () {
                                $('#save_btn .icon i').removeClass('fas fa-check').addClass('far fa-circle');
                                $('#save_btn').removeClass("disabled");
                            },1000);
                        } else {
                            window.ui_need_save = false;
                            $('#save_btn .icon i').removeClass('fas fa-circle-notch fa-spin').addClass('fas fa-check');
                            setTimeout(function () {
                                $('#save_btn .icon i').removeClass('fas fa-check').addClass('far fa-circle');
                                $('#save_btn').removeClass("disabled");
                            },1000);
                        }
                    } else {
                        $('#modal_presets button').removeClass('disabled');
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
    }

    window.change_ui_position = function (id) {
        var position = $('#'+id+' option:selected').attr('id');
        var target = $('#'+id).attr('data-target');
        switch(target) {
            case 'list_control':
            case 'arrows_control':
                $('.controls_arrows').addClass('ui_hidden');
                $('.list_control').addClass('ui_hidden');
                $('.list_control_alt').addClass('ui_hidden');
                $('.arrows_nav').addClass('ui_hidden');
                $('.prev_arrow').addClass('ui_hidden');
                $('.next_arrow').addClass('ui_hidden');
                var position_list = $('#position_list option:selected').attr('id');
                var position_arrows = $('#position_arrows option:selected').attr('id');
                if(target=='list_control' && position_list!='default' && position_arrows!='default') {
                    $('#position_arrows option').prop('selected',false);
                    $('#position_arrows option[id="'+position_list+'"]').prop('selected',true);
                    position_arrows = position_list;
                }
                if(target=='arrows_control' && position_list!='default' && position_arrows!='default') {
                    $('#position_list option').prop('selected',false);
                    $('#position_list option[id="'+position_arrows+'"]').prop('selected',true);
                    position_list = position_arrows;
                }
                position_list = position_list.replace('button_','');
                position_arrows = position_arrows.replace('button_','');
                if(position_list!='default') {
                    $('#controls_bottom_'+position_list+' .list_control_alt').removeClass('ui_hidden');
                    $('#list_background').removeClass('d-none');
                    $('#list_background_hover').removeClass('d-none');
                } else {
                    $('.list_control').removeClass('ui_hidden');
                    $('#list_background').addClass('d-none');
                    $('#list_background_hover').addClass('d-none');
                }
                if(position_arrows!='default') {
                    $('#controls_bottom_'+position_arrows+' .prev_arrow').removeClass('ui_hidden');
                    $('#controls_bottom_'+position_arrows+' .next_arrow').removeClass('ui_hidden');
                    $('#arrows_background').removeClass('d-none');
                    $('#arrows_background_hover').removeClass('d-none');
                    $('.controls_arrows').css('background-color',$('#arrows_background').val());
                } else {
                    $('.arrows_nav').removeClass('ui_hidden');
                    $('.arrows_nav .prev_arrow').removeClass('ui_hidden');
                    $('.arrows_nav .next_arrow').removeClass('ui_hidden');
                    $('#arrows_background').addClass('d-none');
                    $('#arrows_background_hover').addClass('d-none');
                    $('.controls_arrows').css('background-color','transparent');
                }
                $('.controls_arrows i:not(.ui_hidden):first').css('margin-right','0');
                $('.controls_arrows i:not(.ui_hidden):nth-child(2)').css('margin-right','0');
                if((position_list!='default') || (position_arrows!='default')) {
                    if(position_list!='default') {
                        $('#controls_arrow_'+position_list+'').removeClass('ui_hidden');
                        if(!$('#controls_arrow_'+position_list+' .prev_arrow').hasClass('ui_hidden')) {
                            $('.controls_arrows i:not(.ui_hidden):first').css('margin-right','-9px');
                            $('.controls_arrows i:not(.ui_hidden):nth-child(2)').css('margin-right','-9px');
                        }
                    }
                    if(position_arrows!='default') {
                        $('#controls_arrow_'+position_arrows+'').removeClass('ui_hidden');
                        if(!$('#controls_arrow_'+position_arrows+' .prev_arrow').hasClass('ui_hidden')) {
                            $('.controls_arrows i:not(.ui_hidden):first').css('margin-right','-9px');
                            $('.controls_arrows i:not(.ui_hidden):nth-child(2)').css('margin-right','-9px');
                        }
                    }
                }
                if($('.list_control').hasClass('ui_hidden')) {
                    $('#controls_bottom_center').css('bottom','112px');
                } else {
                    $('#controls_bottom_center').css('bottom','136px');
                }
                break;
            default:
                switch (position) {
                    case 'button_left':
                        $('.'+target).addClass('ui_hidden');
                        $('#controls_bottom_left .'+target).removeClass('ui_hidden');
                        break;
                    case 'button_center':
                        $('.'+target).addClass('ui_hidden');
                        $('#controls_bottom_center .'+target).removeClass('ui_hidden');
                        break;
                    case 'button_right':
                        $('.'+target).addClass('ui_hidden');
                        $('#controls_bottom_right .'+target).removeClass('ui_hidden');
                        break;
                    case 'menu':
                        $('.'+target).addClass('ui_hidden');
                        $('.menu_controls .dropdown .'+target).removeClass('ui_hidden');
                        break;
                }
                break;
        }
        fix_ui_order();
        save_vt_ui(false,false,false);
    }

    window.change_ui_order = function (direction,id_curr,id_pos) {
        var position = $('#'+id_pos+' option:selected').attr('id');
        switch (position) {
            case 'button_left':
                var container = '#controls_bottom_left';
                var elements = container+' div';
                break;
            case 'button_center':
                var container = '#controls_bottom_center';
                var elements = container+' div';
                break;
            case 'button_right':
                var container = '#controls_bottom_right';
                var elements = container+' div';
                break;
            case 'menu':
                var container = '.menu_controls .dropdown';
                var elements = container+' p';
                break;
        }
        var current_order = $(container+' .'+id_curr).css('order');
        var array_orders = [];
        $(elements).each(function () {
           var id = $(this).attr('id');
           if(id!=null && id!='skitt-ui' && !$(this).hasClass('ui_hidden')) {
               var order = $(this).css('order');
               array_orders.push({'id':id,'order':order});
           }
        });
        array_orders.sort((a,b) =>  a.order-b.order);
        var len = array_orders.length;
        for(var i=0;i<len;i++) {
            if(current_order==array_orders[i].order) {
                switch(direction) {
                    case 'next':
                        var current = array_orders[i];
                        var next = array_orders[(i+1)%len];
                        var next_order = $("#"+next.id).css('order');
                        $(container+' .'+id_curr).css('order',next_order);
                        $("#"+next.id).css('order',current_order);
                        break;
                    case 'prev':
                        var current = array_orders[i];
                        var previous = array_orders[(i+len-1)%len];
                        var prev_order = $("#"+previous.id).css('order');
                        $(container+' .'+id_curr).css('order',prev_order);
                        $("#"+previous.id).css('order',current_order);
                        break;
                }
                break;
            }
        }
        setTimeout(function() {
            save_vt_ui(false,false,false);
        },200);
    }

    window.fix_ui_order = function () {
        var array_orders = [];
        $('#controls_bottom_right div').each(function () {
            var id = $(this).attr('id');
            if(id!=null && id!='skitt-ui' && !$(this).hasClass('ui_hidden')) {
                var order = $(this).css('order');
                array_orders.push({'id':id,'order':order});
            }
        });
        array_orders.sort((a,b) =>  a.order-b.order);
        var len = array_orders.length;
        for(var i=0;i<len;i++) {
            $("#"+array_orders[i].id).css('order',i);
        }
        array_orders = [];
        $('#controls_bottom_center div').each(function () {
            var id = $(this).attr('id');
            if(id!=null && id!='skitt-ui' && !$(this).hasClass('ui_hidden')) {
                var order = $(this).css('order');
                array_orders.push({'id':id,'order':order});
            }
        });
        array_orders.sort((a,b) =>  a.order-b.order);
        var len = array_orders.length;
        for(var i=0;i<len;i++) {
            $("#"+array_orders[i].id).css('order',i);
        }
        array_orders = [];
        $('#controls_bottom_left div').each(function () {
            var id = $(this).attr('id');
            if(id!=null && id!='skitt-ui' && !$(this).hasClass('ui_hidden')) {
                var order = $(this).css('order');
                array_orders.push({'id':id,'order':order});
            }
        });
        array_orders.sort((a,b) =>  a.order-b.order);
        var len = array_orders.length;
        for(var i=0;i<len;i++) {
            $("#"+array_orders[i].id).css('order',i);
        }
        array_orders = [];
        $('.menu_controls .dropdown p').each(function () {
            var id = $(this).attr('id');
            if(id!=null && id!='skitt-ui' && !$(this).hasClass('ui_hidden')) {
                var order = $(this).css('order');
                array_orders.push({'id':id,'order':order});
            }
        });
        array_orders.sort((a,b) =>  a.order-b.order);
        var len = array_orders.length;
        for(var i=0;i<len;i++) {
            $("#"+array_orders[i].id).css('order',i);
        }
    }

    window.toggle_menu_control_disabled_ui = function () {
        var all_disabled = true;
        $('.menu_controls .dropdown p').each(function () {
            if(!$(this).hasClass("ui_hidden")) {
                if(!$(this).hasClass('ui_disabled')) {
                    all_disabled = false;
                }
            }
        });
        if(all_disabled) {
            $('.menu_controls #menu_icon').addClass('ui_disabled');
        } else {
            $('.menu_controls #menu_icon').removeClass('ui_disabled');
        }
    }

    window.save_paypal_subscription_id = function (id_user,intent,subscriptionID) {
        $.ajax({
            url: "ajax/save_paypal_subscription_id.php",
            type: "POST",
            data: {
                id_user: id_user,
                intent: intent,
                subscriptionID: subscriptionID
            },
            async: false,
            success: function (json) {
                var rsp = JSON.parse(json);
                if(rsp.status=="ok") {
                    location.reload();
                } else {
                    alert(rsp.msg);
                }
            }
        });
    }

    window.change_preset_editor_ui = function () {
        var id_preset = parseInt($('#presets_editor_ui option:selected').attr('id'));
        var preset_name = $('#presets_editor_ui option:selected').attr('data-name');
        var preset_public = parseInt($('#presets_editor_ui option:selected').attr('data-public'));
        var preset_delete = parseInt($('#presets_editor_ui option:selected').attr('data-delete'));
        var preset_update = parseInt($('#presets_editor_ui option:selected').attr('data-update'));
        $('#preset_new_name').val(preset_name);
        if(preset_public==1) {
            $('#preset_public').prop('checked',true);
        } else {
            $('#preset_public').prop('checked',false);
        }
        if(preset_delete==1) {
            $('#btn_delete_preset').removeClass('disabled');
            $('#btn_delete_preset').attr('onclick','delete_editor_ui_preset('+id_preset+')');
        } else {
            $('#btn_delete_preset').addClass('disabled');
            $('#btn_delete_preset').attr('onclick','');
        }
        if(preset_update==1) {
            $('#btn_update_preset').removeClass('disabled');
            $('#preset_new_name').removeClass('disabled');
        } else {
            $('#btn_update_preset').addClass('disabled');
            $('#preset_new_name').addClass('disabled');
        }
        if(id_preset==0) {
            $('#btn_apply_preset').addClass('disabled');
        } else {
            $('#btn_apply_preset').removeClass('disabled');
        }
    }

    window.get_editor_ui_presets = function (id_preset) {
        $.ajax({
            url: "ajax/get_editor_ui_presets.php",
            type: "POST",
            data: {
                id_preset: id_preset
            },
            async: false,
            success: function (json) {
                var rsp = JSON.parse(json);
                var html = rsp.html;
                $('#presets_editor_ui').html(html).promise().done(function () {
                    change_preset_editor_ui();
                });
            }
        });
    }

    window.delete_editor_ui_preset = function (id_preset) {
        var r = confirm(window.backend_labels.confirm_delete_preset);
        if (r == true) {
            $('#modal_presets button').addClass('disabled');
            $.ajax({
                url: "ajax/delete_editor_ui_preset.php",
                type: "POST",
                data: {
                    id_preset: id_preset
                },
                async: true,
                success: function (json) {
                    $('#modal_presets button').removeClass('disabled');
                    get_editor_ui_presets(0);
                }
            });
        }
    }

    window.apply_editor_ui_preset = function () {
        var r = confirm(window.backend_labels.confirm_apply_preset);
        if (r == true) {
            $('#modal_presets button').addClass('disabled');
            save_vt_ui(true,false,true);
        }
    }

    window.change_multires = function () {
        var multires = $('#multires option:selected').attr('id');
        switch (multires) {
            case 'local':
                $('#multires_cloud_url').prop('disabled',true);
                break;
            case 'cloud':
                $('#multires_cloud_url').prop('disabled',false);
                break;
        }
    }

    window.change_presentation_type = function() {
        var type = $('#presentation_type option:selected').attr('id');
        switch (type) {
            case 'manual':
                $('.automatic_presentation').addClass('d-none');
                $('.manual_presentation').removeClass('d-none');
                $('.video_presentation').addClass('d-none');
                break;
            case 'automatic':
                $('.automatic_presentation').removeClass('d-none');
                $('.manual_presentation').addClass('d-none');
                $('.video_presentation').addClass('d-none');
                break;
            case 'video':
                $('.automatic_presentation').addClass('d-none');
                $('.manual_presentation').addClass('d-none');
                $('.video_presentation').removeClass('d-none');
                break;
        }
    }

})(jQuery); // End of use strict