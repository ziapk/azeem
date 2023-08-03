<?php
include_once dirname(__FILE__) . '/../include/settings.php';
try {

    $accountId = $_POST['id']; // EMPLOYEE ID
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
            'summery' => 'SALARY PAYMENT',
            'title' => 'SALARY PAYMENT',
            'payable' => 'D',
            'shop_account' => $storeAccounts['cash'],
            'type' => 'C'
        ];

        $makeTransaction = [
            'description' => !empty($_POST['summery']) ? $_POST['summery'] : $settings['summery'],
            'transaction_date' => $storeDATA['sale_date'],
            'reference' => !empty($_POST['ref_no']) ? $_POST['ref_no'] : '',
            'transaction_type' => $settings['title'],
            'shopId' => $shop['id'],
            'created_by' => $_SESSION['user_credentials']['id'],
            'salary_ref' => null
        ];

        $makeTransactionId = $doubleEntry->makeTransaction($makeTransaction);


        if (!empty($accountId)) {
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
        }
    }

    echo json_encode(['status' => 200, 'message' => 'successfully done', 'supply' => ['id' => $makeTransactionId]]);
} catch (PDOException $e) {
    die("Error!: " . $e->getMessage() . "<br/>");
}
