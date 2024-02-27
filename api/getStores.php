<?php
include_once dirname(__FILE__) . '/../include/settings.php';
$products = new  Products();
$ownerId = $userData['role'] == 'owner' ? $userData['id'] : $userData['created_by'];
$shopInfo = [];
$search = [];
if (!empty($_SESSION['shopInfo'])) {
    $shopInfo = $_SESSION['shop'];
    $type = $_GET['type'] ? $_GET['type'] : '';
    $searchText = !empty($_GET['term']) ? $_GET['term'] : "";
    $searchBy = !empty($_GET['searchBy']) ? $_GET['searchBy'] : '';
    $shopId = $userData['role'] == 'owner' ? $_GET['shopId'] : $shop['id'];
    $params = ['page' => 1, 'perPage' => 20, 'searchBy' => $searchBy, 'search' => $searchText, 'type' => $type, 'status' => [1]];
    $search = $products->getOwnerProductsPagination($ownerId, $params, $shopId)['records'];
};
echo json_encode($search);
