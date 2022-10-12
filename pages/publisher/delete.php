<?php 

$id = !empty($_GET['id']) ? $_GET['id'] : null;
$error = "";
$message = "";

if(!$id) {
    $error = 'invalid id';
}

include_once dirname(__FILE__).'/../../include/settings.php';
$publisherObj = new Publishers();
$pub = $publisherObj->deletePublisher($_GET);;
echo json_encode(['success' => true, 'message' => 'Deleted Successfully!']);