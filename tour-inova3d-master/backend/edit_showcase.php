<?php
session_start();
require_once("functions.php");
$id_user = $_SESSION['id_user'];
$id_showcase = $_GET['id'];
$showcase = get_showcase($id_showcase,$id_user);
if (is_ssl()) { $protocol = 'https'; } else { $protocol = 'http'; }
$link = $protocol ."://". $_SERVER['SERVER_NAME'] . str_replace("backend/index.php","showcase/index.php?code=",$_SERVER['SCRIPT_NAME']);
$link_f = $protocol ."://". $_SERVER['SERVER_NAME'] . str_replace("backend/index.php","showcase/",$_SERVER['SCRIPT_NAME']);
?>

<?php if(!$showcase): ?>
    <div class="text-center">
        <div class="error mx-auto" data-text="401">401</div>
        <p class="lead text-gray-800 mb-5"><?php echo _("Permission denied"); ?></p>
        <p class="text-gray-500 mb-0"><?php echo _("It looks like that you do not have permission to access this page"); ?></p>
        <a href="index.php?p=dashboard">← <?php echo _("Back to Dashboard"); ?></a>
    </div>
<?php die(); endif; ?>

<div class="d-sm-flex align-items-center justify-content-between mb-3">
    <h1 class="h3 mb-0 text-gray-800"><i class="fas fa-fw fa-object-group text-gray-700"></i> <?php echo _("EDIT SHOWCASE"); ?></h1>
    <div>
        <button <?php echo ($demo) ? 'disabled':''; ?> onclick="modal_delete_showcase(<?php echo $id_showcase; ?>);" class="btn btn-sm btn-danger mb-2 ml-3 float-right"><?php echo _("DELETE"); ?></button>
        <a id="save_btn" href="#" onclick="save_showcase(<?php echo $id_showcase; ?>);return false;" class="btn btn-sm btn-success btn-icon-split mb-2 <?php echo ($demo) ? 'disabled':''; ?>">
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
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="name"><?php echo _("Name"); ?></label>
                            <input type="text" class="form-control" id="name" value="<?php echo $showcase['name']; ?>" />
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="friendly_url"><?php echo _("Friendly URL"); ?></label>
                            <input oninput="change_friendly_url();" type="text" class="form-control" id="friendly_url" value="<?php echo $showcase['friendly_url']; ?>" />
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="bg_color"><?php echo _("Background Color"); ?></label>
                            <input type="text" class="form-control" id="bg_color" value="<?php echo $showcase['bg_color']; ?>" />
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="header_html"><?php echo _("Custom HTML Header"); ?></label><br>
                            <div id="header_html"></div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="footer_html"><?php echo _("Custom HTML Footer"); ?></label><br>
                            <div id="footer_html"></div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <label><?php echo _("Logo"); ?></label>
                        <div style="background-color:#4e73df;display: none" id="div_image_logo" class="col-md-12">
                            <img style="width: 100%" src="../viewer/content/<?php echo $showcase['logo']; ?>" />
                        </div>
                        <div style="display: none" id="div_delete_logo" class="col-md-12 mt-4">
                            <button <?php echo ($demo) ? 'disabled':''; ?> onclick="delete_s_logo();" class="btn btn-block btn-danger"><?php echo _("DELETE IMAGE"); ?></button>
                        </div>
                        <div style="display: none" id="div_upload_logo">
                            <form id="frm" action="ajax/upload_s_logo_image.php" method="POST" enctype="multipart/form-data">
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <input type="file" class="form-control" id="txtFile" name="txtFile" />
                                        </div>
                                    </div>
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <input <?php echo ($demo) ? 'disabled':''; ?> type="submit" class="btn btn-block btn-success" id="btnUpload" value="<?php echo _("Upload Logo Image"); ?>" />
                                        </div>
                                    </div>
                                    <div class="col-md-12">
                                        <div class="preview text-center">
                                            <div class="progress mb-3 mb-sm-3 mb-lg-0 mb-xl-0" style="height: 2.35rem;display: none">
                                                <div class="progress-bar" id="progressBar" role="progressbar" aria-valuemin="0" aria-valuemax="100" style="width:0%;">
                                                    0%
                                                </div>
                                            </div>
                                            <div style="display: none;padding: .38rem;" class="alert alert-danger" id="error"></div>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label><?php echo _("Banner image"); ?></label>
                        <div style="display: none" id="div_image_banner" class="col-md-12">
                            <img style="width: 100%" src="../viewer/content/<?php echo $showcase['banner']; ?>" />
                        </div>
                        <div style="display: none" id="div_delete_banner" class="col-md-12 mt-4">
                            <button <?php echo ($demo) ? 'disabled':''; ?> onclick="delete_s_banner();" class="btn btn-block btn-danger"><?php echo _("DELETE IMAGE"); ?></button>
                        </div>
                        <div style="display: none" id="div_upload_banner">
                            <form id="frm_b" action="ajax/upload_s_banner_image.php" method="POST" enctype="multipart/form-data">
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <input type="file" class="form-control" id="txtFile_b" name="txtFile_b" />
                                        </div>
                                    </div>
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <input <?php echo ($demo) ? 'disabled':''; ?> type="submit" class="btn btn-block btn-success" id="btnUpload_b" value="<?php echo _("Upload Banner Image"); ?>" />
                                        </div>
                                    </div>
                                    <div class="col-md-12">
                                        <div class="preview text-center">
                                            <div class="progress mb-3 mb-sm-3 mb-lg-0 mb-xl-0" style="height: 2.35rem;display: none">
                                                <div class="progress-bar" id="progressBar_b" role="progressbar" aria-valuemin="0" aria-valuemax="100" style="width:0%;">
                                                    0%
                                                </div>
                                            </div>
                                            <div style="display: none;padding: .38rem;" class="alert alert-danger" id="error_b"></div>
                                        </div>
                                    </div>
                                </div>
                            </form>
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
                <h6 class="m-0 font-weight-bold text-primary float-left d-inline-block"><i class="fas fa-route"></i> <?php echo _("Assigned Virtual Tours"); ?></h6> <span style="font-size:12px;vertical-align:text-top;" class="ml-3">* V=<?php echo _("Viewer"); ?>, L=<?php echo _("Landing"); ?></span>
                <span class="float-right d-inline-block"><input class="form-control form-control-sm" id="search_vt" type="search" placeholder="<?php echo _("Search"); ?>" /></span>
            </div>
            <div class="card-body">
                <div class="row list_s_vt">
                    <?php echo get_showcase_virtualtours($id_user,$id_showcase); ?>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-12">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-share-alt"></i> <?php echo _("Share & Embed"); ?></h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                            <label for="link"><i class="fas fa-link"></i> <?php echo _("Showcase Link"); ?></label>
                            <div class="input-group">
                                <input readonly type="text" class="form-control bg-white" id="link" value="<?php echo $link . $showcase['code']; ?>" />
                                <div class="input-group-append">
                                    <button class="btn btn-primary btn-xs" data-clipboard-target="#link">
                                        <i class="far fa-clipboard"></i>
                                    </button>
                                    <button onclick="open_qr_code_modal('<?php echo $link . $showcase['code']; ?>');" class="btn btn-secondary btn-xs">
                                        <i class="fas fa-qrcode"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                            <label for="link_f"><i class="fas fa-link"></i> <?php echo _("Showcase Friendly Url Link"); ?></label>
                            <div class="input-group <?php echo ($showcase['friendly_url']=='') ? 'disabled' : ''; ?>">
                                <input readonly type="text" class="form-control bg-white" id="link_f" value="<?php echo $link_f . $showcase['friendly_url']; ?>" />
                                <div class="input-group-append">
                                    <button class="btn btn-primary btn-xs" data-clipboard-target="#link_f">
                                        <i class="far fa-clipboard"></i>
                                    </button>
                                    <button onclick="open_qr_code_modal('<?php echo $link_f . $showcase['friendly_url']; ?>');" class="btn btn-secondary btn-xs">
                                        <i class="fas fa-qrcode"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                            <label for="code"><i class="fas fa-code"></i> <?php echo _("Showcase Embed Code"); ?></label>
                            <div class="input-group">
                                <textarea id="code" class="form-control" rows="2"><iframe allowfullscreen allow="gyroscope; accelerometer; xr; microphone *" width="100%" height="600px" frameborder="0" scrolling="no" marginheight="0" marginwidth="0" src="<?php echo $link . $showcase['code']; ?>"></iframe></textarea>
                                <div class="input-group-append">
                                    <button class="btn btn-primary btn-xs" data-clipboard-target="#code">
                                        <i class="far fa-clipboard"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-12 mb-4">
        <div class="card shadow mb-12">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary"><i class="fab fa-css3-alt"></i> <?php echo _("Custom Showcase CSS"); ?></h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-12">
                        <div style="position: relative;width: 100%;height: 400px;" class="editors_css" id="custom_s"><?php echo get_editor_css_content_s('custom_'.$showcase['code']); ?></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div id="modal_delete_showcase" class="modal" tabindex="-1" role="dialog" data-backdrop="static">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><?php echo _("Delete Showcase"); ?></h5>
            </div>
            <div class="modal-body">
                <p><?php echo _("Are you sure you want to delete the showcase?"); ?>
                </p>
            </div>
            <div class="modal-footer">
                <button <?php echo ($demo) ? 'disabled':''; ?> id="btn_delete_showcase" onclick="" type="button" class="btn btn-danger"><i class="fas fa-trash"></i> <?php echo _("Yes, Delete"); ?></button>
                <button type="button" class="btn btn-secondary" data-dismiss="modal"><i class="fas fa-times"></i> <?php echo _("Close"); ?></button>
            </div>
        </div>
    </div>
</div>

<div id="modal_qrcode" class="modal" tabindex="-1" role="dialog" data-backdrop="static">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><?php echo _("QR Code"); ?></h5>
            </div>
            <div class="modal-body text-center">
                <i class="fas fa-spin fa-spinner"></i>
                <img style="width: 100%;" src="" />
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal"><i class="fas fa-times"></i> <?php echo _("Close"); ?></button>
            </div>
        </div>
    </div>
</div>

<script>
    (function($) {
        "use strict"; // Start of use strict
        window.showcase_need_save = false;
        window.id_showcase = <?php echo $id_showcase; ?>;
        window.bg_color_spectrum = null;
        window.link_f = '<?php echo $link_f; ?>';
        window.s_logo_image = '<?php echo $showcase['logo']; ?>';
        window.s_banner_image = '<?php echo $showcase['banner']; ?>';
        window.editor_css = null;
        window.editor_html_h = null;
        window.editor_html_f = null;
        $(document).ready(function () {
            window.editor_css = ace.edit('custom_s');
            window.editor_css.session.setMode("ace/mode/css");
            window.editor_css.setOption('enableLiveAutocompletion',true);
            window.editor_html_h = ace.edit('header_html');
            window.editor_html_h.session.setMode("ace/mode/html");
            window.editor_html_h.session.setUseWrapMode(true);
            window.editor_html_h.setOption('enableLiveAutocompletion',true);
            window.editor_html_h.setValue("<?php echo $showcase['header_html']; ?>",-1);
            window.editor_html_f = ace.edit('footer_html');
            window.editor_html_f.session.setMode("ace/mode/html");
            window.editor_html_f.session.setUseWrapMode(true);
            window.editor_html_f.setOption('enableLiveAutocompletion',true);
            window.editor_html_f.setValue("<?php echo $showcase['footer_html']; ?>",-1);
            window.bg_color_spectrum = $('#bg_color').spectrum({
                type: "text",
                preferredFormat: "hex",
                showAlpha: false,
                showButtons: false,
                allowEmpty: false
            });
            new ClipboardJS('.btn');
            if(window.s_logo_image=='') {
                $('#div_delete_logo').hide();
                $('#div_image_logo').hide();
                $('#div_upload_logo').show();
            } else {
                $('#div_delete_logo').show();
                $('#div_image_logo').show();
                $('#div_upload_logo').hide();
            }
            if(window.s_banner_image=='') {
                $('#div_delete_banner').hide();
                $('#div_image_banner').hide();
                $('#div_upload_banner').show();
            } else {
                $('#div_delete_banner').show();
                $('#div_image_banner').show();
                $('#div_upload_banner').hide();
            }
        });
        $("input[type='text']").change(function(){
            window.showcase_need_save = true;
        });
        $("input[type='checkbox']").change(function(){
            window.showcase_need_save = true;
        });
        $("select").change(function(){
            window.showcase_need_save = true;
        });
        $(window).on('beforeunload', function(){
            if(window.showcase_need_save) {
                var c=confirm();
                if(c) return true; else return false;
            }
        });
    })(jQuery); // End of use strict

    $("#search_vt").on("keyup input", function() {
        var value = $(this).val().toLowerCase();
        $(".list_s_vt div").filter(function() {
            $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1);
        });
    });

    function change_friendly_url() {
        var friendly_url = $('#friendly_url').val();
        if(friendly_url=='') {
            $('#link_f').parent().addClass('disabled');
        } else {
            $('#link_f').parent().removeClass('disabled');
        }
        var url = window.link_f+friendly_url;
        $('#link_f').val(url);
    }

    $('body').on('submit','#frm',function(e){
        e.preventDefault();
        $('#error').hide();
        var url = $(this).attr('action');
        var frm = $(this);
        var data = new FormData();
        if(frm.find('#txtFile[type="file"]').length === 1 ){
            data.append('file', frm.find( '#txtFile' )[0].files[0]);
        }
        var ajax  = new XMLHttpRequest();
        ajax.upload.addEventListener('progress',function(evt){
            var percentage = (evt.loaded/evt.total)*100;
            upadte_progressbar(Math.round(percentage));
        },false);
        ajax.addEventListener('load',function(evt){
            if(evt.target.responseText.toLowerCase().indexOf('error')>=0){
                show_error(evt.target.responseText);
            } else {
                if(evt.target.responseText!='') {
                    window.showcase_need_save = true;
                    window.s_logo_image = evt.target.responseText;
                    $('#div_image_logo img').attr('src','../viewer/content/'+window.s_logo_image);
                    $('#div_delete_logo').show();
                    $('#div_image_logo').show();
                    $('#div_upload_logo').hide();
                }
            }
            upadte_progressbar(0);
            frm[0].reset();
        },false);
        ajax.addEventListener('error',function(evt){
            show_error('upload failed');
            upadte_progressbar(0);
        },false);
        ajax.addEventListener('abort',function(evt){
            show_error('upload aborted');
            upadte_progressbar(0);
        },false);
        ajax.open('POST',url);
        ajax.send(data);
        return false;
    });

    function upadte_progressbar(value){
        $('#progressBar').css('width',value+'%').html(value+'%');
        if(value==0){
            $('.progress').hide();
        }else{
            $('.progress').show();
        }
    }

    function show_error(error){
        $('.progress').hide();
        $('#error').show();
        $('#error').html(error);
    }

    $('body').on('submit','#frm_b',function(e){
        e.preventDefault();
        $('#error_b').hide();
        var url = $(this).attr('action');
        var frm = $(this);
        var data = new FormData();
        if(frm.find('#txtFile_b[type="file"]').length === 1 ){
            data.append('file', frm.find( '#txtFile_b' )[0].files[0]);
        }
        var ajax  = new XMLHttpRequest();
        ajax.upload.addEventListener('progress',function(evt){
            var percentage = (evt.loaded/evt.total)*100;
            upadte_progressbar_b(Math.round(percentage));
        },false);
        ajax.addEventListener('load',function(evt){
            if(evt.target.responseText.toLowerCase().indexOf('error')>=0){
                show_error_b(evt.target.responseText);
            } else {
                if(evt.target.responseText!='') {
                    window.showcase_need_save = true;
                    window.s_banner_image = evt.target.responseText;
                    $('#div_image_banner img').attr('src','../viewer/content/'+window.s_banner_image);
                    $('#div_delete_banner').show();
                    $('#div_image_banner').show();
                    $('#div_upload_banner').hide();
                }
            }
            upadte_progressbar_b(0);
            frm[0].reset();
        },false);
        ajax.addEventListener('error',function(evt){
            show_error_b('upload failed');
            upadte_progressbar_b(0);
        },false);
        ajax.addEventListener('abort',function(evt){
            show_error_b('upload aborted');
            upadte_progressbar_b(0);
        },false);
        ajax.open('POST',url);
        ajax.send(data);
        return false;
    });

    function upadte_progressbar_b(value){
        $('#progressBar_b').css('width',value+'%').html(value+'%');
        if(value==0){
            $('.progress').hide();
        }else{
            $('.progress').show();
        }
    }

    function show_error_b(error){
        $('.progress').hide();
        $('#error_b').show();
        $('#error_b').html(error);
    }
</script>