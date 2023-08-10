<?php
include_once dirname(__FILE__) . '/../include/settings.php';
try {

    $customerId = $_POST['id']; // employee account id
    $amount = $_POST['amount'];
    $storeObj = new Store();
    $storeDATA = $storeObj->getStore($shop['id']);
    $shopAccounts = new ShopAccounts();
    $accountsData = $shopAccounts->getSAs($shop['id']);
    $storeAccounts = [];

    foreach ($accountsData as $a) {
        $storeAccounts[$a['key_value']] = $a['account_id'];
    }

    $doubleEntry = new DoubleEntry();

    $makeTransaction = [
        'description' => !empty($_POST['summery']) ? $_POST['summery'] : "LOAN RECOVERY",
        'transaction_date' => $storeDATA['sale_date'],
        'transaction_type' => 'LOAN_RECOVER',
        'reference' => $_POST['ref_no'],
        'shopId' => $shop['id'],
        'created_by' => $_SESSION['user_credentials']['id'],
        'loan_ref' => !empty($_POST['loan_ref']) ? $_POST['loan_ref'] : null,
    ];

    $customerObj = new Employees();

    $customer = $customerObj->getUserByAccount($customerId);
    $makeTransactionId = $doubleEntry->makeTransaction($makeTransaction);

    // cash debit entry
    $entry = [
        'transaction_id' => $makeTransactionId,
        'account_id' => $storeAccounts['cash'],
        'entry_type' => 'D',
        'description' => '',
        'amount' => $amount,
        'payment_mode' => $_POST['payment_mode'],
        'user_id' => $_SESSION['user_credentials']['id'],
    ];
    $a[] = $doubleEntry->makeEntry($entry);

    // payable credit entry
    $entry = [
        'transaction_id' => $makeTransactionId,
        'account_id' => $customer['account_id'],
        'entry_type' => 'C',
        'description' => '',
        'amount' => $amount,
        'payment_mode' => $_POST['payment_mode'],
        'user_id' => $_SESSION['user_credentials']['id'],
    ];
    $a[] = $doubleEntry->makeEntry($entry);


    echo json_encode(['status' => 200, 'message' => 'Successfully Done', 'transaction' => ['id' => $makeTransactionId]]);
} catch (PDOException $e) {
    die("Error!: " . $e->getMessage() . "<br/>");
}
