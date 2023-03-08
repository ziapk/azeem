<?php 

function mainHeader($params = null) {
    ob_start();
    global $pages;

    if(!empty($params['hideheader'])) {
        $params['bodyClasses'][] = 'no-padding';
    }
    ?>

    <!DOCTYPE html>
    <!--[if lt IE 7]>      <html class="no-js lt-ie9 lt-ie8 lt-ie7"> <![endif]-->
    <!--[if IE 7]>         <html class="no-js lt-ie9 lt-ie8"> <![endif]-->
    <!--[if IE 8]>         <html class="no-js lt-ie9"> <![endif]-->
    <!--[if gt IE 8]><!--> <html class="no-js"> <!--<![endif]-->
        <head>
            <meta charset="utf-8">
            <meta http-equiv="X-UA-Compatible" content="IE=edge">
            <title><?php echo $params['title'];?> | <?php echo $pages[$params['page']];?> | POINT OF SALE</title>
            <meta name="description" content="Recline Solution POS, POS, Point of Sale, Smart Commerce">
            <meta name="viewport" content="width=device-width, initial-scale=1">
            <link rel="stylesheet" href="<?php echo SITE_URL; ?>assets/css/bootstrap.min.css">
            <link rel="stylesheet" href="<?php echo SITE_URL; ?>assets/vendors/daterangepicker/daterangepicker.css">
            <link rel="stylesheet" href="<?php echo SITE_URL; ?>assets/vendors/angular-daterangepicker/daterangepicker.css">
            <link rel="stylesheet" href="<?php echo SITE_URL; ?>assets/vendors/angularjs-toaster/toaster.min.css">
            <link rel="stylesheet" href="<?php echo SITE_URL; ?>assets/css/style.css">
            <link rel="stylesheet" href="<?php echo SITE_URL; ?>assets/css/tree.css">
            <link rel="preconnect" href="https://fonts.gstatic.com">
            <link rel="stylesheet" href="<?php echo SITE_URL; ?>assets/vendor/font-awesome/font-awesome-4.7.0/css/font-awesome.min.css">
            <link href="https://fonts.googleapis.com/css2?family=Raleway:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
            <script src="<?php echo SITE_URL; ?>assets/vendor/jquery/jquery.min.js"></script>
            <script src="<?php echo SITE_URL; ?>assets/js/jquery-3.4.1.slim.min.js"></script>
            <script src="<?php echo SITE_URL;?>assets/vendors/daterangepicker/moment.min.js"></script>
            <script type="text/javascript" src="<?php echo SITE_URL?>assets/js/angular.min.js"></script>
            <script type="text/javascript" src="<?php echo SITE_URL;?>assets/js/ui-bootstrap-tpls-2.5.0.min.js"></script>
        </head>
        <script>
            var app = angular.module('mainApp', ['ui.bootstrap', 'daterangepicker', 'toaster']);
        </script>
        <body ng-app="mainApp" class="<?php if(!empty($params['bodyClasses'])) { echo implode(' ', $params['bodyClasses']); };?>">
            <!--[if lt IE 7]>
                <p class="browsehappy">You are using an <strong>outdated</strong> browser. Please <a href="#">upgrade your browser</a> to improve your experience.</p>
            <![endif]-->
            <toaster-container toaster-options="{'close-button':false, 'time-out':{ 'toast-warning': 2000, 'toast-success': 2000, 'toast-error': 0 } }"></toaster-container>
            <!--[if lt IE 7]>
                <p class="browsehappy">You are using an <strong>outdated</strong> browser. Please <a href="#">upgrade your browser</a> to improve your experience.</p>
            <![endif]-->
<?php 
    if(empty($params['hideheader'])) {
        if(!empty($_SESSION['user_credentials']) && $_SESSION['user_credentials']['role'] == 'owner') {
            include_once dirname(__FILE__).'/owner.php';
        }
        if(!empty($_SESSION['user_credentials']) && $_SESSION['user_credentials']['role'] == 'manager') {
            include_once dirname(__FILE__).'/manager.php';
        }
        if(!empty($_SESSION['user_credentials']) && $_SESSION['user_credentials']['role'] == 'shopkeeper') {
            include_once dirname(__FILE__).'/shopkeeper.php';
        }
    }
    ob_get_flush();
}
