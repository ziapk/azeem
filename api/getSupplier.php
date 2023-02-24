<?php 
include_once dirname(__FILE__).'/../include/settings.php';
$suppliers = new Suppliers();
global $shop; 
$search = [];
if(!empty($_SESSION['shop'])) {
    $search = $suppliers->searchSupplier(!empty($_GET['term']) ? $_GET['term'] : "", $shop['id']);
};
echo json_encode($search);
?>