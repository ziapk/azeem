<?php
include_once dirname(__FILE__) . '/../../include/settings.php';

$categoryObj = new DoubleEntry();

if (empty($_POST['title'])) {
    $error = "Please fill all fields";
} else {

    $data = [
        'id' => $_POST['id'],
        'title' => $_POST['title'],
        'code' => $_POST['code'],
        'is_default' => $_POST['is_default'],
        'status' => !empty($_POST['status']) ? $_POST['status'] : 1
    ];

    $update = $categoryObj->updatePaymentMode($data);

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
