<?php
session_start();
include_once dirname(__FILE__) . '/../../include/settings.php';
$_POST = json_decode(file_get_contents('php://input'), true);

$makeTransaction = [
    'description' => $_POST['description'],
    'transaction_date' => $_POST['date'],
    'reference' => $_POST['reference'],
    'created_by' => $_SESSION['user_credentials']['id'],
    'order_ref' => null,
    'supply_ref' => null,
];



$obj = new DoubleEntry();
$transaction_id = $obj->makeTransaction($makeTransaction);
//print_r($_POST);

// prepare journal entries
foreach ($_POST['accounts'] as $account) {
    if (!empty($account['amount']) && !empty($account['type'])) {
        $entry = [
            'transaction_id' => $transaction_id,
            'account_id' => $account['account_id'],
            'entry_type' => $account['type'],
            'description' => $account['description'],
            'amount' => $account['amount'],
            'user_id' => $_SESSION['user_credentials']['id'],
        ];
        $obj->makeEntry($entry);
        unset($entry);
    }
}

echo json_encode(['status' => 200, 'message' => 'Successfully created']);
