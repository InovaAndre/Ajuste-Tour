<?php
error_reporting(E_ALL & ~E_WARNING & ~E_NOTICE & ~E_DEPRECATED);
session_start();
require_once('../../functions.php');
$id_virtualtour = $_GET['id_vt'];
$virtual_tour = get_virtual_tour($id_virtualtour,$_SESSION['id_user']);
if($virtual_tour['html_landing']=='') {
    $virtual_tour['html_landing'] = "<div class=\"row\">
            <div class=\"col-sm-12 ui-resizable\" data-type=\"container-content\">
                <div data-type=\"component-vt\">
                    <div style=\"width: 100%;height: 70vh;border: 1px solid black\">
                        <img style=\"width: 100%;\" src=\"snippets/preview/vt_preview.jpg\">
                    </div>
                </div>
            </div>
        </div>";
}
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1" />
        <link rel="stylesheet" type="text/css" href="plugins/bootstrap-3.4.1/css/bootstrap.min.css" data-type="keditor-style" />
        <link rel="stylesheet" type="text/css" href="plugins/font-awesome-4.7.0/css/font-awesome.min.css" data-type="keditor-style" />
        <link rel="stylesheet" type="text/css" href="css/keditor.css" data-type="keditor-style" />
        <link rel="stylesheet" type="text/css" href="css/keditor-components.css" data-type="keditor-style" />
        <link rel="stylesheet" type="text/css" href="css/editor.css?v=3" />
    </head>
    <body style="overflow: hidden">
        <div id="landing_loading" class="row">
            <div class="col-md-12">
                <i class="fa fa-spin fa-circle-o-notch" aria-hidden="true"></i> <?php echo _("Loading landing page content ..."); ?>
            </div>
        </div>
        <div id="landing_saving" class="row" style="display: none">
            <div class="col-md-12">
                <i class="fa fa-spin fa-circle-o-notch" aria-hidden="true"></i> <?php echo _("Saving landing page content ... Do not close this window!"); ?>
            </div>
        </div>
        <div id="landing_editor" style="display: none" data-keditor="html">
            <div id="content-area">
                <?php echo $virtual_tour['html_landing']; ?>
            </div>
        </div>
        <script type="text/javascript" src="plugins/jquery-1.11.3/jquery-1.11.3.min.js"></script>
        <script type="text/javascript" src="plugins/bootstrap-3.4.1/js/bootstrap.min.js"></script>
        <script type="text/javascript" src="plugins/jquery-ui-1.12.1.custom/jquery-ui.min.js"></script>
        <script type="text/javascript" src="plugins/ckeditor-4.11.4/ckeditor.js"></script>
        <script type="text/javascript" src="plugins/formBuilder-2.5.3/form-builder.min.js"></script>
        <script type="text/javascript" src="plugins/formBuilder-2.5.3/form-render.min.js"></script>
        <script type="text/javascript" src="js/keditor.js"></script>
        <script type="text/javascript" src="js/keditor-components.js"></script>
        <script type="text/javascript" src="../../js/function.js?v=<?php echo time(); ?>"></script>
        <script type="text/javascript" data-keditor="script">
            window.wizard_step = -1;
            $(function () {
                var id_virtualtour = '<?php echo $id_virtualtour; ?>';
                $('#content-area').keditor({
                    onSave: function () {
                        $('#landing_editor').fadeOut(function () {
                            $('#landing_saving').fadeIn(function () {
                                var html = $('#content-area').keditor('getContent');
                                save_landing(id_virtualtour,html);
                            },0);
                        },0);
                    },
                    onReady: function() {
                        $('#landing_loading').hide();
                        $('#landing_editor').show();
                    },
                    containerSettingEnabled: true,
                    containerSettingInitFunction: function (form, keditor) {
                        form.append(
                            '<div class="form-horizontal">' +
                            '   <div class="form-group">' +
                            '       <div class="col-sm-12">' +
                            '           <label>Background color</label>' +
                            '           <input type="text" class="form-control txt-bg-color" />' +
                            '       </div>' +
                            '   </div>' +
                            '</div>'
                        );
                        form.find('.txt-bg-color').on('change', function () {
                            var container = keditor.getSettingContainer();
                            var row = container.find('.row');
                            if (container.hasClass('keditor-sub-container')) {
                                // Do nothing
                            } else {
                                row = row.filter(function () {
                                    return $(this).parents('.keditor-container').length === 1;
                                });
                            }
                            row.css('background-color', this.value);
                        });
                    },
                    containerSettingShowFunction: function (form, container, keditor) {
                        var row = container.find('.row');
                        var backgroundColor = row.prop('style').backgroundColor || '';
                        form.find('.txt-bg-color').val(backgroundColor);
                    },
                    containerSettingHideFunction: function (form, keditor) {
                        form.find('.txt-bg-color').val('');
                    }
                });
            });
        </script>
    </body>
</html>
