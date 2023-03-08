<?php
error_reporting(E_ALL & ~E_WARNING & ~E_NOTICE & ~E_DEPRECATED);
ob_start();
ini_set("memory_limit",-1);
ini_set('max_execution_time', 9999);
include_once(dirname(__FILE__)."/thumb.php");

if (!file_exists(dirname(__FILE__).'/../viewer/panoramas/preview/')) {
    mkdir(dirname(__FILE__).'/../viewer/panoramas/preview/', 0775);
}
if (!file_exists(dirname(__FILE__).'/../viewer/panoramas/lowres/')) {
    mkdir(dirname(__FILE__).'/../viewer/panoramas/lowres/', 0775);
}
if (!file_exists(dirname(__FILE__).'/../viewer/maps/thumb/')) {
    mkdir(dirname(__FILE__).'/../viewer/maps/thumb/', 0775);
}
if (!file_exists(dirname(__FILE__).'/../viewer/products/thumb/')) {
    mkdir(dirname(__FILE__).'/../viewer/products/thumb/', 0775);
}

$path = dirname(__FILE__).'/../viewer/panoramas/';
$dir = new DirectoryIterator($path);
foreach ($dir as $fileinfo) {
    if (!$fileinfo->isDot() && ($fileinfo->isFile())) {
        $file_path = $fileinfo->getRealPath();
        $file_name = $fileinfo->getBasename();
        $file_ext = $fileinfo->getExtension();
        if($file_ext=='json') continue;
        $thumb_path = str_replace(DIRECTORY_SEPARATOR."panoramas".DIRECTORY_SEPARATOR,DIRECTORY_SEPARATOR."panoramas".DIRECTORY_SEPARATOR."thumb".DIRECTORY_SEPARATOR,$file_path);
        $lowres_path = str_replace(DIRECTORY_SEPARATOR."panoramas".DIRECTORY_SEPARATOR,DIRECTORY_SEPARATOR."panoramas".DIRECTORY_SEPARATOR."lowres".DIRECTORY_SEPARATOR,$file_path);
        $preview_path = str_replace(DIRECTORY_SEPARATOR."panoramas".DIRECTORY_SEPARATOR,DIRECTORY_SEPARATOR."panoramas".DIRECTORY_SEPARATOR."preview".DIRECTORY_SEPARATOR,$file_path);
        if(!file_exists($thumb_path)) {
            $tg = new thumbnailGenerator;
            $tg->generate($file_path, 213, 120, $thumb_path);
        }
        if(!file_exists($lowres_path)) {
            $tg = new thumbnailGenerator;
            $tg->generate($file_path, 1280, 640, $lowres_path);
        }
        if(!file_exists($preview_path)) {
            list($width, $height, $type, $attr) = getimagesize($file_path);
            $crop_width = $width * 0.5;
            $crop_height = $height * 0.5;
            $im = @imagecreatefromjpeg($file_path);
            if($im === false) {
                $im = imagecreatefrompng($file_path);
            }
            if($im != false) {
                imagejpeg(cropAlign($im, $crop_width, $crop_height, 'center', 'middle',$width,$height),$preview_path,100);
                $tg = new thumbnailGenerator;
                $tg->generate($preview_path, 213, 120, $preview_path);
            }
        }
    }
}

$path = dirname(__FILE__).'/../viewer/maps/';
$dir = new DirectoryIterator($path);
foreach ($dir as $fileinfo) {
    if (!$fileinfo->isDot() && ($fileinfo->isFile())) {
        $file_path = $fileinfo->getRealPath();
        $file_name = $fileinfo->getBasename();
        $thumb_path = str_replace(DIRECTORY_SEPARATOR."maps".DIRECTORY_SEPARATOR,DIRECTORY_SEPARATOR."maps".DIRECTORY_SEPARATOR."thumb".DIRECTORY_SEPARATOR,$file_path);
        if(!file_exists($thumb_path)) {
            list($width, $height, $type, $attr) = getimagesize($file_path);
            $crop_width = $width * 0.5;
            $crop_height = $height * 0.5;
            $im = @imagecreatefromjpeg($file_path);
            if($im === false) {
                $im = imagecreatefrompng($file_path);
            }
            if($im != false) {
                imagejpeg(cropAlign($im, $crop_width, $crop_height, 'center', 'middle',$width,$height),$thumb_path,100);
                $tg = new thumbnailGenerator;
                $tg->generate($thumb_path, 213, 120, $thumb_path);
            }
        }
    }
}

$path = dirname(__FILE__).'/../viewer/gallery/';
$dir = new DirectoryIterator($path);
foreach ($dir as $fileinfo) {
    if (!$fileinfo->isDot() && ($fileinfo->isFile())) {
        $file_path = $fileinfo->getRealPath();
        $file_name = $fileinfo->getBasename();
        $thumb_path = str_replace(DIRECTORY_SEPARATOR."gallery".DIRECTORY_SEPARATOR,DIRECTORY_SEPARATOR."gallery".DIRECTORY_SEPARATOR."thumb".DIRECTORY_SEPARATOR,$file_path);
        if(!file_exists($thumb_path)) {
            $tg = new thumbnailGenerator;
            $tg->generate($file_path, 200, 200, $thumb_path);
        }
    }
}

$path = dirname(__FILE__).'/../viewer/content/';
$dir = new DirectoryIterator($path);
foreach ($dir as $fileinfo) {
    if (!$fileinfo->isDot() && ($fileinfo->isFile())) {
        $file_path = $fileinfo->getRealPath();
        $file_name = $fileinfo->getBasename();
        $file_ext = $fileinfo->getExtension();
        if($file_ext=='json') continue;
        $thumb_path = str_replace(DIRECTORY_SEPARATOR."content".DIRECTORY_SEPARATOR,DIRECTORY_SEPARATOR."content".DIRECTORY_SEPARATOR."thumb".DIRECTORY_SEPARATOR,$file_path);
        if(!file_exists($thumb_path)) {
            $tg = new thumbnailGenerator;
            $tg->generate($file_path, 200, 200, $thumb_path);
        }
    }
}

$path = dirname(__FILE__).'/../viewer/media/';
$dir = new DirectoryIterator($path);
foreach ($dir as $fileinfo) {
    if (!$fileinfo->isDot() && ($fileinfo->isFile())) {
        $file_path = $fileinfo->getRealPath();
        $file_name = $fileinfo->getBasename();
        $thumb_path = str_replace(DIRECTORY_SEPARATOR."media".DIRECTORY_SEPARATOR,DIRECTORY_SEPARATOR."media".DIRECTORY_SEPARATOR."thumb".DIRECTORY_SEPARATOR,$file_path);
        if(!file_exists($thumb_path)) {
            $tg = new thumbnailGenerator;
            $tg->generate($file_path, 200, 200, $thumb_path);
        }
    }
}

$path = dirname(__FILE__).'/../viewer/products/';
$dir = new DirectoryIterator($path);
foreach ($dir as $fileinfo) {
    if (!$fileinfo->isDot() && ($fileinfo->isFile())) {
        $file_path = $fileinfo->getRealPath();
        $file_name = $fileinfo->getBasename();
        $thumb_path = str_replace(DIRECTORY_SEPARATOR."products".DIRECTORY_SEPARATOR,DIRECTORY_SEPARATOR."products".DIRECTORY_SEPARATOR."thumb".DIRECTORY_SEPARATOR,$file_path);
        if(!file_exists($thumb_path)) {
            list($width, $height, $type, $attr) = getimagesize($file_path);
            $crop_width = $width * 0.5;
            $crop_height = $height * 0.5;
            $im = @imagecreatefromjpeg($file_path);
            if($im === false) {
                $im = imagecreatefrompng($file_path);
            }
            if($im != false) {
                imagejpeg(cropAlign($im, $crop_width, $crop_height, 'center', 'middle',$width,$height),$thumb_path,100);
                $tg = new thumbnailGenerator;
                $tg->generate($thumb_path, 200, 200, $thumb_path);
            }
        }
    }
}
ob_end_clean();

function cropAlign($image, $cropWidth, $cropHeight, $horizontalAlign, $verticalAlign, $width, $height) {
    $horizontalAlignPixels = calculatePixelsForAlign($width, $cropWidth, $horizontalAlign);
    $verticalAlignPixels = calculatePixelsForAlign($height, $cropHeight, $verticalAlign);
    return imageCrop($image, [
        'x' => $horizontalAlignPixels[0],
        'y' => $verticalAlignPixels[0],
        'width' => $horizontalAlignPixels[1],
        'height' => $verticalAlignPixels[1]
    ]);
}

function calculatePixelsForAlign($imageSize, $cropSize, $align) {
    switch ($align) {
        case 'left':
        case 'top':
            return [0, min($cropSize, $imageSize)];
        case 'right':
        case 'bottom':
            return [max(0, $imageSize - $cropSize), min($cropSize, $imageSize)];
        case 'center':
        case 'middle':
            return [
                max(0, floor(($imageSize / 2) - ($cropSize / 2))),
                min($cropSize, $imageSize),
            ];
        default: return [0, $imageSize];
    }
}

function png2jpg($originalFile, $outputFile, $quality) {
    $image = imagecreatefrompng($originalFile);
    imagejpeg($image, $outputFile, $quality);
    imagedestroy($image);
}