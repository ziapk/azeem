<?php

function mainFooter($params = null) {
    ob_start();
    ?>
        <div class="footer">

            <div class="container">
                <div style="border-top: 1px solid; padding: 10px 0; text-align: right;">
                Power by: <strong>Zia ur Rehman <code>92-324-5120412</code></strong>
                </div>
            </div>
        </div>
        <script src="<?php echo SITE_URL;?>assets/js/bootstrap.min.js"></script>
        <script src="<?php echo SITE_URL;?>assets/vendors/daterangepicker/moment.min.js"></script>
        <script src="<?php echo SITE_URL;?>assets/vendors/daterangepicker/daterangepicker.js"></script>
        <script src="<?php echo SITE_URL;?>assets/vendors/angular-daterangepicker/daterangepicker.min.js"></script>
        <script src="<?php echo SITE_URL;?>assets/vendors/angular-daterangepicker/angular-daterangepicker.min.js"></script>
        <script src="<?php echo SITE_URL;?>assets/vendors/angularjs-toaster/toaster.min.js"></script>
        </body>
    </html>
    <?php
    ob_get_flush();
}