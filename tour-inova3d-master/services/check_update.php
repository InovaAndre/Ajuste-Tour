<?php
error_reporting(E_ALL & ~E_WARNING & ~E_NOTICE & ~E_DEPRECATED);
require_once(__DIR__."/../db/connection.php");
require_once(__DIR__."/../backend/functions.php");

function check_directory($path) {
    try {
        if (!file_exists(dirname(__FILE__).$path)) {
            mkdir(dirname(__FILE__).$path, 0775);
        }
    } catch (Exception $e) {}
}

//CHECKING DIRECTORIES
check_directory('/../backend/assets/');
check_directory('/../backend/tmp_panoramas/');
check_directory('/../viewer/content/');
check_directory('/../viewer/content/thumb/');
check_directory('/../viewer/gallery/');
check_directory('/../viewer/gallery/thumb/');
check_directory('/../viewer/icons/');
check_directory('/../viewer/media/');
check_directory('/../viewer/media/thumb/');
check_directory('/../viewer/maps/');
check_directory('/../viewer/videos/');
check_directory('/../viewer/panoramas/');
check_directory('/../viewer/panoramas/lowres/');
check_directory('/../viewer/panoramas/mobile/');
check_directory('/../viewer/panoramas/multires/');
check_directory('/../viewer/panoramas/original/');
check_directory('/../viewer/panoramas/preview/');
check_directory('/../viewer/panoramas/thumb/');
check_directory('/../viewer/panoramas/thumb_custom/');
check_directory('/../viewer/objects360/');
check_directory('/../viewer/products/');
check_directory('/../viewer/products/thumb/');

//UPDATE 1.4
$result = $mysqli->query("SHOW COLUMNS FROM svt_rooms LIKE 'allow_pitch';");
if($result) {
    if ($result->num_rows==0) {
        $mysqli->query("ALTER TABLE svt_rooms ADD `allow_pitch` BOOL NOT NULL DEFAULT '1';");
    }
}

//UPDATE 1.5
$result = $mysqli->query("SHOW COLUMNS FROM svt_virtualtours LIKE 'song';");
if($result) {
    if ($result->num_rows==0) {
        $mysqli->query("ALTER TABLE svt_virtualtours ADD `song` varchar(50) DEFAULT NULL;");
    }
}
$result = $mysqli->query("SHOW COLUMNS FROM svt_virtualtours LIKE 'song_autoplay';");
if($result) {
    if ($result->num_rows==0) {
        $mysqli->query("ALTER TABLE svt_virtualtours ADD `song_autoplay` tinyint(1) NOT NULL DEFAULT '0';");
    }
}

//UPDATE 1.6
$result = $mysqli->query("SHOW COLUMNS FROM svt_users LIKE 'role';");
if($result) {
    if ($result->num_rows==0) {
        $mysqli->query("ALTER TABLE svt_users ADD `role` varchar(50) DEFAULT 'customer';");
        $result2 = $mysqli->query("SELECT * FROM svt_users WHERE role='administrator';");
        if ($result2->num_rows==0) {
            $mysqli->query("UPDATE svt_users SET role='administrator' LIMIT 1;");
        }
    }
}
$result = $mysqli->query("SHOW COLUMNS FROM svt_users LIKE 'id_plan';");
if($result) {
    if ($result->num_rows==0) {
        $mysqli->query("ALTER TABLE svt_users ADD `id_plan` bigint(20) DEFAULT '1';");
    }
}
$result = $mysqli->query("SHOW COLUMNS FROM svt_users LIKE 'active';");
if($result) {
    if ($result->num_rows==0) {
        $mysqli->query("ALTER TABLE svt_users ADD `active` tinyint(1) NOT NULL DEFAULT '1';");
    }
}
$result = $mysqli->query("SHOW TABLES LIKE 'svt_plans';");
if($result) {
    if ($result->num_rows==0) {
        $mysqli->query("CREATE TABLE IF NOT EXISTS `svt_plans` (
                                  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
                                  `name` varchar(50) DEFAULT NULL,
                                  `n_virtual_tours` int(11) DEFAULT NULL,
                                  `n_rooms` int(11) DEFAULT NULL,
                                  `n_markers` int(11) DEFAULT NULL,
                                  `n_pois` int(11) DEFAULT NULL,
                                  PRIMARY KEY (`id`)
                                ) ENGINE=InnoDB DEFAULT CHARSET=utf8;");
        $mysqli->query("INSERT INTO `svt_plans` (`id`, `name`, `n_virtual_tours`, `n_rooms`, `n_markers`, `n_pois`) VALUES(1, 'Unlimited', -1, -1, -1, -1);");
    }
}

//UPDATE 1.7
$result = $mysqli->query("SHOW COLUMNS FROM svt_virtualtours LIKE 'logo';");
if($result) {
    if ($result->num_rows==0) {
        $mysqli->query("ALTER TABLE svt_virtualtours ADD `logo` varchar(50) DEFAULT NULL;");
    }
}

//UPDATE 1.8
$result = $mysqli->query("SHOW TABLES LIKE 'svt_presentations';");
if($result) {
    if ($result->num_rows == 0) {
        $mysqli->query("CREATE TABLE IF NOT EXISTS `svt_presentations` (
                              `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
                              `id_virtualtour` bigint(20) unsigned DEFAULT NULL,
                              `id_room` bigint(20) unsigned DEFAULT NULL,
                              `action` varchar(50) DEFAULT NULL,
                              `params` text,
                              `sleep` int(11) NOT NULL DEFAULT '0',
                              `priority_1` int(11) DEFAULT NULL,
                              `priority_2` int(11) DEFAULT NULL,
                              PRIMARY KEY (`id`),
                              KEY `id_virtual_tour` (`id_virtualtour`),
                              KEY `id_room` (`id_room`),
                              CONSTRAINT `svt_presentations_ibfk_1` FOREIGN KEY (`id_virtualtour`) REFERENCES `svt_virtualtours` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
                              CONSTRAINT `svt_presentations_ibfk_2` FOREIGN KEY (`id_room`) REFERENCES `svt_rooms` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
                            ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;");
    }
}

//UPDATE 1.9
$result = $mysqli->query("SHOW COLUMNS FROM svt_virtualtours LIKE 'nadir_logo';");
if($result) {
    if ($result->num_rows==0) {
        $mysqli->query("ALTER TABLE svt_virtualtours ADD `nadir_logo` varchar(50) DEFAULT NULL;");
    }
}
$result = $mysqli->query("SHOW COLUMNS FROM svt_virtualtours LIKE 'nadir_size';");
if($result) {
    if ($result->num_rows==0) {
        $mysqli->query("ALTER TABLE svt_virtualtours ADD `nadir_size` varchar(25) NOT NULL DEFAULT 'small';");
    }
}
$result = $mysqli->query("SHOW COLUMNS FROM svt_virtualtours LIKE 'autorotate_inactivity';");
if($result) {
    if ($result->num_rows==0) {
        $mysqli->query("ALTER TABLE svt_virtualtours ADD `autorotate_inactivity` int(11) NOT NULL DEFAULT '0';");
    }
}
$result = $mysqli->query("SHOW COLUMNS FROM svt_virtualtours LIKE 'autorotate_speed';");
if($result) {
    if ($result->num_rows==0) {
        $mysqli->query("ALTER TABLE svt_virtualtours ADD `autorotate_speed` int(11) NOT NULL DEFAULT '0';");
    }
}
$result = $mysqli->query("SHOW COLUMNS FROM svt_virtualtours LIKE 'markers_icon';");
if($result) {
    if ($result->num_rows==0) {
        $mysqli->query("ALTER TABLE svt_virtualtours ADD `markers_icon` varchar(50) NOT NULL DEFAULT 'fas fa-chevron-circle-up';");
    }
}
$result = $mysqli->query("SHOW COLUMNS FROM svt_virtualtours LIKE 'markers_show_room';");
if($result) {
    if ($result->num_rows==0) {
        $mysqli->query("ALTER TABLE svt_virtualtours ADD `markers_show_room` tinyint(1) NOT NULL DEFAULT '1';");
    }
}

//UPDATE 2.0
$result = $mysqli->query("SHOW COLUMNS FROM svt_pois LIKE 'type';");
if($result) {
    if ($result->num_rows==1) {
        $row = $result->fetch_array(MYSQLI_ASSOC);
        $type = $row['Type'];
        if (strpos($type, 'html') === false) {
            $mysqli->query("ALTER TABLE svt_pois MODIFY COLUMN `type` enum('image','video','link','html') DEFAULT NULL;");
        }
    }
}
$result = $mysqli->query("SHOW COLUMNS FROM svt_virtualtours LIKE 'arrows_nav';");
if($result) {
    if ($result->num_rows==0) {
        $mysqli->query("ALTER TABLE svt_virtualtours ADD `arrows_nav` tinyint(1) NOT NULL DEFAULT '1';");
    }
}
$result = $mysqli->query("SHOW COLUMNS FROM svt_virtualtours LIKE 'info_box';");
if($result) {
    if ($result->num_rows==0) {
        $mysqli->query("ALTER TABLE svt_virtualtours ADD `info_box` longtext;");
    }
}
$result = $mysqli->query("SHOW TABLES LIKE 'svt_gallery';");
if($result) {
    if ($result->num_rows == 0) {
        $mysqli->query("CREATE TABLE IF NOT EXISTS `svt_gallery` (
                              `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
                              `id_virtualtour` bigint(20) unsigned DEFAULT NULL,
                              `image` varchar(50) DEFAULT NULL,
                              `priority` int(11) NOT NULL DEFAULT '0',
                              PRIMARY KEY (`id`),
                              KEY `id_virtualtour` (`id_virtualtour`),
                              CONSTRAINT `svt_gallery_ibfk_1` FOREIGN KEY (`id_virtualtour`) REFERENCES `svt_virtualtours` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
                            ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;");
    }
}

//UPDATE 2.1
$result = $mysqli->query("SHOW COLUMNS FROM svt_rooms LIKE 'priority';");
if($result) {
    if ($result->num_rows==0) {
        $mysqli->query("ALTER TABLE svt_rooms ADD `priority` int(11) NOT NULL DEFAULT '0';");
    }
}
$result = $mysqli->query("SHOW COLUMNS FROM svt_virtualtours LIKE 'password';");
if($result) {
    if ($result->num_rows==0) {
        $mysqli->query("ALTER TABLE svt_virtualtours ADD `password` varchar(200) DEFAULT NULL;");
    }
}

//UPDATE 2.2
$result = $mysqli->query("SHOW COLUMNS FROM svt_rooms LIKE 'id_map';");
if($result) {
    if ($result->num_rows==0) {
        $mysqli->query("ALTER TABLE svt_rooms ADD `id_map` bigint(20) unsigned DEFAULT NULL AFTER `yaw`;");
    }
}
$result = $mysqli->query("SHOW TABLES LIKE 'svt_maps';");
if($result) {
    if ($result->num_rows == 0) {
        $mysqli->query("CREATE TABLE IF NOT EXISTS `svt_maps` (
                              `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
                              `id_virtualtour` bigint(20) unsigned NOT NULL,
                              `map` varchar(200) DEFAULT NULL,
                              `point_color` varchar(25) NOT NULL DEFAULT '#005eff',
                              `name` varchar(200) DEFAULT NULL,
                              PRIMARY KEY (`id`),
                              KEY `id_virtualtour` (`id_virtualtour`),
                              CONSTRAINT `svt_maps_ibfk_1` FOREIGN KEY (`id_virtualtour`) REFERENCES `svt_virtualtours` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
                            ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;");
        $query_m = "SELECT id,map FROM svt_virtualtours WHERE map <> '';";
        $result_m = $mysqli->query($query_m);
        if($result_m) {
            if($result_m->num_rows>0) {
                while($row_m = $result_m->fetch_array(MYSQLI_ASSOC)) {
                    $id_vt = $row_m['id'];
                    $map = $row_m['map'];
                    $result_i = $mysqli->query("INSERT INTO svt_maps(id_virtualtour,map,name) VALUES($id_vt,'$map','Main');");
                    if($result_i) {
                        $id_map = $mysqli->insert_id;
                        $mysqli->query("UPDATE svt_rooms SET id_map=$id_map WHERE id_virtualtour=$id_vt AND map_top IS NOT NULL;");
                    }
                }
            }
        }
        $mysqli->query("ALTER TABLE svt_virtualtours DROP COLUMN `map`;");
    }
}

//UPDATE 2.5
$result = $mysqli->query("SHOW TABLES LIKE 'svt_voice_commands';");
if($result) {
    if ($result->num_rows == 0) {
        $mysqli->query("CREATE TABLE IF NOT EXISTS `svt_voice_commands` (
                              `id` int(11) NOT NULL DEFAULT '0',
                              `language` varchar(10) NOT NULL DEFAULT 'en-US',
                              `initial_msg` varchar(200) NOT NULL DEFAULT 'Listening ... Say HELP for command list',
                              `listening_msg` varchar(200) NOT NULL DEFAULT 'Listening ...',
                              `next_cmd` varchar(200) NOT NULL DEFAULT 'next',
                              `next_msg` varchar(200) NOT NULL DEFAULT 'Ok, going to next room',
                              `prev_cmd` varchar(200) NOT NULL DEFAULT 'prev',
                              `prev_msg` varchar(200) NOT NULL DEFAULT 'Ok, going to previous room',
                              `left_cmd` varchar(200) NOT NULL DEFAULT 'left',
                              `left_msg` varchar(200) NOT NULL DEFAULT 'Ok, looking left',
                              `right_cmd` varchar(200) NOT NULL DEFAULT 'right',
                              `right_msg` varchar(200) NOT NULL DEFAULT 'Ok, looking right',
                              `up_cmd` varchar(200) NOT NULL DEFAULT 'up',
                              `up_msg` varchar(200) NOT NULL DEFAULT 'Ok, looking up',
                              `down_cmd` varchar(200) NOT NULL DEFAULT 'down',
                              `down_msg` varchar(200) NOT NULL DEFAULT 'Ok, looking down',
                              `help_cmd` varchar(200) NOT NULL DEFAULT 'help',
                              `help_msg_1` varchar(200) NOT NULL DEFAULT 'Say NEXT / PREVIOUS to navigate between rooms',
                              `help_msg_2` varchar(200) NOT NULL DEFAULT 'Say LEFT / RIGHT / UP / DOWN to look around',
                              `error_msg` varchar(200) NOT NULL DEFAULT 'I do not understand, repeat please...',
                              PRIMARY KEY (`id`)
                            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;");
        $mysqli->query("INSERT IGNORE INTO `svt_voice_commands` (`id`) VALUES(1);");
    }
}
$result = $mysqli->query("SHOW COLUMNS FROM svt_virtualtours LIKE 'voice_commands';");
if($result) {
    if ($result->num_rows==0) {
        $mysqli->query("ALTER TABLE svt_virtualtours ADD `voice_commands` tinyint(1) NOT NULL DEFAULT '0';");
    }
}
$result = $mysqli->query("SHOW COLUMNS FROM svt_pois LIKE 'icon';");
if($result) {
    if ($result->num_rows==0) {
        $mysqli->query("ALTER TABLE svt_pois ADD `icon` varchar(50) DEFAULT NULL AFTER `type`;");
        $mysqli->query("UPDATE svt_pois SET icon='fas fa-image' WHERE `type`='image';");
        $mysqli->query("UPDATE svt_pois SET icon='fas fa-video' WHERE `type`='video';");
        $mysqli->query("UPDATE svt_pois SET icon='fas fa-link' WHERE `type`='link';");
        $mysqli->query("UPDATE svt_pois SET icon='fas fa-info-circle' WHERE `type`='html';");
    }
}
$result = $mysqli->query("SHOW COLUMNS FROM svt_pois LIKE 'type';");
if($result) {
    if ($result->num_rows==1) {
        $row = $result->fetch_array(MYSQLI_ASSOC);
        $type = $row['Type'];
        if (strpos($type, 'html_sc') === false) {
            $mysqli->query("ALTER TABLE svt_pois MODIFY COLUMN `type` enum('image','video','link','html','html_sc','download') DEFAULT NULL;");
        }
    }
}
$result = $mysqli->query("SHOW COLUMNS FROM svt_virtualtours LIKE 'markers_color';");
if($result) {
    if ($result->num_rows==0) {
        $mysqli->query("ALTER TABLE svt_virtualtours ADD `markers_color` varchar(25) NOT NULL DEFAULT '#000000' AFTER `markers_icon`;");
    }
}
$result = $mysqli->query("SHOW COLUMNS FROM svt_virtualtours LIKE 'markers_background';");
if($result) {
    if ($result->num_rows==0) {
        $mysqli->query("ALTER TABLE svt_virtualtours ADD `markers_background` varchar(25) NOT NULL DEFAULT 'rgba(255,255,255,0.7)' AFTER `markers_icon`;");
    }
}
$result = $mysqli->query("SHOW COLUMNS FROM svt_pois LIKE 'color';");
if($result) {
    if ($result->num_rows==0) {
        $mysqli->query("ALTER TABLE svt_pois ADD `color` varchar(25) NOT NULL DEFAULT '#000000' AFTER `icon`;");
    }
}
$result = $mysqli->query("SHOW COLUMNS FROM svt_pois LIKE 'background';");
if($result) {
    if ($result->num_rows==0) {
        $mysqli->query("ALTER TABLE svt_pois ADD `background` varchar(25) NOT NULL DEFAULT 'rgba(255,255,255,0.7)' AFTER `icon`;");
    }
}

//UPDATE 2.6
$result = $mysqli->query("SHOW COLUMNS FROM svt_virtualtours LIKE 'compass';");
if($result) {
    if ($result->num_rows==0) {
        $mysqli->query("ALTER TABLE svt_virtualtours ADD `compass` tinyint(1) NOT NULL DEFAULT '1';");
    }
}
$result = $mysqli->query("SHOW COLUMNS FROM svt_virtualtours LIKE 'background_image';");
if($result) {
    if ($result->num_rows==0) {
        $mysqli->query("ALTER TABLE svt_virtualtours ADD `background_image` varchar(50) DEFAULT NULL;");
    }
}
$result = $mysqli->query("SHOW COLUMNS FROM svt_virtualtours LIKE 'auto_start';");
if($result) {
    if ($result->num_rows==0) {
        $mysqli->query("ALTER TABLE svt_virtualtours ADD `auto_start` tinyint(1) NOT NULL DEFAULT '1';");
    }
}

//UPDATE 2.7
$result = $mysqli->query("SHOW COLUMNS FROM svt_virtualtours LIKE 'description';");
if($result) {
    if ($result->num_rows==0) {
        $mysqli->query("ALTER TABLE svt_virtualtours ADD `description` text DEFAULT NULL;");
    }
}
$result = $mysqli->query("SHOW COLUMNS FROM svt_virtualtours LIKE 'ga_tracking_id';");
if($result) {
    if ($result->num_rows==0) {
        $mysqli->query("ALTER TABLE svt_virtualtours ADD `ga_tracking_id` varchar(25) DEFAULT NULL;");
    }
}
$result = $mysqli->query("SHOW COLUMNS FROM svt_virtualtours LIKE 'friendly_url';");
if($result) {
    if ($result->num_rows==0) {
        $mysqli->query("ALTER TABLE svt_virtualtours ADD `friendly_url` varchar(100) DEFAULT NULL;");
    }
}

//UPDATE 2.8
$result = $mysqli->query("SHOW COLUMNS FROM svt_maps LIKE 'point_size';");
if($result) {
    if ($result->num_rows==0) {
        $mysqli->query("ALTER TABLE svt_maps ADD `point_size` int(11) NOT NULL DEFAULT '20';");
    }
}
$result = $mysqli->query("SHOW COLUMNS FROM svt_virtualtours LIKE 'compress_jpg';");
if($result) {
    if ($result->num_rows==0) {
        $mysqli->query("ALTER TABLE svt_virtualtours ADD `compress_jpg` int(11) NOT NULL DEFAULT '90';");
    }
}
$result = $mysqli->query("SHOW COLUMNS FROM svt_virtualtours LIKE 'active';");
if($result) {
    if ($result->num_rows==0) {
        $mysqli->query("ALTER TABLE svt_virtualtours ADD `active` tinyint(1) NOT NULL DEFAULT '1';");
    }
}
$result = $mysqli->query("SHOW COLUMNS FROM svt_rooms LIKE 'max_pitch';");
if($result) {
    if ($result->num_rows==0) {
        $mysqli->query("ALTER TABLE svt_rooms ADD `max_pitch` int(11) NOT NULL DEFAULT '90' AFTER `allow_pitch`;");
    }
}
$result = $mysqli->query("SHOW COLUMNS FROM svt_rooms LIKE 'min_pitch';");
if($result) {
    if ($result->num_rows==0) {
        $mysqli->query("ALTER TABLE svt_rooms ADD `min_pitch` int(11) NOT NULL DEFAULT '-90' AFTER `allow_pitch`;");
    }
}
$result = $mysqli->query("SHOW COLUMNS FROM svt_markers LIKE 'rotateZ';");
if($result) {
    if ($result->num_rows==0) {
        $mysqli->query("ALTER TABLE svt_markers ADD `rotateZ` int(11) NOT NULL DEFAULT '0' AFTER `yaw`;");
    }
}
$result = $mysqli->query("SHOW COLUMNS FROM svt_markers LIKE 'rotateX';");
if($result) {
    if ($result->num_rows==0) {
        $mysqli->query("ALTER TABLE svt_markers ADD `rotateX` int(11) NOT NULL DEFAULT '0' AFTER `yaw`;");
    }
}
$result = $mysqli->query("SHOW TABLES LIKE 'svt_settings';");
if($result) {
    if ($result->num_rows == 0) {
        $mysqli->query("CREATE TABLE IF NOT EXISTS `svt_settings` (
                              `id` int(11) NOT NULL DEFAULT '0',
                              `purchase_code` varchar(250) DEFAULT NULL,
                              `license` varchar(250) DEFAULT NULL,
                              `name` varchar(200) DEFAULT 'Simple Virtual Tour',
                              `logo` varchar(50) DEFAULT NULL,
                              `background` varchar(50) DEFAULT NULL,
                              PRIMARY KEY (`id`)
                            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;");
        $mysqli->query("INSERT IGNORE INTO `svt_settings` (`id`) VALUES(1);");
    }
}

//UPDATE 2.9
$result = $mysqli->query("SHOW TABLES LIKE 'svt_icons';");
if($result) {
    if ($result->num_rows == 0) {
        $mysqli->query("CREATE TABLE IF NOT EXISTS `svt_icons` (
                              `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
                              `id_virtualtour` bigint(20) unsigned DEFAULT NULL,
                              `image` varchar(50) DEFAULT NULL,
                              PRIMARY KEY (`id`),
                              KEY `id_virtualtour` (`id_virtualtour`),
                              CONSTRAINT `svt_icon_ibfk_1` FOREIGN KEY (`id_virtualtour`) REFERENCES `svt_virtualtours` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
                            ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;");
    }
}
$result = $mysqli->query("SHOW COLUMNS FROM svt_virtualtours LIKE 'pois_color';");
if($result) {
    if ($result->num_rows==0) {
        $mysqli->query("ALTER TABLE svt_virtualtours ADD `pois_color` varchar(25) NOT NULL DEFAULT '#000000' AFTER `markers_show_room`;");
        $mysqli->query("ALTER TABLE svt_virtualtours ADD `pois_background` varchar(25) NOT NULL DEFAULT 'rgba(255,255,255,0.7)' AFTER `markers_show_room`;");
        $mysqli->query("ALTER TABLE svt_virtualtours ADD `pois_icon` varchar(50) NOT NULL DEFAULT 'fas fa-info-circle' AFTER `markers_show_room`;");
        $mysqli->query("ALTER TABLE svt_virtualtours ADD `pois_style` tinyint(1) NOT NULL DEFAULT '0';");
    }
}
$result = $mysqli->query("SHOW COLUMNS FROM svt_pois LIKE 'style';");
if($result) {
    if ($result->num_rows==0) {
        $mysqli->query("ALTER TABLE svt_pois ADD `style` tinyint(1) NOT NULL DEFAULT '0' AFTER `type`;");
    }
}
$result = $mysqli->query("SHOW COLUMNS FROM svt_markers LIKE 'icon';");
if($result) {
    if ($result->num_rows==0) {
        $mysqli->query("ALTER TABLE svt_markers ADD `icon` varchar(50) NOT NULL DEFAULT 'fas fa-chevron-circle-up';");
        $mysqli->query("ALTER TABLE svt_markers ADD `color` varchar(25) NOT NULL DEFAULT '#000000';");
        $mysqli->query("ALTER TABLE svt_markers ADD `background` varchar(25) NOT NULL DEFAULT 'rgba(255,255,255,0.7)';");
        $mysqli->query("ALTER TABLE svt_markers ADD `show_room` tinyint(1) NOT NULL DEFAULT '1';");
        $query_v = "SELECT id,markers_icon,markers_color,markers_background,markers_show_room FROM svt_virtualtours;";
        $result_v = $mysqli->query($query_v);
        if($result_v) {
            if($result_v->num_rows>0) {
                while($row_v = $result_v->fetch_array(MYSQLI_ASSOC)) {
                    $id_vt = $row_v['id'];
                    $markers_icon = $row_v['markers_icon'];
                    $markers_color = $row_v['markers_color'];
                    $markers_background = $row_v['markers_background'];
                    $markers_show_room = $row_v['markers_show_room'];
                    $mysqli->query("UPDATE svt_markers SET icon='$markers_icon',color='$markers_color',background='$markers_background',show_room=$markers_show_room 
                                            WHERE id_room IN (SELECT id FROM svt_rooms WHERE id_virtualtour=$id_vt);");
                }
            }
        }
    }
}
$result = $mysqli->query("SHOW COLUMNS FROM svt_pois LIKE 'size_scale';");
if($result) {
    if ($result->num_rows==0) {
        $mysqli->query("ALTER TABLE svt_pois ADD `size_scale` float NOT NULL DEFAULT '1';");
    }
}
$result = $mysqli->query("SHOW COLUMNS FROM svt_markers LIKE 'size_scale';");
if($result) {
    if ($result->num_rows==0) {
        $mysqli->query("ALTER TABLE svt_markers ADD `size_scale` float NOT NULL DEFAULT '1';");
    }
}
$result = $mysqli->query("SHOW COLUMNS FROM svt_pois LIKE 'id_icon_library';");
if($result) {
    if ($result->num_rows==0) {
        $mysqli->query("ALTER TABLE svt_pois ADD `id_icon_library` bigint(20) unsigned NOT NULL DEFAULT '0' AFTER `icon`;");
    }
}
$result = $mysqli->query("SHOW COLUMNS FROM svt_markers LIKE 'id_icon_library';");
if($result) {
    if ($result->num_rows==0) {
        $mysqli->query("ALTER TABLE svt_markers ADD `id_icon_library` bigint(20) unsigned NOT NULL DEFAULT '0' AFTER `icon`;");
    }
}

//UPDATE 2.9.1
$result = $mysqli->query("SHOW COLUMNS FROM svt_pois LIKE 'type';");
if($result) {
    if ($result->num_rows==1) {
        $row = $result->fetch_array(MYSQLI_ASSOC);
        $type = $row['Type'];
        if (strpos($type, 'link_ext') === false) {
            $mysqli->query("ALTER TABLE svt_pois MODIFY COLUMN `type` enum('image','video','link','link_ext','html','html_sc','download') DEFAULT NULL;");
        }
    }
}

//UPDATE 2.9.2
$result = $mysqli->query("SHOW COLUMNS FROM svt_virtualtours LIKE 'link_logo';");
if($result) {
    if ($result->num_rows==0) {
        $mysqli->query("ALTER TABLE svt_virtualtours ADD `link_logo` varchar(250) DEFAULT NULL AFTER `logo`;");
    }
}

//UPDATE 3.0
$result = $mysqli->query("SHOW COLUMNS FROM svt_virtualtours LIKE 'max_width_compress';");
if($result) {
    if ($result->num_rows==0) {
        $mysqli->query("ALTER TABLE svt_virtualtours ADD `max_width_compress` int(11) NOT NULL DEFAULT '8192' AFTER `compress_jpg`;");
    }
}
$result = $mysqli->query("SHOW COLUMNS FROM svt_virtualtours LIKE 'sameAzimuth';");
if($result) {
    if ($result->num_rows==0) {
        $mysqli->query("ALTER TABLE svt_virtualtours ADD `sameAzimuth` tinyint(1) NOT NULL DEFAULT '0';");
    }
}
$result = $mysqli->query("SHOW COLUMNS FROM svt_pois LIKE 'access_count';");
if($result) {
    if ($result->num_rows==0) {
        $mysqli->query("ALTER TABLE svt_pois ADD `access_count` bigint(20) NOT NULL DEFAULT '0';");
    }
}
$result = $mysqli->query("SHOW COLUMNS FROM svt_rooms LIKE 'access_count';");
if($result) {
    if ($result->num_rows==0) {
        $mysqli->query("ALTER TABLE svt_rooms ADD `access_count` bigint(20) NOT NULL DEFAULT '0';");
    }
}
$result = $mysqli->query("SHOW TABLES LIKE 'svt_rooms_access_log';");
if($result) {
    if ($result->num_rows == 0) {
        $mysqli->query("CREATE TABLE `svt_rooms_access_log` (
                              `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
                              `id_room` bigint(20) unsigned DEFAULT NULL,
                              `time` int(11) DEFAULT NULL,
                              PRIMARY KEY (`id`),
                              KEY `id_room` (`id_room`),
                              CONSTRAINT `svt_rooms_access_log_ibfk_1` FOREIGN KEY (`id_room`) REFERENCES `svt_rooms` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
                            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;");
    }
}
$result = $mysqli->query("SHOW COLUMNS FROM svt_pois LIKE 'type';");
if($result) {
    if ($result->num_rows==1) {
        $row = $result->fetch_array(MYSQLI_ASSOC);
        $type = $row['Type'];
        if (strpos($type, 'form') === false) {
            $mysqli->query("ALTER TABLE svt_pois MODIFY COLUMN `type` enum('image','video','link','link_ext','html','html_sc','download','form') DEFAULT NULL;");
        }
    }
}
$result = $mysqli->query("SHOW TABLES LIKE 'svt_forms_data';");
if($result) {
    if ($result->num_rows == 0) {
        $mysqli->query("CREATE TABLE `svt_forms_data` (
                              `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
                              `id_virtualtour` bigint(20) unsigned NOT NULL,
                              `id_room` bigint(20) unsigned DEFAULT NULL,
                              `title` varchar(250) DEFAULT NULL,
                              `field1` text,
                              `field2` text,
                              `field3` text,
                              `field4` text,
                              `field5` text,
                              PRIMARY KEY (`id`),
                              KEY `id_virtualtour` (`id_virtualtour`),
                              CONSTRAINT `svt_forms_data_ibfk_1` FOREIGN KEY (`id_virtualtour`) REFERENCES `svt_virtualtours` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
                            ) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;");
    }
}

//UPDATE 3.1
$result = $mysqli->query("SHOW COLUMNS FROM svt_virtualtours LIKE 'auto_show_slider';");
if($result) {
    if ($result->num_rows==0) {
        $mysqli->query("ALTER TABLE svt_virtualtours ADD `auto_show_slider` tinyint(1) NOT NULL DEFAULT '0';");
    }
}
$result = $mysqli->query("SHOW COLUMNS FROM svt_virtualtours LIKE 'form_enable';");
if($result) {
    if ($result->num_rows==0) {
        $mysqli->query("ALTER TABLE svt_virtualtours ADD `form_enable` tinyint(1) NOT NULL DEFAULT '0';");
    }
}
$result = $mysqli->query("SHOW COLUMNS FROM svt_virtualtours LIKE 'form_icon';");
if($result) {
    if ($result->num_rows==0) {
        $mysqli->query("ALTER TABLE svt_virtualtours ADD `form_icon` varchar(50) NOT NULL DEFAULT 'fas fa-file-signature';");
    }
}
$result = $mysqli->query("SHOW COLUMNS FROM svt_virtualtours LIKE 'form_content';");
if($result) {
    if ($result->num_rows==0) {
        $mysqli->query("ALTER TABLE svt_virtualtours ADD `form_content` text DEFAULT NULL;");
    }
}

//UPDATE 3.2
$result = $mysqli->query("SHOW COLUMNS FROM svt_users LIKE 'email';");
if($result) {
    if ($result->num_rows==0) {
        $mysqli->query("ALTER TABLE svt_users ADD `email` varchar(100) DEFAULT NULL AFTER `username`;");
    }
}
$result = $mysqli->query("SHOW COLUMNS FROM svt_users LIKE 'forgot_code';");
if($result) {
    if ($result->num_rows==0) {
        $mysqli->query("ALTER TABLE svt_users ADD `forgot_code` varchar(16) DEFAULT NULL;");
    }
}
$result = $mysqli->query("SHOW COLUMNS FROM svt_settings LIKE 'smtp_server';");
if($result) {
    if ($result->num_rows==0) {
        $mysqli->query("ALTER TABLE svt_settings ADD `smtp_server` varchar(100) DEFAULT NULL;");
        $mysqli->query("ALTER TABLE svt_settings ADD `smtp_auth` tinyint(1) NOT NULL DEFAULT '0';");
        $mysqli->query("ALTER TABLE svt_settings ADD `smtp_username` varchar(100) DEFAULT NULL;");
        $mysqli->query("ALTER TABLE svt_settings ADD `smtp_password` varchar(100) DEFAULT NULL;");
        $mysqli->query("ALTER TABLE svt_settings ADD `smtp_secure` enum('none','ssl','tls') DEFAULT NULL;");
        $mysqli->query("ALTER TABLE svt_settings ADD `smtp_port` int(11) DEFAULT NULL;");
        $mysqli->query("ALTER TABLE svt_settings ADD `smtp_from_email` varchar(100) DEFAULT NULL;");
        $mysqli->query("ALTER TABLE svt_settings ADD `smtp_from_name` varchar(100) DEFAULT NULL;");
        $mysqli->query("ALTER TABLE svt_settings ADD `smtp_valid` tinyint(1) NOT NULL DEFAULT '0';");
    }
}
$result = $mysqli->query("SHOW COLUMNS FROM svt_pois LIKE 'label';");
if($result) {
    if ($result->num_rows==0) {
        $mysqli->query("ALTER TABLE svt_pois ADD `label` varchar(100) DEFAULT NULL AFTER `icon`;");
    }
}
$result = $mysqli->query("SHOW COLUMNS FROM svt_pois LIKE 'title';");
if($result) {
    if ($result->num_rows==0) {
        $mysqli->query("ALTER TABLE svt_pois ADD `title` varchar(100) DEFAULT NULL AFTER `color`;");
    }
}
$result = $mysqli->query("SHOW COLUMNS FROM svt_pois LIKE 'description';");
if($result) {
    if ($result->num_rows==0) {
        $mysqli->query("ALTER TABLE svt_pois ADD `description` text AFTER `title`;");
    }
}

//UPDATE 3.3
$result = $mysqli->query("SHOW COLUMNS FROM svt_rooms LIKE 'visible_list';");
if($result) {
    if ($result->num_rows==0) {
        $mysqli->query("ALTER TABLE svt_rooms ADD `visible_list` tinyint(1) NOT NULL DEFAULT '1';");
    }
}
$result = $mysqli->query("SHOW COLUMNS FROM svt_virtualtours LIKE 'html_landing';");
if($result) {
    if ($result->num_rows==0) {
        $mysqli->query("ALTER TABLE svt_virtualtours ADD `html_landing` longtext;");
    }
}
$result = $mysqli->query("SHOW COLUMNS FROM svt_virtualtours LIKE 'fb_messenger';");
if($result) {
    if ($result->num_rows==0) {
        $mysqli->query("ALTER TABLE svt_virtualtours ADD `fb_messenger` tinyint(1) NOT NULL DEFAULT '0';");
    }
}
$result = $mysqli->query("SHOW COLUMNS FROM svt_virtualtours LIKE 'fb_page_id';");
if($result) {
    if ($result->num_rows==0) {
        $mysqli->query("ALTER TABLE svt_virtualtours ADD `fb_page_id` varchar(50) DEFAULT NULL;");
    }
}

//UPDATE 3.4
$result = $mysqli->query("SHOW COLUMNS FROM svt_pois LIKE 'type';");
if($result) {
    if ($result->num_rows==1) {
        $row = $result->fetch_array(MYSQLI_ASSOC);
        $type = $row['Type'];
        if (strpos($type, 'video360') === false) {
            $mysqli->query("ALTER TABLE svt_pois MODIFY COLUMN `type` enum('image','video','link','link_ext','html','html_sc','download','form','video360') DEFAULT NULL;");
        }
    }
}
$result = $mysqli->query("SHOW COLUMNS FROM svt_settings LIKE 'enable_registration';");
if($result) {
    if ($result->num_rows==0) {
        $mysqli->query("ALTER TABLE svt_settings ADD `enable_registration` tinyint(1) NOT NULL DEFAULT '0';");
    }
}
$result = $mysqli->query("SHOW COLUMNS FROM svt_settings LIKE 'default_id_plan';");
if($result) {
    if ($result->num_rows==0) {
        $mysqli->query("ALTER TABLE svt_settings ADD `default_id_plan` bigint(20) unsigned DEFAULT '1';");
    }
}
$result = $mysqli->query("SHOW COLUMNS FROM svt_settings LIKE 'furl_blacklist';");
if($result) {
    if ($result->num_rows==0) {
        $mysqli->query("ALTER TABLE svt_settings ADD `furl_blacklist` text DEFAULT 'ajax,content,css,gallery,icons,js,maps,media,object360,panoramas,products,vendor';");
    }
}
$result = $mysqli->query("SHOW COLUMNS FROM svt_plans LIKE 'days';");
if($result) {
    if ($result->num_rows==0) {
        $mysqli->query("ALTER TABLE svt_plans ADD `days` int(11) NOT NULL DEFAULT '-1';");
    }
}
$result = $mysqli->query("SHOW COLUMNS FROM svt_users LIKE 'registration_date';");
if($result) {
    if ($result->num_rows==0) {
        $mysqli->query("ALTER TABLE svt_users ADD `registration_date` TIMESTAMP DEFAULT CURRENT_TIMESTAMP;");
    }
}
$result = $mysqli->query("SHOW COLUMNS FROM svt_users LIKE 'expire_plan_date';");
if($result) {
    if ($result->num_rows==0) {
        $mysqli->query("ALTER TABLE svt_users ADD `expire_plan_date` DATETIME DEFAULT NULL;");
    }
}
$result = $mysqli->query("SHOW COLUMNS FROM svt_rooms LIKE 'type';");
if($result) {
    if ($result->num_rows==0) {
        $mysqli->query("ALTER TABLE svt_rooms ADD `type` enum('image','video') DEFAULT 'image' AFTER `name`;");
    }
}
$result = $mysqli->query("SHOW COLUMNS FROM svt_rooms LIKE 'panorama_video';");
if($result) {
    if ($result->num_rows==0) {
        $mysqli->query("ALTER TABLE svt_rooms ADD `panorama_video` varchar(100) DEFAULT NULL AFTER `panorama_image`;");
    }
}
$result = $mysqli->query("SHOW COLUMNS FROM svt_plans LIKE 'create_landing';");
if($result) {
    if ($result->num_rows==0) {
        $mysqli->query("ALTER TABLE svt_plans ADD `create_landing` tinyint(1) NOT NULL DEFAULT '1';");
    }
}

//UPDATE 3.5
$result = $mysqli->query("SHOW COLUMNS FROM svt_virtualtours LIKE 'show_info';");
if($result) {
    if ($result->num_rows==0) {
        $mysqli->query("ALTER TABLE svt_virtualtours ADD `show_info` tinyint(1) NOT NULL DEFAULT '1';");
    }
}
$result = $mysqli->query("SHOW COLUMNS FROM svt_virtualtours LIKE 'show_gallery';");
if($result) {
    if ($result->num_rows==0) {
        $mysqli->query("ALTER TABLE svt_virtualtours ADD `show_gallery` tinyint(1) NOT NULL DEFAULT '1';");
    }
}
$result = $mysqli->query("SHOW COLUMNS FROM svt_virtualtours LIKE 'show_icons_toggle';");
if($result) {
    if ($result->num_rows==0) {
        $mysqli->query("ALTER TABLE svt_virtualtours ADD `show_icons_toggle` tinyint(1) NOT NULL DEFAULT '1';");
    }
}
$result = $mysqli->query("SHOW COLUMNS FROM svt_virtualtours LIKE 'show_presentation';");
if($result) {
    if ($result->num_rows==0) {
        $mysqli->query("ALTER TABLE svt_virtualtours ADD `show_presentation` tinyint(1) NOT NULL DEFAULT '1';");
    }
}
$result = $mysqli->query("SHOW COLUMNS FROM svt_virtualtours LIKE 'show_main_form';");
if($result) {
    if ($result->num_rows==0) {
        $mysqli->query("ALTER TABLE svt_virtualtours ADD `show_main_form` tinyint(1) NOT NULL DEFAULT '0';");
    }
}
$result = $mysqli->query("SHOW COLUMNS FROM svt_virtualtours LIKE 'show_share';");
if($result) {
    if ($result->num_rows==0) {
        $mysqli->query("ALTER TABLE svt_virtualtours ADD `show_share` tinyint(1) NOT NULL DEFAULT '1';");
    }
}
$result = $mysqli->query("SHOW COLUMNS FROM svt_virtualtours LIKE 'show_device_orientation';");
if($result) {
    if ($result->num_rows==0) {
        $mysqli->query("ALTER TABLE svt_virtualtours ADD `show_device_orientation` tinyint(1) NOT NULL DEFAULT '1';");
    }
}
$result = $mysqli->query("SHOW COLUMNS FROM svt_virtualtours LIKE 'show_webvr';");
if($result) {
    if ($result->num_rows==0) {
        $mysqli->query("ALTER TABLE svt_virtualtours ADD `show_webvr` tinyint(1) NOT NULL DEFAULT '1';");
    }
}
$result = $mysqli->query("SHOW COLUMNS FROM svt_virtualtours LIKE 'show_map';");
if($result) {
    if ($result->num_rows==0) {
        $mysqli->query("ALTER TABLE svt_virtualtours ADD `show_map` tinyint(1) NOT NULL DEFAULT '1';");
    }
}
$result = $mysqli->query("SHOW COLUMNS FROM svt_virtualtours LIKE 'show_fullscreen';");
if($result) {
    if ($result->num_rows==0) {
        $mysqli->query("ALTER TABLE svt_virtualtours ADD `show_fullscreen` tinyint(1) NOT NULL DEFAULT '1';");
    }
}
$result = $mysqli->query("SHOW COLUMNS FROM svt_virtualtours LIKE 'show_audio';");
if($result) {
    if ($result->num_rows==0) {
        $mysqli->query("ALTER TABLE svt_virtualtours ADD `show_audio` tinyint(1) NOT NULL DEFAULT '1';");
    }
}
$result = $mysqli->query("SHOW COLUMNS FROM svt_virtualtours LIKE 'live_session';");
if($result) {
    if ($result->num_rows==0) {
        $mysqli->query("ALTER TABLE svt_virtualtours ADD `live_session` tinyint(1) NOT NULL DEFAULT '0';");
    }
}
$result = $mysqli->query("SHOW COLUMNS FROM svt_rooms LIKE 'song';");
if($result) {
    if ($result->num_rows==0) {
        $mysqli->query("ALTER TABLE svt_rooms ADD `song` varchar(50) DEFAULT NULL;");
    }
}

//UPDATE 3.6
$result = $mysqli->query("SHOW COLUMNS FROM svt_rooms LIKE 'annotation_title';");
if($result) {
    if ($result->num_rows==0) {
        $mysqli->query("ALTER TABLE svt_rooms ADD `annotation_title` varchar(100) DEFAULT NULL;");
    }
}
$result = $mysqli->query("SHOW COLUMNS FROM svt_rooms LIKE 'annotation_description';");
if($result) {
    if ($result->num_rows==0) {
        $mysqli->query("ALTER TABLE svt_rooms ADD `annotation_description` text;");
    }
}
$result = $mysqli->query("SHOW COLUMNS FROM svt_virtualtours LIKE 'show_annotations';");
if($result) {
    if ($result->num_rows==0) {
        $mysqli->query("ALTER TABLE svt_virtualtours ADD `show_annotations` tinyint(1) NOT NULL DEFAULT '1';");
    }
}
$result = $mysqli->query("SHOW COLUMNS FROM svt_virtualtours LIKE 'show_list_alt';");
if($result) {
    if ($result->num_rows==0) {
        $mysqli->query("ALTER TABLE svt_virtualtours ADD `show_list_alt` tinyint(1) NOT NULL DEFAULT '0';");
    }
}
$result = $mysqli->query("SHOW COLUMNS FROM svt_virtualtours LIKE 'list_alt';");
if($result) {
    if ($result->num_rows==0) {
        $mysqli->query("ALTER TABLE svt_virtualtours ADD `list_alt` text;");
    }
}
$result = $mysqli->query("SHOW COLUMNS FROM svt_pois LIKE 'type';");
if($result) {
    if ($result->num_rows==1) {
        $row = $result->fetch_array(MYSQLI_ASSOC);
        $type = $row['Type'];
        if (strpos($type, 'audio') === false) {
            $mysqli->query("ALTER TABLE svt_pois MODIFY COLUMN `type` enum('image','video','link','link_ext','html','html_sc','download','form','video360','audio') DEFAULT NULL;");
        }
    }
}

//UPDATE 3.7
$result = $mysqli->query("SHOW TABLES LIKE 'svt_poi_gallery';");
if($result) {
    if ($result->num_rows == 0) {
        $mysqli->query("CREATE TABLE IF NOT EXISTS `svt_poi_gallery` (
                              `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
                              `id_poi` bigint(20) unsigned DEFAULT NULL,
                              `image` varchar(50) DEFAULT NULL,
                              `priority` int(11) NOT NULL DEFAULT '0',
                              PRIMARY KEY (`id`),
                              KEY `id_poi` (`id_poi`),
                              CONSTRAINT `svt_poi_gallery_ibfk_1` FOREIGN KEY (`id_poi`) REFERENCES `svt_pois` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
                            ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;");
    }
}
$result = $mysqli->query("SHOW COLUMNS FROM svt_pois LIKE 'type';");
if($result) {
    if ($result->num_rows==1) {
        $row = $result->fetch_array(MYSQLI_ASSOC);
        $type = $row['Type'];
        if (strpos($type, 'gallery') === false) {
            $mysqli->query("ALTER TABLE svt_pois MODIFY COLUMN `type` enum('image','video','link','link_ext','html','html_sc','download','form','video360','audio','gallery') DEFAULT NULL;");
        }
    }
}
$result = $mysqli->query("SHOW COLUMNS FROM svt_virtualtours LIKE 'intro_desktop';");
if($result) {
    if ($result->num_rows==0) {
        $mysqli->query("ALTER TABLE svt_virtualtours ADD `intro_desktop` varchar(50) DEFAULT NULL;");
    }
}
$result = $mysqli->query("SHOW COLUMNS FROM svt_virtualtours LIKE 'intro_mobile';");
if($result) {
    if ($result->num_rows==0) {
        $mysqli->query("ALTER TABLE svt_virtualtours ADD `intro_mobile` varchar(50) DEFAULT NULL;");
    }
}

//UPDATE 3.8
$result = $mysqli->query("SHOW COLUMNS FROM svt_virtualtours LIKE 'start_date';");
if($result) {
    if ($result->num_rows==0) {
        $mysqli->query("ALTER TABLE svt_virtualtours ADD `start_date` date DEFAULT NULL;");
    }
}
$result = $mysqli->query("SHOW COLUMNS FROM svt_virtualtours LIKE 'end_date';");
if($result) {
    if ($result->num_rows==0) {
        $mysqli->query("ALTER TABLE svt_virtualtours ADD `end_date` date DEFAULT NULL;");
    }
}
$result = $mysqli->query("SHOW COLUMNS FROM svt_virtualtours LIKE 'start_url';");
if($result) {
    if ($result->num_rows==0) {
        $mysqli->query("ALTER TABLE svt_virtualtours ADD `start_url` varchar(250) DEFAULT NULL;");
    }
}
$result = $mysqli->query("SHOW COLUMNS FROM svt_virtualtours LIKE 'end_url';");
if($result) {
    if ($result->num_rows==0) {
        $mysqli->query("ALTER TABLE svt_virtualtours ADD `end_url` varchar(250) DEFAULT NULL;");
    }
}
$result = $mysqli->query("SHOW COLUMNS FROM svt_maps LIKE 'north_degree';");
if($result) {
    if ($result->num_rows==0) {
        $mysqli->query("ALTER TABLE svt_maps ADD `north_degree` int(11) NOT NULL DEFAULT '0';");
    }
}
$result = $mysqli->query("SHOW COLUMNS FROM svt_pois LIKE 'target';");
if($result) {
    if ($result->num_rows==0) {
        $mysqli->query("ALTER TABLE svt_pois ADD `target` enum('_blank','_self') DEFAULT NULL AFTER `content`;");
        $mysqli->query("UPDATE svt_pois SET `target`='_blank' WHERE `type`='link_ext';");
    }
}
$result = $mysqli->query("SHOW COLUMNS FROM svt_virtualtours LIKE 'auto_presentation_speed';");
if($result) {
    if ($result->num_rows==0) {
        $mysqli->query("ALTER TABLE svt_virtualtours ADD `auto_presentation_speed` int(11) NOT NULL DEFAULT '5';");
    }
}

//UPDATE 3.9
$result = $mysqli->query("SHOW COLUMNS FROM svt_settings LIKE 'language';");
if($result) {
    if ($result->num_rows==0) {
        $mysqli->query("ALTER TABLE svt_settings ADD `language` varchar(10) NOT NULL DEFAULT 'en_US';");
    }
}
$result = $mysqli->query("SHOW COLUMNS FROM svt_settings LIKE 'language_domain';");
if($result) {
    if ($result->num_rows==0) {
        $mysqli->query("ALTER TABLE svt_settings ADD `language_domain` varchar(50) NOT NULL DEFAULT 'default';");
    }
}
$result = $mysqli->query("SHOW TABLES LIKE 'svt_assign_virtualtours';");
if($result) {
    if ($result->num_rows==0) {
        $mysqli->query("CREATE TABLE IF NOT EXISTS `svt_assign_virtualtours` (
                                  `id_user` int(11) unsigned DEFAULT NULL,
                                  `id_virtualtour` bigint(20) unsigned DEFAULT NULL,
                                  UNIQUE KEY `id_user` (`id_user`,`id_virtualtour`),
                                  KEY `id_virtualtour` (`id_virtualtour`),
                                  CONSTRAINT `svt_assign_virtualtours_ibfk_1` FOREIGN KEY (`id_user`) REFERENCES `svt_users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
                                  CONSTRAINT `svt_assign_virtualtours_ibfk_2` FOREIGN KEY (`id_virtualtour`) REFERENCES `svt_virtualtours` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
                                ) ENGINE=InnoDB DEFAULT CHARSET=utf8;");

    }
}
$result = $mysqli->query("SHOW COLUMNS FROM svt_gallery LIKE 'description';");
if($result) {
    if ($result->num_rows==0) {
        $mysqli->query("ALTER TABLE svt_gallery ADD `description` text AFTER `image`;");
    }
}
$result = $mysqli->query("SHOW COLUMNS FROM svt_gallery LIKE 'title';");
if($result) {
    if ($result->num_rows==0) {
        $mysqli->query("ALTER TABLE svt_gallery ADD `title` varchar(100) DEFAULT NULL AFTER `image`;");
    }
}
$result = $mysqli->query("SHOW COLUMNS FROM svt_poi_gallery LIKE 'description';");
if($result) {
    if ($result->num_rows==0) {
        $mysqli->query("ALTER TABLE svt_poi_gallery ADD `description` text AFTER `image`;");
    }
}
$result = $mysqli->query("SHOW COLUMNS FROM svt_poi_gallery LIKE 'title';");
if($result) {
    if ($result->num_rows==0) {
        $mysqli->query("ALTER TABLE svt_poi_gallery ADD `title` varchar(100) DEFAULT NULL AFTER `image`;");
    }
}

//UPDATE 4.0
$result = $mysqli->query("SHOW COLUMNS FROM svt_virtualtours LIKE 'enable_multires';");
if($result) {
    if ($result->num_rows==0) {
        $mysqli->query("ALTER TABLE svt_virtualtours ADD `enable_multires` tinyint(1) NOT NULL DEFAULT '0';");
    }
}
$result = $mysqli->query("SHOW COLUMNS FROM svt_rooms LIKE 'multires_status';");
if($result) {
    if ($result->num_rows==0) {
        $mysqli->query("ALTER TABLE svt_rooms ADD `multires_status` tinyint(1) NOT NULL DEFAULT '0';");
    }
}
$result = $mysqli->query("SHOW COLUMNS FROM svt_pois LIKE 'rotateZ';");
if($result) {
    if ($result->num_rows==0) {
        $mysqli->query("ALTER TABLE svt_pois ADD `rotateZ` int(11) NOT NULL DEFAULT '0' AFTER `yaw`;");
    }
}
$result = $mysqli->query("SHOW COLUMNS FROM svt_pois LIKE 'rotateX';");
if($result) {
    if ($result->num_rows==0) {
        $mysqli->query("ALTER TABLE svt_pois ADD `rotateX` int(11) NOT NULL DEFAULT '0' AFTER `yaw`;");
    }
}
$result = $mysqli->query("SHOW COLUMNS FROM svt_virtualtours LIKE 'whatsapp_number';");
if($result) {
    if ($result->num_rows==0) {
        $mysqli->query("ALTER TABLE svt_virtualtours ADD `whatsapp_number` varchar(25) DEFAULT NULL AFTER `fb_page_id`;");
    }
}
$result = $mysqli->query("SHOW COLUMNS FROM svt_virtualtours LIKE 'whatsapp_chat';");
if($result) {
    if ($result->num_rows==0) {
        $mysqli->query("ALTER TABLE svt_virtualtours ADD `whatsapp_chat` tinyint(1) NOT NULL DEFAULT '0' AFTER `fb_page_id`;");
    }
}
$result = $mysqli->query("SHOW COLUMNS FROM svt_virtualtours LIKE 'show_chat';");
if($result) {
    if ($result->num_rows==0) {
        $mysqli->query("ALTER TABLE svt_virtualtours ADD `show_chat` tinyint(1) NOT NULL DEFAULT '1' AFTER `show_gallery`;");
    }
}
$result = $mysqli->query("SHOW COLUMNS FROM svt_virtualtours LIKE 'markers_tooltip_type';");
if($result) {
    if ($result->num_rows==0) {
        $mysqli->query("ALTER TABLE svt_virtualtours ADD `markers_tooltip_type` enum('none','text','preview','room_name') NOT NULL DEFAULT 'none' AFTER `markers_show_room`;");
    }
}
$result = $mysqli->query("SHOW COLUMNS FROM svt_virtualtours LIKE 'pois_tooltip_type';");
if($result) {
    if ($result->num_rows==0) {
        $mysqli->query("ALTER TABLE svt_virtualtours ADD `pois_tooltip_type` enum('none','text') NOT NULL DEFAULT 'none' AFTER `pois_style`;");
    }
}
$result = $mysqli->query("SHOW COLUMNS FROM svt_markers LIKE 'tooltip_text';");
if($result) {
    if ($result->num_rows==0) {
        $mysqli->query("ALTER TABLE svt_markers ADD `tooltip_text` varchar(100) DEFAULT NULL AFTER `color`;");
    }
}
$result = $mysqli->query("SHOW COLUMNS FROM svt_markers LIKE 'tooltip_type';");
if($result) {
    if ($result->num_rows==0) {
        $mysqli->query("ALTER TABLE svt_markers ADD `tooltip_type` enum('none','text','preview','room_name') NOT NULL DEFAULT 'none' AFTER `color`;");
    }
}
$result = $mysqli->query("SHOW COLUMNS FROM svt_pois LIKE 'tooltip_text';");
if($result) {
    if ($result->num_rows==0) {
        $mysqli->query("ALTER TABLE svt_pois ADD `tooltip_text` varchar(100) DEFAULT NULL AFTER `color`;");
    }
}
$result = $mysqli->query("SHOW COLUMNS FROM svt_pois LIKE 'tooltip_type';");
if($result) {
    if ($result->num_rows==0) {
        $mysqli->query("ALTER TABLE svt_pois ADD `tooltip_type` enum('none','text') NOT NULL DEFAULT 'none' AFTER `color`;");
    }
}
$result = $mysqli->query("SHOW COLUMNS FROM svt_rooms LIKE 'audio_track_enable';");
if($result) {
    if ($result->num_rows==0) {
        $mysqli->query("ALTER TABLE svt_rooms ADD `audio_track_enable` tinyint(1) NOT NULL DEFAULT '0' AFTER `song`;");
    }
}
$result = $mysqli->query("SHOW COLUMNS FROM svt_virtualtours LIKE 'transition_loading';");
if($result) {
    if ($result->num_rows==0) {
        $mysqli->query("ALTER TABLE svt_virtualtours ADD `transition_loading` tinyint(1) NOT NULL DEFAULT '1';");
    }
}
$result = $mysqli->query("SHOW COLUMNS FROM svt_virtualtours LIKE 'transition_time';");
if($result) {
    if ($result->num_rows==0) {
        $mysqli->query("ALTER TABLE svt_virtualtours ADD `transition_time` int(11) NOT NULL DEFAULT '250';");
    }
}
$result = $mysqli->query("SHOW COLUMNS FROM svt_virtualtours LIKE 'transition_zoom';");
if($result) {
    if ($result->num_rows==0) {
        $mysqli->query("ALTER TABLE svt_virtualtours ADD `transition_zoom` int(11) NOT NULL DEFAULT '20';");
    }
}

//UPDATE 4.1
$result = $mysqli->query("SHOW COLUMNS FROM svt_virtualtours LIKE 'transition_fadeout';");
if($result) {
    if ($result->num_rows==0) {
        $mysqli->query("ALTER TABLE svt_virtualtours ADD `transition_fadeout` int(11) NOT NULL DEFAULT '400';");
    }
}
$result = $mysqli->query("SHOW COLUMNS FROM svt_virtualtours LIKE 'note';");
if($result) {
    if ($result->num_rows==0) {
        $mysqli->query("ALTER TABLE svt_virtualtours ADD `note` text DEFAULT NULL;");
    }
}
$result = $mysqli->query("SHOW COLUMNS FROM svt_plans LIKE 'create_gallery';");
if($result) {
    if ($result->num_rows==0) {
        $mysqli->query("ALTER TABLE svt_plans ADD `create_gallery` tinyint(1) NOT NULL DEFAULT '1';");
    }
}
$result = $mysqli->query("SHOW COLUMNS FROM svt_plans LIKE 'create_presentation';");
if($result) {
    if ($result->num_rows==0) {
        $mysqli->query("ALTER TABLE svt_plans ADD `create_presentation` tinyint(1) NOT NULL DEFAULT '1';");
    }
}
$result = $mysqli->query("SHOW COLUMNS FROM svt_plans LIKE 'price';");
if($result) {
    if ($result->num_rows==0) {
        $mysqli->query("ALTER TABLE svt_plans ADD `price` float NOT NULL DEFAULT '0';");
    }
}
$result = $mysqli->query("SHOW COLUMNS FROM svt_plans LIKE 'currency';");
if($result) {
    if ($result->num_rows==0) {
        $mysqli->query("ALTER TABLE svt_plans ADD `currency` varchar(3) NOT NULL DEFAULT 'USD';");
    }
}
$result = $mysqli->query("SHOW COLUMNS FROM svt_virtualtours LIKE 'markers_tooltip_type';");
if($result) {
    if ($result->num_rows==1) {
        $row = $result->fetch_array(MYSQLI_ASSOC);
        $type = $row['Type'];
        if (strpos($type, 'preview_square') === false) {
            $mysqli->query("ALTER TABLE svt_virtualtours MODIFY COLUMN `markers_tooltip_type` enum('none','text','preview','preview_square','preview_rect','room_name') NOT NULL DEFAULT 'none';");
        }
    }
}
$result = $mysqli->query("SHOW COLUMNS FROM svt_markers LIKE 'tooltip_type';");
if($result) {
    if ($result->num_rows==1) {
        $row = $result->fetch_array(MYSQLI_ASSOC);
        $type = $row['Type'];
        if (strpos($type, 'preview_square') === false) {
            $mysqli->query("ALTER TABLE svt_markers MODIFY COLUMN `tooltip_type` enum('none','text','preview','preview_square','preview_rect','room_name') NOT NULL DEFAULT 'none';");
        }
    }
}
$result = $mysqli->query("SHOW COLUMNS FROM svt_settings LIKE 'contact_email';");
if($result) {
    if ($result->num_rows==0) {
        $mysqli->query("ALTER TABLE svt_settings ADD `contact_email` varchar(100) DEFAULT NULL;");
    }
}
$result = $mysqli->query("SHOW COLUMNS FROM svt_settings LIKE 'change_plan';");
if($result) {
    if ($result->num_rows==0) {
        $mysqli->query("ALTER TABLE svt_settings ADD `change_plan` tinyint(1) NOT NULL DEFAULT '0';");
    }
}

//UPDATE 4.2
$result = $mysqli->query("SHOW COLUMNS FROM svt_plans LIKE 'id_product_stripe';");
if($result) {
    if ($result->num_rows==0) {
        $mysqli->query("ALTER TABLE svt_plans ADD `id_product_stripe` varchar(50) DEFAULT NULL;");
    }
}
$result = $mysqli->query("SHOW COLUMNS FROM svt_plans LIKE 'id_price_stripe';");
if($result) {
    if ($result->num_rows==0) {
        $mysqli->query("ALTER TABLE svt_plans ADD `id_price_stripe` varchar(50) DEFAULT NULL;");
    }
}
$result = $mysqli->query("SHOW COLUMNS FROM svt_settings LIKE 'stripe_enabled';");
if($result) {
    if ($result->num_rows==0) {
        $mysqli->query("ALTER TABLE svt_settings ADD `stripe_enabled` tinyint(1) NOT NULL DEFAULT '0';");
    }
}
$result = $mysqli->query("SHOW COLUMNS FROM svt_settings LIKE 'stripe_secret_key';");
if($result) {
    if ($result->num_rows==0) {
        $mysqli->query("ALTER TABLE svt_settings ADD `stripe_secret_key` varchar(200) DEFAULT NULL;");
    }
}
$result = $mysqli->query("SHOW COLUMNS FROM svt_settings LIKE 'stripe_public_key';");
if($result) {
    if ($result->num_rows==0) {
        $mysqli->query("ALTER TABLE svt_settings ADD `stripe_public_key` varchar(200) DEFAULT NULL;");
    }
}
$result = $mysqli->query("SHOW COLUMNS FROM svt_users LIKE 'id_customer_stripe';");
if($result) {
    if ($result->num_rows==0) {
        $mysqli->query("ALTER TABLE svt_users ADD `id_customer_stripe` varchar(50) DEFAULT NULL;");
    }
}
$result = $mysqli->query("SHOW COLUMNS FROM svt_users LIKE 'id_subscription_stripe';");
if($result) {
    if ($result->num_rows==0) {
        $mysqli->query("ALTER TABLE svt_users ADD `id_subscription_stripe` varchar(50) DEFAULT NULL;");
    }
}
$result = $mysqli->query("SHOW COLUMNS FROM svt_users LIKE 'status_subscription_stripe';");
if($result) {
    if ($result->num_rows==0) {
        $mysqli->query("ALTER TABLE svt_users ADD `status_subscription_stripe` tinyint(1) NOT NULL DEFAULT '0';");
    }
}
$result = $mysqli->query("SHOW COLUMNS FROM svt_users LIKE 'language';");
if($result) {
    if ($result->num_rows==0) {
        $mysqli->query("ALTER TABLE svt_users ADD `language` varchar(10) DEFAULT NULL;");
    }
}
$result = $mysqli->query("SHOW COLUMNS FROM svt_virtualtours LIKE 'show_vt_title';");
if($result) {
    if ($result->num_rows==0) {
        $mysqli->query("ALTER TABLE svt_virtualtours ADD `show_vt_title` tinyint(1) NOT NULL DEFAULT '1' AFTER `show_info`;");
    }
}
$result = $mysqli->query("SHOW COLUMNS FROM svt_maps LIKE 'priority';");
if($result) {
    if ($result->num_rows==0) {
        $mysqli->query("ALTER TABLE svt_maps ADD `priority` int(11) NOT NULL DEFAULT '0';");
    }
}
$result = $mysqli->query("SHOW COLUMNS FROM svt_markers LIKE 'yaw_room_target';");
if($result) {
    if ($result->num_rows==0) {
        $mysqli->query("ALTER TABLE svt_markers ADD `yaw_room_target` int(11) DEFAULT NULL AFTER `id_room_target`;");
    }
}
$result = $mysqli->query("SHOW COLUMNS FROM svt_markers LIKE 'pitch_room_target';");
if($result) {
    if ($result->num_rows==0) {
        $mysqli->query("ALTER TABLE svt_markers ADD `pitch_room_target` int(11) DEFAULT NULL AFTER `id_room_target`;");
    }
}

//UPDATE 4.2.1
$result = $mysqli->query("SHOW COLUMNS FROM svt_settings LIKE 'version';");
if($result) {
    if ($result->num_rows==0) {
        $mysqli->query("ALTER TABLE svt_settings ADD `version` varchar(10) NOT NULL DEFAULT '';");
    }
}

//UPDATE 4.3
$result = $mysqli->query("SHOW COLUMNS FROM svt_pois LIKE 'tooltip_text';");
if($result) {
    if ($result->num_rows==1) {
        $row = $result->fetch_array(MYSQLI_ASSOC);
        $type = $row['Type'];
        if (strpos($type, 'text') === false) {
            $mysqli->query("ALTER TABLE svt_pois MODIFY COLUMN `tooltip_text` text;");
        }
    }
}
$result = $mysqli->query("SHOW COLUMNS FROM svt_plans LIKE 'enable_live_session';");
if($result) {
    if ($result->num_rows==0) {
        $mysqli->query("ALTER TABLE svt_plans ADD `enable_live_session` tinyint(1) NOT NULL DEFAULT '1' AFTER `create_presentation`;");
    }
}
$result = $mysqli->query("SHOW COLUMNS FROM svt_users LIKE 'hash';");
if($result) {
    if ($result->num_rows==0) {
        $mysqli->query("ALTER TABLE svt_users ADD `hash` varchar(36) DEFAULT NULL AFTER `forgot_code`;");
    }
}
$result = $mysqli->query("SHOW COLUMNS FROM svt_settings LIKE 'validate_email';");
if($result) {
    if ($result->num_rows==0) {
        $mysqli->query("ALTER TABLE svt_settings ADD `validate_email` tinyint(1) NOT NULL DEFAULT '0' AFTER `enable_registration`;");
    }
}
$result = $mysqli->query("SHOW COLUMNS FROM svt_rooms LIKE 'h_roll';");
if($result) {
    if ($result->num_rows==0) {
        $mysqli->query("ALTER TABLE svt_rooms ADD `h_roll` int(11) NOT NULL DEFAULT '0' AFTER `yaw`;");
    }
}
$result = $mysqli->query("SHOW COLUMNS FROM svt_rooms LIKE 'h_pitch';");
if($result) {
    if ($result->num_rows==0) {
        $mysqli->query("ALTER TABLE svt_rooms ADD `h_pitch` int(11) NOT NULL DEFAULT '0' AFTER `yaw`;");
    }
}

//UPDATE 4.4
$result = $mysqli->query("SHOW COLUMNS FROM svt_rooms LIKE 'passcode_title';");
if($result) {
    if ($result->num_rows==0) {
        $mysqli->query("ALTER TABLE svt_rooms ADD `passcode_title` varchar(250) DEFAULT NULL;");
    }
}
$result = $mysqli->query("SHOW COLUMNS FROM svt_rooms LIKE 'passcode_description';");
if($result) {
    if ($result->num_rows==0) {
        $mysqli->query("ALTER TABLE svt_rooms ADD `passcode_description` text;");
    }
}
$result = $mysqli->query("SHOW COLUMNS FROM svt_rooms LIKE 'passcode';");
if($result) {
    if ($result->num_rows==0) {
        $mysqli->query("ALTER TABLE svt_rooms ADD `passcode` varchar(32) DEFAULT NULL;");
    }
}
$result = $mysqli->query("SHOW COLUMNS FROM svt_rooms LIKE 'transition_time';");
if($result) {
    if ($result->num_rows==0) {
        $mysqli->query("ALTER TABLE svt_rooms ADD `transition_time` int(11) NOT NULL DEFAULT '250';");
    }
}
$result = $mysqli->query("SHOW COLUMNS FROM svt_rooms LIKE 'transition_zoom';");
if($result) {
    if ($result->num_rows==0) {
        $mysqli->query("ALTER TABLE svt_rooms ADD `transition_zoom` int(11) NOT NULL DEFAULT '20';");
    }
}
$result = $mysqli->query("SHOW COLUMNS FROM svt_rooms LIKE 'transition_fadeout';");
if($result) {
    if ($result->num_rows==0) {
        $mysqli->query("ALTER TABLE svt_rooms ADD `transition_fadeout` int(11) NOT NULL DEFAULT '400';");
    }
}
$result = $mysqli->query("SHOW COLUMNS FROM svt_rooms LIKE 'transition_override';");
if($result) {
    if ($result->num_rows==0) {
        $mysqli->query("ALTER TABLE svt_rooms ADD `transition_override` tinyint(1) NOT NULL DEFAULT '0';");
    }
}
$result = $mysqli->query("SHOW COLUMNS FROM svt_virtualtours LIKE 'flyin';");
if($result) {
    if ($result->num_rows==0) {
        $mysqli->query("ALTER TABLE svt_virtualtours ADD `flyin` tinyint(1) NOT NULL DEFAULT '0';");
    }
}

//UPDATE 4.5
$result = $mysqli->query("SHOW COLUMNS FROM svt_rooms LIKE 'protect_type';");
if($result) {
    if ($result->num_rows==0) {
        $mysqli->query("ALTER TABLE svt_rooms ADD `protect_type` enum('none','passcode','leads') DEFAULT 'none' AFTER `passcode`;");
        $mysqli->query("UPDATE svt_rooms SET protect_type='passcode' WHERE passcode IS NOT NULL;");
    }
}
$result = $mysqli->query("SHOW TABLES LIKE 'svt_leads';");
if($result) {
    if ($result->num_rows==0) {
        $mysqli->query("CREATE TABLE IF NOT EXISTS `svt_leads` (
                              `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
                              `id_virtualtour` bigint(20) unsigned NOT NULL,
                              `name` varchar(250) DEFAULT NULL,
                              `email` varchar(250) DEFAULT NULL,
                              `phone` varchar(25) DEFAULT NULL,
                              PRIMARY KEY (`id`),
                              KEY `id_virtualtour` (`id_virtualtour`),
                              CONSTRAINT `svt_leads_ibfk_1` FOREIGN KEY (`id_virtualtour`) REFERENCES `svt_virtualtours` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
                            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;");
        }
}
$result = $mysqli->query("SHOW COLUMNS FROM svt_rooms LIKE 'vaov';");
if($result) {
    if ($result->num_rows==0) {
        $mysqli->query("ALTER TABLE svt_rooms ADD `vaov` int(11) NOT NULL DEFAULT '180' AFTER `max_pitch`;");
    }
}
$result = $mysqli->query("SHOW COLUMNS FROM svt_rooms LIKE 'haov';");
if($result) {
    if ($result->num_rows==0) {
        $mysqli->query("ALTER TABLE svt_rooms ADD `haov` int(11) NOT NULL DEFAULT '360' AFTER `max_pitch`;");
    }
}
$result = $mysqli->query("SHOW COLUMNS FROM svt_rooms LIKE 'max_yaw';");
if($result) {
    if ($result->num_rows==0) {
        $mysqli->query("ALTER TABLE svt_rooms ADD `max_yaw` int(11) NOT NULL DEFAULT '180' AFTER `max_pitch`;");
    }
}
$result = $mysqli->query("SHOW COLUMNS FROM svt_rooms LIKE 'min_yaw';");
if($result) {
    if ($result->num_rows==0) {
        $mysqli->query("ALTER TABLE svt_rooms ADD `min_yaw` int(11) NOT NULL DEFAULT '-180' AFTER `max_pitch`;");
    }
}
$result = $mysqli->query("SHOW TABLES LIKE 'svt_rooms_alt';");
if($result) {
    if ($result->num_rows==0) {
        $mysqli->query("CREATE TABLE IF NOT EXISTS `svt_rooms_alt` (
                              `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
                              `id_room` bigint(20) unsigned NOT NULL,
                              `panorama_image` varchar(100) DEFAULT NULL,
                              `multires_status` tinyint(1) NOT NULL DEFAULT '0',
                              PRIMARY KEY (`id`),
                              KEY `id_room` (`id_room`),
                              CONSTRAINT `svt_rooms_alt_ibfk_1` FOREIGN KEY (`id_room`) REFERENCES `svt_rooms` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
                            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;");
    }
}
$result = $mysqli->query("SHOW COLUMNS FROM svt_virtualtours LIKE 'show_vt_title';");
if($result) {
    if ($result->num_rows==1) {
        $row = $result->fetch_array(MYSQLI_ASSOC);
        $default = $row['Default'];
        if($default==0) {
            $mysqli->query("ALTER TABLE svt_virtualtours MODIFY COLUMN `show_vt_title` tinyint(1) NOT NULL DEFAULT '1';");
        }
    }
}

//UPDATE 4.6
$result = $mysqli->query("SHOW COLUMNS FROM svt_pois LIKE 'schedule';");
if($result) {
    if ($result->num_rows==0) {
        $mysqli->query("ALTER TABLE svt_pois ADD `schedule` varchar(250) DEFAULT NULL;");
    }
}
$result = $mysqli->query("SHOW COLUMNS FROM svt_rooms LIKE 'filters';");
if($result) {
    if ($result->num_rows==0) {
        $mysqli->query("ALTER TABLE svt_rooms ADD `filters` varchar(250) DEFAULT NULL;");
    }
}

//UPDATE 4.7
$result = $mysqli->query("SHOW COLUMNS FROM svt_virtualtours LIKE 'meeting';");
if($result) {
    if ($result->num_rows==0) {
        $mysqli->query("ALTER TABLE svt_virtualtours ADD `meeting` tinyint(1) NOT NULL DEFAULT '0' AFTER `live_session`;");
    }
}
$result = $mysqli->query("SHOW COLUMNS FROM svt_plans LIKE 'custom_features';");
if($result) {
    if ($result->num_rows==0) {
        $mysqli->query("ALTER TABLE svt_plans ADD `custom_features` text;");
    }
}
$result = $mysqli->query("SHOW COLUMNS FROM svt_plans LIKE 'enable_chat';");
if($result) {
    if ($result->num_rows==0) {
        $mysqli->query("ALTER TABLE svt_plans ADD `enable_chat` tinyint(1) NOT NULL DEFAULT '1';");
    }
}
$result = $mysqli->query("SHOW COLUMNS FROM svt_plans LIKE 'enable_voice_commands';");
if($result) {
    if ($result->num_rows==0) {
        $mysqli->query("ALTER TABLE svt_plans ADD `enable_voice_commands` tinyint(1) NOT NULL DEFAULT '1';");
    }
}
$result = $mysqli->query("SHOW COLUMNS FROM svt_plans LIKE 'enable_voice_commands';");
if($result) {
    if ($result->num_rows==0) {
        $mysqli->query("ALTER TABLE svt_plans ADD `enable_voice_commands` tinyint(1) NOT NULL DEFAULT '1';");
    }
}
$result = $mysqli->query("SHOW COLUMNS FROM svt_plans LIKE 'enable_share';");
if($result) {
    if ($result->num_rows==0) {
        $mysqli->query("ALTER TABLE svt_plans ADD `enable_share` tinyint(1) NOT NULL DEFAULT '1';");
    }
}
$result = $mysqli->query("SHOW COLUMNS FROM svt_plans LIKE 'enable_device_orientation';");
if($result) {
    if ($result->num_rows==0) {
        $mysqli->query("ALTER TABLE svt_plans ADD `enable_device_orientation` tinyint(1) NOT NULL DEFAULT '1';");
    }
}
$result = $mysqli->query("SHOW COLUMNS FROM svt_plans LIKE 'enable_webvr';");
if($result) {
    if ($result->num_rows==0) {
        $mysqli->query("ALTER TABLE svt_plans ADD `enable_webvr` tinyint(1) NOT NULL DEFAULT '1';");
    }
}
$result = $mysqli->query("SHOW COLUMNS FROM svt_plans LIKE 'enable_logo';");
if($result) {
    if ($result->num_rows==0) {
        $mysqli->query("ALTER TABLE svt_plans ADD `enable_logo` tinyint(1) NOT NULL DEFAULT '1';");
    }
}
$result = $mysqli->query("SHOW COLUMNS FROM svt_plans LIKE 'enable_nadir_logo';");
if($result) {
    if ($result->num_rows==0) {
        $mysqli->query("ALTER TABLE svt_plans ADD `enable_nadir_logo` tinyint(1) NOT NULL DEFAULT '1';");
    }
}
$result = $mysqli->query("SHOW COLUMNS FROM svt_plans LIKE 'enable_song';");
if($result) {
    if ($result->num_rows==0) {
        $mysqli->query("ALTER TABLE svt_plans ADD `enable_song` tinyint(1) NOT NULL DEFAULT '1';");
    }
}
$result = $mysqli->query("SHOW COLUMNS FROM svt_plans LIKE 'enable_forms';");
if($result) {
    if ($result->num_rows==0) {
        $mysqli->query("ALTER TABLE svt_plans ADD `enable_forms` tinyint(1) NOT NULL DEFAULT '1';");
    }
}
$result = $mysqli->query("SHOW COLUMNS FROM svt_plans LIKE 'enable_logo';");
if($result) {
    if ($result->num_rows==0) {
        $mysqli->query("ALTER TABLE svt_plans ADD `enable_logo` tinyint(1) NOT NULL DEFAULT '1';");
    }
}
$result = $mysqli->query("SHOW COLUMNS FROM svt_plans LIKE 'enable_annotations';");
if($result) {
    if ($result->num_rows==0) {
        $mysqli->query("ALTER TABLE svt_plans ADD `enable_annotations` tinyint(1) NOT NULL DEFAULT '1';");
    }
}
$result = $mysqli->query("SHOW COLUMNS FROM svt_plans LIKE 'enable_logo';");
if($result) {
    if ($result->num_rows==0) {
        $mysqli->query("ALTER TABLE svt_plans ADD `enable_logo` tinyint(1) NOT NULL DEFAULT '1';");
    }
}
$result = $mysqli->query("SHOW COLUMNS FROM svt_plans LIKE 'enable_rooms_multiple';");
if($result) {
    if ($result->num_rows==0) {
        $mysqli->query("ALTER TABLE svt_plans ADD `enable_rooms_multiple` tinyint(1) NOT NULL DEFAULT '1';");
    }
}
$result = $mysqli->query("SHOW COLUMNS FROM svt_plans LIKE 'enable_rooms_protect';");
if($result) {
    if ($result->num_rows==0) {
        $mysqli->query("ALTER TABLE svt_plans ADD `enable_rooms_protect` tinyint(1) NOT NULL DEFAULT '1';");
    }
}
$result = $mysqli->query("SHOW COLUMNS FROM svt_plans LIKE 'enable_info_box';");
if($result) {
    if ($result->num_rows==0) {
        $mysqli->query("ALTER TABLE svt_plans ADD `enable_info_box` tinyint(1) NOT NULL DEFAULT '1';");
    }
}
$result = $mysqli->query("SHOW COLUMNS FROM svt_plans LIKE 'enable_maps';");
if($result) {
    if ($result->num_rows==0) {
        $mysqli->query("ALTER TABLE svt_plans ADD `enable_maps` tinyint(1) NOT NULL DEFAULT '1';");
    }
}
$result = $mysqli->query("SHOW COLUMNS FROM svt_plans LIKE 'enable_icons_library';");
if($result) {
    if ($result->num_rows==0) {
        $mysqli->query("ALTER TABLE svt_plans ADD `enable_icons_library` tinyint(1) NOT NULL DEFAULT '1';");
    }
}
$result = $mysqli->query("SHOW COLUMNS FROM svt_plans LIKE 'enable_password_tour';");
if($result) {
    if ($result->num_rows==0) {
        $mysqli->query("ALTER TABLE svt_plans ADD `enable_password_tour` tinyint(1) NOT NULL DEFAULT '1';");
    }
}
$result = $mysqli->query("SHOW COLUMNS FROM svt_plans LIKE 'enable_expiring_dates';");
if($result) {
    if ($result->num_rows==0) {
        $mysqli->query("ALTER TABLE svt_plans ADD `enable_expiring_dates` tinyint(1) NOT NULL DEFAULT '1';");
    }
}
$result = $mysqli->query("SHOW COLUMNS FROM svt_plans LIKE 'enable_statistics';");
if($result) {
    if ($result->num_rows==0) {
        $mysqli->query("ALTER TABLE svt_plans ADD `enable_statistics` tinyint(1) NOT NULL DEFAULT '1';");
    }
}
$result = $mysqli->query("SHOW COLUMNS FROM svt_plans LIKE 'enable_flyin';");
if($result) {
    if ($result->num_rows==0) {
        $mysqli->query("ALTER TABLE svt_plans ADD `enable_flyin` tinyint(1) NOT NULL DEFAULT '1';");
    }
}
$result = $mysqli->query("SHOW COLUMNS FROM svt_plans LIKE 'enable_auto_rotate';");
if($result) {
    if ($result->num_rows==0) {
        $mysqli->query("ALTER TABLE svt_plans ADD `enable_auto_rotate` tinyint(1) NOT NULL DEFAULT '1';");
    }
}
$result = $mysqli->query("SHOW COLUMNS FROM svt_plans LIKE 'enable_multires';");
if($result) {
    if ($result->num_rows==0) {
        $mysqli->query("ALTER TABLE svt_plans ADD `enable_multires` tinyint(1) NOT NULL DEFAULT '1';");
    }
}
$result = $mysqli->query("SHOW COLUMNS FROM svt_plans LIKE 'enable_meeting';");
if($result) {
    if ($result->num_rows==0) {
        $mysqli->query("ALTER TABLE svt_plans ADD `enable_meeting` tinyint(1) NOT NULL DEFAULT '1';");
    }
}
$result = $mysqli->query("SHOW COLUMNS FROM svt_plans LIKE 'max_file_size_upload';");
if($result) {
    if ($result->num_rows==0) {
        $mysqli->query("ALTER TABLE svt_plans ADD `max_file_size_upload` int(11) NOT NULL DEFAULT '-1';");
    }
}

//UPDATE 4.8
$result = $mysqli->query("SHOW COLUMNS FROM svt_rooms LIKE 'blur';");
if($result) {
    if ($result->num_rows==0) {
        $mysqli->query("ALTER TABLE svt_rooms ADD `blur` tinyint(1) NOT NULL DEFAULT '0';");
    }
}
$result = $mysqli->query("SHOW TABLES LIKE 'svt_showcases';");
if($result) {
    if ($result->num_rows==0) {
        $mysqli->query("CREATE TABLE IF NOT EXISTS `svt_showcases` (
                                  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
                                  `id_user` int(11) unsigned DEFAULT NULL,
                                  `code` varchar(100) DEFAULT NULL,
                                  `name` varchar(250) DEFAULT NULL,
                                  `friendly_url` varchar(100) DEFAULT NULL,
                                  `banner` varchar(100) DEFAULT NULL,
                                  `logo` varchar(100) DEFAULT NULL,
                                  `bg_color` varchar(10) NOT NULL DEFAULT '#EEEEEE',
                                  PRIMARY KEY (`id`),
                                  KEY `id_user` (`id_user`),
                                  CONSTRAINT `svt_showcases_ibfk_1` FOREIGN KEY (`id_user`) REFERENCES `svt_users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
                                ) ENGINE=InnoDB DEFAULT CHARSET=utf8;");
    }
}
$result = $mysqli->query("SHOW TABLES LIKE 'svt_showcase_list';");
if($result) {
    if ($result->num_rows==0) {
        $mysqli->query("CREATE TABLE IF NOT EXISTS `svt_showcase_list` (
                                  `id_showcase` bigint(20) unsigned DEFAULT NULL,
                                  `id_virtualtour` bigint(20) unsigned DEFAULT NULL,
                                  KEY `id_showcase` (`id_showcase`),
                                  KEY `id_virtualtour` (`id_virtualtour`),
                                  CONSTRAINT `svt_showcase_list_ibfk_1` FOREIGN KEY (`id_showcase`) REFERENCES `svt_showcases` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
                                  CONSTRAINT `svt_showcase_list_ibfk_2` FOREIGN KEY (`id_virtualtour`) REFERENCES `svt_virtualtours` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
                                ) ENGINE=InnoDB DEFAULT CHARSET=utf8;");
    }
}
$result = $mysqli->query("SHOW COLUMNS FROM svt_plans LIKE 'create_showcase';");
if($result) {
    if ($result->num_rows==0) {
        $mysqli->query("ALTER TABLE svt_plans ADD `create_showcase` tinyint(1) NOT NULL DEFAULT '1';");
    }
}
$result = $mysqli->query("SHOW COLUMNS FROM svt_rooms LIKE 'thumb_image';");
if($result) {
    if ($result->num_rows == 0) {
        $mysqli->query("ALTER TABLE `svt_rooms` ADD `thumb_image` varchar(100) DEFAULT NULL AFTER `panorama_image`");
    }
}

//UPDATE 4.9
$result = $mysqli->query("SHOW COLUMNS FROM svt_settings LIKE 'social_google_enable';");
if($result) {
    if ($result->num_rows==0) {
        $mysqli->query("ALTER TABLE svt_settings ADD `social_google_enable` tinyint(1) NOT NULL DEFAULT '0';");
    }
}
$result = $mysqli->query("SHOW COLUMNS FROM svt_settings LIKE 'social_facebook_enable';");
if($result) {
    if ($result->num_rows==0) {
        $mysqli->query("ALTER TABLE svt_settings ADD `social_facebook_enable` tinyint(1) NOT NULL DEFAULT '0';");
    }
}
$result = $mysqli->query("SHOW COLUMNS FROM svt_settings LIKE 'social_twitter_enable';");
if($result) {
    if ($result->num_rows==0) {
        $mysqli->query("ALTER TABLE svt_settings ADD `social_twitter_enable` tinyint(1) NOT NULL DEFAULT '0';");
    }
}
$result = $mysqli->query("SHOW COLUMNS FROM svt_settings LIKE 'social_google_id';");
if($result) {
    if ($result->num_rows==0) {
        $mysqli->query("ALTER TABLE svt_settings ADD `social_google_id` varchar(200) DEFAULT NULL;");
    }
}
$result = $mysqli->query("SHOW COLUMNS FROM svt_settings LIKE 'social_google_secret';");
if($result) {
    if ($result->num_rows==0) {
        $mysqli->query("ALTER TABLE svt_settings ADD `social_google_secret` varchar(200) DEFAULT NULL;");
    }
}
$result = $mysqli->query("SHOW COLUMNS FROM svt_settings LIKE 'social_facebook_id';");
if($result) {
    if ($result->num_rows==0) {
        $mysqli->query("ALTER TABLE svt_settings ADD `social_facebook_id` varchar(200) DEFAULT NULL;");
    }
}
$result = $mysqli->query("SHOW COLUMNS FROM svt_settings LIKE 'social_facebook_secret';");
if($result) {
    if ($result->num_rows==0) {
        $mysqli->query("ALTER TABLE svt_settings ADD `social_facebook_secret` varchar(200) DEFAULT NULL;");
    }
}
$result = $mysqli->query("SHOW COLUMNS FROM svt_settings LIKE 'social_twitter_id';");
if($result) {
    if ($result->num_rows==0) {
        $mysqli->query("ALTER TABLE svt_settings ADD `social_twitter_id` varchar(200) DEFAULT NULL;");
    }
}
$result = $mysqli->query("SHOW COLUMNS FROM svt_settings LIKE 'social_twitter_secret';");
if($result) {
    if ($result->num_rows==0) {
        $mysqli->query("ALTER TABLE svt_settings ADD `social_twitter_secret` varchar(200) DEFAULT NULL;");
    }
}

//UPDATE 5.0
$result = $mysqli->query("SHOW COLUMNS FROM svt_virtualtours LIKE 'language';");
if($result) {
    if ($result->num_rows==0) {
        $mysqli->query("ALTER TABLE svt_virtualtours ADD `language` varchar(10) DEFAULT NULL;");
    }
}
$result = $mysqli->query("SHOW COLUMNS FROM svt_virtualtours LIKE 'external';");
if($result) {
    if ($result->num_rows==0) {
        $mysqli->query("ALTER TABLE svt_virtualtours ADD `external` tinyint(1) NOT NULL DEFAULT '0';");
    }
}
$result = $mysqli->query("SHOW COLUMNS FROM svt_virtualtours LIKE 'external_url';");
if($result) {
    if ($result->num_rows==0) {
        $mysqli->query("ALTER TABLE svt_virtualtours ADD `external_url` varchar(250) DEFAULT NULL;");
    }
}
$result = $mysqli->query("SHOW COLUMNS FROM svt_settings LIKE 'help_url';");
if($result) {
    if ($result->num_rows==0) {
        $mysqli->query("ALTER TABLE svt_settings ADD `help_url` varchar(250) DEFAULT NULL;");
    }
}
$result = $mysqli->query("SHOW COLUMNS FROM svt_settings LIKE 'enable_external_vt';");
if($result) {
    if ($result->num_rows==0) {
        $mysqli->query("ALTER TABLE svt_settings ADD `enable_external_vt` tinyint(1) NOT NULL DEFAULT '0';");
    }
}
$result = $mysqli->query("SHOW COLUMNS FROM svt_pois LIKE 'yaw';");
if($result) {
    if ($result->num_rows==1) {
        $row = $result->fetch_array(MYSQLI_ASSOC);
        $type = strtolower($row['Type']);
        if (strpos($type, 'float') === false) {
            $mysqli->query("ALTER TABLE `svt_pois` MODIFY COLUMN `yaw` float DEFAULT NULL;");
        }
    }
}
$result = $mysqli->query("SHOW COLUMNS FROM svt_pois LIKE 'pitch';");
if($result) {
    if ($result->num_rows==1) {
        $row = $result->fetch_array(MYSQLI_ASSOC);
        $type = strtolower($row['Type']);
        if (strpos($type, 'float') === false) {
            $mysqli->query("ALTER TABLE `svt_pois` MODIFY COLUMN `pitch` float DEFAULT NULL;");
        }
    }
}
$result = $mysqli->query("SHOW COLUMNS FROM svt_markers LIKE 'yaw';");
if($result) {
    if ($result->num_rows==1) {
        $row = $result->fetch_array(MYSQLI_ASSOC);
        $type = strtolower($row['Type']);
        if (strpos($type, 'float') === false) {
            $mysqli->query("ALTER TABLE `svt_markers` MODIFY COLUMN `yaw` float DEFAULT NULL;");
        }
    }
}
$result = $mysqli->query("SHOW COLUMNS FROM svt_markers LIKE 'pitch';");
if($result) {
    if ($result->num_rows==1) {
        $row = $result->fetch_array(MYSQLI_ASSOC);
        $type = strtolower($row['Type']);
        if (strpos($type, 'float') === false) {
            $mysqli->query("ALTER TABLE `svt_markers` MODIFY COLUMN `pitch` float DEFAULT NULL;");
        }
    }
}
$result = $mysqli->query("SHOW COLUMNS FROM svt_rooms LIKE 'virtual_staging';");
if($result) {
    if ($result->num_rows==0) {
        $mysqli->query("ALTER TABLE svt_rooms ADD `virtual_staging` tinyint(1) NOT NULL DEFAULT '0';");
    }
}
$result = $mysqli->query("SHOW COLUMNS FROM svt_rooms LIKE 'main_view_tooltip';");
if($result) {
    if ($result->num_rows==0) {
        $mysqli->query("ALTER TABLE svt_rooms ADD `main_view_tooltip` varchar(100) NOT NULL DEFAULT '';");
    }
}
$result = $mysqli->query("SHOW COLUMNS FROM svt_rooms_alt LIKE 'view_tooltip';");
if($result) {
    if ($result->num_rows==0) {
        $mysqli->query("ALTER TABLE svt_rooms_alt ADD `view_tooltip` varchar(100) NOT NULL DEFAULT '';");
    }
}
$result = $mysqli->query("SHOW COLUMNS FROM svt_settings LIKE 'mail_activate_subject';");
if($result) {
    if ($result->num_rows==0) {
        $mysqli->query("ALTER TABLE svt_settings ADD `mail_activate_subject` varchar(250) NOT NULL DEFAULT 'Activation Account';");
    }
}
$result = $mysqli->query("SHOW COLUMNS FROM svt_settings LIKE 'mail_activate_body';");
if($result) {
    if ($result->num_rows==0) {
        $mysqli->query("ALTER TABLE svt_settings ADD `mail_activate_body` text;");
        $mysqli->query("UPDATE svt_settings SET `mail_activate_body`='<p>Thanks for signing up!</p><p><br></p><p>Please click on this link to activate your account:</p><p>%LINK%</p>';");
    }
}
$result = $mysqli->query("SHOW COLUMNS FROM svt_settings LIKE 'mail_forgot_subject';");
if($result) {
    if ($result->num_rows==0) {
        $mysqli->query("ALTER TABLE svt_settings ADD `mail_forgot_subject` varchar(250) NOT NULL DEFAULT 'Forgot Password';");
    }
}
$result = $mysqli->query("SHOW COLUMNS FROM svt_settings LIKE 'mail_forgot_body';");
if($result) {
    if ($result->num_rows==0) {
        $mysqli->query("ALTER TABLE svt_settings ADD `mail_forgot_body` text;");
        $mysqli->query("UPDATE svt_settings SET `mail_forgot_body`='<p>This is your verification code: %VERIFICATION_CODE%</p><p><br></p><p>Please click on this link to change your password:</p><p>%LINK%</p>';");
    }
}

//UPDATE 5.1
$result = $mysqli->query("SHOW COLUMNS FROM svt_users LIKE 'avatar';");
if($result) {
    if ($result->num_rows==0) {
        $mysqli->query("ALTER TABLE svt_users ADD `avatar` varchar(50) DEFAULT NULL;");
    }
}
$result = $mysqli->query("SHOW COLUMNS FROM svt_plans LIKE 'visible';");
if($result) {
    if ($result->num_rows==0) {
        $mysqli->query("ALTER TABLE svt_plans ADD `visible` tinyint(1) NOT NULL DEFAULT '1';");
    }
}
$result = $mysqli->query("SHOW COLUMNS FROM svt_plans LIKE 'external_url';");
if($result) {
    if ($result->num_rows==0) {
        $mysqli->query("ALTER TABLE svt_plans ADD `external_url` varchar(250) DEFAULT NULL;");
    }
}
$result = $mysqli->query("SHOW TABLES LIKE 'svt_categories';");
if($result) {
    if ($result->num_rows==0) {
        $mysqli->query("CREATE TABLE IF NOT EXISTS `svt_categories` (
                                  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
                                  `name` varchar(100) DEFAULT NULL,
                                  PRIMARY KEY (`id`)
                                ) ENGINE=InnoDB DEFAULT CHARSET=utf8;");
    }
}
$result = $mysqli->query("SHOW COLUMNS FROM svt_virtualtours LIKE 'id_category';");
if($result) {
    if ($result->num_rows==0) {
        $mysqli->query("ALTER TABLE svt_virtualtours ADD `id_category` int(11) unsigned DEFAULT NULL;");
        $mysqli->query("ALTER TABLE svt_virtualtours ADD FOREIGN KEY (`id_category`) REFERENCES `svt_categories` (`id`) ON DELETE SET NULL ON UPDATE NO ACTION;");
    }
}
generate_favicons();
$result = $mysqli->query("SHOW COLUMNS FROM svt_virtualtours LIKE 'keyboard_mode';");
if($result) {
    if ($result->num_rows==0) {
        $mysqli->query("ALTER TABLE svt_virtualtours ADD `keyboard_mode` tinyint(1) NOT NULL DEFAULT '1';");
    }
}
$result = $mysqli->query("SHOW COLUMNS FROM svt_users LIKE 'first_name';");
if($result) {
    if ($result->num_rows==0) {
        $mysqli->query("ALTER TABLE svt_users ADD `first_name` varchar(100) DEFAULT NULL;");
    }
}
$result = $mysqli->query("SHOW COLUMNS FROM svt_users LIKE 'last_name';");
if($result) {
    if ($result->num_rows==0) {
        $mysqli->query("ALTER TABLE svt_users ADD `last_name` varchar(100) DEFAULT NULL;");
    }
}
$result = $mysqli->query("SHOW COLUMNS FROM svt_users LIKE 'company';");
if($result) {
    if ($result->num_rows==0) {
        $mysqli->query("ALTER TABLE svt_users ADD `company` varchar(100) DEFAULT NULL;");
    }
}
$result = $mysqli->query("SHOW COLUMNS FROM svt_users LIKE 'tax_id';");
if($result) {
    if ($result->num_rows==0) {
        $mysqli->query("ALTER TABLE svt_users ADD `tax_id` varchar(100) DEFAULT NULL;");
    }
}
$result = $mysqli->query("SHOW COLUMNS FROM svt_users LIKE 'street';");
if($result) {
    if ($result->num_rows==0) {
        $mysqli->query("ALTER TABLE svt_users ADD `street` varchar(100) DEFAULT NULL;");
    }
}
$result = $mysqli->query("SHOW COLUMNS FROM svt_users LIKE 'city';");
if($result) {
    if ($result->num_rows==0) {
        $mysqli->query("ALTER TABLE svt_users ADD `city` varchar(100) DEFAULT NULL;");
    }
}
$result = $mysqli->query("SHOW COLUMNS FROM svt_users LIKE 'postal_code';");
if($result) {
    if ($result->num_rows==0) {
        $mysqli->query("ALTER TABLE svt_users ADD `postal_code` varchar(100) DEFAULT NULL;");
    }
}
$result = $mysqli->query("SHOW COLUMNS FROM svt_users LIKE 'province';");
if($result) {
    if ($result->num_rows==0) {
        $mysqli->query("ALTER TABLE svt_users ADD `province` varchar(100) DEFAULT NULL;");
    }
}
$result = $mysqli->query("SHOW COLUMNS FROM svt_users LIKE 'country';");
if($result) {
    if ($result->num_rows==0) {
        $mysqli->query("ALTER TABLE svt_users ADD `country` varchar(100) DEFAULT NULL;");
    }
}
$result = $mysqli->query("SHOW COLUMNS FROM svt_users LIKE 'tel';");
if($result) {
    if ($result->num_rows==0) {
        $mysqli->query("ALTER TABLE svt_users ADD `tel` varchar(100) DEFAULT NULL;");
    }
}
$result = $mysqli->query("SHOW COLUMNS FROM svt_settings LIKE 'first_name_enable';");
if($result) {
    if ($result->num_rows==0) {
        $mysqli->query("ALTER TABLE svt_settings ADD `first_name_enable` tinyint(1) NOT NULL DEFAULT '1';");
    }
}
$result = $mysqli->query("SHOW COLUMNS FROM svt_settings LIKE 'last_name_enable';");
if($result) {
    if ($result->num_rows==0) {
        $mysqli->query("ALTER TABLE svt_settings ADD `last_name_enable` tinyint(1) NOT NULL DEFAULT '1';");
    }
}
$result = $mysqli->query("SHOW COLUMNS FROM svt_settings LIKE 'company_enable';");
if($result) {
    if ($result->num_rows==0) {
        $mysqli->query("ALTER TABLE svt_settings ADD `company_enable` tinyint(1) NOT NULL DEFAULT '1';");
    }
}
$result = $mysqli->query("SHOW COLUMNS FROM svt_settings LIKE 'tax_id_enable';");
if($result) {
    if ($result->num_rows==0) {
        $mysqli->query("ALTER TABLE svt_settings ADD `tax_id_enable` tinyint(1) NOT NULL DEFAULT '1';");
    }
}
$result = $mysqli->query("SHOW COLUMNS FROM svt_settings LIKE 'street_enable';");
if($result) {
    if ($result->num_rows==0) {
        $mysqli->query("ALTER TABLE svt_settings ADD `street_enable` tinyint(1) NOT NULL DEFAULT '1';");
    }
}
$result = $mysqli->query("SHOW COLUMNS FROM svt_settings LIKE 'city_enable';");
if($result) {
    if ($result->num_rows==0) {
        $mysqli->query("ALTER TABLE svt_settings ADD `city_enable` tinyint(1) NOT NULL DEFAULT '1';");
    }
}
$result = $mysqli->query("SHOW COLUMNS FROM svt_settings LIKE 'postal_code_enable';");
if($result) {
    if ($result->num_rows==0) {
        $mysqli->query("ALTER TABLE svt_settings ADD `postal_code_enable` tinyint(1) NOT NULL DEFAULT '1';");
    }
}
$result = $mysqli->query("SHOW COLUMNS FROM svt_settings LIKE 'province_enable';");
if($result) {
    if ($result->num_rows==0) {
        $mysqli->query("ALTER TABLE svt_settings ADD `province_enable` tinyint(1) NOT NULL DEFAULT '1';");
    }
}
$result = $mysqli->query("SHOW COLUMNS FROM svt_settings LIKE 'country_enable';");
if($result) {
    if ($result->num_rows==0) {
        $mysqli->query("ALTER TABLE svt_settings ADD `country_enable` tinyint(1) NOT NULL DEFAULT '1';");
    }
}
$result = $mysqli->query("SHOW COLUMNS FROM svt_settings LIKE 'tel_enable';");
if($result) {
    if ($result->num_rows==0) {
        $mysqli->query("ALTER TABLE svt_settings ADD `tel_enable` tinyint(1) NOT NULL DEFAULT '1';");
    }
}
$result = $mysqli->query("SHOW COLUMNS FROM svt_settings LIKE 'first_name_mandatory';");
if($result) {
    if ($result->num_rows==0) {
        $mysqli->query("ALTER TABLE svt_settings ADD `first_name_mandatory` tinyint(1) NOT NULL DEFAULT '0';");
    }
}
$result = $mysqli->query("SHOW COLUMNS FROM svt_settings LIKE 'last_name_mandatory';");
if($result) {
    if ($result->num_rows==0) {
        $mysqli->query("ALTER TABLE svt_settings ADD `last_name_mandatory` tinyint(1) NOT NULL DEFAULT '0';");
    }
}
$result = $mysqli->query("SHOW COLUMNS FROM svt_settings LIKE 'company_mandatory';");
if($result) {
    if ($result->num_rows==0) {
        $mysqli->query("ALTER TABLE svt_settings ADD `company_mandatory` tinyint(1) NOT NULL DEFAULT '0';");
    }
}
$result = $mysqli->query("SHOW COLUMNS FROM svt_settings LIKE 'tax_id_mandatory';");
if($result) {
    if ($result->num_rows==0) {
        $mysqli->query("ALTER TABLE svt_settings ADD `tax_id_mandatory` tinyint(1) NOT NULL DEFAULT '0';");
    }
}
$result = $mysqli->query("SHOW COLUMNS FROM svt_settings LIKE 'street_mandatory';");
if($result) {
    if ($result->num_rows==0) {
        $mysqli->query("ALTER TABLE svt_settings ADD `street_mandatory` tinyint(1) NOT NULL DEFAULT '0';");
    }
}
$result = $mysqli->query("SHOW COLUMNS FROM svt_settings LIKE 'city_mandatory';");
if($result) {
    if ($result->num_rows==0) {
        $mysqli->query("ALTER TABLE svt_settings ADD `city_mandatory` tinyint(1) NOT NULL DEFAULT '0';");
    }
}
$result = $mysqli->query("SHOW COLUMNS FROM svt_settings LIKE 'postal_code_mandatory';");
if($result) {
    if ($result->num_rows==0) {
        $mysqli->query("ALTER TABLE svt_settings ADD `postal_code_mandatory` tinyint(1) NOT NULL DEFAULT '0';");
    }
}
$result = $mysqli->query("SHOW COLUMNS FROM svt_settings LIKE 'province_mandatory';");
if($result) {
    if ($result->num_rows==0) {
        $mysqli->query("ALTER TABLE svt_settings ADD `province_mandatory` tinyint(1) NOT NULL DEFAULT '0';");
    }
}
$result = $mysqli->query("SHOW COLUMNS FROM svt_settings LIKE 'country_mandatory';");
if($result) {
    if ($result->num_rows==0) {
        $mysqli->query("ALTER TABLE svt_settings ADD `country_mandatory` tinyint(1) NOT NULL DEFAULT '0';");
    }
}
$result = $mysqli->query("SHOW COLUMNS FROM svt_settings LIKE 'tel_mandatory';");
if($result) {
    if ($result->num_rows==0) {
        $mysqli->query("ALTER TABLE svt_settings ADD `tel_mandatory` tinyint(1) NOT NULL DEFAULT '0';");
    }
}
$result = $mysqli->query("SHOW COLUMNS FROM svt_plans LIKE 'frequency';");
if($result) {
    if ($result->num_rows==0) {
        $mysqli->query("ALTER TABLE svt_plans ADD `frequency` enum('one_time','recurring') DEFAULT 'recurring';");
    }
}
$result = $mysqli->query("SHOW COLUMNS FROM svt_plans LIKE 'interval_count';");
if($result) {
    if ($result->num_rows==0) {
        $mysqli->query("ALTER TABLE svt_plans ADD `interval_count` int(11) NOT NULL DEFAULT '1';");
    }
}

//UPDATE 5.2
$result = $mysqli->query("SHOW COLUMNS FROM svt_rooms LIKE 'hfov';");
if($result) {
    if ($result->num_rows == 0) {
        $mysqli->query("ALTER TABLE `svt_rooms` ADD `hfov` int(11) NOT NULL DEFAULT '0' AFTER `pitch`;");
    }
}
$result = $mysqli->query("SHOW COLUMNS FROM svt_virtualtours LIKE 'show_map_tour';");
if($result) {
    if ($result->num_rows==0) {
        $mysqli->query("ALTER TABLE svt_virtualtours ADD `show_map_tour` tinyint(1) NOT NULL DEFAULT '1' AFTER `show_map`;");
    }
}
$result = $mysqli->query("SHOW COLUMNS FROM svt_maps LIKE 'map_type';");
if($result) {
    if ($result->num_rows==0) {
        $mysqli->query("ALTER TABLE svt_maps ADD `map_type` enum('floorplan','map') DEFAULT 'floorplan' AFTER `id_virtualtour`;");
    }
}
$result = $mysqli->query("SHOW COLUMNS FROM svt_rooms LIKE 'lon';");
if($result) {
    if ($result->num_rows == 0) {
        $mysqli->query("ALTER TABLE `svt_rooms` ADD `lon` varchar(50) DEFAULT NULL AFTER `map_left`;");
    }
}
$result = $mysqli->query("SHOW COLUMNS FROM svt_rooms LIKE 'lat';");
if($result) {
    if ($result->num_rows == 0) {
        $mysqli->query("ALTER TABLE `svt_rooms` ADD `lat` varchar(50) DEFAULT NULL AFTER `map_left`;");
    }
}
$result = $mysqli->query("SHOW COLUMNS FROM svt_maps LIKE 'zoom_to_point';");
if($result) {
    if ($result->num_rows==0) {
        $mysqli->query("ALTER TABLE svt_maps ADD `zoom_to_point` tinyint(1) NOT NULL DEFAULT '0' AFTER `north_degree`;");
    }
}
$result = $mysqli->query("SHOW COLUMNS FROM svt_maps LIKE 'zoom_level';");
if($result) {
    if ($result->num_rows==0) {
        $mysqli->query("ALTER TABLE svt_maps ADD `zoom_level` int(11) NOT NULL DEFAULT '16' AFTER `north_degree`;");
    }
}

//UPDATE 5.3
$result = $mysqli->query("SHOW COLUMNS FROM svt_settings LIKE 'peerjs_host';");
if($result) {
    if ($result->num_rows==0) {
        $mysqli->query("ALTER TABLE svt_settings ADD `peerjs_host` varchar(250) NOT NULL DEFAULT 'svtpeerjs.simpledemo.it';");
    }
}
$result = $mysqli->query("SHOW COLUMNS FROM svt_settings LIKE 'peerjs_port';");
if($result) {
    if ($result->num_rows==0) {
        $mysqli->query("ALTER TABLE svt_settings ADD `peerjs_port` int(5) NOT NULL DEFAULT '9000';");
    }
}
$result = $mysqli->query("SHOW COLUMNS FROM svt_settings LIKE 'peerjs_path';");
if($result) {
    if ($result->num_rows==0) {
        $mysqli->query("ALTER TABLE svt_settings ADD `peerjs_path` varchar(250) NOT NULL DEFAULT '/svt';");
    }
}
$result = $mysqli->query("SHOW COLUMNS FROM svt_settings LIKE 'jitsi_domain';");
if($result) {
    if ($result->num_rows==0) {
        $mysqli->query("ALTER TABLE svt_settings ADD `jitsi_domain` varchar(250) NOT NULL DEFAULT 'meet.jit.si';");
    }
}
$result = $mysqli->query("SHOW COLUMNS FROM svt_settings LIKE 'leaflet_street_basemap';");
if($result) {
    if ($result->num_rows==0) {
        $mysqli->query("ALTER TABLE svt_settings ADD `leaflet_street_basemap` varchar(250) NOT NULL DEFAULT 'https://{s}.google.com/vt/lyrs=m&x={x}&y={y}&z={z}';");
    }
}
$result = $mysqli->query("SHOW COLUMNS FROM svt_settings LIKE 'leaflet_street_subdomain';");
if($result) {
    if ($result->num_rows==0) {
        $mysqli->query("ALTER TABLE svt_settings ADD `leaflet_street_subdomain` varchar(250) NOT NULL DEFAULT 'mt0,mt1,mt2,mt3';");
    }
}
$result = $mysqli->query("SHOW COLUMNS FROM svt_settings LIKE 'leaflet_street_maxzoom';");
if($result) {
    if ($result->num_rows==0) {
        $mysqli->query("ALTER TABLE svt_settings ADD `leaflet_street_maxzoom` int(2) NOT NULL DEFAULT '20';");
    }
}
$result = $mysqli->query("SHOW COLUMNS FROM svt_settings LIKE 'leaflet_satellite_basemap';");
if($result) {
    if ($result->num_rows==0) {
        $mysqli->query("ALTER TABLE svt_settings ADD `leaflet_satellite_basemap` varchar(250) NOT NULL DEFAULT 'https://{s}.google.com/vt/lyrs=s&x={x}&y={y}&z={z}';");
    }
}
$result = $mysqli->query("SHOW COLUMNS FROM svt_settings LIKE 'leaflet_satellite_subdomain';");
if($result) {
    if ($result->num_rows==0) {
        $mysqli->query("ALTER TABLE svt_settings ADD `leaflet_satellite_subdomain` varchar(250) NOT NULL DEFAULT 'mt0,mt1,mt2,mt3';");
    }
}
$result = $mysqli->query("SHOW COLUMNS FROM svt_settings LIKE 'leaflet_satellite_maxzoom';");
if($result) {
    if ($result->num_rows==0) {
        $mysqli->query("ALTER TABLE svt_settings ADD `leaflet_satellite_maxzoom` int(2) NOT NULL DEFAULT '20';");
    }
}
$result = $mysqli->query("SHOW TABLES LIKE 'svt_advertisements';");
if($result) {
    if ($result->num_rows==0) {
        $mysqli->query("CREATE TABLE IF NOT EXISTS `svt_advertisements` (
                                  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
                                  `name` varchar(100) DEFAULT NULL,
                                  `image` varchar(50) DEFAULT NULL,
                                  `link` varchar(250) DEFAULT NULL,
                                  `countdown` int(11) NOT NULL DEFAULT 0,
                                  `id_plans` varchar(100) DEFAULT NULL,
                                  `auto_assign` tinyint(1) NOT NULL DEFAULT 0,
                                  PRIMARY KEY (`id`)
                                ) ENGINE=InnoDB DEFAULT CHARSET=utf8;");
    }
}
$result = $mysqli->query("SHOW TABLES LIKE 'svt_assign_advertisements';");
if($result) {
    if ($result->num_rows==0) {
        $mysqli->query("CREATE TABLE IF NOT EXISTS `svt_assign_advertisements` (
                                  `id_advertisement` int(11) unsigned NOT NULL,
                                  `id_virtualtour` bigint(20) unsigned NOT NULL,
                                  KEY `id_advertisement` (`id_advertisement`),
                                  KEY `id_virtualtour` (`id_virtualtour`),
                                  CONSTRAINT `svt_assign_advertisements_ibfk_1` FOREIGN KEY (`id_advertisement`) REFERENCES `svt_advertisements` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
                                  CONSTRAINT `svt_assign_advertisements_ibfk_2` FOREIGN KEY (`id_virtualtour`) REFERENCES `svt_virtualtours` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
                                ) ENGINE=InnoDB DEFAULT CHARSET=utf8;");
    }
}
$result = $mysqli->query("SHOW COLUMNS FROM svt_markers LIKE 'css_class';");
if($result) {
    if ($result->num_rows==0) {
        $mysqli->query("ALTER TABLE svt_markers ADD `css_class` varchar(250) NOT NULL DEFAULT '';");
    }
}
$result = $mysqli->query("SHOW COLUMNS FROM svt_pois LIKE 'css_class';");
if($result) {
    if ($result->num_rows==0) {
        $mysqli->query("ALTER TABLE svt_pois ADD `css_class` varchar(250) NOT NULL DEFAULT '';");
    }
}
$result = $mysqli->query("SHOW COLUMNS FROM svt_showcase_list LIKE 'type_viewer';");
if($result) {
    if ($result->num_rows==0) {
        $mysqli->query("ALTER TABLE svt_showcase_list ADD `type_viewer` enum('viewer','landing') NOT NULL DEFAULT 'viewer';");
    }
}

//UPDATE 5.4
$result = $mysqli->query("SHOW COLUMNS FROM svt_showcases LIKE 'header_html';");
if($result) {
    if ($result->num_rows==0) {
        $mysqli->query("ALTER TABLE svt_showcases ADD `header_html` longtext DEFAULT NULL;");
    }
}
$result = $mysqli->query("SHOW COLUMNS FROM svt_showcases LIKE 'footer_html';");
if($result) {
    if ($result->num_rows==0) {
        $mysqli->query("ALTER TABLE svt_showcases ADD `footer_html` longtext DEFAULT NULL;");
    }
}
$result = $mysqli->query("SHOW COLUMNS FROM svt_virtualtours LIKE 'markers_id_icon_library';");
if($result) {
    if ($result->num_rows==0) {
        $mysqli->query("ALTER TABLE svt_virtualtours ADD `markers_id_icon_library` bigint(20) unsigned NOT NULL DEFAULT 0 AFTER `markers_icon`;");
    }
}
$result = $mysqli->query("SHOW COLUMNS FROM svt_virtualtours LIKE 'pois_id_icon_library';");
if($result) {
    if ($result->num_rows==0) {
        $mysqli->query("ALTER TABLE svt_virtualtours ADD `pois_id_icon_library` bigint(20) unsigned NOT NULL DEFAULT 0 AFTER `pois_icon`;");
    }
}
$result = $mysqli->query("SHOW COLUMNS FROM svt_virtualtours LIKE 'transition_effect';");
if($result) {
    if ($result->num_rows==0) {
        $mysqli->query("ALTER TABLE svt_virtualtours ADD `transition_effect` varchar(25) NOT NULL DEFAULT 'fade' AFTER `transition_fadeout`;");
    }
}
$result = $mysqli->query("SHOW COLUMNS FROM svt_rooms LIKE 'transition_effect';");
if($result) {
    if ($result->num_rows==0) {
        $mysqli->query("ALTER TABLE svt_rooms ADD `transition_effect` varchar(25) NOT NULL DEFAULT 'fade' AFTER `transition_fadeout`;");
    }
}
$result = $mysqli->query("SHOW COLUMNS FROM svt_settings LIKE 'languages_enabled';");
if($result) {
    if ($result->num_rows==0) {
        $mysqli->query("ALTER TABLE svt_settings ADD `languages_enabled` text;");
    }
}

//UPDATE 5.5
$result = $mysqli->query("SHOW COLUMNS FROM svt_settings LIKE 'background_reg';");
if($result) {
    if ($result->num_rows==0) {
        $mysqli->query("ALTER TABLE svt_settings ADD `background_reg` varchar(50) DEFAULT NULL AFTER `background`;");
    }
}
$result = $mysqli->query("SHOW COLUMNS FROM svt_settings LIKE 'welcome_msg';");
if($result) {
    if ($result->num_rows==0) {
        $mysqli->query("ALTER TABLE svt_settings ADD `welcome_msg` text;");
    }
}
$result = $mysqli->query("SHOW COLUMNS FROM svt_virtualtours LIKE 'password_meeting';");
if($result) {
    if ($result->num_rows==0) {
        $mysqli->query("ALTER TABLE svt_virtualtours ADD `password_meeting` varchar(200) DEFAULT NULL;");
    }
}
$result = $mysqli->query("SHOW COLUMNS FROM svt_virtualtours LIKE 'password_livesession';");
if($result) {
    if ($result->num_rows==0) {
        $mysqli->query("ALTER TABLE svt_virtualtours ADD `password_livesession` varchar(200) DEFAULT NULL;");
    }
}
$result = $mysqli->query("SHOW COLUMNS FROM svt_settings LIKE 'enable_sample';");
if($result) {
    if ($result->num_rows==0) {
        $mysqli->query("ALTER TABLE svt_settings ADD `enable_sample` tinyint(1) NOT NULL DEFAULT '1';");
    }
}
$result = $mysqli->query("SHOW COLUMNS FROM svt_settings LIKE 'id_vt_sample';");
if($result) {
    if ($result->num_rows==0) {
        $mysqli->query("ALTER TABLE svt_settings ADD `id_vt_sample` bigint(20) unsigned DEFAULT NULL;");
        $mysqli->query("ALTER TABLE `svt_settings` ADD FOREIGN KEY (`id_vt_sample`) REFERENCES `svt_virtualtours` (`id`) ON DELETE SET NULL ON UPDATE NO ACTION;");
    }
}
$result = $mysqli->query("SHOW COLUMNS FROM svt_rooms LIKE 'allow_hfov';");
if($result) {
    if ($result->num_rows==0) {
        $mysqli->query("ALTER TABLE svt_rooms ADD `allow_hfov` tinyint(1) NOT NULL DEFAULT '1' AFTER `allow_pitch`;");
    }
}
$result = $mysqli->query("SHOW COLUMNS FROM svt_pois LIKE 'type';");
if($result) {
    if ($result->num_rows==1) {
        $row = $result->fetch_array(MYSQLI_ASSOC);
        $type = $row['Type'];
        if (strpos($type, 'google_maps') === false) {
            $mysqli->query("ALTER TABLE svt_pois MODIFY COLUMN `type` enum('image','video','link','link_ext','html','html_sc','download','form','video360','audio','gallery','google_maps') DEFAULT NULL;");
        }
    }
}
$result = $mysqli->query("SHOW TABLES LIKE 'svt_visitors';");
if($result) {
    if ($result->num_rows==0) {
        $mysqli->query("CREATE TABLE IF NOT EXISTS `svt_visitors` (
                              `id_virtualtour` bigint(20) unsigned DEFAULT NULL,
                              `datetime` datetime DEFAULT NULL,
                              `ip` varchar(50) DEFAULT NULL,
                              UNIQUE KEY `id_virtualtour` (`id_virtualtour`,`ip`),
                              CONSTRAINT `svt_visitors_ibfk_1` FOREIGN KEY (`id_virtualtour`) REFERENCES `svt_virtualtours` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
                            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;");
    }
}
$result = $mysqli->query("SHOW COLUMNS FROM svt_settings LIKE 'theme_color';");
if($result) {
    if ($result->num_rows==0) {
        $mysqli->query("ALTER TABLE svt_settings ADD `theme_color` varchar(25) NOT NULL DEFAULT '#0b5394' AFTER `name`;");
    }
}
$result = $mysqli->query("SHOW COLUMNS FROM svt_assign_virtualtours LIKE 'edit_virtualtour';");
if($result) {
    if ($result->num_rows==0) {
        $mysqli->query("ALTER TABLE svt_assign_virtualtours ADD `edit_virtualtour` tinyint(1) NOT NULL DEFAULT 1;");
        $mysqli->query("ALTER TABLE svt_assign_virtualtours ADD `create_rooms` tinyint(1) NOT NULL DEFAULT 0;");
        $mysqli->query("ALTER TABLE svt_assign_virtualtours ADD `edit_rooms` tinyint(1) NOT NULL DEFAULT 1;");
        $mysqli->query("ALTER TABLE svt_assign_virtualtours ADD `delete_rooms` tinyint(1) NOT NULL DEFAULT 0;");
        $mysqli->query("ALTER TABLE svt_assign_virtualtours ADD `create_markers` tinyint(1) NOT NULL DEFAULT 1;");
        $mysqli->query("ALTER TABLE svt_assign_virtualtours ADD `edit_markers` tinyint(1) NOT NULL DEFAULT 1;");
        $mysqli->query("ALTER TABLE svt_assign_virtualtours ADD `delete_markers` tinyint(1) NOT NULL DEFAULT 1;");
        $mysqli->query("ALTER TABLE svt_assign_virtualtours ADD `create_pois` tinyint(1) NOT NULL DEFAULT 1;");
        $mysqli->query("ALTER TABLE svt_assign_virtualtours ADD `edit_pois` tinyint(1) NOT NULL DEFAULT 1;");
        $mysqli->query("ALTER TABLE svt_assign_virtualtours ADD `delete_pois` tinyint(1) NOT NULL DEFAULT 1;");
        $mysqli->query("ALTER TABLE svt_assign_virtualtours ADD `create_maps` tinyint(1) NOT NULL DEFAULT 0;");
        $mysqli->query("ALTER TABLE svt_assign_virtualtours ADD `edit_maps` tinyint(1) NOT NULL DEFAULT 1;");
        $mysqli->query("ALTER TABLE svt_assign_virtualtours ADD `delete_maps` tinyint(1) NOT NULL DEFAULT 0;");
        $mysqli->query("ALTER TABLE svt_assign_virtualtours ADD `info_box` tinyint(1) NOT NULL DEFAULT 1;");
        $mysqli->query("ALTER TABLE svt_assign_virtualtours ADD `presentation` tinyint(1) NOT NULL DEFAULT 1;");
        $mysqli->query("ALTER TABLE svt_assign_virtualtours ADD `gallery` tinyint(1) NOT NULL DEFAULT 1;");
        $mysqli->query("ALTER TABLE svt_assign_virtualtours ADD `icons_library` tinyint(1) NOT NULL DEFAULT 1;");
        $mysqli->query("ALTER TABLE svt_assign_virtualtours ADD `publish` tinyint(1) NOT NULL DEFAULT 0;");
        $mysqli->query("ALTER TABLE svt_assign_virtualtours ADD `landing` tinyint(1) NOT NULL DEFAULT 1;");
        $mysqli->query("ALTER TABLE svt_assign_virtualtours ADD `forms` tinyint(1) NOT NULL DEFAULT 1;");
        $mysqli->query("ALTER TABLE svt_assign_virtualtours ADD `leads` tinyint(1) NOT NULL DEFAULT 1;");
    }
}
$result = $mysqli->query("SHOW COLUMNS FROM svt_virtualtours LIKE 'nav_slider';");
if($result) {
    if ($result->num_rows==0) {
        $mysqli->query("ALTER TABLE svt_virtualtours ADD `nav_slider` tinyint(1) NOT NULL DEFAULT '0' AFTER `auto_show_slider`;");
    }
}
$result = $mysqli->query("SHOW COLUMNS FROM svt_rooms LIKE 'background_color';");
if($result) {
    if ($result->num_rows==0) {
        $mysqli->query("ALTER TABLE svt_rooms ADD `background_color` varchar(25) NOT NULL DEFAULT '1,1,1';");
    }
}
$result = $mysqli->query("SHOW TABLES LIKE 'svt_poi_objects360';");
if($result) {
    if ($result->num_rows==0) {
        $mysqli->query("CREATE TABLE IF NOT EXISTS `svt_poi_objects360` (
                              `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
                              `id_poi` bigint(20) unsigned DEFAULT NULL,
                              `image` varchar(50) DEFAULT NULL,
                              `priority` int(11) NOT NULL DEFAULT 0,
                              PRIMARY KEY (`id`),
                              KEY `id_poi` (`id_poi`),
                              CONSTRAINT `svt_poi_objects360_ibfk_1` FOREIGN KEY (`id_poi`) REFERENCES `svt_pois` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
                            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;");
    }
}
$result = $mysqli->query("SHOW COLUMNS FROM svt_pois LIKE 'type';");
if($result) {
    if ($result->num_rows==1) {
        $row = $result->fetch_array(MYSQLI_ASSOC);
        $type = $row['Type'];
        if (strpos($type, 'object360') === false) {
            $mysqli->query("ALTER TABLE svt_pois MODIFY COLUMN `type` enum('image','video','link','link_ext','html','html_sc','download','form','video360','audio','gallery','google_maps','object360') DEFAULT NULL;");
        }
    }
}

//UPDATE 5.5.1
$result = $mysqli->query("SHOW COLUMNS FROM svt_rooms LIKE 'id_poi_autoopen';");
if($result) {
    if ($result->num_rows==0) {
        $mysqli->query("ALTER TABLE svt_rooms ADD `id_poi_autoopen` bigint(20) unsigned DEFAULT NULL;");
    }
}

//UPDATE 5.6
$result = $mysqli->query("SHOW COLUMNS FROM svt_virtualtours LIKE 'preload_panoramas';");
if($result) {
    if ($result->num_rows==0) {
        $mysqli->query("ALTER TABLE svt_virtualtours ADD `preload_panoramas` tinyint(1) NOT NULL DEFAULT '1';");
    }
}
$result = $mysqli->query("SHOW COLUMNS FROM svt_forms_data LIKE 'field6';");
if($result) {
    if ($result->num_rows==0) {
        $mysqli->query("ALTER TABLE svt_forms_data ADD `field6` text;");
    }
}
$result = $mysqli->query("SHOW COLUMNS FROM svt_forms_data LIKE 'field7';");
if($result) {
    if ($result->num_rows==0) {
        $mysqli->query("ALTER TABLE svt_forms_data ADD `field7` text;");
    }
}
$result = $mysqli->query("SHOW COLUMNS FROM svt_forms_data LIKE 'field8';");
if($result) {
    if ($result->num_rows==0) {
        $mysqli->query("ALTER TABLE svt_forms_data ADD `field8` text;");
    }
}
$result = $mysqli->query("SHOW COLUMNS FROM svt_forms_data LIKE 'field9';");
if($result) {
    if ($result->num_rows==0) {
        $mysqli->query("ALTER TABLE svt_forms_data ADD `field9` text;");
    }
}
$result = $mysqli->query("SHOW COLUMNS FROM svt_forms_data LIKE 'field10';");
if($result) {
    if ($result->num_rows==0) {
        $mysqli->query("ALTER TABLE svt_forms_data ADD `field10` text;");
    }
}
$result = $mysqli->query("SHOW COLUMNS FROM svt_virtualtours LIKE 'click_anywhere';");
if($result) {
    if ($result->num_rows==0) {
        $mysqli->query("ALTER TABLE svt_virtualtours ADD `click_anywhere` tinyint(1) NOT NULL DEFAULT '0';");
    }
}
$result = $mysqli->query("SHOW COLUMNS FROM svt_virtualtours LIKE 'hide_markers';");
if($result) {
    if ($result->num_rows==0) {
        $mysqli->query("ALTER TABLE svt_virtualtours ADD `hide_markers` tinyint(1) NOT NULL DEFAULT '0';");
    }
}
$result = $mysqli->query("SHOW COLUMNS FROM svt_pois LIKE 'type';");
if($result) {
    if ($result->num_rows==1) {
        $row = $result->fetch_array(MYSQLI_ASSOC);
        $type = $row['Type'];
        if (strpos($type, 'embed') === false) {
            $mysqli->query("ALTER TABLE svt_pois MODIFY COLUMN `type` enum('image','video','link','link_ext','html','html_sc','download','form','video360','audio','gallery','google_maps','object360','embed') DEFAULT NULL;");
        }
    }
}
$result = $mysqli->query("SHOW COLUMNS FROM svt_pois LIKE 'embed_type';");
if($result) {
    if ($result->num_rows==0) {
        $mysqli->query("ALTER TABLE svt_pois ADD `embed_type` enum('image','video') DEFAULT NULL AFTER `type`;");
    }
}
$result = $mysqli->query("SHOW COLUMNS FROM svt_pois LIKE 'embed_coords';");
if($result) {
    if ($result->num_rows==0) {
        $mysqli->query("ALTER TABLE svt_pois ADD `embed_coords` varchar(200) DEFAULT NULL AFTER `embed_type`;");
    }
}
$result = $mysqli->query("SHOW COLUMNS FROM svt_pois LIKE 'embed_size';");
if($result) {
    if ($result->num_rows==0) {
        $mysqli->query("ALTER TABLE svt_pois ADD `embed_size` varchar(200) DEFAULT NULL AFTER `embed_coords`;");
    }
}
$result = $mysqli->query("SHOW COLUMNS FROM svt_pois LIKE 'embed_content';");
if($result) {
    if ($result->num_rows==0) {
        $mysqli->query("ALTER TABLE svt_pois ADD `embed_content` longtext AFTER `embed_size`;");
    }
}
$result = $mysqli->query("SHOW COLUMNS FROM svt_pois LIKE 'embed_video_muted';");
if($result) {
    if ($result->num_rows==0) {
        $mysqli->query("ALTER TABLE svt_pois ADD `embed_video_muted` tinyint(1) NOT NULL DEFAULT '1' AFTER `embed_content`;");
    }
}
$result = $mysqli->query("SHOW COLUMNS FROM svt_pois LIKE 'embed_video_autoplay';");
if($result) {
    if ($result->num_rows==0) {
        $mysqli->query("ALTER TABLE svt_pois ADD `embed_video_autoplay` tinyint(1) NOT NULL DEFAULT '1' AFTER `embed_video_muted`;");
    }
}
$result = $mysqli->query("SHOW COLUMNS FROM svt_pois LIKE 'view_type';");
if($result) {
    if ($result->num_rows==0) {
        $mysqli->query("ALTER TABLE svt_pois ADD `view_type` tinyint(1) NOT NULL DEFAULT '0' AFTER `css_class`;");
    }
}
$result = $mysqli->query("SHOW COLUMNS FROM svt_pois LIKE 'box_pos';");
if($result) {
    if ($result->num_rows==0) {
        $mysqli->query("ALTER TABLE svt_pois ADD `box_pos` varchar(10) DEFAULT 'right' AFTER `view_type`;");
    }
}
$result = $mysqli->query("SHOW COLUMNS FROM svt_users LIKE 'expire_plan_date_manual';");
if($result) {
    if ($result->num_rows==0) {
        $mysqli->query("ALTER TABLE svt_users ADD `expire_plan_date_manual` DATETIME DEFAULT NULL AFTER `expire_plan_date`;");
    }
}
$result = $mysqli->query("SHOW COLUMNS FROM svt_virtualtours LIKE 'font_viewer';");
if($result) {
    if ($result->num_rows==0) {
        $mysqli->query("ALTER TABLE svt_virtualtours ADD `font_viewer` varchar(50) DEFAULT 'Roboto';");
    }
}
$result = $mysqli->query("SHOW COLUMNS FROM svt_settings LIKE 'font_backend';");
if($result) {
    if ($result->num_rows==0) {
        $mysqli->query("ALTER TABLE svt_settings ADD `font_backend` varchar(50) DEFAULT 'Nunito' AFTER `theme_color`;");
    }
}
$result = $mysqli->query("SHOW COLUMNS FROM svt_virtualtours LIKE 'hfov_mobile_ratio';");
if($result) {
    if ($result->num_rows==0) {
        $mysqli->query("ALTER TABLE svt_virtualtours ADD `hfov_mobile_ratio` float NOT NULL DEFAULT '1' AFTER `max_hfov`;");
    }
}
$result = $mysqli->query("SHOW TABLES LIKE 'svt_media_library';");
if($result) {
    if ($result->num_rows==0) {
        $mysqli->query("CREATE TABLE IF NOT EXISTS `svt_media_library` (
                              `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
                              `id_virtualtour` bigint(20) unsigned DEFAULT NULL,
                              `file` varchar(50) DEFAULT NULL,
                              PRIMARY KEY (`id`),
                              KEY `id_virtualtour` (`id_virtualtour`),
                              CONSTRAINT `svt_media_library_ibfk_1` FOREIGN KEY (`id_virtualtour`) REFERENCES `svt_virtualtours` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
                            ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;");
    }
}
$result = $mysqli->query("SHOW COLUMNS FROM svt_virtualtours LIKE 'background_video';");
if($result) {
    if ($result->num_rows==0) {
        $mysqli->query("ALTER TABLE svt_virtualtours ADD `background_video` varchar(50) DEFAULT NULL AFTER `background_image`;");
    }
}
$result = $mysqli->query("SHOW COLUMNS FROM svt_virtualtours LIKE 'background_video_delay';");
if($result) {
    if ($result->num_rows==0) {
        $mysqli->query("ALTER TABLE svt_virtualtours ADD `background_video_delay` int(11) NOT NULL DEFAULT '0' AFTER `background_video`;");
    }
}
$result = $mysqli->query("SHOW COLUMNS FROM svt_virtualtours LIKE 'hide_loading';");
if($result) {
    if ($result->num_rows==0) {
        $mysqli->query("ALTER TABLE svt_virtualtours ADD `hide_loading` tinyint(1) NOT NULL DEFAULT '0' AFTER `auto_start`;");
    }
}

//UPDATE 5.6.1
$result = $mysqli->query("SHOW TABLES LIKE 'svt_poi_embedded_gallery';");
if($result) {
    if ($result->num_rows == 0) {
        $mysqli->query("CREATE TABLE IF NOT EXISTS `svt_poi_embedded_gallery` (
                              `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
                              `id_poi` bigint(20) unsigned DEFAULT NULL,
                              `image` varchar(50) DEFAULT NULL,
                              `priority` int(11) NOT NULL DEFAULT '0',
                              PRIMARY KEY (`id`),
                              KEY `id_poi` (`id_poi`),
                              CONSTRAINT `svt_poi_embedded_gallery_ibfk_1` FOREIGN KEY (`id_poi`) REFERENCES `svt_pois` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
                            ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;");
    }
}
$result = $mysqli->query("SHOW COLUMNS FROM svt_pois LIKE 'embed_type';");
if($result) {
    if ($result->num_rows==1) {
        $row = $result->fetch_array(MYSQLI_ASSOC);
        $type = $row['Type'];
        if (strpos($type, 'gallery') === false) {
            $mysqli->query("ALTER TABLE svt_pois MODIFY COLUMN `embed_type` enum('image','video','gallery') DEFAULT NULL;");
        }
    }
}
$result = $mysqli->query("SHOW COLUMNS FROM svt_pois LIKE 'embed_gallery_autoplay';");
if($result) {
    if ($result->num_rows==0) {
        $mysqli->query("ALTER TABLE svt_pois ADD `embed_gallery_autoplay` int(11) NOT NULL DEFAULT '0' AFTER `embed_video_autoplay`;");
    }
}

//UPDATE 5.7
$result = $mysqli->query("SHOW COLUMNS FROM svt_maps LIKE 'width_d';");
if($result) {
    if ($result->num_rows==0) {
        $mysqli->query("ALTER TABLE svt_maps ADD `width_d` int(11) NOT NULL DEFAULT '300' AFTER `point_size`;");
    }
}
$result = $mysqli->query("SHOW COLUMNS FROM svt_maps LIKE 'width_m';");
if($result) {
    if ($result->num_rows==0) {
        $mysqli->query("ALTER TABLE svt_maps ADD `width_m` int(11) NOT NULL DEFAULT '225' AFTER `width_d`;");
    }
}
$result = $mysqli->query("SHOW COLUMNS FROM svt_rooms LIKE 'effect';");
if($result) {
    if ($result->num_rows==0) {
        $mysqli->query("ALTER TABLE svt_rooms ADD `effect` enum('none','snow','rain','fog','fireworks','confetti','sparkle') DEFAULT 'none' AFTER `filters`;");
    }
}
$result = $mysqli->query("SHOW COLUMNS FROM svt_forms_data LIKE 'datetime';");
if($result) {
    if ($result->num_rows==0) {
        $mysqli->query("ALTER TABLE svt_forms_data ADD `datetime` datetime DEFAULT NULL AFTER `id`;");
    }
}
$result = $mysqli->query("SHOW COLUMNS FROM svt_leads LIKE 'datetime';");
if($result) {
    if ($result->num_rows==0) {
        $mysqli->query("ALTER TABLE svt_leads ADD `datetime` datetime DEFAULT NULL AFTER `id`;");
    }
}
$result = $mysqli->query("SHOW COLUMNS FROM svt_pois LIKE 'embed_type';");
if($result) {
    if ($result->num_rows==1) {
        $row = $result->fetch_array(MYSQLI_ASSOC);
        $type = $row['Type'];
        if (strpos($type, 'video_transparent') === false) {
            $mysqli->query("ALTER TABLE svt_pois MODIFY COLUMN `embed_type` enum('image','video','gallery','video_transparent') DEFAULT NULL;");
        }
    }
}
$result = $mysqli->query("SHOW COLUMNS FROM svt_virtualtours LIKE 'show_autorotation_toggle';");
if($result) {
    if ($result->num_rows==0) {
        $mysqli->query("ALTER TABLE svt_virtualtours ADD `show_autorotation_toggle` tinyint(1) NOT NULL DEFAULT '1' AFTER `show_icons_toggle`;");
    }
}
$result = $mysqli->query("SHOW COLUMNS FROM svt_virtualtours LIKE 'show_nav_control';");
if($result) {
    if ($result->num_rows==0) {
        $mysqli->query("ALTER TABLE svt_virtualtours ADD `show_nav_control` tinyint(1) NOT NULL DEFAULT '0' AFTER `show_autorotation_toggle`;");
    }
}
$result = $mysqli->query("SHOW COLUMNS FROM svt_plans LIKE 'enable_export_vt';");
if($result) {
    if ($result->num_rows==0) {
        $mysqli->query("ALTER TABLE svt_plans ADD `enable_export_vt` tinyint(1) NOT NULL DEFAULT '1' AFTER `enable_multires`;");
    }
}
$result = $mysqli->query("SHOW COLUMNS FROM svt_assign_virtualtours LIKE 'media_library';");
if($result) {
    if ($result->num_rows==0) {
        $mysqli->query("ALTER TABLE svt_assign_virtualtours ADD `media_library` tinyint(1) NOT NULL DEFAULT '1' AFTER `icons_library`;");
    }
}
$result = $mysqli->query("SHOW TABLES LIKE 'svt_music_library';");
if($result) {
    if ($result->num_rows==0) {
        $mysqli->query("CREATE TABLE IF NOT EXISTS `svt_music_library` (
                                 `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
                                 `id_virtualtour` bigint(20) unsigned DEFAULT NULL,
                                 `file` varchar(50) DEFAULT NULL,
                                 PRIMARY KEY (`id`),
                                 KEY `id_virtualtour` (`id_virtualtour`),
                                 CONSTRAINT `svt_music_library_ibfk_1` FOREIGN KEY (`id_virtualtour`) REFERENCES `svt_virtualtours` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
                                ) ENGINE=InnoDB DEFAULT CHARSET=utf8;");
    }
}
$result = $mysqli->query("SHOW COLUMNS FROM svt_assign_virtualtours LIKE 'music_library';");
if($result) {
    if ($result->num_rows==0) {
        $mysqli->query("ALTER TABLE svt_assign_virtualtours ADD `music_library` tinyint(1) NOT NULL DEFAULT '1' AFTER `media_library`;");
    }
}

//UPDATE 5.8
$result = $mysqli->query("SHOW COLUMNS FROM svt_pois LIKE 'type';");
if($result) {
    if ($result->num_rows==1) {
        $row = $result->fetch_array(MYSQLI_ASSOC);
        $type = $row['Type'];
        if (strpos($type, 'object3d') === false) {
            $mysqli->query("ALTER TABLE svt_pois MODIFY COLUMN `type` enum('image','video','link','link_ext','html','html_sc','download','form','video360','audio','gallery','google_maps','object360','embed','object3d') DEFAULT NULL;");
        }
    }
}
$result = $mysqli->query("SHOW COLUMNS FROM svt_pois LIKE 'embed_type';");
if($result) {
    if ($result->num_rows==1) {
        $row = $result->fetch_array(MYSQLI_ASSOC);
        $type = $row['Type'];
        if (strpos($type, 'link') === false) {
            $mysqli->query("ALTER TABLE svt_pois MODIFY COLUMN `embed_type` enum('image','video','gallery','video_transparent','link') DEFAULT NULL;");
        }
    }
}
$result = $mysqli->query("SHOW COLUMNS FROM svt_pois LIKE 'embed_type';");
if($result) {
    if ($result->num_rows==1) {
        $row = $result->fetch_array(MYSQLI_ASSOC);
        $type = $row['Type'];
        if (strpos($type, 'text') === false) {
            $mysqli->query("ALTER TABLE svt_pois MODIFY COLUMN `embed_type` enum('image','video','gallery','video_transparent','link','text') DEFAULT NULL;");
        }
    }
}
$result = $mysqli->query("SHOW TABLES LIKE 'svt_presets';");
if($result) {
    if ($result->num_rows==0) {
        $mysqli->query("CREATE TABLE IF NOT EXISTS `svt_presets` (
                              `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
                              `id_virtualtour` bigint(20) unsigned DEFAULT NULL,
                              `name` varchar(100) DEFAULT NULL,
                              `type` varchar(50) DEFAULT NULL,
                              `value` text DEFAULT NULL,
                              PRIMARY KEY (`id`)
                            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;");
    }
}
$result = $mysqli->query("SHOW COLUMNS FROM svt_music_library LIKE 'file';");
if($result) {
    if ($result->num_rows==1) {
        $row = $result->fetch_array(MYSQLI_ASSOC);
        $type = $row['Type'];
        if (strpos($type, '200') === false) {
            $mysqli->query("ALTER TABLE `svt_music_library` MODIFY `file` varchar(200);");
        }
    }
}
$result = $mysqli->query("SHOW COLUMNS FROM svt_virtualtours LIKE 'quality_viewer';");
if($result) {
    if ($result->num_rows==0) {
        $mysqli->query("ALTER TABLE svt_virtualtours ADD `quality_viewer` float NOT NULL DEFAULT '1' AFTER `max_width_compress`;");
    }
}
$result = $mysqli->query("SHOW COLUMNS FROM svt_pois LIKE 'song_bg_volume';");
if($result) {
    if ($result->num_rows==0) {
        $mysqli->query("ALTER TABLE svt_pois ADD `song_bg_volume` float NOT NULL DEFAULT '0.3';");
    }
}
$result = $mysqli->query("SHOW COLUMNS FROM svt_rooms LIKE 'song_bg_volume';");
if($result) {
    if ($result->num_rows==0) {
        $mysqli->query("ALTER TABLE svt_rooms ADD `song_bg_volume` float NOT NULL DEFAULT '0.3' AFTER `song`;");
    }
}
$result = $mysqli->query("SHOW COLUMNS FROM svt_pois LIKE 'embed_type';");
if($result) {
    if ($result->num_rows==1) {
        $row = $result->fetch_array(MYSQLI_ASSOC);
        $type = $row['Type'];
        if (strpos($type, 'selection') === false) {
            $mysqli->query("ALTER TABLE svt_pois MODIFY COLUMN `embed_type` enum('image','video','gallery','video_transparent','link','text','selection') DEFAULT NULL;");
        }
    }
}
$result = $mysqli->query("SHOW COLUMNS FROM svt_pois LIKE 'background';");
if($result) {
    if ($result->num_rows==1) {
        $row = $result->fetch_array(MYSQLI_ASSOC);
        $type = $row['Type'];
        if (strpos($type, '50') === false) {
            $mysqli->query("ALTER TABLE `svt_pois` MODIFY `background` varchar(50) NOT NULL DEFAULT 'rgba(255,255,255,0.7)';");
        }
    }
}
$result = $mysqli->query("SHOW COLUMNS FROM svt_pois LIKE 'color';");
if($result) {
    if ($result->num_rows==1) {
        $row = $result->fetch_array(MYSQLI_ASSOC);
        $type = $row['Type'];
        if (strpos($type, '50') === false) {
            $mysqli->query("ALTER TABLE `svt_pois` MODIFY `color` varchar(50) NOT NULL DEFAULT '#000000';");
        }
    }
}
$result = $mysqli->query("SHOW COLUMNS FROM svt_markers LIKE 'background';");
if($result) {
    if ($result->num_rows==1) {
        $row = $result->fetch_array(MYSQLI_ASSOC);
        $type = $row['Type'];
        if (strpos($type, '50') === false) {
            $mysqli->query("ALTER TABLE `svt_markers` MODIFY `background` varchar(50) NOT NULL DEFAULT 'rgba(255,255,255,0.7)';");
        }
    }
}
$result = $mysqli->query("SHOW COLUMNS FROM svt_markers LIKE 'color';");
if($result) {
    if ($result->num_rows==1) {
        $row = $result->fetch_array(MYSQLI_ASSOC);
        $type = $row['Type'];
        if (strpos($type, '50') === false) {
            $mysqli->query("ALTER TABLE `svt_markers` MODIFY `color` varchar(50) NOT NULL DEFAULT '#000000';");
        }
    }
}
$result = $mysqli->query("SHOW COLUMNS FROM svt_virtualtours LIKE 'autoclose_map';");
if($result) {
    if ($result->num_rows==0) {
        $mysqli->query("ALTER TABLE svt_virtualtours ADD `autoclose_map` tinyint(1) NOT NULL DEFAULT '0' AFTER `show_map`;");
    }
}
$result = $mysqli->query("SHOW COLUMNS FROM svt_virtualtours LIKE 'autoclose_list_alt';");
if($result) {
    if ($result->num_rows==0) {
        $mysqli->query("ALTER TABLE svt_virtualtours ADD `autoclose_list_alt` tinyint(1) NOT NULL DEFAULT '0' AFTER `show_list_alt`;");
    }
}
$result = $mysqli->query("SHOW COLUMNS FROM svt_virtualtours LIKE 'autoclose_slider';");
if($result) {
    if ($result->num_rows==0) {
        $mysqli->query("ALTER TABLE svt_virtualtours ADD `autoclose_slider` tinyint(1) NOT NULL DEFAULT '0' AFTER `auto_show_slider`;");
    }
}
$result = $mysqli->query("SHOW COLUMNS FROM svt_virtualtours LIKE 'autoclose_menu';");
if($result) {
    if ($result->num_rows==0) {
        $mysqli->query("ALTER TABLE svt_virtualtours ADD `autoclose_menu` tinyint(1) NOT NULL DEFAULT '0' AFTER `sameAzimuth`;");
    }
}
$result = $mysqli->query("SHOW COLUMNS FROM svt_markers LIKE 'embed_type';");
if($result) {
    if ($result->num_rows==0) {
        $mysqli->query("ALTER TABLE svt_markers ADD `embed_type` enum('selection') DEFAULT NULL;");
    }
}
$result = $mysqli->query("SHOW COLUMNS FROM svt_markers LIKE 'embed_coords';");
if($result) {
    if ($result->num_rows==0) {
        $mysqli->query("ALTER TABLE svt_markers ADD `embed_coords` varchar(200) DEFAULT NULL AFTER `embed_type`;");
    }
}
$result = $mysqli->query("SHOW COLUMNS FROM svt_markers LIKE 'embed_size';");
if($result) {
    if ($result->num_rows==0) {
        $mysqli->query("ALTER TABLE svt_markers ADD `embed_size` varchar(200) DEFAULT NULL AFTER `embed_coords`;");
    }
}
$result = $mysqli->query("SHOW COLUMNS FROM svt_markers LIKE 'embed_content';");
if($result) {
    if ($result->num_rows==0) {
        $mysqli->query("ALTER TABLE svt_markers ADD `embed_content` longtext AFTER `embed_size`;");
    }
}
$result = $mysqli->query("SHOW COLUMNS FROM svt_settings LIKE 'small_logo';");
if($result) {
    if ($result->num_rows==0) {
        $mysqli->query("ALTER TABLE svt_settings ADD `small_logo` varchar(50) DEFAULT NULL AFTER `logo`;");
    }
}
$result = $mysqli->query("SHOW COLUMNS FROM svt_pois LIKE 'type';");
if($result) {
    if ($result->num_rows==1) {
        $row = $result->fetch_array(MYSQLI_ASSOC);
        $type = $row['Type'];
        if (strpos($type, 'lottie') === false) {
            $mysqli->query("ALTER TABLE svt_pois MODIFY COLUMN `type` enum('image','video','link','link_ext','html','html_sc','download','form','video360','audio','gallery','google_maps','object360','embed','object3d','lottie') DEFAULT NULL;");
        }
    }
}

//UPDATE 5.9
$result = $mysqli->query("SHOW COLUMNS FROM svt_settings LIKE 'footer_link_1';");
if($result) {
    if ($result->num_rows==0) {
        $mysqli->query("ALTER TABLE svt_settings ADD `footer_link_1` varchar(200) DEFAULT NULL;");
    }
}
$result = $mysqli->query("SHOW COLUMNS FROM svt_settings LIKE 'footer_value_1';");
if($result) {
    if ($result->num_rows==0) {
        $mysqli->query("ALTER TABLE svt_settings ADD `footer_value_1` text;");
    }
}
$result = $mysqli->query("SHOW COLUMNS FROM svt_settings LIKE 'footer_link_2';");
if($result) {
    if ($result->num_rows==0) {
        $mysqli->query("ALTER TABLE svt_settings ADD `footer_link_2` varchar(200) DEFAULT NULL;");
    }
}
$result = $mysqli->query("SHOW COLUMNS FROM svt_settings LIKE 'footer_value_2';");
if($result) {
    if ($result->num_rows==0) {
        $mysqli->query("ALTER TABLE svt_settings ADD `footer_value_2` text;");
    }
}
$result = $mysqli->query("SHOW COLUMNS FROM svt_settings LIKE 'footer_link_3';");
if($result) {
    if ($result->num_rows==0) {
        $mysqli->query("ALTER TABLE svt_settings ADD `footer_link_3` varchar(200) DEFAULT NULL;");
    }
}
$result = $mysqli->query("SHOW COLUMNS FROM svt_settings LIKE 'footer_value_3';");
if($result) {
    if ($result->num_rows==0) {
        $mysqli->query("ALTER TABLE svt_settings ADD `footer_value_3` text;");
    }
}
$result = $mysqli->query("SHOW COLUMNS FROM svt_virtualtours LIKE 'password_title';");
if($result) {
    if ($result->num_rows==0) {
        $mysqli->query("ALTER TABLE svt_virtualtours ADD `password_title` varchar(500) DEFAULT NULL AFTER `password`;");
    }
}
$result = $mysqli->query("SHOW COLUMNS FROM svt_virtualtours LIKE 'password_description';");
if($result) {
    if ($result->num_rows==0) {
        $mysqli->query("ALTER TABLE svt_virtualtours ADD `password_description` text AFTER `password_title`;");
    }
}
$result = $mysqli->query("SHOW COLUMNS FROM svt_rooms LIKE 'logo';");
if($result) {
    if ($result->num_rows==0) {
        $mysqli->query("ALTER TABLE svt_rooms ADD `logo` varchar(50) DEFAULT NULL AFTER `name`;");
    }
}
$result = $mysqli->query("SHOW COLUMNS FROM svt_pois LIKE 'transform3d';");
if($result) {
    if ($result->num_rows==0) {
        $mysqli->query("ALTER TABLE svt_pois ADD `transform3d` tinyint(1) NOT NULL DEFAULT '1';");
    }
}
$result = $mysqli->query("SHOW COLUMNS FROM svt_markers LIKE 'transform3d';");
if($result) {
    if ($result->num_rows==0) {
        $mysqli->query("ALTER TABLE svt_markers ADD `transform3d` tinyint(1) NOT NULL DEFAULT '1';");
    }
}
$result = $mysqli->query("SHOW COLUMNS FROM svt_settings LIKE 'welcome_msg';");
if($result) {
    if ($result->num_rows==1) {
        $row = $result->fetch_array(MYSQLI_ASSOC);
        $type = strtolower($row['Type']);
        if (strpos($type, 'longtext') === false) {
            $mysqli->query("ALTER TABLE `svt_settings` MODIFY COLUMN `welcome_msg` longtext;");
        }
    }
}
$result = $mysqli->query("SHOW COLUMNS FROM svt_virtualtours LIKE 'pan_speed';");
if($result) {
    if ($result->num_rows==0) {
        $mysqli->query("ALTER TABLE svt_virtualtours ADD `pan_speed` float NOT NULL DEFAULT '1' AFTER `hfov_mobile_ratio`;");
    }
}
$result = $mysqli->query("SHOW COLUMNS FROM svt_virtualtours LIKE 'pan_speed_mobile';");
if($result) {
    if ($result->num_rows==0) {
        $mysqli->query("ALTER TABLE svt_virtualtours ADD `pan_speed_mobile` float NOT NULL DEFAULT '2' AFTER `pan_speed`;");
    }
}
$result = $mysqli->query("SHOW COLUMNS FROM svt_virtualtours LIKE 'friction';");
if($result) {
    if ($result->num_rows==0) {
        $mysqli->query("ALTER TABLE svt_virtualtours ADD `friction` float NOT NULL DEFAULT '0.1' AFTER `pan_speed_mobile`;");
    }
}
$result = $mysqli->query("SHOW COLUMNS FROM svt_virtualtours LIKE 'friction_mobile';");
if($result) {
    if ($result->num_rows==0) {
        $mysqli->query("ALTER TABLE svt_virtualtours ADD `friction_mobile` float NOT NULL DEFAULT '0.4' AFTER `friction`;");
    }
}
$result = $mysqli->query("SHOW COLUMNS FROM svt_markers LIKE 'lookat';");
if($result) {
    if ($result->num_rows==0) {
        $mysqli->query("ALTER TABLE svt_markers ADD `lookat` tinyint(1) NOT NULL DEFAULT '2';");
    }
}

//UPDATE 6.0
$result = $mysqli->query("SHOW TABLES LIKE 'svt_products';");
if($result) {
    if ($result->num_rows==0) {
        $mysqli->query("CREATE TABLE IF NOT EXISTS `svt_products` (
                                 `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
                                 `id_virtualtour` bigint(20) unsigned DEFAULT NULL,
                                 `name` varchar(100) DEFAULT NULL,
                                 `description` text,
                                 `price` float NOT NULL DEFAULT '0',
                                 `purchase_type` enum('none','cart','link') NOT NULL DEFAULT 'none',
                                 `link` varchar(250) DEFAULT NULL,
                                 PRIMARY KEY (`id`),
                                 KEY `id_virtualtour` (`id_virtualtour`),
                                 CONSTRAINT `svt_products_ibfk_1` FOREIGN KEY (`id_virtualtour`) REFERENCES `svt_virtualtours` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
                                ) ENGINE=InnoDB DEFAULT CHARSET=utf8;");
    }
}
$result = $mysqli->query("SHOW TABLES LIKE 'svt_product_images';");
if($result) {
    if ($result->num_rows==0) {
        $mysqli->query("CREATE TABLE IF NOT EXISTS `svt_product_images` (
                                 `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
                                 `id_product` bigint(20) unsigned DEFAULT NULL,
                                 `image` varchar(50) DEFAULT NULL,
                                 `priority` int(11) NOT NULL DEFAULT '0',
                                 PRIMARY KEY (`id`),
                                 KEY `id_product` (`id_product`),
                                 CONSTRAINT `svt_product_images_ibfk_1` FOREIGN KEY (`id_product`) REFERENCES `svt_products` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
                                ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;");
    }
}
$result = $mysqli->query("SHOW COLUMNS FROM svt_markers LIKE 'animation';");
if($result) {
    if ($result->num_rows==0) {
        $mysqli->query("ALTER TABLE svt_markers ADD `animation` varchar(50) NOT NULL DEFAULT 'none' AFTER `css_class`;");
    }
}
$result = $mysqli->query("SHOW COLUMNS FROM svt_pois LIKE 'animation';");
if($result) {
    if ($result->num_rows==0) {
        $mysqli->query("ALTER TABLE svt_pois ADD `animation` varchar(50) NOT NULL DEFAULT 'none' AFTER `css_class`;");
    }
}
$result = $mysqli->query("SHOW COLUMNS FROM svt_pois LIKE 'type';");
if($result) {
    if ($result->num_rows==1) {
        $row = $result->fetch_array(MYSQLI_ASSOC);
        $type = $row['Type'];
        if (strpos($type, 'product') === false) {
            $mysqli->query("ALTER TABLE svt_pois MODIFY COLUMN `type` enum('image','video','link','link_ext','html','html_sc','download','form','video360','audio','gallery','google_maps','object360','embed','object3d','lottie','product') DEFAULT NULL;");
        }
    }
}
$result = $mysqli->query("SHOW COLUMNS FROM svt_virtualtours LIKE 'snipcart_api_key';");
if($result) {
    if ($result->num_rows==0) {
        $mysqli->query("ALTER TABLE svt_virtualtours ADD `snipcart_api_key` varchar(100) DEFAULT NULL;");
    }
}
$result = $mysqli->query("SHOW COLUMNS FROM svt_virtualtours LIKE 'snipcart_currency';");
if($result) {
    if ($result->num_rows==0) {
        $mysqli->query("ALTER TABLE svt_virtualtours ADD `snipcart_currency` varchar(3) NOT NULL DEFAULT 'USD';");
    }
}
$result = $mysqli->query("SHOW COLUMNS FROM svt_plans LIKE 'enable_shop';");
if($result) {
    if ($result->num_rows==0) {
        $mysqli->query("ALTER TABLE svt_plans ADD `enable_shop` tinyint(1) NOT NULL DEFAULT '1' AFTER `enable_export_vt`;");
    }
}
$result = $mysqli->query("SHOW COLUMNS FROM svt_assign_virtualtours LIKE 'shop';");
if($result) {
    if ($result->num_rows==0) {
        $mysqli->query("ALTER TABLE svt_assign_virtualtours ADD `shop` tinyint(1) NOT NULL DEFAULT '1';");
    }
}
$result = $mysqli->query("SHOW COLUMNS FROM svt_settings LIKE 'enable_wizard';");
if($result) {
    if ($result->num_rows==0) {
        $mysqli->query("ALTER TABLE svt_settings ADD `enable_wizard` tinyint(1) NOT NULL DEFAULT '1' AFTER `enable_external_vt`;");
    }
}
$result = $mysqli->query("SELECT * FROM svt_settings WHERE peerjs_host='svtpeerjs.simpledemo.it' AND peerjs_port=443;");
if($result) {
    if ($result->num_rows==1) {
        $mysqli->query("UPDATE svt_settings SET peerjs_port=9000;;");
    }
}
$result = $mysqli->query("SHOW COLUMNS FROM svt_settings LIKE 'turn_host';");
if($result) {
    if ($result->num_rows==0) {
        $mysqli->query("ALTER TABLE svt_settings ADD `turn_host` varchar(250) NOT NULL DEFAULT 'svtpeerjs.simpledemo.it' AFTER `peerjs_path`;");
    }
}
$result = $mysqli->query("SHOW COLUMNS FROM svt_settings LIKE 'turn_port';");
if($result) {
    if ($result->num_rows==0) {
        $mysqli->query("ALTER TABLE svt_settings ADD `turn_port` int(5) NOT NULL DEFAULT 5349 AFTER `turn_host`;");
    }
}
$result = $mysqli->query("SHOW COLUMNS FROM svt_settings LIKE 'turn_username';");
if($result) {
    if ($result->num_rows==0) {
        $mysqli->query("ALTER TABLE svt_settings ADD `turn_username` varchar(100) NOT NULL DEFAULT 'svt' AFTER `turn_port`;");
    }
}
$result = $mysqli->query("SHOW COLUMNS FROM svt_settings LIKE 'turn_password';");
if($result) {
    if ($result->num_rows==0) {
        $mysqli->query("ALTER TABLE svt_settings ADD `turn_password` varchar(100) NOT NULL DEFAULT 'svt' AFTER `turn_username`;");
    }
}
$result = $mysqli->query("SHOW COLUMNS FROM svt_virtualtours LIKE 'show_logo';");
if($result) {
    if ($result->num_rows==0) {
        $mysqli->query("ALTER TABLE svt_virtualtours ADD `show_logo` tinyint(1) NOT NULL DEFAULT '1' AFTER `show_vt_title`;");
    }
}
$result = $mysqli->query("SHOW COLUMNS FROM svt_pois LIKE 'target';");
if($result) {
    if ($result->num_rows==1) {
        $row = $result->fetch_array(MYSQLI_ASSOC);
        $type = $row['Type'];
        if (strpos($type, '_parent') === false) {
            $mysqli->query("ALTER TABLE svt_pois MODIFY COLUMN `target` enum('_blank','_self','_parent','_top') DEFAULT NULL;");
        }
    }
}
$result = $mysqli->query("SHOW COLUMNS FROM svt_virtualtours LIKE 'ui_style';");
if($result) {
    if ($result->num_rows==0) {
        $mysqli->query("ALTER TABLE svt_virtualtours ADD `ui_style` text DEFAULT NULL AFTER `font_viewer`;");
    }
}
$result = $mysqli->query("SHOW COLUMNS FROM svt_advertisements LIKE 'type';");
if($result) {
    if ($result->num_rows==0) {
        $mysqli->query("ALTER TABLE svt_advertisements ADD `type` enum('image','video','iframe') NOT NULL DEFAULT 'image' AFTER `name`;");
    }
}
$result = $mysqli->query("SHOW COLUMNS FROM svt_advertisements LIKE 'video';");
if($result) {
    if ($result->num_rows==0) {
        $mysqli->query("ALTER TABLE svt_advertisements ADD `video` varchar(50) DEFAULT NULL AFTER `image`;");
    }
}
$result = $mysqli->query("SHOW COLUMNS FROM svt_advertisements LIKE 'youtube';");
if($result) {
    if ($result->num_rows==0) {
        $mysqli->query("ALTER TABLE svt_advertisements ADD `youtube` varchar(250) DEFAULT NULL AFTER `video`;");
    }
}
$result = $mysqli->query("SHOW COLUMNS FROM svt_advertisements LIKE 'iframe_link';");
if($result) {
    if ($result->num_rows==0) {
        $mysqli->query("ALTER TABLE svt_advertisements ADD `iframe_link` varchar(250) DEFAULT NULL AFTER `youtube`;");
    }
}

//UPDATE 6.0.3
$result = $mysqli->query("SHOW COLUMNS FROM svt_access_log LIKE 'ip';");
if($result) {
    if ($result->num_rows==0) {
        $mysqli->query("ALTER TABLE svt_access_log ADD `ip` varchar(50) DEFAULT NULL;");
    }
}
$result = $mysqli->query("SHOW COLUMNS FROM svt_rooms_access_log LIKE 'ip';");
if($result) {
    if ($result->num_rows==0) {
        $mysqli->query("ALTER TABLE svt_rooms_access_log ADD `ip` varchar(50) DEFAULT NULL;");
    }
}
$result = $mysqli->query("SHOW TABLES LIKE 'svt_access_log_room';");
if($result) {
    if ($result->num_rows==0) {
        $mysqli->query("CREATE TABLE IF NOT EXISTS `svt_access_log_room` (
                                 `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
                                 `id_room` bigint(20) unsigned DEFAULT NULL,
                                 `date_time` datetime DEFAULT NULL,
                                 `ip` varchar(50) DEFAULT NULL,
                                 PRIMARY KEY (`id`),
                                 KEY `id_room` (`id_room`),
                                 CONSTRAINT `svt_access_log_room_ibfk_1` FOREIGN KEY (`id_room`) REFERENCES `svt_rooms` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
                                ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;");
    }
}
$result = $mysqli->query("SHOW TABLES LIKE 'svt_access_log_poi';");
if($result) {
    if ($result->num_rows==0) {
        $mysqli->query("CREATE TABLE IF NOT EXISTS `svt_access_log_poi` (
                                 `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
                                 `id_poi` bigint(20) unsigned DEFAULT NULL,
                                 `date_time` datetime DEFAULT NULL,
                                 `ip` varchar(50) DEFAULT NULL,
                                 PRIMARY KEY (`id`),
                                 KEY `id_poi` (`id_poi`),
                                 CONSTRAINT `svt_access_log_poi_ibfk_1` FOREIGN KEY (`id_poi`) REFERENCES `svt_pois` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
                                ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;");
    }
}
$result = $mysqli->query("SHOW COLUMNS FROM svt_plans LIKE 'enable_panorama_video';");
if($result) {
    if ($result->num_rows==0) {
        $mysqli->query("ALTER TABLE svt_plans ADD `enable_panorama_video` tinyint(1) NOT NULL DEFAULT '1' AFTER `enable_shop`;");
    }
}
$result = $mysqli->query("SHOW COLUMNS FROM svt_virtualtours LIKE 'show_custom';");
if($result) {
    if ($result->num_rows==0) {
        $mysqli->query("ALTER TABLE svt_virtualtours ADD `show_custom` tinyint(1) NOT NULL DEFAULT '0' AFTER `show_info`;");
    }
}
$result = $mysqli->query("SHOW COLUMNS FROM svt_virtualtours LIKE 'custom_content';");
if($result) {
    if ($result->num_rows==0) {
        $mysqli->query("ALTER TABLE svt_virtualtours ADD `custom_content` longtext AFTER `show_custom`;");
    }
}

//UPDATE 6.1
$result = $mysqli->query("SHOW COLUMNS FROM svt_settings LIKE 'paypal_enabled';");
if($result) {
    if ($result->num_rows==0) {
        $mysqli->query("ALTER TABLE svt_settings ADD `paypal_enabled` tinyint(1) NOT NULL DEFAULT '0' AFTER `stripe_public_key`;");
    }
}
$result = $mysqli->query("SHOW COLUMNS FROM svt_settings LIKE 'paypal_live';");
if($result) {
    if ($result->num_rows==0) {
        $mysqli->query("ALTER TABLE svt_settings ADD `paypal_live` tinyint(1) NOT NULL DEFAULT '0' AFTER `paypal_enabled`;");
    }
}
$result = $mysqli->query("SHOW COLUMNS FROM svt_settings LIKE 'paypal_client_id';");
if($result) {
    if ($result->num_rows==0) {
        $mysqli->query("ALTER TABLE svt_settings ADD `paypal_client_id` varchar(200) DEFAULT NULL AFTER `paypal_live`;");
    }
}
$result = $mysqli->query("SHOW COLUMNS FROM svt_settings LIKE 'paypal_client_secret';");
if($result) {
    if ($result->num_rows==0) {
        $mysqli->query("ALTER TABLE svt_settings ADD `paypal_client_secret` varchar(200) DEFAULT NULL AFTER `paypal_client_id`;");
    }
}
$result = $mysqli->query("SHOW COLUMNS FROM svt_settings LIKE 'id_product_paypal';");
if($result) {
    if ($result->num_rows==0) {
        $mysqli->query("ALTER TABLE svt_settings ADD `id_product_paypal` varchar(50) DEFAULT NULL AFTER `paypal_client_secret`;");
    }
}
$result = $mysqli->query("SHOW COLUMNS FROM svt_plans LIKE 'id_plan_paypal';");
if($result) {
    if ($result->num_rows==0) {
        $mysqli->query("ALTER TABLE svt_plans ADD `id_plan_paypal` varchar(50) DEFAULT NULL AFTER `id_price_stripe`;");
    }
}
$result = $mysqli->query("SHOW COLUMNS FROM svt_users LIKE 'status_subscription_paypal';");
if($result) {
    if ($result->num_rows==0) {
        $mysqli->query("ALTER TABLE svt_users ADD `status_subscription_paypal` tinyint(1) NOT NULL DEFAULT '0' AFTER `status_subscription_stripe`;");
    }
}
$result = $mysqli->query("SHOW COLUMNS FROM svt_users LIKE 'id_subscription_paypal';");
if($result) {
    if ($result->num_rows==0) {
        $mysqli->query("ALTER TABLE svt_users ADD `id_subscription_paypal` varchar(50) DEFAULT NULL AFTER `id_subscription_stripe`;");
    }
}
$result = $mysqli->query("SHOW COLUMNS FROM svt_assign_virtualtours LIKE 'edit_virtualtour_ui';");
if($result) {
    if ($result->num_rows==0) {
        $mysqli->query("ALTER TABLE svt_assign_virtualtours ADD `edit_virtualtour_ui` tinyint(1) NOT NULL DEFAULT '1' AFTER `edit_virtualtour`;");
    }
}
$result = $mysqli->query("SHOW TABLES LIKE 'svt_editor_ui_presets';");
if($result) {
    if ($result->num_rows==0) {
        $mysqli->query("CREATE TABLE IF NOT EXISTS `svt_editor_ui_presets` (
                                 `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
                                 `id_user` int(11) unsigned DEFAULT NULL,
                                 `name` varchar(100) DEFAULT NULL,
                                 `public` tinyint(1) NOT NULL DEFAULT '0',
                                 `ui_style` text DEFAULT NULL,
                                 PRIMARY KEY (`id`),
                                 KEY `id_user` (`id_user`),
                                 CONSTRAINT `svt_editor_ui_presets_ibfk_1` FOREIGN KEY (`id_user`) REFERENCES `svt_users` (`id`) ON DELETE SET NULL
                                ) ENGINE=InnoDB DEFAULT CHARSET=utf8;");
    }
}
$result = $mysqli->query("SHOW COLUMNS FROM svt_rooms LIKE 'type';");
if($result) {
    if ($result->num_rows==1) {
        $row = $result->fetch_array(MYSQLI_ASSOC);
        $type = $row['Type'];
        if (strpos($type, 'hls') === false) {
            $mysqli->query("ALTER TABLE svt_rooms MODIFY COLUMN `type` enum('image','video','hls') DEFAULT 'image';");
        }
    }
}
$result = $mysqli->query("SHOW COLUMNS FROM svt_rooms LIKE 'panorama_url';");
if($result) {
    if ($result->num_rows==0) {
        $mysqli->query("ALTER TABLE svt_rooms ADD `panorama_url` varchar(250) DEFAULT NULL AFTER `panorama_video`;");
    }
}
$result = $mysqli->query("SHOW COLUMNS FROM svt_virtualtours LIKE 'show_main_form';");
if($result) {
    if ($result->num_rows==1) {
        $row = $result->fetch_array(MYSQLI_ASSOC);
        $default = $row['Default'];
        if ($default=='1') {
            $mysqli->query("ALTER TABLE svt_virtualtours MODIFY COLUMN `show_main_form` tinyint(1) NOT NULL DEFAULT '0';");
        }
    }
}
$result = $mysqli->query("SHOW COLUMNS FROM svt_visitors LIKE 'id_room';");
if($result) {
    if ($result->num_rows==0) {
        $mysqli->query("ALTER TABLE svt_visitors ADD `id_room` bigint(20) unsigned DEFAULT NULL;");
    }
}
$result = $mysqli->query("SHOW COLUMNS FROM svt_visitors LIKE 'yaw';");
if($result) {
    if ($result->num_rows==0) {
        $mysqli->query("ALTER TABLE svt_visitors ADD `yaw` float DEFAULT NULL;");
    }
}
$result = $mysqli->query("SHOW COLUMNS FROM svt_visitors LIKE 'pitch';");
if($result) {
    if ($result->num_rows==0) {
        $mysqli->query("ALTER TABLE svt_visitors ADD `pitch` float DEFAULT NULL;");
    }
}
$result = $mysqli->query("SHOW COLUMNS FROM svt_visitors LIKE 'color';");
if($result) {
    if ($result->num_rows==0) {
        $mysqli->query("ALTER TABLE svt_visitors ADD `color` varchar(10) NOT NULL DEFAULT '#000000';");
    }
}
$result = $mysqli->query("SHOW COLUMNS FROM svt_visitors LIKE 'id';");
if($result) {
    if ($result->num_rows==0) {
        $mysqli->query("DELETE FROM svt_visitors;");
        $mysqli->query("DROP TABLE svt_visitors;");
        $mysqli->query("CREATE TABLE IF NOT EXISTS `svt_visitors` (
                                  `id_virtualtour` bigint(20) unsigned DEFAULT NULL,
                                  `initial_datetime` datetime NOT NULL DEFAULT current_timestamp(),
                                  `datetime` datetime DEFAULT NULL,
                                  `ip` varchar(50) DEFAULT NULL,
                                  `id` varchar(100) DEFAULT NULL,
                                  `id_room` bigint(20) unsigned DEFAULT NULL,
                                  `yaw` float DEFAULT NULL,
                                  `pitch` float DEFAULT NULL,
                                  `color` varchar(10) NOT NULL DEFAULT '#000000',
                                  UNIQUE KEY `id_virtualtour` (`id_virtualtour`,`ip`,`id`),
                                  CONSTRAINT `svt_visitors_ibfk_1` FOREIGN KEY (`id_virtualtour`) REFERENCES `svt_virtualtours` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
                                ) ENGINE=InnoDB DEFAULT CHARSET=utf8;");
    }
}
$result = $mysqli->query("SHOW COLUMNS FROM svt_rooms LIKE 'type';");
if($result) {
    if ($result->num_rows==1) {
        $row = $result->fetch_array(MYSQLI_ASSOC);
        $type = $row['Type'];
        if (strpos($type, 'lottie') === false) {
            $mysqli->query("ALTER TABLE svt_rooms MODIFY COLUMN `type` enum('image','video','hls','lottie') DEFAULT 'image';");
        }
    }
}
$result = $mysqli->query("SHOW COLUMNS FROM svt_rooms LIKE 'panorama_json';");
if($result) {
    if ($result->num_rows==0) {
        $mysqli->query("ALTER TABLE svt_rooms ADD `panorama_json` varchar(100) DEFAULT NULL AFTER `panorama_url`;");
    }
}
$result = $mysqli->query("SHOW COLUMNS FROM svt_virtualtours LIKE 'enable_visitor_rt';");
if($result) {
    if ($result->num_rows==0) {
        $mysqli->query("ALTER TABLE svt_virtualtours ADD `enable_visitor_rt` tinyint(1) NOT NULL DEFAULT '0';");
    }
}
$result = $mysqli->query("SHOW COLUMNS FROM svt_virtualtours LIKE 'interval_visitor_rt';");
if($result) {
    if ($result->num_rows==0) {
        $mysqli->query("ALTER TABLE svt_virtualtours ADD `interval_visitor_rt` int(11) NOT NULL DEFAULT '1000';");
    }
}
$result = $mysqli->query("SHOW COLUMNS FROM svt_maps LIKE 'default_view';");
if($result) {
    if ($result->num_rows==0) {
        $mysqli->query("ALTER TABLE svt_maps ADD `default_view` enum('street','satellite') DEFAULT 'street' AFTER `map_type`;");
    }
}
$result = $mysqli->query("SHOW COLUMNS FROM svt_virtualtours LIKE 'markers_default_lookat';");
if($result) {
    if ($result->num_rows==0) {
        $mysqli->query("ALTER TABLE svt_virtualtours ADD `markers_default_lookat` tinyint(1) NOT NULL DEFAULT '2' AFTER `markers_tooltip_type`;");
    }
}
$result = $mysqli->query("SHOW COLUMNS FROM svt_maps LIKE 'info_link';");
if($result) {
    if ($result->num_rows==0) {
        $mysqli->query("ALTER TABLE svt_maps ADD `info_link` varchar(250) DEFAULT NULL;");
    }
}
$result = $mysqli->query("SHOW COLUMNS FROM svt_maps LIKE 'info_type';");
if($result) {
    if ($result->num_rows==0) {
        $mysqli->query("ALTER TABLE svt_maps ADD `info_type` enum('blank','iframe') DEFAULT 'blank';");
    }
}
$result = $mysqli->query("SHOW COLUMNS FROM svt_rooms_alt LIKE 'poi';");
if($result) {
    if ($result->num_rows==0) {
        $mysqli->query("ALTER TABLE svt_rooms_alt ADD `poi` tinyint(1) NOT NULL DEFAULT '0';");
    }
}
$result = $mysqli->query("SHOW COLUMNS FROM svt_pois LIKE 'type';");
if($result) {
    if ($result->num_rows==1) {
        $row = $result->fetch_array(MYSQLI_ASSOC);
        $type = $row['Type'];
        if (strpos($type, 'switch_pano') === false) {
            $mysqli->query("ALTER TABLE svt_pois MODIFY COLUMN `type` enum('image','video','link','link_ext','html','html_sc','download','form','video360','audio','gallery','google_maps','object360','embed','object3d','lottie','product','switch_pano') DEFAULT NULL;");
        }
    }
}
$result = $mysqli->query("SHOW COLUMNS FROM svt_settings LIKE 'multires';");
if($result) {
    if ($result->num_rows==0) {
        $mysqli->query("ALTER TABLE svt_settings ADD `multires` enum('local','cloud') DEFAULT 'local';");
    }
}
$result = $mysqli->query("SHOW COLUMNS FROM svt_settings LIKE 'multires_cloud_url';");
if($result) {
    if ($result->num_rows==0) {
        $mysqli->query("ALTER TABLE svt_settings ADD `multires_cloud_url` varchar(250) NOT NULL DEFAULT 'https://simplevirtualtour.it/app/tools/multires_cloud.php';");
    }
}

//UPDATE 6.2
$result = $mysqli->query("SHOW COLUMNS FROM svt_virtualtours LIKE 'dollhouse';");
if($result) {
    if ($result->num_rows==0) {
        $mysqli->query("ALTER TABLE svt_virtualtours ADD `dollhouse` longtext AFTER `info_box`;");
    }
}
$result = $mysqli->query("SHOW COLUMNS FROM svt_virtualtours LIKE 'show_dollhouse';");
if($result) {
    if ($result->num_rows==0) {
        $mysqli->query("ALTER TABLE svt_virtualtours ADD `show_dollhouse` tinyint(1) NOT NULL DEFAULT '0' AFTER `show_info`;");
    }
}
$result = $mysqli->query("SHOW COLUMNS FROM svt_plans LIKE 'enable_dollhouse';");
if($result) {
    if ($result->num_rows==0) {
        $mysqli->query("ALTER TABLE svt_plans ADD `enable_dollhouse` tinyint(1) NOT NULL DEFAULT '1' AFTER `enable_shop`;");
    }
}
$result = $mysqli->query("SHOW COLUMNS FROM svt_pois LIKE 'zIndex';");
if($result) {
    if ($result->num_rows==0) {
        $mysqli->query("ALTER TABLE svt_pois ADD `zIndex` int(11) NOT NULL DEFAULT '1';");
    }
}
$result = $mysqli->query("SHOW COLUMNS FROM svt_pois LIKE 'params';");
if($result) {
    if ($result->num_rows==0) {
        $mysqli->query("ALTER TABLE svt_pois ADD `params` text AFTER `content`;");
        $mysqli->query("UPDATE svt_pois SET params='floor' WHERE type='object3d';");
    }
}
$result = $mysqli->query("SHOW COLUMNS FROM svt_pois LIKE 'auto_close';");
if($result) {
    if ($result->num_rows==0) {
        $mysqli->query("ALTER TABLE svt_pois ADD `auto_close` int(11) NOT NULL DEFAULT '0' AFTER `box_pos`;");
    }
}
$result = $mysqli->query("SHOW COLUMNS FROM svt_rooms LIKE 'hfov';");
if($result) {
    if ($result->num_rows==1) {
        $row = $result->fetch_array(MYSQLI_ASSOC);
        $default = $row['Default'];
        if ($default=='') {
            $mysqli->query("ALTER TABLE svt_rooms MODIFY `hfov` int(11) NOT NULL DEFAULT '0';");
        }
    }
}
$result = $mysqli->query("SHOW COLUMNS FROM svt_plans LIKE 'customize_menu';");
if($result) {
    if ($result->num_rows==0) {
        $mysqli->query("ALTER TABLE svt_plans ADD `customize_menu` text;");
    }
}
$result = $mysqli->query("SHOW COLUMNS FROM svt_pois LIKE 'embed_type';");
if($result) {
    if ($result->num_rows==1) {
        $row = $result->fetch_array(MYSQLI_ASSOC);
        $type = $row['Type'];
        if (strpos($type, 'video_chroma') === false) {
            $mysqli->query("ALTER TABLE svt_pois MODIFY COLUMN `embed_type` enum('image','video','gallery','video_transparent','link','text','selection','video_chroma') DEFAULT NULL;");
        }
    }
}
$result = $mysqli->query("SHOW COLUMNS FROM svt_virtualtours LIKE 'custom_html';");
if($result) {
    if ($result->num_rows==0) {
        $mysqli->query("ALTER TABLE svt_virtualtours ADD `custom_html` longtext;");
    }
}
$result = $mysqli->query("SHOW COLUMNS FROM svt_virtualtours LIKE 'context_info';");
if($result) {
    if ($result->num_rows==0) {
        $mysqli->query("ALTER TABLE svt_virtualtours ADD `context_info` text;");
    }
}
$result = $mysqli->query("SHOW COLUMNS FROM svt_plans LIKE 'enable_context_info';");
if($result) {
    if ($result->num_rows==0) {
        $mysqli->query("ALTER TABLE svt_plans ADD `enable_context_info` tinyint(1) NOT NULL DEFAULT '1' AFTER `enable_info_box`;");
    }
}
$result = $mysqli->query("SHOW COLUMNS FROM svt_virtualtours LIKE 'presentation_video';");
if($result) {
    if ($result->num_rows==0) {
        $mysqli->query("ALTER TABLE svt_virtualtours ADD `presentation_video` varchar(250) DEFAULT NULL AFTER `auto_presentation_speed`;");
    }
}
$result = $mysqli->query("SHOW COLUMNS FROM svt_virtualtours LIKE 'presentation_type';");
if($result) {
    if ($result->num_rows==0) {
        $mysqli->query("ALTER TABLE svt_virtualtours ADD `presentation_type` enum('manual','automatic','video') NOT NULL DEFAULT 'manual' AFTER `presentation_video`;");
        $mysqli->query("UPDATE svt_virtualtours SET presentation_type='automatic' WHERE auto_presentation_enable=1;");
        $mysqli->query("ALTER TABLE svt_virtualtours DROP COLUMN auto_presentation_enable;");
    }
}
$result = $mysqli->query("SHOW COLUMNS FROM svt_settings LIKE 'enable_screencast';");
if($result) {
    if ($result->num_rows==0) {
        $mysqli->query("ALTER TABLE svt_settings ADD `enable_screencast` tinyint(1) NOT NULL DEFAULT '1';");
    }
}
$result = $mysqli->query("SHOW COLUMNS FROM svt_settings LIKE 'enable_screencast';");
if($result) {
    if ($result->num_rows==0) {
        $mysqli->query("ALTER TABLE svt_settings ADD `enable_screencast` tinyint(1) NOT NULL DEFAULT '1';");
    }
}
$result = $mysqli->query("SHOW COLUMNS FROM svt_settings LIKE 'url_screencast';");
if($result) {
    if ($result->num_rows==0) {
        $mysqli->query("ALTER TABLE svt_settings ADD `url_screencast` varchar(250) NOT NULL DEFAULT 'https://studio.snipclip.app/record';");
    }
}
$result = $mysqli->query("SHOW COLUMNS FROM svt_virtualtours LIKE 'hover_markers';");
if($result) {
    if ($result->num_rows==0) {
        $mysqli->query("ALTER TABLE svt_virtualtours ADD `hover_markers` tinyint(1) NOT NULL DEFAULT '0' AFTER `hide_markers`;");
    }
}
$result = $mysqli->query("SHOW COLUMNS FROM svt_assign_virtualtours LIKE 'edit_3d_view';");
if($result) {
    if ($result->num_rows==0) {
        $mysqli->query("ALTER TABLE svt_assign_virtualtours ADD `edit_3d_view` tinyint(1) NOT NULL DEFAULT '1' AFTER `edit_virtualtour_ui`;");
    }
}

//UPDATE 6.3
$result = $mysqli->query("SHOW COLUMNS FROM svt_rooms LIKE 'protect_send_email';");
if($result) {
    if ($result->num_rows==0) {
        $mysqli->query("ALTER TABLE svt_rooms ADD `protect_send_email` tinyint(1) NOT NULL DEFAULT '0' AFTER `protect_type`;");
    }
}
$result = $mysqli->query("SHOW COLUMNS FROM svt_rooms LIKE 'protect_email';");
if($result) {
    if ($result->num_rows==0) {
        $mysqli->query("ALTER TABLE svt_rooms ADD `protect_email` varchar(250) NOT NULL AFTER `protect_send_email`;");
    }
}
$result = $mysqli->query("SHOW COLUMNS FROM svt_virtualtours LIKE 'drag_device_orientation';");
if($result) {
    if ($result->num_rows==0) {
        $mysqli->query("ALTER TABLE svt_virtualtours ADD `drag_device_orientation` tinyint(1) NOT NULL DEFAULT '1' AFTER `show_device_orientation`;");
    }
}
$result = $mysqli->query("SHOW COLUMNS FROM svt_plans LIKE 'max_storage_space';");
if($result) {
    if ($result->num_rows==0) {
        $mysqli->query("ALTER TABLE svt_plans ADD `max_storage_space` int(11) NOT NULL DEFAULT '-1' AFTER `max_file_size_upload`;");
    }
}
$result = $mysqli->query("SHOW COLUMNS FROM svt_users LIKE 'storage_space';");
if($result) {
    if ($result->num_rows==0) {
        $mysqli->query("ALTER TABLE svt_users ADD `storage_space` float NOT NULL DEFAULT '0';");
    }
}
$result = $mysqli->query("SHOW COLUMNS FROM svt_maps LIKE 'id_room_default';");
if($result) {
    if ($result->num_rows==0) {
        $mysqli->query("ALTER TABLE svt_maps ADD `id_room_default` bigint(20) unsigned DEFAULT NULL;");
        $mysqli->query("ALTER TABLE svt_maps ADD CONSTRAINT `svt_maps_svt_rooms_id_fk` FOREIGN KEY (`id_room_default`) REFERENCES `svt_rooms` (`id`) ON DELETE SET NULL;");
    }
}
$result = $mysqli->query("SHOW TABLES LIKE 'svt_notifications';");
if($result) {
    if ($result->num_rows==0) {
        $mysqli->query("CREATE TABLE IF NOT EXISTS `svt_notifications` (
                                 `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
                                 `id_user` int(11) unsigned DEFAULT NULL,
                                 `notify_date` timestamp DEFAULT CURRENT_TIMESTAMP,
                                 `subject` varchar(250) DEFAULT NULL,
                                 `body` text,
                                 `notified` tinyint(1) NOT NULL DEFAULT '0',
                                 PRIMARY KEY (`id`),
                                 KEY `id_user` (`id_user`),
                                 CONSTRAINT `svt_notifications_ibfk_1` FOREIGN KEY (`id_user`) REFERENCES `svt_users` (`id`) ON DELETE SET NULL
                                ) ENGINE=InnoDB DEFAULT CHARSET=utf8;");
    }
}
$result = $mysqli->query("SHOW COLUMNS FROM svt_settings LIKE 'notify_email';");
if($result) {
    if ($result->num_rows==0) {
        $mysqli->query("ALTER TABLE svt_settings ADD `notify_email` varchar(100) DEFAULT NULL AFTER `contact_email`;");
        $mysqli->query("UPDATE svt_settings SET notify_email=contact_email;");
    }
}
$result = $mysqli->query("SHOW COLUMNS FROM svt_settings LIKE 'notify_registrations';");
if($result) {
    if ($result->num_rows==0) {
        $mysqli->query("ALTER TABLE svt_settings ADD `notify_registrations` tinyint(1) NOT NULL DEFAULT '1';");
    }
}
$result = $mysqli->query("SHOW COLUMNS FROM svt_settings LIKE 'notify_plan_expires';");
if($result) {
    if ($result->num_rows==0) {
        $mysqli->query("ALTER TABLE svt_settings ADD `notify_plan_expires` tinyint(1) NOT NULL DEFAULT '1';");
    }
}
$result = $mysqli->query("SHOW COLUMNS FROM svt_settings LIKE 'notify_plan_changes';");
if($result) {
    if ($result->num_rows==0) {
        $mysqli->query("ALTER TABLE svt_settings ADD `notify_plan_changes` tinyint(1) NOT NULL DEFAULT '1';");
    }
}
$result = $mysqli->query("SHOW COLUMNS FROM svt_settings LIKE 'notify_plan_cancels';");
if($result) {
    if ($result->num_rows==0) {
        $mysqli->query("ALTER TABLE svt_settings ADD `notify_plan_cancels` tinyint(1) NOT NULL DEFAULT '1';");
    }
}
$result = $mysqli->query("SHOW COLUMNS FROM svt_settings LIKE 'notify_vt_create';");
if($result) {
    if ($result->num_rows==0) {
        $mysqli->query("ALTER TABLE svt_settings ADD `notify_vt_create` tinyint(1) NOT NULL DEFAULT '1';");
    }
}
$result = $mysqli->query("SHOW COLUMNS FROM svt_settings LIKE 'id_vt_template';");
if($result) {
    if ($result->num_rows==0) {
        $mysqli->query("ALTER TABLE svt_settings ADD `id_vt_template` bigint(20) unsigned DEFAULT NULL AFTER `id_vt_sample`;");
        $mysqli->query("ALTER TABLE `svt_settings` ADD FOREIGN KEY (`id_vt_template`) REFERENCES `svt_virtualtours` (`id`) ON DELETE SET NULL ON UPDATE NO ACTION;");
    }
}