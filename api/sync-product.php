<?php
// api/sync-product.php
// Reconciles inventory ledger for one product or many.
// Inserts any missing ledger entries and corrects store_products.qty.
//
// ── SINGLE ──────────────────────────────────────────────────────────
// GET  api/sync-product.php?product_id=123
// GET  api/sync-product.php?product_id=123&shop_id=5
//
// Response:
//   { "status": 200, "inserted": 2, "qty": 42, "message": "..." }
//
// ── BULK ────────────────────────────────────────────────────────────
// GET  api/sync-product.php?product_ids=5,12,99
// GET  api/sync-product.php?product_ids=5,12,99&shop_id=5
//
// POST api/sync-product.php   body: { product_ids: [5, 12, 99], shop_id: 5 }
//
// Response:
//   {
//     "status": 200,
//     "total_inserted": 3,
//     "products": {
//       "5":  { "inserted": 1, "qty": 42 },
//       "12": { "inserted": 0, "qty": 7  },
//       "99": { "inserted": 2, "qty": 15 }
//     },
//     "message": "3 missing ledger entries added across 3 products"
//   }

include_once dirname(__FILE__) . '/../include/settings.php';

$shop_id  = !empty($_GET['shop_id'])   ? (int)$_GET['shop_id']  :
            (!empty($_POST['shop_id']) ? (int)$_POST['shop_id'] : (int)$userData['shopId']);
$owner_id = $userData['role'] == 'owner' ? (int)$userData['id'] : (int)$userData['created_by'];

$inventory = new Inventory();

// ── BULK: product_ids passed as comma-separated GET or POST JSON ─────
$rawIds = null;
if (!empty($_GET['product_ids'])) {
    $rawIds = explode(',', $_GET['product_ids']);
} elseif (!empty($_POST['product_ids'])) {
    $rawIds = is_array($_POST['product_ids']) ? $_POST['product_ids'] : explode(',', $_POST['product_ids']);
} else {
    // try JSON body  { "product_ids": [5, 12, 99] }
    $body = json_decode(file_get_contents('php://input'), true);
    if (!empty($body['product_ids'])) {
        $rawIds = $body['product_ids'];
        if (!empty($body['shop_id'])) {
            $shop_id = (int)$body['shop_id'];
        }
    }
}

if (!empty($rawIds)) {
    $product_ids = array_values(array_unique(array_filter(array_map('intval', $rawIds))));

    if (empty($product_ids)) {
        http_response_code(400);
        echo json_encode(['status' => 400, 'message' => 'No valid product IDs provided']);
        exit;
    }

    $result  = $inventory->reconcileProducts($product_ids, $shop_id, $owner_id);
    $count   = count($product_ids);
    $ins     = $result['total_inserted'];

    echo json_encode([
        'status'          => 200,
        'total_inserted'  => $ins,
        'products'        => $result['products'],
        'message'         => $ins > 0
            ? $ins . ' missing ledger entr' . ($ins === 1 ? 'y' : 'ies') . ' added across ' . $count . ' product' . ($count === 1 ? '' : 's')
            : 'All ' . $count . ' product' . ($count === 1 ? '' : 's') . ' already in sync',
    ]);
    exit;
}

// ── SINGLE: product_id ───────────────────────────────────────────────
$product_id = !empty($_GET['product_id']) ? (int)$_GET['product_id'] : 0;

if (empty($product_id)) {
    http_response_code(400);
    echo json_encode(['status' => 400, 'message' => 'Provide product_id (single) or product_ids (bulk)']);
    exit;
}

$result = $inventory->reconcileProduct($product_id, $shop_id, $owner_id);
$ins    = $result['inserted'];

echo json_encode([
    'status'   => 200,
    'inserted' => $ins,
    'qty'      => $result['qty'],
    'message'  => $ins > 0
        ? $ins . ' missing ledger entr' . ($ins === 1 ? 'y' : 'ies') . ' added. qty set to ' . $result['qty']
        : 'Already in sync. qty is ' . $result['qty'],
]);