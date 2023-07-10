<?php
function mainFooter($params = null)
{
    ob_start();
    global $shop;
?>
    <div class="footer" ng-controller="headerController">

        <div class="container">
            <div class="pull-left sale-date" style="padding: 10px 0;" ng-class="{'blink': getClass()}">Sale Date: <?php echo $shop['sale_date']; ?></div>
            <div style="border-top: 1px solid; padding: 10px 0; text-align: right;">
                Powered by: <strong>Zia ur Rehman <code>92-324-5120412</code></strong>
            </div>
        </div>
    </div>
    <script src="<?php echo SITE_URL; ?>assets/js/bootstrap.min.js"></script>
    <script src="<?php echo SITE_URL; ?>assets/vendors/daterangepicker/daterangepicker.js"></script>
    <script src="<?php echo SITE_URL; ?>assets/vendors/angular-daterangepicker/daterangepicker.min.js"></script>
    <script src="<?php echo SITE_URL; ?>assets/vendors/angular-daterangepicker/angular-daterangepicker.min.js"></script>
    <script src="<?php echo SITE_URL; ?>assets/vendors/angularjs-toaster/toaster.min.js"></script>
<?php
    $JsFiles = array(
        "assets/vendor/angular-sanitize.min.js",
        "assets/vendor/select2/select.min.js",
        "assets/vendor/validate/jquery.validate.min.js",
        "assets/vendor/bootstrap-datetimepicker/js/moment-with-locales.min.js",
        "assets/vendor/bootstrap-datetimepicker/js/bootstrap-datetimepicker.js",
        "assets/js/jquery.dataTree.min.js",
        "assets/js/script.js"
    );


    if ($params['page'] == 'coa') {
        $js = drawJs($JsFiles);
        echo $js;
    }
    ob_get_flush();
}
