<?php
include_once dirname(__FILE__) . '/../include/settings.php';
$orders = new Orders();
if (!empty($_GET['id'])) {
    $order = $orders->getOrder($_GET['id']);
}
$action = $_GET['action'];
if (empty($order) || empty($action) || empty($_GET['id'])) {
    json_encode(['status' => 402, 'message' => 'Invalid data']);
    exit;
}

$o = $order['order'];
$oitems = $order['order_items'];

$amount = 0;
if ($action == 1 || $action == 2) {
    echo $action;
    foreach ($oitems as $row) {
        $data = [
            'order_id' => $row['order_id'],
            'shopId' => $o['shopId'],
            'type' => $action,
            'user_id' => $userData['id'],
            'product_id' => $row['product_id'],
            'quantity' => $row['quantity'],
        ];
        // manage product qty
        $amount += ($row['price'] * $row['quantity']);
        $orders->orderReturnAll($data, $action, $action);
    }
    echo json_encode(['status' => 200, 'message' => 'Successfully Done!']);
}

if ($action == 3) {
    $items = json_decode($_GET['items'], true);
    if (empty($items)) {
        echo json_encode(['status' => 402, 'message' => 'Invalid data']);
    } else {
        foreach ($items as $key => $row) {
            $rr = [];
            foreach ($oitems as $r) {
                if ($r['id'] == $key) {
                    $rr = $r;
                }
            }
            if (!empty($row['inventory'])) {
                $data = [
                    'order_id' => $_GET['id'],
                    'type' => 1,
                    'user_id' => $userData['id'],
                    'product_id' => $key,
                    'quantity' => $row['inventory'],
                ];
                $orders->orderReturnAll($data, $action, 1);
                $amount += ($r['price'] * $row['inventory']);
            }
            if (!empty($row['faulty'])) {
                $data = [
                    'order_id' => $_GET['id'],
                    'type' => 2,
                    'user_id' => $userData['id'],
                    'product_id' => $key,
                    'quantity' => $row['faulty'],
                ];
                $orders->orderReturnAll($data, $action, 2);
                $amount += ($r['price'] * $row['faulty']);
            }
        }
        // manage wallat
        $transaction = [
            'customer_id' => $o['customer_id'],
            'user_id' => $userData['id'],
            'amount' => -1 * ($amount - $o['discount']),
            'payment_date' => date('Y-m-d H:i:s'),
            'order_id' => $o['id'],
            'shopId' => $userData['shopId']
        ];

        $transactionId = $orders->makeTransaction($transaction);


        $doubleEntry = new DoubleEntry();

        $makeTransaction = [
            'description' => !empty($_POST['summery']) ? $_POST['summery'] : "Sale Return PLACED",
            'transaction_date' => $storeDATA['sale_date'],
            'reference' => $_POST['ref_no'],
            'shopId' => $shopId,
            'created_by' => $userData['id'],
            'order_ref' => $o['id'],
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
            'payment_mode' => $_POST['payment_mode'],
            'user_id' => $userData['id'],
        ];

        $a[] = $doubleEntry->makeEntry($entry);


        $entry = [
            'transaction_id' => $makeTransactionId,
            'account_id' => $supplier['account_id'],
            'entry_type' => 'C',
            'description' => '',
            'amount' => $returnAmount,
            'payment_mode' => $_POST['payment_mode'],
            'user_id' => $userData['id'],
        ];

        $a[] = $doubleEntry->makeEntry($entry);

        if (!empty($saleDiscount)) {
            // saleDiscount credit entry
            $entry = [
                'transaction_id' => $makeTransactionId,
                'account_id' => $storeDATA['sale_discount'],
                'entry_type' => 'C',
                'description' => '',
                'amount' => $saleDiscount, // 200 @ 10%
                'payment_mode' => $_POST['payment_mode'],
                'user_id' => $userData['id'],
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
                'account_id' => $storeDATA['sale_returns'],
                'entry_type' => 'C',
                'description' => '',
                'amount' => $payment_amount, // 200 @ 10%
                'payment_mode' => $_POST['payment_mode'],
                'user_id' => $_SESSION['user_credentials']['id'],
            ];
            $a[] = $doubleEntry->makeEntry($entry);
        }

        echo json_encode(['status' => 200, 'message' => 'successfully done', 'supply' => ['id' => $makeTransactionId]]);
    }
}
