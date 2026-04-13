<?php
// api/sync-product.php
// Reconciles the inventory ledger for a single product.
// Inserts any missing ledger entries and corrects store_products.qty.
//
// GET  api/sync-product.php?product_id=123
// GET  api/sync-product.php?product_id=123&shop_id=5   (optional, defaults to session shop)
//
// Response:
//   { "status": 200, "inserted": 3, "qty": 42, "message": "Synced successfully" }
//   { "status": 400, "message": "product_id is required" }

include_once dirname(__FILE__) . '/../include/settings.php';

$product_id = !empty($_GET['product_id']) ? (int)$_GET['product_id'] : 0;
$shop_id    = !empty($_GET['shop_id'])    ? (int)$_GET['shop_id']    : (int)$userData['shopId'];
$owner_id   = $userData['role'] == 'owner' ? (int)$userData['id']   : (int)$userData['created_by'];

if (empty($product_id)) {
    http_response_code(400);
    echo json_encode(['status' => 400, 'message' => 'product_id is required']);
    exit;
}

$inventory = new Inventory();
$result    = $inventory->reconcileProduct($product_id, $shop_id, $owner_id);

echo json_encode([
    'status'   => 200,
    'inserted' => $result['inserted'],
    'qty'      => $result['qty'],
    'message'  => $result['inserted'] > 0
        ? $result['inserted'] . ' missing ledger entr' . ($result['inserted'] === 1 ? 'y' : 'ies') . ' added. qty set to ' . $result['qty']
        : 'Already in sync. qty is ' . $result['qty'],
]);