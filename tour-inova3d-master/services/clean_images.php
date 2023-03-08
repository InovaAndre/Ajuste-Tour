<?php
error_reporting(E_ALL & ~E_WARNING & ~E_NOTICE & ~E_DEPRECATED);
require_once(__DIR__."/../db/connection.php");

$path = __DIR__.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR;

$array_content_files = array();
$array_map_files = array();
$array_rooms_files = array();
$array_rooms_files_multires = array();
$array_rooms_v_files = array();
$array_gallery_files = array();
$array_media_library_files = array();
$array_icon_files = array();
$array_assets_files = array();
$array_thumbs_files = array();
$array_object360_files = array();
$array_products_files = array();

$query = "SELECT song,logo,nadir_logo,background_image,background_video,intro_desktop,intro_mobile,presentation_video FROM svt_virtualtours;";
$result = $mysqli->query($query);
if($result) {
    if($result->num_rows>0) {
        while($row=$result->fetch_array(MYSQLI_ASSOC)) {
            $song = $row['song'];
            $logo = $row['logo'];
            $nadir_logo = $row['nadir_logo'];
            $background_image = $row['background_image'];
            $background_video = $row['background_video'];
            $intro_desktop = $row['intro_desktop'];
            $intro_mobile = $row['intro_mobile'];
            $presentation_video = $row['presentation_video'];
            if($song!='') {
                if(!in_array($song,$array_content_files)) {
                    array_push($array_content_files,$song);
                }
            }
            if($logo!='') {
                if(!in_array($logo,$array_content_files)) {
                    array_push($array_content_files,$logo);
                }
            }
            if($nadir_logo!='') {
                if(!in_array($nadir_logo,$array_content_files)) {
                    array_push($array_content_files,$nadir_logo);
                }
            }
            if($background_image!='') {
                if(!in_array($background_image,$array_content_files)) {
                    array_push($array_content_files,$background_image);
                }
            }
            if($background_video!='') {
                if(!in_array($background_video,$array_content_files)) {
                    array_push($array_content_files,$background_video);
                }
            }
            if($intro_desktop!='') {
                if(!in_array($intro_desktop,$array_content_files)) {
                    array_push($array_content_files,$intro_desktop);
                }
            }
            if($intro_mobile!='') {
                if(!in_array($intro_mobile,$array_content_files)) {
                    array_push($array_content_files,$intro_mobile);
                }
            }
            if($presentation_video!='') {
                $presentation_video = basename($presentation_video);
                if(!in_array($presentation_video,$array_content_files)) {
                    array_push($array_content_files,$presentation_video);
                }
            }
        }
    }
}

$query = "SELECT map FROM svt_maps;";
$result = $mysqli->query($query);
if($result) {
    if($result->num_rows>0) {
        while($row=$result->fetch_array(MYSQLI_ASSOC)) {
            $map = $row['map'];
            if($map!='') {
                if(!in_array($map,$array_map_files)) {
                    array_push($array_map_files,$map);
                }
            }
        }
    }
}

$query = "SELECT content,type,embed_type,embed_content FROM svt_pois WHERE type IN ('image','download','video','video360','audio','embed','object3d','lottie') AND content LIKE '%content/%' OR embed_content LIKE '%content/%';";
$result = $mysqli->query($query);
if($result) {
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_array(MYSQLI_ASSOC)) {
            switch($row['type']) {
                case 'object3d':
                    if (strpos($row['content'], ',') !== false) {
                        $array_contents = explode(",",$row['content']);
                        foreach ($array_contents as $content) {
                            $content = basename($content);
                            if($content!='') {
                                if(!in_array($content,$array_content_files)) {
                                    array_push($array_content_files,$content);
                                }
                            }
                        }
                    } else {
                        $content = basename($row['content']);
                        if($content!='') {
                            if(!in_array($content,$array_content_files)) {
                                array_push($array_content_files,$content);
                            }
                        }
                    }
                    break;
                default:
                    $content = basename($row['content']);
                    if($content!='') {
                        if(!in_array($content,$array_content_files)) {
                            array_push($array_content_files,$content);
                        }
                    }
                    break;
            }
            switch ($row['embed_type']) {
                case 'image':
                case 'video':
                case 'video_chroma':
                    $content = basename($row['embed_content']);
                    if($content!='') {
                        if(!in_array($content,$array_content_files)) {
                            array_push($array_content_files,$content);
                        }
                    }
                    break;
                case 'video_transparent':
                    if (strpos($row['embed_content'], ',') !== false) {
                        $array_contents = explode(",",$row['embed_content']);
                        foreach ($array_contents as $content) {
                            $content = basename($content);
                            if($content!='') {
                                if(!in_array($content,$array_content_files)) {
                                    array_push($array_content_files,$content);
                                }
                            }
                        }
                    } else {
                        $content = basename($row['embed_content']);
                        if($content!='') {
                            if(!in_array($content,$array_content_files)) {
                                array_push($array_content_files,$content);
                            }
                        }
                    }
                    break;
            }
        }
    }
}

$query = "SELECT panorama_image,panorama_json,thumb_image,panorama_video,song,logo FROM svt_rooms;";
$result = $mysqli->query($query);
if($result) {
    if($result->num_rows>0) {
        while($row=$result->fetch_array(MYSQLI_ASSOC)) {
            $panorama_image = $row['panorama_image'];
            if($panorama_image!='') {
                if(!in_array($panorama_image,$array_rooms_files)) {
                    array_push($array_rooms_files,$panorama_image);
                }
            }
            $thumb_image = $row['thumb_image'];
            if($thumb_image!='') {
                if(!in_array($thumb_image,$array_thumbs_files)) {
                    array_push($array_thumbs_files,$thumb_image);
                }
            }
            $panorama_video = $row['panorama_video'];
            if($panorama_video!='') {
                if(!in_array($panorama_video,$array_rooms_v_files)) {
                    array_push($array_rooms_v_files,$panorama_video);
                }
            }
            $panorama_json = $row['panorama_json'];
            if($panorama_json!='') {
                if(!in_array($panorama_json,$array_rooms_files)) {
                    array_push($array_rooms_files,$panorama_json);
                }
            }
            $song = $row['song'];
            if($song!='') {
                if(!in_array($song,$array_content_files)) {
                    array_push($array_content_files,$song);
                }
            }
            $logo = $row['logo'];
            if($logo!='') {
                if(!in_array($logo,$array_content_files)) {
                    array_push($array_content_files,$logo);
                }
            }
        }
    }
}

$query = "SELECT panorama_image FROM svt_rooms WHERE id_virtualtour IN(SELECT id FROM svt_virtualtours WHERE enable_multires=0);";
$result = $mysqli->query($query);
if($result) {
    if($result->num_rows>0) {
        while($row=$result->fetch_array(MYSQLI_ASSOC)) {
            $panorama_image = $row['panorama_image'];
            if($panorama_image!='') {
                if(!in_array($panorama_image,$array_rooms_files_multires)) {
                    array_push($array_rooms_files_multires,$panorama_image);
                }
            }
        }
    }
}

$query_check = "SELECT ra.id,p.id as c FROM svt_rooms_alt as ra
LEFT JOIN svt_pois as p ON p.type='switch_pano' AND p.content=ra.id
WHERE ra.poi=1;";
$result_check = $mysqli->query($query_check);
if($result_check) {
    if($result_check->num_rows>0) {
        while($row_check = $result_check->fetch_array(MYSQLI_ASSOC)) {
            if(empty($row_check['c'])) {
                $id_room_alt = $row_check['id'];
                $mysqli->query("DELETE FROM svt_rooms_alt WHERE id=$id_room_alt;");
            }
        }
        $mysqli->query("ALTER TABLE svt_rooms_alt AUTO_INCREMENT = 1;");
    }
}

$query = "SELECT panorama_image FROM svt_rooms_alt;";
$result = $mysqli->query($query);
if($result) {
    if($result->num_rows>0) {
        while($row=$result->fetch_array(MYSQLI_ASSOC)) {
            $panorama_image = $row['panorama_image'];
            if($panorama_image!='') {
                if(!in_array($panorama_image,$array_rooms_files)) {
                    array_push($array_rooms_files,$panorama_image);
                }
            }
        }
    }
}

$query = "SELECT image FROM svt_gallery;";
$result = $mysqli->query($query);
if($result) {
    if($result->num_rows>0) {
        while($row=$result->fetch_array(MYSQLI_ASSOC)) {
            $image = $row['image'];
            if($image!='') {
                if(!in_array($image,$array_gallery_files)) {
                    array_push($array_gallery_files,$image);
                }
            }
        }
    }
}
$query = "SELECT image FROM svt_poi_gallery;";
$result = $mysqli->query($query);
if($result) {
    if($result->num_rows>0) {
        while($row=$result->fetch_array(MYSQLI_ASSOC)) {
            $image = $row['image'];
            if($image!='') {
                if(!in_array($image,$array_gallery_files)) {
                    array_push($array_gallery_files,$image);
                }
            }
        }
    }
}
$query = "SELECT image FROM svt_poi_embedded_gallery;";
$result = $mysqli->query($query);
if($result) {
    if($result->num_rows>0) {
        while($row=$result->fetch_array(MYSQLI_ASSOC)) {
            $image = $row['image'];
            if($image!='') {
                if(!in_array($image,$array_gallery_files)) {
                    array_push($array_gallery_files,$image);
                }
            }
        }
    }
}

$query = "SELECT file FROM svt_media_library;";
$result = $mysqli->query($query);
if($result) {
    if($result->num_rows>0) {
        while($row=$result->fetch_array(MYSQLI_ASSOC)) {
            $file = $row['file'];
            if($file!='') {
                if(!in_array($file,$array_media_library_files)) {
                    array_push($array_media_library_files,$file);
                }
            }
        }
    }
}

$query = "SELECT file FROM svt_music_library;";
$result = $mysqli->query($query);
if($result) {
    if($result->num_rows>0) {
        while($row=$result->fetch_array(MYSQLI_ASSOC)) {
            $file = $row['file'];
            if($file!='') {
                if(!in_array($file,$array_content_files)) {
                    array_push($array_content_files,$file);
                }
            }
        }
    }
}

$query = "SELECT image FROM svt_poi_objects360;";
$result = $mysqli->query($query);
if($result) {
    if($result->num_rows>0) {
        while($row=$result->fetch_array(MYSQLI_ASSOC)) {
            $image = $row['image'];
            if($image!='') {
                if(!in_array($image,$array_object360_files)) {
                    array_push($array_object360_files,$image);
                }
            }
        }
    }
}

$query = "SELECT image FROM svt_product_images;";
$result = $mysqli->query($query);
if($result) {
    if($result->num_rows>0) {
        while($row=$result->fetch_array(MYSQLI_ASSOC)) {
            $image = $row['image'];
            if($image!='') {
                if(!in_array($image,$array_products_files)) {
                    array_push($array_products_files,$image);
                }
            }
        }
    }
}

$query = "SELECT image FROM svt_icons;";
$result = $mysqli->query($query);
if($result) {
    if($result->num_rows>0) {
        while($row=$result->fetch_array(MYSQLI_ASSOC)) {
            $image = $row['image'];
            if($image!='') {
                if(!in_array($image,$array_icon_files)) {
                    array_push($array_icon_files,$image);
                }
            }
        }
    }
}

$query = "SELECT logo,small_logo,background,background_reg FROM svt_settings;";
$result = $mysqli->query($query);
if($result) {
    if($result->num_rows>0) {
        while($row=$result->fetch_array(MYSQLI_ASSOC)) {
            $logo = $row['logo'];
            if($logo!='') {
                if(!in_array($logo,$array_assets_files)) {
                    array_push($array_assets_files,$logo);
                }
            }
            $small_logo = $row['small_logo'];
            if($small_logo!='') {
                if(!in_array($small_logo,$array_assets_files)) {
                    array_push($array_assets_files,$small_logo);
                }
            }
            $background = $row['background'];
            if($background!='') {
                if(!in_array($background,$array_assets_files)) {
                    array_push($array_assets_files,$background);
                }
            }
            $background_reg = $row['background_reg'];
            if($background_reg!='') {
                if(!in_array($background_reg,$array_assets_files)) {
                    array_push($array_assets_files,$background_reg);
                }
            }
        }
    }
}

$query = "SELECT avatar FROM svt_users WHERE avatar != '';";
$result = $mysqli->query($query);
if($result) {
    if($result->num_rows>0) {
        while($row=$result->fetch_array(MYSQLI_ASSOC)) {
            $avatar = $row['avatar'];
            if($avatar!='') {
                if(!in_array($avatar,$array_assets_files)) {
                    array_push($array_assets_files,$avatar);
                }
            }
        }
    }
}

$query = "SELECT banner,logo FROM svt_showcases;";
$result = $mysqli->query($query);
if($result) {
    if($result->num_rows>0) {
        while($row=$result->fetch_array(MYSQLI_ASSOC)) {
            $banner = $row['banner'];
            $logo = $row['logo'];
            if($banner!='') {
                if(!in_array($banner,$array_content_files)) {
                    array_push($array_content_files,$banner);
                }
            }
            if($logo!='') {
                if(!in_array($logo,$array_content_files)) {
                    array_push($array_content_files,$logo);
                }
            }
        }
    }
}

$query = "SELECT image FROM svt_advertisements WHERE image IS NOT NULL;";
$result = $mysqli->query($query);
if($result) {
    if($result->num_rows>0) {
        while($row=$result->fetch_array(MYSQLI_ASSOC)) {
            $image = $row['image'];
            if($image!='') {
                if(!in_array($image,$array_content_files)) {
                    array_push($array_content_files,$image);
                }
            }
        }
    }
}
$query = "SELECT video FROM svt_advertisements WHERE video IS NOT NULL;";
$result = $mysqli->query($query);
if($result) {
    if($result->num_rows>0) {
        while($row=$result->fetch_array(MYSQLI_ASSOC)) {
            $video = $row['video'];
            if($video!='') {
                if(!in_array($video,$array_content_files)) {
                    array_push($array_content_files,$video);
                }
            }
        }
    }
}


$files = glob($path."viewer".DIRECTORY_SEPARATOR."content".DIRECTORY_SEPARATOR."*");
foreach($files as $file){
    if(is_file($file)) {
        $filename = basename($file);
        if(!in_array($filename,$array_content_files)) {
            unlink($file);
        }
    }
}

$files = glob($path . "viewer" . DIRECTORY_SEPARATOR . "maps" . DIRECTORY_SEPARATOR . "*");
foreach ($files as $file) {
    if (is_file($file)) {
        $filename = basename($file);
        if (!in_array($filename, $array_map_files)) {
            unlink($file);
        }
    }
}
$files = glob($path . "viewer" . DIRECTORY_SEPARATOR . "maps" . DIRECTORY_SEPARATOR . "thumb" . DIRECTORY_SEPARATOR . "*");
foreach ($files as $file) {
    if (is_file($file)) {
        $filename = basename($file);
        if (!in_array($filename, $array_map_files)) {
            unlink($file);
        }
    }
}

$files = glob($path . "viewer" . DIRECTORY_SEPARATOR . "panoramas" . DIRECTORY_SEPARATOR . "*");
foreach ($files as $file) {
    if (is_file($file)) {
        $filename = basename($file);
        if (!in_array($filename, $array_rooms_files)) {
            unlink($file);
        }
    }
}
$files = glob($path . "viewer" . DIRECTORY_SEPARATOR . "panoramas" . DIRECTORY_SEPARATOR . "original" . DIRECTORY_SEPARATOR . "*");
foreach ($files as $file) {
    if (is_file($file)) {
        $filename = basename($file);
        if (!in_array($filename, $array_rooms_files)) {
            unlink($file);
        }
    }
}
$files = glob($path . "viewer" . DIRECTORY_SEPARATOR . "panoramas" . DIRECTORY_SEPARATOR . "mobile" . DIRECTORY_SEPARATOR . "*");
foreach ($files as $file) {
    if (is_file($file)) {
        $filename = basename($file);
        if (!in_array($filename, $array_rooms_files)) {
            unlink($file);
        }
    }
}
$files = glob($path . "viewer" . DIRECTORY_SEPARATOR . "panoramas" . DIRECTORY_SEPARATOR . "thumb" . DIRECTORY_SEPARATOR . "*");
foreach ($files as $file) {
    if (is_file($file)) {
        $filename = basename($file);
        if (!in_array($filename, $array_rooms_files)) {
            unlink($file);
        }
    }
}
$files = glob($path . "viewer" . DIRECTORY_SEPARATOR . "panoramas" . DIRECTORY_SEPARATOR . "lowres" . DIRECTORY_SEPARATOR . "*");
foreach ($files as $file) {
    if (is_file($file)) {
        $filename = basename($file);
        if (!in_array($filename, $array_rooms_files)) {
            unlink($file);
        }
    }
}
$files = glob($path . "viewer" . DIRECTORY_SEPARATOR . "panoramas" . DIRECTORY_SEPARATOR . "preview" . DIRECTORY_SEPARATOR . "*");
foreach ($files as $file) {
    if (is_file($file)) {
        $filename = basename($file);
        if (!in_array($filename, $array_rooms_files)) {
            unlink($file);
        }
    }
}
$files = glob($path . "viewer" . DIRECTORY_SEPARATOR . "panoramas" . DIRECTORY_SEPARATOR . "multires" . DIRECTORY_SEPARATOR . "*");
foreach ($files as $file) {
    if (is_dir($file)) {
        $filename = basename($file).".jpg";
        if (!in_array($filename, $array_rooms_files)) {
            try {
                deleteDir($path . "viewer" . DIRECTORY_SEPARATOR . "panoramas" . DIRECTORY_SEPARATOR . "multires" . DIRECTORY_SEPARATOR . basename($file));
                rmdir($path . "viewer" . DIRECTORY_SEPARATOR . "panoramas" . DIRECTORY_SEPARATOR . "multires" . DIRECTORY_SEPARATOR . basename($file));
            } catch (Exception $e) {}
        }
    }
}

foreach ($array_rooms_files_multires as $filename) {
    if(file_exists($path . "viewer" . DIRECTORY_SEPARATOR . "panoramas" . DIRECTORY_SEPARATOR . "multires" . DIRECTORY_SEPARATOR . basename($filename, ".jpg"))) {
        try {
            deleteDir($path . "viewer" . DIRECTORY_SEPARATOR . "panoramas" . DIRECTORY_SEPARATOR . "multires" . DIRECTORY_SEPARATOR . basename($filename, ".jpg"));
            rmdir($path . "viewer" . DIRECTORY_SEPARATOR . "panoramas" . DIRECTORY_SEPARATOR . "multires" . DIRECTORY_SEPARATOR . basename($filename, ".jpg"));
        } catch (Exception $e) {}
    }
}

$files = glob($path . "viewer" . DIRECTORY_SEPARATOR . "videos" . DIRECTORY_SEPARATOR . "*");
foreach ($files as $file) {
    if (is_file($file)) {
        $filename = basename($file);
        if (!in_array($filename, $array_rooms_v_files)) {
            unlink($file);
        }
    }
}

$files = glob($path . "viewer" . DIRECTORY_SEPARATOR . "gallery" . DIRECTORY_SEPARATOR . "*");
foreach ($files as $file) {
    if (is_file($file)) {
        $filename = basename($file);
        if (!in_array($filename, $array_gallery_files)) {
            unlink($file);
        }
    }
}
$files = glob($path . "viewer" . DIRECTORY_SEPARATOR . "gallery" . DIRECTORY_SEPARATOR . "thumb" . DIRECTORY_SEPARATOR . "*");
foreach ($files as $file) {
    if (is_file($file)) {
        $filename = basename($file);
        if (!in_array($filename, $array_gallery_files)) {
            unlink($file);
        }
    }
}

$files = glob($path . "viewer" . DIRECTORY_SEPARATOR . "media" . DIRECTORY_SEPARATOR . "*");
foreach ($files as $file) {
    if (is_file($file)) {
        $filename = basename($file);
        if (!in_array($filename, $array_media_library_files)) {
            unlink($file);
        }
    }
}
$files = glob($path . "viewer" . DIRECTORY_SEPARATOR . "media" . DIRECTORY_SEPARATOR . "thumb" . DIRECTORY_SEPARATOR . "*");
foreach ($files as $file) {
    if (is_file($file)) {
        $filename = basename($file);
        if (!in_array($filename, $array_media_library_files)) {
            unlink($file);
        }
    }
}

$files = glob($path . "viewer" . DIRECTORY_SEPARATOR . "objects360" . DIRECTORY_SEPARATOR . "*");
foreach ($files as $file) {
    if (is_file($file)) {
        $filename = basename($file);
        if (!in_array($filename, $array_object360_files)) {
            unlink($file);
        }
    }
}

$files = glob($path . "viewer" . DIRECTORY_SEPARATOR . "products" . DIRECTORY_SEPARATOR . "*");
foreach ($files as $file) {
    if (is_file($file)) {
        $filename = basename($file);
        if (!in_array($filename, $array_products_files)) {
            unlink($file);
        }
    }
}

$files = glob($path . "viewer" . DIRECTORY_SEPARATOR . "products" . DIRECTORY_SEPARATOR . "thumb" . DIRECTORY_SEPARATOR . "*");
foreach ($files as $file) {
    if (is_file($file)) {
        $filename = basename($file);
        if (!in_array($filename, $array_products_files)) {
            unlink($file);
        }
    }
}

$files = glob($path . "viewer" . DIRECTORY_SEPARATOR . "icons" . DIRECTORY_SEPARATOR . "*");
foreach ($files as $file) {
    if (is_file($file)) {
        $filename = basename($file);
        if (!in_array($filename, $array_icon_files)) {
            unlink($file);
        }
    }
}

$files = glob($path . "backend" . DIRECTORY_SEPARATOR . "assets" . DIRECTORY_SEPARATOR . "*");
foreach ($files as $file) {
    if (is_file($file)) {
        $filename = basename($file);
        if (!in_array($filename, $array_assets_files)) {
            unlink($file);
        }
    }
}

$files = glob($path . "viewer" . DIRECTORY_SEPARATOR . "panoramas" . DIRECTORY_SEPARATOR . "thumb_custom" . DIRECTORY_SEPARATOR . "*");
foreach ($files as $file) {
    if (is_file($file)) {
        $filename = basename($file);
        if (!in_array($filename, $array_thumbs_files)) {
            unlink($file);
        }
    }
}

function deleteDir($dirPath) {
    if (! is_dir($dirPath)) {
        throw new InvalidArgumentException("$dirPath must be a directory");
    }
    if (substr($dirPath, strlen($dirPath) - 1, 1) != '/') {
        $dirPath .= '/';
    }
    $files = glob($dirPath . '*', GLOB_MARK);
    foreach ($files as $file) {
        if (is_dir($file)) {
            deleteDir($file);
        } else {
            unlink($file);
        }
    }
    rmdir($dirPath);
}