<?php

include_once dirname(__FILE__) . '/../../include/settings.php';


$customerObj = new Employees();

$ownerId = $shop['owner_id'];
$empId = $shop['employee_id'];
$userId = $userData['id'];
$shopId = $shop['id'];

$emp = $customerObj->getEmployee($empId);

if (empty($_POST['description']) || empty($_POST['installment_amount']) || empty($_POST['loan_applied'])) {
    echo json_encode(['success' => false, 'message' => 'Please fill all requried fields']);
} else {

    $data = [
        'shop_id' => $shopId,
        'owner_id' => $ownerId,
        "description" => !empty($_POST['description']) ? $_POST['description'] : null,
        "applied_date" => !empty($_POST['applied_date']) ? $_POST['applied_date'] : date('Y-m-d'),
        "issued_date" => !empty($_POST['issued_date']) ? $_POST['issued_date'] : null,
        "installment_amount" => !empty($_POST['installment_amount']) ? $_POST['installment_amount'] : null,
        "load_issued" => !empty($_POST['load_issued']) ? $_POST['load_issued'] : null,
        "loan_applied" => !empty($_POST['loan_applied']) ? $_POST['loan_applied'] : null,
        "status" => !empty($_POST['status']) ? $_POST['status'] : 1,
    ];

    $loanId = $customerObj->insertLoan($data);

    $de = new DoubleEntry();
    $shopAccounts = new ShopAccounts();
    $accountsData = $shopAccounts->getSAs($shop['id']);
    $storeAccounts = [];
    foreach ($accountsData as $a) {
        $storeAccounts[$a['key_value']] = $a['account_id'];
    }

    $loanId = $customerObj->createEmployee($data);

    $storeDATA = $storeObj->getStore($shop['id']);

    $makeTransaction = [
        'description' => "LOAN_ISSUED",
        'transaction_date' => $storeDATA['sale_date'],
        'reference' => $loanId,
        'transaction_type' => 'LOAN_ISSUED',
        'shopId' => $shop['id'],
        'created_by' => $_SESSION['user_credentials']['id'],
        'loan_ref' => $loanId
    ];


    $makeTransactionId = $de->makeTransaction($makeTransaction);

    $defaultMode = $doubleEntry->getDefaultPaymentMode(['shopId' => $shop['id']]);


    // payable credit entry
    $entry = [
        'transaction_id' => $makeTransactionId,
        'account_id' => $emp['account_id'],
        'entry_type' => 'D',
        'description' => $makeTransaction['description'] . ' TO ' . $emp['full_name'],
        'amount' => $amount,
        'payment_mode' => $defaultMode['id'],
        'user_id' => $_SESSION['user_credentials']['id'],
    ];
    $a[] = $doubleEntry->makeEntry($entry);


    // payable credit entry
    $entry = [
        'transaction_id' => $makeTransactionId,
        'account_id' => $storeAccounts['cash'],
        'entry_type' => 'C',
        'description' => $makeTransaction['description'] . ' TO ' . $emp['full_name'],
        'amount' => $amount,
        'payment_mode' => $defaultMode['id'],
        'user_id' => $_SESSION['user_credentials']['id'],
    ];

    $a[] = $doubleEntry->makeEntry($entry);


    if ($create) {
        echo json_encode(["success" => true, "message" => "Successfully created!"]);
    } else {
        echo json_encode(["success" => false, "message" => "Check form carefully!"]);
    }
}
