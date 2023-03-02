<?php
    include_once dirname(__FILE__).'/../../include/settings.php';

    $customerObj = new Categories();
    
    $ownerId = $userData['role'] == 'owner' ? $userData['id'] : $userData['created_by'];
    $userId = $userData['id'];
    $shopId = $shop['id'];
    $data = $_POST;
    
    $de = new DoubleEntry();

    $payableAccount = $de->getAccount($shop['expense']);
    
    $accountData = [
        'title' => 'Expense - '.$_POST['full_name'].' - '.$_POST['groupName'],
        'code' => $payableAccount['code'],
        'account_type' => $payableAccount['account_type'],
        'group_id' => $payableAccount['group_id'],
        'status' => $payableAccount['status'],
        'parent_id' => $payableAccount['id'],
        'opening_balance' => 0,
        'created_by' => $userId
    ];

    $accountId = $de->insertAccount($accountData);
    $data['account_id'] = $accountId;
    $create = $customerObj->linkAccount($data);
    if($create) {
        echo json_encode(["success" => true, "message" => "Successfully Linked!"]);
    } else {
        echo json_encode(["success" => false, "message" => "Check form carefully!"]);
    }