<?php
error_reporting(E_ALL & ~E_WARNING & ~E_NOTICE & ~E_DEPRECATED);
ob_start();
session_start();
if((($_SERVER['SERVER_ADDR']=='5.9.29.89') && ($_SERVER['REMOTE_ADDR']!=$_SESSION['ip_developer']) && ($_SESSION['id_user']==1)) || ($_SESSION['svt_si']!=session_id())) {
    die();
}
require_once("../../db/connection.php");
require_once("../functions.php");
$id_virtualtour = $_POST['id_virtualtour'];
$id_user = $_POST['id_user'];
$arrows_nav = $_POST['arrows_nav'];
$voice_commands = $_POST['voice_commands'];
$compass = $_POST['compass'];
$auto_show_slider = $_POST['auto_show_slider'];
$nav_slider = $_POST['nav_slider'];
$show_list_alt = $_POST['show_list_alt'];
$show_info = $_POST['show_info'];
$show_dollhouse = $_POST['show_dollhouse'];
$show_custom = $_POST['show_custom'];
$show_gallery = $_POST['show_gallery'];
$show_icons_toggle = $_POST['show_icons_toggle'];
$show_autorotation_toggle = $_POST['show_autorotation_toggle'];
$show_nav_control = $_POST['show_nav_control'];
$show_presentation = $_POST['show_presentation'];
$show_main_form = $_POST['show_main_form'];
$show_share = $_POST['show_share'];
$show_device_orientation = $_POST['show_device_orientation'];
$drag_device_orientation = $_POST['drag_device_orientation'];
$show_webvr = $_POST['show_webvr'];
$show_audio = $_POST['show_audio'];
$show_vt_title = $_POST['show_vt_title'];
$show_fullscreen = $_POST['show_fullscreen'];
$show_map = $_POST['show_map'];
$show_map_tour = $_POST['show_map_tour'];
$live_session = $_POST['live_session'];
$meeting = $_POST['meeting'];
$show_annotations = $_POST['show_annotations'];
$autoclose_menu = $_POST['autoclose_menu'];
$autoclose_list_alt = $_POST['autoclose_list_alt'];
$autoclose_slider = $_POST['autoclose_slider'];
$autoclose_map = $_POST['autoclose_map'];
$fb_messenger = $_POST['fb_messenger'];
$whatsapp_chat = $_POST['whatsapp_chat'];
$show_logo = $_POST['show_logo'];
$array_colors = $_POST['array_colors'];
$array_positions = $_POST['array_positions'];
$array_orders = $_POST['array_orders'];
$array_icons = $_POST['array_icons'];
$annotation_position = $_POST['annotation_position'];
$map_position = $_POST['map_position'];
$logo_position = $_POST['logo_position'];
$logo_height = $_POST['logo_height'];
if(empty($logo_height)) $logo_height=40;
$form_enable = $_POST['form_enable'];
$form_content = $_POST['form_content'];
$custom_title = str_replace("'","\'",$_POST['custom_title']);
$custom_content = str_replace("'","\'",$_POST['custom_content']);
$custom_content = htmlspecialchars_decode($custom_content);
$markers_icon = $_POST['markers_icon'];
$markers_id_icon_library = $_POST['markers_id_icon_library'];
$markers_color = $_POST['markers_color'];
$markers_background = $_POST['markers_background'];
$markers_show_room = $_POST['markers_show_room'];
if($markers_show_room!=4) $markers_id_icon_library=0;
$markers_tooltip_type = $_POST['markers_tooltip_type'];
$pois_icon = $_POST['pois_icon'];
$pois_id_icon_library = $_POST['pois_id_icon_library'];
$pois_color = $_POST['pois_color'];
$pois_background = $_POST['pois_background'];
$pois_style = $_POST['pois_style'];
if($pois_style!=1) $pois_id_icon_library=0;
$pois_tooltip_type = $_POST['pois_tooltip_type'];
$position_list = $array_positions['position_list'];
if($position_list!='default') {
    $tmp = explode("_",$position_list);
    $type_list = $tmp[0];
    $position_list = $tmp[1];
} else {
    $type_list = "default";
    $position_list = "";
}
$position_arrows = $array_positions['position_arrows'];
if($position_arrows!='default') {
    $tmp = explode("_",$position_arrows);
    $type_arrows = $tmp[0];
    $position_arrows = $tmp[1];
} else {
    $type_arrows = "default";
    $position_arrows = "";
}
foreach($array_orders as $key => $value) {
    $array_orders[str_replace(['_left','_center','_right','_menu'], '', $key)] = $value;
    unset($array_orders[$key]);
}
if(!isset($array_orders['controls_arrow'])) $array_orders['controls_arrow']=0;
if(!isset($array_colors['title_background'])) $array_colors['title_background']='';
$font_viewer = $_POST['font_viewer'];
$id_preset = $_POST['id_preset'];
$name_preset = str_replace("'","\'",$_POST['name_preset']);
$preset_public = $_POST['preset_public'];
$apply_preset = $_POST['apply_preset'];
$ui_style = [
    'items'=>[
        'list'=>[
            'background_initial'=>'',
            'background'=>$array_colors['slider_background']
        ],
        'annotation'=>[
            'position'=>$annotation_position,
            'color'=>$array_colors['annotation_color'],
            'background'=>$array_colors['annotation_background']
        ],
        'title'=>[
            'color'=>$array_colors['title_color'],
            'background'=>rtrim(str_replace("rgb","rgba",$array_colors['title_background']), ")")
        ],
        'nav_control'=>[
            'color'=>$array_colors['nav_control_color'],
            'color_hover'=>$array_colors['nav_control_color_hover'],
            'background'=>$array_colors['nav_background']
        ],
        'logo'=>[
            'position'=>$logo_position,
            'height'=>$logo_height
        ],
        'map'=>[
            'position'=>$map_position
        ],
    ],
    'icons'=>[
        'menu'=>[
            'color'=>$array_colors['menu_color'],
            'color_hover'=>$array_colors['menu_color_hover']
        ],
        'list_alt'=>[
            'color'=>$array_colors['list_alt_color'],
            'color_hover'=>$array_colors['list_alt_color_hover']
        ],
        'audio'=>[
            'color'=>$array_colors['audio_color'],
            'color_hover'=>$array_colors['audio_color_hover']
        ],
        'floorplan'=>[
            'color'=>$array_colors['floorplan_color'],
            'color_hover'=>$array_colors['floorplan_color_hover']
        ],
        'map'=>[
            'color'=>$array_colors['map_color'],
            'color_hover'=>$array_colors['map_color_hover']
        ],
        'fullscreen'=>[
            'color'=>$array_colors['fullscreen_color'],
            'color_hover'=>$array_colors['fullscreen_color_hover']
        ]
    ],
    'controls'=>[
        'list_alt_menu'=>[
            'style'=>'background-color:'.$array_colors['list_alt_menu_background'].';color:'.$array_colors['list_alt_menu_color'].';',
            'style_hover'=>'background-color:'.$array_colors['list_alt_menu_background_hover'].';color:'.$array_colors['list_alt_menu_color_hover'].';',
            'icon_color'=>$array_colors['list_alt_menu_icon_color'],
            'icon_color_hover'=>$array_colors['list_alt_menu_icon_color_hover']
        ],
        'list'=>[
            'type'=>explode("_",$array_positions['position_list'])[0],
            'position'=>explode("_",$array_positions['position_list'])[1],
            'order'=>$array_orders['controls_arrow'],
            'style'=>'background-color:'.$array_colors['list_background'].';color:'.$array_colors['list_color'].';',
            'style_hover'=>'background-color:'.$array_colors['list_background_hover'].';color:'.$array_colors['list_color_hover'].';'
        ],
        'arrows'=>[
            'type'=>explode("_",$array_positions['position_arrows'])[0],
            'position'=>explode("_",$array_positions['position_arrows'])[1],
            'order'=>$array_orders['controls_arrow'],
            'style'=>'background-color:'.$array_colors['arrows_background'].';color:'.$array_colors['arrows_color'].';',
            'style_hover'=>'background-color:'.$array_colors['arrows_background_hover'].';color:'.$array_colors['arrows_color_hover'].';'
        ],
        'nav_arrows'=>[
            'style'=>'background-color:transparent;color:'.$array_colors['nav_arrows_color'].';',
            'style_hover'=>'background-color:transparent;color:'.$array_colors['nav_arrows_color_hover'].';'
        ],
        'voice'=>[
            'type'=>'button',
            'position'=>'left',
            'order'=>0
        ],
        'custom'=>[
            'type'=>explode("_",$array_positions['position_custom'])[0],
            'position'=>explode("_",$array_positions['position_custom'])[1],
            'order'=>$array_orders['custom_control'],
            'style'=>'background-color:'.$array_colors['custom_background'].';color:'.$array_colors['custom_color'].';',
            'style_hover'=>'background-color:'.$array_colors['custom_background_hover'].';color:'.$array_colors['custom_color_hover'].';',
            'icon'=>$array_icons['custom'],
            'label'=>$custom_title
        ],
        'info'=>[
            'type'=>explode("_",$array_positions['position_info'])[0],
            'position'=>explode("_",$array_positions['position_info'])[1],
            'order'=>$array_orders['info_control'],
            'style'=>'background-color:'.$array_colors['info_background'].';color:'.$array_colors['info_color'].';',
            'style_hover'=>'background-color:'.$array_colors['info_background_hover'].';color:'.$array_colors['info_color_hover'].';',
            'icon'=>$array_icons['info']
        ],
        'dollhouse'=>[
            'type'=>explode("_",$array_positions['position_dollhouse'])[0],
            'position'=>explode("_",$array_positions['position_dollhouse'])[1],
            'order'=>$array_orders['dollhouse_control'],
            'style'=>'background-color:'.$array_colors['dollhouse_background'].';color:'.$array_colors['dollhouse_color'].';',
            'style_hover'=>'background-color:'.$array_colors['dollhouse_background_hover'].';color:'.$array_colors['dollhouse_color_hover'].';',
            'icon'=>$array_icons['dollhouse']
        ],
        'gallery'=>[
            'type'=>explode("_",$array_positions['position_gallery'])[0],
            'position'=>explode("_",$array_positions['position_gallery'])[1],
            'order'=>$array_orders['gallery_control'],
            'style'=>'background-color:'.$array_colors['gallery_background'].';color:'.$array_colors['gallery_color'].';',
            'style_hover'=>'background-color:'.$array_colors['gallery_background_hover'].';color:'.$array_colors['gallery_color_hover'].';',
            'icon'=>$array_icons['gallery']
        ],
        'facebook'=>[
            'type'=>explode("_",$array_positions['position_facebook'])[0],
            'position'=>explode("_",$array_positions['position_facebook'])[1],
            'order'=>$array_orders['facebook_control'],
            'style'=>'background-color:'.$array_colors['facebook_background'].';color:'.$array_colors['facebook_color'].';',
            'style_hover'=>'background-color:'.$array_colors['facebook_background_hover'].';color:'.$array_colors['facebook_color_hover'].';',
            'icon'=>$array_icons['facebook']
        ],
        'whatsapp'=>[
            'type'=>explode("_",$array_positions['position_whatsapp'])[0],
            'position'=>explode("_",$array_positions['position_whatsapp'])[1],
            'order'=>$array_orders['whatsapp_control'],
            'style'=>'background-color:'.$array_colors['whatsapp_background'].';color:'.$array_colors['whatsapp_color'].';',
            'style_hover'=>'background-color:'.$array_colors['whatsapp_background_hover'].';color:'.$array_colors['whatsapp_color_hover'].';',
            'icon'=>$array_icons['whatsapp']
        ],
        'presentation'=>[
            'type'=>explode("_",$array_positions['position_presentation'])[0],
            'position'=>explode("_",$array_positions['position_presentation'])[1],
            'order'=>$array_orders['presentation_control'],
            'style'=>'background-color:'.$array_colors['presentation_background'].';color:'.$array_colors['presentation_color'].';',
            'style_hover'=>'background-color:'.$array_colors['presentation_background_hover'].';color:'.$array_colors['presentation_color_hover'].';',
            'icon'=>$array_icons['presentation']
        ],
        'share'=>[
            'type'=>explode("_",$array_positions['position_share'])[0],
            'position'=>explode("_",$array_positions['position_share'])[1],
            'order'=>$array_orders['share_control'],
            'style'=>'background-color:'.$array_colors['share_background'].';color:'.$array_colors['share_color'].';',
            'style_hover'=>'background-color:'.$array_colors['share_background_hover'].';color:'.$array_colors['share_color_hover'].';',
            'icon'=>$array_icons['share']
        ],
        'form'=>[
            'type'=>explode("_",$array_positions['position_form'])[0],
            'position'=>explode("_",$array_positions['position_form'])[1],
            'order'=>$array_orders['form_control'],
            'style'=>'background-color:'.$array_colors['form_background'].';color:'.$array_colors['form_color'].';',
            'style_hover'=>'background-color:'.$array_colors['form_background_hover'].';color:'.$array_colors['form_color_hover'].';',
            'icon'=>$array_icons['form']
        ],
        'live'=>[
            'type'=>explode("_",$array_positions['position_live'])[0],
            'position'=>explode("_",$array_positions['position_live'])[1],
            'order'=>$array_orders['live_control'],
            'style'=>'background-color:'.$array_colors['live_background'].';color:'.$array_colors['live_color'].';',
            'style_hover'=>'background-color:'.$array_colors['live_background_hover'].';color:'.$array_colors['live_color_hover'].';',
            'icon'=>$array_icons['live']
        ],
        'meeting'=>[
            'type'=>explode("_",$array_positions['position_meeting'])[0],
            'position'=>explode("_",$array_positions['position_meeting'])[1],
            'order'=>$array_orders['meeting_control'],
            'style'=>'background-color:'.$array_colors['meeting_background'].';color:'.$array_colors['meeting_color'].';',
            'style_hover'=>'background-color:'.$array_colors['meeting_background_hover'].';color:'.$array_colors['meeting_color_hover'].';',
            'icon'=>$array_icons['meeting']
        ],
        'vr'=>[
            'type'=>explode("_",$array_positions['position_vr'])[0],
            'position'=>explode("_",$array_positions['position_vr'])[1],
            'order'=>$array_orders['vr_control'],
            'style'=>'background-color:'.$array_colors['vr_background'].';color:'.$array_colors['vr_color'].';',
            'style_hover'=>'background-color:'.$array_colors['vr_background_hover'].';color:'.$array_colors['vr_color_hover'].';',
            'icon'=>$array_icons['vr']
        ],
        'compass'=>[
            'type'=>explode("_",$array_positions['position_compass'])[0],
            'position'=>explode("_",$array_positions['position_compass'])[1],
            'order'=>$array_orders['compass_control'],
            'style'=>'background-color:'.$array_colors['compass_background'].';color:'.$array_colors['compass_color'].';',
            'style_hover'=>'background-color:'.$array_colors['compass_background_hover'].';color:'.$array_colors['compass_color_hover'].';'
        ],
        'icons'=>[
            'type'=>explode("_",$array_positions['position_icons'])[0],
            'position'=>explode("_",$array_positions['position_icons'])[1],
            'order'=>$array_orders['icons_control'],
            'style'=>'background-color:'.$array_colors['icons_background'].';color:'.$array_colors['icons_color'].';',
            'style_hover'=>'background-color:'.$array_colors['icons_background_hover'].';color:'.$array_colors['icons_color_hover'].';',
            'icon'=>$array_icons['icons']
        ],
        'autorotate'=>[
            'type'=>explode("_",$array_positions['position_autorotate'])[0],
            'position'=>explode("_",$array_positions['position_autorotate'])[1],
            'order'=>$array_orders['autorotate_control'],
            'style'=>'background-color:'.$array_colors['autorotate_background'].';color:'.$array_colors['autorotate_color'].';',
            'style_hover'=>'background-color:'.$array_colors['autorotate_background_hover'].';color:'.$array_colors['autorotate_color_hover'].';',
            'icon'=>$array_icons['autorotate']
        ],
        'orient'=>[
            'type'=>explode("_",$array_positions['position_orient'])[0],
            'position'=>explode("_",$array_positions['position_orient'])[1],
            'order'=>$array_orders['orient_control'],
            'style'=>'background-color:'.$array_colors['orient_background'].';color:'.$array_colors['orient_color'].';',
            'style_hover'=>'background-color:'.$array_colors['orient_background_hover'].';color:'.$array_colors['orient_color_hover'].';',
            'icon'=>$array_icons['orient']
        ],
        'annotations'=>[
            'type'=>explode("_",$array_positions['position_annotations'])[0],
            'position'=>explode("_",$array_positions['position_annotations'])[1],
            'order'=>$array_orders['annotations_control'],
            'style'=>'background-color:'.$array_colors['annotations_background'].';color:'.$array_colors['annotations_color'].';',
            'style_hover'=>'background-color:'.$array_colors['annotations_background_hover'].';color:'.$array_colors['annotations_color_hover'].';',
            'icon'=>$array_icons['annotations']
        ],
    ]
];
$ui_style = json_encode($ui_style);

if($id_preset!=null && $apply_preset==0) {
    if($id_preset==0) {
        $mysqli->query("INSERT INTO svt_editor_ui_presets(id_user,name,public,ui_style) VALUES($id_user,'$name_preset',$preset_public,'$ui_style');");
        $id_new_preset = $mysqli->insert_id;
    } else {
        $mysqli->query("UPDATE svt_editor_ui_presets SET name='$name_preset',public=$preset_public,ui_style='$ui_style' WHERE id=$id_preset;");
        $id_new_preset = 0;
    }
    ob_end_clean();
    echo json_encode(array("status"=>"ok","id_preset"=>$id_new_preset));
    exit;
}

if($apply_preset==1) {
    $query_p = "SELECT ui_style FROM svt_editor_ui_presets WHERE id=$id_preset LIMIT 1;";
    $result_p = $mysqli->query($query_p);
    if($result_p) {
        $row_p = $result_p->fetch_array(MYSQLI_ASSOC);
        $ui_style = $row_p['ui_style'];
    }
}

$query = "UPDATE svt_virtualtours SET font_viewer='$font_viewer',arrows_nav=$arrows_nav,voice_commands=$voice_commands,compass=$compass,auto_show_slider=$auto_show_slider,nav_slider=$nav_slider,show_custom=$show_custom,show_info=$show_info,show_gallery=$show_gallery,show_icons_toggle=$show_icons_toggle,show_autorotation_toggle=$show_autorotation_toggle,show_nav_control=$show_nav_control,show_presentation=$show_presentation,show_main_form=$show_main_form,show_share=$show_share,show_device_orientation=$show_device_orientation,drag_device_orientation=$drag_device_orientation,show_webvr=$show_webvr,show_audio=$show_audio,show_vt_title=$show_vt_title,show_fullscreen=$show_fullscreen,show_map=$show_map,show_map_tour=$show_map_tour,live_session=$live_session,show_annotations=$show_annotations,show_list_alt=$show_list_alt, fb_messenger=$fb_messenger, whatsapp_chat=$whatsapp_chat,meeting=$meeting,autoclose_menu=$autoclose_menu,autoclose_list_alt=$autoclose_list_alt,autoclose_slider=$autoclose_slider,autoclose_map=$autoclose_map,show_logo=$show_logo,ui_style='$ui_style',form_enable=$form_enable,form_content='$form_content',custom_content='$custom_content',markers_icon='$markers_icon',markers_id_icon_library=$markers_id_icon_library,markers_color='$markers_color',markers_background='$markers_background',markers_show_room=$markers_show_room,pois_icon='$pois_icon',pois_id_icon_library=$pois_id_icon_library,pois_color='$pois_color',pois_background='$pois_background',pois_style=$pois_style,show_dollhouse=$show_dollhouse WHERE id=$id_virtualtour;";
$result = $mysqli->query($query);

if($result) {
    ob_end_clean();
    echo json_encode(array("status"=>"ok"));
} else {
    ob_end_clean();
    echo json_encode(array("status"=>"error"));
}

