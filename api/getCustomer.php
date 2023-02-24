<?php 
include_once dirname(__FILE__).'/../include/settings.php';
global $shop;
$customers = new  Customers();
$search = [];
if(!empty($_SESSION['shopInfo'])) {
    $search = $customers->searchCustomer($shop['id'], $_GET['term']);
};
echo json_encode($search);
?>