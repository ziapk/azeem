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
$supplierId = $_POST['supplierId'];


$products = new Products();
$orders = new Supply();

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


$productIds = [];
foreach ($_POST['items'] as $key => $value) {
    $productIds[$value['product_id'] . '_' . $value['price']] = ['qty' => $value['qty'], 'product_id' => $value['product_id'], 'price' => $value['price']];
}


$orderDetails = $orders->getSupplyItemsByProductIds(array_keys($productIds), $supplierId);
$or = [];
$oi = [];
foreach ($productIds as $key => $row) {
    $qty = $row['qty'];
    $remaining = $qty;
    $id = $row['product_id'];
    foreach ($orderDetails as $key => $value) {
        if (!empty($remaining)) {
            $oi[$value['supply_id']]['total'] += 1;
            if (!empty($remaining) && $value['product_id'] == $id && $value['quantity'] <= $remaining) { // full return products
                $remaining -= $value['quantity'];
                $oi[$value['supply_id']]['full'] += 1;
                $oi[$value['supply_id']]['full_items'][$id] = ['quantity' => $value['quantity'], 'price' => $value['price']];
            } elseif (!empty($remaining) &&  $value['product_id'] == $id && $value['quantity'] > $remaining) { // partial return products
                // $diff = $value['quantity'] - $remaining;
                $oi[$value['supply_id']]['partial'] += 1;
                $oi[$value['supply_id']]['partial_items'][$id] = ['quantity' => $remaining, 'price' => $value['price']];;
                $remaining = 0;
            }
        }
    }
}

$res = [];
foreach ($oi as $id => $row) {
    $t = $row['total'];
    $t -= $row['full'];

    if ($t == 0) {
        echo 'full';
        if (!empty($row['full_items'])) {
            foreach ($row['full_items'] as $product_id => $value) {
                $data = [
                    'supply_id' => $id,
                    'shopId' => $shopId,
                    'type' => 1, // back to supplier
                    'user_id' => $userData['id'],
                    'product_id' => $product_id,
                    'quantity' => $value['quantity'],
                    'price' => $value['price'],
                ];
                $res[$product_id][] = $orders->orderReturnAll($data, 1);
            }
        }
        // full return here
    } else {
        echo 'partial';
        // partial
        if (!empty($row['partial_items'])) {
            foreach ($row['partial_items'] as $product_id => $value) {
                $data = [
                    'order_id' => $id,
                    'shopId' => $shopId,
                    'type' => 1, // back to supplier
                    'user_id' => $userData['id'],
                    'product_id' => $product_id,
                    'quantity' => $value['quantity'],
                    'price' => $value['price'],
                ];
                $res[$product_id][] = $orders->orderReturnAll($data, 1);
            }
        }

        if (!empty($row['full_items'])) {
            foreach ($row['full_items'] as $product_id => $value) {
                $data = [
                    'order_id' => $id,
                    'shopId' => $shopId,
                    'type' => 1, // back to supplier
                    'user_id' => $userData['id'],
                    'product_id' => $product_id,
                    'quantity' => $value['quantity'],
                    'price' => $value['price'],
                ];
                $res[$product_id][] = $orders->orderReturnAll($data, 1, 1);
            }
        }
    }
}

// $data = [];
// if(sizeof($_POST['items'])) {
//     foreach($_POST['items'] as $item) {
//         $data[] = [
//             'order_id' => $item['order_id'],
//             'shopId' => $shopId,
//             'type' => 3,
//             'user_id' => $userData['id'],
//             'product_id' => $item['id'],
//             'quantity' => $item['qty'],
//         ];
//         // $orders->orderReturnAll($data, 1, 3);
//     }
// }
// print_r($data);
// // exit;
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
$supplier = $supplierObj->getSupplier($supplierId);
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
    'description' => !empty($_POST['summery']) ? $_POST['summery'] : "Purchase Return PLACED",
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
    'entry_type' => 'C',
    'description' => '',
    'amount' => $assetPrice, // 2000
    'payment_mode' => $_POST['payment_mode'],
    'user_id' => $_SESSION['user_credentials']['id'],
];

$a[] = $doubleEntry->makeEntry($entry);


$entry = [
    'transaction_id' => $makeTransactionId,
    'account_id' => $supplier['account_id'],
    'entry_type' => 'D',
    'description' => '',
    'amount' => $returnAmount,
    'payment_mode' => $_POST['payment_mode'],
    'user_id' => $_SESSION['user_credentials']['id'],
];

$a[] = $doubleEntry->makeEntry($entry);

// no record saved for now need to store discount values in supply items in next work

// if(!empty($saleDiscount)) {
//     // saleDiscount credit entry
//     $entry = [
//         'transaction_id' => $makeTransactionId,
//         'account_id' => $storeDATA['sale_discount'],
//         'entry_type' => 'C',
//         'description' => '',
//         'amount' => $saleDiscount, // 200 @ 10%
//         'payment_mode'=> $_POST['payment_mode'],
//         'user_id' => $_SESSION['user_credentials']['id'],
//     ];
//     $a[] = $doubleEntry->makeEntry($entry);
// }


// assets debit entry - debit
// liability payable entry - credit
// purchase discount entry - credit


if (!empty($payment_amount)) {
    // $makeTransactionId = $doubleEntry->makeTransaction($makeTransaction);
    // payable credit entry
    $entry = [
        'transaction_id' => $makeTransactionId,
        'account_id' => $supplier['account_id'],
        'entry_type' => 'C',
        'description' => '',
        'amount' => $payment_amount,
        'payment_mode' => $_POST['payment_mode'],
        'user_id' => $_SESSION['user_credentials']['id'],
    ];
    $a[] = $doubleEntry->makeEntry($entry);
    // cash credit entry
    $entry = [
        'transaction_id' => $makeTransactionId,
        'account_id' => $storeDATA['purchase_returns'],
        'entry_type' => 'D',
        'description' => '',
        'amount' => $payment_amount, // 200 @ 10%
        'payment_mode' => $_POST['payment_mode'],
        'user_id' => $_SESSION['user_credentials']['id'],
    ];
    $a[] = $doubleEntry->makeEntry($entry);
}

echo json_encode(['status' => 200, 'message' => 'successfully done', 'supply' => ['id' => $makeTransactionId]]);
