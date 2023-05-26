<?php
include_once dirname(__FILE__) . '/include/settings.php';


if (!empty($_SESSION['user_credentials']) && $_SESSION['user_credentials']['role'] == 'owner' && $_SESSION['user_credentials']['id'] != 12) {
    echo mainHeader(['page' => 'dashboard']);
    include_once dirname(__FILE__) . '/pages/dashboard/owner.php';
    echo mainFooter();
}
if (!empty($_SESSION['user_credentials']) && $_SESSION['user_credentials']['role'] == 'owner' && $_SESSION['user_credentials']['id'] == 12) {
    echo mainHeader(['page' => 'dashboard']);
    include_once dirname(__FILE__) . '/pages/dashboard/owner.php';
    echo mainFooter();
}
if (!empty($_SESSION['user_credentials']) && $_SESSION['user_credentials']['role'] == 'manager') {
    include_once dirname(__FILE__) . '/pages/dashboard/manager.php';
}
if (!empty($_SESSION['user_credentials']) && $_SESSION['user_credentials']['role'] == 'shopkeeper') {
    include_once dirname(__FILE__) . '/pages/recipt/index.php';
}
if (!empty($_SESSION['user_credentials']) && $_SESSION['user_credentials']['role'] == 'superadmin') {
    echo mainHeader(['page' => 'dashboard']);
    include_once dirname(__FILE__) . '/pages/dashboard/superadmin.php';
    echo mainFooter();
}
