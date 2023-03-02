<?php 
if(!empty($_POST)) {
include_once dirname(__FILE__).'/../../include/settings.php';
$error = "";


if(empty($_POST['items']) || empty($_POST['demand_title']) || empty($_POST['demand_date']) || empty($_POST['shop_id'])) {
    echo json_encode(['message' => "Please fill all fields"]);
}
else {

    $data = $_POST;

    $data['owner_id'] = $shop['owner_id'];

    $demandObj = new Demands();
    $isOwner = $userData['role'] == 'owner' ? true : false;
    $create = $demandObj->createDemand($data, $isOwner);

    if($create) {
        echo json_encode(['status' => 200, 'message' =>"Created Successfully!"]);
    } else {
        echo json_encode(['status' => 400, 'message' =>"Check form carefully!"]);
    }
}
}
