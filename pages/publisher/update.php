<?php
include_once dirname(__FILE__) . '/../../include/settings.php';

$publisherObj = new Publishers();

if (empty($_POST['full_name'])) {
    $error = "Please fill all fields";
} else {

    $data = [
        'id' => $_POST['id'],
        'full_name' => $_POST['full_name'],
        'discount_type' => $_POST['discount_type'],
        'discount_amount' => $_POST['discount_amount'],
        'discount_status' => $_POST['discount_status'],
        'pin' => !empty($_POST['pin']) ? 1 : 0,
    ];

    $update = $publisherObj->updatePublisher($data);

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
