<?php

include_once dirname(__FILE__) . '/../../include/settings.php';


$programObj = new Programs();

$error = "";
$message = "";
$error = "";

if (empty($_POST['degree']) || empty($_POST['program'])) {

    $error = "Please fill all fields";
} else {

    $data = [
        'degree' => $_POST['degree'],
        'program' => $_POST['program'],
        'class' => !empty($_POST['class']) ? $_POST['class'] : null,
        'pin' => !empty($_POST['pin']) ? 1 : 0
    ];

    $create = $programObj->createProgram($data);

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
