<?php
session_start();
include_once dirname(__FILE__).'/../../include/settings.php';
$store = new Store();
$accounts = [
"cash" => !empty($_POST['cash']) ? $_POST['cash'] : '',
"payable" => !empty($_POST['payable']) ? $_POST['payable'] : '',
"receivable" => !empty($_POST['receivable']) ? $_POST['receivable'] : '',
"expense" => !empty($_POST['expense']) ? $_POST['expense'] : '',
"sale_discount" => !empty($_POST['sale_discount']) ? $_POST['sale_discount'] : '',
"purchase_discount" => !empty($_POST['purchase_discount']) ? $_POST['purchase_discount'] : '',
"assets" => !empty($_POST['assets']) ? $_POST['assets'] : '',
];
$shopId = !empty($_POST['shopId']) ? $_POST['shopId'] : "";
if(empty($shopId)) {
    header('HTTP/1.1 500 ServerError');
    header('Content-Type: application/json; charset=UTF-8');
    echo json_encode('internal server');	
} 
else {
    $data = $store->updateAccounts($shopId, $accounts);
    header('Content-Type: application/json; charset=UTF-8');
    echo json_encode($data);
}
