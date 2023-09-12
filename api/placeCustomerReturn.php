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
$returnType = !empty($_POST['return_type']) ? $_POST['return_type'] : 1; // 1 = sale return, 2 = purchase return
$isSupplier = !empty($_POST['is_supplier']) ? $_POST['is_supplier'] : 1; // 1 = customer, 2 = supplier

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

    $orderDetail = $order = $orders->getReturnOrder($_POST['returnOrder']);

    if (!empty($order['order']['id'])) {

        // rollback products first
        foreach ($orderDetail['order_items'] as $prod) {
            $qty = 0;
            $pack_qty = 0;
            if ($returnType == 1) {
                $products->subProductQty(['product_id' => $prod['product_id'], 'quantity' => $prod['quantity'], 'pack_qty' => $prod['pack_qty'], 'owner_id' => $storeDATA['owner_id'], 'shopId' => $storeDATA['id']]);
            } else {
                $products->addProductQty($prod['product_id'], ['qty' => $prod['quantity'], 'pack_qty' => $prod['pack_qty']], $storeDATA['id']);
            }
        }
        $orders->deleteReturnOrderItem($order['order']['id']);
        // delete transactions
        $doubleEntry->deleteTransactionByReturnId($_POST['returnOrder']);
    }
}
$returnId = $orders->makeReturn([
    "id" => $_POST['returnOrder'],
    "amount" => $purchaseValue,
    "paid" => $payment_amount,
    "discount" => $discount,
    "shopId" => $shopId,
    "return_date" => $storeDATA['sale_date'],
    "owner_id" => $storeDATA['owner_id'],
    "order_id" => !empty($_POST['order_id']) ? $_POST['order_id'] : null,
    "customer_id" => $supplierId,
    "customer_name" => $_POST['supplierName'],
    "return_type" => $returnType,
    "is_supplier" => $isSupplier
]);

$products = [];
foreach ($_POST['items'] as $key => $value) {
    $products[] = [
        'user_id' => $userData['id'],
        'shopId' => $shopId,
        'order_id' => $returnId,
        'product_id' => $value['product_id'],
        'quantity' => $value['qty'],
        "owner_id" => $storeDATA['owner_id'],
        'pack_size' => !empty($value['pack_size']) ? $value['pack_size'] : 0,
        'pack_qty' => !empty($value['pack_qty']) ? $value['pack_qty'] : 0,
        'price' => $value['price'],
        'discount_type' => !empty($value['discount_type']) ? $value['discount_type'] : 1,
        'discount_value' => !empty($value['discount_value']) ? $value['discount_value'] : 0,
        'discount' => !empty($value['discount']) ? $value['discount'] : 0,
        'type' => 1,
    ];
}

$res = [];
foreach ($products as $id => $row) {
    $res[$row['product_id']][] = $orders->orderReturnAll($row, $returnType == 1 ? false : true);
}


if ($isSupplier == 1) {
    $supplier = $supplierObj->getCustomer($supplierId);
} else {
    $supplierObj = new Suppliers();
    $supplier = $supplierObj->getSupplier($supplierId);
}

$config = [
    'description' => !empty($_POST['summery']) ? $_POST['summery'] : ($returnType == 1 ? "Sale Return PLACED" : "Purchase Return PLACED"),
    'transaction_type' => ($returnType == 1 ? 'SALE_RETURN' : 'PURCHASE_RETURN'),
    'shop_entry' => 'D',
    'customer_entry' => 'C',
    'customer_cash_entry' => 'D',
    'shop_cash_entry' => 'C',
];

if ($returnType == 2) {
    $config['shop_entry'] = 'C';
    $config['customer_entry'] = 'D';
    $config['customer_cash_entry'] = 'C';
    $config['shop_cash_entry'] = 'D';
}

$makeTransaction = [
    'description' => $config['description'],
    'transaction_date' => $storeDATA['sale_date'],
    'reference' => $_POST['ref_no'],
    'transaction_type' => $config['transaction_type'],
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
    'entry_type' => $config['shop_entry'],
    'description' => '',
    'amount' => $assetPrice, // 2000
    'payment_mode' => $_POST['payment_mode'],
    'user_id' => $_SESSION['user_credentials']['id'],
];

$a[] = $doubleEntry->makeEntry($entry);


$entry = [
    'transaction_id' => $makeTransactionId,
    'account_id' => $supplier['account_id'],
    'entry_type' => $config['customer_entry'],
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
        'account_id' => $returnType == 1 ? $storeAccounts['sale_discount'] : $storeAccounts['purchase_discount'],
        'entry_type' => $config['customer_entry'], // as per customer
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
        'entry_type' => $config['customer_cash_entry'],
        'description' => '',
        'amount' => $payment_amount,
        'payment_mode' => $_POST['payment_mode'],
        'user_id' => $_SESSION['user_credentials']['id'],
    ];
    $a[] = $doubleEntry->makeEntry($entry);
    // cash credit entry
    $entry = [
        'transaction_id' => $makeTransactionId,
        'account_id' => $returnType == 1 ? $storeAccounts['sale_returns'] : $storeAccounts['purchase_returns'],
        'entry_type' => $config['shop_cash_entry'],
        'description' => '',
        'amount' => $payment_amount, // 200 @ 10%
        'payment_mode' => $_POST['payment_mode'],
        'user_id' => $_SESSION['user_credentials']['id'],
    ];
    $a[] = $doubleEntry->makeEntry($entry);
}

$newsletter = new Newsletter();
$send = $newsletter->send([
    'subject' => $config['description'],
    'body' => $newsletter->drawReturn($returnId),
    'sentTo' => [['email' => !empty($supplier['email']) ? $supplier['email'] : 'zia.pccr@yahoo.com', 'name' => $_POST['supplierName']]],
    'ccEmails' => [['email' => $storeDATA['company_email'], 'name' => $storeDATA['full_name']]],
    'client' => $storeDATA['full_name'],
]);

echo json_encode(['status' => 200, 'message' => 'successfully done', 'order' => ['id' => $returnId]]);
