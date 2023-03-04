<?php 
include_once dirname(__FILE__).'/../include/settings.php';
global $shop;
$customers = new DoubleEntry();
$search = $customers->getOpeningBalance($_GET['account_id']);
echo json_encode($search);
?>