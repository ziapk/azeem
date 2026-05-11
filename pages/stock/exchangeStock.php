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
        $inventory  = new Inventory();
        $create = [];
        foreach ($_POST['items'] as $value) {
            $value['owner_id']   = $owner_id;
            $value['shopId']     = $shopId;
            $value['created_by'] = $created_by;

            $exchangeId = $productObj->createExchange($value);
            $create[] = $exchangeId;

            if ($exchangeId) {
                $inventory->logMovement([
                    'product_id'    => (int)$value['fromId'],
                    'shop_id'       => (int)$shopId,
                    'owner_id'      => (int)$owner_id,
                    'movement_type' => Inventory::SALE,
                    'quantity'      => -1 * (float)$value['qty'],
                    'ref_type'      => Inventory::REF_EXCHANGE,
                    'ref_id'        => (int)$exchangeId,
                    'note'          => 'Exchange out #' . $exchangeId,
                    'created_by'    => (int)$created_by,
                ]);
                $inventory->logMovement([
                    'product_id'    => (int)$value['toId'],
                    'shop_id'       => (int)$shopId,
                    'owner_id'      => (int)$owner_id,
                    'movement_type' => Inventory::SUPPLY,
                    'quantity'      => (float)$value['qty'],
                    'ref_type'      => Inventory::REF_EXCHANGE,
                    'ref_id'        => (int)$exchangeId,
                    'note'          => 'Exchange in #' . $exchangeId,
                    'created_by'    => (int)$created_by,
                ]);
            }
        }


        if (sizeof($create)) {
            echo json_encode(['status' => 200, 'message' => "Created Successfully!", "ids" => $create]);
        } else {
            echo json_encode(['status' => 400, 'message' => "Check form carefully!"]);
        }
    }
}
