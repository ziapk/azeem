<?php 
include_once dirname(__FILE__).'/../include/settings.php';
$ids = !empty($_POST['ids']) ? $_POST['ids'] : [];
$title = !empty($_POST['title']) ? $_POST['title'] : '';

$orders = new Orders();

if(!empty($title) && !empty($ids)) {
    $array = $orders->getFaultyProductsById($ids);
    $product_ids = [];
    foreach ($array as $row) {
        $product_ids[] = $row['id'];
    }
    $orders->makeReturn($title, $product_ids);
    echo json_encode(['status' => 200, 'message' => 'Successfully Return Created']);
}
else {
    echo json_encode(['status' => 400, 'message' => 'Data Incomplete']);
}
?>