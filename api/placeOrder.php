<?php 
include_once dirname(__FILE__).'/../include/settings.php';
try {
    global $shop;
    $orders = new Orders();
    $storeObj = new Store();
    $customersObj = new Customers();
    $storeDATA = $storeObj->getStore($shop['id']);
    $status = 9;
    $totalDiscount = 0;
    $additionalDiscount = 0;
    $parked = false;
    if($_POST['status'] == 1) {
        $status = 1; // parked
        $parked = true;
    }
    else if(!empty($_POST['payment_amount'])) {
        $gst = round($_POST['subTotal'] * ($_POST['gst'] / 100));
        $service_charges = round($_POST['subTotal'] * ($_POST['service_charges'] / 100));
        $status = 8;
        if((($_POST['subTotal'] + $gst + $service_charges) - $_POST['discount'] - $_POST['payment_amount']) == 0) {
            $status = 2;
        }
    }

    $data = [
        'user_id' => $_SESSION['user_credentials']['id'],
        'customer_id' => !empty($_POST['customerId']) ? $_POST['customerId'] : 1,
        'status' => $status,
        'price' => $_POST['subTotal'],
        'paid_amount' => $_POST['payment_amount'],
        'show_discount' => !empty($_POST['show_discount']) ? 1 : 0,
        'discount' => $_POST['discount'],
        'gst' => $_POST['gst'],
        'service_charges' => !empty($_POST['service_charges']) ? $_POST['service_charges'] : 0,
        'shopId' => $userData['shopId'],
        'order_date' => $storeDATA['sale_date'],
        'summery' => $_POST['summery'],
        'ref_no' => $_POST['ref_no'],
        'id' => $_POST['id'],
    ];

    $additionalDiscount += $_POST['discount'];

    $order_id = $orders->createOrder($data);

    if($status == 1 && !empty($_POST['id'])) { // when edit a parked entry
        $order_id = $_POST['id'];
    }

    if($order_id) {
        $items = [];
        if(sizeof($_POST['items'])) {
            $orders->deleteOrderItems($order_id);
            foreach ($_POST['items'] as $item) {
                $d = [
                    'shopId' => $userData['shopId'],
                    'owner_id' => $userData['role'] == 'owner' ? $userData['id'] : $userData['created_by'],
                    'product_id' => $item['id'],
                    'order_id' => $order_id,
                    'description' => $item['description'],
                    'quantity' => $item['qty'],
                    'discount' => $item['discount'],
                    'price' => $item['price'],
                    'status' => $staus,
                ];
                $totalDiscount += $item['discount'];
                $orders->createOrderDetails($d);
            }
        }

        if($status != 1) {

            $customer = $customersObj->getCustomer($data['customer_id']);
            $doubleEntry = new DoubleEntry();

            $makeTransaction = [
                'description' => !empty($_POST['summery']) ? $_POST['summery'] : "ORDER ID: ".$order_id." PLACED",
                'transaction_date' => $storeDATA['sale_date'],
                'reference' => $_POST['ref_no'],
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

            // assets debit entry - credit
            // assets receivable entry - debit
            // expense discount entry - debit
            $entry = [
                'transaction_id' => $makeTransactionId,
                'account_id' => $shop['assets'],
                'entry_type' => 'C',
                'description' => '',
                'amount' => $assetPrice, // 2000
                'payment_mode'=> $_POST['payment_mode'],
                'user_id' => $_SESSION['user_credentials']['id'],
            ];

            $a[] = $doubleEntry->makeEntry($entry);

            // receivable credit entry
            $entry = [
                'transaction_id' => $makeTransactionId,
                'account_id' => $customer['account_id'],
                'entry_type' => 'D',
                'description' => '',
                'amount' => $receivable,
                'payment_mode'=> $_POST['payment_mode'],
                'user_id' => $_SESSION['user_credentials']['id'],
            ];
            $a[] = $doubleEntry->makeEntry($entry);

            if(!empty($saleDiscount)) {
                // saleDiscount credit entry
                $entry = [
                    'transaction_id' => $makeTransactionId,
                    'account_id' => $shop['sale_discount'],
                    'entry_type' => 'D',
                    'description' => '',
                    'amount' => $saleDiscount, // 200 @ 10%
                    'payment_mode'=> $_POST['payment_mode'],
                    'user_id' => $_SESSION['user_credentials']['id'],
                ];
                $a[] = $doubleEntry->makeEntry($entry);
            }

            
            if(!empty($cash)) {
                $makeTransactionId = $doubleEntry->makeTransaction($makeTransaction);
                // cash credit entry
                $entry = [
                    'transaction_id' => $makeTransactionId,
                    'account_id' => $shop['cash'],
                    'entry_type' => 'D',
                    'description' => '',
                    'amount' => $cash, // 200 @ 10%
                    'payment_mode'=> $_POST['payment_mode'],
                    'user_id' => $_SESSION['user_credentials']['id'],
                ];
                $a[] = $doubleEntry->makeEntry($entry);
                
                // receivable credit entry
                $entry = [
                    'transaction_id' => $makeTransactionId,
                    'account_id' => $customer['account_id'],
                    'entry_type' => 'C',
                    'description' => '',
                    'amount' => $cash,
                    'payment_mode'=> $_POST['payment_mode'],
                    'user_id' => $_SESSION['user_credentials']['id'],
                ];
                $a[] = $doubleEntry->makeEntry($entry);
            }
        }



        // $manageWallet = $orders->manageWallet($wallet);
        /* $productUpdated = [];
        foreach ($items as $value) {
            $productUpdated[] = $products->assignProduct($value);
        }
    */

        echo json_encode(['status' => 200, 'message' => 'successfully done', 'order' => [ 'id'=> $order_id ]]);
    }
} catch (PDOException $e) {
    die("Error!: " . $e->getMessage() . "<br/>");
}
?>