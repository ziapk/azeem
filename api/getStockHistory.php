<?php
include_once dirname(__FILE__) . '/../include/settings.php';
$ownerId = $userData['role'] == 'owner' ? $userData['id'] : $userData['created_by'];
$suppliers = new Products();
$page = !empty($_GET['page']) ? $_GET['page'] : 1;
$perPage = !empty($_GET['perPage']) ? $_GET['perPage'] : 1;
$search = !empty($_GET['search']) ? $_GET['search'] : "";
$type = !empty($_GET['type']) ? $_GET['type'] : 1;
if (!empty($_SESSION['shopInfo'])) {
    $search = $suppliers->getStockHistoryPagination(['page' => $page, 'type' => $type, 'perPage' => $perPage, 'search' => $search, 'shopId' => $shop['id'], 'owner_id' => $ownerId]);
};
echo json_encode($search);
