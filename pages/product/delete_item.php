<?php 
include_once dirname(__FILE__).'/../../include/settings.php';

$ownerId = $userData['role'] == 'owner' ? $userData['id'] : $userData['created_by'];

$productObj = new Products();
$message = "";

if(!empty($_GET) && isset($_GET['id'] )) {

    $id = $_GET['id'];
    $data = [                
        'id' => $_GET['id'],
        'owner_id' => $ownerId,
    ];

    $assign = $productObj->deleteStoreProduct($data);

        if($assign) {
            echo json_encode(['message' => "Successfully Updated!"]);
        } else {
            echo json_encode(['message' => "Oops! not deleted"]);
        }
    }