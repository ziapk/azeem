<?php 
include_once dirname(__FILE__).'/../include/settings.php';
$orders = new Orders();

$id = $_POST['id'];
$amount = $_POST['amount'];

$order = $orders->getOrder($_POST['id']);

$o = $order['order'];

$price = $o['price'] - $o['discount'] - $o['paid_amount'];

$balance = $price - $amount;

$status = 9;

if($balance > 0) {
    $status = 8;
}
else {
    $status = 2;
}

$data = [
    'id' => $id,
    'amount' => $amount,
    'status' => $status
];


$order_id = $orders->updateOrderAdjustment($data);


if($order_id) {

    $transaction = [
        'customer_id' => $o['customer_id'],
        'user_id' => $userData['id'],
        'amount' => $amount,
        'payment_date' => date('Y-m-d H:i:s'),
        'order_id' => $o['id'],
        'shopId' => $userData['shopId']
    ];

    $transactionId = $orders->makeTransaction($transaction);

    $wallet = [
        'id' => $o['customer_id'],
        'wallet' => $amount,
        'shopId' => $userData['shopId']
    ];

    $manageWallet = $orders->manageWallet($wallet);

    echo json_encode(['status' => 200, 'message' => 'successfully done', 'transaction' => [ 'id'=> $transactionId ]]);

}
else {
    echo json_encode(['status' => 400, 'message' => 'transaction failed']);
}