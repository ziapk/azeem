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
$shopAccounts = new ShopAccounts();
$accountsData = $shopAccounts->getSAs($shop['id']);
$storeAccounts = [];
$cash = !empty($_POST['payment_amount']) ? $_POST['payment_amount'] : 0;
$isDemandCreate = !empty($_POST['createDemand']);
$payment_with_credit = !empty($_POST['payment_with_credit']) ? $_POST['payment_with_credit'] : 0;
$de = new DoubleEntry();
foreach ($accountsData as $a) {
    $storeAccounts[$a['key_value']] = $a['account_id'];
}
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


    $shopAccounts = new ShopAccounts();
    $accountsData = $shopAccounts->getSAs($shop['id']);
    $storeAccounts = [];
    foreach ($accountsData as $a) {
        $storeAccounts[$a['key_value']] = $a['account_id'];
    }


    $payableAccount = $de->getAccount($storeAccounts['payable']);

    $accountData = [
        'title' => 'Supplier - ' . $_POST['supplierName'] . ' - ' . $_POST['company'],
        'code' => $payableAccount['code'],
        'account_type' => $payableAccount['account_type'],
        'group_id' => $payableAccount['group_id'],
        'status' => $payableAccount['status'],
        'parent_id' => $payableAccount['id'],
        'created_by' => $userData['id'],
        'shopId' => $shop['id'],
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
$status = $_POST['status'] ? $_POST['status'] : 2;
$purchaseValue = 0;
$productsValue = 0;
$demandProducts = [];
if (sizeof($_POST['items'])) {
    foreach ($_POST['items'] as $item) {
        if (!empty($item['id'])) {

            if ($isDemandCreate) {
                $demandProducts[] = [
                    'id' => $item['id'],
                    'qty' => $item['qty'],
                ];
            }

            $purchaseValue += ($item['pprice'] * $item['qty']);
            $productsValue += ($item['price'] * $item['qty']);
            $items[] = [
                'pprice' => $item['pprice'],
                'price' => $item['price'],
                'discount' => !empty($item['discount']) ? $item['discount'] : 0,
                'qty' => $item['qty'],
                'stock_out' => 0,
                'pin' => $item['pin'],
                'minQty' => $item['minQty'],
                'product_id' => $item['id'],
                'shopId' => $_POST['shopId'],
                'owner_id' => $ownerId,
            ];
        }
    }
}

if (sizeof($items)) {

    foreach ($items as $item) {
        $products->assignProduct($item);
        if (!empty($item['pin'])) {
            $products->setPriority($item['product_id'], 1);
        }
    }
}
$supply = new Supply();
$data = [
    'user_id' => $userData['id'],
    'supplier_id' => !empty($supplierId) ? $supplierId : 1,
    'status' => $status,
    'ref_no' => $_POST['ref_no'],
    'price' => $_POST['payable'],
    'payment_amount' => $cash,
    'payment_with_credit' => $payment_with_credit,
    'discount' => !empty($_POST['discount']) ? $_POST['discount'] : 0,
    'supplier_type' => $_POST['supplier_type'],
    'id' => $_POST['id'],
    'shopId' => $userData['shopId'],
    'supply_date' => $shop['sale_date']
];

if (!empty($_POST['id'])) {
    $de->deleteTransactionBySupplyId($_POST['id']);
}
$supply_id = $supply->createSupply($data);
$dd = 0;
if (sizeof($demandProducts)) {
    $demands = new Demands();
    $final = [
        'demand_title' => 'Created With Supply ID: ' . $supply_id,
        'demand_date' => $shop['sale_date'],
        'shop_id' => $shop['id'],
        'owner_id' => $shop['owner_id'],
        'created_by' => $userData['id'],
        'items' => $demandProducts,
    ];
    $dd = $demands->createDemand($final, false);
}

if ($supply_id) {
    if (sizeof($items)) {
        $supply->deleteSupplyDetails($supply_id);
        foreach ($items as $item) {
            $d = [
                'supply_id' => $supply_id,
                'product_id' => $item['product_id'],
                'quantity' => $item['qty'],
                'discount' => !empty($item['discount']) ? $item['discount'] : 0,
                'price' => $item['price'],
            ];
            $supply->createSupplyDetails($d);
        }
    }

    if ($status != 1) {

        $account_id = $_POST['account_id'];
        $credit_amount = !empty($_POST['payment_with_credit']) ? $_POST['payment_with_credit'] : 0;

        $makeTransaction = [
            'description' => !empty($_POST['summery']) ? $_POST['summery'] : "Supply Invoice: " . $supply_id . " PLACED",
            'transaction_date' => $storeDATA['sale_date'],
            'reference' => $data['ref_no'],
            'transaction_type' => !empty($credit_amount) ? 'EXCHANGE' : 'PURCHASE',
            'shopId' => $shop['id'],
            'created_by' => $_SESSION['user_credentials']['id'],
            'order_ref' => null,
            'supply_ref' => $supply_id,
        ];

        $makeTransactionId = $de->makeTransaction($makeTransaction);

        $totalDiscount = $productsValue - $purchaseValue;

        $assetPrice = $productsValue;
        $purchaseDiscount = $totalDiscount + $data['discount'];
        $payableAmount = $purchaseValue - $data['discount'];

        // assets debit entry - debit
        // liability payable entry - credit
        // purchase discount entry - credit
        $entry = [
            'transaction_id' => $makeTransactionId,
            'account_id' => $storeAccounts['assets'],
            'entry_type' => 'D',
            'description' => '',
            'amount' => $assetPrice, // 2000
            'payment_mode' => $_POST['payment_mode'],
            'user_id' => $_SESSION['user_credentials']['id'],
        ];

        $a[] = $de->makeEntry($entry);

        // payable credit entry
        $entry = [
            'transaction_id' => $makeTransactionId,
            'account_id' => $account_id,
            'entry_type' => 'C',
            'description' => '',
            'amount' => $payableAmount,
            'payment_mode' => $_POST['payment_mode'],
            'user_id' => $_SESSION['user_credentials']['id'],
        ];
        $a[] = $de->makeEntry($entry);

        if (!empty($purchaseDiscount)) {
            // saleDiscount credit entry
            $entry = [
                'transaction_id' => $makeTransactionId,
                'account_id' => $storeAccounts['purchase_discount'],
                'entry_type' => 'C',
                'description' => '',
                'amount' => $purchaseDiscount, // 200 @ 10%
                'payment_mode' => $_POST['payment_mode'],
                'user_id' => $_SESSION['user_credentials']['id'],
            ];
            $a[] = $de->makeEntry($entry);
        }


        if (!empty($cash)) {
            $makeTransactionId = $de->makeTransaction($makeTransaction);
            // payable credit entry
            $entry = [
                'transaction_id' => $makeTransactionId,
                'account_id' => $account_id,
                'entry_type' => 'D',
                'description' => '',
                'amount' => $cash,
                'payment_mode' => $_POST['payment_mode'],
                'user_id' => $_SESSION['user_credentials']['id'],
            ];
            $a[] = $de->makeEntry($entry);
            // cash credit entry
            $entry = [
                'transaction_id' => $makeTransactionId,
                'account_id' => $storeAccounts['cash'],
                'entry_type' => 'C',
                'description' => '',
                'amount' => $cash, // 200 @ 10%
                'payment_mode' => $_POST['payment_mode'],
                'user_id' => $_SESSION['user_credentials']['id'],
            ];
            $a[] = $de->makeEntry($entry);
        }
    }

    echo json_encode(['status' => 200, 'message' => 'successfully done', 'supply' => ['id' => $supply_id]]);
}
