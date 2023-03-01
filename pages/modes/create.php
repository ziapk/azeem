<?php

include_once dirname(__FILE__).'/../../include/settings.php';


$categoryObj = new DoubleEntry();

$error = "";
$message = "";
$error = "";

$ownerId = $userData['role'] == 'owner' ? $userData['id'] : $userData['created_by'];
    
if(empty($_POST['title'])) {

    $error = "Please fill all fields";
}
else {

    $data = [                
        'title' => $_POST['title'],
        'code' => $_POST['code'],
        'status' => !empty($_POST['status']) ? $_POST['status'] : 1,
        'shopId' => $shop['id'],
        'owner_id' => $ownerId
    ];

    $create = $categoryObj->createPaymentMode($data);

    if($create) {
        $message = "Successfully created!";
    } else {
        $error = "Check form carefully!";
    }
}

if(!empty($error)) {
  echo json_encode(['success' => false, 'error' => $error]);
}
if(!empty($message)) {
  echo json_encode(['success' => true, 'message' => $message]);
}