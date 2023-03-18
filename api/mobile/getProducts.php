<?php 
require_once(dirname(__FILE__).'/autoload.php');
$ownerId = $_GET['owner_id'];
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
$publisher_id = !empty($_GET['publisher_id']) ? $_GET['publisher_id'] : "";
$sortByField = !empty($_GET['sortByField']) ? $_GET['sortByField'] : "";
$sortByOrder = !empty($_GET['sortByOrder']) ? $_GET['sortByOrder'] : "";
$correction = !empty($_GET['correction']) ? $_GET['correction'] : false;
$selectedPublisherId = !empty($_GET['selectedPublisherId']) ? $_GET['selectedPublisherId'] : false;
$pin = !empty($_GET['bookmark']) ? $_GET['bookmark'] : "";
$dup = !empty($_GET['dup']) ? $_GET['dup'] : "";

$shopId = $_GET['shop_id'];
if(!empty($selectedPublisherId) && !empty($correction)) {
    $search = $products->assignProductsPublisher($ownerId, ['page' => (int)$page, 'perPage' => (int)$perPage, 'search' => $search, 'searchBy' => $searchBy, 'courceId' => $courceId, 'group' => $group, 'board' => $board, 'full_name' => $full_name, 'author' => $author, 'sortByField' => $sortByField, 'sortByOrder' => $sortByOrder, 'pin' => $pin, 'dup' => $dup, 'publisher_id' => $publisher_id, 'correction' => $correction, 'selectedPublisherId' => $selectedPublisherId], $shopId);
}
else {
    $search = $products->getOwnerProductsPagination($ownerId, ['page' => (int)$page, 'perPage' => (int)$perPage, 'search' => $search, 'searchBy' => $searchBy, 'courceId' => $courceId, 'group' => $group, 'board' => $board, 'full_name' => $full_name, 'author' => $author, 'sortByField' => $sortByField, 'sortByOrder' => $sortByOrder, 'pin' => $pin, 'dup' => $dup, 'publisher_id' => $publisher_id, 'correction' => $correction], $shopId);
}
echo json_encode($search);