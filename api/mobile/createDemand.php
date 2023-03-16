<?php 
require_once(dirname(__FILE__).'/autoload.php');
$_POST = json_decode(file_get_contents('php://input'), true);

if(empty($_POST['items']) || empty($_POST['demand_title']) || empty($_POST['demand_date']) || empty($_POST['shop_id'])) {
    echo json_encode(['message' => "Please fill all fields"]);
}
else {

    $data = $_POST;

    $demandObj = new Demands();
    $isOwner = false; // if true is will assign to relativent store
    $create = $demandObj->createDemand($data, $isOwner);

    if($create) {
        echo json_encode(['status' => 200, 'message' =>"Created Successfully!"]);
    } else {
        echo json_encode(['status' => 400, 'message' =>"Check form carefully!"]);
    }
}
