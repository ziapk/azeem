<?php

include_once dirname(__FILE__).'/../../include/settings.php';


$categoryObj = new Categories();

$error = "";
$message = "";
$error = "";

$ownerId = $userData['role'] == 'owner' ? $userData['id'] : $userData['created_by'];
    
if(empty($_POST['full_name'])) {

    $error = "Please fill all fields";
}
else {

    $data = [                
        'full_name' => $_POST['full_name'],
        'cat_type' => $_POST['cat_type'],
        'groupName' => !empty($_POST['groupName']) ? $_POST['groupName'] : 'General',
        'owner_id' => $ownerId
    ];

    $create = $categoryObj->createCategory($data);

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