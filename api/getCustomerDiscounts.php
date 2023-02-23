<?php 
include_once dirname(__FILE__).'/../include/settings.php';
$products = new Customers();
if(!empty($_SESSION['shopInfo']) && !empty($_GET['id'])) {
    $id = $_GET['id'];
    $search = $products->getCustomerDiscounts(['shopId' => $_SESSION['shopInfo']['id'], 'customer_id'=> $id]);
};
echo json_encode($search);
?>