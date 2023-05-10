<?php
include_once dirname(__FILE__) . '/../include/settings.php';
try {

    $customerId = $_POST['id'];


    $amount = $_POST['amount'];


    $storeObj = new Store();
    $storeDATA = $storeObj->getStore($shop['id']);



    $doubleEntry = new DoubleEntry();

    $makeTransaction = [
        'description' => !empty($_POST['summery']) ? $_POST['summery'] : "PAYMENT RECEVIED",
        'transaction_date' => $storeDATA['sale_date'],
        'reference' => $_POST['ref_no'],
        'shopId' => $shop['id'],
        'created_by' => $_SESSION['user_credentials']['id'],
        'order_ref' => !empty($_POST['order_ref']) ? $_POST['order_ref'] : null,
        'supply_ref' => null
    ];

    $customerObj = new Customers();

    $customer = $customerObj->getUserByAccount($customerId);
    $makeTransactionId = $doubleEntry->makeTransaction($makeTransaction);

    // payable credit entry
    $entry = [
        'transaction_id' => $makeTransactionId,
        'account_id' => $storeDATA['receiving'],
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
