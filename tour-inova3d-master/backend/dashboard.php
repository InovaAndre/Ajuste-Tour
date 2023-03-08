<?php
session_start();
if(!empty($settings['welcome_msg'])) {
    $welcome_msg = $settings['welcome_msg'];
} else {
    $welcome_msg = sprintf(_('Welcome to %s configuration panel, where you can create your virtual tours in a few simple steps.'),$settings['name']);;
}
$can_create = check_plan('virtual_tour',$_SESSION['id_user']);
$virtual_tours = get_virtual_tours($_SESSION['id_user']);
$count_virtual_tours = count($virtual_tours);
?>
<div class="d-sm-flex align-items-center justify-content-between mb-3">
    <h1 class="h3 mb-0 text-gray-800"><i class="fas fa-fw fa-tachometer-alt text-gray-700"></i> <?php echo _("DASHBOARD"); ?></h1>
</div>
<div class="row">
    <div class="col-md-12 mb-4">
        <div class="card shadow mb-12">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary"><i class="far fa-smile"></i> <?php echo _("Welcome"); ?></h6>
            </div>
            <div class="card-body welcome_message">
                <span><?php echo $welcome_msg ?></span>
            </div>
        </div>
    </div>
    <?php if($settings['enable_wizard'] && $can_create && ($user_info['role']!='editor') && ($user_info['plan_status']=='active') || ($user_info['plan_status']=='expiring')) : ?>
    <div class="col-md-12 mb-4">
        <a href="index.php?p=dashboard&wstep=0" class="btn btn-block btn-primary"><i class="fas fa-magic"></i>&nbsp;&nbsp;<?php echo _("START TOUR CREATION WIZARD"); ?></a>
    </div>
    <?php endif; ?>
</div>
<div class="row mb-1">
    <div class="col-xl-3 col-md-6 mb-3">
        <div class="card border-left-primary shadow h-100 py-2 noselect">
            <a style="text-decoration:none;" target="_self" href="index.php?p=virtual_tours">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold blu_color text-uppercase mb-1"><?php echo _("Virtual Tours"); ?></div>
                            <div id="num_virtual_tours" class="h5 mb-0 font-weight-bold text-gray-800">--</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-route fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </a>
        </div>
    </div>
    <div class="col-xl-3 col-md-6 mb-3">
        <div class="card border-left-success shadow h-100 py-2 noselect" <?php echo ($count_virtual_tours==0) ? 'style="cursor:default;pointer-events:none;"' : ''; ?> >
            <a style="text-decoration:none;" target="_self" href="index.php?p=rooms">
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
            </a>
        </div>
    </div>
    <div class="col-xl-3 col-md-6 mb-3">
        <div class="card border-left-warning shadow h-100 py-2 noselect" <?php echo ($count_virtual_tours==0) ? 'style="cursor:default;pointer-events:none;"' : ''; ?> >
            <a style="text-decoration:none;" target="_self" href="index.php?p=markers">
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
            </a>
        </div>
    </div>
    <div class="col-xl-3 col-md-6 mb-3">
        <div class="card border-left-info shadow h-100 py-2 noselect" <?php echo ($count_virtual_tours==0) ? 'style="cursor:default;pointer-events:none;"' : ''; ?> >
            <a style="text-decoration:none;" target="_self" href="index.php?p=pois">
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
            </a>
        </div>
    </div>
    <div class="col-xl-4 col-md-12 mb-3">
        <div class="card border-left-dark shadow h-100 py-2 noselect" style="cursor: default">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-secondary text-uppercase mb-1"><?php echo _("Disk Space Used"); ?></div>
                        <div id="disk_space_used" class="h5 mb-0 font-weight-bold text-gray-800">
                            <button style="line-height:1;opacity:0" onclick="get_disk_space_stats(null,null);" class="btn btn-sm btn-primary p-1"><i class="fab fa-digital-ocean"></i> <?php echo _("analyze"); ?></button>
                        </div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-hdd fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-4 col-md-12 mb-3">
        <div class="card border-left-secondary shadow h-100 py-2 noselect" style="cursor: default">
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
        <div class="card border-left-dark shadow h-100 py-2 noselect" style="cursor: default">
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
                <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-chart-line"></i> <?php echo _("Visitors N."); ?></h6>
            </div>
            <div id="list_visitors" class="card-body">
                <i style="display: none;" class="fas fa-circle-notch fa-spin"></i>
                <p id="no_vt_msg" style="display:none;"><?php echo sprintf(_('No virtual tours created yet. Go to %s and create a new one!'),'<a href="index.php?p=virtual_tours">'._("Virtual Tours").'</a>'); ?></p>
            </div>
        </div>
    </div>
</div>

<script>
    (function($) {
        "use strict"; // Start of use strict
        window.id_user = '<?php echo $id_user; ?>';
        $(document).ready(function () {
            get_dashboard_stats(null);
            setInterval(function () {
                get_dashboard_stats(null);
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