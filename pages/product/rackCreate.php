<?php

include_once dirname(__FILE__) . '/../../include/settings.php';


$productObj = new Products();
$products = $productObj->getRackByTitle($_POST['rack'], $shop['id']);
$exits = [];
$id = '';
foreach ($products as $value) {
    if ($value['product_id'] == $_POST['product_id']) {
        $exits = $value;
    }
    $id = $value['rack_id'];
}

if (empty($exits)) {
    if (empty($products)) {
        $data = [
            'title' => $_POST['rack'],
            'shop_id' => $shop['id'],
            'owner_id' => $shop['owner_id'],
            'status' => 1,
        ];
        $id = $productObj->createRack($data);
    }
    $childData = [
        'product_id' => $_POST['product_id'],
        'rack_id' => $id,
        'status' => 1
    ];
    $childId = $productObj->createRackProducts($childData);
    echo json_encode(['message' => "Successfully Created!", 'data' => $id]);
} else {
    echo json_encode(['message' => "Already Created!"]);
}
