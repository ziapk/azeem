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
$isDemandCreate = !empty($_POST['createDemand']) && $_POST['createDemand'] == "true";
$payment_with_credit = !empty($_POST['payment_with_credit']) ? $_POST['payment_with_credit'] : 0;
$de = new DoubleEntry();
$payment_mode = $de->getDefaultPaymentMode($_POST);
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
            if(!empty($item['update'])) {
                $products->updateProductPPrice(['product_id' => $item['id'], 'pprice' => $item['pprice']]);
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
            if(!empty($_POST['code'])) {
                $arr['code'] = $_POST['code'];
                $arr['product_id'] = $item['id'];
                $products->createProductCode($arr);
            }
        }
    }
}

$supply = new Supply();

$overide = $_POST['overide'];
$saleDate = $shop['sale_date'];

if (!empty($_POST['id'])) {
    $orderDetail = $supply->getOrder($_POST['id']);
    $currentStatus = $orderDetail['order']['status'];

    $saleDate = !empty($overide) ? $orderDetail['order']['supply_date'] : $storeDATA['sale_date'];

    $remainingOldSupplyQty = [];
    // Only carry forward old quantities when the supply was already active (had inventory logged).
    // Parked supplies (status=1) have no ledger entries, so all items must be treated as new.
    if ($currentStatus != 1) {
        foreach ($orderDetail['order_items'] as $item) {
            $qty = (float)$item['quantity'];
            if ($qty > 0) {
                $remainingOldSupplyQty[$item['product_id']] = ($remainingOldSupplyQty[$item['product_id']] ?? 0) + $qty;
            }
        }
    }

    if (in_array($orderDetail['order']['status'], [2, 8, 9])) {
        $inventory = new Inventory();

        if ($status == 1) {
            // ── INVENTORY: reverse entire supply when moving to park/draft — no new inventory should remain.
            $inventory->reverseByRef(
                Inventory::REF_SUPPLY,
                (int)$orderDetail['order']['id'],
                (int)$ownerId,
                'Rollback before re-processing supply #' . $orderDetail['order']['id']
            );
        } else {
            $oldEntries = [];
            foreach ($orderDetail['order_items'] as $item) {
                $qty = (float)$item['quantity'];
                if ($qty <= 0) {
                    continue;
                }
                $oldEntries[] = [
                    'product_id' => $item['product_id'],
                    'shop_id' => (int)$_POST['shopId'],
                    'movement_type' => Inventory::SUPPLY,
                    'quantity' => $qty,
                ];
            }

            $newEntries = [];
            foreach ($items as $item) {
                $qty = (float)$item['qty'] + (!empty($item['unpack_qty']) ? (float)$item['unpack_qty'] : 0);
                if ($qty <= 0) {
                    continue;
                }
                $newEntries[] = [
                    'product_id' => $item['product_id'],
                    'shop_id' => (int)$_POST['shopId'],
                    'movement_type' => Inventory::SUPPLY,
                    'quantity' => $qty,
                ];
            }

            $oldMap = [];
            foreach ($oldEntries as $entry) {
                $key = $entry['product_id'] . '|' . $entry['shop_id'] . '|' . $entry['movement_type'];
                $oldMap[$key] = ($oldMap[$key] ?? 0) + $entry['quantity'];
            }
            $newMap = [];
            foreach ($newEntries as $entry) {
                $key = $entry['product_id'] . '|' . $entry['shop_id'] . '|' . $entry['movement_type'];
                $newMap[$key] = ($newMap[$key] ?? 0) + $entry['quantity'];
            }

            foreach ($oldMap as $key => $oldQty) {
                $newQty = $newMap[$key] ?? 0;
                if ($oldQty > $newQty) {
                    list($product_id, $shop_id, $movement_type) = explode('|', $key);
                    $inventory->logMovement([
                        'product_id' => (int)$product_id,
                        'shop_id' => (int)$shop_id,
                        'owner_id' => (int)$ownerId,
                        'movement_type' => $inventory->invertMovementType($movement_type),
                        'quantity' => (float)($oldQty - $newQty),
                        'ref_type' => Inventory::REF_SUPPLY,
                        'ref_id' => (int)$orderDetail['order']['id'],
                        'note' => 'Partial rollback before re-processing supply #' . $orderDetail['order']['id'],
                        'created_by' => (int)$ownerId,
                    ]);
                }
            }
        }

        // delete transactions
        $de->deleteTransactionBySupplyId($orderDetail['order']['id']);
    }
}

if (sizeof($items)) {

    foreach ($items as &$item) {
        $qty = (float)$item['qty'] + (!empty($item['unpack_qty']) ? (float)$item['unpack_qty'] : 0);
        $inventoryQuantity = 0;
        if (!empty($remainingOldSupplyQty[$item['product_id']])) {
            if ($qty > $remainingOldSupplyQty[$item['product_id']]) {
                $inventoryQuantity = $qty - $remainingOldSupplyQty[$item['product_id']];
                $remainingOldSupplyQty[$item['product_id']] = 0;
            } else {
                $remainingOldSupplyQty[$item['product_id']] -= $qty;
                $inventoryQuantity = 0;
            }
        } else {
            $inventoryQuantity = $qty;
        }
        $item['inventory_quantity'] = $inventoryQuantity;

        $products->assignProduct($item);
        if (!empty($item['pin'])) {
            $products->setPriority($item['product_id'], 1);
        }
    }
    unset($item);
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
    'supply_date' => $saleDate
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
    $dd = $demands->createDemand($final, false, $shop['id']);
}

if ($supply_id) {
    if (sizeof($items)) {
        $supply->deleteSupplyDetails($supply_id);
        $inventory = new Inventory();
        foreach ($items as $item) {
            $totalQty = $item['qty']  + (!empty($item['unpack_qty']) ? $item['unpack_qty'] : 0);
            $d = [
                'supply_id' => $supply_id,
                'product_id' => $item['product_id'],
                'product_title' => $item['full_name'],
                'quantity' => $totalQty,
                'discount' => !empty($item['discount']) ? $item['discount'] : 0,
                'price' => $item['price'],
                'pprice' => $item['pprice'],
                'pack_size' => !empty($item['pack_size']) ? $item['pack_size'] : 0,
                'pack_qty' => !empty($item['pack_qty']) ? $item['pack_qty'] : 0,
                'unpack_qty' => !empty($item['unpack_qty']) ? $item['unpack_qty'] : 0,
            ];
            $supply->createSupplyDetails($d);

            if ($status != 1 && !empty($item['inventory_quantity'])) {
                // ── INVENTORY: log stock IN for this supply item ──
                $inventory->logMovement([
                    'product_id'    => (int)$item['product_id'],
                    'shop_id'       => (int)$item['shopId'],
                    'owner_id'      => (int)$item['owner_id'],
                    'movement_type' => Inventory::SUPPLY,
                    'quantity'      => (float)$item['inventory_quantity'],
                    'ref_type'      => Inventory::REF_SUPPLY,
                    'ref_id'        => (int)$supply_id,
                    'note'          => 'Supply #' . $supply_id,
                    'created_by'    => (int)$userData['id'],
                ]);
            }
        }

        // ── INVENTORY: reconcile the cached store_products.qty against the ledger
        // for EVERY product in this supply. logMovement()/reverseByRef() only fire
        // for changed quantities, so an item whose qty was unchanged (or a parked
        // supply) would otherwise keep the inflated value written by the legacy
        // assignProduct() += above — that is the "++ on every update" bug. syncQty
        // sets qty = SUM(ledger), which is the single source of truth.
        $reconciled = [];
        foreach ($items as $item) {
            $key = $item['product_id'] . '|' . $item['shopId'];
            if (isset($reconciled[$key])) {
                continue;
            }
            $reconciled[$key] = true;
            $inventory->syncQty((int)$item['product_id'], (int)$item['shopId']);
        }
    }

    if ($status != 1) {

        $account_id = $_POST['account_id'];
        $credit_amount = !empty($_POST['payment_with_credit']) ? $_POST['payment_with_credit'] : 0;

        $makeTransaction = [
            'description' => !empty($data['description']) ? $data['description'] : "Supply Invoice: " . $supply_id . " PLACED",
            'transaction_date' => $saleDate,
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
                'payment_mode' => $payment_mode['id'],
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
                'payment_mode' => $payment_mode['id'],
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
            'payment_mode' => $payment_mode['id'],
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
                'payment_mode' => $payment_mode['id'],
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
                'payment_mode' => $payment_mode['id'],
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
                'payment_mode' => $payment_mode['id'],
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
