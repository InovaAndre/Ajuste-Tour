<?php
error_reporting(E_ALL & ~E_WARNING & ~E_NOTICE & ~E_DEPRECATED);
ini_set("memory_limit",-1);
ini_set('max_execution_time', 9999);
if(!class_exists('Gumlet\ImageResize')) {
    require_once(dirname(__FILE__).'/ImageResizeException.php');
    require_once(dirname(__FILE__).'/ImageResize.php');
}
use \Gumlet\ImageResize;

$path = dirname(__FILE__).'/../viewer/panoramas/';
$dir = new DirectoryIterator($path);
foreach ($dir as $fileinfo) {
    if (!$fileinfo->isDot() && ($fileinfo->isFile())) {
        $file_path = $fileinfo->getRealPath();
        $file_name = $fileinfo->getBasename();
        $mobile_path = str_replace(DIRECTORY_SEPARATOR."panoramas".DIRECTORY_SEPARATOR,DIRECTORY_SEPARATOR."panoramas".DIRECTORY_SEPARATOR."mobile".DIRECTORY_SEPARATOR,$file_path);
        if(!file_exists($mobile_path)) {
            try {
                $image = new ImageResize($file_path);
                $image->quality_jpg = 90;
                $image->interlace = 1;
                $image->gamma(false);
                $image->resizeToWidth(4096,false);
                $image->save($mobile_path,IMAGETYPE_JPEG);
            } catch (Exception $e) {}
        }
    }
}