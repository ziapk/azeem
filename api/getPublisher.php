<?php 
include_once dirname(__FILE__).'/../include/settings.php';
$publishers = new  Publishers();
$search = $publishers->searchPublisher($ownerId, !empty($_GET['term']) ? $_GET['term'] : "");
echo json_encode($search);
?>