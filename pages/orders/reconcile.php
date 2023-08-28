<?php

$id = !empty($_GET['id']) ? $_GET['id'] : 0;
$flag = !empty($_GET['flag']) ? $_GET['flag'] : 0;

if (empty($id) || empty($flag)) {
    echo json_encode(['success' => false, 'error' => 'Invalid order']);
}

include_once dirname(__FILE__) . '/../../include/settings.php';

if (!empty($id) && !empty($flag)) {
    $orders = new Orders();
    $orders->reconcileBill($id, $flag);
    echo json_encode(['success' => true, 'message' => "Successfully reconciled!"]);
}
