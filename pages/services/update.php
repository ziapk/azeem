<?php
include_once dirname(__FILE__) . '/../../include/settings.php';

$categoryObj = new Services();

if (empty($_POST['full_name'])) {
    $error = "Please fill all fields";
} else {

    $data = [
        'id' => $_POST['id'],
        'full_name' => $_POST['full_name'],
    ];

    $update = $categoryObj->updateService($data);

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
