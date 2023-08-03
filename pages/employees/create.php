<?php

include_once dirname(__FILE__) . '/../../include/settings.php';


$customerObj = new Employees();

$ownerId = $shop['owner_id'];
$userId = $userData['id'];
$shopId = $shop['id'];

if (empty($_POST['full_name'])) {
    echo json_encode(['success' => false, 'message' => 'Please fill all requried fields']);
} else {

    $data = [
        'shop_id' => $shopId,
        'owner_id' => $ownerId,
        "full_name" => !empty($_POST['full_name']) ? $_POST['full_name'] : null,
        "email" => !empty($_POST['email']) ? $_POST['email'] : null,
        "designation" => !empty($_POST['designation']) ? $_POST['designation'] : null,
        "doj" => !empty($_POST['doj']) ? $_POST['doj'] : null,
        "contact_1" => !empty($_POST['contact_1']) ? $_POST['contact_1'] : null,
        "contact_2" => !empty($_POST['contact_2']) ? $_POST['contact_2'] : null,
        "emg_contact_1" => !empty($_POST['emg_contact_1']) ? $_POST['emg_contact_1'] : null,
        "emg_contact_2" => !empty($_POST['emg_contact_2']) ? $_POST['emg_contact_2'] : null,
        "salary" => !empty($_POST['salary']) ? $_POST['salary'] : null,
        "opening_balance" => !empty($_POST['opening_balance']) ? $_POST['opening_balance'] : null,
    ];

    $de = new DoubleEntry();
    $shopAccounts = new ShopAccounts();
    $accountsData = $shopAccounts->getSAs($shop['id']);
    $storeAccounts = [];
    foreach ($accountsData as $a) {
        $storeAccounts[$a['key_value']] = $a['account_id'];
    }

    $salaryAccount = $de->getAccount($storeAccounts['salary']);



    $accountData = [
        'title' => 'Employee - ' . $_POST['full_name'] . ' - ' . $_POST['company'],
        'code' => $salaryAccount['code'],
        'account_type' => $salaryAccount['account_type'],
        'group_id' => $salaryAccount['group_id'],
        'status' => $salaryAccount['status'],
        'parent_id' => $salaryAccount['id'],
        'shopId' => $shop['id'],
        'opening_balance' => $_POST['opening_balance'],
        'created_by' => $userId
    ];

    $accountId = $de->insertAccount($accountData);
    $data['account_id'] = $accountId;
    $create = $customerObj->createEmployee($data);


    if ($create) {
        echo json_encode(["success" => true, "message" => "Successfully created!"]);
    } else {
        echo json_encode(["success" => false, "message" => "Check form carefully!"]);
    }
}
