<?php
/**
 * Bulk update of products.pprice from the Fix Purchase Prices grid.
 *
 * Accepts JSON: {"rows": [{"product_id": 123, "pprice": 19.5}, ...]}
 */
include_once dirname(__FILE__) . '/../include/settings.php';

header('Content-Type: application/json');

if ($userData['role'] != 'owner' && $userData['role'] != 'manager') {
    http_response_code(403);
    echo json_encode(['status' => 403, 'message' => 'Only an owner or manager can change purchase prices.']);
    exit;
}

$payload = json_decode(file_get_contents('php://input'), true);
$rows    = !empty($payload['rows']) ? $payload['rows'] : [];

if (empty($rows)) {
    echo json_encode(['status' => 400, 'message' => 'Nothing to save.', 'updated' => 0]);
    exit;
}

$productObj = new Products();
$updated    = 0;
$skipped    = [];

foreach ($rows as $row) {
    $productId = !empty($row['product_id']) ? (int) $row['product_id'] : 0;
    // A blank or negative cost is a mistake, not a deliberate zero.
    $pprice = isset($row['pprice']) && $row['pprice'] !== '' ? (float) $row['pprice'] : -1;

    if ($productId <= 0 || $pprice < 0) {
        $skipped[] = $productId;
        continue;
    }

    $productObj->updateProductPPrice(['product_id' => $productId, 'pprice' => $pprice]);
    $updated++;
}

echo json_encode([
    'status'  => 200,
    'message' => $updated . ' product(s) updated.',
    'updated' => $updated,
    'skipped' => $skipped,
]);
