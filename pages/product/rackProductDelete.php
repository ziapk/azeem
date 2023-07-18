<?php

include_once dirname(__FILE__) . '/../../include/settings.php';


$productObj = new Products();
$products = $productObj->deleteRackProduct($_POST['rack_id'], $_POST['product_id']);
echo json_encode(['message' => "Successfully Delete!"]);
