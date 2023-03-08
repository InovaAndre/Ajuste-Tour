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

?>

<?php if($user_info['plan_status']=='expired') : ?>
    <div class="card bg-warning text-white shadow mb-4">
        <div class="card-body">
            <?php echo sprintf(_('Your "%s" plan has expired!'),$user_info['plan']); ?>
        </div>
    </div>
<?php exit; endif; ?>

<div class="d-sm-flex align-items-center justify-content-between mb-3">
    <h1 class="h3 mb-0 text-gray-800"><i class="fas fa-fw fa-eye text-gray-700"></i> <?php echo _("PREVIEW"); ?></h1>
    <?php echo print_virtualtour_selector($array_list_vt,$id_virtualtour_sel); ?>
</div>
<div class="row">
    <div class="col-md-12">
        <div class="card shadow mb-2">
            <div class="card-body p-0">
                <p style="display: none;padding: 15px 15px 0;" id="msg_no_room"><?php echo sprintf(_('No rooms created for this Virtual Tour. Go to %s and create a new one!'),'<a href="index.php?p=rooms">'._("Rooms").'</a>'); ?></p>
                <div style="display: none;margin-bottom:-10px;" id="iframe_div"></div>
            </div>
        </div>
    </div>
</div>

<script>
    (function($) {
        "use strict"; // Start of use strict
        window.id_user = '<?php echo $id_user; ?>';
        window.id_virtualtour = '<?php echo $id_virtualtour_sel; ?>';
        $(document).ready(function () {
            var container_h = $('#content-wrapper').height() - 170;
            preview(window.id_virtualtour,container_h);
        });
        $(window).resize(function () {
            var container_h = $('#content-wrapper').height() - 170;
            $('#iframe_div iframe').attr('height',container_h+'px');
        });
    })(jQuery); // End of use strict
</script>