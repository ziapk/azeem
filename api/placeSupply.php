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
        'email' => "",
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

$status = 9;
if ($_POST['status'] == 1) {
    $status = 1; // parked
} else if (!empty($_POST['payment_amount'])) {
    $status = 8;
    if ((($_POST['payable']) - $_POST['discount'] - $_POST['payment_amount']) == 0) {
        $status = 2;
    }
}

$purchaseValue = 0;
$productsValue = 0;
$fixAssetsValue = 0;
$fixAssetsPurchaseValue = 0;
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

            if ($item['product_type'] == 4) {
                $fixAssetsValue += ($item['price'] * $item['qty']);
                $fixAssetsPurchaseValue += ($item['pprice'] * $item['qty']);
            } else {
                $productsValue += ($item['price'] * $item['qty']);
                $purchaseValue += ($item['pprice'] * $item['qty']);
            }
            $items[] = [
                'pprice' => $item['pprice'],
                'price' => $item['price'],
                'discount' => !empty($item['discount']) ? $item['discount'] : 0,
                'qty' => $item['qty'],
                'pack_size' => !empty($item['pack_size']) ? $item['pack_size'] : 0,
                'pack_qty' => !empty($item['pack_qty']) ? $item['pack_qty'] : 0,
                'unpack_qty' => !empty($item['unpack_qty']) ? $item['unpack_qty'] : 0,
                'stock_out' => 0,
                'pin' => $item['pin'],
                'minQty' => $item['minQty'],
                'full_name' => $item['full_name'],
                'product_id' => $item['id'],
                'shopId' => $_POST['shopId'],
                'owner_id' => $ownerId,
            ];
        }
    }
}

$supply = new Supply();

if (!empty($_POST['id'])) {
    $orderDetail = $supply->getOrder($_POST['id']);
    // rollback products first
    foreach ($orderDetail['order_items'] as $prod) {
        $products->addProductQty($prod['product_id'], ['qty' => -1 * $prod['quantity'], 'pack_size' => $prod['pack_size'], 'pack_qty' => -1 * $prod['pack_qty']], $orderDetail['order']['shopId']);
    }
    // delete transactions
    $de->deleteTransactionBySupplyId($orderDetail['order']['id']);
}

if (sizeof($items)) {

    foreach ($items as $item) {
        $products->assignProduct($item);
        if (!empty($item['pin'])) {
            $products->setPriority($item['product_id'], 1);
        }
    }
}

$supplier = $supplierObj->getSupplier($supplierId);
$data = [
    'user_id' => $userData['id'],
    'supplier_id' => !empty($supplierId) ? $supplierId : 1,
    'status' => $status,
    'ref_no' => $_POST['ref_no'],
    'show_bundle' => !empty($_POST['show_bundle']) ? 1 : 0,
    'description' => !empty($_POST['description']) ? $_POST['description'] : (!empty($_POST['summery']) ? $_POST['summery'] : ''),
    'price' => $_POST['payable'],
    'payment_amount' => $cash,
    'payment_with_credit' => $payment_with_credit,
    'discount' => !empty($_POST['discount']) ? $_POST['discount'] : 0,
    'supplier_type' => $_POST['supplier_type'],
    'id' => $_POST['id'],
    'shopId' => $userData['shopId'],
    'supply_date' => $shop['sale_date']
];

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
                'product_title' => $item['full_name'],
                'quantity' => $item['qty']  + (!empty($item['unpack_qty']) ? $item['unpack_qty'] : 0),
                'discount' => !empty($item['discount']) ? $item['discount'] : 0,
                'price' => $item['price'],
                'pprice' => $item['pprice'],
                'pack_size' => !empty($item['pack_size']) ? $item['pack_size'] : 0,
                'pack_qty' => !empty($item['pack_qty']) ? $item['pack_qty'] : 0,
                'unpack_qty' => !empty($item['unpack_qty']) ? $item['unpack_qty'] : 0,
                'unpack_qty' => !empty($item['unpack_qty']) ? $item['unpack_qty'] : 0,
            ];
            $supply->createSupplyDetails($d);
        }
    }

    if ($status != 1) {

        $account_id = $_POST['account_id'];
        $credit_amount = !empty($_POST['payment_with_credit']) ? $_POST['payment_with_credit'] : 0;

        $makeTransaction = [
            'description' => !empty($data['description']) ? $data['description'] : "Supply Invoice: " . $supply_id . " PLACED",
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
        $totalDiscount += $fixAssetsValue - $fixAssetsPurchaseValue;

        $assetPrice = $productsValue;
        $assetFAPrice = $fixAssetsValue;

        $purchaseDiscount = $totalDiscount + $data['discount'];
        $payableAmount = ($purchaseValue + $fixAssetsValue) - $data['discount'];

        // assets debit entry - debit
        // liability payable entry - credit
        // purchase discount entry - credit
        if (!empty($assetPrice)) {
            $entry = [
                'transaction_id' => $makeTransactionId,
                'account_id' => $storeAccounts['assets'],
                'entry_type' => 'D',
                'description' => !empty($data['description']) ? $data['description'] : '',
                'amount' => $assetPrice, // 2000
                'payment_mode' => $_POST['payment_mode'],
                'user_id' => $_SESSION['user_credentials']['id'],
            ];
            $a[] = $de->makeEntry($entry);
        }

        if (!empty($fixAssetsValue)) {
            $entry = [
                'transaction_id' => $makeTransactionId,
                'account_id' => $storeAccounts['fix_assets'],
                'entry_type' => 'D',
                'description' => !empty($data['description']) ? $data['description'] : '',
                'amount' => $fixAssetsValue, // 2000
                'payment_mode' => $_POST['payment_mode'],
                'user_id' => $_SESSION['user_credentials']['id'],
            ];
            $a[] = $de->makeEntry($entry);
        }


        // payable credit entry
        $entry = [
            'transaction_id' => $makeTransactionId,
            'account_id' => $account_id,
            'entry_type' => 'C',
            'description' => !empty($data['description']) ? $data['description'] : '',
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
                'description' => !empty($data['description']) ? $data['description'] : '',
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
        $newsletter = new Newsletter();
        try {
            $send = $newsletter->send([
                'subject' => $makeTransaction['description'],
                'body' => $newsletter->drawSupply($supply_id),
                'sentTo' => [['email' => !empty($supplier['email']) ? $supplier['email'] : 'zia.pccr@yahoo.com', 'name' => !empty($_POST['supplierName']) ? $_POST['supplierName'] : $supplier['name']]],
                'ccEmails' => [['email' => $shop['company_email'], 'name' => $shop['full_name']]],
                'client' => $shop['full_name'],
                'labels' => [$makeTransaction['transaction_type']]
            ]);
        } catch (Exception $e) {
            print_r($e);
        }
    }

    echo json_encode(['status' => 200, 'message' => 'successfully done', 'supply' => ['id' => $supply_id]]);
}
