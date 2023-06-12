<?php
session_start();
include_once dirname(__FILE__) . '/../../include/settings.php';
$store = new ShopAccounts();
foreach ($_POST['accounts'] as $key => $value) {
    $store->updateSA($value);
}
header('Content-Type: application/json; charset=UTF-8');
echo json_encode(['status' => 'Successfully Updated']);
