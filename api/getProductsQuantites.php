<?php
include_once dirname(__FILE__) . '/../include/settings.php';
$ownerId = $userData['role'] == 'owner' ? $userData['id'] : $userData['created_by'];
$products = new  Products();

// $shopId = $userData['role'] == 'owner' ? null : $userData['shopId'];
$shopId = $userData['shopId'];

$search = $products->getOwnerProductsStock($shopId);

echo safe_json_encode($search);
