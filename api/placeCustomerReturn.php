<?php
include_once dirname(__FILE__) . '/../include/settings.php';
$ownerId = $userData['role'] == 'owner' ? $userData['id'] : $userData['created_by'];

// 1. add supplier if not get id or if didn't get id or name set no supplier [done]
// 2. add product with minimal detail as new product [done]
// 3. add barcode in product barcode table if new [done]
// 4. update product purchase price and qty and sale price if demanded [done]
// 5. make a transation base on payment [done]
// 6. maintain a supplier wallet base on what we paid to him/her. [done]
// 7. success or error response 

$supplierId = 1;
$supplierObj = new Customers();
$storeObj = new Store();
$supplierId = $_POST['supplierId'];


$products = new Products();
$orders = new Orders();

$items = [];

$purchaseValue = $_POST['subTotal'];
$discount = !empty($_POST['discount']) ? $_POST['discount'] : 0;
$givenDiscount = !empty($_POST['givenDiscount']) ? $_POST['givenDiscount'] : 0;

$discount += $givenDiscount;

$opening_balance = !empty($_POST['opening_balance']) ? $_POST['opening_balance'] : 0;
$remaining_balance = $opening_balance - $_POST['subTotal'];
$productsValue = $purchaseValue;
$payment_amount = !empty($_POST['payment_amount']) ? $_POST['payment_amount'] : 0;
$shopId = $_POST['shopId'];
$productsForReturn = [];

$storeDATA = $storeObj->getStore($shopId);

$shopAccounts = new ShopAccounts();
$accountsData = $shopAccounts->getSAs($shop['id']);
$storeAccounts = [];
foreach ($accountsData as $a) {
    $storeAccounts[$a['key_value']] = $a['account_id'];
}

if (empty($_POST['order_id'])) {
    $orderDetails = []; // $orders->getOrderItemsByProductIds(array_keys($productIds), $supplierId);
    $orders->orderReturn($_POST['order_id'], 5);
} else {
    $orderDetails = $_POST['items'];
}
$doubleEntry = new DoubleEntry();
if (!empty($_POST['returnOrder'])) {

    $order = $orders->getReturnOrder($_POST['returnOrder']);

    if (!empty($order['order']['id'])) {
        $orders->deleteReturnOrderItem($order['order']['id']);
        // delete transactions
        $doubleEntry->deleteTransactionByReturnId($_POST['returnOrder']);
    }
}
$returnId = $orders->makeReturn([
    "id" => $_POST['returnOrder'],
    "amount" => $purchaseValue,
    "amount" => $purchaseValue,
    "paid" => $payment_amount,
    "discount" => $discount,
    "shopId" => $shopId,
    "return_date" => $storeDATA['sale_date'],
    "owner_id" => $storeDATA['owner_id'],
    "order_id" => !empty($_POST['order_id']) ? $_POST['order_id'] : null,
    "customer_id" => $supplierId,
    "customer_name" => $_POST['supplierName'],
]);

$products = [];
foreach ($_POST['items'] as $key => $value) {
    $products[] = [
        'user_id' => $userData['id'],
        'shopId' => $shopId,
        'order_id' => $returnId,
        'product_id' => $value['product_id'],
        'quantity' => $value['qty'],
        'price' => $value['price'],
        'discount_type' => !empty($value['discount_type']) ? $value['discount_type'] : 1,
        'discount_value' => !empty($value['discount_value']) ? $value['discount_value'] : 0,
        'discount' => $value['discount'],
        'type' => 1,
    ];
}


$res = [];
foreach ($products as $id => $row) {
    $res[$row['product_id']][] = $orders->orderReturnAll($row);
}

$supplier = $supplierObj->getCustomer($supplierId);



$makeTransaction = [
    'description' => !empty($_POST['summery']) ? $_POST['summery'] : "Sale Return PLACED",
    'transaction_date' => $storeDATA['sale_date'],
    'reference' => $_POST['ref_no'],
    'transaction_type' => 'SALE_RETURN',
    'shopId' => $shopId,
    'created_by' => $_SESSION['user_credentials']['id'],
    'return_ref' => $returnId
];

$makeTransactionId = $doubleEntry->makeTransaction($makeTransaction);


$assetPrice = $productsValue; // D 1000
$saleDiscount = $discount; // C 200
$returnAmount = $purchaseValue - $discount; // C 800

$entry = [
    'transaction_id' => $makeTransactionId,
    'account_id' => $storeAccounts['assets'],
    'entry_type' => 'D',
    'description' => '',
    'amount' => $assetPrice, // 2000
    'payment_mode' => $_POST['payment_mode'],
    'user_id' => $_SESSION['user_credentials']['id'],
];

$a[] = $doubleEntry->makeEntry($entry);


$entry = [
    'transaction_id' => $makeTransactionId,
    'account_id' => $supplier['account_id'],
    'entry_type' => 'C',
    'description' => '',
    'amount' => $returnAmount,
    'payment_mode' => $_POST['payment_mode'],
    'user_id' => $_SESSION['user_credentials']['id'],
];

$a[] = $doubleEntry->makeEntry($entry);

if (!empty($saleDiscount)) {
    // saleDiscount credit entry
    $entry = [
        'transaction_id' => $makeTransactionId,
        'account_id' => $storeAccounts['sale_discount'],
        'entry_type' => 'C',
        'description' => '',
        'amount' => $saleDiscount, // 200 @ 10%
        'payment_mode' => $_POST['payment_mode'],
        'user_id' => $_SESSION['user_credentials']['id'],
    ];
    $a[] = $doubleEntry->makeEntry($entry);
}


// assets debit entry - debit
// liability payable entry - credit
// purchase discount entry - credit


if (!empty($payment_amount)) {
    // $makeTransactionId = $doubleEntry->makeTransaction($makeTransaction);
    // payable credit entry
    $entry = [
        'transaction_id' => $makeTransactionId,
        'account_id' => $supplier['account_id'],
        'entry_type' => 'D',
        'description' => '',
        'amount' => $payment_amount,
        'payment_mode' => $_POST['payment_mode'],
        'user_id' => $_SESSION['user_credentials']['id'],
    ];
    $a[] = $doubleEntry->makeEntry($entry);
    // cash credit entry
    $entry = [
        'transaction_id' => $makeTransactionId,
        'account_id' => $storeAccounts['sale_returns'],
        'entry_type' => 'C',
        'description' => '',
        'amount' => $payment_amount, // 200 @ 10%
        'payment_mode' => $_POST['payment_mode'],
        'user_id' => $_SESSION['user_credentials']['id'],
    ];
    $a[] = $doubleEntry->makeEntry($entry);
}

echo json_encode(['status' => 200, 'message' => 'successfully done', 'order' => ['id' => $returnId]]);
