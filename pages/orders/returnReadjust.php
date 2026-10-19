<?php

// Re-adjust stock of selected deleted returns (flag 99) whose stock was never
// rolled back. Safe to re-run: only the outstanding net quantity is reversed.

include_once dirname(__FILE__) . '/../../include/settings.php';

if ($userData['role'] !== 'owner') {
    echo json_encode(['success' => false, 'error' => 'Not allowed']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$ids = !empty($input['ids']) && is_array($input['ids']) ? array_unique(array_map('intval', $input['ids'])) : [];

if (empty($ids)) {
    echo json_encode(['success' => false, 'error' => 'No return orders selected']);
    exit;
}

$orders = new Orders();
$done = [];
$skipped = [];

foreach ($ids as $id) {
    $returnDetail = $orders->getReturnOrder($id);
    $row = $returnDetail['order'] ?? null;
    $ownShop = !empty($row) && ((int)$row['shopId'] === (int)$userData['shopId'] || (int)$row['main_shop_rid'] === (int)$userData['shopId']);

    if (empty($row) || (int)$row['flag'] !== 99 || !$ownShop) {
        $skipped[] = $id;
        continue;
    }

    $orders->rollbackReturnStock($id, (int)$userData['id'], "Re-adjust stock of deleted return order #$id");
    $done[] = $id;
}

echo json_encode([
    'success' => true,
    'done' => $done,
    'skipped' => $skipped,
    'message' => count($done) . ' return order(s) re-adjusted' . (count($skipped) ? ', ' . count($skipped) . ' skipped' : ''),
]);
