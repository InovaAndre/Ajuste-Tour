<?php
require_once(__DIR__."/../db/connection.php");

function is_ssl() {
    if ( isset( $_SERVER['HTTPS'] ) ) {
        if ( 'on' == strtolower( $_SERVER['HTTPS'] ) ) {
            return true;
        }
        if ( '1' == $_SERVER['HTTPS'] ) {
            return true;
        }
    } elseif ( isset( $_SERVER['SERVER_PORT'] ) && ( '443' == $_SERVER['SERVER_PORT'] ) ) {
        return true;
    }
    return false;
}

function isEnabled($func) {
    return is_callable($func) && false === stripos(ini_get('disable_functions'), $func);
}

function get_user_info($id_user) {
    global $mysqli;
    $return = array();
    $query = "SELECT u.*,p.name as plan,p.max_storage_space FROM svt_users as u LEFT JOIN svt_plans as p ON p.id=u.id_plan WHERE u.id = $id_user LIMIT 1;";
    $result = $mysqli->query($query);
    if($result) {
        if($result->num_rows==1) {
            $row=$result->fetch_array(MYSQLI_ASSOC);
            $settings = get_settings();
            if(($settings['stripe_enabled']) && !empty($row['id_subscription_stripe']) && ($row['status_subscription_stripe']==0)) {
                $row['plan_status']='invalid_payment';
            } else {
                if($row['expire_plan_date']==null) {
                    $row['plan_status']='active';
                } else {
                    if (new DateTime() > new DateTime($row['expire_plan_date'])) {
                        $row['plan_status']='expired';
                    } else{
                        $row['plan_status']='expiring';
                    }
                }
            }
            if(empty($row['avatar'])) {
                $row['avatar']='img/avatar1.png';
            } else {
                $row['avatar']='assets/'.$row['avatar'];
            }
            if($row['role']=='editor') $row['plan_status']="active";
            $return=$row;
        }
    }
    return $return;
}

function get_user_role($id_user) {
    global $mysqli;
    $return = 'user';
    $query = "SELECT role FROM svt_users WHERE id = $id_user LIMIT 1;";
    $result = $mysqli->query($query);
    if($result) {
        if($result->num_rows==1) {
            $row=$result->fetch_array(MYSQLI_ASSOC);
            $return=$row['role'];
        }
    }
    return $return;
}

function check_can_delete($id_user,$id_virtualtour) {
    global $mysqli;
    $return = false;
    switch(get_user_role($id_user)) {
        case 'administrator':
            $return = true;
            break;
        case 'customer':
            $query = "SELECT id FROM svt_virtualtours WHERE id=$id_virtualtour AND id_user=$id_user LIMIT 1;";
            $result = $mysqli->query($query);
            if($result) {
                if($result->num_rows==1) {
                    $return = true;
                }
            }
            break;
        case 'editor':
            $query = "SELECT id_virtualtour FROM svt_assign_virtualtours WHERE id_user=$id_user AND id_virtualtour=$id_virtualtour LIMIT 1;";
            $result = $mysqli->query($query);
            if($result) {
                if($result->num_rows==1) {
                    $return = true;
                }
            }
            break;
    }
    return $return;
}

function get_virtual_tour($id_virtual_tour,$id_user) {
    global $mysqli;
    $return = array();
    $query = "SELECT v.*,(SELECT image FROM svt_icons WHERE id=v.markers_id_icon_library) as markers_image_icon_library,(SELECT image FROM svt_icons WHERE id=v.pois_id_icon_library) as pois_image_icon_library FROM svt_virtualtours as v WHERE v.id = $id_virtual_tour LIMIT 1;";
    $result = $mysqli->query($query);
    if($result) {
        if($result->num_rows==1) {
            $row=$result->fetch_array(MYSQLI_ASSOC);
            $id_user_vt = $row['id_user'];
            switch(get_user_role($id_user)) {
                case 'administrator';
                    break;
                case 'customer':
                    if($id_user!=$id_user_vt) return false;
                    break;
                case 'editor':
                    $query = "SELECT * FROM svt_assign_virtualtours WHERE id_user=$id_user AND id_virtualtour=$id_virtual_tour;";
                    $result = $mysqli->query($query);
                    if($result) {
                        if($result->num_rows==0) {
                            return false;
                        }
                    }
                    break;
            }
            $return=$row;
        }
    }
    return $return;
}

function get_showcase($id_showcase,$id_user) {
    global $mysqli;
    $return = array();
    $query = "SELECT * FROM svt_showcases WHERE id = $id_showcase LIMIT 1;";
    $result = $mysqli->query($query);
    if($result) {
        if($result->num_rows==1) {
            $row=$result->fetch_array(MYSQLI_ASSOC);
            $id_user_s = $row['id_user'];
            $row['header_html'] = htmlspecialchars_decode($row['header_html']);
            $row['footer_html'] = htmlspecialchars_decode($row['footer_html']);
            $row['header_html'] = str_replace(["\r\n","\r","\n"], "<br>", $row['header_html']);
            $row['footer_html'] = str_replace(["\r\n","\r","\n"], "<br>", $row['footer_html']);
            $row['header_html'] = str_replace('"', '\"', $row['header_html']);
            $row['footer_html'] = str_replace('"', '\"', $row['footer_html']);
            switch(get_user_role($id_user)) {
                case 'administrator';
                    break;
                case 'customer':
                    if($id_user!=$id_user_s) return false;
                    break;
                case 'editor':
                    return false;
                    break;
            }
            $return=$row;
        }
    }
    return $return;
}

function get_advertisement($id_advertisement,$id_user) {
    global $mysqli;
    $return = array();
    if(get_user_role($id_user)!='administrator') {
        return false;
    }
    $query = "SELECT * FROM svt_advertisements WHERE id = $id_advertisement LIMIT 1;";
    $result = $mysqli->query($query);
    if($result) {
        if($result->num_rows==1) {
            $row=$result->fetch_array(MYSQLI_ASSOC);
            $return=$row;
        }
    }
    return $return;
}

function print_virtualtour_selector($array_list_vt,$id_virtualtour_sel) {
    $return = '<div><a href="index.php?p=edit_virtual_tour&id='.$id_virtualtour_sel.'" class="btn btn-warning btn-sm"><i class="fas fa-edit"></i></a>';
    $return .= '<select onchange="change_virtualtour();" id="virtualtour_selector" class="selectpicker" data-container="body" data-width="fit" data-live-search="true" data-style="btn-sm btn-primary" data-none-results-text="'._("No results matched").' {0}">';
    foreach ($array_list_vt as $vt) {
        $name_vt = strlen($vt['name']) > 30 ? substr($vt['name'],0,30)."..." : $vt['name'];
        $return .= "<option data-subtext=\"".$vt['author']."\" ".(($id_virtualtour_sel==$vt['id']) ? 'selected' : '')." id='".$vt['id']."'>".$name_vt."</option>";
    }
    $return .= '</select></div>';
    return $return;
}

function get_virtual_tours($id_user) {
    global $mysqli;
    $return = array();
    switch(get_user_role($id_user)) {
        case 'administrator':
            $where = "";
            break;
        case 'customer':
            $where = " WHERE v.id_user=$id_user ";
            break;
        case 'editor':
            $where = " WHERE v.id IN () ";
            $query = "SELECT GROUP_CONCAT(id_virtualtour) as ids FROM svt_assign_virtualtours WHERE id_user=$id_user;";
            $result = $mysqli->query($query);
            if($result) {
                if($result->num_rows==1) {
                    $row=$result->fetch_array(MYSQLI_ASSOC);
                    $ids = $row['ids'];
                    $where = " WHERE v.id IN ($ids) ";
                }
            }
            break;
    }
    $query = "SELECT id,UPPER(name) as name,author FROM svt_virtualtours as v $where ORDER BY date_created DESC;";
    $result = $mysqli->query($query);
    if($result) {
        if($result->num_rows>0) {
            while($row=$result->fetch_array(MYSQLI_ASSOC)) {
                $return[]=$row;
            }
        }
    }
    return $return;
}

function get_virtual_tours_options_css() {
    global $mysqli;
    $return = "";
    $query = "SELECT code,name FROM svt_virtualtours ORDER BY name ASC;";
    $result = $mysqli->query($query);
    if($result) {
        if($result->num_rows>0) {
            while($row=$result->fetch_array(MYSQLI_ASSOC)) {
                $code = $row['code'];
                $name = $row['name'];
                $return .= "<option id='css_custom_$code'>$name</option>";
            }
        }
    }
    return $return;
}

function get_virtual_tours_options_js() {
    global $mysqli;
    $return = "";
    $query = "SELECT code,name FROM svt_virtualtours ORDER BY name ASC;";
    $result = $mysqli->query($query);
    if($result) {
        if($result->num_rows>0) {
            while($row=$result->fetch_array(MYSQLI_ASSOC)) {
                $code = $row['code'];
                $name = $row['name'];
                $return .= "<option id='js_custom_$code'>$name</option>";
            }
        }
    }
    return $return;
}

function get_virtual_tours_editors_css() {
    global $mysqli;
    $return = "";
    $query = "SELECT code FROM svt_virtualtours;";
    $result = $mysqli->query($query);
    if($result) {
        if($result->num_rows>0) {
            while($row=$result->fetch_array(MYSQLI_ASSOC)) {
                $code = $row['code'];
                $return .= '<div style="display:none;position: relative;width: 100%;height: 400px;" class="editors_css" id="custom_'.$code.'">'.get_editor_css_content('custom_'.$code).'</div>';
            }
        }
    }
    return $return;
}

function get_virtual_tours_editors_js() {
    global $mysqli;
    $return = "";
    $query = "SELECT code FROM svt_virtualtours;";
    $result = $mysqli->query($query);
    if($result) {
        if($result->num_rows>0) {
            while($row=$result->fetch_array(MYSQLI_ASSOC)) {
                $code = $row['code'];
                $return .= '<div style="display:none;position: relative;width: 100%;height: 400px;" class="editors_js" id="custom_js_'.$code.'">'.get_editor_js_content('custom_'.$code).'</div>';
            }
        }
    }
    return $return;
}

function get_editor_css_content($css) {
    if($css=='custom_b') {
        $url_css = __DIR__.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'backend'.DIRECTORY_SEPARATOR.'css'.DIRECTORY_SEPARATOR.'custom_b.css';
        if(file_exists($url_css)) {
            return @file_get_contents($url_css);
        } else {
            return '';
        }
    } else {
        $url_css = __DIR__.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'viewer'.DIRECTORY_SEPARATOR.'css'.DIRECTORY_SEPARATOR.$css.'.css';
        if(file_exists($url_css)) {
            return @file_get_contents($url_css);
        } else {
            return '';
        }
    }
}

function get_editor_css_content_s($css) {
    $url_css = __DIR__.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'showcase'.DIRECTORY_SEPARATOR.'css'.DIRECTORY_SEPARATOR.$css.'.css';
    if(file_exists($url_css)) {
        return @file_get_contents($url_css);
    } else {
        return '';
    }
}

function get_editor_js_content($js) {
    $url_js = __DIR__.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'viewer'.DIRECTORY_SEPARATOR.'js'.DIRECTORY_SEPARATOR.$js.'.js';
    if(file_exists($url_js)) {
        return @file_get_contents($url_js);
    } else {
        return '';
    }
}

function get_fisrt_room($id_virtualtour) {
    global $mysqli;
    $return = array();
    $query = "SELECT id,name,panorama_image FROM svt_rooms WHERE id_virtualtour=$id_virtualtour ORDER BY priority LIMIT 1;";
    $result = $mysqli->query($query);
    if($result) {
        if ($result->num_rows == 1) {
            $row = $result->fetch_array(MYSQLI_ASSOC);
            $return = $row;
        }
    }
    return $return;
}

function get_fisrt_floorplan($id_virtualtour) {
    global $mysqli;
    $return = array();
    $query = "SELECT id,map,width_d FROM svt_maps WHERE id_virtualtour=$id_virtualtour AND map_type='floorplan' ORDER BY priority LIMIT 1;";
    $result = $mysqli->query($query);
    if($result) {
        if ($result->num_rows == 1) {
            $row = $result->fetch_array(MYSQLI_ASSOC);
            $return = $row;
        }
    }
    return $return;
}

function get_first_room_panorama($id_virtualtour) {
    global $mysqli;
    $panorama_image = '';
    $query = "SELECT panorama_image FROM svt_rooms WHERE id_virtualtour=$id_virtualtour LIMIT 1";
    $result = $mysqli->query($query);
    if($result) {
        if ($result->num_rows == 1) {
            $row = $result->fetch_array(MYSQLI_ASSOC);
            $panorama_image = $row['panorama_image'];
        }
    }
    return $panorama_image;
}

function get_room($id_room,$id_user) {
    global $mysqli;
    $return = array();
    $query = "SELECT r.*,v.id_user,m.map,m.point_size,m.north_degree,v.id as id_virtualtour,v.enable_multires FROM svt_rooms as r
            JOIN svt_virtualtours as v ON r.id_virtualtour=v.id
            LEFT JOIN svt_maps as m ON m.id=r.id_map
            WHERE r.id = $id_room LIMIT 1;";
    $result = $mysqli->query($query);
    if($result) {
        if($result->num_rows==1) {
            $row=$result->fetch_array(MYSQLI_ASSOC);
            $id_user_vt = $row['id_user'];
            $id_virtual_tour = $row['id_virtualtour'];
            switch(get_user_role($id_user)) {
                case 'administrator';
                    break;
                case 'customer':
                    if($id_user!=$id_user_vt) return false;
                    break;
                case 'editor':
                    $query = "SELECT * FROM svt_assign_virtualtours WHERE id_user=$id_user AND id_virtualtour=$id_virtual_tour;";
                    $result = $mysqli->query($query);
                    if($result) {
                        if($result->num_rows==0) {
                            return false;
                        }
                    }
                    break;
            }
            if($row['enable_multires']) {
                $room_pano = str_replace('.jpg','',$row['panorama_image']);
                $multires_config_file = __DIR__.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'viewer'.DIRECTORY_SEPARATOR.'panoramas'.DIRECTORY_SEPARATOR.'multires'.DIRECTORY_SEPARATOR.$room_pano.DIRECTORY_SEPARATOR.'config.json';
                if(file_exists($multires_config_file)) {
                    $multires_tmp = file_get_contents($multires_config_file);
                    $multires_array = json_decode($multires_tmp,true);
                    $multires_config = $multires_array['multiRes'];
                    $multires_config['basePath'] = '../viewer/panoramas/multires/'.$room_pano;
                    $row['multires']=1;
                    $row['multires_config']=json_encode($multires_config);
                    $row['multires_dir']='../viewer/panoramas/multires/'.$room_pano;
                } else {
                    $row['multires']=0;
                    $row['multires_config']='';
                    $row['multires_dir']='';
                }
            } else {
                $row['multires']=0;
                $row['multires_config']='';
                $row['multires_dir']='';
            }
            $return=$row;
        }
    }
    return $return;
}

function get_product($id_product,$id_user) {
    global $mysqli;
    $return = array();
    $query = "SELECT p.*,v.id_user FROM svt_products as p JOIN svt_virtualtours as v ON v.id=p.id_virtualtour WHERE p.id = $id_product LIMIT 1;";
    $result = $mysqli->query($query);
    if($result) {
        if($result->num_rows==1) {
            $row=$result->fetch_array(MYSQLI_ASSOC);
            $id_user_vt = $row['id_user'];
            $id_virtual_tour = $row['id_virtualtour'];
            switch(get_user_role($id_user)) {
                case 'administrator';
                    break;
                case 'customer':
                    if($id_user!=$id_user_vt) return false;
                    break;
                case 'editor':
                    $query = "SELECT * FROM svt_assign_virtualtours WHERE id_user=$id_user AND id_virtualtour=$id_virtual_tour;";
                    $result = $mysqli->query($query);
                    if($result) {
                        if($result->num_rows==0) {
                            return false;
                        }
                    }
                    break;
            }
            $return=$row;
        }
    }
    return $return;
}

function check_map_type($id_virtualtour) {
    global $mysqli;
    $query = "SELECT id FROM svt_maps WHERE map_type='map' AND id_virtualtour=$id_virtualtour;";
    $result = $mysqli->query($query);
    if($result) {
        if($result->num_rows==0) {
            return false;
        } else {
            return true;
        }
    }
}

function get_map($id_map,$id_user) {
    global $mysqli;
    $return = array();
    $query = "SELECT m.*,v.id_user,v.id as id_virtualtour FROM svt_maps as m
            JOIN svt_virtualtours as v ON m.id_virtualtour=v.id
            WHERE m.id = $id_map LIMIT 1;";
    $result = $mysqli->query($query);
    if($result) {
        if($result->num_rows==1) {
            $row=$result->fetch_array(MYSQLI_ASSOC);
            $id_user_vt = $row['id_user'];
            $id_virtual_tour = $row['id_virtualtour'];
            switch(get_user_role($id_user)) {
                case 'administrator';
                    break;
                case 'customer':
                    if($id_user!=$id_user_vt) return false;
                    break;
                case 'editor':
                    $query = "SELECT * FROM svt_assign_virtualtours WHERE id_user=$id_user AND id_virtualtour=$id_virtual_tour;";
                    $result = $mysqli->query($query);
                    if($result) {
                        if($result->num_rows==0) {
                            return false;
                        }
                    }
                    break;
            }
            $return=$row;
        }
    }
    return $return;
}

function get_virtual_tours_options($id_vt_sel) {
    global $mysqli;
    $return = "";
    $query = "SELECT id,name,author FROM svt_virtualtours ORDER BY name ASC;";
    $result = $mysqli->query($query);
    if($result) {
        if($result->num_rows>0) {
            while($row=$result->fetch_array(MYSQLI_ASSOC)) {
                $id = $row['id'];
                $name = $row['name'];
                $author = $row['author'];
                if($id==$id_vt_sel) {
                    $return .= "<option selected id='$id'>$name ($author)</option>";
                } else {
                    $return .= "<option id='$id'>$name ($author)</option>";
                }
            }
        }
    }
    return $return;
}

function get_rooms($id_virtualtour) {
    global $mysqli;
    $array_rooms = [];
    $query = "SELECT id,name,panorama_image FROM svt_rooms WHERE id_virtualtour=$id_virtualtour;";
    $result = $mysqli->query($query);
    if($result) {
        if($result->num_rows>0) {
            while($row=$result->fetch_array(MYSQLI_ASSOC)) {
                array_push($array_rooms,$row);
            }
        }
    }
    return $array_rooms;
}

function get_rooms_3d_view($id_virtualtour) {
    global $mysqli;
    $array_rooms = [];
    $query = "SELECT id,name,panorama_image FROM svt_rooms WHERE id_virtualtour=$id_virtualtour;";
    $result = $mysqli->query($query);
    if($result) {
        if($result->num_rows>0) {
            while($row=$result->fetch_array(MYSQLI_ASSOC)) {
                $pano_lowres = __DIR__.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'viewer'.DIRECTORY_SEPARATOR.'panoramas'.DIRECTORY_SEPARATOR.'lowres'.DIRECTORY_SEPARATOR.$row['panorama_image'];
                if(file_exists($pano_lowres)) {
                    $row['panorama_3d']='lowres/'.$row['panorama_image'];
                } else {
                    $row['panorama_3d']=$row['panorama_image'];
                }
                array_push($array_rooms,$row);
            }
        }
    }
    return $array_rooms;
}

function get_rooms_option($id_virtualtour) {
    global $mysqli;
    $options = "";
    $query = "SELECT id,name FROM svt_rooms WHERE id_virtualtour=$id_virtualtour;";
    $result = $mysqli->query($query);
    if($result) {
        while($row=$result->fetch_array(MYSQLI_ASSOC)) {
            $id = $row['id'];
            $name = $row['name'];
            $options .= "<option id='$id'>$name</option>";
        }
    }
    return $options;
}

function get_categories() {
    global $mysqli;
    $return = array();
    $query = "SELECT * FROM svt_categories;";
    $result = $mysqli->query($query);
    if($result) {
        if($result->num_rows>0) {
            while($row=$result->fetch_array(MYSQLI_ASSOC)) {
                $return[] = $row;
            }
        }
    }
    return $return;
}

function get_categories_option($id_category) {
    global $mysqli;
    $options = "";
    $query = "SELECT id,name FROM svt_categories;";
    $result = $mysqli->query($query);
    if($result) {
        while($row=$result->fetch_array(MYSQLI_ASSOC)) {
            $id = $row['id'];
            $name = $row['name'];
            if($id_category==$id) {
                $options .= "<option selected id='$id'>$name</option>";
            } else {
                $options .= "<option id='$id'>$name</option>";
            }
        }
    }
    return $options;
}

function get_plans_options($id_plan_sel) {
    global $mysqli;
    if($id_plan_sel==0) {
        $options = "<option selected id='0'>None</option>";
    } else {
        $options = "<option id='0'>None</option>";
    }
    $query = "SELECT * FROM svt_plans;";
    $result = $mysqli->query($query);
    if($result) {
        if($result->num_rows>0) {
            while($row=$result->fetch_array(MYSQLI_ASSOC)) {
                $id = $row['id'];
                $name = $row['name'];
                if($id_plan_sel==$id) {
                    $options .= "<option selected id='$id'>$name</option>";
                } else {
                    $options .= "<option id='$id'>$name</option>";
                }
            }
        }
    }
    return $options;
}

function get_plans($id_user) {
    global $mysqli;
    $return = array();
    $query = "SELECT p.* FROM svt_plans as p WHERE p.visible=1 ORDER BY IF(p.frequency='recurring',((p.price*12)/p.interval_count),p.price) ASC;";
    $result = $mysqli->query($query);
    if($result) {
        if($result->num_rows>0) {
            while($row=$result->fetch_array(MYSQLI_ASSOC)) {
                $return[] = $row;
            }
        }
    }
    return $return;
}

function check_plan($object,$id_user) {
    global $mysqli;
    $role = get_user_role($id_user);
    switch($object) {
        case 'virtual_tour':
            $count_virtual_tours = 0;
            $plan_virtual_tours = -1;
            $query = "SELECT COUNT(*) as num FROM svt_virtualtours WHERE id_user = $id_user LIMIT 1;";
            $result = $mysqli->query($query);
            if($result) {
                if($result->num_rows==1) {
                    $row=$result->fetch_array(MYSQLI_ASSOC);
                    $count_virtual_tours = $row['num'];
                }
            }
            $query = "SELECT n_virtual_tours FROM svt_plans as p LEFT JOIN svt_users AS u ON u.id_plan=p.id WHERE u.id = $id_user LIMIT 1;";
            $result = $mysqli->query($query);
            if($result) {
                if($result->num_rows==1) {
                    $row=$result->fetch_array(MYSQLI_ASSOC);
                    $plan_virtual_tours = $row['n_virtual_tours'];
                }
            }
            $can_create = 0;
            if($plan_virtual_tours<0) {
                $can_create = 1;
            } else {
                if($count_virtual_tours>=$plan_virtual_tours) {
                    $can_create = 0;
                } else {
                    $can_create = 1;
                }
            }
            return $can_create;
            break;
        case 'room':
            $count_rooms = 0;
            $plan_rooms = -1;
            $query = "SELECT COUNT(*) as num FROM svt_rooms as r
                        JOIN svt_virtualtours as v ON v.id = r.id_virtualtour
                        WHERE id_user = $id_user LIMIT 1;";
            $result = $mysqli->query($query);
            if($result) {
                if($result->num_rows==1) {
                    $row=$result->fetch_array(MYSQLI_ASSOC);
                    $count_rooms = $row['num'];
                }
            }
            $query = "SELECT n_rooms FROM svt_plans as p LEFT JOIN svt_users AS u ON u.id_plan=p.id WHERE u.id = $id_user LIMIT 1;";
            $result = $mysqli->query($query);
            if($result) {
                if($result->num_rows==1) {
                    $row=$result->fetch_array(MYSQLI_ASSOC);
                    $plan_rooms = $row['n_rooms'];
                }
            }
            $can_create = 0;
            if($plan_rooms<0) {
                $can_create = 1;
            } else {
                if($count_rooms>=$plan_rooms) {
                    $can_create = 0;
                } else {
                    $can_create = 1;
                }
            }
            return $can_create;
            break;
        case 'marker':
            $count_markers = 0;
            $plan_markers = -1;
            $query = "SELECT COUNT(*) as num FROM svt_markers as m
                        JOIN svt_rooms as r ON m.id_room = r.id
                        JOIN svt_virtualtours as v ON v.id = r.id_virtualtour
                        WHERE id_user = $id_user LIMIT 1;";
            $result = $mysqli->query($query);
            if($result) {
                if($result->num_rows==1) {
                    $row=$result->fetch_array(MYSQLI_ASSOC);
                    $count_markers = $row['num'];
                }
            }
            $query = "SELECT n_markers FROM svt_plans as p LEFT JOIN svt_users AS u ON u.id_plan=p.id WHERE u.id = $id_user LIMIT 1;";
            $result = $mysqli->query($query);
            if($result) {
                if($result->num_rows==1) {
                    $row=$result->fetch_array(MYSQLI_ASSOC);
                    $plan_markers = $row['n_markers'];
                }
            }
            $can_create = 0;
            if($plan_markers<0) {
                $can_create = 1;
            } else {
                if($count_markers>=$plan_markers) {
                    $can_create = 0;
                } else {
                    $can_create = 1;
                }
            }
            return $can_create;
            break;
        case 'poi':
            $count_pois = 0;
            $plan_pois = -1;
            $query = "SELECT COUNT(*) as num FROM svt_pois as m
                        JOIN svt_rooms as r ON m.id_room = r.id
                        JOIN svt_virtualtours as v ON v.id = r.id_virtualtour
                        WHERE id_user = $id_user LIMIT 1;";
            $result = $mysqli->query($query);
            if($result) {
                if($result->num_rows==1) {
                    $row=$result->fetch_array(MYSQLI_ASSOC);
                    $count_pois = $row['num'];
                }
            }
            $query = "SELECT n_pois FROM svt_plans as p LEFT JOIN svt_users AS u ON u.id_plan=p.id WHERE u.id = $id_user LIMIT 1;";
            $result = $mysqli->query($query);
            if($result) {
                if($result->num_rows==1) {
                    $row=$result->fetch_array(MYSQLI_ASSOC);
                    $plan_pois = $row['n_pois'];
                }
            }
            $can_create = 0;
            if($plan_pois<0) {
                $can_create = 1;
            } else {
                if($count_pois>=$plan_pois) {
                    $can_create = 0;
                } else {
                    $can_create = 1;
                }
            }
            return $can_create;
            break;
        default:
            return 0;
            break;
    }
}

function get_plan_permission($id_user) {
    global $mysqli;
    $return = [];
    $query = "SELECT max_file_size_upload,enable_statistics,create_landing,create_showcase,create_gallery,create_presentation,enable_live_session,enable_meeting,enable_chat,enable_voice_commands,enable_share,enable_device_orientation,enable_webvr,enable_logo,enable_nadir_logo,enable_song,enable_forms,enable_annotations,enable_panorama_video,enable_rooms_multiple,enable_rooms_protect,enable_info_box,enable_maps,enable_icons_library,enable_password_tour,enable_expiring_dates,enable_auto_rotate,enable_flyin,enable_multires,enable_export_vt,enable_shop,enable_dollhouse,enable_context_info FROM svt_plans as p LEFT JOIN svt_users AS u ON u.id_plan=p.id WHERE u.id = $id_user LIMIT 1;";
    $result = $mysqli->query($query);
    if($result) {
        if ($result->num_rows == 1) {
            $row=$result->fetch_array(MYSQLI_ASSOC);
            $return=$row;
        }
    }
    $return['create_landing'] = (empty($return['create_landing'])) ? 0 : $return['create_landing'];
    $return['create_showcase'] = (empty($return['create_showcase'])) ? 0 : $return['create_showcase'];
    $return['enable_live_session'] = (empty($return['enable_live_session'])) ? 0 : $return['enable_live_session'];
    $return['enable_meeting'] = (empty($return['enable_meeting'])) ? 0 : $return['enable_meeting'];
    $return['create_gallery'] = (empty($return['create_gallery'])) ? 0 : $return['create_gallery'];
    $return['create_presentation'] = (empty($return['create_presentation'])) ? 0 : $return['create_presentation'];
    $return['enable_chat'] = (empty($return['enable_chat'])) ? 0 : $return['enable_chat'];
    $return['enable_voice_commands'] = (empty($return['enable_voice_commands'])) ? 0 : $return['enable_voice_commands'];
    $return['enable_share'] = (empty($return['enable_share'])) ? 0 : $return['enable_share'];
    $return['enable_device_orientation'] = (empty($return['enable_device_orientation'])) ? 0 : $return['enable_device_orientation'];
    $return['enable_webvr'] = (empty($return['enable_webvr'])) ? 0 : $return['enable_webvr'];
    $return['enable_logo'] = (empty($return['enable_logo'])) ? 0 : $return['enable_logo'];
    $return['enable_nadir_logo'] = (empty($return['enable_nadir_logo'])) ? 0 : $return['enable_nadir_logo'];
    $return['enable_song'] = (empty($return['enable_song'])) ? 0 : $return['enable_song'];
    $return['enable_forms'] = (empty($return['enable_forms'])) ? 0 : $return['enable_forms'];
    $return['enable_annotations'] = (empty($return['enable_annotations'])) ? 0 : $return['enable_annotations'];
    $return['enable_panorama_video'] = (empty($return['enable_panorama_video'])) ? 0 : $return['enable_panorama_video'];
    $return['enable_rooms_multiple'] = (empty($return['enable_rooms_multiple'])) ? 0 : $return['enable_rooms_multiple'];
    $return['enable_rooms_protect'] = (empty($return['enable_rooms_protect'])) ? 0 : $return['enable_rooms_protect'];
    $return['enable_info_box'] = (empty($return['enable_info_box'])) ? 0 : $return['enable_info_box'];
    $return['enable_maps'] = (empty($return['enable_maps'])) ? 0 : $return['enable_maps'];
    $return['enable_icons_library'] = (empty($return['enable_icons_library'])) ? 0 : $return['enable_icons_library'];
    $return['enable_password_tour'] = (empty($return['enable_password_tour'])) ? 0 : $return['enable_password_tour'];
    $return['enable_expiring_dates'] = (empty($return['enable_expiring_dates'])) ? 0 : $return['enable_expiring_dates'];
    $return['enable_statistics'] = (empty($return['enable_statistics'])) ? 0 : $return['enable_statistics'];
    $return['enable_auto_rotate'] = (empty($return['enable_auto_rotate'])) ? 0 : $return['enable_auto_rotate'];
    $return['enable_flyin'] = (empty($return['enable_flyin'])) ? 0 : $return['enable_flyin'];
    $return['enable_multires'] = (empty($return['enable_multires'])) ? 0 : $return['enable_multires'];
    $return['enable_export_vt'] = (empty($return['enable_export_vt'])) ? 0 : $return['enable_export_vt'];
    $return['enable_shop'] = (empty($return['enable_shop'])) ? 0 : $return['enable_shop'];
    $return['enable_dollhouse'] = (empty($return['enable_dollhouse'])) ? 0 : $return['enable_dollhouse'];
    $return['enable_context_info'] = (empty($return['enable_context_info'])) ? 0 : $return['enable_context_info'];
    return $return;
}

function check_plan_rooms_count($id_user) {
    global $mysqli;
    $rooms_count_create = 0;
    $count_rooms = 0;
    $plan_rooms = -1;
    $query = "SELECT COUNT(*) as num FROM svt_rooms as r
                        JOIN svt_virtualtours as v ON v.id = r.id_virtualtour
                        WHERE id_user = $id_user LIMIT 1;";
    $result = $mysqli->query($query);
    if($result) {
        if($result->num_rows==1) {
            $row=$result->fetch_array(MYSQLI_ASSOC);
            $count_rooms = $row['num'];
        }
    }
    $query = "SELECT n_rooms FROM svt_plans as p LEFT JOIN svt_users AS u ON u.id_plan=p.id WHERE u.id = $id_user LIMIT 1;";
    $result = $mysqli->query($query);
    if($result) {
        if($result->num_rows==1) {
            $row=$result->fetch_array(MYSQLI_ASSOC);
            $plan_rooms = $row['n_rooms'];
        }
    }
    if($plan_rooms<0) {
        $rooms_count_create = -1;
    } else {
        $rooms_count_create = $plan_rooms-$count_rooms;
    }
    return $rooms_count_create;
}

function get_voice_commands() {
    global $mysqli;
    $return = array();
    $query = "SELECT * FROM svt_voice_commands LIMIT 1;";
    $result = $mysqli->query($query);
    if($result) {
        if($result->num_rows==1) {
            $row=$result->fetch_array(MYSQLI_ASSOC);
            $return=$row;
        }
    }
    return $return;
}

function get_settings() {
    global $mysqli;
    $return = array();
    $query = "SELECT * FROM svt_settings LIMIT 1;";
    $result = $mysqli->query($query);
    if($result) {
        if($result->num_rows==1) {
            $row=$result->fetch_array(MYSQLI_ASSOC);
            if(empty($row['languages_enabled'])) {
                $row['languages_enabled']=array();
                $row['languages_enabled']['en_US']=1;
            } else {
                $row['languages_enabled']=json_decode($row['languages_enabled'],true);
            }
            $row['languages_count']=0;
            foreach ($row['languages_enabled'] as $lang) {
                if($lang==1) {
                    $row['languages_count']++;
                }
            }
            if($row['languages_count']==0) {
                $row['languages_enabled']=array();
                $row['languages_enabled']['en_US']=1;
                $row['languages_count']=1;
            }
            if(empty($row['contact_email'])) {
                $query_ce = "SELECT email FROM svt_users WHERE role='administrator' LIMIT 1;";
                $result_ce = $mysqli->query($query_ce);
                if($result_ce) {
                    if ($result_ce->num_rows == 1) {
                        $row_ce = $result_ce->fetch_array(MYSQLI_ASSOC);
                        $row['contact_email'] = $row_ce['email'];
                    }
                }
            }
            $return=$row;
        }
    }
    return $return;
}

function check_language_enabled($lang,$languages_enabled) {
    if(empty($languages_enabled) && $lang=='en_US') {
        return true;
    } else if(empty($languages_enabled[$lang])) {
        return false;
    } else if($languages_enabled[$lang]==1) {
        return true;
    } else {
        return false;
    }
}

function get_library_icons($id_virtualtour,$p) {
    global $mysqli;
    $return = "";
    $query = "SELECT * FROM svt_icons WHERE id_virtualtour=$id_virtualtour OR id_virtualtour IS NULL ORDER BY id DESC;";
    $result = $mysqli->query($query);
    if($result) {
        if($result->num_rows>0) {
            while($row=$result->fetch_array(MYSQLI_ASSOC)) {
                $id = $row['id'];
                $image = $row['image'];
                $tmp = explode('.',$image);
                $ext = strtolower(end($tmp));
                if($ext=='json') {
                    $return .= "<div onclick='select_icon_library(\"$p\",$id,\"$image\",\"\");' class=\"lottie_icon_list\" data-id=\"$id\" data-image=\"$image\" id=\"lottie_icon_$id\" style=\"display:inline-block;height:50px;width:50px;vertical-align:middle;cursor:pointer;\"></div>";
                } else {
                    if(!empty($image)) {
                        $base64 = convert_image_to_base64(dirname(__FILE__).'/../viewer/icons/'.$image);;
                    } else {
                        $base64 = '';
                    }
                    $return .= "<img onclick='select_icon_library(\"$p\",$id,\"$image\",\"$base64\");' style='display: inline-block;width:50px;padding:3px;cursor:pointer;' src='../viewer/icons/$image' />";
                }
            }
        } else {
            $return = _("No icons in this library.");
        }
    }
    return $return;
}

function get_library_icons_v($id_virtualtour,$p) {
    global $mysqli;
    $return = "";
    $query = "SELECT * FROM svt_icons WHERE id_virtualtour=$id_virtualtour OR id_virtualtour IS NULL ORDER BY id DESC;";
    $result = $mysqli->query($query);
    if($result) {
        if($result->num_rows>0) {
            while($row=$result->fetch_array(MYSQLI_ASSOC)) {
                $id = $row['id'];
                $image = $row['image'];
                $tmp = explode('.',$image);
                $ext = strtolower(end($tmp));
                if($ext=='json') {
                    $return .= "<div onclick='select_icon_library_v(\"$p\",$id,\"$image\",\"\");' class=\"lottie_icon_".$p."_list\" data-id=\"$id\" data-image=\"$image\" id=\"lottie_icon_".$p."_$id\" style=\"display:inline-block;height:50px;width:50px;vertical-align:middle;cursor:pointer;\"></div>";
                } else {
                    $return .= "<img onclick='select_icon_library_v(\"$p\",$id,\"$image\");' style='display: inline-block;width:50px;padding:3px;cursor:pointer;' src='../viewer/icons/$image' />";
                }
            }
        } else {
            $return = _("No icons in this library.");
        }
    }
    return $return;
}

function get_option_exist_logo($id_user) {
    global $mysqli;
    $return = "";
    $query = "SELECT DISTINCT logo FROM svt_virtualtours WHERE id_user=$id_user AND logo!='';";
    $result = $mysqli->query($query);
    if($result) {
        if($result->num_rows>0) {
            while($row=$result->fetch_array(MYSQLI_ASSOC)) {
                $logo = $row['logo'];
                $return .= "<option data-left='../viewer/content/$logo' id='$logo'>$logo</option>";
            }
        }
    }
    return $return;
}

function get_option_exist_nadir_logo($id_user) {
    global $mysqli;
    $return = "";
    $query = "SELECT DISTINCT nadir_logo FROM svt_virtualtours WHERE id_user=$id_user AND nadir_logo!='';";
    $result = $mysqli->query($query);
    if($result) {
        if($result->num_rows>0) {
            while($row=$result->fetch_array(MYSQLI_ASSOC)) {
                $nadir_logo = $row['nadir_logo'];
                $return .= "<option data-left='../viewer/content/$nadir_logo' id='$nadir_logo'>$nadir_logo</option>";
            }
        }
    }
    return $return;
}

function get_option_exist_background_logo($id_user) {
    global $mysqli;
    $return = "";
    $query = "SELECT DISTINCT background_image FROM svt_virtualtours WHERE id_user=$id_user AND background_image!='';";
    $result = $mysqli->query($query);
    if($result) {
        if($result->num_rows>0) {
            while($row=$result->fetch_array(MYSQLI_ASSOC)) {
                $background_image = $row['background_image'];
                $return .= "<option data-left='../viewer/content/$background_image' id='$background_image'>$background_image</option>";
            }
        }
    }
    return $return;
}

function get_option_exist_background_video($id_user) {
    global $mysqli;
    $return = "";
    $query = "SELECT DISTINCT background_video FROM svt_virtualtours WHERE id_user=$id_user AND background_video!='';";
    $result = $mysqli->query($query);
    if($result) {
        if($result->num_rows>0) {
            while($row=$result->fetch_array(MYSQLI_ASSOC)) {
                $background_video = $row['background_video'];
                $return .= "<option data-left='../viewer/content/$background_video' id='$background_video'>$background_video</option>";
            }
        }
    }
    return $return;
}

function get_option_exist_song($id_user,$id_virtualtour) {
    global $mysqli;
    $return = "";
    $array_audio = array();
    $query = "SELECT file as song FROM svt_music_library WHERE id_virtualtour=$id_virtualtour OR id_virtualtour IS NULL ORDER BY id DESC;";
    $result = $mysqli->query($query);
    if($result) {
        if($result->num_rows>0) {
            while($row=$result->fetch_array(MYSQLI_ASSOC)) {
                $song = $row['song'];
                if(!in_array($song,$array_audio)) {
                    array_push($array_audio,$song);
                }
            }
        }
    }
    if($id_user==null) {
        $query = "SELECT DISTINCT song FROM svt_virtualtours WHERE id=$id_virtualtour AND song!='';";
    } else {
        $query = "SELECT DISTINCT song FROM svt_virtualtours WHERE id_user=$id_user AND song!='';";
    }
    $result = $mysqli->query($query);
    if($result) {
        if($result->num_rows>0) {
            while($row=$result->fetch_array(MYSQLI_ASSOC)) {
                $song = $row['song'];
                if(!in_array($song,$array_audio)) {
                    array_push($array_audio,$song);
                }
            }
        }
    }
    $query = "SELECT song FROM svt_rooms WHERE id_virtualtour=$id_virtualtour AND song!='';";
    $result = $mysqli->query($query);
    if($result) {
        if($result->num_rows>0) {
            while($row=$result->fetch_array(MYSQLI_ASSOC)) {
                $song = $row['song'];
                if(!in_array($song,$array_audio)) {
                    array_push($array_audio,$song);
                }
            }
        }
    }
    $query = "SELECT content as song FROM svt_pois WHERE type IN ('audio') AND content LIKE 'content/%' AND id_room IN (SELECT id FROM svt_rooms WHERE id_virtualtour=$id_virtualtour);";
    $result = $mysqli->query($query);
    if($result) {
        if($result->num_rows>0) {
            while($row=$result->fetch_array(MYSQLI_ASSOC)) {
                $song = str_replace('content/','',$row['song']);
                if(!in_array($song,$array_audio)) {
                    array_push($array_audio,$song);
                }
            }
        }
    }
    foreach($array_audio as $song) {
        $return .= "<option id='$song'>$song</option>";
    }
    return $return;
}

function get_option_exist_introd($id_user) {
    global $mysqli;
    $return = "";
    $query = "SELECT DISTINCT intro_desktop FROM svt_virtualtours WHERE id_user=$id_user AND intro_desktop!='';";
    $result = $mysqli->query($query);
    if($result) {
        if($result->num_rows>0) {
            while($row=$result->fetch_array(MYSQLI_ASSOC)) {
                $intro_desktop = $row['intro_desktop'];
                $return .= "<option data-left='../viewer/content/$intro_desktop' id='$intro_desktop'>$intro_desktop</option>";
            }
        }
    }
    return $return;
}

function get_option_exist_introm($id_user) {
    global $mysqli;
    $return = "";
    $query = "SELECT DISTINCT intro_mobile FROM svt_virtualtours WHERE id_user=$id_user AND intro_mobile!='';";
    $result = $mysqli->query($query);
    if($result) {
        if($result->num_rows>0) {
            while($row=$result->fetch_array(MYSQLI_ASSOC)) {
                $intro_mobile = $row['intro_mobile'];
                $return .= "<option data-left='../viewer/content/$intro_mobile' id='$intro_mobile'>$intro_mobile</option>";
            }
        }
    }
    return $return;
}

function get_option_products($id_virtualtour) {
    global $mysqli;
    $return = "";
    $query = "SELECT p.id,p.name,MIN(spi.image) as image FROM svt_products as p
                LEFT JOIN svt_product_images spi on p.id = spi.id_product
                WHERE p.id_virtualtour=$id_virtualtour
                GROUP BY p.id;";
    $result = $mysqli->query($query);
    if($result) {
        if($result->num_rows>0) {
            while($row=$result->fetch_array(MYSQLI_ASSOC)) {
                $id_product = $row['id'];
                $name = $row['name'];
                $image = $row['image'];
                $return .= "<option id='$id_product' value='$id_product'>$name</option>";
            }
        }
    }
    return $return;
}

function generateRandomString($length = 10) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, $charactersLength - 1)];
    }
    return $randomString;
}

function update_plans_expires_date($id_user) {
    global $mysqli;
    if(!empty($id_user)) {
        $where = " WHERE u.id=$id_user";
    } else {
        $where = "";
    }
    $query = "SELECT u.id,u.registration_date,u.expire_plan_date_manual,p.days,u.role FROM svt_users as u
                JOIN svt_plans as p ON p.id=u.id_plan $where";
    $result = $mysqli->query($query);
    if($result) {
        if($result->num_rows>0) {
            while($row=$result->fetch_array(MYSQLI_ASSOC)) {
                $id_user = $row['id'];
                $reg_date = $row['registration_date'];
                $expire_plan_date_manual = $row['expire_plan_date_manual'];
                $days = $row['days'];
                $role = $row['role'];
                switch($role) {
                    case 'administrator':
                    case 'editor':
                        $mysqli->query("UPDATE svt_users SET expire_plan_date=NULL WHERE id=$id_user;");
                        break;
                    case 'customer':
                        if(!empty($expire_plan_date_manual)) {
                            $mysqli->query("UPDATE svt_users SET expire_plan_date=expire_plan_date_manual WHERE id=$id_user;");
                        } else {
                            if(empty($row['id_subscription_stripe'])) {
                                if($days<0) {
                                    $mysqli->query("UPDATE svt_users SET expire_plan_date=NULL WHERE id=$id_user;");
                                } else {
                                    $exp_date = date('Y-m-d H:i:s', strtotime($reg_date. " + $days days"));
                                    $mysqli->query("UPDATE svt_users SET expire_plan_date='$exp_date' WHERE id=$id_user;");
                                }
                            }
                        }
                        break;
                }
            }
        }
    }
}

function get_user_stats($id_user) {
    global $mysqli;
    $stats = array();
    $stats['count_virtual_tours'] = 0;
    $stats['count_rooms'] = 0;
    $stats['count_markers'] = 0;
    $stats['count_pois'] = 0;
    $query = "SELECT COUNT(*) as num FROM svt_virtualtours WHERE id_user = $id_user LIMIT 1";
    $result = $mysqli->query($query);
    if($result) {
        if($result->num_rows==1) {
            $row=$result->fetch_array(MYSQLI_ASSOC);
            $num = $row['num'];
            $stats['count_virtual_tours'] = $num;
        }
    }
    $query = "SELECT COUNT(*) as num FROM svt_rooms as r
                JOIN svt_virtualtours as v ON v.id = r.id_virtualtour
                WHERE id_user = $id_user LIMIT 1;";
    $result = $mysqli->query($query);
    if($result) {
        if($result->num_rows==1) {
            $row=$result->fetch_array(MYSQLI_ASSOC);
            $num = $row['num'];
            $stats['count_rooms'] = $num;
        }
    }
    $query = "SELECT COUNT(*) as num FROM svt_markers as m
                JOIN svt_rooms as r ON m.id_room = r.id
                JOIN svt_virtualtours as v ON v.id = r.id_virtualtour
                WHERE id_user = $id_user LIMIT 1;";
    $result = $mysqli->query($query);
    if($result) {
        if($result->num_rows==1) {
            $row=$result->fetch_array(MYSQLI_ASSOC);
            $num = $row['num'];
            $stats['count_markers'] = $num;
        }
    }
    $query = "SELECT COUNT(*) as num FROM svt_pois as m
                JOIN svt_rooms as r ON m.id_room = r.id
                JOIN svt_virtualtours as v ON v.id = r.id_virtualtour
                WHERE id_user = $id_user LIMIT 1;";
    $result = $mysqli->query($query);
    if($result) {
        if($result->num_rows==1) {
            $row=$result->fetch_array(MYSQLI_ASSOC);
            $num = $row['num'];
            $stats['count_pois'] = $num;
        }
    }
    return $stats;
}

function get_plan($id) {
    global $mysqli;
    $return = array();
    $query = "SELECT * FROM svt_plans WHERE id=$id LIMIT 1;";
    $result = $mysqli->query($query);
    if($result) {
        if($result->num_rows==1) {
            $row=$result->fetch_array(MYSQLI_ASSOC);
            $return=$row;
        }
    }
    return $return;
}

function get_id_plan_stripe($id_product_stripe) {
    global $mysqli;
    $id_plan = "";
    $query = "SELECT id FROM svt_plans WHERE id_product_stripe='$id_product_stripe' LIMIT 1;";
    $result = $mysqli->query($query);
    if($result) {
        if($result->num_rows==1) {
            $row=$result->fetch_array(MYSQLI_ASSOC);
            $id_plan=$row['id'];
        }
    }
    return $id_plan;
}

function get_name_plan_stripe($id_product_stripe) {
    global $mysqli;
    $name_plan = "";
    $query = "SELECT name FROM svt_plans WHERE id_product_stripe='$id_product_stripe' LIMIT 1;";
    $result = $mysqli->query($query);
    if($result) {
        if($result->num_rows==1) {
            $row=$result->fetch_array(MYSQLI_ASSOC);
            $name_plan=$row['name'];
        }
    }
    return $name_plan;
}

function get_next_prev_room_id($id_room,$id_virtualtour) {
    global $mysqli;
    $array_rooms = array();
    $query = "SELECT id FROM svt_rooms WHERE id_virtualtour=$id_virtualtour ORDER BY priority;";
    $result = $mysqli->query($query);
    if($result) {
        while($row=$result->fetch_array(MYSQLI_ASSOC)) {
            $id = $row['id'];
            array_push($array_rooms,$id);
        }
    }
    $index = array_search($id_room,$array_rooms);
    $len = count($array_rooms);
    $prev_id = $array_rooms[($index+$len-1)%$len];
    $next_id = $array_rooms[($index+1)%$len];
    return [$next_id,$prev_id];
}

function get_assign_virtualtours($id_user) {
    global $mysqli;
    $return = "";
    $query = "SELECT id,name,author FROM svt_virtualtours ORDER BY name;";
    $result = $mysqli->query($query);
    if($result) {
        if($result->num_rows>0) {
            while($row=$result->fetch_array(MYSQLI_ASSOC)) {
                $id = $row['id'];
                $name = $row['name'];
                $author = $row['author'];
                $query_c = "SELECT * FROM svt_assign_virtualtours WHERE id_user=$id_user AND id_virtualtour=$id LIMIT 1";
                $result_c = $mysqli->query($query_c);
                if($result_c) {
                    if ($result_c->num_rows == 1) {
                        $return .= "<div class='col-md-4 mb-1'><label><input id='$id' checked type='checkbox'> $name ($author)</label></div>";
                    } else {
                        $return .= "<div class='col-md-4 mb-1'><label><input id='$id' type='checkbox'> $name ($author)</label></div>";
                    }
                }
            }
        }
    }
    return $return;
}

function get_editor_permissions($id_user,$id_virtualtour) {
    global $mysqli;
    $query = "SELECT * FROM svt_assign_virtualtours WHERE id_user=$id_user AND id_virtualtour=$id_virtualtour LIMIT 1;";
    $result = $mysqli->query($query);
    $return = array();
    if($result) {
        if($result->num_rows==1) {
            $row=$result->fetch_array(MYSQLI_ASSOC);
            $return=$row;
        }
    }
    return $return;
}

function get_showcase_virtualtours($id_user,$id_showcase) {
    global $mysqli;
    $return = "";
    $where = "";
    switch(get_user_role($id_user)) {
        case 'administrator';
            $where = "";
            break;
        case 'customer':
            $where = "WHERE v.id_user=$id_user";
            break;
        case 'editor':
            return '';
            break;
    }
    $query = "SELECT v.id,v.name,v.author,s.id_virtualtour as id_s,s.type_viewer FROM svt_virtualtours AS v
                LEFT JOIN svt_showcase_list AS s ON s.id_virtualtour=v.id AND s.id_showcase=$id_showcase
                $where
                ORDER BY v.date_created;";
    $result = $mysqli->query($query);
    if($result) {
        if($result->num_rows>0) {
            while($row=$result->fetch_array(MYSQLI_ASSOC)) {
                $id = $row['id'];
                $name = $row['name'];
                $author = $row['author'];
                $id_s = $row['id_s'];
                $type_viewer = $row['type_viewer'];
                $select_type = "<select style='margin-left:5px;font-size:12px;vertical-align:text-top' id='t_$id'>";
                switch($type_viewer) {
                    case 'viewer':
                        $select_type .= "<option selected id='viewer'>V</option><option id='landing'>L</option>";
                        break;
                    case 'landing':
                        $select_type .= "<option id='viewer'>V</option><option selected id='landing'>L</option>";
                        break;
                    default:
                        $select_type .= "<option selected id='viewer'>V</option><option id='landing'>L</option>";
                        break;
                }
                $select_type .= "</select>";
                if (!empty($id_s)) {
                    $return .= "<div class='col-md-4 mb-1'><label><input id='$id' checked type='checkbox'>$select_type $name ($author)</label></div>";
                } else {
                    $return .= "<div class='col-md-4 mb-1'><label><input id='$id' type='checkbox'>$select_type $name ($author)</label></div>";
                }
            }
        }
    }
    return $return;
}

function get_advertisement_virtualtours($id_advertisement) {
    global $mysqli;
    $return = "";
    $query = "SELECT v.id,v.name,s.id_virtualtour as id_s FROM svt_virtualtours AS v
                LEFT JOIN svt_assign_advertisements AS s ON s.id_virtualtour=v.id AND s.id_advertisement=$id_advertisement
                WHERE v.external=0
                ORDER BY v.date_created;";
    $result = $mysqli->query($query);
    if($result) {
        if($result->num_rows>0) {
            while($row=$result->fetch_array(MYSQLI_ASSOC)) {
                $id = $row['id'];
                $name = $row['name'];
                $id_s = $row['id_s'];
                if (!empty($id_s)) {
                    $return .= "<div class='col-md-4 mb-1'><label><input id='$id' checked type='checkbox'> $name</label></div>";
                } else {
                    $return .= "<div class='col-md-4 mb-1'><label><input id='$id' type='checkbox'> $name</label></div>";
                }
            }
        }
    }
    return $return;
}

function get_advertisement_plans($id_advertisement) {
    global $mysqli;
    $return = "";
    $id_plans = array();
    $query = "SELECT id_plans FROM svt_advertisements WHERE id=$id_advertisement LIMIT 1;";
    $result = $mysqli->query($query);
    if($result) {
        if ($result->num_rows == 1) {
            $row = $result->fetch_array(MYSQLI_ASSOC);
            $id_plans = explode(",",$row['id_plans']);
        }
    }
    $query = "SELECT id,name FROM svt_plans;";
    $result = $mysqli->query($query);
    if($result) {
        if($result->num_rows>0) {
            while($row=$result->fetch_array(MYSQLI_ASSOC)) {
                $id = $row['id'];
                $name = $row['name'];
                if (in_array($id,$id_plans)) {
                    $return .= "<div class='col-md-4 mb-1'><label><input id='$id' checked type='checkbox'> $name</label></div>";
                } else {
                    $return .= "<div class='col-md-4 mb-1'><label><input id='$id' type='checkbox'> $name</label></div>";
                }
            }
        }
    }
    return $return;
}

function get_users($id_user_sel) {
    global $mysqli;
    $options = "";
    $count = 0;
    $query = "SELECT id,username,role FROM svt_users WHERE role IN('customer','administrator') ORDER BY username;";
    $result = $mysqli->query($query);
    if($result) {
        $count = $result->num_rows;
        if ($count > 0) {
            while ($row = $result->fetch_array(MYSQLI_ASSOC)) {
                $id_user = $row['id'];
                $username = $row['username'];
                $role = $row['role'];
                if($role=='administrator') $username=$username." ("._("administrator").")";
                if($id_user==$id_user_sel) {
                    $options .= "<option selected id='$id_user'>$username</option>";
                } else {
                    $options .= "<option id='$id_user'>$username</option>";
                }
            }
        }
    }
    return array("options"=>$options,"count"=>$count);
}

function check_profile_to_complete($id_user) {
    global $mysqli;
    $settings = get_settings();
    $query = "SELECT first_name,last_name,company,tax_id,street,city,province,postal_code,country,tel FROM svt_users WHERE id=$id_user;";
    $result = $mysqli->query($query);
    if($result) {
        if ($result->num_rows == 1) {
            $row = $result->fetch_array(MYSQLI_ASSOC);
            if($settings['first_name_enable'] && $settings['first_name_mandatory'] && empty($row['first_name'])) {
                return true;
            }
            if($settings['last_name_enable'] && $settings['last_name_mandatory'] && empty($row['last_name'])) {
                return true;
            }
            if($settings['company_enable'] && $settings['company_mandatory'] && empty($row['company'])) {
                return true;
            }
            if($settings['tax_id_enable'] && $settings['tax_id_mandatory'] && empty($row['tax_id'])) {
                return true;
            }
            if($settings['street_enable'] && $settings['street_mandatory'] && empty($row['street'])) {
                return true;
            }
            if($settings['city_enable'] && $settings['city_mandatory'] && empty($row['city'])) {
                return true;
            }
            if($settings['province_enable'] && $settings['province_mandatory'] && empty($row['province'])) {
                return true;
            }
            if($settings['postal_code_enable'] && $settings['postal_code_mandatory'] && empty($row['postal_code'])) {
                return true;
            }
            if($settings['country_enable'] && $settings['country_mandatory'] && empty($row['country'])) {
                return true;
            }
            if($settings['tel_enable'] && $settings['tel_mandatory'] && empty($row['tel'])) {
                return true;
            }
        }
    }
    return false;
}

function get_presets($id_virtualtour,$type) {
    global $mysqli;
    $return = array();
    $query = "SELECT * FROM svt_presets WHERE id_virtualtour=$id_virtualtour AND type='$type';";
    $result = $mysqli->query($query);
    if($result) {
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_array(MYSQLI_ASSOC)) {
                $return[]=$row;
            }
        }
    }
    return $return;
}

function get_presets_editor_ui($id_user) {
    global $mysqli;
    $return = array();
    if(get_user_role($id_user)=='administrator') {
        $query = "SELECT id,id_user,name,public FROM svt_editor_ui_presets ORDER BY name;";
    } else {
        $query = "SELECT id,id_user,name,public FROM svt_editor_ui_presets WHERE id_user=$id_user OR public=1 ORDER BY name;";
    }
    $result = $mysqli->query($query);
    if($result) {
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_array(MYSQLI_ASSOC)) {
                $return[]=$row;
            }
        }
    }
    return $return;
}

function get_disk_size_stat($id_user,$id_virtualtour) {
    global $mysqli;
    $total_size = 0;
    $path = realpath(dirname(__FILE__) . '/..').DIRECTORY_SEPARATOR."viewer".DIRECTORY_SEPARATOR;
    if($id_virtualtour==null) {
        switch(get_user_role($id_user)) {
            case 'administrator':
                $where = " WHERE 1=1 ";
                break;
            case 'customer':
                $where = " WHERE 1=1 AND v.id_user=$id_user ";
                break;
            case 'editor':
                $where = " WHERE 1=1 AND v.id IN () ";
                $query = "SELECT GROUP_CONCAT(id_virtualtour) as ids FROM svt_assign_virtualtours WHERE id_user=$id_user;";
                $result = $mysqli->query($query);
                if($result) {
                    if($result->num_rows==1) {
                        $row=$result->fetch_array(MYSQLI_ASSOC);
                        $ids = $row['ids'];
                        $where = " WHERE 1=1 AND v.id IN ($ids) ";
                    }
                }
                break;
        }
    } else {
        $where = " WHERE 1=1 AND v.id = $id_virtualtour ";
    }
    $query = "SELECT v.id,v.logo,v.nadir_logo,v.song,v.background_image,v.background_video,v.intro_desktop,v.intro_mobile FROM svt_virtualtours as v $where;";
    $result = $mysqli->query($query);
    if($result) {
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_array(MYSQLI_ASSOC)) {
                $id_vt = $row['id'];
                $logo = $row['logo'];
                $nadir_logo = $row['nadir_logo'];
                $song = $row['song'];
                $background_image = $row['background_image'];
                $background_video = $row['background_video'];
                $intro_desktop = $row['intro_desktop'];
                $intro_mobile = $row['intro_mobile'];
                if(!empty($logo)) {
                    $total_size += get_file_size($path.'content'.DIRECTORY_SEPARATOR.$logo);
                    $total_size += get_file_size($path.'content'.DIRECTORY_SEPARATOR.'thumb'.DIRECTORY_SEPARATOR.$logo);
                }
                if(!empty($nadir_logo)) {
                    $total_size += get_file_size($path.'content'.DIRECTORY_SEPARATOR.$nadir_logo);
                    $total_size += get_file_size($path.'content'.DIRECTORY_SEPARATOR.'thumb'.DIRECTORY_SEPARATOR.$nadir_logo);
                }
                if(!empty($song)) {
                    $total_size += get_file_size($path.'content'.DIRECTORY_SEPARATOR.$song);
                }
                if(!empty($background_image)) {
                    $total_size += get_file_size($path.'content'.DIRECTORY_SEPARATOR.$background_image);
                    $total_size += get_file_size($path.'content'.DIRECTORY_SEPARATOR.'thumb'.DIRECTORY_SEPARATOR.$background_image);
                }
                if(!empty($background_video)) {
                    $total_size += get_file_size($path.'content'.DIRECTORY_SEPARATOR.$background_video);
                }
                if(!empty($intro_desktop)) {
                    $total_size += get_file_size($path.'content'.DIRECTORY_SEPARATOR.$intro_desktop);
                    $total_size += get_file_size($path.'content'.DIRECTORY_SEPARATOR.'thumb'.DIRECTORY_SEPARATOR.$intro_desktop);
                }
                if(!empty($intro_mobile)) {
                    $total_size += get_file_size($path.'content'.DIRECTORY_SEPARATOR.$intro_mobile);
                    $total_size += get_file_size($path.'content'.DIRECTORY_SEPARATOR.'thumb'.DIRECTORY_SEPARATOR.$intro_mobile);
                }
                $query_a = "SELECT a.image FROM svt_advertisements as a JOIN svt_assign_advertisements as aa ON aa.id_advertisement=a.id WHERE aa.id_virtualtour=$id_vt;";
                $result_a = $mysqli->query($query_a);
                if($result_a) {
                    if ($result_a->num_rows > 0) {
                        while ($row_a = $result_a->fetch_array(MYSQLI_ASSOC)) {
                            $image = $row_a['image'];
                            $total_size += get_file_size($path.'content'.DIRECTORY_SEPARATOR.$image);
                            $total_size += get_file_size($path.'content'.DIRECTORY_SEPARATOR.'thumb'.DIRECTORY_SEPARATOR.$image);
                        }
                    }
                }
                $query_i = "SELECT image FROM svt_icons WHERE id_virtualtour=$id_vt;";
                $result_i = $mysqli->query($query_i);
                if($result_i) {
                    if ($result_i->num_rows > 0) {
                        while ($row_i = $result_i->fetch_array(MYSQLI_ASSOC)) {
                            $image = $row_i['image'];
                            $total_size += get_file_size($path.'icons'.DIRECTORY_SEPARATOR.$image);
                        }
                    }
                }
                $query_g = "SELECT image FROM svt_gallery WHERE id_virtualtour=$id_vt;";
                $result_g = $mysqli->query($query_g);
                if($result_g) {
                    if ($result_g->num_rows > 0) {
                        while ($row_g = $result_g->fetch_array(MYSQLI_ASSOC)) {
                            $image = $row_g['image'];
                            $total_size += get_file_size($path.'gallery'.DIRECTORY_SEPARATOR.$image);
                            $total_size += get_file_size($path.'gallery'.DIRECTORY_SEPARATOR.'thumb'.DIRECTORY_SEPARATOR.$image);
                        }
                    }
                }
                $query_ml = "SELECT file FROM svt_media_library WHERE id_virtualtour=$id_vt;";
                $result_ml = $mysqli->query($query_ml);
                if($result_ml) {
                    if ($result_ml->num_rows > 0) {
                        while ($row_ml = $result_ml->fetch_array(MYSQLI_ASSOC)) {
                            $file = $row_ml['file'];
                            $total_size += get_file_size($path.'media'.DIRECTORY_SEPARATOR.$file);
                            $total_size += get_file_size($path.'media'.DIRECTORY_SEPARATOR.'thumb'.DIRECTORY_SEPARATOR.$file);
                        }
                    }
                }
                $query_mu = "SELECT file FROM svt_music_library WHERE id_virtualtour=$id_vt;";
                $result_mu = $mysqli->query($query_mu);
                if($result_mu) {
                    if ($result_mu->num_rows > 0) {
                        while ($row_mu = $result_mu->fetch_array(MYSQLI_ASSOC)) {
                            $file = $row_mu['file'];
                            $total_size += get_file_size($path.'content'.DIRECTORY_SEPARATOR.$file);
                        }
                    }
                }
                $query_m = "SELECT map FROM svt_maps WHERE map!='' AND map IS NOT NULL AND id_virtualtour=$id_vt;";
                $result_m = $mysqli->query($query_m);
                if($result_m) {
                    if ($result_m->num_rows > 0) {
                        while ($row_m = $result_m->fetch_array(MYSQLI_ASSOC)) {
                            $image = $row_m['map'];
                            $total_size += get_file_size($path.'maps'.DIRECTORY_SEPARATOR.$image);
                            $total_size += get_file_size($path.'maps'.DIRECTORY_SEPARATOR.'thumb'.DIRECTORY_SEPARATOR.$image);
                        }
                    }
                }
                $query_p = "SELECT pi.image FROM svt_product_images as pi LEFT JOIN svt_products as p ON p.id=pi.id_product WHERE p.id_virtualtour=$id_vt;";
                $result_p = $mysqli->query($query_p);
                if($result_p) {
                    if ($result_p->num_rows > 0) {
                        while ($row_p = $result_p->fetch_array(MYSQLI_ASSOC)) {
                            $image = $row_p['image'];
                            $total_size += get_file_size($path.'products'.DIRECTORY_SEPARATOR.$image);
                            $total_size += get_file_size($path.'products'.DIRECTORY_SEPARATOR.'thumb'.DIRECTORY_SEPARATOR.$image);
                        }
                    }
                }
                $query_r = "SELECT id,type,panorama_image,panorama_video,thumb_image FROM svt_rooms WHERE id_virtualtour=$id_vt;";
                $result_r = $mysqli->query($query_r);
                if($result_r) {
                    if ($result_r->num_rows > 0) {
                        while ($row_r = $result_r->fetch_array(MYSQLI_ASSOC)) {
                            $id_room = $row_r['id'];
                            $type = $row_r['type'];
                            $panorama_image = $row_r['panorama_image'];
                            $panorama_video = $row_r['panorama_video'];
                            $thumb_image = $row_r['thumb_image'];
                            if(!empty($thumb_image)) {
                                $total_size += get_file_size($path.'panoramas'.DIRECTORY_SEPARATOR.'thumb_custom'.DIRECTORY_SEPARATOR.$thumb_image);
                            }
                            switch($type) {
                                case 'image':
                                    $total_size += get_file_size($path.'panoramas'.DIRECTORY_SEPARATOR.$panorama_image);
                                    $total_size += get_file_size($path.'panoramas'.DIRECTORY_SEPARATOR.'original'.DIRECTORY_SEPARATOR.$panorama_image);
                                    $total_size += get_file_size($path.'panoramas'.DIRECTORY_SEPARATOR.'lowres'.DIRECTORY_SEPARATOR.$panorama_image);
                                    $total_size += get_file_size($path.'panoramas'.DIRECTORY_SEPARATOR.'mobile'.DIRECTORY_SEPARATOR.$panorama_image);
                                    $total_size += get_file_size($path.'panoramas'.DIRECTORY_SEPARATOR.'preview'.DIRECTORY_SEPARATOR.$panorama_image);
                                    $total_size += get_file_size($path.'panoramas'.DIRECTORY_SEPARATOR.'thumb'.DIRECTORY_SEPARATOR.$panorama_image);
                                    $panorama_name = str_replace('.jpg','',$panorama_image);
                                    if(file_exists($path.'panoramas'.DIRECTORY_SEPARATOR.'multires'.DIRECTORY_SEPARATOR.$panorama_name)) {
                                        $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($path.'panoramas'.DIRECTORY_SEPARATOR.'multires'.DIRECTORY_SEPARATOR.$panorama_name), RecursiveIteratorIterator::LEAVES_ONLY);
                                        foreach ($files as $file) {
                                            if (!$file->isDir()) {
                                                $total_size += $file->getSize();
                                            }
                                        }
                                    }
                                    break;
                                case 'video':
                                    $total_size += get_file_size($path.'videos'.DIRECTORY_SEPARATOR.$panorama_video);
                                    break;
                            }
                            $query_ra = "SELECT panorama_image FROM svt_rooms_alt WHERE id_room=$id_room;";
                            $result_ra = $mysqli->query($query_ra);
                            if($result_ra) {
                                if ($result_ra->num_rows > 0) {
                                    while ($row_ra = $result_ra->fetch_array(MYSQLI_ASSOC)) {
                                        $panorama_image = $row_ra['panorama_image'];
                                        $total_size += get_file_size($path.'panoramas'.DIRECTORY_SEPARATOR.$panorama_image);
                                        $total_size += get_file_size($path.'panoramas'.DIRECTORY_SEPARATOR.'original'.DIRECTORY_SEPARATOR.$panorama_image);
                                        $total_size += get_file_size($path.'panoramas'.DIRECTORY_SEPARATOR.'lowres'.DIRECTORY_SEPARATOR.$panorama_image);
                                        $total_size += get_file_size($path.'panoramas'.DIRECTORY_SEPARATOR.'mobile'.DIRECTORY_SEPARATOR.$panorama_image);
                                        $total_size += get_file_size($path.'panoramas'.DIRECTORY_SEPARATOR.'preview'.DIRECTORY_SEPARATOR.$panorama_image);
                                        $total_size += get_file_size($path.'panoramas'.DIRECTORY_SEPARATOR.'thumb'.DIRECTORY_SEPARATOR.$panorama_image);
                                        $panorama_name = str_replace('.jpg','',$panorama_image);
                                        if(file_exists($path.'panoramas'.DIRECTORY_SEPARATOR.'multires'.DIRECTORY_SEPARATOR.$panorama_name)) {
                                            $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($path.'panoramas'.DIRECTORY_SEPARATOR.'multires'.DIRECTORY_SEPARATOR.$panorama_name), RecursiveIteratorIterator::LEAVES_ONLY);
                                            foreach ($files as $file) {
                                                if (!$file->isDir()) {
                                                    $total_size += $file->getSize();
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                            $query_poi = "SELECT content,embed_type,embed_content FROM svt_pois WHERE type IN ('image','download','video','video360','audio','embed','object3d') AND (content LIKE '%content/%' OR embed_content LIKE '%content/%') AND id_room=$id_room;";
                            $result_poi = $mysqli->query($query_poi);
                            if($result_poi) {
                                if ($result_poi->num_rows > 0) {
                                    while ($row_poi = $result_poi->fetch_array(MYSQLI_ASSOC)) {
                                        switch ($row_poi['type']) {
                                            case 'object3d':
                                                if (strpos($row_poi['content'], ',') !== false) {
                                                    $array_contents = explode(",",$row['content']);
                                                    foreach ($array_contents as $content) {
                                                        $content = basename($content);
                                                        if($content!='') {
                                                            $total_size += get_file_size($path.'content'.DIRECTORY_SEPARATOR.$content);
                                                        }
                                                    }
                                                } else {
                                                    $content = basename($row_poi['content']);
                                                    if($content!='') {
                                                        $total_size += get_file_size($path.'content'.DIRECTORY_SEPARATOR.$content);
                                                    }
                                                }
                                                break;
                                            default:
                                                $content = basename($row_poi['content']);
                                                if($content!='') {
                                                    $total_size += get_file_size($path.'content'.DIRECTORY_SEPARATOR.$content);
                                                    $total_size += get_file_size($path.'content'.DIRECTORY_SEPARATOR.'thumb'.DIRECTORY_SEPARATOR.$content);
                                                }
                                                break;
                                        }
                                        switch ($row_poi['embed_type']) {
                                            case 'image':
                                            case 'video':
                                            case 'video_chroma':
                                                $content = basename($row_poi['embed_content']);
                                                if($content!='') {
                                                    $total_size += get_file_size($path.'content'.DIRECTORY_SEPARATOR.$content);
                                                    $total_size += get_file_size($path.'content'.DIRECTORY_SEPARATOR.'thumb'.DIRECTORY_SEPARATOR.$content);
                                                }
                                                break;
                                            case 'video_transparent':
                                                if (strpos($row_poi['embed_content'], ',') !== false) {
                                                    $array_contents = explode(",",$row['embed_content']);
                                                    foreach ($array_contents as $content) {
                                                        $content = basename($content);
                                                        if($content!='') {
                                                            $total_size += get_file_size($path.'content'.DIRECTORY_SEPARATOR.$content);
                                                            $total_size += get_file_size($path.'content'.DIRECTORY_SEPARATOR.'thumb'.DIRECTORY_SEPARATOR.$content);
                                                        }
                                                    }
                                                } else {
                                                    $content = basename($row_poi['embed_content']);
                                                    if($content!='') {
                                                        $total_size += get_file_size($path.'content'.DIRECTORY_SEPARATOR.$content);
                                                        $total_size += get_file_size($path.'content'.DIRECTORY_SEPARATOR.'thumb'.DIRECTORY_SEPARATOR.$content);
                                                    }
                                                }
                                                break;
                                        }
                                    }
                                }
                            }
                            $query_pg = "SELECT id FROM svt_pois WHERE (type='gallery' OR type='object360' OR embed_type='gallery') AND id_room=$id_room;";
                            $result_pg = $mysqli->query($query_pg);
                            if($result_pg) {
                                if ($result_pg->num_rows > 0) {
                                    while ($row_pg = $result_pg->fetch_array(MYSQLI_ASSOC)) {
                                        $id_poi = $row_pg['id'];
                                        $query_eg = "SELECT image FROM svt_poi_gallery WHERE id_poi=$id_poi;";
                                        $result_eg = $mysqli->query($query_eg);
                                        if($result_eg) {
                                            if ($result_eg->num_rows > 0) {
                                                while ($row_eg = $result_eg->fetch_array(MYSQLI_ASSOC)) {
                                                    $image = $row_eg['image'];
                                                    $total_size += get_file_size($path.'gallery'.DIRECTORY_SEPARATOR.$image);
                                                    $total_size += get_file_size($path.'gallery'.DIRECTORY_SEPARATOR.'thumb'.DIRECTORY_SEPARATOR.$image);
                                                }
                                            }
                                        }
                                        $query_eg = "SELECT image FROM svt_poi_embedded_gallery WHERE id_poi=$id_poi;";
                                        $result_eg = $mysqli->query($query_eg);
                                        if($result_eg) {
                                            if ($result_eg->num_rows > 0) {
                                                while ($row_eg = $result_eg->fetch_array(MYSQLI_ASSOC)) {
                                                    $image = $row_eg['image'];
                                                    $total_size += get_file_size($path.'gallery'.DIRECTORY_SEPARATOR.$image);
                                                    $total_size += get_file_size($path.'gallery'.DIRECTORY_SEPARATOR.'thumb'.DIRECTORY_SEPARATOR.$image);
                                                }
                                            }
                                        }
                                        $query_eg = "SELECT image FROM svt_poi_objects360 WHERE id_poi=$id_poi;";
                                        $result_eg = $mysqli->query($query_eg);
                                        if($result_eg) {
                                            if ($result_eg->num_rows > 0) {
                                                while ($row_eg = $result_eg->fetch_array(MYSQLI_ASSOC)) {
                                                    $image = $row_eg['image'];
                                                    $total_size += get_file_size($path.'objects360'.DIRECTORY_SEPARATOR.$image);
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
    }
    return formatBytes($total_size);
}

function get_disk_size_stat_uploaded($id_user) {
    global $mysqli;
    $total_size = 0;
    $path = realpath(dirname(__FILE__) . '/..').DIRECTORY_SEPARATOR."viewer".DIRECTORY_SEPARATOR;
    $query = "SELECT v.id,v.logo,v.nadir_logo,v.song,v.background_image,v.background_video,v.intro_desktop,v.intro_mobile FROM svt_virtualtours as v WHERE v.id_user=$id_user;";
    $result = $mysqli->query($query);
    if($result) {
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_array(MYSQLI_ASSOC)) {
                $id_vt = $row['id'];
                $logo = $row['logo'];
                $nadir_logo = $row['nadir_logo'];
                $song = $row['song'];
                $background_image = $row['background_image'];
                $background_video = $row['background_video'];
                $intro_desktop = $row['intro_desktop'];
                $intro_mobile = $row['intro_mobile'];
                if(!empty($logo)) {
                    $total_size += get_file_size($path.'content'.DIRECTORY_SEPARATOR.$logo);
                }
                if(!empty($nadir_logo)) {
                    $total_size += get_file_size($path.'content'.DIRECTORY_SEPARATOR.$nadir_logo);
                }
                if(!empty($song)) {
                    $total_size += get_file_size($path.'content'.DIRECTORY_SEPARATOR.$song);
                }
                if(!empty($background_image)) {
                    $total_size += get_file_size($path.'content'.DIRECTORY_SEPARATOR.$background_image);
                }
                if(!empty($background_video)) {
                    $total_size += get_file_size($path.'content'.DIRECTORY_SEPARATOR.$background_video);
                }
                if(!empty($intro_desktop)) {
                    $total_size += get_file_size($path.'content'.DIRECTORY_SEPARATOR.$intro_desktop);
                }
                if(!empty($intro_mobile)) {
                    $total_size += get_file_size($path.'content'.DIRECTORY_SEPARATOR.$intro_mobile);
                }
                $query_a = "SELECT a.image FROM svt_advertisements as a JOIN svt_assign_advertisements as aa ON aa.id_advertisement=a.id WHERE aa.id_virtualtour=$id_vt;";
                $result_a = $mysqli->query($query_a);
                if($result_a) {
                    if ($result_a->num_rows > 0) {
                        while ($row_a = $result_a->fetch_array(MYSQLI_ASSOC)) {
                            $image = $row_a['image'];
                            $total_size += get_file_size($path.'content'.DIRECTORY_SEPARATOR.$image);
                        }
                    }
                }
                $query_i = "SELECT image FROM svt_icons WHERE id_virtualtour=$id_vt;";
                $result_i = $mysqli->query($query_i);
                if($result_i) {
                    if ($result_i->num_rows > 0) {
                        while ($row_i = $result_i->fetch_array(MYSQLI_ASSOC)) {
                            $image = $row_i['image'];
                            $total_size += get_file_size($path.'icons'.DIRECTORY_SEPARATOR.$image);
                        }
                    }
                }
                $query_g = "SELECT image FROM svt_gallery WHERE id_virtualtour=$id_vt;";
                $result_g = $mysqli->query($query_g);
                if($result_g) {
                    if ($result_g->num_rows > 0) {
                        while ($row_g = $result_g->fetch_array(MYSQLI_ASSOC)) {
                            $image = $row_g['image'];
                            $total_size += get_file_size($path.'gallery'.DIRECTORY_SEPARATOR.$image);
                        }
                    }
                }
                $query_ml = "SELECT file FROM svt_media_library WHERE id_virtualtour=$id_vt;";
                $result_ml = $mysqli->query($query_ml);
                if($result_ml) {
                    if ($result_ml->num_rows > 0) {
                        while ($row_ml = $result_ml->fetch_array(MYSQLI_ASSOC)) {
                            $file = $row_ml['file'];
                            $total_size += get_file_size($path.'media'.DIRECTORY_SEPARATOR.$file);
                        }
                    }
                }
                $query_mu = "SELECT file FROM svt_music_library WHERE id_virtualtour=$id_vt;";
                $result_mu = $mysqli->query($query_mu);
                if($result_mu) {
                    if ($result_mu->num_rows > 0) {
                        while ($row_mu = $result_mu->fetch_array(MYSQLI_ASSOC)) {
                            $file = $row_mu['file'];
                            $total_size += get_file_size($path.'content'.DIRECTORY_SEPARATOR.$file);
                        }
                    }
                }
                $query_m = "SELECT map FROM svt_maps WHERE map!='' AND map IS NOT NULL AND id_virtualtour=$id_vt;";
                $result_m = $mysqli->query($query_m);
                if($result_m) {
                    if ($result_m->num_rows > 0) {
                        while ($row_m = $result_m->fetch_array(MYSQLI_ASSOC)) {
                            $image = $row_m['map'];
                            $total_size += get_file_size($path.'maps'.DIRECTORY_SEPARATOR.$image);
                        }
                    }
                }
                $query_p = "SELECT pi.image FROM svt_product_images as pi LEFT JOIN svt_products as p ON p.id=pi.id_product WHERE p.id_virtualtour=$id_vt;";
                $result_p = $mysqli->query($query_p);
                if($result_p) {
                    if ($result_p->num_rows > 0) {
                        while ($row_p = $result_p->fetch_array(MYSQLI_ASSOC)) {
                            $image = $row_p['image'];
                            $total_size += get_file_size($path.'products'.DIRECTORY_SEPARATOR.$image);
                            $total_size += get_file_size($path.'products'.DIRECTORY_SEPARATOR.'thumb'.DIRECTORY_SEPARATOR.$image);
                        }
                    }
                }
                $query_r = "SELECT id,type,panorama_image,panorama_video,thumb_image FROM svt_rooms WHERE id_virtualtour=$id_vt;";
                $result_r = $mysqli->query($query_r);
                if($result_r) {
                    if ($result_r->num_rows > 0) {
                        while ($row_r = $result_r->fetch_array(MYSQLI_ASSOC)) {
                            $id_room = $row_r['id'];
                            $type = $row_r['type'];
                            $panorama_image = $row_r['panorama_image'];
                            $panorama_video = $row_r['panorama_video'];
                            $thumb_image = $row_r['thumb_image'];
                            if(!empty($thumb_image)) {
                                $total_size += get_file_size($path.'panoramas'.DIRECTORY_SEPARATOR.'thumb_custom'.DIRECTORY_SEPARATOR.$thumb_image);
                            }
                            switch($type) {
                                case 'image':
                                    $total_size += get_file_size($path.'panoramas'.DIRECTORY_SEPARATOR.'original'.DIRECTORY_SEPARATOR.$panorama_image);
                                    break;
                                case 'video':
                                    $total_size += get_file_size($path.'videos'.DIRECTORY_SEPARATOR.$panorama_video);
                                    break;
                            }
                            $query_ra = "SELECT panorama_image FROM svt_rooms_alt WHERE id_room=$id_room;";
                            $result_ra = $mysqli->query($query_ra);
                            if($result_ra) {
                                if ($result_ra->num_rows > 0) {
                                    while ($row_ra = $result_ra->fetch_array(MYSQLI_ASSOC)) {
                                        $panorama_image = $row_ra['panorama_image'];
                                        $total_size += get_file_size($path.'panoramas'.DIRECTORY_SEPARATOR.'original'.DIRECTORY_SEPARATOR.$panorama_image);
                                    }
                                }
                            }
                            $query_poi = "SELECT content,type,embed_type,embed_content FROM svt_pois WHERE type IN ('image','download','video','video360','audio','embed','object3d') AND (content LIKE '%content/%' OR embed_content LIKE '%content/%') AND id_room=$id_room;";
                            $result_poi = $mysqli->query($query_poi);
                            if($result_poi) {
                                if ($result_poi->num_rows > 0) {
                                    while ($row_poi = $result_poi->fetch_array(MYSQLI_ASSOC)) {
                                        switch ($row_poi['type']) {
                                            case 'object3d':
                                                if (strpos($row_poi['content'], ',') !== false) {
                                                    $array_contents = explode(",",$row['content']);
                                                    foreach ($array_contents as $content) {
                                                        $content = basename($content);
                                                        if($content!='') {
                                                            $total_size += get_file_size($path.'content'.DIRECTORY_SEPARATOR.$content);
                                                        }
                                                    }
                                                } else {
                                                    $content = basename($row_poi['content']);
                                                    if($content!='') {
                                                        $total_size += get_file_size($path.'content'.DIRECTORY_SEPARATOR.$content);
                                                    }
                                                }
                                                break;
                                            default:
                                                $content = basename($row_poi['content']);
                                                if($content!='') {
                                                    $total_size += get_file_size($path.'content'.DIRECTORY_SEPARATOR.$content);
                                                }
                                                break;
                                        }
                                        switch ($row_poi['embed_type']) {
                                            case 'image':
                                            case 'video':
                                            case 'video_chroma':
                                                $content = basename($row_poi['embed_content']);
                                                if($content!='') {
                                                    $total_size += get_file_size($path.'content'.DIRECTORY_SEPARATOR.$content);
                                                }
                                                break;
                                            case 'video_transparent':
                                                if (strpos($row_poi['embed_content'], ',') !== false) {
                                                    $array_contents = explode(",",$row['embed_content']);
                                                    foreach ($array_contents as $content) {
                                                        $content = basename($content);
                                                        if($content!='') {
                                                            $total_size += get_file_size($path.'content'.DIRECTORY_SEPARATOR.$content);
                                                        }
                                                    }
                                                } else {
                                                    $content = basename($row_poi['embed_content']);
                                                    if($content!='') {
                                                        $total_size += get_file_size($path.'content'.DIRECTORY_SEPARATOR.$content);
                                                    }
                                                }
                                                break;
                                        }
                                    }
                                }
                            }
                            $query_pg = "SELECT id FROM svt_pois WHERE (type='gallery' OR type='object360' OR embed_type='gallery') AND id_room=$id_room;";
                            $result_pg = $mysqli->query($query_pg);
                            if($result_pg) {
                                if ($result_pg->num_rows > 0) {
                                    while ($row_pg = $result_pg->fetch_array(MYSQLI_ASSOC)) {
                                        $id_poi = $row_pg['id'];
                                        $query_eg = "SELECT image FROM svt_poi_gallery WHERE id_poi=$id_poi;";
                                        $result_eg = $mysqli->query($query_eg);
                                        if($result_eg) {
                                            if ($result_eg->num_rows > 0) {
                                                while ($row_eg = $result_eg->fetch_array(MYSQLI_ASSOC)) {
                                                    $image = $row_eg['image'];
                                                    $total_size += get_file_size($path.'gallery'.DIRECTORY_SEPARATOR.$image);
                                                }
                                            }
                                        }
                                        $query_eg = "SELECT image FROM svt_poi_embedded_gallery WHERE id_poi=$id_poi;";
                                        $result_eg = $mysqli->query($query_eg);
                                        if($result_eg) {
                                            if ($result_eg->num_rows > 0) {
                                                while ($row_eg = $result_eg->fetch_array(MYSQLI_ASSOC)) {
                                                    $image = $row_eg['image'];
                                                    $total_size += get_file_size($path.'gallery'.DIRECTORY_SEPARATOR.$image);
                                                }
                                            }
                                        }
                                        $query_eg = "SELECT image FROM svt_poi_objects360 WHERE id_poi=$id_poi;";
                                        $result_eg = $mysqli->query($query_eg);
                                        if($result_eg) {
                                            if ($result_eg->num_rows > 0) {
                                                while ($row_eg = $result_eg->fetch_array(MYSQLI_ASSOC)) {
                                                    $image = $row_eg['image'];
                                                    $total_size += get_file_size($path.'objects360'.DIRECTORY_SEPARATOR.$image);
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
    }
    return array(formatBytes($total_size), isa_convert_bytes_to_specified($total_size,'M',2));
}

$array_files_size = array();
function get_file_size($path) {
    global $array_files_size;
    if(file_exists($path)) {
        if(!in_array($path,$array_files_size)) {
            array_push($array_files_size,$path);
            try {
                $size = filesize($path);
            } catch (Exception $e) {
                $size = 0;
            }
            return $size;
        } else {
            return 0;
        }
    } else {
        return 0;
    }
}

function update_user_space_storage($id_user,$force=false) {
    global $mysqli;
    if(get_user_info($id_user)['max_storage_space']!=-1 || $force) {
        $size = get_disk_size_stat_uploaded($id_user)[1];
        $mysqli->query("UPDATE svt_users SET storage_space=$size WHERE id=$id_user;");
    }
}

function generate_multires($force_update,$id_virtualtour) {
    if(isEnabled('shell_exec')) {
        $settings = get_settings();
        try {
            $command = 'command -v php 2>&1';
            $output = shell_exec($command);
            if(empty($output)) $output = PHP_BINDIR."/php";
            $path_php = trim($output);
            $path = realpath(dirname(__FILE__) . '/..');
            if($force_update) {
                switch($settings['multires']) {
                    case 'local':
                        $command = $path_php." ".$path.DIRECTORY_SEPARATOR."services".DIRECTORY_SEPARATOR."generate_multires.php 1 $id_virtualtour > /dev/null &";
                        break;
                    case 'cloud':
                        $command = $path_php." ".$path.DIRECTORY_SEPARATOR."services".DIRECTORY_SEPARATOR."generate_multires_cloud.php 1 $id_virtualtour > /dev/null &";
                        break;
                }
            } else {
                switch($settings['multires']) {
                    case 'local':
                        $command = $path_php." ".$path.DIRECTORY_SEPARATOR."services".DIRECTORY_SEPARATOR."generate_multires.php 0 $id_virtualtour > /dev/null &";
                        break;
                    case 'cloud':
                        $command = $path_php." ".$path.DIRECTORY_SEPARATOR."services".DIRECTORY_SEPARATOR."generate_multires_cloud.php 0 $id_virtualtour > /dev/null &";
                        break;
                }
            }
            shell_exec($command);
        } catch (Exception $e) {}
    }
}

function generate_favicons() {
    $currentPath = $_SERVER['PHP_SELF'];
    $pathInfo = pathinfo($currentPath);
    $hostName = $_SERVER['HTTP_HOST'];
    if (is_ssl()) { $protocol = 'https'; } else { $protocol = 'http'; }
    $url = $protocol."://".$hostName.$pathInfo['dirname'];
    if(isEnabled('shell_exec')) {
        try {
            $command = 'command -v php 2>&1';
            $output = shell_exec($command);
            if(empty($output)) $output = PHP_BINDIR."/php";
            $path_php = trim($output);
            $path = realpath(dirname(__FILE__) . '/..');
            $command = $path_php." ".$path.DIRECTORY_SEPARATOR."services".DIRECTORY_SEPARATOR."generate_favicons.php $url > /dev/null &";
            shell_exec($command);
        } catch (Exception $e) {}
    }
}

function convert_image_to_base64($path) {
    $type = pathinfo($path, PATHINFO_EXTENSION);
    $data = file_get_contents($path);
    $base64 = 'data:image/' . $type . ';base64,' . base64_encode($data);
    return $base64;
}

function set_language($language,$domain) {
    if(!isset($_SESSION['lang'])) {
        $_SESSION['lang']=$language;
    } else {
        $language=$_SESSION['lang'];
    }
    if(defined('LC_MESSAGES')) {
        $result = setlocale(LC_MESSAGES, $language);
        if(!$result) {
            setlocale(LC_MESSAGES, $language.'.UTF-8');
        }
        $result = putenv('LC_MESSAGES='.$language);
        if(!$result) {
            putenv('LC_MESSAGES='.$language.'.UTF-8');
        }
    } else {
        $result = putenv('LC_ALL='.$language);
        if(!$result) {
            putenv('LC_ALL='.$language.'.UTF-8');
        }
    }
    $result = bindtextdomain($domain, "../locale");
    if(!$result) {
        $domain = "default";
        bindtextdomain($domain, "../locale");
    }
    bind_textdomain_codeset($domain, 'UTF-8');
    textdomain($domain);
}

function print_favicons_backend($logo) {
    $path = '';
    $version = time();
    if (file_exists(dirname(__FILE__).'/../favicons/custom/favicon.ico')) {
        $path = 'custom/';
        $version = preg_replace('/[^0-9]/', '', $logo);
    }
    return '<link rel="apple-touch-icon" sizes="180x180" href="../favicons/'.$path.'apple-touch-icon.png?v='.$version.'">
    <link rel="icon" type="image/png" sizes="32x32" href="../favicons/'.$path.'favicon-32x32.png?v='.$version.'">
    <link rel="icon" type="image/png" sizes="16x16" href="../favicons/'.$path.'favicon-16x16.png?v='.$version.'">
    <link rel="manifest" href="../favicons/'.$path.'site.webmanifest?v='.$version.'">
    <link rel="mask-icon" href="../favicons/'.$path.'safari-pinned-tab.svg?v='.$version.'" color="#ffffff">
    <link rel="shortcut icon" href="../favicons/'.$path.'favicon.ico?v='.$version.'">
    <meta name="msapplication-TileColor" content="#ffffff">
    <meta name="msapplication-config" content="../favicons/'.$path.'browserconfig.xml?v='.$version.'">
    <meta name="theme-color" content="#ffffff">';
}

function dateDiffInDays($date1, $date2) {
    $datetime1 = new DateTime($date1);
    $datetime2 = new DateTime($date2);
    $difference = $datetime1->diff($datetime2);
    $diff = $difference->format("%r%a");
    return $diff;
}

function _GetMaxAllowedUploadSize(){
    $Sizes = array();
    $Sizes[] = ini_get('upload_max_filesize');
    $Sizes[] = ini_get('post_max_size');
    $Sizes[] = ini_get('memory_limit');
    for($x=0;$x<count($Sizes);$x++){
        $Last = strtolower($Sizes[$x][strlen($Sizes[$x])-1]);
        if($Last == 'k'){
            $Sizes[$x] *= 1024;
        } elseif($Last == 'm'){
            $Sizes[$x] *= 1024;
            $Sizes[$x] *= 1024;
        } elseif($Last == 'g'){
            $Sizes[$x] *= 1024;
            $Sizes[$x] *= 1024;
            $Sizes[$x] *= 1024;
        } elseif($Last == 't'){
            $Sizes[$x] *= 1024;
            $Sizes[$x] *= 1024;
            $Sizes[$x] *= 1024;
            $Sizes[$x] *= 1024;
        }
    }
    return isa_convert_bytes_to_specified(min($Sizes),'M',0);
}

function format_currency($currency,$price) {
    switch ($currency) {
        case 'AUD':
            $currency = "A$ ";
            $price = $currency.number_format($price,2,'.',' ');
            break;
        case 'BRL':
            $currency = "R$ ";
            $price = $currency.number_format($price,2,',','.');
            break;
        case 'CAD':
            $currency = "C$ ";
            $price = $currency.number_format($price,2,'.',',');
            break;
        case 'CHF':
            $currency = "₣ ";
            $price = $currency.number_format($price,2,',','.');
            break;
        case 'CNY':
            $currency = "¥ ";
            $price = $currency.number_format($price,2,'.',',');
            break;
        case 'CZK':
            $currency = "Kč ";
            $price = $currency.number_format($price,2,',','.');
            break;
        case 'JPY':
            $currency = "¥ ";
            $price = $currency.number_format($price,0,'.',',');
            break;
        case 'EUR':
            $currency = "€ ";
            $price = $currency.number_format($price,2,',','.');
            break;
        case 'GBP':
            $currency = "£ ";
            $price = $currency.number_format($price,2,'.',',');
            break;
        case 'IDR':
            $currency = "Rp ";
            $price = $currency.number_format($price,2,'.',',');
            break;
        case 'INR':
            $currency = "Rs ";
            $price = $currency.number_format($price,2,'.',',');
            break;
        case 'PLN':
            $currency = "zł ";
            $price = $currency.number_format($price,2,',','.');
            break;
        case 'SEK':
            $currency = "kr ";
            $price = $currency.number_format($price,2,',','.');
            break;
        case 'TRY':
            $currency = "₺ ";
            $price = $currency.number_format($price,2,'.',',');
            break;
        case 'TJS':
            $currency = "SM ";
            $price = $currency.number_format($price,2,'.',',');
            break;
        case 'USD':
        case 'ARS':
            $currency = "$ ";
            $price = $currency.number_format($price,2,'.',',');
            break;
        case 'HKD':
            $currency = "HK$ ";
            $price = $currency.number_format($price,2,'.',',');
            break;
        case 'MXN':
            $currency = "Mex$ ";
            $price = $currency.number_format($price,2,',','.');
            break;
        case 'PHP':
            $currency = "₱ ";
            $price = $currency.number_format($price,2,'.',',');
            break;
        case 'THB':
            $currency = "฿ ";
            $price = $currency.number_format($price,2,'.',',');
            break;
        case 'RWF':
            $currency = "FRw ";
            $price = $currency.number_format($price,0,'',',');
            break;
        case 'VND':
            $currency = "₫ ";
            $price = $currency.number_format($price,0,'.',',');
            break;
        case 'PYG':
            $currency = "₲ ";
            $price = $currency.number_format($price,0,'.',',');
            break;
        default:
            $price = $currency." ".$price;
            break;
    }
    return $price;
}

function formatBytes($bytes, $precision = 2) {
    $units = array('B', 'KB', 'MB', 'GB', 'TB');

    $bytes = max($bytes, 0);
    $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
    $pow = min($pow, count($units) - 1);

     $bytes /= pow(1024, $pow);

    return round($bytes, $precision) . ' ' . $units[$pow];
}

function isa_convert_bytes_to_specified($bytes, $to, $decimal_places = 1) {
    $formulas = array(
        'K' => number_format($bytes / 1024, $decimal_places,'.',''),
        'M' => number_format($bytes / 1048576, $decimal_places,'.',''),
        'G' => number_format($bytes / 1073741824, $decimal_places,'.','')
    );
    return isset($formulas[$to]) ? $formulas[$to] : 0;
}

function xor_obfuscator($string) {
    if (!strlen($string)) {
        return $string;
    }
    $key = ord($string[0]);
    $new = pack("C",
        ($key & 0xf0)
        |
        (
            ($key & 0x0f)
            ^
            (($key >> 4) & 0x0f)
        )
    );
    for ($c=1;$c<strlen($string);$c++) {
        $new .= pack("C",ord($string[$c]) ^ $key);
    }
    return base64_encode($new);
}

function xor_deobfuscator($string) {
    $string = base64_decode($string);
    if (!strlen($string)) {
        return $string;
    }
    $keys = unpack("C*",$string);
    $key = $keys[1];
    $key = ($key & 0xf0)
        |
        (
            ($key & 0x0f)
            ^
            (
                ($key >> 4)
                &
                0x0f
            )
        );
    $new = chr($key);
    for ($c=2;$c<=count($keys);$c++) {
        $new .= chr($keys[$c] ^ $key);
    }
    return $new;
}

function formatTime($format, $language = null, $timestamp = null) {
    switch ($language) {
        case 'ar_SA':
            setlocale(LC_TIME, 'ar_SA.utf8', 'ar_SA.UTF-8', 'ar_SA', 'ar');
            break;
        case 'zh_CN':
            setlocale(LC_TIME, 'zh_CN.utf8', 'zh_CN.UTF-8', 'zh_CN', 'zh');
            break;
        case 'zh_HK':
            setlocale(LC_TIME, 'zh_HK.utf8', 'zh_HK.UTF-8', 'zh_HK', 'zh');
            break;
        case 'zh_TW':
            setlocale(LC_TIME, 'zh_TW.utf8', 'zh_TW.UTF-8', 'zh_TW', 'zh');
            break;
        case 'ja_JP':
            setlocale(LC_TIME, 'ja_JP.utf8', 'ja_JP.UTF-8', 'ja_JP', 'ja');
            break;
        case 'hu_HU':
            setlocale(LC_TIME, 'hu_HU.utf8', 'hu_HU.UTF-8', 'hu_HU', 'hr');
            break;
        case 'ru_RU':
            setlocale(LC_TIME, 'ru_RU.utf8', 'ru_RU.UTF-8', 'ru_RU', 'ru');
            break;
        case 'de_DE':
            setlocale(LC_TIME, 'de_DE.utf8', 'de_DE.UTF-8', 'de_DE', 'de');
            break;
        case 'fr_FR':
            setlocale(LC_TIME, 'fr_FR.utf8', 'fr_FR.UTF-8', 'fr_FR', 'fr');
            break;
        case 'pl_PL':
            setlocale(LC_TIME, 'pl_PL.utf8', 'pl_PL.UTF-8', 'pl_PL', 'pl');
            break;
        case 'tr_TR':
            setlocale(LC_TIME, 'tr_TR.utf8', 'tr_TR.UTF-8', 'tr_TR', 'tr');
            break;
        case 'cs_CZ':
            setlocale(LC_TIME, 'cs_CZ.utf8', 'cs_CZ.UTF-8', 'cs_CZ', 'cz');
            break;
        case 'rw_RW':
            setlocale(LC_TIME, 'rw_RW.utf8', 'rw_RW.UTF-8', 'rw_RW', 'rw');
            break;
        case 'fil_PH':
            setlocale(LC_TIME, 'fil_PH.utf8', 'fil_PH.UTF-8', 'fil_PH', 'ph');
            break;
        case 'fa_IR':
            setlocale(LC_TIME, 'fa_IR.utf8', 'fa_IR.UTF-8', 'fa_IR', 'ir');
            break;
        case 'tg_TJ':
            setlocale(LC_TIME, 'tg_TJ.utf8', 'tg_TJ.UTF-8', 'tg_TJ', 'tj');
            break;
        default:
            setlocale(LC_TIME, $language);
            break;
    }
    if (!is_numeric($timestamp)) {
        $datetime = strftime($format);
    } else {
        $datetime = strftime($format, $timestamp);
    }
    return $datetime;
}

function get_ip_server() {
    $server_ip = '';
    $server_name = $_SERVER['SERVER_NAME'];
    if(array_key_exists('SERVER_ADDR', $_SERVER)) {
        $server_ip = $_SERVER['SERVER_ADDR'];
        if(!filter_var($server_ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
            $server_ip = gethostbyname($server_name);
        }
    } elseif(array_key_exists('LOCAL_ADDR', $_SERVER)) {
        $server_ip = $_SERVER['LOCAL_ADDR'];
    } elseif(array_key_exists('SERVER_NAME', $_SERVER)) {
        $server_ip = gethostbyname($_SERVER['SERVER_NAME']);
    } else {
        if(stristr(PHP_OS, 'WIN')) {
            $server_ip = gethostbyname(php_uname("n"));
        } else {
            $ifconfig = shell_exec('/sbin/ifconfig eth0');
            preg_match('/addr:([\d\.]+)/', $ifconfig, $match);
            $server_ip = $match[1];
        }
    }
    return $server_ip;
}

function get_string_between($string, $start, $end){
    $string = ' ' . $string;
    $ini = strpos($string, $start);
    if ($ini == 0) return '';
    $ini += strlen($start);
    $len = strpos($string, $end, $ini) - $ini;
    return substr($string, $ini, $len);
}

function encrypt_decrypt($action, $string, $secret_key = "supersecret_key") {
    $output = false;
    $encrypt_method = "AES-256-CBC";
    $secret_iv = '#svt#';
    $key = hash('sha256', $secret_key);
    $iv = substr(hash('sha256', $secret_iv), 0, 16);
    if ( $action == 'encrypt' ) {
        $output = openssl_encrypt($string, $encrypt_method, $key, 0, $iv);
        $output = base64_encode($output);
    } else if( $action == 'decrypt' ) {
        $output = openssl_decrypt(base64_decode($string), $encrypt_method, $key, 0, $iv);
    }
    return $output;
}


function calculatePercentage($first, $second) {
    return ($first / $second) * 100;
}