<?php 
include_once dirname(__FILE__).'/../include/settings.php';
$customers = new  Customers();
$page = !empty($_GET['page']) ? $_GET['page'] : 1;
$account_type = !empty($_GET['account_type']) ? $_GET['account_type'] : 1;
$perPage = !empty($_GET['perPage']) ? $_GET['perPage'] : 1;
$search = !empty($_GET['search']) ? $_GET['search'] : "";
$result = $customers->getCustomersPagination(['page' => $page, 'perPage' => $perPage, 'search' => $search, 'account_type' => $account_type, 'shopId' => $shop['id']]);
echo json_encode($result);
