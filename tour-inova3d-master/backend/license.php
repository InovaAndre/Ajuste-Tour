<?php
error_reporting(E_ALL & ~E_WARNING & ~E_NOTICE & ~E_DEPRECATED);
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

echo "<div class=\"d-sm-flex align-items-center justify-content-between mb-3\">
<h1 class=\"h3 mb-0 text-gray-800\"><i class=\"fas fa-fw fa-key text-gray-700\"></i> LICENSE</h1>
</div>
<div class=\"row\">
    <div class=\"col-md-12 mb-4\">
    <div class=\"card shadow mb-12\">
    <div class=\"card-header py-3\">
    <h6 class=\"m-0 font-weight-bold text-primary\">Warning</h6>
</div>
<div class=\"card-body\">
    <div class=\"col-md-12\">
        <p>The multi users / plans is available only with an Extended License. Please go to settings and insert your purchase code.</p>
    </div>
    <div class=\"col-md-12\">
        <a href='index.php?p=settings' class=\"btn btn-success btn-block\">GO TO SETTINGS</a>
    </div>
</div>
</div>
</div>
</div>";