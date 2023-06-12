<?php
include_once dirname(__FILE__) . '/../../include/settings.php';

$categoryObj = new ShopAccounts();

if (empty($_POST['key_value']) && empty($_POST['label_value']) && empty($_POST['account_id']) && empty($_POST['id'])) {
    $error = "Please fill all fields";
} else {

    $data = [
        'id' => $_POST['id'],
        'key_value' => $_POST['key_value'],
        'label_value' => $_POST['label_value'],
        'account_id' => $_POST['account_id'],
    ];

    $update = $categoryObj->updateSA($data);

    if ($update) {
        $message = "Successfully Assigned!";
    } else {
        $message = "Nothing change";
    }
}

if (!empty($error)) {
    echo json_encode(['success' => false, 'error' => $error]);
}
if (!empty($message)) {
    echo json_encode(['success' => true, 'message' => $message]);
}
