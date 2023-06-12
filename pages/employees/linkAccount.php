<?php
include_once dirname(__FILE__) . '/../../include/settings.php';

$customerObj = new Employees();

$ownerId = $userData['role'] == 'owner' ? $userData['id'] : $userData['created_by'];
$userId = $userData['id'];
$shopId = $shop['id'];
$data = $_POST;

$de = new DoubleEntry();
$shopAccounts = new ShopAccounts();
$accountsData = $shopAccounts->getSAs($shop['id']);
$storeAccounts = [];
foreach ($accountsData as $a) {
    $storeAccounts[$a['key_value']] = $a['account_id'];
}

$receivableAccount = $de->getAccount($storeAccounts['salary']);

$accountData = [
    'title' => 'Employee - ' . $_POST['full_name'] . ' - ' . $_POST['company'],
    'code' => $receivableAccount['code'],
    'account_type' => $receivableAccount['account_type'],
    'group_id' => $receivableAccount['group_id'],
    'status' => $receivableAccount['status'],
    'shopId' => $shop['id'],
    'parent_id' => $receivableAccount['id'],
    'opening_balance' => 0,
    'created_by' => $userId
];

$accountId = $de->insertAccount($accountData);
$data['account_id'] = $accountId;
$create = $customerObj->linkAccount($data);

if ($create) {
    echo json_encode(["success" => true, "message" => "Successfully Linked!"]);
} else {
    echo json_encode(["success" => false, "message" => "Check form carefully!"]);
}
