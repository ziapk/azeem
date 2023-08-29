<?php
include_once dirname(__FILE__) . '/../include/settings.php';
$ordersObj = new Orders();
$from = $_GET['from'];
$to = $_GET['to'];
$orderId = $_GET['orderId'];
$data = [
    'from' => $_GET['from'],
    'to' => $_GET['to'],
    'orderId' => $_GET['orderId'],
    'orderType' => !empty($_GET['orderType']) ? $_GET['orderType'] : 'all'
];
$orders = $ordersObj->userOrders($userData['shopId'], $data);
array_multisort(array_column($orders, 'order_custom_id'), SORT_ASC, $orders);
$total = 0;
foreach ($orders as $key => $row) {
    $total += $row['price'] - $row['discount'];
    $orders[$key]['runningTotal'] = $total;
}
array_multisort(array_column($orders, 'order_custom_id'), SORT_DESC, $orders);

$data = [];
$data['records'] = $orders;
$data['income'] = 0;
foreach ($orders as $key => $row) {
    if ($row['flag'] == 1) {
        $data['totalIncome'] += $row['price'] - $row['discount'];
        if (!empty($row['prices'])) {
            foreach ($row['prices'] as $id => $amount) {
                $data['via'][$id] += $amount;
            }
        }
    } else {
        $data['totalReturn'] += $row['paid_amount'];
    }
    if ($row['status'] != 1) {
        $data['credit'] += $row['price'] - $row['discount'] - $row['paid_amount'];
    }
    if ($row['status'] == 1) {
        $data['park'] += $row['price'] - $row['discount'];
    } else {
        if ($row['flag'] == 1) {
            $data['income'] += $row['paid_amount'];
        } else {
            $data['return'] += $row['paid_amount'];
        }
    }
}
$data['total'] = sizeof($orders);

echo json_encode($data);
