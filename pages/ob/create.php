<?php

include_once dirname(__FILE__) . '/../../include/settings.php';

$ownerId = $userData['role'] == 'owner' ? $userData['id'] : $userData['created_by'];
$userId = $userData['id'];
$shopId = $shop['id'];

if (empty($_POST['amount']) || empty($_POST['sale_date'])) {
    echo json_encode(['success' => false, 'message' => 'Please fill all requried fields']);
} else {

    $data = [
        'shop_id' => $shopId,
        'owner_id' => $ownerId,
        'amount' => $_POST['amount'],
        'sale_date' => $_POST['sale_date'],
    ];

    $de = new DoubleEntry();
    $create = $de->insertOB($data);


    if ($create) {
        echo json_encode(["success" => true, "message" => "Successfully created!"]);
    } else {
        echo json_encode(["success" => false, "message" => "Check form carefully!"]);
    }
}
