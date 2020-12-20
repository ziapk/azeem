<?php 
include_once dirname(__FILE__).'/../include/settings.php';
$suppliers = new Suppliers();
$shopInfo = []; 
$search = [];
if(!empty($_SESSION['shopInfo'])) {
    $shopInfo = $_SESSION['shopInfo'];
    $search = $suppliers->searchSupplier(!empty($_GET['term']) ? $_GET['term'] : "");
};
echo json_encode($search);
?>