<?php
session_start();
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
$virtual_tour = get_virtual_tour($id_virtualtour_sel,$id_user);
$max_file_size_upload = _GetMaxAllowedUploadSize();
$media_library = true;
if($user_info['role']=='editor') {
    $editor_permissions = get_editor_permissions($id_user,$id_virtualtour_sel);
    if($editor_permissions['media_library']==0) {
        $media_library = false;
    }
}
if(isset($_SESSION['library_type'])) {
    $library_type = $_SESSION['library_type'];
} else {
    $library_type = 'tour';
}
?>

<?php include("check_plan.php"); ?>

<div class="d-sm-flex align-items-center justify-content-between mb-3">
    <h1 class="h3 mb-0 text-gray-800"><i class="fas fa-fw fa-photo-video text-gray-700"></i> <?php echo _("MEDIA LIBRARY"); ?></h1>
    <div class="<?php echo ($library_type=='public') ? 'disabled' : ''; ?>"><?php echo print_virtualtour_selector($array_list_vt,$id_virtualtour_sel); ?></div>
</div>

<?php if(!$media_library): ?>
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
            <?php echo _("You cannot use Media Library on an external virtual tour!"); ?>
        </div>
    </div>
<?php exit; endif; ?>

<?php if($user_info['role']=='administrator') : ?>
    <div class="row mb-3">
        <div class="col-md-6">
            <button onclick="session_library('tour');" class="btn btn-block <?php echo ($library_type=='tour') ? 'btn-primary' : 'btn-outline-primary'; ?>"><?php echo _("Tour Library"); ?></button>
        </div>
        <div class="col-md-6">
            <button onclick="session_library('public');" class="btn btn-block <?php echo ($library_type=='public') ? 'btn-primary' : 'btn-outline-primary'; ?>"><?php echo _("Public Library"); ?></button>
        </div>
    </div>
<?php endif; ?>

<?php if($library_type=='public') : ?>
    <div class="card bg-warning text-white shadow mb-4">
        <div class="card-body">
            <?php echo _("The contents of this library will be shared for all the virtual tours."); ?>
        </div>
    </div>
<?php endif; ?>

<div class="row">
    <div class="col-md-12">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-grip-horizontal"></i> <?php echo _("Files List"); ?></h6>
            </div>
            <div class="card-body">
                <form action="ajax/upload_media_library_file.php" class="dropzone mb-3 noselect <?php echo ($demo || $disabled_upload) ? 'disabled' : ''; ?>" id="media-dropzone"></form>
                <div id="list_files">
                    <p><?php echo _("Loading files ..."); ?></p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    (function($) {
        "use strict"; // Start of use strict
        window.id_user = '<?php echo $id_user; ?>';
        window.id_virtualtour = '<?php echo ($library_type=='tour') ? $id_virtualtour_sel : ''; ?>';
        window.media_library_files = [];
        Dropzone.autoDiscover = false;
        $(document).ready(function () {
            get_media_library_files(id_virtualtour);
            var media_files_dropzone = new Dropzone("#media-dropzone", {
                url: "ajax/upload_media_library_file.php",
                parallelUploads: 1,
                maxFilesize: <?php echo $max_file_size_upload; ?>,
                timeout: 120000,
                dictDefaultMessage: "<?php echo _("Drop files or click here to upload"); ?>",
                dictFallbackMessage: "<?php echo _("Your browser does not support drag'n'drop file uploads."); ?>",
                dictFallbackText: "<?php echo _("Please use the fallback form below to upload your files like in the olden days."); ?>",
                dictFileTooBig: "<?php echo sprintf(_("File is too big (%sMiB). Max filesize: %sMiB."),'{{filesize}}','{{maxFilesize}}'); ?>",
                dictInvalidFileType: "<?php echo _("You can't upload files of this type."); ?>",
                dictResponseError: "<?php echo sprintf(_("Server responded with %s code."),'{{statusCode}}'); ?>",
                dictCancelUpload: "<?php echo _("Cancel upload"); ?>",
                dictCancelUploadConfirmation: "<?php echo _("Are you sure you want to cancel this upload?"); ?>",
                dictRemoveFile: "<?php echo _("Remove file"); ?>",
                dictMaxFilesExceeded: "<?php echo _("You can not upload any more files."); ?>",
                acceptedFiles: 'image/*,video/mp4'
            });
            media_files_dropzone.on("addedfile", function(file) {
                $('#list_files').addClass('disabled');
            });
            media_files_dropzone.on("success", function(file,rsp) {
                add_file_to_media_library(id_virtualtour,rsp);
            });
            media_files_dropzone.on("queuecomplete", function() {
                $('#list_files').removeClass('disabled');
                media_files_dropzone.removeAllFiles();
            });
        });
    })(jQuery); // End of use strict
</script>