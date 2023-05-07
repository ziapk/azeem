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
    $data['totalIncome'] += $row['total'] - $row['discount'];
    $data['credit'] += $row['price'] - $row['discount'] - $row['paid_amount'];
    if ($row['status'] == 1) {
        $data['park'] += $row['price'] - $row['discount'];
    } else {
        $data['income'] += $row['paid_amount'] - $row['discount'];
    }
}
$data['total'] = sizeof($orders);

echo json_encode($data);
