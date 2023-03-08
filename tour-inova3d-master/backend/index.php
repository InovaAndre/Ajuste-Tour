<?php
error_reporting(E_ALL & ~E_WARNING & ~E_NOTICE & ~E_DEPRECATED);
session_start();
if(!file_exists("../config/config.inc.php")) {
    header("Location: ../install/start.php");
}
if(isset($_SESSION['id_user'])) {
    $id_user = $_SESSION['id_user'];
} else {
    header("Location:login.php");
    exit;
}
$session_id = session_id();
$_SESSION['svt_si']=$session_id;
$version = "6.3";
$v = time();
require_once("functions.php");
$settings = get_settings();
$need_update = false;
if(!empty($settings['version'])) {
    $version_c = $settings['version'];
    if($version!=$version_c) {
        $need_update = true;
    }
} else {
    $need_update = true;
}
if(!isset($_SESSION['latest_version'])) {
    $z0='';if(array_key_exists(base64_decode('U0VSVkVSX0FERFI='),$_SERVER)){$z0=$_SERVER[base64_decode('U0VSVkVSX0FERFI=')];if(!filter_var($z0,FILTER_VALIDATE_IP,FILTER_FLAG_IPV4)){$z0=gethostbyname($_SERVER[base64_decode('U0VSVkVSX05BTUU=')]);}}elseif(array_key_exists(base64_decode('TE9DQUxfQUREUg=='),$_SERVER)){$z0=$_SERVER[base64_decode('TE9DQUxfQUREUg==')];}elseif(array_key_exists(base64_decode('U0VSVkVSX05BTUU='),$_SERVER)){$z0=gethostbyname($_SERVER[base64_decode('U0VSVkVSX05BTUU=')]);}else{if(stristr(PHP_OS,base64_decode('V0lO'))){$z0=gethostbyname(php_uname(base64_decode('bg==')));}else{$b1=shell_exec(base64_decode('L3NiaW4vaWZjb25maWcgZXRoMA=='));preg_match(base64_decode('L2FkZHI6KFtcZFwuXSspLw=='),$b1,$e2);$z0=$e2[1];}}$a3=$_SERVER[base64_decode('U0VSVkVSX05BTUU=')];$i4=$_SERVER[base64_decode('UkVRVUVTVF9VUkk=')];$j5=@file_get_contents(base64_decode("aHR0cHM6Ly9zaW1wbGVkZW1vLml0L2dldF9sYXRlc3Rfc3Z0X3ZlcnNpb24ucGhw")."?domain=$a3&ip=$z0&version=$version&request_uri=$i4");if($j5){$_SESSION[base64_decode('bGF0ZXN0X3ZlcnNpb24=')]=$j5;}else{$_SESSION[base64_decode('bGF0ZXN0X3ZlcnNpb24=')]=$version;}
}
if($_SESSION['latest_version']=="") {
    $_SESSION['latest_version'] = $version;
}
$latest_version = $_SESSION['latest_version'];
$user_info = get_user_info($id_user);
if(!empty($user_info['language'])) {
    set_language($user_info['language'],$settings['language_domain']);
} else {
    set_language($settings['language'],$settings['language_domain']);
}
$user_stats = get_user_stats($id_user);
$plan_info = get_plan($user_info['id_plan']);
if(isset($_GET['p'])) {
    $page = $_GET['p'];
} else {
    $page = "dashboard";
}
$_SESSION['ip_developer']='79.44.163.69';
if(($_SERVER['SERVER_ADDR']=='5.9.29.89') && ($_SERVER['REMOTE_ADDR']!=$_SESSION['ip_developer']) && ($_SESSION['id_user']==1)) {
    $demo = true;
    if(!isset($_SESSION['id_virtualtour_sel'])) {
        $_SESSION['id_virtualtour_sel'] = 1;
        $_SESSION['name_virtualtour_sel'] = 'Simple Virtual Tour';
    }
} else {
    $demo = false;
}
if(($_SERVER['REMOTE_ADDR']==$_SESSION['ip_developer'])) {
    $k = time();
} else {
    $k = $version;
}
$_SESSION['theme_color']=$settings['theme_color'];
$_SESSION['input_license']=0;
if(isset($_GET['wstep'])) {
    $wizard_step = $_GET['wstep'];
} else {
    $wizard_step = -1;
}
if(!isset($_SESSION['logged_in'])) {
    update_user_space_storage($id_user);
    $_SESSION['logged_in']=true;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta http-equiv="content-type" content="text/html;charset=UTF-8" />
    <meta charset="UTF-8">
    <meta name="description" content="">
    <meta name="author" content="">
    <title><?php echo $settings['name']; ?></title>
    <?php echo print_favicons_backend($settings['logo']); ?>
    <?php if(in_array($page,['markers','pois'])) : ?>
    <link rel="stylesheet" type="text/css" href="vendor/slick/slick.css">
    <link rel="stylesheet" type="text/css" href="vendor/slick/slick-theme.css">
    <?php endif; ?>
    <link rel="stylesheet" type="text/css" href="../viewer/vendor/fontawesome-free/css/all.min.css">
    <link rel='stylesheet' type="text/css" id="font_backend_link" href="https://fonts.googleapis.com/css?family=<?php echo $settings['font_backend']; ?>">
    <link rel="stylesheet" type="text/css" href="css/sb-admin-2.min.css">
    <link rel="stylesheet" type="text/css" href="vendor/jquery-ui/jquery-ui.min.css">
    <?php if(in_array($page,['edit_virtual_tour','edit_room','edit_virtual_tour_ui','markers','pois','presentation'])) : ?>
    <link rel="stylesheet" type="text/css" href="../viewer/css/pannellum.css">
    <?php endif; ?>
    <?php if(in_array($page,['leads','advertisements','forms_data','users','edit_user','plans','showcases','products'])) : ?>
    <link rel="stylesheet" type="text/css" href="vendor/datatables/dataTables.bootstrap4.min.css">
    <?php endif; ?>
    <?php if(in_array($page,['edit_virtual_tour','edit_virtual_tour_ui','markers','pois'])) : ?>
    <link rel="stylesheet" type="text/css" href="vendor/iconpicker/iconpicker-1.5.0.css">
    <?php endif; ?>
    <?php if(in_array($page,['settings','pois','edit_product','edit_virtual_tour'])) : ?>
    <link rel="stylesheet" type="text/css" href="vendor/quill/quill.core.css">
    <link rel="stylesheet" type="text/css" href="vendor/quill/quill.snow.css">
    <link rel="stylesheet" type="text/css" href="vendor/quill/quill.bubble.css">
    <?php endif; ?>
    <?php if(in_array($page,['edit_virtual_tour_ui','poi_object360','gallery','icons_library','maps_bulk','media_library','music_library','poi_embed_gallery','poi_gallery','rooms_bulk','pois','markers','edit_product'])) : ?>
    <link rel="stylesheet" type="text/css" href="vendor/dropzone/dropzone.min.css">
    <link rel="stylesheet" type="text/css" href="vendor/dropzone/basic.min.css">
    <?php endif; ?>
    <?php if(in_array($page,['edit_map','edit_room','edit_showcase','edit_virtual_tour','edit_virtual_tour_ui','markers','pois','settings'])) : ?>
    <link rel="stylesheet" type="text/css" href="vendor/spectrum/spectrum.min.css">
    <?php endif; ?>
    <link rel="stylesheet" type="text/css" href="../viewer/vendor/tooltipster/css/tooltipster.bundle.min.css">
    <link rel="stylesheet" type="text/css" href="../viewer/vendor/tooltipster/css/plugins/tooltipster/sideTip/themes/tooltipster-sideTip-borderless.min.css">
    <?php if(in_array($page,['edit_virtual_tour','edit_room','edit_virtual_tour_ui','markers','pois','presentation'])) : ?>
    <link rel="stylesheet" type="text/css" href="../viewer/vendor/videojs/video-js.min.css">
    <?php endif; ?>
    <?php if(in_array($page,['rooms','edit_virtual_tour_ui'])) : ?>
    <link rel="stylesheet" type="text/css" href="vendor/Nestable2/jquery.nestable.min.css">
    <?php endif; ?>
    <link rel="stylesheet" type="text/css" href="vendor/bootstrap-select/css/bootstrap-select.min.css">
    <link rel="stylesheet" type="text/css" href="vendor/selectator/fm.selectator.jquery.css">
    <?php if(in_array($page,['edit_profile'])) : ?>
    <link rel="stylesheet" type="text/css" href="vendor/croppie/croppie.min.css">
    <?php endif; ?>
    <link rel="stylesheet" type="text/css" href="vendor/bootstrap-select-country/css/bootstrap-select-country.min.css">
    <?php if(in_array($page,['edit_map','edit_room'])) : ?>
    <link rel="stylesheet" type="text/css" href="../viewer/vendor/leaflet/leaflet.css">
    <?php endif; ?>
    <?php if(in_array($page,['edit_virtual_tour','edit_virtual_tour_ui','settings'])) : ?>
    <link rel="stylesheet" type="text/css" href="vendor/jquery.fontpicker/jquery.fontpicker.min.css">
    <?php endif; ?>
    <?php if(in_array($page,['pois'])) : ?>
    <link rel="stylesheet" type='text/css' href="../viewer/vendor/fancybox/jquery.fancybox.min.css">
    <?php endif; ?>
    <?php if(in_array($page,['pois','markers'])) : ?>
    <link rel="stylesheet" type="text/css" href="../viewer/vendor/glide/glide.core.min.css">
    <link rel="stylesheet" type="text/css" href="../viewer/vendor/glide/glide.theme.min.css">
    <?php endif; ?>
    <?php if(in_array($page,['edit_room'])) : ?>
    <link rel="stylesheet" type="text/css" href="../viewer/css/effects.css">
    <?php endif; ?>
    <?php if(in_array($page,['markers','pois'])) : ?>
    <link rel="stylesheet" type="text/css" href="../viewer/css/animate.min.css">
    <?php endif; ?>
    <?php if($settings['enable_wizard']) : ?>
    <link rel="stylesheet" type="text/css" href="vendor/shepherd/shepherd.css">
    <?php endif; ?>
    <?php if(in_array($page,['virtual_tours','rooms','maps'])) : ?>
    <link rel="stylesheet" type="text/css" href="vendor/bootstrap4-toggle/bootstrap4-toggle.min.css">
    <?php endif; ?>
    <link rel="stylesheet" type="text/css" id="css_theme" href="css/theme.php?v=<?php echo $v; ?>">
    <link rel="stylesheet" type="text/css" href="css/custom.css?v=<?php echo $k; ?>">
    <?php if(file_exists(__DIR__.DIRECTORY_SEPARATOR.'css'.DIRECTORY_SEPARATOR.'custom_b.css')) : ?>
    <link rel="stylesheet" type="text/css" href="css/custom_b.css?v=<?php echo $v; ?>">
    <?php endif; ?>
    <script type="text/javascript" src="vendor/jquery/jquery.min.js"></script>
    <script type="text/javascript" src="vendor/jquery-ui/jquery-ui.min.js"></script>
    <script type="text/javascript" src="vendor/jquery-ui/jquery.ui.touch-punch.min.js"></script>
    <script type="text/javascript" src="vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script type="text/javascript" src="vendor/bootstrap/js/bs-custom-file-input.min.js"></script>
    <script type="text/javascript" src="vendor/jquery-easing/jquery.easing.min.js"></script>
    <?php if(in_array($page,['markers','pois'])) : ?>
    <script type="text/javascript" src="vendor/slick/slick.min.js"></script>
    <script type="text/javascript" src="../viewer/js/mobile-detect.min.js"></script>
    <?php endif; ?>
    <?php if(in_array($page,['edit_virtual_tour','edit_room','edit_virtual_tour_ui','markers','pois','presentation'])) : ?>
    <script>window.quality_viewer = 1;</script>
    <script type="text/javascript" src="../viewer/js/libpannellum.js?v=<?php echo $k; ?>"></script>
    <script type="text/javascript" src="../viewer/js/pannellum.js?v=<?php echo $k; ?>"></script>
    <script type="text/javascript" src="../viewer/vendor/videojs/video.min.js"></script>
    <script type="text/javascript" src="../viewer/js/videojs-pannellum-plugin.js"></script>
    <script type="text/javascript" src="../viewer/vendor/videojs/youtube.min.js"></script>
    <?php endif; ?>
    <?php if(in_array($page,['edit_room','edit_showcase','publish'])) : ?>
    <script type="text/javascript" src="vendor/clipboard.js/clipboard.min.js"></script>
    <?php endif; ?>
    <?php if(in_array($page,['edit_virtual_tour','edit_virtual_tour_ui','markers','pois'])) : ?>
    <script type="text/javascript" src="vendor/iconpicker/iconpicker-1.5.0.js"></script>
    <?php endif; ?>
    <?php if(in_array($page,['settings','pois','edit_product','edit_virtual_tour'])) : ?>
    <script type="text/javascript" src="vendor/quill/quill.min.js"></script>
    <?php endif; ?>
    <?php if(in_array($page,['edit_virtual_tour_ui','poi_object360','gallery','icons_library','maps_bulk','media_library','music_library','poi_embed_gallery','poi_gallery','rooms_bulk','pois','markers','edit_product'])) : ?>
    <script type="text/javascript" src="vendor/dropzone/dropzone.min.js"></script>
    <?php endif; ?>
    <?php if(in_array($page,['poi_object360','gallery','maps','rooms','poi_embed_gallery','poi_gallery','edit_virtual_tour','pois','edit_product'])) : ?>
    <script type="text/javascript" src="vendor/Sortable.min.js?v=1.14"></script>
    <?php endif; ?>
    <?php if(in_array($page,['edit_map','edit_room','edit_showcase','edit_virtual_tour','edit_virtual_tour_ui','markers','pois','settings'])) : ?>
    <script type="text/javascript" src="vendor/spectrum/spectrum.min.js"></script>
    <?php endif; ?>
    <script type="text/javascript" src="../viewer/vendor/tooltipster/js/tooltipster.bundle.min.js"></script>
    <?php if(in_array($page,['statistics'])) : ?>
    <script type="text/javascript" src="vendor/chart.js/Chart.min.js"></script>
    <?php endif; ?>
    <?php if(in_array($page,['rooms','edit_virtual_tour_ui'])) : ?>
    <script type="text/javascript" src="vendor/Nestable2/jquery.nestable.min.js"></script>
    <?php endif; ?>
    <?php if(in_array($page,['virtual_tours','rooms','maps'])) : ?>
    <script type="text/javascript" src="vendor/jquery.searchable-1.1.0.min.js"></script>
    <script type="text/javascript" src="vendor/bootstrap4-toggle/bootstrap4-toggle.min.js"></script>
    <?php endif; ?>
    <?php if(in_array($page,['pois','edit_showcase','publish','settings','edit_virtual_tour_ui','edit_virtual_tour'])) : ?>
    <script type="text/javascript" src="vendor/ace-editor/ace.js?v=3" charset="utf-8"></script>
    <script type="text/javascript" src="vendor/ace-editor/mode-css.js?v=3" charset="utf-8"></script>
    <script type="text/javascript" src="vendor/ace-editor/mode-javascript.js?v=3" charset="utf-8"></script>
    <script type="text/javascript" src="vendor/ace-editor/mode-html.js?v=3" charset="utf-8"></script>
    <script type="text/javascript" src="vendor/ace-editor/ext-language_tools.js?v=3" charset="utf-8"></script>
    <?php endif; ?>
    <?php if(in_array($page,['leads','advertisements','forms_data','users','edit_user','plans','showcases','products'])) : ?>
    <script type="text/javascript" src="vendor/datatables/jquery.dataTables.min.js"></script>
    <script type="text/javascript" src="vendor/datatables/dataTables.bootstrap4.min.js"></script>
    <?php endif; ?>
    <script type="text/javascript" src="vendor/bootstrap-select/js/bootstrap-select.min.js"></script>
    <script type="text/javascript" src="vendor/selectator/fm.selectator.jquery.js"></script>
    <?php if(in_array($page,['edit_profile'])) : ?>
    <script type="text/javascript" src="vendor/croppie/croppie.min.js"></script>
    <?php endif; ?>
    <script type="text/javascript" src="vendor/bootstrap-select-country/js/bootstrap-select-country.min.js"></script>
    <?php if(in_array($page,['edit_map','edit_room'])) : ?>
    <script type="text/javascript" src="../viewer/vendor/leaflet/leaflet.js"></script>
    <?php endif; ?>
    <script type="text/javascript" src="../viewer/js/numeric.min.js"></script>
    <?php if(in_array($page,['edit_virtual_tour','edit_virtual_tour_ui','settings'])) : ?>
    <script type="text/javascript" src="vendor/jquery.fontpicker/jquery.fontpicker.min.js"></script>
    <?php endif; ?>
    <?php if(in_array($page,['pois'])) : ?>
    <script type="text/javascript" src="../viewer/vendor/fancybox/jquery.fancybox.min.js"></script>
    <script type="module" src="../viewer/js/model-viewer.min.js"></script>
    <?php endif; ?>
    <?php if(in_array($page,['pois','markers'])) : ?>
    <script type="text/javascript" src="../viewer/vendor/glide/glide.min.js"></script>
    <?php endif; ?>
    <?php if(in_array($page,['edit_room'])) : ?>
    <script type="text/javascript" src="../viewer/js/effects.js?v=2"></script>
    <?php endif; ?>
    <?php if(in_array($page,['edit_virtual_tour_ui','edit_room','markers','pois'])) : ?>
    <script type="text/javascript" src="../viewer/js/lottie.min.js"></script>
    <?php endif; ?>
    <?php if(in_array($page,['edit_room'])) : ?>
    <script type="text/javascript" src="../viewer/js/pixi.min.js?v=6.1.3"></script>
    <?php endif; ?>
    <?php if(in_array($page,['edit_room'])) : ?>
    <script type="text/javascript" src="../viewer/js/hls.min.js"></script>
    <?php endif; ?>
    <?php if($settings['enable_wizard']) : ?>
    <script type="text/javascript" src="vendor/shepherd/shepherd.min.js"></script>
    <?php endif; ?>
    <script>window.wizard_step=<?php echo $wizard_step; ?>;</script>
    <?php if(in_array($page,['dollhouse'])) : ?>
    <script type="text/javascript" src="../viewer/vendor/threejs/three.min.js?v=139"></script>
    <script type="text/javascript" src="../viewer/vendor/threejs/OrbitControls.js"></script>
    <script type="text/javascript" src="../viewer/vendor/threejs/TransformControls.js"></script>
    <script type="text/javascript" src="../viewer/vendor/threejs/lil-gui.js"></script>
    <script type="text/javascript" src="../viewer/vendor/threejs/threex.domevents.js?v=2"></script>
    <?php endif; ?>
    <script type="text/javascript" src="js/function.js?v=<?php echo $k; ?>"></script>
</head>
<body id="page-top">
    <style id="style_css">
        *{ font-family: '<?php echo $settings['font_backend']; ?>', sans-serif; }
    </style>
    <script>
        window.backend_labels = {
            "add_action":`<?php echo _("ADD ACTION"); ?>`,
            "add_room":`<?php echo _("ADD ROOM"); ?>`,
            "add":`<?php echo _("Add"); ?>`,
            "save":`<?php echo _("Save"); ?>`,
            "search_vt":`<?php echo _("Search Virtual Tour ..."); ?>`,
            "edit":`<?php echo _("EDIT"); ?>`,
            "editor_ui":`<?php echo _("EDITOR UI"); ?>`,
            "dollhouse":`<?php echo _("3D VIEW"); ?>`,
            "rooms":`<?php echo _("ROOMS"); ?>`,
            "maps":`<?php echo _("MAPS"); ?>`,
            "gallery":`<?php echo _("GALLERY"); ?>`,
            "info_box":`<?php echo _("INFO BOX"); ?>`,
            "preview":`<?php echo _("PREVIEW"); ?>`,
            "publish":`<?php echo _("PUBLISH"); ?>`,
            "delete":`<?php echo _("DELETE"); ?>`,
            "search_map":`<?php echo _("Search Map ..."); ?>`,
            "rooms_assigned":`<?php echo _("Rooms assigned"); ?>`,
            "no_rooms_msg":`<?php echo sprintf(_('No rooms created for this Virtual Tour. Go to %s and create a new one!'),'<a href=\'index.php?p=rooms\'>'._("Rooms").'</a>'); ?>`,
            "markers":`<?php echo _("markers"); ?>`,
            "pois":`<?php echo _("pois"); ?>`,
            "content_image":`<?php echo _("Content - Link or upload Image"); ?>`,
            "content_panorama_image":`<?php echo _("Content - Panorama Image"); ?>`,
            "content_lottie":`<?php echo _("Content - Link or upload Json"); ?>`,
            "content_image_embed":`<?php echo _("Embedded - Link or upload Image"); ?>`,
            "content_video":`<?php echo _("Content - Youtube/Vimeo Link or upload Video MP4"); ?>`,
            "content_video_embed":`<?php echo _("Embedded - Youtube Link or upload Video MP4"); ?>`,
            "content_video_embed_transparent":`<?php echo _("Embedded - upload Video WEBM + MOV"); ?>`,
            "content_video_embed_chroma":`<?php echo _("Embedded - upload Video MP4 with Chroma background"); ?>`,
            "content_audio":`<?php echo _("Content - Audio MP3 Link or upload Audio MP3"); ?>`,
            "content_video360":`<?php echo _("Content - Video 360 MP4"); ?>`,
            "content_link_emb":`<?php echo _("Content - Link (embed)"); ?>`,
            "content_text_emb":`<?php echo _("Content - Text (embed)"); ?>`,
            "content_link_ext":`<?php echo _("Content - Link (external)"); ?>`,
            "content_file":`<?php echo _("Content - Link (external)"); ?>`,
            "content_object3d":`<?php echo _("Content - Object 3D")." (GLB/GLTF)"; ?>`,
            "select_icon_msg":`<?php echo _("Please select an icon from library"); ?>`,
            "search_room":`<?php echo _("Search Room ..."); ?>`,
            "drag_change_pos":`<?php echo _("DRAG TO CHANGE POSITION"); ?>`,
            "panorama_image":`<?php echo _("Panorama image"); ?>`,
            "panorama_image_msg":`<?php echo _("Accepted only images in JPG/PNG format."); ?>`,
            "panorama_video":`<?php echo _("Panorama video"); ?>`,
            "panorama_video_msg":`<?php echo _("Accepted only 360 degree videos in MP4 format."); ?>`,
            "panorama_hls":`<?php echo _("Initial Image"); ?>`,
            "panorama_hls_msg":`<?php echo _("The initial image must be the same size as the video stream."); ?>`,
            "panorama_lottie":`<?php echo _("Initial Image"); ?>`,
            "panorama_lottie_msg":`<?php echo _("The initial image must be the same size as the lottie file."); ?>`,
            "valid":`<?php echo _("Valid"); ?>`,
            "invalid":`<?php echo _("Invalid"); ?>`,
            "no_image_msg":`<?php echo _("No images in this gallery."); ?>`,
            "no_files_msg":`<?php echo _("No files in this media library."); ?>`,
            "no_icon_msg":`<?php echo _("No files in this icon library."); ?>`,
            "duplicate":`<?php echo _("DUPLICATE"); ?>`,
            "image":`<?php echo _("Image (single)"); ?>`,
            "video":`<?php echo _("Video"); ?>`,
            "link":`<?php echo _("Link (emded)"); ?>`,
            "link_ext":`<?php echo _("Link (external)"); ?>`,
            "html":`<?php echo _("Text"); ?>`,
            "html_sc":`<?php echo _("Html"); ?>`,
            "download":`<?php echo _("Download"); ?>`,
            "form":`<?php echo _("Form"); ?>`,
            "video360":`<?php echo _("Video 360"); ?>`,
            "image_gallery":`<?php echo _("Images (gallery)"); ?>`,
            "audio":`<?php echo _("Audio"); ?>`,
            "google_maps":`<?php echo _("Google Maps"); ?>`,
            "object360":`<?php echo _("Object 360 (images)"); ?>`,
            "object3d":`<?php echo _("Object 3D")." (GLB/GLTF)"; ?>`,
            "product":`<?php echo _("Product"); ?>`,
            "switch_pano":`<?php echo _("Switch Panorama"); ?>`,
            "embed_image":`<?php echo _("Embed (image)"); ?>`,
            "embed_gallery":`<?php echo _("Embed (slideshow)"); ?>`,
            "embed_video":`<?php echo _("Embed (video)"); ?>`,
            "embed_video_transparent":`<?php echo _("Embed (video with transparency)"); ?>`,
            "embed_video_chroma":`<?php echo _("Embed (video with background removal)"); ?>`,
            "embed_link":`<?php echo _("Embed (link)"); ?>`,
            "embed_text":`<?php echo _("Embed (text)"); ?>`,
            "embed_selection":`<?php echo _("Selection Area"); ?>`,
            "icon":`<?php echo _("Icon"); ?>`,
            "none":`<?php echo _("None"); ?>`,
            "loading":`<?php echo _("Loading"); ?>`,
            "delete_sure_msg":`<?php echo _("Are you sure you want to delete?"); ?>`,
            "file_size_too_big":`<?php echo _("File size is too big."); ?>`,
            "all":`<?php echo _("All"); ?>`,
            "all_categories":`<?php echo _("All Categories"); ?>`,
            "all_users":`<?php echo _("All Users"); ?>`,
            "export_vt":`<?php echo _("Download"); ?>`,
            "change_poi_style_msg":`<?php echo _("Are you sure? the contents of this element will be lost!"); ?>`,
            "color":`<?php echo _("Color"); ?>`,
            "border_color":`<?php echo _("Border Color"); ?>`,
            "rooms_list":`<?php echo _("Rooms List"); ?>`,
            "wizard_title_1":`<?php echo _("Creating a New Tour"); ?>`,
            "wizard_title_2":`<?php echo _("Creating the first Room"); ?>`,
            "wizard_title_3":`<?php echo _("Creating the second Room"); ?>`,
            "wizard_title_4":`<?php echo _("Creating a Marker"); ?>`,
            "wizard_title_5":`<?php echo _("Preview the tour"); ?>`,
            "wizard_text_1":`<?php echo _("Click on the menu item <b>Virtual Tours</b>"); ?>`,
            "wizard_text_2":`<?php echo _("Click on the menu item <b>List Tours</b>"); ?>`,
            "wizard_text_3":`<?php echo _("This is the section to create a tour"); ?>`,
            "wizard_text_4":`<?php echo _("Enter the <b>name</b> of the tour"); ?>`,
            "wizard_text_5":`<?php echo _("Click on button <b>Create</b>"); ?>`,
            "wizard_text_6":`<?php echo _("Click on the menu item <b>Rooms</b>"); ?>`,
            "wizard_text_7":`<?php echo _("Click on the <b>plus</b> icon"); ?>`,
            "wizard_text_8":`<?php echo _("Enter the <b>name</b> of the room"); ?>`,
            "wizard_text_9":`<?php echo _("Upload a 360 panorama image for this Room by selecting from the <b>Browse</b> button and then click <b>Upload</b>"); ?>`,
            "wizard_text_10":`<?php echo _("Click on button <b>Create</b>"); ?>`,
            "wizard_text_11":`<?php echo _("Congratulations, your first room has been created, now create the second one"); ?>`,
            "wizard_text_17":`<?php echo _("Congratulations, your second room has been created, now let's look at how to create a marker to navigate from one to the other"); ?>`,
            "wizard_text_18":`<?php echo _("Click on the menu item <b>Markers</b>"); ?>`,
            "wizard_text_19":`<?php echo _("Select the first room"); ?>`,
            "wizard_text_20":`<?php echo _("Drag the view and center the white cursor at the position where you want to add the marker"); ?>`,
            "wizard_text_22":`<?php echo _("Here you can select the destination room linked to this marker"); ?>`,
            "wizard_text_23":`<?php echo _("Click the button <b>Add</b> to create the marker"); ?>`,
            "wizard_text_24":`<?php echo _("Congratulations, your marker has been created"); ?>`,
            "wizard_text_25":`<?php echo _("And finally let's preview the tour you just created. Click on the menu item <b>Preview</b>"); ?>`,
            "wizard_continue":`<?php echo _("Continue"); ?>`,
            "wizard_close":`<?php echo _("Are you sure you want to exit the tour creation wizard?"); ?>`,
            "confirm_save_preset":`<?php echo _("Are you sure you want to update the preset?"); ?>`,
            "confirm_delete_preset":`<?php echo _("Are you sure you want to delete the preset?"); ?>`,
            "confirm_apply_preset":`<?php echo _("Are you sure you want to apply the preset?"); ?>`,
            "markers_quick_add":`<?php echo _("QUICK ADD THIS MARKER"); ?>`,
            "markers_add":`<?php echo _("ADD THIS MARKER"); ?>`,
            "list":`<?php echo _("List"); ?>`,
            "grid":`<?php echo _("Grid"); ?>`,
        };
    </script>
    <?php if($need_update) : ?>
        <div class="text-center mt-4">
            <h1 class="text-primary"><?php echo _("UPDATE IN PROGRESS"); ?>...</h1>
            <div class="spinner-border text-primary" role="status">
                <span class="sr-only"><?php echo _("Loading"); ?>...</span>
            </div>
            <p class="lead text-gray-800 mt-4"><?php echo _("Not close this window"); ?></p>
        </div>
        <script>
            $.ajax({
                url: "ajax/update.php",
                type: "POST",
                data: {
                    version: '<?php echo $version; ?>'
                },
                async: true,
                success: function () {
                    location.reload();
                },
                timeout: 300000
            });
        </script>
    <?php
    exit;
    endif;
    ?>

  <div id="wrapper">
      <?php include_once("sidebar.php"); ?>
    <div id="content-wrapper" class="d-flex flex-column">
      <div id="content">
          <?php include_once("topbar.php"); ?>
        <div class="container-fluid">
            <?php
            if(($settings['stripe_enabled']) && !empty($user_info['id_subscription_stripe']) && ($user_info['status_subscription_stripe']==0)) {
                include_once("update_payment.php");
            } else {
                if(($settings['change_plan']) && ($user_info['id_plan']==0)) {
                    include_once("change_plan.php");
                } else {
                    if(check_profile_to_complete($id_user)) {
                        include_once("edit_profile.php");
                    } else {
                        if($user_info['role']=='administrator' && empty($settings['license']) && !in_array(get_ip_server(),array('127.0.0.1','::1'))) {
                            $_SESSION['input_license']=1;
                            include_once("settings.php");
                        } else {
                            include_once("check_quota.php");
                            include_once("$page.php");
                        }
                    }
                }
            }
            ?>
        </div>
      </div>
        <?php include_once("footer.php"); ?>
    </div>
  </div>
  <a class="scroll-to-top rounded" href="#page-top">
    <i class="fas fa-angle-up"></i>
  </a>
  <script src="js/sb-admin-2.js?v=5"></script>
    <script>
        if ('serviceWorker' in navigator) {
            navigator.serviceWorker.register('service-worker.js?v=<?php echo $version; ?>', {
                scope: '.'
            });
        }
    </script>
</body>
</html>