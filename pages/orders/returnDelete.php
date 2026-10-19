<?php


include_once dirname(__FILE__) . '/../../include/settings.php';
$currentReturnDeleteID = !empty($_GET['id']) ? (int)$_GET['id'] : null;
$reason = !empty($_GET['reason']) ? $_GET['reason'] : null;

if (empty($currentReturnDeleteID) || empty($reason)) {
    echo json_encode(['success' => false, 'error' => 'Invalid order']);
    exit;
}

$orders = new Orders();
$returnDetail = $orders->getReturnOrder($currentReturnDeleteID);
if (empty($returnDetail['order']['id'])) {
    echo json_encode(['success' => false, 'error' => 'Return order not found']);
    exit;
}

$orders->changeReturnFlag(['id' => $currentReturnDeleteID, 'reason' => $reason, 'flag' => 99]);

// rollback stock posted by this return (only approved returns have ledger entries)
$orders->rollbackReturnStock($currentReturnDeleteID, (int)$userData['id'], "Deleting return order #$currentReturnDeleteID");

$doubleEntry = new DoubleEntry();
$doubleEntry->deleteTransactionByReturnId($currentReturnDeleteID);

echo json_encode(['success' => true, 'message' => "Successfully deleted!"]);
