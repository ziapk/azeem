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
$data['return'] = 0;
$data['totalReturn'] = 0;
$data['park'] = 0;
$data['credit'] = 0;
$data['via'] = [];
// flag 1 = parked/draft return, flag 2 = submitted return
foreach ($orders as $row) {
    $value = $row['price'] - $row['discount'];
    if ($row['flag'] == 2) {
        $data['totalReturn'] += $value;
        $data['return'] += $row['paid'];
        $data['credit'] += $value - $row['paid'];
        if (!empty($row['prices'])) {
            foreach ($row['prices'] as $id => $amount) {
                $data['via'][$id] = (!empty($data['via'][$id]) ? $data['via'][$id] : 0) + $amount;
            }
        }
    } else {
        $data['park'] += $value;
    }
}
$data['total'] = sizeof($orders);

echo json_encode($data);
