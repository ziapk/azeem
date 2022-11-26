<?php 
if(!empty($_POST)) {
include_once dirname(__FILE__).'/../../include/settings.php';
$error = "";


if(empty($_POST['items']) || empty($_POST['title']) || empty($_POST['demand_date']) || empty($_POST['shop_id'])) {
    echo json_encode(['message' => "Please fill all fields"]);
}
else {

    $data = $_POST;

    $data['owner_id'] = $shop['owner_id'];

    $demandObj = new Demands();
    
    $create = $demandObj->modifyDemand($data);

    echo json_encode(['status' => 200, 'message' =>"Updated Successfully!"]);
}
}
