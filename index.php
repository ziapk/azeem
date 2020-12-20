<?php
include_once dirname(__FILE__).'/include/settings.php';

echo mainHeader();

if(!empty($_SESSION['user_credentials']) && $_SESSION['user_credentials']['role'] == 'owner') {
    include_once dirname(__FILE__).'/pages/dashboard/owner.php';
}
if(!empty($_SESSION['user_credentials']) && $_SESSION['user_credentials']['role'] == 'manager') {
    include_once dirname(__FILE__).'/pages/dashboard/manager.php';
}
if(!empty($_SESSION['user_credentials']) && $_SESSION['user_credentials']['role'] == 'shopkeeper') {
    include_once dirname(__FILE__).'/pages/recipt/index.php';
}
echo mainFooter();