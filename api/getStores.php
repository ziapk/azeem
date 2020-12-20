<?php 
include_once dirname(__FILE__).'/../include/settings.php';
$products = new  Products();
$shopInfo = []; 
$search = [];
if(!empty($_SESSION['shopInfo'])) {
    $shopInfo = $_SESSION['shopInfo'];
    $search = $products->searchProducts($shopInfo['shopId'], !empty($_GET['term']) ? $_GET['term'] : "");
};
echo json_encode($search);
?>