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

    $receivableAccount = $de->getAccount($shop['expense']);

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


    $accountId = $de->insertAccount($accountData);
    $data['account_id'] = $accountId;
    $create = $customerObj->createEmployee($data);


    if ($create) {
        echo json_encode(["success" => true, "message" => "Successfully created!"]);
    } else {
        echo json_encode(["success" => false, "message" => "Check form carefully!"]);
    }
}
