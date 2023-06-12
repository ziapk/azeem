<?php
include_once dirname(__FILE__) . '/../include/settings.php';
$shop_id = $shop['id'];
$categories = new ShopAccounts();
$page = !empty($_GET['page']) ? $_GET['page'] : 1;
$perPage = !empty($_GET['perPage']) ? $_GET['perPage'] : 1;
$search = !empty($_GET['search']) ? $_GET['search'] : "";
if (!empty($_SESSION['shopInfo'])) {
    $search = $categories->getSAPagination(['page' => $page, 'perPage' => $perPage, 'search' => $search, 'shop_id' => $shop_id]);
};
echo json_encode($search);
