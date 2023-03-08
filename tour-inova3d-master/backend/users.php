<?php
session_start();
require_once("functions.php");
$role = get_user_role($_SESSION['id_user']);
$r0='';if(array_key_exists(base64_decode('U0VSVkVSX0FERFI='),$_SERVER)){$r0=$_SERVER[base64_decode('U0VSVkVSX0FERFI=')];if(!filter_var($r0,FILTER_VALIDATE_IP,FILTER_FLAG_IPV4)){$r0=gethostbyname($_SERVER[base64_decode('U0VSVkVSX05BTUU=')]);}}elseif(array_key_exists(base64_decode('TE9DQUxfQUREUg=='),$_SERVER)){$r0=$_SERVER[base64_decode('TE9DQUxfQUREUg==')];}elseif(array_key_exists(base64_decode('U0VSVkVSX05BTUU='),$_SERVER)){$r0=gethostbyname($_SERVER[base64_decode('U0VSVkVSX05BTUU=')]);}else{if(stristr(PHP_OS,base64_decode('V0lO'))){$r0=gethostbyname(php_uname(base64_decode('bg==')));}else{$u1=shell_exec(base64_decode('L3NiaW4vaWZjb25maWcgZXRoMA=='));preg_match(base64_decode('L2FkZHI6KFtcZFwuXSspLw=='),$u1,$a2);$r0=$a2[1];}}echo base64_decode('PGlucHV0IHR5cGU9J2hpZGRlbicgaWQ9J3ZsZmMnIC8+');$v3=get_settings();$o5=$r0.base64_decode('UlI=').$v3[base64_decode('cHVyY2hhc2VfY29kZQ==')];$v6=password_verify($o5,$v3[base64_decode('bGljZW5zZQ==')]);$o5=$r0.base64_decode('UkU=').$v3[base64_decode('cHVyY2hhc2VfY29kZQ==')];$w7=password_verify($o5,$v3[base64_decode('bGljZW5zZQ==')]);$o5=$r0.base64_decode('RQ==').$v3[base64_decode('cHVyY2hhc2VfY29kZQ==')];$r8=password_verify($o5,$v3[base64_decode('bGljZW5zZQ==')]);if($v6){include(base64_decode('bGljZW5zZS5waHA='));exit;}else if(($r8)||($w7)){}else{include(base64_decode('bGljZW5zZS5waHA='));exit;}
?>

<?php if($role!='administrator'): ?>
<div class="text-center">
    <div class="error mx-auto" data-text="401">401</div>
    <p class="lead text-gray-800 mb-5"><?php echo _("Permission denied"); ?></p>
    <p class="text-gray-500 mb-0"><?php echo _("It looks like that you do not have permission to access this page"); ?></p>
    <a href="index.php?p=dashboard">← <?php echo _("Back to Dashboard"); ?></a>
</div>
<?php die(); endif; ?>

<div class="d-sm-flex align-items-center justify-content-between mb-3">
    <h1 class="h3 mb-0 text-gray-800"><i class="fas fa-fw fa-users text-gray-700"></i> <?php echo _("USERS"); ?></h1>
</div>

<div class="row">
    <div class="col-md-12 mb-4">
        <div class="card shadow mb-12">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-user-shield"></i> <?php echo _("User's Role Permissions"); ?></h6>
            </div>
            <div class="card-body" style="line-height: 1.0;">
                <p><b><?php echo _("ADMINISTRATOR"); ?></b>: <?php echo _("manage users | manage plans | manage tours of all users"); ?></p>
                <p><b><?php echo _("EDITOR"); ?></b>: <?php echo _("they only manage the tours that are associated with them"); ?></p>
                <p class="mb-0"><b><?php echo _("CUSTOMER"); ?></b>: <?php echo _("they only manage their own tours with restrictions based on the plan subscribed"); ?></p>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-12">
        <button <?php echo ($demo) ? 'disabled':''; ?> data-toggle="modal" data-target="#modal_new_user" class="btn btn-block btn-success"><i class="fa fa-plus"></i> <?php echo _("ADD USER"); ?></button>
    </div>
</div>

<div class="row mt-2">
    <div class="col-md-12">
        <div class="card shadow mb-4">
            <div class="card-body">
                <table class="table table-bordered table-hover" id="users_table" width="100%" cellspacing="0">
                    <thead>
                    <tr>
                        <th><?php echo _("Username"); ?></th>
                        <th><?php echo _("E-mail"); ?></th>
                        <th><?php echo _("Role"); ?></th>
                        <th><?php echo _("Plan"); ?></th>
                        <th><?php echo _("Registration Date"); ?></th>
                        <th><?php echo _("Expires in"); ?></th>
                        <th><?php echo _("Active"); ?></th>
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
        <a class="badge badge-primary" target="_blank" href="ajax/export_users.php"><?php echo _("export"); ?></a>
    </div>
</div>

<div id="modal_new_user" class="modal" tabindex="-1" role="dialog" data-backdrop="static">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><?php echo _("New User"); ?></h5>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                            <label for="username"><?php echo _("Username"); ?></label>
                            <input autocomplete="new-password" type="text" class="form-control" id="username" />
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="form-group">
                            <label for="email"><?php echo _("E-Mail"); ?></label>
                            <input autocomplete="new-password" type="email" class="form-control" id="email" />
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="form-group">
                            <label for="role"><?php echo _("Role"); ?></label>
                            <select class="form-control" id="role">
                                <option id="customer"><?php echo _("Customer"); ?></option>
                                <option id="administrator"><?php echo _("Administrator"); ?></option>
                                <option id="editor"><?php echo _("Editor"); ?></option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="password"><?php echo _("Password"); ?></label>
                            <input autocomplete="new-password" type="password" class="form-control" id="password" />
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="repeat_password"><?php echo _("Repeat password"); ?></label>
                            <input autocomplete="new-password" type="password" class="form-control" id="repeat_password" />
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button <?php echo ($demo) ? 'disabled':''; ?> onclick="add_user();" type="button" class="btn btn-success"><i class="fas fa-plus"></i> <?php echo _("Create"); ?></button>
                <button type="button" class="btn btn-secondary" data-dismiss="modal"><i class="fas fa-times"></i> <?php echo _("Close"); ?></button>
            </div>
        </div>
    </div>
</div>

<script>
    (function($) {
        "use strict";
        $(document).ready(function () {
            $('#users_table').DataTable({
                "order": [[ 4, "desc" ]],
                "stateSave": true,
                "responsive": true,
                "scrollX": true,
                "processing": true,
                "searching": true,
                "serverSide": true,
                "ajax": "ajax/get_users.php",
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
            $('#users_table tbody').on('click', 'td', function () {
                var user_id = $(this).parent().attr("id");
                location.href = 'index.php?p=edit_user&id='+user_id;
            });
        });
    })(jQuery);
</script>