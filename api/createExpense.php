<?php
include_once dirname(__FILE__).'/../include/settings.php';
try {
if(!empty($_POST['price']) && !empty($_POST['cat_id'])) {
    $shopId = $shop['id'];
    $expenseObj = new Expenses();
    $categoryObj = new Categories();
    $cat_id = $_POST['cat_id'];
    $cat = $categoryObj->getCategory($cat_id);
    $data = [                
        'title' => $cat['full_name'],
        'description' => $_POST['description'],
        'cat_id' => $cat_id,
        'price' => $_POST['price'],
        'details' => $cat['groupName'],
        'exp_date' => $shop['sale_date'],
        'shop_id' => $shopId
    ];
    
    $create = $expenseObj->createExpense($data);

    if($create) {
        echo json_encode(['status' => 200, 'message' => 'Successfully Created']);
    } else {
        echo json_encode(['status' => 400, 'message' => 'Oops! Not Created']);
    }
}
} catch (PDOException $e) {
    die("Error!: " . $e->getMessage() . "<br/>");
}