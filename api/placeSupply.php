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
$supplierObj = new Suppliers();
$storeObj = new Store();
$storeDATA = $storeObj->getStore($shop['id']);
if (empty($_POST['supplierId']) && !empty($_POST['supplierName'])) {
    $data = [
        'name' => $_POST['supplierName'],
        'contact' => !empty($_POST['supplierContact']) ? $_POST['supplierContact'] : "",
        'address' => "",
        'wallet' => 0,
        'company' => "",
        'title' => "",
        'user_id' => $userData['id'],
        'shopId' => $shop['id'],
    ];

    $de = new DoubleEntry();

    $payableAccount = $de->getAccount($shop['payable']);

    $accountData = [
        'title' => 'Supplier - ' . $_POST['supplierName'] . ' - ' . $_POST['company'],
        'code' => $payableAccount['code'],
        'account_type' => $payableAccount['account_type'],
        'group_id' => $payableAccount['group_id'],
        'status' => $payableAccount['status'],
        'parent_id' => $payableAccount['id'],
        'created_by' => $userData['id'],
        'opening_balance' => 0,
    ];

    $accountId = $de->insertAccount($accountData);
    $data['account_id'] = $accountId;

    $supplierId = $supplierObj->createSupplier($data);
} else {
    $supplierId = $_POST['supplierId'];
}


$products = new Products();

$items = [];

$purchaseValue = 0;
$productsValue = 0;
if (sizeof($_POST['items'])) {
    foreach ($_POST['items'] as $item) {
        if (!empty($item['id'])) {
            $purchaseValue += ($item['pprice'] * $item['qty']);
            $productsValue += ($item['price'] * $item['qty']);
            $items[] = [
                'pprice' => $item['pprice'],
                'qty' => $item['qty'],
                'stock_out' => 0,
                'product_id' => $item['id'],
                'shopId' => $_POST['shopId'],
                'owner_id' => $ownerId,
            ];
        }
        // else {
        //     $id = $products->createProduct([
        //         'full_name' => $item['full_name'],
        //         'owner_id' => $ownerId,
        //         'user_id' => $userData['id'],
        //         'price' => $item['price'],
        //         'pprice' => $item['pprice'],
        //         'in_hand' => 0,
        //         'min_qty' => 0,
        //         'pack_size' => 0,
        //         'pack_price' => 0,
        //         'image' => null,
        //         'code' => null,
        //         'description' => "",
        //         'group' => null,
        //         'barcode' => !empty($item['barcode']) ? $item['barcode'] : null,
        //     ]);

        //     if(!empty($item['barcode'])) {
        //         $products->createProductCode([
        //             'product_id' => $id,
        //             'code' => $item['barcode']
        //         ]);
        //     }

        //     $items[] = [
        //         'id' => $id,
        //         'pprice' => $item['pprice'],
        //         'price' => $item['price'],
        //         'barcode' => !empty($item['barcode']) ? $item['barcode'] : null,
        //         'full_name' => $item['full_name'],
        //         'qty' => $item['qty'],
        //     ];
        // }
    }
}



if (sizeof($items)) {

    foreach ($items as $item) {
        $products->assignProduct($item);
    }
}

$supply = new Supply();
$data = [
    'user_id' => $userData['id'],
    'supplier_id' => !empty($supplierId) ? $supplierId : 1,
    'status' => 2,
    'ref_no' => $_POST['ref_no'],
    'price' => $_POST['subTotal'],
    'discount' => $_POST['discount'],
    'shopId' => $userData['shopId'],
    'supply_date' => date('Y-m-d')
];

$supply_id = $supply->createSupply($data);

if ($supply_id) {

    if (sizeof($items)) {
        foreach ($items as $item) {
            $d = [
                'supply_id' => $supply_id,
                'product_id' => $item['product_id'],
                'quantity' => $item['qty'],
                'price' => $item['pprice'],
            ];
            /*  $items[] = [
                'qty' => 0,
                'stock_out' => $item['qty'],
                'shopId' => $userData['shopId'],
                'product_id' => $item['id']
            ]; */
            $supply->createSupplyDetails($d);
        }
    }

    $supplier = $supplierObj->getSupplier($data['supplier_id']);
    $doubleEntry = new DoubleEntry();

    $makeTransaction = [
        'description' => !empty($_POST['summery']) ? $_POST['summery'] : "Supplier Invoice: " . $supply_id . " PLACED",
        'transaction_date' => $storeDATA['sale_date'],
        'reference' => $data['ref_no'],
        'transaction_type' => 'PURCHASE',
        'shopId' => $shop['id'],
        'created_by' => $_SESSION['user_credentials']['id'],
        'order_ref' => null,
        'supply_ref' => $supply_id,
    ];

    $makeTransactionId = $doubleEntry->makeTransaction($makeTransaction);

    $totalDiscount = $productsValue - $purchaseValue;

    $assetPrice = $productsValue;
    $cash = !empty($_POST['payment_amount']) ? $_POST['payment_amount'] : 0;
    $purchaseDiscount = $totalDiscount;
    $payableAmount = $purchaseValue;

    // assets debit entry - debit
    // liability payable entry - credit
    // purchase discount entry - credit
    $entry = [
        'transaction_id' => $makeTransactionId,
        'account_id' => $shop['assets'],
        'entry_type' => 'D',
        'description' => '',
        'amount' => $assetPrice, // 2000
        'payment_mode' => $_POST['payment_mode'],
        'user_id' => $_SESSION['user_credentials']['id'],
    ];

    $a[] = $doubleEntry->makeEntry($entry);

    // payable credit entry
    $entry = [
        'transaction_id' => $makeTransactionId,
        'account_id' => $supplier['account_id'],
        'entry_type' => 'C',
        'description' => '',
        'amount' => $payableAmount,
        'payment_mode' => $_POST['payment_mode'],
        'user_id' => $_SESSION['user_credentials']['id'],
    ];
    $a[] = $doubleEntry->makeEntry($entry);

    if (!empty($purchaseDiscount)) {
        // saleDiscount credit entry
        $entry = [
            'transaction_id' => $makeTransactionId,
            'account_id' => $shop['purchase_discount'],
            'entry_type' => 'C',
            'description' => '',
            'amount' => $purchaseDiscount, // 200 @ 10%
            'payment_mode' => $_POST['payment_mode'],
            'user_id' => $_SESSION['user_credentials']['id'],
        ];
        $a[] = $doubleEntry->makeEntry($entry);
    }

    if (!empty($cash)) {
        $makeTransactionId = $doubleEntry->makeTransaction($makeTransaction);
        // payable credit entry
        $entry = [
            'transaction_id' => $makeTransactionId,
            'account_id' => $supplier['account_id'],
            'entry_type' => 'D',
            'description' => '',
            'amount' => $cash,
            'payment_mode' => $_POST['payment_mode'],
            'user_id' => $_SESSION['user_credentials']['id'],
        ];
        $a[] = $doubleEntry->makeEntry($entry);
        // cash credit entry
        $entry = [
            'transaction_id' => $makeTransactionId,
            'account_id' => $shop['cash'],
            'entry_type' => 'C',
            'description' => '',
            'amount' => $cash, // 200 @ 10%
            'payment_mode' => $_POST['payment_mode'],
            'user_id' => $_SESSION['user_credentials']['id'],
        ];
        $a[] = $doubleEntry->makeEntry($entry);
    }
    // $transaction = [
    //     'supplier_id' => !empty($supplierId) ? $supplierId : 1,
    //     'user_id' => $userData['id'],
    //     'amount' => !empty($_POST['payment_amount']) ? $_POST['payment_amount'] : 0,
    //     'payment_date' => date('Y-m-d H:i:s'),
    //     'transaction_type' => 1,        
    //     'supply_id' => $supply_id,
    //     'shopId' => $userData['shopId']
    // ];

    // $transactionId = $supply->makeTransaction($transaction);

    // $amount = !empty($_POST['payment_amount']) ? $_POST['payment_amount'] : 0;

    // $wallet = [
    //     'id' => !empty($supplierId) ? $supplierId : 1,
    //     'wallet' => $amount - ($_POST['subTotal'] - $_POST['discount'])
    // ];


    // $manageWallet = $supply->manageWallet($wallet);
    /* $productUpdated = [];
    foreach ($items as $value) {
        $productUpdated[] = $products->assignProduct($value);
    }
 */

    echo json_encode(['status' => 200, 'message' => 'successfully done', 'supply' => ['id' => $supply_id]]);
}
