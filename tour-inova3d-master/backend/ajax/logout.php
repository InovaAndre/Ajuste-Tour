<?php
error_reporting(E_ALL & ~E_WARNING & ~E_NOTICE & ~E_DEPRECATED);
session_start();
$lang = $_SESSION['lang'];
unset($_SESSION['id_user']);
session_destroy();
session_start();
$_SESSION['lang'] = $lang;