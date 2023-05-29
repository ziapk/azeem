<?php
include_once dirname(__FILE__) . '/../include/settings.php';
$ownerId = $shopData['owner_id'];
$categories = new Statuses();
$page = !empty($_GET['page']) ? $_GET['page'] : 1;
$perPage = !empty($_GET['perPage']) ? $_GET['perPage'] : 1;
$search = !empty($_GET['search']) ? $_GET['search'] : "";
$search = $categories->getStatusPagination(['page' => $page, 'perPage' => $perPage, 'search' => $search, 'shop_id' => $shop['id']]);
echo json_encode($search);
