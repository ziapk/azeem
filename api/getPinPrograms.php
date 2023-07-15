<?php
include_once dirname(__FILE__) . '/../include/settings.php';
$products = new Programs();
$search = $products->getPinPrograms();
echo json_encode($search);
