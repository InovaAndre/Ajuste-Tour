<?php
session_start();
require_once("functions.php");
$id_user = $_SESSION['id_user'];
$id_product = $_GET['id'];
$product = get_product($id_product,$id_user);
$virtual_tour = get_virtual_tour($product['id_virtualtour'],$id_user);
?>

<?php if(!$product): ?>
    <div class="text-center">
        <div class="error mx-auto" data-text="401">401</div>
        <p class="lead text-gray-800 mb-5"><?php echo _("Permission denied"); ?></p>
        <p class="text-gray-500 mb-0"><?php echo _("It looks like that you do not have permission to access this page"); ?></p>
        <a href="index.php?p=dashboard">← <?php echo _("Back to Dashboard"); ?></a>
    </div>
<?php die(); endif; ?>

<div class="d-sm-flex align-items-center justify-content-between mb-3">
    <h1 class="h3 mb-0 text-gray-800"><i class="fas fa-fw fa-shopping-cart text-gray-700"></i> <?php echo _("EDIT PRODUCT"); ?></h1>
    <div>
        <button <?php echo ($demo) ? 'disabled':''; ?> onclick="modal_delete_product(<?php echo $id_product; ?>);" class="btn btn-sm btn-danger mb-2 ml-3 float-right"><?php echo _("DELETE"); ?></button>
        <a id="save_btn" href="#" onclick="save_product(<?php echo $id_product; ?>);return false;" class="btn btn-sm btn-success btn-icon-split mb-2 <?php echo ($demo) ? 'disabled':''; ?>">
            <span class="icon text-white-50">
              <i class="far fa-circle"></i>
            </span>
            <span class="text"><?php echo _("SAVE"); ?></span>
        </a>
    </div>
</div>

<div class="row">
    <div class="col-md-12">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-info-circle"></i> <?php echo _("Details"); ?></h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-8">
                        <div class="form-group">
                            <label for="name"><?php echo _("Name"); ?></label>
                            <input type="text" class="form-control" id="name" value="<?php echo $product['name']; ?>" />
                        </div>
                    </div>
                    <div class="col-md-4">
                        <label for="price"><?php echo _("Price"); ?></label><br>
                        <div class="input-group mb-0">
                            <input min="0" type="number" class="form-control" id="price" value="<?php echo $product['price']; ?>" />
                            <div class="input-group-append">
                                <span class="input-group-text"><?php echo $virtual_tour['snipcart_currency'] ?></span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="type"><?php echo _("Purchase Type"); ?> <i title="<?php echo _("the cart works only if snipcart is configured in the shop section of the tour"); ?>" class="help_t fas fa-question-circle"></i></label>
                            <select onchange="change_product_type();" class="form-control" id="type">
                                <option <?php echo ($product['purchase_type']=='none') ? 'selected' : ''; ?> id="t_none"><?php echo _("None"); ?></option>
                                <option <?php echo ($product['purchase_type']=='link') ? 'selected' : ''; ?> id="t_link"><?php echo _("Link"); ?></option>
                                <option <?php echo ($product['purchase_type']=='cart') ? 'selected' : ''; ?> id="t_cart"><?php echo _("Cart"); ?></option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-8">
                        <div class="form-group">
                            <label for="link"><?php echo _("Link"); ?></label>
                            <input <?php echo ($product['purchase_type']!='link') ? 'disabled' : ''; ?> type="text" class="form-control" id="link" value="<?php echo $product['link']; ?>" />
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="form-group">
                            <label for="description"><?php echo _("Description"); ?></label>
                            <div id="description"><?php echo $product['description']; ?></div>
                        </div>
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
                <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-grip-horizontal"></i> <?php echo _("Images List"); ?> <i style="font-size:12px">(<?php echo _("drag images to change order"); ?>)</i></h6>
            </div>
            <div class="card-body">
                <form action="ajax/upload_product_image.php" class="dropzone mb-3 noselect" id="product-dropzone"></form>
                <div id="list_images" class="noselect">
                    <p><?php echo _("Loading images ..."); ?></p>
                </div>
            </div>
        </div>
    </div>
</div>

<div id="modal_delete_product" class="modal" tabindex="-1" role="dialog" data-backdrop="static">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><?php echo _("Delete Product"); ?></h5>
            </div>
            <div class="modal-body">
                <p><?php echo _("Are you sure you want to delete the product?"); ?>
                </p>
            </div>
            <div class="modal-footer">
                <button <?php echo ($demo) ? 'disabled':''; ?> id="btn_delete_product" onclick="" type="button" class="btn btn-danger"><i class="fas fa-trash"></i> <?php echo _("Yes, Delete"); ?></button>
                <button type="button" class="btn btn-secondary" data-dismiss="modal"><i class="fas fa-times"></i> <?php echo _("Close"); ?></button>
            </div>
        </div>
    </div>
</div>

<script>
    (function($) {
        "use strict"; // Start of use strict
        window.product_need_save = false;
        window.id_product = <?php echo $id_product; ?>;
        window.description_editor = null;
        var DirectionAttribute = Quill.import('attributors/attribute/direction');
        Quill.register(DirectionAttribute,true);
        var AlignClass = Quill.import('attributors/class/align');
        Quill.register(AlignClass,true);
        var BackgroundClass = Quill.import('attributors/class/background');
        Quill.register(BackgroundClass,true);
        var ColorClass = Quill.import('attributors/class/color');
        Quill.register(ColorClass,true);
        var DirectionClass = Quill.import('attributors/class/direction');
        Quill.register(DirectionClass,true);
        var FontClass = Quill.import('attributors/class/font');
        Quill.register(FontClass,true);
        var SizeClass = Quill.import('attributors/class/size');
        Quill.register(SizeClass,true);
        var AlignStyle = Quill.import('attributors/style/align');
        Quill.register(AlignStyle,true);
        var BackgroundStyle = Quill.import('attributors/style/background');
        Quill.register(BackgroundStyle,true);
        var ColorStyle = Quill.import('attributors/style/color');
        Quill.register(ColorStyle,true);
        var DirectionStyle = Quill.import('attributors/style/direction');
        Quill.register(DirectionStyle,true);
        var FontStyle = Quill.import('attributors/style/font');
        Quill.register(FontStyle,true);
        var SizeStyle = Quill.import('attributors/style/size');
        Quill.register(SizeStyle,true);
        var LinkFormats = Quill.import("formats/link");
        Quill.register(LinkFormats,true);
        window.product_images = [];
        Dropzone.autoDiscover = false;
        $(document).ready(function () {
            $('.help_t').tooltip();
            var toolbarOptions = [
                ['bold', 'italic', 'underline', 'strike'],
                [{ 'list': 'ordered'}, { 'list': 'bullet' }],
                [{ 'color': [] }, { 'background': [] }],
                [{ 'align': [] }],
                ['clean']
            ];
            window.description_editor = new Quill('#description', {
                modules: {
                    toolbar: toolbarOptions
                },
                theme: 'snow'
            });
            get_product_images(id_product);
            var product_dropzone = new Dropzone("#product-dropzone", {
                url: "ajax/upload_product_image.php",
                parallelUploads: 1,
                maxFilesize: 20,
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
                acceptedFiles: 'image/*'
            });
            product_dropzone.on("addedfile", function(file) {
                $('#list_images').addClass('disabled');
            });
            product_dropzone.on("success", function(file,rsp) {
                add_image_to_product(id_product,rsp);
            });
            product_dropzone.on("queuecomplete", function() {
                $('#list_images').removeClass('disabled');
                product_dropzone.removeAllFiles();
            });
        });
        $("input[type='text']").change(function(){
            window.product_need_save = true;
        });
        $("input[type='checkbox']").change(function(){
            window.product_need_save = true;
        });
        $("select").change(function(){
            window.product_need_save = true;
        });
        $(window).on('beforeunload', function(){
            if(window.product_need_save) {
                var c=confirm();
                if(c) return true; else return false;
            }
        });
    })(jQuery); // End of use strict
</script>