<?php 
include_once dirname(__FILE__).'/../include/settings.php';
$ownerId = $userData['role'] == 'owner' ? $userData['id'] : $userData['created_by'];
$suppliers = new Suppliers();
$page = !empty($_GET['page']) ? $_GET['page'] : 1;
$search = !empty($_GET['search']) ? $_GET['search'] : "";
if(!empty($_SESSION['shopInfo'])) {
    $search = $suppliers->getSuppliersPagination(['page' => $page, 'search' => $search]);
};
echo json_encode($search);
?>