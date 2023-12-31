<?php
include_once dirname(__FILE__) . '/../include/settings.php';
$ordersObj = new Orders();
$data = [
    'from' => $_GET['from'],
    'to' => $_GET['to'],
    'orderId' => $_GET['orderId'],
    'orderType' => !empty($_GET['orderType']) && $_GET['orderType'] == 'linked' ? 1 : 0
];
$orders = $ordersObj->userReturnOrders($userData['shopId'], $data);
$data = [];
$data['records'] = $orders;
$data['income'] = 0;
foreach ($orders as $row) {
    if ($row['flag'] == 1) {
        $data['totalIncome'] += $row['price'] - $row['discount'];
        if (!empty($row['prices'])) {
            foreach ($row['prices'] as $id => $amount) {
                $data['via'][$id] += $amount;
            }
        }
    } else {
        $data['totalReturn'] += $row['paid'];
    }
    $data['credit'] += $row['price'] - $row['discount'] - $row['paid'];
    if ($row['flag'] == 1) {
        $data['return'] += $row['paid'];
    } else {
        $data['return'] += 0;
    }
}
$data['total'] = sizeof($orders);

echo json_encode($data);
