<?php
session_start();
$id_user = $_SESSION['id_user'];
$virtual_tours = get_virtual_tours($id_user);
$count_virtual_tours = count($virtual_tours);
$array_list_vt = array();
if ($count_virtual_tours==1) {
    $id_virtualtour_sel = $virtual_tours[0]['id'];
    $name_virtualtour_sel = $virtual_tours[0]['name'];
    $author_virtualtour_sel = $virtual_tours[0]['author'];
    $_SESSION['id_virtualtour_sel'] = $id_virtualtour_sel;
    $_SESSION['name_virtualtour_sel'] = $name_virtualtour_sel;
    $array_list_vt[] = array("id"=>$id_virtualtour_sel,"name"=>$name_virtualtour_sel,"author"=>$author_virtualtour_sel);
} else {
    if(isset($_GET['id_vt'])) {
        $id_virtualtour_sel = $_GET['id_vt'];
        $name_virtualtour_sel = get_virtual_tour($_GET['id_vt'],$id_user)['name'];
        $_SESSION['id_virtualtour_sel'] = $id_virtualtour_sel;
        $_SESSION['name_virtualtour_sel'] = $name_virtualtour_sel;
    } else {
        if(isset($_SESSION['id_virtualtour_sel'])) {
            $id_virtualtour_sel = $_SESSION['id_virtualtour_sel'];
            $name_virtualtour_sel = $_SESSION['name_virtualtour_sel'];
        } else {
            $id_virtualtour_sel = $virtual_tours[0]['id'];
            $name_virtualtour_sel = $virtual_tours[0]['name'];
            $_SESSION['id_virtualtour_sel'] = $id_virtualtour_sel;
            $_SESSION['name_virtualtour_sel'] = $name_virtualtour_sel;
        }
    }
    foreach ($virtual_tours as $virtual_tour) {
        $id_virtualtour = $virtual_tour['id'];
        $name_virtualtour = $virtual_tour['name'];
        $author_virtualtour = $virtual_tour['author'];
        $array_list_vt[] = array("id"=>$id_virtualtour,"name"=>$name_virtualtour,"author"=>$author_virtualtour);
    }
}
$can_create = get_plan_permission($id_user)['enable_statistics'];
$virtual_tour = get_virtual_tour($id_virtualtour_sel,$id_user);
if ($virtual_tour['external'] == 1) {
    $hide_external = "d-none";
} else {
    $hide_external = "";
}
$settings = get_settings();
$theme_color = $settings['theme_color'];
if(isset($_SESSION['statistics_type'])) {
    $statistics_type = $_SESSION['statistics_type'];
} else {
    $statistics_type = 'all';
}
?>

<?php include("check_plan.php"); ?>

<?php if(!$can_create) : ?>
    <div class="card bg-warning text-white shadow mb-4">
        <div class="card-body">
            <?php echo sprintf(_('Your "%s" plan not allow to view Statistics!'),$user_info['plan'])." ".$msg_change_plan; ?>
        </div>
    </div>
<?php exit; endif; ?>

<div class="d-sm-flex align-items-center justify-content-between mb-3">
    <h1 class="h3 mb-0 text-gray-800"><i class="fas fa-fw fa-chart-area text-gray-700"></i> <?php echo _("STATISTICS"); ?></h1>
    <?php echo print_virtualtour_selector($array_list_vt,$id_virtualtour_sel); ?>
</div>

<div class="row mb-3">
    <div class="col-md-6">
        <button onclick="session_statistics('all');" class="btn btn-block <?php echo ($statistics_type=='all') ? 'btn-primary' : 'btn-outline-primary'; ?>"><?php echo _("All"); ?></button>
    </div>
    <div class="col-md-6">
        <button onclick="session_statistics('unique');" class="btn btn-block <?php echo ($statistics_type=='unique') ? 'btn-primary' : 'btn-outline-primary'; ?>"><?php echo _("Unique"); ?></button>
    </div>
</div>

<div class="row">
    <div class="col-xl-4 col-md-6 mb-3">
        <div class="card border-left-success shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1"><?php echo _("Rooms"); ?></div>
                        <div id="num_rooms" class="h5 mb-0 font-weight-bold text-gray-800">--</div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-vector-square fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-4 col-md-6 mb-3">
        <div class="card border-left-warning shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-warning text-uppercase mb-1"><?php echo _("Markers"); ?></div>
                        <div id="num_markers" class="h5 mb-0 font-weight-bold text-gray-800">--</div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-caret-square-up fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-4 col-md-6 mb-3">
        <div class="card border-left-info shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-info text-uppercase mb-1"><?php echo _("POIs"); ?></div>
                        <div id="num_pois" class="h5 mb-0 font-weight-bold text-gray-800">--</div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-bullseye fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-4 col-md-12 mb-3">
        <div class="card border-left-dark shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-secondary text-uppercase mb-1"><?php echo _("Disk Space Used"); ?></div>
                        <div id="disk_space_used" class="h5 mb-0 font-weight-bold text-gray-800">--</div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-hdd fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-4 col-md-12 mb-3">
        <div class="card border-left-secondary shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-secondary text-uppercase mb-1"><?php echo _("Total Visitors"); ?></div>
                        <div id="total_visitors" class="h5 mb-0 font-weight-bold text-gray-800">--</div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-chart-line fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-4 col-md-12 mb-3">
        <div class="card border-left-dark shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-dark text-uppercase mb-1"><?php echo _("Online Visitors"); ?></div>
                        <div id="total_online_visitors" class="h5 mb-0 font-weight-bold text-gray-800">--</div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-eye fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-md-12">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-chart-line"></i> <?php echo _("Virtual Tour Accesses"); ?></h6>
            </div>
            <div class="card-body">
                <canvas height="250" id="chart_visitor_vt"></canvas>
            </div>
        </div>
    </div>
    <div class="col-md-6 <?php echo $hide_external; ?>">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-door-open"></i> <?php echo _("Rooms Accesses"); ?></h6>
            </div>
            <div class="card-body">
                <canvas id="chart_rooms_access"></canvas>
            </div>
        </div>
    </div>
    <div class="col-md-6 <?php echo $hide_external; ?>">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-stopwatch"></i> <?php echo _("Rooms Permanence (seconds)"); ?></h6>
            </div>
            <div class="card-body">
                <canvas id="chart_rooms_time"></canvas>
            </div>
        </div>
    </div>
    <div class="col-md-12 <?php echo $hide_external; ?>">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary"><i class="far fa-eye"></i> <?php echo _("POI views"); ?></h6>
            </div>
            <div id="chart_poi_views" class="card-body">

            </div>
        </div>
    </div>
</div>

<?php if($user_info['role']!='editor') : ?>
<div class="row mb-3">
    <div class="col-md-12 text-center">
        <a class="badge badge-danger" target="_blank" href="#" data-toggle="modal" data-target="#modal_reset_statistics"><?php echo _("reset"); ?></a>
    </div>
</div>
<?php endif; ?>

<div id="modal_reset_statistics" class="modal" tabindex="-1" role="dialog" data-backdrop="static">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><?php echo _("Reset Statistics"); ?></h5>
            </div>
            <div class="modal-body">
                <p><?php echo _("Are you sure you want to reset all statistics for this virtual tour?"); ?></p>
            </div>
            <div class="modal-footer">
                <button <?php echo ($demo) ? 'disabled':''; ?> onclick="reset_statistics();" type="button" class="btn btn-danger"><i class="fas fa-trash"></i> <?php echo _("Yes, Reset"); ?></button>
                <button type="button" class="btn btn-secondary" data-dismiss="modal"><i class="fas fa-times"></i> <?php echo _("Close"); ?></button>
            </div>
        </div>
    </div>
</div>

<script>
    (function($) {
        "use strict"; // Start of use strict
        Chart.defaults.global.defaultFontFamily = 'Nunito', '-apple-system,system-ui,BlinkMacSystemFont,"Segoe UI",Roboto,"Helvetica Neue",Arial,sans-serif';
        window.id_user = '<?php echo $id_user; ?>';
        window.id_virtualtour = '<?php echo $id_virtualtour_sel; ?>';
        window.theme_color = '<?php echo $theme_color; ?>';
        $(document).ready(function () {
            get_statistics('chart_visitor_vt');
            get_statistics('chart_rooms_access');
            get_statistics('chart_rooms_time');
            get_statistics('chart_poi_views');
            get_dashboard_stats(window.id_virtualtour);
            setInterval(function () {
                get_dashboard_stats(window.id_virtualtour);
            },30 * 1000);
        });
        var xhrPool = [];
        $(document).ajaxSend(function(e, jqXHR, options){
            xhrPool.push(jqXHR);
        });
        $(document).ajaxComplete(function(e, jqXHR, options) {
            xhrPool = $.grep(xhrPool, function(x){return x!=jqXHR});
        });
        var abort = function() {
            console.log('abort');
            $.each(xhrPool, function(idx, jqXHR) {
                jqXHR.abort();
            });
        };
        var oldbeforeunload = window.onbeforeunload;
        window.onbeforeunload = function() {
            var r = oldbeforeunload ? oldbeforeunload() : undefined;
            if (r == undefined) {
                abort();
            }
            return r;
        }
    })(jQuery); // End of use strict
</script>