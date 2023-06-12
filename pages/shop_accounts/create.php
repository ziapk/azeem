<?php

include_once dirname(__FILE__) . '/../../include/settings.php';


$categoryObj = new ShopAccounts();

$error = "";
$message = "";
$error = "";

$userId = $userData['id'];

if (empty($_POST['key_value']) && empty($_POST['label_value']) && empty($_POST['account_id'])) {

    $error = "Please fill all fields";
} else {

    $data = [
        'key_value' => $_POST['key_value'],
        'label_value' => $_POST['label_value'],
        'account_id' => $_POST['account_id'],
        'shop_id' => $shop['id']
    ];

    $create = $categoryObj->createSA($data);

    if ($create) {
        $message = "Successfully created!";
    } else {
        $error = "Check form carefully!";
    }
}

if (!empty($error)) {
    echo json_encode(['success' => false, 'error' => $error]);
}
if (!empty($message)) {
    echo json_encode(['success' => true, 'message' => $message]);
}
