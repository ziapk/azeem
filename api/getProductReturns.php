<?php 
include_once dirname(__FILE__).'/../include/settings.php';
$ownerId = $userData['role'] == 'owner' ? $userData['id'] : $userData['created_by'];
$shopId = $shop['id'];
$supplyObj = new  Orders();
$product_id = !empty($_GET['product_id']) ? $_GET['product_id'] : "";
if(!empty($_SESSION['shopInfo'])) {
    $search = $supplyObj->getProductReturns(['product_id' => $product_id, 'shopId' => $shopId, 'owner_id' => $ownerId]);
};
echo json_encode($search);
?>