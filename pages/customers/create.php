<?php

include_once dirname(__FILE__) . '/../../include/settings.php';


$customerObj = new Customers();

$ownerId = $userData['role'] == 'owner' ? $userData['id'] : $userData['created_by'];
$userId = $userData['id'];
$shopId = $shop['id'];

$shopAccounts = new ShopAccounts();
$accountsData = $shopAccounts->getSAs($shopId);
$storeAccounts = [];
foreach ($accountsData as $a) {
    $storeAccounts[$a['key_value']] = $a['account_id'];
}

if (empty($_POST['full_name'])) {
    echo json_encode(['success' => false, 'message' => 'Please fill all requried fields']);
} else {

    $data = [
        'shopId' => $shopId,
        'code' => '',
        'title' => $_POST['title'],
        'full_name' => $_POST['full_name'],
        'company' => $_POST['company'],
        'email' => $_POST['email'],
        'account_type' => !empty($_POST['account_type']) ? $_POST['account_type'] : 1,
        'type' => !empty($_POST['type']) ? 2 : 1,
        'address' => !empty($_POST['address']) ? $_POST['address'] : "",
        'phoneNumber' => !empty($_POST['phoneNumber']) ? $_POST['phoneNumber'] : "",
        'default_discount' => !empty($_POST['default_discount']) ? $_POST['default_discount'] : 0,
    ];

    $de = new DoubleEntry();

    if($data['account_type'] == 1) {

    $receivableAccount = $de->getAccount($storeAccounts['receivable']);

    $accountData = [
        'title' => 'Customer - ' . $_POST['full_name'] . ' - ' . $_POST['company'],
        'code' => $receivableAccount['code'],
        'account_type' => $receivableAccount['account_type'],
        'group_id' => $receivableAccount['group_id'],
        'status' => $receivableAccount['status'],
        'parent_id' => $receivableAccount['id'],
        'shopId' => $shop['id'],
        'opening_balance' => $_POST['opening_balance'],
        'created_by' => $userId
    ];
    } else if($data['account_type'] == 2) {

        $receivableAccount = $de->getAccount($storeAccounts['locker']);

        $accountData = [
            'title' => 'Locker - ' . $_POST['full_name'],
            'code' => $receivableAccount['code'],
            'account_type' => $receivableAccount['account_type'],
            'group_id' => $receivableAccount['group_id'],
            'status' => $receivableAccount['status'],
            'parent_id' => $receivableAccount['id'],
            'shopId' => $shop['id'],
            'opening_balance' => $_POST['opening_balance'],
            'created_by' => $userId
        ];
    }


    $accountId = $de->insertAccount($accountData);
    $data['account_id'] = $accountId;
    $create = $customerObj->createCustomer($data);


    if ($create) {
        echo json_encode(["success" => true, "message" => "Successfully created!"]);
    } else {
        echo json_encode(["success" => false, "message" => "Check form carefully!"]);
    }
}
