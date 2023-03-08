<?php
error_reporting(E_ALL & ~E_WARNING & ~E_NOTICE & ~E_DEPRECATED);
ob_start();
require_once("../../db/connection.php");
$code = $_POST['code'];
$export_mode = $_POST['export_mode'];
$preview = $_POST['preview'];
$ip_visitor = $_POST['ip_visitor'];

$query_vt = "SELECT v.id,v.external,v.external_url,v.name as name_virtualtour,v.id_user,v.form_enable,v.form_icon,v.form_content,v.auto_show_slider,v.nav_slider,v.sameAzimuth,v.arrows_nav,v.autorotate_speed,v.autorotate_inactivity,v.nadir_logo,v.nadir_size,v.song as song_bg,v.song_autoplay,v.voice_commands,v.compass,v.author,v.hfov as hfov_default,v.min_hfov as min_hfov_default,v.max_hfov as max_hfov_default,IF(v.info_box='' OR v.info_box IS NULL OR v.info_box='<p><br></p>',0,v.show_info) as show_info,IF(v.custom_content='' OR v.custom_content IS NULL OR v.custom_content='<div></div>',0,v.show_custom) as show_custom,v.show_gallery,v.fb_messenger,v.show_icons_toggle,v.show_autorotation_toggle,v.show_nav_control,v.show_presentation,v.show_main_form,v.show_share,v.show_device_orientation,v.drag_device_orientation,v.show_webvr,v.show_audio,v.show_vt_title,v.show_logo,v.show_fullscreen,v.show_map,v.show_map_tour,v.live_session,v.show_annotations,v.show_list_alt,v.list_alt,v.intro_desktop,v.intro_mobile,v.presentation_type,v.presentation_video,v.auto_presentation_speed,v.enable_multires,v.whatsapp_chat,v.whatsapp_number,v.transition_loading as transition_loading_v,v.transition_time as transition_time_v,v.transition_zoom as transition_zoom_v,v.transition_fadeout as transition_fadeout_v,v.transition_effect as transition_effect_v,v.meeting,v.keyboard_mode,v.preload_panoramas,v.click_anywhere,v.hide_markers,v.hover_markers,v.autoclose_menu,v.autoclose_list_alt,v.autoclose_slider,v.autoclose_map,v.pan_speed,v.pan_speed_mobile,v.friction,v.friction_mobile,v.snipcart_currency,v.enable_visitor_rt,v.interval_visitor_rt,v.show_dollhouse,v.dollhouse FROM svt_virtualtours AS v WHERE v.code = '$code' LIMIT 1;";
$result_vt = $mysqli->query($query_vt);
if($result_vt) {
    if ($result_vt->num_rows == 1) {
        $row = $result_vt->fetch_array(MYSQLI_ASSOC);
        $id_virtualtour = $row['id'];
        $external = $row['external'];
        $external_url = $row['external_url'];
        $background_color = $row['background_color'];
        $id_user = $row['id_user'];
        $name_virtualtour = $row['name_virtualtour'];
        $author = trim($row['author']);
        $hfov_default = $row['hfov_default'];
        $min_hfov = $row['min_hfov_default'];
        $max_hfov = $row['max_hfov_default'];
        $show_audio = $row['show_audio'];
        if($show_audio) {
            if($row['song']==null) $row['song']='';
            $song = $row['song_bg'];
            if($song==null) $song='';
            $song_autoplay = $row['song_autoplay'];
        } else {
            $row['song'] = '';
            $song='';
            $song_autoplay = false;
        }
        $show_vt_title = $row['show_vt_title'];
        $show_logo = $row['show_logo'];
        $nadir_logo = $row['nadir_logo'];
        $nadir_size = $row['nadir_size'];
        $autorotate_speed = $row['autorotate_speed']*2;
        $autorotate_inactivity = $row['autorotate_inactivity'];
        if($autorotate_speed==0) $autorotate_inactivity=0;
        $arrows_nav = $row['arrows_nav'];
        $show_info = $row['show_info'];
        $show_custom = $row['show_custom'];
        $show_gallery = $row['show_gallery'];
        $show_facebook = $row['fb_messenger'];
        $show_icons_toggle = $row['show_icons_toggle'];
        $show_autorotation_toggle = $row['show_autorotation_toggle'];
        $show_nav_control = $row['show_nav_control'];
        $show_presentation = $row['show_presentation'];
        $show_share = $row['show_share'];
        $show_device_orientation = $row['show_device_orientation'];
        $drag_device_orientation = $row['drag_device_orientation'];
        $show_webvr = $row['show_webvr'];
        $show_fullscreen = $row['show_fullscreen'];
        $show_map = $row['show_map'];
        $show_map_tour = $row['show_map_tour'];
        $show_annotations = $row['show_annotations'];
        $live_session = $row['live_session'];
        $show_list_alt = $row['show_list_alt'];
        $list_alt = $row['list_alt'];
        $intro_desktop = $row['intro_desktop'];
        if(empty($intro_desktop)) $intro_desktop = "";
        $intro_mobile = $row['intro_mobile'];
        if(empty($intro_mobile)) $intro_mobile = "";
        $voice_commands = $row['voice_commands'];
        $compass = $row['compass'];
        $sameAzimuth = $row['sameAzimuth'];
        $transition_loading = $row['transition_loading_v'];
        $transition_time = $row['transition_time_v'];
        $transition_zoom = $row['transition_zoom_v'];
        $transition_fadeout = $row['transition_fadeout_v'];
        $transition_effect = $row['transition_effect_v'];
        $auto_show_slider = $row['auto_show_slider'];
        $nav_slider = $row['nav_slider'];
        $presentation_type = $row['presentation_type'];
        $presentation_video = $row['presentation_video'];
        $auto_presentation_speed = $row['auto_presentation_speed']*2;
        $show_main_form = $row['show_main_form'];
        $whatsapp_chat = $row['whatsapp_chat'];
        $whatsapp_number = $row['whatsapp_number'];
        if(empty($whatsapp_number)) $whatsapp_number='';
        if($show_main_form) {
            $form_enable = $row['form_enable'];
        } else {
            $form_enable = false;
        }
        $form_icon = $row['form_icon'];
        $form_content = $row['form_content'];
        if(empty($form_content)) {
            $form_enable=false;
        } else {
            $form_array = json_decode($form_content,true);
            $form_all_disabled = true;
            for($i=1;$i<=11;$i++) {
                if($form_array[$i]['enabled']) {
                    $form_all_disabled = false;
                }
            }
            if($form_all_disabled) $form_enable=false;
        }
        $enable_multires = $row['enable_multires'];
        $meeting = $row['meeting'];
        $keyboard_mode = $row['keyboard_mode'];
        $preload_panoramas = $row['preload_panoramas'];
        $click_anywhere = $row['click_anywhere'];
        $hide_markers = $row['hide_markers'];
        $hover_markers = $row['hover_markers'];
        $autoclose_menu = $row['autoclose_menu'];
        $autoclose_list_alt = $row['autoclose_list_alt'];
        $autoclose_slider = $row['autoclose_slider'];
        $autoclose_map = $row['autoclose_map'];
        $pan_speed = $row['pan_speed'];
        $pan_speed_mobile = $row['pan_speed_mobile'];
        $friction = $row['friction'];
        $friction_mobile = $row['friction_mobile'];
        $snipcart_currency = $row['snipcart_currency'];
        $enable_visitor_rt = $row['enable_visitor_rt'];
        $interval_visitor_rt = $row['interval_visitor_rt'];
        if(!$enable_visitor_rt) {
            $interval_visitor_rt = 20000;
        }
        $show_dollhouse = $row['show_dollhouse'];
        $dollhouse = $row['dollhouse'];
        if(empty($dollhouse)) $show_dollhouse=0;
    } else {
        ob_end_clean();
        echo json_encode(array("status"=>"invalid","error"=>$mysqli->error));
        exit;
    }
} else {
    ob_end_clean();
    echo json_encode(array("status"=>"invalid","error"=>$mysqli->error));
    exit;
}

$enable_rooms_multiple=true;
$enable_rooms_protect=true;
$query = "SELECT create_landing,create_gallery,create_presentation,enable_live_session,enable_meeting,enable_chat,enable_voice_commands,enable_share,enable_device_orientation,enable_webvr,enable_logo,enable_nadir_logo,enable_song,enable_forms,enable_annotations,enable_rooms_multiple,enable_rooms_protect,enable_info_box,enable_maps,enable_icons_library,enable_password_tour,enable_expiring_dates,enable_auto_rotate,enable_flyin,enable_multires,enable_dollhouse FROM svt_plans as p LEFT JOIN svt_users AS u ON u.id_plan=p.id WHERE u.id = $id_user LIMIT 1;";
$result = $mysqli->query($query);
if($result) {
    if ($result->num_rows == 1) {
        $row=$result->fetch_array(MYSQLI_ASSOC);
        if(!$row['enable_live_session']) $live_session=0;
        if(!$row['enable_meeting']) $meeting=0;
        if(!$row['create_gallery']) $show_gallery=0;
        if(!$row['enable_info_box']) $show_info=0;
        if(!$row['enable_voice_commands']) $voice_commands=0;
        if(!$row['enable_chat']) {
            $show_facebook=0;
            $whatsapp_chat=0;
        }
        if(!$row['enable_song']) {
            $song="";
            $song_autoplay=false;
            $show_audio=false;
        }
        if(!$row['enable_maps']) {
            $show_map=0;
            $show_map_tour=0;
        }
        if(!$row['enable_annotations']) $show_annotations=0;
        if(!$row['enable_forms']) $form_enable=false;
        if(!$row['enable_share']) $show_share=0;
        if(!$row['enable_device_orientation']) $show_device_orientation=0;
        if(!$row['enable_webvr']) $show_webvr=0;
        if(!$row['create_presentation']) $show_presentation=0;
        if(!$row['enable_rooms_multiple']) $enable_rooms_multiple=false;
        if(!$row['enable_rooms_protect']) $enable_rooms_protect=false;
        if(!$row['enable_nadir_logo']) $nadir_logo='';
        if(!$row['enable_auto_rotate']) {
            $autorotate_speed=0;
            $autorotate_inactivity=0;
        }
        if(!$row['enable_multires']) $enable_multires=false;
        if(!$row['enable_dollhouse']) $show_dollhouse=false;
    }
}

if($export_mode==1) {
    $enable_rooms_protect=false;
    $form_enable=false;
    $live_session=0;
}

$array_base64 = array();

if($external==0) {
    $query = "SELECT r.* FROM svt_rooms AS r 
JOIN svt_virtualtours AS v ON v.id=r.id_virtualtour
WHERE v.id = $id_virtualtour
ORDER BY r.priority ASC, r.id ASC";
    $result = $mysqli->query($query);
    $rooms = array();
    $array = array();
    $has_annotation = false;
    if($result) {
        if($result->num_rows>0) {
            while($row=$result->fetch_array(MYSQLI_ASSOC)) {
                $id_room = $row['id'];
                if(empty($row['annotation_title'])) $row['annotation_title']='';
                if(empty($row['annotation_description'])) $row['annotation_description']='';
                if($row['annotation_title']!='' || $row['annotation_description']!='') {
                    $has_annotation = true;
                }
                if(empty($row['logo'])) $row['logo']='';
                $query_m = "SELECT m.*,r.name as name_room_target, 'marker' as type,'marker' as object,i.id as id_icon_library, i.image as img_icon_library,r.panorama_image FROM svt_markers AS m
                        JOIN svt_rooms AS r ON m.id_room_target = r.id
                        LEFT JOIN svt_icons as i ON i.id=m.id_icon_library
                        WHERE m.id_room = $id_room";
                $result_m = $mysqli->query($query_m);
                $markers = array();
                if($result_m) {
                    if ($result_m->num_rows > 0) {
                        while ($row_m = $result_m->fetch_array(MYSQLI_ASSOC)) {
                            if($row_m['embed_type']==null) $row_m['embed_type']='';
                            $img_icon_library = $row_m["img_icon_library"];
                            if(!empty($img_icon_library)) {
                                $base64_img = convert_image_to_base64(dirname(__FILE__).'/../icons/'.$img_icon_library);
                            } else {
                                $base64_img = '';
                            }
                            if(!array_key_exists($img_icon_library,$array_base64)) {
                                $array_base64[$img_icon_library] = $base64_img;
                            }
                            $markers[] = $row_m;
                        }
                    }
                }
                $query_p = "SELECT p.*,'poi' as object,i.id as id_icon_library, i.image as img_icon_library FROM svt_pois AS p 
                        LEFT JOIN svt_icons as i ON i.id=p.id_icon_library
                        WHERE p.id_room = $id_room";
                $result_p = $mysqli->query($query_p);
                if($result_p) {
                    if ($result_p->num_rows > 0) {
                        while ($row_p = $result_p->fetch_array(MYSQLI_ASSOC)) {
                            $img_icon_library = $row_p["img_icon_library"];
                            if(!empty($img_icon_library)) {
                                $base64_img = convert_image_to_base64(dirname(__FILE__).'/../icons/'.$img_icon_library);
                            } else {
                                $base64_img = '';
                            }
                            if(!array_key_exists($img_icon_library,$array_base64)) {
                                $array_base64[$img_icon_library] = $base64_img;
                            }
                            if(empty($row_p['schedule'])) $row_p['schedule']='';
                            if($row_p['label']==null) $row_p['label']='';
                            if($row_p['embed_type']==null) $row_p['embed_type']='';
                            switch($row_p['type']) {
                                case 'gallery':
                                    $id_poi = $row_p['id'];
                                    $array_images = array();
                                    $query_g = "SELECT image,title,description FROM svt_poi_gallery WHERE id_poi=$id_poi ORDER BY priority;";
                                    $result_g = $mysqli->query($query_g);
                                    if($result_g) {
                                        if ($result_g->num_rows > 0) {
                                            $index_g = 1;
                                            while ($row_g = $result_g->fetch_array(MYSQLI_ASSOC)) {
                                                if((!empty($row_g['title'])) || (!empty($row_g['description']))) {
                                                    $array_images[] = array("ID"=>$index_g,"kind"=>"image","src"=>"gallery/".$row_g['image'],"srct"=>"gallery/thumb/".$row_g['image'],"title"=>"<div><h4>".$row_g['title']."</h4><p>".$row_g['description']."</p></div>");
                                                } else {
                                                    $array_images[] = array("ID"=>$index_g,"kind"=>"image","src"=>"gallery/".$row_g['image'],"srct"=>"gallery/thumb/".$row_g['image']);
                                                }
                                                $index_g++;
                                            }
                                        }
                                    }
                                    $row_p['content'] = $array_images;
                                    $markers[] = $row_p;
                                    break;
                                case 'object360':
                                    $id_poi = $row_p['id'];
                                    $array_object360 = array();
                                    $query_g = "SELECT image,COUNT(*) as count_images FROM svt_poi_objects360 WHERE id_poi=$id_poi LIMIT 1;";
                                    $result_g = $mysqli->query($query_g);
                                    if($result_g) {
                                        if ($result_g->num_rows == 1) {
                                            $row_g = $result_g->fetch_array(MYSQLI_ASSOC);
                                            $array_object360['count_images'] = $row_g['count_images'];
                                            $tmp = explode(".",$row_g['image']);
                                            $ext = end($tmp);
                                            $tmp = explode("_",$tmp[0]);
                                            $array_object360['name_images'] = $tmp[0]."_".$tmp[1]."_{index}.".$ext;
                                        }
                                    }
                                    $row_p['content'] = $array_object360;
                                    $markers[] = $row_p;
                                    break;
                                case 'product':
                                    $id_product = $row_p['content'];
                                    $row_product = array();
                                    $query_product = "SELECT * FROM svt_products WHERE id=$id_product LIMIT 1;";
                                    $result_product = $mysqli->query($query_product);
                                    if($result_product) {
                                        if ($result_product->num_rows == 1) {
                                            $row_product = $result_product->fetch_array(MYSQLI_ASSOC);
                                            if(empty($row_product['description'])) $row_product['description']='';
                                            switch ($snipcart_currency) {
                                                case 'AUD':
                                                    $currency = "A$ ";
                                                    $price = $currency.number_format($row_product['price'],2,'.',' ');
                                                    break;
                                                case 'BRL':
                                                    $currency = "R$ ";
                                                    $price = $currency.number_format($row_product['price'],2,',','.');
                                                    break;
                                                case 'CAD':
                                                    $currency = "C$ ";
                                                    $price = $currency.number_format($row_product['price'],2,'.',',');
                                                    break;
                                                case 'CHF':
                                                    $currency = "₣ ";
                                                    $price = $currency.number_format($row_product['price'],2,',','.');
                                                    break;
                                                case 'CNY':
                                                    $currency = "¥ ";
                                                    $price = $currency.number_format($row_product['price'],2,'.',',');
                                                    break;
                                                case 'CZK':
                                                    $currency = "Kč ";
                                                    $price = $currency.number_format($row_product['price'],2,',','.');
                                                    break;
                                                case 'JPY':
                                                    $currency = "¥ ";
                                                    $price = $currency.number_format($row_product['price'],0,'.',',');
                                                    break;
                                                case 'EUR':
                                                    $currency = "€ ";
                                                    $price = $currency.number_format($row_product['price'],2,',','.');
                                                    break;
                                                case 'GBP':
                                                    $currency = "£ ";
                                                    $price = $currency.number_format($row_product['price'],2,'.',',');
                                                    break;
                                                case 'IDR':
                                                    $currency = "Rp ";
                                                    $price = $currency.number_format($row_product['price'],2,'.',',');
                                                    break;
                                                case 'INR':
                                                    $currency = "Rs ";
                                                    $price = $currency.number_format($row_product['price'],2,'.',',');
                                                    break;
                                                case 'PLN':
                                                    $currency = "zł ";
                                                    $price = $currency.number_format($row_product['price'],2,',','.');
                                                    break;
                                                case 'SEK':
                                                    $currency = "kr ";
                                                    $price = $currency.number_format($row_product['price'],2,',','.');
                                                    break;
                                                case 'TRY':
                                                    $currency = "₺ ";
                                                    $price = $currency.number_format($row_product['price'],2,'.',',');
                                                    break;
                                                case 'TJS':
                                                    $currency = "SM ";
                                                    $price = $currency.number_format($row_product['price'],2,'.',',');
                                                    break;
                                                case 'USD':
                                                case 'ARS':
                                                    $currency = "$ ";
                                                    $price = $currency.number_format($row_product['price'],2,'.',',');
                                                    break;
                                                case 'HKD':
                                                    $currency = "HK$ ";
                                                    $price = $currency.number_format($row_product['price'],2,'.',',');
                                                    break;
                                                case 'MXN':
                                                    $currency = "Mex$ ";
                                                    $price = $currency.number_format($row_product['price'],2,',','.');
                                                    break;
                                                case 'PHP':
                                                    $currency = "₱ ";
                                                    $price = $currency.number_format($row_product['price'],2,'.',',');
                                                    break;
                                                case 'THB':
                                                    $currency = "฿ ";
                                                    $price = $currency.number_format($row_product['price'],2,'.',',');
                                                    break;
                                                case 'RWF':
                                                    $currency = "FRw ";
                                                    $price = $currency.number_format($row_product['price'],0,'',',');
                                                    break;
                                                case 'VND':
                                                    $currency = "₫ ";
                                                    $price = $currency.number_format($row_product['price'],0,'.',',');
                                                    break;
                                                case 'PYG':
                                                    $currency = "₲ ";
                                                    $price = $currency.number_format($row_product['price'],0,'.',',');
                                                    break;
                                            }
                                            $row_product['price_html']=$price;
                                        }
                                    }
                                    $array_images = array();
                                    $query_product_images = "SELECT image FROM svt_product_images WHERE id_product=$id_product ORDER BY priority;";
                                    $result_product_images = $mysqli->query($query_product_images);
                                    if($result_product_images) {
                                        if ($result_product_images->num_rows > 0) {
                                            while ($row_product_images = $result_product_images->fetch_array(MYSQLI_ASSOC)) {
                                                $array_images[] = array("src"=>"products/".$row_product_images['image'],"src_thumb"=>"products/thumb/".$row_product_images['image']);
                                            }
                                        }
                                    }
                                    $row_p['product'] = $row_product;
                                    $row_p['product_images'] = $array_images;
                                    $markers[] = $row_p;
                                    break;
                                case 'html_sc':
                                    $row_p['content'] = htmlspecialchars_decode($row_p['content']);
                                    $markers[] = $row_p;
                                    break;
                                default:
                                    switch ($row_p['embed_type']) {
                                        case 'image':
                                        case 'video':
                                            if(!empty($row_p['embed_content'])) {
                                                $markers[] = $row_p;
                                            }
                                            break;
                                        case 'gallery':
                                            $id_poi = $row_p['id'];
                                            $array_images = array();
                                            $query_g = "SELECT image FROM svt_poi_embedded_gallery WHERE id_poi=$id_poi ORDER BY priority;";
                                            $result_g = $mysqli->query($query_g);
                                            if($result_g) {
                                                if ($result_g->num_rows > 0) {
                                                    while ($row_g = $result_g->fetch_array(MYSQLI_ASSOC)) {
                                                        $array_images[] = "gallery/".$row_g['image'];
                                                    }
                                                }
                                            }
                                            $row_p['embed_content'] = $array_images;
                                            $markers[] = $row_p;
                                            break;
                                        case 'text':
                                            if($row_p['embed_type']=='text') {
                                                if (strpos($row_p['embed_content'], 'border-width') === false) {
                                                    $row_p['embed_content'] = $row_p['embed_content']." border-width:0px;";
                                                }
                                            }
                                            $markers[] = $row_p;
                                            break;
                                        default:
                                            $markers[] = $row_p;
                                            break;
                                    }
                                    break;
                            }
                        }
                    }
                }
                $row['markers'] = $markers;
                $array_rooms_alt = array();
                if($enable_rooms_multiple) {
                    $query_ra = "SELECT * FROM svt_rooms_alt WHERE id_room = $id_room";
                    $result_ra = $mysqli->query($query_ra);
                    if($result_ra) {
                        if ($result_ra->num_rows > 0) {
                            while ($row_ra = $result_ra->fetch_array(MYSQLI_ASSOC)) {
                                $room_pano_ra = str_replace('.jpg','',$row_ra['panorama_image']);
                                if($enable_multires) {
                                    $multires_config_file = __DIR__.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'panoramas'.DIRECTORY_SEPARATOR.'multires'.DIRECTORY_SEPARATOR.$room_pano_ra.DIRECTORY_SEPARATOR.'config.json';
                                    if(file_exists($multires_config_file)) {
                                        $multires_tmp = file_get_contents($multires_config_file);
                                        $multires_array = json_decode($multires_tmp,true);
                                        $multires_config = $multires_array['multiRes'];
                                        $multires_config['basePath'] = 'panoramas/multires/'.$room_pano_ra;
                                        $row_ra['multires']=1;
                                        $row_ra['multires_config']=$multires_config;
                                        $row_ra['multires_dir']='panoramas/multires/'.$room_pano_ra;
                                    } else {
                                        $row_ra['multires']=0;
                                        $row_ra['multires_config']='';
                                        $row_ra['multires_dir']='';
                                    }
                                } else {
                                    $row_ra['multires']=0;
                                    $row_ra['multires_config']='';
                                    $row_ra['multires_dir']='';
                                }
                                $pano_mobile_ra = __DIR__.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'panoramas'.DIRECTORY_SEPARATOR.'mobile'.DIRECTORY_SEPARATOR.$row_ra['panorama_image'];
                                if(file_exists($pano_mobile_ra)) {
                                    $row_ra['pano_mobile']=1;
                                } else {
                                    $row_ra['pano_mobile']=0;
                                }
                                $array_rooms_alt[] = $row_ra;
                            }
                        }
                    }
                }
                $row['array_rooms_alt'] = $array_rooms_alt;
                if(count($array_rooms_alt)==0) $row['virtual_staging']=0;
                $room_pano = str_replace('.jpg','',$row['panorama_image']);
                if($enable_multires) {
                    $multires_config_file = __DIR__.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'panoramas'.DIRECTORY_SEPARATOR.'multires'.DIRECTORY_SEPARATOR.$room_pano.DIRECTORY_SEPARATOR.'config.json';
                    if(file_exists($multires_config_file)) {
                        $multires_tmp=file_get_contents($multires_config_file);
                        $multires_array=json_decode($multires_tmp,true);
                        $multires_config=$multires_array['multiRes'];
                        $multires_config['basePath']='panoramas/multires/'.$room_pano;
                        $row['multires']=1;
                        $row['multires_config']=$multires_config;
                        $row['multires_dir']='panoramas/multires/'.$room_pano;
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
                if($enable_rooms_protect) {
                    switch($row['protect_type']) {
                        case 'none':
                            $row['protected'] = 0;
                            break;
                        case 'passcode':
                            if(empty($row['passcode'])) {
                                $row['protected'] = 0;
                            } else {
                                $row['protected'] = 1;
                            }
                            break;
                        case 'leads':
                            $row['protected'] = 1;
                            break;
                    }
                } else {
                    $row['protect_type'] = 'none';
                    $row['protected'] = 0;
                }
                unset($row['passcode']);
                if($show_audio) {
                    if(empty($row['song'])) $row['song']='';
                } else {
                    $row['song']='';
                }
                if(empty($row['filters'])) $row['filters']='';
                if(empty($row['thumb_image'])) $row['thumb_image']='';
                $pano_mobile = __DIR__.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'panoramas'.DIRECTORY_SEPARATOR.'mobile'.DIRECTORY_SEPARATOR.$row['panorama_image'];
                if(file_exists($pano_mobile)) {
                    $row['pano_mobile']=1;
                } else {
                    $row['pano_mobile']=0;
                }
                $pano_lowres = __DIR__.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'panoramas'.DIRECTORY_SEPARATOR.'lowres'.DIRECTORY_SEPARATOR.$row['panorama_image'];
                if(file_exists($pano_lowres)) {
                    $row['panorama_3d']='lowres/'.$row['panorama_image'];
                } else {
                    $row['panorama_3d']=$row['panorama_image'];
                }
                if($row['hfov']==0) {
                    $row['hfov']=$hfov_default;
                } else if($row['hfov']>$max_hfov) {
                    $row['hfov']=$max_hfov;
                } else if($row['hfov']<$min_hfov) {
                    $row['hfov']=$min_hfov;
                }
                $rooms[] = $row;
                $array[$row['id']]=$row['name'];
            }
            if(!$has_annotation) $show_annotations=0;
            $array_list_alt = array();
            $array_id_rooms = array();
            if ($list_alt == '') {
                foreach ($rooms as $room) {
                    array_push($array_list_alt,["id"=>$room['id'],"type"=>"room","hide"=>"0","name"=>$room['name']]);
                }
            } else {
                $list_alt_array = json_decode($list_alt, true);
                foreach ($list_alt_array as $item) {
                    switch ($item['type']) {
                        case 'room':
                            if(array_key_exists($item['id'],$array)) {
                                array_push($array_list_alt, ["id" => $item['id'], "type" => "room", "hide" => $item['hide'], "name" => $array[$item['id']]]);
                            }
                            array_push($array_id_rooms,$item['id']);
                            break;
                        case 'category':
                            $childrens = array();
                            foreach ($item['children'] as $children) {
                                if ($children['type'] == "room") {
                                    if(array_key_exists($children['id'],$array)) {
                                        array_push($childrens, ["id" => $children['id'], "type" => "room", "hide" => $children['hide'], "name" => $array[$children['id']]]);
                                    }
                                    array_push($array_id_rooms,$children['id']);
                                }
                            }
                            array_push($array_list_alt, ["id" => $item['id'], "type" => "category", "name" => $item['cat'], "childrens" => $childrens]);
                            break;
                    }
                }
                foreach ($rooms as $room) {
                    $id_room = $room['id'];
                    if(!in_array($id_room,$array_id_rooms)) {
                        array_push($array_list_alt,["id"=>$room['id'],"type"=>"room","hide"=>"0","name"=>$room['name']]);
                    }
                }
            }
        } else {
            ob_end_clean();
            echo json_encode(array("status"=>"invalid","error"=>$mysqli->error));
            exit;
        }
    } else {
        ob_end_clean();
        echo json_encode(array("status"=>"invalid","error"=>$mysqli->error));
        exit;
    }
} else {
    $rooms = array();
    $array_rooms_alt = array();
    $array_list_alt = array();
}
if($preview==0) {
    $mysqli->query("INSERT INTO svt_access_log(id_virtualtour,date_time,ip) VALUES($id_virtualtour,NOW(),'$ip_visitor');");
}
ob_end_clean();
echo json_encode(array("status"=>"ok",
    "rooms"=>$rooms,
    "id_virtualtour"=>$id_virtualtour,
    "array_base64"=>$array_base64,
    "external"=>$external,
    "external_url"=>$external_url,
    "name_virtualtour"=>$name_virtualtour,
    "song"=>$song,
    "song_autoplay"=>$song_autoplay,
    "nadir_logo"=>$nadir_logo,
    "nadir_size"=>$nadir_size,
    "autorotate_inactivity"=>$autorotate_inactivity,
    "autorotate_speed"=>$autorotate_speed,
    "arrows_nav"=>$arrows_nav,
    "voice_commands"=>$voice_commands,
    "compass"=>$compass,
    "sameAzimuth"=>$sameAzimuth,
    "auto_show_slider"=>$auto_show_slider,
    "nav_slider"=>$nav_slider,
    "form_enable"=>$form_enable,
    "form_icon"=>$form_icon,
    "form_content"=>$form_content,
    "author"=>$author,
    "hfov"=>$hfov_default,
    "min_hfov"=>$min_hfov,
    "max_hfov"=>$max_hfov,
    "show_audio"=>$show_audio,
    "show_logo"=>$show_logo,
    "show_vt_title"=>$show_vt_title,
    "show_gallery"=>$show_gallery,
    "show_info"=>$show_info,
    "show_dollhouse"=>$show_dollhouse,
    "show_custom"=>$show_custom,
    "show_facebook"=>$show_facebook,
    "show_icons_toggle"=>$show_icons_toggle,
    "show_autorotation_toggle"=>$show_autorotation_toggle,
    "show_nav_control"=>$show_nav_control,
    "show_presentation"=>$show_presentation,
    "show_share"=>$show_share,
    "show_device_orientation"=>$show_device_orientation,
    "drag_device_orientation"=>$drag_device_orientation,
    "show_webvr"=>$show_webvr,
    "show_fullscreen"=>$show_fullscreen,
    "show_map"=>$show_map,
    "show_map_tour"=>$show_map_tour,
    "live_session"=>$live_session,
    "meeting"=>$meeting,
    "show_annotations"=>$show_annotations,
    "show_list_alt"=>$show_list_alt,
    "list_alt"=>$array_list_alt,
    "intro_desktop"=>$intro_desktop,
    "intro_mobile"=>$intro_mobile,
    "presentation_type"=>$presentation_type,
    "presentation_video"=>$presentation_video,
    "auto_presentation_speed"=>$auto_presentation_speed,
    "whatsapp_chat"=>$whatsapp_chat,
    "whatsapp_number"=>$whatsapp_number,
    "transition_loading"=>$transition_loading,
    "transition_time"=>$transition_time,
    "transition_zoom"=>$transition_zoom,
    "transition_fadeout"=>$transition_fadeout,
    "transition_effect"=>$transition_effect,
    "keyboard_mode"=>$keyboard_mode,
    "preload_panoramas"=>$preload_panoramas,
    "click_anywhere"=>$click_anywhere,
    "hide_markers"=>$hide_markers,
    "hover_markers"=>$hover_markers,
    "autoclose_menu"=>$autoclose_menu,
    "autoclose_list_alt"=>$autoclose_list_alt,
    "autoclose_slider"=>$autoclose_slider,
    "autoclose_map"=>$autoclose_map,
    "pan_speed"=>$pan_speed,
    "pan_speed_mobile"=>$pan_speed_mobile,
    "friction"=>$friction,
    "friction_mobile"=>$friction_mobile,
    "enable_visitor_rt"=>$enable_visitor_rt,
    "interval_visitor_rt"=>$interval_visitor_rt,
    "dollhouse"=>$dollhouse
));

function convert_image_to_base64($path) {
    $type = pathinfo($path, PATHINFO_EXTENSION);
    $data = file_get_contents($path);
    $base64 = 'data:image/' . $type . ';base64,' . base64_encode($data);
    return $base64;
}