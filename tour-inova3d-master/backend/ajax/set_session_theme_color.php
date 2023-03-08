<?php
error_reporting(E_ALL & ~E_WARNING & ~E_NOTICE & ~E_DEPRECATED);
ob_start();
session_start();
$_SESSION['theme_color'] = $_POST['theme_color'];
ob_end_clean();
