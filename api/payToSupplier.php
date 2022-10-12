<?php
include_once dirname(__FILE__).'/../include/settings.php';


$supply = new Supply();

$supplierId = $_POST['id'];
$amount = $_POST['amount'];

$transaction = [
    'supplier_id' => $supplierId,
    'user_id' => $userData['id'],
    'amount' => $amount,
    'payment_date' => date('Y-m-d H:i:s'),
    'supply_id' => 0,
    'transaction_type' => 2,
    'shopId' => $userData['shopId']
];

$transactionId = $supply->makeTransaction($transaction);

$wallet = [
    'id' => $supplierId,
    'wallet' => $amount
];

$manageWallet = $supply->manageWallet($wallet);

echo json_encode(['status' => 200, 'message' => 'successfully done', 'supply' => [ 'id'=> $transactionId ]]);