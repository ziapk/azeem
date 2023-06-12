<?php

include_once dirname(__FILE__) . '/../../include/settings.php';


$categoryObj = new Services();

$error = "";
$message = "";
$error = "";

$ownerId = $userData['role'] == 'owner' ? $userData['id'] : $userData['created_by'];


$userId = $userData['id'];

if (empty($_POST['full_name'])) {

    $error = "Please fill all fields";
} else {

    $data = [
        'full_name' => $_POST['full_name'],
        'owner_id' => $ownerId,
        'shop_id' => $shop['id']
    ];

    $create = $categoryObj->createService($data);

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
