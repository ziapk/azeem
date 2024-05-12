<?php 
include_once dirname(__FILE__).'/../include/settings.php';
global $shop;
$customers = new  Customers();
$search = [];
if(!empty($_SESSION['shopInfo'])) {
    $shopId = !empty($_GET['shopId']) ? $_GET['shopId'] : $shop['id'];
    $account_type = !empty($_GET['account_type']) ? $_GET['account_type'] : 1;
    $accountsOnly = !empty($_GET['accountsOnly']) ? true : false;
    $search = $customers->searchCustomer($shopId, $_GET['term'], $accountsOnly, $account_type);
};
echo json_encode($search);
