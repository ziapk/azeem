<?php
include_once dirname(__FILE__) . '/../../include/settings.php';
try {
    $categoryObj = new Statuses();

    $error = "";
    $message = "";
    $error = "";

    if (empty($_POST['title'])) {

        $error = "Please fill all fields";
    } else {


        $data = [
            'title' => $_POST['title'],
            'type' => $_POST['type'],
            'progress_value' => $_POST['progress_value'],
            'shop_id' => $shop['id'],
            'created_by' => $userData['id'],
        ];

        $create = $categoryObj->create($data);

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
} catch (PDOException $e) {
    die("Error!: " . $e->getMessage() . "<br/>");
}
