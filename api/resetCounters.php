<?php
include_once dirname(__FILE__) . '/../include/settings.php';
$products = new  Products();
$search = ['message' => "Reset All"];
if ($userData['role'] == 'owner') {
    $ownerId = $userData['id'];
    $shopId = $_GET['shopId'];
    $search['data'] = $products->resetCounters($ownerId, $shopId);
}
echo json_encode($search);
