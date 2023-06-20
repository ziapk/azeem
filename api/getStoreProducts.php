<?php
include_once dirname(__FILE__) . '/../include/settings.php';
$ownerId = $userData['role'] == 'owner' ? $userData['id'] : $userData['created_by'];
$products = new  Products();
$page = !empty($_GET['page']) ? $_GET['page'] : 1;
$perPage = !empty($_GET['perPage']) ? $_GET['perPage'] : 0;
$search = !empty($_GET['search']) ? $_GET['search'] : "";
$searchBy = !empty($_GET['searchBy']) ? $_GET['searchBy'] : "";
$full_name = !empty($_GET['full_name']) ? $_GET['full_name'] : "";
$group = !empty($_GET['group']) ? $_GET['group'] : "";
$author = !empty($_GET['author']) ? $_GET['author'] : "";
$board = !empty($_GET['board']) ? $_GET['board'] : "";
$courceId = !empty($_GET['courceId']) ? $_GET['courceId'] : "";
$sortByField = !empty($_GET['sortByField']) ? $_GET['sortByField'] : "";
$sortByOrder = !empty($_GET['sortByOrder']) ? $_GET['sortByOrder'] : "";
$publisher_id = !empty($_GET['publisher_id']) ? $_GET['publisher_id'] : "";
$pin = !empty($_GET['bookmark']) ? $_GET['bookmark'] : "";

$shopId = $userData['role'] == 'owner' ? (!empty($_GET['shopId']) ? $_GET['shopId'] : null) : $userData['shopId'];
if (!empty($_SESSION['shopInfo'])) {
    $search = $products->getStoreProductsPagination($ownerId, ['page' => (int)$page, 'perPage' => (int)$perPage, 'search' => $search, 'searchBy' => $searchBy, 'courceId' => $courceId, 'group' => $group, 'board' => $board, 'full_name' => $full_name, 'author' => $author, 'sortByField' => $sortByField, 'sortByOrder' => $sortByOrder, 'pin' => $pin, 'publisher_id' => $publisher_id], $shopId);
};
echo json_encode($search);
