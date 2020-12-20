<?php 
include_once dirname(__FILE__).'/../include/settings.php';
$products = new  Products();
$ownerId = $userData['role'] == 'owner' ? $userData['id'] : $userData['created_by'];
if(!empty($_SESSION['shopInfo'])) {
    $search = $products->searchProductGroups($ownerId, !empty($_GET['term']) ? $_GET['term'] : "");
};
echo json_encode($search);
?>