<?php


include_once dirname(__FILE__) . '/../../include/settings.php';
$currentReturnDeleteID = !empty($_GET['id']) ? $_GET['id'] : null;
$reason = !empty($_GET['reason']) ? $_GET['reason'] : null;

if (empty($id) || empty($reason)) {
    echo json_encode(['success' => false, 'error' => 'Invalid order']);
}

if (!empty($currentReturnDeleteID) && !empty($reason)) {
    $orders = new Orders();
    $orders->changeReturnFlag(['id' => $currentReturnDeleteID, 'reason' => $reason, 'flag' => 99]);
    echo json_encode(['success' => true, 'message' => "Successfully deleted!"]);
}
