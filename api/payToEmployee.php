<?php
include_once dirname(__FILE__) . '/../include/settings.php';
try {

    $supplierId = $_POST['id']; // employee account_id


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
        'description' => !empty($_POST['summery']) ? $_POST['summery'] : "SUPPLY PAYMENT",
        'transaction_date' => $storeDATA['sale_date'],
        'reference' => $_POST['ref_no'],
        'transaction_type' => 'PURCHASE_PAYMENT',
        'shopId' => $shop['id'],
        'created_by' => $_SESSION['user_credentials']['id'],
        'order_ref' => null,
        'supply_ref' => !empty($_POST['supply_ref']) ? $_POST['supply_ref'] : null
    ];

    $supplierObj = new Employees();

    $supplier = $supplierObj->getUserByAccount($supplierId);
    $makeTransactionId = $doubleEntry->makeTransaction($makeTransaction);

    // payable credit entry
    $entry = [
        'transaction_id' => $makeTransactionId,
        'account_id' => $supplier['account_id'],
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
        'account_id' => $storeAccounts['cash'],
        'entry_type' => 'C',
        'description' => '',
        'amount' => $amount,
        'payment_mode' => $_POST['payment_mode'],
        'user_id' => $_SESSION['user_credentials']['id'],
    ];

    $a[] = $doubleEntry->makeEntry($entry);

    echo json_encode(['status' => 200, 'message' => 'successfully done', 'supply' => ['id' => $makeTransactionId]]);
} catch (PDOException $e) {
    die("Error!: " . $e->getMessage() . "<br/>");
}
