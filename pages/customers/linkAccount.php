<?php
include_once dirname(__FILE__) . '/../../include/settings.php';

$customerObj = new Customers();

$ownerId = $userData['role'] == 'owner' ? $userData['id'] : $userData['created_by'];
$userId = $userData['id'];
$shopId = $shop['id'];
$data = $_POST;

$de = new DoubleEntry();

$receivableAccount = $de->getAccount($shop['receivable']);

$accountData = [
    'title' => 'Customer - ' . $_POST['full_name'] . ' - ' . $_POST['company'],
    'code' => $receivableAccount['code'],
    'account_type' => $receivableAccount['account_type'],
    'group_id' => $receivableAccount['group_id'],
    'status' => $receivableAccount['status'],
    'parent_id' => $receivableAccount['id'],
    'shopId' => $shop['id'],
    'opening_balance' => 0,
    'created_by' => $userId
];

$accountId = $de->insertAccount($accountData);
$data['account_id'] = $accountId;
$create = $customerObj->linkAccountCustomer($data);

if ($create) {
    echo json_encode(["success" => true, "message" => "Successfully Linked!"]);
} else {
    echo json_encode(["success" => false, "message" => "Check form carefully!"]);
}
