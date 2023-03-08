<?php
if(!file_exists("config/config.inc.php")) {
    header("Location: install/start.php");
} else {
    if(!file_exists("index.html")) {
        header("Location: backend/login.php");
    }
}