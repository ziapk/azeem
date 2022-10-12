<?php 
include_once dirname(__FILE__).'/../include/settings.php';
$ownerId = $userData['role'] == 'owner' ? $userData['id'] : $userData['created_by'];
$supply = new Supply();
$page = !empty($_GET['page']) ? $_GET['page'] : 1;
$id = !empty($_GET['id']) ? $_GET['id'] : 0;
$search = !empty($_GET['search']) ? $_GET['search'] : "";
if(!empty($_SESSION['shopInfo'])) {
    $search = $supply->getSupplierTransactions(['page' => $page, 'id' => $id, 'search' => $search]);
};
echo json_encode($search);
?>