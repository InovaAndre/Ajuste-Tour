<?php
error_reporting(E_ALL & ~E_WARNING & ~E_NOTICE & ~E_DEPRECATED);
ob_start();
session_start();
if((($_SERVER['SERVER_ADDR']=='5.9.29.89') && ($_SERVER['REMOTE_ADDR']!=$_SESSION['ip_developer']) && ($_SESSION['id_user']==1)) || ($_SESSION['svt_si']!=session_id())) {
    die();
}
require_once("../../db/connection.php");
$id = $_POST['id'];
$id_virtualtour = $_POST['id_virtualtour'];
$direction = $_POST['direction'];
$priority = $_POST['priority'];

$query = "SELECT priority_1,priority_2,id_room FROM svt_presentations WHERE id=$id;";
$result = $mysqli->query($query);
if($result) {
    if ($result->num_rows == 1) {
        $row = $result->fetch_array(MYSQLI_ASSOC);
        $priority_1 = $row['priority_1'];
        $priority_2 = $row['priority_2'];
        $id_room = $row['id_room'];
    }
}

switch($priority) {
    case 1:
        switch($direction) {
            case 'down':
                $query = "SELECT id FROM svt_presentations WHERE priority_1=".($priority_1+1).";";
                $result = $mysqli->query($query);
                if($result) {
                    if ($result->num_rows > 0) {
                        $mysqli->query("UPDATE svt_presentations SET priority_1=priority_1+1 WHERE priority_1=$priority_1;");
                        while ($row = $result->fetch_array(MYSQLI_ASSOC)) {
                            $idm = $row['id'];
                            $mysqli->query("UPDATE svt_presentations SET priority_1=priority_1-1 WHERE id=$idm;");
                        }
                    }
                }
                break;
            case 'up':
                $query = "SELECT id FROM svt_presentations WHERE priority_1=".($priority_1-1).";";
                $result = $mysqli->query($query);
                if($result) {
                    if ($result->num_rows > 0) {
                        $mysqli->query("UPDATE svt_presentations SET priority_1=priority_1-1 WHERE priority_1=$priority_1;");
                        while ($row = $result->fetch_array(MYSQLI_ASSOC)) {
                            $idm = $row['id'];
                            $mysqli->query("UPDATE svt_presentations SET priority_1=priority_1+1 WHERE id=$idm;");
                        }
                    }
                }
                break;
        }
        break;
    case 2:
        switch($direction) {
            case 'down':
                $query = "SELECT id FROM svt_presentations WHERE priority_2=".($priority_2+1)." AND id_room=$id_room;";
                $result = $mysqli->query($query);
                if($result) {
                    if ($result->num_rows > 0) {
                        $mysqli->query("UPDATE svt_presentations SET priority_2=priority_2+1 WHERE priority_2=$priority_2 AND id_room=$id_room;");
                        while ($row = $result->fetch_array(MYSQLI_ASSOC)) {
                            $idm = $row['id'];
                            $mysqli->query("UPDATE svt_presentations SET priority_2=priority_2-1 WHERE id=$idm;");
                        }
                    }
                }
                break;
            case 'up':
                $query = "SELECT id FROM svt_presentations WHERE priority_2=".($priority_2-1)." AND id_room=$id_room;";
                $result = $mysqli->query($query);
                if($result) {
                    if ($result->num_rows > 0) {
                        $mysqli->query("UPDATE svt_presentations SET priority_2=priority_2-1 WHERE priority_2=$priority_2 AND id_room=$id_room;");
                        while ($row = $result->fetch_array(MYSQLI_ASSOC)) {
                            $idm = $row['id'];
                            $mysqli->query("UPDATE svt_presentations SET priority_2=priority_2+1 WHERE id=$idm;");
                        }
                    }
                }
                break;
        }
        break;
}
ob_end_clean();

