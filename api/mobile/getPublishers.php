<?php 
require_once(dirname(__FILE__).'/autoload.php');
$publishers = new Publishers();
$page = !empty($_GET['page']) ? $_GET['page'] : 1;
$perPage = !empty($_GET['perPage']) ? $_GET['perPage'] : 10;
$search = !empty($_GET['search']) ? $_GET['search'] : "";
$search = $publishers->getPublishersPagination(['page' => $page, 'perPage' => $perPage, 'search' => $search, 'shopId' => $_GET['shop_id']]);
echo json_encode($search);
?>