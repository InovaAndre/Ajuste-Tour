<?php
session_start();
if(isset($_SESSION['id_virtualtour_sel'])) {
    $id_vt_rm = $_SESSION['id_virtualtour_sel'];
    $virtual_tour = get_virtual_tour($id_vt_rm,$_SESSION['id_user']);
    $show_in_ui = $virtual_tour['show_list_alt'];
} else {
    $id_vt_rm = '';
}
?>

<div class="d-sm-flex align-items-center justify-content-between mb-3">
    <h1 class="h3 mb-0 text-gray-800"><i class="fas fa-fw fa-list-alt text-gray-700"></i> <?php echo _("ROOMS LIST"); ?> <i style="font-size:12px;vertical-align:middle;color:<?php echo ($show_in_ui>0)?'green':'orange'; ?>" <?php echo ($show_in_ui==0)?'title="'._("Not visible in the tour, enable it in the Editor UI").'"':''; ?> class="<?php echo ($show_in_ui==0)?'help_t':''; ?> show_in_ui fas fa-circle"></i></h1>
</div>
<div class="row">
    <div class="col-md-12">
        <div id="rooms_list">
            <div class="card mb-4 py-3">
                <div class="card-body" style="padding-top: 0;padding-bottom: 0;">
                    <div style="display: none;max-width: 600px;" class="row add_cat_div">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="add_cat"><?php echo _("Add Category"); ?></label>
                                <div class="input-group">
                                    <input type="text" class="form-control bg-white" id="add_cat" value="">
                                    <div class="input-group-append">
                                        <button onclick="add_menu_list_cat()" class="btn btn-success btn-xs">
                                            <i class="fa fa-plus"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <?php echo _("Drag and drop rooms into categories or up/down to change its order"); ?>
                        </div>
                    </div>
                    <div class="row list_div">
                        <div class="col-md-8 text-center text-sm-center text-md-left text-lg-left">
                            <?php echo _("LOADING MENU LIST ..."); ?>
                        </div>
                        <div class="col-md-4 text-center text-sm-center text-md-right text-lg-right">
                            <a href="#" class="btn btn-primary btn-circle">
                                <i class="fas fa-spin fa-spinner"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    (function($) {
        "use strict"; // Start of use strict
        window.id_vt_rm = '<?php echo $id_vt_rm; ?>';

        jQuery.fn.scrollParent = function() {
            var overflowRegex = /(auto|scroll)/,
                position = this.css( "position" ),
                excludeStaticParent = position === "absolute",
                scrollParent = this.parents().filter( function() {
                    var parent = $( this );
                    if ( excludeStaticParent && parent.css( "position" ) === "static" ) {
                        return false;
                    }
                    var overflowState = parent.css(["overflow", "overflowX", "overflowY"]);
                    return (overflowRegex).test( overflowState.overflow + overflowState.overflowX + overflowState.overflowY );
                }).eq( 0 );

            return position === "fixed" || !scrollParent.length ? $( this[ 0 ].ownerDocument || document ) : scrollParent;
        };

        $(document).ready(function () {
            $('.help_t').tooltip();
            if(window.id_vt_rm!='') {
                get_rooms_menu_list(window.id_vt_rm);
            }
        });
    })(jQuery); // End of use strict
</script>