<?php 
include_once dirname(__FILE__).'/../include/settings.php';
global $shop;
$customers = new  Customers();
$search = [];
if(!empty($_SESSION['shopInfo'])) {
    $shopId = !empty($_GET['shopId']) ? $_GET['shopId'] : $shop['id'];
    $accountsOnly = !empty($_GET['accountsOnly']) ? true : false;
    $search = $customers->searchCustomer($shopId, $_GET['term'], $accountsOnly);
};
echo json_encode($search);
