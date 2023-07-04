<?php
include_once dirname(__FILE__) . '/../include/settings.php';
try {

    $accountId = $_POST['id'];
    $royaltyId = $_POST['royalty_id'];
    $mode = $_POST['mode'];
    $type = $_POST['type'];

    $storeObj = new Store();
    $storeDATA = $storeObj->getStore($shop['id']);
    $shopAccounts = new ShopAccounts();
    $accountsData = $shopAccounts->getSAs($shop['id']);
    $storeAccounts = [];
    foreach ($accountsData as $a) {
        $storeAccounts[$a['key_value']] = $a['account_id'];
    }

    $doubleEntry = new DoubleEntry();

    foreach ($mode as $mode_id => $amount) {



        $settings = [
            'summery' => !empty($royaltyId) ? 'ROYALTY PAYMENT' : 'DIRECT PAYMENT',
            'title' => !empty($royaltyId) ? 'ROYALTY PAYMENT' : 'DIRECT_PAYMENT',
            'payable' => 'D',
            'shop_account' => $storeAccounts['cash'],
            'type' => 'C'
        ];

        // royalty 
        if (!empty($royaltyId) && $amount > 0) {
            $royaltySettings = [
                'summery' => 'ROYALTY PAYMENT',
                'title' => 'ROYALTY PAYMENT',
                'payable' => 'D',
                'shop_account' => $storeAccounts['royalty_pay'],
                'type' => 'C'
            ];
        }

        if (!empty($royaltyId) && $amount < 0) {
            $amount *= -1;
            $royaltySettings = [
                'summery' => 'ROYALTY PAYMENT',
                'title' => 'ROYALTY PAYMENT',
                'payable' => 'C',
                'shop_account' => $storeAccounts['royalty_pay'],
                'type' => 'D'
            ];
        }

        if (!empty($_POST['adjustment']) && $amount < 0) {
            $amount *= -1;
            $settings = [
                'summery' => 'ADJUSTMENT',
                'title' => 'ADJUSTMENT',
                'payable' => 'C',
                'shop_account' => $storeAccounts['adjustment'],
                'type' => 'D'
            ];
        }

        if (!empty($_POST['adjustment']) && $amount > 0) {
            $settings = [
                'summery' => 'ADJUSTMENT',
                'title' => 'ADJUSTMENT',
                'payable' => 'D',
                'shop_account' => $storeAccounts['adjustment'],
                'type' => 'C'
            ];
        }

        $makeTransaction = [
            'description' => !empty($_POST['summery']) ? $_POST['summery'] : $settings['summery'],
            'transaction_date' => $storeDATA['sale_date'],
            'reference' => !empty($_POST['ref_no']) ? $_POST['ref_no'] : '',
            'transaction_type' => $settings['title'],
            'shopId' => $shop['id'],
            'created_by' => $_SESSION['user_credentials']['id'],
            'order_ref' => !empty($_POST['order_ref']) ? $_POST['order_ref'] : null,
            'supply_ref' => !empty($_POST['supply_ref']) ? $_POST['supply_ref'] : null
        ];

        $makeTransactionId = $doubleEntry->makeTransaction($makeTransaction);

        // payable credit entry
        $entry = [
            'transaction_id' => $makeTransactionId,
            'account_id' => $accountId,
            'entry_type' => $settings['payable'],
            'description' => !empty($_POST['summery']) ? $_POST['summery'] : $settings['summery'],
            'amount' => $amount,
            'payment_mode' => $mode_id,
            'user_id' => $_SESSION['user_credentials']['id'],
        ];
        $a[] = $doubleEntry->makeEntry($entry);


        // payable credit entry
        $entry = [
            'transaction_id' => $makeTransactionId,
            'account_id' => $settings['shop_account'],
            'entry_type' => $settings['type'],
            'description' => !empty($_POST['summery']) ? $_POST['summery'] : $settings['summery'],
            'amount' => $amount,
            'payment_mode' => $mode_id,
            'user_id' => $_SESSION['user_credentials']['id'],
        ];


        $a[] = $doubleEntry->makeEntry($entry);



        if (!empty($royaltyId)) {
            // payable credit entry
            $entry = [
                'transaction_id' => $makeTransactionId,
                'account_id' => $royaltyId,
                'entry_type' => $royaltySettings['payable'],
                'description' => !empty($_POST['summery']) ? $_POST['summery'] : $royaltySettings['summery'],
                'amount' => $amount,
                'payment_mode' => $mode_id,
                'user_id' => $_SESSION['user_credentials']['id'],
            ];
            $a[] = $doubleEntry->makeEntry($entry);


            // payable credit entry
            $entry = [
                'transaction_id' => $makeTransactionId,
                'account_id' => $royaltySettings['shop_account'],
                'entry_type' => $royaltySettings['type'],
                'description' => !empty($_POST['summery']) ? $_POST['summery'] : $settings['summery'],
                'amount' => $amount,
                'payment_mode' => $mode_id,
                'user_id' => $_SESSION['user_credentials']['id'],
            ];


            $a[] = $doubleEntry->makeEntry($entry);
        }
    }

    echo json_encode(['status' => 200, 'message' => 'successfully done', 'supply' => ['id' => $makeTransactionId]]);
} catch (PDOException $e) {
    die("Error!: " . $e->getMessage() . "<br/>");
}
