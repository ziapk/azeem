<?php
include_once dirname(__FILE__) . '/../include/settings.php';
try {

    $accountId = $_POST['id'];
    $mode = $_POST['mode'];
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
            'summery' => 'DIRECT RECEIVING',
            'title' => 'DIRECT_RECEIVING',
            'receivable' => 'C',
            'shop_account' => $storeAccounts['cash'],
            'type' => 'D'
        ];

        if (empty($_POST['adjustment']) && $amount < 0) {
            $amount *= -1;
            $settings = [
                'summery' => 'DIRECT RECEIVING',
                'title' => 'DIRECT_RECEIVING',
                'receivable' => 'D',
                'shop_account' => $storeAccounts['cash'],
                'type' => 'C'
            ];
        }

        if (!empty($_POST['adjustment']) && $amount < 0) {

            $amount *= -1;

            $settings = [
                'summery' => 'ADJUSTMENT',
                'title' => 'ADJUSTMENT',
                'receivable' => 'D',
                'shop_account' => $storeAccounts['adjustment'],
                'type' => 'C'
            ];
        } elseif (!empty($_POST['adjustment']) && $amount > 0) {
            $settings = [
                'summery' => 'ADJUSTMENT',
                'title' => 'ADJUSTMENT',
                'receivable' => 'C',
                'shop_account' => $storeAccounts['adjustment'],
                'type' => 'D'
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
            'account_id' => $settings['shop_account'],
            'entry_type' => $settings['type'],
            'description' => !empty($_POST['summery']) ? $_POST['summery'] : $settings['summery'],
            'amount' => $amount,
            'payment_mode' => $mode_id,
            'user_id' => $_SESSION['user_credentials']['id'],
        ];
        $a[] = $doubleEntry->makeEntry($entry);


        // payable credit entry
        $entry = [
            'transaction_id' => $makeTransactionId,
            'account_id' => $accountId,
            'entry_type' => $settings['receivable'],
            'description' => !empty($_POST['summery']) ? $_POST['summery'] : $settings['summery'],
            'amount' => $amount,
            'payment_mode' => $mode_id,
            'user_id' => $_SESSION['user_credentials']['id'],
        ];

        $a[] = $doubleEntry->makeEntry($entry);
    }

    $newsletter = new Newsletter();
    $send = $newsletter->send([
        'subject' => $settings['summery'],
        'body' => $newsletter->drawReceiving($makeTransactionId),
        'sentTo' => [['email' => !empty($customer['email']) ? $customer['email'] : 'zia.pccr@yahoo.com', 'name' => $_POST['customer_name']]],
        'ccEmails' => [['email' => $shop['company_email'], 'name' => $shop['full_name']]],
        'client' => $shop['full_name'],
        'labels' => [$makeTransaction['transaction_type']]
    ]);


    echo json_encode(['status' => 200, 'message' => 'successfully done', 'supply' => ['id' => $makeTransactionId]]);
} catch (PDOException $e) {
    die("Error!: " . $e->getMessage() . "<br/>");
}
