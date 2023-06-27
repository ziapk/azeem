<?php
include_once dirname(__FILE__) . '/../include/settings.php';
$suppliers = new Suppliers();
global $shop;
$search = [];
if (!empty($_SESSION['shop'])) {
    $type = !empty($_GET['type']) ? $_GET['type'] : 1;
    $search = $suppliers->searchSupplier(!empty($_GET['term']) ? $_GET['term'] : "", $shop['id'], $type);
};
echo json_encode($search);
