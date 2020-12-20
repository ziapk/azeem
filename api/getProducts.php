<?php 
include_once dirname(__FILE__).'/../include/settings.php';
$ownerId = $userData['role'] == 'owner' ? $userData['id'] : $userData['created_by'];
$products = new  Products();
$page = !empty($_GET['page']) ? $_GET['page'] : 1;
$search = !empty($_GET['search']) ? $_GET['search'] : "";
if(!empty($_SESSION['shopInfo'])) {
    $search = $products->getOwnerProductsPagination($ownerId, ['page' => $page, 'search' => $search]);
};
echo json_encode($search);
?>