<?php 
include_once dirname(__FILE__).'/../include/settings.php';
//$customers = new  Customers();
//$shopInfo = []; 
//$search = [];
/* if(!empty($_SESSION['shopInfo'])) {
    $shopInfo = $_SESSION['shopInfo'];
    $search = $customers->searchCustomer($shopInfo['shopId'], $_GET['term']);
}; */

$orders = new Orders();
//$products = new Products();
$data = [
    'user_id' => $userData['id'],
    'customer_id' => !empty($_POST['customerId']) ? $_POST['customerId'] : 1,
    'status' => 2,
    'price' => $_POST['subTotal'],
    'discount' => $_POST['discount'],
    'shopId' => $userData['shopId'],
    'order_date' => date('Y-m-d')
];

$order_id = $orders->createOrder($data);

if($order_id) {

    $items = [];

    if(sizeof($_POST['items'])) {
        foreach ($_POST['items'] as $item) {
            $d = [
                'order_id' => $order_id,
                'product_id' => $item['id'],
                'quantity' => $item['qty'],
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