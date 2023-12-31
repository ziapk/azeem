<?php
include_once dirname(__FILE__) . '/../include/settings.php';
$ownerId = $userData['role'] == 'owner' ? $userData['id'] : $userData['created_by'];

// 1. add supplier if not get id or if didn't get id or name set no supplier [done]
// 2. add product with minimal detail as new product [done]
// 3. add barcode in product barcode table if new [done]
// 4. update product purchase price and qty and sale price if demanded [done]
// 5. make a transation base on payment [done]
// 6. maintain a supplier wallet base on what we paid to him/her. [done]
// 7. success or error response 

$supplierObj = new Customers();
$storeObj = new Store();
$products = new Products();
$orders = new Orders();
$shopAccounts = new ShopAccounts();
$response = $orders->prepareReturn($_POST);

if ($response['status'] === 200) {
    echo json_encode(['status' => 200, 'message' => 'successfully done', 'order' => ['id' => $response['order']['id'], 'full' => $response]]);
}
