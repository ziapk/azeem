<?php
if (!empty($_POST)) {
    include_once dirname(__FILE__) . '/../../include/settings.php';
    $error = "";


    if (empty($_POST['items'])) {
        echo json_encode(['message' => "Please fill all fields"]);
    } else {

        $data = $_POST;

        
        $created_by = $userData['id'];
        $owner_id = $shop['owner_id'];
        $shopId = $shop['id'];

        $productObj = new Products();
        $create = [];
        foreach ($_POST['items'] as $value) {
            $productObj->subProductQty(['product_id' => $value['fromId'], 'quantity' => $value['qty'],  'owner_id' => $owner_id,  'shopId' => $shopId]);

            $productObj->addProductQty($value['toId'], ['qty' => $value['qty']], $shopId, 1);
            
            $value['owner_id'] = $owner_id;
            $value['shopId'] = $shopId;
            $value['created_by'] = $created_by;
            $create[] = $productObj->createExchange($value);
        }


        if (sizeof($create)) {
            echo json_encode(['status' => 200, 'message' => "Created Successfully!", "ids" => $create]);
        } else {
            echo json_encode(['status' => 400, 'message' => "Check form carefully!"]);
        }
    }
}
