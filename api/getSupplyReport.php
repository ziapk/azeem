<?php
include_once dirname(__FILE__) . '/../include/settings.php';
$ordersObj = new Supply();
$from = $_GET['from'];
$to = $_GET['to'];
$orders = $ordersObj->userOrders($userData['shopId'], $from, $to);
$data = [];
$data['records'] = $orders;
$data['income'] = 0;
foreach ($orders as $k => $row) {

    $data['records'][$k]['full_name'] = !empty($row['c_full_name']) ? $row['c_full_name'] : $row['s_full_name'];
    $data['records'][$k]['price'] = round($row['price']);
    if ($row['flag'] == 1) {
        $data['totalIncome'] += round($row['price'] - $row['discount']);
    } else {
        $data['totalReturn'] += round($row['payment_amount']);
    }
    $data['credit'] += $row['price'] - $row['discount'] - $row['payment_amount'];
    if ($row['status'] == 1) {
        $data['park'] += round($row['price'] - $row['discount']);
    } else {
        if ($row['flag'] == 1) {
            $data['income'] += round($row['payment_amount']);
        } else {
            $data['return'] += round($row['payment_amount']);
        }
    }
}
$data['total'] = sizeof($orders);

echo json_encode($data);
