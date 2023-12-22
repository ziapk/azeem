<?php
include_once dirname(__FILE__) . '/../include/settings.php';
$ordersObj = new Orders();
$id = $_GET['id'];
$shopId = $_GET['shopId'];
$order = $ordersObj->getOrder($id);
echo json_encode($order);
