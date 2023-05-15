<?php

$id = !empty($_GET['id']) ? $_GET['id'] : null;
$reason = !empty($_GET['reason']) ? $_GET['reason'] : null;

if (empty($id) || empty($reason)) {
    echo json_encode(['success' => false, 'error' => 'Invalid order']);
}

include_once dirname(__FILE__) . '/../../include/settings.php';

if (!empty($id) && !empty($reason)) {
    $orders = new Orders();
    $orders->changeOrderFlag(['id' => $id, 'reason' => $reason, 'flag' => 99]);
    echo json_encode(['success' => true, 'message' => "Successfully deleted!"]);
}
