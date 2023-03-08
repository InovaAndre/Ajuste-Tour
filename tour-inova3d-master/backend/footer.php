<?php require_once('functions.php');?>
<footer class="sticky-footer bg-white py-2 noselect">
    <div class="container my-auto">
        <div class="copyright text-center my-auto">
            <span>Copyright &copy; <?php echo $settings['name']; ?> <?php echo date('Y'); ?> - Version <?php echo $version; ?></span>
            <?php if(get_user_role($_SESSION['id_user'])=='administrator') : ?>
                <?php if(version_compare($version,$latest_version)==-1) : ?>
                <span><i style="cursor: pointer;" class="fas fa-exclamation-circle"></i></span>
                <a target="_blank" href="index.php?p=updater">New version <?php echo $latest_version; ?> available!</a>
                <?php endif; ?>
            <?php endif; ?>
            <?php
            if(!empty($settings['footer_link_1']) || !empty($settings['footer_link_2']) || !empty($settings['footer_link_3'])) {
                echo "&nbsp;&nbsp;&nbsp;";
            }
            if(!empty($settings['footer_link_1'])) {
                if(strpos($settings['footer_value_1'], 'http') === 0) {
                    echo "<span class='footer_link'><a target='_blank' href='".$settings['footer_value_1']."'>".$settings['footer_link_1']."</a></span> ";
                } else if(!empty($settings['footer_value_1']) && $settings['footer_value_1']!='<p></p>') {
                    echo "<span class='footer_link'><a href='#' data-toggle='modal' data-target='#modal_footer_value_1'>".$settings['footer_link_1']."</a></span> ";
                } else {
                    echo "<span>".$settings['footer_link_1']."</span> ";
                }
            }
            if(!empty($settings['footer_link_2'])) {
                if(strpos($settings['footer_value_2'], 'http') === 0) {
                    echo "| <span class='footer_link'><a target='_blank' href='".$settings['footer_value_2']."'>".$settings['footer_link_2']."</a></span> ";
                } else if(!empty($settings['footer_value_2']) && $settings['footer_value_2']!='<p></p>') {
                    echo "| <span class='footer_link'><a href='#' data-toggle='modal' data-target='#modal_footer_value_2'>".$settings['footer_link_2']."</a></span> ";
                } else {
                    echo "| <span>".$settings['footer_link_2']."</span> ";
                }
            }
            if(!empty($settings['footer_link_3'])) {
                if(strpos($settings['footer_value_3'], 'http') === 0) {
                    echo "| <span class='footer_link'><a target='_blank' href='".$settings['footer_value_3']."'>".$settings['footer_link_3']."</a></span>";
                } else if(!empty($settings['footer_value_3']) && $settings['footer_value_3']!='<p></p>') {
                    echo "| <span class='footer_link'><a href='#' data-toggle='modal' data-target='#modal_footer_value_3'>".$settings['footer_link_3']."</a></span>";
                } else {
                    echo "| <span>".$settings['footer_link_3']."</span>";
                }
            }
            ?>
        </div>
    </div>
</footer>

<?php if(!empty($settings['footer_value_1']) && $settings['footer_value_1']!='<p></p>') : ?>
<div id="modal_footer_value_1" class="modal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-body">
                <?php echo $settings['footer_value_1']; ?>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>
<?php if(!empty($settings['footer_value_2']) && $settings['footer_value_2']!='<p></p>') : ?>
    <div id="modal_footer_value_2" class="modal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-body">
                    <?php echo $settings['footer_value_2']; ?>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>
<?php if(!empty($settings['footer_value_3']) && $settings['footer_value_3']!='<p></p>') : ?>
    <div id="modal_footer_value_3" class="modal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-body">
                    <?php echo $settings['footer_value_3']; ?>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>
