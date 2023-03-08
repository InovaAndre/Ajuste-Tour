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
$can_create = get_plan_permission($id_user)['enable_forms'];
$virtual_tour = get_virtual_tour($id_virtualtour_sel,$id_user);
$forms = true;
if($user_info['role']=='editor') {
    $editor_permissions = get_editor_permissions($id_user,$id_virtualtour_sel);
    if($editor_permissions['forms']==0) {
        $forms = false;
    }
}
?>

<?php include("check_plan.php"); ?>

<div class="d-sm-flex align-items-center justify-content-between mb-3">
    <h1 class="h3 mb-0 text-gray-800"><i class="fas fa-fw fa-database text-gray-700"></i> <?php echo _("FORMS"); ?></h1>
    <?php echo print_virtualtour_selector($array_list_vt,$id_virtualtour_sel); ?>
</div>

<?php if(!$forms): ?>
    <div class="text-center">
        <div class="error mx-auto" data-text="401">401</div>
        <p class="lead text-gray-800 mb-5"><?php echo _("Permission denied"); ?></p>
        <p class="text-gray-500 mb-0"><?php echo _("It looks like that you do not have permission to access this page"); ?></p>
        <a href="index.php?p=dashboard">← <?php echo _("Back to Dashboard"); ?></a>
    </div>
<?php die(); endif; ?>

<?php if($virtual_tour['external']==1) : ?>
    <div class="card bg-warning text-white shadow mb-4">
        <div class="card-body">
            <?php echo _("You cannot manage Forms on an external virtual tour!"); ?>
        </div>
    </div>
<?php exit; endif; ?>

<?php if(!$can_create) : ?>
    <div class="card bg-warning text-white shadow mb-4">
        <div class="card-body">
            <?php echo sprintf(_('Your "%s" plan not allow to manage Forms!'),$user_info['plan'])." ".$msg_change_plan; ?>
        </div>
    </div>
<?php exit; endif; ?>

<div class="row">
    <div class="col-md-12">
        <div class="card shadow mb-4">
            <div class="card-body">
                <table class="table table-bordered table-hover" id="forms_table" width="100%" cellspacing="0">
                    <thead>
                    <tr>
                        <th><?php echo _("Date"); ?></th>
                        <th><?php echo _("Room"); ?></th>
                        <th><?php echo _("Form"); ?></th>
                        <th><?php echo _("Field"); ?> 1</th>
                        <th><?php echo _("Field"); ?> 2</th>
                        <th><?php echo _("Field"); ?> 3</th>
                        <th><?php echo _("Field"); ?> 4</th>
                        <th><?php echo _("Field"); ?> 5</th>
                        <th><?php echo _("Field"); ?> 6</th>
                        <th><?php echo _("Field"); ?> 7</th>
                        <th><?php echo _("Field"); ?> 8</th>
                        <th><?php echo _("Field"); ?> 9</th>
                        <th><?php echo _("Field"); ?> 10</th>
                    </tr>
                    </thead>
                    <tbody>

                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="row mb-3">
    <div class="col-md-12 text-center">
        <a class="badge badge-primary" target="_blank" href="ajax/export_forms_data.php?id_vt=<?php echo $id_virtualtour_sel; ?>"><?php echo _("export"); ?></a>
        <?php if($user_info['role']!='editor') : ?>
        &nbsp;&nbsp;<a class="badge badge-danger" target="_blank" href="#" data-toggle="modal" data-target="#modal_reset_forms_data"><?php echo _("reset"); ?></a>
        <?php endif; ?>
    </div>
</div>

<div id="modal_reset_forms_data" class="modal" tabindex="-1" role="dialog" data-backdrop="static">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><?php echo _("Reset Forms Data"); ?></h5>
            </div>
            <div class="modal-body">
                <p><?php echo _("Are you sure you want to reset all forms data for this virtual tour?"); ?></p>
            </div>
            <div class="modal-footer">
                <button <?php echo ($demo) ? 'disabled':''; ?> onclick="reset_forms_data();" type="button" class="btn btn-danger"><i class="fas fa-trash"></i> <?php echo _("Yes, Reset"); ?></button>
                <button type="button" class="btn btn-secondary" data-dismiss="modal"><i class="fas fa-times"></i> <?php echo _("Close"); ?></button>
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
            $('#forms_table').DataTable({
                "order": [[ 0, "desc" ]],
                "responsive": true,
                "scrollX": true,
                "processing": true,
                "searching": false,
                "serverSide": true,
                "ajax": "ajax/get_forms_data.php?id_vt="+id_virtualtour,
                "language": {
                    "decimal":        "",
                    "emptyTable":     "<?php echo _("No data available in table"); ?>",
                    "info":           "<?php echo sprintf(_("Showing %s to %s of %s entries"),'_START_','_END_','_TOTAL_'); ?>",
                    "infoEmpty":      "<?php echo _("Showing 0 to 0 of 0 entries"); ?>",
                    "infoFiltered":   "<?php echo sprintf(_("(filtered from %s total entries)"),'_MAX_'); ?>",
                    "infoPostFix":    "",
                    "thousands":      ",",
                    "lengthMenu":     "<?php echo sprintf(_("Show %s entries"),'_MENU_'); ?>",
                    "loadingRecords": "<?php echo _("Loading"); ?>...",
                    "processing":     "<?php echo _("Processing"); ?>...",
                    "search":         "<?php echo _("Search"); ?>:",
                    "zeroRecords":    "<?php echo _("No matching records found"); ?>",
                    "paginate": {
                        "first":      "<?php echo _("First"); ?>",
                        "last":       "<?php echo _("Last"); ?>",
                        "next":       "<?php echo _("Next"); ?>",
                        "previous":   "<?php echo _("Previous"); ?>"
                    },
                    "aria": {
                        "sortAscending":  ": <?php echo _("activate to sort column ascending"); ?>",
                        "sortDescending": ": <?php echo _("activate to sort column descending"); ?>"
                    }
                }
            });
        });
    })(jQuery); // End of use strict
</script>