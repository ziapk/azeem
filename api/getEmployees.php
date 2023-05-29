<?php
include_once dirname(__FILE__) . '/../include/settings.php';
$customers = new  Employees();
$page = !empty($_GET['page']) ? $_GET['page'] : 1;
$perPage = !empty($_GET['perPage']) ? $_GET['perPage'] : 1;
$search = !empty($_GET['search']) ? $_GET['search'] : "";
$result = $customers->getEmployeesPagination(['page' => $page, 'perPage' => $perPage, 'search' => $search, 'shopId' => $shop['id']]);
echo json_encode($result);
