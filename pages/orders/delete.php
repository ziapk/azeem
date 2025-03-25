<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$id = !empty($_GET['id']) ? $_GET['id'] : null;
$reason = !empty($_GET['reason']) ? $_GET['reason'] : null;


var_dump($_GET);exit;

if (empty($id) || empty($reason)) {
    echo json_encode(['success' => false, 'error' => 'Invalid order']);
}

include_once dirname(__FILE__) . '/../../include/settings.php';

if (!empty($id) && !empty($reason)) {
    $orders = new Orders();
    $orderDetail = $orders->getOrder($id);
    print_r($id);
    $currentStatus = $orderDetail['order']['status'];
    if (in_array((int)$orderDetail['order']['status'], [2, 8, 9])) {
        // rollback products first
        $products = new Products();
        foreach ($orderDetail['order_items'] as $prod) {
            $products->subProductQty(['product_id' => $prod['product_id'], 'quantity' => -1 * $prod['quantity'], 'pack_qty' => $prod['pack_qty'], 'owner_id' => $userData['role'] == 'owner' ? $userData['id'] : $userData['created_by'], 'shopId' => $orderDetail['order']['shopId']]);
        }
    }
    $orders->changeOrderFlag(['id' => $id, 'reason' => $reason, 'flag' => 99]);
    echo json_encode(['success' => true, 'message' => "Successfully deleted!"]);
}
