<?php 
include_once dirname(__FILE__).'/../include/settings.php';

$orders = new Orders();

$status = 9;
if(!empty($_POST['payment_amount'])) {
    $gst = round($_POST['subTotal'] * ($_POST['gst'] / 100));
    $service_charges = round($_POST['subTotal'] * ($_POST['service_charges'] / 100));
    $status = 8;
    if((($_POST['subTotal'] + $gst + $service_charges) - $_POST['discount'] - $_POST['payment_amount']) == 0) {
        $status = 2;
    }
}
$data = [
    'user_id' => $userData['id'],
    'customer_id' => !empty($_POST['customerId']) ? $_POST['customerId'] : 1,
    'status' => $status,
    'price' => $_POST['subTotal'],
    'paid_amount' => $_POST['payment_amount'],
    'discount' => $_POST['discount'],
    'gst' => $_POST['gst'],
    'service_charges' => $_POST['service_charges'],
    'shopId' => $userData['shopId'],
    'order_date' => date('Y-m-d')
];

$order_id = $orders->createOrder($data);

if($order_id) {

    $items = [];

    if(sizeof($_POST['items'])) {
        foreach ($_POST['items'] as $item) {
            $d = [
                'shopId' => $userData['shopId'],
                'owner_id' => $userData['role'] == 'owner' ? $userData['id'] : $userData['created_by'],
                'product_id' => $item['id'],
                'order_id' => $order_id,
                'quantity' => $item['qty'],
                'discount' => $item['discount'],
                'price' => $item['price'],
                
            ];
           /*  $items[] = [
                'qty' => 0,
                'stock_out' => $item['qty'],
                'shopId' => $userData['shopId'],
                'product_id' => $item['id']
            ]; */
            $orders->createOrderDetails($d);
        }
    }

    $transaction = [
        'customer_id' => !empty($_POST['customerId']) ? $_POST['customerId'] : 1,
        'user_id' => $userData['id'],
        'amount' => !empty($_POST['payment_amount']) ? $_POST['payment_amount'] : 0,
        'payment_date' => date('Y-m-d H:i:s'),
        'order_id' => $order_id,
        'shopId' => $userData['shopId']
    ];

    $transactionId = $orders->makeTransaction($transaction);

    $amount = !empty($_POST['payment_amount']) ? $_POST['payment_amount'] : 0;

    $wallet = [
        'id' => !empty($_POST['customerId']) ? $_POST['customerId'] : 1,
        'wallet' => $amount - ($_POST['subTotal'] - $_POST['discount']),
        'shopId' => $userData['shopId']
    ];

    $manageWallet = $orders->manageWallet($wallet);
    /* $productUpdated = [];
    foreach ($items as $value) {
        $productUpdated[] = $products->assignProduct($value);
    }
 */

    echo json_encode(['status' => 200, 'message' => 'successfully done', 'order' => [ 'id'=> $order_id ]]);
}

?>