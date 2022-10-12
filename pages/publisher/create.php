<?php

include_once dirname(__FILE__).'/../../include/settings.php';


$publisherObj = new Publishers();

$error = "";
$message = "";
$error = "";
    
if(empty($_POST['full_name'])) {

    $error = "Please fill all fields";
}
else {

    $data = [                
        'full_name' => $_POST['full_name'],
        'discount_type' => $_POST['discount_type'],
        'discount_amount' => $_POST['discount_amount'],
        'discount_status' => $_POST['discount_status'],
    ];

    $create = $publisherObj->createPublisher($data);

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