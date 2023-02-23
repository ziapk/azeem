<?php

    include_once dirname(__FILE__).'/../../include/settings.php';


    $customerObj = new Customers();
    
    $ownerId = $userData['role'] == 'owner' ? $userData['id'] : $userData['created_by'];
    $userId = $userData['id'];
    $shopId = $userData['shopId'];

    if(empty($_POST['full_name'])) {
      echo json_encode(['success' => false, 'message' => 'Please fill all requried fields']);
    }
    else {

        $data = [                
            'shopId' => $shopId,
            'title' => $_POST['title'],
            'full_name' => $_POST['full_name'],
            'company' => $_POST['company'],
            'type' => !empty($_POST['type']) ? 2 : 1,
            'address' => !empty($_POST['address']) ? $_POST['address'] : "",
            'phoneNumber' => !empty($_POST['phoneNumber']) ? $_POST['phoneNumber'] : "",
        ];

        $create = $customerObj->createCustomer($data);

        if($create) {
            echo json_encode(["success" => true, "message" => "Successfully created!"]);
        } else {
            echo json_encode(["success" => false, "message" => "Check form carefully!"]);
        }
    }
