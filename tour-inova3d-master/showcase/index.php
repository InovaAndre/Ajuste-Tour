<?php
header('Access-Control-Allow-Origin: *');
error_reporting(E_ALL & ~E_WARNING & ~E_NOTICE & ~E_DEPRECATED);
session_start();
require_once("../db/connection.php");

$v = time();
$array_vt = array();
$array_cat = array();
$header_html = '';
$footer_html = '';
if((isset($_GET['furl'])) || (isset($_GET['code']))) {
    if (isset($_GET['furl'])) {
        $furl = str_replace("'","\'",$_GET['furl']);
        $query = "SELECT id,code,name,banner,logo,bg_color,header_html,footer_html FROM svt_showcases WHERE (friendly_url='$furl' OR code='$furl');";
    }
    if (isset($_GET['code'])) {
        $code = $_GET['code'];
        $query = "SELECT id,code,name,banner,logo,bg_color,header_html,footer_html FROM svt_showcases WHERE code='$code';";
    }
    $result = $mysqli->query($query);
    if ($result) {
        if ($result->num_rows==1) {
            $row = $result->fetch_array(MYSQLI_ASSOC);
            $id_s = $row['id'];
            $code = $row['code'];
            $name_s = strtoupper($row['name']);
            $banner_s = $row['banner'];
            $logo_s = $row['logo'];
            $bg_color_s = $row['bg_color'];
            $header_html = $row['header_html'];
            $footer_html = $row['footer_html'];
            $query_list = "SELECT v.id,s.type_viewer,v.code,v.author,v.name as title,v.description,v.background_image as image,r.panorama_image,c.id as id_category,c.name as name_category,COUNT(al.id) as total_access
                            FROM svt_showcase_list as s
                            JOIN svt_virtualtours as v ON s.id_virtualtour=v.id
                            LEFT JOIN svt_categories as c ON c.id=v.id_category
                            LEFT JOIN svt_rooms as r ON r.id_virtualtour=v.id AND r.id=(SELECT id FROM svt_rooms WHERE id_virtualtour=v.id ORDER BY priority LIMIT 1)
                            LEFT JOIN svt_access_log as al ON al.id_virtualtour=v.id
                            WHERE s.id_showcase=$id_s AND v.active=1
                            GROUP BY v.id,s.type_viewer,v.code,v.author,v.name,v.description,v.background_image,r.panorama_image,c.id,c.name;";
            $result_list = $mysqli->query($query_list);
            if($result_list) {
                if($result_list->num_rows>0) {
                    while($row_list = $result_list->fetch_array(MYSQLI_ASSOC)) {
                        if(empty($row_list['image'])) {
                            if(!empty($row_list['panorama_image'])) {
                                $row_list['image']='../viewer/panoramas/preview/'.$row_list['panorama_image'];
                            }
                        } else {
                            $row_list['image']='../viewer/content/'.$row_list['image'];
                        }
                        if(!empty($row_list['id_category'])) {
                            $category = $row_list['id_category']."|".$row_list['name_category'];
                            if(!in_array($category,$array_cat)) {
                                array_push($array_cat,$category);
                            }
                        }
                        $array_vt[] = $row_list;
                    }
                }
            } else {
                die("Invalid Link");
            }
        } else {
            die("Invalid Link");
        }
    } else {
        die("Invalid Link");
    }
} else {
    die("Invalid Link");
}

?>
<!DOCTYPE HTML>
<html>
<head>
    <title><?php echo $name_s; ?></title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, user-scalable=no, maximum-scale=1, minimum-scale=1">
    <meta property="og:title" content="<?php echo $name_s; ?>">
    <?php echo print_favicons_showcase($code,$logo_s); ?>
    <link rel="preconnect" href="https://fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@100;300;400;500;600;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" type='text/css' href="../viewer/vendor/fontawesome-free/css/all.min.css">
    <link rel="stylesheet" type="text/css" href="css/bootstrap.min.css">
    <link rel="stylesheet" type="text/css" href="css/index.css?v=<?php echo $v; ?>">
    <?php if(file_exists(__DIR__.DIRECTORY_SEPARATOR.'css'.DIRECTORY_SEPARATOR.'custom_'.$code.'.css')) : ?>
        <link rel="stylesheet" type="text/css" href="css/custom_<?php echo $code; ?>.css?v=<?php echo $v; ?>">
    <?php endif; ?>
    <script type="text/javascript" src="js/jquery-3.4.1.min.js"></script>
    <script type="text/javascript" src="js/bootstrap.bundle.min.js"></script>
</head>
<body style="background: <?php echo $bg_color_s; ?>">
<style>
    :root {
        --bg_color: <?php echo $bg_color_s; ?>;
    }
    .header:before {
        <?php if(!empty($banner_s)) : ?>
        background-image: url('../viewer/content/<?php echo $banner_s; ?>');
        <?php endif; ?>
    }
    .frame_banner:before {
    <?php if(!empty($banner_s)) : ?>
        background-image: url('../viewer/content/<?php echo $banner_s; ?>');
    <?php endif; ?>
    }
</style>
<div class="showcase noselect">
    <div class="header">
        <div class="info">
            <h1><?php echo $name_s; ?></h1>
            <?php if(!empty($logo_s)) : ?>
                <div class="logo">
                    <img src="../viewer/content/<?php echo $logo_s; ?>" />
                </div>
            <?php endif; ?>
        </div>
    </div>
    <div class="custom_header <?php echo (empty($header_html)) ? 'd-none' : ''; ?>">
        <?php echo html_entity_decode($header_html); ?>
    </div>
    <?php
    if(count($array_cat)>1) {
        echo "<div class='categories'>";
        foreach ($array_cat as $category) {
            $res = explode("|",$category);
            $id_cat = $res[0];
            $name_cat = $res[1];
            echo "<button id='btn_cat_$id_cat' onclick='filter_cat($id_cat);' class='btn bg-light text-dark border border-secondary'>$name_cat</button>";
        }
        echo "</div>";
    }
    ?>
    <section>
        <div class="container">
            <div class="d-flex flex-row flex-wrap">
                <?php foreach ($array_vt as $vt) { ?>
                    <div class="col-xl-3 col-lg-4 col-sm-6 col-xs-12">
                        <div data-image="<?php echo $vt['image']; ?>" data-type="<?php echo $vt['type_viewer']; ?>" data-category="<?php echo $vt['id_category']; ?>" data-code="<?php echo $vt['code']; ?>" class="card vt-card">
                            <div class="card-img-block">
                                <div class="overlay"></div>
                                <i class="fas fa-play-circle"></i>
                                <?php if(empty($vt['image'])) { ?>
                                    <div style="height: 180px;background-color: darkgrey" class="card-img-top"></div>
                                <?php } else { ?>
                                    <img class="card-img-top" src="<?php echo $vt['image']; ?>" alt="card image">
                                <?php } ?>
                                <div class="card-access"><i class="far fa-eye"></i> <?php echo $vt['total_access']; ?></div>
                            </div>
                            <div class="card-body pt-0">
                                <h5 class="card-title"><?php echo strtoupper($vt['title']); ?></h5>
                                <p class="card-author"><?php echo $vt['author']; ?></p>
                                <p class="card-text"><?php echo $vt['description']; ?></p>
                            </div>
                        </div>
                    </div>
                <?php } ?>
            </div>
        </div>
    </section>
    <div class="custom_footer <?php echo (empty($footer_html)) ? 'd-none' : ''; ?>">
        <?php echo html_entity_decode($footer_html); ?>
    </div>
</div>
<div class="vt_viewer">
    <i class="fa fa-spin fa-circle-notch loading_icon"></i>
    <div class="frame_banner noselect">
        <?php if(!empty($logo_s)) : ?>
            <img src="../viewer/content/<?php echo $logo_s; ?>" />
        <?php endif; ?>
        <span><?php echo $name_s; ?></span>
        <i onclick="show_showcase()" class="fas fa-arrow-circle-left"></i>
    </div>
    <iframe referrerpolicy="origin" allow="gyroscope; accelerometer; xr; microphone *" allowfullscreen frameborder="0" scrolling="no" marginheight="0" marginwidth="0" src=""></iframe>
</div>
<div class="ripple-wrap"><div class="ripple"><i class="fa fa-spin fa-circle-notch"></i></div></div>
<script>
    if ('serviceWorker' in navigator) {
        navigator.serviceWorker.register('service-worker.js', {
            scope: '.'
        });
    }
</script>
<script>
    $(document).ready(function() {
        var height_footer = $('.custom_footer ').outerHeight();
        $('section').css('margin-bottom',height_footer+'px');
        var ripple_wrap = $('.ripple-wrap'), rippler = $('.ripple'), finish = false, vt_code='', type='viewer', image_sel='',
        monitor = function(el) {
            var computed = window.getComputedStyle(el, null),
                borderwidth = parseFloat(computed.getPropertyValue('border-left-width'));
            if (!finish && borderwidth >= 1500) {
                el.style.WebkitAnimationPlayState = "paused";
                el.style.animationPlayState = "paused";
            }
            if (finish) {
                el.style.WebkitAnimationPlayState = "running";
                el.style.animationPlayState = "running";
                return;
            } else {
                window.requestAnimationFrame(function() {monitor(el)});
            }
        };
        rippler.bind("webkitAnimationEnd oAnimationEnd msAnimationEnd mozAnimationEnd animationend", function(e){
            $('.ripple i').hide();
            ripple_wrap.removeClass('goripple');
        });
        $('body').on('click', '.vt-card', function(e) {
            vt_code = $(this).attr('data-code');
            type = $(this).attr('data-type');
            image_sel = $(this).attr('data-image');
            $('body').css('overflow-y','hidden');
            $('.vt_viewer').css('height','100vh');
            $('.ripple i').show();
            rippler.css('left', e.clientX + 'px');
            rippler.css('top', e.clientY + 'px');
            e.preventDefault();
            finish = false;
            ripple_wrap.addClass('goripple');
            setTimeout(function () {
                swapContent();
            },1000);
            window.requestAnimationFrame(function() {monitor(rippler[0])});
        });
        function swapContent() {
            $('.vt_viewer iframe').attr('src','../'+type+'/index.php?code='+vt_code);
            switch(type) {
                case 'viewer':
                    $('.vt_viewer iframe').attr('scrolling','no');
                    break;
                case 'landing':
                    $('.vt_viewer iframe').attr('scrolling','yes');
                    break;
            }
            $('.vt_viewer').show();
            $('.showcase').hide();
            if(!image_sel.includes('preview')) {
                $('.vt_viewer').css('background-image','url('+image_sel+')');
            } else {
                $('.vt_viewer').css('background-image','none');
            }
            $('.ripple i').fadeOut(500);
            setTimeout(function() {
                finish = true;
            },500);
        }
    });
    var show_showcase = function() {
        $('.vt_viewer').fadeOut(function () {
            $('.vt_viewer iframe').attr('src','');
            $('.showcase').fadeIn();
            $('body').css('overflow-y','auto');
        });
    };
    var filter_cat = function (id) {
        if($('#btn_cat_'+id).hasClass('bg-primary')) {
            $('#btn_cat_'+id).removeClass('bg-primary').addClass('bg-light').removeClass('text-white').addClass('text-dark');
        } else {
            $('#btn_cat_'+id).removeClass('bg-light').addClass('bg-primary').removeClass('text-dark').addClass('text-white');
        }
        filter_cats();
    }

    function filter_cats() {
        var all_disabled = true;
        $('.vt-card').parent().addClass('d-none');
        $('.categories .bg-primary').each(function(i, obj) {
            all_disabled = false;
            var id = $(this).attr('id').replace('btn_cat_','');
            $('.vt-card[data-category="'+id+'"]').parent().removeClass('d-none');
        });
        if(all_disabled) {
            $('.vt-card').parent().removeClass('d-none');
        }
    }
</script>
</body>
</html>

<?php
function print_favicons_showcase($code,$logo) {
    $path = '';
    $version = time();
    $path_m = 's_'.$code.'/';
    if (file_exists(dirname(__FILE__).'/../favicons/s_'.$code.'/favicon.ico')) {
        $path = 's_'.$code.'/';
        $version = preg_replace('/[^0-9]/', '', $logo);
    } else {
        if (file_exists(dirname(__FILE__).'/../favicons/custom/favicon.ico')) {
            $path = 'custom/';
        }
    }
    return '<link rel="apple-touch-icon" sizes="180x180" href="../favicons/'.$path.'apple-touch-icon.png?v='.$version.'">
    <link rel="icon" type="image/png" sizes="32x32" href="../favicons/'.$path.'favicon-32x32.png?v='.$version.'">
    <link rel="icon" type="image/png" sizes="16x16" href="../favicons/'.$path.'favicon-16x16.png?v='.$version.'">
    <link rel="manifest" href="../favicons/'.$path_m.'site.webmanifest?v='.$version.'">
    <link rel="mask-icon" href="../favicons/'.$path.'safari-pinned-tab.svg?v='.$version.'" color="#ffffff">
    <link rel="shortcut icon" href="../favicons/'.$path.'favicon.ico?v='.$version.'">
    <meta name="msapplication-TileColor" content="#ffffff">
    <meta name="msapplication-config" content="../favicons/'.$path.'browserconfig.xml?v='.$version.'">
    <meta name="theme-color" content="#ffffff">';
}
?>
