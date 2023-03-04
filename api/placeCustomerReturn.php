<?php 
include_once dirname(__FILE__).'/../include/settings.php';
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
$opening_balance = !empty($_POST['opening_balance']) ? $_POST['opening_balance'] : 0;
$remaining_balance = $opening_balance - $_POST['subTotal'];
$productsValue = $purchaseValue + $discount;
$payment_amount = !empty($_POST['payment_amount']) ? $_POST['payment_amount'] : 0;
$shopId = $_POST['shopId'];
$productsForReturn = [];

$storeDATA = $storeObj->getStore($shopId);


if(sizeof($_POST['items'])) {
    foreach($_POST['items'] as $item) {
        $data = [
            'order_id' => $item['order_id'],
            'shopId' => $shopId,
            'type' => 3,
            'user_id' => $userData['id'],
            'product_id' => $item['id'],
            'quantity' => $item['qty'],
        ];
        $orders->orderReturnAll($data, 1, 3);
    }
}
// echo 'Opening Balance';
// print_r($opening_balance);
// echo '<br />';
// echo '<br />';
// echo 'Remaining Balance';
// print_r($remaining_balance);
// echo '<br />';
// echo '<br />';
// echo 'Paid Amount';
// print_r($purchaseValue);
// echo '<br />';
// echo '<br />';
// echo 'Assets Amount';
// print_r($productsValue);
// echo '<br />';
// echo '<br />';
// echo 'Given Discount';
// print_r($discount);
// echo '<br />';
// echo '<br />';
// echo 'Products';
// print_r($productsForReturn);
$supplier = $supplierObj->getCustomer($supplierId);
// echo '<br />';
// echo '<br />';
// echo 'Products';
// print_r($supplier);
// echo '<br />';
// echo '<br />';
// echo 'Products';
// print_r($storeDATA);

    $doubleEntry = new DoubleEntry();

    $makeTransaction = [
        'description' => !empty($_POST['summery']) ? $_POST['summery'] : "Sale Return PLACED",
        'transaction_date' => $storeDATA['sale_date'],
        'reference' => $_POST['ref_no'],
        'shopId' => $shopId,
        'created_by' => $_SESSION['user_credentials']['id'],
        'order_ref' => null,
        'supply_ref' => null,
    ];

    $makeTransactionId = $doubleEntry->makeTransaction($makeTransaction);

    
    $assetPrice = $productsValue; // D 1000
    $saleDiscount = $discount; // C 200
    $returnAmount = $purchaseValue; // C 800

    $entry = [
        'transaction_id' => $makeTransactionId,
        'account_id' => $storeDATA['assets'],
        'entry_type' => 'D',
        'description' => '',
        'amount' => $assetPrice, // 2000
        'payment_mode'=> $_POST['payment_mode'],
        'user_id' => $_SESSION['user_credentials']['id'],
    ];

    $a[] = $doubleEntry->makeEntry($entry);


    $entry = [
        'transaction_id' => $makeTransactionId,
        'account_id' => $supplier['account_id'],
        'entry_type' => 'C',
        'description' => '',
        'amount' => $returnAmount,
        'payment_mode'=> $_POST['payment_mode'],
        'user_id' => $_SESSION['user_credentials']['id'],
    ];

    $a[] = $doubleEntry->makeEntry($entry);

    if(!empty($saleDiscount)) {
        // saleDiscount credit entry
        $entry = [
            'transaction_id' => $makeTransactionId,
            'account_id' => $storeDATA['sale_discount'],
            'entry_type' => 'C',
            'description' => '',
            'amount' => $saleDiscount, // 200 @ 10%
            'payment_mode'=> $_POST['payment_mode'],
            'user_id' => $_SESSION['user_credentials']['id'],
        ];
        $a[] = $doubleEntry->makeEntry($entry);
    }


    // assets debit entry - debit
    // liability payable entry - credit
    // purchase discount entry - credit
    

    if(!empty($payment_amount)) {
        $makeTransactionId = $doubleEntry->makeTransaction($makeTransaction);
        // payable credit entry
        $entry = [
            'transaction_id' => $makeTransactionId,
            'account_id' => $supplier['account_id'],
            'entry_type' => 'C',
            'description' => '',
            'amount' => $payment_amount,
            'payment_mode'=> $_POST['payment_mode'],
            'user_id' => $_SESSION['user_credentials']['id'],
        ];
        $a[] = $doubleEntry->makeEntry($entry);
        // cash credit entry
        $entry = [
            'transaction_id' => $makeTransactionId,
            'account_id' => $storeDATA['sale_returns'],
            'entry_type' => 'D',
            'description' => '',
            'amount' => $payment_amount, // 200 @ 10%
            'payment_mode'=> $_POST['payment_mode'],
            'user_id' => $_SESSION['user_credentials']['id'],
        ];
        $a[] = $doubleEntry->makeEntry($entry);
        
    }

    echo json_encode(['status' => 200, 'message' => 'successfully done', 'supply' => [ 'id'=> $makeTransactionId ]]);

?>