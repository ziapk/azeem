<?php
include_once dirname(__FILE__) . '/../include/settings.php';
$ordersObj = new Orders();
$from = $_GET['from'];
$to = $_GET['to'];
$orders = $ordersObj->userOrders($userData['shopId'], $from, $to);
$data = [];
$data['records'] = $orders;
$data['income'] = 0;
foreach ($orders as $row) {
    $data['totalIncome'] += $row['price'] - $row['discount'];
    if ($row['status'] == 1) {
        $data['park'] += $row['price'] - $row['discount'];
    } else {
        $data['income'] += $row['price'] - $row['discount'];
    }
}
$data['total'] = sizeof($orders);

echo json_encode($data);
