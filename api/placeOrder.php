<?php
include_once dirname(__FILE__) . '/../include/settings.php';
try {
    global $shop;
    $orders = new Orders();
    $storeObj = new Store();
    $customersObj = new Customers();
    $storeDATA = $storeObj->getStore($shop['id']);
    $shopAccounts = new ShopAccounts();
    $accountsData = $shopAccounts->getSAs($shop['id']);
    $storeAccounts = [];
    foreach ($accountsData as $a) {
        $storeAccounts[$a['key_value']] = $a['account_id'];
    }

    $status = 9;
    $totalDiscount = 0;
    $additionalDiscount = 0;
    $parked = false;
    if ($_POST['status'] == 1) {
        $status = 1; // parked
        $parked = true;
    } else if (!empty($_POST['payment_amount'])) {
        $gst = round($_POST['subTotal'] * ($_POST['gst'] / 100));
        $service_charges = round($_POST['subTotal'] * ($_POST['service_charges'] / 100));
        $status = 8;
        if ((($_POST['subTotal'] + $gst + $service_charges) - $_POST['discount'] - $_POST['payment_amount']) == 0) {
            $status = 2;
        }
    }

    $data = [
        'user_id' => $_SESSION['user_credentials']['id'],
        'customer_name' => $_POST['customer_name'],
        'customer_id' => !empty($_POST['customerId']) ? $_POST['customerId'] : 1,
        'status' => $status,
        'price' => $_POST['subTotal'],
        'paid_amount' => $_POST['payment_amount'],
        'show_discount' => !empty($_POST['show_discount']) ? 1 : 0,
        'discount' => $_POST['discount'],
        'gst' => $_POST['gst'],
        'service_charges' => !empty($_POST['service_charges']) ? $_POST['service_charges'] : 0,
        'shopId' => !empty($_POST['shopId']) ? $_POST['shopId'] : $userData['shopId'],
        'order_date' => $storeDATA['sale_date'],
        'summery' => $_POST['summery'],
        'ref_no' => $_POST['ref_no'],
        'id' => $_POST['id'],
        'status_id' => !empty($_POST['status_id']) ? $_POST['status_id'] : null,
        'expected_delivery_date' => !empty($_POST['expected_delivery_date']) ? $_POST['expected_delivery_date'] : null,
    ];

    $additionalDiscount += $_POST['discount'];

    $doubleEntry = new DoubleEntry();

    if (!empty($_POST['id'])) {
        $orderDetail = $orders->getOrder($_POST['id']);
        $currentStatus = $orderDetail['order']['status'];
        if (in_array($orderDetail['order']['status'], [2, 8, 9])) {

            // rollback products first
            $products = new Products();
            foreach ($orderDetail['order_items'] as $prod) {
                $products->addProductQty($prod['product_id'], $prod['quantity'], $orderDetail['order']['shopId']);
            }

            // delete transactions
            $doubleEntry->deleteTransactionByOrderId($orderDetail['order']['id']);
        }
    }
    $order_id = $orders->createOrder($data);

    if ($status == 1 && !empty($_POST['id'])) { // when edit a parked entry
        $order_id = $_POST['id'];
    }
    if ($order_id) {
        $items = [];
        if (sizeof($_POST['items'])) {
            $orders->deleteOrderItems($order_id);
            $orders->deleteOrderServices($order_id);

            $c = [];
            foreach ($_POST['items'] as $item) {
                $d = [
                    'shopId' => $userData['shopId'],
                    'owner_id' => $userData['role'] == 'owner' ? $userData['id'] : $userData['created_by'],
                    'product_id' => $item['id'],
                    'order_id' => $order_id,
                    'description' => $item['description'],
                    'quantity' => $item['qty'],
                    'discount' => !empty($item['discount']) ? $item['discount'] : 0,
                    'price' => $item['price'],
                    'services' => $item['services'],
                    'raw_items' => $item['raw_items'],
                    'status' => $staus,
                    'item_status' => !empty($item['item_status']) ? $item['item_status'] : 1,
                    'employee_id' => !empty($item['employee_id']) ? $item['employee_id'] : null,
                    'start_date' => !empty($item['start_date']) ? $item['start_date'] : null,
                    'end_date' => !empty($item['end_date']) ? $item['end_date'] : null,
                    'priority' => !empty($item['priority']) ? $item['priority'] : 1,
                ];
                $totalDiscount += $item['discount'];
                $c[] = $orders->createOrderDetails($d);
            }
        }

        if ($status != 1) {

            $customer = $customersObj->getCustomer($data['customer_id']);

            $makeTransaction = [
                'description' => !empty($_POST['summery']) ? $_POST['summery'] : "ORDER ID: " . $order_id . " PLACED",
                'transaction_date' => $storeDATA['sale_date'],
                'reference' => !empty($_POST['ref_no']) ? $_POST['ref_no'] : '',
                'transaction_type' => 'SALE',
                'shopId' => $shop['id'],
                'created_by' => $_SESSION['user_credentials']['id'],
                'order_ref' => $order_id,
                'supply_ref' => null,
            ];

            $makeTransactionId = $doubleEntry->makeTransaction($makeTransaction);

            $assetPrice = $data['price'] + $totalDiscount;
            $cash = $data['paid_amount'];
            $saleDiscount = $totalDiscount + $additionalDiscount;
            $receivable = ($assetPrice - $saleDiscount);
            $defaultId = 0;
            foreach ($_POST['payment_with'] as $value) {
                if (!empty($value['is_default'])) {
                    $defaultId = $value['id'];
                }
            }

            // assets debit entry - credit
            // assets receivable entry - debit
            // expense discount entry - debit

            // receivable credit entry
            $entry = [
                'transaction_id' => $makeTransactionId,
                'account_id' => $customer['account_id'],
                'entry_type' => 'D',
                'description' => '',
                'amount' => $receivable,
                'payment_mode' => $defaultId,
                'user_id' => $_SESSION['user_credentials']['id'],
            ];
            $a[] = $doubleEntry->makeEntry($entry);

            if (!empty($saleDiscount)) {
                // saleDiscount credit entry
                $entry = [
                    'transaction_id' => $makeTransactionId,
                    'account_id' => $storeAccounts['sale_discount'],
                    'entry_type' => 'D',
                    'description' => '',
                    'amount' => $saleDiscount, // 200 @ 10%
                    'payment_mode' => $defaultId,
                    'user_id' => $_SESSION['user_credentials']['id'],
                ];
                $a[] = $doubleEntry->makeEntry($entry);
            }

            $entry = [
                'transaction_id' => $makeTransactionId,
                'account_id' => $storeAccounts['assets'],
                'entry_type' => 'C',
                'description' => '',
                'amount' => $assetPrice, // 2000
                'payment_mode' => $defaultId,
                'user_id' => $_SESSION['user_credentials']['id'],
            ];

            $a[] = $doubleEntry->makeEntry($entry);

            if (!empty($cash)) {
                $makeTransactionId = $doubleEntry->makeTransaction($makeTransaction);
                foreach ($_POST['payment_with'] as $value) {
                    if (!empty($value['amount'])) {
                        // cash credit entry
                        $entry = [
                            'transaction_id' => $makeTransactionId,
                            'account_id' => $storeAccounts['cash'],
                            'entry_type' => 'D',
                            'description' => '',
                            'amount' => $value['amount'], // 200 @ 10%
                            'payment_mode' => $value['id'],
                            'user_id' => $_SESSION['user_credentials']['id'],
                        ];
                        $a[] = $doubleEntry->makeEntry($entry);

                        // receivable credit entry
                        $entry = [
                            'transaction_id' => $makeTransactionId,
                            'account_id' => $customer['account_id'],
                            'entry_type' => 'C',
                            'description' => '',
                            'amount' => $value['amount'],
                            'payment_mode' => $value['id'],
                            'user_id' => $_SESSION['user_credentials']['id'],
                        ];
                        $a[] = $doubleEntry->makeEntry($entry);
                    }
                }
            }
        }

        echo json_encode(['status' => 200, 'message' => 'successfully done', 'order' => ['id' => $order_id]]);
    }
} catch (PDOException $e) {
    die("Error!: " . $e->getMessage() . "<br/>");
}
