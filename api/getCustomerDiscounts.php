<?php
include_once dirname(__FILE__) . '/../include/settings.php';
global $shop;
$products = new Customers();
if (!empty($shop) && !empty($_GET['id'])) {
    $id = $_GET['id'];
    $search = $products->getCustomerDiscounts(['shopId' => $shop['id'], 'ownerId' => $shop['owner_id'], 'customer_id' => $id]);
};
echo json_encode($search);
