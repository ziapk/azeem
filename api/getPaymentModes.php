<?php 
include_once dirname(__FILE__).'/../include/settings.php';
$shopId = $shop['id'];
$categories = new DoubleEntry();
$page = !empty($_GET['page']) ? $_GET['page'] : 1;
$perPage = !empty($_GET['perPage']) ? $_GET['perPage'] : 10;
$search = !empty($_GET['search']) ? $_GET['search'] : "";
if(!empty($_SESSION['shopInfo'])) {
    $search = $categories->getPaymentModes(['page' => $page, 'perPage' => $perPage, 'search' => $search, 'shopId' => $shopId]);
};
echo json_encode($search);
?>