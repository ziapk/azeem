<?php
include_once dirname(__FILE__).'/include/settings.php';


if(!empty($_SESSION['user_credentials']) && $_SESSION['user_credentials']['role'] == 'owner') {
    echo mainHeader(['page' => 'dashboard']);
    include_once dirname(__FILE__).'/pages/dashboard/owner.php';
    echo mainFooter();
}
if(!empty($_SESSION['user_credentials']) && $_SESSION['user_credentials']['role'] == 'manager') {
    echo mainHeader(['page' => 'product']);
    include_once dirname(__FILE__).'/pages/dashboard/manager.php';
    echo mainFooter();
}
if(!empty($_SESSION['user_credentials']) && $_SESSION['user_credentials']['role'] == 'shopkeeper') {
    include_once dirname(__FILE__).'/pages/recipt/index.php';
}