<?php
include_once dirname(__FILE__) . '/../../include/settings.php';

$categoryObj = new Statuses();

if (empty($_POST['title'])) {
    $error = "Please fill all fields";
} else {


    $data = [
        'id' => $_POST['id'],
        'title' => $_POST['title'],
        'type' => $_POST['type'],
        'progress_value' => $_POST['progress_value'],
    ];

    $update = $categoryObj->update($data);

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
