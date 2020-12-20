<?php 
include_once dirname(__FILE__).'/../include/settings.php';
$customers = new  Customers();
$shopInfo = []; 
$search = [];
if(!empty($_SESSION['shopInfo'])) {
    $shopInfo = $_SESSION['shopInfo'];
    $search = $customers->searchCustomer($shopInfo['shopId'], $_GET['term']);
};
echo json_encode($search);
?>