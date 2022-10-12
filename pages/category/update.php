<?php 
    include_once dirname(__FILE__).'/../../include/settings.php';

    $categoryObj = new Categories();

    if(empty($_POST['full_name']) ) {
        $error = "Please fill all fields";
    }
    else {

        $data = [                
            'id' => $_POST['id'],
            'full_name' => $_POST['full_name'],
            'groupName' => $_POST['groupName'],
            'cat_type' => $_POST['cat_type']
        ];

        $update = $categoryObj->updateCategory($data);

        if($update) {
            $message = "Successfully Assigned!";
        } else {
            $message = "Nothing change";
        }
    }

    if(!empty($error)) {
        echo json_encode(['success' => false, 'error' => $error]);
    }
    if(!empty($message)) {
        echo json_encode(['success' => true, 'message' => $message]);
    }