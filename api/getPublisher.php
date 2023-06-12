<?php
include_once dirname(__FILE__) . '/../include/settings.php';
$publishers = new  Publishers();
$search = !empty($_GET['term']) ? $_GET['term'] : "";
$data = $publishers->getPublishersPagination(['page' => 1, 'perPage' => 30, 'search' => $search, 'shopId' => $shop['id']]);
echo json_encode($data);
