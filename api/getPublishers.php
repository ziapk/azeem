<?php 
include_once dirname(__FILE__).'/../include/settings.php';
$ownerId = $userData['role'] == 'owner' ? $userData['id'] : $userData['created_by'];
$publishers = new Publishers();
$page = !empty($_GET['page']) ? $_GET['page'] : 1;
$perPage = !empty($_GET['perPage']) ? $_GET['perPage'] : 10;
$search = !empty($_GET['search']) ? $_GET['search'] : "";
if(!empty($_SESSION['shopInfo'])) {
    $search = $publishers->getPublishersPagination(['page' => $page, 'perPage' => $perPage, 'search' => $search]);
};
echo json_encode($search);
?>