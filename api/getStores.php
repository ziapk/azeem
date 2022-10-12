<?php 
include_once dirname(__FILE__).'/../include/settings.php';
$products = new  Products();
$ownerId = $userData['role'] == 'owner' ? $userData['id'] : $userData['created_by'];
$shopInfo = []; 
$search = [];
if(!empty($_SESSION['shopInfo'])) {
    $shopInfo = $_SESSION['shop'];
    $shopId = $userData['role'] == 'owner' ? null : $shop['id'];
    $params = ['page' => 1, 'perPage' => 10, 'searchBy' => '', 'search' => !empty($_GET['term']) ? $_GET['term'] : "" ];
    $search = $products->getOwnerProductsPagination($ownerId, $params, $shopId)['records'];
    
};
echo json_encode($search);
?>