<?php 
include_once dirname(__FILE__).'/../include/settings.php';
$ownerId = $shopData['owner_id'];
$categories = new Categories();
$page = !empty($_GET['page']) ? $_GET['page'] : 1;
$perPage = !empty($_GET['perPage']) ? $_GET['perPage'] : 1;
$search = !empty($_GET['search']) ? $_GET['search'] : "";
if(!empty($_SESSION['shopInfo'])) {
    $search = $categories->getCategoriesPagination(['page' => $page, 'perPage' => $perPage, 'search' => $search, 'owner_id' => $ownerId]);
};
echo json_encode($search);
?>